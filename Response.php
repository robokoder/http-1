<?php
/* ===========================================================================
 * Opis Project
 * http://opis.io
 * ===========================================================================
 * Copyright 2013 Marius Sarca
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

namespace Opis\Http;

use Closure;
use Opis\Http\ResponseContainerInterface;
use Opis\Http\Cotainer\Stream;
use Opis\Http\Container\File;

class Response
{
  
    /** @var \Opis\Http\Request Request instance. */
    protected $request;
  
    /** @var mixed Response body. */
    protected $body = '';
    
    /** @var string Response content type. */
    protected $contentType = 'text/html';
    
    /** @var string Response charset. */
    protected $charset = 'UTF-8';
    
    /** @var integer Status code. */
    protected $statusCode = 200;
    
    /** @var array Response headers. */
    protected $headers = array();
    
    /** @var array Cookies. */
    protected $cookies = array();
    
    /** @var boolean Compress output? */
    protected $outputCompression;
    
    /** @var boolean Enable response cache? */
    protected $responseCache;
    
    /** @var array Output filters. */
    protected $outputFilters = array();
    
    /** @var array HTTP status codes. */
    protected $statusCodes = array(
        // 1xx Informational
        '100' => 'Continue',
        '101' => 'Switching Protocols',
        '102' => 'Processing',
        // 2xx Success
        '200' => 'OK',
        '201' => 'Created',
        '202' => 'Accepted',
        '203' => 'Non-Authoritative Information',
        '204' => 'No Content',
        '205' => 'Reset Content',
        '206' => 'Partial Content',
        '207' => 'Multi-Status',
        // 3xx Redirection
        '300' => 'Multiple Choices',
        '301' => 'Moved Permanently',
        '302' => 'Found',
        '303' => 'See Other',
        '304' => 'Not Modified',
        '305' => 'Use Proxy',
        //'306' => 'Switch Proxy',
        '307' => 'Temporary Redirect',
        // 4xx Client Error
        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '402' => 'Payment Required',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '405' => 'Method Not Allowed',
        '406' => 'Not Acceptable',
        '407' => 'Proxy Authentication Required',
        '408' => 'Request Timeout',
        '409' => 'Conflict',
        '410' => 'Gone',
        '411' => 'Length Required',
        '412' => 'Precondition Failed',
        '413' => 'Request Entity Too Large',
        '414' => 'Request-URI Too Long',
        '415' => 'Unsupported Media Type',
        '416' => 'Requested Range Not Satisfiable',
        '417' => 'Expectation Failed',
        '418' => 'I\'m a teapot',
        '421' => 'There are too many connections from your internet address',
        '422' => 'Unprocessable Entity',
        '423' => 'Locked',
        '424' => 'Failed Dependency',
        '425' => 'Unordered Collection',
        '426' => 'Upgrade Required',
        '449' => 'Retry With',
        '450' => 'Blocked by Windows Parental Controls',
        // 5xx Server Error
        '500' => 'Internal Server Error',
        '501' => 'Not Implemented',
        '502' => 'Bad Gateway',
        '503' => 'Service Unavailable',
        '504' => 'Gateway Timeout',
        '505' => 'HTTP Version Not Supported',
        '506' => 'Variant Also Negotiates',
        '507' => 'Insufficient Storage',
        '509' => 'Bandwidth Limit Exceeded',
        '510' => 'Not Extended',
        '530' => 'User access denied',
    );
      
    /**
     * Constructor.
     *
     * @access public
     * @param \Opis\Http\Request $request Request instance
     * @param string $body (optional) Response body
     */
    
    public function __construct(Request $request, $body = NULL)
    {
        $this->request = $request;
        $this->body($body);
    }
    
    
    /**
     * Sets the response body.
     *
     * @access public
     * @param mixed $body Response body
     * @return \Opis\Http\Response
     */
    
    public function body($body)
    {
        $this->body = ($body instanceof $this) ? $body->getBody() : $body;
        return $this;
    }
    
    /**
     * Returns the response body.
     *
     * @access public
     * @return mixed
     */
    
    public function getBody()
    {
        return $this->body;
    }
    
    /**
     * Sets the response content type.
     *
     * @access public
     * @param string $contentType Content type
     * @param string $charset (optional) Charset
     * @return \Opis\Http\Response
     */
    
    public function type($contentType, $charset = null)
    {
        $this->contentType = $contentType;
        
        if($charset !== null)
        {
            $this->charset = $charset;
        }
        
        return $this;
    }
    
    /**
     * Sets the response charset.
     *
     * @access public
     * @param string $charset Charset
     * @return \Opis\Http\Response
     */
    
    public function charset($charset)
    {
        $this->charset = $charset;
        return $this;
    }
    
    
    /**
     * Sets the HTTP status code.
     *
     * @access public
     * @param int $statusCode HTTP status code
     * @return \Opis\Http\Response
     */
    
    public function status($statusCode)
    {
        if(isset($this->statusCodes[$statusCode]))
        {
            $this->statusCode = $statusCode;
        }
        return $this;
    }
    
    /**
     * Adds output filter that all output will be passed through before being sent.
     *
     * @access public
     * @param \Closure $filter Closure used to filter output
     * @return \Opis\Http\Response
     */
    
    public function filter(Closure $filter)
    {
        $this->outputFilters[] = $filter;
        return $this;
    }
    
    /**
     * Clears all output filters.
     *
     * @access public
     * @return \Opis\Http\Response
     */
    
    public function clearFilters()
    {
        $this->outputFilters = array();
        return $this;
    }
    
    
    /**
     * Sets a response header.
     *
     * @access public
     * @param string $name Header name
     * @param string $value Header value
     * @return \Opis\Http\Response
     */
    
    public function header($name, $value)
    {
        $this->headers[strtolower($name)] = $value;
        return $this;
    }
    
    
    /**
     * Clear the response headers.
     *
     * @access public
     * @return \Opis\Http\Response
     */
    
    public function clearHeaders()
    {
        $this->headers = array();
        return $this;
    }
    
    /**
     * Sets a cookie.
     *
     * @access public
     * @param string $name Cookie name
     * @param string $value Cookie value
     * @param int $ttl (optional) Time to live - if omitted or set to 0 the cookie will expire when the browser closes
     * @param array $options (optional) Cookie options
     * @return \Opis\Http\Response
     */
    
    public function cookie($name, $value, $ttl = 0, array $options = array())
    {
        $ttl = ($ttl > 0) ? (time() + $ttl) : 0;
        $defaults = array('path' => '/', 'domain' => '', 'secure' => false, 'httponly' => false);
        $this->cookies[] = array('name' => $name, 'value' => $value, 'ttl' => $ttl) + $options + $defaults;
        return $this;
    }
    
    
    /**
     * Deletes a cookie.
     *
     * @access public
     * @param string $name Cookie name
     * @param array $options (optional) Cookie options
     * @return \Opis\Http\Response
     */
    
    public function deleteCookie($name, array $options = array())
    {
        return $this->cookie($name, '', time() - 3600, $options);
    }
    
    /**
     * Clear cookies.
     *
     * @access public
     * @return \Opis\Http\Response
     */
    
    public function clearCookies()
    {
        $this->cookies = array();
        return $this;
    }
    
    /**
     * Sends response headers.
     *
     * @access protected
     */
    
    protected function sendHeaders()
    {
        // Send status header
        if($this->request->server('FCGI_SERVER_VERSION', false) !== false)
        {
            $protocol = 'Status:';
        }
        else
        {
            $protocol = $this->request->server('SERVER_PROTOCOL', 'HTTP/1.1');
        }
        
        header($protocol . ' ' . $this->statusCode . ' ' . $this->statusCodes[$this->statusCode]);
        // Send content type header
        $contentType = $this->contentType;
        if(stripos($contentType, 'text/') === 0 || in_array($contentType, array('application/json', 'application/xml')))
        {
            $contentType .= '; charset=' . $this->charset;
        }
        header('Content-Type: ' . $contentType);
        // Send other headers
        foreach($this->headers as $name => $value)
        {
            header($name . ': ' . $value);
        }
        // Send cookie headers
        foreach($this->cookies as $cookie)
        {
            setcookie($cookie['name'], $cookie['value'], $cookie['ttl'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
        }
    }
        
    /**
     * Enables ETag response cache.
     *
     * @access public
     * @return \mako\http\Response
     */
    
    public function cache($value = true)
    {
        $this->responseCache = $value;
        return $this;
    }
    
    /**
     * Enables output compression.
     *
     * @access public
     * @return \mako\http\Response
     */
    
    public function compress($value = true)
    {
        $this->outputCompression = $value;
        return $this;
    }
    
    
    /**
     * Returns a stream container.
     *
     * @access public
     * @param \Closure $stream Stream
     * @return \Opis\Http\StreamContainer
     */
    
    public function stream(Closure $stream)
    {
        return new Stream($stream);
    }
    
    /**
     * Return a file container
     *
     * @access public
     * 
     */
    
    public function file($file, array $options = array())
    {
        return new File($file, $options);
    }
        
        
    /**
     * Redirects to another location.
     *
     * @access public
     * @param string $location (optional) Location
     * @param int $statusCode (optional) HTTP status code
     */
    
    public function redirect($location = '', $statusCode = 302)
    {
        
        $this->status($statusCode);
        
        $this->header('Location', $location);
        
        $this->sendHeaders();
        
        exit();
    }
    
    /**
     * Redirects the user back to the previous page.
     *
     * @access public
     * @param int $statusCode (optional) HTTP status code
     */
    
    public function back($statusCode = 302)
    {
        $this->redirect($this->request->referer(), $statusCode);
    }
    
    /**
     * Send output to browser.
     *
     * @access public
     */
    
    public function send()
    {
        if($this->body instanceof ResponseContainerInterface)
        {
            $this->body->send($this->request, $this);
        }
        else
        {
            $sendBody = true;
            // Make sure that output buffering is enabled
            if(ob_get_level() === 0)
            {
                ob_start();
            }
            // Run body through the response filters
            foreach($this->outputFilters as $outputFilter)
            {
                $this->body = $outputFilter($this->body);
            }
            // Check ETag if response cache is enabled
            if($this->responseCache === true)
            {
                $hash = '"' . sha1($this->body) . '"';
                $this->header('ETag', $hash);
                if($this->request->header('if-none-match') === $hash)
                {
                    $this->status(304);
                    $sendBody = false;
                }
            }
            
            if($sendBody && !in_array($this->statusCode, array(100, 101, 102, 204, 304)))
            {
                // Start compressed output buffering if output compression is enabled
                if($this->outputCompression)
                {
                    ob_start('ob_gzhandler');
                }
                echo $this->body;
                // If output compression is enabled then we'll have to flush the compressed buffer
                // so that we can get the compressed content length when setting the content-length header
                if($this->outputCompression)
                {
                    ob_end_flush();
                }
                // Add the content-length header
                if(!array_key_exists('transfer-encoding', $this->headers))
                {
                    $this->header('content-length', ob_get_length());
                }
            }
            // Send the headers and flush the output buffer
            $this->sendHeaders();
            ob_end_flush();
        }
    }
    
}