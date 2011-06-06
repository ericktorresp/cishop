<?php
/**
 * 文件 : /_app/controller/marketmgr.php
 * 功能 : 控制器  - 市场管理
 *
 * 功能:
 *  + actionUsers               用户信息图表
 *  + actionFinance             财务信息图表
 *  + actionLogs                日志信息图表
 *  + actionUserWinOrder        用户输赢排名
 *  + actionUserWinDetail       用户输赢详情
 *  + actionPlayUserOrder       参与人数排序
 *  + actionCompanyWinOrder     公司盈亏排序
 * 
 * @author    MARK
 * @version   1.2.0
 * @package   highadmin
 */

class controller_marketmgr extends basecontroller
{
    /* 用户信息图表
     * URL = ./index.php?controller=marketmgr&action=users
     * @author MARK
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
            $iIsTester   = isset($_GET['istester']) ? intval($_GET['istester']) : 0;//区分测试账户
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
            $oChart         = A::singleton("model_charts", $GLOBALS['aSysDbServer']['report']);
            $iAllCount      = 1;
            $aLabelAll      = array();
            $aDataAllUser   = array();
            if( $iShowLayer1 ) // 显示用户总数
            {
                $aResultAllUser = $oChart->getChartsResult( " WHERE `chartid`=1 AND `days` >= '".$sSdate.
                                "' AND `days` <= '".$sEdate."' GROUP BY `days` ORDER BY `days`" );
                $iAllCount      = count( $aResultAllUser ) + 1; // 为了防止分母为0
                if( !empty($aResultAllUser) )
                {
                    foreach( $aResultAllUser AS $v )
                    {
                        $aLabelAll[]    = $v['days'];
                        if( $iIsTester == -1 )
                        {
                            $aDataAllUser[] = $v['NORMALCOUNT'] + $v['TESTCOUNT'];
                        }
                        elseif ($iIsTester == 0)
                        {
                            $aDataAllUser[] = $v['NORMALCOUNT'];
                        }
                        else
                        {
                            $aDataAllUser[] = $v['TESTCOUNT'];
                        }
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
                    $iAllCount     = count($aResultActiveUser) + 1;
                }
                if( !empty($aResultActiveUser) )
                { 
                    foreach( $aResultActiveUser AS $v )
                    {
                        if( $bAddtoLable )
                        {
                            $aLabelAll[]    = $v['days'];
                        }
                        if( $iIsTester == -1 )
                        {
                            $aDataActiveUser[] = $v['NORMALCOUNT'] + $v['TESTCOUNT'];
                        }
                        elseif ($iIsTester == 0)
                        {
                            $aDataActiveUser[] = $v['NORMALCOUNT'];
                        }
                        else
                        {
                            $aDataActiveUser[] = $v['TESTCOUNT'];
                        }
                    }
                }
                else
                {
                    $aDataActiveUser[] = 0;
                }
            }
            $aLabelAll       = $iShowLayer1 || $iShowLayer2 ? $aLabelAll : array();
            $aDataWinUser    = array();
            if( $iShowLayer3 ) // 中奖用户数
            {
                $bAddtoLable = empty($aLabelAll);
                $aResultWinUser = $oChart->getChartsResult( " WHERE `chartid`=3 AND `days` >= '".$sSdate.
                                "' AND `days` <= '".$sEdate."' GROUP BY `days` ORDER BY `days`" );
                if( !($iShowLayer1 || $iShowLayer2) )
                {
                    $iAllCount     = count($aResultWinUser) + 1;
                }
                if( !empty($aResultWinUser) )
                { 
                    foreach( $aResultWinUser AS $v )
                    {
                        if( $bAddtoLable )
                        {
                            $aLabelAll[]    = $v['days'];
                        }
                        if( $iIsTester == -1 )
                        {
                            $aDataWinUser[] = $v['NORMALCOUNT'] + $v['TESTCOUNT'];
                        }
                        elseif ($iIsTester == 0)
                        {
                            $aDataWinUser[] = $v['NORMALCOUNT'];
                        }
                        else
                        {
                            $aDataWinUser[] = $v['TESTCOUNT'];
                        }
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
                $oXml->addData( $aDataAllUser, '高频用户总数' );
            }
            if( $iShowLayer2 )
            {
                $oXml->addData( $aDataActiveUser, '高频参与游戏用户数', 'FF0000' );
            }
            if( $iShowLayer3 )
            {
                $oXml->addData( $aDataWinUser, '高频中奖用户数', '00FF00' );
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
        $aHtml['istester']   = isset($_GET['istester']) ? intval($_GET['istester']) : 0;//区分测试账户
        $aHtml['dates'] = '%26sdate='.$aHtml['sdate'].'%26edate='.$aHtml['edate'];
        $GLOBALS['oView']->assign('s', $aHtml );
        $GLOBALS['oView']->assign( "ur_here", "用户信息图表" );
        $GLOBALS['oView']->display( "marketmgr_users.html" );
        EXIT;
    }



    /**
     * 财务信息图表
     * URL = ./index.php?controller=marketmgr&action=finance
     * @author MARK
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
            $iIsTester   = isset($_GET['istester']) ? intval($_GET['istester']) : 0;//区分测试账户
            $iShowLayer1 = isset($_GET['show1']) ? intval($_GET['show1']) : 0; // 是否显示: 获取游戏总额
            $iShowLayer2 = isset($_GET['show2']) ? intval($_GET['show2']) : 0; // 是否显示: 获取返奖总额
            $iShowLayer3 = isset($_GET['show3']) ? intval($_GET['show3']) : 0; // 是否显示: 获取银行转出
            $iShowLayer4 = isset($_GET['show4']) ? intval($_GET['show4']) : 0; // 是否显示: 获取高频转入
            if( $iShowLayer1==0 && $iShowLayer2==0 && $iShowLayer3==0 && $iShowLayer4==0 )
            { // 默认同时显示4根线
                $iShowLayer1 = 1;
                $iShowLayer2 = 1;
                $iShowLayer3 = 1;
                $iShowLayer4 = 1;
            }
            /* @var $oChart model_charts */
            $oChart       = A::singleton("model_charts", $GLOBALS['aSysDbServer']['report']);
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
                        if( $iIsTester == -1 )
                        {
                            $aGameMoneyIn[] = $v['NORMALCOUNT'] + $v['TESTCOUNT'];
                        }
                        elseif ($iIsTester == 0)
                        {
                            $aGameMoneyIn[] = $v['NORMALCOUNT'];
                        }
                        else
                        {
                            $aGameMoneyIn[] = $v['TESTCOUNT'];
                        }
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
                        if( $iIsTester == -1 )
                        {
                            $aGameMoneyOut[] = $v['NORMALCOUNT'] + $v['TESTCOUNT'];
                        }
                        elseif ($iIsTester == 0)
                        {
                            $aGameMoneyOut[] = $v['NORMALCOUNT'];
                        }
                        else
                        {
                            $aGameMoneyOut[] = $v['TESTCOUNT'];
                        }
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
                        if( $iIsTester == -1 )
                        {
                            $aMoneyOut[] = $v['NORMALCOUNT'] + $v['TESTCOUNT'];
                        }
                        elseif ($iIsTester == 0)
                        {
                            $aMoneyOut[] = $v['NORMALCOUNT'];
                        }
                        else
                        {
                            $aMoneyOut[] = $v['TESTCOUNT'];
                        }
                    }
                }
                else
                {
                    $aMoneyOut[] = 0;
                }
            }
            $aLabelAll = ($iShowLayer1 || $iShowLayer2 || $iShowLayer3) ? $aLabelAll : array();
            $aMoneyIn = array();
            if( $iShowLayer4 ) // 获取高频转入
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
                        if( $iIsTester == -1 )
                        {
                            $aMoneyIn[] = $v['NORMALCOUNT'] + $v['TESTCOUNT'];
                        }
                        elseif ($iIsTester == 0)
                        {
                            $aMoneyIn[] = $v['NORMALCOUNT'];
                        }
                        else
                        {
                            $aMoneyIn[] = $v['TESTCOUNT'];
                        }
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
                $oXml->addData( $aMoneyOut, '高频转出', '00FF00' );
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
        $aHtml['show1'] = isset($_GET['show1']); // 是否显示: 游戏总额
        $aHtml['show2'] = isset($_GET['show2']); // 是否显示: 返奖总额
        $aHtml['show3'] = isset($_GET['show3']); // 是否显示: 高频转出
        $aHtml['show4'] = isset($_GET['show4']); // 是否显示: 银行转入
        
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
        $aHtml['istester']   = isset($_GET['istester']) ? intval($_GET['istester']) : 0;//区分测试账户
        $aHtml['dates'] = '%26sdate='.$aHtml['sdate'].'%26edate='.$aHtml['edate'];
        $GLOBALS['oView']->assign('s', $aHtml );
        $GLOBALS['oView']->assign( "ur_here", "财务信息图表" );
        $GLOBALS['oView']->display( "marketmgr_finance.html" );
        EXIT;
    }



    /**
     * 日志信息图表
     * URL = ./index.php?controller=marketmgr&action=logs
     * @author MARK
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
            $iIsTester   = isset($_GET['istester']) ? intval($_GET['istester']) : 0;//区分测试账户
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
            $oChart         = A::singleton("model_charts", $GLOBALS['aSysDbServer']['report']);
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
                        if( $iIsTester == -1 )
                        {
                            $aDataAllCount[] = $v['NORMALCOUNT'] + $v['TESTCOUNT'];
                        }
                        elseif ($iIsTester == 0)
                        {
                            $aDataAllCount[] = $v['NORMALCOUNT'];
                        }
                        else
                        {
                            $aDataAllCount[] = $v['TESTCOUNT'];
                        }
                    }
                }
                else
                {
                    $aDataAllCount[] =0;
                }
            }
            $aLabelAll = $iShowLayer1 ? $aLabelAll : array();
            $aDataTask = array();
            if( $iShowLayer2 ) // 追号个数
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
                        if( $iIsTester == -1 )
                        {
                            $aDataTask[] = $v['NORMALCOUNT'] + $v['TESTCOUNT'];
                        }
                        elseif ($iIsTester == 0)
                        {
                            $aDataTask[] = $v['NORMALCOUNT'];
                        }
                        else
                        {
                            $aDataTask[] = $v['TESTCOUNT'];
                        }
                    }
                }
                else
                {
                    $aDataTask[] = 0;
                }
            }
            $aLabelAll = $iShowLayer1 || $iShowLayer2 ? $aLabelAll : array();
            $aDataProject = array();
            if( $iShowLayer3 ) // 注单个数
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
                        if( $iIsTester == -1 )
                        {
                            $aDataProject[] = $v['NORMALCOUNT'] + $v['TESTCOUNT'];
                        }
                        elseif ($iIsTester == 0)
                        {
                            $aDataProject[] = $v['NORMALCOUNT'];
                        }
                        else
                        {
                            $aDataProject[] = $v['TESTCOUNT'];
                        }
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
        $aHtml['show2'] = isset($_GET['show2']); // 是否显示: 追号个数,蓝色
        $aHtml['show3'] = isset($_GET['show3']); // 是否显示: 注单个数,绿色
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
        $aHtml['istester']   = isset($_GET['istester']) ? intval($_GET['istester']) : 0;//区分测试账户
        $aHtml['dates'] = '%26sdate='.$aHtml['sdate'].'%26edate='.$aHtml['edate'];
        $GLOBALS['oView']->assign('s', $aHtml );
        $GLOBALS['oView']->assign( "ur_here", "日志信息图表" );
        $GLOBALS['oView']->display( "marketmgr_logs.html" );
        EXIT;
    }
    
    
    /**
     * 用户输赢排名
     * URL = ./index.php?controller=marketmgr&action=userwinorder
     * @author MARK
     */
    public function actionUserWinOrder()
    {
        $iSearchType = isset($_GET['searchtype']) && $_GET['searchtype'] != '' ? intval($_GET['searchtype']) : 0;
        $sStartDate  = isset($_GET['sdate']) && $_GET['sdate'] != '' ? $_GET['sdate'] : date("Y-m-d 00:00:00",time());
        $sEndStart   = isset($_GET['edate']) && $_GET['edate'] != '' ? $_GET['edate'] : date("Y-m-d 23:59:59",time());
        $iLotteryId  = isset($_GET['lottery']) && $_GET['lottery'] != '' ? intval($_GET['lottery']) : 0;
        $iOrderNum   = isset($_GET['ordernum']) && $_GET['ordernum'] != '' ? intval($_GET['ordernum']) : 20;
        $aHtmlVal['searchtype'] = $iSearchType;
        $aHtmlVal['sdate']      = $sStartDate;
        $aHtmlVal['edate']      = $sEndStart;
        $aHtmlVal['lottery']    = $iLotteryId;
        $aHtmlVal['ordernum']   = $iOrderNum;
        $sWhere = '1';
        if( $sStartDate != '' )
        {
            $sWhere .= " AND p.`writetime` >= '" . $sStartDate ."'";
        }
        if( $sEndStart != '' )
        {
            $sWhere .= " AND p.`writetime` <= '" . $sEndStart ."'";
        }
        switch($iSearchType)
        {
            case 0://中奖最多
                $sOrderBy     = 'totalbonus';
                $sOrderByType = 'DESC';
                break;
            case 1://投注最多
                $sOrderBy     = 'totalprice';
                $sOrderByType = 'DESC';
                break;
            case 2://输得最多
                $sOrderBy     = 'totallose';
                $sOrderByType = 'DESC';
                break;
            case 3://赢得最多
                $sOrderBy     = 'totallose';
                $sOrderByType = 'ASC';
                break;
            default:
                break;
        }
        $oLottery = new model_lottery();
        $aLottery = $oLottery->lotteryGetList();
        if( $iLotteryId != 0 )
        {
            $sWhere .= " AND p.`lotteryid` = '" . $iLotteryId . "'";
        }
        $oMarketmgr  = new model_marketmgr();
        $aUserWinOrder = $oMarketmgr->getUserWinOrder( $sWhere, $sOrderBy, $sOrderByType , $iOrderNum );
        foreach ( $aUserWinOrder as $iKey => & $aUserList )
        {
            $aUserList['companyrate'] = $aUserList['totallose']/$aUserList['totalprice']*100;
            $aUserList['order'] = $iKey + 1;
        }
        $GLOBALS['oView']->assign( 'ur_here',   '用户输赢排名' );
        $GLOBALS['oView']->assign( "s", $aHtmlVal);
        $GLOBALS['oView']->assign( "alottery", $aLottery);
        $GLOBALS['oView']->assign( "aUserWinOrder", $aUserWinOrder);
        $oMarketmgr->assignSysInfo();
        $GLOBALS['oView']->display( "marketmgr_userwinorder.html" );
        EXIT;
    }
    
    
     /**
     * 用户输赢详情
     * URL = ./index.php?controller=marketmgr&action=userwindetail
     * @author MARK
     */
    public function actionUserWinDetail()
    {
        $sStartDate     = isset($_GET['sdate']) && $_GET['sdate'] != '' ? $_GET['sdate'] : date("Y-m-d 00:00:00",time());
        $sEndStart      = isset($_GET['edate']) && $_GET['edate'] != '' ? $_GET['edate'] : date("Y-m-d 23:59:59",time());
        $iUserId        = isset($_GET['userid']) && $_GET['userid'] != '' ? intval($_GET['userid']) : 0;
        $sWhere = ' 1 ';
        $oMarketmgr  = new model_marketmgr();
        if( $iUserId != 0 )
        {
            $sWhere .= " AND p.`userid` = '" . $iUserId ."'";
        }
        if( $sStartDate != '' )
        {
            $sWhere .= " AND p.`writetime` >= '" . $sStartDate ."'";
        }
        if( $sEndStart != '' )
        {
            $sWhere .= " AND p.`writetime` <= '" . $sEndStart ."'";
        }
        $p  = isset($_GET['p'])  ? intval($_GET['p'])  : 1;    // 分页用1
        $pn = isset($_GET['pn'])&&in_array($_GET['pn'],array(25,50,75,100,150,200)) ? intval($_GET['pn']) : 25;//分页用2                                  // 分页用2
        $aUserWinDatail = $oMarketmgr->getUserWinDetail( $sWhere, $pn , $p );
        $fTotalPrice = 0;
        $fTotalBonus = 0;
        foreach ($aUserWinDatail['results'] as &$aDetail)
        {
            $aDetail['projectnum'] = model_projects::HighEnCode("D".$aDetail['issue']."-".$aDetail['projectid'],"ENCODE");
            $fTotalPrice  += $aDetail['totalprice'];
            $fTotalBonus  += $aDetail['bonus'];
        }
        $oPager = new pages( $aUserWinDatail['affects'], $pn, 10);    // 分页用3
        $GLOBALS['oView']->assign( 'pages',     $oPager->show(2) ); // 分页用4
        $GLOBALS['oView']->assign( "aUserWinDetail", $aUserWinDatail['results']);
        $GLOBALS['oView']->assign( 'ur_here',   '用户输赢详情' );
        $GLOBALS['oView']->assign( 'totalprice',   $fTotalPrice );
        $GLOBALS['oView']->assign( 'totalbonus',   $fTotalBonus );
        $oMarketmgr->assignSysInfo();
        $GLOBALS['oView']->display( "marketmgr_userwindetail.html" );
        EXIT;
    }
    
    /**
     * 参与人数排序
     * URL = ./index.php?controller=marketmgr&action=playuserorder
     * @author MARK
     */
    public function actionPlayUserOrder()
    {
        $iSearchType = isset($_GET['searchtype']) && $_GET['searchtype'] != '' ? intval($_GET['searchtype']) : 0;
        $sStartDate  = isset($_GET['sdate']) && $_GET['sdate'] != '' ? $_GET['sdate'] : date("Y-m-d 00:00:00",time());
        $sEndStart   = isset($_GET['edate']) && $_GET['edate'] != '' ? $_GET['edate'] : date("Y-m-d 23:59:59",time());
        $iLotteryId  = isset($_GET['lottery']) && $_GET['lottery'] != '' ? intval($_GET['lottery']) : 0;
        $iOrderNum   = isset($_GET['ordernum']) && $_GET['ordernum'] != '' ? intval($_GET['ordernum']) : 20;
        $aHtmlVal['searchtype'] = $iSearchType;
        $aHtmlVal['sdate']      = $sStartDate;
        $aHtmlVal['edate']      = $sEndStart;
        $aHtmlVal['lottery']    = $iLotteryId;
        $aHtmlVal['ordernum']   = $iOrderNum;
        $sWhere = '1';
        if( $sStartDate != '' )
        {
            $sWhere .= " AND p.`writetime` >= '" . $sStartDate ."'";
        }
        if( $sEndStart != '' )
        {
            $sWhere .= " AND p.`writetime` <= '" . $sEndStart ."'";
        }
        switch($iSearchType)
        {
            case 0://参与人数最多的奖期
                $sFiled       = " l.`cnname`,p.`issue`,COUNT( DISTINCT p.`userid` )  AS usercount "; 
                $sOrderBy     = 'usercount';
                $sOrderByType = 'DESC';
                $sGroupBy     = " p.`lotteryid`,p.`issue`";
                break;
            case 1://参与人数最多的游戏
                $sFiled       = " l.`cnname`,COUNT( DISTINCT p.`userid` )  AS usercount "; 
                $sOrderBy     = 'usercount';
                $sOrderByType = 'DESC';
                $sGroupBy     = " p.`lotteryid`";
                break;
            case 2://参与人数最多的玩法
                $sFiled       = " l.`cnname`,m.`methodname`,COUNT( DISTINCT p.`userid` )  AS usercount "; 
                $sOrderBy     = 'usercount';
                $sOrderByType = 'DESC';
                $sGroupBy     = " p.`lotteryid`,p.`methodid`";
                break;
            case 3://销售量最好的奖期
                $sFiled       = " l.`cnname`,p.`issue`,SUM(p.`totalprice`)  AS totalprice "; 
                $sOrderBy     = 'totalprice';
                $sOrderByType = 'DESC';
                $sGroupBy     = " p.`lotteryid`,p.`issue`";
                break;
            default:
                break;
        }
        $oLottery = new model_lottery();
        $aLottery = $oLottery->lotteryGetList();
        if( $iLotteryId != 0 )
        {
            $sWhere .= " AND p.`lotteryid` = '" . $iLotteryId . "'";
        }
        $oMarketmgr  = new model_marketmgr();
        $aPlayUserOrder = $oMarketmgr->getPlayUserOrder( $sFiled, $sWhere, 
                                    $sOrderBy, $sGroupBy, $sOrderByType, $iOrderNum);
        foreach ( $aPlayUserOrder as $iKey => & $aUserList )
        {
            $aUserList['order'] = $iKey + 1;
        }
        $GLOBALS['oView']->assign( "s", $aHtmlVal);
        $GLOBALS['oView']->assign( 'ur_here',   '参与人数排序' );
        $GLOBALS['oView']->assign( "alottery", $aLottery);
        $GLOBALS['oView']->assign( "aPlayUserOrder", $aPlayUserOrder);
        $oMarketmgr->assignSysInfo();
        $GLOBALS['oView']->display( "marketmgr_playuserorder.html" );
        EXIT;
    }
    
    
    /**
     * 公司盈亏排序
     * URL = ./index.php?controller=marketmgr&action=companywinorder
     * @author MARK
     */
    public function actionCompanyWinOrder()
    {
        $iSearchType = isset($_GET['searchtype']) && $_GET['searchtype'] != '' ? intval($_GET['searchtype']) : 0;
        $sStartDate  = isset($_GET['sdate']) && $_GET['sdate'] != '' ? $_GET['sdate'] : date("Y-m-d 00:00:00",time());
        $sEndStart   = isset($_GET['edate']) && $_GET['edate'] != '' ? $_GET['edate'] : date("Y-m-d 23:59:59",time());
        $iLotteryId  = isset($_GET['lottery']) && $_GET['lottery'] != '' ? intval($_GET['lottery']) : 0;
        $iOrderNum   = isset($_GET['ordernum']) && $_GET['ordernum'] != '' ? intval($_GET['ordernum']) : 20;
        $aHtmlVal['searchtype'] = $iSearchType;
        $aHtmlVal['sdate']      = $sStartDate;
        $aHtmlVal['edate']      = $sEndStart;
        $aHtmlVal['lottery']    = $iLotteryId;
        $aHtmlVal['ordernum']   = $iOrderNum;
        $sWhere = '1';
        if( $sStartDate != '' )
        {
            $sWhere .= " AND p.`writetime` >= '" . $sStartDate ."'";
        }
        if( $sEndStart != '' )
        {
            $sWhere .= " AND p.`writetime` <= '" . $sEndStart ."'";
        }
        switch($iSearchType)
        {
            case 0://公司亏损最多的奖期 
                $sOrderBy     = 'totallose';
                $sOrderByType = 'ASC';
                break;
            case 1://公司盈利最多的奖期
                $sOrderBy     = 'totallose';
                $sOrderByType = 'DESC';
                break;
            default:
                break;
        }
        $oLottery = new model_lottery();
        $aLottery = $oLottery->lotteryGetList();
        if( $iLotteryId != 0 )
        {
            $sWhere .= " AND p.`lotteryid` = '" . $iLotteryId . "'";
        }
        $oMarketmgr  = new model_marketmgr();
        $aCompanyWinOrder = $oMarketmgr->getCompanyWinOrder($sWhere, $sOrderBy, $sOrderByType, $iOrderNum);
        foreach ( $aCompanyWinOrder as $iKey => & $aUserList )
        {
            $aUserList['order'] = $iKey + 1;
        }
        $GLOBALS['oView']->assign( "s", $aHtmlVal);
        $GLOBALS['oView']->assign( 'ur_here',   '公司盈亏排序' );
        $GLOBALS['oView']->assign( "alottery", $aLottery);
        $GLOBALS['oView']->assign( "aCompanyWinOrder", $aCompanyWinOrder);
        $oMarketmgr->assignSysInfo();
        $GLOBALS['oView']->display( "marketmgr_companywinorder.html" );
        EXIT;
    }
}
?>