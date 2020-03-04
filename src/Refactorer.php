<?php

namespace MWI\LaravelRefactor;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;

class Refactorer
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new refactorer instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Run a series of refactors on the database
     *
     * @param array $paths The array of paths to check for refactors in
     * @param bool $rollback Whether we are rolling back refactors
     * @param int $steps The number of steps to refactor or rollback
     */
    public function run($paths = [], $rollback = false, $steps = 0)
    {
        $this->requireFiles($files = $this->getRefactorFiles($paths));

        $this->runRefactor(
            $refactors = $this->validateRefactors($files, $rollback, $steps),
            $rollback ? 'down' : 'up'
        );
    }

    /**
     * Get a collection of refactor files within the specified paths
     *
     * @param  array  $paths                 The paths in which to retrive files
     * @return array
     */
    public function getRefactorFiles($paths)
    {
        return Collection::make($paths)->flatMap(function ($path) {
            return Str::endsWith($path, '.php') ? [$path] : $this->files->glob($path.'/*_*.php');
        })->filter()->sortBy(function ($file) {
            return $this->getRefactorName($file);
        })->values()->keyBy(function ($file) {
            return $this->getRefactorName($file);
        })->all();
    }

    /**
     * Get the base name of the refactor negating extension
     *
     * @param  string $file  The file to parse
     * @return string        The base name
     */
    public function getRefactorName($file)
    {
        return str_replace('.php', '', basename($file));
    }

    /**
     * Require all nessecary files for refactoring
     *
     * @param  array $files The array of files to require
     */
    public function requireFiles($files)
    {
        foreach ($files as $file) {
            require_once $file;
        }
    }

    /**
     * Get a list of validated refactors based on whether they have been ran already
     *
     * @param  array                          $files    The array of files to check against
     * @param  array|string|null              $rollback Whether this is a rollback or not
     * @param  array|string|null              $steps    The number of steps to run
     * @return array                                    A collection of validated files
     */
    public function validateRefactors($files, $rollback, $steps)
    {
        $validated = Collection::make($files)->filter(function ($path, $name) use ($rollback) {
            return $this->validRefactor($name, $rollback);
        });

        if ($rollback) {
            $validated = $validated->reverse();
        }

        return $validated->take($steps ?: $validated->count())->all();
    }

    /**
     * Validate an individual refactor
     *
     * @param  string            $name     The name of the refactor
     * @param  array|string|null $rollback Whether this is a rollback or not
     * @return boolean                     The result of validation
     */
    protected function validRefactor($name, $rollback)
    {
        $exists = DB::table('refactors')->where('refactor', $name)->exists();

        return $rollback ? $exists : ! $exists;
    }

    /**
     * Run the the array of validated refactors
     * @param  array                          $refactors The array of refactors
     * @param  string                         $method    The method we're running
     */
    protected function runRefactor($refactors, $method)
    {
        foreach ($refactors as $name => $path) {
            $class = Str::studly(implode('_', array_slice(explode('_', $name), 4)));
            $refactor = new $class;

            if (method_exists($refactor, $method)) {
                $refactor->{$method}();
                $this->transaction($name, $method);
                echo $name . "\n";
            }
        }
    }

    /**
     * Run the database transaction for the refactor
     * @param string $name The name of the refactor
     * @param string $method The method we're running
     * @return bool
     */
    protected function transaction($name, $method)
    {
        if (! Schema::hasTable('refactors')) {
            return false;
        }

        return $method == 'up'
            ? DB::table('refactors')->insert(['refactor' => $name])
            : DB::table('refactors')->where('refactor', $name)->delete();
    }
}
