<?php
namespace cryptmail\util;

class HttpNavigator
{
    protected $cookies = array();
    protected $base_url;
    protected $user_agent = self::FIREFOX4;
    protected $error = "No error occured yet!";
    protected $referer = "www.google.de";
    protected $send_headers = array();

    // data about the last request
    protected $returns = "";
    protected $header = "404 Not Found";
    protected $content_type = "text/html";
    protected $statuscode = 404;
    protected $statustext = "Not Found";
    protected $charset = "utf-8";

    const FIREFOX4="Mozilla/5.0 (Windows NT 6.1; rv:2.0) Gecko/20100101 Firefox/4.0";
    const FIREFOX3="Mozilla/5.0 (Windows NT 6.1; rv:2.0) Gecko/20100101 Firefox/3.6";
    const SAFARI5="Mozilla/5.0 (Windows; U; Windows NT 6.0; de-DE) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27";
    const SAFARI4="Mozilla/5.0 (Windows; U; Windows NT 6.0; de-DE) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16";
    const IE9="Mozilla/5.0 (Windows; U; MSIE 9.0; WIndows NT 9.0; de-DE))";
    const IE8="Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.2; Trident/4.0; Media Center PC 4.0; SLCC1; .NET CLR 3.0.04320)";
    const IE7="Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 6.0; de-DE)";
    const CHROME="Mozilla/5.0 (Windows NT 5.1) AppleWebKit/534.25 (KHTML, like Gecko) Chrome/12.0.706.0 Safari/534.25";
    const OPERA11="Opera/9.80 (Windows NT 6.0; U; de) Presto/2.8.99 Version/11.10";
    const OPERA10="Opera/9.80 (X11; Linux i686; U; de) Presto/2.5.24 Version/10.54";
    const OPERA9="Opera 9.7 (Windows NT 5.2; U; de)";

    public function __construct($base_url)
    {
        if (substr($base_url, strlen($base_url)-1, 1) == "/")
        {
            $base_url .= substr($base_url, 0, strlen($base_url)-1);
        }
        if (strToLower(substr($base_url, 0, 7))=="http://")
        {
            $base_url=substr($base_url, 7);
        }
        
        $this->base_url = $base_url;
        ini_set("default_socket_timeout", 2);
    }
    
    public function setHeaders(array $h)
    {
        $this->send_headers = $h;
    }
    
    public function setHeader($key, $value)
    {
        $this->send_headers[$key] = $value;
    }
    
    public function setCookie($key, $value=null)
    {
        $this->cookies[$key] = $value;
    }
    
    public function getCookies()
    {
        return $this->cookies;
    }
    
    public function setUserAgent($agent)
    {
            $this->user_agent=$agent;
    }
    
    public function browseTo($sub_url, $method = "GET", $data = array())
    {
        if ($method != "GET" && $method!="POST" && $method != "HEAD")
        {
            throw new Exception("Unknown Method '".$method."'");
        }
        if (!empty($data) && $method == "GET")
        {
            $sub_url .= "?";
            foreach ($data as $k => $v)
            {
                $sub_url .= urlencode($k). '=' .urlencode($v) . '&';
            }
            // cut off the trailing & or ?
            $sub_url = substr($sub_url, 0, strlen($sub_url) - 1);
        }
        
        $request = $method ." ". $sub_url . " HTTP/1.1\r\n"
            ."Host: " . $this->base_url . "\r\n"
            ."User-Agent: " . $this->user_agent . "\r\n";
        
        if (!empty($this->cookies))
        {
            $request .= "Cookies: ";
            $i = 0;
            $j = count($this->cookies)-1;
            foreach ($this->cookies as $name=>$value)
            {
                $request .= $name. "=" .$value;
                if ($i != $j)
                {
                    $request .= ";";
                }
                $i++;
            }
            $request .= "\r\n";
        }
        
        $request .= "Accept: text/html,application/xhtml+xml,application/xml\r\n"
            ."Accept-Charset: utf-8,ISO-8859\r\n"
            ."Referer: " . $this->referer . "\r\n"
            ."Connection: close\r\n";
        
        // append user headers
        foreach ($this->send_headers as $key => $value)
        {
            $request .= $key . ": " . $value . "\r\n";
        }
        
        if ($method == "POST" && !empty($data))
        {
            $data = "";
            $i = 0;
            $j = count($data)-1;
            foreach ($data as $k=>$v)
            {
                $data .= urlencode($k) ."=". urlencode($v);
                if ($i != $j)
                {
                    $data .= "&";
                }
                $i++;
            }
            $request .= "Content-Encoding: identity\r\n"
                ."Content-Length: " . strlen($data) . "\r\n\n" . $data;
        }
        else
        {
            $request .= "\r\n";
        }
        $socket = fsockopen($this->base_url, 80, $errno, $errstr, 20);
        if (!$socket)
        {
            $this->error = $errstr." (Level " . $errno . ")";
            return false;
        }
        fwrite($socket, $request);
        $response = "";
        stream_set_blocking($socket, false);
        stream_set_timeout($socket, 2);
        while (!feof($socket))
        {
            // 1KB Buffer
            $response .= fgets($socket, 1024);
        }
        fclose($socket);
        $this->referer = $this->base_url . $sub_url;
        $pos=strpos($response, "\r\n\r\n");
        $this->returns = substr($response, $pos + 2);
        $header = explode("\n", substr($response, 0, $pos));
        $this->header = $header;
        $i = true;
        foreach ($header as $item)
        {
            $item = explode(":", $item);
            $key = strtolower(trim($item[0]));
            if (isset($item[1]))
            {
                $value = trim($item[1]);
            }
            // Status
            if ($i)
            {
                $v = trim(substr($key, strpos($key, " ")));
                $pos = strpos($v, " ");
                $a = substr($v, 0, $pos);
                $b = trim(substr($v, $pos+1));
                if ("" . intval($a) == $a)
                {
                    $this->statuscode=intval($a);
                    $this->statustext=strToUpper($b);
                }
                else
                {
                    $this->statuscode=intval($b);
                    $this->statustext=strToUpper($a);
                }
            }
            if ($key == "set-cookie")
            {
                $x = explode(";", trim($value));
                foreach ($x as $cname => $cval)
                {
                    $cname = trim($cname);
                    if ( $cname == "expires" || $cname == "path")
                    {
                        continue;
                    }
                    $this->cookie[$cname] = trim($cval);
                }
            }
            if ($key=="content-type")
            {
                if ($pos = strpos($value, ";"))
                {
                    $this->content_type = strToLower(trim(substr($value, 0, $pos)));
                    $v2 = trim(substr($value, $pos));
                    if (substr($v2, 0, 8) == "charset=")
                    {
                        $this->charset = trim(substr($v2, 8));
                    }
                }
                else
                {
                    $this->content_type = strToLower($value);
                    $this->charset = "indentify";
                }
            }
            $i = false;
        }
        return true;
    }
    
    public function getRequestInfo()
    {
        return array(
            "status" => $this->statuscode." ".$this->statustext,
            "statuscode" => $this->statuscode,
            "statustext" => $this->statustext,
            "type" => $this->content_type,
            "charset" => $this->charset
        );
    }
    
    public function getRequestHeader()
    {
        return $this->header;
    }
    
    public function getOutput()
    {
        return $this->returns;
    }
}
?>