<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Symfony\Component\Finder\Finder;
use App\ImportHelper;

class GetGitManPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'manpages:git';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get git man pages';

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
        //
        // Prerequisities
        //
        echo "Installing prerequisite packages.\n";
        $packages = "asciidoc xmlto";
        echo "Installing: \n\t" . implode("\n\t", explode(' ', $packages)) . "\n";
        $process = new Process("sudo apt-get -y install {$packages}");
        $process->run();

        $directory = storage_path() . '/man_pages/git';
        $github_url = 'https://github.com/git/git';
        if (!file_exists($directory)) {
            echo "Cloning git git repository ({$github_url}) to {$directory}\n";
            $repository = \Gitonomy\Git\Admin::cloneTo($directory, $github_url, false);
        }
        else {
            echo "Updating git git repository ({$github_url}) in {$directory}\n";
            $process = new Process("cd {$directory} && git pull");
            $process->run();

            if (!$process->isSuccessful())
            {
                exit($process->getErrorOutput());
            }
        }

        $commands = [
            [
                'message' => "Building documentation.\n",
                'command' => "cd {$directory} && make doc",
                'timeout' => 900,
            ],
        ];

        foreach($commands as $command) {
            if (isset($command['message'])) {
                echo $command['message'];
            }
            $process = new Process($command['command']);
            $process->setTimeout($command['timeout'] ?? 60);
            $process->run();
            if (!$process->isSuccessful())
            {
                exit($process->getErrorOutput());
            }
            echo $process->getOutput();
        }

        $finder = new Finder();
        $finder->depth('== 0');
        $finder->files()->in($directory . '/Documentation')->name("/(.*)\.(\d)$/");

        echo "Searching for files in {$directory}/man\n";
        foreach($finder as $file) {
            $filename = $file->getFilename();
            preg_match('/(.*)\.(\d)/', $filename, $matches);
            $page_name = trim($matches[1]);
            $section = (int) $matches[2];

            $commands = [
                [
                    'command' => "mkdir -p {$directory}/Documentation/man/man{$section}",
                ],
                [
                    'command' => "cp {$directory}/Documentation/$filename {$directory}/Documentation/man/man{$section}",
                ]
            ];

            foreach($commands as $command) {
                if (isset($command['message'])) {
                    echo $command['message'];
                }
                $process = new Process($command['command']);
                $process->setTimeout($command['timeout'] ?? 60);
                $process->run();
                if (!$process->isSuccessful())
                {
                    exit($process->getErrorOutput());
                }
                echo $process->getOutput();
            }

            // Generate HTML
            $process = new Process([
                'mman',
                '-T', 'html',
                '-M', $directory . '/Documentation/man',
                $page_name
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

            // Add sectioning elements and rearrange section IDs
            if ($root_div) {
                $in_section = false;
                $section_number = 0;
                $sections = [];

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
            $table_of_contents = ImportHelper::makeTableOfContents($doc);
            $table_of_contents_html = $doc->saveHTML($table_of_contents);

            // Add hyperlinks to other pages
            $bolded = $doc->getElementsByTagName('b');
            for($i = 0; $i < $bolded->length; $i++) {
                $bold = $bolded->item($i);
                $text = trim($bold->textContent);

                if (empty($text)) {
                    continue;
                }

                if ($text === $page_name) {
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
            if ($os) {
                preg_match('/^(Git \d+\.\d+\.\d+).*$/', $os, $matches);
                $os = $matches[1];
                if (empty($os)) {
                    exit("{$page_name}: OS was empty");
                }
            }

            echo sprintf("Section %s, Category '%-30s': %s\n", $section, $category, $page_name);

            $page = \App\Page::firstOrCreate(
                [
                    'name' => trim($page_name),
                    'source' => 'Git',
                    'section' => $section,
                ],
                [
                    'name' => trim($page_name),
                    'source' => 'Git',
                    'section' => $section,
                ]
            );
            $page->category = trim($category);
            $page->raw_html = trim($html);
            $page->short_description = $this->trimAndClean($short_description);
            $page->description = $this->trimAndClean($description);
            $page->page_updated_at = $updated_at_date->format('Y-m-d H:i:s');
            $page->table_of_contents_html = $table_of_contents_html;
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
