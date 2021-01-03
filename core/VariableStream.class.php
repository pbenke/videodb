<?php
/**
 * VariableStream class
 *
 * @package Core
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @version $Id: VariableStream.class.php,v 1.4 2004/10/30 11:48:36 andig2 Exp $
 */

// stream wrappers require php > 4.3
if (version_compare(phpversion(), '4.3') < 0) {
    errorpage('PHP version mismatch',
        'At least PHP version 4.3.0 is required to run the VariableStream, please check the documentation!');
}

/**
 * VariableStream allows XML reading from variables
 * @package Core
 */
class VariableStream
{
    private $varname;
    private $position;

    function stream_open($path, $mode, $options, &$opened_path)
    {
        $url = parse_url($path);
        $this->varname = $url['host'];
        if (!isset($GLOBALS[$this->varname])) {
            trigger_error('Global variable ' . $this->varname . ' does not exist', E_USER_WARNING);
            return false;
        }
        $this->position = 0;
        return true;
    }

    function stream_read($count)
    {
        $ret = substr($GLOBALS[$this->varname], $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    function stream_eof()
    {
        return $this->position >= strlen($GLOBALS[$this->varname]);
    }

    function stream_tell()
    {
        return $this->position;
    }

    function stream_seek($offset, $whence)
    {
        if ($whence == SEEK_SET) {
            $this->position = $offset;
            return true;
        }
        return false;
    }

    function stream_stat()
    {
        return array();
    }
}

// register stream type to allow use in xml->load
stream_wrapper_register('var', 'VariableStream');

?>
