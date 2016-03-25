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
    protected $results;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->projectDir = absoluteRepositoriesDir() . '/' . $repository->full_name;
        $this->buildTool = $this->getBuildTool();
        $this->results = [];
    }

    public function run($tool)
    {
        $changedDir = @chdir($this->projectDir);
        if (!$changedDir) {
            throw new InvalidArgumentException("Project directory {$this->repository->full_name} does not exist, clone it first!");
        }
        
        $this->results[$tool] = $this->{'run' . ucfirst($tool)}();
    }

    protected function getBuildTool()
    {
        $buildTools = $this->repository->buildTools;
        if ($buildTools->count() == 1) {
            return $buildTools->first()->name;
        }
        return null;
    }

    /**
     * @param $tool
     *
     * @return mixed
     */
    abstract public function numberOfWarnings($tool);

    // go to repo dir
    // figure out whether asat can be run as build tool task (edit: probably not the best idea)
    // if so, run as build tool task
    // else, extract configuration file and pass that as argument to run the asat directly if necessary

    // TODO
    // - Map output to GDC
    // - only run if at least 2 out of 3 asat properties true
}
