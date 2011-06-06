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
 * - getProjectBonusDescription     获取奖金描述
 * - getExtendCode                  获取扩展号码
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
		if( empty($aArr['packageid']) || !is_numeric($aArr['packageid']) || $aArr['packageid'] <=0 )
        {//定单ID
            return -1;
        }
        $aArr['packageid'] = intval($aArr['packageid']);
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
        $aArr['singleprice'] = number_format(floatval($aArr['singleprice']),4, '.', '');
        if( empty($aArr['multiple']) || !is_numeric($aArr['multiple']) || $aArr['multiple'] <=0 )
        {//倍数
            return -7;
        }
        $aArr['multiple'] = intval($aArr['multiple']);
        if( empty($aArr['totalprice']) || !is_numeric($aArr['totalprice']) || $aArr['totalprice'] <=0 )
        {//总共价格
            return -8;
        }
        $aArr['totalprice'] = number_format(floatval($aArr['totalprice']),4, '.', '');
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
        if( empty($aArr['modes']) || !is_numeric($aArr['modes']) || $aArr['modes'] <=0 )
        {//模式
            return -12;
        }
        $aArr['modes']       = intval($aArr['modes']);
        $aArr['writetime']   = date("Y-m-d H:i:s");//写入方案时间
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
                                'packageid'     => $aArr['packageid'],
                                'taskid'        => $aArr['taskid'],
                                'lotteryid'     => $aArr['lotteryid'],
                                'methodid'      => $aArr['methodid'],
                                'issue'         => $aArr['issue'],
                                'code'          => $aArr['code'],
                                'singleprice'   => $aArr['singleprice'],
                                'multiple'      => $aArr['multiple'],
                                'totalprice'    => $aArr['totalprice'],
                                'lvtopid'       => $aArr['lvtopid'],
                                'lvtoppoint'    => $aArr['lvtoppoint'],
                                'lvproxyid'     => $aArr['lvproxyid']
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
	        $aArr['updatetime'] = date("Y-m-d H:i:s");
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
	               '".$aArr['codetimes']."','".$aArr['prize']."','".$aArr['expandcode']."','".$aArr['updatetime']."','".$aArr['hashvar']."')";
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
            if( empty($aArr['modes']) || !is_numeric($aArr['modes']) || $aArr['modes'] <=0 )
            {//方案ID
                return FALSE;
            }
            $aArr['modes']        = intval($aArr['modes']);
            $aArr['status']       = (isset($aArr['status']) && intval($aArr['status']) > 0)? 1 : 0;
            $aArr['cancelstatus'] = 0;
            $aArr['sendtime']     = $aArr['status'] == 1 ? "'".date("Y-m-d H:i:s")."'" : "'0000-00-00 00:00:00'";
            $aValues[]            = "('".$aArr['userid']."','".$aArr['projectid']."','".$aArr['diffmoney']."',".
                                    "'".$aArr['diffpoint']."','".$aArr['status']."','".$aArr['cancelstatus'].
                                    "',".$aArr['sendtime'].",".$aArr['modes'].")";
        }
        //构造SQL语句
        $sSql = " INSERT INTO `userdiffpoints`(`userid`,`projectid`,`diffmoney`,`diffpoint`,`status`,".
                "`cancelstatus`,`sendtime`,`modes`) VALUES".implode(",",$aValues);
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
        $sSql = " SELECT e.`projectid`,e.`prize`,p.`issue`
                  FROM `expandcode` AS e LEFT JOIN `projects` AS p ON e.`projectid`=p.`projectid`
                  WHERE p.`userid`='".$iUserId."' AND p.`lotteryid`='".$iLotteryId."' 
                  AND p.`methodid`='".$iMethodId."' AND p.`code`='".$sCode."'
                  AND p.`iscancel`='0' AND p.`isgetprize`='0' AND p.`prizestatus`='0' ";
        if( !is_array($sIssue) )
        {//单个查询
            $sSql .= " AND p.`issue`='".$sIssue."' ";
        }
        else
        {
            $sSql .= " AND p.`issue` IN(".implode(",",$sIssue).") ";
        }
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
    		$sField = "P.*,L.`cnname`,L.`lotterytype`,M.`methodname`,M.`functionname`,M.`nocount`,UT.`username`,I.`code` AS `nocode`";	
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
    	//获取总数SQL
    	$sCountTableName = " `projects` AS P LEFT JOIN `usertree` AS UT ON (P.`userid`=UT.`userid`) ";
    	if(strpos($sWhere,"M.") !== FALSE)
    	{
    	    $sCountTableName .= " LEFT JOIN `method` AS M ON (P.`methodid`=M.`methodid`) ";
    	}
    	$sCountSql = " SELECT COUNT(*) AS TOMCOUNT FROM ".$sCountTableName." WHERE ".$sWhere;
    	return $this->oDB->getPageResult( $sTableName, $sField, $sWhere, $iPageRecord, $iCurrPage, $sOrderBy, '', $sCountSql );
    }



    /**
     * 游戏方案编号加密以及解密
     *
     * @param string $sString
     * @param string $sOption
     */
    static function HighEnCode( $sString, $sOption="DECODE" )
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
			$tmp = explode( "-", $sString );
            $tmp2 = array_pop($tmp);
            $aData = array(implode('-', $tmp), $tmp2);
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
     * 反转义方案或者扩展表里的号码:有些新规则的号码需要修改些函数目前只支持当前的十三种类型
     *
     * @param string $sCode
     * @param int    $iMethodId 玩法ID
     * @param int    $iAddslasType 转义类型
     * @return string  code
     */
    static function AddslasCode( $sCode, $iMethodId, $iAddslasType = 0)
    {
        $iMethodId = intval($iMethodId);
        if($iAddslasType == 0)
        {
            /* @var $oMethod model_method */
            $oMethod = A::singleton("model_method");
            $aMethod = $oMethod->getItem($iMethodId);
            $iAddslasType = $aMethod['addslastype'];
        }
        switch($iAddslasType)
        {
            case 1://大小单双型
                $sCode = str_replace( array(0,1,2,3), array('大','小','单','双'), $sCode );
                break;
            case 2://定单双型
                $sCode = str_replace( array(5,4,3,2,1,0),
                array('五单零双','四单一双','三单两双','两单三双','一单四双','零单五双'), $sCode );
                $sCode = str_replace( array("五","四","三","两","一","零"), array(5,4,3,2,1,0), $sCode );
                break;
            case 3://和值单双型
                $sCode = str_replace( array(0,1), array('和值单','和值双'), $sCode );
                break;
            case 4://和值大小型
                $sCode = str_replace( array(0,1,2), array('和值大','和值小','和值810'), $sCode );
                break;
            case 5://上中下盘型
                $sCode = str_replace( array(0,1,2), array('上盘','下盘','中盘'), $sCode );
                break;
            case 6://奇偶盘型
                $sCode = str_replace( array(0,1,2), array('奇盘','偶盘','和盘'), $sCode );
                break;
            case 7://三星特殊号码型
                $sCode = str_replace( array(0,1,2), array('豹子','顺子','对子'), $sCode );
                break;
            case 8://三星趣味型
                $aCode = explode("|",$sCode);
                if(count($aCode) == 1)
                {//后台封锁显示
                    $stmpCode = str_replace( array(0,1), array('小号','大号'), substr($sCode,0,1));
                    $sCode = $stmpCode.",".substr($sCode,1,strlen($sCode)-1);
                }
                else 
                {//单子号码显示
                    $aCode[0] = implode(" ",str_split($aCode[0]));
                    $aCode[0] = str_replace( array(0,1), array('小号','大号'), $aCode[0] );
                    $sCode = implode("|",$aCode);
                }
                break;
            case 9://三星区间型
                $aCode = explode("|",$sCode);
                if(count($aCode) == 1)
                {//后台封锁显示
                    $stmpCode = str_replace( array(0,1,2,3,4), array('一区','二区','三区','四区','五区'), substr($sCode,0,1));
                    $sCode = $stmpCode.",".substr($sCode,1,strlen($sCode)-1);
                }
                else
                {//单子号码显示
                    $aCode[0] = implode(" ",str_split($aCode[0]));
                    $aCode[0] = str_replace( array(0,1,2,3,4), array('一区','二区','三区','四区','五区'), $aCode[0] );
                    $sCode = implode("|",$aCode);
                }
                break;
            case 10://四星趣味型
                $aCode = explode("|",$sCode);
                $aCode[0] = implode(" ",str_split($aCode[0]));
                $aCode[0] = str_replace( array(0,1), array('小号','大号'),  $aCode[0] );
                $sCode = implode("|",$aCode);
                break;
            case 11://四星区间型
                $aCode = explode("|",$sCode);
                $aCode[0] = implode(" ",str_split($aCode[0]));
                $aCode[0] = str_replace( array(0,1,2,3,4), array('一区','二区','三区','四区','五区'),  $aCode[0] );
                $sCode = implode("|",$aCode);
                break;
            case 12://五星趣味型
                $aCode = explode("|",$sCode);
                $aCode[0] = implode(" ",str_split($aCode[0]));
                $aCode[0] = str_replace( array(0,1), array('小号','大号'),  $aCode[0] );
                $aCode[1] = implode(" ",str_split($aCode[1]));
                $aCode[1] = str_replace( array(0,1), array('小号','大号'),  $aCode[1] );
                $sCode = implode("|",$aCode);
                break;
            case 13://五星区间型
                $aCode = explode("|",$sCode);
                $aCode[0] = implode(" ",str_split($aCode[0]));
                $aCode[0] = str_replace( array(0,1,2,3,4), array('一区','二区','三区','四区','五区'),  $aCode[0] );
                $aCode[1] = implode(" ",str_split($aCode[1]));
                $aCode[1] = str_replace( array(0,1,2,3,4), array('一区','二区','三区','四区','五区'),  $aCode[1] );
                $sCode = implode("|",$aCode);
                break;
        }
        $sCode = str_replace("|",",",$sCode);
        return $sCode;
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
    		          AND `ordertypeid`='5' LIMIT 1";
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
        if( $sAction == 'buy' )
        { // 团队总代购费  = 未撤 + 真实扣款:在拍快照时间内进行扣款的单子
            $sWhere = '';
            $sSql   = '';
            if( $tBeginTime!=0 )
            {
                $sWhere .= " AND p.`deducttime` >= '".daddslashes($tBeginTime)."' ";
            }
            if( $tEndTime!=0 )
            {
                $sWhere .= " AND p.`deducttime` <= '".daddslashes($tEndTime)."' ";
            }
            $sWhere .= $sCondition;
            if( $tBeginTime!=0 && $tEndTime!=0 )
            { // 如果同时存在2个时间, 则使用 BETWEEN (效率更高些)
                $sWhere = " AND p.`deducttime` BETWEEN '".daddslashes($tBeginTime)."' AND '".daddslashes($tEndTime)."' ";
            }
            $sSql = "SELECT lvtopid, SUM(`totalprice`) AS TOMSUM FROM `projects` p  ".
                    " WHERE p.`iscancel` = 0 AND p.`isdeduct` = 1 ".$sWhere . " GROUP BY `lvtopid` ";
        }
        if( $sAction == 'bingo' )
        { // 团队中奖总额  = 未撤 + 已中 + 已派:在拍快照时间内的派奖单子
            $sWhere = '';
            $sSql   = '';
            if( $tBeginTime!=0 )
            {
                $sWhere .= " AND p.`bonustime` >= '".daddslashes($tBeginTime)."' ";
            }
            if( $tEndTime!=0 )
            {
                $sWhere .= " AND p.`bonustime` <= '".daddslashes($tEndTime)."' ";
            }
            $sWhere .= $sCondition;
            if( $tBeginTime!=0 && $tEndTime!=0 )
            { // 如果同时存在2个时间, 则使用 BETWEEN (效率更高些)
                $sWhere = " AND p.`bonustime` BETWEEN '".daddslashes($tBeginTime)."' AND '".daddslashes($tEndTime)."' ";
            }
            $sSql = "SELECT lvtopid, SUM(`bonus`) AS TOMSUM FROM `projects` p ".
                    " WHERE p.`iscancel` = 0 AND p.`isgetprize`=1 AND p.`prizestatus` = 1 ".
                    $sWhere . " GROUP BY p.`lvtopid`";
        }
        if( $sAction == 'point' )
        { // 团队返点总额  = 方案未撤 + 返点状态(已返) + 返点撤单状态(未撤):在拍快照时间内的进行的返点操作
            $sWhere = '';
            $sSql   = '';
            if( $tBeginTime!=0 )
            {
                $sWhere .= " AND udp.`sendtime` >= '".daddslashes($tBeginTime)."' ";
            }
            if( $tEndTime!=0 )
            {
                $sWhere .= " AND udp.`sendtime` <= '".daddslashes($tEndTime)."' ";
            }
            $sWhere .= $sCondition;
            if( $tBeginTime!=0 && $tEndTime!=0 )
            { // 如果同时存在2个时间, 则使用 BETWEEN (效率更高些)
                $sWhere = " AND udp.`sendtime` BETWEEN '".daddslashes($tBeginTime)."' AND '".daddslashes($tEndTime)."' ";
            }
            /**
             * 如:在2010-05-19 02:20:00进行拍快照
             * 返点计算方式:
             * 1.在2010-05-18 02:20:00到2010-05-19 02:20:00时间的正常单子进行的返点值[加上].
             * 2.在2010-05-18 02:20:00之前购买的单子,在2010-05-18 02:20:00到2010-05-19 02:20:00时间进行撤单的返点值.[减去]
             */
            $sSql = "SELECT lvtopid, SUM(udp.`diffmoney`) AS TOMSUM FROM `projects` p LEFT JOIN `userdiffpoints` udp ".
                    " ON ( p.projectid =udp.projectid ) ".
                    " WHERE p.`iscancel`=0 AND udp.`status`=1 AND udp.`cancelstatus`=0 ".
                    $sWhere . " GROUP BY p.`lvtopid`";
            $sAbnormalPointSql = "SELECT lvtopid, SUM(udp.`diffmoney`) AS TOMSUM FROM `projects` p LEFT JOIN `userdiffpoints` udp ".
                    " ON ( p.projectid =udp.projectid ) ".
                    " WHERE p.`iscancel`>0 AND udp.`sendtime` != '0000-00-00 00:00:00' AND udp.`cancelstatus`>0 ".
                    " AND p.`writetime` < '".$tBeginTime."' ".str_replace("udp.`sendtime`","p.canceltime",$sWhere).
                    " GROUP BY p.`lvtopid`";
           $aNormalPoint   = $this->oDB->getAll($sSql);
           $aAbnormalPoint = $this->oDB->getAll($sAbnormalPointSql);
           $aAbnormalTmpPoint = array();
           $aPointResult = array();
           foreach ( $aAbnormalPoint as $aTmpPoint )
           {
           	    $aAbnormalTmpPoint[$aTmpPoint['lvtopid']] = $aTmpPoint;
           }
           foreach ( $aNormalPoint as $aPoint )
           {
           	    if(isset($aAbnormalTmpPoint[$aPoint['lvtopid']]))
           	    {
           	        $aPoint['TOMSUM'] -= $aAbnormalTmpPoint[$aPoint['lvtopid']]['TOMSUM'];//减去特殊撤单单子返点
                }
                $aPointResult[] = $aPoint;
           }
           //减去特殊撤单单子返点
           if(empty($aPointResult))
           {
           	    foreach ( $aAbnormalPoint as $aTmpPoint )
           	    {
           		   $aTmpPoint['TOMSUM'] = -$aTmpPoint['TOMSUM'];
           		   $aPointResult[] = $aTmpPoint;
           	    }
           }
           unset($aAbnormalTmpPoint);
           return $aPointResult;
        }
        return $this->oDB->getAll($sSql);
    }
}
?>