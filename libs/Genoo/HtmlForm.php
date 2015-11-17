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


class HtmlForm
{
    /** @var string */
    private $html;
    /** @var \DOMDocument */
    private $dom;
    /** @var  */
    private $form;


    /**
     * Constructor
     *
     * @param $html
     */

    public function __construct($html)
    {
        // suppress warnings of invalid html
        libxml_use_internal_errors(true);
        // prep
        $this->html = $html;
        $this->dom = new \DOMDocument;
        $this->dom->loadHTML($this->html);
        $this->dom->preserveWhiteSpace = false;
        // html
        $this->form = $this->dom->getElementsByTagName("form")->item(0);
        $this->msg = $this->dom->getElementById("genooMsg");
    }


    /**
     * Append hidden input
     *
     * @param array $array
     * @return Html
     */

    public function appendHiddenInputs(array $array = array())
    {
        if($array){
            foreach($array as $key => $value){
                $node = $this->dom->createElement("input");
                $node->setAttribute("type","hidden");
                $node->setAttribute("name", $key);
                $node->setAttribute("value", $value);
                if(!empty($this->form)){
                    $this->form->insertBefore($node, $this->form->childNodes->item(0));
                }
            }
        }
        return $this;
    }


    /**
     * Append Message
     *
     * @param string $msg
     * @param bool $err
     */

    public function appendMsg($msg = '', $err = false)
    {
        $html = '';
        if(!empty($msg)){
            $strongClass = $err == true ? 'genooSucess' : 'genooError fielderror';
            // remove form if succes
            if($err == true){
                if(!empty($this->form)){
                    if($this->form->parentNode){
                        $this->form->parentNode->removeChild($this->form);
                    } else {
                        // No form parent
                    }
                }
            }
            if(!empty($this->msg)){
                // html
                $html .= '<strong class="'.$strongClass.'">' . strip_tags($msg, '<br><br/>') . '</strong>'; //htmlspecialchars
                $fragment = $this->dom->createDocumentFragment();
                $fragment->appendXML($html);
                $this->msg->appendChild($fragment);
            }
        }
    }


    /**
     * Cleared from doctype, html, body
     *
     * @return mixed
     */

    public function __toString()
    {
        return preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $this->dom->saveHTML());
    }


    /**
     * Destructor to clean errors.
     */

    public function __destruct(){ libxml_clear_errors(); }
}