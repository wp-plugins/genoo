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

use Genoo\Api;


class RepositorySettings extends Repository
{
    /** settings key */
    const KEY_SETTINGS = 'genooApiSettings';
    /** general - used only by plugin calls */
    const KEY_GENERAL = 'genooApiGeneral';
    /** theme */
    const KEY_THEME = 'genooThemeSettings';
    /** @var get_option key */
    var $key;


    /**
     * Constructor
     */

    public function __construct()
    {
        $this->key = GENOO_KEY;
    }


    /**
     * Get the value of a settings field
     *
     * @param string  $option  settings field name
     * @param string  $section the section name this field belongs to
     * @param string  $default default text if it's not found
     * @return string
     */

    public function getOption($option, $section, $default = '')
    {
        $options = get_option($section);
        if (isset($options[$option])){
            return $options[$option];
        }
        return $default;
    }


    /**
     * Get options namespace
     *
     * @param $namespace
     * @return mixed
     */

    public function getOptions($namespace){ return get_option($namespace); }


    /**
     * Set option
     *
     * @param $option
     * @param $value
     * @return mixed
     */

    public function setOption($option, $value){ return update_option($option, $value); }


    /**
     * Delete option
     *
     * @param $option
     * @return mixed
     */

    public function deleteOption($option){ return delete_option($option); }


    /**
     * Update options, we don't need to check if it exists, it will create it if not.
     *
     * @param $namespace
     * @param array $options
     * @return mixed
     */

    public function updateOptions($namespace, array $options = array()){ return update_option($namespace, $options); }


    /**
     * Get API key from settings
     *
     * @return string
     */

    public function getApiKey(){ return $this->getOption('apiKey', self::KEY_SETTINGS); }


    /**
     * Get active form id
     *
     * @return string
     */

    public function getActiveForm(){ return $this->getOption('activeForm', self::KEY_GENERAL); }


    /**
     * Get current active theme
     *
     * @return string
     */

    public function getActiveTheme(){ return $this->getOption('genooFormTheme', self::KEY_THEME); }


    /**
     * Sets active form
     *
     * @param $id
     * @return mixed
     */

    public function setActiveForm($id){ return $this->injectSingle('activeForm', $id, self::KEY_GENERAL); }


    /**
     * Add saved notice
     *
     * @param $key
     * @param $value
     */

    public function addSavedNotice($key, $value){ $this->injectSingle('notices', array($key => $value), self::KEY_GENERAL); }


    /**
     * Get saved notices
     *
     * @return null
     */

    public function getSavedNotices()
    {
        $general = $this->getOptions(self::KEY_GENERAL);
        if(isset($general['notices'])){
            return $general['notices'];
        }
        return null;
    }


    /**
     * Flush aaved notices - just rewrites with null value
     *
     * @return bool
     */

    public function flushSavedNotices()
    {
        $this->injectSingle('notices', null, self::KEY_GENERAL);
        return true;
    }


    /**
     * Get lead types
     *
     * @return array
     */

    public function getSettingsFieldLeadTypes()
    {
        $api = new Api($this);
        $arr = array();
        $arr[] = __('- Select commenter lead type', 'genoo');
        if(GENOO_PART_SETUP){
            try{
                foreach($api->getLeadTypes() as $lead){
                    $arr[$lead->id] = $lead->name;
                }
            } catch (\Exception $e){}
            return array(
                'name' => 'apiCommenterLeadType',
                'label' => __('Blog commenter lead type', 'genoo'),
                'type' => 'select',
                'desc' => __('You control your Lead Types in: Lead Management > Leads.', 'genoo'),
                'options' => $arr
            );
        }
        return null;
    }


    /**
     * Set single
     *
     * @param $key
     * @param $value
     * @param $namespace
     * @return mixed
     */

    public function injectSingle($key, $value, $namespace)
    {
        $original = $this->getOptions($namespace);
        if(is_array($value)){
            // probably notices
            $inject[$key] = array_merge((array)$original[$key], array($value));
        } else {
            $inject[$key] = $value;
        }
        return $this->updateOptions($namespace, array_merge((array)$original, (array)$inject));
    }


    /**
     * Get's tracking code
     *
     * @return string
     */

    public function getTrackingCode(){ return $this->getOption('apiExternalTrackingCode', self::KEY_SETTINGS); }


    /**
     * Get lead type
     *
     * @return string
     */

    public function getLeadType(){ return $this->getOption('apiCommenterLeadType', self::KEY_SETTINGS); }



    /**
     * Gets settings page sections
     *
     * @return array
     */

    public function getSettingsSections()
    {
        if(GENOO_SETUP){
            return array(
                array(
                    'id' => 'genooApiSettings',
                    'title' => __('API settings', 'genoo')
                ),
                array(
                    'id' => 'genooThemeSettings',
                    'title' => __('Form themes', 'genoo')
                )
            );
        } else {
            return array(
                array(
                    'id' => 'genooApiSettings',
                    'title' => __('API settings', 'genoo')
                ),
            );
        }
    }


    /**
     * Set debug
     *
     * @param bool $val
     */

    public function setDebug($val = true)
    {
        if($val === TRUE){
            $this->setOption('genooDebug', true);
        } else {
            $this->deleteOption('genooDebug');
        }
    }


    /**
     * Debug check removal
     *
     * @return mixed
     */

    public function flushDebugCheck(){ return $this->deleteOption('genooDebugCheck'); }


    /**
     * Gets settings page fields
     *
     * @return array
     */

    public function getSettingsFields()
    {
        return array(
            'genooApiSettings' => array(
                array(
                    'name' => 'apiKey',
                    'label' => __('API key', 'genoo'),
                    'type' => 'text',
                    'default' => '',
                    'desc' => __('You can generate your API key in: Control panel > Settings > Api.', 'genoo')
                ),
                array(
                    'name' => 'apiExternalTrackingCode',
                    'label' => __('External tracking code', 'genoo'),
                    'type' => 'textarea',
                    'desc' => __('You can generate your tracking code in: Control panel > Settings > External tracking.', 'genoo')
                ),
                $this->getSettingsFieldLeadTypes()
            ),
            'genooThemeSettings' => array(
                array(
                    'desc' => __('Set the theme to use for your forms. “Default” means that Genoo forms will conform to the default form look associated with your WordPress theme.', 'genoo'),
                    'type' => 'desc',
                    'name' => 'genooForm',
                    'label' => '',
                ),
                array(
                    'name' => 'genooFormTheme',
                    'label' => __('Form theme', 'genoo'),
                    'type' => 'select',
                    'attr' => array(
                        'onchange' => 'Genoo.switchToImage(this)'
                    ),
                    'options' => $this->getSettingsThemes()
                ),
                array(
                    'name' => 'genooFormPrev',
                    'type' => 'html',
                    'label' => __('Form preview', 'genoo'),
                ),
            )
        );
    }


    /**
     * Get settings themes
     *
     * @return array
     */

    public static function getSettingsThemes()
    {
        return array(
            'themeDefault' => 'Default',
            'themeBlackYellow' => 'Black &amp; Yellow',
            'themeBlue' => 'Blue',
            'themeFormal' => 'Formal',
            'themeBlackGreen' => 'Black &amp; Green',
            'themeGreeny' => 'Greeny',
        );
    }


    /**
     * Flush all settings
     */

    public static function flush()
    {
        delete_option('genooApiSettings');
        delete_option('genooApiGeneral');
        delete_option('genooDebug');
        delete_option('genooDebugCheck');
    }
}