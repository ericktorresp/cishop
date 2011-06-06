<?php
/**
 * 路径: /_cli/inituser.php
 * 功能: 用户整体迁移(从高频运行迁移用户)
 * 
 * @author    saul     090908
 * @version   1.0.0
 * @package   passportadmin
 */


// init
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件


class cli_inituser extends basecli
{
    /**
     * 步骤:
     *      1:本地数据库初始化(主要是对相关的表进行数据清除以及起始ID还原成1)
     *      2:数据库导入完毕
     * @return bool
     */
    protected function _runCli()
    {
        //die("\n Run This Program you must cancel this line at 27.\n");
        //检测锁文件是否存在
        $sLockFile = PDIR.DS."_cli".DS."inituser.lock";
        if( file_exists( $sLockFile ) )
        {
            die("init User run or del this file:".PDIR.DS."_cli".DS."inituser.lock");
        }
        $fp = fopen( $sLockFile, "w");
        fwrite($fp,"1",strlen("1"));
        fclose($fp);
        set_time_limit(0);
        $oDB = new db( $GLOBALS["aSysDbServer"]["master"] );
        //清理数据请打开下面这一行,否则手工清理 tempusermap表
        //$this->clearLocalData( $oDB );
        $this->CreateUser( $oDB );
        unlink( $sLockFile );
        echo "The User Insert OK.\n\nPlease Delete This File.";
        return true;
    }



    /**
     * 清理本地数据库
     *
     * @param db $oDB
     */
    function clearLocalData( $oDB )
    {
        echo "\nFirst:Init Tables:\n";
        //对其他表进行清空处理
        $aTables = array("users","usertree","usersession","activity","userchannel","userfund",
        "activityanswer","activityinfo","activityuser","adminlog","adminnote","adminproxy",
        "banksnapshot","chartdatas","domains","msgcontent","msglist","notices","orders",
        "sessions","topproxyset","useradminproxy","userdomain","userlog","userskins",
        "userunite","withdrawel","reportcount","firewallaction","firewallrules","tempusermap"); //需要清理的表
        foreach($aTables as $table)
        {
            $oDB->query("DELETE FROM `".$table."`;");
            $oDB->query("ALTER TABLE `".$table."` AUTO_INCREMENT=1");
            echo $table." tables has been init\n";
        }
        $oDB->query("DELETE FROM `proxygroup` where `groupid`>7");
        $oDB->query("ALTER TABLE `proxygroup`  AUTO_INCREMENT=1");
        echo "proxygroup tables has been init\n";        
        echo"First Finished\n";
    }



    /**
     * 导入用户
     *
     * @param db $oDB
     */
    function CreateUser( $oDB )
    {
        $sSqlCheckUser2 ="SHOW TABLES LIKE 'users2'";
        $oDB->query( $sSqlCheckUser2 );
        if($oDB->numRows()==0)
        {
            echo "The User not Exists\n";
            return 0;
        }
        $oDB->doTransaction();
        echo "\nCreate User Start\n";
        $sSqlCreateUser = "insert into users "
        ."(`userid`,`username`,`loginpwd`,`securitypwd`,`nickname`,`lastip`,`lasttime`,"
        ."`registerip`,`registertime`,`usertype`) "
        ."select `id`,`username`,`pwd`,`fpwd`,`nickname`,'127.0.0.1', '" . date("Y-m-d H:i:s", time()) . "','127.0.0.1',`reg_time`,`gid`"
        ." from `users2`";
        $oDB->query($sSqlCreateUser);
        if( $oDB->errno()>0 )
        {
            $oDB->doRollback();
            return -1; //导入用户时候失败
        }
        $oDB->query("update `users` set `usertype`='0' where `usertype`='5'");//用户
        if( $oDB->errno()>0 )
        {
            $oDB->doRollback();
            return -2; //更新用户时候失败
        }
        $oDB->query("update `users` set `usertype`='1' where `usertype`='4'");//代理
        if( $oDB->errno()>0 )
        {
            $oDB->doRollback();
            return -3; //更新代理身份时候失败
        }
        echo "Create User Finished\n";
        echo "Create Usertree Start\n";
        $sSqlCreateUserTree ="INSERT INTO `usertree`"
        ."(`userid`, `username`, `nickname`, `usertype`, `lvtopid`, `lvproxyid`, `parentid`, "
        ."`parenttree`, `userrank`) select `id`,`username`,`nickname`,`gid`,`id`,'0',`parent_id`,"
        ."`parent_str`,'0' from `users2`";
        $oDB->query( $sSqlCreateUserTree );
        if( $oDB->errno()>0 )
        {
            $oDB->doRollback();
            return -4; //导入用户关系树失败
        }
        $oDB->query("update `usertree` set `usertype`='0' where `usertype`='5'");//用户
        if( $oDB->errno()>0 )
        {
            $oDB->doRollback();
            return -5; //更新用户时候失败
        }
        $oDB->query("update `usertree` set `usertype`='1' where `usertype`='4'");//代理
        if( $oDB->errno()>0 )
        {
            $oDB->doRollback();
            return -6; //更新代理身份时候失败
        }
        $oDB->query("Update `usertree` set `lvtopid`=substring_index(`parenttree`,',',1) where `parentid`<>0");
        if( $oDB->errno()>0 )
        {
            $oDB->doRollback();
            return -7; //更新lvtopid时候失败
        }
        $oDB->query("update `usertree` set `lvproxyid`=substring_index(substring_index(parenttree,',',2) ,',',-1)"
            ." where `parentid`>0 and `usertype`<2 and `parenttree`<>`parentid`");
        //更新了非总代+非一代的用户
        if( $oDB->errno()>0 )
        {
            $oDB->doRollback();
            return -8; //更新非一代lvproxyid时候失败
        }
        $oDB->query("update `usertree` set `lvproxyid`=`userid` where `parenttree`=`parentid` and `usertype`<2 and `parentid`>0");
        if( $oDB->errno()>0 )
        {
            $oDB->doRollback();
            return -9; //更新一代时候lvproxyid时候失败
        }
        echo "Create UserTree Finished\n";
        echo "Create UserSession Start\n";
        $sSqlCreateUserSession = "INSERT INTO `usersession`(`userid`, `sessionkey`, `isadmin`)"
        ." Select `id`,'','0' from `users2`";
        $oDB->query( $sSqlCreateUserSession );
        if( $oDB->errno()>0 )
        {
            $oDB->doRollback();
            return -10; //插入用户SESSION时候失败
        }
        echo "Create UserSession Finished\n";
        echo "Create UserChannnel Start\n";
        $sSqlCreateUserChannel = "INSERT INTO `userchannel` (`userid`, `channelid`, `groupid`)"
            ."Select `id`,'0',`gid` from `users2`";
        $oDB->query( $sSqlCreateUserChannel );
        if( $oDB->errno()>0 )
        {
            $oDB->doRollback();
            return -10; //插入用户SESSION时候失败
        }
        $oDB->query("update `userchannel` set `groupid`='3' where `groupid`='4'"); //代理修正A
        if($oDB->errno()>0 )
        {
            $oDB->doRollback();
            return -11; //更新用户频道表
        }
        $oDB->query("update `userchannel` set `groupid`='4' where `groupid`='5'"); //用户修正
        if($oDB->errno()>0 )
        {
            $oDB->doRollback();
            return -12; //更新用户频道表
        }
        $oDB->query("update `userchannel` left join `usertree` on (`userchannel`.`userid`=`usertree`.`userid`)"
            ." set `userchannel`.`groupid`=2 where `userchannel`.`groupid`='3' and `usertree`.`parentid`=`usertree`.`parenttree`"
            ." and `usertree`.`usertype`<2 and `usertree`.`parentid`>0");//一代用户的修正
        if($oDB->errno()>0 )
        {
            $oDB->doRollback();
            return -13; //对一代用户groupid修正
        }
        $oDB->query("update `userchannel` left join `usertree` on (`userchannel`.`userid`=`usertree`.`userid`)"
            ." set `userchannel`.`groupid`=1 where `userchannel`.`groupid`='3'"
            ." and `usertree`.`usertype`<2 and `usertree`.`parentid`=0");//一代用户的修正
        if($oDB->errno()>0 )
        {
            $oDB->doRollback();
            return -14; //对总代用户groupid修正
        }
        echo "Create UserChannnel Finished\n";
        echo "Create UserFund Start\n";
        $sSqlCreateUserFund ="INSERT INTO `userfund` (`userid`, `channelid`)"
                            ." Select `id`,'0' from `users2`";
        $oDB->query($sSqlCreateUserFund);
        if($oDB->errno()>0 )
        {
            $oDB->doRollback();
            return -15; //插入用户资金失败
        }
        echo "Create UserFund Finished\n";
        echo "Init TopProxySet start\n";
        $sqlCreateTopproxySet = "INSERT INTO `topproxyset` (`userid`,`proxykey`,`proxyvalue`)"
            ."SELECT `id`,'credit','0' FROM `users2` where `parent_id`='0'";
        $oDB->query($sqlCreateTopproxySet);
        if($oDB->errno()>0)
        {
            $oDB->doRollback();
            return -16; //对总代用户信用欠款初始化失败
        }
        $sqlCreateTopproxySet = "INSERT INTO `topproxyset` (`userid`,`proxykey`,`proxyvalue`)"
            ."SELECT `id`,'open_level','0' FROM `users2` where `parent_id`='0'";
        $oDB->query($sqlCreateTopproxySet);
        if($oDB->errno()>0)
        {
            $oDB->doRollback();
            return -17; //对总代用户开户层级初始化失败
        }
        $sqlCreateTopproxySet = "INSERT INTO `topproxyset` (`userid`,`proxykey`,`proxyvalue`)"
            ."SELECT `id`,'can_create','0' FROM `users2` where `parent_id`='0'";
        $oDB->query($sqlCreateTopproxySet);
        if($oDB->errno()>0)
        {
            $oDB->doRollback();
            return -18; //对总代用户能否开户初始化失败
        }
        echo "Init TopProxySet Finished\n";
        echo "Init UserSkins start\n";
        $sqlCreateUserSkins = "INSERT INTO `userskins` (`userid`,`skins`)"
            ."SELECT `id`,'default' FROM `users2` where `parent_id`='0'";
        $oDB->query($sqlCreateUserSkins);
        if($oDB->errno()>0)
        {
            $oDB->doRollback();
            return -19; //对总代用户的皮肤初始化失败
        }
        echo "Init UserSkins Finished\n";
        echo "Init tempusermap Start\n";
        $sqlCreateUserMap = "INSERT INTO `tempusermap` (`gpuserid`,`gpusername`,`dpuserid`,`dpusername`,`dpuserdomain`,`gpuserdomain`,`status`)"
            ."SELECT `id`,`username`,`id`,`username`,'','','0' FROM `users2` where `parent_id`='0'";
        $oDB->query($sqlCreateUserMap);
        if($oDB->errno()>0)
        {
            $oDB->doRollback();
            return -20; //对总代用户的皮肤初始化失败
        }
        echo "Init tempusermap Finished\n";
        $oDB->doCommit();
        echo "Create User Finished\n";
        return 1;
    }
}

$oCli = new cli_inituser(TRUE);
EXIT;
?>