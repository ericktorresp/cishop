<?php
/**
 * 路径: /_cli/0000_changeloginimg.php
 * 功能: 零点运行,更换首页插图图片
 * 			从 images/day 目录中的对应文件复制到 images/login/login_main.jpg
 * 
 * 	使用:
 * 		可指定日期作为第一参数输入, 格式:月-日 (以英文-符号分割,无须0补位)
 * 		默认使用当天
 * 
 * @author    Jim
 * @version   0.1 (1/18/2011)
 * @package   passportadmin
 * 
 * 
	[ERROR CODE]
	-1 图片无需更换
	-2 备更换的图片后缀名不符合要求,当前是 jpg
	-3 源文件无效(可能已被删除)
	-4 目标文件不可写 (可能权限不足或没有)
	-5 目标目录不是0777权限
 *
 */

// init
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件


class cli_changeloginimg extends basecli
{
    /**
     * 析构函数, 程序完整执行成功或执行错误后. 删除 locks 文件
     */
    public function __destruct()
    {
        parent::__destruct();
        @unlink( $this->_getBaseFileName() . '.locks' );
    }


    /**
     * 重写基类 _runCli() 方法, 程序主流程
     */
    protected function _runCli()
    {
        // Step 01: 检查当前时间是否允许启动
        if( FALSE === $this->_IsAllowRun() && !is_array($this->aArgv)  )
        {
            $this->halt( '[d] [' . date('Y-m-d H:i:s') .'] Time Not Allowed!');
        }


    	// Step 02: 在此 CLI 程序运行时, 获取独占锁. 禁止多进程同时运行
    	$sLocksFileName = $this->_getBaseFileName() . '.locks';
    	if( file_exists( $sLocksFileName ) )
    	{
    		$this->halt( '[d] [' . date('Y-m-d H:i:s') .'] The CLI is running');
    	}
    	file_put_contents( $sLocksFileName ,"running", LOCK_EX );


        // Step 03: 执行图片更换
        $oGala 	= new model_gala();
        $oGala->Argv = $this->aArgv[1];
        
		$aResult = $oGala->changeimg();
        
		@unlink( $sLocksFileName );
		if ( $aResult === TRUE )
		{
			echo "[OK] [".date('Y-m-d H:i:s')."] Change is Done.\n";
		}
		else 
		{
			echo "[Error] [".date('Y-m-d H:i:s')."] faild. Error:".$aResult."\n";
		}
        
    }


	/**
     * 检查当前时间是否可以运行
     * @return bool
     */
    private function _IsAllowRun()
    {
        // 许可时间;  零点至零点零3分, 零5分进行服务器间同步
        $sAllowTimeStart 	= '00:00:00';
        $sAllowTimeStop 	= '00:05:00';
        $sNow = date('H:i:s');
        if ( $sNow > $sAllowTimeStart && $sNow < $sAllowTimeStop )
        {
        	return TRUE;
        }
        else 
        {
        	return FALSE;
        }
        
    }
    
}


$oCli = new cli_changeloginimg(TRUE);
EXIT;
?>