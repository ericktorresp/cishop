<?php
/**
 * 文件 : /_app/controller/marketmgr.php
 * 功能 : 控制器  - 市场管理
 *
 * 功能:
 *  + actionUsers           用户信息图表
 *  + actionFinance         财务信息图表
 *  + actionLogs            日志信息图表
 * 
 * @author    SAUL
 * @version   1.2.0
 * @package   lowadmin
 */

class controller_marketmgr extends basecontroller
{
    /* 用户信息图表
     * URL = ./index.php?controller=marketmgr&action=users
     * @author SAUL
     */
    function actionUsers()
    {
        if( isset($_GET['getdatas']) ) // 供FLASH调用的URL,生成数据XML
        {
            $sSdate = date( 'ymd', time()-2678400 ); // 默认显示31天数据, 86400*31
            $sEdate = date( 'ymd'); // end date
            if( isset($_GET['sdate']) && false!==strtotime('20'.$_GET['sdate']) )
            {
                $sSdate = date( 'ymd', strtotime('20'.$_GET['sdate']) );
            }
            if( isset($_GET['edate']) && false!==strtotime('20'.$_GET['edate']) )
            {
                $sEdate = date( 'ymd', strtotime('20'.$_GET['edate']) );
            }
            $iShowLayer1 = isset($_GET['show1']) ? intval($_GET['show1']) : 0; // 是否显示: 用户总数的蓝曲线
            $iShowLayer2 = isset($_GET['show2']) ? intval($_GET['show2']) : 0; // 是否显示: 活跃用户的红曲线
            $iShowLayer3 = isset($_GET['show3']) ? intval($_GET['show3']) : 0; // 是否显示: 活跃用户的红曲线
            if( $iShowLayer1==0 && $iShowLayer2==0 && $iShowLayer3==0 )
            { // 默认同时显示3根线
                $iShowLayer1 = 1;
                $iShowLayer2 = 1;
                $iShowLayer3 = 1;
            }
            /* @var $ochart model_charts */
//            $oChart         = A::singleton("model_charts");
            $oChart = new model_charts($GLOBALS['aSysDbServer']['report']);
            $iAllCount      = 1;
            $aLabelAll      = array();
            $aDataAllUser   = array();
            if( $iShowLayer1 ) // 显示用户总数
            {
                $aResultAllUser = $oChart->getChartsResult( " WHERE `chartid`=1 AND `days` >= '".$sSdate.
                                "' AND `days` <= '".$sEdate."' GROUP BY `days` ORDER BY `days`" );
                $iAllCount      = count( $aResultAllUser )+1; // 为了防止分母为0
                if( !empty($aResultAllUser) )
                {
                    foreach( $aResultAllUser AS $v )
                    {
                        $aLabelAll[]    = $v['days'];
                        $aDataAllUser[] = $v['TOMCOUNT'];
                    }
                }
                else
                {
                    $aDataAllUser[] = 0;
                }
            }
            $aLabelAll       = $iShowLayer1 ? $aLabelAll : array();
            $aDataActiveUser = array();
            if( $iShowLayer2 ) // 显示活跃用户数
            {
                $bAddtoLable = empty( $aLabelAll );
                $aResultActiveUser = $oChart->getChartsResult( " WHERE `chartid`=2 AND `days` >= '".$sSdate.
                                "' AND `days` <= '".$sEdate."' GROUP BY `days` ORDER BY `days`" );
                if( !$iShowLayer1 )
                {
                    $iAllCount     = count($aResultActiveUser)+1;
                }
                if( !empty($aResultActiveUser) )
                { 
                    foreach( $aResultActiveUser AS $v )
                    {
                        if( $bAddtoLable )
                        {
                            $aLabelAll[]    = $v['days'];
                        }
                        $aDataActiveUser[] = $v['TOMCOUNT'];
                    }
                }
                else
                {
                    $aDataActiveUser[] = 0;
                }
            }
            $aLabelAll       = $iShowLayer1 || $iShowLayer2 ? $aLabelAll : array();
            $aDataWinUser    = array();
            if( $iShowLayer3 ) // 盈利用户数
            {
                $bAddtoLable = empty($aLabelAll);
                $aResultWinUser = $oChart->getChartsResult( " WHERE `chartid`=3 AND `days` >= '".$sSdate.
                                "' AND `days` <= '".$sEdate."' GROUP BY `days` ORDER BY `days`" );
                if( !($iShowLayer1 || $iShowLayer2) )
                {
                    $iAllCount     = count($aResultWinUser)+1;
                }
                if( !empty($aResultWinUser) )
                { 
                    foreach( $aResultWinUser AS $v )
                    {
                        if( $bAddtoLable )
                        {
                            $aLabelAll[]    = $v['days'];
                        }
                        $aDataWinUser[] = $v['TOMCOUNT'];
                    }
                }
                else
                {
                    $aDataWinUser[] = 0;
                }
            }
            /* @var $oXml astats */
            $oXml = A::singleton("astats");
            $oXml->setChart( 'labelstep', ceil($iAllCount/10) );
            $oXml->addLabels( $aLabelAll );
            if( $iShowLayer1 )
            {
                $oXml->addData( $aDataAllUser, '低频用户总数' );
            }
            if( $iShowLayer2 )
            {
                $oXml->addData( $aDataActiveUser, '低频参与游戏用户数', 'FF0000' );
            }
            if( $iShowLayer3 )
            {
                $oXml->addData( $aDataWinUser, '低频盈利用户数', '00FF00' );
            }
            $oXml->display();
            EXIT;
        }
        $aHtml['sdate'] = date( 'ymd', time()-86400*31 ); // 默认显示最近一个月的数据
        $aHtml['edate'] = date( 'ymd');
        $aHtml['show1'] = isset($_GET['show1']); // 是否显示: 用户总数的蓝曲线
        $aHtml['show2'] = isset($_GET['show2']); // 是否显示: 活跃用户数的红曲线
        $aHtml['show3'] = isset($_GET['show3']); // 是否显示: 活跃用户数的红曲线
        
        if( $aHtml['show1']==0 && $aHtml['show2'] ==0 && $aHtml['show3']==0 )
        { // 如果全部为空, 则默认全选
            $aHtml['show1'] = 1;
            $aHtml['show2'] = 1;
            $aHtml['show3'] = 1;
        }
        if( isset($_GET['sdate']) && false!==strtotime('20'.$_GET['sdate']) )
        {
            $aHtml['sdate'] = date( 'ymd', strtotime('20'.$_GET['sdate']) );
        }
        if( isset($_GET['edate']) && false!==strtotime('20'.$_GET['edate']) )
        {
            $aHtml['edate'] = date( 'ymd', strtotime('20'.$_GET['edate']) );
        }
        $aHtml['dates'] = '%26sdate='.$aHtml['sdate'].'%26edate='.$aHtml['edate'];
        $GLOBALS['oView']->assign('s', $aHtml );
        $GLOBALS['oView']->assign( "ur_here", "用户信息图表" );
        $GLOBALS['oView']->display( "marketmgr_users.html" );
        EXIT;
    }



    /**
     * 财务信息图表
     * URL = ./index.php?controller=marketmgr&action=finance
     * @author SAUL
     */
    function actionFinance()
    {
        if( isset($_GET['getdatas']) ) // 供FLASH调用的URL,生成数据XML
        {
            $sSdate = date( 'ymd', time()-2678400 ); // 默认显示31天数据, 86400*31
            $sEdate = date( 'ymd'); // end date
            if( isset($_GET['sdate']) && false!==strtotime('20'.$_GET['sdate']) )
            {
                $sSdate = date( 'ymd', strtotime('20'.$_GET['sdate']) );
            }
            if( isset($_GET['edate']) && false!==strtotime('20'.$_GET['edate']) )
            {
                $sEdate = date( 'ymd', strtotime('20'.$_GET['edate']) );
            }
            $iShowLayer1 = isset($_GET['show1']) ? intval($_GET['show1']) : 0; // 是否显示: 充值总额的蓝曲线
            $iShowLayer2 = isset($_GET['show2']) ? intval($_GET['show2']) : 0; // 是否显示: 提现总额的红曲线
            $iShowLayer3 = isset($_GET['show3']) ? intval($_GET['show3']) : 0; // 是否显示: 充值总额的蓝曲线
            $iShowLayer4 = isset($_GET['show4']) ? intval($_GET['show4']) : 0; // 是否显示: 提现总额的红曲线
            if( $iShowLayer1==0 && $iShowLayer2==0 && $iShowLayer3==0 && $iShowLayer4==0 )
            { // 默认同时显示4根线
                $iShowLayer1 = 1;
                $iShowLayer2 = 1;
                $iShowLayer3 = 1;
                $iShowLayer4 = 1;
            }
            /* @var $oChart model_charts */
            //$oChart       = A::singleton("model_charts");
            $oChart = new model_charts($GLOBALS['aSysDbServer']['report']);
            $iAllCount    = 1;
            $aLabelAll    = array();
            $aGameMoneyIn = array();
            if( $iShowLayer1 ) // 获取游戏总额
            {
                $aResultGameMoneyIn = $oChart->getChartsResult( " WHERE `chartid`=10 AND `days` >= '".$sSdate.
                                "' AND `days` <= '".$sEdate."' GROUP BY `days` ORDER BY `days`" );
                $iAllCount = count($aResultGameMoneyIn)+1; // 为了防止分母为0
                if( !empty($aResultGameMoneyIn) )
                {
                    foreach( $aResultGameMoneyIn AS $v )
                    {
                        $aLabelAll[]    = $v['days'];
                        $aGameMoneyIn[]   = $v['TOMCOUNT'];
                    }
                }
                else
                {
                    $aGameMoneyIn[] = 0;
                }
            }
            $aLabelAll = $iShowLayer1 ? $aLabelAll : array();
            $aGameMoneyOut = array();
            if( $iShowLayer2 ) // 获取返奖总额
            {
                $bAddtoLable = empty($aLabelAll);
                $aResultGameMoneyOut = $oChart->getChartsResult( " WHERE `chartid`=11 AND `days` >= '".$sSdate.
                                "' AND `days` <= '".$sEdate."' GROUP BY `days` ORDER BY `days`" );
                if( !$iShowLayer1 )
                {
                    $iAllCount      = count($aResultGameMoneyOut)+1;
                }
                if( !empty($aResultGameMoneyOut) )
                { 
                    foreach( $aResultGameMoneyOut AS $v )
                    {
                        if( $bAddtoLable )
                        {
                            $aLabelAll[]    = $v['days'];
                        }
                        $aGameMoneyOut[] = $v['TOMCOUNT'];
                    }
                }
                else
                {
                    $aGameMoneyOut[]= 0;
                }
            }
            $aLabelAll = $iShowLayer1 || $iShowLayer2 ? $aLabelAll : array();
            $aMoneyOut = array();
            if( $iShowLayer3 ) // 获取银行转出
            {
                $bAddtoLable = empty($aLabelAll);
                $aResultMoneyOut = $oChart->getChartsResult( " WHERE `chartid`=12 AND `days` >= '".$sSdate.
                                "' AND `days` <= '".$sEdate."' GROUP BY `days` ORDER BY `days`" );
                if( !($iShowLayer1 ||$iShowLayer2) )
                {
                    $iAllCount      = count($aResultMoneyOut)+1;
                }
                if( !empty($aResultMoneyOut) )
                { 
                    foreach( $aResultMoneyOut AS $v )
                    {
                        if( $bAddtoLable )
                        {
                            $aLabelAll[]    = $v['days'];
                        }
                        $aMoneyOut[] = $v['TOMCOUNT'];
                    }
                }
                else
                {
                    $aMoneyOut[] = 0;
                }
            }
            $aLabelAll = ($iShowLayer1 || $iShowLayer2 || $iShowLayer3) ? $aLabelAll : array();
            $aMoneyIn = array();
            if( $iShowLayer4 ) // 获取低频转入
            {
                $bAddtoLable = empty($aLabelAll);
                $aResultMoneyIn = $oChart->getChartsResult( " WHERE `chartid`=13 AND `days` >= '".$sSdate.
                                "' AND `days` <= '".$sEdate."' GROUP BY `days` ORDER BY `days`" );
                if( !($iShowLayer1 || $iShowLayer2 || $iShowLayer3) )
                {
                    $iAllCount      = count($aResultMoneyIn)+1;
                }
                if( !empty($aResultMoneyIn) )
                { 
                    foreach( $aResultMoneyIn AS $v )
                    {
                        if( $bAddtoLable )
                        {
                            $aLabelAll[]    = $v['days'];
                        }
                        $aMoneyIn[] = $v['TOMCOUNT'];
                    }
                }
                else
                {
                    $aMoneyIn[] = 0;
                }
            }
            /* @var $oXml astats */
            $oXml = A::singleton("astats");
            $oXml->setChart( 'labelstep', ceil($iAllCount/10) );
            $oXml->addLabels( $aLabelAll );
            if( $iShowLayer1 )
            {
                $oXml->addData( $aGameMoneyIn, '游戏总额' );
            }
            if( $iShowLayer2 )
            {
                $oXml->addData( $aGameMoneyOut, '返奖总额', 'FF0000' );
            }
            if( $iShowLayer3 )
            {
                $oXml->addData( $aMoneyOut, '低频转出', '00FF00' );
            }
            if( $iShowLayer4 )
            {
                $oXml->addData( $aMoneyIn, '银行转入', '000000' );
            }
            $oXml->display();
            EXIT;
        }
        $aHtml['sdate'] = date( 'ymd', time()-86400*31 ); //  默认显示最近一个月的数据
        $aHtml['edate'] = date( 'ymd');
        $aHtml['show1'] = isset($_GET['show1']); // 是否显示: 用户总数的蓝曲线
        $aHtml['show2'] = isset($_GET['show2']); // 是否显示: 活跃用户数的红曲线
        $aHtml['show3'] = isset($_GET['show3']); // 是否显示: 用户总数的蓝曲线
        $aHtml['show4'] = isset($_GET['show4']); // 是否显示: 活跃用户数的红曲线
        
        if( $aHtml['show1']==0 && $aHtml['show2'] ==0 && $aHtml['show3']==0 && $aHtml['show4']==0 )
        { // 如果全部为空, 则默认全选
            $aHtml['show1'] = 1;
            $aHtml['show2'] = 1;
            $aHtml['show3'] = 1;
            $aHtml['show4'] = 1;
        }
        if( isset($_GET['sdate']) && false!==strtotime('20'.$_GET['sdate']) )
        {
            $aHtml['sdate'] = date( 'ymd', strtotime('20'.$_GET['sdate']) );
        }
        if( isset($_GET['edate']) && false!==strtotime('20'.$_GET['edate']) )
        {
            $aHtml['edate'] = date( 'ymd', strtotime('20'.$_GET['edate']) );
        }
        $aHtml['dates'] = '%26sdate='.$aHtml['sdate'].'%26edate='.$aHtml['edate'];
        $GLOBALS['oView']->assign('s', $aHtml );
        $GLOBALS['oView']->assign( "ur_here", "财务信息图表" );
        $GLOBALS['oView']->display( "marketmgr_finance.html" );
        EXIT;
    }



    /**
     * 日志信息图表
     * URL = ./index.php?controller=marketmgr&action=logs
     * @author SAUL
     */
    function actionLogs()
    {
        if( isset($_GET['getdatas']) ) // 供FLASH调用的URL,生成数据XML
        {
            $sSdate = date( 'ymd', time()-2678400 ); // 默认显示31天数据, 86400*31
            $sEdate = date( 'ymd'); // end date
            if( isset($_GET['sdate']) && false!==strtotime('20'.$_GET['sdate']) )
            {
                $sSdate = date( 'ymd', strtotime('20'.$_GET['sdate']) );
            }
            if( isset($_GET['edate']) && false!==strtotime('20'.$_GET['edate']) )
            {
                $sEdate = date( 'ymd', strtotime('20'.$_GET['edate']) );
            }
            $iShowLayer1 = isset($_GET['show1']) ? intval($_GET['show1']) : 0; // 是否显示: 总账变数, 红色
            $iShowLayer2 = isset($_GET['show2']) ? intval($_GET['show2']) : 0; // 是否显示: 追号个数, 蓝色
            $iShowLayer3 = isset($_GET['show3']) ? intval($_GET['show3']) : 0; // 是否显示: 注单个数, 绿色
            if( $iShowLayer1==0 && $iShowLayer2==0 && $iShowLayer3==0 )
            { // 默认同时显示3根线
                $iShowLayer1 = 1;
                $iShowLayer2 = 1;
                $iShowLayer3 = 1;
            }
            /* @var $oChart model_charts */
            //$oChart         = A::singleton("model_charts");
            $oChart = new model_charts($GLOBALS['aSysDbServer']['report']);
            $iAllCount      = 1;
            $aLabelAll      = array();
            $aDataAllCount  = array();
            if( $iShowLayer1 ) // 显示总账变数
            {
                $aResultAllCount = $oChart->getChartsResult( " WHERE `chartid`=20 AND `days` >= '".$sSdate.
                                "' AND `days` <= '".$sEdate."' GROUP BY `days` ORDER BY `days`" );
                $iAllCount       = count($aResultAllCount)+1;
                if( !empty($aResultAllCount) )
                {
                    foreach( $aResultAllCount AS $v )
                    {
                        $aLabelAll[]     = $v['days'];
                        $aDataAllCount[] = $v['TOMCOUNT'];
                    }
                }
                else
                {
                    $aDataAllCount[] =0;
                }
            }
            $aLabelAll = $iShowLayer1 ? $aLabelAll : array();
            $aDataTask = array();
            if( $iShowLayer2 ) // 显示充值账变数
            {
                $bAddtoLable = empty($aLabelAll);
                $aResultMoneyIn = $oChart->getChartsResult( " WHERE `chartid`=21 AND `days` >= '".$sSdate.
                                "' AND `days` <= '".$sEdate."' GROUP BY `days` ORDER BY `days`" );
                if( !$iShowLayer1 )
                {
                    $iAllCount  = count($aResultMoneyIn)+1;
                }
                if( !empty($aResultMoneyIn) )
                { 
                    foreach( $aResultMoneyIn AS $v )
                    {
                        if( $bAddtoLable )
                        {
                            $aLabelAll[]    = $v['days'];
                        }
                        $aDataTask[] = $v['TOMCOUNT'];
                    }
                }
                else
                {
                    $aDataTask[] = 0;
                }
            }
            $aLabelAll = $iShowLayer1 || $iShowLayer2 ? $aLabelAll : array();
            $aDataProject = array();
            if( $iShowLayer3 ) // 显示提现账变数
            {
                $bAddtoLable = empty($aLabelAll);
                $aResultMoneyOut = $oChart->getChartsResult( " WHERE `chartid`=22 AND `days` >= '".$sSdate.
                                "' AND `days` <= '".$sEdate."' GROUP BY `days` ORDER BY `days`" );
                if( !$iShowLayer1 && !$iShowLayer2 )
                {
                    $iAllCount   = count($aResultMoneyOut)+1;
                }
                if( !empty($aResultMoneyOut) )
                {
                    foreach( $aResultMoneyOut AS $v )
                    {
                        if( $bAddtoLable )
                        {
                            $aLabelAll[]    = $v['days'];
                        }
                        $aDataProject[] = $v['TOMCOUNT'];
                    }
                }
                else
                {
                    $aDataProject[] = 0;
                }
            }
            /* @var $oXml astats */
            $oXml = A::singleton("astats");
            $oXml->setChart( 'labelstep', ceil($iAllCount/10) );
            $oXml->addLabels( $aLabelAll );
            if( $iShowLayer1 )
            {
                $oXml->addData( $aDataAllCount, '总账变数' );
            }
            if( $iShowLayer2 )
            {
                $oXml->addData( $aDataTask, '追号个数', 'FF0000' );
            }
            if( $iShowLayer3 )
            {
                $oXml->addData( $aDataProject, '注单个数', '669900' );
            }
            $oXml->display();
            EXIT;
        }
        $aHtml['sdate'] = date( 'ymd', time()-86400*31 ); //默认显示最近一个月的数据
        $aHtml['edate'] = date( 'ymd');
        $aHtml['show1'] = isset($_GET['show1']); // 是否显示: 总账变数, 红色
        $aHtml['show2'] = isset($_GET['show2']); // 是否显示: 充值账变数,蓝色
        $aHtml['show3'] = isset($_GET['show3']); // 是否显示: 提现账变数,绿色
        if( $aHtml['show1']==0 && $aHtml['show2'] ==0 && $aHtml['show3'] ==0 )
        { // 如果全部为空, 则默认全选
            $aHtml['show1'] = 1;
            $aHtml['show2'] = 1;
            $aHtml['show3'] = 1;
        }
        if( isset($_GET['sdate']) && false!==strtotime('20'.$_GET['sdate']) )
        {
            $aHtml['sdate'] = date( 'ymd', strtotime('20'.$_GET['sdate']) );
        }
        if( isset($_GET['edate']) && false!==strtotime('20'.$_GET['edate']) )
        {
            $aHtml['edate'] = date( 'ymd', strtotime('20'.$_GET['edate']) );
        }
        $aHtml['dates'] = '%26sdate='.$aHtml['sdate'].'%26edate='.$aHtml['edate'];
        $GLOBALS['oView']->assign('s', $aHtml );
        $GLOBALS['oView']->assign( "ur_here", "日志信息图表" );
        $GLOBALS['oView']->display( "marketmgr_logs.html" );
        EXIT;
    }
}
?>