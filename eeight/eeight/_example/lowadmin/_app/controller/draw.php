<?php
/**
 * 文件 : /_app/controller/draw.php
 * 功能 : 控制器  - 开奖管理
 *
 * 功能:
 *    - action3d()          3d号码录入
 *    - actionp5()          P5号码录入
 *    - actionCompare()     方案比较
 *    - actionCancelbonus() 撤销派奖
 *    - actionRepeal()      系统撤单
 * 
 * 
 * @author    TOM, SAUL
 * @version   1.2.0
 * @package   lowadmin
 */
class controller_draw extends basecontroller
{
    /**
     * 3D号码录入
     * URL: ./index.php?controller=draw&action=3d
     * @author SAUL
     */
    function action3d()
    {
        $iLotteryId =1; //系统默认
        $aLocation[0] = array( "text"=>'3D号码录入',"href"=>url('draw','3d') );
        /* @var $oIssue model_issue */
        $oIssue = A::singleton("model_issueinfo");
        if( isset($_POST)&&!empty($_POST) )
        {
            //对号码进行更新
            $code    = isset($_POST["code"])&&!empty($_POST["code"]) ? daddslashes($_POST["code"]) : "";
            $issueid = isset($_POST["issueid"])&&is_numeric($_POST["issueid"]) ? intval($_POST["issueid"]) : 0;
            $issue   = isset($_POST["issue"])&&!empty($_POST["issue"]) ? daddslashes($_POST["issue"]):"";
            $iResult = $oIssue->issueUpdateNo($code, "`issueid`='".$issueid."' and `issue`='".$issue."'"
                    ." and `lotteryid`='".$iLotteryId."'");
            switch( $iResult )
            {
                case -1:
                    sysMessage( '操作失败:条件为空.', 1, $aLocation );
                    break;
                case -2:
                    sysMessage( '操作失败:用户错误.', 1, $aLocation );
                    break;
                case -3:
                    sysMessage( '操作失败:奖期不存在.', 1, $aLocation );
                    break;
                case -4:
                    sysMessage( '操作失败:号码状态为已验证.', 1, $aLocation );
                    break;
                case -5:
                    sysMessage( '操作失败:不能同时录入号码和审核号码.', 1, $aLocation );
                    break;
                case -6:
                    sysMessage( '操作失败:更新失败.', 1, $aLocation );
                    break;
                case -7:
                    sysMessage( '操作成功:审核完毕.', 0, $aLocation );
                    break;
                case -8:
                    sysMessage( '操作失败:号码审核不正确,更新失败.', 1, $aLocation );
                    break;
                case -9:
                    sysMessage( '号码审核不正确,需要重新输入号码.', 1, $aLocation );
                    break;
                case -10:
                    sysMessage( '操作失败:彩种不存在.', 1, $aLocation );
                    break;
                case -11:
                    sysMessage( '操作失败:还没有到彩种录号时间.', 1, $aLocation );
                    break;
                case -12:
                    sysMessage( '操作失败:彩种没有销售结束.', 1, $aLocation );
                    break;
                case -13:
                    sysMessage( '操作失败:号码格式不正确.', 1, $aLocation );
                    break;
                case -14:
                    sysMessage( '操作失败:对其他类型彩种的号码验证，暂不支持.', 1, $aLocation );
                    break;
                case -15:
                    sysMessage( '操作失败:录入号码失败.', 1, $aLocation );
                    break;
                case -16:
                    sysMessage( '操作失败:更新号码失败.', 1, $aLocation );
                    break;
                default:
                    sysMessage('操作成功.', 0, $aLocation );
                    break;
            }
        }
        else
        {
            $aLottery = $oIssue->IssueGetOne("A.`issueid`,A.`lotteryid`,A.`issue`,A.`statuscode`,"
                ."A.`saleend`,B.`edittime`,B.`lotterytype`,B.`numberrule`,B.`cnname`",
                "A.`lotteryid`='".$iLotteryId."' AND A.`salestart`<now() "
                ." ORDER BY A.`saleend` DESC limit 0,1",
                " LEFT JOIN `lottery` AS B ON (A.`lotteryid`=B.`lotteryid`) ");
            $aLottery["numberrule"] =@unserialize( $aLottery["numberrule"] );
            if( $aLottery["saleend"]<=date("Y-m-d H:i:s") )
            {
                 $sWhere = " A.`lotteryid`='".$iLotteryId."' AND  A.`salestart`<='".date("Y-m-d H:i:s",strtotime("+1 days"))."'";
            }
            else
            {
                $sWhere = " A.`lotteryid`='".$iLotteryId."' AND  A.`salestart`<='".date("Y-m-d H:i:s")."'";
            }
            $iPageRecord = isset($_GET["pn"])&&is_numeric($_GET["pn"]) ? intval($_GET["pn"]) : 25;
            $iPage = isset($_GET["p"])&&is_numeric($_GET["p"]) ? intval($_GET["p"]) : 1; 
            $aIssue = $oIssue->issueGetList("A.*,B.`cnname`", $sWhere, "order By A.`saleend` DESC",
                        $iPageRecord, $iPage );
            $oPage = new pages( $aIssue['affects'], $iPageRecord );
            $GLOBALS['oView']->assign( "aLottery",  $aLottery );
            $GLOBALS['oView']->assign( "ur_here",   "3D开奖" );
            $GLOBALS['oView']->assign( "aIssue",    $aIssue["results"] );
            $GLOBALS['oView']->assign( "pageinfo",  $oPage->show(1) );
            $GLOBALS['oView']->assign( "actionlink",$aLocation[0] );
            $GLOBALS['oView']->assign( "action",    "3d" );
            $oIssue->assignSysInfo();
            $GLOBALS['oView']->display("draw_info.html");
            EXIT;
        }
    }



    /**
     * P5号码录入
     * URL:./index.php?controller=draw&action=P5
     * @author SAUL
     */
    function actionp5()
    {
        $iLotteryId     = 2; //系统默认
        $aLocation[0]   = array("text"=>'P5号码录入',"href"=>url('draw','p5'));
        /* @var $oIssue model_issueinfo*/
        $oIssue = A::singleton("model_issueinfo");
        if( isset($_POST)&&!empty($_POST) )
        { //对号码进行更新
            $code    = isset($_POST["code"])&&!empty($_POST["code"]) ? daddslashes($_POST["code"]) : "";
            $issueid = isset($_POST["issueid"])&&is_numeric($_POST["issueid"]) ? intval($_POST["issueid"]):0;
            $issue   = isset($_POST["issue"])&&!empty($_POST["issue"]) ? daddslashes($_POST["issue"]) : "";
            $iResult = $oIssue->issueUpdateNo($code,"`issueid`='".$issueid."' AND `issue`='".$issue."'"
                    ." AND `lotteryid`='".$iLotteryId."'");
            switch( $iResult )
            {
                case -1:
                    sysMessage( '操作失败:条件为空.', 1, $aLocation );
                    break;
                case -2:
                    sysMessage( '操作失败:用户错误.', 1, $aLocation );
                    break;
                case -3:
                    sysMessage( '操作失败:奖期不存在.', 1, $aLocation );
                    break;
                case -4:
                    sysMessage( '操作失败:号码状态为已验证.', 1, $aLocation );
                    break;
                case -5:
                    sysMessage( '操作失败:不能同时录入号码和审核号码.', 1, $aLocation );
                    break;
                case -6:
                    sysMessage( '操作失败:更新失败.', 1, $aLocation );
                    break;
                case -7:
                    sysMessage( '操作成功:审核完毕.', 0, $aLocation );
                    break;
                case -8:
                    sysMessage( '操作失败:号码审核不正确,更新失败.', 1, $aLocation );
                    break;
                case -9:
                    sysMessage( '号码审核不正确,需要重新输入号码.', 1, $aLocation );
                    break;
                case -10:
                    sysMessage( '操作失败:彩种不存在.', 1, $aLocation );
                    break;
                case -11:
                    sysMessage( '操作失败:还没有到彩种录号时间.', 1, $aLocation );
                    break;
                case -12:
                    sysMessage( '操作失败:彩种没有销售结束.', 1, $aLocation );
                    break;
                case -13:
                    sysMessage( '操作失败:号码格式不正确.', 1, $aLocation );
                    break;
                case -14:
                    sysMessage( '操作失败:对其他类型彩种的号码验证，暂不支持.', 1, $aLocation );
                    break;
                case -15:
                    sysMessage( '操作失败:录入号码失败.', 1, $aLocation );
                    break;
                case -16:
                    sysMessage( '操作失败:更新号码失败.', 1, $aLocation );
                    break;
                default:
                    sysMessage( '操作成功.', 0, $aLocation );
                    break;
            }
        }
        else
        {
            $aLottery = $oIssue->IssueGetOne("A.`issueid`,A.`lotteryid`,A.`issue`,A.`saleend`,"
                ."A.`statuscode`,B.`edittime`,B.`lotterytype`,B.`numberrule`,B.`cnname`",
                "A.`lotteryid`='".$iLotteryId."' AND A.`salestart`<now()"
                ." ORDER BY A.`saleend` DESC LIMIT 0,1", 
                " LEFT JOIN `lottery` AS B ON (A.`lotteryid`=B.`lotteryid`) ");
            $aLottery["numberrule"] =@unserialize($aLottery["numberrule"]);
            if( $aLottery["saleend"]<=date("Y-m-d H:i:s") )
            {
                $sWhere = " A.`lotteryid`='".$iLotteryId."' AND  A.`salestart`<='".date("Y-m-d H:i:s",strtotime("+1 days"))."'";
            }
            else
            {
                $sWhere = " A.`lotteryid`='".$iLotteryId."' AND  A.`salestart`<='".date("Y-m-d H:i:s")."'";
            }
            $iPageRecord = isset($_GET["pn"])&&is_numeric($_GET["pn"]) ? intval($_GET["pn"]) : 25;
            $iPage       = isset($_GET["p"])&&is_numeric($_GET["p"]) ? intval($_GET["p"]) : 1; 
            $aIssue      = $oIssue->issueGetList("A.*,B.`cnname`",$sWhere,
                    "ORDER BY A.`saleend` DESC", $iPageRecord, $iPage );
            $oPage       = new pages($aIssue['affects'], $iPageRecord );
            $GLOBALS['oView']->assign( "aLottery",   $aLottery );
            $GLOBALS['oView']->assign( "ur_here",    "P5开奖" );
            $GLOBALS['oView']->assign( "aIssue",     $aIssue["results"] );
            $GLOBALS['oView']->assign( "pageinfo",   $oPage->show(1) );
            $GLOBALS['oView']->assign( "action",     "p5" );
            $GLOBALS['oView']->assign( "actionlink", $aLocation[0] );
            $oIssue->assignSysInfo();
            $GLOBALS['oView']->display("draw_info.html");
            EXIT;
        }
    }



    /**
     * 方案比较
     * URL: ./index.php?controller=draw&action=compare
     * @author SAUL
     */
    function actioncompare()
    {
        if( isset($_POST)&&!empty($_POST) )
        {
            $do = isset($_POST["do"])&&!empty($_POST["do"]) ? daddslashes($_POST["do"]) : "";
            if( $do=="getdata" )
            { //获取数据
                //判断是否时间已经到了
                $iLottery = $_POST["lotteryid"];
                $iIssue = $_POST["issue"];
                $sFile = "_data/projects/".$iLottery."_".$iIssue.".gz";
                if( file_exists( PDIR.DS.$sFile ) )
                {
                    $a["file"] = $sFile;
                    $a["str"]  = md5_file(PDIR.DS.$sFile);
                }
                else
                {
                    $a["file"] = "";
                    $a["str"]  = "";
                }
                echo json_encode( $a );
                EXIT;
            }
            elseif( $do=="checkData" )
            { //比较数据
                //两种方式:A:文件上传方式 ;B:文件字符串方式
                $aLocation[0] = array( "text"=>'方案比较', "href"=>url('draw','compare') );
                $iType = isset($_POST["type"])&&is_numeric($_POST["type"]) ? intval($_POST["type"]) : 0;
                if( $iType <= 0 || $iType >2 )
                {
                    sysMessage( "比较文件方式错误.", 1, $aLocation );
                }
                $iLottery = isset($_POST["lottery"])&&is_numeric($_POST["lottery"])
                            ? intval($_POST["lottery"]) : 0;
                if( $iLottery ==0 )
                { //彩种错误
                    sysMessage( "彩种错误", 1, $aLocation );
                }
                $sIssue = isset($_POST["issue"]) ? daddslashes($_POST["issue"]): "";
                if( $sIssue=="" )
                {
                    sysMessage( "彩种奖期错误", 1, $aLocation );
                }
                if( $iType == 1 )
                { //文件上传比较方式
                    if( is_uploaded_file($_FILES["bakfile"]["tmp_name"]) )
                    {
                        if($_FILES["bakfile"]["error"]<>0)
                        {
                            sysMessage( "文件上传失败,错误编号:".$_FILES["bakfile"]["error"].".", 1, $aLocation );
                        }
                        if($_FILES["bakfile"]["name"] <> $iLottery."_".$sIssue.".gz")
                        {
                            sysMessage( "上传的文件错误.",1, $aLocation );
                        }
                        $sCheckFile = $_FILES["bakfile"]["tmp_name"];
                    }
                    else
                    {
                        sysMessage( '文件不存在.', 1, $aLocation );
                    }
                }
                elseif( $iType == 2 )
                { //字符串比较
                    $sFileName = "_data".DS."projects".DS.$iLottery."_".$sIssue.".gz";
                    if( file_exists(PDIR.DS.$sFileName) )
                    {
                        if( md5_file(PDIR.DS.$sFileName)==$_POST["bakstr"] )
                        {
                            $sCheckFile = PDIR.DS.$sFileName;
                        }
                        else
                        {
                            sysMessage('字符串比较方式出错。', 1, $aLocation );
                        }
                    }
                    else
                    {
                        sysMessage('服务器上文件不存在,请使用文件上传方式.', 1, $aLocation );
                    }
                }
                //根据文件导入数据库
                /* @var $oProjects model_projects */
                $oProjects = A::singleton("model_projects");
                $iResult   = $oProjects->loadCheckDataFormFile( $sCheckFile );
                switch( $iResult )
                {
                    case 0:
                        sysMessage( '导入数据至核对库失败.',1 ,$aLocation );
                        break;
                    case 1:
                        sysMessage( '核对文件不存在.', 1, $aLocation );
                        break;
                    default:
                        if($iResult<0)
                        {
                            sysMessage("执行清空历史核对数据时候失败,错误编号:".-($iResult), 1, $aLocation );
                            break;
                        }
                }
                //根据奖期彩种进行对比得出最终结果需要的进行比较的数据项
                $aCheck = array(
                    "用户编号"      =>  "userid",
                    "追号方案"      =>  "taskid",
                    "游戏彩种"      =>  "lotteryid",
                    "游戏玩法"      =>  "methodid",
                    "奖期期号"      =>  "issue",
                    "是否被调价"    =>  "isdynamicprize",
                    "号码原复式"    =>  "code",
                    "单倍价格"      =>  "singleprice",
                    "倍数"          =>  "multiple",
                    "总共价格"      =>  "totalprice",
                    "总代ID"        =>  "lvtopid",
                    "总代返点"      =>  "lvtoppoint",
                    "一代ID"        =>  "lvproxyid",
                    "方案生成时间"  =>  "writetime",
                    "是否撤单"      =>  "iscancel",
                    "用户IP"        =>  "userip",
                    "服务器CDNIP"   =>  "cdnip",
                    "验证字符串"    =>  "hashvar"
                ); //需要比较的数据
                $mResult = $oProjects->checkProject( $iLottery, $sIssue, $aCheck );
                if( empty($mResult) )
                {
                    sysMessage( "方案核对结果:无差异.", 0, $aLocation );
                }
                if( isset($mResult["PTPC"]) )
                {
                    foreach( $mResult["PTPC"] as &$v )
                    {
                        if(isset($v["code"]))
                        {
                            $v["code"][0] = wordwrap(str_replace( "|", " ", $v["code"][0]), 70, "<br/>");
                            $v["code"][1] = wordwrap(str_replace( "|", " ", $v["code"][1]), 70, "<br/>");
                            
                        }
                    }
                }
                if( isset($mResult["PCTP"]) )
                {
                    foreach($mResult["PCTP"] as &$v)
                    {
                        if(isset($v["code"]))
                        {
                            $v["code"][0] = wordwrap(str_replace( "|", " ", $v["code"][0]), 70, "<br/>");
                            $v["code"][1] = wordwrap(str_replace( "|", " ", $v["code"][1]), 70, "<br/>");
                        }
                    }
                }
                $GLOBALS['oView']->assign( "ur_here",    "方案核对结果" );
                $GLOBALS['oView']->assign( "mResult",    $mResult );
                $GLOBALS['oView']->assign( "checkname",  array_flip($aCheck) );
                $GLOBALS['oView']->assign( "actionlink", $aLocation[0] );
                $oProjects->assignSysInfo();
                $GLOBALS['oView']->display( 'draw_compare_result.html' );
                EXIT;
            }
        }
        else
        {
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery"); 
            $aLottery = $oLottery->lotteryGetList( "`lotteryid`,`cnname`", "", "", 0 );
            /* @var $oIssue model_issueinfo */
            $oIssue   = A::singleton("model_issueinfo"); 
            foreach($aLottery as $lottery )
            {
                $aIssue[$lottery["lotteryid"]] = $oIssue->issueGetList("A.`issue`,date(A.saleend) AS endtime,"
                    ."(UNIX_TIMESTAMP(A.`saleend`) - UNIX_TIMESTAMP(now())) AS T ",
                    "A.`lotteryid`='".$lottery["lotteryid"]."' AND A.`salestart`<now()",
                    " date(A.saleend) DESC limit 0,2", 0 );
            }
            $GLOBALS['oView']->assign( "aLottery",  $aLottery );
            $GLOBALS['oView']->assign( "aIssue",    $aIssue );
            $GLOBALS['oView']->assign( "data_issue",json_encode($aIssue) );
            $GLOBALS['oView']->assign( "ur_here",   "方案比较" );
            $oLottery->assignSysInfo();
            $GLOBALS['oView']->display("draw_compare.html");
            EXIT;
        }
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
     * 撤销派奖
     * URL: ./index.php?controller=draw&action=cancelbonus
     * @author TOM
     */
    function actionCancelbonus()
    {
        $aLocation[0] = array( "text"=>"撤销派奖", "href"=>url('draw','cancelbonus') );
        if( isset($_POST) && !empty($_POST) )
        {
            $aIssueError = $_POST;
            /* @var $oIssueError model_issueerror */
            $oIssueError = A::singleton("model_issueerror");
            $iResult = $oIssueError->errorRecallInsert( $aIssueError );
            switch( intval($iResult) )
            {
                case -1:
                {
                    sysMessage( '提交数据错误,请仔细检查', 1, $aLocation );
                    EXIT;
                }
                case -2:
                {
                    sysMessage( '处理原因错误', 1, $aLocation );
                    EXIT;
                }
                case -3:
                {
                    sysMessage( '时间格式错误', 1, $aLocation );
                    EXIT;
                }
                case -4:
                {
                    sysMessage( '奖期数据获取错误', 1, $aLocation );
                    EXIT;
                }
                case -5:
                {
                    sysMessage( '输入官方提前开奖的时间无效', 1, $aLocation );
                    EXIT;
                }
                case -6:
                {
                    sysMessage( '新录入的号码规则错误', 1, $aLocation );
                    EXIT;
                }
                case -7:
                {
                    sysMessage( '已有相同任务尚未完成', 1, $aLocation );
                    EXIT;
                }
                case -8:
                {
                    sysMessage( '撤销期的下一期已经开售, 无法撤销派奖', 1, $aLocation );
                    EXIT;
                }
                case -9:
                {
                    sysMessage( '撤消派奖时间已过，无法撤销派奖', 1, $aLocation );
                    EXIT;
                }
                case -20:
                {
                    sysMessage( '撤消派奖时间的执行时间不正确.', 1, $aLocation );
                    EXIT;
                }
                case 0:
                {
                    sysMessage( '任务插入失败', 1, $aLocation );
                    EXIT;
                }
                default:
                {
                    sysMessage( '操作成功.', 0, $aLocation );
                    EXIT;
                }
            }
        }
        else
        {
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $aLottery = $oLottery->lotteryGetList( '`lotteryid`,`cnname`', '', '', 0 );
            /* @var $oIssue model_issueinfo */
            $oIssue = A::singleton("model_issueinfo");
            // 获取当前时间点上, 截止销售的最后一期
            foreach( $aLottery as $lottery )
            {
                $aIssue[$lottery["lotteryid"]] = $oIssue->issueGetList("A.`issue`,date(A.saleend) AS endtime ",
                "A.`lotteryid`='".$lottery["lotteryid"]."' AND A.`saleend` < now() ",
                " A.saleend DESC LIMIT 0,1", 0);
            }
            //获取时间限制
            /* @var $oConfig model_config */
            $oConfig = A::singleton("model_config");
            $aTempConfig = $oConfig->getConfigs( array("issueexceptiontime","cd_3dp5_repealtimerange") );
            //print_rr($aTempConfig);exit;
            $iTempLimitMinute = empty($aTempConfig['issueexceptiontime']) ? 60 : intval($aTempConfig['issueexceptiontime']);
            $sTempLimitRepeal = empty($aTempConfig['cd_3dp5_repealtimerange']) ? '20:30-23:59' : $aTempConfig['cd_3dp5_repealtimerange'];
            $GLOBALS['oView']->assign( "issueexceptiontime",   $iTempLimitMinute );
            $GLOBALS['oView']->assign( "issueexceptionrepeal",   $sTempLimitRepeal );
            $GLOBALS['oView']->assign( "aLottery",   $aLottery );
            $GLOBALS['oView']->assign( "data_issue", json_encode($aIssue) );
            $GLOBALS['oView']->assign( "ur_here",    "撤销派奖" );
            $GLOBALS['oView']->assign( "action",     url("draw","cancelbonus") );
            $oLottery->assignSysInfo();
            $GLOBALS['oView']->display("draw_cancelbonus.html");
            EXIT;
        }
    }



    /**
     * 系统撤单
     * URL: ./index.php?controller=draw&action=repeal
     * @author TOM
     */
    function actionRepeal()
    {
        $aLocation[0] = array( "text"=>"系统撤单", "href"=>url('draw','repeal') );
        if( isset($_POST) && !empty($_POST) )
        {
            $aIssueError = $_POST;
            /* @var $oIssueError model_issueerror */
            $oIssueError = A::singleton("model_issueerror");
            $iResult = $oIssueError->errorRecallInsert( $aIssueError );
            switch( intval($iResult) )
            {
                case -1:
                {
                    sysMessage( '提交数据错误,请仔细检查', 1, $aLocation );
                    break;
                }
                case -2:
                {
                    sysMessage( '处理原因错误', 1, $aLocation );
                    break;
                }
                case -3:
                {
                    sysMessage( '时间格式错误', 1, $aLocation );
                    break;
                }
                case -4:
                {
                    sysMessage( '奖期数据获取错误', 1, $aLocation );
                    break;
                }
                case -5:
                {
                    sysMessage( '输入官方提前开奖的时间无效', 1, $aLocation );
                    break;
                }
                case -6:
                {
                    sysMessage( '新录入的号码规则错误', 1, $aLocation );
                    break;
                }
                case -7:
                {
                    sysMessage( '已有相同任务尚未完成', 1, $aLocation );
                    break;
                }
                case -8:
                {
                    sysMessage( '撤单期的下一期已经开售, 无法系统撤单', 1, $aLocation );
                    break;
                }
                case -9:
                {
                    sysMessage( '撤消派奖时间已过，无法撤销派奖', 1, $aLocation );
                    break;
                }
                case -20:
                {
                    sysMessage( '撤消派奖时间的执行时间不正确.', 1, $aLocation );
                    EXIT;
                }
                case 0:
                {
                    sysMessage( '任务插入失败', 1, $aLocation );
                    break;
                }
                default:
                {
                    sysMessage( '操作成功.', 0, $aLocation );
                    break;
                }
            }
        }
        else
        {
            /* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            $aLottery = $oLottery->lotteryGetList( '`lotteryid`,`cnname`', '', '', 0 );
            /* @var $oIssue model_issueinfo */
            $oIssue   = A::singleton("model_issueinfo");
            // 获取当前时间点上, 截止销售的最后一期
            foreach($aLottery as $lottery)
            {
                $aIssue[$lottery["lotteryid"]] = $oIssue->issueGetList("A.`issue`,date(A.saleend) AS endtime ",
                "A.`lotteryid`='".$lottery["lotteryid"]."' AND A.`saleend` < now() ",
                " date(A.saleend) DESC limit 0,1", 0);
            }
            $GLOBALS['oView']->assign( "aLottery",   $aLottery );
            $GLOBALS['oView']->assign( "data_issue", json_encode($aIssue) );
            $GLOBALS['oView']->assign( "ur_here",    "系统撤单");
            $GLOBALS['oView']->assign( "action",     url("draw","repeal") );
            $oLottery->assignSysInfo();
            $GLOBALS['oView']->display("draw_repeal.html");
            EXIT;
        }
    }
}
?>