<?php
namespace App\Parsers;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 10-03-2016
 * Time: 11:54
 */
interface JavaBuildInfo
{
    public function containsPlugin($tool);
    public function hasPluginInBuild($tool);
    public function hasCustomCheckstyleConfig();
    public function hasCustomPmdConfig();
}
