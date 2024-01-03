<?php declare(strict_types=1);
/**
 * Allocine Parser
 *
 * Parses data from the Allocine.fr
 *
 * @package Engines
 * @author  Douglas Mayle   <douglas@mayle.org>
 * @author  Andreas Gohr    <a.gohr@web.de>
 * @author  tedemo          <tedemo@free.fr>
 * @link    http://www.allocine.fr  Internet Movie Database
 * @version $Id: allocine.php,v 1.17 2011/06/24 23:08:06 robelix Exp $
 */

$GLOBALS['allocineServer']	    = 'https://www.allocine.fr';
$GLOBALS['allocineIdPrefix']    = 'allocine:';

/**
 *  Get meta information about the engine
 *
 * @todo Include image search capabilities etc in meta information
 *
 * @return string[]
 *
 * @psalm-return array{name: 'Allocine (fr)'}
 */
function allocineMeta(): array
{
    return array('name' => 'Allocine (fr)');
}

/**
 * Encode title search to allow results with accentued characters
 * @author Martin Vauchel <martin@vauchel.com>
 * @param string	The search string
 * @return string	The search string with no accents
 */
function removeAccents($title)
{
	$accentued = array("à","á","â","ã","ä","ç","è","é","ê","ë","ì",
	"í","î","","ï","ñ","ò","ó","ô","õ","ö","ù","ú","û","ü","ý","ÿ",
	"À","Á","Â","Ã","Ä","Ç","È","É","Ê","Ë","Ì","Í","Î","Ï","Ñ","Ò",
	"Ó","Ô","Õ","Ö","Ù","Ú","Û","Ü","Ý");
	$nonaccentued = array("a","a","a","a","a","c","e","e","e","e","i","i",
	"i","i","n","o","o","o","o","o","u","u","u","u","y","y","A","A","A",
	"A","A","C","E","E","E","E","I","I","I","I","N","O","O","O","O","O",
	"U","U","U","U","Y");

	$title = str_replace($accentued, $nonaccentued, $title);

	return $title;
}

/**
 * Get Url to search Allocine for a movie
 *
 * @author  Douglas Mayle <douglas@mayle.org>
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   string    The search string
 * @return  string    The search URL (GET)
 */
function allocineSearchUrl($title)
{
	global $allocineServer;

	// The removeAccents function is added here
	return $allocineServer.'/rechercher/?q='.urlencode(removeAccents($title));
}

/**
 * Get Url to visit Allocine for a specific movie
 *
 * @author  Douglas Mayle <douglas@mayle.org>
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   string    $id    The movie's external id
 * @return  string        The visit URL
 */
function allocineContentUrl($id): string
{
   global $allocineServer;
   global $allocineIdPrefix;

   $allocineID = preg_replace('/^'.$allocineIdPrefix.'/', '', $id);
   return $allocineServer.'/film/fichefilm_gen_cfilm='.$allocineID.'.html';
}


/**
 * Search a Movie
 *
 * Searches for a given title on Allocine and returns the found links in
 * an array
 *
 * @author  Douglas Mayle <douglas@mayle.org>
 * @author  Tiago Fonseca <t_r_fonseca@yahoo.co.uk>
 * @author  Charles Morgan <cmorgan34@yahoo.com>
 * @param   string    The search string
 * @return  array     Associative array with id and title
 */
function allocineSearch($title)
{
    global $allocineServer;
    global $CLIENTERROR;

    // The removeAccents function is added here
    $resp = httpClient(allocineSearchUrl($title), 1);
    if (!$resp['success']) {
        $CLIENTERROR .= $resp['error']."\n";
    }

    $data = array();

//    echo '<pre>';
//    dump(htmlspecialchars($resp['data']));
//    echo '</pre>';

    // add encoding
    $data['encoding'] = $resp['encoding'];

//    // direct match (redirecting to individual title)?
//    // no longer needed??
//    $single = array();
//    if (preg_match('#^'.preg_quote($allocineServer,'/').'/film/fichefilm_gen_cfilm=(\d+)\.html#', $resp['url'], $single))
//    {
//        $data[0]['id']   = 'allocine:'.$single[2];
//        $data[0]['title']= $title;
//        return $data;
//    }
//
//    // multiple matches
//    // We remove all the multiples spaces and line breakers
//	$resp['data'] = preg_replace('/[\s]{2,}/','',$resp['data']);
//	// To have the result zone
//	#$debutr  = strpos($resp['data'], '<table class="totalwidth noborder purehtml">')+strlen('<table class="totalwidth noborder purehtml">');
//	#$finr    = strpos($resp['data'], '</table>', $debutr);
//	#$chaine  = substr($resp['data'], $debutr, $finr-$debutr);
//
//    preg_match('#<h2>\s*?Films\s*?</h2>(.*?)<h2>#si',$resp['data'],$ary);
//
//    $chaine = $ary[1];
//    # contains some pretty random <b></b>
//    $chaine = preg_replace('/<b>/', '', $chaine);
//    $chaine = preg_replace('/<\/b>/', '', $chaine);
//
//    /*
//    <tr><td style=" vertical-align:top;">
//    <a href='/film/fichefilm_gen_cfilm=57999.html'><img
//    src='http://images.allocine.fr/r_75_106/medias/nmedia/18/36/26/78/18759563.jpg'
//    alt='Clerks II' /></a>
//    </td><td style=" vertical-align:top;" class="totalwidth"><div><div style="margin-top:-5px;">
//    <a href='/film/fichefilm_gen_cfilm=57999.html'>
//    Clerks II</a>
//    <br />
//    <span class="fs11">
//    2006<br />
//    de Kevin Smith<br />
//    avec Brian O'Halloran, Jeff Anderson<br />
//    <div>
//    <div class="spacer vmargin10"></div>
//    </span> <!-- /fs11 -->
//    */
//
//    preg_match_all('#<a href=\'/film/fichefilm_gen_cfilm=(\d+).html\'>\s*?(.*?)</a>\s*?<br />\s*?<span class=\"fs11\">\s*?(\d+)<br />\s*?de (.*?)\s*?/#si', $chaine, $m, PREG_SET_ORDER);
//
//    foreach ($m as $row)
//    {
//        $info['id']     = 'allocine:'.$row[1];
//
//        $info['title']  = html_clean_utf8(strip_tags($row[2]));
//        $info['title']  = str_replace("(", " (", $info['title']);
//
//        // add year (helpful in case of multiple matches)
//        if (isset($row[3])) {$info['year'] = html_clean_utf8($row[3]);}
//
//        // add director (helpful in case of multiple matches)
//        if (isset($row[4])) {
//          $info['director'] = html_clean_utf8($row[4]);
//          $info['director'] = preg_replace("/^de\s/", "", $info['director']);
//        }
//
//        $data[]          = $info;
//    }

    return $data;
}

/**
 * Fetches the data for a given Allocine-ID
 *
 * @author  Douglas Mayle <douglas@mayle.org>
 * @author  Tiago Fonseca <t_r_fonseca@yahoo.co.uk>
 * @param   int   imdb-ID
 * @return  array Result data
 */
function allocineData($imdbID)
{
    global $allocineServer;
    global $allocineIdPrefix;
    global $cache;
    global $CLIENTERROR;

    $allocineID = preg_replace('/^'.$allocineIdPrefix.'/', '', $imdbID);

    // fetch mainpage
    $resp = httpClient($allocineServer.'/film/fichefilm_gen_cfilm='.$allocineID.'.html', $cache);		// added trailing / to avoid redirect
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

    $data   = array(); // result
    $ary    = array(); // temp

    // add encoding
    $data['encoding'] = $resp['encoding'];

    // Allocine ID
    $data['id'] = "allocine:".$allocineID;

    // We remove all the multiples spaces and line breakers
    $resp['data'] = preg_replace('/[\s]{2,}/','',$resp['data']);

    preg_match('/<div class="meta-body">(.+?<\/div>)\s*?<\/div>/si', $resp['data'], $metaBody);
//    dlog($metaBody[1]."\n\n");
//    dlog($resp['data']);

    /*
      Title and subtitle
    */
    preg_match('#<h1.*?>(.*?)</h1>#si', $resp['data'], $ary);
    list($t, $s)	  = explode(" - ",trim($ary[1]),2);
    // Some bugs when using html_clean function --> using html_clean_utf8
    $data['title']    = html_clean_utf8($t);
    $data['subtitle'] = html_clean_utf8($s);

    /*
      Title and Subtitle
      If sub-title is blank, we'll try to fill in the original title for foreign films.
    */
    if (empty($data['subtitle']))
    {
        if ($data['origtitle'])
        {
            $data['subtitle'] = $data['title'];
            $data['title']  = $data['origtitle'];
        }
    }

    /*
      Original Title
    */
    if (preg_match('#Titre original\s+<\/span>(.+?)<\/div>#is', $resp['data'], $ary)) {
        $data['origtitle'] = trim($ary[1]);
    }


//dlog($resp['data']);
    /*
      Year
    */
    if (preg_match('#<span class="\w+?== date blue-link">(.+? (\d+)).*?</span>#si', $metaBody[1], $ary)) {
        $data['year'] = trim($ary[2]);

        /*
          Release Date
            added to the comments
        */
        $release_date = "\nDate de sortie cinéma: ".html_clean_utf8($ary[1]);
        $data['comment'] .= $release_date."\n";
    }


    /*
      Director
    */
    if (preg_match('/<span class="light">De<\/span>(.+?)<\/div>/is', $resp['data'], $dirMatch)) {
        preg_match_all('/<span class=".+? blue-link">(.+?)<\/span>/is', $dirMatch[1], $directors, PREG_PATTERN_ORDER);
        $data['director'] = join(', ', $directors[1]);
    }

    /*
      Rating
    */
    if (preg_match('/"ratingValue": "(.+?)"/i', $resp['data'], $ary)) {
        $data['rating'] = str_replace(",", ".", $ary[1]);
        // Allocine rating is based on 5, imdb is based on 10
        $data['rating'] = $data['rating'] * 2;
    }


    /*
      Cover URL
    */
    preg_match('#<figure class="thumbnail ">.+?src="(.+?)"#si', $resp['data'], $ary);
    $data['coverurl'] = trim($ary[1]);


    /*
      Runtime
    */
    preg_match('/\"duration\": \"PT(\d+)H(\d+)M(\d+)S\"/i', $resp['data'], $ary);
    $hours = intval(preg_replace('/,/', '', trim($ary[1])));
    $minutes = intval(preg_replace('/,/', '', trim($ary[2])));
    $data['runtime'] = $hours * 60 + $minutes;



// there are a diffenret on tv series and movies.
// <span class="what light">Nationalité</span>

    if (preg_match_all('#<span class=".+? nationality">\s*(.+?)\s*</span>#si', $resp['data'], $ary, PREG_PATTERN_ORDER)) {
        $data['country'] = allocineGetCountry($ary[1]);
	}


    if (preg_match('/<span class="what light">Langues<\/span>\s*<span class="that">\s*(.+?)<\/span>/i', $resp['data'], $language)) {
        $data['language'] = allocineGetLanguage($language[1]);
    }

    /*
      Plot
    */
    preg_match('#<meta property="og:description" content="(.+?)" \/>#is', $resp['data'], $ary);
    if (!empty($ary[1])) {
		$data['plot'] = $ary[1];
		$data['plot']= html_clean_utf8($data['plot']);

		// And cleanup
		$data['plot'] = trim($data['plot']);
		$data['plot'] = preg_replace('/[\n\r]/',' ', $data['plot']);
		$data['plot'] = preg_replace('/\s\s+/',' ', $data['plot']);
    }


    if (preg_match('/<div class="meta-body.+?">(.+?)<\/div>/si', $resp['data'], $meta)) {
        preg_match_all('#<span class=".+?==">(.+?)<\/span>#si', $meta[1], $ary, PREG_PATTERN_ORDER);
        foreach ($ary[1] as $genre) {
            $data['genres'][] = allocineGetGenre($genre);
        }
    }

    $data['cast'] = allocineGetCast($allocineID);

	// Return the data collected
	return $data;
}

function allocineGetCast(string $allocineID): string {
    global $allocineServer;
    global $cache;
    global $CLIENTERROR;

    /*
      CREDITS AND CAST
    */
    // fetch credits
    // Another HTML page
    // https://www.allocine.fr/film/fichefilm-20754/casting/
    $resp = httpClient($allocineServer.'/film/fichefilm-'.$allocineID.'/casting/', $cache);
    if (!$resp['success']) {
        $CLIENTERROR .= $resp['error']."\n";
    }

    // We remove all the multiples spaces and line breakers
    $resp['data'] = preg_replace('/[\s]{2,}/', '' , $resp['data']);
    //dlog($resp['data']."\n");

    $cast = '';

    // there are stars and actors.
    if (preg_match_all('#<a class="meta-title-link" href="/personne/fichepersonne_gen_cpersonne=(\d+).html">(.+?)</a>\s+?</div>\s+?<div class="meta-sub light">\s+?Rôle : (.+?)\s+?</div>#i', $resp['data'], $actors, PREG_SET_ORDER)) {
        foreach ($actors as list($m, $id, $actor, $role)) {
            $cast .= $actor."::".$role."::allocine:".$id."\n";
        }
    }
    if (preg_match_all('/<div class="gd gd-xs-1 gd-s-2 md-table-row ">\s+?<span class.+?>(.+?)<\/span>\s+?<a href="\/personne\/fichepersonne_gen_cpersonne=(\d+).+?">(.+?)<\/a>/i', $resp['data'], $actors, PREG_SET_ORDER)) {
        foreach ($actors as list($m, $role, $id, $actor)) {
            $cast .= $actor."::".$role."::allocine:".$id."\n";
        }
    }

    return trim($cast);
}

function allocineGetGenre(string $genre): string {
    /*
     AlloCiné genre to Videodb genre.
    */
    $map_genres = [
          'Action'            	=> 'Action',
          'Animation'         	=> 'Animation',
          'Arts Martiaux'     	=> 'Action',
          'Aventure'            => 'Adventure',
          'Biopic'              => 'Biography',
          'Bollywood'           => 'Musical',
          'Classique'           => '-',
          'Comédie Dramatique'  => 'Drama',
          'Comédie musicale'    => 'Musical',
          'Comédie'             => 'Comedy',
          'Dessin animé'        => 'Animation',
          'Divers'              => '-',
          'Documentaire'        => 'Documentary',
          'Drame'               => 'Drama',
          'Epouvante-horreur'   => 'Horror',
          'Erotique'            => 'Adult',
          'Espionnage'          => '-',
          'Famille'             => 'Family',
          'Fantastique'         => 'Fantasy',
          'Guerre'              => 'War',
          'Historique'          => 'History',
          'Horreur'             => 'Horror',
          'Musique'             => 'Musical',
          'Policier'            => 'Crime',
          'Péplum'              => 'History',
          'Romance'             => 'Romance',
          'Science Fiction'     => 'Sci-Fi',
          'Thriller'            => 'Thriller',
          'Western'             => 'Western'];

    return $map_genres[$genre];
}

function allocineGetCountry(array $countries): string {

    $map_countries = [
  		'allemand'			=> 'Germany',
  		'américain'			=> 'USA',
  		'U.S.A.'            => 'USA',
  		'argentin'      	=> 'Argentina',
  		'arménien'      	=> 'Armenia',
  		'belge'				=> 'Belgium',
  		'britannique'		=> 'UK',
  		'bulgare'			=> 'Bulgaria',
  		'canadien'			=> 'Canada',
  		'chinois'			=> 'China',
  		'coréen'			=> 'South Korea',
  		'danois'			=> 'Denmark',
  		'espagnol'			=> 'Spain',
  		'français'			=> 'France',
  		'grec'				=> 'Greece',
  		'hollandais'		=> 'Netherlands',
  		'hong-kongais'		=> 'Hong-Kong',
  		'hongrois'			=> 'Hungary',
  		'indien'			=> 'India',
  		'irlandais'			=> 'Republic of Ireland',
  		'islandais'			=> 'Iceland',
  		'israëlien'			=> 'Israel',
  		'italien'			=> 'Italy',
  		'japonais'			=> 'Japan',
  		'luxembourgeois'	=> 'Luxembourg',
  		'mexicain'			=> 'Mexico',
  		'norvégien'			=> 'Norge',
  		'néo-zélandais'		=> 'New Zealand',
  		'polonais'			=> 'Poland',
  		'portugais'			=> 'Portugal',
  		'roumain'			=> 'Romania',
  		'russe'				=> 'Russia',
  		'serbe'				=> 'Serbia',
  		'sud-africain'  	=> 'South Africa',
  		'suédois'			=> 'Sweden',
  		'taïwanais'			=> 'Taiwan',
  		'tchèque'			=> 'Czech Republic',
  		'thaïlandais'		=> 'Thailand',
  		'turc'				=> 'Turkey',
  		'ukrainien'			=> 'Ukraine',
  		'vietnamien'		=> 'Vietnam',
      	'australien'		=> 'Australia',
    ];

    $englishVersion = '';
    foreach($countries as $country) {
        $eCountry = $map_countries[$country];
        if (empty($eCountry)) {
            dlog("Failed to find country: '$country' in allocineGetCountry");
        }
        $englishVersion .= $eCountry.', ';
    }

    return preg_replace('/, $/', '', $englishVersion);
}

function allocineGetLanguage(string $language): string {
    $languages = explode(',', $language);

    $map_languages = [
  		'Anglais'			=> 'English',
  		'Français'			=> 'French',
  		'Portugais'			=> 'Portuguese',
    ];

    $englishVersion = '';
    foreach($languages as $lang) {
        $elang = $map_languages[trim($lang)];
        if (empty($elang)) {
            dlog("Failed to find language: '$lang' in allocineGetLanguage");
        }
        $englishVersion .= $elang.', ';
    }

    return preg_replace('/, $/', '', $englishVersion);
}

/**
 *  Parses Actor-Details
 *
 *  Find image and detail URL for actor, not sure if this can be made
 *  a one-step process?  Completion waiting on update of actor
 *  functionality to support more than one engine.
 *
 * @author Douglas Mayle <douglas@mayle.org>
 * @author Andreas Goetz <cpuidle@gmx.de>
 *
 * @param string  $name  Name of the Actor
 *
 * @return array|null array with Actor-URL and Thumbnail
 */
function allocineActor($name, $actorid)
{
    global $allocineServer;

    if (empty ($actorid)) {
        return;
    }

    $url = 'http://www.allocine.fr/personne/fichepersonne_gen_cpersonne='.urlencode($actorid).'.html';
    $resp = httpClient($url, 1);

    $single = array();
    if (preg_match ('/src="([^"]+allocine.fr\/acmedia\/medias\/nmedia\/[^"]+\/[0-9]+\.jpg)[^>]+width="120"/', $resp['data'], $single)) {
        $ary[0][0]=$url;
        $ary[0][1]=$single[1];
        return $ary;
    } else {
	    return null;
    }
}

?>
