<?php
/**
 * 文件 : /_app/controller/config.php
 * 功能 : 控制器 - 网站配置
 * 
 * 功能:
 *    - actionAdd()         增加一个网站配置
 *    - actionDel()         删除一个网站配置
 *    - actionDisable()     禁用一个网站配置
 *    - actionEdit()        修改一个网站配置(运营)
 *    - actionEnable()      启用一个网站配置
 *    - actionList()        网站配置列表查看
 *    - actionSave()        保存一个网站配置
 *    - actionSet()         设置一个网站配置(技术)
 *    - actionSkin()        网站模板管理
 *    - actionUpdate()      更新一个网站配置(运营)
 *    - actionUpdateSet()   更新一个网站配置(技术)
 * 	  - actionAreaList()	行政区管理
 * 	  - actionAddArea()	    增加行政区操作
 *    - actionEditArea()    编辑行政区操作
 *    - actionViewCityList()查看省份下的城市列表
 *    - actionAddCity()     增加城市操作
 *    - actionViewCity()    查看城市信息
 *    - actionEditCity()    编辑城市信息
 * 
 * @author    SAUL      090914
 * @version   1.2.0
 * @package   passportadmin
 */
class controller_config extends basecontroller 
{
    /**
     * 查看网站基本配置项
     * URL = ./?controller=config&action=list
     * @author SAUL 090525
     */
    function actionList()
    {
    	$iConfigId = isset($_GET["id"]) && is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
    	/* @var $oConfig model_config */
    	$oConfig   = A::singleton('model_config');
    	$aConfig   = $oConfig->getConfigByPid( $iConfigId );
    	$GLOBALS['oView']->assign( 'actionlink2', array( 'href'=>url("config","add"), 'text'=>'增加配置项' ) );
    	$GLOBALS['oView']->assign( 'actionlink',  array( 'href'=>url("config","list"), 'text'=>'基本配置列表' ) );
    	$GLOBALS['oView']->assign( "ur_here",     "基本配置");
    	$GLOBALS['oView']->assign( "configs",     $aConfig);
    	$oConfig->assignSysInfo();
    	$GLOBALS['oView']->display("config_list.html");
    	EXIT;
    }



    /**
     * 增加一个网站基本配置项
     * URL = ./?controller=config&action=add
     * @author SAUL 090525
     */
    function actionAdd()
    {
    	/* @var $oConfig model_config */
        $oConfig   = A::singleton('model_config');
    	$aConfig  = $oConfig->getConfigByPid(0);
    	$GLOBALS['oView']->assign( 'ur_here',    "增加配置项");
    	$GLOBALS['oView']->assign( 'configpar',  $aConfig);
    	$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("config","list"), 'text'=>'基本配置列表' ) );
    	$oConfig->assignSysInfo();
    	$GLOBALS['oView']->display("config_info.html");
    	EXIT;
    }



    /**
     * 保存一个网站基本配置项
     * URL = ./?controller=config&action=save
     * @author SAUL 090525
     */
    function actionSave()
    {
    	$iParent 	  = isset($_POST["parent"]) && is_numeric($_POST["parent"]) ? intval($_POST["parent"]) : 0; //父级ID
    	$sConfigkey   = isset($_POST["configkey"])    ? $_POST["configkey"] : ""; //配置项名称(EN)
    	$sTitle		  = isset($_POST["title"])        ? $_POST["title"] : ""; //配置项中文(CN)
    	$sDescription = isset($_POST["description"])  ? $_POST["description"] : ""; //配置项描述
    	$sConfigvalue = isset($_POST["configvalue"])  ? $_POST["configvalue"] : "#"; //配置项值
    	$sDefault     = isset($_POST["defaultvalue"]) ? $_POST["defaultvalue"] : "#"; //配置项默认值
    	$iConfigType  = isset($_POST["configtype"])   ? $_POST["configtype"] : 0;
    	$iInputType   = isset($_POST["inputtype"])    ? $_POST["inputtype"] : 0;
    	$iIsdisabled  = isset($_POST["isdisabled"]) && is_numeric($_POST["isdisabled"]) ? intval($_POST["isdisabled"]) : 0 ;
        $aConfig = array(
            'parentid'       => $iParent,
            'configkey'      => $sConfigkey,
            'configvalue'    => $sConfigvalue,
            'defaultvalue'   => $sDefault,
            'configvaluetype'=> $iConfigType,
            'forminputtype'  => $iInputType,
            'channelid'      => '0',
            'title'          => $sTitle,
            'description'    => $sDescription,
            'isdisabled'     => $iIsdisabled,
        );
        /* @var $oConfig model_config */
        $oConfig   = A::singleton('model_config');
        $iFlag   = $oConfig->addConfig( $aConfig );
    	switch( $iFlag )
    	{
    		case -1:
    			sysMessage( '操作失败:参数错误', 1 );
    			break;
    		case -2:
    			sysMessage( '操作失败:配置项主键重复或者为空', 1 );
    			break;
    		default:
    			sysMessage( '操作成功', 0 );
    			break;
    	}
    }



    /**
     * 修改一个网站基本配置项目(运营)
     * URL = ./?controller=config&action=edit
     * @author SAUL 090529
     */
    function actionEdit()
    {
    	$iConfigId = isset($_GET["id"]) && is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
    	if( $iConfigId == 0 )
    	{
    		redirect( url("config","list") );
    	}
    	/* @var $oConfig model_config */
        $oConfig   = A::singleton('model_config');
    	$aConfig = $oConfig->config( $iConfigId );
    	if( $aConfig["forminputtype"]!='input' )
    	{
    		$aDes = explode(chr(13).chr(10),$aConfig["description"]);
    		$GLOBALS['oView']->assign( "des", $aDes );
    	}
    	if( $aConfig["forminputtype"]=="check" )
    	{
    		$configvalue = explode( "|", $aConfig["configvalue"] );
    		$GLOBALS['oView']->assign("configvalue", $configvalue);
    	}
    	$GLOBALS['oView']->assign("config", $aConfig);
    	$GLOBALS['oView']->assign("ur_here","修改配置");
    	$GLOBALS['oView']->display("config_edit.html");
    	EXIT;
    }



    /**
     * 更新一个网站基本配置项目(运营)
     * URL = ./?controller=config&action=update
     * @author SAUL 090529
     */
    function actionUpdate()
    {
    	$iConfigId = isset($_POST["configid"])&&is_numeric($_POST["configid"]) ? intval($_POST["configid"]) : 0;
    	if ( $iConfigId ==0 )
    	{
    		redirect( url("config","list") );
    	}
    	$mConfigValue = isset($_POST["configvalue"]) ? $_POST["configvalue"] : "";
    	if( is_array($mConfigValue) )
    	{
    		$sConfigValue = join( "|", $mConfigValue ); 
    	}
    	else
    	{
    		$sConfigValue = $mConfigValue;
    	}
    	/* @var $oConfig model_config */
        $oConfig   = A::singleton('model_config');
    	if( $oConfig->updateConfig( $iConfigId, $sConfigValue ) )
    	{
    		sysMessage('操作成功', 0, array(0=>array('text'=>'配置列表','href'=>url('config','list'))));
    	}
    	else
    	{
    		sysMessage('操作失败', 1, array(0=>array('text'=>'配置列表','href'=>url('config','list'))));
    	}
    }



    /**
     * 删除一个网站基本配置项
     * URL =./controller=config&action=del
     * @author SAUL 090529
     */
    function actionDel()
    {
    	$iConfigId = isset($_GET["id"]) && is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
    	if( $iConfigId <= 0 )
    	{
    		redirect( url("config","list") );
    	}
    	/* @var $oConfig model_config */
        $oConfig   = A::singleton('model_config');
    	$iFlag     = $oConfig->configDel( $iConfigId );
    	$aLocation = array(0=>array('text'=>'配置列表','href'=>url('config','list')));
    	if( $iFlag === 1 )
    	{
    		sysMessage( '操作成功', 0, $aLocation );	
    	}
    	elseif( $iFlag === 0 )
    	{
    		sysMessage( '操作失败', 1, $aLocation );
    	}
    	elseif( $iFlag === -1 )
    	{
    		sysMessage( '操作失败:配置项不存在', 1, $aLocation );
    	}
    }



    /**
     * 启用一个网站基本配置
     * URL = ./controller=config&action=enable
     * @author SAUL 090529
     */
    function actionEnable()
    {
    	$iConfigId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
    	if( $iConfigId == 0 )
    	{
    		redirect( url('config','list') );
    	}
    	/* @var $oConfig model_config */
        $oConfig   = A::singleton('model_config');
    	if( $oConfig->setStatus( $iConfigId, 0 ) )
    	{
    		sysMessage( '操作成功', 0 );
    	}
    	else
    	{
    		sysMessage( '操作失败', 1 );
    	}
    }



    /**
     * 禁用一个网站基本配置
     * URL = ./?controller=config&action=disable
     * @author SAUL 090529
     */
    function actionDisable()
    {
    	$iConfigId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
    	if( $iConfigId==0 )
    	{
    		redirect( url('config','list') );
    	}
    	/* @var $oConfig model_config */
        $oConfig   = A::singleton('model_config');
    	if( $oConfig->setStatus( $iConfigId, 1 ) )
    	{
    		sysMessage( '操作成功', 0 );
    	}
    	else
    	{
    		sysMessage( '操作失败', 1 );
    	}
    }



    /**
     * 修改一个网站基本配置(技术)
     * URL = ./?controller=config&action=set
     * @author SAUL 090529
     */
    function actionSet()
    {
    	$iConfigId   = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
    	if( $iConfigId==0 )
    	{
    		redirect( url( "config", "list" ) );
    	}
    	/* @var $oConfig model_config */
        $oConfig   = A::singleton('model_config');
    	$aConfigList = $oConfig->getConfigByPid(0);
    	$aConfig     = $oConfig->config( $iConfigId );
    	$GLOBALS['oView']->assign("ur_here",   "设置配置");
    	$GLOBALS['oView']->assign("config",    $aConfig);
    	$GLOBALS['oView']->assign("configpar", $aConfigList);
    	$GLOBALS['oView']->assign('action',    'updateset');
    	$oConfig->assignSysInfo();
    	$GLOBALS['oView']->display("config_info.html");
    	EXIT;
    }



    /**
     * 更新一个网站基本配置(技术)
     * URL = ./?controller=config&action=updateset
     * @author SAUL 090529
     */
    function actionUpdateset()
    {
    	$iConfigId = isset($_POST["configid"])&&is_numeric($_POST["configid"]) ? intval($_POST["configid"]) : 0;
    	$iParent   = isset($_POST["parent"])&&is_numeric($_POST["parent"])     ? intval($_POST["parent"])   : 0;
    	$sKey      = isset($_POST["configkey"])    ? $_POST["configkey"]   : "";
    	$sTitle    = isset($_POST["title"])        ? $_POST["title"]       : "";		
    	$sValue    = isset($_POST["configvalue"])  ? $_POST["configvalue"] : "";
    	$sDefault  = isset($_POST["defaultvalue"]) ? $_POST["defaultvalue"]: "";
    	$iInput    = isset($_POST["inputtype"])    ? $_POST["inputtype"]   : 0;
    	$iType     = isset($_POST["configtype"])   ? $_POST["configtype"]  : 0;
    	$sDescr    = isset($_POST["description"])  ? $_POST["description"] : "";
    	$iIsDisable= isset($_POST["isdisabled"])&&is_numeric($_POST["isdisabled"]) ? intval($_POST["isdisabled"]) : 0;
    	if( $iConfigId==0 )
    	{
    		redirect( url("config","list") );
    	}
    	if($sKey=="" || $sTitle == "")
    	{
    		sysMessage( "参数不全", 1 );
    	}
    	$aConfig = array(
    		"parentid"			=>	$iParent,
    		"configkey"			=>	$sKey,
    		"configvalue"		=>	$sValue,
    		"defaultvalue"		=>	$sDefault,
    		"forminputtype"		=>	$iInput,
    		"configvaluetype"	=>	$iType,
    		"description" 		=>	$sDescr,
    		"isdisabled"		=>	$iIsDisable,
    		"title"				=>	$sTitle
    	);
    	/* @var $oConfig model_config */
        $oConfig   = A::singleton('model_config');
    	$iFlag   = $oConfig->update( $aConfig, $iConfigId );
    	if($iFlag == 1 )
    	{
    		sysMessage( '操作成功', 0 );
    	}
    	else
    	{
    		sysMessage( '操作失败', 1 );
    	}
    }



    /**
     * 模板管理列表
     * URL = ./?controller=config&action=skinlist
     * 检测 PASSPORT 可用模板, 显示每个模板文件数量
     * @author Tom
     */
    function actionSkinlist()
    {
        // 设置模板文件夹基本路径
        $oUserSkins = new model_userskins();
        $sViewSkinBaseDir = $oUserSkins->getBashPath();
        $aDirFileList     = scandir( $sViewSkinBaseDir );
        $aSkinArray       = array(); // 最终模板数组
    
        // 获取模板名(目录名),  忽略隐藏文件 (即: 以 dot 开头的文件)
        foreach( $aDirFileList AS $k=>$v )
        {
            if( FALSE !== strpos( $v, '.' ) || !is_dir($sViewSkinBaseDir.$v) )
            {
                unset($aDirFileList[$k]);
                continue;
            }
            $aTmpDir  = scandir( $sViewSkinBaseDir.$v );
            $aTmp['filelist'] = array();
            foreach ( $aTmpDir AS $x=>$y )
            { // 遍历模板文件
    	        if( substr( $y, 0,1 ) == '.' || is_dir($sViewSkinBaseDir.$y) )
    	        {
    	            unset($aTmpDir[$x]);
    	            continue;
    	        }
    	        array_push( $aTmp['filelist'], $aTmpDir[$x] );
            }
            $aTmp['skinname'] = $v; // 临时数组
            $aTmp['counts']   = count($aTmp['filelist']);
            $aTmp['dirlasttime'] = date("Y-m-d H:i:s", filemtime($sViewSkinBaseDir.$v)); 
            array_push( $aSkinArray, $aTmp );
            unset($aTmp,$aTmpDir);
        }
        //print_rr($aSkinArray);EXIT;
        $oUserSkins->assignSysInfo();
        unset($oUserSkins);
    	$GLOBALS['oView']->assign("ur_here",   "模板管理");
    	$GLOBALS['oView']->assign("aSkinArr",   $aSkinArray);
    	$GLOBALS['oView']->assign('action',    'updateset');
    	$GLOBALS['oView']->display("config_skinlist.html");
    	EXIT;
    }


    /**
     * 模板管理详情
     * URL = ./?controller=config&action=skininfo&n=default
     * 并且根据默认模板,检查HTML完整性
     * @author Tom
     */
    function actionSkininfo()
    {
        $aLocation  = array( 0=>array( "text" => "模板管理", "href" => url( 'config', 'skinlist' ) ));
        $oUserSkins = new model_userskins(); 

        if( empty($_GET['n']) || FALSE==$oUserSkins->skinsCheck($_GET['n']) )
        {
            sysMessage( '模板名称不符合规则或不存在', 1, $aLocation );
        }
        $sSkinName = $_GET['n'];

        // 设置模板文件夹基本路径
        $sDefaultSkinDir  = $oUserSkins->getDirDefault();
        $sViewSkinBaseDir = $oUserSkins->getBashPath();

        // 1, 获取 DEFAULT 所有模板文件
        $aDefaultSkinFileList = array();
        $aDefaultSkinFile     = array();  // 用于计算差集
        $aDirFileList     = scandir( $sViewSkinBaseDir.$sDefaultSkinDir );
        foreach( $aDirFileList AS $v )
        {
            if( substr( $v, 0,1 ) == '.' || is_dir($sViewSkinBaseDir.$v) )
            {
                continue;
            }
            // flag = 0   默认模板
            // flag = 1   表示存在此模板
            // flag = -1  表示多出模板文件
            $aDefaultSkinFileList[] = array( 'fname'=>$v, 'flag'=>0 );
            $aDefaultSkinFile[] = $v;
        }
        unset($aDirFileList);
        // 只有当前选择的模板, 不等于默认模板时, 才执行差异计算
        if( $sSkinName != $sDefaultSkinDir )
        {
            // 2, 获取当前选择模板 所有文件
            $iCountDefaultFile = count($aDefaultSkinFileList);
    	    $aSelectSkinFile     = array(); // 用于计算差集
            $aDirFileList     = scandir( $sViewSkinBaseDir.$sSkinName );
            foreach( $aDirFileList AS $v )
            {
                if( substr( $v, 0,1 ) == '.' || is_dir($sViewSkinBaseDir.$v) )
    	        {
    	            continue;
    	        }
    	        $aSelectSkinFile[] = $v;
    	        for( $i=0; $i<$iCountDefaultFile; $i++ )
    	        {
    	            if( $aDefaultSkinFileList[$i]['fname'] == $v )
    	            {
    	                $aDefaultSkinFileList[$i]['flag'] = 1;
    	                break;
    	            }
    	        }
            }
            unset($aDirFileList);

            // 3, 获取新模板中, 比默认模板多的 html 模板文件
            $aDiff = array_diff( $aSelectSkinFile, $aDefaultSkinFile );
            foreach( $aDiff AS $v )
            {
                $aDefaultSkinFileList[] = array( 'fname'=>$v, 'flag'=>-1 );
            }
            unset($aDiff);
        }

        $oUserSkins->assignSysInfo();
        $GLOBALS['oView']->assign('actionlink',  array( 'href'=>url("config","skinlist"), 'text'=>'模板管理' ) );
    	$GLOBALS['oView']->assign("ur_here",   "模板详情 [ $sSkinName ]");
    	$GLOBALS['oView']->assign("aDefaultSkinFileList",  $aDefaultSkinFileList);
    	$GLOBALS['oView']->assign("sSkinName",   $sSkinName);
    	$GLOBALS['oView']->assign('action',    'updateset');
    	$GLOBALS['oView']->display("config_skininfo.html");
    	EXIT;
    }
    
    
    /**
     * 行政区管理
     *
     * @version 	v1.0	2010-04-20
     * @author 		louis
     */
    public function actionAreaList(){
    	// 获取行政区列表
    	$oAreaList = new model_withdraw_AreaList();
    	$oAreaList->ParentId = 0;
//    	$oAreaList->Used = 1; // 后台管理页面可以看到所有行政区列表
    	$oAreaList->init();
    	
        $GLOBALS['oView']->assign('actionlink',  array( 'href'=>url("config","addarea"), 'text'=>'增加行政区' ) );
        $GLOBALS['oView']->assign("ur_here",   "行政区管理");
        $GLOBALS['oView']->assign("arealist",   $oAreaList->Data);
    	$oAreaList->assignSysInfo();
    	$GLOBALS['oView']->display("config_arealist.html");
    	EXIT;
    }
    
    
    /**
     * 增加行政区操作
     *
     * @version 	v1.0	2010-04-21
     * @author 		louis
     */
    public function actionAddArea(){
    	// 数据检查
    	$aLinks  = array( 0=>array( "text" => "返回行政区列表", "href" => url( 'config', 'arealist' ) ));
    	$oArea = new model_withdraw_Area();
    	
    	if ($_POST['flag'] == "add"){
    		$_POST['province'] or sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
    		if (($_POST['zipcode'] != "" && !is_numeric($_POST['zipcode'])) || strlen($_POST['zipcode']) > 6)
    			sysMessage("您提交的邮编格式错误，请核对后重新提交！", 1, $aLinks);
    		if (($_POST['telecode'] != "" && !is_numeric($_POST['telecode'])) || strlen($_POST['telecode']) > 5)
    			sysMessage("您提交的区号格式错误，请核对后重新提交！", 1, $aLinks);
    		if ($_POST['orders'] != "" && !is_numeric($_POST['orders']))
    			sysMessage("您提交的排序格式错误，请核对后重新提交！", 1, $aLinks);
    		
    		$oArea->ParentId 	= 0;
    		$oArea->Name 		= $_POST['province'];
    		if ($oArea->areaIsExistByName() === true){
    			sysMessage("行政区 {$_POST['province']} 已经存在！", 1, $aLinks);
    		}
    		$oArea->Zipcode		= $_POST['zipcode'];
    		$oArea->Telecode	= $_POST['telecode'];
    		$oArea->Orders		= $_POST['orders'] > 0 ? $_POST['orders'] : 0;
    		$oArea->Used		= 0; // 新增加的行政区默认都为禁用，需要上级审核才能使用<br>
			if ($oArea->save())
				sysMessage("操作成功", 0, $aLinks);
			else 
				sysMessage("操作失败", 0, $aLinks);
    	}
        $GLOBALS['oView']->assign("ur_here",   "增加行政区");
    	$oArea->assignSysInfo();
    	$GLOBALS['oView']->display("config_addarea.html");
    	EXIT;
    }
    
    
    
    /**
     * 删除行政区信息
     *
     * @version 	v1.0	2010-05-11
     * @author 		louis
     */
    public function actionDelArea(){
    	$aLinks  = array( 0=>array( "text" => "返回行政区列表", "href" => url( 'config', 'arealist' ) ));
    	
    	$sHasSon = ""; // 有下级城市的行政区
    	$iSuccess = 0; // 成功操作的个数
    	$sFailed = ""; // 失败的行政区
    	$aInfo = array(); // 记录数组
    	
    	if (!empty($_POST['area'])){
    		$aInfo = $_POST['area'];
    	}
    	
    	if (is_numeric($_GET['id']) && $_GET['id'] > 0){
    		$aInfo[] = intval($_GET['id']) > 0 ? $_GET['id'] : '';
    	}
    	
    	// 数据检查
    	if (empty($aInfo)) sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
    	foreach ($aInfo as $k => $v){
    		$oArea = new model_withdraw_Area($v);
    		// 检查数据是否存在
    		if ($oArea->areaIsExist() === false){
    			$sFailed .= $oArea->Name . ',';
    			continue;
    		}
    		
    		// 检查行政区下是否有城市
    		if ($oArea->getCount() > 0){
    			$sHasSon .= $oArea->Name . ',';
    			continue;
    		}
    		if ($oArea->erse() > 0){
    			$iSuccess++;
    		} else {
    			$sFailed .= $oArea->Name . ',';
    		}
    	}
    	if ($iSuccess == count($aInfo)){
    		sysMessage("操作成功！", 0, $aLinks);
    	} else {
    		if (!empty($sHasSon)){
    			$sHasSon = mb_substr($sHasSon, 0, -1, 'utf-8');
    			sysMessage("行政区 {$sHasSon} 下有城市，不能删除！", 1, $aLinks);
    		}
    		if (!empty($sFailed)){
    			$sFailed = mb_substr($sFailed, 0, -1, 'utf-8');
    			sysMessage("删除行政区 {$sFailed} 失败！", 1, $aLinks);
    		}
    	}
    }
    
    
    /**
     * 编辑行政区操作
     *
     * @version 	v1.0	2010-04-21
     * @author 		louis
     */
    public function actionEditArea(){
    	$aLinks  = array( 0=>array( "text" => "返回行政区列表", "href" => url( 'config', 'arealist' ) ));
    	
    	if (!is_numeric($_GET['id']) || $_GET['id'] <= 0){
    		sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
    	}
    	// 获取省份信息
    	$oArea = new model_withdraw_Area($_GET['id']);
    	if ($oArea->areaIsExist() === false){
    		sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
    	}
    	
    	$_POST['flag'] = isset($_POST['flag']) ? $_POST['flag'] : '';
    	// 编辑
    	if ($_POST['flag'] == "edit"){
    		$_POST['province'] or sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
    		if (($_POST['zipcode'] != "" && !is_numeric($_POST['zipcode'])) || strlen($_POST['zipcode']) > 6)
    			sysMessage("您提交的邮编格式错误，请核对后重新提交！", 1, $aLinks);
    		if (($_POST['telecode'] != "" && !is_numeric($_POST['telecode'])) || strlen($_POST['telecode']) > 5)
    			sysMessage("您提交的区号格式错误，请核对后重新提交！", 1, $aLinks);
    		if ($_POST['orders'] != "" && !is_numeric($_POST['orders']))
    			sysMessage("您提交的排序格式错误，请核对后重新提交！", 1, $aLinks);
    		$oArea->Name 	 = $_POST['province'];
    		if ($oArea->areaIsExistByName() === true){
    			sysMessage("行政区 {$_POST['province']} 已经存在！", 1, $aLinks);
    		}
    		$oArea->Zipcode  = $_POST['zipcode'];
    		$oArea->Telecode = $_POST['telecode'];
    		$oArea->Orders   = $_POST['orders'] > 0 ? $_POST['orders'] : 0;
    		$oArea->Used	 = $_POST['used'] != 0 ? 1 : 0;
    		if ($oArea->save())
    			sysMessage("修改成功！", 0, $aLinks);
    		else 
    			sysMessage("修改失败！", 0, $aLinks);
    	}
    	
        $GLOBALS['oView']->assign("ur_here",   "编辑行政区信息");
        $GLOBALS['oView']->assign("name",   $oArea->Name);
        $GLOBALS['oView']->assign("zipcode",   $oArea->Zipcode);
        $GLOBALS['oView']->assign("telecode",   $oArea->Telecode);
        $GLOBALS['oView']->assign("used",   $oArea->Used);
        $GLOBALS['oView']->assign("orders",   $oArea->Orders);
    	$oArea->assignSysInfo();
    	$GLOBALS['oView']->display("config_editarea.html");
    	EXIT;
    }
    
    
    /**
     * 修改行政区状态
     *
     * @version 	v1.0	2010-05-12
     * @author 		louis
     * 
     * @return 		bool
     */
    public function actionSetStatus(){
    	$aInfo = array(); // 行政区数组
    	$iSuccess = 0 ; // 操作成功数
    	$sFailed = ""; // 失败记录
    	
    	if (!empty($_POST['area'])){
    		$aInfo = $_POST['area'];
    	}
    	if (is_numeric($_GET['id']) && $_GET['id'] > 0){
    		$aInfo[] = $_GET['id'];
    	}
    	
    	$aLinks  = array( 0=>array( "text" => "返回行政区列表", "href" => url( 'config', 'arealist' ) ));
    	
    	if (empty($aInfo))	sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
    	// 修改状态
    	foreach ($aInfo as $k => $v){
    		$oArea = new model_withdraw_Area($v);
    		if ($oArea->areaIsExist() === false){
    			$sFailed .= $oArea->Name . ',';
    			continue;
    		}
    		$oArea->Used = intval(1 - $oArea->Used);
    		$oArea->ParentId = 0;
    		if ($oArea->save()){
    			$iSuccess++;
    		} else {
    			$sFailed .= $oArea->Name . ',';
    		}
    	}
		if ($iSuccess == count($aInfo))
			sysMessage("修改成功！", 0, $aLinks);
		else {
			$sFailed = mb_substr($sFailed, 0, -1, "utf-8");
			sysMessage("行政区{$sFailed}修改状态失败！", 1, $aLinks);
		}
    }
    
    
    /**
     * 查看省份下的城市列表
     *
     * @version 	v1.0	2010-04-21
     * @author 		louis
     */
    public function actionViewCityList(){
    	$aLinks  = array( 0=>array( "text" => "返回行政区列表", "href" => url( 'config', 'arealist' ) ));
    	if (!is_numeric($_GET['id']) || $_GET['id'] <= 0){
    		sysMessage("你提交的数据有误，请核对后重新提交！", 1, $aLinks);
    	}
    	// 获取行政区列表
    	$oAreaList = new model_withdraw_AreaList();
    	$oAreaList->ParentId = $_GET['id'];
//    	$oAreaList->Used = 1; // 后台管理页面显示所有城市列表
    	$oAreaList->init();
    	
    	// 获取省份信息
    	$oArea = new model_withdraw_Area($_GET['id']);
    	
        $GLOBALS['oView']->assign('actionlink',  array( 'href'=>url("config","addcity"), 'text'=>'增加城市' ) );
        $GLOBALS['oView']->assign("ur_here",    "{$oArea->Name}城市列表");
        $GLOBALS['oView']->assign("area",   $_GET['id']);
        $GLOBALS['oView']->assign("citylist",   $oAreaList->Data);
        $GLOBALS['oView']->assign("province",   $oArea->Name);
    	$oAreaList->assignSysInfo();
    	$GLOBALS['oView']->display("config_viewcitylist.html");
    	EXIT;
    }
    
    
    /**
     * 增加城市操作
     *
     * @version 	v1.0	2010-04-21
     * @author 		louis
     */
    public function actionAddCity(){
    	// 数据检查
    	$aLinks  = array( 0=>array( "text" => "返回城市列表", "href" => 'javascript:history.back();' ));
    	// 获取行政区列表
    	$oAreaList = new model_withdraw_AreaList();
    	$oAreaList->ParentId = 0;
    	$oAreaList->Used = 1;
    	$oAreaList->init();
    	
    	if ($_POST['flag'] == "add"){
    		if (intval($_POST['province']) <= 0 || empty($_POST['city'])){
    			sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
    		}
    		$aLinks  = array( 0=>array( "text" => "返回城市列表", "href" => '?controller=config&action=viewcitylist&id=' . $_POST['province'] ));
    		if (($_POST['zipcode'] != "" && !is_numeric($_POST['zipcode'])) || strlen($_POST['zipcode']) > 6)
    			sysMessage("您提交的邮编格式错误，请核对后重新提交！", 1, $aLinks);
    		if (($_POST['telecode'] != "" && !is_numeric($_POST['telecode'])) || strlen($_POST['telecode']) > 5)
    			sysMessage("您提交的区号格式错误，请核对后重新提交！", 1, $aLinks);
    		if ($_POST['orders'] != "" && !is_numeric($_POST['orders']))
    			sysMessage("您提交的排序格式错误，请核对后重新提交！", 1, $aLinks);
    		
    		$oArea = new model_withdraw_Area();
    		$oArea->ParentId 	= $_POST['province'];
    		$oArea->Name 		= $_POST['city'];
    		if ($oArea->cityIsExistByName() === true){
    			sysMessage("城市 {$_POST['city']} 已经存在！", 1, $aLinks);
    		}
    		$oArea->Zipcode		= $_POST['zipcode'];
    		$oArea->Telecode	= $_POST['telecode'];
    		$oArea->Orders		= $_POST['orders'] > 0 ? $_POST['orders'] : 0;
    		$oArea->Used		= 0; // 新增加的行政区默认都为禁用，需要上级审核才能使用<br>
			if ($oArea->save())
				sysMessage("操作成功", 0, $aLinks);
			else 
				sysMessage("操作失败", 0, $aLinks);
    	}
        $GLOBALS['oView']->assign("ur_here",   "增加行政区");
        $GLOBALS['oView']->assign("provincelist",   $oAreaList->Data);
    	$oAreaList->assignSysInfo();
    	$GLOBALS['oView']->display("config_addcity.html");
    	EXIT;
    }
    
    
    /**
     * 查看城市信息
     *
     * @version 	v1.0	2010-04-21
     * @author 		louis
     */
    public function actionViewCity(){
    	$aLinks  = array( 0=>array( "text" => "返回城市列表", "href" => 'javascript:history.back();' ));
    	if (!is_numeric($_GET['id']) || $_GET['id'] <= 0 || !is_numeric($_GET['parent_id']) || $_GET['parent_id'] <= 0){
    		sysMessage("你提交的数据有误，请核对后重新提交！", 1, $aLinks);
    	}
    	
    	// 检查行政区是否存在
    	$oArea = new model_withdraw_Area();
    	$oArea->Id = $_GET['id'];
    	$oArea->ParentId = $_GET['parent_id'];
		// 检查数据是否存在
		if ($oArea->cityIsExist() === false){
			sysMessage("你提交的数据有误，请核对后重新提交！", 1, $aLinks);
		}
    	
    	// 获取城市信息
    	$oArea = new model_withdraw_Area($_GET['id']);
        $GLOBALS['oView']->assign("ur_here",    "{$oArea->Name}市信息");
        $GLOBALS['oView']->assign("city",    $oArea->Name);
        $GLOBALS['oView']->assign("zipcode",    $oArea->Zipcode);
        $GLOBALS['oView']->assign("telecode",    $oArea->Telecode);
        $GLOBALS['oView']->assign("used",    $oArea->Used);
        // 获取所属行政区名称
        $oArea = new model_withdraw_Area($oArea->ParentId);
        $GLOBALS['oView']->assign("province",    $oArea->Name);
    	$oArea->assignSysInfo();
    	$GLOBALS['oView']->display("config_viewcity.html");
    	EXIT;
    }
    
    
    /**
     * 编辑城市信息
     *
     * @version 	v1.0	2010-04-21
     * @author 		louis
     */
    public function actionEditCity(){
    	$aLinks  = array( 0=>array( "text" => "返回城市列表", "href" => 'javascript:history.back();' ));
    	if (!is_numeric($_GET['id']) || $_GET['id'] <= 0){
    		sysMessage("你提交的数据有误，请核对后重新提交！", 1, $aLinks);
    	}
    	
    	// 编辑
    	if ($_POST['flag'] == "edit"){
    		$oArea = new model_withdraw_Area($_GET['id']);
    		if ($oArea->cityIsExist() === false) sysMessage("你提交的数据有误，请核对后重新提交！", 1, $aLinks);
    		$aLinks  = array( 0=>array( "text" => "返回城市列表", "href" => '?controller=config&action=viewcitylist&id=' . $oArea->ParentId ));
    		if (empty($_POST['city']) || intval($_POST['province']) <= 0)
    			sysMessage("你提交的数据有误，请核对后重新提交！", 1, $aLinks);
    		if (($_POST['zipcode'] != "" && !is_numeric($_POST['zipcode'])) || strlen($_POST['zipcode']) > 6)
    			sysMessage("您提交的邮编格式错误，请核对后重新提交！", 1, $aLinks);
    		if (($_POST['telecode'] != "" && !is_numeric($_POST['telecode'])) || strlen($_POST['telecode']) > 5)
    			sysMessage("您提交的区号格式错误，请核对后重新提交！", 1, $aLinks);
    		if ($_POST['orders'] != "" && !is_numeric($_POST['orders']))
    			sysMessage("您提交的排序格式错误，请核对后重新提交！", 1, $aLinks);
    			
    		$oArea->Name 	 = $_POST['city'];
    		$oArea->ParentId = $_POST['province'];
    		if ($oArea->cityIsExistByName() === true){
    			sysMessage("城市 {$_POST['city']} 已经存在！", 1, $aLinks);
    		}
    		$oArea->Zipcode  = $_POST['zipcode'];
    		$oArea->Telecode = $_POST['telecode'];
    		$oArea->Orders   = $_POST['orders'] > 0 ? $_POST['orders'] : 0;
    		$oArea->Used	 = $_POST['used'] != 0 ? 1 : 0;
    		if ($oArea->save())
    			sysMessage("修改成功！", 0, $aLinks);
    		else 
    			sysMessage("修改失败！", 0, $aLinks);
    	}
    	
    	// 获取行政区列表
    	$oAreaList = new model_withdraw_AreaList();
    	$oAreaList->ParentId = 0;
    	$oAreaList->init();
    	
    	// 获取城市信息
    	$oArea = new model_withdraw_Area($_GET['id']);
    	
        $GLOBALS['oView']->assign("ur_here",    "{$oArea->Name}市信息");
        $GLOBALS['oView']->assign("city",    $oArea->Name);
        $GLOBALS['oView']->assign("provincelist",    $oAreaList->Data);
        $GLOBALS['oView']->assign("parentid",    $oArea->ParentId);
        $GLOBALS['oView']->assign("zipcode",    $oArea->Zipcode);
        $GLOBALS['oView']->assign("telecode",    $oArea->Telecode);
        $GLOBALS['oView']->assign("used",    $oArea->Used);
        $GLOBALS['oView']->assign("orders",    $oArea->Orders);
    	$oArea->assignSysInfo();
    	$GLOBALS['oView']->display("config_editcity.html");
    	EXIT;
    }
    
    
    /**
     * 修改城市状态
     *
     * @version 	v1.0	2010-05-12
     * @author 		louis
     * 
     * @return 		bool
     */
    public function actionSetCityStatus(){
    	$aInfo = array(); // 行政区数组
    	$iSuccess = 0 ; // 操作成功数
    	$sFailed = ""; // 失败记录
    	    	
    	if (!empty($_POST['city'])){
    		$aInfo = $_POST['city'];
    	}
    	if (is_numeric($_GET['cityid']) && $_GET['cityid'] > 0){
    		$aInfo[] = $_GET['cityid'];
    	}
    	
    	$iParentId = intval($_GET['parent_id']) > 0 ? $_GET['parent_id'] : $_POST['parent_id'];
    	if (!is_numeric($iParentId) || $iParentId <= 0){
    		sysMessage("您提交的数据有误，请核对后重新提交！", 1);
    	}
    	
    	$aLinks  = array(
    		0 => array(
    			'text' => '返回城市列表',
    			'href' => '?controller=config&action=viewcitylist&id=' . $iParentId
    		)
    	);
    	
    	
    	if (empty($aInfo))	sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
    	
    	// 修改状态
    	foreach ($aInfo as $k => $v){
    		$oArea = new model_withdraw_Area($v);
    		if ($oArea->cityIsExist() === false){
    			$sFailed .= $oArea->Name . ',';
    			continue;
    		}
			$oArea->Used = intval(1 - $oArea->Used);
			$oArea->ParentId = $iParentId;
			if ($oArea->save()){
				$iSuccess++;
			} else {
				$sFailed .= $oArea->Name . ',';
			}
    	}
    	if ($iSuccess == count($aInfo))
				sysMessage("修改成功！", 0, $aLinks);
			else {
				$sFailed = mb_substr($sFailed, 0, -1, "utf-8");
				sysMessage("修改城市{$sFailed}时失败！", 1, $aLinks);
			}
    }
    
    
    /**
     * 删除城市信息
     *
     * @version 	v1.0	2010-05-11
     * @author 		louis
     * 
     * @return 		bool
     */
    public function actionDelCity(){
    	$iParentId = intval($_POST['parent_id']) > 0 ? $_POST['parent_id'] : $_GET['parent_id'];
    	$aLinks = array(
    		0 => array(
    			'text' => '返回城市列表',
    			'href' => '?controller=config&action=viewcitylist&id=' . $iParentId
    		)
    	);
    	if (!is_numeric($iParentId) || $iParentId <= 0){
    		sysMessage("你提交的数据有误，请核对后重新提交！", 1, $aLinks);
    	}
    	
    	$aInfo = array(); // 记录数组
    	$iSuccess = 0; // 成功操作的个数
    	$sFailed = ""; // 操作失败的城市
    	
    	if (!empty($_POST['city'])){
    		$aInfo = $_POST['city'];
    	} else {
    		$aInfo[] = intval($_GET['id']) > 0 ? $_GET['id'] : '';
    	}
    	
    	// 数据检查
    	if (empty($aInfo)) sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
    	
    	// 检查城市信息是否存在
    	foreach ($aInfo as $k => $v){
    		$oArea = new model_withdraw_Area($v);
    		$oArea->ParentId = $iParentId;
    		if ($oArea->cityIsExist() === false){
    			$sFailed .= $oArea->Name . ',';
    			continue;
    		}
    		
    		if ($oArea->delCity() > 0){
    			$iSuccess++;
    		} else {
    			$sFailed .= $oArea->Name . ',';
    		}
    	}
    	
    	if ($iSuccess == count($aInfo)){
    		sysMessage("操作成功！", 0, $aLinks);
    	} else {
    		$sFailed = mb_substr($sFailed, 0, -1, 'utf-8');
    		sysMessage("删除城市{$sFailed}时失败！", 1, $aLinks);
    	}
    }




    /**
     * 图片服务器设置
     * URL = ./index.php?controller=config&action=adminimg
     * @author Mark
     */
    public function actionAdminImg()
    {
        /* @var $oConfig model_config */
        $oConfig    = A::singleton("model_config");
        if( isset($_POST)&&!empty($_POST) )
        {
            $sImgServer = isset($_POST["imgserver"]) ? $_POST["imgserver"] : '';
            $aConfig    = array( "imgserver" => $sImgServer );
            $aLocation[0]  = array('text'=>'图片服务器的设置','href'=>url('config','adminimg'));
            if( $oConfig->updateConfigs($aConfig) )
            {
                $sGamePath = PDIR_USER;
                $sGameCssBasePath = $sGamePath.'/css';
                $aTmpDirPathList = scandir( $sGameCssBasePath );
                $aDirPathList = array();//模板目录
                foreach ( $aTmpDirPathList as $sPath )
                {
                    if( strpos($sPath, '.') === FALSE )
                    {
                        $aDirPathList[] = $sPath;
					}
                }
                unset($aTmpDirPathList);
                $aCssFileList = array();//文件位置
                foreach ( $aDirPathList as $sCssPathList )
                {
                    $aTmpCssFileList = scandir($sGameCssBasePath."/".$sCssPathList) ;
                    foreach ($aTmpCssFileList as $sTmpFile)
                    {
                        $sFile = $sGameCssBasePath . "/" . $sCssPathList . "/" .$sTmpFile;
                        if( preg_match("/^(base_).*(.css)$/", $sTmpFile) )
                        {
                            $aCssFileList[] = $sFile;
                        }
                    }
                }
                //更新CSS文件
                foreach ($aCssFileList as $sCssFile )
                {
                    $sFileContent = file_get_contents($sCssFile);
                    $sNewFileContent = str_replace("{\$sSystemImagesAndCssPath}",$sImgServer,$sFileContent);
                    $sNewCssFile = str_replace("base_", "", $sCssFile );
                    file_put_contents($sNewCssFile,$sNewFileContent);
                }
                sysMessage( '操作成功', 0, $aLocation );
            }
            else
            {
                sysMessage( '操作失败', 1, $aLocation );
            }
        }
        else
        {
            $sConfig = $oConfig->getConfigs("imgserver");
            $GLOBALS['oView']->assign("config",$sConfig);
            $GLOBALS['oView']->assign("ur_here","图片服务器的设置");
            $GLOBALS['oView']->display("config_adminimg.html");
            EXIT;
        }
    }
    
    
    /**
     * 网管参数设置
     *
     */
    public function actionNMParam()
    {
    	$oConfig = A::singleton("model_config");
    	$aLocation[0]  = array('text'=>'网管参数设置','href'=>url('config','nmparam'));
    	
    	if ( $_POST )
    	{
    		if ( $oConfig->updateConfigs( array( 'domainbind_seemgroup' => intval( $_POST['domainbind_seemgroup'] ),
    				'domainbind_seemdomain' => intval( $_POST['domainbind_seemdomain'] ),
    				'domainbind_checkimage' => daddslashes( $_POST['domainbind_checkimage'] )
    				 ) ) )
    		{
    			sysMessage( '配置保存成功', 0, $aLocation );
    		}
    		else 
    		{
    			sysMessage( '配置保存失败', 1, $aLocation );
    		}
    	}
    	else 
    	{

    		$GLOBALS['oView']->assign( 'domainbind_seemgroup', $oConfig->getConfigs('domainbind_seemgroup') );
    		$GLOBALS['oView']->assign( 'domainbind_seemdomain',  $oConfig->getConfigs('domainbind_seemdomain') );
    		$GLOBALS['oView']->assign( 'domainbind_checkimage',  $oConfig->getConfigs('domainbind_checkimage') );
    		$GLOBALS['oView']->assign( 'ur_here',  '网管参数设置' );
            $GLOBALS['oView']->display( 'config_nmparam.html' );
            EXIT;
    	}
    	
    }
}
?>