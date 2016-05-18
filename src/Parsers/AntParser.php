<?php
namespace App\Parsers;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 10-03-2016
 * Time: 12:05
 */
class AntParser extends XmlParser implements JavaBuildInfo
{
    public function containsPlugin($tool)
    {
        // No specific dependency management in Ant
        return (bool) $this->root->xpath("//$tool");
    }

    public function hasPluginInBuild($tool)
    {
        return $this->containsPlugin($tool);
    }

    public function hasCustomCheckstyleConfig()
    {
        $filePath = $this->checkstyleConfigFile();

        return $filePath && (!ends_with($filePath, 'sun_checks.xml')) && (!ends_with($filePath, 'google_checks.xml'));
    }

    public function checkstyleConfigFile()
    {
        return array_get($this->root->xpath("//checkstyle/@config"), 0);
    }

    public function hasCustomPmdConfig()
    {
        // PMD config always required, so config will always be at least semi-custom (by combining presets)
        return $this->hasPluginInBuild('pmd');
    }
}
