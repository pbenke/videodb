<?php
/**
 * testEngines.php
 *
 * Engines test case
 *
 * @package Test
 */

require_once './core/functions.php';
require_once './engines/engines.php';
use PHPUnit\Framework\TestCase;

class TestEngines extends TestCase {

    function testGetEngineFromId() {
        $engineName = engineGetEngine('imdb:tt1234567');
        $this->assertEquals('imdb', $engineName);

        $engineName = engineGetEngine('imdbapi:tt1234567');
        $this->assertEquals('imdbapi', $engineName);

        $engineName = engineGetEngine('ABCDE12345');
        $this->assertEquals('amazonaws', $engineName);

        $engineName = engineGetEngine('default ot imdb');
        $this->assertEquals('imdb', $engineName);
    }

    function testEngineGetActorEngine() {
        $engineName = engineGetActorEngine('imdb:nm1234567');
        $this->assertEquals('imdb', $engineName);

        $engineName = engineGetActorEngine('imdbapi:nm1234567');
        $this->assertEquals('imdbapi', $engineName);

        $engineName = engineGetActorEngine('default to imdb');
        $this->assertEquals('imdb', $engineName);
    }
}

?>
