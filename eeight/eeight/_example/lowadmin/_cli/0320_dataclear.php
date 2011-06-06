<?php
/**
 * 路径 : /_cli/dataclear.php
 * 功能 : CLI - 数据清理
 * 
 * 调用方式: dataclear.php i   (i:为日志清理类型)
 * ----------------------------------------------
 *   1、管理员日志清理
 *   2、用户日志清理
 *   3、帐变清理
 *
 * @author     Saul
 * @version    1.2.0
 * @package    lowadmin
 */

define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件

class cli_dataclear extends basecli
{
    /**
     * 1、管理员日志清理
     * 2、用户日志清理
     * 3、帐变清理
     * 4、奖期清理
     * 5、方案清理
     * 6、总代投注量清理
     * 7、数据快照表清理
     * @return Bool
     */
    protected function _runCli()
    {
        if( !isset($this->aArgv[1]) )
        {
            echo('Need More Params');
            return false;
        }
        if( !is_numeric($this->aArgv[1]) )
        {
            echo('Error Params');
            return false;
        }
        $iAction = $this->aArgv[1];
        if ($iAction==1)
        { //管理员日志清理
            $oConfig = new model_config();
            $configs = $oConfig->getConfigs(array('logcleardate','logclearstarttime','logclearendtime','logclearrun')); 
            if ($configs["logclearrun"]==0)
            {
                return "This Programm is not allowed";
            }
            if ($configs["logclearstarttime"]>$configs["logclearendtime"])
            {//对跨天的支持
                $now =date("Y-m-d H:i:s");
                $starttime = date("Y-m-d ").$configs["logclearstarttime"];
                $endtime = date("Y-m-d ").$configs["logclearendtime"];
                if (($now < $starttime)&&($endtime>$now))
                {
                    return "Now is not allow Programm run.";
                }
            }
            else
            {//没有跨天的支持
                $now = date("H:i:s");
                if($now<$configs["logclearstarttime"])
                {
                    return "Now is not allow Programm run.";
                }
                if ($now > $configs["logclearendtime"])
                {
                    return "Now is not allow Programm run.";
                }
            }
            $day = $configs["logcleardate"];
            $File = date("Ymd")."_admin.gz";
            $Path = PDIR.DS."_data".DS."adminlog".DS;
            $oAdminlog = new model_adminlog();
            makeDir($Path);
            $oAdminlog->bakLog($day,$Path.$File);
            echo "[d] [".date('Y-m-d H:i:s'). " Admin Log had Clear\n";
            return TRUE;
        }
        elseif($iAction==2)
        { // 用户日志清理
            $oConfig = new model_config();
            $configs = $oConfig->getConfigs(array('logcleardate','logclearstarttime','logclearendtime','logclearrun')); 
            if ($configs["logclearrun"]==0)
            {
                return "This Programm is not allowed";
            }
            if ($configs["logclearstarttime"]>$configs["logclearendtime"])
            {//对跨天的支持
                $now =date("Y-m-d H:i:s");
                $starttime = date("Y-m-d ").$configs["logclearstarttime"];
                $endtime = date("Y-m-d ").$configs["logclearendtime"];
                if (($now < $starttime)&&($endtime>$now))
                {
                    return "Now is not allow Programm run.";
                }
            }
            else
            {//没有跨天的支持
                $now = date("H:i:s");
                if($now<$configs["logclearstarttime"])
                {
                    return "Now is not allow Programm run.";
                }
                if ($now > $configs["logclearendtime"])
                {
                    return "Now is not allow Programm run.";
                }
            }
            $day = $configs["logcleardate"];
            $File = date("Ymd")."_user.gz";
            $Path = PDIR.DS."_data".DS."userlog".DS; 
            $oUserlog = new model_userlog();
            makeDir($Path);
            $oUserlog->bakLog($day,$Path.$File);
            echo "[d] [".date('Y-m-d H:i:s'). " User Log had Clear\n";
            return TRUE;
        }
        elseif($iAction==3)
        { // 帐变清理
            $oConfig = new model_config();
            $configs = $oConfig->getConfigs(array('orderscleardate','ordersclearstarttime','ordersclearendtime','ordersclearrun')); 
            if ($configs["ordersclearrun"]==0)
            {
                return "This Programm is not allowed";
            }
            if ($configs["ordersclearstarttime"]>$configs["ordersclearendtime"])
            {//对跨天的支持
                $now =date("Y-m-d H:i:s");
                $starttime = date("Y-m-d ").$configs["ordersclearstarttime"];
                $endtime = date("Y-m-d ").$configs["ordersclearendtime"];
                if (($now < $starttime)&&($endtime>$now))
                {
                    return "Now is not allow Programm run.";
                }
            }
            else
            {//没有跨天的支持
                $now = date("H:i:s");
                if($now<$configs["ordersclearstarttime"])
                {
                    return "Now is not allow Programm run.";
                }
                if ($now > $configs["ordersclearendtime"])
                {
                    return "Now is not allow Programm run.";
                }
            }
            $day = $configs["orderscleardate"];
            $File = date("Ymd")."_orders.gz";
            $Path = PDIR.DS."_data".DS."orders".DS;
            $oOrders = new model_orders();
            makeDir($Path);
            $oOrders->bakLog($day,$Path.$File);
            echo "[d] [".date('Y-m-d H:i:s'). " Orders had Clear\n";
            return TRUE;
        }
        elseif($iAction==4)
        { // 奖期清理
            $oConfig = new model_config();
            $configs = $oConfig->getConfigs(array('issuecleardate','issueclearstarttime','issueclearendtime','issueclearrun')); 
            if ($configs["issueclearrun"]==0)
            {
                return "This Programm is not allowed";
            }
            if ($configs["issueclearstarttime"]>$configs["issueclearendtime"])
            {//对跨天的支持
                $now =date("Y-m-d H:i:s");
                $starttime = date("Y-m-d ").$configs["issueclearstarttime"];
                $endtime = date("Y-m-d ").$configs["issueclearendtime"];
                if (($now < $starttime)&&($endtime>$now))
                {
                    return "Now is not allow Programm run.";
                }
            }
            else
            {//没有跨天的支持
                $now = date("H:i:s");
                if($now<$configs["issueclearstarttime"])
                {
                    return "Now is not allow Programm run.";
                }
                if ($now > $configs["issueclearendtime"])
                {
                    return "Now is not allow Programm run.";
                }
            }
            $day = $configs["issuecleardate"];
            $Path = PDIR.DS."_data".DS;
            $oIssue = new model_issueinfo();
            $oIssue->bakLog( $day ,$Path );
            echo "[d] [".date('Y-m-d H:i:s'). " Issue had Clear\n";
            return TRUE;
        }
        elseif($iAction==5)
        { // 方案清理
            $oConfig = new model_config();
            $configs = $oConfig->getConfigs(array('projectcleardate','projectclearstarttime','projectclearendtime','projectclearrun')); 
            if ($configs["projectclearrun"]==0)
            {
                return "This Programm is not allowed";
            }
            if ($configs["projectclearstarttime"]>$configs["projectclearendtime"])
            {//对跨天的支持
                $now =date("Y-m-d H:i:s");
                $starttime = date("Y-m-d ").$configs["projectclearstarttime"];
                $endtime = date("Y-m-d ").$configs["projectclearendtime"];
                if (($now < $starttime)&&($endtime>$now))
                {
                    return "Now is not allow Programm run.";
                }
            }
            else
            {//没有跨天的支持
                $now = date("H:i:s");
                if($now<$configs["projectclearstarttime"])
                {
                    return "Now is not allow Programm run.";
                }
                if ($now > $configs["projectclearendtime"])
                {
                    return "Now is not allow Programm run.";
                }
            }
            $day = $configs["projectcleardate"];
            $Path = PDIR.DS."_data".DS;
            $oProjects = new model_projects();
            makeDir($Path);
            $oProjects->bakLog($day,$Path);
            echo "[d] [".date('Y-m-d H:i:s'). " Projects had Clear\n";
            return TRUE;
        }
        /*   6/21/2010   */
    	elseif( $iAction == 6 )
        { 
        	// 	代理最近投注量数据表清理 recentbuy
            $oConfig = new model_config();
            $configs = $oConfig->getConfigs(array('recentbuycleardate','recentbuyclearstarttime','recentbuyclearendtime','recentbuyclearrun')); 
            if ($configs["recentbuyclearrun"]==0)
            {
                return "This Programm is not allowed";
            }
            if ($configs["recentbuyclearstarttime"]>$configs["recentbuyclearendtime"])
            {
            	//对跨天的支持
                $now =date("Y-m-d H:i:s");
                $starttime = date("Y-m-d ").$configs["recentbuyclearstarttime"];
                $endtime = date("Y-m-d ").$configs["recentbuyclearendtime"];
                if (($now < $starttime)&&($endtime>$now))
                {
                    return "Now is not allow Programm run.";
                }
            }
            else
            {
            	//没有跨天的支持
                $now = date("H:i:s");
                if($now<$configs["recentbuyclearstarttime"])
                {
                    return "Now is not allow Programm run.";
                }
                if ($now > $configs["recentbuyclearendtime"])
                {
                    return "Now is not allow Programm run.";
                }
            }
            $day = $configs["recentbuycleardate"];
            $Path = PDIR.DS."_data".DS;
            $oRecentbuy = new model_sale();
            makeDir($Path);
            $oRecentbuy->backandclearData($day,$Path);
            echo "[d] [".date('Y-m-d H:i:s'). " Rencentbuy had Clear\n";
            return TRUE;
            
        }
        
    	elseif( $iAction == 7 )
        { 
        	// 	数据快照表清理 snapshot
            $oConfig = new model_config();
            $configs = $oConfig->getConfigs(array('snapshotcleardate','snapshotclearstarttime','snapshotclearendtime','snapshotclearrun')); 
            if ($configs["snapshotclearrun"]==0)
            {
                return "This Programm is not allowed";
            }
            if ($configs["snapshotclearstarttime"]>$configs["snapshotclearendtime"])
            {
            	//对跨天的支持
                $now =date("Y-m-d H:i:s");
                $starttime = date("Y-m-d ").$configs["snapshotclearstarttime"];
                $endtime = date("Y-m-d ").$configs["snapshotclearendtime"];
                if (($now < $starttime)&&($endtime>$now))
                {
                    return "Now is not allow Programm run.";
                }
            }
            else
            {
            	//没有跨天的支持
                $now = date("H:i:s");
                if($now<$configs["snapshotclearstarttime"])
                {
                    return "Now is not allow Programm run.";
                }
                if ($now > $configs["snapshotclearendtime"])
                {
                    return "Now is not allow Programm run.";
                }
            }
            $day = $configs["snapshotcleardate"];
            $Path = PDIR.DS."_data".DS;
            $osnapshot = new model_snapshot();
            makeDir($Path);
            $osnapshot->backandclear($day,$Path);
            echo "[d] [".date('Y-m-d H:i:s'). " Snapshot had Clear\n";
            return TRUE;
            
        }
        
        return FALSE;
    }
}

$oCli = new cli_dataclear();
exit;
?>