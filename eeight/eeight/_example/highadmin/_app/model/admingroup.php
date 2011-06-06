<?php
/**
 * 文件 : /_app/model/admingroup.php
 * 功能 : 模型 - 管理员分组
 * 
 * 功能：
 *    - admingroup          根据组别ID获取结果集
 *    - update              crud - 更新组别信息
 *    - insert              crud - 建立新组别
 *    - delete              crud - 删除组别
 *    - EnableAll           启用全部管理员分组
 *    - getAdminGroupList   获取管理员分组列表
 *    - getParentStrByGroupId 
 *    - groupHasUser        检查组别是否拥有用户
 *    - groupHasChild       检查组别是否含有子组别
 *    - batchStatusSet      批量: [禁用|启用] 组别 
 * 
 * @author	  Tom, mark
 * @version   1.0.0
 * @package   highadmin
 */
class model_admingroup extends basemodel
{
    /* ---------------------------------------- CRUD ---------------------------------------- */
	/**
	 * 通过ID实例化一个管理员分组
	 * @author mark
	 * @param int $iGroupId
	 * @return -1 | array
	 */
	public function admingroup( $iGroupId = 0 )
	{
	    $iGroupId = intval( $iGroupId );
		$this->oDB->query("SELECT * FROM `admingroup` WHERE `groupid`='".$iGroupId."' ");
		if( $this->oDB->ar() == 0 )
		{
			return -1;
		}
		else
		{
			return $this->oDB->fetchArray();
		}
	}


    /**
	 * crud - 更新组别信息
	 * @author tom
	 * @param  int    $iGroupId
	 * @param  array  $aPostArr
	 * @return int
	 */
    public function update( $iGroupId=0, $aPostArr=array() )
    {
        $iGroupId = is_numeric($iGroupId) && $iGroupId > 0 ? $iGroupId : 0;
        // STEP 01: 数据整理
        if( $iGroupId == 0 )
        {
            return -1; // 数据初始错误
        }
        $sName = isset($aPostArr['groupname']) ? daddslashes($aPostArr['groupname']) : '';
        $iParentId = is_numeric($aPostArr['parentid']) ? intval($aPostArr['parentid']) : 0; // 默认开启
        $iIsDisabled = is_numeric($aPostArr['isdisabled']) ? intval($aPostArr['isdisabled']) : 0; // 默认开启
        $iIsSales = is_numeric($aPostArr['issales']) ? intval($aPostArr['issales']) : 0; // 默认非销售
        $iSort = is_numeric($aPostArr['sort']) ? intval($aPostArr['sort']) : 0; // 默认排序0
        $sDesc = isset($aPostArr['description']) ? daddslashes($aPostArr['description']) : '';
        $menustrs = '';
        foreach( $aPostArr['menustrs'] as $v ) // 菜单权限
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
        $aOldGroupInfo = $this->admingroup( $iGroupId );
        $sSql = '';
        $aChildrenArr = array();
        $sChildrenStr = ''; // 存放 IN 语句用的下级组别id字符串
        $bCanMove = TRUE; // 是否可以移动分类的标记

        if( $aOldGroupInfo['parentid'] != $iParentId ) // 所属组别发生改变的处理, 判断是否移动至自己下级
        { 
            //echo "<font color=red>01, 启动分组移动 old_parentid = ".$aOldGroupInfo['parentid']." iparentid=$iParentId</font><br/>";
            if( $iParentId == $aOldGroupInfo['groupid'] )
            { // die('不允许组别移动至自己,直接中断');
                return -100;
            }
            $sSql = "SELECT `groupid` FROM `admingroup` WHERE FIND_IN_SET( ".$iGroupId.", `parentstr` ) ";
            $aChildrenArr = $this->oDB->getAll($sSql);
            if( !empty($aChildrenArr) )
            {  // 数组非空, 即: 旧分组 c2 包含下级分组 d1,d2
                foreach( $aChildrenArr AS $v )
                {
                    if( $iParentId == $v['groupid'] )
                    { // 此处同时生成 $sChildrenStr, 不用 BREAK
                        $bCanMove = FALSE;
                    }
                    $sChildrenStr .= $v['groupid'].',';
                }
                if( substr( $sChildrenStr, -1, 1 ) == ',' )
                {
                    $sChildrenStr = substr( $sChildrenStr, 0, -1 );
                }
            }

            if( $bCanMove == FALSE )
            { // die('返回错误: 不能将组别移动至自己的下级');
                return -101;
            }

            //echo $bCanMove ? '<font color=green>可以移动</font>' : '<font color=red>不可移动</font>';echo "<br/>";
            // 进行分组移动的处理
            if( $iParentId == 0 )
            { // 如果组别移动至最外, 避免SQL查询, 直接赋值
                $iGrpMoveTo['parentid']  = 0;   // 移动至目标组别的 parentid
                $iGrpMoveTo['parentstr'] = '';  // 移动至目标组别的 parentstr 
            }
            else
            {
                $sSql = "SELECT `parentid`, `parentstr` FROM `admingroup` WHERE `groupid`='".$iParentId."' LIMIT 1";
                $iGrpMoveTo = $this->oDB->getOne($sSql);
                $iGrpMoveTo['parentid'] = empty($iGrpMoveTo['parentid']) ? 0 : intval($iGrpMoveTo['parentid']);
                $iGrpMoveTo['parentstr'] = empty($iGrpMoveTo['parentstr']) ? '' : $iGrpMoveTo['parentstr'];
            }
            $iGrpMoveTo['groupid'] = $iParentId; // 移动至目标组别的 groupid

            // 1, 更新自己组别的 parentid, parentstr
            $bFlag = FALSE;
            $this->oDB->doTransaction(); // 事务开始
            if( $iGrpMoveTo['groupid'] == 0 )
            {
                $sSql = "UPDATE `admingroup` SET `parentid`='0', `parentstr`='' WHERE `groupid`='".$iGroupId."' LIMIT 1" ;
            }
            else 
            { // 如果目标是1级组, 则 parentid=0, parentstr
                $sParentstr = $iGrpMoveTo['parentid'] == 0 ? $iGrpMoveTo['groupid'] :  
                        ($iGrpMoveTo['parentstr'] .','. $iGrpMoveTo['groupid'] ) ;
                $sSql = "UPDATE `admingroup` SET `parentid`='".$iGrpMoveTo['groupid'].
            			"', `parentstr`='". $sParentstr."' WHERE `groupid`='".$iGroupId."' LIMIT 1" ;
            }
            if( FALSE == $this->oDB->query( $sSql ) )
            {
                $bFlag = -200;
                $this->oDB->doRollback();
            }

            // 2, 更新自己下级组别的 parentid(不变), parentstr
            /*      0 ( gid=0, pid=0, pstr='' )
             *      A ( gid=1, pid=0, pstr='' ) 1级(开发部)      F    ( gid=100, pid=0, pstr='' ) (1级市场部)
             *     / \                                          / \
             *    B   C ( gid=2, pid=1, pstr=1 )              F1   F2 ( gid=101, pid=100, pstr=100 )
             *       / \                                          /  \
             *      c1  c2 ( gid=10, pid=2, pstr=1,2 )           F5  F6 ( gid=102, pid=101, pstr=100,101 )
             *         /  \                                         / \
             *       d1   d2 ( gid=20, pid=10, pstr=1,2,10 )          F7( gid=103, pid=102, pstr=100,101,102 )
             *           /  \
             *          e1   e2 ( gid=30, pid=20, pstr=1,2,10,20 )
             * 
             * A=>F6  :    c2.pstr = 100,101,1,2 +  parent.etc
             */
            $iCount = 0;
            if( !empty($sChildrenStr) ) // 即: 有下级组别
            {
                // 任意集(市场B1组) => 顶级 0     抹掉上级的字符即可
                if( $iGrpMoveTo['groupid'] == 0 )
                {
                    $iCount = strlen($aOldGroupInfo['parentstr']) + 2; // 计算MYSQL多1位 + 逗号
                    $sParentstr = " SUBSTRING( `parentstr`, $iCount ) ";
                }
                // 1级移动到1级, 例: 市场部 => 开发组,  加上移动至组的 groupid + parentstr 即可
                elseif( $aOldGroupInfo['parentstr']=='' && $iGrpMoveTo['parentstr']=='' )
                {
                    $sParentstr = " CONCAT( '". $iGrpMoveTo['groupid'] .',' ."', `parentstr` ) ";
                }
                // 任意子集 => 1级
                elseif( $aOldGroupInfo['parentstr']!='' && $iGrpMoveTo['parentstr']=='' )
                {
                    $iCount = strlen($aOldGroupInfo['parentstr']) + 2; // 计算MYSQL多1位 + 逗号
                    //$tmpMoveToParentStr = $aOldGroupInfo['parentstr']=='' ? '' : $aOldGroupInfo['parentstr'].',';
                    $sParentstr = " CONCAT( '". $iGrpMoveTo['groupid'] .',' . 
                    		 	  "',  SUBSTRING( `parentstr`, $iCount ) ) ";
                }
                // 1级 => 任意子集, 目标组 pstr + ',' + 目标组gid + ',' 
                elseif( $aOldGroupInfo['parentstr']=='' && $iGrpMoveTo['parentstr']!='' )
                {
                    $sParentstr = " CONCAT( '" . ($iGrpMoveTo['parentstr'].',' .
                    			  $iGrpMoveTo['groupid'] . ',' ) . "', `parentstr` ) ";
                }
                // 任意子集 => 任意子集   例如: 市场B1组 => 技术部 
                else
                {
                    $iCount = strlen($aOldGroupInfo['parentstr']) + 2; // 计算MYSQL多1位 + 逗号
                    $sParentstr = " CONCAT( '". $iGrpMoveTo['parentstr'].',' . $iGrpMoveTo['groupid'] . ',' .
                    				"',  SUBSTRING( `parentstr`, $iCount ) ) ";
                }
                $sSql = "UPDATE `admingroup` SET `parentstr` = $sParentstr WHERE `groupid` IN ( $sChildrenStr ) ";
                if( FALSE == $this->oDB->query( $sSql ) )
                {
                    $bFlag = -200;
                    $this->oDB->doRollback();
                }
            }
        }
        $sSql = "UPDATE `admingroup` SET `groupname`='$sName', `isdisabled`='$iIsDisabled', ".
                    " `issales`='$iIsSales', `description`='$sDesc', `sort`='$iSort', `menustrs`='$menustrs' ".
                	" WHERE `groupid`='$iGroupId' LIMIT 1";
        if( FALSE == $this->oDB->query( $sSql ) )
        {
            $bFlag = -200;
            $this->oDB->doRollback();
        }
        $bFlag = TRUE;
        $this->oDB->doCommit();
        return $bFlag;
    }



	/**
	 * crud - 建立新组别 
	 * @author tom 
	 * @param  array $aPostArr
	 * @return int
	 */
    public function insert( $aPostArr )
    {
        // 数据整理
        $aArr['groupname']   = isset($aPostArr['groupname']) ? daddslashes($aPostArr['groupname']) : '';
        $aArr['parentid']    = is_numeric($aPostArr['parentid']) ? intval($aPostArr['parentid']) : 0; // 默认开启
        $aArr['isdisabled']  = is_numeric($aPostArr['isdisabled']) ? intval($aPostArr['isdisabled']) : 0; // 默认开启
        $aArr['issales']     = is_numeric($aPostArr['issales']) ? intval($aPostArr['issales']) : 0; // 默认非销售
        $aArr['sort']        = is_numeric($aPostArr['sort']) ? intval($aPostArr['sort']) : 0; // 默认排序0
        $aArr['description'] = isset($aPostArr['description']) ? daddslashes($aPostArr['description']) : '';
        $menustrs = '';

        // 对于 parentstr 的支持
        if( $aArr['parentid'] == 0 )
        {
            $aArr['parentstr'] = '';
        }
        else 
        {
            $sParentString = $this->getParentStrByGroupId( $aArr['parentid'] );
            if( $sParentString == -1 )
            { // 数据返回出错
                return -1;
            }
            else if( $sParentString == '' )
            { // 目标组别是1级组
                $aArr['parentstr'] = $aArr['parentid'];
            }
            else 
            { // 目标组别是2级或一下组
                $aArr['parentstr'] = $sParentString.','.$aArr['parentid'];
            }
        }        
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
        $aArr['menustrs'] = $menustrs;
        return $this->oDB->insert( 'admingroup', $aArr );
    }



	/**
	 *  crud - 根据组别ID 删除一个组别
	 *       - 如果组包含下级分组, 不允许删除
	 *       - 如果组中有管理员, 不允许删除
	 * @author tom 
	 * @param  int $iGroupId
	 * @return mix
	 */
	public function delete( $iGroupId )
	{
		if( $this->admingroup($iGroupId) == -1 )
		{
			return -1; // 组别 ID 不存在
		}
		if( $this->groupHasChild($iGroupId) > 0 )
		{
			return -2; //含有下级的管理员分组
		}
		if( $this->groupHasUser($iGroupId) > 0 )
		{
		    return -3; // 组别中含有用户
		}
		return $this->oDB->query("DELETE FROM `admingroup` WHERE `groupid`='".$iGroupId."' LIMIT 1");
	}



	/**
	 * 启用所有的管理员组
	 * @author mark
	 * @return mix
	 */
	public function EnableAll()
	{
		return $this->oDB->query( "UPDATE `admingroup` SET `isdisabled`='0'" );
	}

	/**
	 * 获取带缩进的菜单
	 *
	 * @author mark
	 * 
	 * @param 指定菜单ID   $iParentId
	 * @param 菜单数据     $aSourceMenu
	 * @param 最后结果数据  $aResult
	 * 
	 * 
	 */
	public function getLevelMenu( $iParentId, $aSourceMenu, & $aResult )
	{
	    foreach( $aSourceMenu[$iParentId] as $aMenu )
	    {
	        $aMenu['level'] = $aMenu['parentstr'] == '' ? 0 : count(explode(",", $aMenu['parentstr']));
	        $aResult[] = $aMenu;
	        if(isset($aSourceMenu[$aMenu['groupid']]))
	        {
	            $this->getLevelMenu( $aMenu['groupid'], $aSourceMenu, $aResult );//递归
	        }
	    }
	}

    /**
     * 获取管理员分组列表
     * @author mark
     * @param  int   $iGroupid
     * @param  int   $iSelect
     * @param  bool  $bHtmlSelectBox
     * @return mix
     */
    function getAdminGroupList( $iGroupid=0 , $iSelect=0, $bHtmlSelectBox = FALSE )
    {
        $sSql = "SELECT c.* , COUNT( s.`groupid` ) AS countchildren, COUNT( u.`adminid` ) AS countuser ".
                " FROM `admingroup` AS c LEFT JOIN `admingroup` AS s ON s.`parentid` = c.`groupid` ".
                " LEFT JOIN `adminuser` AS u ON u.`groupid` = c.`groupid` ".
                " GROUP BY c.`groupid` ORDER BY `parentid`,`sort`";
        $aResultMenu = $this->oDB->getAll($sSql);
        if( empty($aResultMenu) == TRUE )
        {
            return $bHtmlSelectBox ? '' : array();
        }
        $aGroupByGroupId = array();
        foreach ( $aResultMenu as $aTemp )
        {
            $aGroupByGroupId[$aTemp['parentid']][$aTemp['groupid']] = $aTemp;
        }
        //获取带缩进的菜单
        $aOptions = array();
        $this->getLevelMenu($iGroupid, $aGroupByGroupId, $aOptions);
        
        if( $bHtmlSelectBox == TRUE )
        {
            $sSelect = '';
            foreach( $aOptions as $aValue )
            {
                $sSelect .= '<option value="' . $aValue['groupid'] . '" ';
                $sSelect .= ($iSelect == $aValue['groupid']) ? "selected='TRUE'" : '';
                $sSelect .= '>';
                if ($aValue['level'] > 0)
                {
                    $sSelect .= str_repeat('&nbsp;', $aValue['level'] * 2);
                }
                $sSelect .= htmlspecialchars($aValue['groupname']) . '</option>';
            }
            return $sSelect;
        }
        else
        {
            return $aOptions;
        }
    }


    /**
     * 根据分组ID 获取分组 parentstr 字段内容
     * @author tom 090430
     * @param  int $iGroupId
     * @return -1 | array
     */
    public function getParentStrByGroupId( $iGroupId )
    {
        $iGroupId = intval($iGroupId);
        $aTmpArr = $this->oDB->getOne("SELECT `parentstr` FROM `admingroup` WHERE `groupid`='$iGroupId' LIMIT 1");
        if( !empty($aTmpArr) )
        {
            return $aTmpArr['parentstr'];
        }
        else
        {
            return -1;
        }
    }



    /**
     * 根据组别ID, 判断该组别是否拥有用户
     * @author tom
     * @param  int $iGroupId
     * @return int
     */
	public function groupHasUser( $iGroupId )
	{
	    $this->oDB->query("SELECT 1 FROM `adminuser` WHERE `groupid` = '".intval($iGroupId)."' ");
	    return $this->oDB->ar();
	}



	/**
	 * 检测管理员分组是否有下级, 返回下级的数量
	 * @author tom
	 * @param  int $iGroupId
	 * @return int
	 */
	public function groupHasChild( $iGroupId )
	{
	    $iGroupId = intval($iGroupId);
	    $this->oDB->query("SELECT `groupid` FROM `admingroup` WHERE `parentid`='". $iGroupId .
						"' or FIND_IN_SET(". $iGroupId .",`parentstr`) ");
		return $this->oDB->ar();
	}



	/**
	 * 批量管理员分组 (启用/禁用) 设置
	 * @author tom
	 * @param  int|array   $mGroupId
	 * @param  int         $iStatus
	 */
	public function batchStatusSet( $mGroupId, $iStatus )
	{
	    if( empty($mGroupId) )
	    {
	        return -1;
	    }
	    $sWhere = '';
	    $iStatus = ($iStatus == 0) ? 0 : 1;
	    if( is_array($mGroupId) )
	    {
	        $sWhere = $this->oDB->CreateIn( $mGroupId, 'groupid' );
	    }
	    else
	    {
	        $sWhere = " `groupid` = '$mGroupId' ";
	    }
	    return $this->oDB->query("UPDATE `admingroup` SET `isdisabled`='$iStatus' WHERE $sWhere " );
	}



	/**
	 * 获取管理员分组的下级管理员分组
	 * @author tom
	 * @param  int $iGroupId 分组ID
	 * @return array
	 */
	public function admingroupChild( $iGroupId = 0 )
	{
		return $this->oDB->getAll("SELECT * FROM `admingroup` WHERE `parentid`='".intval($iGroupId)."'");
	}



	/**
	 * 根据分组ID, 获取组别的菜单
	 * @param  int $iGroupId
	 * @return array
	 * @author tom
	 */
	public function getMenuStringByGrpId( $iGroupId = 0 )
	{
	    return $this->oDB->getOne( "SELECT `menustrs` FROM `admingroup` WHERE `groupid`='".intval($iGroupId)."' " . ' LIMIT 1' );
	}
}
?>