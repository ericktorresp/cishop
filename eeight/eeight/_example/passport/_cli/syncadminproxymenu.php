<?php
/**
 * 路径: /_cli/syncadminproxymenu.php
 * 功能: 同步总代管理员权限
 * 将原有proxygroup,拆分成为两个表，proxygroup 和 admin_proxy_menu,将原有总代管理员的权限同步到新表中
 * 
 * 
 * 命令行可接受参数: 空
 * 
 * 
 * @author    louis
 * @version   v1.0		2010-05-21
 */

// init
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件

class cli_syncadminproxymenu extends basecli
{
	private $bDoUnLock  = FALSE;   // 是否允许释放 LOCK 文件
	
    /**
     * 析构
     */
    public function __destruct()
    {
        parent::__destruct();
        if( $this->bDoUnLock == TRUE )
        {
            @unlink( $this->_getBaseFileName() . '.locks' );
        }
    }
    
    /**
     * 重写基类 _runCli() 方法
     */
    protected function _runCli()
    {
    	// Step: 01 检查是否已有相同CLI在运行中
        $sLocksFileName = $this->_getBaseFileName() . '.locks';
        if( file_exists( $sLocksFileName ) )
        { // 如果有运行的就终止本个CLI
            die("Error : The CLI is running\n" );
        }
        file_put_contents( $sLocksFileName ,"running", LOCK_EX ); // CLI 独占锁
        
        $oGroup = new model_proxygroup();
        $mResult = $oGroup->syncAdminProxyMenu();
        if ($mResult === 'null'){
        	echo "no records to sync\n";
        } else if ($mResult === false){
        	echo "failed\n";	
        } else {
        	echo "successful\n";
        }
        
        $this->bDoUnLock = TRUE; // 允许程序结束后, 对 LOCK 文件解锁
        return TRUE;
    }
}

$oCli = new cli_syncadminproxymenu(TRUE);
EXIT;
?>