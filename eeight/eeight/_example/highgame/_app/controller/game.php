<?php
/**
 * 文件 : /_app/controller/game.php
 * 功能 : 控制器 - 游戏平台玩游戏投注等相关操作
 *  
 *  - actionPlay()          参与游戏(用户购彩)
 *  - actionSpecial()       靓号区(靓号区购买)
 *  - actionCodeDynamic()   查看动态调价当前价格
 *  - actionBonusCode()     查看历史中奖号码
 *  - actionMissCode()     查看遗漏号码
 *  - actionHotCode()     查看冷热号码
 * 
 * @author     james    090914
 * @version    1.0.0
 * @package    highgame   
 */

class controller_game extends basecontroller
{
    private $aCodeGroup = array(
       5=>array("万","千","百","十","个"),
       3=>array("百","十","个"),
	   20=>array("第一区（01-16）","第二区（17-32）","第三区（33-48）","第四区（49-65）","第五区（65-80）"),
    );
    /**
	 * 用户投注
	 */
	function actionPlay()
	{
		$iUserId = intval($_SESSION['userid']);
		if( empty($_POST['flag']) || 
		    ( $_POST['flag']!= 'save' && $_POST['flag']!= 'read' && $_POST['flag'] != 'getlotterycode'
		      && $_POST['flag']!= 'gethistory' && $_POST['flag']!= 'getprojects' && $_POST['flag'] != 'gettime' ) )
		{
			$aLocation[0] = array( "title"=>'系统公告',"url"=>url('default', 'start') );
	        $iCurrentMenuId = 0;	//默认当前菜单为0
		    if( empty($_REQUEST['curmid']) || !is_numeric($_REQUEST['curmid']) || intval($_REQUEST['curmid']) <= 0 )
            {
            	//当前菜单ID
                sysMsg( "操作错误", 2 );
            }
            $iCurrentMenuId = intval($_REQUEST['curmid']);
            //01:获取当前菜单所对应的菜单信息[所对应的彩种ID]
            $oUserMenu  = new model_usermenu();
            $aCurrentMenu = $oUserMenu->userMenu($iCurrentMenuId," lotteryid ");
            if( empty($aCurrentMenu) )
            {
                sysMsg( "数据错误", 2 );
            }
            $iLotteryId = $aCurrentMenu['lotteryid'];
            $oLottery = new model_lottery();
			$aL = $oLottery->lotteryGetOne( 'lotterytype', 'lotteryid='.$iLotteryId );
            unset($aCurrentMenu);
            //02:获取当前标签的所有子标签
            $sFields    = "um.`menuid`,um.`title`, mc.`crowdname`, mc.`crowdid`";
            $sCondition = " AND um.`isdisabled`=0 ";
            $aMenuTopData  = $oUserMenu->userMenuChild( $iCurrentMenuId, FALSE, $sFields, $sCondition );

            if( empty($aMenuTopData) )
            {
                sysMsg( "没有权限001", 2, $aLocation );
            }
            $aTempArr   = $aTempArrCrowd = array();
            foreach( $aMenuTopData as $m )
            {
                $aTempArr[] = $m['menuid'];

				$aTempArrCrowd[$m['crowdid']]['name'] = $m['crowdname'];
				$aTempArrCrowd[$m['crowdid']]['crowdid'] = $m['crowdid'];
				$aTempArrCrowd[$m['crowdid']]['group'][] = array('menuid'=>$m['menuid'], 'title'=>$m['title']);
            }
            $sMenuIds = implode( ",", $aTempArr );
            //03:获取当前彩种的奖期信息
            $oIssue        = new model_issueinfo();
            $aCurrentIssue = $oIssue->getCurrentIssue( $iLotteryId );
		    if( empty($aCurrentIssue) )
            {
                sysMsg( "未到销售时间", 2, $aLocation );
            }
            //04:获取用户所有可以玩的玩法
            $oUserMethod = new model_usermethodset();
            $sFields     = " m.`methodid`,upl.`level`,upl.`prize` ";
            $sCondition  = " AND m.`lotteryid`='".$iLotteryId."' ";
            $aMethodGroup= $oUserMethod->getUserMethodPrize( $iUserId, $sFields, $sCondition, FALSE );
            if( empty($aMethodGroup) )
            {
            	//没有可以玩的玩法组
                sysMsg( "没有权限002", 2, $aLocation );
            }
            $aTempArr = array();
            $aTempArr2= array();
            foreach( $aMethodGroup as $v )
            {
                $aTempArr[] = $v['methodid'];
                $aTempArr2[$v['methodid']][$v['level']] = $v['prize'];
                //$aTempArr2[$v['methodid']][] = $v['level'].":'".$v['prize']."'";
            }
            $aMethodGroup = $aTempArr2;
            //print_rr($aMethodGroup);
            /*foreach( $aMethodGroup as $k=>$v )
            {
                $aMethodGroup[$k] = '{'.implode(',',$v).'}';
            }*/
            unset($aTempArr2);
            //05:获取所有有效标签
            $sFields = 'um.`parentid`,um.`title`,um.`description`,um.`methodid`,um.`faceparameter`,m.`pid`,m.`modes`,m.`nocount`,m.`maxcodecount`';
            $sCondition = " AND m.`isclose`=0 AND um.`isdisabled`=0 AND m.`pid` IN(".implode(",",$aTempArr).") ".
                          " AND um.`parentid` IN(".$sMenuIds.") ORDER BY um.`sort` ";
            $sLeft  = " LEFT JOIN `method` AS m ON m.`methodid`=um.`methodid` ";
            $aMenuChildData = $oUserMenu->menuList( $sFields, $sCondition, $sLeft );
            if( empty($aMenuChildData) )
            {
                sysMsg( "数据错误", 2, $aLocation );
            }
            $aMenuData = array();
            $aTempArr  = array();
            foreach( $aMenuChildData as $v )
            {
                $v['faceparameter'] = base64_decode($v['faceparameter']);
                $v['nocount'] = unserialize($v['nocount']);
                $aTmpB = array();
                foreach( $v['nocount'] as $kk=>$vv )
                {
                    if( isset($vv['use']) && $vv['use'] == 1 )
                    {
                    	//使用了该奖级
                        $aTmpB[] = intval($kk);
                    }
                }
                $v['nocount'] = array();
                foreach( $aMethodGroup[$v['pid']] as $kk=>$vv )
                {
                    if( in_array($kk, $aTmpB) )
                    {
                        $v['nocount'][] = $kk.":'".$vv."'";
                    }
                }
                $aTmp  = explode( ",", $v['modes'] );
                $aTmpB = array();
                foreach( $aTmp as $tt )
                {
                    if( isset($GLOBALS['config']['modes'][$tt]) )
                    {
                        $aTmpB[] = "{modeid:".$tt.",name:'".$GLOBALS['config']['modes'][$tt]['name'].
                                 "',rate:".$GLOBALS['config']['modes'][$tt]['rate']."}";
                    }
                }
                $aTempArr[$v['parentid']][] = "{".$v['faceparameter']."
                                                  methodid : ".$v['methodid'].",
                                                  name:'".$v['title']."',
                                                  prize:{".implode(',',$v['nocount'])."},
                                                  modes:[".implode(",",$aTmpB)."],
                                                  desc:'".$v['description']."',maxcodecount:".$v['maxcodecount']."
                                                }";
            }
            //print_rr($aTempArr);
            unset($aMenuChildData);
			if( $aL['lotterytype'] == 0 )
			{
				foreach( $aTempArrCrowd as $cid => $c )
				{
					$aGtemp = array();
					$sCtemp = '';
					$sCtemp .= '{title:"'.$c['name'].'", label:[';
					foreach($c['group'] AS $g)
					{
						//$aMenuData[$cid] .= '{gtitle:"'.$g['title'].'"';
						if( !empty($aTempArr[$g['menuid']]) )
						{
							$aGtemp[] = "{gtitle:'".$g['title']."', label:[".implode(",",$aTempArr[$g['menuid']])."]}";
						}
					}
					if($aGtemp)
					{
						$sCtemp .= implode(',',$aGtemp);
						$sCtemp .= ']}';
						$aMenuData[] = $sCtemp;
					}
				}
			}
			else
			{
			//var_dump($aMenuData);die;
				foreach( $aMenuTopData as $v )
				{
					if( !empty($aTempArr[$v['menuid']]) )
					{
						$aMenuData[] = "{title:'".$v['title']."',label:[".implode(",",$aTempArr[$v['menuid']])."]}";
					}
				}
			}
            unset($aMenuTopData, $aTempArrCrowd);
            if( empty($aMenuData) )
            {
                sysMsg( "没有权限003", 2, $aLocation );
            }
            //11:获取追号期
            $aTaskIssue = $oIssue->getTaskIssue( $iLotteryId, " `issue`,`saleend` " );
            if( empty($aTaskIssue['today']) || empty($aTaskIssue['tomorrow']) )
            {
                sysMsg( "数据错误", 2, $aLocation );
            }
            //12:获取历史开奖号码
            $oGameManage = new model_gamemanage( $GLOBALS['aSysDbServer']['report'] );
            $aHistory    = $oGameManage->getHistoryCodeFile( $iLotteryId, 15, 600 );
            //13:获取最近参与游戏记录10条[从DB]
            $oProject    = new model_projects( $GLOBALS['aSysDbServer']['report'] );
            $sFields     = " P.`projectid`,P.`writetime`,P.`code`,P.`multiple`,P.`totalprice`,P.`taskid`,P.`methodid`,".
                           " M.`methodname`,I.`issue`,P.`codetype`,P.`modes` ";
            $sCondition  = " AND L.`lotteryid`='".$iLotteryId."' ";
            $sOrderBY    = " P.`projectid` DESC LIMIT 0,10";
            $aProjects   = $oProject->projectGetResult( $iUserId, FALSE, $sFields, $sCondition, $sOrderBY, 0 );
            foreach( $aProjects as $k=>$v )
            {
                //13 01:加密
                $aProjects[$k]['projectid'] = model_projects::HighEnCode("D".$v["issue"]."-".$v["projectid"],"ENCODE");
                //反转义code
                $aProjects[$k]['code'] = model_projects::AddslasCode( $v['code'], $v['methodid'] );
                if( $v['codetype'] == 'input' && !strpos($v['methodname'], '混合') )
                {
                    $aProjects[$k]['methodname'] .= ' (单式)';
                }
                //模式
                $aProjects[$k]['modes'] = $GLOBALS['config']['modes'][$v['modes']]['name'];
            }
            //14:获取用户开通的彩种
            $aLottery = $oLottery->getLotteryByUser( $iUserId );
            $iNowTime = time();
            foreach( $aLottery as $k=>$v )
            {
                $aTempArr = $oGameManage->getHistoryCodeFile( $v['lotteryid'], 1 );
                if( !empty($aTempArr) )
                {
                    $aLottery[$k]['issue'] = $aTempArr[0]['issue'];
                    $aLottery[$k]['code']  = empty($aTempArr[0]['code']) ? "未开奖" : $aTempArr[0]['code'];
                }
                else
                {
                    $aLottery[$k]['issue'] = "未开始";
                    $aLottery[$k]['code']  = "未开始";
                }
                //获取最近没开奖一期的结束时间
                $sFields    = " A.`saleend` ";
                $sCondition = " A.`lotteryid`='".$v['lotteryid']."' AND A.`statuscode`<2 ORDER BY A.`saleend` ASC ";
                $aTempArr   = $oIssue->IssueGetOne( $sFields, $sCondition );
                if( empty($aTempArr) )
                {//如果没有则不进行再次获取
                    $aLottery[$k]['timeout'] = -1;
                }
                else
                {//计算下一次请求时间
                    $iTempInt = strtotime($aTempArr['saleend']) - $iNowTime;
                    if( $iTempInt > 0 )
                    {//如果最近没有开奖的一期还没有结束，则在结束时间后再等待2分钟再请求
                        $aLottery[$k]['timeout'] = intval($iTempInt)*1000+120000 + mt_rand(-30,30) * 1000;
                    }
                    else 
                    {//如果已经结束，则等待2分钟后再请求
                        $aLottery[$k]['timeout'] = 120000 + mt_rand(-30,30) * 1000;
                    }
                }
            }
            $GLOBALS['oView']->assign( "aCurrentIssue", $aCurrentIssue );
            $GLOBALS['oView']->assign( "aTaskIssue", $aTaskIssue );
            $GLOBALS['oView']->assign( "aMenuData", $aMenuData );
            $GLOBALS['oView']->assign( "sNowTime", date("Y-m-d H:i:s") );
            $GLOBALS['oView']->assign( "iLotteryId", $iLotteryId );
            $GLOBALS['oView']->assign( "curmid", $iCurrentMenuId );
            $GLOBALS['oView']->assign( "ahistory", $aHistory );
            $GLOBALS['oView']->assign( "aProjects", $aProjects );
            $GLOBALS['oView']->assign( "aLottery", $aLottery );
            $GLOBALS['oView']->assign( "sys_header_title", TRUE );
            //jack
            $GLOBALS['oView']->assign( "ur_here", "参与游戏" );
			//var_dump($aLottery);die;
            $oUserMenu->assignSysInfo();
            $GLOBALS['oView']->display( 'game_play_type_'.$aL['lotterytype'].'.html' );
            exit;
		}
		elseif( $_POST['flag'] == 'read' )
		{//获取当前期[时间自动过了以后读取下一期数据]
		    if( empty($_POST['lotteryid']) || !is_numeric($_POST['lotteryid']) || intval($_POST['lotteryid']) <= 0 )
            {//彩种ID
                die("empty");
            }
            $iLotteryId  = intval($_POST['lotteryid']);
            $oIssue      = new model_issueinfo();
            //获取当前期数据
		    $aMethodData = $oIssue->getCurrentIssue( $iLotteryId );
            if( empty($aMethodData) )
            {
                die("empty");
            }
            $sStr = "{issue:'".$aMethodData['issue']."',nowtime:'".date("Y-m-d H:i:s")."',saleend:'".
                    $aMethodData['saleend']."'}";
            echo $sStr;
            exit;
		}
		elseif( $_POST['flag'] == 'gethistory' )
		{//获取历史开奖号码[1条]
		    if( empty($_POST['lotteryid']) || !is_numeric($_POST['lotteryid']) || intval($_POST['lotteryid']) <= 0 )
            {//彩种ID
                die("empty");
            }
            $iLotteryId  = intval($_POST['lotteryid']);
            $oGameManage = new model_gamemanage($GLOBALS['aSysDbServer']['report']);
            $aHistory    = $oGameManage->getHistoryCodeFile( $iLotteryId, 1, 300 );
            if( empty($aHistory) )
            {
                die("empty");
            }
            echo json_encode($aHistory);
            exit;
		}
		elseif( $_POST['flag'] == 'getprojects' )
		{//获取最近的投注记录[10条]
		    if( empty($_POST['lotteryid']) || !is_numeric($_POST['lotteryid']) || intval($_POST['lotteryid']) <= 0 )
            {//彩种ID
                die("empty");
            }
            $iLotteryId  = intval($_POST['lotteryid']);
		    $oProject    = new model_projects( $GLOBALS['aSysDbServer']['report'] );
            $sFields     = " P.`projectid`,P.`writetime`,P.`code`,P.`multiple`,P.`totalprice`,P.`taskid`,P.`methodid`,".
                           " M.`methodname`,I.`issue`,P.`codetype`,P.`modes` ";
            $sCondition  = " AND L.`lotteryid`='".$iLotteryId."' ";
            $sOrderBY    = " P.`projectid` DESC LIMIT 0,10";
            $aProjects   = $oProject->projectGetResult( $iUserId, FALSE, $sFields, $sCondition, $sOrderBY, 0 );
            foreach( $aProjects as $k=>$v )
            {
                //13 01:加密
                $aProjects[$k]['projectid'] = model_projects::HighEnCode("D".$v["issue"]."-".$v["projectid"],"ENCODE");
                //反转义code
                $aProjects[$k]['code'] = model_projects::AddslasCode( $v['code'], $v['methodid'] );
                if( $v['codetype'] == 'input' && !strpos($v['methodname'], '混合') )
                {
                    $aProjects[$k]['methodname'] .= ' (单式)';
                }
                $aProjects[$k]['modes'] = $GLOBALS['config']['modes'][$v['modes']]['name'];
            }
            if( empty($aProjects) )
            {
                die("empty");
            }
            echo json_encode($aProjects);
            exit;
		}
		elseif( $_POST['flag'] == 'getlotterycode' )
		{//获取彩种的最近一期开奖号码
		    if( empty($_POST['lotteryid']) || !is_numeric($_POST['lotteryid']) || intval($_POST['lotteryid']) <= 0 )
            {//彩种ID
                die("empty");
            }
            $iLotteryId  = intval($_POST['lotteryid']);
            $oGameManage = new model_gamemanage($GLOBALS['aSysDbServer']['report']);
		    $oIssue      = new model_issueinfo($GLOBALS['aSysDbServer']['report']);
		    $aLottery    = $oGameManage->getHistoryCodeFile( $iLotteryId, 1 );
            if( empty($aLottery) )
            {
                die("empty");
            }
            $aLottery = $aLottery[0];
            $aLottery['code']  = empty($aLottery['code']) ? "未开奖" : $aLottery['code'];
            //获取最近没开奖一期的结束时间
            $sFields    = " A.`saleend`,A.`issue` ";
            $sCondition = " A.`lotteryid`='".$iLotteryId."' AND A.`statuscode`<2 ORDER BY A.`saleend` ASC ";
            $aTempArr   = $oIssue->IssueGetOne( $sFields, $sCondition );
            if( empty($aTempArr) )
            {//如果没有则不进行再次获取
                $aLottery['timeout'] = -1;
            }
            else
            {//计算下一次请求时间
                $aLottery['nextissue'] = $aTempArr['issue'];
                $iTempInt = strtotime($aTempArr['saleend']) - time();
                if( $iTempInt > 0 )
                {//如果最近没有开奖的一期还没有结束，则在结束时间后再等待2分钟再请求
                    $aLottery['timeout'] = intval($iTempInt)*1000+120000 + mt_rand(-30,30) * 1000;
                }
                else 
                {//如果已经结束，则等待2分钟后再请求
                    $aLottery['timeout'] = 120000 + mt_rand(-30,30) * 1000;
                }
            }
            echo json_encode($aLottery);
            exit;
		}
		elseif( $_POST['flag'] == 'gettime' )
		{
		    //01：数据完整性检测
            if( empty($_POST['lotteryid']) || !is_numeric($_POST['lotteryid']) || $_POST['lotteryid'] <= 0 )
            {
            	//彩种
                die(ajaxMsg("error", "操作错误"));
            }
            $iLotteryId = intval($_POST['lotteryid']);
            if( empty($_POST['issue']) )
            {
                die(ajaxMsg("error", "操作错误"));
            }
            $sIssue = daddslashes($_POST['issue']);
            $oIssue = new model_issueinfo();
            //获取当前期数据
            $aIssue = $oIssue->getItem( 0, $sIssue, $iLotteryId );
		    if( empty($aIssue) )
            {
                die("empty");
            }
            $iLeftTime = ceil(strtotime($aIssue['saleend']) - time());
            $iLeftTime = $iLeftTime <= 0 ? 0 : $iLeftTime;
            die($iLeftTime);
            
		}
		elseif( $_POST['flag'] == 'save' )
		{
			//01：数据完整性检测
			if( empty($_POST['lotteryid']) || !is_numeric($_POST['lotteryid']) || $_POST['lotteryid'] <= 0 )
			{//彩种
				die(ajaxMsg("error", "操作错误"));
			}
			$iLotteryId = intval($_POST['lotteryid']);
			$aData     = array(); //投注数据
			if( empty($_POST['lt_issue_start']) )
			{//购买的起始期号
			    die(ajaxMsg("error", "操作错误"));
			}
			$aData['sIssue'] = $_POST['lt_issue_start'];
			if( empty($_POST['lt_total_nums']) || !is_numeric($_POST['lt_total_nums']) || $_POST['lt_total_nums'] <=0 )
			{//购买的总注数
			    die(ajaxMsg("error", "请选择投注内容"));
			}
			$aData['iTotalNum'] = intval($_POST['lt_total_nums']);
			if( empty($_POST['lt_total_money']) || !is_numeric($_POST['lt_total_money']) || $_POST['lt_total_money'] <=0 )
			{//投注总金额
			    die(ajaxMsg("error", "请选择投注内容"));
			}
			$aData['iTotalAmount'] = floatval($_POST['lt_total_money']);
			if( empty($_POST['lt_project']) || !is_array($_POST['lt_project']) )
			{//投注内容
			    die(ajaxMsg("error", "请选择投注内容"));
			}
			$aData['aProject'] = stripslashes_deep($_POST['lt_project']);
			foreach( $aData['aProject'] as & $p )
			{
			    $p = json_decode(preg_replace("/'/",'"',$p), TRUE);
			    if( empty($p) || !is_array($p) )
			    {
			        die(ajaxMsg("error", "请选择投注内容"));
			    }
			}
			$aData['bIsTrace'] = FALSE;
			if( !empty($_POST['lt_trace_if']) && $_POST['lt_trace_if'] == "yes" )
			{//如果是追号[获取一些追号数据]
			    $aData['bIsTrace'] = TRUE;
			    $aData['bIsTraceStop'] = (isset($_POST['lt_trace_stop']) && $_POST['lt_trace_stop'] == "yes" ) 
			                             ? TRUE : FALSE;
    			if( empty($_POST['lt_trace_money']) || !is_numeric($_POST['lt_trace_money']) 
    			    || $_POST['lt_trace_money'] <=0 )
                {//追号总金额
                    die(ajaxMsg("error", "请选择投注内容"));
                }
                $aData['iTotalAmount'] = floatval($_POST['lt_trace_money']);
			    if( empty($_POST['lt_trace_issues']) || !is_array($_POST['lt_trace_issues']) )
                {//追号期号
                    die(ajaxMsg("error", "请选择追号期数"));
                }
                $aTraceIssue = $_POST['lt_trace_issues'];
                $aTempArr    = array();
                $sTempArr    = "";//错误的期数
                $iTempTimes  = 0;
                foreach( $aTraceIssue as $v )
                {
                    if( empty($_POST['lt_trace_times_'.$v]) || !is_numeric($_POST['lt_trace_times_'.$v]) 
                    || $_POST['lt_trace_times_'.$v] <= 0  )
                    {
                        $sTempArr .= $v." ";
                    }
                    else 
                    {
                        $aTempArr[$v] = intval($_POST['lt_trace_times_'.$v]);
                        $iTempTimes += intval($_POST['lt_trace_times_'.$v]);
                    }
                }
                if( !empty($sTempArr) )
                {
                    die(ajaxMsg("error", "追号中第[".$sTempArr."]期倍数错误"));
                }
                $aData['aTraceIssue'] = $aTempArr;
			    /*if( $aData['iTotalAmount'] != ($aData['iTotalNum'] * $iTempTimes * 2)  )
                {
                    die(ajaxMsg("error", "操作错误"));
                }*/
                unset($aTempArr,$sTempArr,$iTempTimes);
			}
            //02: 提交数据
            $oGamePlay = new model_gameplay();
            $mResult   = $oGamePlay->gameBuy( $iUserId, $iLotteryId, $aData );
            if( $mResult === TRUE )
            {
            	die("success");
            }
            die($mResult);
            
		}
		
	}



    /**
     * 查看历史中奖号码
     * 
     * @author mark
     */
    function actionBonusCode()
    {
    	$iLotteryId	 = isset($_GET['lotteryid']) && is_numeric($_GET['lotteryid']) ? intval($_GET['lotteryid']) : 1;
    	$iIssueCount = isset($_GET['issuecount']) && is_numeric($_GET['issuecount']) ? intval($_GET['issuecount']) : 30;
    	if( !in_array($iIssueCount, array(30, 50, 100)) )
    	{
    		$iIssueCount = 30;
    	}
    	$sStartTime = isset($_POST['starttime']) ? $_POST['starttime'] : '';
    	$sEndTime	 = isset($_POST['endtime']) ? $_POST['endtime'] : '';
    	$sWhere      = 1;
    	$oLottery    = new model_lottery( $GLOBALS['aSysDbServer']['report'] );
    	//获取游戏基本信息
    	$aLottery    = $oLottery->lotteryGetOne( 'cnname,numberrule,issuerule,lotterytype', "lotteryid=" . $iLotteryId );
    	if( $sStartTime && $sEndTime && round((strtotime($sEndTime)-strtotime($sStartTime))/3600/24)<=1 )
    	{
    		$sWhere .= " AND belongdate >= '$sStartTime'  AND belongdate <= '$sEndTime' ";//查询开始期数
    	}
		//else
		//{
			//if( $aLottery['lotterytype'] == 3 )
			//{
    			//$sWhere .= " AND belongdate = '".date('Y-m-d')."'";//查询开始期数
			//}
		//}
    	$aValidNUmString   = unserialize( $aLottery['numberrule'] );
    	$oGameManage       = new model_gamemanage( $GLOBALS['aSysDbServer']['report'] );
    	$aValidNUm       = range( $aValidNUmString['startno'], $aValidNUmString['endno'] );//获取游戏有效号码
    	$GLOBALS['oView']->assign( 'totalNum', count($aValidNUm) );
    	$aHistoryBonusCode = $oGameManage->getHistoryBounsCode( $iLotteryId, $iIssueCount, $sWhere );//获取历史中奖号码
    	krsort($aHistoryBonusCode);
    	$temp_HistoryBonusCode = array();
        $temp_Appears = array_fill( 0, ($iLotteryId != 9 ? $aValidNUmString['len'] : 1), array_fill_keys( $aValidNUm, 0 ) );
        $temp_TotalAppears = array_fill_keys( $aValidNUm, 0 );
        $temp_MaxMiss = array_fill( 0, ($iLotteryId != 9 ? $aValidNUmString['len'] : 1), array_fill_keys( $aValidNUm, 0 ) );
        $temp_TotalMaxMiss = array_fill_keys( $aValidNUm, 0 );
        $temp_MaxSequence = array_fill( 0, ($iLotteryId != 9 ? $aValidNUmString['len'] : 1), array_fill_keys( $aValidNUm, 0 ) );
        $temp_TotalMaxSequence = array_fill_keys( $aValidNUm, 0 );
		if( $aLottery['lotterytype'] != 3 )
		{
			foreach ( $aHistoryBonusCode as $key => $aBonusCode )
			{
				if( strpos( $aBonusCode['code'],' ' ) )
				{
					$aWeiCode = split( ' ',$aBonusCode['code'] );
					foreach( $aWeiCode as $k=>$aw )
					{
						$aWeiCode[$k] = intval($aw);
					}
				}
				else 
				{
					$aWeiCode = str_split($aBonusCode['code'],1);//拆分中奖号码每一位
				}
				$temp_HistoryBonusCode[$key] 		= $aBonusCode;
				$temp_HistoryBonusCode[$key]['wei']	= $aWeiCode;
				$temp_misseddata = unserialize( $aBonusCode['misseddata'] );
				$temp_totalmissed = unserialize( $aBonusCode['totalmissed'] );
				$temp_series = unserialize( $aBonusCode['series'] );
				$temp_totalseries = unserialize( $aBonusCode['totalseries'] );
				$temp_HistoryBonusCode[$key]['misseddata'] = $temp_misseddata;
				$temp_HistoryBonusCode[$key]['totalmissed'] = $temp_totalmissed;
				foreach( $aWeiCode as $p=>$digit )
				{
					//分位出现总次数
					$temp_Appears[$p][$digit] += 1;
					//不分位出现总次数
					$temp_TotalAppears[$digit] += 1;
					//最大连出值
					$temp_MaxSequence[$p][$digit] = max( $temp_MaxSequence[$p][$digit], $temp_series[$p][$digit] );
					//不分位最大连出值
					$temp_TotalMaxSequence[$digit] = max( $temp_TotalMaxSequence[$digit], $temp_totalseries[$digit] );
					//最大遗漏值
					if(is_array($temp_misseddata[$p]))
					{
						foreach($temp_misseddata[$p] as $d=>$m)
						{
							$temp_MaxMiss[$p][$d] = max($temp_MaxMiss[$p][$d], $m);
						}
					}
				}
				//不分位最大遗漏值
				if(is_array($temp_totalmissed))
				{
					foreach($temp_totalmissed as $d=>$m)
					{
						$temp_TotalMaxMiss[$d] = max($temp_TotalMaxMiss[$d], $m['missed']);
					}
				}
			}
			$aCodeGroup = array();
			for( $i = 0; $i < $aValidNUmString['len']; $i++ )//号码组
			{
				$aCodeGroup[] = array(	'wei' 			=> $i,
										'ballstytle' 	=> $i%2+1,
										'normalstytle'	=> $i%2+3
									);
			}
    		$GLOBALS['oView']->assign( "acodegroup", $aCodeGroup );
		}
		else
		{
			$total = array_sum($aValidNUm);
			$middle = count($aValidNUm)/2;
			foreach ( $aHistoryBonusCode as $key => $aBonusCode )
			{
					$aHistoryBonusCode[$key]['wei'] = split( ' ',$aBonusCode['code'] );
					$aHistoryBonusCode[$key]['total'] = array_sum($aHistoryBonusCode[$key]['wei']);
					$aHistoryBonusCode[$key]['bigsmall'] = $aHistoryBonusCode[$key]['total'] > $total/4 ? '大' : ($aHistoryBonusCode[$key]['total'] < $total/4 ? '小' : '和');
					$aHistoryBonusCode[$key]['oddeven'] = $aHistoryBonusCode[$key]['total']%2==0 ? '双' : '单';
					//上下盘、奇偶盘
					$ups = $downs = $odds = $evens = 0;
					foreach($aHistoryBonusCode[$key]['wei'] AS $num)
					{
						if( $num > $middle )
						{
							$downs += 1;
						}
						else
						{
							$ups += 1;
						}
						if( $num%2 == 0 )
						{
							$evens += 1;
						}
						else
						{
							$odds += 1;
						}
					}
					$aHistoryBonusCode[$key]['updowns'] = $ups>$downs ? '上' : ($ups<$downs ? '下' : '中');
					$aHistoryBonusCode[$key]['oddevens'] = $odds>$evens ? '奇' : ($odds<$evens ? '偶' : '和');

					unset($aHistoryBonusCode[$key]['misseddata']);
					unset($aHistoryBonusCode[$key]['totalmissed']);
					unset($aHistoryBonusCode[$key]['series']);
					unset($aHistoryBonusCode[$key]['totalseries']);
			}
		}
    	$GLOBALS['oView']->assign( 'vaildnum', $aValidNUm );
    	$GLOBALS['oView']->assign( "ur_here", "查看历史号码走势" );
    	$GLOBALS['oView']->assign( "lotteryid", $iLotteryId );
    	$GLOBALS['oView']->assign( "bonuscode", $aLottery['lotterytype']==3 ? $aHistoryBonusCode : $temp_HistoryBonusCode );
    	$GLOBALS['oView']->assign( "appears", $temp_Appears );
    	$GLOBALS['oView']->assign( "maxmiss", $temp_MaxMiss );
    	$GLOBALS['oView']->assign( 'series', $temp_MaxSequence );
    	$GLOBALS['oView']->assign( "totalmaxmiss", $temp_TotalMaxMiss );
    	$GLOBALS['oView']->assign( "totalappears", $temp_TotalAppears );
    	$GLOBALS['oView']->assign( 'totalmaxseries', $temp_TotalMaxSequence );
    	$GLOBALS['oView']->assign( "bonuscodelength", $aValidNUmString['len'] );
    	$GLOBALS['oView']->assign( "lotteryname", $aLottery['cnname'] );
    	$GLOBALS['oView']->assign( "starttime", $sStartTime );
    	$GLOBALS['oView']->assign( "endtime", $sEndTime );
    	$GLOBALS['oView']->assign( "lcodegroup", $this->aCodeGroup[$aValidNUmString['len']] );
    	unset($temp_HistoryBonusCode);
		$oGameManage->assignSysInfo();
		$template = 'game_bonuscode_type_'.$aLottery['lotterytype'].'.html';
		$GLOBALS['oView']->display( $template );
		exit;
    }

    /**
     * 查看遗漏
     * 
     * @author floyd
     */
    function actionMissCode()
    {
    	$iLotteryId	 = isset($_GET['lotteryid']) && is_numeric($_GET['lotteryid']) ? intval($_GET['lotteryid']) : 1;
    	$iIssueCount = isset($_GET['issuecount']) && is_numeric($_GET['issuecount']) ? intval($_GET['issuecount']) : 30;
    	if( !in_array($iIssueCount, array(30, 50, 100)) )
    	{
    		$iIssueCount = 30;
    	}
    	$sStartIssue = isset($_POST['startissue']) ? $_POST['startissue'] : '';
    	$sEndIssue	 = isset($_POST['endissue']) ? $_POST['endissue'] : '';
    	$sWhere      = 1;
    	$oLottery    = new model_lottery( $GLOBALS['aSysDbServer']['report'] );
    	//获取游戏基本信息
    	$aLottery    = $oLottery->lotteryGetOne( 'cnname,numberrule,issuerule', "lotteryid=" . $iLotteryId );
    	if( $sStartIssue )
    	{
    		if( !A::singleton("model_issueinfo")->checkIssueRule($sStartIssue, $aLottery['issuerule']) )
    		{
    			sysMsg( '输入的开始奖期不符合规则！', 2 );
    		}
    		$sWhere .= " AND issue >= '$sStartIssue' ";//查询开始期数
    	}
    	if( $sEndIssue )
    	{
			$bTmp = A::singleton( "model_issueinfo", $GLOBALS['aSysDbServer']['report'] )->checkIssueRule($sEndIssue, $aLottery['issuerule']);
    		if( !$bTmp )
    		{
    			sysMsg( '输入的结束奖期不符合规则！', 2 );
    		}
    		$sWhere .= " AND issue <= '$sEndIssue' ";//查询结束期数
    	}
    	$aValidNUmString   = unserialize( $aLottery['numberrule'] );
    	$oGameManage       = new model_gamemanage( $GLOBALS['aSysDbServer']['report'] );
    	$aValidNUm[]       = range( $aValidNUmString['startno'], $aValidNUmString['endno'] );//获取游戏有效号码
    	$aHistoryBonusCode = $oGameManage->getHistoryBounsCode( $iLotteryId, $iIssueCount, $sWhere );//获取历史中奖号码
    	krsort($aHistoryBonusCode);
    	$temp_HistoryBonusCode = array();
    	foreach ( $aHistoryBonusCode as $key => $aBonusCode )
    	{
    		$aWeiCode                           = str_split($aBonusCode['code'],1);//拆分中奖号码每一位
    		$temp_HistoryBonusCode[$key] 		= $aBonusCode;
    		$temp_HistoryBonusCode[$key]['wei']	= $aWeiCode;
    	}
    	$aCodeGroup = array();
    	for( $i = 0; $i < $aValidNUmString['len']; $i++ )//号码组
    	{
    		$aCodeGroup[] = array(	'wei' 			=> $i,
    								'ballstytle' 	=> $i%2+1,
    								'normalstytle'	=> $i%2+3
    							);
    	}
    	$GLOBALS['oView']->assign( 'vaildnum', $aValidNUm[0] );
    	$GLOBALS['oView']->assign( "ur_here", "查看历史号码走势" );
    	$GLOBALS['oView']->assign( "lotteryid", $iLotteryId );
    	$GLOBALS['oView']->assign( "bonuscode", $temp_HistoryBonusCode );
    	$GLOBALS['oView']->assign( "bonuscodelength", $aValidNUmString['len'] );
    	$GLOBALS['oView']->assign( "lotteryname", $aLottery['cnname'] );
    	$GLOBALS['oView']->assign( "acodegroup", $aCodeGroup );
    	$GLOBALS['oView']->assign( "startissue", $sStartIssue );
    	$GLOBALS['oView']->assign( "endissue", $sEndIssue );
    	unset($temp_HistoryBonusCode);
		$oGameManage->assignSysInfo();
		$GLOBALS['oView']->display( "game_bonuscode$iLotteryId.html" );
		exit;
    }

    /**
     * 查看冷热分析
     * @author floyd
     */
    function actionHotCode()
    {
    	$iLotteryId	 = isset($_GET['lotteryid']) && is_numeric($_GET['lotteryid']) ? intval($_GET['lotteryid']) : 1;
    	$iIssueCount = isset($_GET['issuecount']) && is_numeric($_GET['issuecount']) ? intval($_GET['issuecount']) : 30;
    	if( !in_array($iIssueCount, array(30, 50, 100)) )
    	{
    		$iIssueCount = 30;
    	}
    	$sStartTime = isset($_POST['starttime']) ? $_POST['starttime'] : '';
    	$sEndTime	 = isset($_POST['endtime']) ? $_POST['endtime'] : '';
    	$sWhere      = 1;
    	$oLottery    = new model_lottery( $GLOBALS['aSysDbServer']['report'] );
    	//获取游戏基本信息
    	$aLottery    = $oLottery->lotteryGetOne( 'cnname,numberrule,issuerule,lotterytype', "lotteryid=" . $iLotteryId );
    	if( $sStartTime )
    	{
    		$sWhere .= " AND `belongdate` >= '$sStartTime' ";//查询开始日期
    		$iIssueCount = 0;
    	}
    	if( $sEndTime )
    	{
    		$sWhere .= " AND `belongdate` <= '$sEndTime' ";//查询结束日期
    	}
    	$aValidNUmString   = unserialize( $aLottery['numberrule'] );
    	$oGameManage       = new model_gamemanage( $GLOBALS['aSysDbServer']['report'] );
    	$aValidNUm       = range( $aValidNUmString['startno'], $aValidNUmString['endno'] );//获取游戏有效号码
    	$iLen = $aValidNUmString['len'];
    	
    	$aHotCode = $oGameManage->getHotCode( $iLotteryId, $iIssueCount, $sWhere, $iLen, $aValidNUm );//获取历史中奖号码
		//基诺分区
		if( $aLottery['lotterytype'] == 3 )
		{
			$aTmpHotCode = array();
			for($i=0;$i<5;$i++)
			{
				$aTmpHotCode[] = array_slice( $aHotCode[0], $i*16, 16, TRUE );
			}
			$aHotCode = $aTmpHotCode;
		}
        $aTotal = array();
    	foreach( $aHotCode AS $k=>$v )
    	{
    	    arsort( $aHotCode[$k] );
    	}
	    $iTotalIssues = array_sum( $aHotCode[0] );
    	foreach( $aHotCode AS $w=>$ary )
    	{
    	    $i = 0;
    	    foreach( $ary AS $digit=>$count )
    	    {
    	        $aTotal[$i][] = array(
    	           'digit'=>$digit,
    	           'count'=>$count ? $count : 0,
    	           'percent'=>$iTotalIssues ? round( $count/$iTotalIssues*100, 2 ) : 0
    	        );
    	        $i++;
    	    }
    	}
        unset( $aHotCode );

    	$GLOBALS['oView']->assign( 'vaildnum', $aValidNUm );
    	$GLOBALS['oView']->assign( "ur_here", "查看冷热号码" );
    	$GLOBALS['oView']->assign( "lotteryid", $iLotteryId );
    	$GLOBALS['oView']->assign( "codegroup", $this->aCodeGroup[$iLen] );
    	$GLOBALS['oView']->assign( "bonuscode", $aTotal );
    	$GLOBALS['oView']->assign( "lotteryname", $aLottery['cnname'] );
    	$GLOBALS['oView']->assign( "starttime", $sStartTime );
    	$GLOBALS['oView']->assign( "endtime", $sEndTime );
		$oGameManage->assignSysInfo();
		//$template = $aLottery['lotterytype'] == 3 ? "game_hotcode_type_3.html" : "game_hotcode.html";
		$GLOBALS['oView']->display( "game_hotcode.html" );
		exit;
    }

}
?>