<?php
/**
 * 路径: /_cli/0330_userfavorite.php
 * 功能: 统计各个用户最喜欢的玩法
 *       
 *       
 *
 * @author    mark
 * @version   1.0.0
 * @package   highadmin
 */
//初始化配置
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件

class cli_methodcode extends basecli
{
    private $bDoUnLock  = FALSE;   // 是否允许释放 LOCK 文件
    /**
     * 析构函数, 程序完整执行成功或执行错误后. 删除 locks 文件
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
     * 重写基类 _runCli() 方法, 程序主流程
     */
    protected function _runCli()
    {
        //检查是否已有相同CLI在运行中
        $sLocksFileName = $this->_getBaseFileName() . '.locks';
        if( file_exists( $sLocksFileName ) )
        { // 如果有运行的就终止本个CLI
            $this->halt( '[d] [' . date('Y-m-d H:i:s') .'] The CLI is running');
        }
        $oMethodCode = new model_methodcode();
        $fSartTime = microtime(true);
        $oMethodCode->createMethodCode(); 
        $fUseTime = microtime(true) - $fSartTime;
        echo "use $fUseTime seconds\n";
        file_put_contents( $sLocksFileName ,"running", LOCK_EX ); // CLI 独占锁
        $this->bDoUnLock = TRUE;
    }
}
$oCli = new cli_methodcode(TRUE); // 生产版本可以考虑改为 FALSE, 关闭调试
EXIT;
?>