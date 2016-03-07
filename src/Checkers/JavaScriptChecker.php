<?php
namespace App\Checkers;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 04-03-2016
 * Time: 14:23
 */
class JavaScriptChecker extends ProjectChecker
{
    protected $buildFile;
    protected $packageArray;
    protected $dependenciesJSON;

    public function doLanguageSpecificProcessing()
    {
        $packageContent = $this->project->getFile('package.json');
        $this->packageArray = json_decode($packageContent, true);
        $this->dependenciesJSON = $this->getCombinedDependenciesJSON();

        if ($this->project->usesBuildTool('grunt')) {
            $this->buildFile = $this->project->getFile('Gruntfile.js');
        }
        elseif ($this->project->usesBuildTool('gulp')) {
            $this->buildFile = $this->project->getFile('gulpfile.js');
        }

        $jshint = $this->checkJSHint();
        $jscs = $this->checkJSCS();
        $eslint = $this->checkESLint();

        return $jshint || $jscs || $eslint;
    }

    protected function checkJSHint()
    {
        $jshintConfigFile = in_array('.jshintrc', $this->projectRootFiles) || array_has($this->packageArray, 'jshintConfig');
        $jshintDependency = str_contains($this->dependenciesJSON, 'jshint');
        $jshintBuildTask = codeContains($this->buildFile, 'jshint');

        return $this->attachASAT('jshint', $jshintConfigFile, $jshintDependency, $jshintBuildTask);
    }

    protected function checkJSCS()
    {
        $jscsConfigFile = in_array('.jscsrc', $this->projectRootFiles) || array_has($this->packageArray, 'jscsConfig');
        $jscsDependency = str_contains($this->dependenciesJSON, 'jscs');
        $jscsBuildTask = codeContains($this->buildFile, 'jscs');

        return $this->attachASAT('jscs', $jscsConfigFile, $jscsDependency, $jscsBuildTask);
    }

    protected function checkESLint()
    {
        $eslintConfigFile = (bool) array_intersect(['.eslintrc', '.eslintrc.js', '.eslintrc.json', '.eslintrc.yml', '.eslintrc.yaml'], $this->projectRootFiles) || array_has($this->packageArray, 'eslintConfig');
        $eslintDependency = str_contains($this->dependenciesJSON, 'eslint');
        $eslintBuildTask = codeContains($this->buildFile, 'eslint');

        return $this->attachASAT('eslint', $eslintConfigFile, $eslintDependency, $eslintBuildTask);
    }

    protected function getCombinedDependenciesJSON()
    {
        $dependencies = array_get($this->packageArray, 'dependencies', []);
        $devDependencies = array_get($this->packageArray, 'devDependencies', []);
        return json_encode($dependencies) . json_encode($devDependencies);
    }

    protected function getBuildTools()
    {
        return [
            'grunt' => 'Gruntfile.js',
            'gulp' => 'gulpfile.js'
        ];
    }
}
