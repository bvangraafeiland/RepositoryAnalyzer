<?php
namespace App\Runners;

use App\Parsers\XmlParser;
use Exception;
use SimpleXMLElement;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 17-03-2016
 * Time: 19:39
 */
class JavaToolrunner extends ToolRunner
{
    protected $outputLocation = 'asat-result.xml';

    protected $projectConfigs = [
        'square/retrofit' => [
            'src' => 'retrofit',
            'asat-version' => '6.1.1'
        ],
        'bumptech/glide' => [
            'src' => 'library',
            'asat-version' => '6.1.1',
            'properties' => [
                'checkStyleConfigDir' => '.'
            ]
        ],
        'netflix/servo' => [
            'src' => 'servo-core/src/main/java',
            'asat-version' => '5.2.3',
            'config-location' => 'codequality/pmd.xml'
        ],
        'opengrok/opengrok' => [
            'config-location' => 'tools/pmd_ruleset.xml',
            'src' => 'src/org/opensolaris/opengrok'
        ],
        'sleekbyte/tailor' => [
            'config-location' => 'config/pmd/tailorRuleSet.xml',
            'src' => 'src/main/java'
        ],
        'facebook/buck' => [
            'config-location' => 'pmd/rules.xml',
            'src' => 'src/com/facebook'
        ]
    ];

    protected function getResults($tool)
    {
        chdir($this->projectDir);
        $buildToolCommand = $this->{'get' . ucfirst($tool) . 'Command'}();

        system("rm -f $this->outputLocation");
        $this->fixConfigErrors($tool);
        exec($buildToolCommand);
        $this->revertGitChanges($tool);

        return $this->getWarnings($tool, $this->outputLocation);
    }

    protected function getCheckstyleCommand()
    {
        $projectConfig = $this->getProjectConfig();
        $version = array_get($projectConfig, 'asat-version', '6.15');
        $properties = array_get($projectConfig, 'properties', []);
        $commandProperties = '';
        foreach ($properties as $property => $value) {
            $commandProperties .= "-D$property=$value ";
        }

        $src = implode(' ', array_map(function ($dir) use ($version) {
            $prefix = $version == '6.1.1' ? '-r ' : '';
            return "$prefix$dir/src/main/java";
        }, (array) $projectConfig['src']));
        $configLocation = array_get($projectConfig, 'config-location', 'checkstyle.xml');
        return "java -Dcheckstyle.cache.file=checkstyle-cache $commandProperties -jar ~/checkstyle-$version-all.jar -c $configLocation $src -o $this->outputLocation -f xml";
    }

    protected function getPmdCommand()
    {
        $projectConfig = $this->getProjectConfig();
        $version = array_get($projectConfig, 'asat-version', '5.4.1');
        $src = implode(' ', (array) $projectConfig['src']);
        $configLocation = array_get($projectConfig, 'config-location', 'pmd.xml');

        return "~/pmd-bin-$version/bin/run.sh pmd -d $src -R $configLocation -f xml -r $this->outputLocation";
    }

    protected function fixConfigErrors($tool)
    {
        if ($tool == 'pmd') {
            $pmdConfig = $this->getConfigLocation('pmd');
            $config = file_get_contents($pmdConfig);
            file_put_contents($pmdConfig, str_replace('<exclude name="ShortMethod"/>', '', $config));
        }
    }

    protected function revertGitChanges($tool)
    {
        exec('git checkout ' . $this->getConfigLocation($tool));
    }

    protected function getConfigLocation($tool)
    {
        return array_get($this->getProjectConfig(), 'config-location', "$tool.xml");
    }

    protected function getWarnings($tool, $resultsFileLocation)
    {
        $xmlContent = file_get_contents($resultsFileLocation);

        if (!$xmlContent) {
            throw new Exception("$tool results file could not be read.");
        }

        $parser = new XmlParser($xmlContent);

        $results = [];
        foreach ($parser->xpath('file') as $fileElement) {
            $this->{'get' . ucfirst($tool) . 'Results'}($fileElement, $fileElement['name'], $results);
        };

        return $results;
    }

    protected function getCheckstyleResults(SimpleXMLElement $fileElement, $file, &$results)
    {
        $warnings = $fileElement->xpath('error');
        foreach ($warnings as $warning) {
            $attributes = current($warning);
            $rule = $this->shortCheckstyleRule($attributes['source']);
            $results[] = compact('file', 'rule')
                + array_only($attributes, ['line', 'column', 'message']);
        }
    }

    protected function shortCheckstyleRule($fullRule)
    {
        return str_replace('Check', '', substr($fullRule, strrpos($fullRule, '.') + 1));
    }

    protected function getPmdResults(SimpleXMLElement $fileElement, $file, &$results) {
        $warnings = $fileElement->xpath('violation');
        foreach ($warnings as $warning) {
            $message = trim($warning);
            $attributes = current($warning);
            $rule = $attributes['rule'];
            $line = $attributes['beginline'];
            $column = $attributes['begincolumn'];
            $results[] = compact('file', 'message', 'rule', 'line', 'column');
        }
    }
}
