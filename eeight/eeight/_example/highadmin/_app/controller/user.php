<?php
/**
 * 文件 : /_app/controller/user.php
 * 功能 : 控制器 - 用户管理
 * 
 * 功能:
 * + actionUserlist     用户列表
 * + actionIndex        用户列表集成以及用户中间面板
 * + actionSetprize     分配奖金组
 * + actionUnlock       资金解锁
 * + actionInfo         用户详情
 * + actionTeam         用户团队
 * 
 * @author      mark
 * @version     1.2.0 
 * @package     highadmin
 */

class controller_user extends basecontroller
{
    /**
     * 用户列表
     * @author mark
     * URL: ./index.php?controller=user&action=userlist
     */
    function actionUserlist()
    {
        $sWhere  = "";
        if( isset($_GET["username"]) && $_GET["username"] != '' )
        { //用户名称
            $sUserName = daddslashes( trim($_GET["username"]) );
            if ( strpos($sUserName,"*") >= 0 )
            {
                $sWhere .= " AND ut.`username` LIKE '".str_replace("*","%",$sUserName)."'";
            }
            else
            {
                $sWhere .= " AND ut.`username` = '".$sUserName."'";
            }
            $GLOBALS['oView']->assign( "username", stripslashes_deep($sUserName) );
        }
        //用户组
        $iUserTeam = isset($_GET["team"]) && is_numeric($_GET["team"]) ? intval($_GET["team"]) : 0;
        $GLOBALS['oView']->assign( "userteam", $iUserTeam );
        if( $iUserTeam > 0 )
        {
            if( $iUserTeam == 1 )
            {
                $sWhere .= "AND ut.`parentid`='0'";
            }
            elseif( $iUserTeam == 2 )
            {
                //一代
                $sWhere .= "AND ut.`usertype`='1' and ut.`parenttree`=ut.`lvtopid`";
            }
            elseif( $iUserTeam == 3 )
            { //普代理
                $sWhere .= "AND ut.`usertype`='1' and ut.`userid` != ut.`lvproxyid` and ut.`parentid`>0";
            }
            else
            {
                $sWhere .= "AND ut.`usertype`='0'";
            }
        }
        $fMinMoney = isset($_GET["minmoney"]) && is_numeric($_GET["minmoney"]) ? doubleval($_GET["minmoney"]) : 0.00;//最小金额
        if( $fMinMoney > 0.00 )
        {
            $sWhere .= " AND uf.`availablebalance`>=" . $fMinMoney;
            $GLOBALS['oView']->assign( "minmoney", $fMinMoney );
        }
        $fMaxMoney = isset($_GET["maxmoney"]) && is_numeric($_GET["maxmoney"]) ? doubleval($_GET["maxmoney"]) : 0.00;//最大金额
        if( $fMaxMoney > 0.00 )
        {
            $sWhere .= " AND uf.`availablebalance`<" . $fMaxMoney;
            $GLOBALS['oView']->assign( "maxmoney", $fMaxMoney );
        }
        //排序
        $sOrder   = isset($_GET["order"]) ? $_GET["order"] : "";
        $sOrderBy = '';
        $sDesc    =  isset($_GET["desc"]) ? "DESC" : "ASC";
        switch( $sOrder )
        {
            case "ID":
                $sOrderBy = "ut.`userid` ".$sDesc;
                break;
            case "name":
                $sOrderBy = "ut.`username` ".$sDesc;
                break;
            case "money":
                $sOrderBy = "uf.`availablebalance` ".$sDesc;
                break;
            default:
                $sOrderBy = "ut.`username` ".$sDesc;
                break;
        }
        if( $sWhere == "" )
        {
            $iUserId = isset($_GET["id"]) && is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
            $bBool = FALSE;
        }
        else
        {
            $iUserId = 0;
            $bBool   = TRUE;
        }
        $GLOBALS['oView']->assign( "order", $sOrder );
        $GLOBALS['oView']->assign( "desc",  $sDesc );
        /* @var $oUser model_user */
        $oUser = A::singleton("model_user");
        $sFileds  = "ut.`userid`,ut.`usertype`,ut.`username`,ut.`parentid`,uf.`availablebalance`,"
        ."uf.`channelbalance`,uf.`cashbalance`,uf.`holdbalance`";
        $iPage   = isset($_GET["p"]) && is_numeric($_GET["p"]) ? intval($_GET["p"]) :1;
        $aUsers  = $oUser->getChildrenList($iUserId, $sFileds, $sWhere, $sOrderBy, 20, $iPage, $bBool, TRUE, 0);
        $GLOBALS['oView']->assign("users",$aUsers["results"]);
        if(($aUsers['affects'] == 0) && (!empty($aUsers['self'])))
        { //用户个数修正..
            $aUsers['affects'] = 1;
        }
        $oPage = new pages( $aUsers['affects'], 20 );
        $GLOBALS['oView']->assign("pageinfo",$oPage->show(1));
        if(isset($aUsers["self"]))
        {
            $GLOBALS['oView']->assign("self",$aUsers["self"]);
            if(isset($aUsers['self']['bannners']))
            {
                $GLOBALS['oView']->assign("banner",$aUsers['self']['bannners']);
            }
        }
        $oUser->assignSysInfo();
        $GLOBALS['oView']->assign("ur_here","用户列表");
        $GLOBALS['oView']->assign("actionlink",array('text'=>'用户列表','href'=>url('user','userlist')));
        $GLOBALS['oView']->display("user_userlist.html");
        EXIT;
    }



    /**
     * 用户列表集成用户树以及用户面板中间部分
     * @author mark
     * URL:./index.php?controller=user&action=index
     */
    function actionIndex()
    {
        $sWhatDo = isset($_GET["do"]) && !empty($_GET["do"]) ? daddslashes($_GET["do"]) : "index";
        if( $sWhatDo == "index" )
        {
            $GLOBALS['oView']->display("user_index.html");
            EXIT;
        }
        elseif( $sWhatDo == "drag" )
        {
            $GLOBALS["oView"]->display("default_drag.html");
            EXIT;
        }
        elseif( $sWhatDo == "menu" )
        {
            $iUserId = isset($_GET["id"]) && is_numeric($_GET["id"]) ? intval($_GET["id"]) : -1;
            if($iUserId == -1)
            { //面板
                $GLOBALS['oView']->display("user_menu.html");
                EXIT;
            }
            else
            { //返回JSON
                $oUser = new model_user();
                $aUser = $oUser->getChildListID( $iUserId, " ORDER BY a.`username`", TRUE );
                echo json_encode( $aUser );
                EXIT;
            }
        }
    }



    /**
     * 分配奖金组
     * @author mark
     * URL:./index.php?controller=user&action=setprize
     */
    function actionSetprize()
    {
        $aLocation[0] = array("text"=>'分配奖金组',"href"=>url('user','setprize'));
        /* @var $oUser model_usertree */
        $oUser = A::singleton("model_usertree");
        $aUser = $oUser->userAgentget(" 1 ORDER BY `username`");
        $GLOBALS['oView']->assign("aUser",$aUser);
        /* @var $oLottery model_lottery */
        $oLottery     = A::singleton("model_lottery");
        $aLottery     = $oLottery->getItems();
        /* @var $oPrizeGroup model_userprizegroup */
        $oPrizeGroup  = new model_userprizegroup();
        $aPrizegroup  = $oPrizeGroup->userpgGetList("`pgid`,`lotteryid`,`title`,`userid`,`userpgid`,`status`",
                          "1", "", 0 );
        $GLOBALS['oView']->assign( "pg_data", json_encode($aPrizegroup) );
        $GLOBALS['oView']->assign( "aLottery",    $aLottery );
        $GLOBALS['oView']->assign( "ur_here",     "分配奖金组" );
        $GLOBALS['oView']->assign( "actionlink",  $aLocation[0] );
        $oPrizeGroup->assignSysInfo();
        $GLOBALS['oView']->display("user_setprize.html");
        EXIT;
    }



    /**
     * 资金解锁
     * @author mark
     * URL: ./index.php?controller=user&action=unlock
     */
    function actionUnlock()
    {
        /* @var $oUserFund model_userfund */
        $oUserFund = A::singleton("model_userfund");
        if( isset($_POST["unlock"]) && $_POST["unlock"] )
        {
            $aLocation[0] = array('text'=>'资金解锁','href'=>url('user','unlock'));
            $aEntrys      = isset($_POST["entry"]) ? $_POST["entry"] : array();
            $iFlag        = $oUserFund->fundUnlock( $aEntrys, 300 );
            if( $iFlag === -1 )
            {
                sysMessage( '没有提交数据', 1, $aLocation );
            }
            elseif( $iFlag === FALSE )
            {
                sysMessage( '解锁部分失败', 1, $aLocation );
            }
            else 
            {
                sysMessage( '操作成功', 0, $aLocation );
            }
        }
        else
        { //显示页面
            $aUsers = $oUserFund->fundUnlockList();
            if( count($aUsers) > 0 )
            {
                $GLOBALS['oView']->assign( "users", $aUsers );
            }
            $GLOBALS['oView']->assign( "ur_here",   "资金解锁" );
            $oUserFund->assignSysInfo();
            $GLOBALS['oView']->display("user_unlock.html");
            EXIT;
        }
    }



    /**
     * 用户详情
     * @author mark
     * URL:./index.php?controller=user&action=info
     */
    function actionInfo()
    {
        $iUserId = isset($_GET["id"]) && is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iUserId == 0 )
        {
            sysMessage('操作失败:数据错误.',1 );
        }
        $aLocation[0] = array("text"=>"用户列表","href"=>url('user','userlist',array('id'=>$iUserId)));
        $GLOBALS['oView']->assign( "ur_here",    "用户详情" );
        $GLOBALS['oView']->assign( "actionlink", $aLocation[0] );
        /* @var $oUserFund model_userfund */
        $oUserFund = A::singleton("model_userfund");
        $aUserFund = $oUserFund->getFundByUser($iUserId,'*', SYS_CHANNELID, FALSE);
        if( empty($aUserFund) )
        {
            sysMessage('操作失败:用户没有激活.', 1, $aLocation );
        }
        $GLOBALS['oView']->assign( 'userinfo',   $aUserFund );
        /* @var $oMethod model_method */
        $oMethod = A::singleton("model_method");
        $aMethod = $oMethod->methodGetList( "a.`methodid`,a.`lotteryid`,a.`methodname`,a.`level`,"
                  ."a.`nocount`,b.`cnname`", "a.`pid`='0'",'',0);
        foreach( $aMethod as &$method )
        {
            $aNocount = @unserialize($method["nocount"]);
            for( $j=1; $j<=$method["level"]; $j++ )
            {
                $method["name"][$j] = $aNocount[$j]["name"];
            }
            unset($method["nocount"]);
            $aLottery[$method["lotteryid"]] = $method["cnname"];
            $aMethods[$method["lotteryid"]][] = $method;
        }
        unset( $aMethod );
        foreach($aMethods as $i=>$v )
        {
            $aMethod[$i] = json_encode($v);
        }
        $GLOBALS["oView"]->assign( "aLottery", $aLottery );//彩种信息
        $GLOBALS["oView"]->assign( "aMethod",  $aMethod );
        if( $aUserFund["parentid"]==0 )
        {//总代
            $oUserPg = new model_userprizegroup();
            $aUserPg = $oUserPg->getUserPrizeGroupList($iUserId,true,'upg.*,upl.*');
            $aPg = array();
            $aPL = array();
            foreach ($aUserPg as $i=>$k)
            {
                $aPg[$k["lotteryid"]][$k["userpgid"]] = $k["title"]; 
                $aPL[$k["userpgid"]][$k["methodid"]][$k["level"]] = array(
                    "userpoint"=>number_format($k["userpoint"]*100, 1),
                    "bonus"=>number_format($k["prize"], 2, ".", ","),
                    "status"=>$k["isclose"]
                );
            }
            $GLOBALS['oView']->assign("aPg",$aPg);
            $GLOBALS["oView"]->assign("aPL",json_encode($aPL));
        }
        else
        { //非总代数据
            $oUserMethodSet = new model_usermethodset();
            $aUserMethodSet = $oUserMethodSet->getUserMethodPoint($iUserId,'m.lotteryid,upl.`level`,upl.`prize`,upl.`userpgid`,ums.*,UPG.title',"");
            foreach ($aUserMethodSet as $i=>$k)
            {
                $aPg[$k["lotteryid"]][$k["userpgid"]] = $k["title"]; 
                $aPL[$k["userpgid"]][$k["methodid"]][$k["level"]] = array(
                    "userpoint"=>number_format($k["userpoint"]*100, 1),
                    "bonus"=>number_format($k["prize"],2,".",","),
                    "status"=>$k["isclose"]
                );
            }
            $GLOBALS['oView']->assign("aPg",$aPg);
            $GLOBALS["oView"]->assign("aPL",json_encode($aPL));
        }
        $limitBonus = getConfigValue( 'limitbonus','100000' );
        $GLOBALS['oView']->assign( 'limitbonus',   $limitBonus );
        $oUserFund->assignSysInfo();
        $GLOBALS['oView']->display("user_info.html");
        EXIT;
    }



    /**
     * 用户团队
     * @author mark
     * URL:./index.php?controller=user&action=team
     */
    function actionTeam()
    {
        $iUserId = isset($_GET["id"]) && is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iUserId == 0 )
        {
            sysMessage('操作失败:数据错误.',1 );
        }
        $aLocation[0] = array("text"=>"用户列表","href"=>url('user','userlist',array('id'=>$iUserId)));
        $GLOBALS['oView']->assign("ur_here","用户团队");
        $GLOBALS['oView']->assign("actionlink",$aLocation[0]);
        /* @var $oUser model_user */
        $oUser     = A::singleton("model_user");
        $aUserInfo = $oUser->getUsersProfile('ut.`username`,ut.`nickname`', '',  "AND ut.`userid`='".$iUserId."'", FALSE );
        $fUserBank = $oUser->getTeamBank( $iUserId );
        $GLOBALS['oView']->assign('userinfo',   $aUserInfo);
        $GLOBALS['oView']->assign('userbank',   $fUserBank);
        $oUser->assignSysInfo();
        $GLOBALS['oView']->display("user_team.html");
        EXIT;
    }
}
?>