<?php
namespace App\Parsers;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 10-03-2016
 * Time: 12:06
 */
class GradleParser implements JavaBuildInfo
{
    protected $gradleBuildFile;

    public function __construct($gradleBuildFile)
    {
        $this->gradleBuildFile = $gradleBuildFile;
    }

    public function containsPlugin($tool)
    {
        return codeContains($this->gradleBuildFile, '%("|\')'.$tool.'\1%', true);
    }

    public function hasPluginInBuild($tool)
    {
        // Adding the dependency also adds checkstyle's/pmd's tasks to the check task
        return $this->containsPlugin($tool);
    }

    public function hasCustomCheckstyleConfig()
    {
        // Mandatory with Gradle
        return true;
    }

    public function hasCustomPmdConfig()
    {
        // By default, uses just the basic rule set (java-basic) so if any of these are defined,
        // the configuration is considered custom
        $ruleConfigNames = 'ruleSetConfig|ruleSetFiles|ruleSets';
        return (bool) preg_match('%pmd\s*{[^}]*('.$ruleConfigNames.')\s*=.+%', $this->gradleBuildFile);
    }
}
