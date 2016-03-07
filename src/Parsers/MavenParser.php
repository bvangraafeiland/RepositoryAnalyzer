<?php
namespace Parsers;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 07-03-2016
 * Time: 17:43
 */
class MavenParser extends XmlParser
{
    public function hasBuildPlugin($plugin)
    {
        return (bool) $this->pluginRootXpath($plugin);
    }

    public function hasCustomCheckstyleConfig()
    {
        $configFileLocation = $this->checkstyleConfigFile();
        // sun and google checks are default presets
        return $configFileLocation && $configFileLocation[0] != 'sun_checks.xml' && $configFileLocation[0] != 'google_checks.xml';
    }

    public function hasCustomPMDConfig()
    {
        // Having the rulesets element means deviating from the default 3 rulesets used
        return (bool) $this->pluginRootXpath("//configuration/rulesets");
    }

    public function checkstyleConfigFile()
    {
        return $this->pluginRootXpath('checkstyle', '//configuration/configLocation');
    }

    public function buildIncludesPlugin($plugin)
    {
        // either check or <plugin name> goal can be used, the latter to also generate a report
        $goals = $this->pluginRootXpath($plugin, '/executions/execution/goals/goal');
        return (bool) array_intersect($goals, [$plugin, 'check']);
    }

    protected function pluginRootXpath($plugin, $path = '')
    {
        $base = "build//plugin[artifactId = 'maven-$plugin-plugin']";

        return $this->root->xpath($base . $path);
    }
}
