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

use Genoo\Utils\CSS;


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

        if(!is_null($img)){ $src = wp_get_attachment_image_src($img, $size); }
        if(!is_null($imgHover)){ $srcHover = wp_get_attachment_image_src($imgHover, $size); }

        $css = new CSS();
        if(!is_null($img)){
            $css->addRule('#' . $id.  ' input')
                ->add('background', 'url(\'' . $src[0] . '\') top left no-repeat transparent')
                ->add('display', 'inline-block')
                ->add('width', 'auto')
                ->add('height', 'auto')
                ->add('width', $src[1] . 'px')
                ->add('height', $src[2] . 'px')
                ->add('min-height', $src[2] . 'px')
                ->add('max-width', '100%');
        }
        if(!is_null($imgHover)){
            $css->addRule('#' . $id . ' input:hover, ' . '#' . $id . ' input:focus, ' . '#' . $id . ' input:active')
                ->add('background', 'url(\'' . $srcHover[0] . '\') top left no-repeat transparent')
                ->add('width', $srcHover[1] . 'px')
                ->add('height', $srcHover[2] . 'px')
                ->add('min-height', $srcHover[2] . 'px')
                ->add('max-width', '100%');
        }

        // clean up theme styles
        $css->addRule('#' . $id.  ' input')
            ->add('box-shadow', 'none !important')
            ->add('border', 'none !important')
            ->add('border-radius', '0 !important');

        return $css;
    }
}