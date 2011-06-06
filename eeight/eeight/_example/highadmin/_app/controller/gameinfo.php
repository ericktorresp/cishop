<?php
/**
 * 文件 : /_app/controller/gameinfo.php
 * 功能 : 控制器 - 游戏信息管理
 * 
 *    - actionList()            游戏信息列表
 *    - actionAdd()             增加彩种
 *    - actionEdit()            修改彩种
 *    - actionIssueSetList()    奖期时间管理列表
 *    - actionEditIssueSet()    修改奖期时间段
 *    - actionDelIssueSet()     删除奖期时间段
 *    - actionAddIssueSet()     添加奖期时间段
 *    - actionIssuelist()       查看奖期
 *    - actionGeneralissue()    批量生成奖期
 *    - actionPlaylist()        游戏玩法组列表
 *    - actionAddplay()         增加游戏玩法
 *    - actionPlayedit()        修改玩法
 *    - actionPointSet()        设置点差
 *    - actionSleepdate         休市时间管理
 *    - actionGameStart()       开始销售游戏
 *    - actionGameStop()        停止销售游戏
 *    - actionPrizegroup        奖金组模版     
 *    - actionPrizegroupadd     增加奖金组模版
 *    - actionPrizegroupedit    修改奖金组模版
 *    - actionassign            分配奖金组模版
 *    - actionverify            验证奖金组模版
 *    - actionPrizelevel        奖金组详情查看
 *    - actionUserpgstart()     启用用户奖金组
 *    - actionUserpgview()      对总代奖金组进行查看
 *    - actionUserpgedit()      对总代奖金组进行调整
 *    - actionassign()          分配奖组给总代
 *    - actionVerify()          对方案组进行审核
 * 
 * @author	    Mark, Rojer
 * @version    1.0.0
 * @package    highadmin
 */

class controller_gameinfo extends basecontroller
{
    /**
     * 游戏信息列表
     * URL = ./index.php?controller=gameinfo&action=list
     * @author Mark
     * Tom 效验通过于 0204 11:21
     */
    public function actionList()
    {
        /* @var $oLottery model_lottery */
    	$oLottery = A::singleton("model_lottery");
    	$aLottery = $oLottery->lotteryMethodGetList( '', '', ' ORDER BY a.`sorts` ASC ', 0 );
    	foreach( $aLottery as & $aLotteryDetail )
    	{
    	    $aLotteryDetail['issueset']      = unserialize($aLotteryDetail['issueset']);
    	    $aLotteryDetail['starttime']     = ''; // 某阶段的 奖期开始时间
            $aLotteryDetail['endtime']       = ''; // 某阶段的 奖期结束时间
    	    $aLotteryDetail['cycle']         = ''; // 某阶段的 奖期周期
    	    $aLotteryDetail['inputcodetime'] = ''; // 录入号码的差异秒数
    	    $aLotteryDetail['endsale']       = ''; // 截止销售的差异描述
    	    foreach( $aLotteryDetail['issueset'] as $aIssueSet )
    	    {
    	        $aLotteryDetail['cycle']         .= $aIssueSet['cycle'] . ',';
    	        $aLotteryDetail['inputcodetime'] .= $aIssueSet['inputcodetime'].',';
    	        $aLotteryDetail['endsale']       .= $aIssueSet['endsale'].',';
    	    }
    	    //获取第一段的开始时间和最后一段的结束时间
    	    reset($aLotteryDetail['issueset']);
    	    $aTemp = current($aLotteryDetail['issueset']);
    	    $aLotteryDetail['starttime'] = $aTemp['starttime'];
    	    $aTemp = end($aLotteryDetail['issueset']);
    	    $aLotteryDetail['endtime'] = $aTemp['endtime'];
    	    unset($aTemp);
    	    $aLotteryDetail['cycle'] = substr($aLotteryDetail['cycle'],0,-1);
    	    $aLotteryDetail['inputcodetime'] = substr($aLotteryDetail['inputcodetime'],0,-1);
    	    $aLotteryDetail['endsale'] = substr($aLotteryDetail['endsale'],0,-1);
    	}
        $GLOBALS['oView']->assign( "ur_here", "游戏信息列表");
        $aLocation[0] = array('text'=>'增加游戏信息','href'=>url('gameinfo','add'));
        $GLOBALS["oView"]->assign( "actionlink",    $aLocation[0] );
        $GLOBALS['oView']->assign( "aLottery",      $aLottery );
        $oLottery->assignSysinfo();
        $GLOBALS['oView']->display('gameinfo_list.html');
        EXIT;
    }



    /**
     * 增加彩种
     * URL = ./index.php?controller=gameinfo&action=add
     * @author Mark
     * Tom 效验通过于 0204 11:23
     */
    public function actionAdd()
    {
        $aLocation[0] = array( "text"=>'游戏信息列表', "href"=>url('gameinfo','list') );
        $aLocation[1] = array( "text"=>'增加游戏信息', "href"=>url('gameinfo','add') );
        /* @var $oLottery model_lottery */
        $oLottery = A::singleton("model_lottery");
        if( isset($_POST) && !empty($_POST) )
        {
			$aChineseNumber = array('一', '二', '三', '四', '五', '六', '七', '八', '九', '十');
			$aIssueSet = array();  // 存储每个阶段的各个时间
			/*
			 * 每个彩种允许包含多种销售时间(阶)段
			 *     例: 重庆时时彩 10:00-22:00 期间是10分钟一期
			 *                 而 22:00-02:00 期间是5分钟一期
			 *                 每天一共120期. 遵守相同的奖期编号递增规则
			 * -----------------------------------
			 * 由此产生了每个彩种,在不同销售时间阶段, 细节的参数可以不同
			 *     例: 10分钟周期时, 最后撤单,停售时间也许是 60 秒
			 *          5分钟周期时, 也许是30秒 ( 因为销售周期缩短 ) 
			 * -----------------------------------
			 * 需求:
			 *   每个阶段需要设置以下参数, 从而满足当前业务需求
			 *    1, 销售开始时间           例: 早上5点     ( 只是允许5点可以开始预先购买彩票 )
			 *    2, 第一期销售截止时间     例: 早上10:10   ( 此至减去周期数,既:得到某阶段第一期开售时间 )
			 *    3, 销售结束时间           例: 晚上22:00   ( 表示某阶段的销售结束时间 )
			 *    4, 销售周期               例: 10分钟
			 *    5, 停售时间(差异值)       例: 某期销售截止时间 - 此值 = 某期销售停止时间(之后用户无法购买)
			 *                                  此值=30, 则 10:09:30 秒后禁止购买当期
			 *    6, 号码录入时间(差异值)   例: 某期销售截止时间 + 此值 = 某期结束后开奖员允许录号的最早时间
			 *                                  此值=35, 则 10:10:35 后才允许开奖员进行号码录入 
			 *    7, 撤单时间(差异值)       例: 某期销售截止时间 - 此值 = 某期用户可以执行撤单的最晚时间
			 *                                  此值=40, 则 10:09:20 后禁止用户撤销当期注单的行为
			 * -----------------------------------
			 * 从 HTML 提交的字段描述:
			 *       html.starthour[]          每阶段 销售开始时间的 "小时" 数
			 *       html.startminute[]        每阶段 销售开始时间的 "分钟" 数
			 *       html.startsecond[]        每阶段 销售开始时间的 "秒" 数
			 *       html.firstendhour[]       每阶段 第一期销售截止时间的 "小时" 数
			 *       html.firstendminute[]     每阶段 第一期销售截止时间的 "分钟" 数
			 *       html.firstendsecond[]     每阶段 第一期销售截止时间的 "秒" 数
			 *       html.endhour[]            每阶段 总体的销售销售结束时间 "小时" 数
			 *       html.endminute[]          每阶段 总体的销售销售结束时间 "分钟" 数
			 *       html.endsecond[]          每阶段 总体的销售销售结束时间 "秒" 数
			 *       html.cycle[]              每阶段 的销售周期
			 *       html.endsale[]            每阶段 停售时间(差异值)
			 *       html.inputcodetime[]      每阶段 号码录入时间(差异值)
			 *       html.droptime[]           每阶段 撤单时间(差异值)
			 */
			$aTempSort = array_count_values($_POST['sort']);
			foreach( $aTempSort as $iSortCount )
			{
			    if( $iSortCount > 1 )
			    {
			        sysMessage('段间序号不能重复', 1 );
			    }
			}
			unset($aTempSort);
			foreach( $_POST['starthour'] as $iKey => $sStartHour )
			{
                $aIssueSet[$iKey]['starttime']    = $sStartHour . ":" . $_POST['startminute'][$iKey] . ":" .  
			                                         $_POST['startsecond'][$iKey];
			    $aIssueSet[$iKey]['firstendtime'] = $_POST['firstendhour'][$iKey] . ":" . $_POST['firstendminute'][$iKey] . 
			                                         ":" .  $_POST['firstendsecond'][$iKey];
			    $aIssueSet[$iKey]['endtime']      = $_POST['endhour'][$iKey] . ":" . $_POST['endminute'][$iKey] . ":" .  
			                                         $_POST['endsecond'][$iKey];                                  
			    if( !isset($_POST['cycle'][$iKey]) || $_POST['cycle'][$iKey] == '' )
			    {
			        sysMessage('第' . $aChineseNumber[$iKey]  . '段销售周期不能为空', 1, $aLocation);
			    }
			    $aIssueSet[$iKey]['cycle'] = intval($_POST['cycle'][$iKey]);
			    if( !isset($_POST['endsale'][$iKey]) || $_POST['endsale'][$iKey] == '' )
			    {
			        sysMessage('第' . $aChineseNumber[$iKey]  . '段停售时间不能为空', 1, $aLocation);
			    }
			    $aIssueSet[$iKey]['endsale'] = intval($_POST['endsale'][$iKey]);
			    if( !isset($_POST['inputcodetime'][$iKey]) || $_POST['inputcodetime'][$iKey] == '' )
			    {
			        sysMessage('第' . $aChineseNumber[$iKey]  . '段号码录入时间不能为空', 1, $aLocation);
			    }
			    $aIssueSet[$iKey]['inputcodetime'] = intval($_POST['inputcodetime'][$iKey]);
			    if( !isset($_POST['droptime'][$iKey]) || $_POST['droptime'][$iKey] == '' )
			    {
			        sysMessage('第' . $aChineseNumber[$iKey]  . '段撤单时间不能为空', 1, $aLocation);
			    }
			    $aIssueSet[$iKey]['droptime'] = intval($_POST['droptime'][$iKey]);
			    $aIssueSet[$iKey]['status'] = intval($_POST['status'][$iKey]);
			    if( !isset($_POST['sort'][$iKey]) || $_POST['sort'][$iKey] == '' )
			    {
			        sysMessage('第' . $aChineseNumber[$iKey]  . '段序号不能为空', 1, $aLocation);
			    } 
			    $aIssueSet[$iKey]['sort']   = intval($_POST['sort'][$iKey]);
			}
			unset($aChineseNumber);
			//按序号排序存储
			$aNewIssueSet = array();
			foreach( $aIssueSet as $aIssueSetDetail )
			{
			    $aNewIssueSet[$aIssueSetDetail['sort']] = $aIssueSetDetail;
			}
			unset($aIssueSet);
			ksort($aNewIssueSet);//按序号排序
			//页面POST数据
			$aLotteryData['cnname']              = $_POST['cnname'];
			$aLotteryData['enname']              = $_POST['enname'];
			$aLotteryData['date']                = $_POST['date'];
			$aLotteryData['type']                = $_POST['type'];
			$aLotteryData['norule']              = $_POST['norule'];
			$aLotteryData['issuerule']           = $_POST['issuerule'];
			$aLotteryData['minprofit']           = $_POST['minprofit'];
			$aLotteryData['mincommissiongap']    = $_POST['mincommissiongap'];
			$aLotteryData['description']         = $_POST['description'];
			$aLotteryData['yearlybreakstart']    = $_POST['yearlybreakstart'];
			$aLotteryData['yearlybreakend']      = $_POST['yearlybreakend'];
			$aLotteryData['sorts']               = $_POST['sorts'];
			$aLotteryData['pushtime']			 = $_POST['pushtime'];
			$aLotteryData['retry']				 = $_POST['retry'];
			$aLotteryData['delay']			 	 = $_POST['delay'];
			$aLotteryData['issueset']            = $aNewIssueSet;
            // 合并奖期规则
            $resetrule_year = intval($_POST['resetrule_year']) ? 1 : 0;
            $resetrule_month = intval($_POST['resetrule_month']) ? 1 : 0;
            $resetrule_day = intval($_POST['resetrule_day']) ? 1 : 0;
            $aLotteryData['issuerule'] .= "|{$resetrule_year},{$resetrule_month},{$resetrule_day}";
			$iResult = $oLottery->lotteryInsert( $aLotteryData );
			switch( $iResult )
			{
			    case 0:
			        sysMessage( '操作失败:数据错误.', 1 );
			        BREAK;
			    case -1:
			        sysMessage( '操作失败:彩种中文名称不存在.', 1 );
			        BREAK;
			    case -2:
			        sysMessage( '操作失败:彩种英文名称不存在.', 1 );
			        BREAK;
			    case -3:
			        sysMessage( '操作失败:彩种类型错误.', 1 );
			        BREAK;
			    case -4:
			        sysMessage( '操作失败:彩种周期错误.', 1 );
			        BREAK;
			    case -5:
			        sysMessage( '操作失败:彩种的奖期规则错误.', 1 );
			        BREAK;
			    case -6:
			        sysMessage( '操作失败:彩种的返点差错误.', 1 );
			        BREAK;
			    case -7:
			        sysMessage( '操作失败:彩种的最小留水错误.', 1 );
			        BREAK;
			    case -8:
			        sysMessage( '操作失败:奖期时间设置错误.', 1 );
			        BREAK;
			    case -9:
			        sysMessage( '奖期时间不符合规范. 时间点上有冲突', 1);
			        BREAK;
			    case -10:
                   sysMessage( '操作失败:休开始时间不能小于休市结束时间.', 1 );
                   BREAK;
                case -11:
			        sysMessage( '操作失败:抓号次数设置错误.', 1 );
			        BREAK;
			    case -12:
			        sysMessage( '操作失败：抓号间隔时间设置错误', 1);
			        BREAK;
			    case -13:
                   sysMessage( '操作失败:延后时间设置错误.', 1 );
                   BREAK;
			    default:
			        sysMessage( '操作成功', 0, $aLocation );
			        BREAK;
			}
        }
		else
		{
			$aHour = range(0,23);
			$aMinuteOrSecond = range(0,59);
			for( $i=0; $i<10; $i++ )
			{
			    $aHour[$i] = '0'.$aHour[$i];
			    $aMinuteOrSecond[$i] = '0'.$aMinuteOrSecond[$i];
			}
			$GLOBALS['oView']->assign( "ahour", $aHour );
			$GLOBALS['oView']->assign( "aminuteorsecond", $aMinuteOrSecond );
			$GLOBALS["oView"]->assign( "ur_here",       "增加游戏信息" );
			$GLOBALS['oView']->assign( "action", 'add');
			$GLOBALS["oView"]->assign( "actionlink",    $aLocation[0] );
			$oLottery->assignSysinfo();
			$GLOBALS["oView"]->display("gameinfo_edit.html" );
			EXIT;
        }
    }



    /**
     * 修改彩种
     * URL = ./index.php?controller=gameinfo&action=edit
     * @author mark
     * Tom 效验通过于 0204 11:23
     */
    public function actionEdit()
    {
        $aLocation[0] = array( "text"=>'游戏信息列表', "href"=>url('gameinfo','list') );
        $aChineseNumber = array('一', '二', '三', '四', '五', '六', '七', '八', '九', '十');
        if( isset($_POST) && !empty($_POST) )
        {
			$aIssueSet = array();
			$aTempSort = array_count_values($_POST['sort']); // 时间段的数量
			foreach( $aTempSort as $iSortCount )
			{
			    if( $iSortCount > 1 )
			    {
			        sysMessage('段间序号不能重复', 1 );
			    }
			}
			unset( $aTempSort );
			foreach( $_POST['starthour'] as $iKey => $sStartHour )
            {
			    $aIssueSet[$iKey]['starttime']    = $sStartHour . ":" . $_POST['startminute'][$iKey] . ":" .  
			                                         $_POST['startsecond'][$iKey];
			    $aIssueSet[$iKey]['firstendtime'] = $_POST['firstendhour'][$iKey] . ":" . $_POST['firstendminute'][$iKey] . 
			                                         ":" .  $_POST['firstendsecond'][$iKey];
			    $aIssueSet[$iKey]['endtime']      = $_POST['endhour'][$iKey] . ":" . $_POST['endminute'][$iKey] . ":" .  
			                                         $_POST['endsecond'][$iKey];
			    if( !isset($_POST['cycle'][$iKey]) || $_POST['cycle'][$iKey] == '' )
			    {
			        sysMessage('第' . $aChineseNumber[$iKey]  . '段销售周期不能为空', 1, $aLocation);
			    }
			    $aIssueSet[$iKey]['cycle'] = intval($_POST['cycle'][$iKey]);
			    if( !isset($_POST['endsale'][$iKey]) || $_POST['endsale'][$iKey] == '' )
			    {
			        sysMessage('第' . $aChineseNumber[$iKey]  . '段停售时间不能为空', 1, $aLocation);
			    }
			    $aIssueSet[$iKey]['endsale'] = intval($_POST['endsale'][$iKey]);
			    if( !isset($_POST['inputcodetime'][$iKey]) || $_POST['inputcodetime'][$iKey] == '' )
			    {
			        sysMessage('第' . $aChineseNumber[$iKey]  . '段号码录入时间不能为空', 1, $aLocation);
			    }
			    $aIssueSet[$iKey]['inputcodetime'] = intval($_POST['inputcodetime'][$iKey]);
			    if( !isset($_POST['droptime'][$iKey]) || $_POST['droptime'][$iKey] == '' )
			    {
			        sysMessage('第' . $aChineseNumber[$iKey]  . '段撤单时间不能为空', 1, $aLocation);
			    }
			    $aIssueSet[$iKey]['droptime'] = intval($_POST['droptime'][$iKey]);
			    $aIssueSet[$iKey]['status'] = intval($_POST['status'][$iKey]);
			    if( !isset($_POST['sort'][$iKey]) || $_POST['sort'][$iKey] == '' )
			    {
			        sysMessage('第' . $aChineseNumber[$iKey]  . '段序号不能为空', 1, $aLocation);
			    }
                $aIssueSet[$iKey]['sort']   = intval($_POST['sort'][$iKey]);
            }
			//按序号排序存储
			$aNewIssueSet = array();
			foreach( $aIssueSet as $aIssueSetDetail )
			{
			    $aNewIssueSet[$aIssueSetDetail['sort']] = $aIssueSetDetail;
            }
            unset($aIssueSet);
            ksort($aNewIssueSet);//按序号排序
            //页面POST数据
            $aLotteryData['lotteryid']           = $_POST['lotteryid'];
			$aLotteryData['cnname']              = $_POST['cnname'];
			$aLotteryData['enname']              = $_POST['enname'];
			$aLotteryData['date']                = $_POST['date'];
			$aLotteryData['norule']              = $_POST['norule'];
			$aLotteryData['issuerule']           = $_POST['issuerule'];
			$aLotteryData['minprofit']           = $_POST['minprofit'];
			$aLotteryData['mincommissiongap']    = $_POST['mincommissiongap'];
			$aLotteryData['description']         = $_POST['description'];
			$aLotteryData['yearlybreakstart']    = $_POST['yearlybreakstart'];
			$aLotteryData['yearlybreakend']      = $_POST['yearlybreakend'];
			$aLotteryData['sorts']               = $_POST['sorts'];
            $aLotteryData['issueset']            = $aNewIssueSet;
            $aLotteryData['delay']              = $_POST['delay'];
            $aLotteryData['retry']              = $_POST['retry'];
            $aLotteryData['pushtime']           = $_POST['pushtime'];
            $iResetRule_year                     = intval($_POST['resetrule_year'])  ? 1 : 0;
            $iResetRule_month                    = intval($_POST['resetrule_month']) ? 1 : 0;
            $iResetRule_day                      = intval($_POST['resetrule_day'])   ? 1 : 0;
            $aLotteryData['issuerule'] .= "|{$iResetRule_year},{$iResetRule_month},{$iResetRule_day}";
            $iLottery = isset($aLotteryData['lotteryid']) && is_numeric($aLotteryData['lotteryid'])
                            ? intval($aLotteryData['lotteryid']) : 0;
            if( $iLottery <= 0 )
            {
                sysMessage( '操作失败:数据错误.', 1, $aLocation );
            }
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $iResult  = $oLottery->lotteryUpdate( $aLotteryData, "`lotteryid`='".$iLottery."'" );
            switch( $iResult )
            {
				case 0:
				    sysMessage( '操作失败:没有数据更新', 1 );
				    BREAK;
				case -1:
				    sysMessage( '操作失败:数据错误.', 1 );
				    BREAK;
				case -2:
				    sysMessage( '操作失败:彩种中文名称不存在.', 1 );
				    BREAK;
				case -3:
				    sysMessage( '操作失败:彩种英文名称不存在.', 1 );
				    BREAK;
				case -4:
				    sysMessage( '操作失败:不能修改彩种类型.', 1 );
				    BREAK;
				case -5:
				    sysMessage( '操作失败:彩种周期错误.', 1 );
				    BREAK;
				case -6:
				    sysMessage( '操作失败:彩种的奖期规则错误.', 1 );
				    BREAK;
				case -7:
				    sysMessage( '操作失败:彩种的返点差.', 1 );
				    BREAK;
				case -8:
				    sysMessage( '操作失败:彩种的最小留水错误.', 1 );
				    BREAK;
				case -9:
				    sysMessage( '操作失败:彩种的公司最小留水(动态调价参数)错误.', 1 );
				    BREAK;
				case -10:
				    sysMessage( '操作失败:奖期时间不符合规范. 时间点上有冲突.', 1 );
				    BREAK;
				case -11:
				    sysMessage( '操作失败:休开始时间不能小于休市结束时间.', 1 );
				    BREAK;
                case -12:
                    sysMessage( '操作失败:请填写抓号重试次数', 1 );
                    BREAK;
                case -13:
                    sysMessage( '操作失败:请正确填写抓号间隔时间，最低不得低于30秒，最高不得高于300秒', 1 );
                    BREAK;
                case -14:
                    sysMessage( '操作失败:请填写延后时间', 1 );
                    BREAK;
				default:
				    sysMessage( '操作成功', 0, $aLocation );
				    BREAK;
            }
        }
        else
        {
            /* @var $oLottery model_lottery */
            $oLottery   = A::singleton("model_lottery");
            $iLotteryId = isset($_GET['id']) && $_GET['id'] != '' ? intval($_GET['id']) : 0;
            if( $iLotteryId == 0 )
            {
                sysMessage( '请选择彩种', 1, $aLocation );
            }
            $aLottery = $oLottery->getItem( $iLotteryId );
            $aLottery['no_rule']   = unserialize( $aLottery['numberrule'] );
            //整理issueset数据,用于表单显示
            foreach ( $aLottery['issueset'] as & $aIssueSet )
            {
                $aStartTime = explode( ":", $aIssueSet['starttime'] );
                unset($aIssueSet['starttime']);
                $aFirstEndTime = explode( ":", $aIssueSet['firstendtime'] );
                unset($aIssueSet['firstendtime']);
                $aEndTime = explode( ":", $aIssueSet['endtime'] );
                unset($aIssueSet['endtime']);
                list($aIssueSet['starthour'], $aIssueSet['startminute'], $aIssueSet['startsecond']) = $aStartTime;
                list($aIssueSet['firstendhour'], $aIssueSet['firstendminute'], $aIssueSet['firstendsecond']) = $aFirstEndTime;
                list($aIssueSet['endhour'], $aIssueSet['endminute'], $aIssueSet['endsecond']) = $aEndTime;
            }
            $aHour           = range(0,23);
            $aMinuteOrSecond = range(0,59);
            for ($i = 0; $i<10; $i++)
            {
                $aHour[$i] = '0'.$aHour[$i];
                $aMinuteOrSecond[$i] = '0'.$aMinuteOrSecond[$i];
            }
            $GLOBALS['oView']->assign( "ahour", $aHour );
            $GLOBALS['oView']->assign( "aminuteorsecond", $aMinuteOrSecond );
            $GLOBALS['oView']->assign( "lottery", $aLottery);
            $GLOBALS['oView']->assign( "issueformat", substr($aLottery['issuerule'], 0, strpos($aLottery['issuerule'], '|')));
            $GLOBALS['oView']->assign( "resetrule", explode(',', substr($aLottery['issuerule'], strpos($aLottery['issuerule'], '|')+1)));
            $GLOBALS['oView']->assign( "action", 'edit');
            $GLOBALS['oView']->assign( "chinesenumber", $aChineseNumber );
            $GLOBALS["oView"]->assign( "ur_here",       "修改游戏信息" );
            $GLOBALS["oView"]->assign( "actionlink",    $aLocation[0] );
            $oLottery->assignSysinfo();
            $GLOBALS["oView"]->display("gameinfo_edit.html" );
            EXIT;
        }
    }



    /**
     * 奖期时间管理列表
     * URL = ./index.php?controller=gameinfo&action=issuesetlist
     * @author mark
     * Tom 效验通过于 0204 11:35
     */
    public function actionIssueSetList()
    {
    	$aLocation[0] = array( "text"=>'游戏信息列表', "href"=>url('gameinfo','list') );
        /* @var $oLottery model_lottery */
        $oLottery = A::singleton("model_lottery");
        $iLotteryId = isset($_GET['id']) && $_GET['id'] != '' ? intval($_GET['id']) : 0;
        if( $iLotteryId == 0 )
        {
            sysMessage( '请选择彩种', 1, $aLocation );
        }
        $aLottery = $oLottery->getItem( $iLotteryId );
        //整理issueset数据,用于显示
        $aLottery['issueset'] = $aLottery['issueset'];
        $GLOBALS["oView"]->assign( "ur_here", "奖期时间规则管理" );
        $GLOBALS["oView"]->assign( "aLottery",   $aLottery );
        $GLOBALS["oView"]->assign( "actionlink", $aLocation[0] );
        $oLottery->assignSysinfo();
        $GLOBALS['oView']->display( "gameinfo_issuetsetlist.html" );
        EXIT;
    }



    /**
     * 修改奖期时间段
     * URL = ./index.php?controller=gameinfo&action=editissueset
     * @author mark
     * Tom 效验通过于 0204 11:56
     */
    public function actionEditIssueSet()
    {
        $aLocation[0] = array( "text"=>'游戏信息列表', "href"=>url('gameinfo','list') );
        /* @var $oLottery model_lottery */
        $oLottery = A::singleton("model_lottery");
        if( isset($_POST) && !empty($_POST) )
        {
            $iLotteryId  = isset($_POST['lotteryid']) && $_POST['lotteryid'] != '' ? intval($_POST['lotteryid']) : 0;
            $iIssueSetId = isset($_POST['issuesetid']) && $_POST['issuesetid'] != '' ? intval($_POST['issuesetid']) : 0;
            $aLottery = $oLottery->getItem($iLotteryId );
            if( empty($aLottery))
            {
                sysMessage( '游戏不存在', 1, $aLocation );
            }
            $aLocation[1] = array( "text"=>'修改奖期时间规则', "href"=>url('gameinfo', 'editissueset', array('lotteryid'=>$iLotteryId,'id'=>$iIssueSetId)) );
            $aLocation[0] = array( "text"=>'奖期时间规则管理', "href"=>url('gameinfo', 'issuesetlist', array('id'=>$iLotteryId)) );
            $aOldIssueSet = $aLottery['issueset'];
            $aIssueSet = array();
            $aIssueSet['starttime']    = $_POST['starthour'] . ":" . $_POST['startminute'] . ":" . $_POST['startsecond'];
            $aIssueSet['firstendtime'] = $_POST['firstendhour'] . ":" . $_POST['firstendminute'] .":" . $_POST['firstendsecond'];
            $aIssueSet['endtime']      = $_POST['endhour'] . ":" . $_POST['endminute'] . ":" . $_POST['endsecond'];
            if( !isset($_POST['sort']) || $_POST['sort'] == '' )
            {
                sysMessage('分段序号不能为空', 1, $aLocation);
            }
            $aIssueSet['sort'] = intval($_POST['sort']);
            if( !isset($_POST['cycle']) || $_POST['cycle'] == '' )
            {
                sysMessage('段销售周期不能为空', 1, $aLocation);
            }
            $aIssueSet['cycle'] = intval($_POST['cycle']);
            if( !isset($_POST['endsale']) || $_POST['endsale'] == '' )
            {
                sysMessage('段停售时间不能为空', 1, $aLocation);
            }
            $aIssueSet['endsale'] = intval($_POST['endsale']);
            if( !isset($_POST['inputcodetime']) || $_POST['inputcodetime'] == '' )
            {
                sysMessage('段号码录入时间不能为空', 1, $aLocation);
            }
            $aIssueSet['inputcodetime'] = intval($_POST['inputcodetime']);
            if( !isset($_POST['droptime']) || $_POST['droptime'] == '' )
            {
                sysMessage('段撤单时间不能为空', 1, $aLocation);
            }
            $aIssueSet['droptime'] = intval($_POST['droptime']);
            $aIssueSet['status'] = intval($_POST['status']);
            $bIsCreateNow = isset($_POST['iscreatnow']) && $_POST['iscreatnow'] != '' ? intval($_POST['iscreatnow']) : 0;
            if( !in_array($bIsCreateNow, array(0,1) ))
            {
                sysMessage('是否立即生成数据不正确', 1, $aLocation);
            }
            unset($aOldIssueSet[$iIssueSetId]);
            $aNewIssueSet = array();
            foreach( $aOldIssueSet as $aValue )
            {
                $aNewIssueSet[$aValue['sort']] = $aValue;
            }
            $iRepeat = 0;
            foreach ($aNewIssueSet as $aNewIssueSetDetail)
            {
                if( $aNewIssueSetDetail['sort'] == $aIssueSet['sort'] )
                {
                    $iRepeat++;
                }
            }
            if( $iRepeat > 0)
            {
                sysMessage('段间序号不能重复', 1, $aLocation);
            }
            $aNewIssueSet[$aIssueSet['sort']] = $aIssueSet;
            ksort($aNewIssueSet); 
            $iFlag = $oLottery->updateIssueSet( $aNewIssueSet, $iLotteryId, $bIsCreateNow );
            if($iFlag > 0)
            {
                sysMessage('操作成功', 0, $aLocation);
            }
            elseif ($iFlag == 0 )
            {
                sysMessage( '操作失败：没有数据更新', 1, $aLocation );
            }
            elseif ($iFlag == -2 )
            {
                sysMessage( '操作失败：奖期时间设置不正确，时间点有冲突', 1, $aLocation );
            }
            else 
            {
                sysMessage('操作失败:更新数据发生错误！', 1, $aLocation);
            }
        }
        else 
        {
            $iLotteryId  = isset($_GET['lotteryid']) && $_GET['lotteryid'] != '' ? intval($_GET['lotteryid']) : 0;
            $iIssueSetId = isset($_GET['id']) && $_GET['id'] != '' ? intval($_GET['id']) : 0;
            if( $iLotteryId == 0 )
            {
                sysMessage( '请选择彩种', 1, $aLocation );
            }
            $aLottery = $oLottery->getItem($iLotteryId );
            //整理issueset数据,用于显示
            $aIssueSet = $aLottery['issueset'][$iIssueSetId];
            list($aIssueSet['starthour'], $aIssueSet['startminute'], $aIssueSet['startsecond']) = explode(":",$aIssueSet['starttime']);
            list($aIssueSet['firstendhour'], $aIssueSet['firstendminute'], $aIssueSet['firstendsecond']) = explode(":",$aIssueSet['firstendtime']);
            list($aIssueSet['endhour'], $aIssueSet['endminute'], $aIssueSet['endsecond']) = explode(":",$aIssueSet['endtime']);
            $aHour = range(0,23);
            $aMinuteOrSecond = range(0,59);
            for ($i = 0; $i<10; $i++)
            {
                $aHour[$i] = '0'.$aHour[$i];
                $aMinuteOrSecond[$i] = '0'.$aMinuteOrSecond[$i];
            }
            $GLOBALS['oView']->assign( "ahour", $aHour );
            $GLOBALS['oView']->assign( "aminuteorsecond", $aMinuteOrSecond );
            $GLOBALS["oView"]->assign( "ur_here", "修改奖期时间规则" );
            $GLOBALS["oView"]->assign( "aLottery", $aLottery );
            $GLOBALS['oView']->assign( "aIssueSet", $aIssueSet);
            $GLOBALS['oView']->assign( "lotteryid", $iLotteryId);
            $GLOBALS["oView"]->assign( "iIssueSetId", $iIssueSetId );
            $GLOBALS["oView"]->assign( "actionlink", array( "text"=>'修改奖期时间规则', 
                                   "href"=>url('gameinfo', 'issuesetlist', array('id'=>$iLotteryId)) ));
            $GLOBALS['oView']->assign( "action", 'editissueset');
            $oLottery->assignSysinfo();
            $GLOBALS['oView']->display( "gameinfo_issuesetinfo.html" );
            EXIT;
        }
    }



    /**
     * 删除奖期时间段
     * URL = ./index.php?controller=gameinfo&action=delissueset
     * @author mark
     * Tom 效验通过于 0204 12:02
     */
    public function actionDelIssueSet()
    {
        $aLocation[0] = array( "text"=>'游戏信息列表', "href"=>url('gameinfo','list') );
        /* @var $oLottery model_lottery */
        $oLottery = A::singleton("model_lottery");
        $iLotteryId  = isset($_GET['lotteryid']) && $_GET['lotteryid'] != '' ? intval($_GET['lotteryid']) : 0;
        $iIssueSetId = isset($_GET['id']) && $_GET['id'] != '' ? intval($_GET['id']) : 0;
        if( $iLotteryId == 0 )
        {
            sysMessage( '请选择彩种', 1, $aLocation );
        }
        $aLocation[1] = array( "text"=>'奖期时间规则管理', "href"=>url('gameinfo','issuesetlist',array('id'=>$iLotteryId)) );
        $aLottery = $oLottery->getItem($iLotteryId);
        $aOldIssueSet = $aLottery['issueset'];
        unset($aOldIssueSet[$iIssueSetId]);
        $aNewIssueSet = array();
        foreach( $aOldIssueSet as $aValue )
        {
            $aNewIssueSet[$aValue['sort']] = $aValue;
        }
        ksort($aNewIssueSet);
        $bFlag = $oLottery->updateIssueSet( $aNewIssueSet, $iLotteryId );
        if( $bFlag )
        {
            sysMessage('操作成功', 0, $aLocation);
        }
        else
        {
            sysMessage('操作失败', 1, $aLocation);
        }
    }



    /**
     * 查看奖期
     * URL = ./index.php?controller=gameinfo&action=issuelist
     * @author Rojer
     * Tom 效验通过于 0222 14:47
     */
    public function actionIssuelist()
    {
        $aLocation[0]   = array( "text"=>'游戏信息列表', "href"=>url('gameinfo','list') );
        $iLotteryId     = isset($_GET["lotteryId"])&&is_numeric($_GET["lotteryId"]) ? intval($_GET["lotteryId"]) : 0;
        if( $iLotteryId==0 )
        {
            sysMessage("操作失败:彩种ID不正确.", 1, $aLocation);
        }
        $aLocation[1] = array( 'text'=>'批量生成游戏奖期', 'href'=>url('gameinfo','generalissue',array('lotteryId'=>$iLotteryId)));
        
        $GLOBALS['oView']->assign( "lotteryId", $iLotteryId );
        $GLOBALS['oView']->assign( "ur_here",       "奖期管理" );
        $GLOBALS['oView']->assign( "actionlink",    $aLocation[0] );
        $GLOBALS['oView']->assign( "actionlink2",   $aLocation[1] );
        /* @var $oIssue model_issueinfo */
        $oIssue = A::singleton("model_issueinfo");
        $iPage  = isset($_GET["p"])&&is_numeric($_GET["p"]) ? intval($_GET["p"]) : 1;
        $aIssue = $oIssue->issueGetList("A.*,B.`cnname`",
                    "A.`lotteryid`='".$iLotteryId."'", '', 25, $iPage );
        $GLOBALS['oView']->assign( "aIssue", $aIssue['results'] );
        $oPage  = new pages( $aIssue['affects'], 25 );
        $GLOBALS['oView']->assign( "pageinfo", $oPage->show(1) );
        $oIssue->assignSysInfo();
        $GLOBALS['oView']->display( "gameinfo_issuelist.html" );
        EXIT;
    }



    /**
     * 删除奖期
     * URL = ./index.php?controller=gameinfo&action=deleteissue
     * @author Rojer
     * Tom 效验通过于 0222 15:02
     */
    function actionDeleteissue()
    {
        $aLocation[0] = array(  "text"=>'游戏信息管理', "href"=>url('gameinfo','list') );
        $deleteItems  = (array)$_POST['deleteItems'];
        $count        = array(0, 0);
        $oIssue = A::singleton("model_issueinfo");
        foreach( $deleteItems as $v )
        {
            if( $oIssue->deleteItem($v) )
            {
                $count[0]++;
            }
            else
            {
                $count[1]++;
            }
        }
        sysMessage( "共有 {$count[0]} 个被成功删除，{$count[1]} 个删除失败！", 0, $aLocation );
    }



    /**
     * 奖期修改
     * @author Rojer
     * URL: ./index.php?controller=gameinfo&action=editissue
     * Tom 效验通过于 0222 15:52
     */
    function actionEditissue()
    {
        $aLocation[0] = array( "text"=>"游戏信息列表", "href"=>url('gameinfo','list') );
        $iLotteryId   = isset($_GET["lotteryId"]) ? intval($_GET["lotteryId"]) : 0;
        $iIssueId     = isset($_GET["issueId"]) ? intval($_GET["issueId"]) : 0;
        if( $iIssueId <= 0 || $iLotteryId <= 0 )
        {
            sysMessage('奖期ID或彩种ID不正确！', 1);
        }

        if( isset($_POST)&&!empty($_POST) )
        {
            $aIssue = array(
                'salestart' => $_POST['salestart'],
                'saleend' => $_POST['saleend'],
                'canneldeadline' => $_POST['canneldeadline'],
                );
            /* @var $oIssue model_issueinfo */
            $oIssue = A::singleton("model_issueinfo");
            $iResult = $oIssue->updateItem( $iIssueId, $aIssue );
            $aLocation[1] =array( "text"=>'奖期列表', "href"=>url('gameinfo',"issuelist",array('lotteryId'=>$iLotteryId)) );
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
                    sysMessage( '操作失败:时间顺序不一致，开始时间 < 最后撤单时间 < 奖期截止时间.', 1, $aLocation );
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
            /* @var $oIssue model_issueinfo */
            $oIssue = A::singleton("model_issueinfo");
            if( !$aIssue = $oIssue->getItem($iIssueId) )
            {
                sysMessage('取数据失败！', 1);
            }
            $oLottery = A::singleton("model_lottery");
            $aLottery = $oLottery->getItem($aIssue['lotteryid']);

            $GLOBALS['oView']->assign( "issueId", $iIssueId );
            $GLOBALS['oView']->assign( "lotteryId", $iLotteryId );
            $GLOBALS['oView']->assign( "aIssue", $aIssue );
            $GLOBALS['oView']->assign( "aLottery", $aLottery );
            $GLOBALS['oView']->assign( "ur_here", "修改奖期信息" );
            $GLOBALS['oView']->assign( "actionlink", $aLocation[0] );
            $GLOBALS['oView']->assign( "actionlink2", array( "text"=>'游戏奖期列表', "href"=>url('gameinfo', "issuelist", array('lotteryId'=>$aIssue["lotteryid"]))));
            $oIssue->assignSysInfo();
            $GLOBALS['oView']->display("gameinfo_editissue.html");
            EXIT;
        }
    }



    /**
     * 批量生成奖期
     * URL = ./index.php?controller=gameinfo&action=generalissue
     * @author Rojer
     * Tom 效验通过于 0222 16:40
     */
    public function actionGeneralissue()
    {
        $aLocation[0]  = array( "text"=>'游戏信息列表', "href"=>url('gameinfo','list') );
        $iLotteryId    = isset($_GET["lotteryId"]) ? intval($_GET["lotteryId"]) : 0;
        $sa            = isset($_POST['sa']) ? $_POST['sa'] : NULL;
        $oLottery      = A::singleton("model_lottery");
        if( !$aLottery = $oLottery->getItem($iLotteryId) )
        {
            sysMessage( "操作失败:获取彩种信息失败.", 1, $aLocation );
        }

        switch( $sa )
        {
            case 'confirm': // 生成奖期执行前的 "确认页面"
            {
                $firstDate = isset($_POST['firstDate']) ? $_POST['firstDate'] : '';
                $startDate = strtotime($_POST['startDate']);
                $endDate   = strtotime($_POST['endDate']);
                $date1     = date('Y-m-d', $startDate);  // 管理员提交的生成奖期的 起始时间
                $date2     = date('Y-m-d', $endDate);    // 管理员提交的生成奖期的 结束时间
                if( $endDate < $startDate)
                {
                    sysMessage( "结束日期不能小于开始日期！", 1);
                }

                $oIssue = A::singleton("model_issueinfo");
                //找出和现有奖期日期是否有重复
                $intersectDates = array('startday' => '0', 'endday' => '0', 'intersect_startday' => '0', 'intersect_endday' => '0');
                if( $dayIssues = $oIssue->getDayIssues($iLotteryId) )
                {
                	/**
                	 * $dayIssues = 
                	 *       [2010-02-21] => 72
					 *       [2010-02-22] => 72
					 *       [2010-02-24] => 72
					 *       [2010-02-26] => 72
                	 */
                    $tmp = array_keys($dayIssues);
                    $intersectDates = array('startday' => reset($tmp), 'endday' => end($tmp));
                    if( $date1 <= $intersectDates['endday'] )
                    {
                        if( $date1 < $intersectDates['startday'] )
                        {
                            $intersectDates['intersect_startday'] = $intersectDates['startday'];
                        }
                        else
                        {
                            $intersectDates['intersect_startday'] = $date1;
                        }

                        if ($date2 >= $intersectDates['endday'])
                        {
                            $intersectDates['intersect_endday'] = $intersectDates['endday'];
                        }
                        else
                        {
                            $intersectDates['intersect_endday'] = $date2;
                        }
                    }
                }
                //$i = 0;
                foreach( $dayIssues as $k => $v )
                {
                    if( $k == $date1 )
                    {
                        //$intersectDates = array_slice($dayIssues, $i, count($dayIssues), true);
                        $intersectDates['intersectday'] = $k;
                    }
                    //$i++;
                }

                $GLOBALS['oView']->assign( "ur_here", "确认增加游戏奖期" );
                $GLOBALS['oView']->assign( "lottery", $aLottery );
                $GLOBALS['oView']->assign( "intersectDates", $intersectDates );
                $GLOBALS['oView']->assign( "actionlink", $aLocation[0] );
                $GLOBALS['oView']->assign( "firstDate", $firstDate);
                $GLOBALS['oView']->assign( "date1", $date1);
                $GLOBALS['oView']->assign( "date2", $date2);
                $oLottery->assignSysInfo();
                $GLOBALS['oView']->display( "gameinfo_generalissueconfirm.html" );
                BREAK;
            }

            case 'addItem': // 生成奖期
            {
            	//print_rr($_POST);exit;
                $firstDate = isset($_POST['firstDate']) ? $_POST['firstDate'] : '';
                $startDate = strtotime($_POST['startDate']);
                $endDate   = strtotime($_POST['endDate']);
                if( $endDate < $startDate)
                {
                    sysMessage( "结束日期不能小于开始日期！", 1);
                }

                $oIssue = A::singleton("model_issueinfo");
                $number = $oIssue->generalIssue($aLottery['lotteryid'], $firstDate, $startDate, $endDate);
                sysMessage("操作成功，共生成 $number 个奖期。", 0, array(array( "text"=>'游戏信息列表', "href"=>url('gameinfo', 'list'))));
                BREAK;
            }

            default:
            {
            	// 显示增加游戏奖期页面
                $GLOBALS['oView']->assign( "ur_here", "增加游戏奖期" );
                $GLOBALS['oView']->assign( "lottery", $aLottery );
                $GLOBALS['oView']->assign( "needFirst",  strpos($aLottery['issuerule'], 'd') === false);
                $GLOBALS['oView']->assign( "actionlink", $aLocation[0] );
                $oLottery->assignSysInfo();
                $GLOBALS['oView']->display( "gameinfo_generalissue.html" );
                BREAK;
            }
        }
        EXIT;
    }



    /**
     * 添加奖期时间段
     * URL = ./index.php?controller=gameinfo&action=addissueset
     * @author mark
     * Tom 效验通过于 0222 16:41
     */
    public function actionAddIssueSet()
    {
        $aLocation[0] = array( "text"=>'游戏信息列表', "href"=>url('gameinfo','list') );
        /* @var $oLottery model_lottery */
        $oLottery = A::singleton("model_lottery");
        if( isset($_POST) && !empty($_POST) )
        {
            $iLotteryId  = isset($_POST['lotteryid']) && $_POST['lotteryid'] != '' ? intval($_POST['lotteryid']) : 0;
            $aLottery = $oLottery->getItem($iLotteryId );
            if( empty($aLottery) )
            {
                sysMessage('游戏不存在', 1, $aLocation);
            }
            $aLocation[1] = array( "text"=>'奖期时间规则管理', "href"=>url('gameinfo','issuesetlist',array('id'=>$iLotteryId)) );
            $aOldIssueSet = $aLottery['issueset'];
            $aIssueSet    = array();
            $aIssueSet['starttime']    = $_POST['starthour'] . ":" . $_POST['startminute'] . ":" . $_POST['startsecond'];
            $aIssueSet['firstendtime'] = $_POST['firstendhour'] . ":" . $_POST['firstendminute'] .":" . $_POST['firstendsecond'];
            $aIssueSet['endtime']      = $_POST['endhour'] . ":" . $_POST['endminute'] . ":" . $_POST['endsecond'];
            if( !isset($_POST['sort']) || $_POST['sort'] == '' )
            {
                sysMessage('段序号不能为空', 1, $aLocation);
            }
            $aIssueSet['sort'] = intval($_POST['sort']);
            if( !isset($_POST['cycle']) || $_POST['cycle'] == '' )
            {
                sysMessage('段销售周期不能为空', 1, $aLocation);
            }
            $aIssueSet['cycle'] = intval($_POST['cycle']);
            if( !isset($_POST['endsale']) || $_POST['endsale'] == '' )
            {
                sysMessage('段停售时间不能为空', 1, $aLocation);
            }
            $aIssueSet['endsale'] = intval($_POST['endsale']);
            if( !isset($_POST['inputcodetime']) || $_POST['inputcodetime'] == '' )
            {
                sysMessage('段号码录入时间不能为空', 1, $aLocation);
            }
            $aIssueSet['inputcodetime'] = intval($_POST['inputcodetime']);
            if( !isset($_POST['droptime']) || $_POST['droptime'] == '' )
            {
                sysMessage('段撤单时间不能为空', 1, $aLocation);
            }
            $aIssueSet['droptime'] = intval($_POST['droptime']);
            $aIssueSet['status']   = intval($_POST['status']);
            // bIsCreateNow 立即生成
            $bIsCreateNow = isset($_POST['iscreatnow']) ? intval($_POST['iscreatnow']) : 0 ;
            if( !in_array($bIsCreateNow, array(0,1)) )
            {
                sysMessage('是否立即生成数据不正确', 1, $aLocation);
            }
            $iRepeat = 0;
            foreach( $aOldIssueSet as $aOldIssueSetDetail )
            {
                if( $aOldIssueSetDetail['sort'] == $aIssueSet['sort'] )
                {
                    $iRepeat++;
                }
            }
            if( $iRepeat > 0 )
            {
                sysMessage('段间序号不能重复', 1, $aLocation);
            }
            $aOldIssueSet[$aIssueSet['sort']] = $aIssueSet;
            ksort($aOldIssueSet); 
            $iFlag = $oLottery->updateIssueSet( $aOldIssueSet, $iLotteryId, $bIsCreateNow );
            if( $iFlag > 0)
            {
                sysMessage('操作成功', 0, $aLocation);
            }
            elseif( $iFlag == 0 )
            {
                sysMessage( '操作失败：没有数据更新', 1, $aLocation );
            }
            elseif( $iFlag == -2 )
            {
                sysMessage( '操作失败：奖期时间设置不正确，时间点有冲突', 1, $aLocation );
            }
            else 
            {
                sysMessage('操作失败:更新数据发生错误！', 1, $aLocation);
            }
        }
        else 
        {
            $iLotteryId  = isset($_GET['lotteryid']) && $_GET['lotteryid'] != '' ? intval($_GET['lotteryid']) : 1;
            $aLottery = $oLottery->getItem($iLotteryId );
            if( empty($aLottery) )
            {
                sysMessage('游戏不存在', 1, $aLocation);
            }
            $aHour = range(0,23);
            $aMinuteOrSecond = range(0,59);
            for ($i = 0; $i<10; $i++)
            {
                $aHour[$i] = '0'.$aHour[$i];
                $aMinuteOrSecond[$i] = '0'.$aMinuteOrSecond[$i];
            }
            $GLOBALS['oView']->assign( "ahour", $aHour );
            $GLOBALS['oView']->assign( "aminuteorsecond", $aMinuteOrSecond );
            $GLOBALS["oView"]->assign( "aLottery", $aLottery );
            $GLOBALS["oView"]->assign( "ur_here", "增加奖期时间规则" );
            $GLOBALS['oView']->assign( "lotteryid", $iLotteryId);
            $GLOBALS['oView']->assign( "action", 'addissueset');
            $GLOBALS['oView']->display( "gameinfo_issuesetinfo.html" );
            EXIT;
        }       
    }



    /**
     * 点差的设置
     * URL = ./index.php?controller=gameinfo&action=pointset
     * @author mark
     * Tom 效验通过于 0222 16:45
     */
    public function actionPointSet()
    {
        $aLocation[0] = array( 'text'=>'游戏信息管理', 'href'=>url('gameinfo','list') );
        if( isset($_POST) && !empty($_POST) )
        {
            $aLottery = array();
            $aLottery['minprofit']        = isset($_POST['minprofit']) ? floatval($_POST['minprofit']) : '';
            $aLottery['mincommissiongap'] = isset($_POST['mincommissiongap']) ? floatval($_POST['mincommissiongap']) : '';
            $aLottery['lotteryid']        = isset($_POST['lotteryid']) && is_numeric($_POST['lotteryid']) ? intval($_POST['lotteryid']) : 0;
            $iLottery = isset($aLottery['lotteryid']) && is_numeric($aLottery['lotteryid']) ? intval($aLottery['lotteryid']) : 0;
            if( $iLottery <= 0 || $aLottery['minprofit'] == '' || $aLottery['mincommissiongap'] == '' )
            {
                sysMessage( '操作失败:数据错误--公司留水、最小点差、彩种ID不能为空.', 1, $aLocation );
            }
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $iResult = $oLottery->lotteryUpdate( $aLottery, "`lotteryid`='" . $iLottery . "'" );
            if( $iResult > 0 )
            {
                sysMessage( '操作成功.', 0, $aLocation );
            }
            elseif( $iResult == 0 )
            {
                sysMessage( '操作失败:没有数据更新', 1, $aLocation );
            }
            else
            {
                sysMessage( '操作失败:参数不正确', 1, $aLocation );
            }
        }
        else
        {
            $iLotteryId = isset($_GET["id"]) && is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
            if( $iLotteryId == 0 )
            {
                sysMessage( '操作失败:数据错误.', 1, $aLocation );
            }
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $aLottery = $oLottery->getItem($iLotteryId );
            $GLOBALS['oView']->assign( "lottery", $aLottery );
            $GLOBALS['oView']->assign( "actionlink", $aLocation[0] );
            $GLOBALS['oView']->assign( "ur_here", "点差管理" );
            $oLottery->assignSysInfo();
            $GLOBALS['oView']->display("gameinfo_pointset.html");
            EXIT;
        }
    }



    /**
     * 休市时间管理
     * @author mark
     * URL: ./index.php?controller=gameinfo&action=sleepdate
     * Tom 效验通过于 0224 14:58
     */
    function actionSleepdate()
    {
        $aLocation[0] = array( 'text'=>'游戏信息管理', 'href'=>url('gameinfo','list') );
        if( isset($_POST) && !empty($_POST) )
        {
            $aLottery = array();
            $aLottery['yearlybreakstart'] = isset($_POST['yearlybreakstart']) ? $_POST['yearlybreakstart'] : '';
            $aLottery['yearlybreakend']   = isset($_POST['yearlybreakend']) ? $_POST['yearlybreakend'] : '';
            $aLottery['lotteryid']        = isset($_POST['lotteryid']) && is_numeric($_POST['lotteryid']) ? intval($_POST['lotteryid']) : 0;
            $iLottery = isset($aLottery['lotteryid']) && is_numeric($aLottery['lotteryid']) ? intval($aLottery['lotteryid']) : 0;
            if( $iLottery <= 0 || $aLottery['yearlybreakstart'] == '' || $aLottery['yearlybreakend'] == '' )
            {
                sysMessage( '操作失败:数据错误--休市时间和彩种ID不能为空.', 1, $aLocation );
            }
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $iResult = $oLottery->lotteryUpdate( $aLottery, "`lotteryid`='".$iLottery."'" );
            if( $iResult > 0 )
            {
                sysMessage( '操作成功.', 0, $aLocation );
            }
            elseif ( $iResult == 0 )
            {
                sysMessage( '操作失败:没有数据更新', 1, $aLocation );
            }
            else
            {
                sysMessage( '操作失败:参数不正确', 1, $aLocation );
            }
        }
        else
        {
            $iLotteryId = isset($_GET["id"]) && is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
            if( $iLotteryId == 0 )
            {
                sysMessage( '操作失败:数据错误.', 1, $aLocation);
            }
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $aLottery = $oLottery->getItem($iLotteryId );
            $GLOBALS['oView']->assign( "lottery", $aLottery );
            $GLOBALS['oView']->assign( "actionlink", $aLocation[0] );
            $GLOBALS['oView']->assign( "ur_here", "年休市时段管理" );
            $oLottery->assignSysInfo();
            $GLOBALS['oView']->display("gameinfo_sleepdate.html");
            EXIT;
        }
    }



    /**
     * 停止销售游戏
     * @author mark
     * URL: ./index.php?controller=gameinfo&action=gamestop
     * Tom 效验通过于 0222 17:15
     */
    function actionGameStop()
    {
        $aLocation[0] = array( 'text'=>'游戏信息列表', 'href'=>url('gameinfo','list') );
        $iLotteryId   = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iLotteryId == 0 )
        {
            sysMessage( '操作失败:没有指定游戏', 1, $aLocation );
        }
        /* @var $oLottery model_lottery */
        $oLottery = A::singleton("model_lottery");
        $iResult = $oLottery->setStatus( $iLotteryId, 1 );
        if( $iResult > 0)
        {
            sysMessage('操作成功', 0, $aLocation );
        }
        elseif( $iResult == 0 )
        {
            sysMessage('操作失败：没有数据更新，检查游戏玩法是否存在.', 1, $aLocation );
        }
        else
        {
            sysMessage('操作失败：数据更新失败.', 1, $aLocation );
        }
    }



    /**
     * 开始销售游戏
     * @author mark
     * URL: ./index.php?controller=gameinfo&action=gamestart
     * Tom 效验通过于 0222 17:15
     */
    function actionGameStart()
    {
        $aLocation[0] = array( 'text'=>'游戏信息列表', 'href'=>url('gameinfo','list') );
        $iLotteryId   = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iLotteryId == 0 )
        {
            sysMessage( '操作失败:没有指定游戏.', 1, $aLocation );
        }
        /* @var $oLottery model_lottery */
        $oLottery = A::singleton("model_lottery");
        $iResult  = $oLottery->setStatus( $iLotteryId, 0 );
        if( $iResult > 0)
        {
            sysMessage('操作成功', 0, $aLocation );
        }
        elseif( $iResult == 0 )
        {
            sysMessage('操作失败：没有数据更新，检查游戏玩法是否存在.', 1, $aLocation );
        }
        else
        {
            sysMessage('操作失败：数据更新失败.', 1, $aLocation );
        }
    }
    


    /**
     * 奖金组列表
     * @author mark
     * URL: ./index.php?controller=gameinfo&action=prizegroup
     * Tom 效验通过于 0225 14:19
     */
    function actionPrizegroup()
    {
        $aLocation[0] = array( "text"=>'游戏信息列表', "href"=>url('gameinfo','list') );
        $iLotteryId   = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iLotteryId==0 )
        {
            sysMessage("操作失败:数据错误.", 1, $aLocation);
        }
        $iPage        = isset($_GET["p"])&&is_numeric($_GET["p"]) ? intval($_GET["p"]) : 1;
        $aLocation[1] = array( "text"=>'增加奖金组信息', 
                            "href"=>url('gameinfo',"prizegroupadd",array('id'=>$iLotteryId)) );
        /* @var $oPrizeGroup model_prizegroup */
        $oPrizeGroup  = A::singleton("model_prizegroup");
        $aPrizeGroup  = $oPrizeGroup->pgGetList( '', "`lotteryid`='".$iLotteryId."'", '', 20, $iPage );
        $GLOBALS["oView"]->assign( "aPrizeGroup", $aPrizeGroup['results'] );
        $oPage        = new pages( $aPrizeGroup['affects'], 20 );
        $GLOBALS['oView']->assign( "pageinfo",  $oPage->show(1) );
        $GLOBALS["oView"]->assign( "ur_here",      "奖金组信息" );
        $GLOBALS["oView"]->assign( "actionlink",  $aLocation[0] );
        $GLOBALS['oView']->assign( "actionlink2", $aLocation[1] );
        $GLOBALS['oView']->assign( "lotteryid", $iLotteryId );
        $oPrizeGroup->assignSysInfo();
        $GLOBALS["oView"]->display("gameinfo_prizegroup.html");
        EXIT;
    }



    /**
     * 增加奖金组
     * @author mark
     * URL: ./index.php?controller=gameinfo&action=prizegroupadd
     * Tom 效验通过于 0225 15:10
     */
    function actionPrizegroupadd()
    {
    	// 增加奖金组提交处理
        if( isset($_POST)&&!empty($_POST) )
        {
        	/**
        	 * $_POST = 
        	 *    methodid  => array( 0=>1, 1=>4, 2=>7 .. )        选中并希望启用的的玩法ID
        	 *    prize     => array( 0=>array(1=>1700), 1=>array(1=>280,2=>570) .. ) 奖金情况
        	 *    userpoint => array( 1=>0.1, 2=>0.1, 3=>0.1 .. )  总代返点
        	 *    groupname => 奖金组名称
        	 *    lotteryid => 彩种D
        	 */
            $aPrizeGroup  = array();
            $aPrizeGroup['methodid']  = isset($_POST['methodid']) && is_array($_POST['methodid']) ? $_POST['methodid'] : '';
            $aPrizeGroup['prize']     = isset($_POST['prize']) && is_array($_POST['prize']) ? $_POST['prize'] : '';
            $aPrizeGroup['userpoint'] = isset($_POST['userpoint']) && is_array($_POST['userpoint']) ? $_POST['userpoint'] : '';
            $aPrizeGroup['groupname'] = isset($_POST['groupname']) ? daddslashes($_POST['groupname']) : '';
            $aPrizeGroup['lotteryid'] = isset($_POST['lotteryid']) ? intval($_POST['lotteryid']) : 0;
            /* @var $oPrizeGroup model_prizegroup */
            $oPrizeGroup  = A::singleton("model_prizegroup");
            $iResult      = $oPrizeGroup->pgInsert( $aPrizeGroup );
            $aLocation[0] = array( "text"=>"奖组管理",
                            "href"=>url("gameinfo","prizegroup",array("id"=>$aPrizeGroup["lotteryid"])) );
            switch( $iResult )
            {
                case 0:
                    sysMessage( '操作失败:数据不完整.', 1, $aLocation );
                    BREAK;
                case -1:
                    sysMessage( '操作失败:彩种ID错误.', 1, $aLocation );
                    BREAK;
                case -2:
                    sysMessage( '操作失败:彩种不存在.', 1, $aLocation );
                    BREAK;
                case -3:
                    sysMessage( '操作失败:彩种名称不存在.', 1, $aLocation );
                    BREAK;
                case -4:
                    sysMessage( '操作失败:奖金错误.', 1, $aLocation );
                    BREAK;
                case -5:
                    sysMessage( '操作失败:返点设置错误.', 1, $aLocation );
                    BREAK;
                case -6:
                    sysMessage( '操作失败:公司最小留水计算错误.', 1, $aLocation );
                    BREAK;
                case -7:
                    sysMessage( '操作失败:数据提交失败.', 1, $aLocation );
                    BREAK;
                case -8:
                    sysMessage( '操作失败:数据提交失败.', 1, $aLocation );
                    BREAK;
                default:
                    sysMessage( '操作成功', 0, $aLocation );
                    BREAK;
            }
        }
        else
        {
        	// 显示 "增加奖金组" 界面
            $aLocation[0] = array("text"=>'游戏信息列表','href'=>url('gameinfo','list') );
            $iLotteryId   = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
            if( $iLotteryId == 0 )
            {
                sysMessage("操作失败:数据不正确.", 1, $aLocation );
            }
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $aLottery = $oLottery->getItem($iLotteryId);
            $GLOBALS['oView']->assign( "alottery", $aLottery );
            /* @var $oMethod model_method */
            $oMethod = A::singleton("model_method");
            $sFiled = "M.`methodid`,M.`lotteryid`,M.`methodname`,M.`level`,"
                    ."M.`nocount`,M.`totalmoney`,L.`cnname`,MC.`crowdname`,MC.`crowdid`";
            $sCondition =  "M.`lotteryid`='".$iLotteryId."' and M.`pid`='0'";
            $aMethod = $oMethod->methodGetListByCrowd( $sFiled, $sCondition );
            $aLocation[1] = array( "text"=>"增加游戏奖金组",
                    'href'=>url("gameinfo","prizegroupadd",array( "id"=>$aLottery["lotteryid"] )) );
            $GLOBALS['oView']->assign( "amethod",  $aMethod );
            $GLOBALS['oView']->assign( "crowdCount",  count($aMethod));
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
     * @author mark
     * URL: ./index.php?controller=gameinfo&action=prizegroupedit
     * Tom 效验通过于 0225 14:51
     */
    function actionPrizegroupedit()
    {
        if( isset($_POST)&&!empty($_POST) )
        {
            $aLocation[0] = array("text"=>'奖金组信息列表',
                    "href"=>url('gameinfo','prizegroup',array('id' => intval($_POST["lotteryid"]))));
            $aPrizeGroup  = array();
            $aPrizeGroup['methodid']  = isset($_POST['methodid']) && is_array($_POST['methodid']) ? $_POST['methodid'] : '';
            $aPrizeGroup['prize']     = isset($_POST['prize']) && is_array($_POST['prize']) ? $_POST['prize'] : '';
            $aPrizeGroup['userpoint'] = isset($_POST['userpoint']) && is_array($_POST['userpoint']) ? $_POST['userpoint'] : '';
            $aPrizeGroup['groupname'] = isset($_POST['groupname']) ? daddslashes($_POST['groupname']) : '';
            $aPrizeGroup['lotteryid'] = isset($_POST['lotteryid']) ? intval($_POST['lotteryid']) : 0;
            $aPrizeGroup['pgid']      = isset($_POST['pgid']) ? intval($_POST['pgid']) : 0;
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
            $aLocation[0] = array("text"=>'奖金组信息列表',
                    "href"=>url('gameinfo','prizegroup',array('id' => intval($_GET["lotteryid"]))));
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
            $sFiled = "M.`methodid`,M.`lotteryid`,M.`methodname`,M.`level`,"
                    ."M.`nocount`,M.`totalmoney`,L.`cnname`,MC.`crowdname`,MC.`crowdid`";
            $sCondition =  "M.`lotteryid`='".$aPrizeGroup['lotteryid']."' and M.`pid`='0'";
            $aMethod = $oMethod->methodGetListByCrowd( $sFiled, $sCondition );
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
            $GLOBALS['oView']->assign( "crowdCount",  count($aMethod));
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
     * @author  mark
     * URL:./index.php?controller=gameinfo&action=assign
     * Tom 效验通过于 0225 14:51
     */
    function actionassign()
    {
        if( isset($_POST) && !empty($_POST) )
        {
        	/*
			 * $_POST = Array
		     * (
			 *    [user] => Array
			 *             [0] => 446
			 *             [1] => 200120
			 *             [2] => 200286
			 *    [prizegroupid] => 4
			 * )
        	 */
        	$aLocation[0] = array("text"=>'奖金组信息列表',
                    "href"=>url('gameinfo','prizegroup',array('id' => intval($_POST["lotteryid"]))));
            $iPrizeGroupId  = intval($_POST["prizegroupid"]);
            $aUser          = isset($_POST["user"]) ? $_POST["user"] : array();
            /* @var $oPrizeGroup model_prizegroup */
            $oPrizeGroup = A::singleton("model_prizegroup");
            $iResult = $oPrizeGroup->userAuth( $iPrizeGroupId, $aUser );
            switch( $iResult )
            {
                case -1:
                    sysMessage( '操作失败:数据不正确.', 1, $aLocation );
                    BREAK;
                case 0:
                    sysMessage( '操作失败: 没有数据更新.', 1,$aLocation );
                    BREAK;
                default:
                    sysMessage( '操作成功.', 0, $aLocation );
                    BREAK;
            }
        }
        else
        {
            $iPgId =isset($_GET["pgid"])&&is_numeric($_GET["pgid"])? intval($_GET["pgid"]) : 0;
            $aLocation[0] = array("text"=>'奖金组信息列表',
                    "href"=>url('gameinfo','prizegroup',array('id' => intval($_GET['lotteryid']))));
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
            $GLOBALS['oView']->assign( "lotteryid", intval($_GET['lotteryid']));
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display( "gameinfo_assign.html" );
            EXIT;
        }
    }



    /**
     * 对方案组进行审核
     * @author  mark
     * URL: ./index.php?controller=gameinfo&action=veerify
     */
    function actionVerify()
    {
        if( isset($_POST)&&!empty($_POST) )
        {
            $aLocation[0] = array("text"=>'奖金组信息列表',
                    "href"=>url('gameinfo','prizegroup',array('id' => intval($_POST["lotteryid"]))));
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
            $aLocation[0] = array("text"=>'奖金组信息列表',
                    "href"=>url('gameinfo','prizegroup',array('id' => intval($_GET["lotteryid"]))));
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
            $sFiled = "M.`methodid`,M.`lotteryid`,M.`methodname`,M.`level`,"
            ."M.`nocount`,M.`totalmoney`,L.`cnname`,MC.`crowdname`,MC.`crowdid`";
            $sCondition =  "M.`lotteryid`='".$aPrizeGroup['lotteryid']."' and M.`pid`='0'";
            $aMethod = $oMethod->methodGetListByCrowd( $sFiled, $sCondition );
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
            $GLOBALS['oView']->assign( "alottery", $aLottery );
            $GLOBALS['oView']->assign( "amethod", $aMethod );
            $GLOBALS['oView']->assign( "crowdCount",  count($aMethod));
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
            $GLOBALS['oView']->assign("ur_here", "奖金组审核");
            $GLOBALS['oView']->assign("lotteryid", intval($_GET["lotteryid"]));
            $GLOBALS['oView']->display("gameinfo_verify.html");
            EXIT;
        }
    }



    /**
     * 奖金组详情查看
     * @author mark
     * URL:./index.php?controller=gameinfo&action=prizelevel
     */
    function actionPrizelevel()
    {
    	$aLocation[0] = array("text"=>'奖金组信息列表',
                    "href"=>url('gameinfo','prizegroup',array('id' => intval($_GET["lotteryid"]))));
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
        $sFiled = "M.`methodid`,M.`lotteryid`,M.`methodname`,M.`level`,"
        ."M.`nocount`,M.`totalmoney`,L.`cnname`,MC.`crowdname`,MC.`crowdid`";
        $sCondition =  "M.`lotteryid`='".$aPrizeGroup['lotteryid']."' and M.`pid`='0'";
        $aMethod = $oMethod->methodGetListByCrowd( $sFiled, $sCondition );
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
        $GLOBALS['oView']->assign( "crowdCount",  count($aMethod));
        $GLOBALS['oView']->assign( "prizegroup", $aPrizeGroup );
        $GLOBALS['oView']->assign( "action",     "prizegroupedit" );
        $oLottery->assignSysInfo();
        $GLOBALS['oView']->assign( "ur_here", "奖金组详情 [ ".$aPrizeGroup["title"]." ]" );
        $GLOBALS['oView']->assign( "actionlink",  $aLocation[0] );
        $GLOBALS['oView']->display( "gameinfo_prizelevel.html" );
        EXIT;
    }
    
    
    /**
     * 停止总代奖金组
     * @author mark
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
     * @author mark
     * URL: ./index.php?controller=gameinfo&action=userpgedit
     */
    function actionUserpgedit()
    {
        $aLocation[0] = array( "text"=>'分配奖金组', "href"=>url('user','setprize') );
        if( isset($_POST)&&!empty($_POST) )
        {
            $aUserPrizeGroup  = array();
            $aUserPrizeGroup['methodid']    = isset($_POST['methodid']) && is_array($_POST['methodid']) ? $_POST['methodid'] : '';
            $aUserPrizeGroup['prize']       = isset($_POST['prize']) && is_array($_POST['prize']) ? $_POST['prize'] : '';
            $aUserPrizeGroup['userpoint']   = isset($_POST['userpoint']) && is_array($_POST['userpoint']) ? $_POST['userpoint'] : '';
            $aUserPrizeGroup['userpgid']    = isset($_POST['userpgid']) ? intval($_POST['userpgid']) : '';
            $aUserPrizeGroup['userid']      = isset($_POST['userid']) ? intval($_POST['userid']) : 0;
            $aUserPrizeGroup['pgid']        = isset($_POST['pgid']) ? intval($_POST['pgid']) : 0;
            $aUserPrizeGroup['lotteryid']   = isset($_POST['lotteryid']) ? intval($_POST['lotteryid']) : 0;
            $aUserPrizeGroup['title']       = isset($_POST['title']) ? daddslashes($_POST['title']) : 0;
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
            $sFiled = "M.`methodid`,M.`lotteryid`,M.`methodname`,M.`level`,"
            ."M.`nocount`,M.`totalmoney`,L.`cnname`,MC.`crowdname`,MC.`crowdid`";
            $sCondition =  "M.`lotteryid`='".$aUserGroup['lotteryid']."' and M.`pid`='0'";
            $aMethod = $oMethod->methodGetListByCrowd( $sFiled, $sCondition );//玩法组按法群分类
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $aLottery = $oLottery->getItem($aUserGroup["lotteryid"]);
            $GLOBALS['oView']->assign("minprofit",$aLottery["minprofit"]);//最小点差
            $GLOBALS['oView']->assign( "data_method",       json_encode($aMethod) );
            $GLOBALS['oView']->assign( "data_userprize",    json_encode($aUserPrizeLevel) );
            $GLOBALS['oView']->assign( "aUserGroup",        $aUserGroup );
            $GLOBALS['oView']->assign( "crowdCount",        count($aMethod));
            $GLOBALS["oView"]->assign( "ur_here",           "用户奖金组修改" );
            $GLOBALS['oView']->assign( "actionlink",        $aLocation[0] );
            $oMethod->assignSysInfo();
            $GLOBALS['oView']->display( "gameinfo_userprizeinfo.html" );
            EXIT;
        }
    }

    

    /**
     * 对总代奖金组进行查看
     * @author mark
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
		$aPrizeLevels = array();
        foreach( $aUserPrizeLevel as $prizelevel )
        {
            $iMethodid = $prizelevel["methodid"];
            $iLevel    = $prizelevel["level"];
            $aPrizeLevels["prize"][$iMethodid][$iLevel]         = $prizelevel["prize"];
            $aPrizeLevels["userpoint"][$iMethodid]              = $prizelevel["userpoint"];
        }
		/* @var $oMethod model_method */
		$oMethod = A::singleton("model_method");
		$sFiled = "M.`methodid`,M.`lotteryid`,M.`methodname`,M.`level`,"
        ."M.`nocount`,M.`totalmoney`,L.`cnname`,MC.`crowdname`,MC.`crowdid`";
        $sCondition =  "M.`lotteryid`='".$aUserGroup['lotteryid']."' and M.`pid`='0'";
        $aMethod = $oMethod->methodGetListByCrowd( $sFiled, $sCondition );
        foreach( $aMethod as &$aMethodDetail)
        {
            foreach($aMethodDetail['method'] as &$aTmpMethod)
            {
                //获取各个奖级的利润率及公司留水
                $fTotalValue = 0;
                foreach($aTmpMethod['nocount'] as $iLevel => &$aMethodCount)
                {
                    $aMethodCount['profit'] = ($aTmpMethod['totalmoney']-$aPrizeLevels['prize'][$aTmpMethod['methodid']][$iLevel]*$aMethodCount['count'])/$aTmpMethod['totalmoney'];
                    $aMethodCount['lastprofit'] = $aMethodCount['profit']-$aPrizeLevels['userpoint'][$aTmpMethod['methodid']];
                    $fTotalValue += $aPrizeLevels['prize'][$aTmpMethod['methodid']][$iLevel]*$aMethodCount['count'];
                }
                if($aTmpMethod['type'] == 3)
                {
                    //总利润及公司留水:利润率累加型的多奖级
                    $aTmpMethod['totalprofit'] = ($aTmpMethod['totalmoney']-$fTotalValue)/$aTmpMethod['totalmoney'];
                    $aTmpMethod['lasttotalprofit'] = $aTmpMethod['totalprofit']-$aPrizeLevels['userpoint'][$aTmpMethod['methodid']];
                }
            }
        }
		$GLOBALS['oView']->assign( "amethod",           $aMethod );
		$GLOBALS['oView']->assign( "prizelevel",        $aPrizeLevels );
		$GLOBALS['oView']->assign( "aUserGroup",        $aUserGroup );
		$GLOBALS['oView']->assign( "crowdCount",        count($aMethod));
		$GLOBALS["oView"]->assign( "ur_here",           "用户奖金组查看" );
		$GLOBALS['oView']->assign( "actionlink",        $aLocation[0] );
		$oMethod->assignSysInfo();
		$GLOBALS['oView']->display( "gameinfo_userprizeinfoview.html" );
		EXIT;
    }


    /**
     * 启用用户奖金组
     * @author mark
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
                    sysMessage( '操作失败:更新数据失败', 1, $aLocation );
                    break;
                case 0:
                    sysMessage( '操作失败:没有数据更新', 1, $aLocation );
                    break;
                default:
                    sysMessage( '操作成功',0, $aLocation );
                    break;
            }
        }
        EXIT;
    }
    
}
?>
