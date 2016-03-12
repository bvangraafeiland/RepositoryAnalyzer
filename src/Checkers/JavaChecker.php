<?php
namespace App\Checkers;

use App\Parsers\AntParser;
use App\Parsers\GradleParser;
use App\Parsers\JavaBuildInfo;
use App\Parsers\MavenParser;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 04-03-2016
 * Time: 13:07
 */
class JavaChecker extends ProjectChecker
{
    protected $buildConfigs = [];

    public function doLanguageSpecificProcessing()
    {
        if ($this->project->usesBuildTool('maven')) {
            $this->buildConfigs[] = new MavenParser($this->project->getFile('pom.xml'));
        }
        if ($this->project->usesBuildTool('ant')) {
            $this->buildConfigs[] = new AntParser($this->project->getFile('build.xml'));
        }
        if ($this->project->usesBuildTool('gradle')) {
            $this->buildConfigs[] = new GradleParser($this->project->getFile('build.gradle'));
        }

        $checkstyle = $this->checkFor('checkstyle');
        $pmd = $this->checkFor('pmd');

        return $checkstyle || $pmd;
    }

    protected function checkFor($tool)
    {
        $dependency = $this->anyConfigDependenciesInclude($tool);
        $buildTask = $this->anyBuildIncludes($tool);
        $customConfigFile = $this->anyCustomConfigSpecifiedFor($tool);
        // || $this->{'projectContains'.ucfirst($tool).'File'}();
        // too slow, if configuration is not specified in build then assume it's not used

        //dd($tool, $dependency, $buildTask, $customConfigFile);

        return $this->attachASAT($tool, $customConfigFile, $dependency, $buildTask);
    }

    protected function anyBuildConfig(callable $callback)
    {
        return (bool) array_first($this->buildConfigs, $callback);
    }

    protected function anyCustomConfigSpecifiedFor($tool)
    {
        return $this->anyBuildConfig(function ($key, JavaBuildInfo $config) use ($tool) {
            $config->{'hasCustom'.ucfirst($tool).'Config'}();
        });
    }

    protected function anyConfigDependenciesInclude($plugin)
    {
        return $this->anyBuildConfig(function ($key, JavaBuildInfo $config) use ($plugin) {
            return $config->containsPlugin($plugin);
        });
    }

    protected function anyBuildIncludes($plugin)
    {
        return $this->anyBuildConfig(function ($key, JavaBuildInfo $config) use ($plugin) {
            return $config->hasPluginInBuild($plugin);
        });
    }

    protected function projectContainsPmdFile()
    {
        return $this->existsInProjectFiles('pmd.sourceforge.net/ruleset extension:xml');
    }

    protected function projectContainsCheckstyleFile()
    {
        return $this->existsInProjectFiles('www.puppycrawl.com/dtds/configuration extension:xml');
    }

    protected function getBuildTools()
    {
        return [
            'maven' => 'pom.xml',
            'ant' => 'build.xml',
            'gradle' => 'build.gradle'
        ];
    }
}
