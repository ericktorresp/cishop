<?php
/**
 * 文件 : /_app/controller/channels.php
 * 功能 : 控制器 - 组别管理
 * 
 * - actionUserSet()        频道设定
 * - _actionUserSet()       频道设定（代理）
 * - actionReview()         频道全览
 * - _actionChannelSet()    频道设定（用户）
 *    
 * 
 * @author      Floyd
 * @version     1.0.0
 * @package     highgame
 */

class controller_channels extends basecontroller 
{
    /**
     * 频道设定
     * URL: ./index.php?controller=channels&action=userset
     */
    function actionUserSet()
    {
        $iUserType = intval( $_SESSION['usertype'] );
        if( $iUserType == 0 )
        {
            //普通用户
            //查询 usermethodsets 表
            $this->_actionChannelSet();
        }
        else 
        {
            //代理用户
            //查询所有
            $this->_actionUserSet();
        }
        EXIT;
    }
    
    
    
    /**
     * 频道设定（代理）
     */
    private function _actionUserSet()
    {
        $iUserId = intval( $_SESSION["userid"] );
        if( $iUserId==0 )
        {
            sysMsg( '操作失败:数据错误.', 2 );
        }
        $GLOBALS['oView']->assign( "ur_here", "频道设定" );
        $oUserFund = A::singleton("model_userfund");
		if( intval($_SESSION["usertype"])==2 )
		{//管理员
			$oUser = A::singleton('model_user');
			$iUserId = $oUser->getTopProxyId( $iUserId, FALSE ); //获取总代
		}
        $aUserFund = $oUserFund->getFundByUser( $iUserId,'*',SYS_CHANNELID,FALSE );
        if( empty($aUserFund) )
        {
            sysMsg( '操作失败:当前用户没有权限.', 2 );
        }
        $GLOBALS['oView']->assign( 'userinfo',   $aUserFund );
        $oMethod = A::singleton("model_method");
        $aMethod = $oMethod->methodGetList("a.`methodid`,a.`lotteryid`,a.`methodname`,a.`level`,"
                ."a.`nocount`,b.`cnname`", "a.`pid`='0'", "", 0);
        $aMethods = array();
        $aLottery = array();
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
        $aMethod = array();
        $aPg = array();
        $aPL = array();
        $aTempMethodIds = array();
        if( $aUserFund["parentid"]==0 )
        {//总代
            $oUserPg = A::singleton("model_userprizegroup");
            $aUserPg = $oUserPg->getUserPrizeGroupList( $iUserId, true, "upg.*,upl.*");
            foreach( $aUserPg as $k )
            {
                $aPg[$k["lotteryid"]][$k["userpgid"]] = $k["title"]; 
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
            $oUserMethodSet = A::singleton("model_usermethodset");
            $aUserMethodSet = $oUserMethodSet->getUserMethodPoint( $iUserId,
                " m.lotteryid,upl.`level`,upl.`prize`,upl.`userpgid`,ums.*,UPG.title", "" );
            foreach( $aUserMethodSet as $k )
            {
                $aPg[$k["lotteryid"]][$k["userpgid"]] = $k["title"]; 
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
            if( !array_key_exists( $lid, $aPg ) )
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
        foreach($aMethods as $i=>$v)
        {
            $tmpAry = array();
            foreach ($v as $vv)
            {
                $tmpAry[] = $vv;
            }
            $aMethod[$i] = json_encode($tmpAry);
        }
        $GLOBALS['oView']->assign( "aPg",   $aPg );
        $GLOBALS["oView"]->assign( "aPL",   json_encode($aPL) );
        $bigMoney = getConfigValue( 'limitbonus', 100000 );
        $GLOBALS['oView']->assign('bigMoney',$bigMoney);
        $GLOBALS["oView"]->assign( "aLottery",  $aLottery );//彩种信息
        $GLOBALS["oView"]->assign( "aMethod",   $aMethod );
        $oUserFund->assignSysInfo();
        $GLOBALS['oView']->display("channels_userinfo.html");
        EXIT;
    }
    
    
    
    /**
     * 频道全览
     * URL: ./index.php?controller=channels&action=review
     * 查看用户的基本信息
     */
    function actionReview()
    {
		$iUserId = intval($_SESSION['userid']);
        $oUser     = new model_user();
		if( intval($_SESSION["usertype"])==2 )
		{//管理员
			$iUserId = $oUser->getTopProxyId( $iUserId, FALSE ); //获取总代
		}
		//用户等级
        $aUser     = $oUser->getUserExtentdInfo( $iUserId, 0 );
        //用户剩余资金
        $oUserFund = new model_userfund();
        $aUserFund = $oUserFund->getFundByUser( $iUserId, "*", SYS_CHANNELID, FALSE );
        //用户参与以及用户中奖
        $oOrders   = new model_orders();
        $aOrders   = $oOrders->getFundTotalByUserId( $iUserId );
        $GLOBALS['oView']->assign( "user",             $aUser );
        $GLOBALS['oView']->assign( "availablebalance", $aUserFund["availablebalance"] );
        $GLOBALS['oView']->assign( "userfund",         $aOrders );
        $GLOBALS['oView']->assign( "ur_here",          "频道全览" );
        $oUser->assignSysInfo();
        $GLOBALS['oView']->display( "channels_review.html" );
        EXIT;
    }
    
    
    
    /**
     * 频道设定（用户）
     * 查看频道中各种玩法的奖金以及返点情况
     */
    private function _actionChannelSet()
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
        $oMethod->assignSysInfo();
        $GLOBALS['oView']->display( "channels_channelset.html" );
        EXIT;
    }
}
?>