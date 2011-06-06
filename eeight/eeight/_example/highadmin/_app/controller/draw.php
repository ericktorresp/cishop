<?php
/**
 * 文件 : /_app/controller/draw.php
 *
 * @author    TOM, SAUL, Rojer
 * @version   1.3.0
 * @package   highadmin
 */
class controller_draw extends basecontroller
{
    static $lotteryTypes = array(
                    '0' => 'Digital',
                    '1' => '乐透分区型（蓝红球）',
                    '2' => 'Lotto',
                    '3' => '基诺型',
                    '4' => '排列型',
                    '5' => '分组型',
                );
    /**
     * 数字型开奖号码录入
     * URL: ./index.php?controller=draw&action=shuzi
     * @author SAUL,Rojer
     */
    function actionShuzi()
    {
        $aLotteryType = isset($_GET['lotteryType']) ? $_GET['lotteryType'] : 0;
        $iLotteryId = isset($_GET['lotteryId']) ? $_GET['lotteryId'] : 0;
        $oLottery = A::singleton("model_lottery");
        $aLotteries = self::array_spec_key($oLottery->getItems($aLotteryType), 'lotteryid');
        // 得到lotteryId
        if (!$iLotteryId)
        {
            $aLottery = reset($aLotteries);
            $iLotteryId = $aLottery['lotteryid'];
        }
        else
        {
            $aLottery = $aLotteries[$iLotteryId];
        }
        $aLocation[0] = array( "text"=>'数字型开奖',"href"=>url('draw','shuzi', array('lotteryId'=>$iLotteryId)) );
        /* @var $oIssueInfo model_issue */
        $oIssueInfo = A::singleton("model_issueinfo");

        if( isset($_POST)&&!empty($_POST) )
        {
            //对号码进行更新
            $code    = isset($_POST["code"])&&!empty($_POST["code"]) ? daddslashes($_POST["code"]) : "";
            $tmp = explode(',', $_POST["issueinfo"]);
            $issueid = $tmp[0];
            $issue = $tmp[1];

            $oConfig    = A::singleton("model_config");
            $aConfig = $oConfig->getConfigs( array("least_score", "person_score") );
            $iResult = $oIssueInfo->drawNumber($iLotteryId, $issue, $code, $aConfig['person_score'], $_SESSION["admin"]);

            if ($iResult === true)
            {
                sysMessage( 'It reached the request least rank, auto draw successfully！', 0, $aLocation);
            }
            else
            {
                sysMessage( "encode successed! rank = `$iResult`", 0, $aLocation);
            }
        }
        else
        {
            // 当前奖期
            //$currentIssue = $oIssueInfo->getCurrentIssue($iLotteryId);
            // 得到未开奖的奖期
            $lastNoDrawIssue = $oIssueInfo->getLastNoDrawIssue($iLotteryId);
            /*
            // 得到所在当天的所有奖期
            $tmp = end($aLottery['issueset']);
            $tmp = time2second($tmp['endtime']);
            if ( $tmp < 36000)
            {
                $tmp += 86400;
            }
            $tmp = strtotime(date('Y-m-d')) + $tmp;
             */
            
            //edit by Jack     抓号历史表取出权重值
            $oConfigValue = A::singleton("model_config");
            //取出配置文件里默认值
            $aConfigValue = $oConfigValue->getConfigs('least_score');

            $sWhere = " A.`lotteryid`='".$iLotteryId."' AND  A.`saleend`<='".date("Y-m-d H:i:s")."'";
            $iPageRecord = isset($_GET["pn"])&&is_numeric($_GET["pn"]) ? intval($_GET["pn"]) : 25;
            $iPage = isset($_GET["p"])&&is_numeric($_GET["p"]) ? intval($_GET["p"]) : 1; 
            $aIssue = $oIssueInfo->issueGetList("A.*,B.`cnname`", $sWhere, "order By A.`saleend` DESC",
                        $iPageRecord, $iPage );
            $aPartOfIssue = array_slice($aIssue["results"], 0, 10);

            $oPage = new pages( $aIssue['affects'], $iPageRecord );
            $GLOBALS['oView']->assign( "aLotteries",  $aLotteries );
            $aLottery['numberrule'] = unserialize($aLottery['numberrule']);
            $GLOBALS['oView']->assign( "aLottery",  $aLottery );
            $GLOBALS['oView']->assign( "lastNoDrawIssue",  $lastNoDrawIssue );
            $GLOBALS['oView']->assign( "lotteryType",  $aLotteryType );
            $GLOBALS['oView']->assign( "lotteryId",  $iLotteryId );
            $GLOBALS['oView']->assign( "ur_here",   "数字型开奖" );
            $GLOBALS['oView']->assign( "aIssue",    $aIssue["results"] );
            $GLOBALS['oView']->assign( "aPartOfIssue",    $aPartOfIssue );
            $GLOBALS['oView']->assign( "pageinfo",  $oPage->show(1) );
            $GLOBALS['oView']->assign( "actionlink",$aLocation[0] );
            $GLOBALS['oView']->assign( "action",    "shuzi" );
            $GLOBALS['oView']->assign( "configvalue", $aConfigValue );
            $oIssueInfo->assignSysInfo();
            $GLOBALS['oView']->display("draw_shuzi.html");
            EXIT;
        }
    }

    /**
     * 乐透同区型开奖
     * @author Rojer
     */
    public function actionLetouarea()
    {
        $aLotteryType = isset($_GET['lotteryType']) ? $_GET['lotteryType'] : 2;
        $iLotteryId = isset($_GET['lotteryId']) ? $_GET['lotteryId'] : 0;
        $oLottery = A::singleton("model_lottery");
        $aLotteries = self::array_spec_key($oLottery->getItems($aLotteryType), 'lotteryid');
        // 得到lotteryId
        if (!$iLotteryId)
        {
            $aLottery = reset($aLotteries);
            $iLotteryId = $aLottery['lotteryid'];
        }
        else
        {
            $aLottery = $aLotteries[$iLotteryId];
        }
        $aLocation[0] = array( "text"=>'乐透同区型开奖',"href"=>url('draw','letouarea', array('lotteryId'=>$iLotteryId)));
        /* @var $oIssueInfo model_issue */
        $oIssueInfo = A::singleton("model_issueinfo");

        if( isset($_POST)&&!empty($_POST) )
        {
            //对号码进行更新
            $code    = isset($_POST["code"])&&!empty($_POST["code"]) ? daddslashes($_POST["code"]) : "";
            $tmp = explode(',', $_POST["issueinfo"]);
            $issueid = $tmp[0];
            $issue = $tmp[1];

            $oConfig    = A::singleton("model_config");
            $aConfig = $oConfig->getConfigs( array("least_score", "person_score") );
            $iResult = $oIssueInfo->drawNumber($iLotteryId, $issue, $code, $aConfig['person_score'], $_SESSION["admin"]);

            if ($iResult === true)
            {
                sysMessage( 'It reached the request least rank, auto draw successfully！', 0, $aLocation);
            }
            else
            {
                sysMessage( "encode successed! rank = `$iResult`", 0, $aLocation);
            }
        }
        else
        {
            // 当前奖期
            //$currentIssue = $oIssueInfo->getCurrentIssue($iLotteryId);
            // 得到未开奖的奖期
            $lastNoDrawIssue = $oIssueInfo->getLastNoDrawIssue($iLotteryId);
            /*
            // 得到所在当天的所有奖期
            $tmp = end($aLottery['issueset']);
            $tmp = time2second($tmp['endtime']);
            if ( $tmp < 36000)
            {
                $tmp += 86400;
            }
            $tmp = strtotime(date('Y-m-d')) + $tmp;
             */

            $sWhere = " A.`lotteryid`='".$iLotteryId."' AND  A.`saleend`<='".date("Y-m-d H:i:s")."'";
            $iPageRecord = isset($_GET["pn"])&&is_numeric($_GET["pn"]) ? intval($_GET["pn"]) : 25;
            $iPage = isset($_GET["p"])&&is_numeric($_GET["p"]) ? intval($_GET["p"]) : 1;
            $aIssue = $oIssueInfo->issueGetList("A.*,B.`cnname`", $sWhere, "order By A.`saleend` DESC",
                        $iPageRecord, $iPage );
            $aPartOfIssue = array_slice($aIssue["results"], 0, 10);

            $oPage = new pages( $aIssue['affects'], $iPageRecord );
            $GLOBALS['oView']->assign( "aLotteries",  $aLotteries );
            $aLottery['numberrule'] = unserialize($aLottery['numberrule']);
            $GLOBALS['oView']->assign( "aLottery",  $aLottery );
            $GLOBALS['oView']->assign( "lastNoDrawIssue",  $lastNoDrawIssue );
            $GLOBALS['oView']->assign( "lotteryType",  $aLotteryType );
            $GLOBALS['oView']->assign( "lotteryId",  $iLotteryId );
            $GLOBALS['oView']->assign( "ur_here",   "乐透同区型开奖" );
            $GLOBALS['oView']->assign( "aIssue",    $aIssue["results"] );
            $GLOBALS['oView']->assign( "aPartOfIssue",    $aPartOfIssue );
            $GLOBALS['oView']->assign( "pageinfo",  $oPage->show(1) );
            $GLOBALS['oView']->assign( "actionlink",$aLocation[0] );
            $GLOBALS['oView']->assign( "action",    "letouarea" );
            $oIssueInfo->assignSysInfo();
            $GLOBALS['oView']->display("draw_shuzi.html");
            EXIT;
        }
    }

    /**
     * 基诺型开奖
     * @author Mark
     */
    public function actionJinuo()
    {
        $aLotteryType = isset($_GET['lotteryType']) ? $_GET['lotteryType'] : 3;
        $iLotteryId = isset($_GET['lotteryId']) ? $_GET['lotteryId'] : 0;
        $oLottery = A::singleton("model_lottery");
        $aLotteries = self::array_spec_key($oLottery->getItems($aLotteryType), 'lotteryid');
        // 得到lotteryId
        if (!$iLotteryId)
        {
            $aLottery = reset($aLotteries);
            $iLotteryId = $aLottery['lotteryid'];
        }
        else
        {
            $aLottery = $aLotteries[$iLotteryId];
        }
        $aLocation[0] = array( "text"=>'基诺型开奖',"href"=>url('draw','jinuo', array('lotteryId'=>$iLotteryId)));
        /* @var $oIssueInfo model_issue */
        $oIssueInfo = A::singleton("model_issueinfo");

        if( isset($_POST)&&!empty($_POST) )
        {
            //对号码进行更新
            $code    = isset($_POST["code"])&&!empty($_POST["code"]) ? daddslashes($_POST["code"]) : "";
            $tmp = explode(',', $_POST["issueinfo"]);
            $issueid = $tmp[0];
            $issue = $tmp[1];

            $oConfig    = A::singleton("model_config");
            $aConfig = $oConfig->getConfigs( array("least_score", "person_score") );
            $iResult = $oIssueInfo->drawNumber($iLotteryId, $issue, $code, $aConfig['person_score'], $_SESSION["admin"]);

            if ($iResult === true)
            {
                sysMessage( 'It reached the request least rank, auto draw successfully！', 0, $aLocation);
            }
            else
            {
                sysMessage( "encode successed! rank = `$iResult`", 0, $aLocation);
            }
        }
        else
        {
            // 当前奖期
            //$currentIssue = $oIssueInfo->getCurrentIssue($iLotteryId);
            // 得到未开奖的奖期
            $lastNoDrawIssue = $oIssueInfo->getLastNoDrawIssue($iLotteryId);
            /*
            // 得到所在当天的所有奖期
            $tmp = end($aLottery['issueset']);
            $tmp = time2second($tmp['endtime']);
            if ( $tmp < 36000)
            {
                $tmp += 86400;
            }
            $tmp = strtotime(date('Y-m-d')) + $tmp;
             */

            $sWhere = " A.`lotteryid`='".$iLotteryId."' AND  A.`saleend`<='".date("Y-m-d H:i:s")."'";
            $iPageRecord = isset($_GET["pn"])&&is_numeric($_GET["pn"]) ? intval($_GET["pn"]) : 25;
            $iPage = isset($_GET["p"])&&is_numeric($_GET["p"]) ? intval($_GET["p"]) : 1;
            $aIssue = $oIssueInfo->issueGetList("A.*,B.`cnname`", $sWhere, "order By A.`saleend` DESC",
                        $iPageRecord, $iPage );
            $aPartOfIssue = array_slice($aIssue["results"], 0, 10);

            $oPage = new pages( $aIssue['affects'], $iPageRecord );
            $GLOBALS['oView']->assign( "aLotteries",  $aLotteries );
            $aLottery['numberrule'] = unserialize($aLottery['numberrule']);
            $GLOBALS['oView']->assign( "aLottery",  $aLottery );
            $GLOBALS['oView']->assign( "lastNoDrawIssue",  $lastNoDrawIssue );
            $GLOBALS['oView']->assign( "lotteryType",  $aLotteryType );
            $GLOBALS['oView']->assign( "lotteryId",  $iLotteryId );
            $GLOBALS['oView']->assign( "ur_here",   "基诺型开奖" );
            $GLOBALS['oView']->assign( "aIssue",    $aIssue["results"] );
            $GLOBALS['oView']->assign( "aPartOfIssue",    $aPartOfIssue );
            $GLOBALS['oView']->assign( "pageinfo",  $oPage->show(1) );
            $GLOBALS['oView']->assign( "actionlink",$aLocation[0] );
            $GLOBALS['oView']->assign( "action",    "jinuo" );
            $oIssueInfo->assignSysInfo();
            $GLOBALS['oView']->display("draw_shuzi.html");
            EXIT;
        }
    }
    
    /**
     * 乐透分区型开奖
     * @author Rojer
     */
    public function actionLetoudiffarea()
    {
        echo '乐透分区型开奖待续。。。';
        die();
    }

    /**
     * 撤销派奖
     * 撤销派奖是因为按彩种和期号撤奖，
     * 避免官方开奖异常或开奖员开错奖的情况；
     * 按时间进行撤单用于处理官方提前开奖的情况。
     * 
     * By Tom:
     *   1, 某期(0902期3D) 改号 321 -> 123
     *   2, 更新历史号码表 321 -> 123
     *   3, 奖金扣回: 根据 projects 方案. 扣钱写账变 (忽略资金为负数)
     *   4, Project 与 123|321 相关号码, 全部改为 '未判断中奖' ,'未派奖状态'
     *   5, (并同时考虑追号表. 封锁表. 销量表 )
     *   6, 重新判断中奖, 重新派奖. 重新生成追号任务
     *   7, 撤单时间必须早于下期开售时间
     * 
     */
    /**
     * 生成撤销派奖记录
     * URL: ./index.php?controller=draw&action=cancelbonus
     * @author Rojer
     */
    function actionCancelbonus()
    {
        $aLocation[0] = array( "text"=>"撤销派奖", "href"=>url('draw','cancelbonus') );
        $sa = isset($_POST['sa']) ? $_POST['sa'] : NULL;
        $oIssueInfo   = A::singleton("model_issueinfo");
        $oConfig = A::singleton("model_config");
        $aTempConfig = $oConfig->getConfigs( array("issueexceptiontime") );
        $issueexceptiontime = empty($aTempConfig['issueexceptiontime']) ? 60 : intval($aTempConfig['issueexceptiontime']);
        
        // 检查权限
        $oAdminuser = A::singleton("model_adminuser");
        $privileges = array(
                array('controller'=>'draw', 'actioner'=>'exceptionforward'),
                array('controller'=>'draw', 'actioner'=>'exceptionerrorcode'),
                array('controller'=>'draw', 'actioner'=>'exceptionnocode'),
            );
        if (!$ownerPrivileges = $oAdminuser->checkAdminPrivilege($_SESSION['admin'], $privileges))
        {
            sysMessage('您没有权限！', 1, $aLocation);
        }
        switch ($sa)
        {
            case 'getIssue':
                $iLotteryId = $_POST['lotteryId'];
                $lastNoDrawIssue = $oIssueInfo->getLastNoDrawIssue($iLotteryId);
                $aIssue = $oIssueInfo->getItems($iLotteryId, '', strtotime("-$issueexceptiontime minutes"), 0, 0, time(), 'issueid DESC');
                die(json_encode($aIssue));
                break;
            case 'cancelBonus':
                $lotteryId = isset($_POST['lottery']) ? intval($_POST['lottery']) : 0;
                $issue = isset($_POST['issue']) ? $_POST['issue'] : '';
                $type = isset($_POST['type']) ? intval($_POST['type']) : 0;
                if ($lotteryId <= 0 || !preg_match('`^\d+[\d-]+$`', $issue) || $type <= 0)
                {
                    sysMessage('参数不完整', 1);
                }

                $aIssueError = $_POST;
                if(!isset($_POST['lotteryType']) || $_POST['lotteryType'] < 0)
                {
                    sysMessage('彩种类型不正确！', 1, $aLocation);
                }
                $aIssueError['lotteryType'] = $_POST['lotteryType'];
                if(!isset($_POST['lottery']) || $_POST['lottery'] < 0 )
                {
                    sysMessage('彩种不正确！', 1, $aLocation);
                }
                $aIssueError['lottery'] = $_POST['lottery'];
                if(!isset($_POST['issue']) || $_POST['issue'] == '' )
                {
                    sysMessage('奖期不正确！', 1, $aLocation);
                }
                $aIssueError['issue'] = $_POST['issue'];
                if(!isset($_POST['type']) || $_POST['type'] == '' ||  !in_array($_POST['type'],array(1,2,3)) )
                {
                    sysMessage('处理原因不正确！', 1, $aLocation);
                }
                $aIssueError['type'] = intval($_POST['type']);
                if($aIssueError['type'] == 1 )
                {
                    if(!isset($_POST['starttime']) || $_POST['starttime'] == '')
                    {
                        sysMessage('请输入官方实际开奖时间！', 1, $aLocation);
                    }
                    else 
                    {
                        $aIssueError['starttime'] = $_POST['starttime'];
                    }
                }
                if($aIssueError['type'] == 2 )
                {
                    if(!isset($_POST['issueno']) || $_POST['issueno'] == '')
                    {
                        sysMessage('请输入正确开奖号码！', 1, $aLocation);
                    }
                    else 
                    {
                        $aIssueError['issueno'] = $_POST['issueno'];
                    }
                }
                /* @var $oIssueInfoError model_issueerror */
                $oIssueInfoError = A::singleton("model_issueerror");
                if ($iResult = $oIssueInfoError->errorRecallInsert($aIssueError))
                {
                    sysMessage('撤销派奖成功！', 0, $aLocation);
                }
                else
                {
                    sysMessage('撤销派奖出错', 1, $aLocation);
                }
                break;
            default:
                $oLottery = A::singleton("model_lottery");
                $tmp = $oLottery->getItems();
                // 获取当前时间点上, 截止销售的最后一期
                foreach($tmp as $v)
                {
                    $aLottery[$v["lotterytype"]][$v['lotteryid']] = $v;
                }
                $GLOBALS['oView']->assign( "issueexceptiontime",   $issueexceptiontime );
                $GLOBALS['oView']->assign( "lotteries", $aLottery);
                $GLOBALS['oView']->assign( "json_lotteries", json_encode($aLottery));
                $GLOBALS['oView']->assign( "lotteryTypes", self::$lotteryTypes);
                $GLOBALS['oView']->assign( "ownerPrivileges",    $ownerPrivileges );
                $GLOBALS['oView']->assign( "ur_here",    "提交" );
                $GLOBALS['oView']->assign( "action",     url("draw","cancelbonus") );
                $oLottery->assignSysInfo();
                $GLOBALS['oView']->display("draw_cancelbonus.html");
                break;
        }
        EXIT;
    }

    function actionDetect()
    {
//        $aLocation[0] = array( "text"=>"抓号检测", "href"=>url('draw','history') );
        $oLottery   = A::singleton("model_lottery", $GLOBALS['aSysDbServer']['report']);
        $oIssueInfo = A::singleton("model_issueinfo", $GLOBALS['aSysDbServer']['report']);
        $oConfig    = A::singleton("model_config", $GLOBALS['aSysDbServer']['report']);
        
        $lotteries  = self::array_spec_key($oLottery->getItems(), 'lotteryid');
        $config     = $oConfig->getConfigs( array("least_score", "person_score") );
        $errors = $drawInfos = array();
        foreach( $lotteries as $v )
        {
            if( !$tmp = $oIssueInfo->getLastNoDrawIssue($v['lotteryid']) )
            {
                $tmp = $oIssueInfo->getLastIssue($v['lotteryid']);
            }
            $tmp['cnname']      = $v['cnname'];
            $tmp['least_score'] = $config['least_score'];
            $drawInfos[$v['lotteryid']] = $tmp;
            if( $tmp['statusfetch'] == 2 )
            {
                if( $tmp['rank'] < $config['least_score'] )
                {
                    $errors[] = $tmp;
                }
            }
        }
        /*
        $oDrawsource = A::singleton("model_drawsource");
        $lastHistories = $oDrawsource->getLastHistories();
        $errors = array();
        foreach ($lastHistories as $v)
        {
            if (!$v['errno'])
            {
                continue;
            }
            
            switch ($v['errno'])
            {
                case 11:
                    $v['errstr'] = "暂不支持的网站！";
                    break;
                case 14:
                    $v['errstr'] = "开奖号码不一致错误！";
                    break;
                case 21:
                    $v['errstr'] = "网站不可访问！";
                    break;
                case 22:
                    $v['errstr'] = "内容解析错误！";
                    break;
                case 31:
                    $v['errstr'] = "未抓到号码，可能因为延迟开奖，请手动开号！";
                    break;
                default:
                    $v['errstr'] = "其他错误！";
                    break;
            }
            $errors[] = $v;
        }
        // 错误提示
        foreach ($errors as $v)
        {
            //echo "<script>alert('错误信息：{$v['errstr']}\\n错误代码：{$v['errno']}');</script>";
        }
         * 
         */
        if( !empty($_POST['flag']) && $_POST['flag'] == 'ajax' )
        {
            echo json_encode(array( 'errors'=>$errors,
                                    'data' => $drawInfos ));
            exit;
        }
//        $GLOBALS['oView']->assign( "lotteries", $lotteries);
        $GLOBALS['oView']->assign( "errors", $errors);
//        $GLOBALS['oView']->assign( "lotteryTypes", self::$lotteryTypes);
        $GLOBALS['oView']->assign( "drawInfos", $drawInfos);
        $GLOBALS['oView']->assign( "config", $config);
        $GLOBALS['oView']->assign( "ur_here",    "AAWN Monitor" );
        //$GLOBALS["oView"]->assign( "actionlink",    array('text'=>'开奖源管理', 'href'=>url('draw','drawsource')));
        $oLottery->assignSysInfo();
        $GLOBALS['oView']->display("draw_detect.html");
        EXIT;
    }

    /**
     * 开奖历史
     */
    function actionHistory()
    {
        $aLocation[0] = array( "text"=>"开奖历史", "href"=>url('draw','history') );
        $oIssueInfo   = A::singleton("model_issueinfo");
        $oDrawsource = A::singleton("model_drawsource");

        $lotteryId = isset($_GET['lotteryId']) ? intval($_GET['lotteryId']) : 0;
        $oLottery = A::singleton("model_lottery");
        $tmp = self::array_spec_key($oLottery->getItems(), 'lotteryid');
        if (!$lotteryId)
        {
            $tmp2 = reset($tmp);
            $lotteryId = $tmp2['lotteryid'];
        }

        $lotteries = array();
        foreach($tmp as $v)
        {
            $lotteries[$v["lotterytype"]][$v['lotteryid']] = $v;
        }

        $sources = $oDrawsource->getItems($lotteryId);

        $pageRecord = isset($_GET["pn"])&&is_numeric($_GET["pn"]) ? intval($_GET["pn"]) : 25;
        $page = isset($_GET["p"])&&is_numeric($_GET["p"]) ? intval($_GET["p"]) : 1;
        $tmp = $oDrawsource->getHistories($lotteryId, $pageRecord, $page);
        $oPage = new pages( $tmp['affects'], $pageRecord );
        $issues = $histories = array();
        foreach ($tmp['results'] as $v)
        {
            $v['His'] = substr($v['date'], 11);
            $v['totalRetryTimes'] = $v['retry']+1;
            $histories[$v['issue']]['detail'][] = $v;
            $v['rank']=$v['number'] != 0 ? $v['rank'] : 0 ;
            if( !isset( $histories[$v['issue']]['totalrank'] ) )
            {
            	$histories[$v['issue']]['totalrank'] = 0;
            }
       		$histories[$v['issue']]['totalrank'] += $v['rank'];
            $issues[] = $v['issue'];
        }
        $issueInfos = $oIssueInfo->getItemsByIssue($lotteryId, $issues);
        
        $GLOBALS['oView']->assign( "histories", $histories );
        $GLOBALS['oView']->assign( "sources",   $sources );
        $GLOBALS['oView']->assign( "cols",   count($sources) + 3);
        $GLOBALS['oView']->assign( "lotteries", $lotteries);
        $GLOBALS['oView']->assign( "lotteryTypes", self::$lotteryTypes);
        $GLOBALS['oView']->assign( "lotteryId", $lotteryId);
        $GLOBALS['oView']->assign( "issueInfos", $issueInfos);
        $GLOBALS['oView']->assign( "pageinfo",  $oPage->show(1));
        $GLOBALS['oView']->assign( "action",    "history" );
        $GLOBALS['oView']->assign( "ur_here",    "AAWN History" );
        $GLOBALS["oView"]->assign( "actionlink",    array('text'=>'开奖源管理', 'href'=>url('draw','drawsource')));
        $oLottery->assignSysInfo();
        $GLOBALS['oView']->display("draw_history.html");
        EXIT;
    }

    /**
     * 开奖源管理
     * 可能要在lottery表中增加一字段：是否自动开奖
     */
    function actionDrawsource()
    {
        $aLocation[0] = array( "text"=>"开奖源管理", "href"=>url('draw','drawsource') );
        $oIssueInfo   = A::singleton("model_issueinfo");
        $oDrawsource = A::singleton("model_drawsource");
        $sa = isset($_POST['sa']) ? $_POST['sa'] : '';
        switch ($sa)
        {
            case 'batch':
                if (!$deleteItem = $_POST['deleteItem'])
                {
                    sysMessage('非法参数', 1, $aLocation);
                }
                $enabled = $_POST['enabled'] == 1 ? 1 : 0;
                //edit by jack
                $unabled = $enabled == 1 ? 0 : 1;
                foreach ($deleteItem as $v)
                {
                    foreach ($oDrawsource->getItems($v,$unabled) as $vv)
                    {
                        $oDrawsource->updateItem($vv['id'], array('enabled' => $enabled));
                    }
                }
                break;
        }

        $tmp = $oDrawsource->getItems(0, NULL);
        $sources = $sourceNums = array();
        foreach ($tmp as $v)
        {
            if (!isset($sourceNums[$v['lotteryid']]))
            {
                $sourceNums[$v['lotteryid']] = 1;
            }
            else
            {
                $sourceNums[$v['lotteryid']]++;
            }
            $sources[$v['lotteryid']][$v['id']] = $v;
        }

        $oLottery = A::singleton("model_lottery");
        $originalLotteries = self::array_spec_key($oLottery->getItems(), 'lotteryid');
        // 获取当前时间点上, 截止销售的最后一期
        $aLottery = array();
        foreach($originalLotteries as $v)
        {
            $aLottery[$v["lotterytype"]][$v['lotteryid']] = $v;
        }

        $GLOBALS['oView']->assign( "sources",   $sources );
        $GLOBALS['oView']->assign( "originalLotteries",   $originalLotteries );
        $GLOBALS['oView']->assign( "lotteries", $aLottery);
        $GLOBALS['oView']->assign( "sourceNums", $sourceNums);
        $GLOBALS['oView']->assign( "json_lotteries", json_encode($aLottery));
        $GLOBALS['oView']->assign( "lotteryTypes", self::$lotteryTypes);
        $GLOBALS['oView']->assign( "ur_here",    "号源网站列表" );
        $GLOBALS["oView"]->assign( "actionlink2", array('text'=>'增加开奖源', 'href'=>url('draw','newdrawsource')));
        $GLOBALS["oView"]->assign( "actionlink", array("text"=>"返回开奖历史", "href"=>url('draw','history')));
        $oLottery->assignSysInfo();
        $GLOBALS['oView']->display("draw_drawsource.html");
        EXIT;
    }
    
    function actionNewdrawsource()
    {
        $aLocation[0] = array( "text"=>"开奖源管理", "href"=>url('draw','drawsource') );
        $sa = isset($_POST['sa']) ? $_POST['sa'] : NULL;
        $oIssueInfo   = A::singleton("model_issueinfo");
        $oDrawsource = A::singleton("model_drawsource");

        switch ($sa)
        {
            case 'addItem':
                $lotteryId = intval($_POST['lottery']);
                $name = trim($_POST['name']);
                $url = trim($_POST['url']);
                $needlogin = isset($_POST['needlogin']) ? intval($_POST['needlogin']) : 0;
                $loginname = trim($_POST['loginname']);
                $loginpwd = trim($_POST['loginpwd']);
                $refresh = isset($_POST['refresh']) ? intval($_POST['refresh']) : 0;
                $interface = isset($_POST['interface']) ? intval($_POST['interface']) : 0;
                $rank = 0;  //intval($_POST['rank'])
                if ($iResult = $oDrawsource->addItem($lotteryId, $name, $url, $needlogin, $loginname, $loginpwd, $refresh, $interface, $rank))
                {
                    sysMessage('增加成功！', 0, $aLocation);
                }
                else
                {
                    sysMessage('增加出错', 1, $aLocation);
                }
                break;
            default:
                $oLottery = A::singleton("model_lottery");
                $tmp = self::array_spec_key($oLottery->getItems(), 'lotteryid');
                // 获取当前时间点上, 截止销售的最后一期
                $aLottery = array();
                foreach($tmp as $v)
                {
                    $aLottery[$v["lotterytype"]][$v['lotteryid']] = $v;
                }
                $GLOBALS['oView']->assign( "originalLotteries",   $tmp );
                $GLOBALS['oView']->assign( "lotteries", $aLottery);
                $GLOBALS['oView']->assign( "json_lotteries", json_encode($aLottery));
                $GLOBALS['oView']->assign( "lotteryTypes", self::$lotteryTypes);
                $GLOBALS['oView']->assign( "ur_here",    "增加开奖源" );
                $GLOBALS['oView']->assign( "form_action", 'new');
                $GLOBALS["oView"]->assign( "actionlink",    array('text'=>'开奖源列表', 'href'=>url('draw','drawsource')));
                $oLottery->assignSysInfo();
                $GLOBALS['oView']->display("draw_newdrawsource.html");
                break;
        }
        EXIT;
    }

    function actionEditdrawsource()
    {
        $aLocation[0] = array( "text"=>"开奖源管理", "href"=>url('draw','drawsource') );
        $sa = isset($_POST['sa']) ? $_POST['sa'] : NULL;
        $oIssueInfo   = A::singleton("model_issueinfo");
        $oDrawsource = A::singleton("model_drawsource");
        
        switch ($sa)
        {
            case 'updateItem':
                $id = $_POST['id'];
                $lotteryId = intval($_POST['lottery']);
                $name = trim($_POST['name']);
                $url = trim($_POST['url']);
                $needlogin = isset($_POST['needlogin']) ? intval($_POST['needlogin']) : 0;
                $loginname = trim($_POST['loginname']);
                $loginpwd = trim($_POST['loginpwd']);
                $refresh = isset($_POST['refresh']) ? intval($_POST['refresh']) : 0;
                $interface = isset($_POST['interface']) ? intval($_POST['interface']) : 0;
                $data = array('lotteryid' => $lotteryId, 'name' => $name, 'url' => $url, 'needlogin' => $needlogin, 'loginname' => $loginname,
                    'loginpwd' => $loginpwd, 'refresh' => $refresh, 'interface' => $interface);
                if ($iResult = $oDrawsource->updateItem($id, $data))
                {
                    sysMessage('修改成功！', 0, $aLocation);
                }
                else
                {
                    sysMessage('修改出错', 1, $aLocation);
                }
                break;
            default:
                $id = $_GET['id'];
                $source = $oDrawsource->getItem($id);
                $oLottery = A::singleton("model_lottery");
                $tmp = self::array_spec_key($oLottery->getItems(), 'lotteryid');
                // 获取当前时间点上, 截止销售的最后一期
                $aLottery = array();
                foreach($tmp as $v)
                {
                    $aLottery[$v["lotterytype"]][$v['lotteryid']] = $v;
                }
                $GLOBALS['oView']->assign( "source",   $source );
                $GLOBALS['oView']->assign( "originalLotteries",   $tmp );
                $GLOBALS['oView']->assign( "lotteries", $aLottery);
                $GLOBALS['oView']->assign( "json_lotteries", json_encode($aLottery));
                $GLOBALS['oView']->assign( "lotteryTypes", self::$lotteryTypes);
                $GLOBALS['oView']->assign( "ur_here",    "编辑开奖源" );
                $GLOBALS['oView']->assign( "form_action", 'edit');
                $GLOBALS["oView"]->assign( "actionlink",    array('text'=>'开奖源列表', 'href'=>url('draw','drawsource')));
                $oLottery->assignSysInfo();
                $GLOBALS['oView']->display("draw_newdrawsource.html");
                break;
        }
        EXIT;
    }

    /**
     * 修改权重
     */
    function actionEditrank()
    {
        $aLocation[0] = array( "text"=>"开奖源管理", "href"=>url('draw','drawsource') );
        $sa = isset($_POST['sa']) ? $_POST['sa'] : NULL;
        $oIssueInfo   = A::singleton("model_issueinfo");
        $oDrawsource = A::singleton("model_drawsource");
        
        switch ($sa)
        {
            case 'updateItem':
                $id = $_POST['id'];
                $rank = intval($_POST['rank']);
                if ($rank < 1 || $rank > 100)
                {
                    sysMessage('权重值不正确：1<rank<100', 1, $aLocation);
                }
                $data = array('rank' => $rank);
                if ($iResult = $oDrawsource->updateItem($id, $data))
                {
                    sysMessage('修改成功！', 0, $aLocation);
                }
                else
                {
                    sysMessage('修改出错', 1, $aLocation);
                }
                break;
            default:
                $id = $_GET['id'];
                if (!$source = $oDrawsource->getItem($id, NULL))
                {
                    sysMessage('数据不存在', 1, $aLocation);
                }
                $oLottery = A::singleton("model_lottery");
                $lottery = $oLottery->getItem($source['lotteryid']);
                
                $GLOBALS['oView']->assign( "source",   $source );
                $GLOBALS['oView']->assign( "lottery", $lottery);
                $GLOBALS['oView']->assign( "lotteryTypes", self::$lotteryTypes);
                $GLOBALS['oView']->assign( "ur_here",    "设置权重" );
                $GLOBALS['oView']->assign( "form_action", 'edit');
                $GLOBALS["oView"]->assign( "actionlink",    array('text'=>'开奖源列表', 'href'=>url('draw','drawsource')));
                $oLottery->assignSysInfo();
                $GLOBALS['oView']->display("draw_editrank.html");
                break;
        }
        EXIT;
    }

    function actionSwitchsource()
    {
        $aLocation[0] = array( "text"=>"开奖源管理", "href"=>url('draw','drawsource') );
        $sa = isset($_POST['sa']) ? $_POST['sa'] : NULL;
        $oIssueInfo   = A::singleton("model_issueinfo");
        $oDrawsource = A::singleton("model_drawsource");

        switch ($sa)
        {
            default:
                $id = $_GET['id'];
                $enabled = isset($_GET['enabled']) && $_GET['enabled'] == 'true' ? 1 : 0;
                if (!$source = $oDrawsource->getItem($id, NULL))
                {
                    sysMessage('数据不存在', 1, $aLocation);
                }

                $data = array('enabled' => $enabled);
                if ($iResult = $oDrawsource->updateItem($id, $data))
                {
                    sysMessage('修改成功！', 0, $aLocation);
                }
                else
                {
                    sysMessage('修改出错', 1, $aLocation);
                }
                break;
        }
        EXIT;
    }

    /**
     * 测试最新抓号
     * @param <type> $sourcId
     */
    function actionTestsource()
    {
        $sourcId = intval($_GET['id']);
        $oDrawsource = A::singleton("model_drawsource");
        if (!$source = $oDrawsource->getItem($sourcId, NULL))
        {
            $result = array('errno' => 1, 'errstr' => '找不到开奖源');
            die(json_encode($result));
        }

        if (!$source['interface'])
        {
            $result = array('errno' => 1, 'errstr' => '接口未实现，无法测试！');
            die(json_encode($result));
        }

        $oLottery = A::singleton("model_lottery");
        if (!$lottery = $oLottery->getItem($source['lotteryid']))
        {
            $result = array('errno' => 1, 'errstr' => '找不到对应彩种');
            die(json_encode($result));
        }

        $oDrawsource = A::singleton("model_drawsource");
        try
        {
            $expectedDate = date("Y-m-d");
            $result = $oDrawsource->fetchFromURL($lottery, $source['url'], $expectedDate);
            $result += array('url' => $source['url'], 'cnname' => $lottery['cnname']);
            //die(json_encode($result));
            //sysMessage('抓取成功！'.var_export($result, true), 0);
            //die("<script>alert('抓取成功!\\n 源：{$source['url']}\\n彩种：{$lottery['cnname']}\\n奖期：{$result['issue']}\\n号码：{$result['number']}\\n耗时：{$result['time']}秒\\n');</script>");
        }
        catch (Exception $e)
        {
            switch(substr($e->getCode(), 0, 1))
            {
                case 1:
                    $errstr = "Error:".$e->getMessage();
                    break;
                case 2:
                    $errstr = "Warning:".$e->getMessage();
                    break;
                case 3:
                    $errstr = "Notice:".$e->getMessage();
                    break;
                default:
                    $errstr = "Unknown:".$e->getMessage();
                    break;
            }
            $result = array('errno' => 1, 'errstr' => $errstr);
            die(json_encode($result));
        }
    }

    /**
     * 重置奖期，这个功能仅供调试用
     * UPDATE `issueinfo` SET `code` = '', `rank` = '0', `statusfetch` = '0', `statuscode` = '0' WHERE lotteryid=3 && issue= '20100623-045' LIMIT 1;
     */
    function actionResetdrawissue()
    {
        $aLocation[0] = array( "text"=>"复位", "href"=>url('draw','resetdrawissue') );
        $lotteryId = intval($_POST['lotteryId']);
        $issue = trim($_POST['issue']);
        $oIssueInfo   = A::singleton("model_issueinfo");
        $result = array('errno' => 0, 'msg' => '');
        if (!$issueInfo = $oIssueInfo->getItem(0, $issue, $lotteryId))
        {
            $result = array('errno' => 1, 'errstr' => '不存在的奖期');
        }
        else
        {
            if (!$oIssueInfo->updateItem($issueInfo['issueid'], array('code' => '', 'rank' => '0', 'statusfetch' => '0', 'statuscode' => '0')))
            {
                $result = array('errno' => 1, 'errstr' => '更新错误');
            }
            else
            {
                $result = array('errno' => 0);
            }
        }

        die(json_encode($result));
    }

    /**
     * 删除一个源
     */
    function actionDeletesource()
    {
        $aLocation[0] = array( "text"=>"开奖源管理", "href"=>url('draw','drawsource') );
        $sa = isset($_POST['sa']) ? $_POST['sa'] : NULL;
        $oIssueInfo   = A::singleton("model_issueinfo");
        $oDrawsource = A::singleton("model_drawsource");

        switch ($sa)
        {
            default:
                $id = $_GET['id'];
                if (!$source = $oDrawsource->getItem($id, NULL))
                {
                    sysMessage('数据不存在', 1, $aLocation);
                }

                if ($iResult = $oDrawsource->deleteItem($id))
                {
                    sysMessage('删除成功！', 0, $aLocation);
                }
                else
                {
                    sysMessage('删除出错', 1, $aLocation);
                }
                break;
        }
        EXIT;
    }

    /**
     * 
     * 奖期顺延管理
     * URL: ./index.php?controller=draw&action=delayissue
     * @author Rojer
     *
     */
    public function actionDelayissue()
    {
        //$aLocation[0] = array( "text"=>'游戏信息列表', "href"=>url('gameinfo','list') );
        $sa = isset($_POST['sa']) ? $_POST['sa'] : NULL;
        $oLottery = A::singleton("model_lottery");
        
        switch ($sa)
        {
            case 'getIssue':
                $iLotteryId = isset($_POST["lotteryId"]) ? intval($_POST["lotteryId"]) : 0;
                if (!$lottery = $oLottery->getItem($iLotteryId))
                {
                    sysMessage( "操作失败:获取彩种信息失败.", 1, $aLocation );
                }

                $oIssueInfo = A::singleton("model_issueinfo");
                $issues = $oIssueInfo->getItems($iLotteryId, date('Y-m-d'), time());
                
                // 延期历史
                $oIssueDelay = A::singleton("model_issuedelay");
                $issueDelays = $oIssueDelay->getItems($iLotteryId);

                $GLOBALS['oView']->assign( "ur_here", "请选择要延后的奖期范围" );
                $GLOBALS['oView']->assign( "lottery", $lottery );
                $GLOBALS['oView']->assign( "issues", $issues );
                $GLOBALS['oView']->assign( "issueDelays", $issueDelays );
                //$GLOBALS['oView']->assign( "actionlink", $aLocation );
                $oLottery->assignSysInfo();
                $GLOBALS['oView']->display( "draw_delayissue_getissue.html" );
                break;
            case 'setDelay': // 设置延迟
                $iLotteryId = isset($_POST["lotteryId"]) ? intval($_POST["lotteryId"]) : 0;
                if (!$aLottery = $oLottery->getItem($iLotteryId))
                {
                    sysMessage( "操作失败:获取彩种信息失败.", 1 );
                }
                $startIssue = $_POST['startIssue'];
                $endIssue = $_POST['endIssue'];
                $delay = intval($_POST['delay']);

                $oIssueDelay = A::singleton("model_issuedelay");
                $oIssueDelay->addItem($iLotteryId, $startIssue, $endIssue, $delay);
                
                $oIssueInfo = A::singleton("model_issueinfo");
                // 如果不需要关注是否与下期时间重叠，则直接更新奖期
                if ($ar = $oIssueInfo->delayIssueTime($iLotteryId, $startIssue, $endIssue, $delay))
                {
                    sysMessage("延迟奖期成功");
                }
                else
                {
                    sysMessage("没有数据被更新", 1);
                }

                break;
            default:
                // 彩种类型(0:数字类型，1:乐透分区型(蓝红球)，2:乐透同区型，3:基诺型，4:排列型，5:分组型)
                $lotteriesTypes = array();
                foreach ($oLottery->getItems() as $v)
                {
                    $lotteriesTypes[$v['lotterytype']][$v['lotteryid']] = $v;
                }
                $GLOBALS['oView']->assign( "ur_here", "奖期延迟" );
                $GLOBALS['oView']->assign( "lotteryTypes", self::$lotteryTypes );
                $GLOBALS['oView']->assign( "json_lotteriesTypes", json_encode($lotteriesTypes ));
                //$GLOBALS['oView']->assign( "actionlink", $aLocation );
                $oLottery->assignSysInfo();
                $GLOBALS['oView']->display( "draw_delayissue.html" );
                EXIT;
                break;
        }
        EXIT;
    }

    // 以下应放到公用函数库
    static private function array_spec_key($array, $key, $unset_key = false)
    {
        if (empty($array) || !is_array($array))
        {
            return array();
        }

        $new_array = array();
        foreach ($array AS $value)
        {
            if (!isset($value[$key]))
            {
                continue;
            }
            $value_key = $value[$key];
            if ($unset_key === true)
            {
                unset($value[$key]);
            }
            $new_array[$value_key] = $value;
        }

        return $new_array;
    }

    // 以下应放到公用函数库
    function time2second($str)
    {
        $tmp = explode(':', $str);
        return $tmp[0] * 3600 + $tmp[1] * 60 + $tmp[2];
    }

    function second2time($second)
    {
        $result['hour'] = intval($second / 3600);
        $second -= $result['hour'] * 3600;
        $result['minute'] = intval($second / 60);
        $result['second'] = $second - $result['minute'] * 60;

        return $result['hour'] . ':' . $result['minute'] . ':' . $result['second'];
    }
}
?>