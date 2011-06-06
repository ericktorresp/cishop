<?php
/**
 * 文件 : /_app/controller/locks.php
 * 功能 : 控制器  - 封锁调价相关
 *
 * 功能:
 *    - actionAdjsutList()  调价方案表
 *    - actionAdjsutAdd()   调价调价方案
 *    - actionUpdate()      更新调价方案
 *    - actionAdjsutDel()   删除调价方案
 *    - actionCheck()       审核调价方案
 *    - actionActive()      激活调价方案
 *    - actionUnactive()    取消激活调价方案
 *    - action3d()          3D封锁查看
 *    - actionP3()          P3封锁查看
 *    - actionP5()          P5后2方案查看
 * 
 * @author    JAMES, SAUL
 * @version   1.2.0
 * @package   lowadmin
 */

class controller_locks extends basecontroller
{
    /**
     * 方案列表
     * @author james
     * URL: ./index.php?controller=locks&action=adjustlist
     */
    function actionAdjustList()
    {
        if( empty($_POST['flag']) || $_POST['flag']!='adjust' )
        {//获取所有彩种
            if( !empty($_REQUEST['lotteryid']) && is_numeric($_REQUEST['lotteryid']) )
            {//默认显示的彩种
                $iLotteryId = intval($_REQUEST['lotteryid']) > 0 ? intval($_REQUEST['lotteryid']) : 0;
            }
            else 
            {
                $iLotteryId = 0;
            }
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $aLotteryData = $oLottery->lotteryGetList( '`lotteryid`,`cnname`,`enname`,`description`' );
            if( empty($aLotteryData) )
            {//没有任何彩种
                $aLocation[0] = array( "text"=>"彩种管理", "href"=>url("gameinfo","list") );
                sysMessage( '请先增加彩种', 1, $aLocation );
            }
            $GLOBALS['oView']->assign( 'lotterys', $aLotteryData );
            $GLOBALS['oView']->assign( 'lotteryid', $iLotteryId );
            $GLOBALS["oView"]->assign( "ur_here", "变价方案列表" );
            $GLOBALS["oView"]->assign("actionlink",array('text'=>'增加变价方案','href'=>url('locks','adjustadd')));
            $oLottery->assignSysInfo();
            $GLOBALS['oView']->display('locks_adjustlist.html');
            EXIT;
        }
        else
        {//根据彩种获取相应的调价方案
            if( empty($_POST['lotteryid']) || !is_numeric($_POST['lotteryid']) )
            {//参数错误
                die("error");
            }
            $iLotteryId = intval($_POST['lotteryid']);
            /* @var $oAdjust model_adjustprice */
            $oAdjust    = A::singleton("model_adjustprice");
            $aData      = $oAdjust->adjustPriceGetList( '*', "lotteryid='".$iLotteryId."'" );
            if( empty($aData) )
            {//没有找到方案
                die("empty");
            }
            echo json_encode($aData);
            EXIT;
        }
    }



    /**
     * 增加方案
     * URL: ./index.php?controller=locks&action=AdjustAdd
     * @author JAMES
     */
    function actionAdjustAdd()
    {
        if( !isset($_POST['flag']) || ($_POST['flag']!='insert' && $_POST['flag']!='adjust') )
        {//插入方案数据
            if( !empty($_REQUEST['lotteryid']) && is_numeric($_REQUEST['lotteryid']) )
            {//默认显示的彩种
                $iLotteryId = intval($_REQUEST['lotteryid']) > 0 ? intval($_REQUEST['lotteryid']) : 0;
            }
            else 
            {
                $iLotteryId = 0;
            }
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $aLotteryData = $oLottery->lotteryGetList( '`lotteryid`,`cnname`,`enname`,`description`,`adjustminprofit`' );
            if( empty($aLotteryData) )
            {//没有任何彩种
                $aLocation[0] = array( "text"=>"彩种管理", "href"=>url("gameinfo","list") );
                sysMessage( '请先增加彩种', 1, $aLocation );
            }
            $GLOBALS['oView']->assign( 'lotterys', $aLotteryData );
            $GLOBALS['oView']->assign( 'lotteryid', $iLotteryId );
            $GLOBALS["oView"]->assign( "ur_here", "增加变价方案" );
            $GLOBALS["oView"]->assign("actionlink",array('text'=>'方案列表','href'=>url('locks','adjustlist')));
            $oLottery->assignSysInfo();
            $GLOBALS['oView']->display('locks_adjustadd.html');
            EXIT;
        }
        else if( $_POST['flag'] == 'adjust' )
        {
            if( empty($_POST['lotteryid']) || !is_numeric($_POST['lotteryid']) )
            {//参数错误
                die("error");
            }
            $iLotteryId = intval($_POST['lotteryid']);
            /* @var $oLocks model_locksname */
            $oLocks     = A::singleton("model_locksname");
            $aData      = $oLocks->locksnamegetOne( '`maxlost`', 
                            "`lotteryid`='".$iLotteryId."' ORDER BY `maxlost` DESC" );
            if( empty($aData) )
            {//没有找到方案
                die("empty");
            }
            echo "{maxlost:".$aData['maxlost']."}";
            EXIT;
        }
        else if( $_POST['flag'] == 'insert' )
        {
            if( empty($_POST['lotteryid']) || !is_numeric($_POST['lotteryid']) || $_POST['lotteryid'] <= 0 )
            {
                die("操作错误，请刷新重试");
            }
            $iLotteryId = intval($_POST['lotteryid']);
            if( empty($_POST['title']) )
            {
                die("请填写方案标题");
            }
            $sTitle = $_POST['title'];
            if( empty($_POST['updata']) || !is_numeric($_POST['updata']) || $_POST['updata'] <= 0 )
            {
                die("请增加调价线");
            }
            $iUpdataLen = intval($_POST['updata']);
            if( empty($_POST['downdata']) || !is_numeric($_POST['downdata']) || $_POST['downdata'] <= 0 )
            {
                die("请增加调价线");
            }
            $iDowndataLen = intval($_POST['downdata']);
            if( empty($_POST['maxlost']) || !is_numeric($_POST['maxlost']) || $_POST['maxlost'] <= 0 )
            {
                die("操作错误，请刷新重试");
            }
            $iMaxLost   = intval($_POST['maxlost']);
            $aUpData    = array();//装载上调数据
            $aDownData  = array();//装载下调数据
            $iWinLine   = -1;      //上调初始线
            $iLoseLine  = -1;      //下调初始线
            //获取上调线数据
            for( $i=0; $i<$iUpdataLen; $i++ )
            {
                $iTemp_Percent = isset($_POST['percent_up_'.$i]) ? intval($_POST['percent_up_'.$i]) : 0;
                $iTemp_Money   = isset($_POST['money_up_'.$i]) ? 
                                 intval(preg_replace('/[^\d]/', "", $_POST['money_up_'.$i])) : 0;
                $aUpData[]     = array('uplimit'=>$iTemp_Money, 'percent'=>$iTemp_Percent, 'isup'=>1);
            }
            for( $i=$iDowndataLen-1; $i>=0; $i-- )
            {
                $iTemp_Percent = isset($_POST['percent_down_'.$i]) ? intval($_POST['percent_down_'.$i]) : 0;
                $iTemp_Money   = isset($_POST['money_down_'.($i+1)]) ? 
                                   intval(preg_replace('/[^\d]/', "", $_POST['money_down_'.($i+1)])) : 0;
                $aDownData[]   = array('uplimit'=>$iTemp_Money, 'percent'=>$iTemp_Percent, 'isup'=>0);
            }
            //检测数据合法性
            for( $i=0; $i<$iUpdataLen; $i++ )
            {
                if( $aUpData[$i]['percent'] > 0 && $iWinLine === -1 )
                {//获取上调初始线
                    $iWinLine = $aUpData[$i]['uplimit'];
                }
                if( $i > 0 )
                {
                    if( $aUpData[$i]['uplimit']   <= $aUpData[$i-1]['uplimit'] || 
                        $aUpData[$i]['percent'] <= $aUpData[$i-1]['percent'] )
                    {
                        die("调价线设置不合理");
                        break;
                    }
                }
            }
            for( $i=0; $i<$iDowndataLen; $i++ )
            {
                if( $aDownData[$i]['percent'] > 0 && $iLoseLine === -1 )
                {//获取上调初始线
                    $iLoseLine = $aDownData[$i]['uplimit'];
                }
                if( $i > 0 )
                {
                    if( $aDownData[$i]['uplimit']   <= $aDownData[$i-1]['uplimit'] || 
                        $aDownData[$i]['percent'] <= $aDownData[$i-1]['percent'] )
                    {
                       die("调价线设置不合理");
                       break;
                    }
                }
            }
            if( $aDownData[$iDowndataLen-1]['uplimit'] > $iMaxLost )
            {//下调线最高不能超过最大封锁值
                die("调价线设置不合理");
            }
            $aNewData = array_merge( $aUpData, $aDownData );
            /* @var $oAdjust model_adjustprice */
            $oAdjust = A::singleton("model_adjustprice");
            $bResult = $oAdjust->adjustInsert( $sTitle, $iLotteryId, $iWinLine, $iLoseLine, $aNewData );
            if( $bResult === TRUE )
            {
                die("TRUE");
            }
            else
            {
                die("提交失败，请重试");
            }
        }
    }



    /**
     * 修改方案
     * @author JAMES
     * URL: ./index.php?controller=locks&action=update
     */
    function actionUpdate()
    {
        if( empty($_POST['flag']) || $_POST['flag'] != 'update' )
        {
            if( empty($_REQUEST['lotteryid']) || !is_numeric($_REQUEST['lotteryid']) )
            {
                sysMessage( '操作错误', 1 );
            }
            $iLotteryId = intval($_REQUEST['lotteryid']);
            $aLocation[0] = array( "text" => "返回方案列表",
                            "href" => url("locks","adjustlist",array('lotteryid'=>$iLotteryId)));
            if( empty($_REQUEST['groupid']) || !is_numeric($_REQUEST['groupid']) )
            {
                sysMessage( '操作错误', 1, $aLocation );
            }
            $iGroupId     = intval($_REQUEST['groupid']);
            /* @var $oAdjust model_adjustprice */
            $oAdjust      = A::singleton("model_adjustprice");
            /* @var $oLocksName model_locksname */
            $oLocksName   = A::singleton("model_locksname");
            $sFileds      = " A.`maxlost`,B.`lotteryid`,B.`cnname`,B.`enname`,B.`description`,B.`adjustminprofit` ";
            $sCondition   = " A.`lotteryid`='".$iLotteryId."' ORDER BY A.`maxlost` DESC ";
            $aMaxLost     = $oLocksName->locksLotteryGetAll( $sFileds, $sCondition );//获取封锁值
            if( empty($aMaxLost) )
            {
                sysMessage( '获取彩种信息失败', 1, $aLocation );
            }
            $aMaxLost     = $aMaxLost[0];
            $sFileds      = " `groupid`,`title`,`winline`,`loseline` ";
            $sCondition   = " `groupid`='".$iGroupId."' AND `lotteryid`='".$iLotteryId."' ";
            $aAdjustPrice = $oAdjust->adjustPriceGetOne( $sFileds, $sCondition );//获取方案组信息
            if( empty($aAdjustPrice) )
            {
                sysMessage( '获取方案信息失败', 1, $aLocation );
            }
            $sFileds      = " * ";
            $sCondition   = " `groupid`='".$iGroupId."' ";
            $sOrderBy     = " ORDER BY `uplimit` ASC, `isup` DESC ";
            $aPriceDetail = $oAdjust->adjustPriceDetailGetList( $sFileds, $sCondition, $sOrderBy );//获取调价线信息
            $aUpData      = array();//上调线
            $aDownData    = array();//下调线
            foreach( $aPriceDetail as $v )
            {
                $v['percent'] = floatval($v['percent']) * 100;
                if( $v['isup'] == 1 )
                {//上调线
                    $aUpData[] = $v;
                }
                else
                {//下调线
                    $aDownData[] = $v;
                }
            }
            if( $aAdjustPrice['winline'] > 0 )
            {
                $aAdjustPrice['zeroup'] = 0;
            }
            else
            {
                if( isset($aUpData[0]) && $aUpData[0]['uplimit'] == 0 )
                {
                    $aAdjustPrice['zeroup'] = $aUpData[0]['percent'];
                    unset( $aUpData[0] );
                }
                else 
                {
                    $aAdjustPrice['zeroup'] = 0;
                }
            }
            $iDownCount = count($aDownData);
            if( $iDownCount > 0 )
            {//如果有下调价线
                $aAdjustPrice['zerodown'] = $aDownData[$iDownCount-1]['percent'];//最后一个出栈
                for( $i=$iDownCount-1; $i>=0; $i-- )
                {
                    $aDownData[$i]['percent'] = $i==0 ? 0 : $aDownData[$i-1]['percent'];
                }
                if( $aAdjustPrice['loseline'] <= 0 && $aDownData[0]['uplimit'] == 0 )
                {
                    unset($aDownData[0]);
                }
            }
            else 
            {
                $aAdjustPrice['zerodown'] = 0;
            }
            $GLOBALS['oView']->assign( 'lotterys',  $aMaxLost );
            $GLOBALS['oView']->assign( 'adjust',    $aAdjustPrice );
            $GLOBALS['oView']->assign( 'updatas',   $aUpData );
            $GLOBALS['oView']->assign( 'downdatas', $aDownData );
            $GLOBALS["oView"]->assign( "ur_here",  "修改变价方案" );
            $GLOBALS["oView"]->assign("actionlink", array('text'=>'方案列表',
                                      'href'=>url('locks','adjustlist',array('lotteryid'=>$iLotteryId))));
            $oLocksName->assignSysInfo();
            $GLOBALS['oView']->display('locks_update.html');
            EXIT;
        }
        else
        {
            if( empty($_POST['lotteryid']) || !is_numeric($_POST['lotteryid']) || $_POST['lotteryid'] <= 0 )
            {
                die("操作错误，请刷新重试");
            }
            $iLotteryId = intval($_POST['lotteryid']);
            if( empty($_POST['groupid']) || !is_numeric($_POST['groupid']) || $_POST['groupid'] <= 0 )
            {
                 die("操作错误，请刷新重试");
            }
            $iGroupId = intval($_POST['groupid']);
            if( empty($_POST['title']) )
            {
                die("请填写方案标题");
            }
            $sTitle = $_POST['title'];
            if( empty($_POST['updata']) || !is_numeric($_POST['updata']) || $_POST['updata'] <= 0 )
            {
                die("请增加调价线");
            }
            $iUpdataLen = intval($_POST['updata']);
            if( empty($_POST['downdata']) || !is_numeric($_POST['downdata']) || $_POST['downdata'] <= 0 )
            {
                die("请增加调价线");
            }
            $iDowndataLen = intval($_POST['downdata']);
            if( empty($_POST['maxlost']) || !is_numeric($_POST['maxlost']) || $_POST['maxlost'] <= 0 )
            {
                die("操作错误，请刷新重试");
            }
            $iMaxLost   = intval($_POST['maxlost']);
            $aUpData    = array();//装载上调数据
            $aDownData  = array();//装载下调数据
            $iWinLine   = -1;      //上调初始线
            $iLoseLine  = -1;      //下调初始线
            //获取上调线数据
            for( $i=0; $i<$iUpdataLen; $i++ )
            {
                $iTemp_Percent = isset($_POST['percent_up_'.$i]) ? intval($_POST['percent_up_'.$i]) : 0;
                $iTemp_Money   = isset($_POST['money_up_'.$i]) ? 
                                 intval(preg_replace('/[^\d]/', "", $_POST['money_up_'.$i])) : 0;
                $aUpData[]     = array('uplimit'=>$iTemp_Money, 'percent'=>$iTemp_Percent, 'isup'=>1);
            }
            for( $i=$iDowndataLen-1; $i>=0; $i-- )
            {
                $iTemp_Percent = isset($_POST['percent_down_'.$i]) ? intval($_POST['percent_down_'.$i]) : 0;
                $iTemp_Money   = isset($_POST['money_down_'.($i+1)]) ? 
                                   intval(preg_replace('/[^\d]/', "", $_POST['money_down_'.($i+1)])) : 0;
                $aDownData[] = array('uplimit'=>$iTemp_Money, 'percent'=>$iTemp_Percent, 'isup'=>0);
            }
            //检测数据合法性
            for( $i=0; $i<$iUpdataLen; $i++ )
            {
                if( $aUpData[$i]['percent'] > 0 && $iWinLine === -1 )
                {//获取上调初始线
                    $iWinLine = $aUpData[$i]['uplimit'];
                }
                if( $i > 0 )
                {
                    if( $aUpData[$i]['uplimit']   <= $aUpData[$i-1]['uplimit'] || 
                        $aUpData[$i]['percent'] <= $aUpData[$i-1]['percent'] )
                    {
                        die("调价线设置不合理");
                        break;
                    }
                }
            }
            for( $i=0; $i<$iDowndataLen; $i++ )
            {
                if( $aDownData[$i]['percent'] > 0 && $iLoseLine === -1 )
                {//获取上调初始线
                    $iLoseLine = $aDownData[$i]['uplimit'];
                }
                if( $i > 0 )
                {
                    if( $aDownData[$i]['uplimit']   <= $aDownData[$i-1]['uplimit'] || 
                        $aDownData[$i]['percent'] <= $aDownData[$i-1]['percent'] )
                    {
                       die("调价线设置不合理");
                       break;
                    }
                }
            }
            if( $aDownData[$iDowndataLen-1]['uplimit'] > $iMaxLost )
            {//下调线最高不能超过最大封锁值
                die("调价线设置不合理");
            }
            $aNewData = array_merge( $aUpData, $aDownData );
            /* @var $oAdjust model_adjustprice */
            $oAdjust = A::singleton("model_adjustprice"); 
            $bResult = $oAdjust->adjustUpdate( $iGroupId, $sTitle, $iLotteryId, $iWinLine, $iLoseLine, $aNewData );
            if( $bResult === TRUE )
            {
                die("TRUE");
            }
            elseif( intval($bResult) == -1 )
            {
                die("错误: 禁止编辑已激活的变价方案");
            }
            else
            {
                die("提交失败，请重试".$bResult);
            }
        }
    }



    /**
     * 删除方案
     * @author JAMES
     * URL: ./index.php?controller=locks&action=adjustdel
     */
    function actionAdjustDel()
    {
        if( empty($_POST['lotteryid']) )
        {
            sysMessage( '操作错误', 1 );
        }
        $iLotteryId   = intval($_POST['lotteryid']);
        $aLocation[0] = array( "text" => "返回方案列表",
                               "href" => url("locks", "adjustlist", array('lotteryid'=>$iLotteryId)) );
        if( empty($_POST['checkboxes']) || !is_array($_POST['checkboxes']) )
        {
            sysMessage( '未选择数据', 1, $aLocation );
        }
        if( count($_POST['checkboxes']) > 1 )
        {
            $sCondition = " `lotteryid`='".$iLotteryId."' AND `groupid` IN(".implode(",",$_POST['checkboxes']).") ";
        }
        else
        {
            $sCondition = " `lotteryid`='".$iLotteryId."' AND `groupid`='".$_POST['checkboxes'][0]."' ";
        }
        /* @var $oAdjust model_adjustprice */
        $oAdjust = A::singleton("model_adjustprice");
        $bResult = $oAdjust->adjustPriceDelete( $sCondition );
        if( $bResult == FALSE )
        {
            sysMessage( '操作失败', 1, $aLocation );
        }
        else
        {
            sysMessage( '操作成功', 0, $aLocation );
        }
    }



    /**
     * 审核方案
     * @author JAMES
     * URL: ./index.php?controller=locks&action=check
     */
    function actionCheck()
    {
        if( empty($_REQUEST['lotteryid']) || !is_numeric($_REQUEST['lotteryid']) )
        {
            sysMessage( '操作错误', 1 );
        }
        $iLotteryId   = intval($_REQUEST['lotteryid']);
        $aLocation[0] = array( "text" => "返回方案列表",
                               "href" => url("locks","adjustlist",array('lotteryid'=>$iLotteryId)));
        if( empty($_REQUEST['groupid']) || !is_numeric($_REQUEST['groupid']) )
        {
            sysMessage( '操作错误', 1, $aLocation );
        }
        $iGroupId = intval($_REQUEST['groupid']);
        /* @var $oAdjust model_adjustprice */
        $oAdjust  = A::singleton("model_adjustprice");
        $mResult  = $oAdjust->adjustPriceVerify( $iGroupId );
        if( $mResult === 1 )
        {
            sysMessage( '操作成功', 0, $aLocation );
        }
        else if( $mResult === -1 )
        {
            sysMessage( '调价方案不完整，没有具体的调价信息', 1, $aLocation );
        }
        else 
        {
            sysMessage( '操作失败', 1, $aLocation );
        }
    }




    /**
     * 激活方案
     * @author JAMES
     * URL: ./index.php?controller=locks&action=active
     */
    function actionActive()
    {
        if( empty($_REQUEST['lotteryid']) || !is_numeric($_REQUEST['lotteryid']) )
        {
            sysMessage( '操作错误', 1 );
        }
        $iLotteryId   = intval($_REQUEST['lotteryid']);
        $aLocation[0] = array( "text" => "返回方案列表",
                               "href" => url("locks","adjustlist",array('lotteryid'=>$iLotteryId)));
        if( empty($_REQUEST['groupid']) || !is_numeric($_REQUEST['groupid']) )
        {
            sysMessage( '操作错误', 1, $aLocation );
        }
        $iGroupId = intval($_REQUEST['groupid']);
        /* @var $oAdjust model_adjustprice */
        $oAdjust  = A::singleton("model_adjustprice");
        $mResult  = $oAdjust->adjustPriceActive( $iGroupId );
        if( $mResult === TRUE )
        {
            sysMessage( '操作成功', 0, $aLocation );
        }
        else if( $mResult === -1 )
        {
            sysMessage( '方案未审核不能激活', 1, $aLocation );
        }
        else 
        {
            sysMessage( '操作失败', 1, $aLocation );
        }
    }



    /**
     * 取消激活
     * @author JAMES
     * URL:./index.php?controller=locks&action=unactive
     */
    function actionUnactive()
    {
        if( empty($_REQUEST['lotteryid']) || !is_numeric($_REQUEST['lotteryid']) )
        {
            sysMessage( '操作错误', 1 );
        }
        $iLotteryId = intval($_REQUEST['lotteryid']);
        $aLocation[0] = array( "text" => "返回方案列表",
                               "href" => url("locks","adjustlist",array('lotteryid'=>$iLotteryId)));
        if( empty($_REQUEST['groupid']) || !is_numeric($_REQUEST['groupid']) )
        {
            sysMessage( '操作错误', 1, $aLocation );
        }
        $iGroupId = intval($_REQUEST['groupid']);
        /* @var $oAdjust model_adjustprice */
        $oAdjust  = A::singleton("model_adjustprice");
        $mResult  = $oAdjust->adjustPriceUnactive( $iGroupId );
        if( $mResult === TRUE )
        {
            sysMessage( '操作成功', 0, $aLocation );
        }
        else 
        {
            sysMessage( '操作失败', 1, $aLocation );
        }
    }



    /**
     * 方案细节查看
     */
    function actionAdjustView()
    {
    	if( empty($_REQUEST['lotteryid']) || !is_numeric($_REQUEST['lotteryid']) )
        {
            sysMessage( '操作错误', 1 );
        }
        $iLotteryId = intval($_REQUEST['lotteryid']);
        $aLocation[0] = array( "text" => "返回方案列表",
                        "href" => url("locks","adjustlist",array('lotteryid'=>$iLotteryId)));
        if( empty($_REQUEST['groupid']) || !is_numeric($_REQUEST['groupid']) )
        {
            sysMessage( '操作错误', 1, $aLocation );
        }
        $iGroupId     = intval($_REQUEST['groupid']);
        /* @var $oAdjust model_adjustprice */
        $oAdjust      = A::singleton("model_adjustprice");
        /* @var $oLocksName model_locksname */
        $oLocksName   = a::singleton("model_locksname");
        $sFileds      = " A.`maxlost`,B.`lotteryid`,B.`cnname`,B.`enname`,B.`description`,B.`adjustminprofit` ";
        $sCondition   = " A.`lotteryid`='".$iLotteryId."' ORDER BY A.`maxlost` DESC ";
        $aMaxLost     = $oLocksName->locksLotteryGetAll( $sFileds, $sCondition );//获取封锁值
        if( empty($aMaxLost) )
        {
            sysMessage( '获取彩种信息失败', 1, $aLocation );
        }
        $aMaxLost     = $aMaxLost[0];
        $sFileds      = " `groupid`,`title`,`winline`,`loseline` ";
        $sCondition   = " `groupid`='".$iGroupId."' AND `lotteryid`='".$iLotteryId."' ";
        $aAdjustPrice = $oAdjust->adjustPriceGetOne( $sFileds, $sCondition );//获取方案组信息
        if( empty($aAdjustPrice) )
        {
            sysMessage( '获取方案信息失败', 1, $aLocation );
        }
        $sFileds      = " * ";
        $sCondition   = " `groupid`='".$iGroupId."' ";
        $sOrderBy     = " ORDER BY `uplimit` ASC, `isup` DESC ";
        $aPriceDetail = $oAdjust->adjustPriceDetailGetList( $sFileds, $sCondition, $sOrderBy );//获取调价线信息
        $aUpData      = array();//上调线
        $aDownData    = array();//下调线
        foreach( $aPriceDetail as $v )
        {
            $v['percent'] = floatval($v['percent']) * 100;
            if( $v['isup'] == 1 )
            {//上调线
                $aUpData[] = $v;
            }
            else
            {//下调线
                $aDownData[] = $v;
            }
        }
        if( $aAdjustPrice['winline'] > 0 )
        {
            $aAdjustPrice['zeroup'] = 0;
        }
        else
        {
            if( isset($aUpData[0]) && $aUpData[0]['uplimit'] == 0 )
            {
                $aAdjustPrice['zeroup'] = $aUpData[0]['percent'];
                unset( $aUpData[0] );
            }
            else 
            {
                $aAdjustPrice['zeroup'] = 0;
            }
        }
        $iDownCount = count($aDownData);
        if( $iDownCount > 0 )
        {//如果有下调价线
            $aAdjustPrice['zerodown'] = $aDownData[$iDownCount-1]['percent'];//最后一个出栈
            for( $i=$iDownCount-1; $i>=0; $i-- )
            {
                $aDownData[$i]['percent'] = $i==0 ? 0 : $aDownData[$i-1]['percent'];
            }
            if( $aAdjustPrice['loseline'] <= 0 && $aDownData[0]['uplimit'] == 0 )
            {
                unset($aDownData[0]);
            }
        }
        else 
        {
            $aAdjustPrice['zerodown'] = 0;
        }
        $GLOBALS['oView']->assign( 'lotterys',  $aMaxLost );
        $GLOBALS['oView']->assign( 'adjust',    $aAdjustPrice );
        $GLOBALS['oView']->assign( 'updatas',   $aUpData );
        $GLOBALS['oView']->assign( 'downdatas', $aDownData );
        $GLOBALS["oView"]->assign( "ur_here",  "查看变价方案" );
        $GLOBALS["oView"]->assign("actionlink", array('text'=>'方案列表',
                                  'href'=>url('locks','adjustlist',array('lotteryid'=>$iLotteryId))));
        $oLocksName->assignSysInfo();
        $GLOBALS['oView']->display('locks_adjustinfo.html');
        EXIT;
    }



    /**
     * 私有方法, 根据参数, 获取方差与变异系数等返回
     * 
     * 参数:
     * @param array $aTotalDatas    所有号码封锁值的数组, 1000 或 100 (P5后2)
     * 
     * 返回:
     * Array(
     *     'junshu'  => '',          // 全体号码     盈亏值 均数
     *     'fangcha' => '',          // 全体号码假设 方差
     *     'biaozhunfangcha' => '',  // 全体号码假设 盈亏值 标准差
     *     'bianyixishu' => '',      // 全体号码假设 盈亏值 变异系数
     * );
     */
    private function getStatDatas( $aTotalDatas = array() )
    {
    	$aReturn = array(
            'junshu'          => 0,
            'fangcha'         => 0,
	    	'biaozhunfangcha' => 0,
	    	'bianyixishu'     => 0,
    	);
    	if( empty($aTotalDatas) || !is_array($aTotalDatas) )
    	{
    		return $aReturn;
    	}

    	$iAllNumbers = count($aTotalDatas);  // 总个数
    	$iSumNumbers = 0;
    	foreach ( $aTotalDatas as $v )
    	{
    		$iSumNumbers += $v*1000; // 放大精度
    	}
    	$aReturn['junshu'] = $iSumNumbers / $iAllNumbers / 1000;  // 均数

    	// 方差计算:
    	foreach( $aTotalDatas as $v )
    	{
    		$aReturn['fangcha'] += pow( ($v-$aReturn['junshu']), 2 ); 
    	}
    	$aReturn['fangcha']         = $aReturn['fangcha'] / $iAllNumbers;
    	$aReturn['biaozhunfangcha'] = sqrt( $aReturn['fangcha'] );
    	$aReturn['bianyixishu']     = $aReturn['junshu']==0 ? 'Null' : 
    	           ($aReturn['biaozhunfangcha'] / $aReturn['junshu']);
    	//print_rr($aReturn);exit;
    	return $aReturn;   	
    }


    /**
     * 3D封锁表
     * @author SAUL
     * URL:./index.php?controller=locks&action=3d
     */
    function action3d()
    {
        $sLocksTableName = "locks3d";
        $iLotteryId      = 1;
        $GLOBALS['oView']->assign("ur_here","3D封锁");
        /* @var $oIssue model_issueinfo */
        $oIssue = A::singleton("model_issueinfo");
        $aIssue = $oIssue->issueGetList('A.`issue`,date(A.`saleend`) as `saledate`',
        "abs(DATEDIFF(A.`saleend`,now()))<10 AND A.`lotteryid`='".$iLotteryId."'" ,
        "A.`issue` DESC", 0 );
        foreach( $aIssue as $issue )
        {
            if( $issue["saledate"] == date("Y-m-d") )
            {
                $sIssue = $issue["issue"];
            }
        }
        $GLOBALS['oView']->assign( 'aIssue', $aIssue );
        $GLOBALS['oView']->assign( "action", "3d" );
        $issue = isset($_GET)&&!empty($_GET)&&!empty($_GET["issue"]) ? $_GET["issue"] : $sIssue;//期数获取
        $GLOBALS['oView']->assign( "sissue", $issue );
        //封锁管理
        /* @var $oLocks model_locks */
        $oLocks  = A::singleton("model_locks");
        $aResult = $oLocks->getData($sLocksTableName, $iLotteryId, $issue);
        $aDebug['iTotalSum'] = 0;
        if( $aResult['error'] === 0 )
        {
            foreach ($aResult['lose'] as $aLose )
            {
                $aLastResult[$aLose['code']] = $aResult['win']['sum_money'] - $aLose['SUM_PRIZES'];
                $aDebug['iTotalSum'] += $aLastResult[$aLose['code']]*1000; 
            }
            $aDebug['extend']    =  $this->getStatDatas( $aLastResult );
            $aDebug['iTotalSum'] = $aDebug['iTotalSum'] / 1000;
            $iOrder = isset($_GET['order']) ? intval($_GET['order']) : 0;
            if( $iOrder == 0 )
            {
                ksort($aLastResult);//按号码排序
            }
            else
            {
                arsort($aLastResult);//按封锁值排序
            }
            $GLOBALS['oView']->assign( "order", $iOrder );
            $iNumPerCol = ceil(count($aLastResult)/5);
            $aData = array_chunk( $aLastResult, $iNumPerCol, TRUE );//将数组分成5组，用于页面显示
            $GLOBALS['oView']->assign("adata",$aData);
            
            $aDebug['iTotalSale']  = empty($aResult['sales']['sum_sales']) ? 0 : $aResult['sales']['sum_sales'];
            $aDebug['iNumCount']   = 1000;
            
	        $aDebug['iPercent']  = ( $aDebug['iTotalSum'] /  1000 ) / 
                ( $aDebug['iTotalSale']!=0 ? $aDebug['iTotalSale'] : 1 ) * 100;
	        $GLOBALS['oView']->assign("aDebug",$aDebug);
        }
        else 
        {
        	$GLOBALS['oView']->assign("lockserror",$aResult['error']);
        }
        
        $oIssue->assignSysInfo();
        $GLOBALS['oView']->display("locks_info.html");
        EXIT;
    }



    /**
     * P3 封锁表
     * @author SAUL
     * URL: ./index.php?controller=locks&action=P3
     */
    function actionP3()
    {
        $sLocksTableName = "locksp3";
        $iLotteryId      = 2;
        $GLOBALS['oView']->assign( "ur_here", "P3封锁" );
        /* @var $oIssue model_issueinfo */
        $oIssue = A::singleton("model_issueinfo");
        $aIssue = $oIssue->issueGetList('A.`issue`,date(A.`saleend`) as `saledate`',
        "abs(DATEDIFF(A.`saleend`,now()))<10 AND A.`lotteryid`='".$iLotteryId."'" ,
        'A.`issue` DESC', 0 );
        foreach($aIssue as $issue)
        {
            if( $issue["saledate"] == date("Y-m-d") )
            {
                $sIssue = $issue["issue"];
            }
        }
        $GLOBALS['oView']->assign( "aIssue", $aIssue );
        $GLOBALS['oView']->assign( "action", "p3" );
        $issue = isset($_GET)&&!empty($_GET)&&!empty($_GET["issue"]) ? $_GET["issue"]: $sIssue;
        $GLOBALS['oView']->assign("sissue",$issue);
        /* @var $oLocks model_locks */
        $oLocks  = A::singleton("model_locks");
        $aResult = $oLocks->getData($sLocksTableName, $iLotteryId, $issue);
        $aDebug['iTotalSum'] = 0;
        if( $aResult['error'] === 0 )
        {
            foreach ($aResult['lose'] as $aLose )
            {
                $aLastResult[$aLose['code']] = $aResult['win']['sum_money'] - $aLose['SUM_PRIZES'];
                $aDebug['iTotalSum'] += $aLastResult[$aLose['code']]*1000; 
            }
            $aDebug['extend']    =  $this->getStatDatas( $aLastResult );
            $aDebug['iTotalSum'] = $aDebug['iTotalSum'] / 1000;

            $iOrder = isset($_GET['order']) ? intval($_GET['order']) : 0;
            if( $iOrder == 0 )
            {
                ksort($aLastResult);//按号码排序
            }
            else
            {
                arsort($aLastResult);//按封锁值排序
            }
            $GLOBALS['oView']->assign( "order", $iOrder );
            $iNumPerCol = ceil(count($aLastResult)/5);
            $aData = array_chunk( $aLastResult, $iNumPerCol, TRUE );
            $GLOBALS['oView']->assign("adata",$aData);
            
            $aDebug['iTotalSale']  = empty($aResult['sales']['sum_sales']) ? 0 : $aResult['sales']['sum_sales'];
            $aDebug['iNumCount']   = 1000;
            $aDebug['iPercent']  = ( $aDebug['iTotalSum'] /  1000 ) / 
                ( $aDebug['iTotalSale']!=0 ? $aDebug['iTotalSale'] : 1 ) * 100;
            $GLOBALS['oView']->assign("aDebug",$aDebug);
        }
        else 
        {
            $GLOBALS['oView']->assign("lockserror",$aResult['error']);
        }
        
        $oIssue->assignSysInfo();
        $GLOBALS['oView']->display("locks_info.html");
        EXIT;
    }



    /**
     * P5后二封锁表
     * @author SAUL
     * URL: ./index.php?controller=locks&action=p5
     */
    function actionP5()
    {
        $sLocksTableName = "locksp5last2";
        $iLotteryId      = 2;
        $GLOBALS['oView']->assign("ur_here","P5后二封锁");
        /* @var $oIssue model_issueinfo */
        $oIssue = A::singleton("model_issueinfo");
        $aIssue = $oIssue->issueGetList('A.`issue`,date(A.`saleend`) as `saledate`',
        "abs(DATEDIFF(A.`saleend`,now()))<10 AND A.`lotteryid`='".$iLotteryId."'" ,
        'A.`issue` DESC', 0 );
        foreach( $aIssue as $issue )
        {
            if( $issue["saledate"] == date("Y-m-d") )
            {
                $sIssue = $issue["issue"];
            }
        }
        $GLOBALS['oView']->assign( 'aIssue', $aIssue );
        $GLOBALS['oView']->assign( "action", "p5" );
        $issue = isset($_GET)&&!empty($_GET)&&!empty($_GET["issue"]) ? $_GET["issue"] : $sIssue;
        $GLOBALS['oView']->assign( "sissue", $issue );
        /* @var $oLocks model_locks */
        $oLocks = A::singleton("model_locks");
        $aResult = $oLocks->getData($sLocksTableName, $iLotteryId, $issue);
        $aDebug['iTotalSum'] = 0;
        if( $aResult['error'] === 0 )
        {
            foreach ($aResult['lose'] as $aLose )
            {
                $aLastResult[$aLose['code']] = $aResult['win']['sum_money'] - $aLose['SUM_PRIZES'];
                $aDebug['iTotalSum'] += $aLastResult[$aLose['code']]*1000;
            }
            $aDebug['extend']    =  $this->getStatDatas( $aLastResult );
            $aDebug['iTotalSum'] = $aDebug['iTotalSum'] / 1000;

            $iOrder = isset($_GET['order']) ? intval($_GET['order']) : 0;
            if( $iOrder == 0 )
            {
                ksort($aLastResult);//按号码排序
            }
            else
            {
                arsort($aLastResult);//按封锁值排序
            }
            $GLOBALS['oView']->assign( "order", $iOrder );
            $iNumPerCol = ceil(count($aLastResult)/5);
            $aData = array_chunk( $aLastResult, $iNumPerCol, TRUE );
            $GLOBALS['oView']->assign("adata",$aData);
            
            $aDebug['iTotalSale']  = empty($aResult['sales']['sum_sales']) ? 0 : $aResult['sales']['sum_sales'];
            $aDebug['iNumCount']   = 100;
            $aDebug['iPercent']  = ( $aDebug['iTotalSum'] /  100 ) / 
                ( $aDebug['iTotalSale']!=0 ? $aDebug['iTotalSale'] : 1 ) * 100;
            $GLOBALS['oView']->assign("aDebug",$aDebug);
        }
        else 
        {
            $GLOBALS['oView']->assign("lockserror",$aResult['error']);
        }

        $oIssue->assignSysInfo();
        $GLOBALS['oView']->display("locks_info.html");
        EXIT;
    }
}
?>