<?php
/**
 * 文件 : /_app/model/charts.php
 * 功能 : 数据模型 - 图表数据
 * 
 * @author	   mark
 * @version    1.0.0
 * @package    highadmin
 */

/*****************************[ 宏定义帐变ID对应类型关系 ]**********************/
define("ORDER_TYPE_ZRPD",       1);   // 转入频道        pid=0   + 游戏币
define("ORDER_TYPE_PDZC",       2);   // 频道转出        pid=0   - 游戏币
define("ORDER_TYPE_JRYX",       3);   // 加入游戏        pid=0   - 游戏币
define("ORDER_TYPE_JJPS",       5);   // 奖金派送        pid=0   + 游戏币
class model_charts extends basemodel 
{
	/**
	 * 构造函数
	 * @author mark
	 * @access	public
	 * @return	void
	 */
	function __construct( $aDBO=array() )
	{
		parent::__construct($aDBO);
	}



	/**
     * 根据 WHERE 语句, 获取 GroupBy '图表' 数据
     * @author mark
     */
	function getChartsResult( $sWhere = '' )
	{
	    return $this->oDB->getAll("SELECT SUM(`chartvalue`) AS NORMALCOUNT,SUM(`testchartvalue`) AS TESTCOUNT, `days` FROM `chartdatas` ".$sWhere);
	}



	/**
	 * 更新统计报表中的当前用户数(CLI)
	 * @author mark
	 */
	function setChartAllUser()
	{
		$sCurrentDate = date('ymd');
	    $aArray['chartid']    = 1; // 更新当天用户总数
	    $aArray['chartvalue']     = intval($this->getAllNotTestUserCount());
	    $aArray['testchartvalue'] = intval($this->getAllTestUserCount());
	    $aArray['times']      = date('Y-m-d H:i:s');
	    $aRes = $this->oDB->getOne("SELECT `entry` FROM `chartdatas` WHERE `chartid`='".$aArray['chartid']."' AND `days`='$sCurrentDate' " . ' LIMIT 1');
	    if( $this->oDB->ar() > 0 )
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
	 * @author mark
	 */
	function setchartGameUser()
	{
		$sCurrentDate = date("ymd");
		$aArray["chartid"]	=	2;
		//计算游戏用户数目
		$aArray["chartvalue"]     = intval($this->getProjectNotTestUserCount());
		$aArray['testchartvalue'] = intval($this->getProjectTestUserCount());
		$aArray["times"]	=	date("Y-m-d H:i:s");
		$aRes = $this->oDB->getOne("SELECT `entry` FROM `chartdatas` WHERE `chartid`='".$aArray['chartid']."' AND `days`='$sCurrentDate' " . ' LIMIT 1');
	    if( $this->oDB->ar() > 0 )
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
	 * @author mark
	 */
	function setGameWinUser()
	{
		$sCurrentDate = date('ymd');
	    $aArray['chartid']    = 3;
	    // 更新游戏获利的用户数目
	    $aArray['chartvalue']     = intval($this->getGameNotTestWinUserCount());
	    $aArray['testchartvalue'] = intval($this->getGameTestWinUserCount());
	    $aArray['times']      = date('Y-m-d H:i:s');
	    $aRes = $this->oDB->getOne("SELECT `entry` FROM `chartdatas` WHERE `chartid`='".$aArray['chartid']."' AND `days`='$sCurrentDate' " . ' LIMIT 1');
	    if( $this->oDB->ar() > 0 )
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
	 * 游戏收入总额
	 * @author mark
	 *
	 */
	function setGameMoneytotal()
	{
		$sCurrentDate = date('ymd');
	    $aArray['chartid']    = 10;
	    // 游戏收入
	    $aArray['chartvalue']     = intval($this->getNotTestGameMoneytotal());
	    $aArray['testchartvalue'] = intval($this->getTestGameMoneytotal());
	    $aArray['times']      = date('Y-m-d H:i:s');
	    $aRes = $this->oDB->getOne("SELECT `entry` FROM `chartdatas` WHERE `chartid`='".$aArray['chartid']."' AND `days`='$sCurrentDate' " . ' LIMIT 1');
	    if( $this->oDB->ar() > 0 )
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
	 * 游戏返奖总额
	 * @author mark
	 */
	function setGameWinMoneytotal()
	{
		$sCurrentDate = date('ymd');
	    $aArray['chartid']    = 11;
	    // 游戏返奖
        $aArray['chartvalue']     = intval($this->getNotTestGameWinMoneytotal());
	    $aArray['testchartvalue'] = intval($this->getTestGameWinMoneytotal());
	    $aArray['times']      = date('Y-m-d H:i:s');
	    $aRes = $this->oDB->getOne("SELECT `entry` FROM `chartdatas` WHERE `chartid`='".$aArray['chartid']."' AND `days`='$sCurrentDate' " . ' LIMIT 1');
	    if( $this->oDB->ar() > 0 )
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
	 * 计算高频转出到银行
	 * @author mark
	 */
	function setGameMoneyOut()
	{
		$sCurrentDate = date('ymd');
	    $aArray['chartid']    = 12;
	    // 高频转出银行
	    $aArray['chartvalue']     = intval($this->getNotTestGameMoneyOut());
	    $aArray['testchartvalue'] = intval($this->getTestGameMoneyOut());
	    $aArray['times']      = date('Y-m-d H:i:s');
	    $aRes = $this->oDB->getOne("SELECT `entry` FROM `chartdatas` WHERE `chartid`='".$aArray['chartid']."' AND `days`='$sCurrentDate' " . ' LIMIT 1');
	    if( $this->oDB->ar() > 0 )
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
	 * 银行转入到高频
	 * @author mark
	 */
	function setGameMoneyIn()
	{
		$sCurrentDate = date('ymd');
	    $aArray['chartid']    = 13;
	    // 银行转入频道
	    $aArray['chartvalue']     = intval($this->getNotTestGameMoneyIn());
	    $aArray['testchartvalue'] = intval($this->getTestGameMoneyIn());
	    $aArray['times']      = date('Y-m-d H:i:s');
	    $aRes = $this->oDB->getOne("SELECT `entry` FROM `chartdatas` WHERE `chartid`='".$aArray['chartid']."' AND `days`='$sCurrentDate' " . ' LIMIT 1');
	    if( $this->oDB->ar() > 0 )
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
	 * @author mark
	 */
	function setOrdersTotal()
	{
		$sCurrentDate = date('ymd');
	    $aArray['chartid']    = 20; // 统计帐变个数
	    $aArray['chartvalue']     = intval($this->getNotTestOrdersTotal());
	    $aArray['testchartvalue'] = intval($this->getTestOrdersTotal());
	    $aArray['times']      = date('Y-m-d H:i:s');
	    $aRes = $this->oDB->getOne("SELECT `entry` FROM `chartdatas` WHERE `chartid`='".$aArray['chartid']."' AND `days`='$sCurrentDate' " . ' LIMIT 1');
	    if( $this->oDB->ar() > 0 )
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
	 * @author mark
	 */
	function setTaskTotal()
	{
		$sCurrentDate = date('ymd');
	    $aArray['chartid']    = 21; 
	    // 统计追号个数
	    $aArray['chartvalue']     = intval($this->getNotTestTaskTotal());
	    $aArray['testchartvalue'] = intval($this->getTestTaskTotal());
	    $aArray['times']      = date('Y-m-d H:i:s');
	    $aRes = $this->oDB->getOne("SELECT `entry` FROM `chartdatas` WHERE `chartid`='".$aArray['chartid']."' AND `days`='$sCurrentDate' " . ' LIMIT 1');
	    if( $this->oDB->ar() > 0 )
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
	 * @author mark
	 */
	function setProjectTotal()
	{
		$sCurrentDate = date('ymd');
	    $aArray['chartid']    = 22;
	    // 统计方案个数
	    $aArray['chartvalue']     = intval($this->getNotTestProjectTotal());
	    $aArray['testchartvalue'] = intval($this->getTestProjectTotal());
	    $aArray['times']      = date('Y-m-d H:i:s');
	    $aRes = $this->oDB->getOne("SELECT `entry` FROM `chartdatas` WHERE `chartid`='".$aArray['chartid']."' AND `days`='$sCurrentDate' " . ' LIMIT 1');
	    if( $this->oDB->ar() > 0 )
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
     * 统计游戏测试用户个数(FOR CLI)
     * @author mark
     */
    function getProjectTestUserCount()
    {
        $sSql = " SELECT COUNT(DISTINCT p.`userid`) AS RESULTCOUNT
                  FROM `projects` AS p LEFT JOIN `usertree` AS ut ON(p.`userid`=ut.`userid`)
                  WHERE DATE(p.`writetime`)='".date("Y-m-d")."' AND ut.`istester` != '0' AND ut.`isdeleted` = 0" . ' LIMIT 1';
    	$aResult = $this->oDB->getOne($sSql);
    	return $this->oDB->ar() > 0 ? $aResult['RESULTCOUNT'] : 0;
    }

    /**
     * 统计游戏非测试用户个数(FOR CLI)
     * @author mark
     */
    function getProjectNotTestUserCount()
    {
    	$sSql = " SELECT COUNT(DISTINCT p.`userid`) AS RESULTCOUNT
                  FROM `projects` AS p LEFT JOIN `usertree` AS ut ON(p.`userid`=ut.`userid`)
                  WHERE DATE(p.`writetime`)='".date("Y-m-d")."' AND ut.`istester` = '0' AND ut.`isdeleted` = 0" . ' LIMIT 1';
    	$aResult = $this->oDB->getOne($sSql);
    	return $this->oDB->ar() > 0 ? $aResult['RESULTCOUNT'] : 0;
    }
    
    
    /**
	 * 获取全部非测试用户数 (for cli charts)
	 * @return mix
	 * @author mark
	 */
	public function getAllNotTestUserCount()
	{
	    $sSql = " SELECT COUNT(uc.`userid`) AS RESULTCOUNT 
	               FROM `userchannel` AS uc LEFT JOIN `usertree` AS ut ON(uc.`userid`=ut.`userid`) 
	               WHERE uc.`isdisabled` = '0' AND uc.`channelid` = '".SYS_CHANNELID."' AND ut.`istester` = '0' AND ut.`isdeleted` = 0" . ' LIMIT 1';
	    $aResult = $this->oDB->getOne($sSql);
	    return ($this->oDB->ar() > 0) ? $aResult['RESULTCOUNT'] : 0;
	}
	
	
	/**
	 * 获取全部测试用户数 (for cli charts)
	 * @return mix
	 * @author mark
	 */
	public function getAllTestUserCount()
	{
	    $sSql = " SELECT COUNT(uc.`userid`) AS RESULTCOUNT 
	               FROM `userchannel` AS uc LEFT JOIN `usertree` AS ut ON(uc.`userid`=ut.`userid`) 
	               WHERE uc.`isdisabled` = '0' AND uc.`channelid` = '".SYS_CHANNELID."' AND ut.`istester` != '0' AND ut.`isdeleted` = 0" . ' LIMIT 1';
	    $aResult = $this->oDB->getOne($sSql);
	    return ($this->oDB->ar() > 0) ? $aResult['RESULTCOUNT'] : 0;
	}
	
	
	/**
	 * 中奖用户(测试)个数查询(FOR CLI)
	 * @author mark
	 * @return integer
	 */
	function getGameTestWinUserCount()
	{
	    $sSql = " SELECT COUNT(DISTINCT o.`fromuserid`) AS `RESULTCOUNT` 
	              FROM `orders` AS o LEFT JOIN `usertree` AS ut ON(o.`fromuserid` = ut.`userid`) 
	              WHERE o.`ordertypeid`='".ORDER_TYPE_JJPS."' AND date(o.`actiontime`)='".date("Y-m-d")."' 
	              AND ut.`istester` != '0' AND ut.`isdeleted` = 0" . ' LIMIT 1';
		$aResult = $this->oDB->getOne($sSql);
		return ($this->oDB->ar()>0) ? $aResult["RESULTCOUNT"] : 0;
	}
	
	
	/**
	 * 中奖用户(非测试)个数查询(FOR CLI)
	 * @author mark
	 * @return integer
	 */
	function getGameNotTestWinUserCount()
	{
		 $sSql = " SELECT COUNT(DISTINCT o.`fromuserid`) AS `RESULTCOUNT` 
	              FROM `orders` AS o LEFT JOIN `usertree` AS ut ON(o.`fromuserid` = ut.`userid`) 
	              WHERE o.`ordertypeid`='".ORDER_TYPE_JJPS."' AND date(o.`actiontime`)='".date("Y-m-d")."' 
	              AND ut.`istester` = '0' AND ut.`isdeleted` = 0" . ' LIMIT 1';
		$aResult = $this->oDB->getOne($sSql);
		return ($this->oDB->ar()>0) ? $aResult["RESULTCOUNT"] : 0;
	}
	
	
	/**
	 * 测试账户-游戏收入总额(FOR CLI，不计算返点)
	 * @author mark
	 * @return float
	 */
	function getTestGameMoneytotal()
	{
	    $sSql = " SELECT SUM(o.`amount`) AS `RESULTCOUNT` 
	              FROM `orders` AS o LEFT JOIN `usertree` AS ut ON(o.`fromuserid` = ut.`userid`) 
	              WHERE o.`ordertypeid`='".ORDER_TYPE_JRYX."' AND DATE(o.`actiontime`)='".date("Y-m-d")."'
	              AND ut.`istester` != '0'" . ' LIMIT 1';
		$aResult = $this->oDB->getOne($sSql);
		return ($this->oDB->ar() > 0) ? $aResult["RESULTCOUNT"] : 0;
	}
	
	
	/**
	 * 非测试账户-游戏收入总额(FOR CLI，不计算返点)
	 * @author mark
	 * @return float
	 */
	function getNotTestGameMoneytotal()
	{
		$sSql = " SELECT SUM(o.`amount`) AS `RESULTCOUNT` 
	              FROM `orders` AS o LEFT JOIN `usertree` AS ut ON(o.`fromuserid` = ut.`userid`) 
	              WHERE o.`ordertypeid`='".ORDER_TYPE_JRYX."' AND DATE(o.`actiontime`)='".date("Y-m-d")."'
	              AND ut.`istester` = '0'" . ' LIMIT 1';
		$aResult = $this->oDB->getOne($sSql);
		return ($this->oDB->ar() > 0) ? $aResult["RESULTCOUNT"] : 0;
	}
	
	
	/**
	 * 测试账户-游戏奖金总额(FOR CLI)
	 * @author mark
	 * @return float
	 */
	function getTestGameWinMoneytotal()
	{
	    $sSql = " SELECT SUM(o.`amount`) AS `RESULTCOUNT` 
	              FROM `orders` AS o LEFT JOIN `usertree` AS ut ON(o.`fromuserid` = ut.`userid`) 
	              WHERE o.`ordertypeid`='".ORDER_TYPE_JJPS."' AND DATE(o.`actiontime`)='".date("Y-m-d")."'
	              AND ut.`istester` != '0'" . ' LIMIT 1';
		$aResult = $this->oDB->getOne($sSql);
		return ($this->oDB->ar() > 0) ? $aResult["RESULTCOUNT"] : 0;
	}
	
	
	/**
	 * 非测试账户-游戏奖金总额(FOR CLI)
	 * @author mark
	 * @return float
	 */
	function getNotTestGameWinMoneytotal()
	{
	    $sSql = " SELECT SUM(o.`amount`) AS `RESULTCOUNT` 
	              FROM `orders` AS o LEFT JOIN `usertree` AS ut ON(o.`fromuserid` = ut.`userid`) 
	              WHERE o.`ordertypeid`='".ORDER_TYPE_JJPS."' AND DATE(o.`actiontime`)='".date("Y-m-d")."'
	              AND ut.`istester` = '0'" . ' LIMIT 1';
		$aResult = $this->oDB->getOne($sSql);
		return ($this->oDB->ar() > 0) ? $aResult["RESULTCOUNT"] : 0;
	}
	
	
	/**
	 * 测试账户-频道转出总额(FOR CLI)
	 * @author  mark
	 * @return float
	 */
	function getTestGameMoneyOut()
	{
	    $sSql = " SELECT SUM(o.`amount`) AS `RESULTCOUNT` 
	              FROM `orders` AS o LEFT JOIN `usertree` AS ut ON(o.`fromuserid` = ut.`userid`) 
	              WHERE o.`ordertypeid`='".ORDER_TYPE_PDZC."' AND DATE(o.`actiontime`)='".date("Y-m-d")."'
	              AND ut.`istester` != '0'" . ' LIMIT 1';
		$aResult = $this->oDB->getOne($sSql);
		return ($this->oDB->ar() > 0) ? $aResult["RESULTCOUNT"] : 0;
	}
	
	
	/**
	 * 非测试账户-频道转出总额(FOR CLI)
	 * @author  mark
	 * @return float
	 */
	function getNotTestGameMoneyOut()
	{
	    $sSql = " SELECT SUM(o.`amount`) AS `RESULTCOUNT` 
	              FROM `orders` AS o LEFT JOIN `usertree` AS ut ON(o.`fromuserid` = ut.`userid`) 
	              WHERE o.`ordertypeid`='".ORDER_TYPE_PDZC."' AND DATE(o.`actiontime`)='".date("Y-m-d")."'
	              AND ut.`istester` = '0'" . ' LIMIT 1';
		$aResult = $this->oDB->getOne($sSql);
		return ($this->oDB->ar() > 0) ? $aResult["RESULTCOUNT"] : 0;
	}



	/**
	 * 测试账户-转入频道总额(FOR CLI)
	 * @author  mark
	 * @return float
	 */
	function getTestGameMoneyIn()
	{
	    $sSql = " SELECT SUM(o.`amount`) AS `RESULTCOUNT`
	              FROM `orders` AS o LEFT JOIN `usertree` AS ut ON(o.`fromuserid` = ut.`userid`) 
	              WHERE o.`ordertypeid`='".ORDER_TYPE_ZRPD."' AND DATE(o.`actiontime`)='".date("Y-m-d")."'
	              AND ut.`istester` != '0'" . ' LIMIT 1';
	    $aResult = $this->oDB->getOne($sSql);
	    return ($this->oDB->ar() > 0) ? $aResult["RESULTCOUNT"] : 0;
	}
	
	
	/**
	 * 非测试账户-转入频道总额(FOR CLI)
	 * @author  mark
	 * @return float
	 */
	function getNotTestGameMoneyIn()
	{
	    $sSql = " SELECT SUM(o.`amount`) AS `RESULTCOUNT`
	              FROM `orders` AS o LEFT JOIN `usertree` AS ut ON(o.`fromuserid` = ut.`userid`) 
	              WHERE o.`ordertypeid`='".ORDER_TYPE_ZRPD."' AND DATE(o.`actiontime`)='".date("Y-m-d")."'
	              AND ut.`istester` = '0'" . ' LIMIT 1';
	    $aResult = $this->oDB->getOne($sSql);
	    return ($this->oDB->ar() > 0) ? $aResult["RESULTCOUNT"] : 0;
	}
	
	
	/**
	 * 测试账户-帐变个数
	 * @author mark
	 * @return integer
	 */
	function getTestOrdersTotal()
	{
	    $sSql = " SELECT COUNT(o.`entry`) AS `RESULTCOUNT`
	              FROM `orders` AS o LEFT JOIN `usertree` AS ut ON(o.`fromuserid` = ut.`userid`) 
	              WHERE DATE(o.`actiontime`)='".date("Y-m-d")."' AND ut.`istester` != '0'" . ' LIMIT 1';
	    $aResult = $this->oDB->getOne($sSql);
	    return ($this->oDB->ar() > 0) ? $aResult["RESULTCOUNT"] : 0;
	}
	
	
	/**
	 * 非测试账户-帐变个数
	 * @author mark
	 * @return integer
	 */
	function getNotTestOrdersTotal()
	{
	    $sSql = " SELECT COUNT(o.`entry`) AS `RESULTCOUNT`
	              FROM `orders` AS o LEFT JOIN `usertree` AS ut ON(o.`fromuserid` = ut.`userid`) 
	              WHERE DATE(o.`actiontime`)='".date("Y-m-d")."' AND ut.`istester` = '0'" . ' LIMIT 1';
	    $aResult = $this->oDB->getOne($sSql);
	    return ($this->oDB->ar() > 0) ? $aResult["RESULTCOUNT"] : 0;
	}
	
	
	/**
     * 测试账户-统计游戏方案个数(FOR CLI)
	 * @author mark
	 * @return integer
     */
    function getTestProjectTotal()
    {
        $sSql = " SELECT COUNT(p.`projectid`) AS `RESULTCOUNT`
	              FROM `projects` AS p LEFT JOIN `usertree` AS ut ON(p.`userid` = ut.`userid`) 
	              WHERE DATE(p.`writetime`)='".date("Y-m-d")."' AND ut.`istester` != '0'" . ' LIMIT 1';
        $aResult = $this->oDB->getOne($sSql);
        return ($this->oDB->ar() > 0) ? $aResult["RESULTCOUNT"] : 0;
    }
    
    
    /**
     * 非测试账户-统计游戏方案个数(FOR CLI)
	 * @author mark
	 * @return integer
     */
    function getNotTestProjectTotal()
    {
        $sSql = " SELECT COUNT(p.`projectid`) AS `RESULTCOUNT`
	              FROM `projects` AS p LEFT JOIN `usertree` AS ut ON(p.`userid` = ut.`userid`) 
	              WHERE DATE(p.`writetime`)='".date("Y-m-d")."' AND ut.`istester` = '0'" . ' LIMIT 1';
        $aResult = $this->oDB->getOne($sSql);
        return ($this->oDB->ar() > 0) ? $aResult["RESULTCOUNT"] : 0;
    }
    
    
    /**
	 * 测试账户-追号个数(FOR CLI)
	 * @author mark
	 * @return integer
	 */
	function getTestTaskTotal()
	{
	    $sSql = " SELECT COUNT(t.`taskid`) AS `RESULTCOUNT`
	              FROM `tasks` AS t LEFT JOIN `usertree` AS ut ON(t.`userid` = ut.`userid`) 
	              WHERE DATE(t.`begintime`)='".date("Y-m-d")."' AND ut.`istester` != '0'" . ' LIMIT 1';
        $aResult = $this->oDB->getOne($sSql);
        return ($this->oDB->ar() > 0) ? $aResult["RESULTCOUNT"] : 0;
	}
	
	
	/**
	 * 非测试账户-追号个数(FOR CLI)
	 * @author mark
	 * @return integer
	 */
	function getNotTestTaskTotal()
	{
	    $sSql = " SELECT COUNT(t.`taskid`) AS `RESULTCOUNT`
	              FROM `tasks` AS t LEFT JOIN `usertree` AS ut ON(t.`userid` = ut.`userid`) 
	              WHERE DATE(t.`begintime`)='".date("Y-m-d")."' AND ut.`istester` = '0'" . ' LIMIT 1';
        $aResult = $this->oDB->getOne($sSql);
        return ($this->oDB->ar() > 0) ? $aResult["RESULTCOUNT"] : 0;
	}
}
?>