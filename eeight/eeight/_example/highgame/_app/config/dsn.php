<?php
/**
 * 功能: 低频平台(投注+代理+公司管理员)  数据库配置文件
 * 路径: aframe/_example/highgame/_app/config/dsn.php
 * 效验: Tom 2010-01-25 14:50
 */
if( !defined('IN_APPLE') || IN_APPLE!==TRUE ) die('Error code: 0x1000');

define( 'SYS_CHANNELID', 4 );           // 平台ID 高频游戏3.0 = 4
define( 'MESSAGE_TYPE_FIREWALL', 1 );   // 防火墙消息

// 低频 主DB 参数配置, 读+写 操作
$GLOBALS['aSysDbServer']['master'] = array(
	'DBHOST' => '127.0.0.1',
	'DBPORT' => '3307',
	'DBUSER' => 'passport',
	'DBPASS' => '2908262',
	'DBNAME' => 'hgame',
	'DBCHAR' => 'UTF8',
);

// 低频 从DB 参数配置, 用于报表读取
$GLOBALS['aSysDbServer']['report'] = array(
    'DBHOST' => '127.0.0.1',
    'DBPORT' => '3307',
    'DBUSER' => 'passport',
    'DBPASS' => '2908262',
    'DBNAME' => 'hgame',
    'DBCHAR' => 'UTF8',
);


// PASSPORT 主DB配置.  用于SESSION 写入与读取
$GLOBALS['aSysDbServer']['session'] = array(
    'DBHOST' => '127.0.0.1',
    'DBPORT' => '3306',
    'DBUSER' => 'passport',
    'DBPASS' => '2908262',
    'DBNAME' => 'passport',
    'DBCHAR' => 'UTF8',
);


// API 效验 ( 开发版临时注释 )
//$GLOBALS['aApi']['verifySenderIp'] = TRUE;  // 是否开启IP效验
//$GLOBALS['aApi']['allowSenderIp']  = array( // 允许Sender的IP地址, CDN IP
//    '127.0.0.1',     // TODO: 生产版本禁止此行
//    '192.168.0.1',
//    '127.0.0.7',
//	//'',
//);


// API 全局配置   [频道ID号]  =>  '服务器IP地址'
$GLOBALS['aApiConfig']['iPort'] = array(
    '0'        => '90',   // PASSPORT API服务器端口: 默认90
    '1'        => '92',   // 低频游戏 API服务器端口: 默认92
    '4'        => '93',   // 高频游戏 API服务器端口: 默认93 (高频3.0)
);
// API 全局配置   [频道ID号]  =>  '服务器IP地址'
$GLOBALS['aApiConfig']['sAddress'] = array(
	'0'     => 'e8cp.com',   // PASSPORT平台  API IP地址.
	'1'     => 'e8cp.com/lowgame',   // 低频游戏平台  API IP地址.
	'4'     => 'e8cp.com/highgame',   // 高频游戏平台  API IP地址. (高频3.0)
);



// 拥有触发器的表, 用于做缓存 class.filecaches, 对应DB中的 caches.tablename
$GLOBALS['aSysDbServer']['trigger'] = array(
);

// 元角模式配置
$GLOBALS['config']['modes'] = array(
        1 => array(
            'modeid' => 1,
            'name' => '元',
            'rate' => 1,
        ),
        2 => array(
            'modeid' => 2,
            'name' => '角',
            'rate' => 0.1,
        ),
    );
    
?>