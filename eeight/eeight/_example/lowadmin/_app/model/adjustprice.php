<?php
/**
 * 地板杆塔调价模型
 *
 * 功能：
 *      设置动态调价的相关信息
 *      CRUD
 *      --insert                增加一个用户组
 *      --delete                删除用户组
 *      --update                修改用户组
 *      --getById               根据ID读取用户组信息
 *      --getOne                根据自定义条件取一条记录
 *      --getList               根据自定义条件取用户组列表
 *      
 *      --getGroupID            根据用户ID，获取其组的团队下面的总代组ID，一代组ID，普代组ID，会员组ID
 *      --getGroupByUser        根据用户ID，获取属于他的组列表
 * 
 * @author  james
 * @version 1.1.0
 * @package lowadmin
 * @since   2009/07/26
 */

class model_adjustprice extends basemodel
{
    /**
     * 构造函数
     */
    function __construct( $aDBO=array() )
    {
        parent::__construct( $aDBO );
    }
    
    
    
    /**
     * 增加新的调价方案
     *
     * @author  james 09/07/26
     * @access  public
     * @param   array $aArr //插入数据数组
     * @return  失败返回FALSE，成功返回插入ID
     */
    public function adjustPriceInsert( $aArr )
    {
    	if( empty($aArr) || !is_array($aArr) )
    	{
    		return FALSE;
    	}
    	//数据安全和完整性检测
    	if( empty($aArr['title']) )
    	{//标题
    		return FALSE; 
    	}
    	$aArr['title'] = daddslashes( $aArr['title'] );
    	if( empty($aArr['lotteryid']) || !is_numeric($aArr['lotteryid']) || intval($aArr['lotteryid']) < 0 )
    	{//彩种安全检查
    		return FALSE;
    	}
    	$aArr['lotteryid'] = intval($aArr['lotteryid']);
    	if( !isset($aArr['winline']) || !is_numeric($aArr['winline']) )
    	{//上调基准线,默认0
    		$aArr['winline'] = 0;
    	}
    	$aArr['winline']  = intval($aArr['winline']);
    	if( !isset($aArr['loseline']) || !is_numeric($aArr['loseline']) )
        {//下调基准线,默认0
            $aArr['loseline'] = 0;
        }
        $aArr['loseline']   = intval($aArr['loseline']);
        $aArr['updatetime'] = date("Y-m-d H:i:s", time());
        $aArr['isverify']   = 0;
        $aArr['isactive']   = 0;
        return $this->oDB->insert( 'adjustprice', $aArr );
    }
    
    
    
    /**
     * 删除动态调价方案[会同时删除方案下的具体调价信息](启用了事务)
     *
     * @author  james 09/07/26
     * @access  public
     * @param   string $sCondition
     * @return  boolean //成功返回TRUE，失败返回FALSE
     */
    public function adjustPriceDelete( $sCondition='' )
    {
    	$sWhere = empty($sCondition) ? "" : " WHERE ".$sCondition;
    	//先查询符合条件的数据
    	$sSql   = "SELECT `groupid` FROM `adjustprice` ".$sWhere;
    	$aData  = $this->oDB->getAll( $sSql );
    	if( empty($aData) )
    	{//没有符合条件的数据则直接返回TRUE
    		return TRUE;
    	}
    	foreach( $aData as & $v )
    	{
    		$v = intval( $v['groupid'] );
    	}
    	$sCondition = count($aData)>1 ? " `groupid` IN(".implode(',', $aData).") " : " `groupid`='".$aData[0]."' ";
    	$this->oDB->doTransaction();   //开始事务
    	$this->oDB->delete( 'adjustprizedetail', $sCondition );
    	if( $this->oDB->errno() > 0 )
    	{//删除对应的具体调价信息[adjustprizedetail表]
    		$this->oDB->doRollback();
    		return FALSE;
    	}
    	$this->oDB->delete( 'adjustprice', $sCondition );
        if( $this->oDB->errno() > 0 )
        {//删除方案信息[adjustprice表]
            $this->oDB->doRollback();
            return FALSE;
        }
        $this->oDB->doCommit();
        return TRUE;
    }
    
    
    
    /**
     * 修改调价方案总体信息
     *
     * @author  james 09/07/26
     * @access  public
     * @param   array   $aArr
     * @param   string  $sCondition
     * @return 成功返回影响行数，失败返回FALSE
     */
    public function adjustPriceUpdate( $aArr, $sCondition="1" )
    {
    	if( empty($aArr) || !is_array($aArr) )
    	{
    		return FALSE;
    	}
    	//数据安全和完整性检测
        if( !empty($aArr['title']) )
        {//标题
            $aArr['title'] = daddslashes( $aArr['title'] );
        }
        if( !empty($aArr['lotteryid']) && is_numeric($aArr['lotteryid']) && intval($aArr['lotteryid']) > 0 )
        {//彩种安全检查
            $aArr['lotteryid'] = intval($aArr['lotteryid']);
        }
        if( isset($aArr['winline']) && is_numeric($aArr['winline']) && $aArr['winline'] >= 0 )
        {//上调基准线,默认0为不调价
            $aArr['winline']   = intval($aArr['winline']);
        }
        if( isset($aArr['loseline']) && is_numeric($aArr['loseline']) && $aArr['loseline'] >= 0 )
        {//下调基准线,默认0为不调价
            $aArr['loseline']  = intval($aArr['loseline']);
        }
        $aArr['updatetime'] = date("Y-m-d H:i:s", time());
        if( isset($aArr['isverify']) && is_numeric($aArr['isverify']) )
        {//审核
        	$aArr['isverify']   = intval($aArr['isverify'])>0 ? 1 : 0;
        }
        if( isset($aArr['isactive']) )
        {//不允许修改激活状态，单独修改
        	unset($aArr['isactive']);
        }
        return $this->oDB->update( 'adjustprice', $aArr, $sCondition );
    }
    
    
    
    /**
     * 获取一条调价方案
     *
     * @author  james 09/07/27
     * @access  public
     * @param   string  $sFields
     * @param   string  $sCondition
     * @return  array   //返回一条记录结果集
     */
    public function & adjustPriceGetOne( $sFields='', $sCondition='' )
    {
    	$sFields    = empty($sFields) ? "*" : daddslashes($sFields);
    	$sCondition = empty($sCondition) ? "" : " WHERE ".$sCondition;
    	$sSql       = "SELECT ".$sFields." FROM `adjustprice` ".$sCondition;
    	return $this->oDB->getOne( $sSql );
    }
    
    
    
    /**
     * 获取调价方案列表
     *
     * @author  james 09/07/27
     * @access  public
     * @param   string   $sFields
     * @param   string   $sCondition
     * @param   string   $sOrderBy
     * @param   int      $iPageRecord
     * @param   int      $iCurrentPage
     * @return  array   //返回多条记录集
     */
    public function & adjustPriceGetList( $sFields='', $sCondition='', $sOrderBy='', $iPageRecord=0, $iCurrentPage=0 )
    {
    	$sFields    = empty($sFields) ? "*" : daddslashes($sFields);
    	if( $iPageRecord == 0 )
    	{//不分页显示
    		$sCondition = empty($sCondition) ? "" : " WHERE ".$sCondition;
            $sSql       = "SELECT ".$sFields." FROM `adjustprice` ".$sCondition." ".$sOrderBy;
            return $this->oDB->getAll( $sSql );
    	}
        else 
        {
        	$sCondition = empty($sCondition) ? "1" : $sCondition;
        	return $this->oDB->getPageResult( 'adjustprice', $sFields, $sCondition, $iPageRecord, $iCurrentPage, 
        	                                   $sOrderBy );
        }
    }
    
    
    
    /**
     * 增加一条调价线
     *
     * @author  james 09/07/27
     * @access  public
     * @param   array    $aArr
     * @return  //成功返回插入ID，失败返回FALSE
     */
    public function adjustPriceDetailInsert( $aArr )
    {
    	if( empty($aArr) || !is_array($aArr) )
    	{
    		return FALSE;
    	}
    	//数据安全以及完整性检查
    	if( empty($aArr['groupid']) || !is_numeric($aArr['groupid']) || $aArr['groupid'] <= 0 )
    	{//方案组ID
    		return FALSE;
    	}
    	$aArr['groupid'] = intval($aArr['groupid']);
    	if( !isset($aArr['uplimit']) || !is_numeric($aArr['uplimit']) || $aArr['uplimit'] < 0 )
        {//调价区间线
            return FALSE;
        }
        $aArr['uplimit'] = intval($aArr['uplimit']);
        if( empty($aArr['percent']) || !is_numeric($aArr['percent']) || $aArr['percent']<=0 || $aArr['percent']>1 )
        {//调价比例
            return FALSE;
        }
        $aArr['percent'] = floatval($aArr['percent']);
        $aArr['isup']    = (isset($aArr['isup']) && intval($aArr['isup'])==1) ? 1 : 0;
        return $this->oDB->insert( 'adjustprizedetail', $aArr );
    }
    
    
    
    /**
     * 删除调价线
     *
     * @author  james 09/07/27
     * @access  public
     * @param   string   $sCondition
     * @return  //成功返回所影响的行数，失败返回FALSE
     */
    public function adjustPriceDetailDelete( $sCondition='1' )
    {
    	return $this->oDB->delete( 'adjustprizedetail', $sCondition );
    }
    
    
    
    /**
     * 修改调价线
     *
     * @author  james 09/07/27
     * @access  public
     * @param   array    $aArr
     * @param   string   $sCondition
     * @return  //成功返回所影响的行数，失败返回FALSE
     */
    public function adjustPriceDetailUpdate( $aArr, $sCondition="1" )
    {
        if( empty($aArr) || !is_array($aArr) )
        {
            return FALSE;
        }
        //数据安全以及完整性检查
        if( isset($aArr['groupid']) )
        {//方案组ID不允许修改，业务逻辑上不需要
            unset($aArr['groupid']);
        }
        if( !isset($aArr['uplimit']) && is_numeric($aArr['uplimit']) && $aArr['uplimit'] >= 0 )
        {//调价区间线
            $aArr['uplimit'] = intval($aArr['uplimit']);
        }
        if( !empty($aArr['percent']) && is_numeric($aArr['percent']) && $aArr['percent']>0 && $aArr['percent']<=1 )
        {//调价比例
            $aArr['percent'] = floatval($aArr['percent']);
        }
        if( isset($aArr['isup']) )
        {
        	$aArr['isup']    = intval($aArr['isup'])>0 ? 1 : 0;
        }
        return $this->oDB->update( 'adjustprizedetail', $aArr, $sCondition );
    }
    
    
    
    /**
     * 获取一条调价线
     *
     * @author  james 09/07/27
     * @access  public
     * @param   string   $sFields
     * @param   string   $sCondition
     * @return  array   //返回一条记录结果集
     */
    public function & adjustPriceDetailGetOne( $sFields='', $sCondition='' )
    {
    	$sFields    = empty($sFields) ? "*" : daddslashes($sFields);
        $sCondition = empty($sCondition) ? "" : " WHERE ".$sCondition;
        $sSql       = "SELECT ".$sFields." FROM `adjustprizedetail` ".$sCondition;
        return $this->oDB->getOne( $sSql );
    }
    
    
    
    /**
     * 获取多条记录集合列表 
     *
     * @author  james 09/07/27
     * @access  public
     * @param   string   $sFields
     * @param   string   $sCondition
     * @param   string   $sOrderBy
     * @param   int      $iPageRecord
     * @param   int      $iCurrentPage
     * @return  array   //返回多条记录集列表
     */
    public function & adjustPriceDetailGetList( $sFields='', $sCondition='', $sOrderBy='', 
                                                $iPageRecord=0, $iCurrentPage=0 )
    {
        $sFields    = empty($sFields) ? "*" : daddslashes($sFields);
        if( $iPageRecord == 0 )
        {//不分页显示
            $sCondition = empty($sCondition) ? "" : " WHERE ".$sCondition;
            $sSql       = "SELECT ".$sFields." FROM `adjustprizedetail` ".$sCondition." ".$sOrderBy;
            return $this->oDB->getAll( $sSql );
        }
        else 
        {
            $sCondition = empty($sCondition) ? "1" : $sCondition;
            return $this->oDB->getPageResult( 'adjustprizedetail', $sFields, $sCondition, $iPageRecord, 
                                              $iCurrentPage, $sOrderBy );
        }
    }
    
    
    
    /**
     * 审核一个方案
     *
     * @author  james 09/07/30
     * @access  public
     * @param   int      $iGroupId
     * @return  mixed    //成功返回所影响的行数，参数错误返回0，没有调价线返回-1
     */
    public function adjustPriceVerify( $iGroupId )
    {
        if( empty($iGroupId) || !is_numeric($iGroupId) || $iGroupId<=0 )
        {
            return 0;
        }
        $iGroupId = intval($iGroupId);
        //检测是否有相应的调价线
        $aResult = $this->adjustPriceDetailGetOne( '`entry`', "`groupid`='".$iGroupId."'");
        if( empty($aResult) )
        {//没有对应的调价线
        	return -1;
        }
        return $this->oDB->update( 'adjustprice', array('isverify'=>1), 
                                   "`groupid`='".$iGroupId."' AND `isverify`='0' " );
    }
    
    
    
    /**
     * 激活某彩种下的一个调价方案
     *
     * @author  james 09/07/27
     * @access  public
     * @param   int     $iGroupId   //方案ID
     * @return  mixed   //0:没有相应方案，-1方案未审核，-2更新方案状态失败，TRUE成功
     */
    public function adjustPriceActive( $iGroupId )
    {
    	if( empty($iGroupId) || !is_numeric($iGroupId) || $iGroupId<=0 )
    	{
    		return 0;
    	}
    	$iGroupId = intval($iGroupId);
    	//获取相应的彩种ID
    	$aResult = $this->adjustPriceGetOne( '`lotteryid`,`isverify`', "`groupid`='".$iGroupId."'" );
    	if( empty($aResult) )
    	{
    		return 0;
    	}
    	if( $aResult['isverify'] == 0 )
    	{//未审核不能激活
    		return -1;
    	}
    	//把该彩种下的其他方案的激活状态改为未激活
    	$sCondition = " `isactive`='1' AND `lotteryid`='".$aResult['lotteryid']."' ";
    	$this->oDB->doTransaction();   //启用事务
    	$this->oDB->update( 'adjustprice', array('isactive'=>0), $sCondition );
    	if( $this->oDB->errno() > 0 )
    	{
    		$this->oDB->doRollback();
    		return -2;
    	}
    	$this->oDB->update( 'adjustprice', array('isactive'=>1), "`groupid`='".$iGroupId."' AND `isactive`='0'" );
    	if( $this->oDB->errno() > 0 )
    	{
    		$this->oDB->doRollback();
            return -2;
    	}
    	$this->oDB->doCommit();
    	return TRUE;
    }
    
    
    
    /**
     * 取消激活某个方案
     *
     * @author  james 09/07/30
     * @access  public
     * @param   int      $iGroupId
     * @return  boolean //成功返回TRUE，失败返回FALSE
     */
    public function adjustPriceUnactive( $iGroupId )
    {
        if( empty($iGroupId) || !is_numeric($iGroupId) || $iGroupId<=0 )
        {
            return FALSE;
        }
        $sCondition = " `groupid`='".$iGroupId."' AND `isactive`='1' ";
        $mResult    = $this->oDB->update( 'adjustprice', array('isactive'=>0), $sCondition );
        if( $mResult == FALSE )
        {
        	return FALSE;
        }
        return TRUE;
    }
    
    
    
    /**
     * 插入一组方案
     *
     * @author  james 09/07/31
     * @access  public
     * @param   string   $sTitle
     * @param   int      $iLotteryId
     * @param   int      $iWinLine
     * @param   int      $iLoseLine
     * @param   array $aArr
     * @return  mixed  成功返回TRUE，失败返回0，-1 ，-2
     */
    public function adjustInsert( $sTitle, $iLotteryId, $iWinLine=0, $iLoseLine=0, $aArr=array() )
    {
    	if( empty($aArr) || !is_array($aArr) )
    	{//方案调价线数据错误
    		return 0;
    	}
    	$aData = array(
    	               'title'     => $sTitle,
    	               'lotteryid' => $iLotteryId,
    	               'winline'   => $iWinLine,
    	               'loseline'  => $iLoseLine
    	           );
    	$this->oDB->doTransaction();//开始事务
    	$mResult = $this->adjustPriceInsert( $aData );
    	if( $mResult == FALSE )
    	{//插入方案组表失败
    		$this->oDB->doRollback();
    		return -1;
    	}
    	$iLen = count($aArr);
    	for( $i=0; $i<$iLen; $i++ )
    	{//写入调价线
    		if( $aArr[$i]['percent'] > 0 )
    		{
    			$aArr[$i]['groupid'] = $mResult;
    			$aArr[$i]['percent'] = $aArr[$i]['percent']/100;
    			$mTempResult = $this->adjustPriceDetailInsert( $aArr[$i] );
    			if( $mTempResult == FALSE )
    			{//插入方案失败
    				$this->oDB->doRollback();
    				return -2;
    				break;
    			}
    		}
    	}
    	$this->oDB->doCommit();//提交事务
    	return TRUE;
    }
    
    
    
    /**
     * 修改一组方案
     *
     * @author  james 09/07/31
     * @access  public
     * @param   int      $iGroupId 方案组ID
     * @param   string   $sTitle    方案标题
     * @param   int      $iLotteryId    彩种ID
     * @param   int      $iWinLine      开始上调线
     * @param   int      $iLoseLine     开始下调线
     * @param   array    $aArr          调线集合
     * @return  mixed  成功返回TRUE，失败返回0，-1 ，-2
     */
    public function adjustUpdate( $iGroupId, $sTitle, $iLotteryId, $iWinLine=0, $iLoseLine=0, $aArr=array() )
    {
    	if( empty($iGroupId) || !is_numeric($iGroupId) || $iGroupId <= 0 )
    	{
    		return 0;
    	}
    	$iGroupId = intval($iGroupId);
        if( empty($aArr) || !is_array($aArr) )
        {//方案调价线数据错误
            return 0;
        }
        $aData = array(
				'title'     => $sTitle,
				'lotteryid' => $iLotteryId,
				'winline'   => $iWinLine,
				'loseline'  => $iLoseLine,
				'isverify'  => 0, // 审核状态
        );
        $this->oDB->doTransaction();//开始事务
        $mResult = $this->adjustPriceUpdate( $aData, " `isactive`!=1 AND `groupid`='".$iGroupId."' " );
        if( $mResult == FALSE )
        {//修改方案组表失败 (禁止编辑已激活的变价方案)
            $this->oDB->doRollback();
            return -1;
        }
        //删除所有调价线重新增加
        $mResult = $this->adjustPriceDetailDelete( " `groupid`='".$iGroupId."' " );
        if( $mResult === FALSE )
        {
        	$this->oDB->doRollback();
            return -2;
        }
        $iLen = count($aArr);
        for( $i=0; $i<$iLen; $i++ )
        {//写入调价线
            if( $aArr[$i]['percent'] > 0 )
            {
                $aArr[$i]['groupid'] = $iGroupId;
                $aArr[$i]['percent'] = $aArr[$i]['percent']/100;
                $mTempResult = $this->adjustPriceDetailInsert( $aArr[$i] );
                if( $mTempResult == FALSE )
                {//插入方案失败
                    $this->oDB->doRollback();
                    return -2;
                    break;
                }
            }
        }
        $this->oDB->doCommit();//提交事务
        return TRUE;
    }
}
?>