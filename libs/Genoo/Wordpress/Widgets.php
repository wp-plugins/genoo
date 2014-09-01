<?php
/**
 * Created by PhpStorm.
 * User: latorante
 * Date: 27.5.14
 * Time: 19:54
 */
namespace Genoo\Wordpress;

use Genoo\Utils\Strings,
    Genoo\CTA;

class Widgets
{

    /**
     * Register widgets
     */

    public static function register()
    {
        add_action('widgets_init', function () {
            register_widget('\Genoo\WidgetForm');
            register_widget('\Genoo\WidgetCTA');
            // If lumens are set up.
            if (GENOO_LUMENS){
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
        // global
        global $wp_widget_factory;
        // vars
        $arr = array();
        // go through
        if ($wp_widget_factory->widgets) {
            foreach ($wp_widget_factory->widgets as $class => $widget) {
                // congratulations, we have a Genoo widget
                if (Strings::contains(Strings::lower($widget->id_base), $name)) {
                    $widget->class = $class;
                    $arr[] = $widget;
                }
            }
        }
        // return widgets
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
        if (is_array($sidebarWidgets) && !empty($sidebarWidgets)) {
            // go through areas
            foreach ($sidebarWidgets as $sidebarKey => $sidebarWidget) {
                // not empty array?
                if (is_array(($sidebarWidget)) && !empty($sidebarWidget)) {
                    // go through
                    foreach ($sidebarWidget as $key => $value) {
                        // is it our widget-like?
                        if (Strings::contains($value, $name)) {
                            unset($sidebarWidgets[$sidebarKey][$key]);
                            $sidebarChanged = true;
                        }
                    }
                }
            }
        }
        if ($sidebarChanged == true) {
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
        // get them
        $widgets = self::get('genoo');
        $widgetsArray = self::getArrayOfWidgets();
        $widgetsObj = array();
        // go through them
        if ($widgets){
            foreach ($widgets as $widget){
                // get instances
                $widgetInstances = $widget->get_settings();
                if (is_array($widgetInstances)){
                    foreach ($widgetInstances as $id => $instance){
                        $currId = $widget->id_base . $id;
                        $currWpId = $widget->id_base . '-' . $id;
                        // this is it! is it modal widget?
                        if ((isset($instance['modal']) && $instance['modal'] == 1) || ($widget->id_base == 'genoocta')){
                            // is it active tho?
                            if (isset($widgetsArray['wp_inactive_widgets']) && !in_array($currWpId, $widgetsArray['wp_inactive_widgets'])) {
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
}