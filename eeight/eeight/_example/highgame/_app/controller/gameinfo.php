<?php
/**
 * 文件 : /_app/controller/gameinfo.php
 * 功能 : 用户游戏信息的查看
 *
 * 功能:
 *  - actionGameList    参与游戏信息
 *  - actionGamedetail  查看游戏详情
 *  - actionCancelGame  用户撤单
 *  - actionTask        用户追号记录
 *  - actionTaskDetail  用户追号详情
 *  - actionCancelTask  取消追号  
 *
 * @author    floyd
 * @version   1.0.0
 * @package   highgame
 */

class controller_gameinfo extends basecontroller
{
	/**
	 * 参与游戏信息
	 * URL: ./index.php?controller=gameinfo&action=gamelist
	 * @author SAUL
	 */
	function actionGameList()
	{ //查询下级以及自身的，不能超过自身
		/* @var $oMethod model_method */
		$iUserId = $_SESSION['userid'];
		$oMethod   = A::singleton( "model_method", $GLOBALS['aSysDbServer']['report'] );
		$oLottery = A::singleton( "model_lottery", $GLOBALS['aSysDbServer']['report'] );
		$oUser = A::singleton( 'model_user', $GLOBALS['aSysDbServer']['report'] );
		//参数整理
		$sWhere = " ";
		//开始时间
		if( isset($_GET["starttime"])&&!empty($_GET["starttime"]) )
		{
			$sStartTime = getFilterDate($_GET["starttime"]);
		}
		else
		{
			$sStartTime = time() < strtotime(date("Y-m-d 02:20:00")) ? date("Y-m-d 02:20:00", strtotime("-1 days")) : date("Y-m-d 02:20:00");  //默认为当天
		}
		if(!empty($sStartTime) )
		{
			$sWhere .= " AND P.`writetime`>'".$sStartTime."'";
			$sHtml["starttime"] = $sStartTime;
		}
		//结束时间
		if( isset($_GET["endtime"])&&!empty($_GET["endtime"]) )
		{
			$sEndtime = getFilterDate($_GET["endtime"]);
		}
		else
		{
			$sEndtime = time() < strtotime(date("Y-m-d 02:20:00")) ? date( "Y-m-d 02:20:00") : date( "Y-m-d 02:20:00", strtotime("+1 days") );
		}
		if( !empty($sEndtime) )
		{
			$sHtml["endtime"] = $sEndtime;
			$sWhere .= " AND P.`writetime`<='".$sEndtime."'";
		}
		/* @var $oIssue model_issueinfo */
		$oIssue = A::singleton( "model_issueinfo", $GLOBALS['aSysDbServer']['report'] );
		//获取奖期
		$aIssue = array();
		//身份转化
		if( intval($_SESSION["usertype"])==2 )
		{//总代管理员
			$bIsAdmin = TRUE;
			$iUserId = $oUser->getTopProxyId( intval($_SESSION["userid"]), FALSE ); //获取总代
			if( $oUser->IsAdminSale( intval($_SESSION["userid"]) ) )
			{ //为销售
				$sUserWhere = " AND P.`lvproxyid` IN ("
				."SELECT `topproxyid` FROM `useradminproxy` WHERE `adminid`='"
				.intval($_SESSION["userid"])."')";
			}
			else
			{
				$sUserWhere = " AND P.`lvtopid`='".$iUserId."'";
			}
		}
		else
		{
			$bIsAdmin   = $oUser->isTopProxy( $iUserId );
			$sUserWhere = " AND (FIND_IN_SET('".$iUserId."',UT.`parenttree`) OR (UT.`userid`='".$iUserId."'))";
		}
		//对include的默认
		if( $bIsAdmin )
		{
			$bInclude = TRUE;
			$sHtml["include"] = 1;
		}
		else
		{
			if($oUser->isTopProxy($iUserId))
			{
				$bInclude = TRUE;
				$sHtml["include"] = 1;
			}
			else
			{
				$bInclude = FALSE;
				$sHtml["include"] = 0;
			}
		}
		$aLottery  = array(); //彩种组
		foreach( $oLottery->getLotteryByUser( $iUserId, $bIsAdmin, 'l.cnname, l.lotteryid' ) AS $l )
		{
			$aLottery[$l['lotteryid']] = $l['cnname'];
		}
		$aMethods  = array(); //玩法组
		if($bIsAdmin)
		{
			$aMethodByCrowd = $oMethod->methodGetAllListByCrowd('','M.`pid`>0');
		}
		else
		{
			$oUserMethod = new model_usermethodset( $GLOBALS['aSysDbServer']['report'] );
			$sFields     = " m.`methodid`, m.`lotteryid`, m.`methodname` ";
			$aMethodGroup= $oUserMethod->getUserMethodPrize( $iUserId, $sFields, '', FALSE );
			$aTempArr = array();
			if(!empty($aMethodGroup))
			{
			    foreach( $aMethodGroup as $method )
			    {
			        $aTempArr[] = $method['methodid'];
			    }
			    $sFields = '`lotteryid`,`methodid`,`methodname`';
			    $sCondition = " M.`isclose`=0 AND (M.`pid` IN(".implode( ",", $aTempArr ).") OR M.`methodid` IN(".implode( ",", $aTempArr ).") )";
			    $aMethodByCrowd = $oMethod->methodGetAllListByCrowd('',$sCondition);
			}
		}
		foreach ($aMethodByCrowd as $iLotteryId => $aCrowd)
		{
		    $aMethods[$iLotteryId] = $aCrowd['crowd'];
		}
		$GLOBALS['oView']->assign("lottery",        $aLottery);
		$GLOBALS['oView']->assign("data_method",    json_encode($aMethods)); //方便JS 调用玩法
        $issueList = $oIssue->getItems(0, date("Y-m-d"), 0, 0, 0, time(), 'saleend DESC');
        foreach ($issueList as $v)
        {
            $aIssue[$v['lotteryid']][] = array('issue' => $v['issue'], 'lotteryid' => $v['issue'], 'dateend' => $v['belongdate']);
        }

		$GLOBALS["oView"]->assign( "data_issue", json_encode($aIssue) );
		$iLotteryId = isset( $_GET["lotteryid"] ) && is_numeric( $_GET["lotteryid"] ) ? intval( $_GET["lotteryid"] ) : 0;
		$sHtml["lotteryid"] = $iLotteryId;
		//玩法
		$iCrowdId = isset($_GET["crowdid"])&&is_numeric($_GET["crowdid"]) ? intval($_GET["crowdid"]): 0;
		$sHtml["crowdid"] = $iCrowdId;
		$iPid = isset($_GET["pid"])&&is_numeric($_GET["pid"]) ? intval($_GET["pid"]): 0;
		$sHtml["pid"] = $iPid;
		$iMethodId = isset( $_GET["methodid"] )&&is_numeric( $_GET["methodid"] ) ? intval( $_GET["methodid"] ) :0;
		$sHtml["methodid"] = $iMethodId;
		if($sHtml["lotteryid"] >0 )
		{
			$sWhere .=" AND P.`lotteryid`='".$iLotteryId."' ";
			//按玩法群查询
			if( $iCrowdId > 0 )
			{
			    $sWhere .=" AND M.`crowdid`='".$iCrowdId."'";
			}
			//按玩法组查询
			if( $iPid > 0 )
			{
			    $sWhere .=" AND M.`pid`='".$iPid."'";
			}
			//按玩法查询
			if( $iMethodId > 0 )
			{
			    $sWhere .=" AND M.`methodid`='".$iMethodId."'";
			}
			$sIssue         = isset( $_GET["issue"] )&&!empty( $_GET["issue"] )? daddslashes( $_GET["issue"] ):"0";
			$sHtml["issue"] = $sIssue;
			if( $sIssue!="0" )
			{
				$sWhere .= " AND P.`issue`='".$sIssue."'";
			}
		}
		else
		{
			$sHtml["methodid"]  = 0;
			$sHtml["issue"]     = 0;
		}
		//用户名以及是否包含(支持*号,不支持包含)
		if( isset($_GET["username"])&&!empty($_GET["username"]) )
		{ //指定了用户名
			$sUserName = daddslashes( $_GET["username"] );
			if( strstr($sUserName,'*') )
			{ // 支持模糊搜索
				$sWhere .= " AND UT.`username` LIKE '".str_replace( "*", "%", $sUserName )."'";
				$sHtml["include"] = 0; //支持*,不支持包含下级
				$iUserId = 0;
				$bInclude = FALSE;
				$sHtml["username"] = stripslashes_deep( $sUserName );
			}
			else
			{ //不支持模糊搜索
				$iUser = $oUser->getUseridByUsername( $sUserName ); //获取ID
				if( $iUser>0 )
				{ //需要检测当前搜索到的用户 和 当前用户的关系
					$iUserId = $iUser;
					$sHtml["username"] = stripslashes_deep( $sUserName );
					if( isset($_GET["include"]) && intval($_GET["include"])==1 )
					{
						$sHtml["include"] = 1;
						$bInclude = TRUE;
					}
					else
					{
						$sHtml["include"] = 0;
						$bInclude = FALSE;
					}
				}
				else
				{ //用户不存在
					$sWhere = " AND 1=0";
				}
			}
		}
		else
		{
			if( isset($_GET["include"])&&is_numeric( $_GET["include"] ) )
			{
				$bInclude   = TRUE;
				$iUserId    = 0;
				$sHtml["include"] = 1;
			}
		}
		if(isset($_GET['modes']) && array_key_exists( $_GET['modes'], $GLOBALS['config']['modes']) )
		{
			$sWhere .= ' AND P.`modes`='.intval($_GET['modes']);
			$sHtml["modes"] = $_GET['modes'];
		}
		//下面是Code
		if( isset($_GET["projectno"])&&!empty($_GET["projectno"]) )
		{
			$iProjectNo = model_projects::HighEnCode( daddslashes($_GET["projectno"]), "DECODE" );
			if( intval( $iProjectNo )>0 )
			{
				$sHtml["projectno"] = daddslashes( $_GET["projectno"] );
				$bInclude = TRUE;
				$iUserId = 0;
				$sWhere .= " AND P.`projectid`='".$iProjectNo."'";
			}
		}
		$sWhere .= $sUserWhere;
		/* @var $oProject model_projects */
		$oProject = new model_projects( $GLOBALS['aSysDbServer']['report'] );
		$iPage = isset($_GET["p"])&&is_numeric($_GET["p"]) ?intval($_GET["p"]):1;
		$iIsGetData  = isset($_GET['isgetdata']) ? intval($_GET['isgetdata']) : 0;//是否查询数据
		$sHtml['isgetdata'] = $iIsGetData;
		$aProjects = $iIsGetData == 0 ? array('affects' => 0, 'results' => array()) : $oProject->projectGetResult( $iUserId, $bInclude, "P.*,L.`cnname`,L.`lotterytype`,M.`methodname`,UT.`username`,I.`code` AS `nocode`,I.`statuscode`", $sWhere,
                                                    "P.`projectid` DESC", 25, $iPage );
		$total["in"]  = 0.00;
		$total["out"] = 0.00;
		foreach($aProjects["results"] as $iProjectId=>&$aProject)
		{
			$aProject["projectid"] = model_projects::HighEnCode("D".$aProject["issue"]."-".$aProject["projectid"],"ENCODE");
			$total["in"]  = $total["in"] + $aProject["bonus"];
			$total["out"] = $total["out"]+ $aProject["totalprice"];
			$aProject['code'] = $oProject->AddslasCode($aProject['code'], $aProject['methodid']);
			//对号码进行整理
			if(strlen($aProject["code"])>20)
			{
				$str = "<a href=\"javascript:show_no('".$iProjectId."');\">详细号码</a>";
				$str .= "<div class=\"task_div\" id=\"code_".$iProjectId."\">号码详情";
				$str .= "[<a href=\"javascript:close_no('".$iProjectId."');\" class='fff600'>关闭</a>]<br/>";
				$str .="<textarea class=\"code\" readonly=\"readonly\">";
				$sTempCode      = "";
				$sProjectCode   = "";
				$aCodeDetail    = explode(",", $aProject["code"]);
				$iCodeLen = strlen($aCodeDetail[0]) + 1;//单个号码长度
				$iRowCodeLen = intval(40/$iCodeLen)*$iCodeLen;//一行的号码最大长度
				foreach ( $aCodeDetail as $sCode )
				{
					$sTempCode .= $sCode .",";
					$sProjectCode .= $sCode .",";
					if( strlen($sTempCode) >= $iRowCodeLen )
					{
						$sProjectCode = substr($sProjectCode, 0,-1);
						$sProjectCode .= "\r\n";
						$sTempCode = "";
					}
				}
				$sProjectCode = substr($sProjectCode, 0,-1);
				//                $code = str_replace( array("|"),array(","), $aProject["code"]);
				$str .= $sProjectCode."</textarea></div>";
				$aProject["code"] =$str;
			}
			else
			{
				$aProject["code"] =str_replace( array("|"),array(","), $aProject["code"]);
			}

			if( $aProject['codetype'] == 'input' && !strpos($aProject['methodname'], '混合') )
			{
				$aProject['methodname'] .= ' (单式)';
			}
			if($aProject['modes'] > 0)
			{
				$aProject['modes'] = $GLOBALS['config']['modes'][$aProject['modes']]['name'];
			}
			else
			{
				$aProject['modes'] = '';
			}

			if ( $aProject['statuscode'] != 2 )
			{
				$aProject['nocode'] = '';
			}
			else
			{
				if($aProject['lotterytype']==3)
				{
					$aProject['nocode'] = substr($aProject['nocode'], 0, 29).'<br />'.substr($aProject['nocode'], 30);
				}
			}
		}
        $uExtInfo = $oUser->getUserExtentdInfo( $iUserId, 0 );
        $bShowInclude = TRUE;
        if($uExtInfo['groupid'] == 4)
        {
        	$bShowInclude = FALSE;
        }
        $GLOBALS["oView"]->assign( "showInclude", $bShowInclude );
		$GLOBALS['oView']->assign( 'modes', $GLOBALS['config']['modes'] );
		$GLOBALS['oView']->assign( "total",    $total );
		$GLOBALS['oView']->assign( "aProject", $aProjects["results"] );
		$oPage = new pages( $aProjects["affects"], 25 );
		$GLOBALS['oView']->assign( "pageinfo", $oPage->show(1));
		$GLOBALS['oView']->assign( "s", $sHtml);
		$GLOBALS['oView']->assign( "actionlink", array('text'=>'清空查询条件',"href"=>url('gameinfo','gamelist')));
		$GLOBALS['oView']->assign( "ur_here", "参与游戏信息" );
		$oMethod->assignSysInfo();
		$GLOBALS['oView']->display( "gameinfo_gamelist.html" );
		EXIT;
	}



	/**
	 * 查看游戏详情
	 * URL：./index.php?controller=gameinfo&action=gamedetail
	 * @author SAUL
	 */
	function actionGamedetail()
	{
		$aLocation[0]   = array("title"=>'参与游戏信息',"url"=>url('gameinfo','gamelist'));
		$iProjectId     = isset($_GET["id"])&&!empty($_GET["id"]) ? model_projects::HighEnCode($_GET["id"],"DECODE"):0;
		if( $iProjectId==0 )
		{
			sysMsg( '权限不足', 2, $aLocation );
		}
		/* @var $oUser model_user */
		$oUser = A::singleton( "model_user", $GLOBALS['aSysDbServer']['report'] );
		if( intval($_SESSION["usertype"])==2 )
		{ //总代管理员
			$iUserId = $oUser->getTopProxyId( intval($_SESSION["userid"]), FALSE ); //获取总代
			if( $oUser->IsAdminSale( intval($_SESSION["userid"]) ) )
			{ //为销售
				$sUserWhere = " AND P.`lvproxyid` IN (SELECT `topproxyid` FROM `useradminproxy`"
				." WHERE `adminid`='".intval($_SESSION["userid"])."')";
			}
			else
			{
				$sUserWhere = " AND P.`lvtopid`='".$iUserId."'";
			}
		}
		else
		{
			$iUserId = intval( $_SESSION["userid"] );
			$sUserWhere = " AND (FIND_IN_SET('".intval($iUserId)."',UT.`parenttree`)"
			." OR (UT.`userid`='".$iUserId."'))";
		}
		$oProject = new model_projects( $GLOBALS['aSysDbServer']['report'] );
		$aProject = $oProject->projectGetResult( 0, FALSE,
             " P.*, L.`cnname`,L.`lotterytype`, M.`methodname`,M.`functionname`,M.`nocount`,UT.`username`, I.`code` as `nocode`, I.`canneldeadline`,I.`statuscode`",
             "AND `projectid`='".$iProjectId."'".$sUserWhere, "", 0 );
		if( empty($aProject[0]) )
		{
			sysMsg( '单子不存在', 2, $aLocation );
		}
		if ( $aProject[0]['statuscode'] != 2 )
		{
			$aProject[0]['nocode'] = '';
		}
		//注单编号
		if(intval($aProject[0]["taskid"])>0)
		{
			$oTask = new model_task( $GLOBALS['aSysDbServer']['report'] );
			$aTask = $oTask->taskgetList(0,TRUE,"T.`taskid`,T.`beginissue`"," and T.`taskid`='".$aProject[0]["taskid"]."'","",0);
			$aProject[0]["taskid"] = model_projects::HighEnCode("T".$aTask[0]["beginissue"]."-".$aTask[0]["taskid"],"ENCODE");
		}
		$GLOBALS['oView']->assign( "ur_here", "查看注单详情" );
		$bigmoney = getConfigValue('bigordercancel','10000'); //大额撤单底线
		if( $aProject[0]["totalprice"] > $bigmoney )
		{
			$big = getConfigValue('bigordercancelpre', '0.01'); //大额撤单的手续费比例
			$money = $big * $aProject[0]["totalprice"];
			$GLOBALS["oView"]->assign( "need",  1 ); //需要收费
			$GLOBALS['oView']->assign( "money", $money );
		}
		if(strtotime($aProject[0]["canneldeadline"]) > time() && $aProject[0]['iscancel'] == 0 )
		{ //没有撤单 && 没有过最后的撤单时间(issueinfo表)
			if( intval($_SESSION["userid"])== intval($aProject[0]["userid"]) )
			{
				$GLOBALS['oView']->assign("can", 1 ); //能否撤单
			}
		}
		//获取扩展号码详情
		$prizelevel = $oProject->getExtendCode( "*", "`projectid`='".$aProject[0]["projectid"]."'",
                  "`level` ASC", 0 );
		$aProject[0]['code'] = $oProject->AddslasCode( $aProject[0]["code"], $aProject[0]['methodid'] );
		$aProject[0]["code"] = wordwrap( str_replace( array("|"),array(","), $aProject[0]["code"]),100,"<br/>" );
		$aProject[0]["projectid"] = model_projects::HighEnCode("D".$aProject[0]["issue"]."-".$aProject[0]["projectid"],"ENCODE");
		if($aProject[0]['modes'] > 0)
		{
			$aProject[0]['modes'] = $GLOBALS['config']['modes'][$aProject[0]['modes']]['name'];
		}
		else
		{
			$aProject[0]['modes'] = '';
		}
		$GLOBALS['oView']->assign( "project", $aProject[0] );
		//扩展号码整理
		$aPrizelevelDesc = unserialize( $aProject[0]['nocount'] );
		foreach($prizelevel as $i => $v)
		{
		    $prizelevel[$i]["leveldesc"] = $aPrizelevelDesc[$v['level']]['name'];
			$prizelevel[$i]["expandcode"] = $oProject->AddslasCode( $v["expandcode"], $aProject[0]['methodid'] );
			$prizelevel[$i]["expandcode"] = wordwrap(
			str_replace( array("|"),array(","), $prizelevel[$i]["expandcode"] ),80,"<br>");
		}
		if($aProject[0]['lotterytype'] == 3 && $aProject[0]['codetype'] == 'dxds' && $aProject[0]['nocode'] != '')
		{//基诺趣味型玩法
		    $aCode = explode(" ",$aProject[0]['nocode']);//开奖号码
		    $iAddCount = 0;
		    $iBigCount = 0;//大号个数
		    $iSmallCount = 0;//小号个数
		    $iEevnCount = 0;//偶数号个数
		    $iOddCount = 0;//奇数号个数
		    foreach ($aCode as $iCode)
		    {
		        $iCode = intval($iCode);
		        $iAddCount += $iCode;
		        $iCode%2 == 0 ? $iEevnCount++ : $iOddCount++;
		        $iCode > 40 ? $iBigCount++ : $iSmallCount++;
		    }
		    if($iAddCount % 2 == 0)
		    {
		        $aFinalBonusCode['bjkl_heds'] ='双';
		    }
		    else
		    {
		        $aFinalBonusCode['bjkl_heds'] ='单';
		    }
		    $aFinalBonusCode['bjkl_hedx'] = '大';
		    if($iAddCount < 810)
		    {
		        $aFinalBonusCode['bjkl_hedx'] = '小';
		    }
		    if($iAddCount == 810)
		    {
		        $aFinalBonusCode['bjkl_hedx'] = '和';
		    }
		    $aFinalBonusCode['bjkl_sxpan'] = '上';
		    if($iBigCount > $iSmallCount)
		    {
		        $aFinalBonusCode['bjkl_sxpan'] = '下';//下盘
		    }
		    elseif($iBigCount == $iSmallCount)
		    {
		        $aFinalBonusCode['bjkl_sxpan'] = '中';//中盘
		    }
		    $aFinalBonusCode['bjkl_jopan'] = '奇';
		    if($iEevnCount > $iOddCount)
		    {
		        $aFinalBonusCode['bjkl_jopan'] = '偶';//偶盘
		    }
		    elseif($iEevnCount == $iOddCount)
		    {
		        $aFinalBonusCode['bjkl_jopan'] = '和';//和盘
		    }
		    $sNoHePan = '和值='.$iAddCount.'('.$aFinalBonusCode['bjkl_hedx'].','.$aFinalBonusCode['bjkl_heds'].')<br>';
		    $sNoHePan .= '盘面=('.$aFinalBonusCode['bjkl_sxpan'].','.$aFinalBonusCode['bjkl_jopan'].')';
		    $GLOBALS['oView']->assign("nohepan",$sNoHePan);
		}
		if($aProject[0]['lotterytype'] == 3 && $aProject[0]['nocode'] != ''
		&& $aProject[0]['codetype'] == 'digital' && $aProject[0]['bonus'] > 0)
		{//基诺任选型玩法
		    $aProjectCode = explode(",",$aProject[0]['code']);//用户购买号码
		    $aCode = explode(" ",$aProject[0]['nocode']);//开奖号码
		    $aSameCode = array_intersect($aProjectCode, $aCode);//中奖号码
		    $GLOBALS['oView']->assign("samecode", implode( " ",$aSameCode));
		    $iSelNum = intval(substr($aProject[0]['functionname'],-1));//玩法最少选择的选择号码个数
		    $aLevelCount = array(1=>1,2=>1,3=>2,4=>3,5=>3,6=>4,7=>5);//各个玩法奖级个数
		    $aLevelBonus = array();
		    foreach ($prizelevel as $aLevel)
		    {
		        $aLevelBonus[$aLevel['level']] = $aLevel['prize'];//获取各个奖级的奖金
		        $aLevelTimes[$aLevel['level']] = $aLevel['codetimes'];//获取各个奖级的奖金
		    }
		    $iInterCount = count($aSameCode);
		    $iCodeCount = count($aProjectCode);
		    $aMinNumCount = array(1=>1,2=>2,3=>2,4=>2,5=>3,6=>3,7=>4);//各个玩法最少中奖号码个数,7中0单独计算
		    $aRealPrize = array();
		    $iTotalCount = 0;
		    $fTotalPrize = 0.00;
		    if($iSelNum == 7 && in_array($iInterCount,array(0,1,2,3)))
		    {
		        $iLevel = 5;//任选七中零
		        $iBonusTimes = $iCodeCount > $iSelNum ? $this->GetCombinCount($iCodeCount-$iInterCount,$iSelNum): 1;
		        if($iBonusTimes > 0)
		        {
		            $aRealPrize[$iLevel]["leveldesc"] = $aPrizelevelDesc[$iLevel]['name'];
		            $aRealPrize[$iLevel]['level'] = $iLevel;
		            $aRealPrize[$iLevel]['nocount'] = $iBonusTimes;
		            $aRealPrize[$iLevel]['singleprize'] = $aLevelBonus[$iLevel]/$aLevelTimes[$iLevel];
		            $aRealPrize[$iLevel]['codetimes'] = $aLevelTimes[$iLevel];
		            $aRealPrize[$iLevel]['prize'] = floatval( $aLevelBonus[$iLevel] * $iBonusTimes );
		            $iTotalCount += $iBonusTimes;
		            $fTotalPrize += $aRealPrize[$iLevel]['prize'];
		        }
		    }
		    else
		    {
		        for($i = $aMinNumCount[$iSelNum]; $i<=$iSelNum; $i++ )
		        {
		            $iLevel = $iSelNum+1-$i;//对应奖级
		            $iBonusTimes = $this->GetCombinCount($iInterCount,$i)*$this->GetCombinCount($iCodeCount-$iInterCount,$iSelNum-$i);//对应奖级中奖注数
		            if($iBonusTimes > 0)
		            {
		                $aRealPrize[$iLevel]["leveldesc"] = $aPrizelevelDesc[$iLevel]['name'];
		                $aRealPrize[$iLevel]['level'] = $iLevel;
		                $aRealPrize[$iLevel]['nocount'] = $iBonusTimes;
		                $aRealPrize[$iLevel]['singleprize'] = $aLevelBonus[$iLevel]/$aLevelTimes[$iLevel];
		                $aRealPrize[$iLevel]['codetimes'] = $aLevelTimes[$iLevel];
		                $aRealPrize[$iLevel]['prize'] = floatval( $aLevelBonus[$iLevel] * $iBonusTimes );
		                $iTotalCount += $iBonusTimes;
		                $fTotalPrize += $aRealPrize[$iLevel]['prize'];
		            }
		        }
		    }
		    ksort($aRealPrize);
		    $GLOBALS['oView']->assign("realprize",$aRealPrize);
		    $GLOBALS['oView']->assign("totalcount",$iTotalCount);
		    $GLOBALS['oView']->assign("totalprize",$fTotalPrize);
		}
		$GLOBALS['oView']->assign( "prizelevel", $prizelevel );
		$GLOBALS['oView']->assign("levelcount",count($prizelevel));
		$oProject->assignSysInfo();
		$GLOBALS['oView']->display( "gameinfo_gamedetail.html" );
		EXIT;
	}

	/**
        * 计算排列组合的个数
        *
        * @author mark
        * 
        * @param integer $iBaseNumber   基数
        * @param integer $iSelectNumber 选择数
        * 
        * @return mixed
        * 
    */
	function GetCombinCount( $iBaseNumber, $iSelectNumber )
	{
	    if($iSelectNumber > $iBaseNumber)
	    {
	        return 0;
	    }
	    if( $iBaseNumber == $iSelectNumber || $iSelectNumber == 0 )
	    {
	        return 1;//全选
	    }
	    if( $iSelectNumber == 1 )
	    {
	        return $iBaseNumber;//选一个数
	    }
	    $iNumerator = 1;//分子
	    $iDenominator = 1;//分母
	    for($i = 0; $i < $iSelectNumber; $i++)
	    {
	        $iNumerator *= $iBaseNumber - $i;//n*(n-1)...(n-m+1)
	        $iDenominator *= $iSelectNumber - $i;//(n-m)....*2*1
	    }
	    return $iNumerator / $iDenominator;
	}

	/**
	 * 用户撤单
	 * URL: ./index.php?controller=gameinfo&action=cancelgame
	 * @author JAMES
	 */
	function actionCancelgame()
	{
		$sProjectNo   = !empty($_GET["id"]) ? $_GET["id"] : "";
		$aLocation[0] = array("title"=>'查看注单详情',"url"=>url('gameinfo', 'gamedetail', array('id'=>$sProjectNo)));
		$iProjectId   = !empty($sProjectNo) ? model_projects::HighEnCode($sProjectNo, "DECODE") : 0;
		if( $iProjectId == 0 )
		{
			sysMsg( '权限不足', 2, $aLocation );
		}
		/* @var $oGame model_gamemanage */
		$oGame      = A::singleton("model_gamemanage");
		$mResult    = $oGame->cancelProject( intval($_SESSION["userid"]), $iProjectId );
		if( $mResult === TRUE )
		{
			sysMsg( '撤单成功', 1, $aLocation );
		}
		else
		{
			sysMsg( $mResult, 2, $aLocation );
		}
	}



	/**
	 * 追号记录
	 * URL: ./index.php?controller=gameinfo&action=task
	 * @author SAUL
	 */
	function actionTask()
	{   //  查询自身+下级的追号记录
		$iUserId = $_SESSION['userid'];
		$oMethod   = A::singleton( "model_method", $GLOBALS['aSysDbServer']['report'] );
		$oLottery = A::singleton( "model_lottery", $GLOBALS['aSysDbServer']['report'] );
		$oUser = A::singleton( 'model_user', $GLOBALS['aSysDbServer']['report'] );
		$aLottery  = array(); //彩种组
		if( intval($_SESSION["usertype"])==2 )
		{//总代管理员
			$bIsAdmin = TRUE;
			$iUserId = $oUser->getTopProxyId(intval($_SESSION["userid"]), FALSE ); //获取总代
		}
		else
		{
			$bIsAdmin   = $oUser->isTopProxy( $iUserId );
		}
		foreach( $oLottery->getLotteryByUser( $iUserId, $bIsAdmin, 'l.cnname, l.lotteryid' ) AS $l )
		{
			$aLottery[$l['lotteryid']] = $l['cnname'];
		}
		$aMethods  = array(); //玩法组
		if($bIsAdmin)
		{
			$aMethodByCrowd = $oMethod->methodGetAllListByCrowd('','M.`pid`>0');
		}
		else
		{
			$oUserMethod = new model_usermethodset( $GLOBALS['aSysDbServer']['report'] );
			$sFields     = " m.`methodid`, m.`lotteryid`, m.`methodname` ";
			$aMethodGroup= $oUserMethod->getUserMethodPrize( $iUserId, $sFields, '', FALSE );
			$aTempArr = array();
			if(!empty($aMethodGroup))
			{
			    foreach( $aMethodGroup as $method )
			    {
			        $aTempArr[] = $method['methodid'];
			    }
			    $sFields = '`lotteryid`,`methodid`,`methodname`';
			    $sCondition = " M.`isclose`=0 AND (M.`pid` IN(".implode( ",", $aTempArr ).") OR M.`methodid` IN(".implode( ",", $aTempArr ).") )";
			    $aMethodByCrowd = $oMethod->methodGetAllListByCrowd('',$sCondition);
			}
		}
		foreach ($aMethodByCrowd as $iLotteryId => $aCrowd)
		{
		    $aMethods[$iLotteryId] = $aCrowd['crowd'];
		}
		$GLOBALS['oView']->assign( "lottery",       $aLottery );
		$GLOBALS['oView']->assign( "data_method",   json_encode($aMethods) );
		//参数整理
		$sWhere = " ";
		//开始时间
		if( isset($_GET["starttime"])&&!empty($_GET["starttime"]) )
		{
			$sStartTime = getFilterDate($_GET["starttime"]);
		}
		else
		{
		    $sStartTime = time() < strtotime(date("Y-m-d 02:20:00")) ? date("Y-m-d 02:20:00", strtotime("-1 days")) : date("Y-m-d 02:20:00");  //默认为当天
		}
		if( !empty($sStartTime) )
		{
			$sWhere .= " AND T.`begintime`>='".$sStartTime."'";
			$sHtml["starttime"] = $sStartTime;
		}
		//结束时间
		if( isset($_GET["endtime"])&&!empty($_GET["endtime"]) )
		{
			$sEndTime = getFilterDate($_GET["endtime"]);
		}
		else
		{
			$sEndTime = time() < strtotime(date("Y-m-d 02:20:00")) ? date("Y-m-d 02:20:00") : date("Y-m-d 02:20:00", strtotime("+1 days"));  //默认为当天
		}
		if( !empty($sEndTime) )
		{
			$sHtml["endtime"] = $sEndTime;
			$sWhere .= " AND T.`begintime`<'".$sEndTime."'";
		}
		/* @var $oIssue model_issueinfo */
		$oIssue     = A::singleton( "model_issueinfo", $GLOBALS['aSysDbServer']['report'] );
		$aIssue = array();
        $issueList = $oIssue->getItems(0, date("Y-m-d"), 0, 0, 0, time(), 'saleend DESC');
        foreach ($issueList as $v)
        {
            $aIssue[$v['lotteryid']][] = array('issue' => $v['issue'], 'lotteryid' => $v['issue'], 'dateend' => $v['belongdate']);
        }
        
		$GLOBALS["oView"]->assign( "data_issue", json_encode($aIssue) );
		$iLotteryId = isset($_GET["lotteryid"])&&is_numeric($_GET["lotteryid"]) ? intval($_GET["lotteryid"]) : 0;
		$sHtml["lotteryid"] = $iLotteryId;
		$iCrowdId = isset($_GET["crowdid"])&&is_numeric($_GET["crowdid"]) ? intval($_GET["crowdid"]): 0;
		$sHtml["crowdid"] = $iCrowdId;
		$iPid = isset($_GET["pid"])&&is_numeric($_GET["pid"]) ? intval($_GET["pid"]): 0;
		$sHtml["pid"] = $iPid;
		$iMethodId = isset( $_GET["methodid"] )&&is_numeric( $_GET["methodid"] ) ? intval( $_GET["methodid"] ) :0;
		$sHtml["methodid"] = $iMethodId;
		if( $iLotteryId>0 )
		{
			$sWhere .=" AND T.`lotteryid`='".$iLotteryId."' ";
			//按玩法群查询
			if( $iCrowdId > 0 )
			{
			    $sWhere .=" AND M.`crowdid`='".$iCrowdId."'";
			}
			//按玩法组查询
			if( $iPid > 0 )
			{
			    $sWhere .=" AND M.`pid`='".$iPid."'";
			}
			//按玩法查询
			if( $iMethodId > 0 )
			{
			    $sWhere .=" AND M.`methodid`='".$iMethodId."'";
			}
			$sIssue = isset($_GET["issue"])&&!empty($_GET["issue"]) ? daddslashes($_GET["issue"]): "0";
			$sHtml["issue"] = $sIssue;
			if( $sIssue<>"0" )
			{
				$sWhere .= " AND T.`beginissue`='".$sIssue."'";
			}
		}
		else
		{
			$sHtml["methodid"] = 0;
			$sHtml["issue"] = 0;
		}
		//用户身份的转化
		/* @var $oUser model_user */
		$oUser = A::singleton( "model_user", $GLOBALS['aSysDbServer']['report'] );
		if( intval($_SESSION["usertype"])==2 )
		{//销售
			$bIsAdmin   = TRUE;
			$iUserId    = $oUser->getTopProxyId( intval($_SESSION["userid"]), FALSE ); //获取总代
			if( $oUser->IsAdminSale( intval($_SESSION["userid"]) ) )
			{ //为销售
				$sUserWhere = " AND T.`lvproxyid` in (SELECT `topproxyid` FROM `useradminproxy`"
				." WHERE `adminid`='".intval($_SESSION["userid"])."')";
			}
			else
			{
				$sUserWhere = " AND T.`lvtopid`='".intval($iUserId)."'";
			}
		}
		else
		{
			$iUserId = intval($_SESSION["userid"]);
			$bIsAdmin = FALSE;
			$sUserWhere = " AND (FIND_IN_SET('".$iUserId."',UT.`parenttree`)"
			." OR (UT.`userid`='".$iUserId."'))";
		}
		if( $bIsAdmin )
		{
			$bInclude = TRUE;
			$sHtml["include"] = 1;
		}
		else
		{
			if($oUser->isTopProxy($iUserId))
			{
				$bInclude = TRUE;
				$sHtml["include"] = 1;
			}
			else
			{
				$bInclude = FALSE;
				$sHtml["include"] = 0;
			}
		}
		//用户名以及是否包含(支持*号,不支持包含)
		if(isset($_GET["username"])&&!empty($_GET["username"]))
		{ //指定了用户名
			$sUserName = daddslashes( $_GET["username"] );
			if( strstr($sUserName,'*') )
			{ // 支持模糊搜索
				$sWhere .= " AND UT.`username` LIKE '".str_replace( "*", "%", $sUserName )."'";
				$sHtml["include"] = 0; //支持*,不支持包含下级
				$iUserId = 0;
				$bInclude = FALSE;
				$sHtml["username"] = stripslashes_deep($sUserName);
			}
			else
			{ //不支持模糊搜索
				$iUser = $oUser->getUseridByUsername( $sUserName ); //获取ID
				if($iUser >0)
				{ //需要检测当前搜索到的用户 和 当前用户的关系
					$iUserId = $iUser;
					$sHtml["username"] = stripslashes_deep($sUserName);
					if( isset($_GET["include"]) && intval($_GET["include"])==1 )
					{
						$sHtml["include"] = 1;
						$bInclude = TRUE;
					}
					else
					{
						$sHtml["include"] = 0;
						$bInclude = FALSE;
					}
				}
				else
				{ //用户不存在
					$sWhere = " AND 1=0";
				}
			}
		}
		else
		{
			if(isset($_GET["include"])&&is_numeric($_GET["include"]))
			{
				$bInclude = TRUE;
				$iUserId = 0;
				$sHtml["include"] = 1;
			}
		}
		if(isset($_GET['modes']) && array_key_exists($_GET['modes'], $GLOBALS['config']['modes']))
		{
			$sWhere .= ' AND T.`modes`='.intval($_GET['modes']);
			$sHtml["modes"] = intval($_GET['modes']);
		}
		//下面是Code
		if( isset($_GET["taskno"])&&!empty($_GET["taskno"]) )
		{
			$iTaskId = model_projects::HighEnCode($_GET["taskno"], "DECODE" );
			if( $iTaskId>0 )
			{
				$sHtml["taskno"] = daddslashes($_GET["taskno"]);
				$sWhere .= " AND T.`taskid`='".intval($iTaskId)."'";
				$iUserId = 0;
				$bInclude = TRUE;
			}
		}
		$sWhere .= $sUserWhere;
		$iPage = isset($_GET["p"])&&is_numeric($_GET["p"]) ?intval($_GET["p"]): 1;
		/* @var $oTask model_task */
		//$oTask = A::singleton("model_task");
		$oTask = new model_task( $GLOBALS['aSysDbServer']['report'] );
		$iIsGetData  = isset($_GET['isgetdata']) ? intval($_GET['isgetdata']) : 0;//是否查询数据
		$sHtml['isgetdata'] = $iIsGetData;
		$aTask = $iIsGetData == 0 ? array('affects' => 0, 'results' => array()) : $oTask->taskgetList( $iUserId, $bInclude,"",$sWhere, "T.`taskid` DESC", 25, $iPage );
		$total["total"]  = 0.00;
		$total["finish"] = 0.00;
		$total["cancel"] = 0.00;
		foreach( $aTask["results"] as $iTaskId=>&$task )
		{
			$task["taskid"] = model_projects::HighEnCode("T".$task["beginissue"]."-".$task["taskid"],"ENCODE");
			$total["total"]  = $total["total"]  + $task["taskprice"];
			$total["finish"] = $total["finish"] + $task["finishprice"];
			$total["cancel"] = $total["cancel"] + $task["cancelprice"];
			$task['codes'] = model_projects::AddslasCode($task['codes'], $task['methodid']);
			//对号码进行整理
			if(strlen($task["codes"])>20)
			{
				$str = "<a href=\"javascript:show_no('".$iTaskId."');\">详细号码</a>";
				$str .= "<div class=\"task_div\" id=\"code_".$iTaskId."\">号码详情";
				$str .= "[<a href=\"javascript:close_no('".$iTaskId."');\" class='fff600'>关闭</a>]<br/>";
				$str .="<textarea class=\"code\" readonly=\"readonly\">";
				$sTempCode      = "";
				$sProjectCode   = "";
				$aCodeDetail    = explode(",", $task['codes']);
				$iCodeLen = strlen($aCodeDetail[0]) + 1;//单个号码长度
				$iRowCodeLen = intval(40/$iCodeLen)*$iCodeLen;//一行的号码最大长度
				foreach ( $aCodeDetail as $sCode )
				{
					$sTempCode .= $sCode .",";
					$sProjectCode .= $sCode .",";
					if( strlen($sTempCode) >= $iRowCodeLen )
					{
						$sProjectCode = substr($sProjectCode, 0,-1);
						$sProjectCode .= "\r\n";
						$sTempCode = "";
					}
				}
				$sProjectCode = substr($sProjectCode, 0,-1);
				$str .= $sProjectCode."</textarea></div>";
				$task["codes"] =$str;
			}
			else
			{
				$task["codes"] =str_replace( array("|"),array(","), $task["codes"]);
			}
			if( $task['codetype'] == 'input' && !strpos($task['methodname'], '混合') )
			{
				$task['methodname'] .= ' (单式)';
			}
			if($task['modes'] > 0)
			{
				$task['modes'] = $GLOBALS['config']['modes'][$task['modes']]['name'];
			}
			else
			{
				$task['modes'] = '';
			}
		}
        $uExtInfo = $oUser->getUserExtentdInfo( $iUserId, 0 );
        $bShowInclude = TRUE;
        if($uExtInfo['groupid'] == 4)
        {
        	$bShowInclude = FALSE;
        }
        $GLOBALS["oView"]->assign( "showInclude", $bShowInclude );
		$GLOBALS["oView"]->assign( "modes",    $GLOBALS["config"]['modes'] );
		$GLOBALS['oView']->assign( "total", $total );
		$GLOBALS['oView']->assign( "aTask", $aTask["results"] );
		$oPage = new pages( $aTask["affects"], 25 );
		$GLOBALS['oView']->assign( "pageinfo", $oPage->show(1) );
		$GLOBALS['oView']->assign( "s", $sHtml );
		$GLOBALS['oView']->assign( "actionlink", array("text"=>'清空查询条件',"href"=>url('gameinfo','task')) );
		$GLOBALS["oView"]->assign( "ur_here", "查看追号信息" );
		$oTask->assignSysInfo();
		$GLOBALS['oView']->display("gameinfo_task.html");
		EXIT;
	}



	/**
	 * 追号详情查看
	 * URL: ./index.php?controller=gameinfo&action=taskdetail
	 * @author SAUL
	 */
	function actionTaskDetail()
	{
		$aLocation[0]   = array( "title"=>'查看追号记录', "url"=>url('gameinfo','task') );
		$iTaskId        = isset($_GET["id"])&&!empty($_GET["id"]) ? model_projects::HighEnCode($_GET["id"],"DECODE") : 0;
		if( $iTaskId==0 )
		{
			sysMsg( '没有权限', 2, $aLocation );
		}
		/* @var $oUser model_user */
		$oUser = A::singleton( "model_user", $GLOBALS['aSysDbServer']['report'] );
		if( intval($_SESSION["usertype"])==2 )
		{//总代管理员
			$iUserId = $oUser->getTopProxyId( intval($_SESSION["userid"]), FALSE ); //获取总代
			if( $oUser->IsAdminSale( intval($_SESSION["userid"]) ) )
			{ //为销售
				$sUserWhere = " AND T.`lvproxyid` IN (SELECT `topproxyid` FROM `useradminproxy` "
				."WHERE `adminid`='".intval($_SESSION["userid"])."')";
			}
			else
			{
				$sUserWhere = " AND T.`lvtopid`='".$iUserId."'";
			}
		}
		else
		{
			$iUserId = intval( $_SESSION["userid"] );
			$sUserWhere = " AND (FIND_IN_SET('".$iUserId."',UT.`parenttree`)"
			." OR (UT.`userid`='".$iUserId."'))";
		}
		/* @var $oTask model_task */
		$oTask = A::singleton( "model_task", $GLOBALS['aSysDbServer']['report'] );
		$aTask = $oTask->taskgetList( 0, FALSE, ""," AND T.`taskid`='".$iTaskId."'".$sUserWhere, "", 0 );
		if( empty($aTask[0]) )
		{
			sysMsg('追号单不存在', 2, $aLocation );
		}
		if( intval($aTask[0]["userid"]) == intval($_SESSION["userid"]) )
		{
			$GLOBALS['oView']->assign("can", 1 ); //能够撤单
		}
		$oProject = A::singleton( "model_projects", $GLOBALS['aSysDbServer']['report'] );
		$aTask[0]['codes'] = $oProject->AddslasCode( $aTask[0]["codes"], $aTask[0]['methodid'] );
		$aTask[0]["codes"] = wordwrap( str_replace( array("|"),array(","), $aTask[0]['codes'] ),100,"<br/>" );
		$aTask[0]["taskid"] = model_projects::HighEnCode("T".$aTask[0]["beginissue"]."-".$aTask[0]["taskid"],"ENCODE");
		$aTaskDetail        = $oTask->taskdetailGetList( $iTaskId, $aTask[0]["lotteryid"] );
		foreach( $aTaskDetail as &$aDetail )
		{
			if( $aDetail["projectid"]>0 )
			{ //注单详情
				$aDetail["projectid"] = model_projects::HighEnCode("D".$aDetail["issue"]."-".$aDetail["projectid"], "ENCODE");
			}
		}
		if($aTask[0]['modes'] > 0)
		{
			$aTask[0]['modes'] = $GLOBALS['config']['modes'][$aTask[0]['modes']]['name'];
		}
		else
		{
			$aTask[0]['modes'] = '';
		}
		$GLOBALS["oView"]->assign( "task",          $aTask[0] );
		$GLOBALS['oView']->assign( "aTaskdetail",   $aTaskDetail );
		$GLOBALS['oView']->assign( "ur_here",       "查看追号详情");
		$oTask->assignSysInfo();
		$GLOBALS['oView']->display("gameinfo_taskdetail.html");
		EXIT;
	}



	/**
	 * 追号单撤单
	 * URL: ./index.php?controller=gameinfo&action=canceltask
	 * @author JAMES
	 */
	function actionCancelTask()
	{
		$sTaskNo      = !empty($_POST["id"]) ? $_POST["id"] : "";
		$aLocation[0] = array("title"=>'查看追号详情',"url"=>url('gameinfo', 'taskdetail', array('id'=>$sTaskNo)));
		$iTaskId      = !empty($sTaskNo) ? model_projects::HighEnCode($sTaskNo, "DECODE") : 0;
		if( $iTaskId == 0 )
		{
			sysMsg( '权限不足', 2, $aLocation );
		}
		$aId = !empty($_POST["taskid"]) ?$_POST["taskid"]: array();
		$oGame   = new model_gamemanage();
		$mResult = $oGame->cancelTask( intval($_SESSION["userid"]), $iTaskId, $aId );
		if( $mResult === TRUE )
		{
			sysMsg( '操作成功', 1, $aLocation );
		}
		else
		{
			sysMsg( $mResult, 2, $aLocation );
		}
	}
}
?>
