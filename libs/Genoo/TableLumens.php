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

use Genoo\Wordpress\Utils,
    Genoo\Wordpress\Notice,
    Genoo\Tools;

class TableLumens extends Table
{
    /** @var \Genoo\RepositoryLumens */
    var $repositoryLumens;
    /** @var \Genoo\RepositorySettings */
    var $repositorySettings;
    /** @var string */
    var $activeForm;

    /**
     * Constructor
     *
     * @param RepositoryLog $log
     */

    function __construct(\Genoo\RepositoryLumens $repositoryLumens, \Genoo\RepositorySettings $repositorySettings)
    {
        global $status, $page;
        $this->repositoryLumens = $repositoryLumens;
        $this->repositorySettings = $repositorySettings;
        parent::__construct();
    }


    /**
     * Basic setup, returns table columns.
     *
     * @return array
     */

    function get_columns()
    {
        return array(
            'id' => 'ID',
            'name' => __('Name', 'genoo'),
            'shortcode' => __('Shortcode', 'genoo'),
            'lumen' => __('Preview', 'genoo'),
        );
    }


    /**
     * Basic setup, returns sortable columns
     *
     * @return array
     */

    function get_sortable_columns(){ return array( 'name' => array('name',false) ); }



    /**
     * Shortcode
     *
     * @param $item
     * @return string
     */

    function column_shortcode($item){ return '<code>[genooLumen id="'. $item['id'] .'"]</code>'; }


    /**
     * Remove cached forms
     *
     * @param $which
     */

    function extra_tablenav($which)
    {
        if($which == 'top'){
            echo '<form style="display: inline; margin: 0" method="POST"><input type="submit" name="genooLumensFlushCache" id="submit" class="button alignCenter" value="'. __('Sync lists', 'genoo') .'"></form>';
        }
    }


    /**
     * Form preview thickbox
     *
     * @param $item
     * @return string
     */
    function column_lumen($item)
    {
        $prepForm = '';
        $form = $this->repositoryLumens->getLumen($item['id']);
        $formData = Tools::parseLumenData($form);
        $prepForm .= '<!--';
            $prepForm .= $form;
        $prepForm .= '-->';
        if(is_object($formData)){
            $prepForm .= '<a href="'. GENOO_HOME_URL .'?genooIframeLumen='. $formData->id .'&genooIframeLumenSrc='. base64_encode($formData->src) .'&TB_iframe=true&width=250&height=300" class="thickbox">'. __('Preview list', 'genoo') .'</a>';
        }
        return $prepForm;
    }


    /**
     * No Items notices
     */

    function no_items(){ __('No lists in your Genoo account.', 'genoo'); }


    /**
     *  Prepares, sorts, delets, all that stuff :)
     */

    public function prepare_items()
    {
        try {
            $perPage = parent::getPerPage();
            $allLogs = $this->repositoryLumens->getLumensTable();
            $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());
            usort($allLogs, array(&$this, 'usort_reorder'));
            $this->found_data = array_slice($allLogs,(($this->get_pagenum()-1)* $perPage), $perPage);
            $this->set_pagination_args(array('total_items' => count($allLogs), 'per_page' => $perPage));
            $this->items = $this->found_data;
        } catch (\Genoo\ApiException $e){
            $this->addNotice('error', 'Genoo API: ' . $e->getMessage());
        } catch (\Exception $e){
            $this->addNotice('error', $e->getMessage());
        }
    }


    /**
     * Process it!
     */

    public function process()
    {
        // sortof beforeRender, add thickbox, just to be sure
        if(function_exists('add_thickbox')){ add_thickbox(); }
        // process actions
        if(isset($_POST['genooLumensFlushCache'])){
            try{
                $this->repositoryLumens->flush();
                $this->addNotice('updated', __('All lists successfully flushed.', 'genoo'));
            } catch (\Exception $e){
                $this->addNotice('error', $e->getMessage());
            }
        }
    }
}
