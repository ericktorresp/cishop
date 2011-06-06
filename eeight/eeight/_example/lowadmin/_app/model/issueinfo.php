<?php
/**
 * 奖期模型
 */
define("MAX_TASK_ISSUES", 20); /// 最大可追号期数
class model_issueinfo extends basemodel
{

	function __construct( $aDBO=array() )
	{
		parent::__construct($aDBO);
	}



	/**
	 * 奖期获取
	 *
	 * @param string $sFields
	 * @param string $sCondition
	 * @param string $sOrderBy
	 * @param integer $iPageRecord
	 * @param integer $iCurrentPage
	 */
	function issueGetList($sFields='', $sCondition='', $sOrderBy='', $iPageRecord=0, $iCurrentPage=0)
	{
		if( empty($sFields) )
		{
			$sFields = "*";
		}
		if( empty($sCondition) )
		{
			$sCondition = " 1 ";
		}
		$iPageRecord = is_numeric($iPageRecord) && $iPageRecord ? intval($iPageRecord) : 0;
		$sTableName = "`issueinfo` AS A LEFT JOIN `lottery` AS B on (A.`lotteryid`=B.`lotteryid`)"; 
		if( $iPageRecord==0 )
		{
			if( !empty($sOrderBy) )
			{
				$sOrderBy =" order by ".$sOrderBy;
			}
			return $this->oDB->getAll("SELECT ".$sFields." FROM ".$sTableName." WHERE ".$sCondition. $sOrderBy);
		}
		return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecord, $iCurrentPage, $sOrderBy );
	}



	/**
	 * 奖期批量生成
	 *
	 * @param integer $aLotteryId
	 * @param string $sStartDate
	 * @param string $sEndDate
	 * @param integer $iStartIssue
	 */
	function issueCreate( $iLotteryId, $sStartDate,$sEndDate,$iStartIssue )
	{
		$id = is_numeric($iLotteryId) ? intval($iLotteryId):0;
		if($id ==0 )
		{
			return -1;
		}
		$aLottery = $this->oDB->getOne("SELECT * FROM `lottery` WHERE `lotteryid`='".$id."'");
		if(empty($aLottery))
		{
			return -2;
		}
		//开奖周期转化成周与天的关系
		$weekcycle = $aLottery["weekcycle"];
		$week[1] = intval(($weekcycle&1)/1);
		$week[2] = intval(($weekcycle&2)/2);
		$week[3] = intval(($weekcycle&4)/4);
		$week[4] = intval(($weekcycle&8)/8);
		$week[5] = intval(($weekcycle&16)/16);
		$week[6] = intval(($weekcycle&32)/32);
		$week[7] = intval(($weekcycle&64)/64);
		foreach ($week as $i=>$v)
		{
			if($v==0)
			{
				unset($week[$i]);
			}
		}			
		$datestart = getFilterDate($sStartDate,"Y-m-d");
		$dateend = getFilterDate($sEndDate,"Y-m-d");
		$sleepdatestart = getFilterDate($aLottery["yearlybreakstart"],"Y-m-d");
		$sleepdateend = getFilterDate($aLottery["yearlybreakend"],"Y-m-d");
		//上周周日
		$dataWeek =date("N",strtotime($datestart)); //开始日期的星期几
		$datebase = date("Y-m-d",strtotime("-".$dataWeek." days",strtotime($datestart)));//上周周日作为基础日期
		$datesub=intval(date("U",strtotime($datebase))/(24*60*60*7));//开始周期
		$j=1;		 		
		//修正开售时间
		$timestart = $aLottery["dailystart"];
		$timeend = $aLottery["dailyend"];
		if($timeend>$timestart)
		{ //非跨天销售
			$iAdd=1;
		}
		else
		{ //跨天销售
			$iAdd=0;
		}
		//不考虑休市,不考虑日期限制
		for($date=intval(date("U",strtotime($datestart))/(24*60*60*7)-1);$date<=intval(date("U",strtotime($dateend))/(24*60*60*7));$date++)
		{
			$i = ($date-$datesub)*7; //开始周期			
			foreach($week as $it=>$v)
			{
				$k = $i+$it;
				$t[$j]['issue']  = date("Y-m-d",strtotime("+ ".$k." days",strtotime($datebase)));
				if($j==1)
				{
					$t[$j]['start'] = $datebase;
				}
				else
				{
					$t[$j]['start'] = date("Y-m-d",strtotime("+". $iAdd." days",strtotime($t[$j-1]['issue'])));
				}
				$j++;					
			}			
		}
		$j = is_numeric($iStartIssue)?intval($iStartIssue):0; //开始的奖期
		if($j==0)
		{
			return -2;
		}
		//对休市,日期限制进行剔除，同时指定奖期
		foreach($t as $i=>$v)
		{
			if($v["issue"]<$datestart)
			{
				unset($t[$i]);
			}
			elseif($v["issue"]>$dateend)
			{
				unset($t[$i]);
			}
			elseif(($v["issue"]>=$sleepdatestart)&&($v['issue']<=$sleepdateend))
			{
				unset($t[$i]);
			}
			else
			{
				$tt[$j] = $v;
				$j++;
			}
		}
		$issuerule = $aLottery["issuerule"];
		$match =array();
		preg_match_all('/^\(([a-zA-Z-_]+)\)\(N(\d+)\)/i',$issuerule,$match);
		$dateStr = $match[1][0];//号码规则前面与日期相关的(允许-_)
		$nolength = $match[2][0];//后面每期的N的长度
		foreach($tt as $i=>$v)
		{
			$aNewIssue['lotteryid'] = $iLotteryId;			
			$aNewIssue["salestart"] = date("Y-m-d H:i:s",strtotime($v['start']." ".$aLottery["dailystart"]));
			$aNewIssue['issue'] =date($dateStr,strtotime($v['issue'])).substr("1000000000".strval($i),-($nolength));
			$aNewIssue['saleend'] = date("Y-m-d H:i:s",strtotime($v['issue']." ".$aLottery["dailyend"]));
			$aNewIssue['dynamicprizestart'] = date("Y-m-d H:i:s",strtotime($v['issue']." ".$aLottery["dynamicprizestart"]));	
			$aNewIssue['dynamicprizeend'] = date("Y-m-d H:i:s",strtotime($v['issue']." ".$aLottery["dynamicprizeend"]));
			$aNewIssue["canneldeadline"] = date("Y-m-d H:i:s",strtotime($v['issue']." ".$aLottery["canceldeadline"]));	
			$aNewIssue["officialtime"] = "0000-00-00 00:00:00";
			$aNewIssue["writetime"] ="0000-00-00 00:00:00";
			$aNewIssue["verifytime"] ="0000-00-00 00:00:00";
			$aNewIssue['code'] = '';
			$aNewIssue["statuscode"] = 0;
			$aNewIssue["statusdeduct"] = 0;
			$aNewIssue["statususerpoint"] = 0;
			$aNewIssue["statusbonus"] = 0;
			$sSql = " SELECT * FROM `issueinfo` WHERE `lotteryid` = '$iLotteryId' AND `issue` = '" 
			             . $aNewIssue['issue'] . "' LIMIT 1";
			$aHaveIssue = $this->oDB->getOne( $sSql );//判断是否有重复奖期存在
			if( empty($aHaveIssue) )
			{
			    $iResult = $this->oDB->insert('issueinfo',$aNewIssue);
			    if($iResult === FALSE)
			    {
			        return -3;
			    }
			}
		}
		return 1;
	}



	/**
	 * 获取单个奖期
	 *
	 * @author james   090810
     * @access public
	 * @param string $sField
	 * @param string $sCondition
	 * @return array
	 */
	function & IssueGetOne( $sField='', $sCondition='', $sLeftJoin='' )
	{
		$sField = empty($sField) ? '*' : daddslashes($sField);
		$sCondition = empty($sCondition) ? '' : ' WHERE '.$sCondition;
		$sTable = "`issueinfo` AS A ";
		return $this->oDB->getOne("SELECT ".$sField." FROM ".$sTable." ".$sLeftJoin." ".$sCondition);
	}
	
	
	/**
	 * 以奖期表为基准获取相关数据列表
	 *
	 * @author james   090815
	 * @access pbulic
	 * @param  string   $sFields
	 * @param  string   $sCondition
	 * @param  string   $sLeftJoin //左联表，基表:usermethodset as ums
	 * @param  string   $sOrderBy
	 * @param  int      $iPageRecord
	 * @param  int      $iCurrentPage
	 * @return array    //数据集合
	 */
	public function & issueMutilTableGetList( $sFields='', $sCondition='', $sLeftJoin='', $sOrderBy='', 
	                                          $iPageRecord=0, $iCurrentPage=0 )
    {
    	$sFields    = empty($sFields) ? "i.*" : daddslashes($sFields);
    	$sTableName = "`issueinfo` AS i ".daddslashes($sLeftJoin);
        if( $iPageRecord == 0 )
        {//不分页显示
            $sCondition = empty($sCondition) ? "" : " WHERE ".$sCondition;
            $sSql       = "SELECT ".$sFields." FROM ".$sTableName." ".$sCondition." ".$sOrderBy;
            return $this->oDB->getAll( $sSql );
        }
        else 
        {
            $sCondition = empty($sCondition) ? "1" : $sCondition;
            return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecord, $iCurrentPage, 
                                               $sOrderBy );
        }
    }
	
	
	
	/**
	 * 获取当前时间所在的销售期
	 *
	 * @author james   090808
	 * @access public
	 * @param  int     $iLotteryId
	 * @param  string  $sFields
	 * @return array
	 */
	public function getCurrentIssue( $iLotteryId, $sFields="" )
	{
		$aResult = array();
		if( empty($iLotteryId) || !is_numeric($iLotteryId) || $iLotteryId <= 0 )
		{
			return $aResult;
		}
		$iLotteryId = intval($iLotteryId);
		$sCurrentTime = date("Y-m-d H:i:s",time());
		$sFields    = empty($sFields) ? 
		              "`issueid`,`issue`,`salestart`,`saleend`,`dynamicprizestart`,`dynamicprizeend`" : 
		              daddslashes($sFields);
		$sSql       = " SELECT ".$sFields." FROM `issueinfo` WHERE `code`='' AND `lotteryid`='".$iLotteryId."'
		                AND `salestart`<='".$sCurrentTime."' AND `saleend`>='".$sCurrentTime."' LIMIT 1";
		return $this->oDB->getOne($sSql);
	}
	
	
	
	/**
	 * 根据彩种和当前奖期读取追号期数据
	 *
	 * @author james   090808
     * @access public
	 * @param  int      $iLotteryId    彩种ID
	 * @param  int      $iCurrentIssueId //当前期号ID
	 * @param  string   $sFields
	 * @return array
	 */
	public function & getTaskIssue( $iLotteryId, $iCurrentIssueId, $sFields="" )
	{
		$aResult = array();
	    if( empty($iLotteryId) || !is_numeric($iLotteryId) || $iLotteryId <= 0 )
        {
            return $aResult;
        }
        $iLotteryId = intval($iLotteryId);
        if( empty($iCurrentIssueId) || !is_numeric($iCurrentIssueId) || $iCurrentIssueId <= 0 )
        {
            return $aResult;
        }
        $iCurrentIssueId = intval($iCurrentIssueId);
        
        // 获取彩种信息
        $oLottery = new model_lottery();
        $aLottery = $oLottery->lotteryGetOne('yearlybreakstart', 'lotteryid = ' . $iLotteryId);
        if (empty($aLottery)){
           return $aResult; 
        }
        $iNow = time();
        $iStopTime = strtotime($aLottery['yearlybreakstart']);
        $iDiff = 0;
        if ($iStopTime > $iNow){
            $iDiff = ($iStopTime - $iNow) / (24 * 3600);
        }
        $sWhere = "";
        if ($iDiff < MAX_TASK_ISSUES && $iDiff > 0){ // 休市日期距当前期不够可追号期数时
            $sWhere = " `saleend`>'".date("Y-m-d H:i:s",time())."' AND `saleend`< '" . date("Y-m-d", $iStopTime) . "'";
        } else {
            $sWhere = " `saleend`>'".date("Y-m-d H:i:s",time())."' LIMIT 0,20 ";
        }
        $sFields         = empty($sFields) ? "*" : daddslashes($sFields);
        $sSql            = " SELECT ".$sFields." FROM `issueinfo` WHERE `lotteryid`='".$iLotteryId."' 
                             AND `issueid`>='".$iCurrentIssueId."' 
                             AND " . $sWhere;
        $aResult = $this->oDB->getDataCached($sSql);
        return $aResult;
	}
	
	
	
	/**
	 * 根据彩种获取最近已开奖奖期的数据[时间由现在往回退]
	 *
	 * @author james   090808
     * @access public
	 * @param  int      $iLotteryId    彩种ID
	 * @param  string   $sFields       要获取的资料
	 * @param  string   $sCondition    附加条件
	 * @param  int      $iLimit        要获取的数量
	 * @return array
	 */
	public function & getLastIssueCode( $iLotteryId, $sFields="", $sCondition="", $iLimit=1 )
	{
	    $aResult = array();
        if( empty($iLotteryId) || !is_numeric($iLotteryId) || $iLotteryId <= 0 )
        {
            return $aResult;
        }
        $iLotteryId = intval($iLotteryId);
        if( empty($iLimit) || !is_numeric($iLimit) || $iLimit <= 0 )
        {
            return $aResult;
        }
        $iLimit = intval($iLimit);
        $sCurrentTime = date("Y-m-d H:i:s");
        $sFields = empty($sFields) ? "*" : daddslashes($sFields);
        $sSql    = " SELECT ".$sFields." FROM `issueinfo` WHERE `saleend`<='".$sCurrentTime."' AND `code`<>''
                     AND `lotteryid`='".$iLotteryId."' AND `statuscode`='2' ".$sCondition."
                     ORDER BY `issueid` DESC LIMIT 0,".$iLimit;
        $aResult = $this->oDB->getDataCached($sSql);
        return $aResult;
	}



	/**
	 * 更新奖期的相关时间
	 *
	 * @param array $aOldIssue
	 * @param string $sCondition
	 */
	function issueUpdateTime( $aOldIssue,$sCondition )
	{
		if(!isset($aOldIssue) || empty($aOldIssue))
		{
			return -1;
		}
		if(getFilterDate($aOldIssue["salestart"])!=$aOldIssue["salestart"])
		{
			return -2;
		}
		$aIssue["salestart"] = getFilterDate($aOldIssue["salestart"]);
		if(getFilterDate($aOldIssue["saleend"])!=$aOldIssue["saleend"])
		{
			return -3;
		}
		$aIssue["saleend"] = getFilterDate($aOldIssue["saleend"]);
		if(getFilterDate($aOldIssue["canneldeadline"])!=$aOldIssue["canneldeadline"])
		{
			return -4;
		}
		$aIssue["canneldeadline"] = getFilterDate($aOldIssue["canneldeadline"]);
		if(getFilterDate($aOldIssue["dynamicprizestart"])!=$aOldIssue["dynamicprizestart"])
		{
			return -5;
		}
		$aIssue["dynamicprizestart"] = getFilterDate($aOldIssue["dynamicprizestart"]);
		if(getFilterDate($aOldIssue["dynamicprizeend"])!=$aOldIssue["dynamicprizeend"])
		{
			return -6;
		}
		if(empty($sCondition))
		{
			return -7;
		}
		return $this->oDB->update("issueinfo", $aIssue, $sCondition);		
	}



	/**
	 * 删除奖期
	 *
	 * @param string $sCondition
	 * @return bool or integer
	 */
	function issueDel($sCondition)
	{
		
		if(empty($sCondition))
		{
			return FALSE;
		}
		return $this->oDB->delete('issueinfo',$sCondition."and ((`statuscode`=0) or (`statuscode`='2' and `statusbonus`='2' and `statususerpoint`='2' and `statusdeduct`='2'))");
	}



	/**
	 * 录入号码或者是验证号码
	 * @author SAUL 090804
	 * @param string $sCode
	 * @param string $sCondition
	 * @return integer
	 */
	function issueUpdateNo($sCode,$sCondition)
	{
		if(empty($sCondition))
		{ //条件为空
			return -1;
		}
		if(!is_numeric($_SESSION["admin"]))
		{ //用户错误
			return -2;
		}
		$aIssue = $this->oDB->getOne("SELECT * FROM `issueinfo` where ".$sCondition);
		if(empty($aIssue))
		{ //奖期不存在
			return -3;
		}
		if($aIssue["statuscode"] ==2)
		{ //号码状态为已验证，如果需要重新录入号码,请系统撤单之后，方可
			return -4;
		}
		if($aIssue["statuscode"] == 1)
		{ //待验证
			if($sCode == $aIssue["code"])
			{ //验证成功，需要检测两个身份是否重合
				if($aIssue["writeid"] == intval($_SESSION["admin"]))
				{ //管理员为同一个人
					return -5;
				}
				//更新相关的数据
				$aNewIssue['statuscode'] = 2;
				$aNewIssue["verifytime"]  = date("Y-m-d H:i:s", time());
				$aNewIssue["verifyid"] = intval($_SESSION["admin"]);
				$iResult = $this->oDB->update('issueinfo',$aNewIssue,$sCondition." AND `statuscode`='1'");
				if($iResult === FALSE)
				{ //更新失败
					return -6;	
				}
				else
				{ //审核成功
					$this->oDB->query("insert into `issuehistory` (`lotteryid`,`issue`,`code`) value('".$aIssue["lotteryid"]."','".$aIssue["issue"]."','".$aIssue["code"]."')");
					return -7;
				}
			}
			else
			{ //验证失败
				if($aIssue["writeid"] == intval($_SESSION["admin"]))
				{ //管理员为同一个人
					return -5;
				}
				$aNewIssue['statuscode'] = 0;
				$aNewIssue["writetime"]  = '0000-00-00 00:00:00';
				$aNewIssue["writeid"] = 0;
				$iResult = $this->oDB->update('issueinfo',$aNewIssue,$sCondition." AND `statuscode`='1'");
				if($iResult === FALSE)
				{ //号码审核不正确,更新失败
					return -8;
				}
				else
				{ //号码审核不正确.
					return -9;
				}
			}
		}
		else
		{ //首次录入号码
			$aLottery = $this->oDB->getOne("SELECT * FROM `lottery` WHERE `lotteryid`='".$aIssue["lotteryid"]."'");
			if(empty($aLottery))
			{ //彩种不存在
				return -10;
			}
			if($aLottery["edittime"]>date("H:i:s"))
			{ //还没有到录号时间
				return -11;
			}
			if(strtotime($aIssue["saleend"])>time())
			{ //彩种没有结束
				return -12;
			}//号码格式验证			
			$aLottery["numberrule"] = @unserialize($aLottery["numberrule"]);
			if($aLottery["lotterytype"]==0)
			{
				$matches =array();
			    preg_match("/^[".$aLottery["numberrule"]["startno"]."-".$aLottery["numberrule"]["endno"]."]{".$aLottery["numberrule"]["len"]."}$/",$sCode,$matches);
				if(empty($matches))
				{ //号码格式不正确
					return -13;
				}				
			}
			else
			{
			     //TODO 对其他类型彩种的号码验证，暂不支持
				return -14;
			}
			$aNewIssue["code"] = $sCode;
			$aNewIssue["writetime"] = date("Y-m-d H:i:s", time());
			$aNewIssue["writeid"] = intval($_SESSION["admin"]);
			$aNewIssue["statuscode"] = 1;
			$iResult = $this->oDB->update("issueinfo",$aNewIssue,$sCondition." AND `statuscode`='0'");
			if($iResult === FALSE)
			{ //更新号码失败
				return -15;
			}
			if($iResult === 0)
			{
				return -16;
			}
			return 1;
		}
	}



	/**
	 * 奖期清理
	 *  A:issueinfo
	 *  B:销量表信息
	 *  C:历史封锁
	 *  D:追号
	 *  E:追号详情 
	 */
	function baklog( $iDay, $sPath )
	{
		if( !is_numeric($iDay) ) 
		{
			return FALSE;
		}
		//追号20期
		if($iDay<20)
		{
			$iDay = 20;
		}
		$sDay = date("Ymd");
		//B、销量表的备份开始
		$numSales = $this->oDB->getOne("SELECT count(S.entry) AS count_sales FROM `sales` AS S"
		." LEFT JOIN `issueinfo` AS II on (II.`issue`=S.`issue` and II.`lotteryid`=S.`lotteryid`)"
		." where II.`saleend`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		$num = $numSales['count_sales'];
		$size = 50000;
		$pages = ceil($num/$size);
		$sFile = $sPath.DS."sales".DS.$sDay."_sales.gz";
		makeDir(dirname($sFile));
		$gz = gzopen($sFile,'w9');
		for($page =0 ; $page < $pages; $page++)
		{
			$FileContent = "";
			$aSales = $this->oDB->getAll("SELECT S.* FROM `sales` AS S LEFT JOIN `issueinfo` AS II on (II.`issue`=S.`issue` and II.`lotteryid`=S.`lotteryid`) where II.`saleend`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."' limit ".($page*$size).",".$size);		
			foreach($aSales as $aSale)
			{
				$keys =array();
				$values =array();
				foreach( $aSale as $key=>$value )
				{
					$keys[] = "`".$key."`";
					if(is_null($value))
					{
						$values[] = 'NULL';
					}
					else 
					{
						$values[] = "'".$this->oDB->es($value)."'";	
					}
				}
				$sql = "insert into `sales` (".join(",",$keys).") values (".join(",",$values).");";
				unset($keys);
				unset($values);
				$FileContent .= $sql."\n";
			}
			gzwrite($gz, $FileContent);
		}
		gzclose($gz);
		//删除销量表中
		$this->oDB->query("DELETE S "
		." FROM `sales` AS S LEFT JOIN `issueinfo` AS II on (II.`issue`=S.`issue` and II.`lotteryid`=S.`lotteryid`)"
		." where II.`saleend`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");		
		//B、销量表的备份结束
		//C、历史封锁表的清理开始
		$aLockTables = $this->oDB->getAll("SELECT * FROM `locksname`");
		foreach($aLockTables as $Locks)
		{
			$sTableName = $Locks["lockname"];
			$iLottery = $Locks["lotteryid"];
			$numLocks = $this->oDB->getOne("SELECT count(H.entry) AS count_locks FROM `".$sTableName."history` AS H LEFT JOIN `issueinfo` AS II on (II.`issue`=H.`issue` and II.`lotteryid`='".$iLottery."') where II.`saleend`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
			$num = $numLocks['count_locks'];
			$size = 50000;
			$pages = ceil($num/$size);
			$sFile = $sPath.DS."locks".DS.$sDay."_".$sTableName."_locks.gz";
			makeDir(dirname($sFile));
			$gz = gzopen($sFile,'w9');
			for($page =0 ; $page < $pages; $page++)
			{
				$FileContent = "";
				$aLocks = $this->oDB->getAll("SELECT H.* FROM `".$sTableName."history` AS H "
				."LEFT JOIN `issueinfo` AS II on (II.`issue`=H.`issue` and II.`lotteryid`='".$iLottery."')"
				." where II.`saleend`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."' limit ".($page*$size).",".$size);		
				foreach($aLocks as $aLock)
				{
					$keys =array();
					$values =array();
					foreach( $aLock as $key=>$value )
					{
						$keys[] = "`".$key."`";
						if(is_null($value))
						{
							$values[] = 'NULL';
						}
						else 
						{
							$values[] = "'".$this->oDB->es($value)."'";	
						}
					}
					$sql = "insert into `".$sTableName."history` (".join(",",$keys).") values (".join(",",$values).");";
					unset($keys);
					unset($values);
					$FileContent .= $sql."\n";
				}
				gzwrite($gz, $FileContent);
			}
			$this->oDB->query("DELETE H FROM `".$sTableName."history` AS H "
				."LEFT JOIN `issueinfo` AS II on (II.`issue`=H.`issue` and II.`lotteryid`='".$iLottery."')"
				." where II.`saleend`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");			
		}
		gzclose($gz);
		//C、历史封锁表的清理结束
		//E、追号详情的处理开始
		$numTaskDetails = $this->oDB->getOne("SELECT count(TD.`entry`) AS `count_taskdetails` FROM `taskdetails` AS TD"
					." left join `tasks` AS T on (T.`taskid` = TD.`taskid`)"
					." LEFT JOIN `issueinfo` AS II on (II.`issue`=TD.`issue` and T.`lotteryid`=II.`lotteryid`)"
					." where II.`saleend`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");		
		$num = $numTaskDetails["count_taskdetails"];
		$size = 50000;
		$pages = ceil($num/$size);
		$sFile = $sPath.DS."taskdetails".DS.$sDay."_taskdetails.gz";
		makeDir(dirname($sFile));
		$gz = gzopen($sFile,'w9');
		for($page =0 ; $page < $pages; $page++)
		{
			$FileContent = "";
			$aTaskdetails = $this->oDB->getAll("SELECT TD.* FROM `taskdetails` AS TD"
					." left join `tasks` AS T on (T.`taskid` = TD.`taskid`)"
					." LEFT JOIN `issueinfo` AS II on (II.`issue`=TD.`issue` and T.`lotteryid`=II.`lotteryid`)"
					." where II.`saleend`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."' limit ".($page*$size).",".$size);		
			foreach($aTaskdetails as $Taskdetails)
			{
				$keys =array();
				$values =array();
				foreach( $Taskdetails as $key=>$value )
				{
					$keys[] = "`".$key."`";
					if(is_null($value))
					{
						$values[] = 'NULL';
					}
					else 
					{
						$values[] = "'".$this->oDB->es($value)."'";	
					}
				}
				$sql = "insert into `taskdetails` (".join(",",$keys).") values (".join(",",$values).");";
				unset($keys);
				unset($values);
				$FileContent .= $sql."\n";
			}
			gzwrite($gz, $FileContent);
		}
		gzclose($gz);
		$this->oDB->query("DELETE TD FROM `taskdetails` AS TD"
					." left join `tasks` AS T on (T.`taskid` = TD.`taskid`)"
					." LEFT JOIN `issueinfo` AS II on (II.`issue`=TD.`issue` and T.`lotteryid`=II.`lotteryid`)"
					." where II.`saleend`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");		
		//E、追号详情的处理结束
		//D、追号单的处理开始
		$numTasks = $this->oDB->getOne("SELECT Count(T.`taskid`) AS `count_task` FROM `tasks` AS `T`" 
			." Left Join `issueinfo` AS `II` ON (T.`beginissue` = II.`issue` and T.`lotteryid`=II.`lotteryid`)"
			." where II.`saleend`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		$num = $numTasks['count_task'];
		$size = 50000;
		$pages = ceil($num/$size);
		$sFile = $sPath.DS."tasks".DS.$sDay."_tasks.gz";
		makeDir(dirname($sFile));
		$gz = gzopen($sFile,'w9');
		for($page =0 ; $page < $pages; $page++)
		{
			$FileContent = "";
			$aTasks = $this->oDB->getAll("SELECT T.* FROM `tasks` AS `T`" 
			." Left Join `issueinfo` AS `II` ON (T.`beginissue` = II.`issue` and T.`lotteryid`=II.`lotteryid`)"
			." where II.`saleend`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."' limit ".($page*$size).",".$size);		
			foreach($aTasks as $task)
			{
				$keys =array();
				$values =array();
				foreach( $task as $key=>$value )
				{
					$keys[] = "`".$key."`";
					if(is_null($value))
					{
						$values[] = 'NULL';
					}
					else 
					{
						$values[] = "'".$this->oDB->es($value)."'";	
					}
				}
				$sql = "insert into `tasks` (".join(",",$keys).") values (".join(",",$values).");";
				unset($keys);
				unset($values);
				$FileContent .= $sql."\n";
			}
			gzwrite($gz, $FileContent);
		}		
		gzclose($gz);
		$this->oDB->query("DELETE T FROM `tasks` AS `T`" 
			." Left Join `issueinfo` AS `II` ON (T.`beginissue` = II.`issue` and T.`lotteryid`=II.`lotteryid`)"
			." where II.`saleend`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		//D、追号单的处理结束
		//奖期部分的备份
		$numIssue = $this->oDB->getOne("SELECT count(*) as `count_issue` FROM `issueinfo` WHERE `saleend`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		$num = $numIssue['count_issue'];
		$size = 50000;
		$pages = ceil($num/$size);
		$sFile = $sPath.DS."issueinfo".DS.$sDay."_issue.gz";
		makeDir(dirname($sFile));
		$gz = gzopen($sFile,'w9');
		for($page =0 ; $page < $pages; $page++)
		{
			$FileContent = "";
			$aIssues = $this->oDB->getAll("SELECT * FROM `issueinfo` WHERE `saleend`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."' limit ".($page*$size).",".$size);		
			foreach($aIssues as $Issues)
			{
				$keys =array();
				$values =array();
				foreach( $Issues as $key=>$value )
				{
					$keys[] = "`".$key."`";
					if(is_null($value))
					{
						$values[] = 'NULL';
					}
					else 
					{
						$values[] = "'".$this->oDB->es($value)."'";	
					}
				}
				$sql = "insert into `issueinfo` (".join(",",$keys).") values (".join(",",$values).");";
				unset($keys);
				unset($values);
				$FileContent .= $sql."\n";
			}
			gzwrite($gz, $FileContent);
		}		
		gzclose($gz);
		//执行删除
		$this->oDB->query("DELETE FROM `issueinfo` WHERE `saleend`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		//奖期部分结束
		return TRUE;
	}
}
?>