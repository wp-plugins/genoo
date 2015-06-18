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

use Genoo\Wordpress\Utils;
use Genoo\Wordpress\Notice;
use Genoo\Tools;

class TableForms extends Table
{
    /** @var \Genoo\RepositoryForms */
    var $repositoryForms;
    /** @var \Genoo\RepositorySettings */
    var $repositorySettings;
    /** @var string */
    var $activeForm;

    /**
     * Constructor
     *
     * @param RepositoryLog $log
     */

    function __construct(\Genoo\RepositoryForms $repositoryForms, \Genoo\RepositorySettings $repositorySettings)
    {
        global $status, $page;
        $this->repositoryForms = $repositoryForms;
        $this->repositorySettings = $repositorySettings;
        $this->activeForm = $repositorySettings->getActiveForm();
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
            'name' => __('Form name', 'genoo'),
            'shortcode' => __('Shortcode', 'genoo'),
            'active' => __('Current active subscription form?', 'genoo'),
            'form' => __('Preview', 'genoo'),
        );
    }


    /**
     * Basic setup, returns sortable columns
     *
     * @return array
     */

    function get_sortable_columns(){ return array( 'name' => array('name',false) ); }


    /**
     * Active column
     *
     * @param $item
     * @return string
     */

    function column_active($item)
    {
        $activeId = isset($_GET['genooFormId']) ? $_GET['genooFormId'] : $this->activeForm;
        $active = $activeId == $item['id'] ? ' active' : '';
        $default = $activeId != $item['id'] ? '&nbsp;&nbsp;&nbsp;<a data-genooFormId="'. $item['id'] .'" href="'. Utils::addQueryParam($this->url, 'genooFormId', $item['id']) .'">Set as default</a>' : '';
        return
            '<a data-genooFormId="'. $item['id'] .'" href="'. Utils::addQueryParam($this->url, 'genooFormId', $item['id']) .'"><span class="genooTick '. $active .'">&nbsp;</span></a>'
            . $default;
    }


    /**
     * Shortcode
     *
     * @param $item
     * @return string
     */

    function column_shortcode($item){ return '<code>[genooForm id="'. $item['id'] .'"]</code>'; }


    /**
     * Remove cached forms
     *
     * @param $which
     */

    function extra_tablenav($which)
    {
        if($which == 'top'){
            echo '<form style="display: inline; margin: 0" method="POST"><input type="submit" name="genooFormsFlushCache" id="submit" class="button alignCenter" value="'. __('Sync forms', 'genoo') .'"></form>';
        }
    }


    /**
     * Form preview thickbox
     *
     * @param $item
     * @return string
     */

    function column_form($item)
    {
        $prepForm = '';
        $prepForm .= '<div id="genooForm'. $item['id'] .'" style="display:none;"><h2>'. $item['name'] .'</h2>';
            $prepForm .= $this->repositoryForms->getForm($item['id']);
        $prepForm .= '</div>';
        $prepForm .= '<a href="#TB_inline?width=600&height=550&inlineId=genooForm'. $item['id'] .'" class="thickbox">'. __('Preview form', 'genoo') .'</a>';
        return $prepForm;
    }


    /**
     * No Items notices
     */

    function no_items(){ __('No forms in your Genoo account.', 'genoo'); }


    /**
     *  Prepares, sorts, delets, all that stuff :)
     */

    public function prepare_items()
    {
        try {
            $perPage = parent::getPerPage();
            $allLogs = $this->repositoryForms->getFormsTable();
            $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());
            usort($allLogs, array(&$this, 'usort_reorder'));
            $this->found_data = array_slice($allLogs,(($this->get_pagenum()-1)* $perPage), $perPage);
            $this->set_pagination_args(array('total_items' => count($allLogs), 'per_page' => $perPage));
            $this->items = $this->found_data;
            $this->checkActiveForm($this->items);
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
        if(isset($_POST['genooFormsFlushCache'])){
            try{
                $this->repositoryForms->flush();
                $this->addNotice('updated', __('All forms successfully flushed.', 'genoo'));
            } catch (\Exception $e){
                $this->addNotice('error', $e->getMessage());
            }
        }
        if(isset($_GET['genooFormId']) && is_numeric($_GET['genooFormId'])){
            $this->repositorySettings->setActiveForm($_GET['genooFormId']);
            $this->addNotice('updated', __('Form set as primary Subscribe Form.', 'genoo'));
        }
    }

    /**
     * Check if Active default form has been changed / removed
     *
     * @param $forms
     */
    public function checkActiveForm($forms)
    {
        if(!empty($this->activeForm)){
            $found = FALSE;
            if(is_array($forms) && !empty($forms) && !empty($this->activeForm)){
                foreach($forms as $form){
                    if(isset($form['id'])){
                        if($form['id'] == $this->activeForm){
                            $found = TRUE;
                            break;
                        }
                    }
                }
            }
            // Only if none of the forms inside matches active form
            if($found == FALSE){
                $this->addNotice('error', 'Have you recently changed your Genoo forms? Your default form seems to be missing, don’t forget to select a new one!');
            }
        }
    }
}
