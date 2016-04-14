<?php
namespace App\Checkers;

use App\AnalysisTool;
use App\BuildTool;
use App\GitHubClient;
use App\Repository;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 04-03-2016
 * Time: 13:08
 */
abstract class ProjectChecker
{
    /**
     * @var GitHubClient
     */
    protected $github;

    /**
     * @var Repository
     */
    protected $project;

    protected $projectRootFiles;

    public function __construct(Repository $project)
    {
        $this->github = GitHubClient::getInstance();
        $this->project = $project;
    }

    public function check()
    {
        $this->projectRootFiles = array_pluck($this->github->getContent($this->project->full_name), 'name');

        $this->checkForBuildTools();

        return $this->doLanguageSpecificProcessing();
    }

    /**
     * @return bool
     */
    abstract protected function doLanguageSpecificProcessing();
    abstract protected function getBuildTools();

    protected function checkForBuildTools() {
        foreach ($this->getBuildTools() as $toolName => $fileName) {
            if (in_array($fileName, $this->projectRootFiles))
                $this->attachBuildTool($toolName);
        }
    }

    protected function attachBuildTool($toolName)
    {
        $tool = BuildTool::whereName($toolName)->firstOrFail();

        if (!$this->project->buildTools()->getRelatedIds()->contains($tool->id))
            $this->project->buildTools()->attach($tool);
    }

    /**
     * @param $file
     * @param $string
     *
     * @return bool
     */
    protected function rootFileContains($file, $string)
    {
        return in_array($file, $this->projectRootFiles) && $this->project->fileContains($file, $string);
    }

    protected function existsInProjectFiles($query)
    {
        $search = $this->github->searchInRepository($this->project->full_name, $query);
        return (bool) array_get($search, 'total_count');
    }

    /**
     * @param $asatName
     * @param $config_file_present
     * @param $in_dev_dependencies
     * @param $in_build_tool
     *
     * @return bool Whether the project uses an ASAT, e.g. any of the provided arguments are true
     */
    protected function attachASAT($asatName, $config_file_present, $in_dev_dependencies, $in_build_tool)
    {
        $tool = AnalysisTool::whereName($asatName)->first();
        $this->project->asats()->detach($tool);

        if (!($config_file_present || $in_dev_dependencies || $in_build_tool)) {
            return false;
        }

        $flags = compact('config_file_present', 'in_dev_dependencies', 'in_build_tool');
        $this->project->asats()->attach($tool, $flags);

        // When multiple ASATs are used, set this to true if any of them are used in the build tool.
        $this->project->asat_in_build_tool = ($this->project->isDirty('asat_in_build_tool') && $this->project->asat_in_build_tool) || $in_build_tool;

        return true;
    }
}
