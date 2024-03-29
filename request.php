<?php

namespace ShadowHost\Cloak;

/**
 * Contains request and response data.
 *
 * If UA is empty, it will choose random UA from
 *   \ShadowHost\Cloak\Request::$userAgentList
 * You can modify it freely.
 *
 * @module     ShadowHost Cloak
 * @author     Daniel Sevcik <sevcik@webdevelopers.cz>
 * @copyright  2019 Daniel Sevcik
 * @since      2019-05-29 12:01:48 UTC
 * @access     public
 */
class Request {
    /**
     * Use this SOCKS proxy server.
     *
     * @access public
     * @var \ShadowHost\Cloak\Server
     */
    public $proxy;

    /**
     * CURL configured resource generated by curl_init() during call to Proxy::exec() or undefined
     *
     * @access public
     * @var resource
     */
    public $curlHandle;

    /**
     * CURL error code.
     * @access public
     * @var int 0: OK, other codes see https://curl.haxx.se/libcurl/c/libcurl-errors.html
     */
    public $curlStatus;

    /**
     * Human readable error description for $this->curlStatus
     * @access public
     * @var string
     */
    public $curlMessage;

    /**
     * HTTP request method.
     *
     * @access public
     * @var string
     */
    public $method="GET";

    /**
     * Request URL.
     *
     * @access public
     * @var string
     */
    public $url;

    /**
     * Post data.
     *
     * @access public
     * @var mixed string or array
     */
    public $data;

    /**
     * Request user-agent string.
     * If empty random UA will be chosen from
     * $this->userAgentList.
     *
     * @access public
     * @var string
     */
    private $userAgent;

    /**
     * The raw response HTTP head section.
     * @access public
     * @var string
     */
    public $responseHead;

    /**
     * Contains parsed information from HTTP head section.
     * @access public
     * @var array (@status, @code, @protocol, @message, lower-case(header-name), ...)
     */
    public $responseHeaders=array();

    /**
     * The raw response HTTP body section - without headers.
     * @access public
     * @var text
     */
    public $responseBody;

    /**
     * The raw response content including all headers.
     * @access public
     * @var string
     */
    private $responseRaw;

    /**
     * The raw response HTTP proxy-generated head section.
     * @access public
     * @var string
     */
    public $proxyHead;

    /**
     * Contains parsed information from PROXY-generated HTTP head section.
     * @access public
     * @var array (@status, @code, @protocol, @message, lower-case(header-name), ...)
     */
    public $proxyHeaders=array();


    /**
     * List of UA strings to choose when $this->userAgent is empty.
     *
     * You can manipulate the list globally using
     * \ShadowHost\Cloak\Request::$userAgentList
     *
     * @access public
     * @var array
     */
    static public $userAgentList=array(
        "facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)",
        "FAST MetaWeb Crawler (helpdesk at fastsearch dot com)",
        "Googlebot/2.1 (+http://www.google.com/bot.html)",
        "Googlebot-Image/1.0",
        "ia_archiver",
        "Mediapartners-Google",
        "Mozilla/4.0 (compatible; MSIE 5.0; Windows 95) VoilaBot BETA 1.2 (http://www.voila.com/)",
        "Mozilla/5.0 (compatible; AhrefsBot/5.2; +http://ahrefs.com/robot/)",
        "Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)",
        "Mozilla/5.0 (compatible; BecomeBot/3.0; +http://www.become.com/site_owners.html)",
        "Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)",
        "Mozilla/5.0 (compatible; DuckDuckGo-Favicons-Bot/1.0; +http://duckduckgo.com)",
        "Mozilla/5.0 (compatible; Exabot/3.0; +http://www.exabot.com/go/robot)",
        "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)",
        "Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)",
        "Mozilla/5.0 (iPad; CPU OS 12_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0 Mobile/15E148 Safari/604.1",
        "Mozilla/5.0 (iPhone; CPU iPhone OS 8_3 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12F70 Safari/600.1.4 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)",
        "Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.96 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.80 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_1) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0.1 Safari/605.1.15",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.80 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_2) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0.2 Safari/605.1.15",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0 Safari/605.1.15",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.14; rv:63.0) Gecko/20100101 Firefox/63.0",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.14; rv:64.0) Gecko/20100101 Firefox/64.0",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.80 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:64.0) Gecko/20100101 Firefox/64.0",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko/20100101 Firefox/65.0",
        "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:63.0) Gecko/20100101 Firefox/63.0",
        "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; rv:63.0) Gecko/20100101 Firefox/63.0",
        "Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko",
        "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.80 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:64.0) Gecko/20100101 Firefox/64.0",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 YaBrowser/18.10.2.163 Yowser/2.5 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:64.0) Gecko/20100101 Firefox/64.0",
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.80 Safari/537.36",
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36",
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/70.0.3538.77 Chrome/70.0.3538.77 Safari/537.36",
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/71.0.3578.80 Chrome/71.0.3578.80 Safari/537.36",
        "Mozilla/5.0 (X11; Linux x86_64; rv:64.0) Gecko/20100101 Firefox/64.0",
        "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:64.0) Gecko/20100101 Firefox/64.0",
        "msnbot/1.1 (+http://search.msn.com/msnbot.htm)",
        "msnbot/2.0b (+http://search.msn.com/msnbot.htm)",
        "msnbot-media/1.1 (+http://search.msn.com/msnbot.htm)",
        "Pinterest/0.2 (+http://www.pinterest.com/)",
        "Sogou web spider/4.0(+http://www.sogou.com/docs/help/webmasters.htm#07)",
    );

    /**
     * RegExp pattern to parse status line.
     * @access private
     * @var string
     */
    const STATUS_LINE_PATTERN='@^HTTP/(?<protocol>[0-9.]+)\s+(?<code>\d+)(?:\s+(?<message>.*))?$@m';

    /**
     * Constructor
     *
     * @access public
     * @param string $method Optional HTTP request method.
     * @param string $url Optional HTTP URL to make request to.
     * @param mixed $data Optional data for POST requests.
     * @param string $userAgent Optional user-agent string.
     * @return void
     */
    public function __construct($method=null, $url=null, $data=null, $userAgent=null) {
        if ($method) $this->method=$method;
        if ($url) $this->url=$url;
        if ($data) $this->data=$data;
        if ($userAgent) $this->userAgent=$userAgent;
    }

    /**
     * Parse HTTP HEAD into array of key-values.
     *
     * @access private
     * @param string $text the HTTP head part
     * @return array (@status, @code, @protocol, @message, lower-case(header-name), ...)
     */
    private function parseHttpHead($text) {
        $headers=array();
        $key=false;

        if (!strlen(trim($text))) return $headers;

        foreach (explode("\r\n", trim($text)) as $i => $line) {
            // Special HTTP first line
            if (!$i && preg_match(self::STATUS_LINE_PATTERN, $line, $match)) {
                $headers['@status']=$line;
                $headers['@code']=$match['code'];
                $headers['@protocol']=$match['protocol'];
                $headers['@message']=$match['message'];
                continue;
            }

            // Multiline header - join with previous
            if ($key && preg_match('/^\s/', $line)) {
                $headers[$key].=' '.trim($line);
                continue;
            }

            @list($key, $value)=explode(': ', $line, 2);
            $key=strtolower($key ?: '@line'.$i);

            // Append duplicate headers - namely Set-Cookie header
            $headers[$key]=isset($headers[$key]) ? $headers[$key].' ' : $value;
        }

        return $headers;
    }


    public function __get($prop) {
        switch ($prop) {
        case 'userAgent':
            if ($this->userAgent) {
                return $this->userAgent;
            } else {
                if ($this->proxy) { // Let's make random UA PROXY-sticky for a day
                    $key=abs(crc32((string) $this->proxy) + floor(time() / 86400)) % count(self::$userAgentList);
                } else { // really random
                    $key=array_rand(self::$userAgentList);
                }
                return self::$userAgentList[$key];
            }
        default:
            return $this->$prop;
        }
    }

    public function __set($prop, $val) {
        switch ($prop) {
        case 'responseRaw':
            $this->responseRaw=$val;

            // Order is: PROXY HEAD, SERVER HEAD, SERVER BODY
            @list($this->proxyHead, $this->responseHead, $this->responseBody)=explode("\r\n\r\n", $val, 3);

            // FIX: Some proxies don't append their own headers: order is SERVER HEAD, SERVER BODY
            if (!preg_match(self::STATUS_LINE_PATTERN, $this->responseHead)) {
                $this->proxyHead='';
                @list($this->responseHead, $this->responseBody)=explode("\r\n\r\n", $val, 2);
            }

            $this->proxyHeaders=$this->parseHttpHead($this->proxyHead);
            $this->responseHeaders=$this->parseHttpHead($this->responseHead);
            break;
        default:
            $this->$prop=$val;
        }
        return $this->$prop;
    }


    public function __toString() {
        $parts=array();
        $parts[]="CURL status ".$this->curlStatus;
        if (isset($this->responseHeaders['@code'])) {
            $parts[]= 'HTTP status '.(int) $this->responseHeaders['@code'];
        }
        $parts[]="Proxy ".$this->proxy;
        $parts[]=$this->url;

        return "Request[".implode(", ", $parts)."]";
    }
}
