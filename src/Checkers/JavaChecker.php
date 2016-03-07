<?php
namespace App\Checkers;

use Parsers\MavenParser;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 04-03-2016
 * Time: 13:07
 */
class JavaChecker extends ProjectChecker
{
    public function doLanguageSpecificProcessing()
    {
        $checkstyleConfigFile = $checkstyleDependency = $checkstyleBuildTask = false;
        $pmdConfigFile = $pmdDependency = $pmdBuildTask = false;

        if ($this->project->usesBuildTool('maven')) {
            $mavenConfig = new MavenParser($this->project->getFile('pom.xml'));

            // Checkstyle
            $checkstyleDependency = $mavenConfig->hasBuildPlugin('checkstyle');
            $checkstyleBuildTask = $mavenConfig->buildIncludesPlugin('checkstyle');
            $checkstyleConfigFile = $mavenConfig->hasCustomCheckstyleConfig();

            // PMD
            $pmdDependency = $mavenConfig->hasBuildPlugin('pmd');
            $pmdBuildTask = $mavenConfig->buildIncludesPlugin('pmd');
            $pmdConfigFile = $mavenConfig->hasCustomPMDConfig();
        }
        if ($this->project->usesBuildTool('ant')) {
            //TODO ant
        }
        if ($this->project->usesBuildTool('gradle')) {
            //TODO gradle
        }

        // config files may still be present without being mentioned in the build tool, maybe for use in IDE?
        if ($this->project->buildTools()->count() == 0) {
            $checkstyleConfigFile = $this->existsInProjectFiles('www.puppycrawl.com/dtds/configuration extension:xml');
            $pmdConfigFile = $this->existsInProjectFiles('pmd.sourceforge.net/ruleset extension:xml');
        }

        $checkstyle = $this->attachASAT('checkstyle', $checkstyleConfigFile, $checkstyleDependency, $checkstyleBuildTask);
        $pmd = $this->attachASAT('pmd', $pmdConfigFile, $pmdDependency, $pmdBuildTask);

        return $checkstyle || $pmd;
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
