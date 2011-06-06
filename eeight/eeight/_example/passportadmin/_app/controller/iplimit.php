<?php
/**
 * 文件 : /_app/controller/iplimit.php
 * 功能 : 控制器 - ip限制管理
 * 
 *     - actionList()               信任IP列表
 *     - actionAdd()                增加信任IP
 *     - actionSave()               保存信任IP
 *     - actionEdit()               编辑信任IP
 *     - actionUpdate()             更新信任IP
 *     - actionDelete()             删除信任IP
 * 
 * @author	  mark
 * @version   1.0
 * @package   passportadmin
 */
class controller_iplimit extends basecontroller 
{
    /**
     * 查看信任IP列表
     * URL = ./?controller=iplimit&action=list
	 * @author mark
     */
    public function actionList()
    {
        $pn = isset($_GET['pn']) ? intval($_GET['pn']) : 20;
        $p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;
        /* @var $oUser model_iplimit */
        $oIPLimit = A::singleton("model_iplimit");
        $aIPList = $oIPLimit->getIPlist( $pn, $p );
        $oPager = new pages( $aIPList['affects'], $pn, 10 );
	    $GLOBALS["oView"]->assign( "ur_here", "信任IP列表" );
	    $GLOBALS['oView']->assign( "aIPLimit", $aIPList['results'] );
	    $GLOBALS['oView']->assign( "pageinfo", $oPager->show() );
	    $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("iplimit","add"), 'text'=>'增加信任IP' ) );
        $oIPLimit->assignSysInfo();
		$GLOBALS['oView']->display("iplimit_list.html");
		EXIT;
    }
    
    
    /**
     * 增加信任IP
     * URL = ./?controller=iplimit&action=add
	 * @author mark
     */
    public function actionAdd()
    {
        /* @var $oUser model_iplimit */
        $oIPLimit = A::singleton("model_iplimit");
        $GLOBALS["oView"]->assign( "ur_here","增加信任IP" );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("iplimit","list"), 'text'=>'信任IP列表' ) );
        $GLOBALS['oView']->assign( "form_action", 'save' );
        $oIPLimit->assignSysInfo();
        $GLOBALS['oView']->display("iplimit_info.html");
		EXIT;
    }
    
    
    /**
     * 保存信任IP
     * URL = ./?controller=iplimit&action=save
	 * @author mark
     */
    public function actionSave()
    {
        $aLocation  = array(
                   0 => array( 'text'=>'返回: 继续添加','href'=>url('iplimit','add') ),
                   1 => array( 'text'=>'返回: 信任IP列表','href'=>url('iplimit','list') )
        );
        $sIPLimit = isset($_POST['iplimit']) ? daddslashes($_POST['iplimit']) : '0.0.0.0';
        $sNotice  = isset($_POST['notice']) ? daddslashes($_POST['notice']) : '';
        if(preg_match('/^((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){0,3}(2[0-4]\d|25[0-5]|[01]?\d\d?)$/',$sIPLimit) == 0)
        {
            sysMessage( 'IP不符合规则', 1 );
        }
		/* @var $oUser model_iplimit */
        $oIPLimit = A::singleton("model_iplimit");
        $aData = array(
                'limitip' => $sIPLimit,
                'notice'  => $sNotice
        );
        $mFlag = $oIPLimit->insert( $aData );
        if( $mFlag == -1 )
        {
            sysMessage( '数据不完整', 1, $aLocation );
        }
        elseif ( $mFlag == -2 )
        {
            sysMessage( '已有相同的IP存在', 1, $aLocation );
        }
        elseif ( $mFlag === FALSE )
        {
            sysMessage( '添加失败', 1, $aLocation );
        }
        else 
        {
            sysMessage( '添加成功', 0, $aLocation );
        }
    }
    
    /**
     * 编辑信任IP
     * URL = ./?controller=iplimit&action=edit
	 * @author mark
     */
    public function actionEdit()
    {
        $iIPId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        /* @var $oUser model_iplimit */
        $oIPLimit = A::singleton("model_iplimit");
        $aIP = $oIPLimit->getIPlist( 0, 0, "`id` = '" . $iIPId ."'" );
        $GLOBALS["oView"]->assign( "ur_here","修改信任IP" );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("iplimit","list"), 'text'=>'信任IP列表' ) );
        $GLOBALS['oView']->assign( "form_action", 'update' );
        $GLOBALS['oView']->assign( "aIP", $aIP );
        $oIPLimit->assignSysInfo();
        $GLOBALS['oView']->display("iplimit_info.html");
		EXIT;
    }
    
    /**
     * 更新信任IP
     * URL = ./?controller=iplimit&action=update
	 * @author mark
     */
    public function actionUpdate()
    {
        $sIPLimit = isset($_POST['iplimit']) ? daddslashes($_POST['iplimit']) : '0.0.0.0';
        $sNotice  = isset($_POST['notice']) ? daddslashes($_POST['notice']) : '';
        $iIPId    = isset($_POST['ipid']) ? intval($_POST['ipid']) : 0;
        $aLocation  = array(
                   0 => array( 'text'=>'返回: 重新更新','href'=>url('iplimit', 'edit', array('id'=>$iIPId)) ),
                   1 => array( 'text'=>'返回: 信任IP列表','href'=>url('iplimit', 'list') )
        );
        if(preg_match('/^((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){0,3}(2[0-4]\d|25[0-5]|[01]?\d\d?)$/',$sIPLimit) == 0)
        {
            sysMessage( 'IP不符合规则', 1 );
        }
		/* @var $oUser model_iplimit */
        $oIPLimit = A::singleton("model_iplimit");
        $aData = array(
                'limitip' => $sIPLimit,
                'notice'  => $sNotice
        );
        $mFlag = $oIPLimit->update( $aData, "`id` = '" . $iIPId ."'" );
        if( $mFlag == -1 )
        {
            sysMessage( '数据不完整', 1, $aLocation );
        }
        elseif ( $mFlag == 0 )
        {
            sysMessage( '没有数据更新', 1, $aLocation );
        }
        elseif ( $mFlag === FALSE )
        {
            sysMessage( '更新失败', 1, $aLocation );
        }
        else 
        {
            sysMessage( '更新成功', 0, $aLocation );
        }
    }
    
    
    /**
     * 删除信任IP
     * URL = ./?controller=iplimit&action=delete
	 * @author mark
     */
    public function actionDelete()
    {
        $aLocation = array( 0 => array('text'=>'返回: 信任IP列表','href'=>url('iplimit','list')) );
        $iIPId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if( $iIPId == 0 )
        {
            $aIPId = isset($_POST['checkboxes']) && is_array($_POST['checkboxes']) ? $_POST['checkboxes'] : array();
            $sIPId = implode(",",$aIPId);//批量删除
        }
        else 
        {
            $sIPId = $iIPId;//单个删除
        }
		/* @var $oUser model_iplimit */
        $oIPLimit = A::singleton("model_iplimit");
        $mFlag = $oIPLimit->delete( "`id` IN (" . $sIPId .")" );
        if( $mFlag == -1 )
        {
            sysMessage( '数据不完整', 1, $aLocation );
        }
        elseif ( $mFlag === FALSE || $mFlag < 1 )
        {
            sysMessage( '删除失败', 1, $aLocation );
        }
        else 
        {
            sysMessage( '删除成功', 0, $aLocation );
        }
    }
}
?>