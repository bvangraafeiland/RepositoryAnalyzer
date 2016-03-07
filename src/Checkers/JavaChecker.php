<?php
namespace App\Checkers;

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
            $pom = getXmlWithoutNamespace($this->project->getFile('pom.xml'));

            // Checkstyle
            $baseCheckstyleXpath = $this->getBaseXpathFor('checkstyle');
            $checkstyleDependency = (bool) $pom->xpath($baseCheckstyleXpath);

            $configFileLocation = $pom->xpath("$baseCheckstyleXpath//configuration/configLocation");
            // sun and google checks are default presets
            $checkstyleConfigFile = $configFileLocation && $configFileLocation[0] != 'sun_checks.xml' && $configFileLocation[0] != 'google_checks.xml';
            // either check or checkstyle goal can be used, the latter to also generate a report
            $goals = $pom->xpath("$baseCheckstyleXpath/executions/execution/goals/goal");
            $checkstyleBuildTask = (bool) array_intersect($goals, ['checkstyle', 'check']);

            // PMD
            $basePMDXpath = $this->getBaseXpathFor('pmd');
            $pmdDependency = (bool) $pom->xpath($basePMDXpath);

            // Having the rulesets element means deviating from the default 3 rulesets used
            $pmdConfigFile = (bool) $pom->xpath("$basePMDXpath//configuration/rulesets");

            $goals = $pom->xpath("$basePMDXpath/executions/execution/goals/goal");
            $pmdBuildTask = (bool) array_intersect($goals, ['pmd', 'check']);
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

    protected function getBaseXpathFor($plugin)
    {
        return "build//plugin[artifactId = 'maven-$plugin-plugin']";
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
