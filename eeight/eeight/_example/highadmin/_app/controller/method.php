<?php
/* 
 * 文件 : /_app/controller/mehtod.php
 * 功能 : 控制器 - 玩法组管理
 * 
 *    - actionPlaygrouplist     游戏玩法组列表
 *    - actionAddplaygroup      增加玩法组
 *    - actionEditplaygroup     修改玩法组
 *    - actionPlaylist          游戏玩法列表
 *    - actionAddplay           增加游戏玩法
 *    - actionEditplay          修改玩法
 *    - actionPlaystop          停止玩法
 *    - actionPlaystart         开发玩法
 * 
 * @author     Rojer
 * @version    1.0.0
 * @package    highadmin
 * Tom 效验通过于 0208 14:11
 */

class controller_method extends basecontroller 
{
    /**
     * 游戏玩法组列表
     * URL:./index.php?controller=method&action=playlist
     * @author Rojer
     * Tom 效验通过于 0203 14:07
     */
    function actionPlaygrouplist()
    {
        $aLocation[0]   = array( "text"=>'游戏信息列表', "href"=>url("gameinfo","list") );
        $iLotteryId     = isset($_GET["lotteryId"]) ? intval($_GET["lotteryId"]) : 0;
        if( $iLotteryId==0 )
        {
            sysMessage( '操作失败:参数错误', 1, $aLocation );
        }
        $pid = isset($_GET["pid"]) && is_numeric($_GET["pid"]) ? intval($_GET["pid"]) : 0; //组ID
        $aLocation[1] = array("text"=>'增加游戏玩法组', 
                        'href'=>url('method','addplaygroup',array('lotteryId'=>$iLotteryId)) );
        /* @var $oMethod model_method */
        $oMethod = A::singleton("model_method");
        $sFiled = "M.*,L.`cnname`,MC.`crowdname`";
        $sCondition = "M.`lotteryid`='".$iLotteryId."' AND M.`pid`='".$pid."'";
        $aMethodData = $oMethod->methodGetListByCrowd( $sFiled, $sCondition );
        $GLOBALS['oView']->assign( "lotteryId",   $iLotteryId );
        $GLOBALS['oView']->assign( "pid",         $pid );
        $GLOBALS['oView']->assign( "amethod",     $aMethodData );
        $GLOBALS['oView']->assign( "crowdCount",  count($aMethodData));
        $GLOBALS['oView']->assign( "actionlink",  $aLocation[0] );
        $GLOBALS['oView']->assign( "actionlink2", $aLocation[1] );
        $GLOBALS['oView']->assign( "ur_here",     "游戏玩法组列表" );
        $oMethod->assignSysInfo();
        $GLOBALS['oView']->display( "method_playgrouplist.html" );
        EXIT;
    }



    /**
     * 增加玩法组
     * @author rojer
     * Tom 效验通过于 0203 14:08
     */
     public function actionAddplaygroup()
     {
        $iLotteryId = isset($_GET["lotteryId"]) ? intval($_GET["lotteryId"]) : 0;
        if( !$iLotteryId )
        {
            sysMessage('彩种ID不正确！', 1);
        }
        $aLocation[0] = array( "text"=>'游戏信息列表','href'=>url('gameinfo','list') );
        if( isset($_POST) && !empty($_POST) )
        { // 执行增加玩法组操作
            $data['lotteryid']  = $iLotteryId;
            $data['lockname']   = daddslashes($_POST['lockname']);
            //$data['maxlost']    = daddslashes($_POST['maxlost']);
            $data['methodname'] = daddslashes($_POST['methodname']);
            $data['level']      = intval($_POST['level']);
            $data['count']      = (array)$_POST['count'];
            $data['totalmoney'] = $_POST['totalmoney'];
            $data['description']= daddslashes($_POST['description']);
            $aLocation[1] = array( "text"=>'游戏玩法组列表', 'href'=>url('method','playgrouplist',array('lotteryId'=>$iLotteryId)) );
            /* @var $oMethod model_method */
            $oMethod = A::singleton("model_method");
            if( !$oMethod->addPlayGroup( $data ) )
            {
                sysMessage( '操作失败，数据不完整', 0, $aLocation );
            }
            else
            {
                sysMessage( '操作成功', 0, $aLocation );
            }
        }
        else
        { // 显示新增玩法组界面
            $GLOBALS['oView']->assign( "ur_here", "增加游戏玩法组" );
            $GLOBALS['oView']->assign( "actionlink", $aLocation[0] );

            $aLocation[1] = array( 'text'=>'游戏玩法组列表', 'href'=>url('method','playgrouplist',array('lotteryId'=>$iLotteryId)) );
            /* @var $oMethod model_method */
            $oMethod = A::singleton("model_method");
            $aMethod = $oMethod->methodGetList( "a.*", "a.`lotteryid`='".$iLotteryId."' AND a.`pid`='0'", "", 0 );
            $oLottery = A::singleton("model_lottery");
            $aLottery = $oLottery->getItem($iLotteryId);
            $oLocks = A::singleton("model_locks");
            $aLocks = $oLocks->getAllLockTable($iLotteryId);

            $GLOBALS['oView']->assign( "lotteryId", $iLotteryId );
            $GLOBALS['oView']->assign( "actionlink2", $aLocation[1] );
            $GLOBALS['oView']->assign("aLottery", $aLottery);
            $GLOBALS['oView']->assign("aLocks", $aLocks);
            $GLOBALS['oView']->assign("action", 'addplaygroup');
            $GLOBALS['oView']->assign( "methodlist", $aMethod );
            $oLottery->assignSysInfo();
            $GLOBALS['oView']->display( "method_addplaygroup.html" );
            EXIT;
        }
    }



    /**
     * 修改玩法组
     * @author Rojer
     * Tom 效验通过于 0208 11:21
     */
    function actionEditplaygroup()
    {
        $iLotteryId = isset($_GET["lotteryId"]) ? intval($_GET["lotteryId"]) : 0;
        if( !$iLotteryId )
        {
            sysMessage('彩种ID不正确!', 1);
        }
    	if( isset($_POST) && !empty($_POST) )
        {
            $aMethod = array();
            $aMethod['methodname']  = isset($_POST['methodname']) ? $_POST['methodname'] : '';
            $aMethod['lockname']    = isset($_POST['lockname']) ? $_POST['lockname'] : '';
            $aMethod['level']       = isset($_POST['level']) ? $_POST['level'] : 0;
            $aMethod['count']       = isset($_POST['count']) && is_array($_POST['count']) ? $_POST['count'] : array();
            $aMethod['totalmoney']  = isset($_POST['totalmoney']) ? $_POST['totalmoney'] : 0;
            $aMethod['description'] = isset($_POST['description']) ? $_POST['description'] : 0;
            $aMethod['methodid']    = isset($_POST['methodid']) ? $_POST['methodid'] : 0;
            $iMethod = intval($aMethod["methodid"]);
            if( $iMethod==0 )
            {
                sysMessage( '玩法ID不正确!', 1 );
            }
            $aLocation[0] = array( "text"=>"游戏玩法组列表", "href"=>url( "method", "playgrouplist", array( "lotteryId"=>$iLotteryId ) ) );
            /* @var $oMethod model_method */
            $oMethod = A::singleton("model_method");
            $iResult = $oMethod->methodUpdate( $aMethod, "`methodid`='".$iMethod."'" );
            $aRelationalParameters = array(
                    'level' => $aMethod['level'],
                    'count' => $aMethod['count'],
                    'totalmoney' => $aMethod['totalmoney'],
                );
            $aRelationMethod = $oMethod->getItems($iLotteryId, $iMethod);
            foreach( $aRelationMethod as $v )
            {
                $iResult += $oMethod->methodUpdate( $aRelationalParameters, "`methodid`='".$v['methodid']."'" );
            }
            if( $iResult > 0 )
            {
                sysMessage( '操作成功', 0, $aLocation );
            }
            else
            {
                sysMessage('操作失败,数据没有更新', 1, $aLocation);
            }
        }
        else
        {
            $iMethodId = isset($_GET["methodId"]) ? intval($_GET["methodId"]) : 0;
            if( $iMethodId==0 )
            {
                sysMessage( '操作失败,玩法ID不正确', 1 );
            }
            /* @var $oMethod model_method */
            $oMethod = A::singleton("model_method");
            $aMethod = $oMethod->getItem($iMethodId);
            if( empty($aMethod) )
            {
                sysMessage( '操作失败:数据不正确.', 1 );
            }
            $aLocation[0] = array( "text"=>'游戏玩法组列表', "href"=>url('method',"playgrouplist", array('lotteryId'=>$aMethod["lotteryid"])) );
            //彩种信息
            $oLottery = A::singleton("model_lottery");
            $aLottery = $oLottery->getItem($aMethod["lotteryid"]);
            $oLocks = A::singleton("model_locks");
            $aLocks = $oLocks->getAllLockTable($iLotteryId);

            $GLOBALS['oView']->assign( "lotteryId", $iLotteryId );
            $GLOBALS['oView']->assign( "ur_here", "修改玩法组" );
            $GLOBALS['oView']->assign( "aMethod", $aMethod);
            $GLOBALS['oView']->assign( "json_aMethod", json_encode($aMethod));
            $GLOBALS['oView']->assign( "aLottery", $aLottery );
            $GLOBALS['oView']->assign( "aLocks", $aLocks);
            $GLOBALS["oView"]->assign( "actionlink", $aLocation[0] );
            $GLOBALS["oView"]->assign( "action", "editplaygroup" );
            $oMethod->assignSysInfo();
            $GLOBALS['oView']->display("method_addplaygroup.html");
            EXIT;
        }
    }



    /**
     * 游戏玩法列表
     * URL:./index.php?controller=method&action=playlist
     * @author Rojer
     * Tom 效验通过于 0208 11:36
     */
    function actionPlaylist()
    {
        $iLotteryId     = isset($_GET["lotteryId"]) ? intval($_GET["lotteryId"]) : 0;
        $aLocation[0]   = array( "text"=>'游戏玩法组列表', "href"=>url("method","playgrouplist",array('lotteryId'=>$iLotteryId)));
        $pid            = isset($_GET["pid"]) ? intval($_GET["pid"]) : 0;
        if( !$pid || !$iLotteryId )
        {
            sysMessage('查看玩法列表：彩种ID或者玩法组PID不能为空');
        }
        $oMethod = A::singleton("model_method");
        $aLottery = array();
        if( $aItems = $oMethod->getItems($iLotteryId, $pid) )
        {
            $oLottery = A::singleton("model_lottery");
            $aLottery = $oLottery->getItem($aItems[0]["lotteryid"]);
        }
        $GLOBALS['oView']->assign( "lotteryId", $iLotteryId );
        $GLOBALS['oView']->assign( "pid", $pid );
        $GLOBALS['oView']->assign( "items", $aItems );
        $GLOBALS['oView']->assign( "aLottery", $aLottery );
        $GLOBALS['oView']->assign( "ur_here",  "游戏玩法列表" );
        $GLOBALS["oView"]->assign( "actionlink", $aLocation[0]);
        $GLOBALS["oView"]->assign( "actionlink2", array( "text"=>'增加游戏玩法', "href"=>url('method',"addplay", array('lotteryId'=>$iLotteryId, 'pid'=>$pid))));
        $oMethod->assignSysInfo();
        $GLOBALS['oView']->display( "method_playlist.html" );
        EXIT;
    }



    /**
     * 增加游戏玩法
     * URL: ./index.php?controller=method&action=addplay
     * @author Rojer
     * Tom 效验通过于 0208 11:36
     */
    function actionAddplay()
    {
        $iLotteryId = isset($_GET['lotteryId']) ? $_GET['lotteryId'] : 0;
        $aLocation[0] = array( "text"=>'游戏信息列表','href'=>url('gameinfo','list') );
        $oMethod = A::singleton("model_method");

        if( isset($_POST) && !empty($_POST) )
        {
            $pid = intval($_POST['pid']);
            if( !$playGroup = $oMethod->getItem($pid) )
            {
                sysMessage("获取玩法组失败（pid={$pid}）" , 1);
            }
            //对应中奖号码规则
            $aRule =  array();
            $aRule['startposition'] = isset($_POST['startposition']) ? intval($_POST['startposition']) : 0;
            $aRule['codecount']     = isset($_POST['codecount']) ? intval($_POST['codecount']) : 0;
            $aRule['issum']         = isset($_POST['issum']) && $_POST['issum'] == 'on' ? 1 : 0;
            $aRule['tagcheck']       = isset($_POST['tagcheck']) ? daddslashes($_POST['tagcheck']) : '';
            $aRule['tagbonus']       = isset($_POST['tagbonus']) ? daddslashes($_POST['tagbonus']) : '';
            $aMethod = array();
            $aMethod['methodname']      = isset($_POST['methodname']) ? $_POST['methodname'] : '';
            $aMethod['code']            = isset($_POST['code']) ? $_POST['code'] : '';
            $aMethod['jscode']          = isset($_POST['jscode']) ? $_POST['jscode'] : '';
            $aMethod['addslastype']     = isset($_POST['addslastype']) ? intval($_POST['addslastype']) : 0;
            $aMethod['pid']             = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
            $aMethod['functionname']    = isset($_POST['functionname']) ? $_POST['functionname'] : '';
            $aMethod['functionrule']    = serialize($aRule);
            $aMethod['initlockfunc']    = isset($_POST['initlockfunc']) ? $_POST['initlockfunc'] : '';
            $aMethod['isclose']         = isset($_POST['isclose']) ? $_POST['isclose'] : $playGroup['isclose'];
            $aMethod['islock']          = isset($_POST['islock']) ? $_POST['islock'] : $playGroup['islock'];
            $aMethod['lockname']        = isset($_POST['lockname']) ? $_POST['lockname'] : $playGroup['lockname'];
            $aMethod['maxcodecount']    = isset($_POST['maxcodecount']) ? $_POST['maxcodecount'] : 0;
            $aMethod['level']           = isset($_POST['level']) ? $_POST['level'] : 0;
            $aMethod['count']           = isset($_POST['count']) && is_array($_POST['count']) ? $_POST['count'] : array();
            $aMethod['totalmoney']      = isset($_POST['totalmoney']) ? $_POST['totalmoney'] : 0;
            $aMethod['modes']           = isset($_POST['modes']) && is_array($_POST['modes']) ? $_POST['modes'] : array();
            $aMethod['description']     = isset($_POST['description']) ? $_POST['description'] : '';
            $aMethod["lotteryid"]       = $iLotteryId;
            $aLocation[1] = array( "text"=>'游戏玩法列表', 'href'=>url('method','playlist',array('lotteryId' => $iLotteryId, 'pid' => $pid)) );
            $aLocation[2] = array( "text"=>'增加游戏玩法', 'href'=>url('method','addplay',array('lotteryId' => $iLotteryId, 'pid' => $pid)) );
            $iResult = $oMethod->addPlay( $aMethod );
            switch( $iResult )
            {
                case 0:
                    sysMessage( '操作失败:数据不正确', 1, $aLocation );
                    BREAK;
                case -1:
                    sysMessage( '操作失败:彩种类型错误.', 1, $aLocation );
                    BREAK;
                case -2:
                    sysMessage( '操作失败:彩种名称为空.', 1, $aLocation );
                    BREAK;
                case -3:
                    sysMessage( '操作失败:彩种的中奖函数名称为空.', 1, $aLocation );
                    BREAK;
                case -5:
                    sysMessage( '操作失败:奖级个数错误.', 1, $aLocation );
                    BREAK;
                case -6:
                    sysMessage( '操作失败:转直注数错误.', 1, $aLocation );
                    BREAK;
                case -7:
                    sysMessage( '操作失败:初始化函数为空.', 1, $aLocation );
                    BREAK;
                default:
                    sysMessage( '操作成功', 0, $aLocation );
                    BREAK;
            }
        }
        else
        {
            $pid = intval($_GET['pid']);
            if (!$playGroup = $oMethod->getItem($pid))
            {
                sysMessage("获取玩法组失败（pid={$pid}）", 1);
            }
            $aLottery = $tmp = array();
            if( $playGroups = $oMethod->getItems($playGroup['lotteryid'], 0) )
            {
                $oLottery = A::singleton("model_lottery");
                $aLottery = $oLottery->getItem($playGroups[0]["lotteryid"]);
                $tmp = self::array_spec_key($playGroups, 'methodid');
            }
            $oLocks = A::singleton("model_locks");
            $aLocks = $oLocks->getAllLockTable($iLotteryId);
            $GLOBALS['oView']->assign( "lotteryId", $iLotteryId );
            $GLOBALS['oView']->assign( "modes", $GLOBALS['config']['modes']);
            $GLOBALS['oView']->assign( "pid", $pid );
            $GLOBALS['oView']->assign( "playGroup", $tmp[$pid] );
            $GLOBALS['oView']->assign( "playGroups", $playGroups);
            $GLOBALS['oView']->assign( "json_playGroups", json_encode(self::array_spec_key($playGroups, 'methodid')));
            $GLOBALS['oView']->assign( "aLottery", $aLottery );
            $GLOBALS['oView']->assign( "aLocks", $aLocks);
            $GLOBALS['oView']->assign( "ur_here", "增加游戏玩法" );
            $GLOBALS['oView']->assign( "actionlink", $aLocation[0] );
            $GLOBALS['oView']->assign( "action", "addplay" );
            $GLOBALS['oView']->display( "method_addplay.html" );
            EXIT;
        }
    }



    /**
     * 修改玩法
     * URL:./index.php?controller=method&action=playedit
     * @author Rojer
     * Tom 效验通过于 0208 13:45
     */
    function actionEditplay()
    {
        $iLotteryId = isset($_GET['lotteryId']) ? intval($_GET['lotteryId']) : 0;
        $pid        = isset($_GET['pid']) ? $_GET['pid'] : 0;
    	if( isset($_POST)&&!empty($_POST) )
        {
            //对应中奖号码规则
            $aRule =  array();
            $aRule['startposition'] = isset($_POST['startposition']) ? intval($_POST['startposition']) : 0;
            $aRule['codecount']     = isset($_POST['codecount']) ? intval($_POST['codecount']) : 0;
            $aRule['issum']         = isset($_POST['issum']) && $_POST['issum'] == 'on' ? 1 : 0;
            $aRule['tagcheck']       = isset($_POST['tagcheck']) ? daddslashes($_POST['tagcheck']) : '';
            $aRule['tagbonus']       = isset($_POST['tagbonus']) ? daddslashes($_POST['tagbonus']) : '';
            $aMethod = array();
            $aMethod['methodname']      = isset($_POST['methodname']) ? $_POST['methodname'] : '';
            $aMethod['code']            = isset($_POST['code']) ? $_POST['code'] : '';
            $aMethod['jscode']          = isset($_POST['jscode']) ? $_POST['jscode'] : '';
            $aMethod['addslastype']     = isset($_POST['addslastype']) ? intval($_POST['addslastype']) : 0;
            $aMethod['pid']             = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
            $aMethod['functionname']    = isset($_POST['functionname']) ? $_POST['functionname'] : '';
            $aMethod['functionrule']    = serialize($aRule);
            $aMethod['initlockfunc']    = isset($_POST['initlockfunc']) ? $_POST['initlockfunc'] : '';
            $aMethod['isclose']         = isset($_POST['isclose']) ? $_POST['isclose'] : 0;
            $aMethod['islock']          = isset($_POST['islock']) ? $_POST['islock'] : 0;
            $aMethod['lockname']        = isset($_POST['lockname']) ? $_POST['lockname'] : '';
            $aMethod['maxcodecount']    = isset($_POST['maxcodecount']) ? $_POST['maxcodecount'] : 0;
            $aMethod['level']           = isset($_POST['level']) ? $_POST['level'] : 0;
            $aMethod['count']           = isset($_POST['count']) && is_array($_POST['count']) ? $_POST['count'] : array();
            $aMethod['totalmoney']      = isset($_POST['totalmoney']) ? $_POST['totalmoney'] : 0;
            $aMethod['modes']           = isset($_POST['modes']) && is_array($_POST['modes']) ? $_POST['modes'] : array();
            $aMethod['description']     = isset($_POST['description']) ? $_POST['description'] : '';
            $aMethod["lotteryid"]       = $iLotteryId;
            $aMethod["methodid"]        = isset($_POST['methodid']) ? $_POST['methodid'] : 0;
            $iMethod = intval($aMethod["methodid"]);
            if( $iMethod == 0 )
            {
                sysMessage( '操作失败', 1 );
            }
            $aLocation[0] = array( "text"=>"游戏玩法列表",
                "href"=>url( "method", "playlist", array( 'lotteryId' => $iLotteryId, "pid"=>$aMethod["pid"] ) ) );
            $aLocation[1]   = array( "text"=>'游戏玩法组列表', "href"=>url("method","playgrouplist",array('lotteryId'=>$iLotteryId)));
            unset( $aMethod["methodid"] );
            /* @var $oMethod model_method */
            $oMethod = A::singleton("model_method");
            $iResult = $oMethod->methodUpdate( $aMethod, "`methodid`='".$iMethod."'" );
            switch( $iResult )
            {
                case -1:
                    sysMessage( '操作失败:数据错误', 1, $aLocation );
                    BREAK;
                case -2:
                    sysMessage( '操作失败:彩种类型错误', 1, $aLocation );
                    BREAK;
                case -3:
                    sysMessage( '操作失败:彩种名称为空', 1, $aLocation );
                    BREAK;
                case -4:
                    sysMessage( '操作失败:彩种的中奖函数名称为空', 1, $aLocation );
                    BREAK;
                case -5:
                    sysMessage( '操作失败:封锁表名称为空', 1, $aLocation );
                    BREAK;
                case -6:
                    sysMessage( '操作失败:奖级个数错误', 1, $aLocation );
                    BREAK;
                case -7:
                    sysMessage( '操作失败:转直注数错误', 1, $aLocation );
                    BREAK;
                case -8:
                    sysMessage( '操作失败:初始化函数为空', 1, $aLocation );
                    BREAK;
                case 0:
                    sysMessage( '操作失败:数据没有更新', 1, $aLocation );
                    BREAK;
                default:
                    sysMessage( '操作成功', 0, $aLocation );
                    BREAK;
            }
        }
        else
        {
            $iMethodId = isset($_GET["methodId"]) ? intval($_GET["methodId"]) : 0;
            if( $iMethodId==0 )
            {
                sysMessage( '操作失败:数据不正确.', 1 );
            }
            $oMethod = A::singleton("model_method");
            $aMethod = $oMethod->getItem($iMethodId);
            if( empty($aMethod) )
            {
                sysMessage( '操作失败:数据不正确.', 1 );
            }
            $aMethod['modes'] = explode(',', $aMethod['modes']);
            $aRule = unserialize($aMethod['functionrule']);//对应中奖号码规则
            $playGroups = $oMethod->getItems($iLotteryId, 0);
            $oLottery = A::singleton("model_lottery");
            $aLottery = $oLottery->getItem($aMethod["lotteryid"]);
            $oLocks = A::singleton("model_locks");
            $aLocks = $oLocks->getAllLockTable($iLotteryId);
            $GLOBALS['oView']->assign( "lotteryId", $iLotteryId );
            $GLOBALS['oView']->assign( "pid", $pid );
            $GLOBALS['oView']->assign( "playGroups", $playGroups );
            $GLOBALS['oView']->assign( "json_playGroups", json_encode(self::array_spec_key($playGroups, 'methodid')));
            $GLOBALS['oView']->assign( "aMethod", json_encode($aMethod) );
            $GLOBALS['oView']->assign( "method", $aMethod );
            $GLOBALS['oView']->assign( "rule", $aRule );
            $GLOBALS['oView']->assign( "modes", $GLOBALS['config']['modes']);
            $GLOBALS['oView']->assign( "aLottery", $aLottery);
            $GLOBALS['oView']->assign( "aLocks", $aLocks);
            $GLOBALS["oView"]->assign( "actionlink", array( "text"=>'游戏玩法列表', "href"=>url('method',"playlist", array('lotteryId'=>$iLotteryId, 'pid' => $pid))));
            $GLOBALS['oView']->assign( "ur_here", "修改玩法" );
            $GLOBALS['oView']->assign( "action", "editplay" );
            $oMethod->assignSysInfo();
            $GLOBALS['oView']->display("method_addplay.html");
            EXIT;
        }
    }



    /**
     * 停止玩法
     * @return <bool>
     * @author Rojer
     * Tom 效验通过于 0208 13:50
     */
    function actionPlaystop()
    {
        $iMethodId   = isset($_GET["methodId"])? intval($_GET["methodId"]) : 0 ;
        $iLotteryId  = isset($_GET["lotteryId"]) ? intval($_GET["lotteryId"]) : 0 ;
        $iPid        = isset($_GET["pid"]) ? intval($_GET["pid"]) : 0 ;
        if( $iMethodId==0 )
        {
            sysMessage('操作失败:数据不正确.', 1 );
        }
        /* @var $oMethod model_method */
        $oMethod = A::singleton("model_method");
        $aMethod = $oMethod->getItem($iMethodId);
        if( empty($aMethod) )
        {
            sysMessage('操作失败:数据不正确.', 1 );
        }
        if( !$iPid )
        {
            $aLocation[0] = array( "text"=>"玩法组列表", "href"=>url('method','playgrouplist',array('lotteryId'=>$iLotteryId)));
        }
        else
        {
            $aLocation[0] = array( "text"=>"玩法列表", "href"=>url('method','playlist',array('lotteryId'=>$iLotteryId, 'pid'=>$aMethod['pid'])));
        }
        if( $oMethod->setMethodstatus( $iMethodId, 1 ) )
        {
            sysMessage('操作成功', 0, $aLocation );
        }
        else
        {
            sysMessage('操作失败', 1, $aLocation );
        }
        return TRUE;
    }



    /**
     * 开始玩法
     * @return <bool>
     * @author Rojer
     */
    function actionPlaystart()
    {
        $iMethodId = isset($_GET["methodId"])? intval($_GET["methodId"]) : 0 ;
        $iLotteryId = isset($_GET["lotteryId"]) ? intval($_GET["lotteryId"]) : 0 ;
        $iPid = isset($_GET["pid"]) ? intval($_GET["pid"]) : 0 ;
        if( $iMethodId==0 )
        {
            sysMessage('操作失败:数据不正确.', 1 );
        }
        /* @var $oMethod model_method */
        $oMethod = A::singleton("model_method");
        $aMethod = $oMethod->getItem($iMethodId);
        if( empty($aMethod) )
        {
            sysMessage('操作失败:数据不正确.', 1 );
        }
        if( !$iPid )
        {
            $aLocation[0] = array( "text"=>"玩法组列表", "href"=>url('method','playgrouplist',array('lotteryId'=>$iLotteryId)));
        }
        else
        {
            $aLocation[0] = array( "text"=>"玩法列表", "href"=>url('method','playlist',array('lotteryId'=>$iLotteryId, 'pid'=>$aMethod['pid'])));
        }
        if( $oMethod->setMethodstatus( $iMethodId, 0 ) )
        {
            sysMessage('操作成功', 0, $aLocation );
        }
        else
        {
            sysMessage('操作失败', 1, $aLocation );
        }

        return true;
    }



    /**
     * 应添加到公共函数库
     * @return array
     * @author Rojer
     */
    static private function array_spec_key($array, $key, $unset_key = false)
    {
        if (empty($array) || !is_array($array))
        {
            return array();
        }

        $new_array = array();
        foreach ($array AS $value)
        {
            if (!isset($value[$key]))
            {
                continue;
            }
            $value_key = $value[$key];
            if ($unset_key === true)
            {
                unset($value[$key]);
            }
            $new_array[$value_key] = $value;
        }

        return $new_array;
    }
}
?>