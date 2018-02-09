<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Symfony\Component\Finder\Finder;

class GetCoreutilsManPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'manpages:coreutils';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get coreutils man pages';

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
        echo "Installing prerequisite packages.\n";
        $packages = "autoconf automake autopoint bison gettext gperf git gzip perl rsync tar texinfo";
        echo "Installing: \n\t" . implode("\n\t", explode(' ', $packages)) . "\n";
        // Prerequisities
        $process = new Process("sudo apt-get -y install {$packages}");
        $process->run();

        $directory = storage_path() . '/linux-man-pages/coreutils';
        $github_url = 'https://github.com/coreutils/coreutils';
        echo "Fetching coreutils git repository ({$github_url})\n";
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

        echo "Bootstrapping coreutils.\n";
        $process = new Process("./bootstrap");
        $process->run();

        echo "Configuring coreutils.\n";
        $process = new Process("./configure");
        $process->run();

        echo "Running automake.\n";
        $process = new Process("make");
        $process->run();

        $section = 1;
        $process = new Process("sudo mkdir -p {$directory}/man/man{$section}");
        $process->run();
        $process = new Process("sudo mv {$directory}/man/*.{$section} {$directory}/man/man{$section}");
        $process->run();

        $finder = new Finder();
        $finder->files()->in($directory . '/man')->name("/(.*)\.(\d)/");

        echo "Searching for files in {$directory}/man\n";
        foreach($finder as $file) {
            $filename = $file->getFilename();
            preg_match('/(.*)\.(\d)/', $filename, $matches);
            $command_name = trim($matches[1]);
            $section = (int) $matches[2];

            // Generate HTML
            $process = new Process([
                'mman',
                '-T', 'html',
                '-M', $directory . '/man',
                $command_name
            ]);
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
                $in_section = false;
                $section_number = 0;
                $sections = [];

                foreach($root_div->childNodes as $sub_child) {
                    if ($sub_child->nodeName === 'h1') {
                        if ($in_section) {
                            $in_section = false;
                        }
                        else {
                            $in_section = true;
                            $section_number++;
                            $sections[$section_number] = [
                                'children' => [],
                                'id' => trim($sub_child->getAttribute('id')),
                            ];
                            $sub_child->removeAttribute('id');
                        }
                    }
                    if ($in_section) {
                        $sections[$section_number]['children'][] = $doc_body_only->importNode($sub_child, true);
                    }
                }

                $root_div = $doc_body_only->importNode($root_div);
                $doc_body_only->appendChild($root_div);

                foreach($sections as $_section) {
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
            // create document fragment
            $frag = $doc->createDocumentFragment();
            // create initial list
            $frag->appendChild($doc->createElement('ol'));
            $head = &$frag->firstChild;
            $xpath = new \DOMXPath($doc);
            $last = 1;

            // get all H1, H2, …, H6 elements
            foreach ($xpath->query('//*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]') as $headline) {
                // get level of current headline
                sscanf($headline->tagName, 'h%u', $curr);

                // move head reference if necessary
                if ($curr < $last) {
                    // move upwards
                    for ($i=$curr; $i<$last; $i++) {
                        $head = &$head->parentNode->parentNode;
                    }
                } else if ($curr > $last && $head->lastChild) {
                    // move downwards and create new lists
                    for ($i=$last; $i<$curr; $i++) {
                        $head->lastChild->appendChild($doc->createElement('ol'));
                        $head = &$head->lastChild->lastChild;
                    }
                }
                $last = $curr;

                // add list item
                $li = $doc->createElement('li');
                $head->appendChild($li);
                $a = $doc->createElement('a', htmlspecialchars($headline->textContent));
                $head->lastChild->appendChild($a);

                // build ID
                $levels = array();
                $tmp = &$head;
                // walk subtree up to fragment root node of this subtree
                while (!is_null($tmp) && $tmp != $frag) {
                    $levels[] = $tmp->childNodes->length;
                    $tmp = &$tmp->parentNode->parentNode;
                }
                $id = 'sect'.implode('.', array_reverse($levels));
                // set destination
                $a->setAttribute('href', '#'.$id);
                // add anchor to headline
                $a = $doc->createElement('a');
                $a->setAttribute('name', $id);
                $a->setAttribute('id', $id);
                $headline->insertBefore($a, $headline->firstChild);
            }

            // append fragment to document
            $toc_header = $doc->createElement('h5');
            $toc_header->nodeValue = 'Table of Contents';
            $toc_div->appendChild($toc_header);
            $toc_div->appendChild($frag);

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
                    $link = $doc->createElement('a');
                    $link->textContent = $text;
                    $link->setAttribute('href', \URL::to('/pages/' . $text));
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

            $time_zone = new \DateTimeZone('UTC');
            $updated_at_date = new \DateTime('now', $time_zone);

            $os = $dom->find('.foot-os')->text;

            echo sprintf("Section %s, Category '%-30s': %s\n", $section, $category, $command_name);

            $page = \App\Page::firstOrCreate(['name' => $command_name, 'source' => 'coreutils']);
            $page->section = (int)$section;
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

    public function trimAndClean(string $text) {
        // Remove redundant whitespace
        $text = preg_replace("/\s\s+/", ' ', $text);

        $text = trim($text);

        return $text;
    }
}
