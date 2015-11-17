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


class CSS
{
    /** String beginning */
    const START = "<style type='text/css' scoped>";
    /** String End */
    const END = "</style>\n";
    /** @var array Rules */
    public $rules = array();


    /**
     * Constructor
     */
    public function __construct(){}


    /**
     * Add rule
     *
     * @param $rule
     * @return CSSRule
     */


    public function addRule($rule)
    {
        return $this->rules[($this->countRules() + 1)] = new CSSRule($rule);
    }


    /**
     * Count rules
     *
     * @return int
     */

    private function countRules(){ return count($this->rules); }


    /**
     * To string
     *
     * @return string
     */

    public function __toString()
    {
        $r = '';
        if(!empty($this->rules)){
            foreach($this->rules as $rule){
                $r .= $rule->getName() . ' {' . $rule . '}' ;
            }
        }
        // Generate CSS
        $cssScoped = self::START . $r . self::END;

        // Add CSS to global generator
        global $GENOO_STYLES;
        if(!empty($GENOO_STYLES)){
            $GENOO_STYLES .= $cssScoped;
        } else {
            $GENOO_STYLES = $cssScoped;
        }

        // Append JS fallback
        $cssJs = '<script type="text/javascript">if(typeof GenooCSS != "undefined"){ GenooCSS.add(' . json_encode($r) . '); }</script>';
        $cssFinal = $cssScoped . $cssJs;

        // Return
        return $cssFinal;
    }
}


class CSSRule
{
    /** @var select */
    private $selector;
    /** @var array rules */
    private $rules = array();

    /**
     * Constructor
     *
     * @param string $selector
     */

    public function __construct($selector = ''){ $this->selector = $selector; }

    /**
     * @param $key
     * @param $value
     */

    public function add($key, $value){ $this->rules[] = ' ' . $key . ': ' . $value . '; '; return $this; }


    /**
     * Get name
     *
     * @return select|string
     */

    public function getName(){ return $this->selector; }


    /**
     * To string
     *
     * @return string
     */

    public function __toString()
    {
        $r = '';
        if(!empty($this->rules)){
            foreach($this->rules as $rule){ $r .= $rule;  }
        }
        return $r;
    }
}