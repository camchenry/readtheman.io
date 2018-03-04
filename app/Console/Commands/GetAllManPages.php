<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GetAllManPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'manpages:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get all available man pages';

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
        echo "\n#\n# Linux kernel\n#\n";
        $this->call('manpages:kernel');

        echo "\n#\n# POSIX\n#\n";
        $this->call('manpages:posix');

        echo "\n#\n# Git\n#\n";
        $this->call('manpages:git');

        echo "\n#\n# Coreutils\n#\n";
        $this->call('manpages:coreutils');
    }
}
