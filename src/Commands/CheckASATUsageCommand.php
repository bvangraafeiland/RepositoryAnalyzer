<?php
namespace App\Commands;

use App\AnalysisTool;
use App\Repository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
        parent::configure();
        $this->setName('check:asats')->setDescription('Update repositories of the given language with information on ASAT usage')
            ->addOption('repository', null, InputOption::VALUE_REQUIRED, 'If provided, only this repository will be checked for ASAT usage');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasOption('repository')) {
            $project = Repository::whereFullName($input->getOption('repository'))->firstOrFail();
            $this->updateProject($project);
        }
        else
            parent::execute($input, $output);
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
        $hasBuildTask = $project->fileContains('Rakefile', 'rubocop') || $project->fileContains('Makefile', 'rubocop');

        if (!$hasDependency && $hasBuildTask) {
            // Check for gemspec file, project may be a ruby gem
            $projectRootFiles = array_pluck($this->github->getContent($project->full_name), 'name');
            $gemspec = $this->findGemspecFile($projectRootFiles);
            $hasDependency = $gemspec && $project->fileContains($gemspec, 'rubocop');
        }

        $project->asat_in_build_tool = (bool) $hasBuildTask;

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
        $hasBuildTask = $project->fileContains('tox.ini', 'pylint') || $project->fileContains('Makefile', 'pylint');

        $project->asat_in_build_tool = (bool) $hasBuildTask;

        return $this->attachASAT($project, 'pylint', $hasConfigFile, $hasDependency, $hasBuildTask);
    }

    public function checkJavascriptASATS(Repository $project)
    {
        $projectRootFiles = array_pluck($this->github->getContent($project->full_name), 'name');
        $package = json_decode($project->getFile('package.json'), true);
        $dependencies = array_get($package, 'dependencies', []);
        $devDependencies = array_get($package, 'devDependencies', []);
        $dependenciesJSON = json_encode($dependencies) . json_encode($devDependencies);

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
        $jshintDependency = str_contains($dependenciesJSON, 'jshint');
        $jshintBuildTask = $this->searchCode($buildFile, 'jshint');

        $jshint = $this->attachASAT($project, 'jshint', $jshintConfigFile, $jshintDependency, $jshintBuildTask);

        // jscs
        $jscsConfigFile = in_array('.jscsrc', $projectRootFiles) || array_has($package, 'jscsConfig');
        $jscsDependency = str_contains($dependenciesJSON, 'jscs');
        $jscsBuildTask = $this->searchCode($buildFile, 'jscs');

        $jscs = $this->attachASAT($project, 'jscs', $jscsConfigFile, $jscsDependency, $jscsBuildTask);

        // eslint
        $eslintConfigFile = (bool) array_intersect(['.eslintrc', '.eslintrc.js', '.eslintrc.json', '.eslintrc.yml', '.eslintrc.yaml'], $projectRootFiles) || array_has($package, 'eslintConfig');
        $eslintDependency = str_contains($dependenciesJSON, 'eslint');
        $eslintBuildTask = $this->searchCode($buildFile, 'eslint');

        $eslint = $this->attachASAT($project, 'eslint', $eslintConfigFile, $eslintDependency, $eslintBuildTask);

        $project->asat_in_build_tool = $jshintBuildTask || $jscsBuildTask || $eslintBuildTask;

        return $jshint || $jscs || $eslint;
    }

    public function checkJavaASATS(Repository $project)
    {
        $projectRootFiles = array_pluck($this->github->getContent($project->full_name), 'name');

        $checkstyleConfigFile = $checkstyleDependency = $checkstyleBuildTask = false;
        $pmdConfigFile = $pmdDependency = $pmdBuildTask = false;

        if (in_array('pom.xml', $projectRootFiles)) {
            $buildFile = $project->getFile('pom.xml');
            $mavenPlugins = simplexml_load_string($buildFile)->build->plugins->children();

            foreach ($mavenPlugins as $plugin) {
                if ($plugin->artifactId == 'maven-checkstyle-plugin') {
                    $checkstyleDependency = true;
                    $configLocation = $plugin->configuration->configLocation;
                    $checkstyleConfigFile = $configLocation && $configLocation != 'sun_checks.xml' && $configLocation != 'google_checks.xml';
                    $checkstyleBuildTask = (bool) $plugin->executions->execution;
                }

                if ($plugin->artifactId == 'maven-pmd-plugin') {
                    $pmdDependency = true;

                }
            }

        }

        $checkstyle = $this->attachASAT($project, 'checkstyle', $checkstyleConfigFile, $checkstyleDependency, $checkstyleBuildTask);
        $pmd = $this->attachASAT($project, 'pmd', $pmdConfigFile, $pmdDependency, $pmdBuildTask);

        $project->asat_in_build_tool = $checkstyleBuildTask || $pmdBuildTask;

        return $checkstyle || $pmd;
    }

    protected function attachASAT(Repository $project, $asatName, $config_file_present, $in_dev_dependencies, $in_build_tool)
    {
        if (!($config_file_present || $in_dev_dependencies || $in_build_tool)) {
            return false;
        }

        $flags = compact('config_file_present', 'in_dev_dependencies', 'in_build_tool');
        $tool = AnalysisTool::whereName($asatName)->first();
        if ($project->asats()->getRelatedIds()->contains($tool->id))
            $project->asats()->updateExistingPivot($tool->id, $flags);
        else
            $project->asats()->attach($tool, $flags);

        return true;
    }

    /**
     * @param $file
     *
     * @param $term
     *
     * @param string $comment
     *
     * @return bool
     */
    protected function searchCode($file, $term, $comment = "//")
    {
        $jshintBuildTask = str_contains(preg_replace("%$comment.+%", "", $file), $term);

        return $jshintBuildTask;
    }
}
