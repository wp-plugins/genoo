<?php

/**
 * This file is part of the Genoo plugin.
 *
 * Copyright (c) 2014 Genoo, LLC (http://www.genoo.com/)
 *
 * For the full copyright and license information, please view
 * the Genoo.php file in root directory of this plugin.
 */

namespace Genoo\Wordpress;


class Page
{
    /** @var string */
    var $renderBefore = '<div class="wrap">';
    /** @var string */
    var $renderAfter = '</div>';
    /** @var string */
    var $renderBeforeWidgets = '<div class="metabox-holder">';
    /** @var string */
    var $renderAfterWidgets = '</div>';
    /** @var string */
    var $title;
    /** @var string */
    var $guts;
    /** @var array */
    var $widgets = array();


    /**
     * Add page title
     *
     * @param $title
     * @return Page
     */

    public function addTitle($title)
    {
        $this->title = $title;
        return $this;
    }


    /**
     * Add widget
     *
     * @param $title
     * @param $guts
     * @return Page
     */

    public function addWidget($title, $guts)
    {
        $this->widgets[] = (object)array('title' => $title, 'guts' => $guts);
        return $this;
    }


    /**
     * Add guts
     *
     * @param $guts
     */

    public function addContent($guts){ $this->guts = $guts; }


    /**
     * Render
     */

    public function __toString()
    {
        $output = '';
        $output .= $this->renderBefore;
            $output .= $this->title ? "<h2>$this->title</h2>" : '';
            $output .= $this->guts ? $this->guts : '';
            if($this->widgets){
                $counter = 1;
                $output .= $this->renderBeforeWidgets;
                foreach($this->widgets as $widget){
                    $output .= '<div class="postbox genooPostbox"><div class="group">';
                    $output .= '<h3>'. $widget->title .'</h3>';
                    $output .= '<table class="form-table"><tbody>';
                    if(is_array($widget->guts)){
                        foreach($widget->guts as $key => $value){
                            $output .= '<tr valign="top"><th scope="row">'. $key .'</th><td>'. $value .'</td></tr>';
                        }
                    } else {
                        $output .= '<tr valign="top"><td>';
                            $output .= $widget->guts;
                        $output .= '</td></tr>';
                    }
                    $output .= '</tbody></table></div></div>';
                    if($counter % 2 == 0){ $output .= '<div class="clear"></div>'; }
                    ++$counter;
                }
                $output .= $this->renderAfterWidgets;
            }
        $output .= $this->renderAfter;
        // return string
        return $output;
    }
}