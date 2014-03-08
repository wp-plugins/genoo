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
    Genoo\Api;


class Widgets
{

    /**
     * Register widges
     */

    public static function register()
    {
        add_action('widgets_init', function(){
            register_widget('\Genoo\WidgetForm');
            register_widget('\Genoo\WidgetLumen');
        });
    }
}


/**
 * Geenoo Form
 */

class WidgetForm extends \WP_Widget
{

    /**
     * Constructor registers widget in wordpress
     */

    function __construct()
    {
        parent::__construct('genooForm', 'Genoo', array('description' => __('Genoo widget form.', 'genoo')));
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
        $repositorySettings = new RepositorySettings();
        $repositoryForms = new RepositoryForms(new Cache(GENOO_CACHE), new Api($repositorySettings));
        $formId = !empty($instance['form']) && is_numeric($instance['form']) ? $instance['form'] : null;
        $formIdFinal = is_null($formId) ? $repositorySettings->getActiveForm() : $formId;
        $formTitle = !empty($instance['title']) ? $instance['title'] : __('Subscribe', 'genoo');
        $formClass = !empty($instance['theme']) ? $instance['theme'] : 'themeDefault';
        if(!empty($formIdFinal)){
            echo $args['before_widget'];
                echo '<div class="genooForm themeResetDefault '. $formClass .'">';
                    echo '<div class="genooTitle">' . $args['before_title'] . $formTitle . $args['after_title'] . '</div>';
                    echo '<div class="clear"></div>';
                    echo '<div class="genooGuts">';
                        echo $repositoryForms->getForm($formIdFinal);
                    echo '</div>';
                    echo '<div class="clear"></div>';
                echo '</div>';
            echo $args['after_widget'];
        }
    }


    /**
     * Widget settings form
     *
     * @param $instance
     */

    function form($instance)
    {
        // prep stuff
        $repoSettings = new RepositorySettings();
        $repoForms = new RepositoryForms(new Cache(GENOO_CACHE), new Api($repoSettings));
        $widgetThemes = $repoSettings->getSettingsThemes();
        $widgetForms = array_merge(array(array('id' => 0, 'name' => __('Default subscription form', 'genoo'))), $repoForms->getFormsTable());
        $instance = wp_parse_args((array) $instance, array('title' => __('Subscribe', 'genoo'), 'form' => 0, 'theme' => 0));
        $widgetTitle = !empty($instance['title']) ? strip_tags($instance['title']) : __('Subscribe', 'genoo');
        $widgetForm = strip_tags($instance['form']);
        $widgetTheme = strip_tags($instance['theme']);
        echo '<p>';
            echo '<label for="'. $this->get_field_id('title') .'">' . __('Genoo form title:', 'genoo') . ' </label>';
            echo '<input class="widefat" id="'. $this->get_field_id('title') .'" name="'. $this->get_field_name('title') .'" value="'. esc_attr($widgetTitle) .'" type="text" />';
        echo '</p>';
        echo '<p>';
            echo '<label for="'. $this->get_field_id('form') .'">' . __('Form:', 'genoo') . ' </label>';
            echo '<select name="'. $this->get_field_name('form') .'" id="'. $this->get_field_id('form') .'">';
                foreach($widgetForms as $value){
                    echo '<option value="'. $value['id'] .'" '. selected($value['id'], $widgetForm, false) .'>' . $value['name'] . '</option>';
                }
            echo '</select>';
        echo '</p>';
        echo '<p>';
            echo '<label for="'. $this->get_field_id('theme') .'">' . __('Form theme:', 'genoo') . ' </label>';
            echo '<select name="'. $this->get_field_name('theme') .'" id="'. $this->get_field_id('theme') .'">';
                foreach($widgetThemes as $key => $value){
                    echo '<option value="'. $key .'" '. selected($key, $widgetTheme, false) .'>' . $value . '</option>';
                }
            echo '</select>';
        echo '</p>';
    }
}


/**
 * Genoo Lumens Class List
 */

class WidgetLumen extends \WP_Widget
{

    /**
     * Constructor registers widget in wordpress
     */

    function __construct()
    {
        parent::__construct('genooLumen', 'Genoo Class List', array('description' => __('Genoo widget class list.', 'genoo')));
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
        $repositorySettings = new RepositorySettings();
        $api =  new Api($repositorySettings);
        $repositoryLumens = new RepositoryLumens(new Cache(GENOO_CACHE), $api);
        $formId = !empty($instance['lumen']) && is_numeric($instance['lumen']) ? $instance['lumen'] : null;
        $formTitle = !empty($instance['title']) ? $instance['title'] : __('Classlist', 'genoo');
        if(!is_null($formId)){
            echo $args['before_widget'];
                echo '<div class="themeResetDefault">';
                    echo '<div class="genooTitle">' . $args['before_title'] . $formTitle . $args['after_title'] . '</div>';
                    echo '<div class="clear"></div>';
                    echo '<div class="genooGuts">';
                        echo $repositoryLumens->getLumen($formId);
                    echo '</div>';
                    echo '<div class="clear"></div>';
                echo '</div>';
            echo $args['after_widget'];
        }
    }


    /**
     * Widget settings form
     *
     * @param $instance
     */

    function form($instance)
    {
        // prep stuff
        $repoSettings = new RepositorySettings();
        $repoLumens = new RepositoryLumens(new Cache(GENOO_CACHE), new Api($repoSettings));
        $widgetLumens = $repoLumens->getLumensTable();
        $instance = wp_parse_args((array) $instance, array('title' => __('Classlist', 'genoo'), 'lumen' => 0));
        $widgetTitle = !empty($instance['title']) ? strip_tags($instance['title']) : __('Classlist', 'genoo');
        $widgetLumen = strip_tags($instance['lumen']);
        echo '<p>';
            echo '<label for="'. $this->get_field_id('title') .'">' . __('Genoo form title:', 'genoo') . ' </label>';
            echo '<input class="widefat" id="'. $this->get_field_id('title') .'" name="'. $this->get_field_name('title') .'" value="'. esc_attr($widgetTitle) .'" type="text" />';
        echo '</p>';
        echo '<p>';
        echo '<label for="'. $this->get_field_id('lumen') .'">' . __('Classlist:', 'genoo') . ' </label>';
        echo '<select name="'. $this->get_field_name('lumen') .'" id="'. $this->get_field_id('lumen') .'">';
            foreach($widgetLumens as $value){
                echo '<option value="'. $value['id'] .'" '. selected($value['id'], $widgetLumen, false) .'>' . $value['name'] . '</option>';
            }
        echo '</select>';
        echo '</p>';
    }
}
