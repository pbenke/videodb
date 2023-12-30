<?php declare(strict_types=1);
/**
 * test_dvdfr.php
 *
 * DVDfr engine test case
 *
 * @package Test
 * @author Sébastien Koechlin <seb.videodb@koocotte.org>
 * @version $Id: test_dvdfr.php,v 1.9 2013/02/02 11:38:59 andig2 Exp $
 */

require_once './core/functions.php';
require_once './engines/engines.php';

use PHPUnit\Framework\TestCase;

class TestDVDFR extends TestCase
{
    function testGetContentUrl(): void
	{
        $id = 'dvdfr:2869';
        $url = engineGetContentUrl($id, 'dvdfr');

        $this->assertEquals('https://www.dvdfr.com/api/dvd.php?id=2869', $url);
	}

    function testGetSearchUrl(): void
	{
        $title = 'Jojo Rabbit';
        $url = engineGetSearchUrl($title, 'dvdfr');

        $this->assertEquals('https://www.dvdfr.com/api/search.php?title=Jojo+Rabbit', $url);
	}

	function testMovie(): void
	{
		// Star Wars: Episode I
		// https://www.dvdfr.com/dvd/f2869_star_wars_-_episode_i_-_la_menace_fantome.html
		// https://www.dvdfr.com/dvd/dvd.php?id=2869
		$id = 'dvdfr:2869';

		$data = engineGetData($id, 'dvdfr', false);

		$this->assertNotCount(0, $data);

//		var_dump($data);

		$this->assertEquals('Star Wars - Episode I', $data['title']);
		$this->assertEquals('La Menace fantôme', $data['subtitle']);
		$this->assertEquals('Star Wars: Episode I - The Phantom Menace', $data['origtitle']);
		$this->assertArrayNotHasKey('language', $data);
		$this->assertEquals(1999, $data['year']);
		$this->assertEquals('https://www.dvdfr.com/images/dvd/covers/100x140/0cc3a79c3a4b3c5f60accb2b611e2043/2869/old-star_wars_1.0.jpg', $data['coverurl']);
		$this->assertEquals(130, $data['runtime']);
		$this->assertEquals('4,53', $data['rating']);
		$this->assertEquals('George Lucas', $data['director']);
		$this->assertEquals('USA', $data['country']);
		$this->assertContains('Sci-Fi', $data['genres']);
		$this->assertContains('Adventure', $data['genres']);

		$this->assertMatchesRegularExpression('/Liam Neeson.*Ewan McGregor.*Natalie Portman/s', $data['cast']);
		$this->assertMatchesRegularExpression("/Bloqués sur la planète Tatooine après avoir secouru la Reine Amidala \(Natalie Portman\), l'apprenti Jedi Obi-Wan Kenobi \(Ewan McGregor\)/", $data['plot']);
	}

	function testMovieWithSubTitle(): void
	{
		// Star Wars: Episode I
		// https://www.dvdfr.com/dvd/f2869_star_wars_-_episode_i_-_la_menace_fantome.html
		// https://www.dvdfr.com/dvd/dvd.php?id=2869
		$id = 'dvdfr:171716';

		$data = engineGetData($id, 'dvdfr', false);

		$this->assertNotCount(0, $data);

//		var_dump($data);

		$this->assertEquals('Mobile Fighter G Gundam', $data['title']);
		$this->assertEquals('Première partie', $data['subtitle']);
		$this->assertEquals('Kidô butôden ji Gandamu', $data['origtitle']);
	}

	function testMovieWithMultipleCounties(): void
	{
		// Jojo Rabit
		// https://www.dvdfr.com/dvd/f167167-jojo-rabbit.html
		// https://www.dvdfr.com/api/dvd.php?id=f167167
		$id = 'dvdfr:167167';
		$data = engineGetData($id, 'dvdfr', false);

		$this->assertNotCount(0, $data);
//		var_dump($data);

		$this->assertEquals('Jojo Rabbit', $data['title']);
		$this->assertEmpty($data['subtitle']);
		$this->assertEmpty($data['origtitle']);
		$this->assertArrayNotHasKey('language', $data);
		$this->assertEmpty($data['rating']);
		$this->assertContains('Comedy', $data['genres']);
		$this->assertEquals('Nouvelle-Zélande, USA, République tchèque', $data['country']);
	}

	function testSearchStarWars(): void
	{
		// Star Wars
		// http://www.dvdfr.com/api/search.php?title=star%20wars
		// Result: XML with many results
		$search = 'star wars';
		$data = engineSearch($search, 'dvdfr');

//		var_dump($data);

		$obj = array('dvdfr:83682', 'dvdfr:90454', 'dvdfr:163500', 'dvdfr:163501', 'dvdfr:163503', 'dvdfr:300695');
		$this->assertTrue(sizeof($data) > 6);

		// Search each movie in result
		foreach($obj as $search) {
			$found = false;
			foreach ($data as $movie) {
				if ($search == $movie['id']) {
				    $found = true;
                }
			}
			$this->assertTrue($found);
		}
	}

	function testGetActorUrl(): void
	{
	    $name = 'Scarlett Johansson';
	    $id = 'dvdfr:4028';
	    $url = engineGetActorUrl($name, $id, 'dvdfr');

        $this->assertEquals('https://www.dvdfr.com/stars/s4028-scarlett-johansson.html', $url);
	}

	function testEngineActor(): void
	{
	    $name = 'Scarlett Johansson';
	    $id = 'dvdfr:4028';
	    $data = engineActor($name, $id, 'dvdfr');

        $this->assertEquals('/stars/s4028-scarlett-johansson.html', $data[0][0]);
        $this->assertEquals("https://upload.wikimedia.org/wikipedia/commons/thumb/6/60/Scarlett_Johansson_by_Gage_Skidmore_2_%28cropped%29.jpg/600px-Scarlett%20Johansson%20by%20Gage%20Skidmore%202%20%28cropped%29.jpg", $data[0][1]);
	}
}

?>
