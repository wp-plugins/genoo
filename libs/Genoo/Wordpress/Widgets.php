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

use Genoo\Tracer;
use Genoo\Utils\Strings;
use Genoo\Utils\ArrayObject;
use Genoo\Wordpress\Action;
use Genoo\Wordpress\Utils;
use Genoo\WidgetCTADynamic;
use Genoo\CTA;

class Widgets
{

    /**
     * Register widgets
     */

    public static function register()
    {
        Action::add('widgets_init', function (){
            // Register main Genoo plugins
            register_widget('\Genoo\WidgetForm');
            // CTA Widget is now only legacy
            if(GENOO_LEGACY === TRUE){
                register_widget('\Genoo\WidgetCTA');
            }
            // If lumens are set up.
            if(GENOO_LUMENS){
                register_widget('\Genoo\WidgetLumen');
            }
        });
    }


    /**
     * Get registered widget by name
     *
     * @param string $name
     * @return array
     */

    public static function get($name = '')
    {
        // Global
        global $wp_widget_factory;
        // Vars
        $arr = array();
        // Go through
        if ($wp_widget_factory->widgets) {
            foreach ($wp_widget_factory->widgets as $class => $widget) {
                // Congratulations, we have a Genoo widget
                if (Strings::contains(Strings::lower($widget->id_base), $name)) {
                    $widget->class = $class;
                    $arr[] = $widget;
                }
            }
        }
        // Return widgets
        return $arr;
    }


    /**
     * Remove instances of 'PLUGIN_ID'
     *
     * @param string $name
     */

    public static function removeInstancesOf($name = '')
    {
        $sidebarChanged = false;
        $sidebarWidgets = wp_get_sidebars_widgets();
        // not empty?
        if (is_array($sidebarWidgets) && !empty($sidebarWidgets)){
            // go through areas
            foreach ($sidebarWidgets as $sidebarKey => $sidebarWidget){
                // not empty array?
                if (is_array(($sidebarWidget)) && !empty($sidebarWidget)){
                    // go through
                    foreach ($sidebarWidget as $key => $value){
                        // is it our widget-like?
                        if (Strings::contains($value, $name)){
                            unset($sidebarWidgets[$sidebarKey][$key]);
                            $sidebarChanged = true;
                        }
                    }
                }
            }
        }
        if($sidebarChanged == true){
            wp_set_sidebars_widgets($sidebarWidgets);
        }
    }


    /**
     * Wordpress innner function
     *
     * @return array | mixed
     */

    public static function getArrayOfWidgets()
    {
        return retrieve_widgets();
    }


    /**
     * Get footer modals
     *
     * @return array
     */

    public static function getFooterModals()
    {
        // Get them
        $widgets = self::get('genoo');
        $widgetsArray = self::getArrayOfWidgets();
        $widgetsObj = array();
        // Go through them
        if ($widgets){
            foreach ($widgets as $widget){
                // Get instances
                $widgetInstances = $widget->get_settings();
                if (is_array($widgetInstances)){
                    foreach ($widgetInstances as $id => $instance){
                        $currId = $widget->id_base . $id;
                        $currWpId = $widget->id_base . '-' . $id;
                        // This is it! is it modal widget?
                        if ((isset($instance['modal']) && $instance['modal'] == 1) || ($widget->id_base == 'genoocta')){
                            // Is it active tho?
                            if (isset($widgetsArray['wp_inactive_widgets']) && !in_array($currWpId, $widgetsArray['wp_inactive_widgets'])){
                                unset($widgetInstances[$id]['modal']);
                                $widgetsObj[$currId] = new \stdClass();
                                $widgetsObj[$currId]->widget = $widget;
                                $widgetsObj[$currId]->instance = $widgetInstances[$id];
                                // Can we get inner instance? (cta widget)
                                if(method_exists($widget, 'getInnerInstance')){
                                    $widgetsObj[$currId]->instance = $widgetsObj[$currId]->instance + $widget->getInnerInstance();
                                }
                            }
                        }
                    }
                }
            }
        }
        // give me
        return $widgetsObj;
    }


    /**
     * Get footer modals, out of previously
     * genereated dynamic ctas.
     *
     *
     * @param $sidebars
     * @return array
     */

    public static function getFooterDynamicModals($sidebars)
    {
        $r = array();
        if(is_array($sidebars) && !empty($sidebars)){
            // Go through sidebars
            foreach($sidebars as $sidebar){
                // Go through widgets in sidebars
                if(is_array($sidebar) && !empty($sidebar)){
                    foreach($sidebar as $widget){
                        if($widget->widgetIsForm){
                            $r[$widget->widget] = new \stdClass();
                            $r[$widget->widget]->widget = $widget->widgetInstance;
                            if(method_exists($widget->widgetInstance, 'getInnerInstance')){
                                $r[$widget->widget]->instance = $widget->widgetInstance->getInnerInstance();
                            }
                        }
                    }
                }
            }
        }
        return $r;
    }


    /**
     * Inject Widget Into sidebar
     *
     * @param $sidebarKey
     * @param $widgetKey
     * @param $position
     */

    public static function injectIntoSidebar($sidebarKey, $widgetKey, $position)
    {
        static $priority = 1;
        // Inject sidebar instance
        Filter::add('sidebars_widgets', function($sidebars) use ($sidebarKey, $widgetKey, $position){
            if(isset($sidebars[$sidebarKey])){
                $sidebars[$sidebarKey] = ArrayObject::appendTo($sidebars[$sidebarKey], $position, $widgetKey);
            }
            return $sidebars;
        }, $priority, 1);
        // Higher number to postpone.
        ++$priority;
    }


    /**
     * Inject multiple Widgets into Sidebars
     *
     * @param $widgets
     */

    public static function injectMultipleIntoSidebar($widgets)
    {
        // Do we have an array? Let's go through
        if(is_array($widgets) && !empty($widgets)){
            Filter::add('sidebars_widgets', function($sidebars) use ($widgets){
                // One last protection inside the filter (just a precaution)
                if(Utils::isSafeFrontend()){
                    // Go through sidebars
                    foreach($widgets as $sidebarKey => $widgetArray){
                        // Sort array by position, only if array and positionable
                        if(is_array($widgetArray) && isset($widgetArray[0]->position)){
                            usort($widgetArray, function($a, $b){
                                return ($a->position == $b->position)
                                    ? (($a->position < $b->position) ? -1 : 1)
                                    : ($a->position - $b->position);
                            });
                        }
                        // Each sidebar has an array of widgets,
                        // even one widget will be in an array
                        if(is_array($widgetArray) && !empty($widgetArray)){
                            // Before going through widgets, removing instances of
                            // all dynamic CTA widgets, so we position them correctly
                            $sidebars[$sidebarKey] = ArrayObject::removeByValueLike($sidebars[$sidebarKey], 'genoodynamiccta');
                            // Going through widgets
                            foreach($widgetArray as $widget){
                                // If the sidebar they are assigned to exists,
                                // continue (if not, might have been removed, theme change etc.)
                                if(isset($sidebars[$sidebarKey])){
                                    // Check if it's not already there, because the widget "id" is unique
                                    // it shouldn't be there more than once
                                    if(!in_array($widget->widget, $sidebars[$sidebarKey])){
                                        // Positin wise setup
                                        if($widget->position == -1){        // Last
                                            $sidebars[$sidebarKey] = ArrayObject::appendToTheEnd($sidebars[$sidebarKey], $widget->widget);
                                        } elseif ($widget->position == 1){  // First
                                            $sidebars[$sidebarKey] = ArrayObject::prependToTheBeginning($sidebars[$sidebarKey], $widget->widget);
                                        } else {                            // Other
                                            $position = ($widget->position < 0) ? 0 : $widget->position - 1;
                                            $sidebars[$sidebarKey] = ArrayObject::appendTo($sidebars[$sidebarKey], $position, $widget->widget);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                return $sidebars;
            }, 999, 1);
        }
    }


    /**
     * Inject values for a widget
     *
     * @param $widgets
     */

    public static function injectMultipleValues($widgets)
    {
        // We will store the data here.
        $r = array();
        $r['_multiwidget'] = 1;
        // Is it not empty?
        if(is_array($widgets) && !empty($widgets)){
            foreach($widgets as $sidebarKey => $widgetArray){
                // This is a sidebar now, let's find those keys we need
                if(is_array($widgetArray) && !empty($widgetArray)){
                    // Get widgets
                    foreach($widgetArray as $widget){
                        // Decode: "widgetname-1"
                        $widgetPrep = explode('-', $widget->widget);
                        $widgetName = $widgetPrep[0];
                        $widgetNumber = $widgetPrep[1];
                        // Add to the array
                        $r[(int)$widgetNumber] = array();
                    }
                }
            }
        }
        // Do we have a name? And data for widgets? Let's do this ...
        if(!empty($widgetName) && !empty($r)){
            // This is the pre_option hook, since the widgets are injected
            // they don't have settings saved in db, we have to create them
            // this way and inject them as well.
            Filter::add('pre_option_widget_' . $widgetName, function($value) use ($r){
                return $r;
            }, 1, 1);
        }
    }


    /**
     * Inject to register widgets.
     *
     * @param array $widgets
     * @param null $prefix
     * @return array
     */

    public static function injectRegisterWidgets(array $widgets, $prefix = 'genoodynamiccta')
    {
        global $wp_registered_widgets;
        $counter = 1;
        $return = array();
        // Going through widgets and registering them
        foreach($widgets as $widget){
            // Current id
            $current = $prefix . '-' . $counter;
            // If widget doesnt exist there ... put it in!
            if(!isset($wp_registered_widgets[$current])){
                // Add do registered widgets
                $wp_registered_widgets[$current] = array(
                    'name' => __('Genoo Dynamic CTA Widget', 'genoo'),
                    'id' => $current,
                    'callback' => array(
                        $widgetInstance = new WidgetCTADynamic($prefix, $counter, $widget),
                        'display_callback'
                    ),
                    'params' => array(
                        array(
                            'number' => $counter
                        )
                    ),
                    'classname' => 'classname',
                    'description' => __('This is Genoo Dynamic CTA Widget.', 'genoo')
                );
                // Add current id, so it can be deleted later
                $return[$widget->sidebar][] = (object)array(
                    'widget' => $current,
                    // Only add if cta is form
                    'widgetInstance' => $widgetInstance->preCta->isForm ? $widgetInstance : null,
                    // WidgetIsForm is used in footer modals
                    'widgetIsForm' => $widgetInstance->preCta->isForm,
                    'position' => $widget->position
                );
            }
            ++$counter;
        }
        // Return array of sidabar => array( widgets ids ) to go through and remove afterwoods
        return $return;
    }


    /** */
    public static function refreshDynamic()
    {
        //wp_registered_widgets
    }
}