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

use Genoo\Url;

class Request
{
    /**
     * Has request in?
     *
     * @param $id
     * @return bool
     */

    public static function has($id)
    {
        if(isset($_GET[$id])){
            return true;
        }
        return false;
    }


    /**
     * Form result?
     *
     * @return bool|null
     */

    public static function formResult()
    {
        if(isset($_GET['formResult'])){
            if($_GET['formResult'] == 'true'){
                return true;
            } elseif($_GET['formResult'] == 'false'){
                return false;
            }
        }
        return null;
    }
}