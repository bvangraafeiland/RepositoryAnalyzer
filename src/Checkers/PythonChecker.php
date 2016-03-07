<?php
namespace App\Checkers;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 04-03-2016
 * Time: 14:29
 */
class PythonChecker extends ProjectChecker
{
    public function doLanguageSpecificProcessing()
    {
        $hasConfigFile = (bool) array_intersect($this->projectRootFiles, ['pylintrc', '.pylintrc']);
        $hasDependency = $this->rootFileContains('setup.py', 'pylint') || $this->rootFileContains('requirements.txt', 'pylint');
        $hasBuildTask = $this->rootFileContains('tox.ini', 'pylint') || $this->rootFileContains('Makefile', 'pylint');

        return $this->attachASAT('pylint', $hasConfigFile, $hasDependency, $hasBuildTask);
    }

    protected function getBuildTools()
    {
        return [
            'tox' => 'tox.ini',
            'make' => 'Makefile'
        ];
    }
}
