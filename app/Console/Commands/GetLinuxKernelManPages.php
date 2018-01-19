<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

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
    // s390
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
            echo $process->getErrorOutput();
            exit();
        }

        echo $process->getOutput();

        for($section = 1; $section <= 8; $section++)
        {
            $section_dir = $directory . "/man$section";

            $files = array_diff(scandir($section_dir), array('..', '.'));
            foreach($files as $file_name)
            {
                preg_match('/(.*)\.(\d)/', $file_name, $matches);
                $command_name = $matches[1];
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
                $doc_body_only = new \DOMDocument;
                $body = $doc->getElementsByTagName('body')->item(0);
                foreach($body->childNodes as $child) {
                    $doc_body_only->appendChild($doc_body_only->importNode($child, true));
                }

                $body_html = $doc_body_only->saveHTML();

                // Strip out <table class='head'> tag
                $html = mb_eregi_replace("<\s*table\s*class=\"head\"\s*[^>]*>(.*?)</\s*table\s*>", '', $html);

                $dom = new \PHPHtmlParser\Dom();
                $dom->load($body_html);

                $category = $dom->find('.head-vol')->text;
                if (isset($this->category_synonyms[strtolower($category)]))
                {
                    $category = $this->category_synonyms[strtolower($category)];
                }

                echo sprintf("Section %s, Category '%-30s': %s\n", $section, $category, $command_name);

                $page = \App\Page::firstOrCreate(['name' => $command_name]);
                $page->section = (int)$section;
                $page->category = trim($category);
                $page->raw_html = trim($html);
                $page->save();
            }
        }

    }
}
