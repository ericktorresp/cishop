<?php
/**
 * 路径: /_cli/e10m_createconfig.php
 * 功能: 每一小时运行的'配置文件 生成' ( * Crontab * )
 * 
 * 
 * 命令行可接受参数: 空
 * 
 * 
 * @author    James     090908 10:18
 * @version   1.1.0
 * @package   lowgame
 */

// init
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件

class cli_createconfig extends basecli
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
            die('Error : The CLI is running' );
        }
        file_put_contents( $sLocksFileName ,"running", LOCK_EX ); // CLI 独占锁

        echo "[d] [".date('Y-m-d H:i:s')." ";
        $oConfig = new model_config();
		if( $oConfig->getConfigFile( realpath(dirname(__FILE__) . '/../').DIRECTORY_SEPARATOR."_app".
		                             DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR ) )
		{
		    echo "Done \n";
		}
		else 
		{
		    echo "Write File Fail\n";
		}
        
        $this->bDoUnLock = TRUE; // 允许程序结束后, 对 LOCK 文件解锁
        return TRUE;
    }
}

$oCli = new cli_createconfig(TRUE);
EXIT;
?>