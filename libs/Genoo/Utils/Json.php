<?php

/**
 * This file is part of the Genoo plugin.
 *
 * Copyright (c) 2014 Genoo, LLC (http://www.genoo.com/)
 *
 * For the full copyright and license information, please view
 * the Genoo.php file in root directory of this plugin.
 *
 * @author     David Grudl
 */

namespace Genoo\Utils;

class Json
{

    /** @var array Messages */
    private static $messages = array(
        JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Syntax error, malformed JSON',
        JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
        JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
        5 /*JSON_ERROR_UTF8*/ => 'Invalid UTF-8 sequence',
        6 /*JSON_ERROR_RECURSION*/ => 'Recursion detected',
        7 /*JSON_ERROR_INF_OR_NAN*/ => 'Inf and NaN cannot be JSON encoded',
        8 /*JSON_ERROR_UNSUPPORTED_TYPE*/ => 'Type is not supported',
    );


    /**
     * Encode
     *
     * @param $value
     * @return string
     * @throws JsonException
     */

    public static function encode($value)
    {
        // needed to receive 'Invalid UTF-8 sequence' error; PHP bugs #52397, #54109, #63004
        if (function_exists('ini_set')) { // ini_set is disabled on some hosts :-(
            $old = ini_set('display_errors', 0);
        }

        // needed to receive 'recursion detected' error
        set_error_handler(function($severity, $message) {
            restore_error_handler();
            throw new JsonException($message);
        });

        $json = json_encode($value);

        restore_error_handler();
        if (isset($old)) {
            ini_set('display_errors', $old);
        }
        if ($error = json_last_error()) {
            $message = isset(static::$messages[$error]) ? static::$messages[$error] : 'Unknown error';
            throw new JsonException($message, $error);
        }
        return $json;
    }


    /**
     * Decode
     *
     * @param $json
     * @return mixed
     * @throws JsonException
     */

    public static function decode($json, $arr = false)
    {
        if (!preg_match('##u', $json)) { // workaround for PHP < 5.3.3 & PECL JSON-C
            throw new JsonException('Invalid UTF-8 sequence', 5);
        }

        $value = json_decode($json, $arr);

        if ($value === NULL
            && $json !== ''  // it doesn't clean json_last_error flag until 5.3.7
            && $json !== 'null' // in this case NULL is not failure
        ){
            $error = json_last_error();
            $message = isset(static::$messages[$error]) ? static::$messages[$error] : 'Unknown error';
            throw new JsonException($message, $error);
        }
        return $value;
    }


    /**
     * Is this json?
     *
     * @param $json
     * @return bool
     */

    public static function isJson($json)
    {
        json_decode($json);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

class JsonException extends \Exception {}