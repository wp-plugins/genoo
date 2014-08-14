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

class Metabox
{

    /** @var string */
    var $id;
    /** @var string */
    var $title;
    /** @var  */
    var $callback;
    /** @var string|array */
    var $postType;
    /** @var string */
    var $context = 'normal';
    /** @var string */
    var $priority = 'high';
    /** @var array */
    var $fields = array();
    /** @var string */
    var $nonceKey = '';

    /**
     * Constructor
     *
     * @param $title
     * @param $postType
     */

    function __construct($title, $postType, $fields)
    {
        // assign
        $this->title = $title;
        $this->id = Strings::webalize($title);
        $this->postType = $postType;
        $this->fields = $fields;
        $this->nonceKey = GENOO_KEY . $this->id . 'Nonce';
        add_action('add_meta_boxes',    array($this, 'register'));
        add_action('save_post',         array($this, 'save'));
        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'));
    }


    /**
     * Enqueue scripts and styles
     */

    public function adminEnqueueScripts()
    {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('thickbox');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('jquery');
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_enqueue_media();
    }


    /**
     * Register metaboxes
     */

    public function register(){
        if(is_array($this->postType)){
            foreach($this->postType as $postType){
                add_meta_box($this->id, $this->title, array($this, 'render'), $postType, $this->context, $this->priority);
            }
        } elseif (is_string($this->postType)) {
            add_meta_box($this->id, $this->title, array($this, 'render'), $this->postType, $this->context, $this->priority);
        }
    }


    /**
     * Save metabox
     *
     * @param $post_id
     * @return mixed
     */

    public function save($post_id){
        // check if our nonce is set.
        if (!isset($_POST[$this->nonceKey])){ return $post_id; }
        // nonce key
        $nonce = $_POST[$this->nonceKey];
        // verify that the nonce is valid.
        if (!wp_verify_nonce($nonce, $this->id)){ return $post_id; }
        // if this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){ return $post_id; }
        // check the user's permissions.
        if (!current_user_can('edit_post', $post_id)){ return $post_id; }
        // Update the meta fields
        if(is_array($this->fields) && !empty($this->fields)){
            foreach($this->fields as $field){
                $fieldId = str_replace('-', '_', Strings::lower(Strings::webalize($field['label'])));
                if(!empty($_POST[$fieldId])){
                    update_post_meta($post_id, $fieldId, sanitize_text_field($_POST[$fieldId]));
                } elseif(empty($_POST[$fieldId])) {
                    delete_post_meta($post_id, $fieldId);
                }
            }
        }
    }


    /**
     * Form renderer
     *
     * @param $post
     */

    public function render($post){
        // set wp_nonce_field
        wp_nonce_field($this->id, $this->nonceKey);
        $metaboxForm = '<div class="themeMetabox">';
        $metaboxClear = '<div class="clear"></div>';
        // go thru fields
        if(is_array($this->fields) && !empty($this->fields)){
            foreach($this->fields as $field){
                $fieldId = isset($field['id']) ? $field['id'] : str_replace('-', '_', Strings::lower(Strings::webalize($field['label'])));
                $fieldRow = '<div class="themeMetaboxRow" id="themeMetaboxRow'. $fieldId .'" >';
                $fieldValue = get_post_meta($post->ID, $fieldId, true);
                $fieldLabel = '<label for="' . $fieldId . '">' . $field['label'] . '</label>';
                $fieldOptions = isset($field['options']) ? $field['options'] : array();
                $fieldAtts = '';
                if(isset($field['atts']) && is_array($field['atts'])){ foreach($field['atts'] as $key => $value){ $fieldAtts .= ' '. $key .'="'. $value .'" '; } }
                switch($field['type']){
                    case 'text':
                    case 'number':
                    case 'tel':
                    case 'email':
                        $fieldInput = '<input id="'. $fieldId .'" name="'. $fieldId .'" type="' . $field['type'] . '" value="'. $fieldValue .'" '. $fieldAtts .' />';
                        break;
                    case 'html':
                        $fieldLabel = '<label for="' . $fieldId . '">&nbsp;</label>';
                        $fieldInput = '<div id="' . $fieldId . '">' . $field['label'] . '</div>';
                        break;
                    case 'select':
                        $fieldInput = '<select id="'. $fieldId .'" name="'. $fieldId .'" '. $fieldAtts .'>';
                        if(!empty($fieldOptions) && is_array($fieldOptions)){
                            foreach($fieldOptions as $key => $option){
                                if($key == $fieldValue){ $selected = 'selected'; } else { $selected = ''; }
                                $fieldInput .= '<option value="' . $key . '" '. $selected .'>'. $option .'</option>';
                            }
                        }
                        $fieldInput .= '</select>';
                        break;
                    case 'textarea':
                        $fieldInput = '<textarea id="'. $fieldId .'" name="'. $fieldId .'" '. $fieldAtts .'>' . $fieldValue . '</textarea>';
                        break;
                    case 'checkbox':
                        if(true == $fieldValue){ $checked = 'checked'; } else { $checked = ''; }
                        $fieldInput = '<input '. $fieldAtts .' id="'. $fieldId .'" name="'. $fieldId .'" value="true" type="'. $field['type'] .'" '. $checked .' />';
                        break;
                    case 'radio':
                        $fieldLabel = '<span class="label">'. $field['label'] .'</span>';
                        $fieldInput = '<span class="radio">';
                        if(!empty($fieldOptions) && is_array($fieldOptions)){
                            foreach($fieldOptions as $key => $option){
                                if(Strings::webalize($option) == $fieldValue){ $selected = 'checked'; } else { $selected = ''; }
                                $fieldInputRadio = '<input type="radio" name="'. $fieldId .'" value="' . Strings::webalize($option) . '" '. $selected .' />';
                                $fieldInput .= '<label>' . $fieldInputRadio . ' ' . $option . '</label>';
                            }
                        }
                        $fieldInput .= '</span>';
                        break;
                    case 'image-select':
                        $fieldTarget =  $fieldId . 'Target';
                        $fieldLabelButton = 'Select Image';
                        $fieldLabel = '<label>'. $field['label'] .':</label>'
                            . '<div class="genooUploadSelect">'
                            . '<div class="genooWidgetImage" id="'. $fieldTarget .'">'
                            . wp_get_attachment_image($fieldValue, 'medium', false)
                            . '</div>';
                        $fieldInput = '<input type="hidden" name="'. $fieldId .'" id="'. $fieldId .'" value="'. $fieldValue .'" />';
                        $fieldInput .= '<a href="#" onclick="Modal.open(event,this);"'
                            . 'id="'. $fieldId . 'Btn' .'"'
                            . 'data-current-id="'. $fieldValue .'"'
                            . 'data-title="'. $fieldLabelButton .'"'
                            . 'data-update-text="'. $fieldLabelButton .'"'
                            . 'data-target="'. $fieldTarget .'"'
                            . 'data-target-input="'. $fieldId .'"'
                            . 'class="button">'. $fieldLabelButton .'</a>';
                        $fieldInput .= ' | ';
                        $fieldInput .= '<a href="#" onclick="Modal.emptyImage(event,'
                            . '\''. $fieldTarget . '\', '
                            . '\''. $fieldId . '\', '
                            . '\''. $fieldId . 'Btn' . '\');'
                            . '">'. __('Remove Image', 'genoo') .'</a>';
                        $fieldInput .= '<div class="clear"></div></div>';
                        break;
                }
                // add elements to a row
                $fieldRow .= $fieldLabel;
                $fieldRow .= $fieldInput;
                $fieldRow .= $metaboxClear;
                // add row to metabox form
                $metaboxForm .= $fieldRow . '</div>';
            }
        }
        // render, well, echo
        echo $metaboxForm . '</div>';
    }
}