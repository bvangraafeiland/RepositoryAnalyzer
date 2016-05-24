<?php

return [
    'pmd' => [
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
        ],
        'capitalone/hygieia' => [
            'src' => ['api/src/main/java', 'core/src/main/java']
        ],
        'checkstyle/checkstyle' => [
            'src' => 'src/main/java',
            'config-location' => 'config/pmd.xml',
            'asat-version' => '5.3.7',
        ]
    ],
    'checkstyle' => [
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
        'scribejava/scribejava' => [
            'src' => ['scribejava-apis', 'scribejava-core']
        ],
        'google/auto' => [
            'src' => ['common', 'factory', 'service', 'value'],
            'asat-version' => '5.6'
        ],
        'zeromq/jeromq' => [
            'src' => '.',
            'config-location' => 'src/checkstyle/checks.xml',
            'asat-version' => '5.6'
        ],
        'mongodb/morphia' => [
            'src' => 'morphia',
            'asat-version' => '6.10',
            'config-location' => 'config/checkstyle.xml'
        ],
    ],
    'rubocop' => [
        'sass/sass' => [],
        'thoughtbot/paperclip' => [
            'rubocop-version' => '0.29.1'
        ],
        'cocoapods/cocoapods' => [
            'rubocop-version' => '0.37.2'
        ],
        'spree/spree' => [
            'rubocop-version' => '0.36.0',
            'src' => ['api', 'backend', 'cmd', 'core', 'frontend', 'guides', 'sample']
        ],
        'ruby-grape/grape' => [
            'rubocop-version' => '0.39.0',
        ]
    ],
    'pylint' => [
        'sirver/ultisnips' => [
            'src' => ['plugin/*.py', 'pythonx/UltiSnips', 'plugin/UltiSnips', 'plugin/PySnipEmu']
        ],
        'cython/cython' => [
            'src' => ['Tools/*.py', 'Demos/*.py']
        ],
    ],
    'eslint' => [
        'jashkenas/backbone' => [
            'src' => 'backbone.js'
        ],
        'freecodecamp/freecodecamp' => [
            'src' => ['client', 'client/**/*.jsx', 'common', 'common/**/*.jsx', 'config', 'server']
        ],
        'gulpjs/gulp' => [
            'src' => '.'
        ],
        'nnnick/chart.js' => [
            'src' => 'src'
        ],
        'jashkenas/underscore' => [
            'src' => ['underscore.js', 'test/*.js']
        ],
        'vuejs/vue' => [
            'src' => ['src', 'test/e2e', 'test/unit/specs', 'build']
        ],
        'bower/bower' => [
            'src' => ['Gruntfile.js', 'bin/*', 'lib/**/*.js', 'test/**/*.js'],
            'ignore' => ['test/assets/**/*', 'test/reports/**/*', 'test/sample/**/*', 'test/tmp/**/*'],
            'asat-version' => '^2.0.0'
        ]
    ],
    'jshint' => [
        'jquery/jquery' => [
            'src' => ['src', 'build', 'test'],
        ],
        'moment/moment' => [
            'src' => ['tasks', 'src']
        ],
        'caolan/async' => [
            'src' => 'lib mocha_test perf/memory.js perf/suites.js perf/benchmark.js support karma.conf.js'
        ],
        'select2/select2' => [
            'src' => 'src tests'
        ],
        'less/less.js' => [
            'src' => [
                'Gruntfile.js',
                'lib/less',
                'lib/less-node',
                'lib/less-browser',
                'lib/less-rhino',
                'bin/lessc'
            ]
        ]
    ],
    'jscs' => [
        'gruntjs/grunt' => [
            'src' => ['lib', 'internal-tasks', 'test/grunt', 'test/gruntfile', 'test/util'],
            'asat-version' => '~2.11.0'
        ],
        'requirejs/requirejs' => [
            'src' => '.'
        ],
        'hexojs/hexo' => [
            'src' => '.',
            'asat-version' => '^2.10.1',
            'npm' => 'jscs-preset-hexo@^1.0.1'
        ],
        'remy/nodemon' => [
            'src' => 'lib',
            'asat-version' => '2.1.1'
        ],
        'jshint/jshint' => [
            'src' => 'src',
            'asat-version' => '1.11.x'
        ]
    ],
];
