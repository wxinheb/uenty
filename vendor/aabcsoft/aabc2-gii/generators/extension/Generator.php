<?php


namespace aabc\gii\generators\extension;

use Aabc;
use aabc\gii\CodeFile;


class Generator extends \aabc\gii\Generator
{
    public $vendorName;
    public $packageName = "aabc2-";
    public $namespace;
    public $type = "aabc2-extension";
    public $keywords = "aabc2,extension";
    public $title;
    public $description;
    public $outputPath = "@app/runtime/tmp-extensions";
    public $license;
    public $authorName;
    public $authorEmail;


    
    public function getName()
    {
        return 'Extension Generator';
    }

    
    public function getDescription()
    {
        return 'This generator helps you to generate the files needed by a Aabc extension.';
    }

    
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [['vendorName', 'packageName'], 'filter', 'filter' => 'trim'],
                [
                    [
                        'vendorName',
                        'packageName',
                        'namespace',
                        'type',
                        'license',
                        'title',
                        'description',
                        'authorName',
                        'authorEmail',
                        'outputPath'
                    ],
                    'required'
                ],
                [['keywords'], 'safe'],
                [['authorEmail'], 'email'],
                [
                    ['vendorName', 'packageName'],
                    'match',
                    'pattern' => '/^[a-z0-9\-\.]+$/',
                    'message' => 'Only lowercase word characters, dashes and dots are allowed.'
                ],
                [
                    ['namespace'],
                    'match',
                    'pattern' => '/^[a-zA-Z0-9\\\]+\\\$/',
                    'message' => 'Only letters, numbers and backslashes are allowed. PSR-4 namespaces must end with a namespace separator.'
                ],
            ]
        );
    }

    
    public function attributeLabels()
    {
        return [
            'vendorName'  => 'Vendor Name',
            'packageName' => 'Package Name',
            'license'     => 'License',
        ];
    }

    
    public function hints()
    {
        return [
            'vendorName'  => 'This refers to the name of the publisher, your GitHub user name is usually a good choice, eg. <code>myself</code>.',
            'packageName' => 'This is the name of the extension on packagist, eg. <code>aabc2-foobar</code>.',
            'namespace'   => 'PSR-4, eg. <code>myself\foobar\</code> This will be added to your autoloading by composer. Do not use aabc, aabc2 or aabcsoft in the namespace.',
            'keywords'    => 'Comma separated keywords for this extension.',
            'outputPath'  => 'The temporary location of the generated files.',
            'title'       => 'A more descriptive name of your application for the README file.',
            'description' => 'A sentence or subline describing the main purpose of the extension.',
        ];
    }

    
    public function stickyAttributes()
    {
        return ['vendorName', 'outputPath', 'authorName', 'authorEmail'];
    }

    
    public function successMessage()
    {
        $outputPath = realpath(\Aabc::getAlias($this->outputPath));
        $output1 = <<<EOD
<p><em>The extension has been generated successfully.</em></p>
<p>To enable it in your application, you need to create a git repository
and require it via composer.</p>
EOD;
        $code1 = <<<EOD
cd {$outputPath}/{$this->packageName}

git init
git add -A
git commit
git remote add origin https://path.to/your/repo
git push -u origin master
EOD;
        $output2 = <<<EOD
<p>The next step is just for <em>initial development</em>, skip it if you directly publish the extension on packagist.org</p>
<p>Add the newly created repo to your composer.json.</p>
EOD;
        $code2 = <<<EOD
"repositories":[
    {
        "type": "git",
        "url": "https://path.to/your/repo"
    }
]
EOD;
        $output3 = <<<EOD
<p class="well">Note: You may use the url <code>file://{$outputPath}/{$this->packageName}</code> for testing.</p>
<p>Require the package with composer</p>
EOD;
        $code3 = <<<EOD
composer.phar require {$this->vendorName}/{$this->packageName}:dev-master
EOD;
        $output4 = <<<EOD
<p>And use it in your application.</p>
EOD;
        $code4 = <<<EOD
\\{$this->namespace}AutoloadExample::widget();
EOD;
        $output5 = <<<EOD
<p>When you have finished development register your extension at <a href='https://packagist.org/' target='_blank'>packagist.org</a>.</p>
EOD;

        $return = $output1 . '<pre>' . highlight_string($code1, true) . '</pre>';
        $return .= $output2 . '<pre>' . highlight_string($code2, true) . '</pre>';
        $return .= $output3 . '<pre>' . highlight_string($code3, true) . '</pre>';
        $return .= $output4 . '<pre>' . highlight_string($code4, true) . '</pre>';
        $return .= $output5;

        return $return;
    }

    
    public function requiredTemplates()
    {
        return ['composer.json', 'AutoloadExample.php', 'README.md'];
    }

    
    public function generate()
    {
        $files = [];
        $modulePath = $this->getOutputPath();
        $files[] = new CodeFile(
            $modulePath . '/' . $this->packageName . '/composer.json',
            $this->render("composer.json")
        );
        $files[] = new CodeFile(
            $modulePath . '/' . $this->packageName . '/AutoloadExample.php',
            $this->render("AutoloadExample.php")
        );
        $files[] = new CodeFile(
            $modulePath . '/' . $this->packageName . '/README.md',
            $this->render("README.md")
        );

        return $files;
    }

    
    public function getOutputPath()
    {
        return Aabc::getAlias($this->outputPath);
    }

    
    public function getKeywordsArrayJson()
    {
        return json_encode(explode(',', $this->keywords), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    
    public function optsType()
    {
        $licenses = [
            'aabc2-extension',
            'library',
        ];

        return array_combine($licenses, $licenses);
    }

    
    public function optsLicense()
    {
        $licenses = [
            'Apache-2.0',
            'BSD-2-Clause',
            'BSD-3-Clause',
            'BSD-4-Clause',
            'GPL-2.0',
            'GPL-2.0+',
            'GPL-3.0',
            'GPL-3.0+',
            'LGPL-2.1',
            'LGPL-2.1+',
            'LGPL-3.0',
            'LGPL-3.0+',
            'MIT'
        ];

        return array_combine($licenses, $licenses);
    }
}
