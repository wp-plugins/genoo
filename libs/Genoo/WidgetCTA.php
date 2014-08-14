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

use Genoo\CTA,
    Genoo\Utils\Strings,
    Genoo\WidgetForm,
    Genoo\Wordpress\Attachment;


/**
 * Genoo CTA
 */

class WidgetCTA extends \WP_Widget
{

    /** @var \Genoo\CTA  */
    var $cta;
    /** @var \Genoo\WidgetForm  */
    var $widgetForm;
    /** @var bool */
    var $isSingle = false;


    /**
     * Constructor registers widget in wordpress
     *
     * @param bool $constructParent
     */

    function __construct($constructParent = true)
    {
        if($constructParent){
            parent::__construct('genooCTA', 'Genoo CTA', array('description' => __('Genoo Call-To-Action widget is empty widget, that displays CTA when its set up on single post / page.', 'genoo')));
        }
    }


    /**
     * Set
     */

    private function set()
    {
        global $post;
        if(is_object($post) && ($post instanceof \WP_Post)){
            global $post;
            $this->isSingle = true;
            $this->cta = new CTA($post);
            $this->widgetForm = new WidgetForm(false);
            $this->widgetForm->id = $this->id;
        }
    }


    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */

    public function widget($args, $instance)
    {
        $this->set();
        // we only care about single post
        echo $this->getHtmlInner($args, $instance);
    }


    /**
     * Return CTA
     *
     * @return CTA
     */

    public function getCta(){ return $this->cta; }


    /**
     * Get html
     *
     * @param $a
     * @param $i
     */

    public function getHtml($a, $i)
    {
        $r = '';
        echo 'aaaa';
        if($this->isSingle){
            $this->set();
            if($this->cta->has){
                if($this->cta->isForm){
                    $instance = array();
                    $instance['modal'] = 0;
                    $instance['choice'] = $this->cta->isHtml ? 'html' : 'img';
                    if($this->cta->isImage){
                        $instance['img'] = $this->cta->image;
                        $instance['imgHover'] = $this->cta->imageHover;
                    } else {
                        $instance['button'] = $this->cta->linkText;
                    }
                    $instance['form'] = $this->cta->formId;
                    $instance['theme'] = $this->cta->formTheme;
                    $instance['desc'] = $this->cta->desc;
                    $instance['title'] = $this->cta->title;
                    $instance['displayTitle'] = $this->cta->displayTitle;
                    $instance['displayDesc'] = $this->cta->displayDesc;
                    $r = $this->widgetForm->getHtml(array(), $instance);
                }
            }
        }

        return $r;
    }


    /**
     * Get inner HTML
     *
     * @param $args
     * @param $instance
     * @return string
     */

    public function getHtmlInner($args, $instance)
    {
        $r = '';
        if($this->isSingle){
            $bid = 'button'. $this->id;
            if($this->cta->has){
                $instance = array();
                $instance['modal'] = 1;
                $instance['choice'] = $this->cta->isHtml ? 'html' : 'img';
                if($this->cta->isImage){
                    $instance['img'] = $this->cta->image;
                    $instance['imgHover'] = $this->cta->imageHover;
                } else {
                    $instance['button'] = $this->cta->linkText;
                }
                $instance['form'] = $this->cta->formId;
                $instance['theme'] = '';
                $instance['desc'] = $this->cta->desc;
                $instance['title'] = $this->cta->title;
                $instance['displayTitle'] = $this->cta->displayTitle;
                $instance['displayDesc'] = $this->cta->displayDesc;
                if($this->cta->isForm){
                    $r .= $this->widgetForm->getHtml($args, $instance);
                } elseif($this->cta->isLink){
                    if(isset($instance['displayTitle']) && $instance['displayTitle'] == true){ $r .= '<div class="genooTitle">' . $args['before_title'] . $instance['title'] . $args['after_title'] . '</div>'; }
                    if(isset($instance['displayDesc']) && $instance['displayDesc'] == true){ $r .= '<div class="genooGuts"><p class="genooPadding">' . $instance['desc'] . '</p></div>'; }
                    $r .= '<form target="_blank" action="'. $this->cta->link .'">';
                        $r .= '<span id="'. $bid .'">';
                        $r .= '<input type="submit" value="'. $this->cta->linkText .'" />';
                        $r .= '</span>';
                    $r .= '</form>';
                    if($this->cta->isImage && (!empty($this->cta->image) || !empty($this->cta->imageHover))){
                        $r .= Attachment::generateCss($this->cta->image, $this->cta->imageHover, $bid);
                    }
                }
            }
        }
        return $r;
    }


    /**
     * Widget settings form
     *
     * @param $instance
     */

    public function form($instance){ echo '&nbsp;'; }
}
