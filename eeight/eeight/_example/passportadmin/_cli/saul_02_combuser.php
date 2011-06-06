<?php
/**
 * 路径: /_cli/combuser.php
 * 功能: 总代合并问题(A+B->A),该程序必须运行在用户初始化之后且不激活其他频道的前提下
 * 
 * @author    saul     090911
 * @version   1.0.0
 * @package   passportadmin
 */


// init
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件

class cli_combuser extends basecli
{
    /**
    * 相关步骤:获取A,B两个用户的相关userid,以及用户所处的相关级别
    *
    */
    protected function _runCli()
    {
        /* @var $oDB db */
        $oDB = new db( $GLOBALS["aSysDbServer"]["master"] );
        $aTopProxy = $this->aArgv; //总代用户的关系树
        $iCountTopProxy  = count($aTopProxy);
        if( $iCountTopProxy<3 )
        {
            die("Params is wrong.\n");
        }
        $sNewName = $aTopProxy[$iCountTopProxy-1];
        $iTopProxy = array();
        unset($aTopProxy[$iCountTopProxy-1]);
        unset($aTopProxy[0]);
        foreach($aTopProxy as $i=>$topProxy)
        {
            $iTopProxy[$i] =  $this->getTopProxyIdByName( $topProxy, $oDB );
            if($iTopProxy[$i]==0)
            {
            die("\nUser is Not Exists.\n");
            }
        }
        $iUserMin = min($iTopProxy);
        foreach($iTopProxy as $topproxy)
        {
            if( $this->UserComb( $iUserMin, $topproxy, $oDB )===TRUE )
            {
                echo "\nComb User Success.\n";
            }
            else
            {
                echo "\nComb User Fail.\n";
                return FALSE;
            }
        }
        if($this->UserReName( $iUserMin, $sNewName, $oDB )===TRUE)
        {
            echo "\nReName success.\n";
        }
        else
        {
            echo "\nReName Fail.\n";
        }
        return TRUE;
    }



    /**
     * 根据用户名获取用户ID
     * @author SAUL
     * 
     * @param  string $sUserName
     * @param  db     $oDB
     */
    function getTopProxyIdByName( $sUserName, $oDB )
    {
        $sSqlUser ="SELECT `userid` from `usertree` where `username`='".$sUserName."' AND `parentId`='0'";
        $aUser = $oDB->getOne( $sSqlUser );
        if( !empty($aUser) )
        {
            return $aUser["userid"];
        }
        return 0;
    }



    /**
     * 受影响的数据库表有：
     *  +  topproxyset  总代设置表 (删除$iUserId2中的设置 )
     *  +  userchannel  用户频道表 ($iUserId2del)
     *  +  proxygroup   总代管理员组 ($iUserId2->$iUserId1)
     *  +  userdomain   分配域名表的使用(更新之后需要删除重复的)
     *  +  userskins    用户皮肤（删除）
     *  +  usertree     用户树
     *  +  users        用户表
     *  +  usersession  用户session表
     */
    /**
     * 总代合并
     *
     * @param integer $iUserId1
     * @param integer $iUserId2
     * @param db      $oDB
     */
    function UserComb( $iUserId1, $iUserId2, $oDB )
    {
        if($iUserId1 == $iUserId2)
        {
            return TRUE;
        }
        $oDB->doTransaction();
        //topproxyset
        $oDB->query("DELETE FROM `topproxyset` WHERE `userid`='".$iUserId2."'");
        if( $oDB->errno()>0 )
        {
            $oDB->doRollback();
            echo("delete topproxyset of ".$iUserId2." Error");
            return -1;
        }
        //userchannel
        $oDB->query("DELETE FROM `userchannel` WHERE `userid`='".$iUserId2."' and `channelid`='0'");
        if( $oDB->errno()>0 )
        {
            $oDB->doRollback();
            echo("delete userchannel of ".$iUserId2." Error");
            return -2;
        }
        //proxygroup
        $oDB->query("UPDATE `proxygroup` SET `ownerid`='".$iUserId1."' WHERE `ownerid`='".$iUserId2."'");
        if( $oDB->errno()>0 )
        {
            $oDB->doRollback();
            echo("update proxygroup of ".$iUserId2." Error");
            return -3;
        }
        //userdomain
        //删除重复的,需要手工处理
        //更新
        $oDB->query("UPDATE `userdomain` set `userid`='".$iUserId1."' where `userid`='".$iUserId2."'");
        if( $oDB->errno()>0 )
        {
            $oDB->doRollback();
            echo("UPDATE userdomain of ".$iUserId2." Error");
            return -5;
        }
        //userskins
        $oDB->query("DELETE FROM `userskins` where `userid`='".$iUserId2."'");
        if( $oDB->errno()>0 )
        {
            $oDB->doRollback();
            echo("DELETE userskins of ".$iUserId2." Error");
            return -6;
        }
        //usertree
        //用户关系树(usertree)
        $oDB->query("UPDATE `usertree` set `parenttree` = "
        ."concat('".$iUserId1."',substring(`parenttree`,".(strlen($iUserId2)+1).")), "
        ."`lvtopid`='".$iUserId1."' where `lvtopid`='".$iUserId2."'");
        if( $oDB->errno()>0 )
        {
            $oDB->doRollback();
            echo("UPDATE usertree of ".$iUserId2." Error");
            return -7;
        }
        //恢复总代管理员
        $oDB->query("UPDATE `usertree` set `lvtopid`='0',`lvproxyid`='0' where `usertype`='2'");
        if( $oDB->errno()>0 )
        {
            $oDB->doRollback();
            echo("UPDATE usertree's TopProxy'Admin of ".$iUserId2." Error");
            return -8;
        }
        //删除自身
        $oDB->query("DELETE FROM `usertree` where `userid`='".$iUserId2."'");
        if( $oDB->errno()>0 )
        {
            $oDB->doRollback();
            echo("DELETE usertree of ".$iUserId2." Error");
            return -9;
        }
        //users
        $oDB->query("DELETE FROM `users` where `userid`='".$iUserId2."'");
        if( $oDB->errno()>0 )
        {
            $oDB->doRollback();
            echo("DELETE users of ".$iUserId2." Error");
            return -10;
        }
        //usersession
        $oDB->query("DELETE FROM `usersession` where `userid`='".$iUserId2."' and `isadmin`='0'");
        if( $oDB->errno()>0 )
        {
            $oDB->doRollback();
            echo("DELETE usersession of ".$iUserId2." Error");
            return -11;
        }
        //tempusermap
        $oDB->query("update `tempusermap` set `dpuserid`='".$iUserId1."' where `gpuserid`='".$iUserId2."'");
        if( $oDB->errno()>0 )
        {
            $oDB->doRollback();
            echo("update tempusermap of ".$iUserId2." Error");
            return -12;
        }
        $oDB->doCommit();
        return TRUE;
    }



    /**
     * 总代重命名
     * 
     */
    function UserReName( $iUserId, $sNewUserName ,$oDB )
    {
        $acheckUserName = $oDB->getOne("SELECT * FROM `users` where `username`='".$sNewUserName."'");
        if( empty($acheckUserName) )
        {
            $oDB->doTransaction();
            $oDB->query("UPDATE `users` set `username`='".$sNewUserName."' where `userid`='".$iUserId."'");
            if( $oDB->errno()>0 )
            {
                $oDB->doRollback();
                return -13;
            }
            $oDB->query("UPDATE `usertree` set `username`='".$sNewUserName."' where `userid`='".$iUserId."'");
            if( $oDB->errno()>0 )
            {
                $oDB->doRollback();
                return -14;
            }
            $oDB->query("UPDATE `tempusermap` set `dpusername`='".$sNewUserName."' where `dpuserid`='".$iUserId."'");
            if( $oDB->errno()>0 )
            {
                $oDB->doRollback();
                return -15;
            }
            $oDB->doCommit();
            return TRUE;
        }
        return FALSE;
    }
}
$oCli = new cli_combuser(TRUE);
EXIT;
?>