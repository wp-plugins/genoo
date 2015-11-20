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
    Genoo\Wordpress\Attachment,
    Genoo\Wordpress\Post;


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
    /** @var bool  */
    var $skipSet = false;
    /** @var bool  */
    var $skipMobileButton = false;
    /** @var array  */
    var $shortcodeAtts = array();
    /** @var bool */
    var $canHaveMobile = false;
    /** @var bool */
    public $isWidgetCTA = false;


    /**
     * Constructor registers widget in WordPress
     *
     * @param bool $constructParent
     */

    function __construct($constructParent = true)
    {
        if($constructParent){
            parent::__construct(
                'genoocta',
                'Genoo CTA',
                array('description' => __('Genoo Call-To-Action widget is empty widget, that displays CTA when its set up on single post / page.', 'genoo'))
            );
        }
    }


    /**
     * Construct Dynamic Widget
     *
     * @param $id_base
     * @param $name
     * @param array $widget_options
     * @param array $control_options
     */

    function __constructDynamic($id_base, $name, $widget_options = array(), $control_options = array())
    {
        parent::__construct($id_base, $name, $widget_options, $control_options);
    }


    /**
     * Set
     */

    public function set()
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
     * Set Widget Through Shortcode
     *
     * @param $id
     * @param $posr
     * @param $atts
     */

    public function setThroughShortcode($id, $post, $atts = array())
    {
        $this->isSingle = true;
        $this->skipSet = true;
        $this->canHaveMobile = false;
        $this->cta = new CTA();
        $this->cta->setCta($post);
        $this->id = $this->id_base . 'Shortcode' . $id;
        $this->widgetForm = new WidgetForm(false);
        $this->widgetForm->id = $this->id;
        $this->skipMobileButton = true;
        $this->shortcodeAtts = $atts;
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
     * Get HTML
     *
     * @param $a
     * @param $i
     * @return null|string
     */

    public function getHtml($a = null, $i = null)
    {
        $instance = !is_null($i) ? $i : $this->getInnerInstance();
        if(is_object($this->widgetForm) && method_exists($this->widgetForm, 'getHtml')){
            return $this->widgetForm->getHtml(array(), $instance);
        }
        return null;
    }

    /**
     * Get CTA Modal Class
     *
     * @param array $instance
     *
     * @return string
     */
    public function getCTAModalClass($instance = array())
    {
        if(isset($instance['popup']['image-on']) && !empty($instance['popup']['image-on'])){
            $image = wp_get_attachment_image($instance['popup']['image'], 'medium', FALSE);
            if($image){
                return 'genooModalPopBig';
            }
        }
        return '';
    }


    /**
     * Get Inner Instance - for modal window processing.
     *
     * @param bool $skip
     * @return array
     */

    public function getInnerInstance()
    {
        $instance = array();
        if($this->isSingle){
            if($this->skipSet == false) $this->set();
            if($this->cta->has){
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
                $instance['msgSuccess'] = $this->cta->messageSuccess;
                $instance['msgFail'] = $this->cta->messageError;
                $instance['skipMobileButton'] = $this->skipMobileButton;
                $instance['shortcodeAtts'] = $this->shortcodeAtts;
                $instance['popup'] = $this->cta->popup;
            }
        }
        return $instance;
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
                $instance['lumen'] = $this->cta->classList;
                $instance['theme'] = '';
                $instance['desc'] = $this->cta->desc;
                $instance['title'] = $this->cta->title;
                $instance['displayTitle'] = $this->cta->displayTitle;
                $instance['displayDesc'] = $this->cta->displayDesc;
                $instance['skipMobileButton'] = $this->skipMobileButton;
                $instance['shortcodeAtts'] = $this->shortcodeAtts;
                $instance['canHaveMobile'] = $this->canHaveMobile;
                $instance['popup'] = $this->cta->popup;
                $instance['isPopOver'] = $this->cta->isPopOver;
                $instance['popOverTime'] = $this->cta->popOverTime;
                $instance['popOverHide'] = $this->cta->popOverHide;
                $instance['hideButton'] = isset($this->shortcodeAtts['time']) ? TRUE : FALSE;
                $instance['hideButtonTIME'] = isset($this->shortcodeAtts['time']) ? $this->shortcodeAtts['time'] : 0;
                $instance['followOriginalUrl'] = $this->cta->followOriginalUrl;
                $isHidePopOver = $instance['isPopOver'] && $instance['popOverHide'] ? TRUE : FALSE;
                if($this->cta->isForm || $this->cta->isClasslist){
                    $r .= $this->widgetForm->getHtml($args, $instance);
                } elseif($this->cta->isLink){
                    // before widget
                    $r .= isset($args['before_widget']) ? $args['before_widget'] : '';
                    // title and data
                    if(isset($instance['displayTitle']) && $instance['displayTitle'] == true){ $r .= '<div class="genooTitle">' . $args['before_title'] . $instance['title'] . $args['after_title'] . '</div>'; }
                    if(isset($instance['displayDesc']) && $instance['displayDesc'] == true){ $r .= '<div class="genooGuts"><p class="genooPadding">' . $instance['desc'] . '</p></div>'; }
                    // only links
                    if($this->cta->isLink){
                        $blank = $this->cta->isNewWindow ? 'target="_blank"'  : '';
                        $hidden = (isset($instance['hideButton']) && $instance['hideButton'] == TRUE) ? 'style="display:none"' : '';
                        $r .= '<form '. $blank .' method="POST" action="'. $this->cta->link .'">';
                        $r .= '<span id="'. $bid .'" '. $hidden .'>';
                        $r .= '<input type="submit" value="'. $this->cta->linkText .'" />';
                        $r .= '</span>';
                        $r .= '</form>';
                        if($this->cta->isImage && (!empty($this->cta->image) || !empty($this->cta->imageHover))){
                            $r .= Attachment::generateCss($this->cta->image, $this->cta->imageHover, $bid, 'full');
                        }
                    } elseif($this->cta->isClasslist){
                        //$r .= print_r($this->cta);
                    }
                    $r .= isset($args['after_widget']) ? $args['after_widget'] : '';
                    if(isset($instance['hideButton']) && $instance['hideButton'] == TRUE){
                        $r .= WidgetForm::getModalFullScrollJavascript($bid, (int)$instance['hideButtonTIME']);
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
