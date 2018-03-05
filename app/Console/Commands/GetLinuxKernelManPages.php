<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Symfony\Component\Finder\Finder;
use App\ImportHelper;

class GetLinuxKernelManPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'manpages:kernel {--fast}';

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
        $page_queue = [];

        if ($this->option('fast')) {
            $pages = \App\Page::where('source', '=', 'Linux kernel')->get();
            foreach($pages as $page) {
                array_push($page_queue, [
                    'name' => trim($page->name),
                    'section' => trim($page->section),
                    'raw_html' => trim($page->raw_html),
                ]);
            }
        }
        else {
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

            $finder = new Finder();
            $finder->files()
                ->in($directory)
                ->depth('== 1')
                ->name("/(.*)\.(\d\w?)$/");

            $this->info("Searching for man pages in {$directory}");

            foreach($finder as $file)
            {
                $filename = $file->getFilename();
                preg_match('/(.*)\.(\d\w?)$/', $filename, $matches);
                $page_name = trim($matches[1]);
                $section = trim($matches[2]);
                $raw_html = ImportHelper::makeHtmlForManPage($page_name, $section, $directory);

                array_push($page_queue, [
                    'name' => trim($page_name),
                    'section' => trim($section),
                    'raw_html' => trim($raw_html),
                ]);
            }
        }

        $progress_bar = $this->output->createProgressBar(count($page_queue));

        foreach($page_queue as $page)
        {
            $page_name = $page['name'];
            $section = $page['section'];
            $raw_html = $page['raw_html'];
            $html = $raw_html;

            $this->output->write("\tSection {$section}, {$page_name}");

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
                'raw_html' => $raw_html,
                'short_description' => $info['short_description'] ?? null,
                'description' => $info['description'] ?? null,
                'page_updated_date' => $info['page_updated_date'],
                'table_of_contents_html' => $table_of_contents_html,
                'os' => $info['os'] ?? null,
            ];

            $page = ImportHelper::createPage($record);

            $progress_bar->advance();
        }

        $progress_bar->finish();
    }
}
