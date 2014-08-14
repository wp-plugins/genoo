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

use Genoo\Utils\Strings;

class PostType
{

    /** @var string */
    public $postType;
    /** @var array */
    var $args;


    /**
     * Constructor
     *
     * @param $postType
     * @param array $args
     */


    function __construct($postType, array $args = array())
    {
        // webalize string, and truncate, max lenght according to specs 20 chars
        $this->postType = $this->purify($postType);
        $this->args = $this->mergeDefaults($postType, $args);
        $this->register();
    }


    /**
     * Purify post-type name
     *
     * @param $postType
     * @return string
     */

    public static function purify($postType){ return Strings::truncate(Strings::webalize($postType),20); }


    /**
     * Adds supports
     *
     * @param $feature
     */

    public function supports($feature){ $this->args['supports'][] = $feature; }


    /**
     * Sets name (really? :D)
     *
     * @param $name
     */

    public function setName($name){
        $this->args['label'] = $name;
        $this->args['labels']['name'] = $name;
    }


    /**
     * Set publicly visible
     *
     * @param $public
     */

    public function setPublic($public){ $this->args['public'] = $public; }


    /**
     * Can export?
     *
     * @param $export
     */

    public function setExport($export){ $this->args['can_export'] = $export; }


    /**
     * Set capabilities
     *
     * @param array $caps
     */

    public function setCapabilities(array $caps){ $this->args['capabilities'] = $caps; }


    /**
     * Has archive
     *
     * @param $archive
     */

    public function hasArchive($archive){ $this->args['has_archive'] = $archive; }


    /**
     * Merge with default
     *
     * @param array $args
     * @return array
     */

    private function mergeDefaults($postType, array $args = array()){
        $upperSingular = ucwords($postType);
        $upperPlural = ucwords($postType);
        $defaults = array(
            'label' =>  $postType,
            'labels' => array(
                'name' => $upperPlural,
                'singular_name' => $upperSingular,
                'add_new' => 'Add New',
                'add_new_item' => 'Add New '.$upperSingular,
                'edit_item' => 'Edit '.$upperSingular,
                'new_item' => 'New '.$upperSingular,
                'view_item' => 'View '.$upperSingular,
                'search_items' => 'Search '.$upperPlural,
                'not_found' =>  'No '.$upperPlural.' found',
                'not_found_in_trash' => 'No '.$upperPlural.' found in Trash',
                'parent_item_colon' => '',
                'menu_name' => $upperPlural),
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_admin_bar' => true,
            'public' => true,
            'menu_position' => 70,
            'supports' => array('title'),
        );
        return array_merge($defaults, $args);
    }


    /**
     * Regisgter's post type
     *
     * @return mixed
     */

    public function register(){ register_post_type($this->postType, $this->args); }


    /**
     * Unregister any post type
     *
     * @param $postType
     * @return bool
     */

    public static function unRegister($postType){
        global $wp_post_types;
        if (isset($wp_post_types[$postType])){
            unset($wp_post_types[$postType]);
            return true;
        }
        return false;
    }


    /**
     * Manage columns
     *
     * @param $postType
     */

    public static function columns($postType){
        $postType = self::purify($postType);
        add_filter('manage_edit-'. $postType .'_columns', function($columns){
            return array_merge(
                array(
                'cb' => '<input type="checkbox" />',
                'title' => 'Title',
            ), AdminUI::getColumns());
        });
    }


    /**
     * Simple helper
     *
     * @param $postType
     */

    public static function columnsContent($postType){
        $postType = self::purify($postType);
        add_action('manage_'. $postType .'_posts_custom_column', function($column, $post_id){
            global $post;
            $keys = AdminUI::getColumnsContent();
            switch($column){
                default:
                    if(array_key_exists($column, $keys)){
                        echo get_post_meta($post->ID, $keys[$column], true);
                    }
                    break;
            }
        }, 10, 2);
    }
}