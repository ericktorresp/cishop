<?php
/**
 * 路径: /_cli/sync_user4deposit.php
 * 功能: 同步用户数据表至user_deposit_card表， 
 * 		一般在表格初始化时使用
 * 
 * @author    Jim
 * @version   0.1 (9/16/2010)
 * @package   mdeposit
 */
set_time_limit(0);
// init
define('DONT_USE_APPLE_FRAME_MVC', TRUE); 											// 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); 	// 引入项目入口文件


class cli_syncuserfordeposit extends basecli
{
    /**
     * 程序完整执行成功或执行错误后. 删除 locks 文件
     */
    public function __destruct()
    {
        parent::__destruct();
        @unlink( $this->_getBaseFileName() . '.locks' );
    }


    /**
     *  程序主流程
     */
    protected function _runCli()
    {
        // Step 01: 检查当前时间是否允许程序启动
        /*if( FALSE === $this->_IsAllowRun() )
        {
            $this->halt( '[d] [' . date('Y-m-d H:i:s') .'] Time Not Allowed!');
        }*/

    	// Step 02: 在此 CLI 程序运行时, 获取独占锁. 禁止多进程同时运行
    	$sLocksFileName = $this->_getBaseFileName() . '.locks';
    	if( file_exists( $sLocksFileName ) )
    	{
    		$this->halt( '[d] [' . date('Y-m-d H:i:s') .'] The CLI is running');
    	}
    	file_put_contents( $sLocksFileName ,"running", LOCK_EX );


        // Step 03: 执行同步
    	$oDep = new model_deposit_companycard();
		$aResult = $oDep->syncUser();
			
			
		@unlink( $sLocksFileName );
		
    		if ($aResult === -1)
			{
				$aResults = '无须更新';
			}
			elseif ($aResult === -2)
			{
				$aResults = '有无效数据';
			}
			elseif ($aResult === FALSE)
			{
				$aResults = '插入时发生SQL失败';	 
			}
			elseif ($aResult === TRUE)
			{
				$aResults = '成功';
				echo "[OK] [".date('Y-m-d H:i:s')."] Sync is Done.";
			}
			else 
			{
				$aResults = '未知错误';
				
			}
			
		
        $this->halt( "[w] [".date('Y-m-d H:i:s')."] $aResults " );
        
        
    }


	/**
     * 检查当前时间是否可以进行同步操作
     * @return bool
     */
    private function _IsAllowRun()
    {
        // 当前: 休市时间最好
        return TRUE;
        
    }
}


$oCli = new cli_syncuserfordeposit(TRUE);
EXIT;
?>