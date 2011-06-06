<?php
/**
 * 文件 : /_app/model/firewall.php
 * 功能 : 数据模型 - 防火墙 [ 规则 | 行为 ]
 * 
 * @author	  Tom
 * @version  1.0.0
 * @package  passportadmin
 * @since    2009-06-15
 */

class model_firewall extends basemodel 
{
    /**
     * 获取防火墙 行为ID和行为名
     * @author Tom 090511
     */
    public function getDistintFwAction( $bReturnArray = TRUE, $sSelected = '' )
	{
	    $aTmpArray = $this->oDB->getAll("SELECT `id`,`actionname` FROM `firewallaction` WHERE `isdisabled` = 0 ");
	    if( $this->oDB->ar() < 1 )
	    {
	        return '';
	    }
	    $aReturn = '';
	    if( $bReturnArray == TRUE )
	    {
            foreach( $aTmpArray as $k => $v )
    	    {
    	        $aReturn[$k] = $v['id'];
    	        $aReturn[$k] = $v['actionname'];
    	    }
            return $aReturn;
	    }
	    else
	    {
	        foreach( $aTmpArray as $k => $v )
	        {
	            $sSel = $sSelected==$v['id'] ? 'SELECTED' : '';
	            $aReturn .= "<OPTION $sSel VALUE=\"".$v['id']."\">".$v['actionname']."</OPTION>";
	        }
	        return $aReturn;
	    }
	}



	/**
	 * 获取用户表数据
	 * @author Tom 090511
	 * @param  string $sWhere
	 * @return array
	 */
	public function & getUserIdByCondition( $sWhere )
	{
	    return $this->oDB->getAll( "SELECT `userid` FROM `users` WHERE 1 $sWhere " );
	}



	/**
     * 获取防火墙规则列表
     * 根据搜索条件, 获取防火墙黑名单列表 
     * @author Tom 090511
     * @return array
     */
    public function & getFirewallList( $sFields = "*" , $sCondition = "1", $iPageRecords = 25 , $iCurrPage = 1)
	{
	    $sTableName = ' `firewallrules` a LEFT JOIN `firewallaction` b ON a.actionid = b.id LEFT JOIN `users` c ON a.`userid` = c.userid ';
	    $sFields    = ' a.*,b.*,a.`isdisabled` AS rulestatus,c.username ';
	    return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, $iCurrPage );
	}



	/**
	 * 获取防火墙行为列表
     * @author Tom 090511
     * @return array
	 */
    public function & getFirewallActionList( $sFields = "*" , $sCondition = "1", $iPageRecords = 25 , $iCurrPage = 1)
	{
	    $sTableName = ' `firewallaction` ';
	    $sFields    = ' * ';
	    return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, $iCurrPage );
	}



	/**
	 * 查询 FirewallAction 信息
     * @author Tom 090511
     * @return array
	 */
	public function getActionChild( $iMenuId , $bAll )
	{
		if( $bAll )
		{
			if( $iMenuId==0 )
			{
				return $this->oDB->getAll("SELECT * FROM `firewallaction` ");
				
			}
			else
			{
				return $this->oDB->getAll("SELECT * FROM `firewallaction` WHERE find_in_set( '".$iMenuId."', `parentstr`) ");
			}
		}
		else
		{
			return $this->oDB->getAll("SELECT * from `firewallaction` WHERE `parentid`='".$iMenuId."'");
		}
	}



	/**
	 * 根据防火墙行为ID, 获取行为数据
     * @author Tom 090511
	 * @param int $iActionId
	 * @return int
	 */
	public function getActionRowsById( $iActionId = 0 )
	{
	    $iActionId = intval($iActionId);
	    if( $iActionId == 0 )
	    {
	        return -1;
	    }
	    $aReturn = $this->oDB->getOne( "SELECT * FROM `firewallaction` WHERE `id`='$iActionId' LIMIT 1 " );
	    if( $this->oDB->ar() < 1 )
	    {
	        return -1;
	    }
	    return $aReturn;
	}



	/**
	 * 根据 ID 号,删除防火墙行为
     * @author Tom 090511
	 * @param int $iActionId
	 * @return int 受影响行数
	 */
    public function delActionRowById( $iActionId = 0 )
	{
	    $iActionId = intval($iActionId);
	    if( $iActionId == 0 )
	    {
	        return -1;
	    }
	    // 删除防火墙行为的同时, 删除此类行为的规则
	    $this->oDB->delete( 'firewallrules', " `actionid`='$iActionId' " );
	    return $this->oDB->delete( 'firewallaction', " `id`='$iActionId' LIMIT 1" );
	}



	/**
	 * 更新防火墙行为信息
     * @author Tom 090511
     * @return int
	 */
    public function updateActionInfo( $iActionId, $aPostArr )
    {
        $iActionId = is_numeric($iActionId) && $iActionId > 0 ? intval($iActionId) : 0;
        // STEP 01: 数据整理
        if( $iActionId == 0 )
        {
            return -1; // 数据初始错误
        }
        $aArr['actionname']    = isset($aPostArr['actionname']) ? daddslashes($aPostArr['actionname']) : '';
        $aArr['msgtouser']     = isset($aPostArr['msgtouser']) ? intval($aPostArr['msgtouser']) : 0;
        $aArr['msgtoadmin']    = isset($aPostArr['msgtoadmin']) ? intval($aPostArr['msgtoadmin']) : 0;
        $aArr['messagetouser'] = isset($aPostArr['messagetouser']) ? daddslashes($aPostArr['messagetouser']) : '';
        $aArr['messagetoadmin']= isset($aPostArr['messagetoadmin']) ? daddslashes($aPostArr['messagetoadmin']) : '';
        $aArr['functionname']  = isset($aPostArr['functionname']) ? daddslashes($aPostArr['functionname']) : '';
        $aArr['functionargs']  = isset($aPostArr['functionargs']) ? daddslashes($aPostArr['functionargs']) : '';
        $aArr['functiondesc']  = isset($aPostArr['functiondesc']) ? daddslashes($aPostArr['functiondesc']) : '';
        $aArr['isexit']        = isset($aPostArr['isexit']) ? intval($aPostArr['isexit']) : 0;
        $aArr['isdisabled']    = isset($aPostArr['isdisabled']) ? intval($aPostArr['isdisabled']) : 0;
        $menustrs = '';

        if( isset($aPostArr['menustrs']) && is_array($aPostArr['menustrs']) )
        {
            foreach( $aPostArr['menustrs'] as $v ) // 整理菜单权限
            {
            	if( is_numeric($v) )
            	{
            	    $menustrs .= trim($v).',';
            	}
            }
            if( substr( $menustrs, -1, 1 ) == ',' )
            {
                $menustrs = substr( $menustrs, 0, -1 );
            }
        }
        $aArr['menustr'] = $menustrs;
        return $this->oDB->update( 'firewallaction', $aArr, " `id`= '$iActionId' LIMIT 1 " );
    }



	/**
	 * 建立新防火墙行为
     * @author Tom 090511
     * @return int
	 */
    public function fwActionInsert( $aPostArr )
    {
        // 数据整理
        $aArr['actionname']   = isset($aPostArr['actionname']) ? daddslashes($aPostArr['actionname']) : '';
        $aArr['msgtouser']    = isset($aPostArr['msgtouser']) ? intval($aPostArr['msgtouser']) : 0;
        $aArr['msgtoadmin']   = isset($aPostArr['msgtoadmin']) ? intval($aPostArr['msgtoadmin']) : 0;
        $aArr['messagetouser']= isset($aPostArr['messagetouser']) ? daddslashes($aPostArr['messagetouser']) : '';
        $aArr['messagetoadmin']= isset($aPostArr['messagetoadmin']) ? daddslashes($aPostArr['messagetoadmin']) : '';
        $aArr['functionname'] = isset($aPostArr['functionname']) ? daddslashes($aPostArr['functionname']) : '';
        $aArr['functionargs'] = isset($aPostArr['functionargs']) ? daddslashes($aPostArr['functionargs']) : '';
        $aArr['functiondesc'] = isset($aPostArr['functiondesc']) ? daddslashes($aPostArr['functiondesc']) : '';
        $aArr['isexit']       = isset($aPostArr['isexit']) ? intval($aPostArr['isexit']) : 0;
        $aArr['isdisabled']   = isset($aPostArr['isdisabled']) ? intval($aPostArr['isdisabled']) : 0;
        $menustrs = '';

        if( isset($aPostArr['menustrs']) && is_array($aPostArr['menustrs']) )
        {
            foreach( $aPostArr['menustrs'] as $v ) // 整理菜单权限
            {
            	if( is_numeric($v) )
            	{
            	    $menustrs .= trim($v).',';
            	}
            }
            if( substr( $menustrs, -1, 1 ) == ',' )
            {
                $menustrs = substr( $menustrs, 0, -1 );
            }
        }
        $aArr['menustr'] = $menustrs;
        return $this->oDB->insert( 'firewallaction', $aArr );
    }



	/**
	 * 建立新防火墙规则
     * @author Tom 090511
     * @return int
	 */
    public function fwRuleInsert( $aPostArr=array() )
    {
        // 数据整理
        $aPostArr['message']      = isset($aPostArr['message']) ? daddslashes($aPostArr['message']) : '';
        $aPostArr['rangetype']    = isset($aPostArr['rangetype']) ? intval($aPostArr['rangetype']) : 0;
        $aPostArr['actionid']     = isset($aPostArr['actionid']) ? intval($aPostArr['actionid']) : 0;
        $aPostArr['isdisabled']   = isset($aPostArr['isdisabled']) ? intval($aPostArr['isdisabled']) : 0;
        return $this->oDB->insert( 'firewallrules', $aPostArr );
    }



	/**
	 * 根据规则ID,获取规则数据
     * @author Tom 090511
	 * @param int $iRuleId
	 * @return mix
	 */
	public function getRuleRowsById( $iRuleId = 0 )
	{
	    $iRuleId = intval($iRuleId);
	    if( $iRuleId == 0 )
	    {
	        return -1;
	    }
	    $aReturn = $this->oDB->getOne( "SELECT a.*, b.`username` FROM `firewallrules` a ". 
	          			" LEFT JOIN `users` b on a.`userid`=b.`userid` WHERE a.`entry`='$iRuleId' LIMIT 1 " );
	    if( !is_array($aReturn) )
	    {
	        return -1;
	    }
	    return $aReturn;
	}



	/**
	 * 更新防火墙规则信息
	 * @author Tom 090511
	 */
    public function updateRuleInfo( $iRuleId, $aPostArr=array() )
    {
        $iRuleId = is_numeric($iRuleId) && $iRuleId > 0 ? intval($iRuleId) : 0;
        if( $iRuleId == 0 )
        {
            return -1; // 数据初始错误
        }
        $aPostArr['message']    = isset($aPostArr['message']) ? daddslashes($aPostArr['message']) : '';
        $aPostArr['rangetype']  = isset($aPostArr['rangetype']) ? intval($aPostArr['rangetype']) : 0;
        $aPostArr['actionid']   = isset($aPostArr['actionid']) ? intval($aPostArr['actionid']) : 0;
        $aPostArr['isdisabled'] = isset($aPostArr['isdisabled']) ? intval($aPostArr['isdisabled']) : 0;
        return $this->oDB->update( 'firewallrules', $aPostArr, " `entry`= '$iRuleId' LIMIT 1 " );
    }



	/**
	 * 根据 ID 号,删除防火墙规则
     * @author Tom 090511
	 * @param int $iActionId
	 * @return int 受影响行数
	 */
    public function delRuleRowById( $iRuleId = 0 )
	{
	    $iRuleId = intval( $iRuleId );
	    if( $iRuleId==0 )
	    {
	        return -1;
	    }
	    return $this->oDB->delete( 'firewallrules', " `entry`='$iRuleId' LIMIT 1" );
	}
}
?>