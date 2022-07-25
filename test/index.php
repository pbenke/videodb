<?php

// move out of test for includes
chdir('..');
require_once './core/functions.php';

localnet_or_die();
permission_or_die(PERM_ADMIN);

?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <title>Test Documentation</title>
        <style>
            body {
                text-rendering: optimizeLegibility;
                font-variant-ligatures: common-ligatures;
                font-kerning: normal;
                margin-left: 2em;
                background-color: #ffffff;
                color: #000000;
            }

            body > ul > li {
                font-family: Source Serif Pro, PT Sans, Trebuchet MS, Helvetica, Arial;
                font-size: 2em;
            }

            h2 {
                font-family: Tahoma, Helvetica, Arial;
                font-size: 3em;
            }

            ul {
                list-style: none;
                margin-bottom: 1em;
            }
        </style>
    </head>
    <body>

<?php

$testClasses = findTestClasses('./test', $_REQUEST['test']);

// 'loadedExtensions', 'extensions', 'notLoadedExtensions' is just to avoid PHP 7.4 warnings.
$args = [];
$args['loadedExtensions'] = [];
$args['extensions'] = [];
$args['notLoadedExtensions'] = [];
$args['testdoxHTMLFile'] = true;
$args['cacheResult'] = false;

$warnings = [];
$stopOnError = false;

// File name and class name must match because TestSuite takes a CLASS NAME!!!
foreach($testClasses as $name => $className) {
    $suite = new PHPUnit\Framework\TestSuite($name);
    ob_start();

    $runner = new PHPUnit\TextUI\TestRunner;
    $testResult = $runner->run($suite, $args, $warnings, $stopOnError);
    $result = ob_get_clean();

    preg_match('/&lt;body&gt;(.+?)&lt;\/body&gt;/si', $result, $body);
    echo html_entity_decode($body[1]);

    preg_match('/&lt;\/body&gt;.+?&lt;\/html&gt;(.+)/si', $result, $description);
    echo "<pre>"; echo $description[1]; echo "</pre><br><br>";
}

echo "</body></html>";


// Find files that either starts with or ends with test.
function findTestClasses(string $dir, string $pattern = null) {
    $res = array();

    if ($dh = @opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            if (preg_match("/^(test(.+?))\.php$/i", $file, $matches) || preg_match("/^((.+?)test)\.php$/i", $file, $matches)) {
                if ($pattern && (stristr($file, $pattern) == false)) {
                    continue;
                }

                $res[$matches[1]] = $matches[2];
                require_once($file);
            }
        }
        closedir($dh);
    }

    return $res;
}

?>
