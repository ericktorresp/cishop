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






 if(!defined('LIVEADMIN')) exit; class LV_Lang { var $lang; var $lang_default; var $lang_ar; function LV_Lang($lang_in) { $this->lang = strtolower($lang_in); $this->lang_default = 'english'; $this->lang_ar = $this->GetLangFile(); } function GetLangFile() { $this->GetLangArray(LIVEADMIN_L.'/'.$this->lang_default.'.rtf',$L_DEFAULT_B,$L_DEFAULT_S); if(!is_file(LIVEADMIN_L.'/'.$this->lang.'.rtf') || $this->lang == $this->lang_default) return(array('base'=>$L_DEFAULT_B,'strings'=>$L_DEFAULT_S)); if($this->GetLangArray(LIVEADMIN_L.'/'.$this->lang.'.rtf',$L_LANG_B,$L_LANG_S)===false) return(array('base'=>$L_DEFAULT_B,'strings'=>$L_DEFAULT_S)); $L_LANG_B = array_merge($L_DEFAULT_B,$L_LANG_B); $L_LANG_S = array_merge($L_DEFAULT_S,$L_LANG_S); $L_LANG_FLIP = array_flip($L_DEFAULT_S); return(array('base'=>$L_LANG_B,'strings'=>$L_LANG_S,'flip'=>$L_LANG_FLIP)); } function MergeWithInfo($ar) { if(is_array($ar)) $this->lang_ar['strings'] = array_merge($this->lang_ar['strings'],$ar); } function GetLangArray($data,&$lang_base,&$lang_strings) { if(is_file($data)) $data = file_get_contents($data); if(strpos($data,'LIVE_ADMIN_LANG_')===false) return false; $lang_base = array(); $lang_strings = array(); $data_ar = explode("\n",$data); foreach($data_ar as $line) { $line = trim($line); if(substr($line,0,1)=='#' || $line=='') continue; if(preg_match('/^@(.*)=>(.*)$/isU',$line,$m)) { $lang_base[$m[1]] = $m[2]; } elseif(preg_match('/^\[(.*)\]=>\[(.*)\]$/isU',$line,$m)) { $lang_strings[$m[1]] = $m[2]; } } return true; } function Translate($in) { $out = $in; $out = preg_replace_callback('/<%s5lang\s+(.+)\s*%>/ismU',array(&$this,'_translate_callback'), $out); $out = preg_replace_callback('/<s5lang>\s*(.+)\s*<\x5C{0,1}\/s5lang>/ismU',array(&$this,'_translate_callback'), $out); $out = preg_replace_callback('/<s5lang_base>\s*(.+)\s*<\/s5lang_base>/ismU',array(&$this,'_translate_callback_base'), $out); $out = preg_replace_callback('/&lt;%s5lang\s+(.+)\s*%&gt;/ismU',array(&$this,'_translate_callback'), $out); $out = preg_replace_callback('/&lt;s5lang&gt;\s*(.+)\s*&lt;\x5C{0,1}\/s5lang&gt;/ismU',array(&$this,'_translate_callback'), $out); $out = preg_replace_callback('/&lt;s5lang_base&gt;\s*(.+)\s*&lt;\/s5lang_base&gt;/ismU',array(&$this,'_translate_callback_base'), $out); return($out); } function _translate_callback($m) { $id = $m[1]; if(isset($this->lang_ar['strings'][$id])) return($this->lang_ar['strings'][$id]); if(isset($this->lang_ar['flip'][$id])) { $id2 = $this->lang_ar['flip'][$id]; if(isset($this->lang_ar['strings'][$id2])) return($this->lang_ar['strings'][$id2]); } return($id); } function _translate_callback_base($m) { $id = $m[1]; if(isset($this->lang_ar['base'][$id])) return($this->lang_ar['base'][$id]); return($id); } function GetLanguageList() { $dir = LIVEADMIN_L; $RV = array(); if (is_dir($dir)) { if ($dh = opendir($dir)) { while (($file = readdir($dh)) !== false) { if(substr($file,-4)=='.rtf') { if($this->GetLangArray(LIVEADMIN_L.'/'.$file,$LB,$LS)) $RV[$LB['name']] = $LB['display']; } } closedir($dh); } } asort($RV); return($RV); } function GetAllStrings() { return($this->lang_ar['strings']); } function GetAllBase() { return($this->lang_ar['base']); } } ?>