<?php
/**
 * 路径: /_api/checkSecurityPass.php
 * 用途: 效验资金密码 (请求接收处理方)
 * 
 * 转账调度器 API 接收参数:
 *    $this->aArgv['iUserId']       =  用户ID
 *    $this->aArgv['sFundPwd']      =  资金密码(未MD5加密)
 * 
 * @author 	    tom  090811 15:18
 * @version	1.0.0
 * @package	passport
 */

// 1, 包含项目头文件, index.php 忽略 MVC
define('DONT_USE_APPLE_FRAME_MVC', TRUE);       // 跳过 MVC 流程
define('DONT_TRY_LOAD_SYSCONFIG_FILE', TRUE);   // 跳过配置文件检测
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件



class api_checkSecurityPass extends baseapi
{
    /**
     * 所有派生的类, 请保留以下注释:
     * 重写基类 _runProcess() 方法, 最后需返回运行成功或失败
     * @return $this->makeApiResponse( TRUE|FALSE, 'STRING DATA' )
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     */
    public function _runProcess()
    {
        // -------------------- 更新转出频道, 转入频道 '转账账变' 的状态值 ------------------
        if( !empty($this->aArgv['iUserId']) && is_numeric( $this->aArgv['iUserId'])
        	&& !empty($this->aArgv['sFundPwd']) )
        {
            // 初始模型层, 进行资金密码效验
            $oUser = new model_user();
			if( FALSE == $oUser->checkSecurityPass( $this->aArgv['iUserId'], $this->aArgv['sFundPwd'] ) )
			{
				return $this->makeApiResponse( FALSE, 'pwd error' );
			}
			else 
			{
			    return $this->makeApiResponse( TRUE, 'successed!' );
			}
        }
        else
        {
            return $this->makeApiResponse( FALSE, 'data error' );
        }
    }

}


// 2, 为调用程序返回 '结果集'
$oApi = new api_checkSecurityPass(TRUE);
$oApi->runApi();
$oApi->showDatas();
EXIT;
?>