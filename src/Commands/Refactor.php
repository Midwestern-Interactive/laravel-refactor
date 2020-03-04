<?php

namespace MWI\LaravelRefactor\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use MWI\LaravelRefactor\Refactorer;

class Refactor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string  Options: videos
     */
    protected $signature = 'refactor
        {--rollback : Whether to rollback existing refactors}
        {--steps=0 : The number of steps to run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run database refactors';


    /**
     * The refactorer instance
     *
     * @var \App\Services\Refactorer
     */
    protected $refactorer;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Refactorer $refactorer)
    {
        parent::__construct();

        $this->refactorer = $refactorer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $paths = [$this->laravel->basePath() . '/database/refactors'];

        return $this->refactorer->run($paths, $this->option('rollback'), $this->option('steps'));
    }
}
