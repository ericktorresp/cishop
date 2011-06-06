<?php
/**
 * 公告列表模型
 * 
 * 功能：
 * -- noticesgetList     获取公告列表
 * -- noticesgetOne      根据ID和频道ID读取一条公告内容(前台用)
 * 
 * @author	   saul, Tom
 * @version   1.1.0
 * @package   passportadmin
 * @since     090429 - 090618
 */

class model_notices extends basemodel 
{
    /**
     * 构造函数
     * 
     * @access  public
     * @return  void
     */
    function __construct( $aDBO=array() )
    {
        parent::__construct( $aDBO );
    }
    
    
    
	/**
	 * 根据频道读取公告列表(前台用)
	 * 
	 * @access 	public
	 * @author 	james	09/05/18
	 * @param 	string	$sFields	//要读取的公告字段内容
	 * @param 	string	$sCondition	//附加搜索条件和排序条件
	 * @param 	int		$iChannelId	//频道ID
	 * @return 	array	//公告列表，失败返回空数组	
	 */
	public function & noticesgetList( $sFields='*', $sCondition='', $iChannelId=0 )
	{
		$sFields	= empty($sFields) ? '*' : $sFields;
		$iChannelId = (empty($iChannelId) || !is_numeric($iChannelId)) ? 0 : intval($iChannelId);
		$sSql = " SELECT ".$sFields." FROM `notices` 
				WHERE `channelid`='".$iChannelId."' AND `checkid`>'0' AND `isdel`='0' ".$sCondition
				." ORDER BY `checktime` DESC ";
		return $this->oDB->getAll($sSql);
	}



	/**
	 * 根据ID和频道ID读取一条公告内容(前台用)
	 * 
	 * @access 	public
	 * @author 	james	09/05/18
	 * @param 	int		$iNoticeId	//公告ID,为0则读取最新的一条
	 * @param 	string	$sFields	//要读取的字段信息
	 * @param 	int		$iChannelId	//频道ID
	 * @return 	array	//公告内容信息，失败返回空数组
	 */
	public function & noticesgetOne( $iNoticeId=0, $sFields='*', $iChannelId=0 )
	{
		$iNoticeId	= (empty($iNoticeId) || !is_numeric($iNoticeId)) ? 0 : intval($iNoticeId);
		$sFields	= empty($sFields) ? '*' : $sFields;
		$iChannelId = (empty($iChannelId) || !is_numeric($iChannelId)) ? 0 : intval($iChannelId);
		$sCondition = '';
		if( $iNoticeId == 0 )
		{
			$sCondition .= "AND `istop`='1' ORDER BY `id` DESC LIMIT 1 ";
		}
		else 
		{
			$sCondition .= " AND `id`='".$iNoticeId."' ";
		}
		$sSql = " SELECT ".$sFields." FROM `notices` 
				WHERE `channelid`='".$iChannelId."' AND `checkid`>'0' AND `isdel`='0' ".$sCondition;
		return $this->oDB->getOne( $sSql );
	}
}
?>