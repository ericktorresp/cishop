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
 * @author	   Tom, Saul   090914
 * @version   1.2.0
 * @package   passportadmin
 */
class model_admingroup extends basemodel
{
    /* ---------------------------------------- CRUD ---------------------------------------- */
	/**
	 * 通过ID实例化一个管理员分组
	 * @author Saul
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
	 * @author saul
	 * @return mix
	 */
	public function EnableAll()
	{
		return $this->oDB->query( "UPDATE `admingroup` SET `isdisabled`='0'" );
	}



    /**
     * 获取管理员分组列表
     * @author Tom 090430
     * @param  int   $iGroupid
     * @param  int   $iSelect
     * @param  bool  $bHtmlSelectBox
     * @param  int   $iLevel
     * @return mix
     */
    function getAdminGroupList( $iGroupid=0 , $iSelect=0, $bHtmlSelectBox = FALSE, $iLevel = 0 )
    {
        static $res = NULL;
        if( $res === NULL )
        {
            $sSql = "SELECT c.* , COUNT( s.groupid ) as countchildren, COUNT( u.adminid ) as countuser ".
	                " FROM `admingroup` as c LEFT JOIN `admingroup` as s on s.`parentid` = c.`groupid` ".
                    " LEFT JOIN `adminuser` as u on u.`groupid` = c.`groupid` ".
                    " GROUP BY c.groupid order by `parentid`,`sort`";
            $res = $this->oDB->getAll($sSql);
        }
        if( empty($res) == TRUE )
        {
            return $bHtmlSelectBox ? '' : array();
        }
        $options = $this->adminGroupOptions( $iGroupid, $res ); // 获得指定分类下的子分类的数组
        /* 截取到指定的缩减级别 */
        if ($iLevel > 0)
        {
            if( $iGroupid == 0 )
            {
                $end_level = $iLevel;
            }
            else
            {
                $first_item = reset( $options ); // 获取第一个元素
                $end_level  = $first_item['level'] + $iLevel;
            }
            /* 保留level小于end_level的部分 */
            foreach ($options AS $key => $val)
            {
                if ($val['level'] >= $end_level)
                {
                    unset($options[$key]);
                }
            }
        }

        $pre_key = 0;
        foreach ($options AS $key => $value)
        {
            // 将子部门中的员工数,累加至主部门中
            //echo $options[$pre_key]['cat_id'] . " => " . $options[$pre_key]['user_num'] . "<br>";
            if( isset($options[$key]['countuser']) && $options[$key]['countuser'] > 0 )
            {
        		//print_rr($value);
        		if( isset($options[ $value['parentid'] ]['countuser']) )
        		{
        			$options[ $value['parentid'] ]['countuser'] += $value['countuser'];
        		}
            }
            
        	$options[$key]['countchildren'] = 1;
            if ($pre_key > 0)
            {
                if ($options[$pre_key]['groupid'] == $options[$key]['parentid'])
                {
                    $options[$pre_key]['countuser'] = 1;
                }
            }
            $pre_key = $key;
        }       

        if( $bHtmlSelectBox == TRUE )
        {
            $select = '';
            foreach( $options AS $var )
            {
                $select .= '<option value="' . $var['groupid'] . '" ';
                $select .= ($iSelect == $var['groupid']) ? "selected='TRUE'" : '';
                $select .= '>';
                if ($var['level'] > 0)
                {
                    $select .= str_repeat('&nbsp;', $var['level'] * 2);
                }
                $select .= htmlspecialchars($var['groupname']) . '</option>';
            }
            return $select;
        }
        else
        {
            return $options;
        }
    }



    /**
     * 过滤和排序所有部门分类，返回一个带有缩进级别的数组
     * @author  tom
     * @param   int     $iSpecialGroupId   上级分类ID
     * @param   array   $arr               含有所有分类的数组
     * @return  void
     */
    function adminGroupOptions( $iSpecialGroupId, $arr )
    {
        static $aGroupCatOptions = array();
        if (isset($aGroupCatOptions[$iSpecialGroupId]))
        {
            return $aGroupCatOptions[$iSpecialGroupId];
        }
        if( !isset($aGroupCatOptions[0]) )
        {
            $level = $last_cat_id = 0;
            $options = $cat_id_array = $level_array = array();
            while( !empty($arr) )
            {
                foreach ($arr AS $key => $value)
                {
                    $cat_id = $value['groupid'];
                    if ($level == 0 && $last_cat_id == 0)
                    {
                        if ($value['parentid'] > 0)
                        {
                            break;
                        }
                        $options[$cat_id]          = $value;
                        $options[$cat_id]['level'] = $level;
                        $options[$cat_id]['id']    = $cat_id;
                        $options[$cat_id]['name']  = $value['groupname'];
                        unset($arr[$key]);
    
                        if ($value['countchildren'] == 0)
                        {
                            continue;
                        }
                        $last_cat_id  = $cat_id;
                        $cat_id_array = array($cat_id);
                        $level_array[$last_cat_id] = ++$level;
                        continue;
                    }
                    if ($value['parentid'] == $last_cat_id)
                    {
                        $options[$cat_id]          = $value;
                        $options[$cat_id]['level'] = $level;
                        $options[$cat_id]['id']    = $cat_id;
                        $options[$cat_id]['name']  = $value['groupname'];
                        unset($arr[$key]);
    
                        if ($value['countchildren'] > 0)
                        {
                            if (end($cat_id_array) != $last_cat_id)
                            {
                                $cat_id_array[] = $last_cat_id;
                            }
                            $last_cat_id    = $cat_id;
                            $cat_id_array[] = $cat_id;
                            $level_array[$last_cat_id] = ++$level;
                        }
                    }
                    elseif ($value['parentid'] > $last_cat_id)
                    {
                        break;
                    }
                }
                $count = count($cat_id_array);
                if ($count > 1)
                {
                    $last_cat_id = array_pop($cat_id_array);
                }
                elseif ($count == 1)
                {
                    if ($last_cat_id != end($cat_id_array))
                    {
                        $last_cat_id = end($cat_id_array);
                    }
                    else
                    {
                        $level = 0;
                        $last_cat_id = 0;
                        $cat_id_array = array();
                        continue;
                    }
                }
                if ($last_cat_id && isset($level_array[$last_cat_id]))
                {
                    $level = $level_array[$last_cat_id];
                }
                else
                {
                    $level = 0;
                }
            }
            $aGroupCatOptions[0] = $options;
        }
        else
        {
            $options = $aGroupCatOptions[0];
        }
    
        if (!$iSpecialGroupId)
        {
            return $options;
        }
        else
        {
            if (empty($options[$iSpecialGroupId]))
            {
                return array();
            }
    
            $iSpecialGroupId_level = $options[$iSpecialGroupId]['level'];
    
            foreach ($options AS $key => $value)
            {
                if ($key != $iSpecialGroupId)
                {
                    unset($options[$key]);
                }
                else
                {
                    break;
                }
            }
    
            $iSpecialGroupId_array = array();
            foreach ($options AS $key => $value)
            {
                if (($iSpecialGroupId_level == $value['level'] && $value['groupid'] != $iSpecialGroupId) ||
                    ($iSpecialGroupId_level > $value['level']))
                {
                    break;
                }
                else
                {
                    $iSpecialGroupId_array[$key] = $value;
                }
            }
            $aGroupCatOptions[$iSpecialGroupId] = $iSpecialGroupId_array;
            return $iSpecialGroupId_array;
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
        $aTmpArr = $this->oDB->getOne("SELECT `parentstr` FROM `admingroup` WHERE `groupid`='$iGroupId' ");
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
	    return $this->oDB->getOne( "SELECT `menustrs` FROM `admingroup` WHERE `groupid`='".intval($iGroupId)."' " );
	}
}
?>