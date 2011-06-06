<?php
/**
 * 帮助信息模型 ( 平台说明管理 | 常见问题管理 )
 * 
 * 功能：
 * 
 * @author     Tom
 * @version   1.1.0
 * @package   passport
 */

class model_helps extends basemodel 
{
    private $sType = 'helps';    // 获取的数据库信息类型
                                 // 对应 `helpinfo`.`subject`
                                 // 'comment'  => 银行说明
                                 // 'faq'      => 常见问题
    public function __construct( $sType = 'comment'  ) 
    {
        parent::__construct();
        	// add 4/6/2010 verinfo 
        if( !in_array( $sType, array( 'comment', 'faq', 'intro', 'verinfo' )))
        { // 数据整理
            $sType = 'comment'; // 默认值 => 银行说明
        }
        $this->sType = $sType;
    }

    
    /**
     * [管理员后台] 获取公告列表
     * @param  string $sFields       查询字段
     * @param  string $sCondition    查询条件
     * @param  int    $iPageRecords  每一页的记录数
     * @param  int    $iCurrPage     当前页
     * @return array
     * @author Tom
     */
    public function & getHelpsList( $sFields = "*" , $sCondition = "1", $iPageRecords = 25 , $iCurrPage = 1)
    {
        $sCondition = $sCondition . " AND `subject`='".$this->sType."' ";
        $sTableName = ' `helpinfo` a LEFT JOIN `adminuser` c ON a.`adminid`=c.`adminid` ';
        $sFields    = ' a.`id`, a.`channelid`, a.`sorts`,a.`tagname`, a.`subject`,`lastupdatetime`,a.`adminid`,
                        `isdel`,c.adminname AS sendername ';
        return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, 
                                          $iCurrPage, ' ORDER BY `sorts` ASC ' );
    }



    /**
     * 插入信息
     * @param strint $aPostArr  用户提交的数据
     * @return int 
     * @author Tom
     */
    public function helpsInsert( $aPostArr )
    {
        // 数据整理
        $aArr['subject']        = $this->sType;
        $aArr['channelid']      = isset($aPostArr['channelid']) ? intval($aPostArr['channelid']) : 1;
        $aArr['tagname']        = isset($aPostArr['tagname']) ? daddslashes($aPostArr['tagname']) : '';
        $aArr['sorts']          = isset($aPostArr['sorts']) ? intval($aPostArr['sorts']) : 100;
        $aArr['content']        = isset($aPostArr['FCKeditor1']) ? daddslashes(trim($aPostArr['FCKeditor1'])) : '';
        $aArr['lastupdatetime'] = date('Y-m-d H:i:s');
        $aArr['adminid']        = intval($_SESSION['admin']);
        $aArr['isdel']          = 0;
        // 2009-11-04 12:41 Tom 增加阅读者权限
        //    PHP 程序提交值     1=总代与总代管理员,  2=1代,  4=普代,  8=会员
        //  MYSQL 字段   0=全部, 1=总代与总代管理员,  2=1代,  4=普代,  8=会员
        $aArr['readergroup']    = 0;
        if( !empty($aPostArr['readergroup']) && is_array($aPostArr['readergroup']) )
        {
       		foreach( $aPostArr['readergroup'] as $v )
       		{
       			$aArr['readergroup'] += $v;
       		}
       		if( $aArr['readergroup'] == 15 )
       		{ // 全选
       			$aArr['readergroup'] = 0;
       		}
        }
        return $this->oDB->insert( 'helpinfo', $aArr );
    }


    /**
     * 更新信息
     * @param  int   $iId        信息ID
     * @param  array $aPostArr   用户提交的数据
     * @return int 
     * @author Tom
     */
    public function helpsUpdate( $iId, $aPostArr )
    {
        $iId = is_numeric($iId) && $iId > 0 ? intval($iId) : 0;
        if( $iId == 0 )
        {
            return -1; // 数据初始错误
        }
        $aArr['subject']        = $this->sType;
        $aArr['channelid']      = isset($aPostArr['channelid']) ? intval($aPostArr['channelid']) : 1;
        $aArr['tagname']        = isset($aPostArr['tagname']) ? daddslashes($aPostArr['tagname']) : '';
        $aArr['sorts']          = isset($aPostArr['sorts']) ? intval($aPostArr['sorts']) : 100;
        $aArr['content']        = isset($aPostArr['FCKeditor1']) ? daddslashes(trim($aPostArr['FCKeditor1'])) : '';
        $aArr['lastupdatetime'] = date('Y-m-d H:i:s');
        $aArr['adminid']        = intval($_SESSION['admin']);
        $aArr['isdel']          = isset($aPostArr['isdel']) ? intval($aPostArr['isdel']) : 0;
        // 2009-11-04 12:41 Tom 增加阅读者权限
        //    PHP 程序提交值     1=总代与总代管理员,  2=1代,  4=普代,  8=会员
        //  MYSQL 字段   0=全部, 1=总代与总代管理员,  2=1代,  4=普代,  8=会员
        $aArr['readergroup']    = 0;
        if( !empty($aPostArr['readergroup']) && is_array($aPostArr['readergroup']) )
        {
            foreach( $aPostArr['readergroup'] as $v )
            {
                $aArr['readergroup'] += $v;
            }
            if( $aArr['readergroup'] == 15 )
            { // 全选
                $aArr['readergroup'] = 0;
            }
        }
        return $this->oDB->update( 'helpinfo', $aArr, " `id`= '$iId' LIMIT 1 " );
    }
    
    
    /**
     * 根据ID 读取标签业内容
     * @param int $iId          标签页Id
     * @param string $sFields   查询字段
     * @author Tom
     */
    public function & getOne( $iId = 0, $sFields = '*' )
    {
        $iId        = (empty($iId) || !is_numeric($iId)) ? 0 : intval($iId);
        $sFields    = empty($sFields) ? '*' : $sFields;
        if( $iId == 0 )
        {
            return -1;
        }
        $sSql = " SELECT ".$sFields." FROM `helpinfo` WHERE `id`='$iId' AND `subject`='".$this->sType."' LIMIT 1 ";
        return $this->oDB->getOne( $sSql );
    }

    /**
     * 获取所有标签
     * @param int $iChannelid   频道Id
     * @return array
     * @author Tom
     */
    public function & getAllTag( $iChannelid = 0 )
    {
        $sSqlAdd = '';
        if( !empty($iChannelid) && in_array( $iChannelid, array(1,2) ) )
        {
            $sSqlAdd = ' AND `channelid`= ' . intval($iChannelid).' ';
        }

        // 2009-11-04 12:41 Tom 增加阅读者权限
        //   0=全部, 1=总代与总代管理员,  2=1代,  4=普代,  8=会员
        $iUserGrp = 0;
    	if( $_SESSION['usertype'] == 0 )
    	{ // 普通用户
            $iUserGrp = 8;
    	}
    	elseif( $_SESSION['usertype'] == 2 )
    	{ // 总代管理员
    		$iUserGrp = 1;
    	}
    	elseif( $_SESSION['usertype'] == 1 )
    	{ // 所有代理
    		$oUser  = A::singleton("model_user");
            $aRows  = $oUser->getUsersProfile( ' ut.parentid, ut.parenttree ', '',  " AND ut.`userid`= '".intval($_SESSION['userid'])."' " );
            if( $aRows['parentid'] == 0 )
            { // 总代
            	$iUserGrp = 1;
            }
            else
            {
	            $iCount = count( explode( ',', $aRows['parenttree'] ) );
	            if( $iCount == 1 )
	            { // 1 代
	            	$iUserGrp = 2;
	            }
	            else
	            { // 普代
	            	$iUserGrp = 4;
	            }
            }
    	}
        $sSqlAdd .= " AND ( `readergroup`=0 OR `readergroup` & ". intval($iUserGrp) ." != 0 ) ";
        $sSql = " SELECT `tagname`,`content` FROM `helpinfo` WHERE `isdel`=0 AND `subject`='".
                $this->sType."' $sSqlAdd ORDER BY `sorts` ASC ";
        return $this->oDB->getAll( $sSql );
    }


    /**
     * 删除标签
     * @author tom
     * @param array $aHtmlCheckBox
     * @return int
     */
    public function helpDel( $aHtmlCheckBox = array() )
    {
        $sWhere         = '';
        $sDelMessageIds = '';
        if( empty($aHtmlCheckBox) || !is_array($aHtmlCheckBox) )
        {
            return FALSE;
        }
        foreach( $aHtmlCheckBox as $v )
        {
            if( is_numeric($v) )
            {
                $sDelMessageIds .= $v.",";
            }
        }
        if( substr($sDelMessageIds, -1, 1) == ',' )
        {
            $sDelMessageIds = substr( $sDelMessageIds, 0, -1 );
        }
        if( $sDelMessageIds == '' )
        { // 消息ID数组为空, 直接返回错误. 不进行更新操作
            return FALSE;
        }
        $sWhere .= " AND `id` IN ( $sDelMessageIds ) ";
        $this->oDB->query( "UPDATE `helpinfo` SET `isdel`='1' WHERE 1 $sWhere" );
        return $this->oDB->ar();
    }

}
?>
