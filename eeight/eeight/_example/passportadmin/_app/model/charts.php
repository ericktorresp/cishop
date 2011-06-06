<?php
/**
 * 文件 : /_app/model/charts.php
 * 功能 : 数据模型 - 图表数据
 * 
 * @author	    Tom
 * @version    1.1.0
 * @package    passportadmin
 * @since      2009-06-15
 */

class model_charts extends basemodel 
{
	/**
	 * 构造函数
	 * @author tom
	 * @access	public
	 * @return	void
	 */
	function __construct( $aDBO=array() )
	{
		parent::__construct( $aDBO );
	}



	/**
	 * 向数据库中插入伪数据用
	 * @author Tom
	 */
	function tomDummyInsert( $iChartId = 0, $iLoopTimes=500 )
	{
	    die('model.charts.tomDummyInsert() Enable me !');
	    for( $i=1; $i<=$iLoopTimes; $i++ )
	    {
	        $temp    = strtotime(date('ymd'))- ($i*86400); // timestamps
	        $iDays   = date('ymd',$temp);
	        $iTimes  = date( 'Y-m-d', $temp );
	        $sChartVal = rand( 1, 120 );
	        $this->oDB->query( "INSERT INTO `chartdatas`( `chartid`,`times`,`days`,`chartvalue` ) VALUES "
	        	." ( '$iChartId', '$iTimes', '$iDays', '$sChartVal' ) " );
	    }
	}
	
	
	
    /**
     * 更新当天用户总数
     * @author Tom 090511
     */
	function setChartAllUser()
	{
	    $sCurrentDate = date('ymd');
	    $oUser = new model_user();
	    $aArray['chartid']    = 1; // 更新当天用户总数
	    $aArray['chartvalue'] = intval($oUser->getAllUserCount());
	    $aArray['times']      = date('Y-m-d H:i:s');
	    $aRes = $this->oDB->getOne("SELECT `entry` from `chartdatas` WHERE `chartid`='".$aArray['chartid']."' AND `days`='$sCurrentDate' ");
	    if( $this->oDB->ar() >0 )
	    { // update
	        $this->oDB->update( 'chartdatas', $aArray, " `entry`='".$aRes['entry']."' LIMIT 1 " );
	    }
	    else
	    { // insert
	        $aArray['days'] = date('ymd');
	        $this->oDB->insert( 'chartdatas', $aArray );
	    }
	}


	/**
     * 更新当天活跃用户数
     * @author Tom 090511
     */
	function setChartActiveUser()
	{
	    $sCurrentDate = date('ymd');
	    $oUser = new model_user();
	    $aArray['chartid']    = 2; // 更新当天活跃用户数
	    $aArray['chartvalue'] = intval($oUser->getActiveUserCount());
	    $aArray['times']      = date('Y-m-d H:i:s');
	    $aRes = $this->oDB->getOne("SELECT `entry` from `chartdatas` WHERE `chartid`='".$aArray['chartid']."' AND `days`='$sCurrentDate' ");
	    if( $this->oDB->ar() >0 )
	    { // update
	        $this->oDB->update( 'chartdatas', $aArray, " `entry`='".$aRes['entry']."' LIMIT 1 " );
	    }
	    else
	    { // insert
	        $aArray['days'] = date('ymd');
	        $this->oDB->insert( 'chartdatas', $aArray );
	    }
	}



    /**
     * 更新充值总额
     * @author Tom 090511
     */
	function setChartMoneyIn()
	{
	    $sCurrentDate = date('ymd');
	    $oUser = new model_orders();
	    $aArray['chartid']    = 10; // 更新'财务图表'充值总额
	    $aArray['chartvalue'] = intval($oUser->getMoneyRealIn());
	    $aArray['times']      = date('Y-m-d H:i:s');
	    $aRes = $this->oDB->getOne("SELECT `entry` from `chartdatas` WHERE `chartid`='".$aArray['chartid']."' AND `days`='$sCurrentDate' ");
	    if( $this->oDB->ar() >0 )
	    { // update
	        $this->oDB->update( 'chartdatas', $aArray, " `entry`='".$aRes['entry']."' LIMIT 1 " );
	    }
	    else
	    { // insert
	        $aArray['days'] = date('ymd');
	        $this->oDB->insert( 'chartdatas', $aArray );
	    }
	}



    /**
     * 更新提现总额
     * @author Tom 090511
     */
	function setChartMoneyOut()
	{
	    $sCurrentDate = date('ymd');
	    $oUser = new model_orders();
	    $aArray['chartid']    = 11; // 更新'财务图表'提现总额
	    $aArray['chartvalue'] = intval($oUser->getMoneyRealOut());
	    $aArray['times']      = date('Y-m-d H:i:s');
	    $aRes = $this->oDB->getOne("SELECT `entry` from `chartdatas` WHERE `chartid`='".$aArray['chartid']."' AND `days`='$sCurrentDate' ");
	    if( $this->oDB->ar() >0 )
	    { // update
	        $this->oDB->update( 'chartdatas', $aArray, " `entry`='".$aRes['entry']."' LIMIT 1 " );
	    }
	    else
	    { // insert
	        $aArray['days'] = date('ymd');
	        $this->oDB->insert( 'chartdatas', $aArray );
	    }
	}



    /**
     * 更新总账变数量
     * @author Tom 090511
     */
	function setChartOrderAllCount()
	{
	    $sCurrentDate = date('ymd');
	    $oUser = new model_orders();
	    $aArray['chartid']    = 20; // 更新'日志图表' 账变总数
	    $aArray['chartvalue'] = intval($oUser->getOrdersCount());
	    $aArray['times']      = date('Y-m-d H:i:s');
	    $aRes = $this->oDB->getOne("SELECT `entry` from `chartdatas` WHERE `chartid`='".$aArray['chartid']."' AND `days`='$sCurrentDate' ");
	    if( $this->oDB->ar() >0 )
	    { // update
	        $this->oDB->update( 'chartdatas', $aArray, " `entry`='".$aRes['entry']."' LIMIT 1 " );
	    }
	    else
	    { // insert
	        $aArray['days'] = date('ymd');
	        $this->oDB->insert( 'chartdatas', $aArray );
	    }
	}


    /**
     * 更新充值账变数量
     * @author Tom 090511
     */
	function setChartOrderMoneyInCount()
	{
	    $sCurrentDate = date('ymd');
	    $oUser = new model_orders();
	    $aArray['chartid']    = 21; // 更新'日志图表' 充值账变数
	    $aArray['chartvalue'] = intval($oUser->getMoneyInCount());
	    $aArray['times']      = date('Y-m-d H:i:s');
	    $aRes = $this->oDB->getOne("SELECT `entry` from `chartdatas` WHERE `chartid`='".$aArray['chartid']."' AND `days`='$sCurrentDate' ");
	    if( $this->oDB->ar() >0 )
	    { // update
	        $this->oDB->update( 'chartdatas', $aArray, " `entry`='".$aRes['entry']."' LIMIT 1 " );
	    }
	    else
	    { // insert
	        $aArray['days'] = date('ymd');
	        $this->oDB->insert( 'chartdatas', $aArray );
	    }
	}


    /**
     * 更新提现账变数量
     * @author Tom 090511
     */
	function setChartOrderMoneyOutCount()
	{
	    $sCurrentDate = date('ymd');
	    $oUser = new model_orders();
	    $aArray['chartid']    = 22; // 更新'日志图表' 提现账变数
	    $aArray['chartvalue'] = intval($oUser->getMoneyOutCount());
	    $aArray['times']      = date('Y-m-d H:i:s');
	    $aRes = $this->oDB->getOne("SELECT `entry` from `chartdatas` WHERE `chartid`='".$aArray['chartid']."' AND `days`='$sCurrentDate' ");
	    if( $this->oDB->ar() >0 )
	    { // update
	        $this->oDB->update( 'chartdatas', $aArray, " `entry`='".$aRes['entry']."' LIMIT 1 " );
	    }
	    else
	    { // insert
	        $aArray['days'] = date('ymd');
	        $this->oDB->insert( 'chartdatas', $aArray );
	    }
	}


	/**
     * 根据 WHERE 语句, 获取 GroupBy '图表' 数据
     * @author Tom 090511
     */
	function getChartsResult( $sWhere = '' )
	{
	    return $this->oDB->getAll("SELECT sum(`chartvalue`) AS TOMCOUNT, `days` from `chartdatas` ".$sWhere);
	}
	
	
	/**
	 * 保存与删除 统计报表历史数据
	 * @param integer $iDay
     * @param string  $sPath
     * @return bool
     * 
     * 涉及表 reportcount
     * 
     * 6/22/2010
	 */
	public function backandclearReportCount($iDay,$sPath)
	{
		if( !is_numeric($iDay) ) 
		{
			return FALSE;
		}
		$iDay = intval($iDay);
		
		if( $iDay < 5 )
		{
			$iDay = 5;
		}
		$sDay = date("Ymd");
		
    	$numCodes = $this->oDB->getOne("SELECT COUNT(entry) AS `numCodes` FROM `reportcount` "
		                        ." WHERE `times`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		$num   = $numCodes['numCodes'];
		$size  = 50000;
		$pages = ceil( $num/$size );
		$sFile = $sPath.DS.$sDay."_reportcount.gz";
		makeDir(dirname($sFile));
		$gz    = gzopen($sFile,'w9');
		for( $page =0 ; $page < $pages; $page++ )
		{
			$FileContent = "";
			$aSales = $this->oDB->getAll("SELECT * FROM `reportcount` "
		                                ." WHERE `times`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))
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
				$sql = "INSERT INTO `reportcount` (".join(",",$keys).") VALUES (".join(",",$values).");";
				unset($keys);
				unset($values);
				$FileContent .= $sql."\n";
			}
			gzwrite($gz, $FileContent);
		}
		gzclose($gz);
		//删除
		$this->oDB->query("DELETE FROM `reportcount` WHERE `times`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
	}
}
?>