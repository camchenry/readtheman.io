<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Symfony\Component\Finder\Finder;
use App\ImportHelper;

class GetPosixManPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'manpages:posix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get posix man pages';

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
        $directory = storage_path() . '/man_pages/posix';

        if (!file_exists($directory)) {
            mkdir($directory, 0775);
        }

        $file_url = 'https://www.kernel.org/pub/linux/docs/man-pages/man-pages-posix/man-pages-posix-2013-a.tar.gz';
        $tar_file_name = $directory . '/man-pages-pos-2013.tar';
        $directory = mb_substr($tar_file_name, 0, -4);

        if (!file_exists($tar_file_name)) {
            echo "Fetching posix man pages from '{$file_url}'\n";
            $ch = curl_init($file_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $tar_gz_file_contents = curl_exec($ch);
            echo "Unzipping file to '{$tar_file_name}'\n";
            $tar_file_contents = gzdecode($tar_gz_file_contents);
            file_put_contents($tar_file_name, $tar_file_contents);
        }

        if (!file_exists($directory)) {
            echo "Extracting tar file...\n";
            $tar = new \PharData($tar_file_name);
            $tar->extractTo($directory);
        }

        $directory = $directory . '/man-pages-posix-2013-a';

        $finder = new Finder();
        $finder->files()
            ->in($directory)
            ->depth('< 2')
            ->name("/(.*)\.(\d\w?)$/");

        echo "Searching for man pages in {$directory}\n";

        foreach($finder as $file) {
            $filename = $file->getFilename();
            preg_match('/(.*)\.(\d\w?)$/', $filename, $matches);
            $page_name = trim($matches[1]);
            $section = trim($matches[2]);

            echo sprintf("Page: %-18s Section: %s\n", $page_name, $section);

            $html = ImportHelper::makeHtmlForManPage($page_name, $section, $directory);
            $doc = ImportHelper::createSectionedDocument($html);

            /*
             * Table of Contents
             */
            $table_of_contents = ImportHelper::makeTableOfContents($doc);
            $table_of_contents_html = $doc->saveHTML($table_of_contents);

            // $doc = ImportHelper::addLinksToManPages($doc, $page_name);

            $body_html = $doc->saveHTML();

            // Strip out <table class='head'> tag
            $html = mb_eregi_replace("<\s*table\s*class=\"head\"\s*[^>]*>(.*?)</\s*table\s*>", '', $body_html);

            // Strip out <table class='head'> tag
            $html = mb_eregi_replace("<\s*table\s*class=\"foot\"\s*[^>]*>(.*?)</\s*table\s*>", '', $html);

            // Scraping to do with pre-processed HTML
            $info = ImportHelper::extractInfo($body_html);

            $record = [
                'name' => $page_name,
                'source' => 'POSIX',
                'section' => $section,
                'category' => $info['category'],
                'html' => $html,
                'short_description' => $info['short_description'],
                'description' => $info['description'],
                'page_updated_date' => $info['page_updated_date'],
                'table_of_contents_html' => $table_of_contents_html,
                'os' => $info['os'],
            ];

            $page = ImportHelper::createPage($record);
        }
    }
}
