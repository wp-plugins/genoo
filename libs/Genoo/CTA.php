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

use Genoo\Wordpress\Post;
use Genoo\RepositorySettings;


class CTA
{
    /** @var \Genoo\RepositorySettings */
    private $repositarySettings;
    /** @var */
    public $post;
    /** @var \WP_Post */
    public $postObject;
    /** @var array post types */
    public $postTypes;
    /** @var bool */
    public $has = false;
    /** @var bool */
    public $hasMultiple = false;
    /** @var bool */
    public $isForm = false;
    /** @var null */
    public $formId = null;
    /** @var null */
    public $desc = null;
    /** @var bool */
    public $displayTitle = false;
    /** @var bool */
    public $displayDesc = false;
    /** @var null */
    public $title = null;
    /** @var null */
    public $formTheme = null;
    /** @var bool */
    public $isLink = false;
    /** @var bool */
    public $isNewWindow = false;
    /** @var bool */
    public $isImage = false;
    /** @var bool */
    public $isHtml = false;
    /** @var bool */
    public $isClasslist = false;
    /** @var null */
    public $linkText = null;
    /** @var null */
    public $link = null;
    /** @var null */
    public $image = null;
    /** @var null */
    public $imageHover = null;
    /** @var null */
    public $messageSuccess = null;
    /** @var null */
    public $messageError = null;
    /** @var null  */
    public $classList = null;
    /** @var null Position should be only set for dynamic CTAs */
    public $position = null;
    /** @var null Sidebar should be only set for dynamic CTAs */
    public $sidebar = null;
    /** @var null */
    public $popup = null;
    /** @var bool */
    public $isPopOver = false;
    /** @var int */
    public $popOverTime = 0;
    /** @var bool */
    public $popOverHide = false;


    /**
     * Constructor
     *
     * @param null $post
     */

    public function __construct($post = null)
    {
        // Don't override
        if($post != false){
            $this->post = Post::set($post);
            $this->repositarySettings = new RepositorySettings();
            $this->postTypes = $this->repositarySettings->getCTAPostTypes();
            $this->postObject = $this->post->getPost();
            if($this->has()){
                $this->resolve();
            }
        }
        return $this;
    }


    /**
     * Has CTA?
     *
     * @return bool
     */

    public function has()
    {
        $meta = $this->post->getMeta('enable_cta_for_this_post');
        $this->has = false;
        if((!empty($this->postTypes) && (is_array($this->postTypes))) && ((in_array($this->postObject->post_type, $this->postTypes)) && (!empty($meta)))){
            $p = $this->post->getMeta('select_cta');
            if(Post::exists($p)){
                $this->post = Post::set($p);
                $this->has = true;
                return true;
            }
        }
        return false;
    }


    /**
     * Set CTA post
     *
     * @param $postIs
     * @return $this
     */

    public function setCta($postId)
    {
        $this->post = Post::set($postId);
        $this->has = true;
        $this->resolve();
        return $this;
    }


    /**
     * Resolves current CTA
     */

    private function resolve()
    {
        $a = $this->post->getMeta('cta_type'); // link form
        $b = $this->post->getMeta('button_url');
        $c = $this->post->getMeta('open_in_new_window');
        $d = $this->post->getMeta('button_type'); // html image
        $e = $this->post->getMeta('button_text');
        $f = $this->post->getMeta('button_image');
        $g = $this->post->getMeta('button_hover_image');
        $h = $this->post->getMeta('form'); // form id
        $i = $this->post->getMeta('form_theme'); // form id
        $j = $this->post->getMeta('description'); // desc
        $z = $this->post->getMeta('display_cta_s');
        $a1 = $this->post->getMeta('class_list');
        // A
        $k = ($z == '0' || empty($z)) ? false : true;
        $this->isPopOver = $this->post->getMeta('enable_pop_up_to_open_automatically') == 0 ? FALSE : TRUE;
        $this->popOverTime = $this->post->getMeta('number_of_seconds_to_open_the_pop_up_after') ? (int)$this->post->getMeta('number_of_seconds_to_open_the_pop_up_after') : 0;
        $this->popOverHide = $this->post->getMeta('hide_pop_up_button') == 0 ? TRUE : FALSE;
        $this->messageSuccess = $this->post->getMeta('form_success_message');
        $this->messageError = $this->post->getMeta('form_error_message');
        $this->isForm = $a == 'form' ? true : false;
        $this->formId = $h;
        $this->formTheme = $i;
        $this->isClasslist = $a == 'class' ? true : false;
        $this->isLink = $this->isForm ? false : $this->isClasslist ? false : true;
        $this->isNewWindow = ($c == 'true') ? true : false;
        $this->isImage = $d == 'image' ? true : false;
        $this->isHtml = $this->isImage ? false : true;
        $this->classList = $this->isClasslist ? $a1 : null;
        $this->linkText = $e;
        $this->link = $b;
        $this->image = $f;
        $this->imageHover = $g;
        $this->desc = $j;
        $this->title = $this->post->getTitle();
        $this->displayTitle = ($k == true && ($z == 'titledesc' || $z == 'title')) ? true : false;
        $this->displayDesc = ($k == true && ($z == 'titledesc' || $z == 'desc')) ? true : false;
        $this->popup = $this->post->getMeta('formpop');
    }

    /**
     * @param $post_id
     * @return bool
     */
    public static function ctaHasPopOver($post_id)
    {
        if(Post::exists($post_id)){
            $popOver = get_post_meta($post_id, 'enable_pop_over_to_open_automatically', TRUE);
            return $popOver == 0 ? FALSE : TRUE;
        }
        return FALSE;
    }

    /**
     * @return bool
     */
    public static function ctaHasHidePopOver($post_id)
    {
        if(Post::exists($post_id)){
            $popOver = get_post_meta($post_id, 'hide_pop_up_button', TRUE);
            return $popOver == 0 ? TRUE : FALSE;
        }
        return FALSE;
    }

    /**
     * @param $post_id
     * @return bool
     */
    public static function ctaPopOverGet($post_id)
    {
        if(Post::exists($post_id)){
            $pop = get_post_meta($post_id, 'pop_over_cta_id', TRUE);
            if(is_numeric($pop)){
                return $pop;
            }
            return FALSE;
        }
        return FALSE;
    }

    /**
     * @return bool
     */
    public static function ctaGetPopOverTime($post_id)
    {
        if(Post::exists($post_id)){
            $popOver = (int)get_post_meta($post_id, 'number_of_seconds_to_open_the_pop_up_after', TRUE);
            return $popOver;
        }
        return FALSE;
    }

    /**
     * @return array
     */
    public static function getFooterPopOvers()
    {
        // Prep
        global $post;
        $r = array();
        $setPopOver = FALSE;
        $repositorySettings = new RepositorySettings();
        if(isset($post) && $post instanceof \WP_Post){
            if(CTA::ctaHasPopOver($post->ID) && $cta_id = CTA::ctaPopOverGet($post->ID)){
                // get CTA
                $ctaTime = CTA::ctaGetPopOverTime($post->ID);
                $cta = new WidgetCTA();
                $cta->setThroughShortcode('popover', $cta_id);
                // PUt in array if it is form CTA
                if($cta->cta->isForm){
                    $r[$cta->id] = new \stdClass();
                    $r[$cta->id]->widget = $cta;
                    $r[$cta->id]->instance = $cta->getInnerInstance();
                    // Set up PopOver
                    IF($setPopOver){
                        $r[$cta->id]->widget->cta->isPopOver = TRUE;
                        $r[$cta->id]->widget->cta->popOverTime = $ctaTime;
                        $r[$cta->id]->widget->cta->popOverHide = TRUE;
                        $r[$cta->id]->instance['modal'] = TRUE;
                        $r[$cta->id]->instance['isPopOver'] = TRUE;
                        $r[$cta->id]->instance['isPopOverInject'] = TRUE;
                        $r[$cta->id]->instance['popOverHide'] = TRUE;
                        $r[$cta->id]->instance['popOverTime'] = $ctaTime;
                    }
                }
                return $r;
            }
        }
        return $r;
    }
}