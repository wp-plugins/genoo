<?php

/**
 * This file is part of the Genoo plugin.
 *
 * Copyright (c) 2014 Genoo, LLC (http://www.genoo.com/)
 *
 * For the full copyright and license information, please view
 * the Genoo.php file in root directory of this plugin.
 */

namespace Genoo;

use Genoo\CTA;


class WidgetCTADynamic extends WidgetCTA
{

    /** @var CTA|int  */
    var $preCta;


    /**
     * Construct me up!
     *
     * @param bool $base
     * @param $number
     * @param CTA $cta
     */
    public function __construct($base, $number, CTA $cta)
    {
        parent::__constructDynamic(
            $base,
            'Genoo CTA',
            array(
                'description' => __('Genoo Call-To-Action widget is empty widget, that displays CTA when its set up on single post / page.', 'genoo')
            )
        );
        $this->id =  $base . '-' . $number;
        $this->number = $number;
        $this->preCta = $cta;
        $this->set();
    }


    /**
     * Overwrite the set method of parent
     */

    public function set()
    {
        global $post;
        if(is_object($post) && ($post instanceof \WP_Post)){
            global $post;
            $this->isSingle = true;
            $this->cta = $this->preCta;
            $this->canHaveMobile = false;
            // Classlist has different rendering
            if($this->cta->isClasslist){
                $this->widgetForm = new WidgetLumen(false);
            } else {
                $this->widgetForm = new WidgetForm(false);
            }
            $this->widgetForm->id = $this->id;
        }
    }


    /**
     * Nope, we don't want to run this one
     *
     * @throws \Exception
     */

    public function setThroughShortcode($id, $post, $atts = array()){
        throw new \Exception('Dynamic CTA Widget cannot be initiated through Shortcode');
    }
}