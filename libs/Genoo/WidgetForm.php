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
use Genoo\Wordpress\MetaboxBuilder;


/**
 * Geenoo Form
 */

class WidgetForm extends \WP_Widget
{
    /** @var bool */
    public $isWidgetCTA = false;

    /**
     * Constructor registers widget in WordPress
     *
     * @param bool $constructParent
     */

    function __construct($constructParent = true, $isWidgetCTA = false)
    {
        if($constructParent){
            parent::__construct(
                'genooform',
                'Genoo',
                array('description' => __('Add Genoo forms to your pages.', 'genoo'))
            );
        } else {
            $this->isWidgetCTA = $isWidgetCTA;
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
        $formPopup = isset($instance['popup']) ? $instance['popup'] : false;
        // Wheater it'only a widget instnace
        $formInWiget = !isset($instance['shortcodeAtts']) ? TRUE : FALSE;
        // Form popover hide button?
        $isHidePopOver = (isset($instance['isPopOver']) && $instance['isPopOver']) && (isset($instance['popOverHide']) && $instance['popOverHide']) ? TRUE : FALSE;
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
            // Pop up window
            if(isset($instance['popup']) && is_array($instance['popup'])){
                // This is pop up stuff
                $cssAdditional = '';
                //$cssAdditional = isset($instance['popup']['image-on']) && !empty($instance['popup']['image-on']) ? 'genooModalPopBig' : '';
                if(isset($instance['popup']['image-on']) && !empty($instance['popup']['image-on'])){
                    $image = wp_get_attachment_image($instance['popup']['image'], 'medium', FALSE);
                    if($image){
                        $cssAdditional = 'genooModalPopBig';
                    }
                }
                $html = $formModal ? '<div id="'. $this->id .'" class="genooModal genooModalPop '. $cssAdditional .'">' : '';
                $html .= '<div class="genooForm themeResetDefault '. $formClass .'">';
                $html .= '<div class="clear"></div>';
                $html .= '<div class="genooGuts">';
                $html .= '<div id="genooMsg"></div>';
                // Close shortcode block
                $html .= MetaboxBuilder::getHTMLRenderer($instance['popup'], $formForm, $this->id);
                $html .= '</div>';
                $html .= '<div class="clear"></div>';
                $html .= '</div>';
                $html .= $formModal ? '</div>' : '';
            } else {
                // Normal pop up or widget
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
            }
        } elseif ($formModal == true){
            // Might be a shortcode
            $hidden = (isset($instance['hideButton']) && $instance['hideButton'] == TRUE) ? 'style="display:none"' : '';
            $html .= $formAlign != false ? '<div '. $hidden .' id="' . $this->id . '" class="genooInlineBlock '. $formAlign .'">' : '<div '. $hidden .' id="' . $this->id . '"">';
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
                    if(isset($instance['canHaveMobile']) && $instance['canHaveMobile'] == false){
                    } else {
                        $html .= '<span class="genooDisplayMobile">' . ModalWindow::button($formButton, $this->id, false, 'genooButton form-button-submit', true) . '<div class="clear"></div></span>';
                    }
                }
                $html .= '<div class="clear"></div></span>';
                $html .= Attachment::generateCss($formImg, $formImgHover, $buttonId, NULL, $this->isWidgetCTA);
            } else {
                // classic html button
                if($is_macIE || $is_winIE || $is_IE || $formHSkipMobileButton){
                    $html .= '<span>' . ModalWindow::button($formButton, $this->id, true, 'genooButton form-button-submit') . '</span>';
                } else {
                    $html .= '<span class="genooDisplayDesktop">' . ModalWindow::button($formButton, $this->id, true, 'genooButton form-button-submit') . '</span>';
                    if(isset($instance['canHaveMobile']) && $instance['canHaveMobile'] == false){
                    } else {
                        $html .= '<span class="genooDisplayMobile">' . ModalWindow::button($formButton, $this->id, false, 'genooButton form-button-submit', true) . '</span>';
                    }
                }
            }
            // Remove desktop display, if not needed
            if(isset($instance['canHaveMobile']) && $instance['canHaveMobile'] == false){
                $html = str_replace('class="genooDisplayDesktop"', '', $html);
            }
            // Continue on html
            $html .= '</div>';
            $html .= $args['after_widget'];
            // Close if shortcode
            $html .= $formAlign != false ? '</div>' : '</div>';
            if(isset($instance['hideButton']) && $instance['hideButton'] == TRUE){
                $html .= self::getModalFullScrollJavascript($this->id, (int)$instance['hideButtonTIME']);
            }
        }
        if(isset($instance['isPopOver']) && $instance['isPopOver'] == TRUE && $formModal == TRUE && isset($instance['isPopOverInject'])){
            $time = is_numeric($instance['popOverTime']) ? $instance['popOverTime'] : 0;
            $html .= self::getModalOpenJavascript(ModalWindow::getModalId($this->id), $time);
        }
        return $html;
    }

    /**
     * @param $modalId
     * @param bool $seconds
     * @return string
     */
    public static function getModalFullScrollJavascript($modalId, $seconds = FALSE)
    {
        $r = '';
        if(is_int($seconds)){
            $r = '<script type="text/javascript">
                        Document.ready(window, function(e){
                            setTimeout(function(){
                                document.getElementById(\''. $modalId .'\').style.display = \'\';
                            }, '. ($seconds * 1000) .');
                        });
                  </script>';
        }
        return $r;
    }

    /**
     * @param $modalId
     * @param int $seconds
     */
    public static function getModalOpenJavascript($modalId, $seconds = FALSE)
    {
        if($seconds === FALSE){
            $seconds = 0;
            global $post;
            if(isset($post) && $post instanceof \WP_Post){
                $seconds = CTA::ctaGetPopOverTime($post->ID);
            }
        }
        return  '<script type="text/javascript">
                        Document.ready(window, function(e){
                            setTimeout(function(){
                                Modal.display(null, \''. $modalId .'\');
                            }, '. ($seconds * 1000) .');
                        });
                      </script>';
    }

    /**
     * Get CTA Modal Class
     *
     * @param array $instance
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
        //return isset($instance['popup']['image-on']) && !empty($instance['popup']['image-on']) ? 'genooModalPopBig' : '';
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
                . '<label for="'. $this->get_field_id('title') .'">' . __('Form title:', 'genoo') . ' </label><div class="clear"></div>'
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