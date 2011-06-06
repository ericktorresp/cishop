<?php
/**
 * 文件 : /_app/controller/config.php
 * 功能 : 控制器 - 网站配置
 * 
 * 功能:
 *  - actionAdd()       增加一个网站配置
 *  - actionDel()       删除一个网站配置
 *  - actionDisable()   禁用一个网站配置
 *  - actionEdit()      修改一个网站配置(运营)
 *  - actionEnable()    启用一个网站配置
 *  - actionList()      网站配置列表查看
 * 	- actionSave()      保存一个网站配置
 *  - actionSet()       设置一个网站配置(技术)
 *  - actionSkin()      网站模板管理
 *  - actionUpdate()    更新一个网站配置(运营)
 *  - actionUpdateSet() 更新一个网站配置(技术)
 *  - actionSkinlist()  模版管理列表
 *  - actionSkinInfo()  模版管理详情
 * 
 * 
 * @author     TOM,SAUL
 * @version    1.2.0
 * @package    lowadmin
 */
class controller_config extends basecontroller 
{
    /**
     * 查看网站基本配置项
     * URL = ./index.php?controller=config&action=list
     * @author SAUL
     */
    function actionList()
    {
        $iConfigId = isset($_GET["id"]) && is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        /* @var $oConfig model_config */
        $oConfig   = A::singleton("model_config");
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
     * URL = ./index.php?controller=config&action=add
     * @author SAUL
     */
    function actionAdd()
    {
        /* @var $oConfig model_config*/
        $oConfig  = A::singleton("model_config");
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
     * URL = ./index.php?controller=config&action=save
     * @author SAUL
     */
    function actionSave()
    {
        $iParent      = isset($_POST["parent"])&&is_numeric($_POST["parent"]) ? intval($_POST["parent"]) : 0; //父级ID
        $sConfigkey   = isset($_POST["configkey"])    ? $_POST["configkey"] : ""; //配置项名称(EN)
        $sTitle       = isset($_POST["title"])        ? $_POST["title"] : ""; //配置项中文(CN)
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
        /* @var $oConfig model_config*/
        $oConfig = A::singleton("model_config");
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
     * URL = ./index.php?controller=config&action=edit
     * @author SAUL
     */
    function actionEdit()
    {
        $iConfigId = isset($_GET["id"]) && is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iConfigId == 0 )
        {
            redirect( url("config","list") );
        }
        /* @var $oConfig model_config */
        $oConfig = A::singleton("model_config");
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
        $GLOBALS['oView']->assign( "config",  $aConfig );
        $GLOBALS['oView']->assign( "ur_here", "修改配置" );
        $GLOBALS['oView']->display("config_edit.html");
        EXIT;
    }



    /**
     * 更新一个网站基本配置项目(运营)
     * URL = ./index.php?controller=config&action=update
     * @author SAUL
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
        $oConfig = A::singleton("model_config");
        $aLocation[0] = array('text'=>'基本配置列表','href'=>url('config','list'));
        if( $oConfig->updateConfig( $iConfigId, $sConfigValue ) )
        {
            sysMessage('操作成功', 0, $aLocation);
        }
        else
        {
            sysMessage('操作失败', 1, $aLocation);
        }
    }



    /**
     * 删除一个网站基本配置项
     * URL =./index.php?controller=config&action=del
     * @author SAUL
     */
    function actionDel()
    {
        $iConfigId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iConfigId <= 0 )
        {
            redirect( url("config","list") );
        }
        /* @var $oConfig model_config*/
        $oConfig   = A::singleton("model_config");
        $iFlag     = $oConfig->configDel( $iConfigId );
        $aLocation[0] = array('text'=>'基本配置列表','href'=>url('config','list'));
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
     * @author SAUL
     */
    function actionEnable()
    {
        $aLocation[0] = array( "text"=>"基本配置列表", "href"=>url("config","list") );
        $iConfigId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iConfigId == 0 )
        {
            redirect( url('config','list') );
        }
        /* @var $oConfig model_config */
        $oConfig = A::singleton("model_config");
        if( $oConfig->setStatus( $iConfigId, 0 ) )
        {
            sysMessage( '操作成功', 0, $aLocation );
        }
        else
        {
            sysMessage( '操作失败', 1, $aLocation );
        }
    }



    /**
     * 禁用一个网站基本配置
     * URL = ./?controller=config&action=disable
     * @author SAUL
     */
    function actionDisable()
    {
        $aLocation[0] = array( "text"=>"基本配置列表", "href"=>url("config","list"));
        $iConfigId    = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iConfigId==0 )
        {
            redirect( url('config','list') );
        }
        /* @var $oConfig model_config */
        $oConfig = A::singleton("model_config");
        if( $oConfig->setStatus( $iConfigId, 1 ) )
        {
            sysMessage( '操作成功', 0, $aLocation );
        }
        else
        {
            sysMessage( '操作失败', 1, $aLocation );
        }
    }



    /**
     * 修改一个网站基本配置(技术)
     * URL = ./index.php?controller=config&action=set
     * @author SAUL
     */
    function actionSet()
    {
        $iConfigId   = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iConfigId==0 )
        {
            redirect( url( "config", "list" ) );
        }
        /* @var $oConfig model_config*/
        $oConfig     = A::singleton("model_config");
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
     * URL = ./index.php?controller=config&action=updateset
     * @author SAUL
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
            "parentid"          =>  $iParent,
            "configkey"         =>  $sKey,
            "configvalue"       =>  $sValue,
            "defaultvalue"      =>  $sDefault,
            "forminputtype"     =>  $iInput,
            "configvaluetype"   =>  $iType,
            "description"       =>  $sDescr,
            "isdisabled"        =>  $iIsDisable,
            "title"             =>  $sTitle
        );
        /* @var $oConfig model_config */
        $oConfig = A::singleton("model_config");
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
     * 检测 游戏平台与代理平台 可用模板, 显示每个模板文件数量
     * @author Tom
     */
    function actionSkinlist()
    {
        if( empty($_GET['channel']) || !in_array($_GET['channel'], array(1,2)) )
        {
            $iChannelId = 1;
        }
        else
        {
            $iChannelId = intval($_GET['channel']);
        }
        $sSkinsBasePath = '';
        if( $iChannelId == 1 )
        { // 低频游戏
            $sSkinsBasePath = PDIR_LOW_USER;
            $aSearch['sel1'] = ' SELECTED ';
        }
        else 
        { // 低频代理
            $sSkinsBasePath = PDIR_LOW_PROXY;
            $aSearch['sel2'] = ' SELECTED ';
        }
        $aSearch['cid'] = $iChannelId;
        // 设置模板文件夹基本路径
        /* @var $oUserSkins model_userskins */
        $oUserSkins = A::singleton("model_userskins");
        $oUserSkins->setBasePath( $sSkinsBasePath. DS .'_app'.DS.'views'.DS );
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
            $aTmp['skinname']    = $v; // 临时数组
            $aTmp['counts']      = count($aTmp['filelist']);
            $aTmp['dirlasttime'] = date("Y-m-d H:i:s", filemtime($sViewSkinBaseDir.$v)); 
            array_push( $aSkinArray, $aTmp );
            unset($aTmp,$aTmpDir);
        }
        $oUserSkins->assignSysInfo();
        unset($oUserSkins);
        $GLOBALS['oView']->assign("ur_here",   "模板管理");
        $GLOBALS['oView']->assign("aSkinArr",   $aSkinArray);
        $GLOBALS['oView']->assign("s",   $aSearch);
        $GLOBALS['oView']->assign('action',    'updateset');
        $GLOBALS['oView']->display("config_skinlist.html");
        EXIT;
    }



    /**
     * 模板管理详情
     * URL = ./index.php?controller=config&action=skininfo&n=default
     * 并且根据默认模板,检查HTML完整性
     * @author Tom
     */
    function actionSkininfo()
    {
        $aLocation[0]  = array( "text" => "模板管理", "href" => url( 'config', 'skinlist' ) );
        if( empty($_GET['cid']) || !in_array($_GET['cid'], array(1,2)) )
        {
            $iChannelId = 1;
        }
        else
        {
            $iChannelId = intval($_GET['cid']);
        }

        $sSkinsBasePath = '';
        if( $iChannelId == 1 )
        { // 低频游戏
            $sSkinsBasePath = PDIR_LOW_USER;
            $aSearch['sel1'] = ' SELECTED ';
            $aSearch['cname'] = ' 低频游戏 ';
        }
        else 
        { // 低频代理
            $sSkinsBasePath = PDIR_LOW_PROXY;
            $aSearch['cname'] = ' 低频代理 ';
        }
        $aSearch['cid'] = $iChannelId;
        /* @var $oUserSkins model_userskins*/
        $oUserSkins = A::singleton("model_userskins");
        $oUserSkins->setBasePath( $sSkinsBasePath. DS .'_app'.DS.'views'.DS );

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
        $GLOBALS['oView']->assign( 'actionlink', $aLocation[0]);
        $GLOBALS['oView']->assign("ur_here",     "模板详情 [".$aSearch['cname']."] - [ $sSkinName ]");
        $GLOBALS['oView']->assign("aDefaultSkinFileList",  $aDefaultSkinFileList);
        $GLOBALS['oView']->assign("sSkinName",   $sSkinName);
        $GLOBALS['oView']->assign("cName",       $aSearch['cname']);
        $GLOBALS['oView']->assign('action',      'updateset');
        $GLOBALS['oView']->display("config_skininfo.html");
        EXIT;
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
                $sGamePath = PDIR_LOW_USER;
                $sGameCssBasePath = $sGamePath.'/css';
                $aTmpDirPathList = scandir( $sGameCssBasePath );
                $aDirPathList = array();//模板目录
                foreach ( $aTmpDirPathList as $sPath )
                {
                    if( strpos($sPath, '.') === FALSE )
                    {
                        $aDirPathList[] = $sGameCssBasePath . '/' . $sPath;
}
                }
                unset($aTmpDirPathList);
                // 低频代理平台
                $sProxyPath = PDIR_LOW_PROXY;
                $sProxyCssBasePath = $sProxyPath.'/css';
                $aTmpDirPathList = scandir( $sProxyCssBasePath );
                foreach ( $aTmpDirPathList as $sPath )
                {
                    if( strpos($sPath, '.') === FALSE )
                    {
                        $aDirPathList[] = $sProxyCssBasePath . '/' . $sPath;
                    }
                }
                unset($aTmpDirPathList);

                $aCssFileList = array();//文件位置
                foreach ( $aDirPathList as $sCssPathList )
                {
                    $aTmpCssFileList = scandir($sCssPathList) ;
                    foreach ($aTmpCssFileList as $sTmpFile)
                    {
                        $sFile = $sCssPathList . "/" .$sTmpFile;
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
}
?>