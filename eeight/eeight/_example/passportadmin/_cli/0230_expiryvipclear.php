<?php
/**
 * 路径: /_cli/0230_expiryvipclear.php
 * 功能: 清理过期VIP名单
 * 		每日休市时间或系统负载较轻的时候执行, 每次执行最多清除200个，可以重复执行
 * 
 * @author    Jim
 * @version   0.1 (9/13/2010)
 * @package   mdeposit
 */

// init
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件


class cli_expiryvipclear extends basecli
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


        // Step 03: 执行清除过期VIP用户标记
        $oDeposit 	= new model_deposit_companycard();
        $sWhere 	= " `isvip`=1 AND `vip_expriy` < '".date('Y-m-d H:i:s')."'";
        $aNeedClear = $oDeposit->getRelationList('`userid`,`username`', $sWhere, 200);
	
        
        foreach ( $aNeedClear['results'] AS $aRe)
        {
        	$aClear[$aRe['userid']]['logo'] 	= 'clidelvip';
        	$aClear[$aRe['userid']]['userid'] 	= $aRe['userid'];
        	$aClear[$aRe['userid']]['username'] = $aRe['username'];
        }

        $aResult = $oDeposit->del('clidelvip',$aClear);
        
		@unlink( $sLocksFileName );
		if ( $aResult === TRUE )
		{
			echo "[OK] [".date('Y-m-d H:i:s')."] Clean VIP is Done.";
		}
		
		if (empty($aNeedClear['results'])) 
		{
			$aResults = 'not need Clean.';
		}
		else 
		{
			$aResults = ($aResult === TRUE) ? 'run Done.' : 'have Error on runing.';
		}
        $this->halt( "[w] [".date('Y-m-d H:i:s')."] $aResults " );
        
        
    }


	/**
     * 检查当前时间是否可以进行过期VIP清理
     * @return bool
     */
    private function _IsAllowRun()
    {
        // 当前: 任意时间都可以
        return TRUE;
        
    }
}


$oCli = new cli_expiryvipclear(TRUE);
EXIT;
?>