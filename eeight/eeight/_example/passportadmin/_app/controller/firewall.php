<?php
/**
 * 文件 : /_app/controller/firewall.php
 * 功能 : 控制器 - 防火墙 [ 规则=rule | 行为=action ]
 * 
 * 
 *    - actionRuleList()        防火墙规则列表
 *    +-- actionAddrule()       增加规则(前台)
 *    +-- actionSaverule()      增加规则    (处理)
 *    +-- actionEditrule()      修改规则(前台)
 *    +-- actionUpdaterule()    修改规则    (处理)
 *    +-- actionDelrule()       删除规则    (处理)
 *     
 *    - actionActionlist()      防火墙行为列表
 *    +-- actionAddaction()     增加行为(前台)
 *    +-- actionSaveaction()    增加行为    (处理)
 *    +-- actionEditaction()    修改行为(前台)
 *    +-- actionUpdateaction()  修改行为    (处理)
 *    +-- actionDelaction()     删除行为    (处理)
 * 
 * 
 * @author	   Tom & James      090914
 * @version   1.2.0
 * @package   passportadmin
 */
class controller_firewall extends basecontroller
{
    /**
     * 防火墙行为列表
     * URL = ./controller=firewall&action=actionlist
     * @author Tom 090511
     */
    public function actionActionlist()
    {
        // 01, 搜索条件整理
        $aSearch['aid']    = isset($_GET['aid']) && !empty($_GET['aid']) ? intval($_GET['aid']) : ""; // 行为id
        $aSearch['fmid']   = isset($_GET['fmid']) ? intval($_GET['fmid']) : ""; // 受限菜单id
        $aSearch['fs']     = isset($_GET['fs']) ? $_GET['fs'] : ""; // 规则状态, 0=启用,1=禁用
        $aSearch['fname']  = isset($_GET['fname']) ? daddslashes($_GET['fname']) : ""; // 函数名
        $oAdminMenu        =  new model_adminmenu();
        $oFirewall         =  new model_firewall();
        $aHtml['fmid']     =  $aSearch['fmid'];
        $aHtml['fs']       =  $aSearch['fs'] == '' ? -1 : intval($aSearch['fs']);
        $aHtml['fname']    =  stripslashes_deep($aSearch['fname']);
        $aHtml['fmidoptions'] = $oAdminMenu->getAdminMenuOptions($aSearch['fmid']);
        unset($oAdminMenu);
        
        // 02, WHERE 语句拼接
        $sWhere = ' 1 '; // WHERE 条件变量声明
        if( $aSearch['aid'] != '' )
        { // 对行为ID的支持. (从规则列表页中跳转过来的)
            $sWhere .= " AND `id` = '".$aSearch['aid']."' ";
        }
        if( $aSearch['fmid'] != '' )
        { // 受限菜单ID
            $sWhere .= " AND FIND_IN_SET( '".$aSearch['fmid']."',  `menustr` ) ";
        }
        if( $aHtml['fs'] != -1 )
        { // 规则状态
            $sWhere .= " AND `isdisabled` = '".$aHtml['fs']."' ";
        }
        if( trim($aHtml['fname']) != ''  )
        { // 受限消息内容
            if( strstr($aSearch['fname'], '*') ) // 搜索到通配符 * 号 
            {
                $tmpMessage = str_replace( '*', '%', $aSearch['fname'] );
                $sWhere .= " AND `functionname` LIKE '".daddslashes($tmpMessage)."' ";
            }
            else
            {
                $sWhere .= " AND `functionname` = '".daddslashes($aSearch['fname'])."' ";
            }
        }
        $p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
        $pn = isset($_GET['pn']) ? intval($_GET['pn']) : 25;   // 分页用2
        $aResult = $oFirewall->getFirewallActionList('*', $sWhere, $pn , $p); // 获取数据结果集
        $oPager = new pages( $aResult['affects'], $pn, 10);    // 分页用3
        $GLOBALS['oView']->assign( 'pages', $oPager->show() ); // 分页用4
        $GLOBALS['oView']->assign( 'aFwList', $aResult['results'] ); // 数据分配
        $GLOBALS['oView']->assign( "s", $aHtml ); // 搜索条件宏
        $GLOBALS['oView']->assign( "ur_here", "防火墙行为列表" );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("firewall","actionlist"), 'text'=>'清空过滤条件' ) );
        $GLOBALS['oView']->assign( 'actionlink2', array( 'href'=>url("firewall","addaction"), 'text'=>'增加防火墙行为' ) );
        $oFirewall->assignSysInfo();
        $GLOBALS['oView']->display("firewall_actionlist.html");
        EXIT;
    }



    /**
     * 增加行为 (前台修改页)
     * URL = ./controller=firewall&action=addaction
     * @author Tom 090511
     */
    function actionAddaction()
    {
        $oUserMenuList  = new model_usermenu();
        $aUserMenuLists = $oUserMenuList->getList(0); // 获取前台用户菜单列表.生成菜单树
        $aMenus = array();
        foreach( $aUserMenuLists as $v )
        {
        	$aMenus[$v["parentid"]][$v["menuid"]]["menuid"] = $v["menuid"];
        	$aMenus[$v["parentid"]][$v["menuid"]]["title"]  = $v["title"];
        }
        foreach( $aMenus as $key => $value )
        {
        	$a[$key] = count($value);
        }
        unset( $a[0], $oUserMenuList, $aUserMenuLists );
        $GLOBALS['oView']->assign( 'menus', $aMenus );
        $GLOBALS['oView']->assign( 'counts', $a );
        $GLOBALS['oView']->assign( 'form_action', 'saveaction' );
        $GLOBALS['oView']->assign( 'ur_here', '增加防火墙行为' );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("firewall","actionlist"), 'text'=>'防火墙行为列表' ) );
        $GLOBALS['oView']->display("firewall_actioninfo.html");
        EXIT;
    }



    /**
     * 新增行为 (执行处理)
     * URL = ./controller=firewall&action=saveaction
     * @author Tom 090511
     */
    function actionSaveaction()
    {
        $aLocation = array(0=>array("text" => "防火墙行为列表","href" => url("firewall","actionlist")));
        if( !isset($_POST) )
        {
            sysMessage("提交数据有缺失,请重新检查", 1, $aLocation);
        }
        /* @var $oFirewall model_firewall */
        $oFirewall  = A::singleton('model_firewall');
        $iFlag      = $oFirewall->fwActionInsert( $_POST );
        if( $iFlag > 0 )
        {
            sysMessage("操作成功", 0, $aLocation);
        }
        else
        {
            sysMessage("操作失败", 1, $aLocation);
        }
    }


    
    /**
     * 修改行为(前台)
     * URL = ./controller=firewall&action=editaction&id=1
     * @author Tom 090511
     */
    function actionEditaction()
    {
        $aLocation = array(0=>array("text" => "防火墙行为列表","href" => url("firewall","actionlist")));
        $id        = is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $id==0 )
        {
            sysMessage("防火墙行为ID错误", 1, $aLocation);
        }
        $oFirewall        = new model_firewall();
        $aFirewallActions = $oFirewall->getActionRowsById( $id ); // 获取防火墙行为信息
        unset($oFirewall); // 就近释放对象
        if( $aFirewallActions == -1 )
        {
            sysMessage("防火墙行为ID不存在", 1, $aLocation);
        }
        $oUserMenuList     = new model_usermenu();
        $aUserMenuLists    = $oUserMenuList->getList(0);
        $aUserMenuSelected = explode( ",", $aFirewallActions["menustr"] );
        $aMenus = array();
        foreach( $aUserMenuLists as $v )
        {
        	$aMenus[$v["parentid"]][$v["menuid"]]["menuid"] = $v["menuid"];
        	$aMenus[$v["parentid"]][$v["menuid"]]["title"]  = $v["title"];
        	$aMenus[$v["parentid"]][$v["menuid"]]["check"]  = in_array( $v["menuid"],$aUserMenuSelected );
        }
        foreach( $aMenus as $key => $value )
        {
        	$a[$key] = count($value);
        }
        unset( $a[0], $oUserMenuList, $aUserMenuLists );
        $GLOBALS['oView']->assign( 'menus', $aMenus );
        $GLOBALS['oView']->assign( 'counts', $a );
        $GLOBALS['oView']->assign( 'form_action', 'updateaction' );
        $GLOBALS['oView']->assign( 'd', $aFirewallActions );
        $GLOBALS['oView']->assign( 'ur_here', "修改防火墙行为&nbsp; (".
                            h($aFirewallActions['actionname']).",ID:". $aFirewallActions['id'] .')' );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("firewall","actionlist"), 'text'=>'防火墙行为列表' ) );
        $GLOBALS['oView']->display("firewall_actioninfo.html");
        EXIT;
    }



    /**
     * 修改行为 (执行处理)
     * URL = ./controller=firewall&action=updateaction
     * @author Tom 090511
     */
    function actionUpdateaction()
    {
        $aLocation = array(0=>array("text" => "防火墙行为列表","href" => url("firewall","actionlist")));
        if( !isset($_POST) )
        {
            sysMessage("提交数据有缺失,请重新检查", 1, $aLocation);
        }
        $iActionId = is_numeric($_POST["actionid"]) ? intval($_POST["actionid"]) : 0;
        $oFirewallAction = new model_firewall();
        $aFirewallAction = $oFirewallAction->getActionRowsById($iActionId);
        if( $aFirewallAction == -1 )
        {
            sysMessage("无效的防火墙行为ID [$iActionId]", 1, $aLocation);
        }
        $iFlag = $oFirewallAction->updateActionInfo( $iActionId, $_POST);
        if( $iFlag > 0 )
        {
            sysMessage("更新成功", 0, $aLocation);
        }
        else
        {
            sysMessage("更新失败.", 1, $aLocation);
        }
    }



    /**
     * 删除行为(执行处理)
     * URL = ./controller=firewall&action=delaction&id=1
     * @author Tom 090511
     */
    function actionDelaction()
    {
        $aLocation = array(0=>array("text" => "防火墙行为列表","href" => url("firewall","actionlist")));
        $id = is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $id == 0 )
        {
        	sysMessage( "防火墙行为ID错误", 1, $aLocation );
        }
        /* @var $oFirewallAction model_firewall */
        $oFirewallAction = A::singleton('model_firewall');
        $iFlag = $oFirewallAction->delActionRowById( $id );
        if( $iFlag > 0 )
        {
        	sysMessage("操作成功", 0, $aLocation );
        }
        else
        {
        	sysMessage("操作失败", 1, $aLocation );
        }
    }






    /**
     * 防火墙规则列表,  HTML 可选条件
     *   - 1, 受限范围        ft        ( Firewall type )  [ 0=userid, 1=userip ]
     *   - 2, 受限值          fv        ( Firewall Value ) 
     *   - 3, 受限菜单        fmid      ( menuid 功能菜单ID )
     *   - 4, 受限行为        faid      ( firewallaction.id )
     *   - 5, 规则状态        fs        ( firewallrules status = `isdisabled` )
     *   - 6, 受限规则描述    fm        ( firewallrules.message )
     * @author Tom 090511
     */
    public function actionRulelist()
    {
        // 01, 搜索条件整理
        $aSearch['ft']     = isset($_GET['ft']) && !empty($_GET['fv']) ? $_GET['ft'] : "";
        $aSearch['fv']     = trim($aSearch['ft'])!='' && !empty($_GET['fv']) ? $_GET['fv'] : "";
        $aSearch['fmid']   = isset($_GET['fmid']) ? intval($_GET['fmid']) : "";
        $aSearch['faid']   = isset($_GET['faid']) ? intval($_GET['faid']) : "";
        $aSearch['fs']     = isset($_GET['fs']) ? $_GET['fs'] : "";
        $aSearch['fm']     = isset($_GET['fm']) ? daddslashes($_GET['fm']) : "";
        
        $oAdminMenu        =  new model_adminmenu();
        $oFirewall         =  new model_firewall();
        $aHtml['ft']       =  $aSearch['ft']=='' ? -1 : $aSearch['ft'];
        $aHtml['fv']       =  stripslashes_deep($aSearch['fv'] );
        $aHtml['fmid']     =  $aSearch['fmid'];
        $aHtml['faid']     =  $oFirewall->getDistintFwAction(FALSE, $aSearch['faid'] );
        $aHtml['fs']       =  $aSearch['fs'] == '' ? -1 : intval($aSearch['fs']);
        $aHtml['fm']       =  stripslashes_deep($aSearch['fm']);
        $aHtml['fmidoptions'] = $oAdminMenu->getAdminMenuOptions($aSearch['fmid']);
        unset($oAdminMenu);
        
        // 02, WHERE 语句拼接
        $sWhere = ' 1 '; // WHERE 条件变量声明
        $sSep = array( " ", "　", ",", "，" ); // 半角,全角逗号, 空格
        if( $aHtml['ft'] == 0 && $aSearch['fv']!='' )
        { // 用户名搜索. 可写多个逗号分隔,并支持通配符*号
            // 01, 整理用户名
            $aTmpArray = explode( ",", trim( str_replace($sSep, ',', $aSearch['fv']) )); // 分隔字符,进数组
            $iTmpArrayCount = count($aTmpArray);
            $sTmpUsernames = '';
            for( $i=0; $i < $iTmpArrayCount; $i++ )
            {
            	if( trim($aTmpArray[$i]) != "" && trim($aTmpArray[$i]) != '*' )
            	{
            	    if( FALSE !== strpos($aTmpArray[$i], '*') )
            	    {
                        $sTmpUsernames .= "OR `username` LIKE '". str_replace('*','%',daddslashes($aTmpArray[$i])). "' ";
            	    }
            	    else
            	    {
            	        $sTmpUsernames .= "OR `username` = '".daddslashes($aTmpArray[$i]) . "' ";
            	    }
            	}
            }
        
            // 封装 SQL语句
            if( substr($sTmpUsernames,0,2) == "OR" ) 
            {
                $sTmpUsernames = " AND (" . substr($sTmpUsernames,2,-1) . ") ";
            }
            if( trim($sTmpUsernames) != '' )
            {
                $aResult = $oFirewall->getUserIdByCondition( $sTmpUsernames );
                $sTmpsWhere = '';
                if( $aResult != '' )
                {
                    foreach( $aResult AS $v )
                    {
                        if( is_numeric($v['userid']) )
                        {
                            $sTmpsWhere .= $v['userid'].',';
                        }
                    }
                    if( substr($sTmpsWhere,-1,1) == ',' )
                    {
                        $sTmpsWhere = daddslashes(substr($sTmpsWhere,0,-1));
                    }
                    $sTmpsWhere = empty($sTmpsWhere)?0:$sTmpsWhere;
                    $sWhere .= " AND `rangetype`='1' AND a.`userid` IN ( $sTmpsWhere ) ";
                }
                else 
                {
                    $sWhere .= " AND 1<0 ";
                }
            }
            unset( $aTmpArray, $iTmpArrayCount, $sTmpUsernames, $sTmpsWhere );
        }
        elseif( $aHtml['ft'] == 1 && $aSearch['fv']!='' )
        { // 网络地址搜索. 只可写一个, 支持通配符*号
            // 02, 整理 IP 地址
            //$sWhere = " AND `rangetype`='userid' AND `rangevalue` IN ( '$sTmpsWhere' ) ";
            if( FALSE !== strpos($aSearch['fv'], '*') ) // 搜索到通配符 * 号 
            {
                $tmpIpaddr = str_replace( '*', '%', $aSearch['fv'] );
                $sWhere .= " AND `rangetype`='2' AND `userip` LIKE '".daddslashes($tmpIpaddr)."' ";
            }
            else
            {
                $sWhere .= " AND `rangetype`='2' AND `userip` = '".daddslashes($aSearch['fv'])."' ";
            }
            $aSearch['fv']  =  stripslashes_deep($aSearch['fv']);
        }
        
        if( $aSearch['fmid'] != '' )
        { // 受限菜单ID
            $sWhere .= " AND FIND_IN_SET( '".$aSearch['fmid']."',  `menustr` ) ";
        }
        
        if( $aHtml['fs'] != -1 )
        { // 规则状态
            $sWhere .= " AND a.`isdisabled` = '".$aHtml['fs']."' ";
        }
        
        if( trim($aHtml['fm']) != ''  )
        { // 受限消息内容
            if( FALSE !== strpos($aSearch['fm'], '*') ) // 搜索到通配符 * 号 
            {
                $tmpMessage = str_replace( '*', '%', $aSearch['fm'] );
                $sWhere .= " AND `message` LIKE '".daddslashes($tmpMessage)."' ";
            }
            else 
            {
                $sWhere .= " AND `message` = '".daddslashes($aSearch['fm'])."' ";
            }
        }
        
        if( $aSearch['faid'] != '')
        { // 受限行为ID
            $sWhere .= " AND b.`id` = '".$aSearch['faid']."' ";
        }
        
        // 03, 数据查询
        // 分页处理,  HTML 宏解析
        $p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
        $pn = isset($_GET['pn']) ? intval($_GET['pn']) : 25;   // 分页用2
        $aResult = $oFirewall->getFirewallList('*', $sWhere, $pn , $p); // 获取数据结果集
        $oPager = new pages( $aResult['affects'], $pn, 10);    // 分页用3
        $GLOBALS['oView']->assign( 'pages', $oPager->show() ); // 分页用4
        $GLOBALS['oView']->assign( 'aFwList', $aResult['results'] ); // 数据分配
        $GLOBALS['oView']->assign( "s", $aHtml );
        $GLOBALS['oView']->assign( "ur_here", "防火墙规则列表" );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("firewall","rulelist"), 'text'=>'清空过滤条件' ) );
        $GLOBALS['oView']->assign( 'actionlink3', array( 'href'=>url("firewall","addrule"), 'text'=>'增加防火墙规则' ) );
        $oFirewall->assignSysInfo();
        $GLOBALS['oView']->display( "firewall_rulelist.html" );
        EXIT;
    }



	/**
	 * 新增规则 (前台修改页)
	 * URL = ./controller=firewall&action=addrule
	 * @author Tom 090511
	 */
	function actionAddrule()
	{
	    /* @var $oFirewall model_firewall */
		$oFirewall        = A::singleton('model_firewall');
	    $aHtml['faid']    = $oFirewall->getDistintFwAction(FALSE);
	    unset($oFirewall);
        $GLOBALS['oView']->assign( "s", $aHtml );
		$GLOBALS['oView']->assign( 'form_action', 'saverule' );
        $GLOBALS['oView']->assign( 'ur_here', '增加防火墙规则' );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("firewall","rulelist"), 'text'=>'防火墙规则列表' ) );
		$GLOBALS['oView']->display( "firewall_ruleinfo.html" );
		EXIT;
	}


    
    /**
     * 新增规则 (执行处理)
     * URL = ./controller=firewall&action=saverule
     * @author Tom 090511
     */
    function actionSaverule()
    {
        $aLocation = array(0=>array("text" => "防火墙规则列表","href" => url("firewall","rulelist")));
        if( !isset($_POST) || !isset($_POST['fv']) || !isset($_POST['faid']) || !isset($_POST['message']) )
        {
            sysMessage("提交数据有缺失,请重新检查", 1, $aLocation);
        }
        // 01,数据整理
        $aRules['rangetype']   = isset($_POST['ft']) ? intval($_POST['ft']) : 0; // 受限范围, 1=用户id, 2=网络地址
        $fv                    = daddslashes( trim($_POST['fv']) );
        $aRules['actionid']    = isset($_POST['faid']) ? intval($_POST['faid']) : 0;
        $aRules['isdisabled']  = isset($_POST['isdisabled']) ? intval($_POST['isdisabled']) : 0;
        $aRules['message']     = daddslashes( trim($_POST['message']) );
        
        // 根据用户名封锁, 判断用户名是否存在
        if( $aRules['rangetype'] == 1 )
        {
            $oUser = new model_user();
            $aUserid = $oUser->getUseridByUsername($fv);
            unset($oUser);
            if( $aUserid == 0 )
            {
                sysMessage("用户名: [$fv] 不存在,请确认", 1, $aLocation);
            }
            else
            {
                $aRules['userid'] = $aUserid;
            }
            unset($aUserid);
        }
        
        // 根据网络IP封锁, 进行 trim 处理
        if( $aRules['rangetype'] == 2 )
        {
            $aRules['userip'] = $fv;
        }
        
        $oFirewall = new model_firewall();
        $iFlag = $oFirewall->fwRuleInsert( $aRules );
        if( $iFlag > 0 )
        {
            sysMessage("操作成功", 0, $aLocation);
        }
        else
        {
            sysMessage("操作失败", 1, $aLocation);
        }
    }



    /**
     * 修改规则(前台)
     * URL = ./controller=firewall&action=editrule&id=1
     * @author Tom 090511
     */
    function actionEditrule()
    {
        $aLocation = array(0=>array("text" => "防火墙规则列表","href" => url("firewall","rulelist")));
        $id = is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $id==0 )
        {
            sysMessage("防火墙规则ID错误", 1, $aLocation);
        }
        $oFirewall = new model_firewall();
        $aFirewallRules = $oFirewall->getRuleRowsById( $id ); // 获取防火墙规则信息
        if( $aFirewallRules == -1 )
        {
            sysMessage("防火墙规则ID不存在", 1, $aLocation);
        }
        $aFirewallRules['faid'] =  $oFirewall->getDistintFwAction(FALSE, $aFirewallRules['actionid'] );
        unset($oFirewall); // 就近释放对象
        $GLOBALS['oView']->assign( 'form_action', 'updaterule' );
        $GLOBALS['oView']->assign( 's', $aFirewallRules );
        $GLOBALS['oView']->assign( 'ur_here', "修改防火墙规则&nbsp; (".
                            h($aFirewallRules['message']).",ID:". $aFirewallRules['entry']. ')' );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("firewall","rulelist"), 'text'=>'防火墙规则列表' ) );
        $GLOBALS['oView']->display("firewall_ruleinfo.html");
        EXIT;
    }



    /**
     * 修改规则 (执行处理)
     * URL = ./controller=firewall&action=updaterule
     * @author Tom 090511
     */
    function actionUpdaterule()
    {
        $aLocation = array(0=>array("text" => "防火墙规则列表","href" => url("firewall","rulelist")));
        if( !isset($_POST) || !isset($_POST['fv']) || !isset($_POST['faid']) || !isset($_POST['message']) )
        {
            sysMessage("提交数据有缺失,请重新检查", 1, $aLocation);
        }
        // 数据整理
        $iRuleId               = isset($_POST['ruleid']) ? intval($_POST['ruleid']) : 0;
        $aRules['rangetype']   = isset($_POST['ft']) ? intval($_POST['ft']) : 0; // 受限范围, 1=用户id, 2=网络地址
        $fv                    = daddslashes( trim($_POST['fv']) );
        $aRules['actionid']    = intval($_POST['faid']);
        $aRules['isdisabled']  = intval($_POST['isdisabled']);
        $aRules['message']     = daddslashes( trim($_POST['message']) );
        if( $iRuleId == 0 )
        {
            sysMessage("提交数据有缺失,请重新检查 #2", 1, $aLocation);
        }
        // 根据用户名封锁, 判断用户名是否存在
        if( $aRules['rangetype'] == 1 )
        {
            $oUser = new model_user();
            $aUserid = $oUser->getUseridByUsername($fv);
            unset($oUser);
            if( $aUserid == 0 )
            {
                sysMessage("用户名: [$fv] 不存在,请确认", 1, $aLocation);
            }
            else
            {
                $aRules['userid'] = $aUserid;
            }
            unset($aUserid);
        }
        // 根据网络IP封锁, 进行 trim 处理
        if( $aRules['rangetype'] == 2 )
        {
            $aRules['userip'] = $fv;
        }
        $oFirewallRule = new model_firewall();
        $aFirewallRule = $oFirewallRule->getRuleRowsById($iRuleId);
        if( $aFirewallRule == -1 )
        {
            sysMessage("无效的防火墙规则ID [$iRuleId]", 1, $aLocation);
        }
        $iFlag = $oFirewallRule->updateRuleInfo( $iRuleId, $aRules );
        if( $iFlag > 0 )
        {
            sysMessage("更新成功", 0, $aLocation);
        }
        else
        {
            sysMessage("更新失败.", 1, $aLocation);
        }
    }



    /**
     * 删除规则 (执行处理)
     * URL = ./controller=firewall&action=delrule&id=1
     * @author Tom 090511
     */
    function actionDelrule()
    {
        $aLocation = array(0=>array("text" => "防火墙规则列表","href" => url("firewall","rulelist")));
        $id = is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $id == 0 )
        {
        	sysMessage( "防火墙规则ID错误", 1, $aLocation );
        }
        /* @var $oFirewallAction model_firewall */
        $oFirewallAction = new model_firewall();
        $iFlag = $oFirewallAction->delRuleRowById( $id );
        if( $iFlag > 0 )
        {
        	sysMessage("操作成功", 0, $aLocation );
        }
        else
        {
        	sysMessage("操作失败", 1, $aLocation );
        }
    }
}
?>