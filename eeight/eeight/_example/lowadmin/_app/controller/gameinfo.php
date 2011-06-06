<?php
/**
 * 文件 : /_app/controller/gameinfo.php
 * 功能 : 游戏信息管理
 *
 * 功能:
 *
 * - actionList             游戏信息列表 
 * - actionAdd              增加游戏信息
 * - actionGameStop         停止销售游戏
 * - actionGameStart        开始销售游戏
 * - actionEdit             修改游戏信息
 * - actionPlayList         游戏玩法组列表(上级id为0的为玩法,否则为买法)
 * - actionAddPlay          增加游戏玩法      
 * - actionSleepdate        更新休市日期
 * - actionPointset         游戏返点设置
 * - actionEdittime         更新游戏时间
 * - actionPlaystart        开始游戏
 * - actionPlayStop         停止游戏
 * - actionPlayEdit         对游戏玩法(买法)进行修改
 * - actionPrizegroup       奖金组模版
 * - actionPrizegroupadd    增加奖金组模版
 * - actionPrizegroupedit   修改奖金组模版
 * - actionassign           分配奖金组模版
 * - actionverify           验证奖金组模版
 * - actionPrizelevel       奖金组详情查看
 * - actionIsssuelist       游戏奖期信息
 * - actionIussueAdd        增加游戏奖期
 * - actionlocksadd         游戏封锁表
 * - actionlocksedit        游戏封锁表修改
 * - actionlocksdel         删除游戏封锁表
 * - actionIssueedit        对游戏奖期进行修改
 * - actionIssuedel         删除游戏奖期
 * - actionUserpgStop       用户奖金组禁用
 * - actionUserpgedit       用户奖金组修改
 * - actionUserpgstart      用户奖金组启用
 *  
 * 
 * @author    saul
 * @version   1.2.0
 * @package   lowadmin
 */

class controller_gameinfo extends basecontroller
{
    /**
     * 游戏信息列表
     * URL = ./index.php?controller=gameinfo&action=list
     * @author SAUL
     */
    function actionList()
    {
    	/* @var $oLottery model_lottery */
    	$oLottery = A::singleton("model_lottery");
        $aLottery = $oLottery->lotteryMethodGetList( '', '', '', 0 );
        $GLOBALS['oView']->assign( "ur_here", "游戏信息列表");
        $aLocation[0] = array('text'=>'增加游戏信息','href'=>url('gameinfo','add'));
        $GLOBALS["oView"]->assign( "actionlink",    $aLocation[0] );
        $GLOBALS['oView']->assign( "aLottery",      $aLottery );
        $oLottery->assignSysinfo();
        $GLOBALS['oView']->display( "gameinfo_list.html" );
        EXIT;
    }



    /**
     * 增加游戏信息
     * @author  SAUL
     * URL: ./index.php?controller=gameinfo&action=add
     */
    function actionAdd()
    {
        $aLocation[0] = array( "text"=>'游戏信息列表', "href"=>url('gameinfo','list') );
        $aLocation[1] = array( "text"=>'增加游戏信息', "href"=>url('gameinfo','add') );
        $oConfig = new model_config();
        if( isset($_POST)&&!empty($_POST) )
        {
        	// 限极上调奖金公司留水不能低于系统设定值
        	if ($_POST['adjustminprofit'] < $oConfig->getConfigs("adjustminprofit")){
        		sysMessage( '操作失败: 限极上调奖金公司留水低于系统设定值', 1, $aLocation );
        	}
            $aLottery = $_POST;
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $iResult = $oLottery->lotteryInsert( $aLottery );
            switch( $iResult )
            {
                case 0:
                    sysMessage( '操作失败:数据错误.', 1, $aLocation );
                    break;
                case -1:
                    sysMessage( '操作失败:彩种中文名称不存在.', 1, $aLocation );
                    break;
                case -2:
                    sysMessage( '操作失败:彩种英文名称不存在.', 1, $aLocation );
                    break;
                case -3:
                    sysMessage( '操作失败:彩种类型错误.', 1, $aLocation );
                    break;
                case -4:
                    sysMessage( '操作失败:彩种周期错误.', 1, $aLocation );
                    break;
                case -5:
                    sysMessage( '操作失败:彩种的奖期规则错误.', 1, $aLocation );
                    break;
                case -6:
                    sysMessage( '操作失败:彩种的返点差错误.', 1, $aLocation );
                    break;
                case -7:
                    sysMessage( '操作失败:彩种的最小留水错误.', 1, $aLocation );
                    break;
                case -8:
                    sysMessage( '操作失败:限极上调奖金公司留水错误.', 1, $aLocation );
                    break;
                case -9:
                    sysMessage( '操作失败:极限下调奖金返奖率失败.', 1, $aLocation );
                    break;
                case -10:
                    sysMessage( '操作失败:公司的最大亏损值错误.', 1, $aLocation );
                    break;
                default:
                    sysMessage( '操作成功', 0, $aLocation );
                    break;
            }
        }
        else
        {
            $GLOBALS["oView"]->assign( "ur_here",       "增加游戏信息" );
            $GLOBALS["oView"]->assign( "actionlink",    $aLocation[0] );
            $GLOBALS['oView']->assign( "minprofit", $oConfig->getConfigs("adjustminprofit") );
            $GLOBALS["oView"]->display("gameinfo_info.html" );
            EXIT;
        }
    }



    /**
     * 停止销售游戏
     * @author SAUL
     * URL: ./index.php?controller=gameinfo&action=gamestop
     */
    function actionGameStop()
    {
        $aLocation[0] = array( 'text'=>'游戏信息列表', 'href'=>url('gameinfo','list') );
        $iLotteryId   = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iLotteryId==0 )
        {
            sysMessage( '操作失败', 1, $aLocation );
        }
        /* @var $oLottery model_lottery */
        $oLottery = A::singleton("model_lottery");
        if( $oLottery->setStatus( $iLotteryId, 1 ) )
        {
            sysMessage('操作成功', 0, $aLocation );
        }
        else
        {
            sysMessage('操作失败', 1, $aLocation );
        }
    }



    /**
     * 开始销售游戏
     * @author SAUL
     * URL: ./index.php?controller=gameinfo&action=gamestart
     */
    function actionGameStart()
    {
        $aLocation[0] = array( 'text'=>'游戏信息列表', 'href'=>url('gameinfo','list') );
        $iLotteryId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iLotteryId==0 )
        {
            sysMessage( '操作失败', 1, $aLocation );
        }
        /* @var $oLottery model_lottery */
        $oLottery = A::singleton("model_lottery");
        if( $oLottery->setStatus( $iLotteryId, 0) )
        {
            sysMessage( '操作成功', 0, $aLocation );
        }
        else
        {
            sysMessage( '操作失败', 1, $aLocation );
        }
    }


    /**
     * 修改游戏信息
     * @author SAUL
     * URL: ./index.php?controller=gameinfo&action=edit
     */
    function actionEdit()
    {
        $aLocation[0] =array( "text"=>'游戏信息列表', 'href'=>url('gameinfo','list') );
        $oConfig = new model_config();
        if( isset($_POST)&&!empty($_POST) )
        {
        	// 限极上调奖金公司留水不能低于系统设定值
        	if ($_POST['adjustminprofit'] < $oConfig->getConfigs("adjustminprofit")){
        		sysMessage( '操作失败: 限极上调奖金公司留水低于系统设定值', 1, $aLocation );
        	}
            $aLottery = $_POST;
            $iLottery = isset($aLottery['lotteryid'])&&is_numeric($aLottery['lotteryid']) ? intval($aLottery['lotteryid']) : 0;
            if( $iLottery<=0 )
            {
                sysMessage( '操作失败:数据错误.', 1, $aLocation );
            }
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $iResult  = $oLottery->lotteryUpdate( $aLottery, "`lotteryid`='".$iLottery."'" );
            switch( $iResult )
            {
                case 0:
                    sysMessage( '操作失败.', 1, $aLocation );
                    break;
                case -1:
                    sysMessage( '操作失败:数据错误.', 1, $aLocation );
                    break;
                case -2:
                    sysMessage( '操作失败:彩种中文名称不存在.', 1, $aLocation );
                    break;
                case -3:
                    sysMessage( '操作失败:彩种英文名称不存在.', 1, $aLocation );
                    break;
                case -4:
                    sysMessage( '操作失败:不能修改彩种类型.', 1, $aLocation );
                    break;
                case -5:
                    sysMessage( '操作失败:彩种周期错误.', 1, $aLocation );
                    break;
                case -6:
                    sysMessage( '操作失败:彩种的奖期规则错误.', 1, $aLocation );
                    break;
                case -7:
                    sysMessage( '操作失败:彩种的返点差.', 1, $aLocation );
                    break;
                case -8:
                    sysMessage( '操作失败:彩种的最小留水错误.', 1, $aLocation );
                    break;
                case -9:
                    sysMessage( '操作失败:彩种的公司最小留水(动态调价参数)错误.', 1, $aLocation );
                    break;
                case -10:
                    sysMessage( '操作失败:极限下调奖金返奖率错误.', 1, $aLocation );
                    break;
                case -11:
                    sysMessage( '操作失败:公司最大亏损值错误.', 1, $aLocation );
                    break;
                default:
                    sysMessage( '操作成功', 0, $aLocation );
                    break;
            }
        }
        else
        {
            $iLotteryId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
            if( $iLotteryId==0 )
            {
                sysMessage( '操作失败',1, $aLocation );
            }
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $aLottery = $oLottery->lotteryGetOne('*', "`lotteryid`='".$iLotteryId."'");
            $aLottery["no_rule"] = @unserialize($aLottery["numberrule"]); //号码规则的转化
            unset( $aLottery["numberrule"] );
            
            // 限极上调奖金公司留水
        	$oConfig = new model_config();
            
            $GLOBALS['oView']->assign( "ur_here", "修改游戏信息" );
            $GLOBALS['oView']->assign( "lottery", $aLottery );
            $GLOBALS['oView']->assign( "actionlink", $aLocation[0] );
            $GLOBALS['oView']->assign( "action", "edit" );
            $GLOBALS['oView']->assign( "minprofit", $oConfig->getConfigs("adjustminprofit") );
            $oLottery->assignSysInfo();
            $GLOBALS['oView']->display( "gameinfo_info.html" );
            EXIT;
        }
    }



    /**
     * 游戏玩法组列表
     * @author SAUL
     * URL:./index.php?controller=gameinfo&action=playlist
     */
    function actionPlaylist()
    {
        $aLocation[0]   = array( "text"=>'游戏信息列表', "href"=>url("gameinfo","list") );
        $iLotteryId     = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iLotteryId==0 )
        {
            sysMessage( '操作失败:参数错误', 1, $aLocation );
        }
        $pid = isset($_GET["pid"]) &&is_numeric($_GET["pid"]) ? intval($_GET["pid"]) : 0; //组ID
        $aLocation[1] = array("text"=>'增加游戏玩法', 'href'=>url('gameinfo','addplay',array('id'=>$iLotteryId)) );
        /* @var $oMethod model_method */
        $oMethod = A::singleton("model_method");
        $aMethod = $oMethod->methodGetList( "a.*,b.`cnname`",
            "a.`lotteryid`='".$iLotteryId."' AND a.`pid`='".$pid."'", "", 0 );
        $GLOBALS['oView']->assign( "pid",         $pid );
        $GLOBALS['oView']->assign( "amethod",     $aMethod );
        $GLOBALS['oView']->assign( "actionlink",  $aLocation[0] );
        $GLOBALS['oView']->assign( "actionlink2", $aLocation[1] );
        $GLOBALS['oView']->assign( "ur_here",     "游戏玩法组列表" );
        $oMethod->assignSysInfo();
        $GLOBALS['oView']->display( "gameinfo_playlist.html" );
        EXIT;
    }



    /**
     * 增加游戏玩法
     * @author SAUL
     * URL: ./index.php?controller=gameinfo&action=addplay
     */
    function actionAddplay()
    {
        $aLocation[0] = array( "text"=>'游戏信息列表','href'=>url('gameinfo','list') );
        if( isset($_POST)&&!empty($_POST) )
        {
            $aMethod = $_POST;
            $iMethod = intval( $aMethod["lotteryid"] );
            $aLocation[1] = array( "text"=>'游戏玩法列表', 'href'=>url('gameinfo','playlist',array('id'=>$iMethod)) );
            $aLocation[2] = array( "text"=>'增加游戏玩法', 'href'=>url('gameinfo','addplay',array('id'=>$iMethod)) );
            /* @var $oMethod model_method */
            $oMethod = A::singleton("model_method");
            $iResult = $oMethod->methodInsert( $aMethod );
            switch( $iResult )
            {
                case 0:
                    sysMessage( '操作失败:数据不正确', 1, $aLocation );
                    break;
                case -1:
                    sysMessage( '操作失败:彩种类型错误.', 1, $aLocation );
                    break;
                case -2:
                    sysMessage( '操作失败:彩种名称为空.', 1, $aLocation );
                    break;
                case -3:
                    sysMessage( '操作失败:彩种的中奖函数名称为空.', 1, $aLocation );
                    break;
                case -4:
                    sysMessage( '操作失败:号码形态描述错误.', 1, $aLocation );
                    break;
                case -5:
                    sysMessage( '操作失败:奖级个数错误.', 1, $aLocation );
                    break;
                case -6:
                    sysMessage( '操作失败:转直注数错误.', 1, $aLocation );
                    break;
                default:
                    sysMessage( '操作成功', 0, $aLocation );
                    break;
            }
        }
        else
        {
            $GLOBALS['oView']->assign( "ur_here",       "增加游戏玩法" );
            $GLOBALS['oView']->assign( "actionlink",    $aLocation[0] );
            if( isset($_GET["id"])&&is_numeric($_GET["id"]) )
            {
                $id = intval($_GET["id"]);
                $GLOBALS['oView']->assign( "id", $id );
                $aLocation[1] = array( 'text'=>'游戏玩法列表',
                        'href'=>url('gameinfo','playlist',array('id'=>$id)) );
                $GLOBALS['oView']->assign( "actionlink2", $aLocation[1] );
                /* @var $oLottery model_lottery */
                $oLottery = A::singleton("model_lottery");
                $aLottery = $oLottery->lotteryMethodGetList( "a.`lotteryid`,a.`cnname`",
                                "a.`lotteryid`='".$id."'", "", 0 );
                $GLOBALS['oView']->assign( "aLottery", $aLottery[0] );
                /* @var $oMethod model_method */
                $oMethod = A::singleton("model_method");
                $aMethod = $oMethod->methodGetList( "a.*", "a.`lotteryid`='".$id."' AND a.`pid`='0'", "", 0 );
                $GLOBALS['oView']->assign( "methodlist", $aMethod );
                $methods = array();
                foreach($aMethod as $v)
                {
                    $v["nocount"] = @unserialize($v["nocount"]);
                    $methods[$v["methodid"]] = $v;
                }
                $GLOBALS['oView']->assign("methods", json_encode($methods) );
                //封锁表
                /* @var $oLocks model_locksname */
                $oLocks = A::singleton("model_locksname");
                $aLocks = $oLocks->locksnamegetAll( "*", "`lotteryid`='".$id."'" );
                $GLOBALS["oView"]->assign( "aLocks", $aLocks );
                $oLottery->assignSysInfo();
            }
            else
            {
                sysMessage('操作失败',1, $aLocation );
            }
            $GLOBALS['oView']->display( "gameinfo_playinfo.html" );
            EXIT;
        }
    }



    /**
     * 休市时间管理
     * @author SAUL
     * URL: ./index.php?controller=gameinfo&action=sleepdate
     */
    function actionSleepdate()
    {
        $aLocation[0] = array( 'text'=>'游戏信息管理', 'href'=>url('gameinfo','list') );
        if( isset($_POST)&&!empty($_POST) )
        {
            $aLottery = $_POST;
            $iLottery = isset($aLottery['lotteryid'])&&is_numeric($aLottery['lotteryid']) ? intval($aLottery['lotteryid']) : 0;
            if( $iLottery<=0 )
            {
                sysMessage( '操作失败:数据错误.', 1, $aLocation );
            }
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $iResult = $oLottery->lotteryUpdate( $aLottery, "`lotteryid`='".$iLottery."'" );
            if( $iResult>0 )
            {
                sysMessage('操作成功.', 0, $aLocation );
            }
            else
            {
                sysMessage('操作失败.', 1, $aLocation );
            }
        }
        else
        {
            $iLotteryId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
            if( $iLotteryId ==0 )
            {
                sysMessage( '操作失败:数据错误.', 1, $aLocation);
            }
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $aLottery = $oLottery->lotteryGetOne("`cnname`,`lotteryid`,`yearlybreakstart`,`yearlybreakend`",
                            "`lotteryid`='".$iLotteryId."'" );
            $GLOBALS['oView']->assign( "lottery", $aLottery );
            $GLOBALS['oView']->assign( "actionlink", $aLocation[0] );
            $GLOBALS['oView']->assign( "ur_here", "年休市时段管理" );
            $oLottery->assignSysInfo();
            $GLOBALS['oView']->display("gameinfo_sleepdate.html");
            EXIT;
        }
    }



    /**
     * 点差设置
     * @author SAUL
     * URL: ./index.php?controller=gameinfo&action=pointset
     */
    function actionPointset()
    {
        $aLocation[0] = array( 'text'=>'游戏信息管理', 'href'=>url('gameinfo','list') );
        if( isset($_POST)&&!empty($_POST) )
        {
            $aLottery = $_POST;
            $iLottery = isset($aLottery['lotteryid'])&&is_numeric($aLottery['lotteryid']) ? intval($aLottery['lotteryid']) : 0;
            if( $iLottery<=0 )
            {
                sysMessage( '操作失败:数据错误.', 1, $aLocation );
            }
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $iResult = $oLottery->lotteryUpdate( $aLottery, "`lotteryid`='".$iLottery."'" );
            if( $iResult>0 )
            {
                sysMessage( '操作成功.', 0, $aLocation );
            }
            else
            {
                sysMessage( '操作失败.', 1, $aLocation );
            }
        }
        else
        {
            $iLotteryId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
            if( $iLotteryId==0 )
            {
                sysMessage( '操作失败:数据错误.', 1, $aLocation );
            }
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $aLottery = $oLottery->lotteryGetOne("`cnname`,`lotteryid`,`mincommissiongap`,`minprofit`",
                            "`lotteryid`='".$iLotteryId."'" );
            $GLOBALS['oView']->assign( "lottery", $aLottery );
            $GLOBALS['oView']->assign( "actionlink", $aLocation[0] );
            $GLOBALS['oView']->assign( "ur_here", "点差管理" );
            $oLottery->assignSysInfo();
            $GLOBALS['oView']->display("gameinfo_pointset.html");
            EXIT;
        }
    }



    /**
     * 时间设置
     * @author  SAUL
     * URL: ./index.php?controller=gameinfo&action=edittime
     */
    function actionEdittime()
    {
        $aLocation[0] = array( 'text'=>'游戏信息管理', 'href'=>url('gameinfo','list') );
        if( isset($_POST)&&!empty($_POST) )
        {
            $aLottery = $_POST;
            $iLottery = isset($aLottery['lotteryid'])&&is_numeric($aLottery['lotteryid']) ? intval($aLottery['lotteryid']) : 0;
            if( $iLottery<=0 )
            {
                sysMessage( '操作失败:数据错误.', 1, $aLocation );
            }
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $iResult = $oLottery->updateLotteryTime($aLottery,$iLottery);
            if($iResult>0)
            {
                
                sysMessage('操作成功.', 0, $aLocation );
            }
            else
            {
                sysMessage('操作失败.', 1, $aLocation );
            }
        }
        else
        {
            $iLotteryId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) :0;
            if( $iLotteryId==0 )
            {
                sysMessage( '操作失败:数据错误.', 1, $aLocation );
            }
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $aLottery = $oLottery->lotteryGetOne("`cnname`,`lotteryid`,`dailystart`,`dailyend`,`edittime`,"
                ."`canceldeadline`,`weekcycle`,`dynamicprizestart`,`dynamicprizeend`",
                "`lotteryid`='".$iLotteryId."'");
            if( empty($aLottery) )
            {
                sysMessage('操作失败:数据错误.', 1, $aLocation );
            }
            $GLOBALS['oView']->assign( "lottery",   $aLottery );
            $GLOBALS['oView']->assign( "actionlink", $aLocation[0] );
            $GLOBALS['oView']->assign( "ur_here", "时间管理" );
            $oLottery->assignSysInfo();
            $GLOBALS['oView']->display("gameinfo_edittime.html");
            EXIT;
        }
    }



    /**
     * 玩法开售
     * @author SAUL
     * URL: ./index.php?controller=gameinfo&action=playstart
     */
    function actionPlaystart()
    {
        $iMethodId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0 ;
        if( $iMethodId==0 )
        {
            sysMessage('操作失败:数据不正确.', 1 );
        }
        /* @var $oMethod model_method */
        $oMethod = A::singleton("model_method");
        $aMethod = $oMethod->methodGetOne( "`lotteryid`", "`methodid`='".$iMethodId."'" );
        if( empty($aMethod) )
        {
            sysMessage('操作失败:数据不正确.', 1 );
        }
        $aLocation[0] = array("text"=>"玩法列表","href"=>url('gameinfo','playlist',array('id'=>$aMethod['lotteryid'])) ); 
        if( $oMethod->setMethodstatus( $iMethodId, 0 ) )
        {
            sysMessage('操作成功', 0, $aLocation );
        }
        else
        {
            sysMessage('操作失败', 1, $aLocation );
        }
    }



     /**
     * 玩法停售
     * @author SAUL
     * URL: ./index.php?controller=gameinfo&action=playstop
     */
    function actionPlaystop()
    {
        $iMethodId = isset( $_GET["id"] )&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0 ;
        if( $iMethodId==0 )
        {
            sysMessage('操作失败:数据不正确.', 1 );
        }
        /* @var $oMethod model_method */
        $oMethod = A::singleton("model_method");
        $aMethod = $oMethod->methodGetOne( "`lotteryid`", "`methodid`='".$iMethodId."'" );
        if( empty($aMethod) )
        {
            sysMessage('操作失败:数据不正确.', 1 );
        }
        $aLocation[0] = array( "text"=>"玩法列表",
            "href"=>url('gameinfo','playlist',array('id'=>$aMethod['lotteryid'])) ); 
        if( $oMethod->setMethodstatus( $iMethodId, 1 ) )
        {
            sysMessage('操作成功', 0, $aLocation );
        }
        else
        {
            sysMessage('操作失败', 1, $aLocation );
        }
    }



    /**
     * 玩法信息修改
     * @author SAUL
     * URL: ./index.php?controller=gameinfo&action=playedit
     */
    function actionPlayedit()
    {
    	if( isset($_POST)&&!empty($_POST) )
        {
            $aMethod = $_POST;
            $iMethod = intval($aMethod["methodid"]);
            if($iMethod==0 )
            {
                sysMessage( '操作失败', 1 );
            }
            $aLocation[0] = array( "text"=>"游戏玩法列表", 
                "href"=>url( "gameinfo", "playlist", array( "id"=>$aMethod["lotteryid"] ) ) );
            $aLocation[1] = array( "text"=>"增加游戏玩法", 
                "href"=>url( "gameinfo", "addplay", array( "id"=>$aMethod["lotteryid"] ) ) );
            $aLocation[2] = array( "text"=>"修改游戏玩法",
                "href"=>url( "gameinfo", "playedit", array( "id"=>$iMethod) ) );
            if( $aMethod["lotteryid"]==0 )
            {
                $aLocation[0] = $aLocation[2];
                unset( $aLocation[1], $aLocation[2] );
            }
            unset( $aMethod["methodid"] );
            /* @var $oMethod model_method */
            $oMethod = A::singleton("model_method");
            $iResult = $oMethod->methodUpdate( $aMethod, "`methodid`='".$iMethod."'" );
            switch( $iResult )
            {
                case -1:
                    sysMessage( '操作失败:数据错误', 1, $aLocation );
                    break;
                case -2:
                    sysMessage( '操作失败:彩种类型错误', 1, $aLocation );
                    break;
                case -3:
                    sysMessage( '操作失败:彩种名称为空', 1, $aLocation );
                    break;
                case -4:
                    sysMessage( '操作失败:彩种的中奖函数名称为空', 1, $aLocation );
                    break;
                case -5:
                    sysMessage( '操作失败:封锁表名称为空', 1, $aLocation );
                    break;
                case -6:
                    sysMessage( '操作失败:奖级个数错误', 1, $aLocation );
                    break;
                case -7:
                    sysMessage( '操作失败:转直注数错误', 1, $aLocation );
                    break;
                case 0:
                    sysMessage( '操作失败:没有数据更新', 1, $aLocation );
                    break;
                default:
                    sysMessage( '操作成功', 0, $aLocation );
                    break;
            }
        }
        else 
        {
            $iMethodId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
            if( $iMethodId==0 )
            {
                sysMessage( '操作失败:数据不正确.', 1 );
            }
            /* @var $oMethod model_method */
            $oMethod = A::singleton("model_method");
            $aMethod = $oMethod->methodGetOne( '*', "`methodid`='".$iMethodId."'" );
            if( empty($aMethod) )
            {
                sysMessage( '操作失败:数据不正确.', 1 );
            }
            $aMethod["nocount"]    = @unserialize($aMethod["nocount"]);
            $aMethod["areatype"] = @unserialize( base64_decode($aMethod["areatype"]) );
            $GLOBALS['oView']->assign( "aMethod", json_encode($aMethod) );
            /* @var $oLocks model_locksname */
            $oLocks = A::singleton("model_locksname");
            $aLocks = $oLocks->locksnamegetAll('*',"`lotteryid`='".$aMethod["lotteryid"]."'");
            $GLOBALS["oView"]->assign("aLocks",$aLocks);
            $aLocation[0] = array( "text"=>'游戏玩法列表', 
                "href"=>url('gameinfo',"playlist", array('id'=>$aMethod["lotteryid"])) );
            $aLocation[1] = array( "text"=>'增加游戏玩法', 
                "href"=>url('gameinfo',"addplay", array('id'=>$aMethod["lotteryid"])) );
            $GLOBALS['oView']->assign( "ur_here", "修改玩法组" );
            $GLOBALS["oView"]->assign( "actionlink", $aLocation[0] );
            $GLOBALS["oView"]->assign( "actionlink2", $aLocation[1] );
            //彩种信息
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $aLottery = $oLottery->lotteryGetOne( "`lotteryid`,`cnname`",
                "`lotteryid`='".$aMethod["lotteryid"]."'", "", 0 );
            $GLOBALS['oView']->assign( "aLottery", $aLottery );			
            //玩法组模板
            $aMethod = $oMethod->methodGetList( "a.*",
                "a.`lotteryid`='".$aMethod["lotteryid"]."' and a.`pid`='0'",'',0);
            $GLOBALS['oView']->assign( "methodlist", $aMethod );
            $methods = array();
            foreach($aMethod as $v)
            {
                $v["nocount"] = @unserialize($v["nocount"]);
                $methods[$v["methodid"]] = $v;
            }
            $GLOBALS['oView']->assign( "methods", json_encode($methods) );
            $GLOBALS["oView"]->assign( "action", "playedit" );
            $oMethod->assignSysInfo();
            $GLOBALS['oView']->display("gameinfo_playinfo.html");
            EXIT;
        }
    }



    /**
     * 奖金组列表
     * @author SAUL
     * URL: ./index.php?controller=gameinfo&action=prizegroup
     */
    function actionPrizegroup()
    {
        $aLocation[0] = array( "text"=>'游戏信息列表', "href"=>url('gameinfo','list') );
        $iLotteryId   = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iLotteryId==0 )
        {
            sysMessage("操作失败:数据错误.", 1, $aLocation);
        }
        $iPage = isset($_GET["p"])&&is_numeric($_GET["p"]) ? intval($_GET["p"]) : 1;
        $aLocation[1] =array( "text"=>'增加奖金组信息', 
                "href"=>url('gameinfo',"prizegroupadd",array('id'=>$iLotteryId)) );
        /* @var $oPrizeGroup model_prizegroup */
        $oPrizeGroup = A::singleton("model_prizegroup");
        $aPrizeGroup = $oPrizeGroup->pgGetList( '', "`lotteryid`='".$iLotteryId."'", '', 20, $iPage );
        $GLOBALS["oView"]->assign( "aPrizeGroup", $aPrizeGroup['results'] );
        $oPage = new pages( $aPrizeGroup['affects'], 20 );
        $GLOBALS['oView']->assign( "pageinfo", $oPage->show(1) );
        $GLOBALS["oView"]->assign( "ur_here",     "奖金组信息" );
        $GLOBALS["oView"]->assign( "actionlink",  $aLocation[0] );
        $GLOBALS['oView']->assign( "actionlink2", $aLocation[1] );
        $oPrizeGroup->assignSysInfo();
        $GLOBALS["oView"]->display("gameinfo_prizegroup.html");
        EXIT;
    }



    /**
     * 增加奖金组
     * @author SAUL
     * URL: ./index.php?controller=gameinfo&action=prizegroupadd
     */
    function actionPrizegroupadd()
    {
        if( isset($_POST)&&!empty($_POST) )
        {
            $aPrizeGroup = $_POST;
            /* @var $oPrizeGroup model_prizegroup */
            $oPrizeGroup = A::singleton("model_prizegroup");
            $iResult = $oPrizeGroup->pgInsert( $aPrizeGroup );
            $aLocation[0] = array( "text"=>"奖组管理",
                "href"=>url("gameinfo","prizegroup",array("id"=>$aPrizeGroup["lotteryid"])) );
            switch( $iResult )
            {
                case 0:
                    sysMessage( '操作失败:数据不完整.', 1, $aLocation );
                    break;
                case -1:
                    sysMessage( '操作失败:彩种ID错误.', 1, $aLocation );
                    break;
                case -2:
                    sysMessage( '操作失败:彩种不存在.', 1, $aLocation );
                    break;
                case -3:
                    sysMessage( '操作失败:彩种名称不存在.', 1, $aLocation );
                    break;
                case -4:
                    sysMessage( '操作失败:奖金错误.', 1, $aLocation );
                    break;
                case -5:
                    sysMessage( '操作失败:返点设置错误.', 1, $aLocation );
                    break;
                case -6:
                    sysMessage( '操作失败:公司最小留水计算错误.', 1, $aLocation );
                    break;
                case -7:
                    sysMessage( '操作失败:数据提交失败.', 1, $aLocation );
                    break;
                case -8:
                    sysMessage( '操作失败:数据提交失败.', 1, $aLocation );
                    break;
                default:
                    sysMessage( '操作成功', 0, $aLocation );
                    break;
            }
        }
        else
        {
            $aLocation[0] = array("text"=>'游戏信息列表','href'=>url('gameinfo','list') );
            $iLotteryId   = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
            if( $iLotteryId==0 )
            {
                sysMessage("操作失败:数据不正确.", 1, $aLocation );
            }
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $aLottery = $oLottery->lotteryGetOne( "*", "`lotteryid`='".$iLotteryId."'" );
            $GLOBALS['oView']->assign( "alottery", $aLottery );
            /* @var $oMethod model_method */
            $oMethod = A::singleton("model_method");
            $aMethod = $oMethod->methodGetList( "a.`methodid`,a.`lotteryid`,a.`methodname`,"
            ."a.`level`,a.`nocount`,a.`totalmoney`,b.`cnname`",
            "a.`lotteryid`='".$iLotteryId."' AND  a.`pid`='0'", "", 0 );
            foreach( $aMethod as &$method )
            {
                $method["nocount"] = @unserialize($method["nocount"]);
                if( isset($method["nocount"]["type"]) )
                {
                    $method["type"] = $method["nocount"]["type"];
                }
                $method["isdesc"] = $method["nocount"]["isdesc"];
                unset( $method["nocount"]["type"], $method["nocount"]["isdesc"] );
            }
            $aLocation[1] = array( "text"=>"增加游戏奖金组",
                'href'=>url("gameinfo","prizegroupadd",array( "id"=>$aLottery["lotteryid"] )) );
            $GLOBALS['oView']->assign( "amethod",  $aMethod );
            $oLottery->assignSysInfo();
            $GLOBALS['oView']->assign( "ur_here", "增加奖金组" );
            $GLOBALS['oView']->assign( "actionlink",  $aLocation[0] );
            $GLOBALS["oView"]->assign( "actionlink2", $aLocation[1] );
            $GLOBALS['oView']->display("gameinfo_prizegroupinfo.html");
            EXIT;
        }
    }



    /**
     * 对奖金组进行修改
     * @author SAUL
     * URL: ./index.php?controller=gameinfo&action=prizegroupedit
     */
    function actionPrizegroupedit()
    {
        $aLocation[0] = array( "text"=>'游戏信息列表', 'href'=>url('gameinfo','list') );
        if( isset($_POST)&&!empty($_POST) )
        {
            $aPrizeGroup    = $_POST;
            $iPrizeGroupId  = $aPrizeGroup["pgid"];
            /* @var $oPrizeGroup model_prizegroup */
            $oPrizeGroup    = A::singleton("model_prizegroup");
            $iResult        = $oPrizeGroup->pgUpdate( $aPrizeGroup, $iPrizeGroupId );
            switch( $iResult )
            {
                case 0:
                    sysMessage( '操作失败.', 1, $aLocation );
                    break;
                case -1:
                    sysMessage( '操作失败:数据不完整.', 1, $aLocation );
                    break;
                case -2:
                    sysMessage( '操作失败:数据不正确.', 1, $aLocation );
                    break;
                case -3:
                    sysMessage( '操作失败:奖组信息不存在.', 1, $aLocation );
                    break;
                case -4:
                    sysMessage( '操作失败:奖组信息错误.', 1, $aLocation );
                    break;
                case -5:
                    sysMessage( '操作失败:彩种不存在.', 1, $aLocation );
                    break;
                case -6:
                    sysMessage( '操作失败:奖金错误.', 1, $aLocation );
                    break;
                case -7:
                    sysMessage( '操作失败:返点设置错误.', 1, $aLocation );
                    break;
                case -8:
                    sysMessage( '操作失败:公司最小留水计算错误.', 1, $aLocation );
                    break;
                case -9:
                    sysMessage( '操作失败:奖金组名称不能为空.', 1, $aLocation );
                    break;
                case -10:
                    sysMessage( '操作失败:插入时候失败.', 1, $aLocation );
                    break;
                case -11:
                    sysMessage( '操作失败:更新数据时候失败.', 1, $aLocation );
                    break;
                case -12:
                    sysMessage( '操作失败:更新奖组信息时候失败.', 1, $aLocation );
                    break;
                default:
                    sysMessage( '操作成功', 0, $aLocation );
                    break;
            }
        }
        else
        {
            $iPgId = isset($_GET["pgid"])&&is_numeric($_GET["pgid"]) ? intval($_GET["pgid"]) : 0;
            if( $iPgId==0 )
            {
                sysMessage( "操作失败:数据不正确.", 1, $aLocation );
            }
            /* @var $oPrizeGroup model_prizegroup */
            $oPrizeGroup = A::singleton("model_prizegroup");
            $aPrizeGroup = $oPrizeGroup->pgGetOne( "*", "`prizegroupid`='".$iPgId."'" );
            if( empty($aPrizeGroup) )
            {
                sysMessage( "操作失败:数据不正确.", 1, $aLocation );
            }//需要先获取奖组信息
            /* @var $oLottery model_lottery */
            $oLottery   = A::singleton("model_lottery");
            $aLottery   = $oLottery->lotteryGetOne( "*", "`lotteryid`='".$aPrizeGroup['lotteryid']."'" );
            /* @var $oMethod model_method */
            $oMethod    = A::singleton("model_method");
            $aMethod    = $oMethod->methodGetList( "a.`methodid`,a.`lotteryid`,a.`methodname`,a.`level`,"
                    ."a.`nocount`,a.`totalmoney`,b.`cnname`",
                    "a.`lotteryid`='".$aPrizeGroup['lotteryid']."' and a.`pid`='0'", "", 0 );
            foreach($aMethod as &$method)
            {
                $method["nocount"] = @unserialize($method["nocount"]);
                if( isset($method["nocount"]["type"]) )
                {
                    $method["type"] = $method["nocount"]["type"];
                    unset( $method["nocount"]["type"] );
                }
                $method["isdesc"] = $method["nocount"]["isdesc"];
                unset( $method["nocount"]["isdesc"] );
            }
            //奖金设置详情
            /* @var $oPrizeLevel model_prizelevel */
            $oPrizeLevel = A::singleton("model_prizelevel");
            $aPrizeLevel = $oPrizeLevel->prizelevelGetList( "", "A.`prizegroupid`='".$iPgId."'", "", 0 );
            $aPrizeLevels = array();
            foreach( $aPrizeLevel as $prizelevel )
            {
            	$iMethodid = $prizelevel["methodid"];
            	$iLevel    = $prizelevel["level"];
                $aPrizeLevels["description"][$iMethodid][$iLevel]   = $prizelevel["description"];
                $aPrizeLevels["prize"][$iMethodid][$iLevel]         = $prizelevel["prize"];
                $aPrizeLevels["userpoint"][$iMethodid]              = $prizelevel["userpoint"];
                $aPrizeLevels["isclose"][$iMethodid]                = $prizelevel["isclose"];
            }
            $GLOBALS['oView']->assign( "prizelevel", $aPrizeLevels );
            $GLOBALS['oView']->assign( "alottery",   $aLottery );
            $GLOBALS['oView']->assign( "amethod",    $aMethod );
            $GLOBALS['oView']->assign( "prizegroup", $aPrizeGroup );
            $GLOBALS['oView']->assign( "action",     "prizegroupedit" );
            $oLottery->assignSysInfo();
            $GLOBALS['oView']->assign( "ur_here", "修改奖金组" );
            $GLOBALS['oView']->assign( "actionlink",  $aLocation[0] );
            $GLOBALS['oView']->display("gameinfo_prizegroupinfo.html");
            EXIT;
        }
    }



    /**
     * 分配奖组给总代
     * @author  SAUL
     * URL:./index.php?controller=gameinfo&action=assign
     */
    function actionassign()
    {
        $aLocation[0] = array("text"=>'游戏信息列表',"href"=>url('gameinfo','list'));
        if(isset($_POST)&&!empty($_POST))
        {
            $iPrizeGroupId  = intval($_POST["prizegroupid"]);
            $aUser          = isset($_POST["user"]) ? $_POST["user"] : array();
            /* @var $oPrizeGroup model_prizegroup */
            $oPrizeGroup = A::singleton("model_prizegroup");
            $iResult = $oPrizeGroup->userAuth( $iPrizeGroupId, $aUser );
            switch( $iResult )
            {
                case -1:
                    sysMessage( '操作失败:数据不正确.', 1, $aLocation );
                    break;
                case 0:
                    sysMessage( '操作失败: 没有数据更新.', 1,$aLocation );
                    break;
                default:
                    sysMessage( '操作成功.', 0, $aLocation );
                    break;
            }
        }
        else
        {
            $iPgId =isset($_GET["pgid"])&&is_numeric($_GET["pgid"])? intval($_GET["pgid"]) : 0;
            if( $iPgId<=0 )
            {
                sysMessage( '操作失败:数据错误.', 1, $aLocation );	
            }
            /* @var $oPrizeGroup model_prizegroup */
            $oPrizeGroup = A::singleton("model_prizegroup");
            $aPrizeGroup = $oPrizeGroup->pgGetOne( "*", "`prizegroupid`='".$iPgId."'" );
            $GLOBALS['oView']->assign("prizegroup",$aPrizeGroup);
            /* @var $oUser model_usertree */
            $oUser = A::singleton("model_usertree");
            $aUser = $oUser->userAgentget();
            /* @var $oUserPg model_userprizegroup */
            $oUserPg = A::singleton("model_userprizegroup");
            $aUserPg = $oUserPg->userpgGetList( "`userid`", "`pgid`='".$iPgId."'", '', 0 );
            $aUsers  = array();
            foreach( $aUserPg as $userPg )
            {
                $aUsers[] = $userPg["userid"];
            }
            $aUserCheck = $oUserPg->checkStatus( $iPgId );
            $aUserChecks = array();
            foreach( $aUserCheck as $user )
            {
                $aUserChecks[] = $user["userid"];
            }
            $GLOBALS['oView']->assign( "usersucc",  join(",",$aUserChecks) );   //用户同步数据
            $GLOBALS['oView']->assign( "userhas",   join(",",$aUsers) );        //用户拥有的数据
            $GLOBALS['oView']->assign( "user",      $aUser );
            $GLOBALS['oView']->assign( "ur_here",   "奖金组分配" );
            $GLOBALS['oView']->assign( "actionlink", $aLocation[0] );
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display( "gameinfo_assign.html" );
            EXIT;
        }
    }



    /**
     * 对方案组进行审核
     * @author  SAUL
     * URL: ./index.php?controller=gameinfo&action=veerify
     */
    function actionVerify()
    {
        $aLocation[0] = array("text"=>'游戏信息列表', "href"=>url('gameinfo','list') );
        if( isset($_POST)&&!empty($_POST) )
        {
            $iPgId = isset($_POST["pgid"])&&is_numeric($_POST["pgid"]) ? intval($_POST["pgid"]) : 0;
            if( $iPgId<0 )
            {
                sysMessage( '操作失败.', 1, $aLocation );
            }
            /* @var $oPrizeGroup model_prizegroup */
            $oPrizeGroup = A::singleton("model_prizegroup");
            $iResult = $oPrizeGroup->pgVerifity( $iPgId );
            if( $iResult>0 )
            {
                sysMessage( '操作成功', 0, $aLocation );
            }
            else
            {
                sysMessage( '操作失败', 1, $aLocation );
            }
        }
        else
        {
            $iPgId = isset($_GET["pgid"])&&is_numeric($_GET["pgid"]) ? intval($_GET["pgid"]):0;
            if( $iPgId==0 )
            {
                sysMessage( "操作失败:数据不正确.", 1, $aLocation );
            }
            /* @var $oPrizeGroup model_prizegroup*/
            $oPrizeGroup = A::singleton("model_prizegroup");
            $aPrizeGroup = $oPrizeGroup->pgGetOne( "*", "`prizegroupid`='".$iPgId."'" );
            if(empty($aPrizeGroup))
            {
                sysMessage( '操作失败:数据不正确', 1, $aLocation );
            }//需要先获取奖组信息
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $aLottery = $oLottery->lotteryGetOne("*", "`lotteryid`='".$aPrizeGroup['lotteryid']."'" );
            /* @var $oMethod model_method */
            $oMethod = A::singleton("model_method");
            $aMethod = $oMethod->methodGetList("", 
                "a.`lotteryid`='".$aPrizeGroup['lotteryid']."' and a.`pid`='0'", "", 0 );
            foreach( $aMethod as &$method )
            {
                $method["nocount"] = @unserialize( $method["nocount"] );
                if( isset($method["nocount"]["type"]) )
                {
                    $method["zhongjiang"] = $method["nocount"]["type"];
                    unset( $method["nocount"]["type"] );
                }
                unset( $method["nocount"]["isdesc"] );
            }
            //奖金设置详情
            /* @var $oPrizeLevel model_prizelevel */
            $oPrizeLevel = A::singleton("model_prizelevel");
            $aPrizeLevel = $oPrizeLevel->prizelevelGetList( "", "A.`prizegroupid`='".$iPgId."'", "", 0 );
            $aPrizeLevels = array();
            foreach( $aPrizeLevel as $prizelevel )
            {
            	$iMethod   = $prizelevel["methodid"];
            	$iLevel    = $prizelevel["level"];
                $aPrizeLevels["prize"][$iMethod][$iLevel]   = $prizelevel["prize"];
                $aPrizeLevels["userpoint"][$iMethod]        = $prizelevel["userpoint"];
                $aPrizeLevels["isclose"][$iMethod]          = $prizelevel["isclose"];
            }
            $GLOBALS['oView']->assign( "prizelevel", $aPrizeLevels );
            $GLOBALS['oView']->assign( "alottery", $aLottery );
            $GLOBALS['oView']->assign( "amethod", $aMethod );
            $GLOBALS['oView']->assign( "prizegroup", $aPrizeGroup );
            $GLOBALS['oView']->assign( "action", "verify");
            /* @var $oUser model_usertree */
            $oUser = A::singleton("model_usertree");
            if($aPrizeGroup["topproxy"]=="")
            {
                $aUser = array();
            }
            else
            {
                $aUser = $oUser->userAgentget("`userid` in (".$aPrizeGroup["topproxy"].")");
            }
            $GLOBALS['oView']->assign( "user", $aUser );
            $oLottery->assignSysInfo();
            $GLOBALS['oView']->assign("ur_here","奖金组审核");
            $GLOBALS['oView']->display("gameinfo_verify.html");
            EXIT;
        }
    }



    /**
     * 价位查看
     * @author SAUL
     * URL:./index.php?controller=gameinfo&action=prizelevel
     */
    function actionPrizelevel()
    {
    	$aLocation[0] = array( "text"=>'游戏信息列表', 'href'=>url('gameinfo','list') );
    	$iPgId = isset($_GET["pgid"])&&is_numeric($_GET["pgid"]) ? intval($_GET["pgid"]) : 0;
        if( $iPgId==0 )
        {
            sysMessage( "操作失败:数据不正确.", 1, $aLocation );
        }
        /* @var $oPrizeGroup model_prizegroup */
        $oPrizeGroup = A::singleton("model_prizegroup");
        $aPrizeGroup = $oPrizeGroup->pgGetOne( "*", "`prizegroupid`='".$iPgId."'" );
        if( empty($aPrizeGroup) )
        {
            sysMessage( "操作失败:数据不正确.", 1, $aLocation );
        }//需要先获取奖组信息
        /* @var $oLottery model_lottery */
        $oLottery   = A::singleton("model_lottery");
        $aLottery   = $oLottery->lotteryGetOne( "*", "`lotteryid`='".$aPrizeGroup['lotteryid']."'" );
        /* @var $oMethod model_method */
        $oMethod    = A::singleton("model_method");
        $aMethod    = $oMethod->methodGetList( "a.`methodid`,a.`lotteryid`,a.`methodname`,a.`level`,"
                ."a.`nocount`,a.`totalmoney`,b.`cnname`",
                "a.`lotteryid`='".$aPrizeGroup['lotteryid']."' and a.`pid`='0'", "", 0 );
        foreach($aMethod as &$method)
        {
            $method["nocount"] = @unserialize($method["nocount"]);
            if( isset($method["nocount"]["type"]) )
            {
                $method["type"] = $method["nocount"]["type"];
                unset( $method["nocount"]["type"] );
            }
            $method["isdesc"] = $method["nocount"]["isdesc"];
            unset( $method["nocount"]["isdesc"] );
        }
        //奖金设置详情
        /* @var $oPrizeLevel model_prizelevel */
        $oPrizeLevel = A::singleton("model_prizelevel");
        $aPrizeLevel = $oPrizeLevel->prizelevelGetList( "", "A.`prizegroupid`='".$iPgId."'", "", 0 );
        $aPrizeLevels = array();
        foreach( $aPrizeLevel as $prizelevel )
        {
            $iMethodid = $prizelevel["methodid"];
            $iLevel    = $prizelevel["level"];
            $aPrizeLevels["description"][$iMethodid][$iLevel]   = $prizelevel["description"];
            $aPrizeLevels["prize"][$iMethodid][$iLevel]         = $prizelevel["prize"];
            $aPrizeLevels["userpoint"][$iMethodid]              = $prizelevel["userpoint"];
            $aPrizeLevels["isclose"][$iMethodid]                = $prizelevel["isclose"];
        }
        $GLOBALS['oView']->assign( "prizelevel", $aPrizeLevels );
        $GLOBALS['oView']->assign( "alottery",   $aLottery );
        $GLOBALS['oView']->assign( "amethod",    $aMethod );
        $GLOBALS['oView']->assign( "prizegroup", $aPrizeGroup );
        $GLOBALS['oView']->assign( "action",     "prizegroupedit" );
        $oLottery->assignSysInfo();
        $GLOBALS['oView']->assign( "ur_here", "奖金组详情[".$aPrizeGroup["title"]."]" );
        $GLOBALS['oView']->assign( "actionlink",  $aLocation[0] );
        $GLOBALS['oView']->display( "gameinfo_prizelevel.html" );
        EXIT;
    }



    /**
     * 奖期管理
     * @author SAUL
     * URL: ./index.php?controller=gameinfo&action=issuelist
     */
    function actionIssuelist()
    {
        $aLocation[0]   = array( "text"=>'游戏信息列表', "href"=>url('gameinfo','list') );
        $iLotteryId     = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iLotteryId==0 )
        {
            sysMessage("操作失败:数据错误.", 1, $aLocation);
        }
        $aLocation[1] = array( 'text'=>'增加游戏奖期',
                'href'=>url('gameinfo','issueadd',array('id'=>$iLotteryId)));
        $GLOBALS['oView']->assign( "ur_here",       "奖期管理" );
        $GLOBALS['oView']->assign( "actionlink",    $aLocation[0] );
        $GLOBALS['oView']->assign( "actionlink2",   $aLocation[1] );
        /* @var $oIssue model_issueinfo */
        $oIssue = A::singleton("model_issueinfo");
        $iPage = isset($_GET["p"])&&is_numeric($_GET["p"]) ? intval($_GET["p"]) : 1;
        $aIssue = $oIssue->issueGetList("A.*,B.`cnname`",
            "A.`lotteryid`='".$iLotteryId."'", '', 25, $iPage );
        $GLOBALS['oView']->assign( "aIssue", $aIssue['results'] );
        $oPage = new pages( $aIssue['affects'], 25 );
        $GLOBALS['oView']->assign( "pageinfo", $oPage->show(1) );
        $oIssue->assignSysInfo();
        $GLOBALS['oView']->display( "gameinfo_issuelist.html" );
        EXIT;
    }



    /**
     * 增加奖期
     * @author SAUL
     * URL:./index.php?controller=gameinfo&action=issueadd
     */
    function actionIssueadd()
    {
        $aLocation[0] = array( "text"=>'游戏信息列表', "href"=>url('gameinfo','list') );
        if( isset($_POST)&&!empty($_POST) )
        {
            $aIssue = $_POST;
            /* @var $oIssue model_issueinfo */
            $oIssue = A::singleton("model_issueinfo");
            $iResult = $oIssue->issueCreate( $aIssue["lotteryid"], $aIssue["issuestart"],
                    $aIssue["issueend"], $aIssue["issuenostart"] );
            switch( $iResult )
            {
                case -1:
                    sysMessage( '操作失败:彩种ID错误.', 1, $aLocation );
                    break;
                case -2:
                    sysMessage( '操作失败:彩种不存在.', 1, $aLocation );
                    break;
                case -3:
                    sysMessage( '操作失败:生成奖期失败.', 1, $aLocation );
                    break;
                default:
                    sysMessage( '操作成功.', 0, $aLocation );
                    break;
            }
        }
        else
        {
            $iLotteryId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
            if( $iLotteryId ==0 )
            {
                sysMessage( "操作失败:数据错误.", 1, $aLocation );
            }
            $GLOBALS['oView']->assign( "ur_here", "增加游戏奖期" );
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $aLottery = $oLottery->lotteryGetOne( "*", "`lotteryid`='".$iLotteryId."'" );
            $GLOBALS['oView']->assign( "lottery",       $aLottery );
            $GLOBALS['oView']->assign( "actionlink",    $aLocation[0] );
            $oLottery->assignSysInfo();
            $GLOBALS['oView']->display( "gameinfo_issueadd.html" );
            EXIT;
        }
    }



    /**
     * 封锁表列表
     * @author  SAUL
     * URL: ./index.php?controller=gameinfo&action=lockslist
     */
    function actionlockslist()
    {
        $aLocation[0]   = array( "text"=>'游戏信息管理', "href"=>url('gameinfo','list') );
        $iLotteryId     = isset($_GET["id"])&&is_numeric($_GET["id"])? intval($_GET["id"]) : 0;
        if($iLotteryId ==0)
        {
            sysMessage( '操作失败:数据不正确.', 1, $aLocation[0] );
        }
        /* @var $oLocksName model_locksname */
        $oLocksName = A::singleton("model_locksname");
        $aLocksName = $oLocksName->locksLotteryGetAll( 'A.*,B.`cnname`', "A.`lotteryid`='".$iLotteryId."'" );
        $GLOBALS['oView']->assign( "alocksname", $aLocksName );
        $GLOBALS['oView']->assign( "ur_here", "封锁表管理" );
        $aLocation[1] =array("text"=>'增加封锁表',
            "href"=>url('gameinfo','locksadd',array('lotteryid'=>$iLotteryId)) );
        $GLOBALS['oView']->assign( "actionlink",    $aLocation[0] );
        $GLOBALS['oView']->assign( "actionlink2",   $aLocation[1] );
        $oLocksName->assignSysInfo();
        $GLOBALS['oView']->display( "gameinfo_lockslist.html" );
        EXIT;
    }



    /**
     * 增加封锁表
     * @author SAUL
     * URL: ./index.php?controller=gameinfo&action=locksadd
     */
    function actionlocksadd()
    {
        $aLocation[0] = array( "text"=>'游戏信息管理', "href"=>url('gameinfo','list') );
        if( isset($_POST)&&!empty($_POST) )
        {
            $aLocks = $_POST;
            /* @var $olocks model_locksname */
            $olocks = A::singleton("model_locksname");
            $iResult = $olocks->locksnameInsert( $aLocks );
            switch( $iResult )
            {
                case -1:
                    sysMessage( '操作失败:数据错误.', 1, $aLocation );
                    break;
                case -2:
                    sysMessage( '操作失败:封锁表名称为空.', 1, $aLocation );
                    break;
                case -3:
                    sysMessage( '操作失败:彩种信息错误.', 1, $aLocation );
                    break;
                case -4:
                    sysMessage( '操作失败:彩种信息不存在.', 1, $aLocation );
                    break;
                case -5:
                    sysMessage( '操作失败:封锁表封锁值错误.', 1, $aLocation );
                    break;
                case -6:
                    sysMessage( '操作失败:封锁表名称重复.', 1, $aLocation );
                    break;
                default:
                    sysMessage( '操作成功.', 0, $aLocation );
                    break;
            }
        }
        else
        {
            $iLotteryId = isset($_GET["lotteryid"])&&is_numeric($_GET["lotteryid"])?intval($_GET["lotteryid"]):0;
            if( $iLotteryId==0 )
            {
                sysMessage('操作失败:数据错误.', 1, $aLocation[0] );
            }
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $aLottery = $oLottery->lotteryGetOne("`cnname`,`lotteryid`", "`lotteryid`='".$iLotteryId."'" );
            $GLOBALS['oView']->assign( "alottery",  $aLottery );
            $GLOBALS['oView']->assign( "ur_here",   "增加封锁表" );
            $GLOBALS['oView']->assign( "actionlink",$aLocation[0] );
            $GLOBALS['oView']->assign( "action", url('gameinfo','locksadd') );
            $oLottery->assignSysInfo();
            $GLOBALS['oView']->display("gameinfo_locksinfo.html");
            EXIT;
        }
    }



    /**
     * 修改封锁表
     * @author SAUL
     * URL:./index.php?controller=gameinfo&action=locksedit
     */
    function actionLocksedit()
    {
        $aLocation[0] =array( "text"=>'游戏信息管理', "href"=>url('gameinfo','list') );
        if( isset($_POST)&&!empty($_POST) )
        {
            $aLocks  = $_POST;
            $iLocks  = intval($aLocks["locksid"]);
            /* @var $oLocks model_locksname */
            $oLocks  = A::singleton("model_locksname");
            $iResult = $oLocks->locksnameUpdate( $aLocks, "`locksid`='".$iLocks."'" );
            switch( $iResult )
            {
                case -1:
                    sysMessage( '操作失败:数据错误.', 1, $aLocation );
                    break;
                case -2:
                    sysMessage( '操作失败:封锁表名称为空.', 1, $aLocation );
                    break;
                case -3:
                    sysMessage( '操作失败:彩种信息错误.', 1, $aLocation );
                    break;
                case -4:
                    sysMessage( '操作失败:彩种信息不存在.', 1, $aLocation );
                    break;
                case -5:
                    sysMessage( '操作失败:封锁表封锁值错误.', 1, $aLocation );
                    break;
                case -6:
                    sysMessage( '操作失败:封锁表名称重复.', 1, $aLocation );
                    break;
                case 0:
                    sysMessage('操作失败:数据没有变更.', 1, $aLocation );
                    break;
                default:
                    sysMessage( '操作成功.', 0, $aLocation );
                    break;
            }
        }
        else
        {
            $iLocksId = isset($_GET["locksid"])&&is_numeric($_GET["locksid"]) ? intval($_GET["locksid"]) : 0;
            if($iLocksId==0)
            {
                sysMessage( '操作失败:数据错误.', 1, $aLocation );
            }
            /* @var $oLocks model_locksname */
            $oLocks = A::singleton("model_locksname");
            $aLocks = $oLocks->locksLotteryGetAll("A.*,B.`cnname`,B.`lotteryid`",
                            "A.`locksid`='".$iLocksId."'" );
            if( empty($aLocks) )
            {
                sysMessage( '操作失败:封锁表信息不存在.', 1, $aLocation );
            }
            $GLOBALS['oView']->assign( "alottery", $aLocks[0] );
            $GLOBALS['oView']->assign( "ur_here", "修改封锁表" );
            $GLOBALS['oView']->assign( "actionlink", $aLocation[0] );
            $GLOBALS['oView']->assign( "action", url('gameinfo','locksedit') );			
            $oLocks->assignSysInfo();
            $GLOBALS['oView']->display("gameinfo_locksinfo.html");
            EXIT;
        }
    }



    /**
     * 删除封锁表
     * @author SAUL
     * URL: ./index.php?controller=gameinfo&action=locksdel
     */
    function actionlocksdel()
    {
        $aLocation[0] = array( "text"=>'游戏信息管理', "href"=>url('gameinfo','list') );
        $iLocksId = isset($_GET["locksid"])&&is_numeric($_GET["locksid"]) ? intval($_GET["locksid"]) : 0;
        if( $iLocksId==0 )
        {
            sysMessage( '操作失败:数据错误.', 1, $aLocation );
        }
        /* @var $olocks model_locksname */
        $olocks = A::singleton("model_locksname");
        $iResult =$olocks->locksLotteryDel( "`locksid`='".$iLocksId."'" );
        if( $iResult>0 )
        {
            sysMessage( "操作成功.", 0, $aLocation );
        }
        else
        {
            sysMessage( "操作失败.", 1, $aLocation );
        }
    }



    /**
     * 奖期修改
     * @author SAUL
     * URL: ./index.php?controller=gameinfo&action=issueedit
     */
    function actionIssueedit()
    {
        $aLocation[0] = array( "text"=>"游戏信息列表", "href"=>url('gameinfo','list') );
        if( isset($_POST)&&!empty($_POST) )
        {
            $aIssue = $_POST;
            /* @var $oIssue model_issueinfo */
            $oIssue = A::singleton("model_issueinfo");
            $iResult = $oIssue->issueUpdateTime( $aIssue, "`issueid`='".$aIssue["issueid"]."'" );
            switch( $iResult )
            {
                case -1:
                    sysMessage( '操作失败:数据错误.', 1, $aLocation );
                    break;
                case -2:
                    sysMessage( '操作失败:开售时间错误.', 1, $aLocation );
                    break;
                case -3:
                    sysMessage( '操作失败:停售时间错误.', 1, $aLocation );
                    break;
                case -4:
                    sysMessage( '操作失败:最后撤单时间错误.', 1, $aLocation );
                    break;
                case -5:
                    sysMessage( '操作失败:动态调价开始时间错误.', 1, $aLocation );
                    break;
                case -6:
                    sysMessage( '操作失败:动态调价结束时间错误.', 1, $aLocation );
                    break;
                case -7:
                    sysMessage( '操作失败:条件错误.', 1, $aLocation );
                    break;
                case 0:
                    sysMessage( '操作失败:数据没有更改.', 1, $aLocation );
                    break;
                default:
                    sysMessage( '操作成功.', 0, $aLocation );
                    break;
            }
        }
        else
        {
            $iIssueId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
            if( $iIssueId<=0 )
            {
                sysMessage( '操作失败:数据错误.', 1, $aLocation );
            }
            /* @var $oIssue model_issueinfo */
            $oIssue = A::singleton("model_issueinfo");
            $aIssue = $oIssue->IssueGetOne( "A.*,B.`cnname`", "`issueid`='".$iIssueId."'",
                " left join `lottery` AS B ON (A.`lotteryid`=B.`lotteryid`) " );
            $GLOBALS['oView']->assign( "aIssue", $aIssue );
            $GLOBALS['oView']->assign( "ur_here", "修改奖期信息" );
            $GLOBALS['oView']->assign( "actionlink",$aLocation[0] );
            $aLocation[1] = array( "text"=>'游戏奖期列表',
                "href"=>url('gameinfo',"issuelist",array('id'=>$aIssue["lotteryid"]) ) );
            $GLOBALS['oView']->assign( "actionlink2", $aLocation[1] );
            $oIssue->assignSysInfo();
            $GLOBALS['oView']->display("gameinfo_issueedit.html");
            EXIT;
        }
    }



    /**
     * 奖期删除
     * @author SAUL
     * URL: ./index.php?controller=gameinfo&action=issuedel
     */
    function actionIssueDel()
    {
        $aLocation[0] = array(  "text"=>'游戏信息管理', "href"=>url('gameinfo','list') );
        $iIssueId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iIssueId==0 )
        {
            sysMessage('操作失败.',1,$aLocation);
        }
        /* @var $oIssue model_issueinfo */
        $oIssue = A::singleton("model_issueinfo");
        $bResult = $oIssue->issueDel("`issueid`='".$iIssueId."'");
        if( $bResult )
        {
            sysMessage( '操作成功', 0, $aLocation );
        }
        else
        {
            sysMessage( '操作失败', 1, $aLocation );
        }
    }



    /**
     * 停止总代奖金组
     * @author SAUL
     * URL: ./index.php?controller=gameinfo&action=userpgstop
     */
    function actionUserpgstop()
    {
        $aLocation[0] =array( "text"=>'分配奖金组', "href"=>url('user',"setprize") );
        $iPgId = isset($_GET["pgid"])&&is_numeric($_GET["pgid"]) ? intval($_GET["pgid"]) : 0;
        if( $iPgId ==0 )
        {
            sysMessage( '操作失败:数据错误.', 1, $aLocation );
        }
        /* @var $oUserPrizeGroup model_userprizegroup */
        $oUserPrizeGroup = A::singleton("model_userprizegroup");
        $iResult = $oUserPrizeGroup->userPrizegroupstop( $iPgId );
        switch($iResult)
        {
            case 0:
                sysMessage( '操作失败:数据错误.',1, $aLocation );
                break;
            case -1:
                sysMessage( '操作失败:用户奖组不存在.', 1, $aLocation );
                break;
            case -2:
                sysMessage( '操作失败:用户奖组处于未激活状态.', 1, $aLocation );
                break;
            case -3:
                sysMessage( '操作失败:更新用户状态时候错误.', 1, $aLocation );
                break;
            case -4:
                sysMessage( '操作失败:更新用户奖组状态时候失败.', 1, $aLocation );
                break;
            default:
                sysMessage( '操作成功', 0, $aLocation );
                break;
        }
    }



    /**
     * 对总代奖金组进行调整
     * @author SAUL
     * URL: ./index.php?controller=gameinfo&action=userpgedit
     */
    function actionUserpgedit()
    {
        $aLocation[0] = array( "text"=>'分配奖金组', "href"=>url('user','setprize') );
        if( isset($_POST)&&!empty($_POST) )
        {
            $aUserPrizeGroup = $_POST;
            $iUserPrizeGroupId = $aUserPrizeGroup["userpgid"];
            /* @var $oUserPrizeGroup model_userprizegroup */
            $oUserPrizeGroup = A::singleton("model_userprizegroup");
            $iResult = $oUserPrizeGroup->userpgUpdate( $aUserPrizeGroup, $iUserPrizeGroupId );
            switch( $iResult )
            {
                case -1:
                    sysMessage( '操作失败:数据不正确.', 1, $aLocation );
                    break;
                case -2:
                    sysMessage( '操作失败:用户奖金组不存在.', 1, $aLocation );
                    break;
                case -3:
                    sysMessage( '操作失败:对应奖金组模板不存在.', 1, $aLocation );
                    break;
                case -4:
                    sysMessage( '操作失败:模板没有同步.', 1, $aLocation );
                    break;
                case -5:
                    sysMessage( '操作失败:彩种不存在.', 1, $aLocation );
                    break;
                case -6:
                    sysMessage( '操作失败:奖金错误.', 1, $aLocation );
                    break;
                case -7:
                    sysMessage( '操作失败:返点设置错误.', 1, $aLocation );
                    break;
                case -8:
                    sysMessage( '操作失败:公司最小留水计算错误.', 1, $aLocation );
                    break;
                case -9:
                    sysMessage( '操作失败:更新用户奖金组详情时候失败.', 1, $aLocation );
                    break;
                case -10:
                    sysMessage( '操作失败:总代的返点比下级的返点+返点差小.', 1, $aLocation );
                    break;
                case -11:
                    sysMessage( '操作失败:更新用户奖金组时候失败.', 1, $aLocation );
                    break;
                default:
                    sysMessage( '操作成功', 0, $aLocation );
                    break;
            }
        }
        else
        {
            $iUserPgId = isset($_GET["pgid"])&&is_numeric($_GET["pgid"]) ? intval($_GET["pgid"]) : 0;
            if( $iUserPgId==0 )
            {
                sysMessage( '操作失败:数据错误.', 1, $aLocation );
            }
            /* @var $oUserGroup model_userprizegroup */
            $oUserGroup = A::singleton("model_userprizegroup");
            $aUserGroup = $oUserGroup->userpgGetOne( '*', "`userpgid`='".$iUserPgId."'" );
            if( empty($aUserGroup) )
            {
                sysMessage( '操作失败:用户奖组信息不存在.', 1, $aLocation );
            }
            /* @var $oUserPrizeLevel model_userprizelevel */
            $oUserPrizeLevel = A::singleton("model_userprizelevel");
            $aUserPrizeLevel = $oUserPrizeLevel->userPglevelGetList( "A.*",
                    "A.`userpgid`='".$iUserPgId."'", "", 0 );
            /* @var $oMethod model_method */
            $oMethod = A::singleton("model_method");
            $aMethod = $oMethod->methodGetList( "a.`methodid`,a.`methodname`,a.`level`,a.`nocount`,"
                ."a.`totalmoney`,b.`cnname`,b.`minprofit`",
                "a.`lotteryid`='".$aUserGroup["lotteryid"]."' and a.`pid`='0'", "", 0 );
            $GLOBALS['oView']->assign("minprofit",$aMethod[0]["minprofit"]);
            foreach( $aMethod as &$method )
            {
                $method['nocount'] = @unserialize($method["nocount"]);
            }
            $GLOBALS['oView']->assign( "data_method",       json_encode($aMethod) );
            $GLOBALS['oView']->assign( "data_userprize",    json_encode($aUserPrizeLevel) );
            $GLOBALS['oView']->assign( "aUserGroup",        $aUserGroup );
            $GLOBALS["oView"]->assign( "ur_here",           "用户奖金组修改" );
            $GLOBALS['oView']->assign( "actionlink",        $aLocation[0] );
            $oMethod->assignSysInfo();
            $GLOBALS['oView']->display( "gameinfo_userprizeinfo.html" );
            EXIT;
        }
    }

    

    /**
     * 对总代奖金组进行查看
     * @author Tom  2009-12-03 16:30
     * URL: ./index.php?controller=gameinfo&action=userpgview
     */
    function actionUserpgview()
    {
        $aLocation[0] = array( "text"=>'分配奖金组', "href"=>url('user','setprize') );
		$iUserPgId = isset($_GET["pgid"])&&is_numeric($_GET["pgid"]) ? intval($_GET["pgid"]) : 0;
		if( $iUserPgId==0 )
		{
		    sysMessage( '操作失败:数据错误.', 1, $aLocation );
		}
		/* @var $oUserGroup model_userprizegroup */
		$oUserGroup = A::singleton("model_userprizegroup");
		$aUserGroup = $oUserGroup->userpgGetOne( '*', "`userpgid`='".$iUserPgId."'" );
		if( empty($aUserGroup) )
		{
		    sysMessage( '操作失败:用户奖组信息不存在.', 1, $aLocation );
		}
		/* @var $oUserPrizeLevel model_userprizelevel */
		$oUserPrizeLevel = A::singleton("model_userprizelevel");
		$aUserPrizeLevel = $oUserPrizeLevel->userPglevelGetList( "A.*",
		        "A.`userpgid`='".$iUserPgId."'", "", 0 );
		/* @var $oMethod model_method */
		$oMethod = A::singleton("model_method");
		$aMethod = $oMethod->methodGetList( "a.`methodid`,a.`methodname`,a.`level`,a.`nocount`,"
		    ."a.`totalmoney`,b.`cnname`,b.`minprofit`",
		    "a.`lotteryid`='".$aUserGroup["lotteryid"]."' and a.`pid`='0'", "", 0 );
		$GLOBALS['oView']->assign("minprofit",$aMethod[0]["minprofit"]);
		foreach( $aMethod as &$method )
		{
		    $method['nocount'] = @unserialize($method["nocount"]);
		}
		$GLOBALS['oView']->assign( "data_method",       json_encode($aMethod) );
		$GLOBALS['oView']->assign( "data_userprize",    json_encode($aUserPrizeLevel) );
		$GLOBALS['oView']->assign( "aUserGroup",        $aUserGroup );
		$GLOBALS["oView"]->assign( "ur_here",           "用户奖金组查看" );
		$GLOBALS['oView']->assign( "actionlink",        $aLocation[0] );
		$oMethod->assignSysInfo();
		$GLOBALS['oView']->display( "gameinfo_userprizeinfoview.html" );
		EXIT;
    }


    /**
     * 启用用户奖金组
     * @author SAUL
     * URL: ./index.php?controller=gameinfo&action=userpgstart
     */
    function actionUserpgstart()
    {
        $aLocation[0] = array("text"=>'分配奖金组',"href"=>url('user','setprize'));
        if( isset($_GET)&&!empty($_GET) )
        {
            $pgid[0] = intval( $_GET["pgid"] );
            /* @var $oUserPrizeGroup model_userprizegroup */
            $oUserPrizeGroup = A::singleton("model_userprizegroup");
            $iResult = $oUserPrizeGroup->userpgVerifity( $pgid );
            switch( $iResult )
            {
                case -1:
                    sysMessage( '操作失败:更新数据失败.', 1, $aLocation );
                    break;
                case 0:
                    sysMessage( '操作失败:没有数据更新.', 1, $aLocation );
                    break;
                default:
                    sysMessage( '操作成功.',0, $aLocation );
                    break;
            }
        }
        EXIT;
    }
}
?>