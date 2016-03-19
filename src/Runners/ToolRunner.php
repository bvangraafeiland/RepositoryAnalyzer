<?php
namespace App\Runners;

use App\Repository;
use InvalidArgumentException;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 15-03-2016
 * Time: 15:40
 */
abstract class ToolRunner
{
    /**
     * @var Repository
     */
    protected $repository;
    protected $projectDir;
    protected $buildTool;
    protected $dependenciesInstalled;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->projectDir = absoluteRepositoriesDir() . '/' . $repository->full_name;
        $this->buildTool = $this->getBuildTool();
        $this->dependenciesInstalled = false;
    }

    public function run($tool)
    {
        $changedDir = chdir($this->projectDir);
        if (!$changedDir) {
            throw new InvalidArgumentException("Project directory {$this->repository->full_name} does not exist!");
        }

        $this->installDependencies();

        return $this->{'run' . ucfirst($tool)}();
    }

    protected function getBuildTool()
    {
        $buildTools = $this->repository->buildTools;
        if ($buildTools->count() == 1) {
            return $buildTools->first()->name;
        }

        //throw new Exception('Build tool cannot be determined.');
        return null;
    }

    protected function installDependencies()
    {
        if (!$this->dependenciesInstalled) {
            system($this->installDependenciesCommand(), $exitCode);
            $this->dependenciesInstalled = $exitCode === 0;
        }
    }

    // TODO dependencies needed? probably not if running tools directly
    abstract protected function installDependenciesCommand();

    // go to repo dir
    // figure out whether asat can be run as build tool task (edit: probably not the best idea)
    // if so, run as build tool task
    // else, extract configuration file and pass that as argument to run the asat directly if necessary

    // TODO
    // - Map output to GDC
    // - only run if at least 2 out of 3 asat properties true
}
