<?php

/**
 * This file is part of the Genoo plugin.
 *
 * Copyright (c) 2014 Genoo, LLC (http://www.genoo.com/)
 *
 * For the full copyright and license information, please view
 * the Genoo.php file in root directory of this plugin.
 *
 */

namespace Genoo\Utils;


class ArrayObject extends \ArrayObject
{

    /**
     * Insert Before
     *
     * @param $input
     * @param $index
     * @param $newKey
     * @param $element
     * @return mixed
     * @throws \Exception
     */

    public static function appendBefore($input, $index, $newKey, $element)
    {
        if (!array_key_exists($index, $input)){
            throw new \Exception("Index not found");
        }
        $tmpArray = array();
        foreach ($input as $key => $value){
            if ($key === $index) {
                $tmpArray[$newKey] = $element;
            }
            $tmpArray[$key] = $value;
        }
        return $input;
    }


    /**
     * Insert After
     *
     * @param $input
     * @param $index
     * @param $newKey
     * @param $element
     * @return array
     * @throws \Exception
     */

    public static function appendAfter($input, $index, $newKey, $element)
    {
        if (!array_key_exists($index, $input)){
            throw new \Exception("Index not found");
        }
        $tmpArray = array();
        foreach ($input as $key => $value){
            $tmpArray[$key] = $value;
            if ($key === $index) {
                $tmpArray[$newKey] = $element;
            }
        }
        return $tmpArray;
    }


    /**
     * Insert To position
     *
     * @param $array
     * @param $element
     * @param null $position
     * @return array
     */

    public static function appendTo(&$array, $position=null, $element)
    {
        if(count($array) == 0){
            $array[] = $element;
        } elseif (is_numeric($position) && $position < 0){
            if((count($array)+position) < 0) {
                $array = array_insert($array,$element,0);
            } else {
                $array[count($array)+$position] = $element;
            }
        } elseif (is_numeric($position) && isset($array[$position])){
            $part1 = array_slice($array,0,$position,true);
            $part2 = array_slice($array,$position,null,true);
            $array = array_merge($part1,array($position=>$element),$part2);
            foreach($array as $key=>$item){
                if (is_null($item)) {
                    unset($array[$key]);
                }
            }
        } elseif (is_null($position)){
            $array[] = $element;
        } elseif (!isset($array[$position])){
            $array[$position] = $element;
        }
        $array = array_merge($array);
        return $array;
    }


    /**
     * Append to the end of array
     *
     * @param $array
     * @param $append
     * @return mixed
     */

    public static function appendToTheEnd($array, $append)
    {
        array_push($array, $append);
        return $array;
    }


    /**
     * Prepend to beginning of the array
     *
     * @param $array
     * @param $prepend
     * @return mixed
     */

    public static function prependToTheBeginning($array, $prepend)
    {
        array_unshift($array, $prepend);
        return $array;
    }


    /**
     * Remove by value
     *
     * @param $array
     * @param $element
     * @return array
     */

    public static function removeByValue($array, $element)
    {
        return array_diff($array, array($element));
    }
}