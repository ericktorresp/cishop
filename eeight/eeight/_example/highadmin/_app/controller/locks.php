<?php
/**
 * 文件 : /_app/controller/locks.php
 * 功能 : 控制器 - 封锁管理
 * 
 * 功能:
 *      - actionList()              彩种封锁列表
 *      - actionMethodLockList()    彩种玩法组子封锁列表
 *      - actionMethodLockEdit()    修改玩法封锁值
 *      - actionView()              查看封锁表详情
 *      - actionAdd()               增加子封锁列表
 *      - actionDelete()            删除封锁表
 * @author      mark
 * @version     1.0.0 
 * @package     highadmin
 */

class controller_locks extends basecontroller
{

    /**
     * 彩种封锁列表
     * URL = ./index.php?controller=locks&action=list
     * @author mark
     */
    public function actionList()
    {
        $aLocation[0] = array('text'=>'增加子封锁表','href'=>url('locks','add'));
        /* @var $oLocks model_locks */
        $oLocks = A::singleton('model_locks');
        $aLockList = $oLocks->getLotteryTotalLockList();
        $GLOBALS['oView']->assign( "ur_here", "封锁管理列表");
        if( isset($aLockList[0]['totallock']) && $aLockList[0]['totallock'] != '' )
        {
            $GLOBALS['oView']->assign( "aLockList", $aLockList);
        }
        $GLOBALS["oView"]->assign( "actionlink",    $aLocation[0] );
        $oLocks->assignSysInfo();
        $GLOBALS['oView']->display('locks_list.html');
        EXIT;
    }


    /**
     * 彩种玩法组子封锁列表
     * URL = ./index.php?controller=locks&action=methodlocklist
     * @author mark
     */
    public function actionMethodLockList()
    {
        $aLocation[0] = array('text'=>'增加子封锁表','href'=>url('locks','add'));
        $iLotteryId = isset($_GET['lotteryid']) && $_GET['lotteryid'] != '' ? intval($_GET['lotteryid']) : 0;
        if( $iLotteryId == 0 )
        {
            sysMessage('没有指定游戏', 1, $aLocation);
        }
        /* @var $oLocks model_locks */
        $oLocks = A::singleton('model_locks');
        $aLockList = array();
        $aMethodLockList = $oLocks->getMethodLockList($iLotteryId);
        
        //统计封锁表中的相关玩法
        foreach ($aMethodLockList as $aLock)
        {
            $aLockList[$aLock['locktablename']] = $aLock;
            foreach ($aMethodLockList as $aLockMethod)
            {
                if($aLockMethod['pid'] == 0)
                {
                    continue;//不包含玩法组
                }
                if($aLock['locktablename'] == $aLockMethod['locktablename'])
                {
                    if(isset($aLockList[$aLock['locktablename']]['lockmethodname']))
                    {
                        $aLockList[$aLock['locktablename']]['lockmethodname'] .= "<br>".$aLockMethod['methodname'];
                    }
                    else 
                    {
                        $aLockList[$aLock['locktablename']]['lockmethodname'] = $aLockMethod['methodname'];
                    }
                }
            }
        }
        $aLockResult = array();
        foreach ($aLockList as $aLockDetail)
        {
            if(!isset($aLockResult[$aLockDetail['crowdid']]['count']))
            {
                $aLockResult[$aLockDetail['crowdid']]['count'] = 1;
            }
            else
            {
                $aLockResult[$aLockDetail['crowdid']]['count']++;
            }
            if(!isset($aLockResult[$aLockDetail['crowdid']]['crowdname']))
            {
                $aLockResult[$aLockDetail['crowdid']]['crowdname'] = $aLockDetail['crowdname'];
            }
            if(!isset($aLockResult[$aLockDetail['crowdid']]['cnname']))
            {
                $aLockResult[$aLockDetail['crowdid']]['cnname'] = $aLockDetail['cnname'];
            }
            $aLockResult[$aLockDetail['crowdid']]['lock'][] = $aLockDetail;
        }
        ksort($aLockResult);
        $GLOBALS['oView']->assign( "ur_here", "子封锁管理列表");
        if( isset($aMethodLockList[0]['locktablename']) && $aMethodLockList[0]['locktablename'] != '' )
        {
            $GLOBALS['oView']->assign( "aLockList", $aLockResult);
        }
        $GLOBALS["oView"]->assign( "actionlink",    $aLocation[0] );
        $GLOBALS["oView"]->assign( "crowdCount",    count($aLockResult) );
        $oLocks->assignSysInfo();
        $GLOBALS['oView']->display('locks_sonlist.html');
        EXIT;
    }


     /**
     * 增加子封锁列表
     * URL = ./index.php?controller=locks&action=add
     * @author mark
     */
    public function actionAdd()
    {
        $aLocation[0] = array( "text"=>'封锁管理列表', "href"=>url('locks','list') );
        if( isset($_POST) && !empty($_POST) )
        {
            /* @var $oLocks model_locks */
            $oLocks = A::singleton('model_locks');
            $iLotteryId = isset($_POST['lotteryid']) && $_POST['lotteryid'] != '' ? intval($_POST['lotteryid']) : 0;
            if( $iLotteryId == 0 )
            {
                sysMessage('没有指定游戏', 1);
            }
            $aLocation[1] = array( "text"=>'子封锁管理列表', "href"=>url('locks', 'methodlocklist', array('lotteryid'=>$iLotteryId)) );
            $fMaxLost = isset($_POST['maxlost']) && $_POST['maxlost'] != '' ? floatval($_POST['maxlost']) : 0.00;
            $sLockTableName = isset($_POST['locktablename']) && $_POST['locktablename'] != '' ? daddslashes($_POST['locktablename']) : '';
            $sLockTableCnname = isset($_POST['locktablecnname']) && $_POST['locktablecnname'] != '' ? daddslashes($_POST['locktablecnname']) : '';
            $sLockCodeFuntion = isset($_POST['codefunction']) && $_POST['codefunction'] != '' ? daddslashes($_POST['codefunction']) : '';
            $mResult = $oLocks->addLockTable($sLockTableName, $sLockTableCnname, $iLotteryId, $fMaxLost, $sLockCodeFuntion);
            if( $mResult === -1 )
            {
                sysMessage('操作失败：没有指定游戏', 1, $aLocation);
            }
            elseif ( $mResult === -2 )
            {
                sysMessage('操作失败：没有指定封锁表名称', 1, $aLocation);
            }
            elseif ( $mResult === -3 )
            {
                sysMessage('操作失败：没有指定确认对应封锁表中的中奖号码的函数', 1, $aLocation);
            }
            elseif ( $mResult === -4 )
            {
                sysMessage('操作失败：已经存在相同的封锁表名', 1, $aLocation);
            }
            elseif ( $mResult === -5 )
            {
                sysMessage('操作失败：添加封锁表失败', 1, $aLocation);
            }
            else
            {
                sysMessage('操作成功：封锁表添加成功', 0, $aLocation);
            }
        }
        else
        {
            $oLottery = A::singleton('model_lottery');
            $aLottery = $oLottery->lotteryGetList();
            $GLOBALS['oView']->assign( "ur_here", "增加子封锁表");
            $GLOBALS['oView']->assign( "aLottery", $aLottery);
            $aLocation[0] = array('text'=>'子封锁表列表','href'=>url('locks','list'));
            $GLOBALS["oView"]->assign( "actionlink",    $aLocation[0] );
            $GLOBALS["oView"]->assign( "action",    'add' );
            $oLottery->assignSysInfo();
            $GLOBALS['oView']->display('locks_info.html');
            EXIT;
        }
    }
    
    
    /**
     * 修改玩法封锁值
     * URL = ./index.php?controller=locks&action=methodlockedit
     * @author mark
     */
    public function actionMethodLockEdit()
    {
        $aLocation[0] = array( "text"=>'封锁管理列表', "href"=>url('locks','list') );
        /* @var $oLocks model_locks */
        $oLocks = A::singleton('model_locks');
        if( isset($_POST) && !empty($_POST) )
        {
            $iLockTableId = isset($_POST['locktableid']) && $_POST['locktableid'] != '' ? intval($_POST['locktableid']) : 0;
            if( $iLockTableId == 0 )
            {
                sysMessage('没有指定封锁表ID', 1);
            }
            $fMaxLost = isset($_POST['maxlost']) && $_POST['maxlost'] != '' ? floatval($_POST['maxlost']) : 0.00;
            $mResult = $oLocks->updateLock($iLockTableId, $fMaxLost);
            $aLocation[0] = array( "text"=>'封锁表列表', "href"=>url('locks','methodlocklist',array('lotteryid'=>$_POST['lotteryid'])) );
            if( $mResult === -1 )
            {
                sysMessage('操作失败：没有指定封锁表ID', 1, $aLocation);
            }
            elseif ( $mResult === -2 )
            {
                sysMessage('操作失败：没有数据更新', 1, $aLocation);
            }
            else
            {
                sysMessage('操作成功：封锁值已更新', 0, $aLocation);
            }
        }
        else
        {
            $iLockTableId = isset($_GET['locktableid']) && $_GET['locktableid'] != '' ? intval($_GET['locktableid']) : 0;
            if( $iLockTableId == 0 )
            {
                sysMessage('没有指定封锁表ID', 1, $aLocation);
            }
            $aLock = $oLocks->getLockById( $iLockTableId );
            $GLOBALS['oView']->assign( "ur_here", "修改子封锁表");
            $GLOBALS['oView']->assign( "alock", $aLock);
            $aLocation[0] = array('text'=>'封锁表列表','href'=>url('locks','list'));
            $GLOBALS["oView"]->assign( "actionlink",    $aLocation[0] );
            $GLOBALS["oView"]->assign( "action",    'methodlockedit' );
            $oLocks->assignSysInfo();
            $GLOBALS['oView']->display('locks_info.html');
            EXIT;
        }
    }
    
    /**
     * 删除封锁表
     * URL = ./index.php?controller=locks&action=delete
     * @author mark
     */
    public function actionDelete()
    {
        $aLocation[0] = array( "text"=>'封锁管理列表', "href"=>url('locks','list') );
        $iLockTableId = isset($_GET['locktableid']) && $_GET['locktableid'] != '' ? intval($_GET['locktableid']) : 0;
        if( $iLockTableId == 0 )
        {
            sysMessage('没有指定封锁表ID', 1, $aLocation);
        }
        /* @var $oLocks model_locks */
        $oLocks = A::singleton('model_locks');
        $mResult = $oLocks->deleteLock( $iLockTableId );
        if( $mResult === -1 )
        {
            sysMessage('操作失败：没有指定封锁表ID', 1, $aLocation);
        }
        elseif ( $mResult === -2 )
        {
            sysMessage('操作失败：删除封锁表失败', 1, $aLocation);
        }
        else
        {
            sysMessage('操作成功：删除封锁表成功', 0, $aLocation);
        }
    }
    
    
    /**
     * 查看封锁表详情
     * URL = ./index.php?controller=locks&action=cq11y
     * @author mark
     * 
     */
    public function actionLockdetail()
    {
        $iLotteryId   = isset($_GET['lotteryid']) && is_numeric($_GET['lotteryid']) ? intval($_GET['lotteryid']) : 0;
        $sLotteryName = isset($_GET['lotteryname']) && $_GET['lotteryname'] != '' ? $_GET['lotteryname'] : '';
        $this->locksView( $iLotteryId, $sLotteryName );
    }


    /**
     * 查看封锁表详情基类函数
     * URL = ./index.php?controller=locks&action=view
     * @author mark
     */
    private function locksView( $iLotteryId = 0, $sLotteryName = '' )
    {
        /* @var $oLocks model_locks */
        $oLocks = A::singleton('model_locks', $GLOBALS['aSysDbServer']['report']);
        $aLocation[0] = array( "text"=>'封锁管理列表', "href"=>url('locks','list') );
        $iLotteryId = isset($iLotteryId) && $iLotteryId != '' ? intval($iLotteryId) : 0;
        $iCrowdId = isset($_GET['crowdid']) && $_GET['crowdid'] != '' ? intval($_GET['crowdid']) : 0;
        $iOrder     = isset($_GET['order']) && $_GET['order'] != '' ? intval($_GET['order']) : 0;
        $sIssue     = isset($_GET['issue']) && $_GET['issue'] != '' ? $_GET['issue'] : '';
        if( $iLotteryId == 0 )
        {
            sysMessage('没有指定游戏', 1, $aLocation);
        }
        /* @var $oLocks model_locks */
        $oCrowd = A::singleton('model_crowd', $GLOBALS['aSysDbServer']['report']);
        $aCrowd = $oCrowd->crowdGetList($iLotteryId);
        if($iCrowdId == 0 && !empty($aCrowd))
        {
            $iCrowdId = $aCrowd[0]['crowdid'];
        }
        $aLocksMethod = $oLocks->getMethodGroupList($iLotteryId, $iCrowdId);
        foreach ($aLocksMethod as $iKey => &$aTemp)
        {
            if( $aTemp['locktablecnname'] == '' )
            {
                unset($aLocksMethod[$iKey]);
            }
        }
        //对玩法进行分行显示
        $aLocksGroup = array_chunk( $aLocksMethod, 9, TRUE );
        //获取默认methodid
        if(isset($_GET['methodid']) && $_GET['methodid'] != '')
        {
            $iDefaultMethodid = intval($_GET['methodid']);
        }
        else
        {
            $iDefaultMethodid = isset($aLocksMethod[0]['methodid']) && $aLocksMethod[0]['methodid'] != 0 ?
                intval($aLocksMethod[0]['methodid']) : 1;
        }
        $iMethodId  = isset($_GET['methodid']) && $_GET['methodid'] != '' ? intval($_GET['methodid']) : $iDefaultMethodid;
        $sIssue     = isset($_GET['issue']) && $_GET['issue'] != '' ? $_GET['issue'] : '';
        $sSeltype   = isset($_GET['seltype']) && $_GET['seltype'] != '' ? $_GET['seltype'] : 'total';//查询类型，默认为总计
        if( $iLotteryId == 0  || $iMethodId == 0 )
        {
            sysMessage('没有指定游戏', 1, $aLocation);
        }
        $aLocks = $oLocks->getMethodLock( $iLotteryId, $iMethodId );
        $sLockTableName = isset($aLocks['lockname']) && $aLocks['lockname'] != '' ? $aLocks['lockname'] : '';
        if( $sLockTableName == '')
        {
            sysMessage('没有指定封锁表', 1, $aLocation);
        }
        $aLocks['lotteryid'] = $iLotteryId;
        $aDebug = array();
        $aDebug['iTotalSum'] = 0;
        /* @var $oIssueInfo model_issueinfo */
        $oIssueInfo = A::singleton('model_issueinfo', $GLOBALS['aSysDbServer']['report']);
        $sNowTime = date("Y-m-d",time());//需要调整
        $aLocks['issue'] = $oIssueInfo->getItems($iLotteryId, $sNowTime);
        if( empty($aLocks['issue']) )
        {
            sysMessage('数据错误:当天奖期不存在!', 1, $aLocation);
        }
        if( $sIssue == '' )
        {
            foreach ( $aLocks['issue'] as $aValue )
            {
                if( $aValue['salestart'] < date("Y-m-d H:i:s",time()) && $aValue['saleend'] > date("Y-m-d H:i:s",time()))
                {
                    $sIssue = $aValue['issue'];//封锁查看调整默认期数为当前销售期
                    break;
                }
            }
            if($sIssue == '')
            {
                $sIssue = $aLocks['issue'][0]['issue'];//封锁查看调整默认期数为当前销售期
            }
        }
        $aLocks['currentissue'] = $sIssue;
        $aLockTableMethod = $oLocks->getLockTableMethod($sLockTableName, $sIssue, $iLotteryId);
        $aLocks['relation'] = '';
        if( count($aLockTableMethod) > 1 )
        {
            $aLocks['relation'] = $aLockTableMethod;//同一个封锁表中有多个玩法存在
        }
        if( $sSeltype == 'total' )//封锁表中数据总计
        {
            $sMethodId = '';
            foreach ($aLockTableMethod as $aMethod)
            {
                $sMethodId .= $aMethod['methodid'].',';//查询的玩法ID组
            }
            $sMethodId = substr($sMethodId, 0, -1);
            //对于同一个封锁表中的玩过多，大于五时，不进行全部的总计
            if( count($aLockTableMethod) > 5)
            {
                $sMethodId = $aLockTableMethod[0]['methodid'];
                $sSeltype = 'method';
                $aLocks['nototal'] = 1;
            }
        }
        else
        {
            $sMethodId = $iMethodId;//查询的玩法ID
        }
        $aLockResult = $oLocks->getLockData($sLockTableName, $sIssue, $iLotteryId, $sMethodId, $sSeltype );
        if( $aLockResult['error'] > 0 )
        {
            sysMessage('数据错误:没有获取到封锁数据!', 1, $aLocation);
        }
        elseif( isset($aLockResult['lose']) && !empty($aLockResult['lose']))
        {
            $aLockData = array();
            $iCodeNum = count($aLockResult['lose']);
            foreach ($aLockResult['lose'] as $aLose )
            {
                if( !isset($aLockResult['win']) || empty($aLockResult['win']) )
                {
                    $aLockData[$aLose['code']] = 0 - $aLose['totalLocks'];
                }
                else
                {
                    $aLockData[$aLose['code']] = $aLockResult['win']['totalmoney'] - $aLockResult['win']['totalpoint'] - $aLose['totalLocks'];
                }
            }
            $aDebug['extend']    =  $this->getStatDatas( $aLockData );
            if( $iOrder == 0 )
            {
                ksort($aLockData);//按号码排序
            }
            else
            {
                arsort($aLockData);//按封锁值排序
            }
            //转义大小单双和定单双以及一些特殊的号码显示
            if( $aLocks['addslastype'] > 0)
            {
                $aNewLockData = array();
                foreach ($aLockData as $sCode => $aLockValue)
                {
                    $sNewCode = model_projects::AddslasCode($sCode, $iMethodId, $aLocks['addslastype']);
                    $aNewLockData[$sNewCode] = $aLockValue;
                }
                $aLockData = $aNewLockData;
            }
            $iNumPerCol = ceil(count($aLockData)/5);
            $aData = array_chunk( $aLockData, $iNumPerCol, TRUE );
            //解决号码个数据不是5的倍数据时候页面显示问题开始
            $iGroupNum = count($aData);
            switch ($iGroupNum )
            {
                case 1:
                    for ( $j=1; $j<=4; $j++ )
                    {
                        for( $i=1; $i<=$iNumPerCol; $i++ )
                        {
                            $aData[$j][] = '---';
                        }
                    }
                    break;
                case 2:
                case 3:
                case 4:
                case 5:
                    $iKey  = $iGroupNum - 1;
                    $iDiff = 0;
                    if(count($aData[$iKey]) != $iNumPerCol)
                    {
                        $iDiff = $iNumPerCol - count($aData[$iKey]);
                    }
                    for( $i=1; $i<=$iDiff; $i++ )
                    {
                        $aData[$iKey][] = '---';
                    }
                    for ( $j=$iGroupNum; $j<=4; $j++ )
                    {
                        for( $i=1; $i<=$iNumPerCol; $i++ )
                        {
                            $aData[$j][] = '---';
                        }
                    }
                    break;
                default:
                    break;
            }
            //解决号码个数据不是5的倍数据时候页面显示问题结束
            $aLocks['lockdata'] = $aData;
            $aDebug['iTotalSale']  = empty($aLockResult['sales']['sum_sales']) ? 0 : $aLockResult['sales']['sum_sales'];
            $aDebug['iNumCount']   = $iCodeNum;
            $aDebug['iTotalPoint'] = empty($aLockResult['win']['totalpoint']) ? 0 : $aLockResult['win']['totalpoint'];
            $iTotalPrice = $aDebug['iTotalSale']-$aDebug['iTotalPoint'];
            //统计理论利润率 
            $oRealLock = new model_reallock();
            $aRealLock = $oRealLock->getRealLock( $sLockTableName, $sIssue );
            if( $aRealLock !== FALSE )
            {
                $aDebug['iPercent'] = ( ($aRealLock['codenum']*$iTotalPrice - $aRealLock['totallock']) / $aRealLock['codenum'] ) /
                ( $aDebug['iTotalSale']!=0 ? $aDebug['iTotalSale'] : 1 ) * 100;
            }
            else 
            {
                $aDebug['iPercent'] = 0;
            }
            if($iCodeNum != $aRealLock['codenum'])
            {
                $aDebug['isshowfanca'] = FALSE;
                $aDebug['extend']['junshu'] = $aRealLock['totallock']/$aRealLock['codenum'];
            }
            else 
            {
                $aDebug['isshowfanca'] = TRUE;
            }
            $aLocks['debug'] = $aDebug;
        }
        $GLOBALS['oView']->assign( "lotteryid", $iLotteryId);
        $GLOBALS['oView']->assign( "methodid", $iMethodId);
        $GLOBALS['oView']->assign( "crowdid", $iCrowdId);
        $GLOBALS['oView']->assign( "lotteryname", $sLotteryName);
        $GLOBALS['oView']->assign( "ur_here", $sLotteryName."封锁表查看");
        $sLotteryName = strtolower($sLotteryName);
        $GLOBALS['oView']->assign( "action", 'lockdetail');
        $GLOBALS["oView"]->assign( "method",    $aLocksGroup );
        $GLOBALS["oView"]->assign( "crowd",    $aCrowd );
        $GLOBALS["oView"]->assign( "defaultmethod", $iDefaultMethodid  );
        $GLOBALS['oView']->assign( "order",$iOrder);
        $GLOBALS['oView']->assign( "issue",$sIssue);
        $GLOBALS['oView']->assign( "adata",$aLocks['lockdata']);
        $GLOBALS['oView']->assign( "aDebug",$aLocks['debug']);
        $GLOBALS['oView']->assign( "aIssue",$aLocks['issue']);
        $GLOBALS['oView']->assign( "currentissue", $aLocks['currentissue']);
        $GLOBALS['oView']->assign( "relation", $aLocks['relation']);
        $GLOBALS['oView']->assign( "nototal", isset($aLocks['nototal']) ? $aLocks['nototal'] : 0);
        $GLOBALS['oView']->assign( "ishaverelation", empty($aLocks['relation']) ? 0 : 1);
        $GLOBALS['oView']->assign( "querytype", $sSeltype);
        $oLocks->assignSysInfo();
        $GLOBALS['oView']->display( "lock_view.html" );
    }
    
    
    
    /**
     * 私有方法, 根据参数, 获取方差与变异系数等返回
     * 
     * 参数:
     * @param array $aTotalDatas    所有号码封锁值的数组
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
    	$aReturn['bianyixishu']     = $aReturn['junshu']==0 ? '0' : ($aReturn['biaozhunfangcha'] / $aReturn['junshu']);
    	return $aReturn;   	
    }
}