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

use Genoo\Utils\Strings,
    Genoo\Wordpress\Filter,
    Genoo\Wordpress\Action;


class MetaboxCTA extends Metabox
{
    /** @var */
    var $title;
    /** @var string  */
    var $id;
    /** @var */
    var $postType;
    /** @var string  */
    var $nonceKey;
    /** @var  */
    var $ctas;


    /**
     * Constructor
     *
     * @param $title
     * @param $postType
     */

    function __construct($title, $postType, $fields, $ctas)
    {
        // assign
        $this->title = $title;
        $this->id = 'repeatable_' . Strings::webalize($title);
        $this->postType = $postType;
        $this->nonceKey =  $this->id . 'Nonce';
        $this->fields = $fields;
        $this->fieldsSanatized = $this->sanatizeFields($this->fields);
        $this->ctas = $ctas;
        Action::add('add_meta_boxes',    array($this, 'register'));
        Action::add('save_post',         array($this, 'save'));
        Filter::add('admin_head',        array($this, 'adminJs'));
    }


    /**
     * Sanatized Fields
     *
     * @param $fields
     * @return array
     */

    private function sanatizeFields($fields)
    {
        $r = array();
        foreach($fields as $field){
            $r[] = self::webalize($field);
        }
        return $r;
    }


    /**
     * Admin js
     */

    public function adminJs()
    {
        global $parent_file;
        global $post_type;
        // If the current post type doesn't match, return, ie. end execution here
        if ((is_array($this->postType) && in_array($post_type, $this->postType)) || (is_string($this->postType) && $this->postType == $post_type)){
            if (Strings::contains($parent_file, 'edit.php') || Strings::contains($parent_file, 'post-new.php')){
                ?>
                <script type="text/javascript">
                    jQuery(function($){
                        jQuery('#<?php echo $this->id; ?> .add-row').on('click', function(e){
                            e.preventDefault();
                            e.returnValue = null;
                            var row = jQuery('#<?php echo $this->id; ?> .empty-row.screen-reader-text').clone(true);
                            row.removeClass('empty-row screen-reader-text');
                            row.insertBefore('#<?php echo $this->id; ?> tbody>tr:last');
                            row.find('select').removeClass('empty');
                        });
                        jQuery('#<?php echo $this->id; ?> .remove-row').on('click', function(e){
                            e.preventDefault();
                            e.returnValue = null;
                            jQuery(this).parents('tr').remove();
                        });
                    });
                </script>
            <?php
            }
        }
    }


    /**
     * Render
     *
     * @param $post
     */

    public function render($post)
    {
        $savedFields = get_post_meta($post->ID, $this->id, true);
        $visibleFields = get_post_meta($post->ID, 'enable_cta_for_this_post_repeat', true);
        $fieldSidebars = Sidebars::getSidebars();
        $fieldCTAs = $this->ctas;
        ?>
        <div class="genooMetabox">
            <div class="themeMetaboxRow" id="themeMetaboxRowenable_cta_for_this_post">
                <label for="enable_cta_for_this_post_repeat">Enable Dynamic CTA for this post</label>
                <input id="enable_cta_for_this_post_repeat" name="enable_cta_for_this_post_repeat" value="true" type="checkbox" <?php if($visibleFields){ echo 'checked'; } ?>><div class="clear"></div>
            </div>
            <div id="themeMetaboxRowselect_cta_repeat">
                <?php if(count($fieldSidebars) < 2){ ?>
                <strong><?php echo __('Your WordPress installation doesn\'t seem to have any registered sidebars.', 'genoo'); ?></strong>
                <?php } elseif (count($fieldCTAs) < 2){ ?>
                <strong><?php echo __('You don\'t have any CTA\'s created.', 'genoo'); ?></strong>
                <?php } else { ?>
                <table id="<?php echo $this->id; ?>" width="100%">
                    <thead>
                    <tr>
                        <th align="left">CTA</th>
                        <th align="left">Sidebar</th>
                        <th align="left">Position</th>
                        <th width="8%"></th>
                    </tr>
                    </thead>
                    <tbody class="genooMetaboxRow genooMetaboxRepeatable">
                    <?php
                    if ($savedFields){
                        foreach ($savedFields as $field){ ?>
                        <tr>
                            <td><?php echo self::select($this->id . '[cta][]', $fieldCTAs, isset($field['cta']) ? $field['cta'] : false); ?></td>
                            <td><?php echo self::select($this->id . '[sidebar][]', $fieldSidebars, isset($field['cta']) ? $field['sidebar'] : false); ?></td>
                            <td><?php echo self::select($this->id . '[position][]', self::selectPosition(), !empty($field['position']) ? $field['position'] : false); ?></td>
                            <td><a class="button remove-row" href="#">Remove</a></td>
                        </tr>
                    <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td><?php echo self::select($this->id . '[cta][]', $fieldCTAs); ?></td>
                            <td><?php echo self::select($this->id . '[sidebar][]', $fieldSidebars); ?></td>
                            <td><?php echo self::select($this->id . '[position][]', self::selectPosition()); ?></td>
                            <td><a class="button remove-row" href="#">Remove</a></td>
                        </tr>
                    <?php } ?>
                    <tr class="empty-row screen-reader-text">
                        <td><?php echo self::select($this->id . '[cta][]', $fieldCTAs, null, 'empty'); ?></td>
                        <td><?php echo self::select($this->id . '[sidebar][]', $fieldSidebars, null, 'empty'); ?></td>
                        <td><?php echo self::select($this->id . '[position][]', self::selectPosition(), null, 'empty'); ?></td>
                        <td><a class="button remove-row" href="#">Remove</a></td>
                    </tr>
                    </tbody>
                    <tfoot>
                    <tr><td colspan="4"><a class="add-row button button-primary button-large" href="#">Add more</a></td></tr>
                    </tfoot>
                </table>
                <?php } ?>
            </div>
        </div>
        <?php
    }


    /**
     * Save
     *
     * @param $post_id
     * @return mixed|void
     */

    public function save($post_id)
    {
        if (!current_user_can('edit_post', $post_id)) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        $r = array();
        if(isset($_POST[$this->id]) && is_array($_POST[$this->id])){
            foreach($_POST[$this->id] as $key => $value){
                $current = $key;
                if(is_array($value)){
                    foreach($value as $row => $field){
                        if(!empty($field)){
                            $r[$row][$current] = $field;
                        }
                    }
                }
            }
        }
        $r = self::go($r);
        delete_post_meta($post_id, 'enable_cta_for_this_post_repeat');
        delete_post_meta($post_id, $this->id);
        if(isset($_POST['enable_cta_for_this_post_repeat']) && $_POST['enable_cta_for_this_post_repeat'] == 'true') {
            update_post_meta($post_id, 'enable_cta_for_this_post_repeat', true);
            update_post_meta($post_id, $this->id, $r);
        }
        // After saving dynamic CTAs, it would be nice to remove any stagging ones
        Widgets::refreshDynamic();
    }


    /**
     * Webialize
     *
     * @param $field
     * @return mixed
     */

    public static function webalize($field){ return str_replace('-', '', Strings::webalize($field)); }


    /**
     * Select
     *
     * @param $name
     * @param $array
     * @param null $selected
     * @param null $class
     * @return string
     */

    public static function select($name, $array, $selected = null, $class = null)
    {
        $r = '';
        if($array){
            $r .= '<select class="'. $class .'" name="'. $name .'">';
                foreach($array as $val => $title){
                    $r .= '<option value="' . $val . '"';
                    $r .= $selected == $val ? ' selected' : null;
                    $r .= '>';
                    $r .= $title;
                    $r .= '</option>';
                }
            $r .= '</select>';
        }
        return $r;
    }


    /**
     * Select Position
     *
     * @return array
     */

    public static function selectPosition()
    {
        $r = array();
        $r[1] =  __('First', 'genoo');
            for($i = 2; $i < 11; ++$i){
                $r[$i] = $i;
            }
        $r[-1] = __('Last', 'genoo');
        return $r;
    }


    /**
     * Go through array, and reorder
     *
     * @param array $arr
     * @return array
     */

    public static function go(array $arr)
    {
        foreach($arr as $key => $value){
            if(empty($value['cta']) || empty($value['sidebar'])){
                unset($arr[$key]);
            }
        }
        return array_values($arr);
    }
}