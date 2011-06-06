<?php
/**
 * 文件 : /_app/controller/channels.php
 * 功能 : 控制器 - 组别管理
 * 
 *    - actionUserset()     频道设定
 * 
 * @author      Saul
 * @version    1.2.0
 * @package    lowproxy
 */

class controller_channels extends basecontroller 
{
    /**
     * 频道设定
     * @author SAUL
     * URL: ./index.php?controller=channels&action=userset
     */
    function actionUserSet()
    {
    	// 2/12/2010 
    	$oUser = A::singleton("model_user");
		$iUserId = $_SESSION['usertype'] == 2  ?  $oUser->getTopProxyId( $_SESSION['userid'] )  :  $_SESSION['userid'];
//        $iUserId = intval( $_SESSION["userid"] );
        if( $iUserId==0 )
        {
            sysMsg( '操作失败:数据错误.', 1 );
        }
        $GLOBALS['oView']->assign( "ur_here", "频道设定" );
        /* @var $oUserFund model_userfund */
        $oUserFund = A::singleton("model_userfund");
        $aUserFund = $oUserFund->getFundByUser( $iUserId,'*',1,FALSE );
        if( empty($aUserFund) )
        {
            sysMsg( '操作失败:当前用户没有权限.', 1 );
        }
        $GLOBALS['oView']->assign( 'userinfo',   $aUserFund );
        /* @var $oMethod model_method */
        $oMethod = A::singleton("model_method");
        $aMethod = $oMethod->methodGetList("a.`methodid`,a.`lotteryid`,a.`methodname`,a.`level`,"
                ."a.`nocount`,b.`cnname`", "a.`pid`='0'", "", 0);
        foreach($aMethod as &$method )
        {
            $aNocount = @unserialize($method["nocount"]);
            for($j=1; $j<=$method["level"]; $j++)
            {
                $method["name"][$j] = $aNocount[$j]["name"];
            }
            unset($method["nocount"]);
            $aLottery[$method["lotteryid"]] = $method["cnname"];
            $aMethods[$method["lotteryid"]][] = $method;
        }
        unset($aMethod);
        foreach($aMethods as $i=>$v)
        {
            $aMethod[$i] = json_encode($v);
        }
        
        $aPg = array();
        $aTempPg = array();
        $aPL = array();
        $aTempMethodIds = array();
        $aTemp = array_keys($aLottery);
        $iLotteryId = intval($_GET['ltid']) > 0 ? intval($_GET['ltid']) : $aTemp[0];
        if( $aUserFund["parentid"]==0 )
        {//总代
            /* @var $oUserPg model_userprizegroup */
            $oUserPg = A::singleton("model_userprizegroup");
            $aUserPg = $oUserPg->getUserPrizeGroupList( $iUserId, true, "upg.*,upl.*");
            foreach( $aUserPg as $k )
            {
                if (intval($k["lotteryid"]) === $iLotteryId){
                    $aPg[$k["lotteryid"]][$k["userpgid"]] = $k["title"]; 
                }
                $aTempPg[$k["lotteryid"]][$k["userpgid"]] = $k["title"]; 
                $aPL[$k["userpgid"]][$k["methodid"]][$k["level"]] = array(
                    "userpoint"=>number_format( $k["userpoint"]*100, 1 ),
                    "bonus"=>number_format( $k["prize"], 2, ".", "," ),
                    "status"=>$k["isclose"]
                );
                if(!in_array($k['methodid'], $aTempMethodIds))
                {
                    $aTempMethodIds[] = $k['methodid'];
                }
            }
            $GLOBALS['oView']->assign( "show", 0 );
        }
        else
        { //非总代用户
            /* @var $oUserMethodSet model_usermethodset */
            $oUserMethodSet = A::singleton("model_usermethodset");
            $aUserMethodSet = $oUserMethodSet->getUserMethodPoint( $iUserId,
                " m.lotteryid,upl.`level`,upl.`prize`,upl.`userpgid`,ums.*,UPG.title", "" );
            foreach( $aUserMethodSet as $k )
            {
                if (intval($k["lotteryid"]) === $iLotteryId){
                    $aPg[$k["lotteryid"]][$k["userpgid"]] = $k["title"]; 
                }
                $aTempPg[$k["lotteryid"]][$k["userpgid"]] = $k["title"];
                $aPL[$k["userpgid"]][$k["methodid"]][$k["level"]] = array(
                    "userpoint"=>number_format( $k["userpoint"]*100, 1 ),
                    "bonus"=>number_format( $k["prize"], 2, ".", "," ),
                    "status"=>$k["isclose"]
                );
                if(!in_array($k['methodid'], $aTempMethodIds))
                {
                    $aTempMethodIds[] = $k['methodid'];
                }
            }
            $GLOBALS['oView']->assign( "show", 1 );
        }
        foreach( $aLottery as $lid=>$name )
        {
            if( !array_key_exists( $lid, $aTempPg ) )
            {
                unset( $aLottery[$lid], $aMethods[$lid] );
            }
            else 
            {
                foreach($aMethods[$lid] as $k=>$m)
                {
                    if(!in_array($m['methodid'], $aTempMethodIds))
                    {
                        unset($aMethods[$lid][$k]);
                    }
                }
            }
        }
        $GLOBALS['oView']->assign( "aPg",   $aPg );
        $GLOBALS["oView"]->assign( "aPL",   json_encode($aPL) );
        $bigMoney = getConfigValue( 'limitbonus', 100000 );
        $GLOBALS['oView']->assign('bigMoney',$bigMoney);
        $GLOBALS['oView']->assign('lotid',$iLotteryId);
        $GLOBALS["oView"]->assign( "aLottery",  $aLottery );//彩种信息
        $GLOBALS["oView"]->assign( "aMethod",   $aMethod );
        $oUserFund->assignSysInfo();
        $GLOBALS['oView']->display("user_userinfo.html");
        EXIT;
    }
}
?>