<?php

/***************************************************************
* Live Admin Standalone
* Copyright 2008-2011 Dayana Networks Ltd.
* All rights reserved, Live Admin  is  protected  by  Canada and
* International copyright laws. Unauthorized use or distribution
* of  Live Admin  is  strictly  prohibited,  violators  will  be
* prosecuted. To  obtain  a license for using Live Admin, please
* register at http://www.liveadmin.net/register.php
*
* For more information please refer to Live Admin official site:
*    http://www.liveadmin.net
*
* Translation service provided by Google Inc.
***************************************************************/






 if(!defined('LIVEADMIN')) exit; class LV_Browser { private $props = array( "Version" => "0.0.0", "Name" => "unknown", "Agent" => "unknown") ; public function __Construct() { $browsers = array("firefox", "msie", "opera", "chrome", "safari", "mozilla", "seamonkey", "konqueror", "netscape", "gecko", "navigator", "mosaic", "lynx", "amaya", "omniweb", "avant", "camino", "flock", "aol"); $this->Agent = strtolower($_SERVER['HTTP_USER_AGENT']); foreach($browsers as $browser) { if (preg_match("#($browser)[/ ]?([0-9.]*)#", $this->Agent, $match)) { $this->Name = $match[1] ; $this->Version = $match[2] ; break ; } } } public function __Get($name) { if (!array_key_exists($name, $this->props)) return("") ; return $this->props[$name] ; } public function __Set($name, $val) { if (!array_key_exists($name, $this->props)) return; $this->props[$name] = $val ; } } ?>