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

use Genoo\RepositorySettings;


class Frontend
{

    /**
     * Enqueue styles
     */

    public function __construct()
    {
        // append frontend form styles
        wp_enqueue_style('genooFrontend', GENOO_ASSETS . 'GenooFrontend.css', null, '1.0');
        // appned tracking code
        add_action('wp_footer', function(){
            if(GENOO_SETUP){
                $settings = new RepositorySettings();
                echo $settings->getTrackingCode();
            }
        }, 999);
    }
}