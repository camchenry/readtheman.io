<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use App\ImportHelper;

class GetLinuxKernelManPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'manpages:kernel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get linux kernel man pages';

    /*
     * Map lowercased category names to the real category name
     *
     * @var array[string] = string
     */
    public $category_synonyms = [
        "linux user's manual"        => "Linux User's Manual",
        "linux user manual"          => "Linux User's Manual",

        "linux programmer's manual"  => "Linux Programmer's Manual",
        "linux programmer'smanual"   => "Linux Programmer's Manual",
        "linuxprogrammer's manual"   => "Linux Programmer's Manual",

        "library functions manual"   => "Library Functions Manual",

        "user commands"              => "User Commands",

        "linux system calls"         => "Linux System Calls",
        "system calls manual"        => "Linux System Calls",

        "linux key management calls" => "Linux Key Management Calls",

        "linux system administration" => "Linux System Administration",

        "miscellaneous information manual" => "Miscellaneous Information Manual",
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $directory = storage_path() . '/linux-man-pages/kernel';
        $github_url = 'https://github.com/mkerrisk/man-pages.git';
        if (!file_exists($directory)) {
            $repository = \Gitonomy\Git\Admin::cloneTo($directory, $github_url, false);
        }
        else {
            $process = new Process("sudo su && cd $directory && git pull");
            $process->run();

            if (!$process->isSuccessful())
            {
                exit($process->getErrorOutput());
            }
        }

        $process = new Process(array(
            'sudo',
            'chown',
            '-R',
            'www-data:www-data',
            storage_path() . '/linux-man-pages',
        ));
        $process->run();

        if (!$process->isSuccessful())
        {
            exit($process->getErrorOutput());
        }

        echo $process->getOutput();

        for($section = 1; $section <= 8; $section++)
        {
            $section_dir = $directory . "/man$section";

            $files = array_diff(scandir($section_dir), array('..', '.'));
            $oses = [];
            foreach($files as $file_name)
            {
                preg_match('/(.*)\.(\d)/', $file_name, $matches);
                $command_name = trim($matches[1]);
                $section = (int) $matches[2];

                // Generate HTML
                $process = new Process(array(
                    'mman',
                    '-T', 'html',
                    '-M', $directory,
                    $command_name
                ));
                $process->run();

                if (!$process->isSuccessful())
                {
                    echo $process->getErrorOutput();
                    exit();
                }

                $html = $process->getOutput();

                $doc = new \DOMDocument;
                $doc->loadXML($html);

                // Strip out everything but the HTML in the <body> tag
                // and add sectioning elements
                $doc_body_only = new \DOMDocument;
                $body = $doc->getElementsByTagName('body')->item(0);
                $root_div = null;
                foreach($body->childNodes as $child) {
                    if ($child->nodeName === 'div') {
                        $root_div = $child;
                        continue;
                    }
                    else {
                        $doc_body_only->appendChild($doc_body_only->importNode($child, true));
                    }
                }

                $toc_div = $doc_body_only->createElement('div');
                $toc_div->setAttribute('id', 'table_of_contents');
                $doc_body_only->appendChild($toc_div);

                // Add sectioning elements and rearrange section IDs
                if ($root_div) {
                    $section_number = 0;
                    $sections = [];
                    $in_section = false;

                    foreach($root_div->childNodes as $sub_child) {
                        if ($sub_child->nodeName === 'h1') {
                            $in_section = true;
                            $section_number++;
                            $sections[$section_number] = [
                                'children' => [],
                                'id' => trim($sub_child->getAttribute('id')),
                            ];
                            $sub_child->removeAttribute('id');
                        }
                        if ($in_section) {
                            $sections[$section_number]['children'][] = $doc_body_only->importNode($sub_child, true);
                        }
                    }

                    $root_div = $doc_body_only->importNode($root_div);
                    $doc_body_only->appendChild($root_div);

                    foreach($sections as $key => $_section) {
                        $parent_div = $doc_body_only->createElement('section');
                        $parent_div->setAttribute('id', $_section['id']);
                        $root_div->appendChild($parent_div);
                        foreach($_section['children'] as $child) {
                            $parent_div->appendChild($child);
                        }
                    }
                }

                $doc = $doc_body_only;


                /*
                 * Table of Contents
                 */
                $table_of_contents = ImportHelper::makeTableOfContents($doc);

                // append fragment to document
                $toc_header = $doc->createElement('h5');
                $toc_header->nodeValue = 'Table of Contents';
                $toc_div->appendChild($toc_header);
                $toc_div->appendChild($table_of_contents);

                // Add hyperlinks to other pages
                $bolded = $doc->getElementsByTagName('b');
                for($i = 0; $i < $bolded->length; $i++) {
                    $bold = $bolded->item($i);
                    $text = trim($bold->textContent);

                    if (empty($text)) {
                        continue;
                    }

                    if ($text === $command_name) {
                        continue;
                    }

                    if (\App\Page::where('name', '=', $text)->exists()) {
                        $page = \App\Page::where('name', '=', $text)->first();
                        $link = $doc->createElement('a');
                        $link->textContent = $text;
                        $link->setAttribute('href', \URL::to('/pages/' . $page->section . '/' . $text));
                        $bold->textContent = '';
                        $bold->appendChild($link);
                    }
                }

                $body_html = $doc->saveHTML();

                // Strip out <table class='head'> tag
                $html = mb_eregi_replace("<\s*table\s*class=\"head\"\s*[^>]*>(.*?)</\s*table\s*>", '', $body_html);

                // Strip out <table class='head'> tag
                $html = mb_eregi_replace("<\s*table\s*class=\"foot\"\s*[^>]*>(.*?)</\s*table\s*>", '', $html);

                // Scraping to do with pre-processed HTML
                $dom = new \PHPHtmlParser\Dom();
                $dom->load($body_html);

                // Extract the category name, like "Linux programmer's manual"
                $category = $dom->find('.head-vol')->text;
                if (isset($this->category_synonyms[strtolower($category)]))
                {
                    $category = $this->category_synonyms[strtolower($category)];
                }

                $short_description = $dom->getElementById('#NAME')->text(true);
                $short_description = preg_replace('/NAME/', '', $short_description);

                $description_section = $dom->getElementById('#DESCRIPTION');
                if ($description_section) {
                    $description = $description_section->text(true);
                    $description = preg_replace('/DESCRIPTION/', '', $description);
                }


                // Extract the last updated time
                $updated_at = $dom->find('.foot-date')->text;
                $time_zone = new \DateTimeZone('UTC');
                $updated_at_date = \DateTime::createFromFormat('Y-m-d', $updated_at, $time_zone);

                // Prev date format failed, try a different one
                if (!$updated_at_date) {
                    $updated_at_date = \DateTime::createFromFormat('F j, Y', $updated_at, $time_zone);
                }

                $os = $dom->find('.foot-os')->text;

                echo sprintf("Section %s, Category '%-30s': %s\n", $section, $category, $command_name);

                $page = \App\Page::firstOrCreate(
                    [
                        'name' => trim($command_name),
                        'source' => 'Linux kernel',
                        'section' => $section,
                    ],
                    [
                        'name' => trim($command_name),
                        'source' => 'Linux kernel',
                        'section' => $section,
                    ]
                );
                $page->category = trim($category);
                $page->raw_html = trim($html);
                $page->short_description = $this->trimAndClean($short_description);
                $page->description = $this->trimAndClean($description);
                $page->page_updated_at = $updated_at_date->format('Y-m-d H:i:s');
                if (!empty($os)) {
                    $page->os = $this->trimAndClean($os);
                }
                $page->save();
            }
        }

    }

    public function trimAndClean(string $text) {
        // Remove redundant whitespace
        $text = preg_replace("/\s\s+/", ' ', $text);

        $text = trim($text);

        return $text;
    }
}
