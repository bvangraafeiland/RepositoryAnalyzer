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
    use GithubApi;

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
    public function checkRubyASATS(Repository $project)
    {
        $hasConfigFile = (bool) $project->getFile('.rubocop.yml');
        $hasDependency = $project->fileContains('Gemfile', 'rubocop');
        $hasBuildTask = $project->fileContains('Rakefile', 'rubocop');

        if (!$hasDependency && $hasBuildTask) {
            // Check for gemspec file, project may be a ruby gem
            $projectRootFiles = array_pluck($this->github->getContent($project->full_name), 'name');
            $gemspec = $this->findGemspecFile($projectRootFiles);
            $hasDependency = $gemspec && $project->fileContains($gemspec, 'rubocop');
        }

        return $this->attachASAT($project, 'rubocop', $hasConfigFile, $hasDependency, $hasBuildTask);
    }

    protected function findGemspecFile(array $filenames)
    {
        foreach ($filenames as $filename) {
            if (preg_match("%(.*).gemspec%", $filename))
                return $filename;
        }
        return null;
    }

    public function checkPythonASATS(Repository $project)
    {
        $hasConfigFile = $project->getFile('pylintrc') || $project->getFile('.pylintrc');
        $hasDependency = $project->fileContains('setup.py', 'pylint') || $project->fileContains('requirements.txt', 'pylint');
        $hasBuildTask = $project->fileContains('tox.ini', 'pylint');

        return $this->attachASAT($project, 'pylint', $hasConfigFile, $hasDependency, $hasBuildTask);
    }

    public function checkJavascriptASATS(Repository $project)
    {
        $projectRootFiles = array_pluck($this->github->getContent($project->full_name), 'name');
        $package = json_decode($project->getFile('package.json'), true);
        $devDependencies = $package['devDependencies'];

        if (in_array('Gruntfile.js', $projectRootFiles)) {
            $buildFile = $project->getFile('Gruntfile.js');
        }
        elseif (in_array('gulpfile.js', $projectRootFiles)) {
            $buildFile = $project->getFile('gulpfile.js');
        }
        else
            $buildFile = null;

        // jshint
        $jshintConfigFile = in_array('.jshintrc', $projectRootFiles) || array_has($package, 'jshintConfig');
        $jshintDependency = str_contains(json_encode($devDependencies), 'jshint');
        $jshintBuildTask = str_contains($buildFile, 'jshint');

        $jshint = $this->attachASAT($project, 'jshint', $jshintConfigFile, $jshintDependency, $jshintBuildTask);


        // jscs
        $jscsConfigFile = in_array('.jscsrc', $projectRootFiles) || array_has($package, 'jscsConfig');
        $jscsDependency = str_contains(json_encode($devDependencies), 'jscs');
        $jscsBuildTask = str_contains($buildFile, 'jscs');

        $jscs = $this->attachASAT($project, 'jscs', $jscsConfigFile, $jscsDependency, $jscsBuildTask);


        // eslint
        $eslintConfigFile = (bool) array_intersect(['.eslintrc', '.eslintrc.js', '.eslintrc.json', '.eslintrc.yml'], $projectRootFiles) || array_has($package, 'eslintConfig');
        $eslintDependency = str_contains(json_encode($devDependencies), 'eslint');
        $eslintBuildTask = str_contains($buildFile, 'eslint');

        $eslint = $this->attachASAT($project, 'eslint', $eslintConfigFile, $eslintDependency, $eslintBuildTask);

        return $jshint || $jscs || $eslint;
    }

    public function checkJavaASATS(Repository $project)
    {

    }

    protected function attachASAT(Repository $project, $asatName, $config_file_present, $in_dev_dependencies, $in_build_tool)
    {
        if (!($config_file_present || $in_dev_dependencies || $in_build_tool)) {
            return false;
        }

        $tool = AnalysisTool::whereName($asatName)->first();
        $project->asats()->attach($tool, compact('config_file_present', 'in_dev_dependencies', 'in_build_tool'));
        $project->asat_in_build_tool = (bool) $in_build_tool;

        return true;
    }
}
