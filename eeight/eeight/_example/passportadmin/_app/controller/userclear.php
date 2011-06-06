<?php
/**
 * 文件:/_app/controller/userclear.php
 * 功能: 控制器 - 用户清理---记录不活跃用户清理过程中进行的小额转账以及转入上级的金额账变报表
 * 
 * @author mark
 * @version 1.0.0
 * @package passportadmin 
 *
 */
class controller_userclear extends basecontroller
{
    /**
     * 用户清理报表
     * URL = ./index.php?controller=userclear&action=report
     * @author Mark
     */
    public function actionReport()
    {
        $sDay = isset($_GET['sdate']) ? $_GET['sdate'] : date("Y-m-d",time());
        $aHtmlValue['sdate'] = $sDay;
        
        //获取用户清理报表统计数据
        /* @var $oClearUser model_clearuser */
        $oClearUser = A::singleton('model_clearuser');
        $sCondition = " AND o.`times` LIKE '".$sDay."%'";
        $aReportData= $oClearUser->getReportData( $sCondition );
        
        //获取频道列表
        /* @var $oChannel model_channels */
        $oChannel = A::singleton('model_channels');
        $aChannel = $oChannel->channelGetAll('*'," `pid`='0' AND `isdisabled`='0' ");
        
        //获取总代列表
         /* @var $oUser model_user */
        $oUser = A::singleton('model_user');
        $sUserWhere = " AND a.`istester` = '0' ";
        $aTopProxy = $oUser->getChildListID(0, FALSE, $sUserWhere);
        
        //整理报表数据
        /*
        数据格式:对每个总代进如下整理
        $aResult[200124] = array(
            'userid' => '200124',
            'username' => 'zdkkfngu',
            'transfer' => array('1'=>100,'4'=>300),
            'smallcashin' => 100,
            'smallcashout' => 100
         );
        */
        $aResult = array();
        $aTotal = array();
        foreach ($aChannel as $aChannelDetail)
        {
            $aTotal['transfer'][$aChannelDetail['id']] = 0.00;
        }
        $aTotal['smallcashin'] = 0.00;
        $aTotal['smallcashout'] = 0.00;
        foreach ( $aTopProxy as $aProxy )
        {
            $iTmpUserid = $aProxy['userid'];
            $aResult[$iTmpUserid] = $aProxy;
            $aResult[$iTmpUserid]['transfer']     = isset($aReportData[$iTmpUserid]['tranfer']) ? $aReportData[$iTmpUserid]['tranfer'] : 0.00;
            $aResult[$iTmpUserid]['smallcashin']  = isset($aReportData[$iTmpUserid]['smallcashin']) ? $aReportData[$iTmpUserid]['smallcashin'] : 0.00;
            $aResult[$iTmpUserid]['smallcashout'] = isset($aReportData[$iTmpUserid]['smallcashout']) ? $aReportData[$iTmpUserid]['smallcashout'] : 0.00;
            $aTotal['smallcashin']  += $aResult[$iTmpUserid]['smallcashin'];
            $aTotal['smallcashout'] += $aResult[$iTmpUserid]['smallcashout'];
            foreach ($aChannel as $aChannelDetail)
            {
                $aTotal['transfer'][$aChannelDetail['id']] += isset($aReportData[$iTmpUserid]['tranfer'][$aChannelDetail['id']]) ? $aReportData[$iTmpUserid]['tranfer'][$aChannelDetail['id']] : 0.00;
            }
        }
        unset($iTmpUserid);
        $GLOBALS['oView']->assign( 'aChannelList', $aChannel );
        $GLOBALS['oView']->assign( 'iChannelCount', count($aChannel) );
        $GLOBALS['oView']->assign( 'aResult', $aResult );
        $GLOBALS['oView']->assign( 'total', $aTotal );
        $GLOBALS['oView']->assign( 's', $aHtmlValue );
        $GLOBALS['oView']->assign( 'ur_here', '用户清理报表' );
        $oClearUser->assignSysInfo();
        $GLOBALS['oView']->display("userclear_report.html");
        EXIT;
    }
}
?>