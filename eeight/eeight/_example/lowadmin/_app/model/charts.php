<?php
/**
 * 文件 : /_app/model/charts.php
 * 功能 : 数据模型 - 图表数据
 * 
 * @author	   SAUL
 * @version    1.1.0
 * @package    lowadmin
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
     * 根据 WHERE 语句, 获取 GroupBy '图表' 数据
     * @author Tom 090511
     */
	function getChartsResult( $sWhere = '' )
	{
	    return $this->oDB->getAll("SELECT sum(`chartvalue`) AS TOMCOUNT, `days` from `chartdatas` ".$sWhere);
	}



	/**
	 * 更新统计报表中的当前用户数(CLI)
	 * @author SAUL 090815
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
	 * 更新参与游戏的用户数目
	 * @author SAUL 090815
	 */
	function setchartGameUser()
	{
		$sCurrentDate = date("ymd");
		$aArray["chartid"]	=	2;
		//计算游戏用户数目
		$oProjects = new model_projects();
		$aArray["chartvalue"] = intval($oProjects->getProjectUserCount());
		$aArray["times"]	=	date("Y-m-d H:i:s");
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
	 * 更新游戏获利的用户数目
	 * @author SAUL 090815
	 */
	function setGameWinUser()
	{
		$sCurrentDate = date('ymd');
	    $oOrders = new model_orders();
	    $aArray['chartid']    = 3; // 更新游戏获利的用户树
	    $aArray['chartvalue'] = intval($oOrders->getGameWinUserCount());
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
	 * 游戏收入
	 *
	 */
	function setGameMoneytotal()
	{
		$sCurrentDate = date('ymd');
	    $oOrders = new model_orders();
	    $aArray['chartid']    = 10; // 游戏收入
	    $aArray['chartvalue'] = intval($oOrders->getGameMoneytotal());
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
	 * 游戏返奖
	 *
	 */
	function setGameWinMoneytotal()
	{
		$sCurrentDate = date('ymd');
	    $oOrders = new model_orders();
	    $aArray['chartid']    = 11; // 游戏返奖
	    $aArray['chartvalue'] = intval($oOrders->getGameWinMoneytotal());
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
	 * 计算低频转出到银行
	 *
	 */
	function setGameMoneyOut()
	{
		$sCurrentDate = date('ymd');
	    $oOrders = new model_orders();
	    $aArray['chartid']    = 12; // 低频转出银行
	    $aArray['chartvalue'] = intval($oOrders->getGameMoneyOut());
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
	 * 银行转入到低频
	 *
	 */
	function setGameMoneyIn()
	{
		$sCurrentDate = date('ymd');
	    $oOrders = new model_orders();
	    $aArray['chartid']    = 13; // 银行转入频道
	    $aArray['chartvalue'] = intval($oOrders->getGameMoneyIn());
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
	 * 统计帐变个数
	 *
	 */
	function setOrdersTotal()
	{
		$sCurrentDate = date('ymd');
	    $oOrders = new model_orders();
	    $aArray['chartid']    = 20; // 统计帐变个数
	    $aArray['chartvalue'] = intval($oOrders->getOrdersTotal());
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
	 * 统计追号个数
	 *
	 */
	function setTaskTotal()
	{
		$sCurrentDate = date('ymd');
	    $oTask = new model_task();
	    $aArray['chartid']    = 21; // 统计追号个数
	    $aArray['chartvalue'] = intval($oTask->getTaskTotal());
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
	 * 统计方案个数
	 *
	 */
	function setProjectTotal()
	{
		$sCurrentDate = date('ymd');
	    $oProject = new model_projects();
	    $aArray['chartid']    = 22; // 统计方案个数
	    $aArray['chartvalue'] = intval($oProject->getProjectTotal());
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
}
?>