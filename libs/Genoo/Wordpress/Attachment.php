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
     * Genreate CSS
     *
     * @param $img
     * @param $imgHover
     * @param $id
     * @param string $size
     * @param bool $ratio
     * @return CSS
     */
    public static function generateCss($img, $imgHover, $id, $size = 'full', $ratio = FALSE)
    {
        $r = '';
        $size = !is_string($size) ? 'full' : $size;
        $experimental = $ratio;
        if(!is_null($img)){ $src = wp_get_attachment_image_src($img, $size); }
        if(!is_null($imgHover)){ $srcHover = wp_get_attachment_image_src($imgHover, $size); }
        $css = new CSS();
        // Image preload for hover
        if(!is_null($imgHover)){
            $css->addRule('body #' . $id.  ' input:after')
                ->add('content', 'url('. $srcHover[0] .')')
                ->add('display', 'none !important');
        }
        // Image
        if(!is_null($img)){
            $css->addRule('body #' . $id.  ' input')
                ->add('background', 'url(\'' . $src[0] . '\') top left no-repeat transparent !important')
                ->add('background-size', '100% auto')
                ->add('display', 'inline-block')
                ->add('width', 'auto')
                ->add('height', 'auto')
                ->add('width', $src[1] . 'px')
                ->add('height', $src[2] . 'px')
                ->add('min-height', $src[2] . 'px')
                ->add('max-width', '100%');
        }
        // Image Hover
        if(!is_null($imgHover)){
            $css->addRule('body #' . $id . ' input:hover, ' . '#' . $id . ' input:focus, ' . '#' . $id . ' input:active')
                ->add('background', 'url(\'' . $srcHover[0] . '\') top left no-repeat transparent !important')
                ->add('background-size', '100% auto')
                ->add('width', $srcHover[1] . 'px')
                ->add('height', $srcHover[2] . 'px')
                ->add('min-height', $srcHover[2] . 'px')
                ->add('max-width', '100%');
        }
        // Image ratio protection (this is experimental
        if($experimental){
            // Width / height only if both are the same size
            //img-height / img-width * container-width * 10000
            if(!is_null($img)){
                $ratio = (($src[2] / ($src[1] * 100))) * 10000;
                $css->addRule('body #' . $id.  ' input')
                    ->add('background-size', 'contain !important')
                    ->add('background-repeat', 'no-repeat !important')
                    ->add('width', '100% !important')
                    ->add('height', '0 !important')
                    ->add('padding-top', $ratio . '% !important')
                    ->add('display', 'block !important')
                    ->add('min-height', '0 !important')
                    ->add('max-width', $src[1] . 'px !important');
            }
            if(!is_null($imgHover)){
                $ratio = (($srcHover[2] / ($srcHover[1] * 100))) * 10000;
                $css->addRule('body #' . $id . ' input:hover, ' . '#' . $id . ' input:focus, ' . '#' . $id . ' input:active')
                    ->add('background-size', 'contain !important')
                    ->add('background-repeat', 'no-repeat !important')
                    ->add('width', '100% !important')
                    ->add('height', '0 !important')
                    ->add('padding-top', $ratio . '% !important')
                    ->add('display', 'block !important')
                    ->add('min-height', '0 !important')
                    ->add('max-width', $src[1] . 'px !important');
            }
        }
        // clean up theme styles
        $css->addRule('body #' . $id.  ' input')
            ->add('box-shadow', 'none !important')
            ->add('border', 'none !important')
            ->add('border-radius', '0 !important');

        return $css;
    }
}