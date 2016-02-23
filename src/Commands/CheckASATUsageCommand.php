<?php
namespace App\Commands;

use App\AnalysisTool;
use App\Repository;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 21-02-2016
 * Time: 16:03
 */
class CheckASATUsageCommand extends CheckUsageCommand
{
    protected $projectProperty = 'ASAT';

    protected function configure()
    {
        $this->setName('check:asats')->setDescription('Update repositories of the given language with information on ASAT usage')
            ->addArgument('language', InputArgument::REQUIRED, 'Language to filter projects');
    }

    protected function updateProject(Repository $project)
    {
        $project->uses_asats = $this->{'check' . ucfirst($project->language) . 'ASATS'}($project);
        $project->save();
    }

    /**
     * - Configuration file in root
     * - Mentioned in Rakefile
     * - Added in Gemfile - "gem 'rubocop'"
     *
     * @param Repository $project
     *
     * @return bool
     */
    protected function checkRubyASATS(Repository $project)
    {
        $hasConfigFile = (bool) $project->getFile('.rubocop.yml');
        $hasDependency = $project->fileContains('Gemfile', 'rubocop');
        $hasBuildTask = $project->fileContains('Rakefile', 'rubocop');

        return $this->checkASATS($project, 'rubocop', $hasConfigFile, $hasDependency, $hasBuildTask);
    }

    protected function checkASATS(Repository $project, $asatName, $config_file_present, $in_dev_dependencies, $in_build_tool)
    {
        if (!($config_file_present || $in_dev_dependencies || $in_build_tool)) {
            return false;
        }

        $tool = AnalysisTool::whereName($asatName)->first();
        $project->asats()->attach($tool, compact('config_file_present', 'in_dev_dependencies', 'in_build_tool'));
        $project->asat_in_build_tool = (bool) $in_build_tool;

        return true;
    }

    protected function checkPythonASATS(Repository $project)
    {
        //TODO check entire file tree instead of 5 requests
        $hasConfigFile = $project->getFile('pylintrc') || $project->getFile('.pylintrc');
        $hasDependency = $project->fileContains('setup.py', 'pylint') || $project->fileContains('requirements.txt', 'pylint');
        $hasBuildTask = $project->fileContains('tox.ini', 'pylint');

        return $this->checkASATS($project, 'pylint', $hasConfigFile, $hasDependency, $hasBuildTask);
    }

    protected function checkJavascriptASATS(Repository $project)
    {

    }

    protected function checkJavaASATS(Repository $project)
    {

    }
}
