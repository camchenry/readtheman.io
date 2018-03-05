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
        $finder->files()->in($directory . '/Documentation')->name("/(.*)\.(\d\w?)$/");

        echo "Searching for files in {$directory}/man\n";
        foreach($finder as $file) {
            $filename = $file->getFilename();
            preg_match('/(.*)\.(\d\w?)/', $filename, $matches);
            $page_name = trim($matches[1]);
            $section = trim($matches[2]);

            echo "Section {$section}, {$page_name}\n";

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

            $html = ImportHelper::makeHtmlForManPage($page_name, $section, $directory . '/Documentation/man');
            $raw_html = $html;

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

            // Replace "file:///" links
            // Example: "file:///var/www/readtheman.io/storage/man_pages/git/share/doc/git-doc/technical/api-credentials.html"
            $html = mb_eregi_replace("file:///(.*)/git-doc/(.*\.html)", '<a href="https://www.kernel.org/pub/software/scm/git/docs/\\2">https://www.kernel.org/pub/software/scm/git/docs/\\2</a>', $html);

            // Scraping to do with pre-processed HTML
            $info = ImportHelper::extractInfo($body_html);

            if ($info['os']) {
                preg_match('/^(Git \d+\.\d+\.\d+).*$/', $info['os'], $matches);
                $os = $matches[1];
                if (empty($os)) {
                    throw new Exception("{$page_name}: OS was empty");
                }
            }

            $category = $info['category'];

            if (empty($info['category'])) {
                $category = 'Git Manual';
            }

            $record = [
                'name' => $page_name,
                'source' => 'Git',
                'section' => $section,
                'category' => $category,
                'html' => $html,
                'raw_html' => $raw_html,
                'short_description' => $info['short_description'] ?? null,
                'description' => $info['description'] ?? null,
                'page_updated_date' => new \DateTime('now'),
                'table_of_contents_html' => $table_of_contents_html,
                'os' => $os ?? null
            ];

            $page = ImportHelper::createPage($record);
        }
    }
}
