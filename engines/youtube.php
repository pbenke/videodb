<?php
/**
 * youtube.com trailer search
 *
 * Search trailers on youtube.com
 *
 * @package Engines
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @author  Adam Benson	    <precarious_panther@bigpond.com>
 * @link    http://www.youtube.com  YouTube
 * @version $Id: youtube.php,v 1.9 2012/08/10 16:07:53 andig2 Exp $
 */

require_once './core/functions.php';
require_once './core/httpclient.php';

define('YOUTUBE_CLIENT_ID', 'ytapi-AndreasGoetz-videodb-g7dk2dh6-0');
define('YOUTUBE_DEVELOPER_KEY', 'AI39si7znfvxGu-6OfT-PIPHxUJbAy429l63_jnWSThlJ7Hitv_gmCpJ9cE_HCnH7PDvSLgthw4wEZ5wSrw139DPLbbmLb50GQ');

// http://gdata.youtube.com/feeds/api/videos?client=ytapi-AndreasGoetz-videodb-g7dk2dh6-0&key=AI39si7znfvxGu-6OfT-PIPHxUJbAy429l63_jnWSThlJ7Hitv_gmCpJ9cE_HCnH7PDvSLgthw4wEZ5wSrw139DPLbbmLb50GQ&v=2&start-index=1&max-results=10&q=alien

/**
 *  Get meta information about the engine
 *
 * @return (int|string|string[])[]
 *
 * @psalm-return array{name: 'YouTube', stable: 1, php: '5.0', capabilities: array{0: 'trailer'}}
 */
function youtubeMeta(): array
{
    return array('name' => 'YouTube', 'stable' => 1, 'php' => '5.0', 'capabilities' => array('trailer'));
}

function youtubeHasTrailer($title): bool
{
	return count(youtubeSearch($title)) > 0;
}

function normalize($str): string|null
{
	return preg_replace('/[^a-zäöüA-ZÄÖÜ0-9\s]/', '', $str);
}

/**
 * @return string[][]
 *
 * @psalm-return list<array{id: string, src: string, title: string}>
 */
function youtubeSearch($title): array
{
	$trailers       = array();
    $title	        = normalize($title);
    $trailerquery	= $title." trailer";

    $youtubeurl     = "http://gdata.youtube.com/feeds/api/videos?client=".YOUTUBE_CLIENT_ID."&key=".YOUTUBE_DEVELOPER_KEY."&v=2&".
                      "q=".urlencode($trailerquery)."&start-index=1&max-results=10";

    $resp = httpClient($youtubeurl, true);    
    if (!$resp['success']) return $trailers;

    $xml    = simplexml_load_string($resp['data']);

    // obtain namespaces
    $namespaces = $xml->getNameSpaces(true);
  
    foreach ($xml->entry as $trailer)
    {
        $media      = $trailer->children($namespaces['media']); 
        $yt         = $media->group->children($namespaces['yt']); 
        $id         = $yt->videoid;

        // API filtering code removed
        $trailers[] = array('id' => (string) $id,
                            'src' => (string) $trailer->content['src'],
                            'title' => (string) $trailer->title);
        if (count($trailers) >= 10) break;
    }

	return $trailers;
}

?>