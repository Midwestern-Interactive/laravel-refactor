<?php

namespace MWI\LaravelRefactor\Commands;

use Illuminate\Console\Command;

class MakeRefactor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string  Options: videos
     */
    protected $signature = 'make:refactor
        {refactor : The snake case name of the refactor e.g. convert_relationship_to_many_to_many}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new refactor';

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
        $name = $this->argument('refactor');
        $stubContent = file_get_contents(__DIR__.'/../stubs/refactor.php');
        $className = str_replace('_', '', ucwords($name, '_'));
        $fileContent = str_replace('RefactorName', $className, $stubContent);
        $fileName = date('Y_m_d_His') . '_' . $name . '.php';

        if (!file_exists(database_path('refactors'))) {
            mkdir(database_path('refactors'), 0777, true);
        }

        file_put_contents(database_path('refactors/') . $fileName, $fileContent);

        $this->info($fileName . ' created');
    }
}
