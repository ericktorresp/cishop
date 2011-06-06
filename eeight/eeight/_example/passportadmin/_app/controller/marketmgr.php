<?php
/**
 * 文件 : /_app/controller/marketmgr.php
 * 功能 : 控制器 - 市场管理
 *
 * TODO: 这只是非完全精确的趋势图, 依赖很多周边因素
 *   例:  依赖系统 CRONTAB 对数据采样的时间点. 会丢失每天最后一分钟的数据
 *        依赖 chartsdatas 表的完整性, 不能出现日期断点情况 
 * 
 * @author	   Tom & Saul & James   090914
 * @version   1.2.0
 * @package   passportadmin
 */
class controller_marketmgr extends basecontroller 
{
    /**
     * 用户信息图表
     * URL = ./controller=marketmgr&action=users
     * TODO: 活跃用户的计算方法, 需改进
     * @author Tom 090512
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
            if( $iShowLayer1==0 && $iShowLayer2==0 )
            { // 默认同时显示2根线
                $iShowLayer1 = 1;
                $iShowLayer2 = 1;
            }
            $oChart         = new model_charts();
            $iAllCount      = 1;
            $aLabelAll      = array();
            $aDataAllUser   = array();
            if( $iShowLayer1 ) // 显示用户总数
            {
        	    $aResultAllUser = $oChart->getChartsResult( " WHERE `chartid`=1 AND `days` >= '".$sSdate.
        	    				"' AND `days` <= '".$sEdate."' GROUP BY `days` ORDER BY `days`" );
        	    $iAllCount      = count($aResultAllUser)+1; // 为了防止分母为0
                if( !empty($aResultAllUser) )
                {
                    foreach( $aResultAllUser AS $v )
                    {
                        $aLabelAll[]    = $v['days'];
                        $aDataAllUser[] = $v['TOMCOUNT'];
                    }
                }
            }
            $aLabelAll       = $iShowLayer1 ? $aLabelAll : array();
            $aDataActiveUser = array();
            if( $iShowLayer2 ) // 显示活跃用户数
            {
                $bAddtoLable = empty($aLabelAll);
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
            }
            /* @var $oXml astats */
    	    $oXml = A::singleton('astats');
    	    $oXml->setChart( 'labelstep', ceil($iAllCount/10) );
    	    $oXml->addLabels( $aLabelAll );
    	    if( $iShowLayer1 )
    	    {
    	        $oXml->addData( $aDataAllUser, '用户总数' );
    	    }
    	    if( $iShowLayer2 )
    	    {
    	        $oXml->addData( $aDataActiveUser, '活跃用户', 'FF0000' );
    	    }
    	    $oXml->display();
    	    EXIT;
        }
        $aHtml['sdate'] = date( 'ymd', time()-86400*31 ); // 默认显示最近一个月的数据
        $aHtml['edate'] = date( 'ymd');
        $aHtml['show1'] = isset($_GET['show1']); // 是否显示: 用户总数的蓝曲线
        $aHtml['show2'] = isset($_GET['show2']); // 是否显示: 活跃用户数的红曲线
        if( $aHtml['show1']==0 && $aHtml['show2'] ==0 )
        { // 如果全部为空, 则默认全选
            $aHtml['show1'] = 1;
            $aHtml['show2'] = 1;
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
     * URL = ./controller=marketmgr&action=finance
     * @author Tom 090513
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
            if( $iShowLayer1==0 && $iShowLayer2==0 )
            { // 默认同时显示2根线
                $iShowLayer1 = 1;
                $iShowLayer2 = 1;
            }
            /* @var $oChart model_charts */
            $oChart       = A::singleton('model_charts');
            $iAllCount    = 1;
            $aLabelAll    = array();
            $aDataMoneyIn = array();
    	    if( $iShowLayer1 ) // 获取充值总额结果集
    	    {
        	    $aResultMoneyIn = $oChart->getChartsResult( " WHERE `chartid`=10 AND `days` >= '".$sSdate.
        	    				"' AND `days` <= '".$sEdate."' GROUP BY `days` ORDER BY `days`" );
        	    $iAllCount = count($aResultMoneyIn)+1; // 为了防止分母为0
                if( !empty($aResultMoneyIn) )
                {
                    foreach( $aResultMoneyIn AS $v )
                    {
                        $aLabelAll[]    = $v['days'];
                        $aDataMoneyIn[] = $v['TOMCOUNT'];
                    }
                }
    	    }
            $aLabelAll = $iShowLayer1 ? $aLabelAll : array();
            $aDataMoneyOut = array();
            if( $iShowLayer2 ) // 获取提现总额结果集
            {
                $bAddtoLable = empty($aLabelAll);
    	        $aResultMoneyOut = $oChart->getChartsResult( " WHERE `chartid`=11 AND `days` >= '".$sSdate.
        	    				"' AND `days` <= '".$sEdate."' GROUP BY `days` ORDER BY `days`" );
                if( !$iShowLayer1 )
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
                        $aDataMoneyOut[] = $v['TOMCOUNT'];
                    }
                }
            }
    	    /* @var $oXml astats */
            $oXml = A::singleton('astats');
    	    $oXml->setChart( 'labelstep', ceil($iAllCount/10) );
    	    $oXml->addLabels( $aLabelAll );
    	    if( $iShowLayer1 )
    	    {
    	        $oXml->addData( $aDataMoneyIn, '充值总额' );
    	    }
    	    if( $iShowLayer2 )
    	    {
    	        $oXml->addData( $aDataMoneyOut, '提现总额', 'FF0000' );
    	    }
    	    $oXml->display();
    	    EXIT;
        }
        $aHtml['sdate'] = date( 'ymd', time()-86400*31 ); //  默认显示最近一个月的数据
        $aHtml['edate'] = date( 'ymd');
        $aHtml['show1'] = isset($_GET['show1']); // 是否显示: 用户总数的蓝曲线
        $aHtml['show2'] = isset($_GET['show2']); // 是否显示: 活跃用户数的红曲线
        if( $aHtml['show1']==0 && $aHtml['show2'] ==0 )
        { // 如果全部为空, 则默认全选
            $aHtml['show1'] = 1;
            $aHtml['show2'] = 1;
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
     * URL = ./controller=marketmgr&action=logs
	 * 完成: 100%
	 * @author Tom 090513
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
	        $iShowLayer2 = isset($_GET['show2']) ? intval($_GET['show2']) : 0; // 是否显示: 充值账变数,蓝色
	        $iShowLayer3 = isset($_GET['show3']) ? intval($_GET['show3']) : 0; // 是否显示: 提现账变数,绿色
	        if( $iShowLayer1==0 && $iShowLayer2==0 && $iShowLayer3==0 )
	        { // 默认同时显示2根线
	            $iShowLayer1 = 1;
	            $iShowLayer2 = 1;
	            $iShowLayer3 = 1;
	        }
	        /* @var $oChart model_charts */
    	    $oChart         = A::singleton('model_charts');
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
	        }
	        $aLabelAll = $iShowLayer1 ? $aLabelAll : array();
            $aDataMoneyIn = array();
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
                        $aDataMoneyIn[] = $v['TOMCOUNT'];
                    }
                }
	        }
	        $aLabelAll = $iShowLayer1 || $iShowLayer2 ? $aLabelAll : array();
            $aDataMoneyOut = array();
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
                        $aDataMoneyOut[] = $v['TOMCOUNT'];
                    }
                }
	        }
            $oXml = new astats();
    	    $oXml->setChart( 'labelstep', ceil($iAllCount/10) );
    	    $oXml->addLabels( $aLabelAll );
    	    if( $iShowLayer1 )
    	    {
    	        $oXml->addData( $aDataAllCount, '总账变数' );
    	    }
    	    if( $iShowLayer2 )
    	    {
    	        $oXml->addData( $aDataMoneyIn, '充值账变数', 'FF0000' );
    	    }
	        if( $iShowLayer3 )
    	    {
    	        $oXml->addData( $aDataMoneyOut, '提现账变数', '669900' );
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
	    $GLOBALS['oView']->assign( "ur_here", "用户信息图表" );
		$GLOBALS['oView']->display( "marketmgr_logs.html" );
		EXIT;
	}



    /**
     * 活动列表
     * URL = ./controller=marketmgr&action=Activitylist
     * @author James 090619
     */
    function actionActivitylist()
    {
        $sCondition = "1";
        $s["type"] = isset($_GET["type"])&&is_numeric($_GET["type"]) ? intval($_GET["type"]): -1 ;
        if( $s["type"] >= 0 )
        {
        	$sCondition .= " AND a.`type`='".$s['type']."'";
        }
        $s["isdel"] = isset($_GET["isdel"])&&is_numeric($_GET["isdel"]) ? intval($_GET["isdel"]): 0 ;
        if( $s["isdel"] >= 0 )
        {
        	$sCondition .= " AND  a.`isdel`='".$s["isdel"]."'";
        }
        $s["isverify"] = isset($_GET["isverify"])&&is_numeric($_GET["isverify"]) ? intval($_GET["isverify"]): -1; 
        if ($s["isverify"] >=0 )
        {
        	$sCondition .= " AND a.`isverify`='".$s["isverify"]."'";
        }
        $page = isset($_GET["p"])&&is_numeric($_GET["p"]) ? intval($_GET["p"]):1;
        /* @var $oActivity model_activity */
        $oActivity = A::singleton('model_activity');
        $aActivity = $oActivity->activityGetList( '', $sCondition, '', 20, $page );
        $GLOBALS['oView']->assign( 'actionlink2', array('href'=>url("marketmgr","activityadd"), 'text'=>'增加促销活动') );
        $GLOBALS['oView']->assign( 'actionlink',  array('href'=>url("marketmgr","activitylist"), 'text'=>'促销活动列表') );
        $GLOBALS['oView']->assign( "ur_here", "促销活动列表" );
        $GLOBALS['oView']->assign( "s", $s );
        $GLOBALS['oView']->assign( "activity", $aActivity['results'] );
        $page = new pages($aActivity['affects'], 20, 10 );
        $GLOBALS['oView']->assign( "pageinfo", $page->show() );
        $oActivity->assignSysInfo();
        $GLOBALS['oView']->display( "marketmgr_activity_list.html" );
        EXIT;
    }



    /**
     * 增加活动
     * URL = ./controller=marketmgr&action=Activityadd
     * @author James 090619
     */
    function actionActivityadd()
    {
       if( empty($_POST['step']) || ($_POST['step']!='first' && $_POST['step']!='second' && $_POST['step']!='last' 
                  && $_POST['step']!='delquestion' ) )
        {
            $GLOBALS['oView']->assign( 'actionlink',  
                                       array('href'=>url("marketmgr","activitylist"), 'text'=>'促销列表') );
            $GLOBALS['oView']->assign( "ur_here", "增加促销活动" );
            $GLOBALS['oView']->display( "marketmgr_activity_add.html" );
            EXIT;
        }
        elseif( $_POST['step'] == 'first' )
        { //echo "fail" means fail, else echo insertid                   
            $aActivity = array(
            	"title"			=>	isset($_POST["acttitle"]) ? $_POST["acttitle"] : "",
            	"description"	=>	isset($_POST["actdescription"]) ? $_POST["actdescription"] : "",
            	"feedback"		=>	"",
            	"starttime"		=>	isset($_POST["actstarttime"]) ? $_POST["actstarttime"] : date("Y-m-d H:i:s"),
            	"endtime"		=>	isset($_POST["actendtime"]) ? $_POST["actendtime"] : date("Y-m-d H:i:s"),
            	"minscore" 		=>  ( isset($_POST["actminscore"]) && is_numeric($_POST["actminscore"]) ) ? 
                                    intval($_POST["actminscore"]) : 0,
            	"prize"			=>	( isset($_POST["actprize"]) && is_numeric($_POST["actprize"]) ) ? 
                                    number_format($_POST["actprize"], 2) : 0.00
            );
            $oActivity = new model_activity();
            if( isset($_POST['activityid']) && is_numeric($_POST['activityid']) && $_POST['activityid'] > 0 )
            {//修改已经不存过的
            	$iActivityId = intval( $_POST['activityid'] );
            	$iResult     = $oActivity->activityUpdate( $aActivity, $iActivityId );
            	if( $iResult > 0 )
            	{
            		$iResult = $iActivityId;
            	}
            }
            else
            {
            	$aActivity['type'] = ( isset($_POST["acttype"]) && is_numeric($_POST["acttype"]) ) ? 
                                    (intval($_POST["acttype"])-1) : -1;
            	$iResult   = $oActivity->activityInsert($aActivity);
            }
            echo $iResult;
            EXIT;
        }
        elseif( $_POST['step'] == 'second' )
        {
            if( empty($_POST['actid']) || !is_numeric($_POST['actid']) )
            {
                echo 'lawless';
                EXIT;
            }
        	if( empty($_POST['questionnum']) || !is_numeric($_POST['questionnum']) )
        	{
        		echo 'fail';
                EXIT;
        	}
        	$aResult       = array();
        	$iQuestionNum  = intval($_POST['questionnum']);
        	$oActivityInfo = new model_activityinfo();
        	for( $i=1; $i<=$iQuestionNum; $i++ )
        	{
        		$aTemp_Arr               = array();
                $aTemp_Arr['activityid'] = intval($_POST['actid']);
        		$iTemp_OptionNum         = 0;
        		$aTemp_Option            = array();
                $mTemp_Answer            = array();
                $iTemp_Result            = 0;
        		if( !empty($_POST['question_title_'.$i]) && !empty($_POST['question_type_'.$i]) )
        		{
        			$aTemp_Arr['title']   = $_POST['question_title_'.$i];
        			$aTemp_Arr['type']    = intval($_POST['question_type_'.$i])-1;
        			$iTemp_OptionNum      = empty( $_POST['question_answers_'.$i.'_options_count'] ) ?
        			                        0 : intval( $_POST['question_answers_'.$i.'_options_count'] );
        			if( $iTemp_OptionNum > 0 )
        			{//获取选项
        				for( $j=1; $j<=$iTemp_OptionNum; $j++ )
        				{
        					if( isset($_POST['question_answers_'.$i.'_option_'.$j]) )
        					{
        						$aTemp_Option[$j] = $_POST['question_answers_'.$i.'_option_'.$j];
        					}
        				}
        			}
        			if( isset($_POST['question_answers_other_'.$i]) && $_POST['question_answers_other_'.$i] == 1 )
        			{
        				$aTemp_Option['other'] = '1';
        			}
        			$mTemp_Answer = isset($_POST['question_answers_'.$i.'_check']) ? 
        			                     $_POST['question_answers_'.$i.'_check'] : '';
        			if( empty($aTemp_Option) )
        			{//没有选项[可能是文本或者段落文本]
        				$aTemp_Arr['options'] = "";
        				$aTemp_Arr['answer']  = ""; //没有答案
        			}
        			else 
        			{
        				$aTemp_Arr['options'] = serialize( $aTemp_Option );
        				if( !is_array($mTemp_Answer) )
        				{
        					$mTemp_Answer  = intval($mTemp_Answer);
        					if( isset($aTemp_Option[$mTemp_Answer]) )
        					{
        						$aTemp_Arr['answer']  = $mTemp_Answer;
        					}
        				}
        				else
        				{
        					foreach( $mTemp_Answer as $k=>&$v )
        					{
        						$v  =  intval($v);
        						if( !isset($aTemp_Option[$v]) )
        						{
        							unset( $mTemp_Answer[$k] );
        						}
        					}
        					$aTemp_Arr['answer']  = implode( ',', $mTemp_Answer );
        				}
        			}
        			$aTemp_Arr['score']     = isset($_POST['question_score_'.$i]) ? 
        			                        intval($_POST['question_score_'.$i]) : 0;
        			$aTemp_Arr['hint']      = isset($_POST['question_help_'.$i]) ? $_POST['question_help_'.$i] : "";
        			$aTemp_Arr['minright']  = isset($_POST['question_answers_minselect_'.$i]) ? 
        			                        intval($_POST['question_answers_minselect_'.$i]) : 0;
        		    $aTemp_Arr['isrequire'] = isset($_POST['question_must_'.$i]) ? 1 : 0;
        		    
        		    if( !empty($_POST['question_id_'.$i]) && is_numeric($_POST['question_id_'.$i]) )
        		    {//修改已经存在的
        		    	$iTemp_Result = $oActivityInfo->activifyInfoUpdate($aTemp_Arr, intval($_POST['question_id_'.$i]));
        		    	if( $iTemp_Result == 1 )
        		    	{
        		    		$iTemp_Result = intval($_POST['question_id_'.$i]);
        		    	}
        		    }
        		    else
        		    {//保存到数据库
        		        $iTemp_Result = $oActivityInfo->activityInfoInsert( $aTemp_Arr );
        		    }
        		    $aResult[] = array( 'key'=>$i, 'value'=>$iTemp_Result );
        		}
        		elseif( isset($_POST['question_title_'.$i]) && empty($_POST['question_title_'.$i]) )
        		{
        			$aResult[] = array( 'key'=>$i, 'value'=>-3 );
        		}
        		elseif( isset($_POST['question_type_'.$i]) && empty($_POST['question_type_'.$i]) )
        		{
        			$aResult[] = array( 'key'=>$i, 'value'=>-4 );
        		}
        	}
        	$sReturn = json_encode( $aResult );
            echo $sReturn;
            EXIT;
        }
        elseif( $_POST['step'] == 'last' )
        {
        	if( empty($_POST['factid']) || !is_numeric($_POST['factid']) )
        	{
        		echo 'first';
        		EXIT;
        	}
        	$iActivityId = intval($_POST['factid']);
        	if( empty($_POST['actfeedback']) )
        	{
        		echo 'empty';
        		EXIT;
        	}
        	$sFeedBack = $_POST['actfeedback'];
        	$oActivity = new model_activity();
        	$iResult   = $oActivity->activityUpdate( array('feedback'=>$sFeedBack), $iActivityId );
        	if( $iResult >= 0 )
        	{
        		$aLocation = array( 0 => array("text"=>'促销活动列表','href'=>url('marketmgr','activitylist')));
        		sysMessage( '操作成功', 0, $aLocation );
        	}
        	else 
        	{
        		echo 'fail';
        	}
        	EXIT;
        }
        elseif( $_POST['step'] == 'delquestion' )
        {
        	if( empty($_POST['qid']) || !is_numeric($_POST['qid']) )
        	{
        		echo 'fail';
        		EXIT;
        	}
        	$iQuestionId   = intval($_POST['qid']);
        	$oActivityInfo = new model_activityinfo();
        	$iResult       = $oActivityInfo->activifyInfoDelete( $iQuestionId );
        	echo $iResult;
        	EXIT; 
        }
    }



	/**
     * 修改活动
     * URL = ./controller=marketmgr&action=Activityedit
	 * @author SAUL 090619
     */
    function actionActivityedit()
	{
		$aLocation = array( 0 => array("text"=>'促销活动列表','href'=>url('marketmgr','activitylist')));
		if( empty($_POST['step']) || ($_POST['step']!='first' && $_POST['step']!='second' && $_POST['step']!='last' 
                  && $_POST['step']!='delquestion' ) )
        {
		    $iActivityId = (isset($_GET["activityid"]) && is_numeric($_GET["activityid"]) ) ? 
		                    intval($_GET["activityid"]) : 0;
		    if( $iActivityId <= 0 )
		    {
		    	sysMessage( '非法操作', 1, $aLocation );
		    }
		    //获取活动基本信息
		    $oActivity = new model_activity();
		    $aActivity = $oActivity->activityGetOne( '*', "`activityid`='".$iActivityId."'" );
		    if( empty($aActivity) )
		    {
		    	sysMessage( '非法操作', 1, $aLocation );
		    }
		    $aActivity["starttime"] = getFilterDate( $aActivity["starttime"], "Y-m-d H:i" );
		    $aActivity["endtime"]   = getFilterDate( $aActivity["endtime"], "Y-m-d H:i" );
		    //获取活动问题
		    $oActivityInfo = new model_activityinfo();
	        $aActivityInfo = $oActivityInfo->activityInfoGetList( '*', "`activityid`='".$iActivityId."'", '', 0 );
	        foreach( $aActivityInfo as &$v )
	        {
	            $v['options'] = json_encode( unserialize($v['options']) );
	            $v['type']   += 1; 
	            if($aActivity['type'] == 1)
	            {
	                $v['answer'] = explode( ',', $v['answer'] );
	            }
	            else
	            {
	            	$v['answer'] = "";
	            }
	            $v['answer'] = json_encode($v['answer']);
	        }
		    $GLOBALS['oView']->assign( "actionlink" , array("text"=>'促销活动列表', 
		                                "href" => url('marketmgr','activitylist')));
			$GLOBALS['oView']->assign( "aActivity", $aActivity );
			$GLOBALS['oView']->assign( "aActivityInfo", $aActivityInfo );
			$GLOBALS['oView']->assign( "iQuestionNum", count($aActivityInfo) );
			$GLOBALS['oView']->assign( "ur_here", "修改促销活动");
			$oActivity->assignSysInfo();
			$GLOBALS['oView']->display( "marketmgr_activity_edit.html" );
		    EXIT;
        }
	    elseif( $_POST['step'] == 'first' )
        {
            $iActivityId = (isset($_POST["activityid"]) && is_numeric($_POST["activityid"]) ) ? 
                            intval($_POST["activityid"]) : 0;
            if( $iActivityId <= 0 )
            {
                sysMessage( '非法操作', 1, $aLocation );
            }   
            $aActivity = array(
                "title"         =>  isset($_POST["acttitle"]) ? $_POST["acttitle"] : "",
                "description"   =>  isset($_POST["actdescription"]) ? $_POST["actdescription"] : "",
                "starttime"     =>  isset($_POST["actstarttime"]) ? $_POST["actstarttime"] : date("Y-m-d H:i:s"),
                "endtime"       =>  isset($_POST["actendtime"]) ? $_POST["actendtime"] : date("Y-m-d H:i:s"),
                "minscore"      =>  ( isset($_POST["actminscore"]) && is_numeric($_POST["actminscore"]) ) ? 
                                    intval($_POST["actminscore"]) : 0,
                "prize"         =>  ( isset($_POST["actprize"]) && is_numeric($_POST["actprize"]) ) ? 
                                    number_format($_POST["actprize"], 2) : 0.00
            );
            $oActivity = new model_activity();
            $iResult   = $oActivity->activityUpdate( $aActivity, $iActivityId );
            if( $iResult > 0 )
            {//修改成功，取消审核状态
            	$oActivity->activitysetStatus( $iActivityId, 0 );
            }
            switch( $iResult )
            {
            	case -5 : sysMessage( '非法操作', 1, $aLocation ); break;
            	case -6 : sysMessage( '活动没有结束,不能更新', 1, $aLocation ); break;
            	case -7 : sysMessage( '非法操作', 1, $aLocation ); break;
            	default : echo $iResult; break;
            }
            EXIT;
        }
        elseif( $_POST['step'] == 'second' )
        {
            if( empty($_POST['actid']) || !is_numeric($_POST['actid']) )
            {
                sysMessage( '非法操作', 1, $aLocation );
            }
            if( empty($_POST['questionnum']) || !is_numeric($_POST['questionnum']) )
            {
                echo 'fail';
                EXIT;
            }
            $aResult       = array();
            $iQuestionNum  = intval($_POST['questionnum']);
            $oActivityInfo = new model_activityinfo();
            $bIsUpdate     = FALSE;
            for( $i=1; $i<=$iQuestionNum; $i++ )
            {
                $aTemp_Arr               = array();
                $aTemp_Arr['activityid'] = intval($_POST['actid']);
                $iTemp_OptionNum         = 0;
                $aTemp_Option            = array();
                $mTemp_Answer            = array();
                $iTemp_Result            = 0;
                if( !empty($_POST['question_title_'.$i]) && !empty($_POST['question_type_'.$i]) )
                {
                    $aTemp_Arr['title']   = $_POST['question_title_'.$i];
                    $aTemp_Arr['type']    = intval($_POST['question_type_'.$i])-1;
                    $iTemp_OptionNum      = empty( $_POST['question_answers_'.$i.'_options_count'] ) ?
                                            0 : intval( $_POST['question_answers_'.$i.'_options_count'] );
                    if( $iTemp_OptionNum > 0 )
                    {//获取选项
                        for( $j=1; $j<=$iTemp_OptionNum; $j++ )
                        {
                            if( isset($_POST['question_answers_'.$i.'_option_'.$j]) )
                            {
                                $aTemp_Option[$j] = $_POST['question_answers_'.$i.'_option_'.$j];
                            }
                        }
                    }
                    if( isset($_POST['question_answers_other_'.$i]) && $_POST['question_answers_other_'.$i] == 1 )
                    {
                        $aTemp_Option['other'] = '1';
                    }
                    $mTemp_Answer = isset($_POST['question_answers_'.$i.'_check']) ? 
                                         $_POST['question_answers_'.$i.'_check'] : '';
                    if( empty($aTemp_Option) )
                    {//没有选项[可能是文本或者段落文本]
                        $aTemp_Arr['options'] = "";
                        $aTemp_Arr['answer']  = ""; //没有答案
                    }
                    else 
                    {
                        $aTemp_Arr['options'] = serialize( $aTemp_Option );
                        if( !is_array($mTemp_Answer) )
                        {
                            $mTemp_Answer  = intval($mTemp_Answer);
                            if( isset($aTemp_Option[$mTemp_Answer]) )
                            {
                                $aTemp_Arr['answer']  = $mTemp_Answer;
                            }
                        }
                        else
                        {
                            foreach( $mTemp_Answer as $k=>&$v )
                            {
                                $v  =  intval($v);
                                if( !isset($aTemp_Option[$v]) )
                                {
                                    unset( $mTemp_Answer[$k] );
                                }
                            }
                            $aTemp_Arr['answer']  = implode( ',', $mTemp_Answer );
                        }
                    }
                    $aTemp_Arr['score']     = isset($_POST['question_score_'.$i]) ? 
                                            intval($_POST['question_score_'.$i]) : 0;
                    $aTemp_Arr['hint']      = isset($_POST['question_help_'.$i]) ? $_POST['question_help_'.$i] : "";
                    $aTemp_Arr['minright']  = isset($_POST['question_answers_minselect_'.$i]) ? 
                                            intval($_POST['question_answers_minselect_'.$i]) : 0;
                    $aTemp_Arr['isrequire'] = isset($_POST['question_must_'.$i]) ? 1 : 0;
                    
                    if( !empty($_POST['question_id_'.$i]) && is_numeric($_POST['question_id_'.$i]) )
                    {//修改已经存在的
                        $iTemp_Result = $oActivityInfo->activifyInfoUpdate($aTemp_Arr, intval($_POST['question_id_'.$i]));
                        if( $iTemp_Result == 1 )
                        {
                            $iTemp_Result = intval($_POST['question_id_'.$i]);
                        }
                    }
                    else
                    {//保存到数据库
                        $iTemp_Result = $oActivityInfo->activityInfoInsert( $aTemp_Arr );
                    }
                    if( $iTemp_Result > 0 )
                    {
                    	$bIsUpdate = TRUE;
                    }
                    $aResult[] = array( 'key'=>$i, 'value'=>$iTemp_Result );
                }
                elseif( isset($_POST['question_title_'.$i]) && empty($_POST['question_title_'.$i]) )
                {
                    $aResult[] = array( 'key'=>$i, 'value'=>-3 );
                }
                elseif( isset($_POST['question_type_'.$i]) && empty($_POST['question_type_'.$i]) )
                {
                    $aResult[] = array( 'key'=>$i, 'value'=>-4 );
                }
            }
            if( $bIsUpdate )
            {//修改成功，取消审核状态
            	$oActivity = new model_activity();
                $oActivity->activitysetStatus( intval($_POST['actid']), 0 );
            }
            $sReturn = json_encode( $aResult );
            echo $sReturn;
            EXIT;
        }
        elseif( $_POST['step'] == 'last' )
        {
            if( empty($_POST['factid']) || !is_numeric($_POST['factid']) )
            {
                 sysMessage( '非法操作', 1, $aLocation );
            }
            $iActivityId = intval($_POST['factid']);
            if( empty($_POST['actfeedback']) )
            {
                echo 'empty';
                EXIT;
            }
            $sFeedBack = $_POST['actfeedback'];
            $oActivity = new model_activity();
            $iResult   = $oActivity->activityUpdate( array('feedback'=>$sFeedBack), $iActivityId );
            if( $iResult > 0 )
            {//修改成功，取消审核状态
                $oActivity->activitysetStatus( $iActivityId, 0 );
            }
            if( $iResult >= 0 )
            {
                sysMessage( '操作成功', 0, $aLocation );
            }
            else 
            {
                echo 'fail';
            }
            EXIT;
        }
        elseif( $_POST['step'] == 'delquestion' )
        {
            if( empty($_POST['qid']) || !is_numeric($_POST['qid']) )
            {
                echo 'fail';
                EXIT;
            }
            $iQuestionId   = intval($_POST['qid']);
            $oActivityInfo = new model_activityinfo();
            $iResult       = $oActivityInfo->activifyInfoDelete( $iQuestionId );
            echo $iResult;
            EXIT; 
        }
	}



    /**
     * 查看活动结果
     * URL = ./controller=marketmgr&action=Activitystatus
     * @author tom 090623
     */
	function actionActivitystatus()
	{
	    // 临时数组, 用于模拟从数据库中获取的结果集
	    $temp = array( 
	            urlencode(@iconv( 'UTF-8', 'GB2312', '实时彩 SSC')), 
	            urlencode(@iconv( 'UTF-8', 'GB2312', '实时乐 SSL')),
	            urlencode(@iconv( 'UTF-8', 'GB2312', '排列三 P3')),
	            urlencode(@iconv( 'UTF-8', 'GB2312', '排列五 P5')),
	            urlencode(@iconv( 'UTF-8', 'GB2312', '22选5 ')),
	        );
	    $GLOBALS['oView']->assign( 'actionlink2', array('href'=>url("marketmgr","activityadd"), 'text'=>'查看活动结果') );
        $GLOBALS['oView']->assign( 'actionlink',  array('href'=>url("marketmgr","activitylist"), 'text'=>'促销活动列表') );
        $GLOBALS['oView']->assign( "var", $temp );
        $GLOBALS['oView']->assign( "ur_here", "增加促销活动" );
        $GLOBALS['oView']->display( "marketmgr_activity_status.html" );
        EXIT;
	    
	}


    
    /**
     * 查看活动结果 FLASH-XML 形式的报表
     * @author Tom 090622
     * url 参数:
     *     settings_file =  返回设置文件的 XML
     *     data_file     =  返回数据文件的 XML 
     */
    function actionActivityviewxml()
    {
        //die( urlencode('其他') );
        $oXml = new astatspie();
        if( isset($_GET['settings_file']) ) 
        { // 生成格式数据XML
        	$oXml->getSettingsFile();
        	EXIT;
        }
        elseif( isset($_GET['data_file']) && isset($_GET['args']) )
        { // 生成实际数据XML
            // 解析 data_file 中 args 传递过来的参数
            // 10|219.159.104.132,222.90.230.4,117.33.225.24,220.166.172.5,117.36.101.154,60.221.125.63,124.94.189.238,119.109.201.66,218.64.17.231,其它
            // |28,18,18,15,13,8,8,8,6,152
            //echo $_GET['args'];//EXIT;
            $aTmpArry = @explode( '|', $_GET['args'] );
            if( !is_array($aTmpArry) || count($aTmpArry) != 3 || $aTmpArry[0] < 1 )
            {
                die('args data error 0x1001');
            }
            $iTotalDatas = $aTmpArry[0];
            $aTitles = @explode(',', $aTmpArry[1]);
            $aValues = @explode(',', $aTmpArry[2]);
            if( $iTotalDatas!=count($aTitles) || $iTotalDatas!=count($aValues) )
            {
                die('args data error 0x1002');
            }
            for( $i=0; $i<$iTotalDatas; $i++ )
            {
                //var_dump( $aTitles[$i] );
                $oXml->addData( $aTitles[$i], $aValues[$i], $i==0?'true':'false' );
            }
            echo $oXml->display();
        }
        else
        {
            EXIT;
        }
    }



    /**
     * 审核活动
     * URL = ./controller=marketmgr&action=Activityverify
     *    - 正在进行中的活动(并且有关联用户结果集) 禁止取消审核
     * @author SAUL 090619
     */
    function actionActivityverify()
    {
        $iActivityId = isset($_POST["id"])&&is_numeric($_POST["id"]) ? intval($_POST["id"]) : 0;
        if( $iActivityId<=0 )
        {
        	sysMessage( 'ID非法', 1 );
        }
        $oActivity    = new model_activity();
        $iResult      = $oActivity->activitysetStatus( $iActivityId, 1 );
        $aLocation[0] = array("text"=>'销售活动列表','href'=>url('marketmgr','activitylist'));
        switch($iResult)
        {
        	case -1:
        		sysMessage( '操作失败:活动不存在或者参数错误.', 1, $aLocation );
        		break;
        	case -2:
        		sysMessage( '操作失败:活动已经删除.', 1, $aLocation );
        		break;
        	case -3:
        		sysMessage( '操作失败:活动开始时间已经过去.', 1, $aLocation );
        		break;
        	case -4:
        		sysMessage( '操作失败:活动题目没有增加.', 1, $aLocation );
        		break;
        	case -5:
        		sysMessage( '操作失败:活动没有分配用户', 1, $aLocation );
        		break;
        	case -6:
        		sysMessage( '操作失败:管理员非法', 1, $aLocation );
        		break;
        	case -7:
        		sysMessage( '操作失败:审核人员和发起人员不能是同一个人', 1, $aLocation );
        		break;
        	case 0:
        		sysMessage( '操作失败:活动的原始状态已经验证.', 1, $aLocation );
        		break;
        	default:
        		sysMessage( '操作成功', 0, $aLocation );
        		break;
        }
    }


    
    /**
     * 删除活动
     * URL = ./controller=marketmgr&action=Activitydel
     *    - 正在进行中的活动(并且有关联用户结果集) 禁止删除
     * @author James 090619
     */
    function actionActivitydel()
    {
    	$aLocation[0] = array( 'text' => '促销活动列表', 'href'=> url('marketmgr','activitylist'));	
    	if(isset($_POST))
    	{
    		$sAction =  isset($_POST["form_action"]) ? $_POST["form_action"] : "";
    		if($sAction == "bat_delete")
    		{ // 批量删除
    			$aActivityId = array();
    			if( isset($_POST["checkboxes"]) )
    			{
    				foreach($_POST["checkboxes"] as $iValue)
    				{
    					if(is_numeric($iValue))
    					{
    						$aActivityId[] = $iValue;
    					}
    				}
    			}
    			if( empty($aActivityId) )
    			{
    				sysMessage( '操作失败:数据非法或者为空.', 1, $aLocation );
    			}
    			$oActivity = new model_activity();
    			$iResult = $oActivity->batDel($aActivityId);
    			switch ($iResult)
    			{
    				case -1:
    					sysMessage( '操作失败:提交数据不正确', 1, $aLocation );
    					break;
    				case -3:
    					sysMessage( '操作失败:中间含有已验证的活动.', 1, $aLocation );
    					break;
    				case -4:
    					sysMessage( '操作失败:中间含有已经完成的活动,但还没有派奖.',1, $aLocation );
    					break;
    				case -5:
    					sysMessage( '操作失败:中间含有已经完成的活动,但还有部分没有派奖.', 1, $aLocation );
    					break;					
    				default:
    					sysMessage( '操作成功', 0, $aLocation );
    					break;
    			}
    		}
    	}
    }



    /**
     * 更新活动信息
     * URL = ./?controller=marketmgr&action=activityupdate
     * @author SAUL 090624
     */
    function actionActivityupdate()
    {
    	$aLocation[0] = array('text'=>'促销活动列表','href'=>url('marketmgr','activitylist'));
    	$iActivityId  = isset($_POST["actactivityid"])&&is_numeric($_POST["actactivityid"]) ? intval($_POST["actactivityid"]) : 0;
    	if( $iActivityId <=0 )
    	{
    		sysMessage( "ID传参错误", 1, $aLocation );
    	}
    	$aActivity = array(
          	"title"			=>	isset($_POST["acttitle"]) ? $_POST["acttitle"] : "",
          	"description"	=>	isset($_POST["actdescription"]) ? $_POST["actdescription"] : "",
          	"feedback"		=>	isset($_POST["actfeedback"]) ? $_POST["actfeedback"]:"",
          	"starttime"		=>	isset($_POST["actstarttime"])?getFilterDate($_POST["actstarttime"]):date("Y-m-d H:i:s"),
          	"endtime"		=>	isset($_POST["actendtime"]) ? getFilterDate($_POST["actendtime"]):date("Y-m-d H:i:s"),
          	"minscore" 		=>  isset($_POST["actminscore"])&&is_numeric($_POST["actminscore"])?intval($_POST["actminscore"]):0,
          	"prize"			=>	isset($_POST["actprize"])&&is_numeric($_POST["actprize"])?number_format($_POST["actprize"],2):0.00	
    	);
        $oActivity = new model_activity();
        $iResult = $oActivity->activityupdate( $aActivity, $iActivityId );
    	switch ($iResult)
    	{
    		case -1:
    			sysMessage( '操作失败:活动不存在.', 1, $aLocation);
    			break;
    		case -2:
    			sysMessage( '操作失败:活动被删除.', 1, $aLocation );
    			break;
    		case -3:
    			sysMessage( '操作失败:活动已经审核.', 1, $aLocation );
    			break;
    		case -4:
    			sysMessage( '操作失败:活动标题为空.', 1, $aLocation );
    			break;
    		case -5:
    			sysMessage( '操作失败:活动时间过早.', 1, $aLocation );
    			break;
    		case -6:
    			sysMessage( '操作失败:答题结束时间不能早于答题开始时间.', 1, $aLocation );
    			break;
    		case -7:
    			sysMessage( '操作失败:答题分数错误.', 1, $aLocation );
    			break;
    		case -8:
    			sysMessage( '操作失败:活动奖金错误.', 1, $aLocation );
    			break;
    		case -9:
    			sysMessage( '操作失败:管理员非法.', 1, $aLocation );
    			break;
    		case -10:
    			sysMessage( '操作失败:审核管理员不能和发起管理员为同一个人.', 1, $aLocation );
    			break;
    		default:
    			sysMessage( '操作成功', 0, $aLocation );
    			break;
    	}
    }


    
    /**
     * 对已经审核的活动进行取消
     * URL = ./?controller=marketmgr&action=cannel
     * @author Saul
     */
    function actionCannel()
    {
    	$aLocation[0] = array('text'=>'促销活动列表','href' => url('marketmgr','activitylist'));
    	$iActivityId = isset($_GET["activityid"])&&is_numeric($_GET["activityid"]) ? intval($_GET["activityid"]) : 0;
    	if( $iActivityId <= 0 )
    	{
    		sysMessage( 'ID非法', 1, $aLocation );
    	}
    	$aActivity = new model_activity();
    	$iResult = $aActivity->activitysetStatus( $iActivityId, 0 );
    	switch( $iResult )
    	{
    		case -1:
    			sysMessage( '操作失败:活动不存在.', 1, $aLocation );
    			break;
    		case -2:
    			sysMessage( '操作失败:活动被删除.', 1, $aLocation );
    			break;
    		case -3:
    			sysMessage( '操作失败:时间已经过去.', 1, $aLocation );
    			break;
    		default:
    			sysMessage( '操作成功', 0, $aLocation );
    			break;
    	}
    }


    
    /**
     * 对已经审核的活动进行取消
     * URL = ./?controller=marketmgr&action=activitycannel
     * @author SUAL
     */
    function actionActivitycannel()
    {
    	$aLocation[0] = array('text'=>'促销活动列表','href' => url('marketmgr','activitylist'));
    	$iActivityId = isset($_GET["activityid"])&&is_numeric($_GET["activityid"]) ? intval($_GET["activityid"]) : 0;
    	if( $iActivityId <= 0 )
    	{
    		sysMessage( 'ID非法', 1, $aLocation );
    	}
    	$aActivity = new model_activity();
    	$iResult = $aActivity->activitysetStatus( $iActivityId, 2 );
    	switch( $iResult )
    	{
    		case -1:
    			sysMessage( '操作失败:活动不存在.', 1, $aLocation );
    			break;
    		case -2:
    			sysMessage( '操作失败:活动被删除.', 1, $aLocation );
    			break;
    		case -8:
    			sysMessage( '操作失败:状态不正确.', 1, $aLocation );
    			break;
    		case -9:
    			sysMessage( '操作失败:时间未到.', 1, $aLocation );
    			break;
    		case -10:
    			sysMessage( '操作失败:时间已过.', 1, $aLocation );
    			break;
    		default:
    			sysMessage( '操作成功', 0, $aLocation );
    			break;
    	}
    }


    
    /**
     * 活动题库分析
     * URL = ./?controller=marketmgr&action=activityinfolist
     * @author SAUL 090624
     */
    function actionActivityinfolist()
    {
    	$iActivityId = isset($_GET["activityid"])&&is_numeric($_GET["activityid"]) ? intval($_GET["activityid"]) : 0;
    	if( $iActivityId <=0 )
    	{
    		sysMessage( 'ID非法', 1 );
    	}
    	/* @var $oActivity model_activity */
    	$oActivity = A::singleton('model_activity');
    	$aActivity = $oActivity->activityGetOne( '*', "`activityid`='".$iActivityId."'" );
    	$oActivityInfo = new model_activityinfo();
    	$aActivityInfo = $oActivityInfo->activityInfoGetList('*', "`activityid`='".$iActivityId."'", '',0);
    	$aIds = array();
    	foreach($aActivityInfo as $aInfo)
    	{
    		$aIds[] = $aInfo["infoid"];
    	}	
    	$oActivityAnswer = new model_activityanswer();
    	if( empty($aIds) )
    	{
    		$aActivityAnswer = array();
    	}
    	else
    	{
    		$aActivityAnswer = $oActivityAnswer->activityAnswerGetList("*", "`infoid` in (".join(",",$aIds).")", "", 0);
    	}// 用户答案整理
    	$aAnswers = array();
    	foreach( $aActivityAnswer as $aAnswer)
    	{
    		$aAnswers[$aAnswer["infoid"]]["answer"][$aAnswer["userid"]] = explode(",",$aAnswer["answerid"]);
    		$aAnswers[$aAnswer["infoid"]]["text"][] = $aAnswer["answermsg"];
    	}
    	foreach($aActivityInfo as $iKey=>$aActicity)
    	{
    		$aActivityInfo[$iKey]['option'] = unserialize($aActicity['options']); 
    		unset($aActivityInfo[$iKey]['options']);
    		if(in_array($aActicity["type"],array(0,1)))
    		{ // 参数个数
    			$aActivityInfo[$iKey]['count']=count($aActivityInfo[$iKey]['option']);
    			if(isset($aActivityInfo[$iKey]['option']['other']))
    			{
    				$aActivityInfo[$iKey]['option']['other'] ='其他';
    			}// 参数内容
    			$aActivityInfo[$iKey]['args'] = join(",",$aActivityInfo[$iKey]['option']);				
    			foreach($aActivityInfo[$iKey]['option'] as $iTempKey )
    			{// 参数初始化
    				$iTemp[$iTempKey] = 0;
    			}
    			if( isset( $aAnswers[$aActicity['infoid']]["answer"] ) )
    			{
    				foreach($aAnswers[$aActicity['infoid']]["answer"] as $aValue)
    				{
    					foreach($aActivityInfo[$iKey]['option'] as $iTempKey )
    					{
    						if( in_array( $iTempKey, $aValue ) )
    						{
    							$iTemp[$iTempKey] += 1;
    						}
    					}
    				}
    			}
    			$aActivityInfo[$iKey]['value'] = join(",",$iTemp);
    			unset($iTemp);
    			if( !empty($aAnswers[$aActicity['infoid']]["text"][0]) )
    			{
    				$aActivityInfo[$iKey]['other'][] = $aAnswers[$aActicity['infoid']]["text"][0];
    			}
    		}
    		else
    		{
    			if( !empty($aAnswers[$aActicity['infoid']]["text"][0]) )
    			{
    				$aActivityInfo[$iKey]['other'][] = $aAnswers[$aActicity['infoid']]["text"][0];
    			}
    		}
    	}
    	$GLOBALS['oView']->assign( "aActivity",     $aActivity );
    	$GLOBALS['oView']->assign( "aActivityInfo", $aActivityInfo );
    	$GLOBALS['oView']->assign( "ur_here", "答题统计" );
    	$GLOBALS['oView']->assign( "actionlink", array('text'=>'促销活动列表','href'=>url('marketmgr','activitylist')));
    	$oActivityInfo->assignSysInfo();
    	$GLOBALS['oView']->display( "marketmgr_activityinfo_list.html" );
    	EXIT;
    }


    
    /**
     * 活动用户列表
     * URL = ./?controller=marketmgr&action=activityuserlist
     * @author SAUL
     */
    function actionActivityuserlist()
    {
    	$aLocation[0] = array('text'=>'促销活动列表', 'href'=>url('marketmgr','activitylist') );		
    	$iActicityId  = isset($_GET["activityid"])&&is_numeric($_GET["activityid"]) ? intval($_GET["activityid"]) : 0;
    	if( $iActicityId<= 0 )
    	{
    		sysMessage( '参数错误', 1, $aLocation );
    	}
    	$sCondition = "`activityid`='".$iActicityId."'";
    	$page = isset($_GET["p"])&&is_numeric($_GET["p"]) ? intval($_GET["p"]) : 1;		
    	$oActivityUser = new model_activityuser();
    	$aActivityUser = $oActivityUser->activityUserGetList('*',$sCondition,'',20,$page);
    	$GLOBALS['oView']->assign( "aActivityUser",$aActivityUser['results'] );
    	$oPage = new pages( $aActivityUser["affects"], 20, 10 );
    	$GLOBALS['oView']->assign( "activityid", $iActicityId );
    	$GLOBALS['oView']->assign( "pageinfo", $oPage->show() );
    	$GLOBALS['oView']->assign( "ur_here", "答题用户列表" );
    	$GLOBALS['oView']->assign( "actionlink", $aLocation[0]);
    	$oActivityUser->assignSysInfo();
    	$GLOBALS['oView']->display( "marketmgr_activityuser_list.html" );
    	EXIT;
    }


    
    /**
     * 增加活动用户
    .* URL = ./?controller=marketmgr&action=activityuseradd
     * @author SAUL
     */
    function actionActivityuseradd()
    {
    	$id = isset($_POST["ID"])&&is_numeric($_POST["ID"]) ? intval($_POST["ID"]) : -1;
    	if($id == -1)
    	{
    		sysMessage( '非法操作', 1 );
    	}
    	elseif( $id ==0 )
    	{ // 直接读取用户数据
    		$ids = isset($_POST["IDS"])?$_POST["IDS"]:"";
    		if( $ids == "")
    		{
    			sysMessage( '没有输入用户', 1 );
    		}
    		$aId = explode(",",$ids);				
    	}
    	else
    	{ //TODO 用户结果缓存利用
    		sysMessage('系统暂不支持', 1 );
    	}
    	$iActivityId = isset($_POST["activityid"])&&is_numeric($_POST["activityid"]) ? intval($_POST["activityid"]) : 0;
    	if( $iActivityId <=0 )
    	{
    		sysMessage( '参数错误', 1 );
    	}
    	$aLocation[0] = array("text"=>'促销活动用户列表','href'=>url('marketmgr','activityuserlist',array('activityid'=>$iActivityId)));
    	$iAppend = isset($_POST["append"])&&is_numeric($_POST["append"]) ?intval($_POST["append"]) : 0;
    	$oActivityUser = new model_activityuser();
    	$iResult = $oActivityUser->activityUserInsert( $iActivityId, $aId, ($iAppend==1) );
    	switch( $iResult )
    	{
    		case -1:
    			sysMessage( '参数错误', 1, $aLocation );
    			break;
    		case -2:
    			sysMessage( '活动不存在', 1, $aLocation );
    			break;
    		case -3:
    			sysMessage( '活动被删除', 1, $aLocation );
    			break;
    		case -4:
    			sysMessage( '活动被验证', 1, $aLocation );
    			break;
    		case -5:
    			sysMessage( '参数错误', 1, $aLocation );
    			break;
    		case -6:
    			sysMessage( '用户Id为空', 1, $aLocation );
    			break;
    		case -7:
    			sysMessage( '执行删除时候失败 ', 1, $aLocation);
    			break;
    		case -8:
    			sysMessage( '增加用户时候失败', 1, $aLocation );
    			break;
    		default:
    			sysMessage( '操作成功', 0, $aLocation );
    			break;					
    	}
    }



    /**
     * 删除促销活动用户
     * URL = ./?controller=marketmgr&action=activityuserdel
     * @author SAUL 090626
     */
    function actionActivityuserdel()
    {
    	$iActivityid = isset($_POST["activityid"])&&is_numeric($_POST["activityid"]) ? intval($_POST["activityid"]) : 0;
    	if( $iActivityid <= 0 )
    	{
    		sysMessage( 'ID非法', 1 );
    	}
    	$aUserId = isset($_POST["userid"])&&is_array($_POST["userid"]) ? $_POST["userid"] : array();
    	if( empty($aUserId) )
    	{
    		sysMessage( '没有提交数据', 1 );
    	}
    	$aLocation[0] = array('text'=>'促销活动用户列表','href'=>url('marketmgr','activityuserlist',array('activityid' => $iActivityid)));
    	$oActivityUser = new model_activityuser();
    	$iResult = $oActivityUser->activityUserDel( $aUserId, $iActivityid );
    	switch( $iResult )
    	{
    		case -1:
    			sysMessage( '操作失败:数据非法.', 1, $aLocation );
    			break;
    		case -2:
    			sysMessage( '操作失败:数据非法.', 1, $aLocation );
    			break;
    		case -3:
    			sysMessage( '操作失败:数据非法.', 1, $aLocation );
    			break;
    		case -4:
    			sysMessage( '操作失败:活动不存在.', 1, $aLocation );
    			break;
    		case -5:
    			sysMessage( '操作失败:活动已经验证,不允许删除.', 1, $aLocation );
    			break;
    		default:
    			sysMessage( '操作成功', 0, $aLocation );
    			break;
    	}
    }


    
    /**
     * 对活动进行审核查看
     * @author SAUL
     * URL = ./?controller=marketmgr&action=activityverifyview
     */
    function actionActivityverifyview()
    {
    	$id = isset($_GET["activityid"])&&is_numeric($_GET["activityid"])? intval($_GET["activityid"]):0;
    	if( $id==0 )
    	{
    		sysMessage( '参数错误', 1 );
    	}
    	$oActivity = new model_activity();
    	$oActivityInfo = new model_activityinfo();
    	$oActivityUser = new model_activityuser();
    	$aActivity = $oActivity->activityGetOne('*', "`activityid`='".$id."'");
    	$aActivityInfo = $oActivityInfo->activityInfoGetList('*', "`activityid`='".$id."'", "", 0 );
    	$aActivityUser = $oActivityUser->activityUserGetList('`usertree`.`username`',"`activityuser`.`activityid`='".$id."'","", 0);
    	//对活动的选项进行改变
    	foreach($aActivityInfo as $iKey=>$aInfo)
    	{
    		$aActivityInfo[$iKey]["option"]  = unserialize( $aInfo["options"] );
    		unset( $aActivityInfo[$iKey]["options"] );
    		$aActivityInfo[$iKey]["answers"] = explode( ",", $aInfo["answer"] );
    		unset( $aActivityInfo[$iKey]["answer"] );	
    	}
    	$GLOBALS['oView']->assign( "activityuser", $aActivityUser );
    	$GLOBALS['oView']->assign( "activityinfo", $aActivityInfo );
    	$GLOBALS['oView']->assign( "activity", $aActivity );
    	$GLOBALS['oView']->assign( "ur_here", "活动审核" );
    	$GLOBALS['oView']->assign( "actionlink", array("text"=>'促销活动列表', "href"=>url('marketmgr','activitylist')));
    	$oActivityUser->assignSysInfo();
    	$GLOBALS['oView']->display( "market_activityview.html" );
    	EXIT;
    }
}
?>