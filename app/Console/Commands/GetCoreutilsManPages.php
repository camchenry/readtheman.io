<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Symfony\Component\Finder\Finder;
use App\ImportHelper;

class GetCoreutilsManPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'manpages:coreutils {--fast}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get coreutils man pages';

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

        $directory = storage_path() . '/man_pages/coreutils';
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

        $section = 1;

        $commands = [
            [
                'command' => "sudo rm -rf {$directory}/autom4te.cache",
            ],
        ];

        if (!$this->option('fast')) {
            array_push($commands,
                [
                    'message' => "Bootstrapping coreutils.\n",
                    'command' => "cd {$directory} && ./bootstrap --skip-po",
                    'timeout' => 900,
                ]
            );
        }

        array_push($commands,
            [
                'message' => "Configuring coreutils.\n",
                'command' => "cd {$directory} && ./configure -C",
                'timeout' => 900,
            ],
            [
                'message' => "Running automake.\n",
                'command' => "cd {$directory} && make",
                'timeout' => 900,
            ],
            [
                'message' => "Creating man directory structure.\n",
                'command' => "mkdir -p {$directory}/man/man{$section}",
            ],
            [
                'message' => "Moving man pages into directories.\n",
                'command' => "mv {$directory}/man/*.{$section} {$directory}/man/man{$section}",
            ]
        );

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
        }

        $finder = new Finder();
        $finder->files()->in($directory . '/man')->name("/(.*)\.(\d)/");

        echo "Searching for files in {$directory}/man\n";
        foreach($finder as $file) {
            $filename = $file->getFilename();
            preg_match('/(.*)\.(\d)/', $filename, $matches);
            $page_name = trim($matches[1]);
            $section = trim($matches[2]);

            echo "Section {$section}, {$page_name}\n";

            $html = ImportHelper::makeHtmlForManPage($page_name, $section, $directory . '/man');
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

            $info = ImportHelper::extractInfo($body_html);

            $record = [
                'name' => $page_name,
                'source' => 'Coreutils',
                'section' => $section,
                'category' => $info['category'],
                'html' => $html,
                'raw_html' => $raw_html,
                'short_description' => $info['short_description'] ?? null,
                'description' => $info['description'] ?? null,
                'page_updated_date' => new \DateTime('now'),
                'table_of_contents_html' => $table_of_contents_html,
                'os' => $info['os'] ?? null,
            ];

            $page = ImportHelper::createPage($record);
        }
    }
}
