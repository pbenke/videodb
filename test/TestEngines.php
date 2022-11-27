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

        $engineName = engineGetEngine('abcde12345');
        $this->assertEquals('amazonaws', $engineName);

        $engineName = engineGetEngine('default ot imdb');
        $this->assertEquals('imdb', $engineName);
    }
}

?>
