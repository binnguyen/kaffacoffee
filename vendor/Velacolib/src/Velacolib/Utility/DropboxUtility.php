<?php
/**
 * Created by PhpStorm.
 * User: MrHung
 * Date: 10/25/14
 * Time: 11:35 AM
 */

namespace Velacolib\Utility;

use Zend\Mail;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Message as MimeMessage;
use Zend\Mvc\Controller\AbstractActionController;
use Velacolib\Utility\Utility;
use Zend\Validator\File\MimeType;

class DropboxUtility extends AbstractActionController {

    public static $option;
    public static $servicelocator;

    public static function getSM()
    {
        return self::$servicelocator;
    }

    public static function setSM($val)
    {
        self::$servicelocator = $val;
    }


    static function store_token($token, $name)
    {
        if(!file_put_contents("public/tokens/$name.token", serialize($token)))
            die('<br />Could not store token! <b>Make sure that the directory `tokens` exists and is writable!</b>');
    }

    static  function load_token($name)
    {
        if(!file_exists("public/tokens/$name.token")) return null;
        return @unserialize(@file_get_contents("public/tokens/$name.token"));
    }

    static function delete_token($name)
        {
            @unlink("public/tokens/$name.token");
        }





    static function enable_implicit_flush()
    {
        @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);
        for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
        ob_implicit_flush(1);
        echo "<!-- ".str_repeat(' ', 2000)." -->";
    }

} 