<?php
if (!defined('IN_APPLE') || IN_APPLE!==TRUE) die( __('error.frame.noAccess') );

// common Language
return $_message = array(
    'error.frame.noAccess' => '无效访问 0x0001', // 非法引用文件
    'error.frame.hacker' => '无效访问 0x0002', // 提交了 $_GLOBAL 数据
    'error.frame.unknown' => '无效访问 0x0003', // __FILE__ == ''



);


?>