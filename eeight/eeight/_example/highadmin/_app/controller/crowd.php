<?php
/* 
 * 文件 : /_app/controller/crowd.php
 * 功能 : 控制器 - 玩法群管理
 * 
 *    - actionPlaycrowdlist   游戏玩法群列表
 *    - actionAddplaycrowd    增加游戏玩法群
 *    - actionEditplaycrowd   修改游戏玩法群
 *    - actionDeleteplaycrowd 删除游戏玩法群
 * 
 * @author     mark
 * @version    1.0.0
 * @package    highadmin
 */

class controller_crowd extends basecontroller 
{
    
    /**
     * 游戏玩法群列表
     *
     */
    public function actionPlaycrowdlist()
    {
        $aLocation[0] = array( "text"=>'游戏信息列表', "href"=>url("gameinfo","list") );
        $iLotteryId   = isset($_GET["lotteryId"]) ? intval($_GET["lotteryId"]) : 0;
        if( $iLotteryId == 0 )
        {
            sysMessage( '操作失败:参数错误', 1, $aLocation );
        }
        $aLocation[1] = array("text"=>'增加游戏玩法群', 'href'=>url('crowd', 'addplaycrowd', array('lotteryid'=>$iLotteryId)) );
        /* @var $oCrowd model_crowd */
        $oCrowd = A::singleton("model_crowd");
        $aCrowd = $oCrowd->crowdGetList($iLotteryId);
        $GLOBALS['oView']->assign( "lotteryId",   $iLotteryId );
        $GLOBALS['oView']->assign( "aCrowd",      $aCrowd );
        $GLOBALS['oView']->assign( "actionlink",  $aLocation[0] );
        $GLOBALS['oView']->assign( "actionlink2", $aLocation[1] );
        $GLOBALS['oView']->assign( "ur_here",     "游戏玩法群列表" );
        $oCrowd->assignSysInfo();
        $GLOBALS['oView']->display( "crowd_playcrowdlist.html" );
        EXIT;
    }
    
    
    /**
     * 修改游戏玩法群
     *
     */
    public  function actionEditplaycrowd()
    {
        /* @var $oCrowd model_crowd */
        $oCrowd = A::singleton("model_crowd");
        if(!empty($_POST))
        {
            $sCrowdName     = isset($_POST['crowdname']) ? $_POST['crowdname'] : '';
            $iCrowdId       = isset($_POST['crowdid']) ? $_POST['crowdid'] : 0;
            $aGroupMethodId = isset($_POST['methodgroupid']) ? $_POST['methodgroupid'] : array();
            $iLotteryId     = isset($_POST['lotteryid']) ? $_POST['lotteryid'] : 0;
            $mFlag = $oCrowd->editCrowdAndMethod($sCrowdName , $iCrowdId , $aGroupMethodId );
            $aLocation[0] = array( "text"=>'游戏玩法群列表', 'href'=>url('crowd', 'playcrowdlist', array('lotteryId'=>$iLotteryId)));
            $aLocation[1] = array( "text"=>'游戏信息列表', 'href'=>url('gameinfo','list') );
            if($mFlag === TRUE)
            {
                sysMessage( '操作成功', 0, $aLocation );
            }
            else
            {
                $sErroeMsg = '';
                switch ($mFlag)
                {
                    case -1:
                        $sErroeMsg = '参数不正确';
                        break;
                    case -2:
                        $sErroeMsg = '玩法群不存在';
                        break;
                    case -30:
                        $sErroeMsg = '事务开始处理失败';
                        break;
                    case -31:
                        $sErroeMsg = '事务回滚处理失败';
                        break;
                    case -32:
                        $sErroeMsg = '事务提交处理失败';
                        break;
                    case -4:
                        $sErroeMsg = '修改玩法群名称失败';
                        break;
                    case -5:
                        $sErroeMsg = '更新玩法的群ID失败';
                        break;
                    default:
                        $sErroeMsg = '末知错误';
                        break;
                }
                sysMessage( $sErroeMsg, 1, $aLocation );
            }
        }
        else
        {
            $iCrowdId = isset($_GET['crowdid']) ? intval($_GET['crowdid']) : 0;
            $aCrowd = $oCrowd->crowdGetItem($iCrowdId);
            /* @var $oMethod model_method */
            $oMethod = A::singleton("model_method");
            $aMethodGroup = $oMethod->getItems($aCrowd['lotteryid'],0);
            $GLOBALS['oView']->assign( "aCrowd",       $aCrowd );
            $GLOBALS['oView']->assign( "aMethodGroup", $aMethodGroup );
            $GLOBALS['oView']->assign( "ur_here",      "修改游戏玩法群" );
            $GLOBALS['oView']->assign( "action",       "editplaycrowd" );
            $GLOBALS['oView']->display( "crowd_playcrowdinfo.html" );
            EXIT;
        }
    }
    
    
    
    /**
     * 增加游戏玩法群
     *
     */
    public  function actionAddplaycrowd()
    {
        /* @var $oCrowd model_crowd */
        $oCrowd = A::singleton("model_crowd");
        if(!empty($_POST))
        {
            $sCrowdName     = isset($_POST['crowdname']) ? $_POST['crowdname'] : '';
            $aGroupMethodId = isset($_POST['methodgroupid']) ? $_POST['methodgroupid'] : array();
            $iLotteryId     = isset($_POST['lotteryid']) ? $_POST['lotteryid'] : 0;
            $mFlag = $oCrowd->insertCrowdAndMethod($sCrowdName, $iLotteryId , $aGroupMethodId );
            $aLocation[0] = array( "text"=>'游戏玩法群列表', 'href'=>url('crowd', 'playcrowdlist', array('lotteryId'=>$iLotteryId)));
            $aLocation[1] = array( "text"=>'游戏信息列表', 'href'=>url('gameinfo', 'list') );
            if( $mFlag === TRUE )
            {
                sysMessage( '操作成功', 0, $aLocation );
            }
            else
            {
                $sErroeMsg = '';
                switch ($mFlag)
                {
                    case -1:
                        $sErroeMsg = '参数不正确';
                        break;
                    case -2:
                        $sErroeMsg = '玩法群已经存在';
                        break;
                    case -3:
                        $sErroeMsg = '彩种不存在';
                        break;
                    case -40:
                        $sErroeMsg = '事务开始处理失败';
                        break;
                    case -41:
                        $sErroeMsg = '事务回滚处理失败';
                        break;
                    case -42:
                        $sErroeMsg = '事务提交处理失败';
                        break;
                    case -5:
                        $sErroeMsg = '增加玩法群失败';
                        break;
                    case -6:
                        $sErroeMsg = '更新玩法的群ID失败';
                        break;
                    default:
                        $sErroeMsg = '末知错误';
                        break;
                }
                sysMessage( $sErroeMsg, 1, $aLocation );
            }
        }
        else
        {
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $aLottery = $oLottery->getItems();
            /* @var $oMethod model_method */
            $oMethod = A::singleton("model_method");
            $aMethodGroup = $oMethod->methodGetList('', "`pid`=0");
            $aLastGroup = array();
            foreach ($aMethodGroup as $aGroup)
            {
                $aLastGroup[$aGroup['lotteryid']][] = array('methodid'=>$aGroup['methodid'], 'methodname'=>$aGroup['methodname']);
            }
            $GLOBALS['oView']->assign( "aLastGroup", $aLastGroup );
            $GLOBALS['oView']->assign( "aLottery", $aLottery );
            $GLOBALS['oView']->assign( "ur_here",  "增加游戏玩法群" );
            $GLOBALS['oView']->assign( "action",   "addplaycrowd" );
            $GLOBALS['oView']->display( "crowd_playcrowdinfo.html" );
            EXIT;
        }
    }
    
    
    /**
     * 删除游戏玩法群
     *
     */
    public  function actionDeleteplaycrowd()
    {
         /* @var $oCrowd model_crowd */
        $oCrowd = A::singleton("model_crowd");
        $iCrowdId = isset($_GET['crowdid']) ? $_GET['crowdid'] : 0;
        $mFlag = $oCrowd->deleteCrowdAndMethod($iCrowdId);
        $aLocation[0] = array( "text"=>'游戏信息列表', 'href'=>url('gameinfo', 'list') );
        if( $mFlag === TRUE )
        {
            sysMessage( '操作成功', 0, $aLocation );
        }
        else
        {
            $sErroeMsg = '';
            switch ($mFlag)
            {
                case -1:
                    $sErroeMsg = '参数不正确';
                    break;
                case -2:
                    $sErroeMsg = '玩法群不存在';
                    break;
                case -30:
                    $sErroeMsg = '事务开始处理失败';
                    break;
                case -31:
                    $sErroeMsg = '事务回滚处理失败';
                    break;
                case -32:
                    $sErroeMsg = '事务提交处理失败';
                    break;
                case -4:
                    $sErroeMsg = '删除玩法群失败';
                    break;
                case -5:
                    $sErroeMsg = '更新玩法的群ID失败';
                    break;
                default:
                    $sErroeMsg = '末知错误';
                    break;
            }
            sysMessage( $sErroeMsg, 1, $aLocation );
        }
    }
}