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


class Http
{
    /** @var  */
    var $response;
    /** @var array */
    var $args = array('sslverify' => false, 'timeout' => 120);
    /** @var  */
    var $url;


    /**
     * Concstructor
     *
     * @param null $url
     */
    public function __construct($url = null)
    {
        $this->url = $url;
        return $this;
    }


    /**
     * @param array $args
     * @return $this
     */
    public function setArgs(array $args = array())
    {
        $this->args = $args;
        return $this;
    }


    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url = '')
    {
        $this->url = $url;
        return $this;
    }


    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body = '')
    {
        $this->args['body'] = $body;
        return $this;
    }

    /**
     * @return $this
     * @throws HttpException
     */
    public function get()
    {
        $this->response = \wp_remote_get($this->url, $this->args);
        $this->check();
        return $this;
    }

    /**
     * @param null $body
     * @param string $method
     * @throws HttpException
     */
    public function post($body = null, $method = 'POST')
    {
        // content type need for correct API resopnse
        $defaults = array(
            'method' => $method,
            'timeout' => 120,
            'body'   => $body,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Content-Length' => strlen($body)
            ),
        );
        // go my man
        $this->response = \wp_remote_post($this->url, array_merge($defaults, $this->args));
        $this->check();
    }


    /**
     * @param null $body
     */
    public function put($body = null)
    {
        $this->post($body, 'PUT');
    }


    /**
     * @throws HttpException
     */
    public function head()
    {
        $this->response = \wp_remote_head($this->url, $this->args);
        $this->check();
    }


    /**
     * Check's response after operation
     *
     * @throws HttpException
     */
    private function check()
    {
        if (is_wp_error($this->response)){
            throw new HttpException($this->response->get_error_message());
        }
        return;
    }


    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response['response'];
    }


    /**
     * Response code
     *
     * @return mixed
     */
    public function getResponseCode()
    {
        return $this->response['response']['code'];
    }


    /**
     * Get response body
     *
     * @return mixed
     */
    public function getBody()
    {
        return $this->response['body'];
    }


    /**
     * Reset
     */
    public function reset()
    {
        $this->response = '';
        $this->args = array();
        $this->url= '';
    }
}


class HttpException extends \Exception{}