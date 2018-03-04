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
        $directory = storage_path() . '/man_pages/kernel';
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

        for($section = 1; $section <= 8; $section++)
        {
            $section_dir = $directory . "/man$section";

            $files = array_diff(scandir($section_dir), array('..', '.'));
            $oses = [];
            foreach($files as $file_name)
            {
                preg_match('/(.*)\.(\d\w?)/', $file_name, $matches);
                $page_name = trim($matches[1]);
                $section = trim($matches[2]);

                echo sprintf("Section %s, %s\n", $section, $page_name);

                $html = ImportHelper::makeHtmlForManPage($page_name, $section, $directory);

                $doc = ImportHelper::createSectionedDocument($html);

                /*
                 * Table of Contents
                 */
                $table_of_contents = ImportHelper::makeTableOfContents($doc);
                $table_of_contents_html = $doc->saveHTML($table_of_contents);

                $doc = ImportHelper::addLinksToManPages($doc, $page_name);

                $body_html = $doc->saveHTML();

                // Strip out <table class='head'> tag
                $html = mb_eregi_replace("<\s*table\s*class=\"head\"\s*[^>]*>(.*?)</\s*table\s*>", '', $body_html);

                // Strip out <table class='head'> tag
                $html = mb_eregi_replace("<\s*table\s*class=\"foot\"\s*[^>]*>(.*?)</\s*table\s*>", '', $html);

                $info = ImportHelper::extractInfo($body_html);

                $record = [
                    'name' => $page_name,
                    'source' => 'Linux kernel',
                    'section' => $section,
                    'category' => $info['category'],
                    'html' => $html,
                    'short_description' => $info['short_description'] ?? null,
                    'description' => $info['description'] ?? null,
                    'page_updated_date' => $info['page_updated_date'],
                    'table_of_contents_html' => $table_of_contents_html,
                    'os' => $info['os'] ?? null,
                ];

                $page = ImportHelper::createPage($record);
            }
        }

    }
}
