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

use Genoo\Utils\Strings,
    Genoo\Wordpress\Utils;


abstract class Repository
{
    /** @var string */
    var $tableName;
    /** @var string */
    var $tableSingleName;


    /**
     * Constructor extracst name of Repository
     */

    public function __construct()
    {
        preg_match('#Repository(\w+)$#', get_class($this), $class);
        $this->tableName = Utils::camelCaseToUnderscore($class[1]);
        $this->tableSingleName = Strings::firstUpper($class[1]);
    }
}