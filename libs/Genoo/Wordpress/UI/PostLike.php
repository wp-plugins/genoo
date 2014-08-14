<?php
/**
 * This file is part of the Genoo plugin.
 *
 * Copyright (c) 2014 Genoo, LLC (http://www.genoo.com/)
 *
 * For the full copyright and license information, please view
 * the Genoo.php file in root directory of this plugin.
 */

namespace Genoo\Wordpress\UI;


class PostLike
{

    var $title = '';


    /**
     * Render
     */

    public function render(){ echo self::__toString(); }


    public function __toString()
    {
        return '
            <h2>Add New Thing</h2>
            <form action="" method="POST">

            </form>
        ';
    }
}