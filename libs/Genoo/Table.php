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

use Genoo\RepositoryUser;
use Genoo\Utils\Strings;
use Genoo\Wordpress\Utils;
use Genoo\Wordpress\Notice;
use Genoo\Wordpress\WPListTable;

abstract class Table extends \Genoo\Wordpress\WPListTable
{
    /** @var array() */
    var $notices;
    /** @var string */
    var $url;
    /** @var string */
    var $tableName;
    /** @var string */
    var $tableSingleName;
    /** @var \Genoo\RepositoryUser */
    var $user;
    /** @var \WP_Screen */
    var $screen;

    /**
     * Constructor
     */

    public function __construct($args = array())
    {
        // real url
        $this->url = Utils::getRealUrl();
        // vars
        preg_match('#Table(\w+)$#', get_class($this), $class);
        $this->tableName = Utils::camelCaseToUnderscore($class[1]);
        $this->tableSingleName = Strings::firstUpper($class[1]);
        // user repo
        $this->user = new RepositoryUser();
        $this->screen = get_current_screen();
        $this->screenId = str_replace('genoo_page_', '', $this->screen->id);
        $this->screenOptions = $this->screenId == 'Genoo' . $this->tableSingleName ? true : false;
        $this->userPerpage = $this->user->getOption('genoo_per_page');
        $this->perPage = $this->userPerpage ? $this->userPerpage : 50;
        // bam
        parent::__construct(array_merge(array('singular'=> 'log', 'plural' => 'logs', 'ajax' => false, 'screen' => $this->screen),$args));
    }


    /**
     * Get current perpage
     *
     * @return int
     */

    public function getPerPage()
    {
        if(isset($_POST['wp_screen_options'])){
            if(isset($_POST['wp_screen_options']['option']) && $_POST['wp_screen_options']['option'] == 'genoo_per_page'){
                return $_POST['wp_screen_options']['value'];
            }
        }
        return $this->perPage;
    }


    /**
     * No Items notices
     */

    function no_items(){ __('We are sorry, but there are no items to be listed.', 'genoo'); }


    /**
     * Prepare items
     */

    public function prepare_items(){}


    /**
     * Reordering function.
     *
     * @param $a
     * @param $b
     * @return int
     */

    function usort_reorder($a, $b)
    {
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'id';
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';
        $result = strcmp($a[$orderby], $b[$orderby]);
        return ($order === 'asc') ? $result : -$result;
    }


    /**
     * Default columns display behaviour
     *
     * @param $item
     * @param $column_name
     * @return mixed
     */

    function column_default($item, $column_name){ return $item[$column_name]; }


    /**
     * Get notices
     *
     * @return mixed
     */

    public function getNotices(){ return $this->notices; }


    /**
     * Has Messages
     *
     * @return bool
     */

    public function hasNotices(){ if(!empty($this->notices)){ return TRUE; } return FALSE; }


    /**
     * Adds Message to be returned later - anywhere we want
     *
     * @param $key
     * @param $msg
     */

    public function addNotice($key, $msg){ $this->notices[] = array($key, $msg); }


    /**
     * Sends notices to renderer
     */

    public function renderTableNotices()
    {
        if($this->notices){
            foreach($this->notices as $key => $value){
                $this->displayAdminNotice($value[0], $value[1]);
            }
        }
    }


    /**
     * Display admin notices
     *
     * @param null $class
     * @param null $text
     */

    private function displayAdminNotice($class = NULL,$text = NULL){ echo Notice::type($class)->text($text); }


    /**
     * Runs default renderer, but displays notices just before that.
     */

    public function display()
    {
        if(method_exists($this, 'process')){ $this->process(); }
        $this->prepare_items();
        $this->renderTableNotices();
        parent::display();
    }
}
