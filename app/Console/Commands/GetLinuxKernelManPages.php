<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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

        $process = new Process(array(
            'mman',
            '-T', 'html',
            '-M', $directory . '',
        ));
        $process->run();

        if (!$process->isSuccessful())
        {
            echo $process->getErrorOutput();
            exit();
        }

        echo $process->getOutput();
    }
}
