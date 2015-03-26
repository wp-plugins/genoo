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

use Genoo\RepositorySettings,
    Genoo\RepositoryForms,
    Genoo\Cache,
    Genoo\Api,
    Genoo\CTA,
    Genoo\Utils\Strings,
    Genoo\ModalWindow,
    Genoo\Wordpress\Attachment;


/**
 * Genoo Lumens Class List
 */

class WidgetLumen extends \WP_Widget
{

    /**
     * Constructor registers widget in WordPress
     */

    function __construct($constructParent = true)
    {
        if($constructParent){
            parent::__construct(
                'genoolumen',
                'Genoo Class List',
                array('description' => __('Genoo widget class list.', 'genoo'))
            );
        }
    }


    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     * @param array $echo
     */

    public function widget($args, $instance, $echo = true)
    {
        try {
            $repositorySettings = new RepositorySettings();
            $api =  new Api($repositorySettings);
            $repositoryLumens = new RepositoryLumens(new Cache(GENOO_CACHE), $api);
            $formId = !empty($instance['lumen']) && is_numeric($instance['lumen']) ? $instance['lumen'] : null;
            $formTitle = !empty($instance['title']) ? $instance['title'] : __('Classlist', 'genoo');
            $r = '';
            if(!is_null($formId)){
                $r .= $args['before_widget'];
                $r .= '<div class="themeResetDefault">';
                $r .= '<div class="genooTitle">' . $args['before_title'] . $formTitle . $args['after_title'] . '</div>';
                if(isset($instance['displayDesc']) && $instance['displayDesc'] == true){
                    $r .= '<div class="genooGuts"><p class="genooPadding">' . $instance['desc'] . '</p></div>';
                }
                $r .= '<div class="clear"></div>';
                $r .= '<div class="genooGuts">';
                $r .= $repositoryLumens->getLumen($formId);
                $r .= '</div>';
                $r .= '<div class="clear"></div>';
                $r .= '</div>';
                $r .= $args['after_widget'];
            }
        } catch (\Exception $e){
            $r .= '<span class="error">';
            $r .= $e->getMessage();
            $r .= '</span>';
        }
        if($echo){
            echo $r;
            return true;
        }
        return $r;
    }


    /**
     * Get HTML
     *
     * @param $args
     * @param $instance
     * @return string
     */

    public function getHtml($args, $instance)
    {
        return $this->widget($args, $instance, false);
    }


    /**
     * Widget settings form
     *
     * @param $instance
     */

    public function form($instance)
    {
        try {
            // prep stuff
            $repoSettings = new RepositorySettings();
            $repoLumens = new RepositoryLumens(new Cache(GENOO_CACHE), new Api($repoSettings));
            $widgetLumens = $repoLumens->getLumensTable();
            $instance = wp_parse_args((array) $instance, array('title' => __('Classlist', 'genoo'), 'lumen' => 0));
            $widgetTitle = !empty($instance['title']) ? strip_tags($instance['title']) : __('Classlist', 'genoo');
            $widgetLumen = strip_tags($instance['lumen']);
            // widget form
            echo '<div class="genooParagraph">';
            echo '<label for="'. $this->get_field_id('title') .'">' . __('Genoo form title:', 'genoo') . ' </label>';
            echo '<input class="widefat" id="'. $this->get_field_id('title') .'" name="'. $this->get_field_name('title') .'" value="'. esc_attr($widgetTitle) .'" type="text" />';
            echo '</div>';
            echo '<div class="genooParagraph">';
            echo '<label for="'. $this->get_field_id('lumen') .'">' . __('Classlist:', 'genoo') . ' </label>';
            echo '<select name="'. $this->get_field_name('lumen') .'" id="'. $this->get_field_id('lumen') .'">';
            foreach($widgetLumens as $value){
                echo '<option value="'. $value['id'] .'" '. selected($value['id'], $widgetLumen, false) .'>' . $value['name'] . '</option>';
            }
            echo '</select>';
            echo '</div>';
        } catch (\Exception $e){
            echo '<span class="error">';
            echo $e->getMessage();
            echo '</span>';
        }
    }
}