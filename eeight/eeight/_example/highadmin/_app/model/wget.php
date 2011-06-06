<?php

    /**
     *
     */
    class model_wget
    {

        var $_debug = 0;

        var $fp;
        var $timeOut = 60;
        var $errstr;
        var $randDelay = 0;
        
        var $url;
        var $port;

        var $inCharset;
        var $outCharset;
        var $charset;

        var $postHead;
        // 响应
        var $responseHead;
        var $responseBody;
        var $_prop; // todo:应去掉这个属性

        public function __construct($inCharset = 'auto', $outCharset = 'utf-8')
        {
            $this->fp = 0;
            $this->errstr = null;
            $this->url = null;
            $this->postHead = array();
            $this->responseHead = $this->responseBody = "";
            $this->_prop = array( "cookie"=>array());
            $this->charset = array( 'auto', 'gb2312', 'gbk', 'gb18030', 'utf-8', 'utf-16', 'UCS-2', 'iso-8859-1' );

            $this->setInCharset($inCharset);
            $this->setOutCharset($outCharset);
        }

        private function connect( $url, $port = 0, $timeOut = 0 )
        {
            if( !$parts = parse_url( $url ))
                return false;
            if( !$port ) {
                $port = $this->port;
            }
            if( !$timeOut ) {
                $timeOut = $this->timeOut;
            }
            if( !$this->fp = @fsockopen( $parts['host'], $port, $errno, $errstr, $timeOut )) {
                echo "连接主机 ".$parts['host']." 失败！";
            }
            // verify we connected properly
            if( empty( $this->fp )) {
                $this->errstr = "failed to connect to server: $errstr";
                return false;
            }
            if( $this->_debug >= 3 ) {
                $this->fp = fopen( "header.txt", "w" );
            }
            return true;
        }

        public function fetchContent( $fetch_page_method, $url, $port = 80, $method = "GET", $referer = "", $cookie = "", $post = "", $userDefineHeader = "" )
        {
            $this->reset();
            if ($this->randDelay > 0) {
                sleep($this->getRandDelay());
            }
            
            if( $fetch_page_method == "FILE" ) {
                $flag = $this->fetchByReadFile( $url );
            }
            elseif( $fetch_page_method == "SOCKET" ) {
                $flag = $this->fetchBySocket( $url, $port, $method, $referer, $cookie, $post, $userDefineHeader );
            }
            else {
                return false;
            }

            if( $this->inCharset == "auto" ) {
                $response = $this->getResponseHeadAsArray();
                if (!empty($response['Content-Type']) && preg_match( "`charset=(.*)$`Uim", $response['Content-Type'], $match))
                {
                    //text/html; charset=utf-8
                    $currentCharset = strtolower(trim($match[1]));
                }
                elseif (preg_match("`<meta.*charset\s*=(.+)['\"].*>`Uim", $this->responseBody, $match))
                {
                    $currentCharset = strtolower(trim($match[1]));
                }
                else
                {
                    echo 'Unrecognizable Charset';
                    $currentCharset = 'utf-8';
                }
            }
            else {
                $currentCharset = $this->inCharset;
            }

            if( in_array( $currentCharset, $this->charset ) && $currentCharset != $this->outCharset ) {
                $this->responseBody = mb_convert_encoding( $this->responseBody, $this->outCharset, $currentCharset );
            }
            
            return $flag;
        }

        private function fetchByReadFile( $url ) {
            if( !$this->responseBody = file_get_contents( $url )) {
                return false;
            }
            return true;
        }

        // The method must be uppercase instead of lowercase, or it will can't transfer the post data  !!!
        private function fetchBySocket( $url, $port = 80, $method = "GET", $referer = "", $cookie = "", $post = "", $userDefineHeader = "" )
        {
            $this->responseHead = $this->responseBody = "";
            $this->postHead = array();
            $parts = parse_url( $url );
            if( !$this->isConnected()) {
                if( !$this->connect( $url, $port )) {
                    $this->errstr = "Called fetch() without being connected";
                    return false;
                }
            }
            
            if (isset($parts['query']))
            {
                $path = $parts['path']."?".$parts['query'];
            }
            else
            {
                $path = $parts['path'];
            }

            // 发送http头。。。
			array_push( $this->postHead, $method." $path HTTP/1.1\n" );
            array_push( $this->postHead, "Host: ".$parts['host']."\n" );
            array_push( $this->postHead, "User-Agent: Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0)\n" );
			array_push( $this->postHead, "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\n" );
            array_push( $this->postHead, "Accept-Language: en-us,en;q=0.5\n" );
			//array_push( $this->postHead, "Accept-Encoding: gzip,deflate\n" );
            array_push( $this->postHead, "Accept-Charset: UTF-8,*\n" );
			// 注意：如果这里用 Keep-Alive 将不能读娶某些网站例如163.com
			//array_push( $this->postHead, "Keep-Alive: 115\n" );
			array_push( $this->postHead, "Connection: close\n" );	//	Keep-Alive
			if( $referer ) {
				if( $referer == "homepage" ) {
					$referer = $parts['scheme']."://".$parts['host'];
				}
                array_push( $this->postHead, "Referer: ".$referer."\n" );	// 设置入口页面
			}
            if ($userDefineHeader) {
                array_push( $this->postHead, "$userDefineHeader\n" );
            }
            if( !empty( $cookie )) {
				array_push( $this->postHead, "Cookie: $cookie\n" );
        	}
            array_push( $this->postHead, "Pragma: no-cache\n" );
            array_push( $this->postHead, "Cache-Control: no-cache\n" );

            if( $method == "POST" ) {
                array_push( $this->postHead, "Content-Type: application/x-www-form-urlencoded\n" );
                array_push( $this->postHead, "Content-length: ".strlen( $post ));   // require
            }

            array_push( $this->postHead, "\n\n" );
            if( !empty( $post )) {
                array_push( $this->postHead, $post );
            }
            foreach( $this->postHead as $k => $v ) {
                fputs( $this->fp, $v );
            }

            stream_set_timeout($this->fp, $this->timeOut);
            while( !feof( $this->fp )) {
                $line = fgets( $this->fp, 8092 );
                $this->responseHead .= $line;
                if( $line == "\r\n" )
                    break;
            }
            $this->parseResponseHeader();
            if( $this->getHeader( "status" ) != 200 && $this->_debug < 3 ) {
                $this->errstr = "the page ‘".$parts['host'].$parts['path']."’ can't be found !";
                $this->responseBody = "";
                $this->close();
                return false;
            }
            
            $this->responseBody = stream_get_contents($this->fp);
            $this->close();
            return true;
        }

        private function parseResponseHeader()
        {
            $arr = explode( "\n", $this->responseHead );
            array_pop( $arr );array_pop( $arr );
            $statusLine = array_shift( $arr );
            preg_match( "@http/1\.\d+\s(\d{3})\s\w+@i", $statusLine, $matches );
            $this->setHeader( "status", $matches[1] );
            foreach( $arr as $k => $v ) {
                $tmp = explode( ":", $v );
                if( !stristr( $tmp[0], "cookie" )) {
                    $this->setHeader( trim( $tmp[0] ), trim( $tmp[1] ));
                }
                else {
                    $tmp = explode( ";", $tmp[1] );
                    foreach( $tmp as $kk => $vv ) {
                        $tmp2 = explode( "=", $vv );
                        $this->setHeader( trim( $tmp2[0] ), trim( isset($tmp2[1]) ? $tmp2[1] : "" ), true );
                    }
                }
            }
            return true;
        }

        public function setHeader( $key, $value, $isCookie = false )
        {
            if( $isCookie ) {
                $this->_prop['cookie'][$key] = $value;
            }
            else {
                $this->_prop[$key] = $value;
            }
        }

        public function getHeader( $key, $defaultValue = null )
        {
            $value = $this->_prop[$key];
            if( $value == "" || $value == null )
                if( $defaulValue != null )
                    $value = $defaultValue;

            return $value;
        }

        private function reset()
        {
            $this->responseHead = '';
            $this->responseBody = '';
            $this->_prop = array();
        }

        public function getBody()
        {
            return $this->responseBody;
        }

        public function getResponseHeadAsArray()
        {
            return $this->_prop;
        }

        // 返回头 这里不应该用两个成员变量来表示同一种数据
        public function getResponseHeadAsString()
        {
            return $this->responseHead;
        }

        public function getPostHeadAsArray()
        {
            return $this->postHead;
        }

        public function getPostHeadAsString()
        {
            return implode("", $this->postHead);
        }

        private function isConnected()
        {
            if( !empty( $this->fp )) {
                //$sock_status = socket_get_status($this->fp);
                //if($sock_status["eof"])
                if( feof( $this->fp )) {
                    $this->Close();
                }
                return true;    // everything looks good
            }
            return false;
        }

        public function close()
        {
            $this->errstr = null;    // so there is no confusion
            $this->url = null;
            if( !empty( $this->fp )) {
                // close the connection and cleanup
                fclose( $this->fp );
                $this->fp = null;
            }
        }
        
        public function setRandDelay($sec = 0)
        {
            $this->randDelay = $sec;
        }

        public function getRandDelay()
        {
            return rand(0, $this->randDelay);
        }

        public function setInCharset( $charset )
        {
            if( in_array( $charset, $this->charset ))
                $this->inCharset = $charset;
            else
                $this->inCharset = "utf-8";
        }

        public function setOutCharset( $charset )
        {
            if( in_array( $charset, $this->charset ))
                $this->outCharset = $charset;
            else
                $this->outCharset = "utf-8";
        }

    }
?>