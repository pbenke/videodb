<?php declare(strict_types=1);
/**
 * Dvdfr Parser
 *
 * Parses data from www.dvdfr.com (french site)
 * 2006-08-12 Update Sebastien Koechlin <seb.videodb@koocotte.org>
 *
 * @package Engines
 * @author  tedemo  <tedemo@free.fr>
 * @link    http://www.dvdfr.com
 * @version $Id: dvdfr.php,v 1.7 2011/06/23 12:27:28 robelix Exp $
 */

require_once './core/compatibility.php';

$GLOBALS['dvdfrServer']	= 'https://www.dvdfr.com';
$GLOBALS['dvdfrIdPrefix'] = 'dvdfr:';

/**
 *  Get meta information about the engine
 *
 * @return (int|string)[]
 *
 * @psalm-return array{name: 'Dvdfr (fr)', stable: 0}
 */
function dvdfrMeta(): array
{
    return array('name' => 'Dvdfr (fr)', 'stable' => 0, 'capabilities' => array('movie', 'image'));
}

/**
 * Clean a string
 *
 * @param   string    The string to clean
 * @return  string    The cleaned string
 */
function dvdfrCleanStr(string $str): string
{
    // Remove spaces
    $str = trim($str);

    // Translate strange (MS-Word?) quotes
    $str = preg_replace('/&#039;/', "'", $str);

    // Remove HTML entities
    $str = html_entity_decode($str);

    return $str;
}


/**
 * Get Url to visit Dvdfr for a specific movie
 *
 * @param   string	$id	The movie's external id
 * @return  string		The visit URL
 */
function dvdfrContentUrl($id): string
{
    global $dvdfrServer;

    list($engineword, $dvdfrID) = explode(':', $id, 2);

    return $dvdfrServer.'/api/dvd.php?id='.$dvdfrID;
}


/**
 * Get Url to search Dvdfr for a movie
 *
 * @param   string    The search string
 * @return  string    The search URL (GET)
 */
function dvdfrSearchUrl(string $title): string
{
    global $dvdfrServer;

    return $dvdfrServer.'/api/search.php?title='.urlencode($title);
}

/**
 * Search a Movie
 *
 * Searches for a given title on Dvdfr and returns the found links in
 * an array
 *
 * @return  array     Associative array with id and title
 */
function dvdfrSearch($title): array
{
    global $dvdfrServer;
    global $dvdfrIdPrefix;
    global $cache;
    global $CLIENTERROR;

    $para['useragent'] = 'VideoDB (http://www.videodb.net/)';
    $resp = httpClient(dvdfrSearchUrl($title), $cache, $para);
    if (!$resp['success']) {
        $CLIENTERROR .= $resp['error']."\n";
    }

    // Encoding
    $data['encoding'] = $resp['encoding'];

    $results = new SimpleXMLElement($resp['data']);

    foreach ($results->dvd as $result) {
        $title = dvdfrSplitTitle((string) $result->titres->fr);

        $row['id']       = $dvdfrIdPrefix.$result->id;
        $row['title']    = $title['title'];
        $row['subtitle'] = $title['subtitle'];
        $row['year']     = $result->annee;
        $row['imgsmall'] = $result->cover;
//        $data['details'] = null;

        $data[] = $row;
    }
    return $data;
}

/**
 * Fetches the data for a given Dvdfr-ID
 *
 * @param   int   IMDB-ID
 * @return  array Result data
 */
function dvdfrData($imdbID): array
{
    global $dvdfrServer;
    global $dvdfrIdPrefix;
    global $CLIENTERROR;
    global $cache;

    $data = array(); // result

    $para['useragent'] = 'VideoDB (http://www.videodb.net/)';

    // fetch mainpage
    $resp = httpClient(dvdfrContentUrl($imdbID), $cache, $para);
    if (!$resp['success']) {
        $CLIENTERROR .= $resp['error']."\n";
    }

    // add encoding
    $data['encoding'] = $resp['encoding'];

    // See http://www.dvdfr.com/api/dvd.php?id=2869 for output
    $movie = new SimpleXMLElement($resp['data']);

    $title = dvdfrSplitTitle((string) $movie->titres->fr);
    $data['title']      = $title['title'];
    $data['subtitle']   = $title['subtitle'];
    $data['origtitle']  = $movie->titres->vo;

//    $data['language'] = does not exist on dvdfr
    $data['year']      = $movie->annee;
    $data['coverurl']  = $movie->cover;
    $data['runtime']   = $movie->duree;
    $data['rating']    = $movie->critiques->public;
    $data['plot']      = $movie->synopsis;

    $countries = [];
    foreach ($movie->listePays->pays as $country) {
        $countries[] = $country;
    }
    $data['country'] = implode(', ', $countries);

    $directors = [];
    $actors = [];
    foreach ($movie->stars->star as $star) {
        if ($star['type'] == 'Réalisateur') {
            $directors[] = $star;
        } else if ($star['type'] == 'Acteur') {
            $actors[] = $star . '::::' . $dvdfrIdPrefix . $star['id'];
        }
    }
    $data['director'] = implode(', ', $directors);
    $data['cast'] = implode("\n", $actors);

    // maps dvdfr category ids to videodb category names
    /*
     Modify	id	name
     edit	1	Action
     edit	2	Adventure
     edit	3	Animation
     edit	4	Comedy
     edit	5	Crime
     edit	6	Documentary
     edit	7	Drama
     edit	8	Family
     edit	9	Fantasy
     edit	10	Film-Noir
     edit	11	Horror
     edit	12	Musical
     edit	13	Mystery
     edit	14	Romance
     edit	15	Sci-Fi
     edit	16	Short
     edit	17	Thriller
     edit	18	War
     edit	19	Western
     edit	20	Adult
     edit	21	Music
     edit	22	Biography
     edit	23	History
     edit	24	Sport
    */

    $category_map = [
        1  =>  "Action",
        2  =>  "Animation",
        3  =>  "Adventure", //"Aventure",
        4  =>  "Comedy", //"Comédie",
        5  =>  "Comedy", //"Comédie dramatique",
        6  =>  "Comedy", //"Comédie musicale",
        7  =>  "Music", //"Concert",
        8  =>  "Conte",
        9  =>  "Court métrage",
        10  => "Culture",
        11  => "Danse",
        12  => "Divers",
        13  => "Documentaire",
        14  => "Drame",
        15  => "Adult", //"Erotique",
        16  => "Espionnage",
        17  => "Fantastique",
        18  => "Guerre",
        19  => "Music", //"Hard Rock / Métal",
        20  => "Historique",
        21  => "Horreur",
        22  => "Comedy", //"Humour",
        23  => "Japanimation",
        24  => "Adult", //"Hentai / Japanimation érotique",
        25  => "Music", //"Jazz / Blues",
        26  => "Music", //"Karaoké",
        27  => "Kung Fu",
        28  => "Méthode",
        29  => "Muet",
        30  => "Musical",
        31  => "Music", //"Opéra",
        32  => "Music", //"Musique classique",
        33  => "Péplum",
        34  => "Policier",
        54  => "Enfants / Famille",
        55  => "Music", //"Rap",
        56  => "Sci-Fi", //"Science-fiction",
        57  => "Mini-series / Feuilletons",
        58  => "Série TV",
        59  => "Sitcom",
        60  => "Série anime / OAV",
        61  => "Autres séries",
        62  => "Spectacle",
        63  => "Sport",
        64  => "Music", //"Techno / Electro",
        65  => "Theatre",
        66  => "Thriller",
        67  => "Variété française",
        68  => "Variété internationale",
        69  => "Voyages",
        70  => "Western",
        71  => "Musiques du monde",
        72  => "Beaux-arts",
        73  => "Emotion",
        74  => "Comédie romantique",
        75  => "Série d'animation enfants",
        76  => "Music", //"R&amp;B / Soul",
        78  => "Univers LGBT",
        79  => "Jeux",
        81  => "Bollywood",
        82  => "Sports mécaniques",
        83  => "Catch",
        85  => "Santé / Bien-être",
        86  => "Animaux",
        87  => "Culture urbaine",
        88  => "Société",
        89  => "Ambiance / Relaxation",
        90  => "Science / Découvertes",
        91  => "Cuisine / Jardinage / Déco",
        92  => "Chasse / Pêche",
        93  => "Nature",
        94  => "Football",
        95  => "Documentaire-fiction",
        96  => "Kickboxing / Freefight",
        97  => "Fan Service",
        98  => "Gore",
        99  => "Biopic / Biographie / Histoire vraie",
        100 => "Série format court",
        101 => "Musique de films",
        102 => "Catastrophe",
        103 => "Romance",
        104 => "Anime Yaoi",
        105 => "Documentaire musical",
        106 => "Emissions TV",
        107 => "Telenovela",
        108 => "Série documentaire",
        111 => "Cinéma expérimental",
        112 => "Super-héros",
        114 => "Parodie",
        117 => "Spiritualité / Religion",
        109 => "Caritatif",
        110 => "Moyen métrage",
        113 => "Fantasy",
        115 => "Court et moyen métrage documentaire"];

    foreach ($movie->categories->categorie as $category) {
        $data['genres'][] = $category_map[(int) $category['id']];
    }

    return $data;
}

function dvdfrSplitTitle(string $title): array
{
    // split title - subtitle
    list($t, $s) = array_pad(explode(': ', $title, 2), 2, '');

    // no dash, lets try colon
    if (empty($s)) {
        list($t, $s) = array_pad(explode(' - ', $title, 2), 2, '');
    }
    $data['title'] = trim($t);
    $data['subtitle'] = trim($s);
    return $data;
}

function dvdfrActorUrl(string $name, string $id): string
{
    global $dvdfrServer;
    global $dvdfrIdPrefix;

    $name = str_replace(' ', '-', $name);
    $name = mb_strtolower($name, 'UTF-8');

    $id = str_replace($dvdfrIdPrefix, "", $id);

    return $dvdfrServer.'/stars/s'.$id."-".$name.'.html';
}

/**
 *  Parses Actor-Details
 *
 *  Find image and detail URL for actor, not sure if this can be made
 *  a one-step process?  Completion waiting on update of actor
 *  functionality to support more than one engine.
 *
 * @param string  $name  Name of the Actor
 *
 * @return void array with Actor-URL and Thumbnail
 */
function dvdfrActor($name, $actorengineid): array
{
    global $cache;

    $url = dvdfrActorUrl($name, $actorengineid);

    $resp = httpClient($url, $cache);
    if (!$resp['success']) {
        $CLIENTERROR .= $resp['error']."\n";
    }

    // add encoding
    $data['encoding'] = $resp['encoding'];

    $ary = [];
    if (preg_match('/<div id="starPicture" .+? src="(.+?)"/si', $resp['data'], $m)) {
        $ary[0][0] = '/stars/s4028-scarlett-johansson.html';
        if (!strstr($m[1], 'nophoto.jpg')) {
            $ary[0][1] = $m[1]; // img url
        }
    }

    return $ary;
}

?>
