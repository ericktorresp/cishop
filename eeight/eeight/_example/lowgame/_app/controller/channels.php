<?php
/**
 * 文件 : /_app/controller/channels.php
 * 功能 : 控制器 - 频道信息
 *  
 *  - actionList    频道全览   
 *  - actionUserSet 频道设定(查看)
 * 
 * @author    SAUL  090914
 * @version   1.2.0
 * @package   lowgame
 */

class controller_channels extends basecontroller 
{
    /**
     * 频道全览
     * URL: ./index.php?controller=channels&action=list
     * 查看用户的基本信息
     */
    function actionList()
    {
        $oUser     = new model_user();
        //用户等级
        $aUser     = $oUser->getUserExtentdInfo( intval($_SESSION["userid"]), 0 );
        //用户剩余资金
        $oUserFund = new model_userfund();
        $aUserFund = $oUserFund->getFundByUser( intval($_SESSION["userid"]), "*", SYS_CHANNELID, FALSE );
        //用户参与以及用户中奖
        $oOrders   = new model_orders();
        $aOrders   = $oOrders->getFundTotalByUserId( intval($_SESSION["userid"]) );
        $GLOBALS['oView']->assign( "user",             $aUser );
        $GLOBALS['oView']->assign( "availablebalance", $aUserFund["availablebalance"] );
        $GLOBALS['oView']->assign( "userfund",         $aOrders );
        $GLOBALS['oView']->assign( "ur_here",          "频道全览" );
        $oUser->assignSysInfo();
        $GLOBALS['oView']->display( "channels_list.html" );
        EXIT;
    }



    /**
     * 频道设定
     * URL: ./index.php?controller=channels&action=userset
     * 查看频道中各种玩法的奖金以及返点情况
     */
    function actionUserSet()
    {
        $oUserMethod    = new model_usermethodset();
        $aUserMethodSet = $oUserMethod->getUserMethodPrize( intval($_SESSION["userid"]),
                                                            "m.`methodid`,m.`lotteryid`,m.`nocount`,m.`methodname`,"
                                                            ."upl.`level`,upl.`prize`,ums.`userpoint`", "", FALSE );
        foreach( $aUserMethodSet as & $userMethodSet )
        { //反解析用户的玩法设置
            $userMethodSet["nocount"] = @unserialize( $userMethodSet["nocount"] );
        }
        $aUserMethodSets = array();
        foreach( $aUserMethodSet as $v )
        {
            $aUserMethodSets[$v["methodid"]]["name"]                          = $v["methodname"];
            $aUserMethodSets[$v["methodid"]]["userpoint"]                     = number_format($v["userpoint"]*100, 1);
            $aUserMethodSets[$v["methodid"]]["nocount"][$v["level"]]["name"]  = $v["nocount"][$v["level"]]["name"];
            $aUserMethodSets[$v["methodid"]]["nocount"][$v["level"]]["bonus"] = $v["prize"];
        }
        $oMethod  = new model_method();
        $aMethod  = $oMethod->methodGetList( "a.`methodid`,a.`lotteryid`,b.`cnname`", " a.`pid`='0'", "", 0 );
        $aLottery = array();
        $aResult  = array();
        foreach( $aMethod as $method )
        {
        	if( isset($aUserMethodSets[$method["methodid"]]) )
        	{
                $aLottery[$method["lotteryid"]]                    = $method["cnname"]; //出现的彩种
                $aUserMethodSets[$method["methodid"]]["lotteryid"] = $method["lotteryid"]; //玩法所属的彩种
        	}
        }
        $aTemp = array_keys($aLottery);
        $iLotteryId = intval($_GET['ltid']) > 0 ? intval($_GET['ltid']) : $aTemp[0];
        $aResult = array();
        foreach( $aUserMethodSets as $userMethodSets )
        {
            $aResult[$userMethodSets["lotteryid"]][] = $userMethodSets;
        }
        unset( $aUserMethodSets );
        $GLOBALS['oView']->assign( "alottery",   $aLottery );
        $GLOBALS['oView']->assign( "UserMethod", $aResult );
        $GLOBALS['oView']->assign( "LimitBonus", getConfigValue('limitbonus','100000') );
        $GLOBALS['oView']->assign( "ur_here",    "频道设定" );
        $GLOBALS['oView']->assign('lotid',$iLotteryId);
        $oMethod->assignSysInfo();
        $GLOBALS['oView']->display( "channels_userset.html" );
        EXIT;
    }
}
?>