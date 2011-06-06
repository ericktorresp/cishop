<?php
/**
 * 文件 : /_app/model/locks.php
 * 功能 : 数据模型 - 封锁模型
 * 
 * - salesUpdate()      更新某一期的销售量
 * - salesGetMoneys()   查询某一期或某几期的销售额
 * - crateLocksData()   生成封锁表
 * - updateSql()        更新封锁表标记位值
 * - getData()          根据封锁表彩种和奖期获取数据(管理员后台读取)
 * - bakData()          备份封锁表(直接转化为对象Cache,方面查询数据的时候数据库调用)
 * - transferLocks()    根据彩种和封锁表名称，在当期结束后转移当期封锁表进历史数据，并生成新的封锁表(cli调用)
 * - createSalesData()  根据彩种一次性生成所有的销量表记录
 * 
 * @author     james    090915
 * @version    1.2.0
 * @package    lowgame
 */

class model_locks extends basemodel
{
	private $_sHistory = 'history'; // 历史表的后缀
    private $_sFuture  = 'future';  // 未来数据表的后缀 
	
	/**
	 * 更新某一期的销售量
	 *
	 * @author james   090812
	 * @access public  
	 * @param  array    $aArr
	 * @return boolean  TRUE OR FALSE
	 */
	public function salesUpdate( $aArr = array() )
	{
	    //01：先进行数据检查
        if( empty($aArr) || !is_array($aArr) )
        {
            return FALSE;
        }
        if( empty($aArr['issue']) )
        {
        	return FALSE;
        }
        if( empty($aArr['lotteryid']) || !is_numeric($aArr['lotteryid']) || $aArr['lotteryid'] < 1 )
        {
        	return FALSE;
        }
        $aArr['lotteryid'] = intval($aArr['lotteryid']);
        $aArr['issue']     = daddslashes($aArr['issue']);
        $aArr['TFWLname']  = empty($aArr['TFWLname']) ? "" : daddslashes($aArr['TFWLname']);
        $aArr['threadid']  = intval($this->oDB->getThreadId()) % 20; //获取当前线程ID[20个线程]
        if( empty($aArr['moneys']) || !is_numeric($aArr['moneys']) )
        {
        	return FALSE;
        }
        $aArr['moneys']   = round( $aArr['moneys'], 4 );
        if( $aArr['moneys'] == 0 )
        {//如果变动金额为0则直接返回TRUE
        	return TRUE;
        }
        //更新
    	$sSql = "UPDATE `sales` SET `moneys`=`moneys`+".$aArr['moneys']." WHERE `issue`='".$aArr['issue']."' 
                 AND `lotteryid`='".$aArr['lotteryid']."' AND `TFWLname`='".$aArr['TFWLname']."' 
                 AND `threadid`='".$aArr['threadid'] ."' ";
    	$this->oDB->query($sSql);
    	if( $this->oDB->errno() > 0 )
        {
            return FALSE;
        }
        return TRUE;
	}
	
	
	
	/**
	 * 查询某一期或某几期的销售额
	 *
	 * @author james   090812
     * @access public  
     * @param  int      $iLotteryId        //彩种ID
	 * @param  string   $sIssue            //期号[或者期号数组]
	 * @param  string   $sLockTableName    //封锁表名称
	 */
	public function salesGetMoneys( $iLotteryId, $mIssue, $sLockTableName='' )
	{
		if( empty($iLotteryId) || !is_numeric($iLotteryId) || $iLotteryId < 1 )
        {
            return FALSE;
        }
        $iLotteryId = intval($iLotteryId);
		if( empty($mIssue) )
		{//期号错误
			return FALSE;
		}
		$sLockTableName = empty($sLockTableName) ? "" : daddslashes($sLockTableName);
		if( !is_array($mIssue) )
		{//只是单期
			$mIssue  = daddslashes($mIssue);
	        $sSql    = " SELECT SUM(`moneys`) AS `salemoney` FROM `sales` WHERE `issue`='".$mIssue."' 
	                     AND `lotteryid`='".$iLotteryId."' AND `TFWLname`='".$sLockTableName."' ";
	        $aResult = $this->oDB->getOne($sSql);
	        if( empty($aResult) )
	        {
	            return FALSE;
	        }
	        return $aResult['salemoney'];
		}
		else
		{//多期
			$sSql    = " SELECT SUM(`moneys`) AS `salemoney`,`issue` 
			             FROM `sales` WHERE `issue` IN (".implode(",",$mIssue).") 
                         AND `lotteryid`='".$iLotteryId."' AND `TFWLname`='".$sLockTableName."' GROUP BY `issue` ";
            $aResult = $this->oDB->getAll($sSql);
            if( empty($aResult) )
            {
                return FALSE;
            }
            return $aResult;
		}
	}
	
	
	
	/**
	 * 生成封锁表
	 */
	
	
	/**
	 * 生成封锁表
	 *
	 * @author james   090812
	 * @param  int     $iNumLen    //号码长度3D：3位，P3：3位，P5后二：2位
	 * @param  int     $iThread    //线程数
	 * @param  string  $sLocksname //封锁表名称
	 */
	public function crateLocksData( $sIssue, $iNumLen, $iThread, $sLocksname )
	{
		if( empty($sIssue) )
        {
            return FALSE;
        }
        $sIssue = daddslashes($sIssue);
		if( empty($iNumLen) || !is_numeric($iNumLen) || $iNumLen < 1 )
		{
			return FALSE;
		}
		$iNumLen = intval($iNumLen);
		if( empty($iThread) || !is_numeric($iThread) || $iThread < 1 )
        {
            return FALSE;
        }
        $iThread = intval($iThread);
		if( empty($sLocksname) )
		{
			return FALSE;
		}
		$sLocksname = daddslashes($sLocksname);
		//检测是否已生成当期
		$sSql    = " SELECT count(1) AS JamesCount FROM `".$sLocksname."` WHERE `issue`='".$sIssue."'";
		$aResult = $this->oDB->getOne($sSql);
		if( $this->oDB->errno() > 0 )
		{
			return FALSE;
		}
		if( !empty($aResult) && $aResult['JamesCount'] > 0 )
		{//已生成
			echo $aResult['JamesCount'];
			return TRUE;
		}
		$aData      = array();
		if( $iNumLen == 3 )
		{
			for( $i=0; $i<10; $i++ )
	        {
	            for( $j=0; $j<10; $j++ )
	            {
	                for( $k=0; $k<10; $k++ )
	                {
	                	$iStamp      = 0;
	                    $sStampValue = '';
	                    $iAddValue   = 0;
	                    $sM2Value    = '';
	                    $sQEValue    = '';
	                    $sHEValue    = '';
	                    if( $i == $j && $j == $k )
	                    {//豹子
	                        $iStamp = 0;
	                        $sStampValue = $i;
	                        $sM2Value = '';//$i.$i;
	                        $sQEValue = '';//$i.$i;
	                        $sHEValue = '';//$i.$i;
	                    }
	                    elseif( $i==$j || $i==$k || $j==$k )
	                    {//组三
	                        $aTemp = array($i,$j,$k);
	                        $aTemp = array_unique($aTemp);
	                        sort($aTemp);
	                        $iStamp =1;
	                        $sStampValue = implode("",$aTemp);
	                        $aTemp = array($i,$j,$k);
	                        sort($aTemp);
	                        $sM2Value = $aTemp[0].$aTemp[1]." ".$aTemp[1].$aTemp[2];
	                        $aTemp = array($i,$j);
	                        sort($aTemp);
	                        $sQEValue = $aTemp[0].$aTemp[1];
	                        $aTemp = array($j,$k);
	                        sort($aTemp);
	                        $sHEValue = $aTemp[0].$aTemp[1];
	                    }
	                    else 
	                    {//组六
	                        $aTemp = array($i,$j,$k);
	                        sort($aTemp);
	                        $iStamp = 2;
	                        $sStampValue = implode("",$aTemp);
	                        $sM2Value = $aTemp[0].$aTemp[1]." ".$aTemp[0].$aTemp[2]." ".$aTemp[1].$aTemp[2];
	                        $aTemp = array($i,$j);
	                        sort($aTemp);
	                        $sQEValue = $aTemp[0].$aTemp[1];
	                        $aTemp = array($j,$k);
	                        sort($aTemp);
	                        $sHEValue = $aTemp[0].$aTemp[1];
	                    }
	                    $iAddValue   = $i+$j+$k;
	                    for( $m=0; $m<$iThread; $m++ )
	                    {//线程数量
	                        $aData[] = "('".$sIssue."','".$m."','".$i.$j.$k."','0','".$iStamp."','".$sStampValue."',
	                                     '".$iAddValue."','".$sM2Value."','".$sQEValue."','".$sHEValue."')";
	                    }
	                }
	            }
	        }
		}
		if( $iNumLen == 2 )
		{
		    for( $i=0; $i<10; $i++ )
            {
                for( $j=0; $j<10; $j++ )
                {
                	$iStamp      = 0;
                    $sStampValue = '';
                    $iAddValue   = $i+$j;
                    $sM2Value    = '';
                    $sQEValue    = '';
                    $sHEValue    = '';
                    $aTemp = array($i,$j);
                    sort($aTemp);
                    $sQEValue = $aTemp[0].$aTemp[1];
                    $sHEValue = $aTemp[0].$aTemp[1];
                    for( $k=0; $k<$iThread; $k++ )
                    {//线程数量
                        $aData[] = "('".$sIssue."','".$k."','".$i.$j."','0','".$iStamp."','".$sStampValue."',
                                         '".$iAddValue."','".$sM2Value."','".$sQEValue."','".$sHEValue."')";
                    }
                }
            }
		}
		
		$sSql = " INSERT INTO `".$sLocksname."`
		          (`issue`,`threadid`,`code`,`prizes`,`stamp`,`stampvalue`,`addvalue`,`m2value`,`q2value`,`h2value`) 
		          VALUES ".implode(",",$aData);
		$this->oDB->query($sSql);
		if( $this->oDB->errno() >0 )
		{
			return FALSE;
		}
		return TRUE;
	}
	
	
	//更新封锁表标记位值
	function updateSql( $sLocksname )
	{
		$this->oDB->doTransaction();
		for( $i=0; $i<10; $i++ )
		{
			for( $j=0; $j<10; $j++ )
			{
				for( $k=0; $k<10; $k++ )
				{
					$iStamp      = 0;  //组三、组六、豹子标记位
					$sStampValue = ''; //组三、组六标记值
					$iAddValue   = 0;  //和值
					$sM2Value    = ''; //不定位(2码不定位) 特征值
					$sQEValue    = ''; //二码(前2) 组选特征值
					$sHEValue    = ''; //二码(后2) 组选特征值
					if( $i == $j && $j == $k )
					{//豹子
						$iStamp = 0;
						$sStampValue = $i;
						$sM2Value = '';//$i.$i;
						$sQEValue = '';//$i.$i;
						$sHEValue = '';//$i.$i;
					}
					elseif( $i==$j || $i==$k || $j==$k )
					{//组三
						$aTemp = array($i,$j,$k);
						$aTemp = array_unique($aTemp);
						sort($aTemp);
						$iStamp =1;
						$sStampValue = implode("",$aTemp);
						$aTemp = array($i,$j,$k);
						sort($aTemp);
						$aTemp = array( $aTemp[0].$aTemp[1], $aTemp[0].$aTemp[2], $aTemp[1].$aTemp[2] );
						$aTemp = array_unique($aTemp);
						$sM2Value = implode(" ",$aTemp);
						$aTemp = array($i,$j);
						sort($aTemp);
						$sQEValue = $aTemp[0].$aTemp[1];
						$aTemp = array($j,$k);
                        sort($aTemp);
                        $sHEValue = $aTemp[0].$aTemp[1];
					}
					else 
					{//组六
						$aTemp = array($i,$j,$k);
						sort($aTemp);
						$iStamp = 2;
						$sStampValue = implode("",$aTemp);
                        $sM2Value = $aTemp[0].$aTemp[1]." ".$aTemp[0].$aTemp[2]." ".$aTemp[1].$aTemp[2];
                        $aTemp = array($i,$j);
                        sort($aTemp);
                        $sQEValue = $aTemp[0].$aTemp[1];
                        $aTemp = array($j,$k);
                        sort($aTemp);
                        $sHEValue = $aTemp[0].$aTemp[1];
					}
					$iAddValue   = $i+$j+$k; //和值
					$sSql = " UPDATE  `".$sLocksname."` SET `stamp`='".$iStamp."', `stampvalue`='".$sStampValue."' , 
					          `addvalue`='".$iAddValue."', `m2value`='".$sM2Value."', 
					          `q2value`='".$sQEValue."',`h2value`='".$sHEValue."' WHERE `code`='".($i.$j.$k)."' ";
					$this->oDB->query($sSql);
					if( $this->oDB->errno()>0 )
					{
						$this->oDB->doRollback();
						return FALSE;
					}
				}
			}
		}
		$this->oDB->doCommit();
		return TRUE;
	}



	/**
	 * 根据封锁表彩种和奖期获取数据(管理员后台读取)
	 * @author SAUL
	 * @param string 	$sLockName
	 * @param integer 	$iLottery
	 * @param string 	$sIssue
	 */
	function getData( $sLockName, $iLottery, $sIssue )
	{
		$aResult    = array();
		$sLockName  = daddslashes($sLockName);
		$iLottery   = intval($iLottery);
		$sIssue     = daddslashes($sIssue);
		//首先检测 封锁表和对应的彩种是否一致
		$aLockCheck = $this->oDB->getDataCached( "SELECT * FROM `locksname` WHERE `lotteryid`='".$iLottery."' 
		                                          AND `lockname`='".$sLockName."'", 1000 );
		if( empty($aLockCheck) )
		{
			$aResult["error"] = "参数错误";
			return $aResult;
		}
		//分析彩种和奖期的关系
		$aIssueCheck = $this->oDB->getDataCached( "SELECT * FROM `issueinfo` WHERE `lotteryid`='".$iLottery."' 
		                                           AND `issue`='".$sIssue."'" );
		if( empty($aIssueCheck) )
		{
			$aResult["error"] = "彩种期数不存在";
			return $aResult;
		}
		//查询拥有的玩法
        $sSql = " SELECT `methodid` FROM `method` WHERE `locksid`='".$aLockCheck[0]['locksid']."' 
                  AND `pid`!='0' ";
        $aTempResult = $this->oDB->getDataCached( $sSql, 1000000 );
        $aMethod     = array();
        foreach( $aTempResult as $v )
        {
        	$aMethod[] = $v['methodid'];
        }
        if( empty($aMethod) )
        {
        	$aResult["error"] = "没有相应玩法";
            return $aResult;
        }
		if( strtotime($aIssueCheck[0]["salestart"]) > time() )
		{ // 尚未开始销售,从封锁扩展表获取相关的数据
			$aIssue['lose'] = $this->oDB->getAll( "SELECT `issue`,`code`,SUM(`prizes`) AS SUM_PRIZES FROM `"
			                             .$sLockName."future` WHERE `issue`='".$sIssue."' GROUP BY `code`,`issue`" );
			if( empty($aIssue['lose']) )
			{
				$aIssue['lose'] = $this->oDB->getAll( "SELECT `issue`,`code`,SUM(`prizes`) AS SUM_PRIZES FROM `"
                                         .$sLockName."` WHERE `issue`='".$sIssue."' GROUP BY `code`,`issue`" );
				if( empty($aIssue['lose']) )
				{
				    $aResult["error"] = "数据获取失败";
				    return $aResult;
				}
			}
			//获取真实销售额
			$aIssue['win'] = $this->oDB->getOne("SELECT SUM(`moneys`) AS sum_money FROM `sales` WHERE `issue`='"
			                         .$sIssue."' AND `lotteryid`='".$iLottery."' AND `TFWLname`='".$sLockName."' 
			                                     GROUP BY `issue`,`lotteryid`,`TFWLname`");
			if( empty($aIssue['win']) )
			{
				$aResult["error"] = "数据获取失败";
				return $aResult;
			}
			//根据追号表查询该期的追号销售额
			$sSql = " SELECT  SUM( t.`singleprice` * td.`multiple` ) AS sum_sales
			          FROM `tasks` AS t LEFT JOIN `taskdetails` AS td ON t.`taskid`=td.`taskid`
			          WHERE t.`lotteryid`='".$iLottery."' AND td.`issue`='".$sIssue."'
			          AND t.`methodid` IN(".implode( ",", $aMethod ).") AND td.`status`!='2' AND t.`status`!='1' ";
			$aIssue['sales'] = $this->oDB->getOne( $sSql );
			if( empty($aIssue['sales']) )
			{
				 $aResult["error"] = "数据获取失败";
                 return $aResult;
			}
			$aIssue['error'] = 0;
			return $aIssue;
		}
		elseif( strtotime($aIssueCheck[0]["saleend"]) < time() )
		{ //已经结束，读取数据备份
			$aIssue['lose'] = $this->oDB->getDataCached("SELECT `issue`,`code`,`prizes` AS SUM_PRIZES FROM `"
			                     .$sLockName."history` WHERE `issue`='".$sIssue."' GROUP BY `code`,`issue`", 1000000);
			
			if( empty($aIssue['lose']) )
			{
				$aResult["error"] = "数据获取失败";
				return $aResult;
			}
			$aWin = $this->oDB->getDataCached("SELECT SUM(`moneys`) AS sum_money FROM `sales` WHERE `issue`='"
			                         .$sIssue."' AND `lotteryid`='".$iLottery."' AND `TFWLname`='".$sLockName."' 
			                                     GROUP BY `issue`,`lotteryid`,`TFWLname`", 1000000);
			$aIssue['win'] = isset($aWin[0]) ? $aWin[0] : array();
			if( empty($aIssue['win']) )
			{
				$aResult["error"] = "数据获取失败";
				return $aResult;
			}
			//从方案表里查销售量
			$sSql = " SELECT SUM(`totalprice`) AS sum_sales
			          FROM `projects` WHERE `issue`='".$sIssue."' AND `lotteryid`='".$iLottery."'
			          AND `methodid` IN(".implode( ",", $aMethod ).") AND `iscancel`='0' ";
			$aIssue['sales'] = $this->oDB->getOne( $sSql );
		    if( empty($aIssue['sales']) )
            {
                $aResult["error"] = "数据获取失败";
                return $aResult;
            }
			$aIssue['error'] = 0;
			return $aIssue;
		}
		else
		{ // 销售中,从相关的数据中
			$aIssue['lose'] = $this->oDB->getAll("SELECT `issue`,`code`,SUM(`prizes`) AS SUM_PRIZES FROM `"
			                                     .$sLockName."` WHERE `issue`='".$sIssue."' GROUP BY `code`,`issue`");
			if( empty($aIssue['lose']) )
			{
				$aResult["error"] = "数据获取失败";
				return $aResult;
			}
			$aIssue['win'] = $this->oDB->getOne("SELECT SUM(`moneys`) AS sum_money FROM `sales` WHERE `issue`='"
			                         .$sIssue."' AND `lotteryid`='".$iLottery."' AND `TFWLname`='".$sLockName."' 
			                                     GROUP BY `issue`,`lotteryid`,`TFWLname`");
			if( empty($aIssue['win']) )
			{
				$aResult["error"] = "数据获取失败";
				return $aResult;
			}
		    //从方案表里查销售量
            $sSql = " SELECT SUM(`totalprice`) AS sum_sales
                      FROM `projects` WHERE `issue`='".$sIssue."' AND `lotteryid`='".$iLottery."'
                      AND `methodid` IN(".implode( ",", $aMethod ).") AND `iscancel`='0' ";
            $aIssue['sales'] = $this->oDB->getOne( $sSql );
            if( empty($aIssue['sales']) )
            {
                $aResult["error"] = "数据获取失败";
                return $aResult;
            }
			$aIssue['error'] = 0;
			return $aIssue;
		}
	}




	/**
	 * 备份封锁表(直接转化为对象Cache,方面查询数据的时候数据库调用)
	 * SAUL
	 */
	function bakData( $sLockName, $iLottery, $sIssue )
	{
		$sLockName = daddslashes($sLockName);
		$iLottery  = intval($iLottery);
		$sIssue    = daddslashes($sIssue);
		//首先检测 封锁表和对应的彩种是否一致
		$aLockCheck = $this->oDB->getDataCached( "SELECT * FROM `locksname` WHERE `lotteryid`='".$iLottery."' 
		                                          AND `lockname`='".$sLockName."'", 1000 );
		if( empty($aLockCheck) )
		{
			return FALSE;
		}
		//分析彩种和奖期的关系
		$aIssueCheck = $this->oDB->getDataCached( "SELECT * FROM `issueinfo` WHERE `lotteryid`='".$iLottery."' 
		                                           AND `issue`='".$sIssue."'" );
		if( empty($aIssueCheck) )
		{
			return FALSE;
		}
		if( strtotime($aIssueCheck[0]["saleend"]) < time() )
		{ //已经结束，读取数据备份
			$aIssue['lose'] = $this->oDB->getAll("SELECT `issue`,`code`,SUM(`prizes`) AS SUM_PRIZES FROM `"
			                                    .$sLockName."` WHERE `issue`='".$sIssue."' GROUP BY `code`,`issue`");
			if( empty($aIssue['lose']) )
			{
				return FALSE;
			}
			$aIssue['win'] = $this->oDB->getOne( "SELECT SUM(`moneys`) AS sum_money FROM `sales` WHERE `issue`='"
			                             .$sIssue."' AND `lotteryid`='".$iLottery."' AND `TFWLname`='".$sLockName."' 
			                                     GROUP BY `issue`,`lotteryid`,`TFWLname`");
			if( empty($aIssue['win']) )
			{
				return FALSE;
			}
			$aIssue['error'] = 0;
			setStaticCache( 'locks_'.$iLottery."_".$sLockName."_".$sIssue, $aIssue );
			return TRUE; 
		}
		return FALSE;
	}
	
	
	/**
	 * 根据彩种和封锁表名称，在当期结束后转移当期封锁表进历史数据，并生成新的封锁表(cli调用)
	 * CLI 调用流程
	 *   1, 2020_initLocks.php 循环调用 doDataCleanUp() n次  n=Count( select * from locksname )
	 *   2, 每次 doDataCleanUp() 调用本方法transferLocks 1次
	 *   3, 对每个封锁表进行清理
	 * 
	 *   1, Step 01: 对数据进行初始:   当前表$sLockName, 历史表名 _sHistory, 未来表_sFuture, 封锁号码数量 $iCodeLength
	 *   2, Step 02: 如果当前为销售期, 则返回错误. (严禁销售期执行本 CLI 程序)
	 *   3, 获取刚截止一起的期号  $sIssue  例: 2009239
	 *   4, 获取未来21期期号数据入数组  $aIssueResult
	 *   5, 检测当前期封锁表里的数据是否为刚结束一期
	 *   --- 事务开始 ----
	 *   6, 把当前期封锁表里的数据整理后进入历史封锁表[并清空当前期封锁表]
	 *   7, 把下一期的封锁数据移动到当前期
	 *   8, 循环判断后面未来1期到21期是否在追号表生成，如果未生成则生成
	 *   9, 在追号封锁表中删除已经移动到当前封锁表的一期数据
	 *  10, 事务提交
	 * 
	 * @author  james  090817
	 * @access  public
	 * @param   int     $iLotteryId
	 * @param   string  $sLockName
	 */
	public function transferLocks( $iLotteryId=0, $sLockName='' )
	{
	    // step: 01 数据初始
	    if( empty($iLotteryId) || !is_numeric($iLotteryId) || $iLotteryId < 1 )
        {
            return -1;
        }
        $iLotteryId = intval($iLotteryId);
        if( empty($sLockName) )
        {
            return -1;
        }
        $sLockTableNameHistory = daddslashes( $sLockName.$this->_sHistory ); // 历史表
        $sLockTableNameFuture  = daddslashes( $sLockName.$this->_sFuture );  // 未来表
        $iCodeLength = $sLockName == 'locksp5last2' ? 100 : 1000; // 封锁表数量


        /**
         * 奖期整理
         */
        // step: 02 判断当前是不是在某一期的销售期，如果是销售期则返回错误
        /* @var $oIssue model_issueinfo */
        $oIssue  = A::singleton('model_issueinfo');
        $aResult = $oIssue->getCurrentIssue( $iLotteryId );
        if( !empty($aResult) )
        {//如果有当前时间的在售期，则返回错误
            return -2;
        }
        

        //step: 03 获取刚结束一期的期号数据
        $sCurrentTime = date("Y-m-d H:i:s",time());
        $sFileds      = " A.`issueid`,A.`issue` ";
        $sCondition   = " A.`lotteryid`='$iLotteryId' AND A.`saleend`<'$sCurrentTime' " 
                         ." ORDER BY A.`saleend` DESC LIMIT 1 ";
        $aResult      = $oIssue->IssueGetOne( $sFileds, $sCondition );
        if( empty($aResult) )
        { // 如果没有获取到数据则返回错误
        	return -3;
        }
        $sIssue = $aResult['issue'];  // 刚结束一期的期号,例 2009239
        

        //step: 04 获取未来21期的期号数据[以开始销售时间排序，第一期进入当期封锁表，后面进入追号期封锁表]
        $sFileds      = " i.`issueid`,i.`issue`,`statuslocks` ";
        $sCondition   = " i.`lotteryid`='$iLotteryId' AND i.`saleend`>'$sCurrentTime' ";
        $sOrderBy     = " ORDER BY i.`salestart` LIMIT 0,21 ";
        $aIssueResult = $oIssue->issueMutilTableGetList( $sFileds, $sCondition, '', $sOrderBy );
        if( empty($aIssueResult) )
        { //获取数据失败或者没有数据
        	return -4;
        }

        /**
         * 实现停售期间的连续执行
         *   1. 判断历史表中是否已经存在当期封锁的完整数据 1000 条 
         *   2. 判断当期表中, 奖期是否已经是下期的奖期号, 表记录是否完整 5*1000
         *   3. 未来表中, 是否仍有当前期的奖期数据
         */
        $aTmp = $this->oDB->getOne("SELECT count(`issue`) AS TOMCOUNT FROM $sLockTableNameHistory WHERE `issue`='$sIssue' ");
        if( !empty($aTmp) && $aTmp['TOMCOUNT']==$iCodeLength && !empty($aIssueResult[0]['issue']) ) // 1, 历史表中数据完整
        {
            $aTmp = $this->oDB->getOne( "SELECT count(`issue`) AS TOMCOUNT FROM $sLockName 
                                        WHERE `issue`='".$aIssueResult[0]['issue']."' " );
            if( !empty($aTmp) && $aTmp['TOMCOUNT'] == 5*$iCodeLength ) // 2, 当期封锁表中, 是否已经是下期的奖期号, 并且完整
            {
                $this->oDB->query( "SELECT 1 FROM $sLockTableNameFuture WHERE 
                                   `issue`='".$aIssueResult[0]['issue']."' LIMIT 1" );
                if( $this->oDB->ar() == 0 )
                {
                    return -55;
                }
            }
        }
        unset($aTmp);
        
        /**
         * 开始封锁表整理
         */
        //step: 05 检测当前期封锁表里的数据是否为刚结束一期的, 如果不是则报错
        $sSql    = " SELECT `issue` FROM $sLockName WHERE `issue`='$sIssue' LIMIT 1 ";
        $aResult = $this->oDB->getOne($sSql);
        if( empty($aResult) )
        {//数据不对
        	 return -5;
        }
        
        //更新奖期里的封锁表转换状态为1，正在执行
        if( $aIssueResult[0]['issue']['statuslocks'] == 0 && $iLotteryId != 2 )
        {
        	$sSql = " UPDATE `issueinfo` SET `statuslocks`='1' WHERE `statuslocks`='0' 
        	          AND `issue`='".$aIssueResult[0]['issue']."' AND `lotteryid`='".$iLotteryId."' ";
        	$this->oDB->query( $sSql );
        	if( $this->oDB->errno() > 0 || $this->oDB->ar() != 1 )
        	{
        		return -555;
        	}
        }
        

	    if( FALSE == $this->oDB->doTransaction() )
        {//事务处理失败
            return 5011;
        }


        //step: 06 把当前期封锁表里的数据整理后进入历史封锁表[并清空当前期封锁表]
        //06 01: 先检测历史表里是否已有当期数据[并且是1000条数据]
        $sSql    = " SELECT COUNT(`entry`) AS CodeCount FROM ".$sLockTableNameHistory
                  ." WHERE `issue`='".$sIssue."' ";
        $aResult = $this->oDB->getOne($sSql);
        if( empty($aResult) || $aResult['CodeCount'] != $iCodeLength )
        { // 如果没有历史数据或者历史数据不全
        	if( !empty($aResult) && $aResult['CodeCount'] > 0 && $aResult['CodeCount'] != $iCodeLength )
        	{//如果是数据不全，则删除残缺的历史数据
        		$aResult = $this->oDB->delete( $sLockTableNameHistory, " `issue`='".$sIssue."' " );
        		if( empty($aResult) )
        		{ // 删除数据出错 
        		    if( FALSE == $this->oDB->doRollback() )
                    { // 回滚事务
                        return 5012;
                    }
        			return -666;
        		}
        	}

        	//06 02: 把数据从当前期封锁表，整理后进入历史封锁表
        	$sSql = "INSERT INTO ".$sLockTableNameHistory."
        	         (`prizes`,`code`,`issue`,`stamp`,`stampvalue`,`addvalue`,`m2value`,`q2value`,`h2value`)
                     SELECT SUM(`prizes`) as prizes,`code`,`issue`,`stamp`,`stampvalue`,`addvalue`,`m2value`,
                     `q2value`,`h2value` FROM ".$sLockName." WHERE `issue`='".$sIssue."' GROUP BY `code`";
            $this->oDB->query($sSql);
            if( $this->oDB->ar() < $iCodeLength )
            { // 插入数据不完整
                if( FALSE == $this->oDB->doRollback() )
                { // 回滚事务
                    return 5012;
                }
                return  -66;
            }
        }

	    //06 03: 清空当前期数据
//        $this->oDB->query("TRUNCATE TABLE ".$sLockName);
        $this->oDB->query("DELETE FROM ".$sLockName);
        if( $this->oDB->errno() > 0 )
        {// 清空当期数据封锁表失败
            if( FALSE == $this->oDB->doRollback() )
            {//回滚事务
                return 5012;
            }
            return  -6;
        }

        //step: 07 把下一期的封锁数据复制到当前期
        $sSql = " INSERT INTO ".$sLockName."
                  (`issue`,`threadid`,`code`,`prizes`,`stamp`,`stampvalue`,`addvalue`,`m2value`,`q2value`,`h2value`) 
                  SELECT `issue`,`threadid`,`code`,`prizes`,`stamp`,`stampvalue`,`addvalue`,`m2value`,`q2value`,`h2value`
                  FROM ".$sLockTableNameFuture." WHERE `issue`='".$aIssueResult[0]['issue']."' ";
        $this->oDB->query($sSql);
	    if( $this->oDB->ar() < (5 * $iCodeLength) )
        { // 插入数据不完整
            if( FALSE == $this->oDB->doRollback() )
            { // 回滚事务
                return 5012;
            }
            return  -7;
        }

        //step: 08 循环判断后面未来1期到21期是否在追号表生成，如果未生成则生成
        $iMaxIssueCount = count($aIssueResult);
        $aSales = array(); // 需要生成的销量表记录
        for( $i=0; $i<$iMaxIssueCount; $i++ )
        {
        	if( $i == 0 )
        	{
        		continue;
        	}
        	// 判断是否生成
            $sSql = " SELECT COUNT(`issue`) AS CodeCount FROM ".$sLockTableNameFuture
                    ." WHERE `issue`='".$aIssueResult[$i]['issue']."' ";
            $aResult = $this->oDB->getOne($sSql);
            if( empty($aResult) || $aResult['CodeCount'] != (5 * $iCodeLength) )
            { //08 01: 如果没有数据或者数据不完整[1000*5个线程]
                if( !empty($aResult) && $aResult['CodeCount'] > 0 && $aResult['CodeCount'] != (5 * $iCodeLength) )
                { // 如果是数据不全，则删除残缺的数据
                    $aResult = $this->oDB->delete( $sLockTableNameFuture, " `issue`='".$aIssueResult[$i]['issue']."' " );
                    if( empty($aResult) )
                    {//删除数据出错 
                        if( FALSE == $this->oDB->doRollback() )
                        {//回滚事务
                            return 5012;
                        }
                        return -888;
                    }
                }
                //08 02:生成
                $sSql = " INSERT INTO $sLockTableNameFuture
                  (`threadid`,`code`,`stamp`,`stampvalue`,`addvalue`,`m2value`,`q2value`,`h2value`,`issue`,`prizes`) 
                  SELECT `threadid`,`code`,`stamp`,`stampvalue`,`addvalue`,`m2value`,`q2value`,`h2value`,'"
                  . $aIssueResult[$i]['issue']."', '0' FROM ".$sLockTableNameFuture
                  . " WHERE `issue`='".$aIssueResult[0]['issue']."' ";
                $this->oDB->query($sSql);
                if( $this->oDB->ar() < (5 * $iCodeLength) )
                { // 插入数据不完整
                    if( FALSE == $this->oDB->doRollback() )
                    { // 回滚事务
                        return 5012;
                    }
                    return  -88;
                }
                $aSales[] = $aIssueResult[$i]['issue'];
            }
        }
        
        if (!empty($aSales)){ // 生成销量表数据
            if ($this->createSalesData($iLotteryId, $aSales[0], $aSales[count($aSales) - 1]) === false){
                if( FALSE == $this->oDB->doRollback() )
                { // 回滚事务
                    return 5012;
                }
                return  -89;
            }
        }
            

        //step: 09 在追号封锁表中删除已经移动到当前封锁表的一期数据
	    $this->oDB->query("DELETE FROM $sLockTableNameFuture WHERE `issue`='".$aIssueResult[0]['issue']."' ");
        if( $this->oDB->errno() > 0 )
        { // 删除发生错误
            if( FALSE == $this->oDB->doRollback() )
            { // 回滚事务
                return 5012;
            }
            return  -9;
        }
        
	    //更新奖期里的封锁表转换状态为2，完成
        if( $iLotteryId == 2 )
        {//P5两个封锁表，每次执行加1
            $sSql = " UPDATE `issueinfo` SET `statuslocks`=`statuslocks`+1 WHERE 
                      `issue`='".$aIssueResult[0]['issue']."' AND `lotteryid`='".$iLotteryId."' ";
        }
        else
        {
            $sSql = " UPDATE `issueinfo` SET `statuslocks`='2' WHERE `statuslocks`='0' 
                      AND `issue`='".$aIssueResult[0]['issue']."' AND `lotteryid`='".$iLotteryId."' ";
        }
        $this->oDB->query( $sSql );
        if( $this->oDB->errno() > 0 || $this->oDB->ar() != 1 )
        {
            if( FALSE == $this->oDB->doRollback() )
            { // 回滚事务
                return 5012;
            }
            return -555;
        }
        
        //step: 10 提交事务完成
	    if( FALSE == $this->oDB->doCommit() )
        { // 事务提交失败
            return 5013;
        }
        
        return TRUE;
	}




	/**
	 * 根据彩种一次性生成所有的销量表记录
	 *
	 * @author james   090818
	 * @access public
	 * @param  int     $iLotteryId
	 * @return boolean TRUE/FALSE
	 */
	public function createSalesData( $iLotteryId, $iStartIssue, $iEndIssue )
	{
		if( empty($iLotteryId) || !is_numeric($iLotteryId) || $iLotteryId <= 0 || $iStartIssue <= 0 || $iEndIssue <= 0)
		{
			return FALSE;
		}
		$iLotteryId = intval($iLotteryId);
		$aData      = array();
		//01: 查询出全部彩种的全部奖期以及封锁表
		$sSql = " SELECT i.`issue`,i.`lotteryid`,ls.`lockname` FROM `locksname` AS ls
		          LEFT JOIN `issueinfo` AS i ON i.`lotteryid`=ls.`lotteryid` WHERE i.`lotteryid`='".$iLotteryId."' AND i.`issue` >= {$iStartIssue} AND i.`issue` <= {$iEndIssue}";
		$aResult = $this->oDB->getAll($sSql);
		if( empty($aResult) )
		{//没有数据
			return FALSE;
		}
		foreach( $aResult as $v )
		{
            // 检查奖期是不已存在于销量表中
            if ($this->salesIsExist($v['issue'], $v['lockname']) === true){
                continue;
            }
			for( $i=0; $i<20; $i++ )
			{
				$aData[] = "('".$v['issue']."','".$v['lotteryid']."','".$v['lockname']."','".$i."','0')";
			}
		}
        if (!empty($aData)){
            $sSql = " INSERT INTO `sales`(`issue`,`lotteryid`,`TFWLname`,`threadid`,`moneys`) 
                      VALUES ".implode(",",$aData);
            $this->oDB->query($sSql);
            if( $this->oDB->errno() > 0 )
            {
                return FALSE;
            }
        }
		return TRUE;
	}
    
    
    
    
    /**
     * 检查销量表中奖期是否已经存在
     * 
     * @param int       $iIssue       // 奖期
     * @param string    $sLockName    // 封锁表名称
     * 
     * @author      louis
     * @version     v1.0
     * @since       2011-01-14
     * 
     * @return      boolean     // true存在，false不存在
     * 
     */
    public function salesIsExist($iIssue, $sLockName){
        if (intval($iIssue) <= 0 || empty($sLockName)){
            return true;
        }
        
        $sSql = "SELECT * FROM `sales` WHERE `issue` = '{$iIssue}' AND `TFWLname` = '{$sLockName}'";
        $aResult = $this->oDB->getAll($sSql);
        return empty($aResult) ? false : true;
    }
    
    
    
    /**
     * 获取指定时间的奖期信息
     * 
     * @param int  $iLotteryId      // 彩种id
     * @param date $sStartTime      // 开始日期
     * @param date $sEndTime        // 结束日期
     * 
     * @author      louis
     * @version     v1.0
     * @since       2011-01-14
     * 
     * @return      array
     * 
     */
//    public function getIssueInfo($iLotteryId, $sStartTime, $sEndTime){
//        // 数据检查
//        if (empty($sStartTime) || empty($sEndTime) || !is_numeric($iLotteryId) || $iLotteryId <= 0){
//            return false;
//        }
//        
//        
//        // 查询指定时间段内的奖期信息
//        $sSql = "SELECT `issue`,`salestart` FROM `issueinfo` WHERE `lotteryid` = '{$iLotteryId}' AND `salestart` >= '{$sStartTime}' AND `saleend` < '{$sEndTime}'";
//        $aResult = $this->oDB->getAll($sSql);
//        return empty($aResult) ? false : $aResult;
//    }
    
    
    
    
    /**
     * 获取封锁表名称
     * 
     * @param int $iLotteryId       // 彩种id
     * 
     * @author      louis
     * @version     v1.0
     * @since       2011-01-14
     * 
     * @return      string or boolean
     */
//    public function getLocksName($iLotteryId){
//        // 数据检查
//        if (!is_numeric($iLotteryId) || $iLotteryId <= 0){
//            return false;
//        }
//        
//        $sSql = "SELECT `lockname` FROM `locksname` WHERE `lotteryid` = '{$iLotteryId}'";
//        $aResult = $this->oDB->getOne($sSql);
//        return empty($aResult) ? false : $aResult['lockname'];
//    }
}
?>
