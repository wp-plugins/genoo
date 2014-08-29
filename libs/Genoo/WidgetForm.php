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
 * Geenoo Form
 */

class WidgetForm extends \WP_Widget
{

    /**
     * Constructor registers widget in WordPress
     *
     * @param bool $constructParent
     */

    function __construct($constructParent = true)
    {
        if($constructParent){
            parent::__construct('genooForm', 'Genoo', array('description' => __('Add Genoo forms to your pages.', 'genoo')));
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

    public function widget($args, $instance){ echo $this->getHtml($args, $instance); }


    /**
     * Get html
     *
     * @param $args
     * @param $instance
     * @return string
     */

    public function getHtml($args, $instance)
    {
        global $is_macIE, $is_winIE, $is_IE;

        // default
        $default = array(
            'before_title' => '',
            'before_widget' => '',
            'after_title' => '',
            'after_widget' => '',
            'modal' => '',
            'title' => '',
        );
        $args = array_merge($default, $args);
        $html = '';

        // prep
        $formTitle = !empty($instance['title']) ? $instance['title'] : __('Subscribe', 'genoo');
        $formClass = !empty($instance['theme']) ? $instance['theme'] : 'themeDefault';
        $formModal = isset($instance['modal']) ? ($instance['modal'] == 1 ? true : false) : false;
        $formButton = !empty($instance['button']) ? strip_tags($instance['button']) : $formTitle;
        $formChoice = !empty($instance['choice']) ? $instance['choice'] : 'html';
        $formImg = !empty($instance['img']) ? $instance['img'] : null;
        $formImgHover = !empty($instance['imgHover']) ? $instance['imgHover'] : null;
        $formHSkipMobileButton = isset($instance['skipMobileButton']) ? $instance['skipMobileButton'] : false;
        $formAlign = isset($instance['shortcodeAtts']['align']) ? $instance['shortcodeAtts']['align'] : false;

        // if form is not in modal window
        if($formModal == false){
            try {
                $repositorySettings = new RepositorySettings();
                $repositoryForms = new RepositoryForms(new Cache(GENOO_CACHE), new Api($repositorySettings));
                $formId = !empty($instance['form']) && is_numeric($instance['form']) ? $instance['form'] : null;
                $formIdFinal = is_null($formId) ? $repositorySettings->getActiveForm() : $formId;
                $formForm = !empty($formIdFinal) ? $repositoryForms->getForm($formIdFinal) : '';
            } catch (\Exception $e){
                $formIdFinal = null;
                $html = "<span class='error'>" . $e->getMessage() . "</span>";
            }
        }

        // form?
        if(isset($formIdFinal) && $formModal == false){
            // html
            // Might be shortcode block
            $html .= $formAlign != false ? '<div class="genooInlineBlock '. $formAlign .'">' : null;
            $html .= $formModal ? '<div id="'. $this->id .'" class="genooModal">' : '';
            $html .= $args['before_widget'];
            $html .= '<div class="genooForm themeResetDefault '. $formClass .'">';
            $html .= '<div class="genooTitle">' . $args['before_title'] . $formTitle . $args['after_title'] . '</div>';
            $html .= '<div class="clear"></div>';
            $html .= '<div class="genooGuts">';
            $html .= '<div id="genooMsg"></div>';
            $html .= $formForm;
            $html .= '</div>';
            $html .= '<div class="clear"></div>';
            $html .= '</div>';
            $html .= $args['after_widget'];
            $html .= $formModal ? '</div>' : '';
            // Close shortcode block
            $html .= $formAlign != false ? '</div>' : null;
        } elseif ($formModal == true){
            // Might be a shortcode
            $html .= $formAlign != false ? '<div class="genooInlineBlock '. $formAlign .'">' : null;
            $html .= $args['before_widget'];
            $html .= '<div class="'. $formClass .' genooNoBG">';
            if(isset($instance['displayTitle']) && $instance['displayTitle'] == true){ $html .= '<div class="genooTitle">' . $args['before_title'] . $instance['title'] . $args['after_title'] . '</div>'; }
            if(isset($instance['displayDesc']) && $instance['displayDesc'] == true){ $html .= '<div class="genooGuts"><p class="genooPadding">' . $instance['desc'] . '</p></div>'; }
            // do we have an image button?
            if($formChoice == 'img' && (!is_null($formImg))){
                $buttonId = "genooGeneratedButton" . $this->id;
                $html .= '<span id="'. $buttonId .'" class="genooStripDown genooWidgetButton">';
                // Skipping mobile button? Shortcodes cant deal with mobile button now
                if($formHSkipMobileButton){
                    $html .= '<span>' . ModalWindow::button($formButton, $this->id, true, 'genooButton form-button-submit') . '<div class="clear"></div></span>';
                } else {
                    $html .= '<span class="genooDisplayDesktop">' . ModalWindow::button($formButton, $this->id, true, 'genooButton form-button-submit') . '<div class="clear"></div></span>';
                    $html .= '<span class="genooDisplayMobile">' . ModalWindow::button($formButton, $this->id, false, 'genooButton form-button-submit', true) . '<div class="clear"></div></span>';
                }
                $html .= '<div class="clear"></div></span>';
                $html .= Attachment::generateCss($formImg, $formImgHover, $buttonId);
            } else {
                // classic html button
                if($is_macIE || $is_winIE || $is_IE || $formHSkipMobileButton){
                    $html .= '<span>' . ModalWindow::button($formButton, $this->id, true, 'genooButton form-button-submit') . '</span>';
                } else {
                    $html .= '<span class="genooDisplayDesktop">' . ModalWindow::button($formButton, $this->id, true, 'genooButton form-button-submit') . '</span>';
                    $html .= '<span class="genooDisplayMobile">' . ModalWindow::button($formButton, $this->id, false, 'genooButton form-button-submit', true) . '</span>';
                }
            }
            $html .= '</div>';
            $html .= $args['after_widget'];
            // Close if shortcode
            $html .= $formAlign != false ? '</div>' : null;
        }

        return $html;
    }


    /**
     * Get id
     *
     * @return mixed
     */

    public function getId(){ return $this->id; }


    /**
     * Widget settings form
     *
     * @param $instance
     */

    public function form($instance)
    {
        try {
            // prep stuff
            // instance
            $instance = wp_parse_args((array) $instance, array('title' => __('Subscribe', 'genoo'), 'form' => 0, 'theme' => 0));
            // reposs
            $repoSettings = new RepositorySettings();
            $repoForms = new RepositoryForms(new Cache(GENOO_CACHE), new Api($repoSettings));
            // widget data
            $widgetThemes = $repoSettings->getSettingsThemes();
            $widgetForms = array_merge(array(array('id' => 0, 'name' => __('Default subscription form', 'genoo'))), $repoForms->getFormsTable());
            $widgetTitle = !empty($instance['title']) ? strip_tags($instance['title']) : __('Subscribe', 'genoo');
            $widgetButton = !empty($instance['button']) ? strip_tags($instance['button']) : $widgetTitle;
            $widgetForm = strip_tags($instance['form']);
            $widgetTheme = strip_tags($instance['theme']);
            $widgetMsgSuccess = !empty($instance['msgSuccess']) ? $instance['msgSuccess'] : $repoSettings->getSuccessMessage();
            $widgetMsgFail = !empty($instance['msgFail']) ? $instance['msgFail'] : $repoSettings->getFailureMessage();
            $widgetImg = !empty($instance['img']) ? $instance['img'] : null;
            $widgetImgHover = !empty($instance['imgHover']) ? $instance['imgHover'] : null;
            $formModal = isset($instance['modal']) ? ($instance['modal'] == 1 ? true : false) : false;
            $formChoice = !empty($instance['choice']) ? $instance['choice'] : 'html';
            $formHtmlClass = $formChoice == 'html' ? '' : 'hidden';
            $formImgClass = $formChoice == 'img' ? '' : 'hidden';
            // widget form
            echo '<div class="genooParagraph">'
                . '<label for="'. $this->get_field_id('title') .'">' . __('Genoo form title:', 'genoo') . ' </label><div class="clear"></div>'
                . '<input class="widefat" id="'. $this->get_field_id('title') .'" name="'. $this->get_field_name('title') .'" value="'. esc_attr($widgetTitle) .'" type="text" />'
                . '</div>';
            echo '<div class="genooParagraph">'
                . '<label for="'. $this->get_field_id('form') .'">' . __('Form:', 'genoo') . ' </label><div class="clear"></div>'
                . '<select name="'. $this->get_field_name('form') .'" id="'. $this->get_field_id('form') .'">';
            foreach($widgetForms as $value){
                echo '<option value="'. $value['id'] .'" '. selected($value['id'], $widgetForm, false) .'>' . $value['name'] . '</option>';
            }
            echo '</select>';
            echo '</div>';
            echo '<div class="genooParagraph">'
                . '<label for="'. $this->get_field_id('theme') .'">' . __('Form theme:', 'genoo') . ' </label><div class="clear"></div>'
                . '<select name="'. $this->get_field_name('theme') .'" id="'. $this->get_field_id('theme') .'">';
            foreach($widgetThemes as $key => $value){
                echo '<option value="'. $key .'" '. selected($key, $widgetTheme, false) .'>' . $value . '</option>';
            }
            echo '</select>';
            echo '</div>';
            echo '<hr />';
            // pop-up switch
            echo '<div class="genooParagraph genooOneline">'
                . '<label for="'. $this->get_field_id('modal') .'">' . __('Display in pop-up:', 'genoo') . '  </label>'
                . '&nbsp;<input onchange="Tool.switchClass(document.getElementById(\'hidden'. $this->get_field_id('button') .'\'), \'genooHidden\');" type="checkbox" value="1" '. checked($formModal, 1, false) .' name="'. $this->get_field_name('modal') .'" id="'. $this->get_field_id('modal') .'">'
                . '</div>';
            echo '<hr />';
            // hidden class
            $paragraphClass = $formModal == 1 ? '' : 'genooHidden';
            echo '<div id="hidden'. $this->get_field_id('button') .'" class="'. $paragraphClass .'">';
            echo '<div class="genooParagraph">';
            echo '<label for="'. $this->get_field_id('choice') .'">' . __('Button choice:', 'genoo') . '  </label>';
            echo '<select  onchange="Tool.switchTab(this, \''. $this->get_field_id('tab') .'\');" id="'. $this->get_field_id('choice') .'" name="'. $this->get_field_name('choice') .'">'
                . '<option value="html" '. selected($formChoice, 'html', false) .'>'. __('HTML Button', 'genoo') .'</option>'
                . '<option value="img" '. selected($formChoice, 'img', false) .'>'. __('Image button', 'genoo') .'</option>';
            echo '</select>';
            echo '</div>';
            echo '<div id="'. $this->get_field_id('tab') .'html" class="genooParagraph '. $formHtmlClass .'">'
                . '<label for="'. $this->get_field_id('button') .'">' . __('Pop-up button text:', 'genoo') . '  </label><div class="clear"></div>'
                . '<input class="widefat" id="'. $this->get_field_id('button') .'" name="'. $this->get_field_name('button') .'" value="'. esc_attr($widgetButton) .'" type="text" />'
                . '</div>';
            echo '<div id="'. $this->get_field_id('tab') .'img" class="genooParagraph '. $formImgClass .'">';
            $this->getUploadField(
                'img',
                'genooImage',
                $widgetImg,
                __('Choose button image', 'genoo'),
                __('Choose image', 'genoo')
            );
            echo '<div class="clear"></div>';
            $this->getUploadField(
                'imgHover',
                'genooImageHover',
                $widgetImgHover,
                __('Choose button hover image', 'genoo'),
                __('Choose image', 'genoo')
            );
            echo '</div>';
            echo '<hr />';
            echo '</div>';
            echo '<div class="genooParagraph">'
                . '<label for="'. $this->get_field_id('msgSuccess') .'">' . __('Form success message:', 'genoo') . '  </label>'
                . '<textarea class="widefat" id="'. $this->get_field_id('msgSuccess') .'" name="'. $this->get_field_name('msgSuccess') .'">'. esc_attr($widgetMsgSuccess) .'</textarea>'
                . '</div>';
            echo '<div class="genooParagraph">'
                . '<label for="'. $this->get_field_id('msgFail') .'">' . __('Form error message:', 'genoo') . '  </label>'
                . '<textarea class="widefat" id="'. $this->get_field_id('msgFail') .'" name="'. $this->get_field_name('msgFail') .'">'. esc_attr($widgetMsgFail) .'</textarea>'
                . '</div>';
            echo '<hr />';
        } catch (\Exception $e){
            echo '<span class="error">';
            echo $e->getMessage();
            echo '</span>';
        }
    }


    /**
     * Generate upload field
     *
     * @param $id
     * @param $target
     * @param $current
     * @param $label
     * @param $chooseLabel
     */

    public function getUploadField($id, $target, $current, $label, $chooseLabel)
    {
        $uTarget = $this->get_field_id('genooImage' . $target);
        echo '<label>'. $label .':</label><div class="clear"></div>'
            . '<div class="genooUploadSelect">'
            . '<div class="genooWidgetImage" id="'. $uTarget .'">'
            . wp_get_attachment_image($current, 'medium', false )
            . '</div>';
        echo '<input type="hidden" name="'. $this->get_field_name($id) .'" id="'. $this->get_field_id($id) .'" value="'. $current .'" />';
        echo '<a href="#" onclick="Modal.open(event,this);"'
            . 'id="'. $this->get_field_id($id . 'Btn') .'"'
            . 'data-current-id="'. $current .'"'
            . 'data-title="'. $label .'"'
            . 'data-update-text="'. $chooseLabel .'"'
            . 'data-target="'. $uTarget .'"'
            . 'data-target-input="'. $this->get_field_id($id) .'"'
            . 'class="button">'. $chooseLabel .'</a>';
        echo ' | ';
        echo '<a href="#" onclick="Modal.emptyImage(event,'
            . '\''. $uTarget . '\', '
            . '\''. $this->get_field_id($id) . '\', '
            . '\''. $this->get_field_id($id . 'Btn') . '\');'
            . '">'. __('Remove Image', 'genoo') .'</a>';
        echo '</div>';
    }
}