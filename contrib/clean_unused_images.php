<?php
/**
 * Cleanup utility to remove unused images from cache folders
 *
 * @package Contrib
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @modified  Constantinos Neophytou   <jaguarcy@gmail.com>
 * @modified  Klaus Christiansen   <klaus_edwin@hotmail.com>
 */

// move out of contrib for includes
chdir('..');

require_once './core/functions.php';
require_once './core/setup.core.php';

error_reporting(E_ALL ^ E_NOTICE);

$coverSQL = "SELECT imgurl FROM ".TBL_DATA;
$actorSQL = "SELECT imgurl FROM ".TBL_ACTORS;
$coverResult = runSQL($coverSQL);
$actorResult = runSQL($actorSQL);

// find covers in cache
$covers = findCacheFileName($coverResult);
$actors = findCacheFileName($actorResult);
$images = array_merge([], $covers, $actors);

$size   	= 0;
$coverSize 	= 0;
$actorSize 	= 0;
$unused 	= 0;
$coverNum 	= 0;
$actorNum 	= 0;
$fileCount  = 0;

set_time_limit(300);
// get list of all images currently in cache/img.
$files = listAllFiles(CACHE.'/'.CACHE_IMG);
$fileCount = count($files);

#echo "<pre>".dump($files)."</pre><br>";

?>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="description" content="VideoDB" />
        <link rel="stylesheet" href="../<?php echo $config['style'] ?>" type="text/css" />
        <title>Cleanup Image Cache</title>
    </head>
    <body>
<?php
if ($submit) echo 'Deleting:<br>';

// loop over cache files
foreach ($files as $file) {
    if (empty($images) || !in_array($file, $images)) {
        $size += filesize($file);
        $unused++;

        if ($submit) {
        	unlink($file);
			dump($file);
		}
    } elseif (!empty($covers) && in_array($file, $covers)) {
		$coverSize += filesize($file);
		$coverNum++;
	} elseif ( in_array($file, $actors)) {
		$actorSize += filesize($file);
		$actorNum++;
	}
}
if ($submit) echo "<br/>";

$megaByte = 1048576;

echo sprintf("
    %d out of %d files with a size of %.2f MB are used for covers<br/>
    %d out of %d files with a size of %.2f MB are used for headshots<br/>
    %d out of %d files with a size of %.2f MB are currently unused<br/>",
        $coverNum, $fileCount, $coverSize / $megaByte,
        $actorNum, $fileCount, $actorSize / $megaByte,
        $unused, $fileCount, $size / $megaByte);

if ($unused) {
    if ($submit) {
        echo "<br/>$unused files with a size of ".round($size / $megaByte, 2)." MB have been deleted<br/>";
    } else {
?>
    <form action=<?php echo $PHP_SELF?>>
        <input type="submit" name="submit" value="Delete" />
    </form>
<?php
    }
}

function findCacheFileName($dbResult) {
    $cache_files = [];

    // find images in cache
    foreach ($dbResult as $row) {
        $url = $row['imgurl'];

        if (preg_match("/\.(jpe?g|gif|png)$/i", $url, $matches)) {
            // get the cache file name, honor manually uploaded files
            if (preg_match('/^cache/i', $url)) {
                $cache_file = $url;
            } else {
                // translates url to cache file name. Result is in $cache_file.
                cache_file_exists($url, $cache_file, CACHE_IMG, $matches[1]);
            }

            $cache_files[] = $cache_file;
        }
    }
    return $cache_files;
}

function listAllFiles($dir) {
    $di = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
    $it = new RecursiveIteratorIterator($di);

    $files = [];
    foreach($it as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) == "jpg") {
            #echo $file ."<br>";
            $files[] = $file->__toString();
        }
    }
    return $files;
}

?>

</body>
</html>
