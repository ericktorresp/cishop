<?php
/**
 * 文件 : /_app/model/projects.php
 * 功能 : 数据模型 - 投注方案
 * 
 * - projectsInsert                 插入方案记录
 * - projectsGetList                方案获取
 * - expandCodeInsert               写入一个号码扩展[多条记录一起写]
 * - userDiffPointInsert            写入用户返点差记录[多条记录一起写]
 * - getUserPrizesBySameCode        查询用户某一期某种玩法同一个投注号码拥有的奖金[用于奖金限额]
 * - projectGetResult               游戏记录查询
 * - ProjectEnCode                  游戏方案编号加密以及解密
 * - getProjectUserCount            统计游戏用户个数(FOR CLI)
 * - getProjectTotal                统计游戏方案个数(FOR CLI)
 * - getProjectBonusDescription     获取奖金描述
 * - getExtendCode                  获取扩展号码
 * - Projectback                    方案备份
 * - ExtendCodeBack                 扩展号码备份
 * - UserDiffback                   扩展号码备份
 * - loadCheckDataFormFile          导入备份数据文件
 * - checkProject                   方案核对比较
 * - bakLog                         方案清理
 * - getTopProxyData                获取总代(本人+下级) 的数据集
 * 
 * @author     james    090915
 * @version    1.2.0
 * @package    lowgame     
 */

class model_projects extends basemodel
{
	/**
	 * 插入方案记录
	 *
	 * @author james    090810
	 * @access public
	 * @param  array    $aArr
	 * @return int    成功返回插入的ID，失败返回0  到 -12
	 */
	public function projectsInsert( $aArr=array() )
	{
		//01：先进行数据检查
		if( empty($aArr) || !is_array($aArr) )
		{
			return 0;
		}
		if( empty($aArr['userid']) || !is_numeric($aArr['userid']) || $aArr['userid'] <=0 )
		{//用户ID
			return -1;
		}
		$aArr['userid'] = intval($aArr['userid']);
	    if( empty($aArr['taskid']) || !is_numeric($aArr['taskid']) || $aArr['taskid'] <=0 )
        {//追号ID
            $aArr['taskid'] = 0;
        }
        $aArr['taskid'] = intval($aArr['taskid']);
        if( empty($aArr['lotteryid']) || !is_numeric($aArr['lotteryid']) || $aArr['lotteryid'] <=0 )
        {//彩种ID
            return -2;
        }
        $aArr['lotteryid'] = intval($aArr['lotteryid']);
        if( empty($aArr['methodid']) || !is_numeric($aArr['methodid']) || $aArr['methodid'] <=0 )
        {//玩法ID
            return -3;
        }
        $aArr['methodid'] = intval($aArr['methodid']);
        if( empty($aArr['issue']) )
        {//奖期
        	return -4;
        }
        $aArr['issue']          = daddslashes($aArr['issue']);
        $aArr['isdynamicprize'] = (isset($aArr['isdynamicprize']) && intval($aArr['isdynamicprize']) > 0)
                                    ? 1 : 0;
        $aArr['bonus']          = 0;    //实际派发奖金初始为0
        if( !isset($aArr['code']) || $aArr['code'] == "" )
        {//购买号码原复式
            return -5;
        }
        $aArr['code'] = daddslashes($aArr['code']);
	    if( empty($aArr['singleprice']) || !is_numeric($aArr['singleprice']) || $aArr['singleprice'] <=0 )
        {//单倍价格
            return -6;
        }
        $aArr['singleprice'] = number_format(intval($aArr['singleprice']),4, '.', '');
        if( empty($aArr['multiple']) || !is_numeric($aArr['multiple']) || $aArr['multiple'] <=0 )
        {//倍数
            return -7;
        }
        $aArr['multiple'] = intval($aArr['multiple']);
        if( empty($aArr['totalprice']) || !is_numeric($aArr['totalprice']) || $aArr['totalprice'] <=0 )
        {//总共价格
            return -8;
        }
        $aArr['totalprice'] = number_format(intval($aArr['totalprice']),4, '.', '');
        if( empty($aArr['lvtopid']) || !is_numeric($aArr['lvtopid']) || $aArr['lvtopid'] <=0 )
        {//总代ID
            return -9;
        }
        $aArr['lvtopid'] = intval($aArr['lvtopid']);
        if( !isset($aArr['lvtoppoint']) || !is_numeric($aArr['lvtoppoint']) || $aArr['lvtoppoint'] <0 )
        {//总代返点
            return -10;
        }
        $aArr['lvtoppoint'] = number_format(floatval($aArr['lvtoppoint']),3, '.', '');
        if( empty($aArr['lvproxyid']) || !is_numeric($aArr['lvproxyid']) || $aArr['lvproxyid'] <=0 )
        {//一代ID
            return -11;
        }
        $aArr['lvproxyid']   = intval($aArr['lvproxyid']);
        $aArr['writetime']   = date("Y-m-d H:i:s", time());//写入方案时间
        $aArr['iscancel']    = 0;      //是否撤单，默认为未撤单
        $aArr['isgetprize']  = 0;      //中奖判断，默认为未判断
        $aArr['prizestatus'] = 0;      //派奖状态，默认为未派奖
        if( empty($aArr['userip']) )
        {//用户真实IP
        	$aArr['userip'] = getRealIP();
        }
        $aArr['userip'] = daddslashes($aArr['userip']);
        if( empty($aArr['cdnip']) )
        {//CDN IP
            $aArr['cdnip'] = $_SERVER['REMOTE_ADDR'];
        }
        $aArr['cdnip'] = daddslashes($aArr['cdnip']);
        //02:购建加密KEY
        $aMd5Data      = array( 'userid'        => $aArr['userid'],
                                'taskid'        => $aArr['taskid'],
                                'lotteryid'     => $aArr['lotteryid'],
                                'methodid'      => $aArr['methodid'],
                                'issue'         => $aArr['issue'],
                                'isdynamicprize'=> $aArr['isdynamicprize'],
                                'code'          => $aArr['code'],
                                'singleprice'   => $aArr['singleprice'],
                                'multiple'      => $aArr['multiple'],
                                'totalprice'    => $aArr['totalprice'],
                                'lvtopid'       => $aArr['lvtopid'],
                                'lvtoppoint'    => $aArr['lvtoppoint'],
                                'lvproxyid'     => $aArr['lvproxyid'],
						        'iscancel'      => 0,
						        'isgetprize'    => 0,
						        'prizestatus'   => 0
                               );
        ksort($aMd5Data);
        $aArr['updatetime'] = date("Y-m-d H:i:s");
        $aArr['hashvar'] = md5( serialize($aMd5Data) );
        $mResult = $this->oDB->insert( 'projects', $aArr );
        if( empty($mResult) )
        {//操作数据库失败
        	return -12;
        }
        return $mResult;
	}

	
    /**
     * 方案获取
     *
     * @param string $sFields
     * @param string $sCondition
     * @param string $sOrderBy
     * @param integer $iPageRecord
     * @param integer $iCurrentPage
     */
    function projectsGetList($sFields='', $sCondition='', $sOrderBy='', $iPageRecord=0, $iCurrentPage=0)
    {
        if(empty($sFields))
        {
            $sFields ="*";
        }
        if(empty($sCondition))
        {
            $sCondition =" 1 ";
        }
        $iPageRecord = is_numeric($iPageRecord)?intval($iPageRecord):0;
        if($iPageRecord<=0)
        {
            $iPageRecord = 0;
        }
        $sTableName = " `projects` "; 
        if($iPageRecord==0)
        {
            if(!empty($sOrderBy))
            {
                $sOrderBy = " ORDER BY " . $sOrderBy;
            }
            return $this->oDB->getAll("SELECT ".$sFields." FROM ".$sTableName." WHERE ".$sCondition. $sOrderBy);
        }
        return $this->oDB->getPageResult($sTableName,$sFields,$sCondition,$iPageRecord,$iCurrentPage,$sOrderBy);
    }


	/**
	 * 写入一个号码扩展[多条记录一起写]
	 *
	 * @author james    090810
     * @access public
	 * @param  array    $aArrData  二维数组
	 * @return int     成功返回TRUE，失败返回FALSE
	 */
	public function expandCodeInsert( $aArrData =array() )
	{
		//01:数据检查
		if( empty($aArrData) || !is_array($aArrData) )
		{
			return FALSE;
		}
		$aValues = array();
		foreach( $aArrData as $aArr )
		{
			if( empty($aArr['projectid']) || !is_numeric($aArr['projectid']) || $aArr['projectid'] <=0 )
	        {//方案ID
	            return FALSE;
	        }
	        $aArr['projectid'] = intval($aArr['projectid']);
	        $aArr['isspecial'] = (isset($aArr['isspecial']) && intval($aArr['isspecial'])>0) ? 1 : 0;
	        if( empty($aArr['level']) || !is_numeric($aArr['level']) || $aArr['level'] <=0 )
	        {//奖金级别
	            return FALSE;
	        }
	        $aArr['level'] = intval($aArr['level']);
	        if( empty($aArr['codetimes']) || !is_numeric($aArr['codetimes']) || $aArr['codetimes'] <=0 )
            {//号码倍数
                return FALSE;
            }
            $aArr['codetimes'] = intval($aArr['codetimes']);
	        if( empty($aArr['prize']) || !is_numeric($aArr['prize']) || $aArr['prize'] <=0 )
	        {//奖金
	            return FALSE;
	        }
	        $aArr['prize'] = number_format(floatval($aArr['prize']), 4, '.', '');
	        if( !isset($aArr['expandcode']) || $aArr['expandcode'] == "" )
	        {//号码扩展
	            return FALSE;
	        }
	        $aArr['expandcode'] = daddslashes($aArr['expandcode']);
	        //02:构建加密KEY
	        $aMd5Data = array(
	                        'projectid'    => $aArr['projectid'],
	                        'isspecial'    => $aArr['isspecial'],
	                        'level'        => $aArr['level'],
	                        'codetimes'    => $aArr['codetimes'],
	                        'prize'        => $aArr['prize'],
	                        'expandcode'   => $aArr['expandcode']
	                    );
	        ksort($aMd5Data);
	        $aArr['hashvar'] = md5( serialize($aMd5Data) );
	        $aValues[]       = "('".$aArr['projectid']."','".$aArr['isspecial']."','".$aArr['level']."',
	               '".$aArr['codetimes']."','".$aArr['prize']."','".$aArr['expandcode']."','".date('Y-m-d H:i:s')."','".$aArr['hashvar']."')";
		}
        //构造SQL语句
        $sSql = " INSERT INTO `expandcode`(`projectid`,`isspecial`,`level`,`codetimes`,`prize`,`expandcode`,`updatetime`,`hashvar`) 
                  VALUES". implode(",", $aValues);
        $this->oDB->query( $sSql );
        if( $this->oDB->errno() > 0 )
        {//操作数据库失败
            return FALSE;
        }
        return TRUE;
	}
	
	
	
	/**
	 * 写入用户返点差记录[多条记录一起写]
	 *
	 * @author james    090811
     * @access public
	 * @param  array    $aArrData  二维数组
     * @return int     成功返回TRUE，失败返回FALSE
	 */
	public function userDiffPointInsert( $aArrData=array() )
	{
	    //01:数据检查
        if( empty($aArrData) || !is_array($aArrData) )
        {
            return FALSE;
        }
        $aValues = array();
        foreach( $aArrData as $aArr )
        {
            if( empty($aArr['userid']) || !is_numeric($aArr['userid']) || $aArr['userid'] <= 0 )
            {//用户ID
                return FALSE;
            }
            $aArr['userid'] = intval($aArr['userid']);
            if( empty($aArr['projectid']) || !is_numeric($aArr['projectid']) || $aArr['projectid'] <=0 )
            {//方案ID
                return FALSE;
            }
            $aArr['projectid'] = intval($aArr['projectid']);
            if( empty($aArr['diffmoney']) || !is_numeric($aArr['diffmoney']) || $aArr['diffmoney'] <=0 )
            {//返点金额
                return FALSE;
            }
            $aArr['diffmoney'] = number_format(floatval($aArr['diffmoney']), 4, '.', '');
            if( empty($aArr['diffpoint']) || !is_numeric($aArr['diffpoint']) || $aArr['diffpoint'] <=0 )
            {//返点
                return FALSE;
            }
            $aArr['diffpoint']    = number_format(floatval($aArr['diffpoint']), 3, '.', '');
            $aArr['status']       = (isset($aArr['status']) && intval($aArr['status']) > 0)? 1 : 0;
            $aArr['cancelstatus'] = 0;
            $aValues[]       = "('".$aArr['userid']."','".$aArr['projectid']."','".$aArr['diffmoney']."',
                                 '".$aArr['diffpoint']."','".$aArr['status']."','".$aArr['cancelstatus']."')";
        }
        //构造SQL语句
        $sSql = " INSERT INTO `userdiffpoints`(`userid`,`projectid`,`diffmoney`,`diffpoint`,`status`,`cancelstatus`) 
                  VALUES".implode(",",$aValues);
        $this->oDB->query( $sSql );
        if( $this->oDB->errno() > 0 )
        {//操作数据库失败
            return FALSE;
        }
        return TRUE;
	}
	
	
	/**
	 * 查询用户某一期某种玩法同一个投注号码拥有的奖金[用于奖金限额]
	 *
	 * @author james   090814
	 * @access public
	 * @param  int      $iUserId
	 * @param  string   $sIssue
	 * @param  int      $iLotteryId
	 * @param  int      $iMethodId
	 * @param  string   $sCode
	 * @return mixed   失败返回FALSE，成功返回查询结果集
	 */
	public function getUserPrizesBySameCode( $iUserId, $iLotteryId, $iMethodId, $sIssue, $sCode )
	{
		//01：数据检查
		if( empty($iUserId) || !is_numeric($iUserId) || $iUserId <= 0 )
		{//用户ID
			return FALSE;
		}
		$iUserId = intval($iUserId);
		if( empty($sIssue) )
		{//奖期
			return FALSE;
		}
		$sIssue = daddslashes($sIssue);
		if( empty($iLotteryId) || !is_numeric($iLotteryId) || $iLotteryId <= 0 )
		{//彩种ID
			return FALSE;
		}
		$iLotteryId = intval($iLotteryId);
		if( empty($iMethodId) || !is_numeric($iMethodId) || $iMethodId <= 0 )
		{//玩法ID
			return FALSE;
		}
		$iMethodId = intval($iMethodId);
	    if( $sCode == "" )
        {//购买的原式号码
            return FALSE;
        }
        $sCode = daddslashes($sCode);
        //02: 构造SQL查询语句
        $sSql = " SELECT e.`projectid`,e.`prize`
                  FROM `expandcode` AS e LEFT JOIN `projects` AS p ON e.`projectid`=p.`projectid`
                  WHERE p.`userid`='".$iUserId."' AND p.`lotteryid`='".$iLotteryId."' 
                  AND p.`methodid`='".$iMethodId."' AND p.`issue`='".$sIssue."' AND p.`code`='".$sCode."'
                  AND p.`iscancel`='0' AND p.`isgetprize`='0' AND p.`prizestatus`='0' ";
        $aResult = $this->oDB->getAll( $sSql );
        if( $this->oDB->errno() > 0 )
        {//数据库错误
        	return FALSE;
        }
        return $aResult;
	}


	/**
	 * 游戏记录查询
	 * @author SAUL 090811
	 * @param integer $iUserId
	 * @param bool $bAllChild
	 * @param string $sField
	 * @param string $sCondtion
	 * @param string $sOrderBy
	 * @param integer $iPageRecord
	 * @param integer $iCurrPage
	 * @return array
	 */
	function & projectGetResult( $iUserId, $bAllChild = TRUE, $sField ="", $sCondtion="", $sOrderBy="",
	                          $iPageRecord=25, $iCurrPage=1 )
    {
    	$aArr       = array( "affects"=>0, "results"=>array() );
    	$sTableName = " `projects` AS P "
    				 ." LEFT JOIN `usertree` AS UT ON (P.`userid`=UT.`userid`) "
    				 ." LEFT JOIN `method` AS M ON (P.`methodid`=M.`methodid`) "
    				 ." LEFT JOIN `lottery` AS L ON (L.`lotteryid`=P.`lotteryid`) "
    				 ." LEFT JOIN `issueinfo` AS I ON (P.`lotteryid`=I.`lotteryid` AND P.`issue`=I.`issue`)";
    	if( empty($iUserId) && !is_numeric($iUserId) )
    	{
    		return $aArr;
    	}
    	$iUserId = intval($iUserId);
    	$sWhere  = " 1 ";
    	if( $bAllChild )
    	{
    		if( $iUserId > 0 )
    		{
    			$sWhere .=" AND (FIND_IN_SET(".intval($iUserId).",UT.`parenttree`) OR (UT.`userid`='".$iUserId."'))";
    		}
    	}
    	else
    	{
    		if( $iUserId > 0 )
    		{
    			$sWhere .=" AND P.`userid`='".$iUserId."'";
    		}
    	}
    	if( empty($sField) )
    	{
    		$sField = "P.*,L.`cnname`,M.`methodname`,UT.`username`,I.`code` AS `nocode`";	
    	}
    	else
    	{
    		$sField = daddslashes($sField);
    	}
    	if( !empty($sCondtion) )
    	{
    		$sWhere .= $sCondtion;
    	}
    	$iPageRecord = isset($iPageRecord) && is_numeric($iPageRecord) ? intval($iPageRecord) : 0;
    	$sOrderBy    = empty($sOrderBy) ? " " : " Order BY ".$sOrderBy;
    	if( $iPageRecord == 0 )
    	{
    		return $this->oDB->getAll("SELECT ".$sField." FROM ".$sTableName." where ".$sWhere.$sOrderBy);
    	}
    	$iCurrPage = isset($iCurrPage) && is_numeric($iCurrPage) ? intval($iCurrPage) : 1;
    	return $this->oDB->getPageResult( $sTableName, $sField, $sWhere, $iPageRecord, $iCurrPage, $sOrderBy );
    }



    /**
     * 游戏方案编号加密以及解密
     *
     * @param string $sString
     * @param string $sOption
     */
    static function ProjectEnCode( $sString, $sOption="DECODE" )
    {
    	if( $sOption == "DECODE" )
		{//解密
			$aData = explode( "V", $sString );
			if( !isset($aData[1]) )
			{
				return 0;
			}
			for( $i=0; $i<strlen($aData[1]); $i++ )
			{
				$ascii = ord($aData[1][$i]);
				if( $ascii > 74 )
				{
					$ascii = 42;
				}
				else
				{
					$ascii -= 17;
				}
				$aData[1][$i] = chr($ascii);
			}
			$aData[1] = str_replace( "*", "", $aData[1] );
			return is_numeric($aData[1]) ? $aData[1] : 0;
		}
		else 
		{//加密
			$aData = explode( "-", $sString );
			if( !isset($aData[1]) )
			{
				return $aData;
			}
			for( $i=strlen($aData[1]); $i<=8; $i++ )
			{
				$aData[1] .= "*";
			}
			$zero = 0;
			for( $i=0; $i<strlen($aData[1]); $i++ )
			{
				$ascii = ord($aData[1][$i]);
				if( $ascii == 42 )
				{
					$ascii += $zero + 33;
					$zero++;
				}
				else
				{
					$ascii += 17;
				}
				$aData[1][$i] = chr($ascii);
			}
			return $aData[0]."V".$aData[1];
		}
    }



    /**
     * 统计游戏用户个数(FOR CLI)
     * @author SAUL
     */
    function getProjectUserCount()
    {
    	$aResult = $this->oDB->getOne(" SELECT COUNT(DISTINCT `userid`) AS `TOMCOUNT` FROM `projects` 
  	                                WHERE DATE(`writetime`)='".date("Y-m-d")."' ");
    	return $this->oDB->ar()>0 ? $aResult['TOMCOUNT'] : 0;
    }



    /**
     * 统计游戏方案个数(FOR CLI)
     * @author SAUL
     */
    function getProjectTotal()
    {
    	$aResult = $this->oDB->getOne(" SELECT COUNT(`projectid`) AS `TOMCOUNT` FROM `projects` 
    	                                WHERE DATE(`writetime`)='".date("Y-m-d")."'");
    	return $this->oDB->ar()>0 ? $aResult['TOMCOUNT'] : 0;
    }



    /**
     * 获取奖金描述
     * @author SAUL
     * @param integer $iProject
     */
    function getProjectBonusDescription( $aProject )
    {
    	if( is_array($aProject) && $aProject["isgetprize"] == 2 )
    	{
    		$sSql = " SELECT `description` FROM `orders` WHERE `projectid`='".intval($aProject["projectid"])."' 
    		          AND `amount`='".number_format($aProject["bonus"],4,".","")."' 
    		          AND `ordertypeid`='5'";
	    	$aArr = $this->oDB->getOne( $sSql );
	    	if( !empty($aArr) )
	    	{
	    		return $aArr["description"];
	    	}
    	}
    	return "";
    }



    /**
     * 获取扩展号码
     * @author saul
     * @param string $sField
     * @param string $sCondition
     * @return array
     */
    function getExtendCode( $sField="", $sCondition="", $sOrderBy="" ,$iPageRecord=0, $iCurrPage = 0)
    {
    	if( empty($sField) )
    	{
    		$sField = "*";
    	}
    	else
    	{
    		$sField = daddslashes( $sField );
    	}
    	if( empty($sCondition) )
    	{
    		$sCondition = " 1 ";
    	}
    	if( !empty($sOrderBy) )
    	{
    		$sOrderBy = " ORDER BY ".$sOrderBy;
    	}
    	$iPageRecord = is_numeric($iPageRecord) ? intval($iPageRecord) : 0;
    	if( $iPageRecord == 0 )
    	{
    		return $this->oDB->getAll( "SELECT ".$sField." FROM `expandcode` where ".$sCondition .$sOrderBy); 
    	}
    	return $this->oDB->getPageResult( "expandcode", $sField, $sCondition, $iPageRecord, $iCurrPage, $sOrderBy );
    }



    /**
     * 方案备份
     *
     * @param unknown_type $iLottery
     * @param unknown_type $iIssue
     * @param unknown_type $sFileName
     */
    function Projectback( $iLottery, $iIssue, $sFileName )
    {
    	$sSql          = " SELECT COUNT(*) AS TOMCOUNT FROM `projects` WHERE `issue`='".$iIssue."' 
    	                   AND `lotteryid`='".$iLottery."' ";
    	$aProjectCount = $this->oDB->getOne($sSql);
    	$iCount        = $aProjectCount["TOMCOUNT"];
    	makeDir( dirname($sFileName) );
    	$fp            = gzopen($sFileName, "wb9" ); //打开文件
    	$iSize         = 50000;
    	$iPages        = ceil($iCount/$iSize);
    	for( $i =0; $i<$iPages; $i++ )
    	{
    		$sProjectSql = " SELECT * FROM `projects` WHERE `issue`='".$iIssue."' AND `lotteryid`='".$iLottery."'"
    				      ." ORDER BY `projectid` ASC LIMIT ".($i*$iSize).",". $iSize;
    		$this->oDB->query( $sProjectSql );
    		$sContent    = "";
    		while( ($aProject = $this->oDB->fetchArray()) )
    		{  //生成SQL
    			$sqlCheck =  "insert into `projectscheck` set ";
    			$aTemp    = array();
    			foreach( $aProject as $sKey=>$sValue )
    			{
    				if( $sValue==NULL )
    				{
    					$aTemp[] = "`check".$sKey."` = NULL";
    				}
    				else
    				{
    					$aTemp[] = "`check".$sKey."` = '".$this->oDB->es($sValue)."'";
    				}
    			}
    			$sqlCheck .= join(",",$aTemp);
    			$sqlCheck .= ";\n";
    			$sContent .= $sqlCheck; 
    		}
    		//写入文件
    		gzwrite( $fp, $sContent );
    		unset( $sContent );
    	}
    	gzclose($fp);
    }



    /**
     *  扩展号码备份
     *
     * @param integer $iLottery
     * @param integer $iIssue
     * @param string  $sFileName
     */
    function ExtendCodeBack( $iLottery, $iIssue, $sFileName )
    {
    	$sSql = "SELECT COUNT(E.entry) AS TOMCOUNT FROM `expandcode` AS E LEFT JOIN `projects` AS P "
    		." ON (E.`projectid` = P.`projectid`) WHERE P.`issue`='".$iIssue."' AND P.`lotteryid`='".$iLottery."'";
    	$aExtendCodeCount = $this->oDB->getOne($sSql);
    	$iCount           = $aExtendCodeCount["TOMCOUNT"]; 
    	makeDir(dirname($sFileName));  	
    	$fp     = gzopen( $sFileName, "wb9" ); //打开文件
    	$iSize  = 50000;
    	$iPages = ceil( $iCount/$iSize );
    	for( $i =0; $i<$iPages; $i++ )
    	{
    		$sExtendCodeSql = "SELECT E.* FROM `expandcode` AS E LEFT JOIN `projects` AS P "
    		." ON (E.`projectid` = P.`projectid`) WHERE P.`issue`='".$iIssue."' AND P.`lotteryid`='".$iLottery."'"
    		." ORDER BY E.`entry` ASC LIMIT ".($i*$iSize).",". $iSize;
    		$this->oDB->query( $sExtendCodeSql );
    		$sContent     = "";
    		while( ($aExtendCode = $this->oDB->fetchArray()) )
    		{ //生成SQL
    			$sqlCheck =  "insert into `expandcodecheck` set ";
    			$aTemp    = array();
    			foreach( $aExtendCode as $sKey=>$sValue )
    			{
    				if( $sValue==NULL )
    				{
    					$aTemp[] = "`check".$sKey."` = NULL";
    				}
    				else
    				{
    					$aTemp[] = "`check".$sKey."` = '".$this->oDB->es($sValue)."'";
    				}
    			}
    			$sqlCheck .= join(",",$aTemp);
    			$sqlCheck .= ";\n";
    			$sContent .= $sqlCheck; 
    		}
    		//写入文件
    		gzwrite( $fp, $sContent );
    		unset($sContent);
    	}
    	gzclose($fp);
    }

   

    /**
     * 扩展号码备份
     *
     * @param integer $iLottery
     * @param integer $iIssueId
     * @param string  $sFileName
     */
    function UserDiffback( $iLottery, $iIssue, $sFileName )
    {
    	$sSql = "SELECT COUNT(U.entry) AS TOMCOUNT FROM `userdiffpoints` AS U LEFT JOIN `projects` AS P "
    		." ON (U.`projectid` = P.`projectid`) WHERE P.`issue`='".$iIssue."' AND P.`lotteryid`='".$iLottery."'";
    	$aUserDiffPointCount = $this->oDB->getOne($sSql);
    	$iCount              = $aUserDiffPointCount["TOMCOUNT"];
    	makeDir(dirname($sFileName)); 	
    	$fp     = gzopen( $sFileName, "wb9" ); //打开文件
    	$iSize  = 50000;
    	$iPages = ceil( $iCount/$iSize );
    	for( $i =0; $i<$iPages; $i++ )
    	{
    		$sUserDiffPointSql = "SELECT U.* FROM `userdiffpoints` AS U LEFT JOIN `projects` AS P "
    		." ON (U.`projectid` = P.`projectid`) WHERE P.`issue`='".$iIssue."' AND P.`lotteryid`='".$iLottery."'"
    		." ORDER BY U.`entry` ASC LIMIT ".($i*$iSize).",". $iSize;
    		$this->oDB->query( $sUserDiffPointSql );
    		$sContent = "";
    		while( ($aUserDiffPoints = $this->oDB->fetchArray()) )
    		{ //生成SQL
    			$sqlCheck =  "INSERT INTO `userdiffpointscheck` set ";
    			$aTemp    = array();
    			foreach( $aUserDiffPoints as $sKey=>$sValue )
    			{
    				if( $sValue==NULL )
    				{
    					$aTemp[] = "`check".$sKey."` = NULL";
    				}
    				else
    				{
    					$aTemp[] = "`check".$sKey."` = '".$this->oDB->es($sValue)."'";
    				}
    			}
    			$sqlCheck .= join(",",$aTemp);
    			$sqlCheck .= ";\n";
    			$sContent .= $sqlCheck; 
    		}
    		//写入文件
    		gzwrite( $fp, $sContent );
    		unset($sContent);
    	}
    	gzclose($fp);
    }



    /**
     * 导入备份数据文件
     *
     * @param string $sFileName
     */
    function loadCheckDataFormFile( $sFileName )
    {
    	if( file_exists($sFileName) )
    	{
    		$fp       = gzopen( $sFileName, "r" );
    		$sContent = "";
    		while( ($sTemp = gzread($fp,1024)) )
    		{
    			$sContent .= $sTemp; 
    		}
    		gzclose($fp);
    		$this->oDB->query("DELETE FROM `projectscheck`");
    		if( $this->oDB->errno() > 0 )
    		{
   				return -($this->oDB->errno());
    		}
    		$aSQL = explode( ";\n", $sContent );
    		foreach( $aSQL as $sSQL )
    		{
    			if( $sSQL != "" )
    			{
    				$this->oDB->query($sSQL);
    				if( $this->oDB->ar() < 1 )
    				{
    					return 0;
    				}
    			}
    		}
    		return 2;
    	}
    	return 1;
    }



    /**
     * 方案核对比较
     *
     * @param integer $iLottery
     * @param issue $sIssue
     * @param array $aCheck
     */
    function checkProject( $iLottery, $sIssue, $aCheck )
    {
    	$aResult   = array();
    	$aProjects = $this->oDB->getAll("SELECT * FROM `projects` AS P "
    			                       ."LEFT JOIN `projectscheck` AS PC on (P.`projectid`=PC.`checkprojectid`)"
    			                       ." WHERE P.`lotteryid`='".$iLottery."' AND P.`issue`='".$sIssue."'");
    	foreach( $aProjects as $aProject )
    	{
    		foreach( $aCheck as $sKey )
    		{
    			if( $aProject[$sKey] != $aProject["check".$sKey] )
    			{
    				$aResult["PTPC"][$aProject["projectid"]][$sKey][0] = $aProject[$sKey];
    				$aResult["PTPC"][$aProject["projectid"]][$sKey][1] = $aProject["check".$sKey];
    			}
    		}
    	}
    	$aProjects = $this->oDB->getAll("SELECT * FROM `projects` AS P "
    			."RIGHT JOIN `projectscheck` AS PC on (P.`projectid`=PC.`checkprojectid`)");
    	foreach( $aProjects as $aProject )
    	{
    		foreach( $aCheck as $sKey )
    		{
    			if( $aProject[$sKey] != $aProject["check".$sKey] )
    			{
    				$aResult["PCTP"][$aProject["projectid"]][$sKey][0] = $aProject["check".$sKey];
    				$aResult["PCTP"][$aProject["projectid"]][$sKey][1] = $aProject[$sKey];
    			}
    		}
    	}
    	return $aResult;
    }



    /**
     * 方案清理
     * @param integer $iDay
     * @param string  $sPath
     * @return bool
     * 
     * 涉及到的相关表
     *  projects
     *  userdiffpoints
     *  expandcode
     */
    function bakLog($iDay,$sPath)
    {
    	if( !is_numeric($iDay) ) 
		{
			return FALSE;
		}
		$iDay = intval($iDay);
		//追号20期
		if( $iDay < 5 )
		{
			$iDay = 5;
		}
		$sDay = date("Ymd");
    	//扩展号码表
    	$numCodes = $this->oDB->getOne("SELECT COUNT(EC.entry) AS `numCodes` FROM `expandcode` AS EC"
		                        ." LEFT JOIN `projects` AS P ON (P.`projectid`=EC.`projectid`)"
		                        ." WHERE P.`writetime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		$num   = $numCodes['numCodes'];
		$size  = 50000;
		$pages = ceil( $num/$size );
		$sFile = $sPath.DS."expandcode".DS.$sDay."_expandcode.gz";
		makeDir(dirname($sFile));
		$gz    = gzopen($sFile,'w9');
		for( $page =0 ; $page < $pages; $page++ )
		{
			$FileContent = "";
			$aSales = $this->oDB->getAll("SELECT EC.* FROM `expandcode` AS EC"
		                                ." LEFT JOIN `projects` AS P ON (P.`projectid`=EC.`projectid`)"
		                                ." WHERE P.`writetime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))
		                                ."' LIMIT ".($page*$size).",".$size);		
			foreach( $aSales as $aSale )
			{
				$keys   = array();
				$values = array();
				foreach( $aSale as $key=>$value )
				{
					$keys[] = "`".$key."`";
					if( is_null($value) )
					{
						$values[] = 'NULL';
					}
					else 
					{
						$values[] = "'".$this->oDB->es($value)."'";	
					}
				}
				$sql = "INSERT INTO `expandcode` (".join(",",$keys).") VALUES (".join(",",$values).");";
				unset($keys);
				unset($values);
				$FileContent .= $sql."\n";
			}
			gzwrite($gz, $FileContent);
		}
		gzclose($gz);
		//删除扩展号码
		$this->oDB->query(" DELETE EC FROM `expandcode` AS EC"
		                 ." LEFT JOIN `projects` AS P on (P.`projectid`=EC.`projectid`)"
		                 ." WHERE P.`writetime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		//用户返点差表
		$numUserDiff = $this->oDB->getOne("SELECT COUNT(UD.entry) AS `count_UD` FROM `userdiffpoints` AS UD"
			                     ." LEFT JOIN `projects` AS P ON (UD.`projectid`=P.`projectid`)"
			                     ." WHERE P.`writetime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		$num   = $numUserDiff['count_UD'];
		$size  = 50000;
		$pages = ceil( $num/$size );
		$sFile = $sPath.DS."userdiffpoints".DS.$sDay."_userdiffpoints.gz";
		makeDir(dirname($sFile));
		$gz    = gzopen( $sFile, 'w9' );
		for( $page =0 ; $page < $pages; $page++ )
		{
			$FileContent = "";
			$aSales = $this->oDB->getAll(" SELECT UD.* FROM `userdiffpoints` AS UD"
			                            ." LEFT JOIN `projects` AS P ON (UD.`projectid`=P.`projectid`)"
			                            ." WHERE P.`writetime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))
			                            ."' LIMIT ".($page*$size).",".$size);		
			foreach( $aSales as $aSale )
			{
				$keys   = array();
				$values = array();
				foreach( $aSale as $key=>$value )
				{
					$keys[] = "`".$key."`";
					if( is_null($value) )
					{
						$values[] = 'NULL';
					}
					else 
					{
						$values[] = "'".$this->oDB->es($value)."'";	
					}
				}
				$sql = "insert into `userdiffpoints` (".join(",",$keys).") values (".join(",",$values).");";
				unset($keys);
				unset($values);
				$FileContent .= $sql."\n";
			}
			gzwrite($gz, $FileContent);
		}
		gzclose($gz);
		//执行删除
		$this->oDB->query(" DELETE UD FROM `userdiffpoints` AS UD"
		                 ." LEFT JOIN `projects` AS P ON (UD.`projectid`=P.`projectid`)"
		                 ." WHERE P.`writetime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		$numProjects = $this->oDB->getOne("SELECT COUNT(P.`projectid`) AS `count_projects` FROM `projects` AS P"
		                         ." WHERE P.`writetime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		$num   = $numProjects["count_projects"];
		$size  = 50000;
		$pages = ceil( $num/$size );
		$sFile = $sPath.DS."projects".DS.$sDay."_projects.gz";
		makeDir(dirname($sFile));
		$gz    = gzopen( $sFile, 'w9' );
		for( $page =0 ; $page < $pages; $page++ )
		{
			$FileContent = "";
			$aSales = $this->oDB->getAll(" SELECT P.* FROM `projects` AS P "
			                            ." WHERE P.`writetime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))
			                            ."' LIMIT ".($page*$size).",".$size);		
			foreach( $aSales as $aSale )
			{
				$keys   = array();
				$values = array();
				foreach( $aSale as $key=>$value )
				{
					$keys[] = "`".$key."`";
					if( is_null($value) )
					{
						$values[] = 'NULL';
					}
					else 
					{
						$values[] = "'".$this->oDB->es($value)."'";	
					}
				}
				$sql = "insert into `projects` (".join(",",$keys).") values (".join(",",$values).");";
				unset($keys);
				unset($values);
				$FileContent .= $sql."\n";
			}
			gzwrite($gz, $FileContent);
		}
		gzclose($gz);
		//执行删除
		$this->oDB->query(" DELETE FROM `projects` WHERE `writetime`<'".
		                   date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		return true;
    }



    /**
     * 获取总代(本人+下级) 的数据集
     *
     * @param datetime $tBeginTime
     * @param datetime $tEndTime
     * @param string   $sAction     buy,point,bingo 
     * @return array
     * @author Tom 090903
     */
    public function & getTopProxyData( $tBeginTime=0, $tEndTime=0, $sAction='buy', $sCondition='' )
    {
        $sWhere = '';
        $sSql   = '';
        if( $tBeginTime!=0 )
        {
            $sWhere .= " AND p.`writetime` >= '".daddslashes($tBeginTime)."' ";
        }
        if( $tEndTime!=0 )
        {
            $sWhere .= " AND p.`writetime` <= '".daddslashes($tEndTime)."' ";
        }
        $sWhere .= $sCondition;
        if( $tBeginTime!=0 && $tEndTime!=0 )
        { // 如果同时存在2个时间, 则使用 BETWEEN (效率更高些)
            $sWhere = " AND p.`writetime` BETWEEN '".daddslashes($tBeginTime)."' AND '".daddslashes($tEndTime)."' ";
        }

        if( $sAction == 'buy' )
        { // 团队总代购费  = 未撤 + 真实扣款
            $sSql = "SELECT lvtopid, SUM(`totalprice`) AS TOMSUM FROM `projects` p  ".
                    " WHERE p.`iscancel` = 0 AND p.`isdeduct` = 1 ".$sWhere . " GROUP BY `lvtopid` ";
        }
        if( $sAction == 'bingo' )
        { // 团队中奖总额  = 未撤 + 已中 + 已派
            $sSql = "SELECT lvtopid, SUM(`bonus`) AS TOMSUM FROM `projects` p ".
                    " WHERE p.`iscancel` = 0 AND p.`isgetprize`=1 AND p.`prizestatus` = 1 ".
                    $sWhere . " GROUP BY p.`lvtopid`";
        }
        if( $sAction == 'point' )
        { // 团队返点总额  = 方案未撤 + 返点状态(已返) + 返点撤单状态(未撤)
            $sSql = "SELECT lvtopid, SUM(udp.`diffmoney`) AS TOMSUM FROM `projects` p LEFT JOIN `userdiffpoints` udp ".
                    " ON ( p.projectid =udp.projectid ) ".
                    " WHERE p.`iscancel`=0 AND udp.`status`=1 AND udp.`cancelstatus`=0 ".
                    $sWhere . " GROUP BY p.`lvtopid`";
        }
        return $this->oDB->getAll($sSql);
    }
}
?>