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


class Attachment
{
    /**
     * Generate CSS
     *
     * @param $img
     * @param $imgHover
     * @param $id
     * @return string
     */

    public static function generateCss($img, $imgHover, $id, $size = 'full')
    {
        $r = '';

        if(!is_null($img)){
            $src = wp_get_attachment_image_src($img, $size);
        }
        if(!is_null($imgHover)){
            $srcHover = wp_get_attachment_image_src($imgHover, $size);
        }

        $r = '<style type="text/css" scoped>';
            if(!is_null($img)){
                $r .='#' . $id . ' input { display: inline-block; width: auto; height: auto; width: '. $src[1] .'px; height: '. $src[2] .'px; min-height: '. $src[2] .'px; }';
                $r .= '#' . $id . ' input { background: url(\''. $src[0] .'\') top left no-repeat transparent; }';
            }
            if(!is_null($imgHover)){
                $r .= '#' . $id . ' input:hover, ';
                $r .= '#' . $id . ' input:focus, ';
                $r .= '#' . $id . ' input:active { background: url(\''. $srcHover[0] .'\') top left no-repeat transparent; width: '. $srcHover[1] .'px; height: '. $srcHover[2] .'px; min-height: '. $srcHover[2] .'px; }';
            }
        $r .= '</style>';

        return $r;
    }
}