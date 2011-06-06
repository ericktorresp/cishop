<?php
/**
 * 文件 : /_app/model/checkbonus.php
 * 功能 : 数据模型 - 中奖判断
 * 
 * @author     tom,mark
 * @version    1.0.0
 * @package    highgame    
 */

class model_checkbonus extends basemodel
{
    // ---------------------------------[ 属性 ]-------------------------------------------
    // 如果 $iStepCounts, $iStepSec 任何一个为 0, 则不做任何限制, 程序将不会进入 SLEEP 状态
    private $iStepCounts = 0;     // [配合] 每次处理 n 条后, PHP 睡眠 $this->iStepSec 秒
    private $iStepSec    = 0;     // [配合] 每次处理$this->$iStepCounts 条后, PHP 睡眠 n 秒

    private $iTotalProcessed = 0; // 本次 '中奖判断' 的执行, 影响 projects 记录数
    private $bLoopMode = FALSE;   // 主要用于内部测试, 遇到无方案的奖期的情况,不中断程序.继续循环执行 
    private $iRunTimes = 1;      // 主要用于内部测试, 遇到无方案的奖期的情况,不中断程序.继续循环执行

    private $sCode      = '';     // 开奖号码
    private $sIssue     = '';     // 奖期名字  issueinfo.issue
    private $sIssueId   = 0;      // 奖期编号  issueinfo.issueid
    private $iLotteryId = 0;      // 彩种ID


    // ---------------------------------[ 方法 ]-------------------------------------------
    /**
     * 根据彩种ID, 对方案进行真实扣款
     * @param  int $iLotteryId
     * @return mix
     */
    public function doCheckBonus( $iLotteryId )
    {
        $this->iLotteryId = intval($iLotteryId);

        // 1, 判断彩种ID 的有效性
        $oLottery   = A::singleton("model_lottery");
        $aRes = $oLottery->lotteryGetOne( ' `lotteryid` ', " `lotteryid` = '$this->iLotteryId' " );
        if( empty($aRes) )
        {
            return -1001; // 彩种ID错误
        }
        unset($aRes,$oLottery);


        // 2, 获取需处理的奖期信息 ( From Table.`IssueInfo` )
        //     2.1  录入开奖号码并已验证的   statuscode = 2
        //     2.2  未完整执行中奖判断的     statuscheckbonus != 2
        //     2.3  符合当前彩种ID的         lotteryid = $iLotteryId
        //     2.4  已停售的                 saleend < 当前时间
        //     2.5  录入的开奖号码不为空     code != ''
        //     2.6  判断当期的追号单是否已经全部转为注单 statustasktoproject = 2
        //     2.7  为了按时间顺序线性执行,取最早一起符合以上要求的  ORDER BY A.`saleend` ASC
        $oIssue       = A::singleton("model_issueinfo");
        $sCurrentTime = date( "Y-m-d H:i:s", time() );
        $sFileds      = " A.`issueid`, A.`issue`, A.`code` ";
        $sCondition   = " A.`statuscode`=2 AND A.`statuscheckbonus`!=2 AND A.`lotteryid`='".$this->iLotteryId
                        ."' AND A.`saleend`<'".$sCurrentTime."' AND `code`!='' AND `statustasktoproject` = 2 "
                        ." ORDER BY A.`saleend` ASC";
        $aRes         = $oIssue->IssueGetOne( $sFileds, $sCondition );
        unset($oIssue,$sFileds,$sCondition);
        if( empty($aRes) )
        {
            return -1002; // 未获取到需要进行'中奖判断'的奖期号 (所有奖期的'中奖判断'皆以完成)
        }
        $this->sIssue   = $aRes['issue'];            // 需进行 '中奖判断' 的首个奖期编号
        $this->sIssueId = intval($aRes['issueid']);  // 奖期表的自增ID号
        $this->sCode    = $aRes['code'];             // 奖期的开奖号码

        if( empty($this->sIssue) || empty($this->sIssueId) || 0==strlen($this->sCode) )
        {
            return -1003; // 数据无效
        }


        /**
         * 3, 保证异常处理的优先级高于任何其他 Cli {真实扣款|集中返点|中奖判断|奖金派发}
         * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
         *    1, 更新 projects.'返奖状态' 之前, 先检测异常表(IssueError) 数据
         *       如果有值, 则将本期 issueInfo.statuscheckbonus=2 并退出 CLI. 等待异常完整处理完毕
         */
        $this->oDB->query("SELECT 1 FROM `issueerror` WHERE `lotteryid`='$this->iLotteryId' "
                          . " AND `issue`='$this->sIssue' "
                          . " AND ( `statuscancelbonus` NOT IN (2,9) OR `statusrepeal` NOT IN (2,9) ) ");
        if( 1 == $this->oDB->ar() )
        {
            // 标记为未执行, CLI 退出 (让异常CLI优先运行)
            $this->oDB->update( "issueinfo", array( 'statuscheckbonus' => 0 ), 
                        " `statuscheckbonus`!=2 AND `issueid`=$this->sIssueId" );
            return -1008;
        }


        // 4, 获取所有尚未 '中奖判断' 当期方案中的玩法ID,中奖判断函数名
        //     3.1  根据奖期号    $this->sIssue
        //     3.2  彩种ID        $this->iLotteryId
        //     3.3  未中奖判断的  isgetprize != 1
        $aRes        = $this->oDB->getAll( "SELECT p.`methodid`, p.`codetype`, m.`functionname`, m.`maxcodecount` "
                        . " FROM `projects` p LEFT JOIN `method` m ON(p.`methodid`=m.`methodid`) " 
                        . " WHERE p.`issue`='$this->sIssue' AND p.`lotteryid`='$this->iLotteryId' "
                        . " AND p.`isgetprize`=0 GROUP BY p.`methodid`,p.`codetype`");
        $iCounts     = count($aRes);  // 获取需处理 '中奖判断' 的玩法ID
        $sDebugMsg = "[d] [".date('Y-m-d H:i:s')."] Issue='$this->sIssue', LotteryId='$this->iLotteryId', Distinct(Methodid)='$iCounts' ";
        echo $sDebugMsg."\n";


        // 5, 如果获取的结果集为空, 则表示当前奖期已完成全部'中奖判断'.更新状态值
        if( 0 == $iCounts )
        {
            $this->oDB->update( "issueinfo", array( 'statuscheckbonus' => 2 ), " `issueid`=$this->sIssueId"  );
            if( 1 != $this->oDB->ar() )
            {
                return -3001; // 更新奖期状态值失败
            }
            if( $this->bLoopMode == TRUE || --$this->iRunTimes > 0 )
            {
                return $this->doCheckBonus( $this->iLotteryId ); // 奖期无需中奖判断的递归
            }
            else
            {
                return -1004; // 当前奖期 '中奖判断' 已经全部完成
            }
        }
        else 
        { // 奖期标记设置为: 进行'中奖判断'中.
            $this->oDB->update( "issueinfo", array( 'statuscheckbonus' => 1 ),  " `issueid`=$this->sIssueId " );
        }


        /** 
         * 6, [循环] 对当前彩种的所有未进行中奖判断玩法, 执行中奖判断 
         * 消息类型
         *   [n]   表示 notice 错误, 并不重要
         *   [w]   表示 warnning 错误, 重要!!! 
         *   [d]   表示 debug 消息
        */
        for( $i=0; $i<$iCounts; $i++ )
        {
            // 1, 数据检查
            if( empty($aRes[$i]['methodid']) || empty($aRes[$i]['functionname']) )
            {
                echo "[w] [".date('Y-m-d H:i:s')."] Data Init Error! LotteryId='".$this->iLotteryId
                    ."' Methodid='".$aRes[$i]['methodid']."' funName='".$aRes[$i]['functionname'].
                    "' Code='$this->sCode' \n";
                return -1005;
            }
            //获取最长号码长度:如有类似北京快乐八任选七的特殊玩法在此处进行处理
            switch ($aRes[$i]['functionname'])
            {
                case 'bjkl_rx7'://北京快乐八任选七单独处理
                    if( $aRes[$i]['maxcodecount'] >= 7 )
                    {
                        $sSql = " SELECT MAX(CHAR_LENGTH(`code`)) AS maxcodecount   FROM `projects` WHERE `issue`='$this->sIssue' AND `lotteryid`='$this->iLotteryId' ";
                        $sSql .= " AND `isgetprize`=0 AND `methodid`='".$aRes[$i]['methodid']."'";
                        $aGetMaxCode = $this->oDB->getOne($sSql);
                        if(empty($aGetMaxCode))
                        {
                            echo "[w] [".date('Y-m-d H:i:s')."] Data Init Error! LotteryId='".$this->iLotteryId
                            ."' Methodid='".$aRes[$i]['methodid']."' Code='$this->sCode' \n";
                            return -1005;
                        }
                        $iCodeMaxCount = ($aGetMaxCode['maxcodecount']-2)/3+1;
                        //取当前期此种玩法的最长号码长度
                        $aRes[$i]['maxcodecount'] = $iCodeMaxCount > $aRes[$i]['maxcodecount'] ? $iCodeMaxCount : $aRes[$i]['maxcodecount'];
                    }
                    break;
                default:
                    break;
            }
            // 2, 开始业务流程操作, 将开奖号码, 玩法
            if( ($iFlag=$this->doProcess( $aRes[$i] )) <= 0 )
            { // 业务流程执行失败
                // 如果受影响行数为0 (或无人中奖), 则显示 notice 错误, 继续执行
                echo "[n] [".date('Y-m-d H:i:s')."] LotteryId='".$this->iLotteryId
                    ."' Methodid='".$aRes[$i]['methodid']."' funName='".$aRes[$i]['functionname'].
                    "' Code='$this->sCode' Skiped\n";
                continue;
            }
            else
            { // 业务流程执行成功
                $this->iTotalProcessed += intval($iFlag);
            }

            // 一些参数判断
            if( $this->iStepCounts !=0 && $this->iStepSec != 0 )
            {
                if( $this->iStepCounts == $i+1 )
                {
                    echo "[d] sleep for $this->iStepSec sec\n";
                    sleep( $this->iStepSec );
                }
            }
        }
        // 奖期标记设置为: 进行'中奖判断'完成.
        //$this->oDB->update( "issueinfo", array( 'statuscheckbonus' => 2 ), " `issueid`=$this->sIssueId"  );
        
        if( $this->bLoopMode == TRUE || --$this->iRunTimes > 0 )
        {
            $this->doCheckBonus( $this->iLotteryId ); // 递归
        }

        // 6, 返回负数表示错误, 正数表示本次 CLI 执行受影响的方案数
        return $this->iTotalProcessed;
    }


    /**
     * 处理业务流程操作,  中奖判断
     * $aDatas = array(
     *      [0] => array(
     *          [methodid] => 22,
     *          [functionname] => defaultfun
     *      )
     * );
     * @return mix 返回更新中奖状态所影响的行数, 错误返回负数
     */
    private function doProcess( $aDatas = array() )
    {
        echo "[d] " . '__fun__'.$aDatas['functionname'] . "\n";
        // 1, 判断玩法中奖函数是否已定义.
        if( TRUE != $this->checkFunctionExists($aDatas['functionname']) )
        {
            echo "[w] [".date('Y-m-d H:i:s')."] Function not Exists! LotteryId='".$this->iLotteryId
                    ."' Methodid='".$aDatas['methodid']."' funName='".$aDatas['functionname'].
                    "' Code='$this->sCode' Ignored\n";
            return -1006;
        }

        // 调用中奖判断方法, 并传递 methodid
        $iTotalAffected = $this->{'__fun__'.$aDatas['functionname']}( $aDatas );
        if( FALSE === $iTotalAffected )
        { // 全等于 FALSE 表示更新有失败
            echo "[w] [".date('Y-m-d H:i:s')."] Function Process Failed! LotteryId='".$this->iLotteryId
                    ."' Methodid='".$aDatas['methodid']."' funName='".$aDatas['functionname'].
                    "' Code='$this->sCode' To Be Continue\n";
            return -1007;
        }
        return $iTotalAffected;
    }





    // ---------------------------[ 中奖方法定义 ]------------------------------------
    /**
     * 中奖判断函数编写规则:
     * 依赖属性如下:
     *      $this->sIssue      =>  当前奖期号
     *      $this->iLotteryId  =>  当前彩种ID
     *      $this->sCode       =>  官方开奖号码.  ssc为3位, P5为5位
     */

    /**
     * 根据枚举的 TagName, 当前彩种ID, 获取中奖或未中奖相应的SQL正则表达式
     * @param string  $sTagName   
     * @param bool    $bIsBingo   TRUE=获取中奖正则, FALSE=未中奖正则
     * @param string  $sCode      用于正则判断的号码, 根据玩法的不同,传递的位数也不同
     *                             例: 定位胆 只传递1未数字(可能是0) 所以不能做 empty 判断
     */
    public function makeRegExp( $sTagName, $bIsBingo=TRUE, $sCode='', $sField='code', $sCodeType = 'digital', $iMaxCodeCount = 0 )
    {
        if( empty($sTagName) || !in_array($bIsBingo,array(TRUE,FALSE)) )
        {
            return '';
        }
        $sRegExp = '';
        switch ( $sTagName )
        {
            // 3位数字.直选   [号码全展开] ---------------------------------------
            case 'n3_zhixuan' :  
            {
                if( 3 != strlen($sCode) )
                {
                    return '';
                }
                if($sCodeType == 'digital')
                {//复式
                    // 对数字进行拆分位
                    $aNumber[0] = substr($sCode,0,1);
                    $aNumber[1] = substr($sCode,1,1);
                    $aNumber[2] = substr($sCode,2,1);
                    $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT');
                    $sRegExp .= ' REGEXP "^.*'.$aNumber[0].'.*\\\|.*'.$aNumber[1].'.*\\\|.*'.$aNumber[2].'.*$" ';
                }
                elseif ($sCodeType == 'input')
                {//单式
                    $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .' REGEXP "'.$sCode.'" ';
                }
                else 
                {
                    return '';
                }
                BREAK;
            }
            //3位直选和值[号码不展开]-------------------------------
            case 'n3_zhixuanhezhi' :  
            {
                $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .
                        ' REGEXP "(^'.$sCode.'$)|(^'.$sCode.'\\\|)|(\\\|'.$sCode.'\\\|)|(\\\|'.$sCode.'$)" ';
                BREAK;
            }
            // 3位数字.混合组选   [号码全展开] -----------------------------------
            case 'n3_hunhezuxuan' :  
            {
                if( 3 != strlen($sCode) )
                {
                    return '';
                }
                // 对数字进行排序
                $aNumber[0] = substr($sCode,0,1);
                $aNumber[1] = substr($sCode,1,1);
                $aNumber[2] = substr($sCode,2,1);
                sort($aNumber);
                $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .' REGEXP "'.implode( '', $aNumber).'" ';
                BREAK;
            }
            // 2位数字.大小单双   "^.*[SA].*\\|.*[SD].*$" -------------------------
            case 'n2_dxds' : 
            {
                if( 2 != strlen($sCode) )
                {
                    return '';
                }
                static $aBSAD = array(    // 大小单双对应号码
                         '0' => array(5,6,7,8,9),  // 大
                         '1' => array(0,1,2,3,4),  // 小
                         '2' => array(1,3,5,7,9),  // 单
                         '3' => array(0,2,4,6,8)   // 双
                );
                $iFristNumber  = substr($sCode,0,1);
                $iSecondNumber = substr($sCode,1,1);
                $sFristString  = '';
                $sSecondString = '';
                foreach( $aBSAD AS $k=>$v )
                {
                    $sFristString  .= in_array( $iFristNumber,  $v ) ? $k : '';
                    $sSecondString .= in_array( $iSecondNumber, $v ) ? $k : '';
                }
                unset( $iFristNumber, $iSecondNumber );
                $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .
                        ' REGEXP "^.*['.$sFristString.'].*\\\|.*['.$sSecondString.'].*$" ';
                BREAK;
            }

            // 3位数字.一码不定位   projects.code = 1|2|3|4|5|6|7...
            case 'n1_budingwei' :
            {
                if( 3 != strlen($sCode) )
                {
                    return '';
                }
                $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .' REGEXP "['.$sCode.']" ';
                BREAK;
            }

            // 3位数字.二码不定位   projects.code = 134567 
            case 'n2_budingwei' :  
            {
                if( 3 != strlen($sCode) )
                {
                    return '';
                }
                $aNumber[0] = substr($sCode,0,1);
                $aNumber[1] = substr($sCode,1,1);
                $aNumber[2] = substr($sCode,2,1);
                $aNumber = array_unique($aNumber);
                sort($aNumber);
                if( 1 == count($aNumber) )
                { // 如果本期开奖为豹子, 全部未中奖
                    $sRegExp = (TRUE==$bIsBingo) ? ' AND 0 ' : ' AND 1 ';
                    BREAK;
                }
                $aRegString = '';
                if( 2 == count($aNumber) )
                { // 组3号: 从小至大排序后, 取前2个唯一的数字  
                    $aRegString =  '.*'.$aNumber[0] .'.*'. $aNumber[1] .'.*';
                }
                if( 3 == count($aNumber) )
                { // 组6号: 从小至大排序后, 所有数字排列
                    $aRegString .=  '.*'.$aNumber[0] .'.*'. $aNumber[1] .'.*';
                    $aRegString .=  '|.*'.$aNumber[0] .'.*'. $aNumber[2] .'.*';
                    $aRegString .=  '|.*'.$aNumber[1] .'.*'. $aNumber[2] .'.*';
                }
                $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .' REGEXP "'.$aRegString.'" ';
                BREAK;
            }

             // 3位数字.组三  projects.code = 0123456789
            case 'n3_zusan' : 
            {
                if( 3 != strlen($sCode) )
                {
                    return '';
                }
                $aNumber[0] = substr($sCode,0,1);
                $aNumber[1] = substr($sCode,1,1);
                $aNumber[2] = substr($sCode,2,1);
                $aTmp = array_unique($aNumber);
                if( 2 != count($aTmp) )
                { // 本期开奖结果不为组3号, 则本期全部组三玩法未中奖.
                    $sRegExp = (TRUE==$bIsBingo) ? ' AND 0 ' : ' AND 1 ';
                    BREAK;
                }
                // 开奖结果为组3号, 则中奖判断
                sort($aTmp);
                $aRegString =  '.*'.$aTmp[0] .'.*'. $aTmp[1] .'.*';
                $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .' REGEXP "'.$aRegString.'" ';
                BREAK;
            }

            // 3位数字.组六  projects.code = 0123456789
            case 'n3_zuliu' :
            {
                if( 3 != strlen($sCode) )
                {
                    return '';
                }
                $aNumber[0] = substr($sCode,0,1);
                $aNumber[1] = substr($sCode,1,1);
                $aNumber[2] = substr($sCode,2,1);
                $aTmp = array_unique($aNumber);
                if( 3 != count($aTmp) )
                { // 本期开奖结果不为组6号, 则本期全部组三玩法未中奖.
                    $sRegExp = (TRUE==$bIsBingo) ? ' AND 0 ' : ' AND 1 ';
                    BREAK;
                }
                // 开奖结果为组6号, 则中奖判断
                sort($aTmp);
                $aRegString  =  '.*'.$aTmp[0] .'.*'. $aTmp[1] .'.*'. $aTmp[2] .'.*';
                $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .' REGEXP "'.$aRegString.'" ';
                BREAK;
            }

            // 2位数字.直选  projects.code = 0123|789
            case 'n2_zhixuan' :
            {
                if( 2 != strlen($sCode) )
                {
                    return '';
                }
                if($sCodeType == 'digital')
                {//复式
                    // 对数字进行拆分位
                    $iFristNumber  = substr($sCode,0,1);
                    $iSecondNumber = substr($sCode,1,1);
                    $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .
                        ' REGEXP "^.*'.$iFristNumber.'.*\\\|.*'.$iSecondNumber.'.*$" ';
                }
                elseif ($sCodeType == 'input')
                {//单式
                    $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .' REGEXP "'.$sCode.'" ';
                }
                else 
                {
                    return '';
                }
                BREAK;
            }

            // 2位数字.组选  projects.code = 0123456789
            case 'n2_zuxuan' : 
            {
                if( 2 != strlen($sCode) )
                {
                    return '';
                }
                $aNumber[0]  = substr($sCode,0,1);
                $aNumber[1] = substr($sCode,1,1);
                $aTmp = array_unique($aNumber);
                if( 2 != count($aTmp) )
                { // 本期开奖结果不为组选号, 则本期全部组选玩法未中奖.
                    $sRegExp = (TRUE==$bIsBingo) ? ' AND 0 ' : ' AND 1 ';
                    BREAK;
                }
                sort($aNumber);
                if($sCodeType == 'digital')
                {//复式
                    $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .
                        ' REGEXP ".*'.$aNumber[0].'.*'.$aNumber[1].'.*" ';
                }
                 elseif ($sCodeType == 'input')
                {//单式
                    $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .' REGEXP "'.$aNumber[0].$aNumber[1].'" ';
                }
                else 
                {
                    return '';
                }
                BREAK;
            }

            // 1位数字.定胆  projects.code = 0|1|2|3|4|5|6|7|8|9
            case 'n1_dan' :
            {
                if( 1 != strlen($sCode) )
                {
                    return '';
                }
                $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .' REGEXP "'.$sCode.'" ';
                BREAK;
            }

            // 3位数字.组选和值  projects.code = 7|9|17|18|19|20   and code REGEXP "(^3$)|(^3\\|)|(\\|3\\|)|(\\|3$)"
            case 'n3_hezhi' : 
            {
                if( 3 != strlen($sCode) )
                {
                    return '';
                }
                $aNumber[0] = substr($sCode,0,1);
                $aNumber[1] = substr($sCode,1,1);
                $aNumber[2] = substr($sCode,2,1);
                if( $aNumber[0] == $aNumber[1] && $aNumber[1] == $aNumber[2] )
                { // 本期开奖为豹子号, 则本期全部组选和值未中奖.
                    $sRegExp = (TRUE==$bIsBingo) ? ' AND 0 ' : ' AND 1 ';
                    BREAK;
                }
                $iCode = $aNumber[0] + $aNumber[1] + $aNumber[2];
                $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .
                        ' REGEXP "(^'.$iCode.'$)|(^'.$iCode.'\\\|)|(\\\|'.$iCode.'\\\|)|(\\\|'.$iCode.'$)" ';
                BREAK;
            }
            /************************************乐透型玩法**************************************/
            //乐透三位直选---------------------------------------------------
            case 'lotto_n3_zhixuan':
            {
                $aCode = explode(" ", $sCode);
                if( 3 != count($aCode))
                {
                    return '';
                }
                if($sCodeType == 'digital')
                {//复式
                    $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT');
                    $sRegExp .= ' REGEXP "^.*'.$aCode[0].'.*\\\|.*'.$aCode[1].'.*\\\|.*'.$aCode[2].'.*$" ';
                }
                elseif ($sCodeType == 'input')
                {//单式
                    $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .' REGEXP "'.$sCode.'" ';
                }
                else
                {
                    return '';
                }
                BREAK;
            }
            //乐透三位组选---------------------------------------------------
            case 'lotto_n3_zhuxuan':
            {
                $aCode = explode(" ", $sCode);
                if( 3 != count($aCode))
                {
                    return '';
                }
                sort($aCode);
                if($sCodeType == 'digital')
                {//复式
                    $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT');
                    $sRegExp .= ' REGEXP "^.*'.$aCode[0].'.*'.$aCode[1].'.*'.$aCode[2].'.*$" ';
                }
                elseif ($sCodeType == 'input')
                {//单式
                    $sCode = implode(" ", $aCode);
                    $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .' REGEXP "'.$sCode.'" ';
                }
                else
                {
                    return '';
                }
                BREAK;
            }
            //乐透二位直选---------------------------------------------------
            case 'lotto_n2_zhixuan':
            {
                $aCode = explode(" ", $sCode);
                if( 2 != count($aCode))
                {
                    return '';
                }
                if($sCodeType == 'digital')
                {//复式
                    $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT');
                    $sRegExp .= ' REGEXP "^.*'.$aCode[0].'.*\\\|.*'.$aCode[1].'.*$" ';
                }
                elseif ($sCodeType == 'input')
                {//单式
                    $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .' REGEXP "'.$sCode.'" ';
                }
                else
                {
                    return '';
                }
                BREAK;
            }
            //乐透二位组选-------------------------------------------------
            case 'lotto_n2_zhuxuan':
            {
                $aCode = explode(" ", $sCode);
                if( 2 != count($aCode))
                {
                    return '';
                }
                sort($aCode);
                if($sCodeType == 'digital')
                {//复式
                    $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT');
                    $sRegExp .= ' REGEXP "^.*'.$aCode[0].'.*'.$aCode[1].'.*$" ';
                }
                elseif ($sCodeType == 'input')
                {//单式
                    $sCode = implode(" ", $aCode);
                    $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .' REGEXP "'.$sCode.'" ';
                }
                else
                {
                    return '';
                }
                BREAK;
            }
            //乐透不定位---------------------------------------------------
            case 'lotto_budingwei':
            {
                $aCode = explode(" ", $sCode);
                if( 3 != count($aCode))
                {
                    return '';
                }
                $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT');
                $sRegExp .= ' REGEXP "^(.*'.$aCode[0].'.*)|(.*'.$aCode[1].'.*)|(.*'.$aCode[2].'.*)$" ';
                BREAK;
            }
            //乐透定位胆---------------------------------------------------
            case 'lotto_dingweidan':
            {
                $aCode = explode(" ", $sCode);
                if( 1 != count($aCode))
                {
                    return '';
                }
                $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT');
                $sRegExp .= ' REGEXP "^.*'.$aCode[0].'.*$" ';
                BREAK;
            }
            //乐透定单双---------------------------------------------------
            case 'lotto_dingdanshuang':
            {
                $aCode = explode(" ", $sCode);
                if( 5 != count($aCode))
                {
                    return '';
                }
                //统计单双个数
                $iSingleCount = 0;//单号个数
                $iDoubleCount = 0;//双号码个数
                foreach ($aCode as $sCodeValue)
                {
                    $sCodeValue%2 == 0 ? $iDoubleCount++ : $iSingleCount++;
                }
                $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT');
                $sRegExp .= ' REGEXP "^.*'.$iSingleCount.'.*$" ';
                BREAK;
            }
            //乐透猜中位-------------------------------------------------
            case 'lotto_zhongwei':
            {
                $aCode = explode(" ", $sCode);
                if( 5 != count($aCode))
                {
                    return '';
                }
                //找出中奖号码的中位
                sort($aCode);
                $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT');
                $sRegExp .= ' REGEXP "^.*'.$aCode[2].'.*$" ';
                BREAK;
            }
            //乐透任选---------------------------------------------------
            //根据可能中奖的组合分别进行正则匹配 AND `code` REGEXP "^(.*01.*)|(.*02.*)|(.*03.*)|(.*04.*)|(.*05.*)$" 
            case 'lotto_rx1' :
            case 'lotto_rx2' :
            case 'lotto_rx3' :
            case 'lotto_rx4' :
            case 'lotto_rx5' :
            case 'lotto_rx6' :
            case 'lotto_rx7' :
            case 'lotto_rx8' :
                $aCode = explode(' ', $sCode);
                sort($aCode);//将开奖号码从小到大进行排序
                if(count($aCode) != 5)
                {
                    return '';
                }
                if($sTagName == 'lotto_rx1')
                {
                    $iSelectNum = 1;//任选中奖的号码个数
                }
                else if($sTagName == 'lotto_rx2')
                {
                    $iSelectNum = 2;//任选中奖的号码个数
                }
                elseif($sTagName == 'lotto_rx3')
                {
                    $iSelectNum = 3;//任选中奖的号码个数
                }
                elseif($sTagName == 'lotto_rx4')
                {
                    $iSelectNum = 4;//任选中奖的号码个数
                }
                else 
                {
                    $iSelectNum = 5;//任选中奖的号码个数
                }
                $aTmpCode = $this->getCombination($aCode, $iSelectNum);//可能中奖的组合
                $aRegExp = array();
                sort($aTmpCode);
                if($sCodeType == 'digital')
                {//复式
                    $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .' REGEXP "^';
                    foreach ( $aTmpCode as $sCode )
                    {
                        $sCode = trim($sCode,' ');
                        $sCode = str_replace(' ','.*',$sCode);
                        $aRegExp[] = '(.*'.$sCode.'.*)';
                    }
                    $sRegExpTmp = implode("|",$aRegExp);
                    $sRegExp .= $sRegExpTmp.'$"';
                }
                elseif ($sCodeType == 'input')
                {//单式
                    $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .' REGEXP "';
                    foreach ( $aTmpCode as $sCode )
                    {
                        $sCode = trim($sCode,' ');
                        if($sTagName == "lotto_rx6" || $sTagName == "lotto_rx7" || $sTagName == "lotto_rx8")
                        {
                            $sCode = str_replace(' ','[^\\|]*',$sCode);
                        }
                        $aRegExp[] = '('.$sCode.')';
                    }
                    $sRegExpTmp = implode("|",$aRegExp);
                    $sRegExp .= $sRegExpTmp.'"';
                }
                BREAK;
            //北京快乐八中奖判断
            case 'bjkl_rx1':
            case 'bjkl_rx2':
            case 'bjkl_rx3':
            case 'bjkl_rx4':
            case 'bjkl_rx5':
            case 'bjkl_rx6':
            case 'bjkl_rx7':
                $aCode = explode(' ', $sCode);
                $aCode = array_unique($aCode);
                if(count($aCode) != 20)
                {
                    return '';
                }
                sort($aCode);//将开奖号码从小到大进行排序
                $iSelNum = intval(substr($sTagName,-1));//选择号码个数
                $aRegExp = array();
                $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .' REGEXP "(';
                foreach ( $aCode as $sCode )
                {
                    $sCode = trim($sCode,' ');
                    $sCode = str_replace(' ','.*',$sCode);
                    $aRegExp[] = '(.*'.$sCode.'.*)';
                }
                $sRegExpTmp = implode("|",$aRegExp);
                if($iSelNum == 1)
                {
                    $sRegExp .= $sRegExpTmp.'){1,}"';//至少有一个相同的号码
                }
                elseif ($iSelNum == 2 || $iSelNum == 3 || $iSelNum == 4)
                {
                    $sRegExp .= $sRegExpTmp.'){2,}"';//相同号码为二个以上
                }
                elseif ($iSelNum == 5 || $iSelNum == 6)
                {
                    $sRegExp .= $sRegExpTmp.'){3,}"';//相同号码为三个以上
                }
                elseif ($iSelNum == 7)
                {
                    if( $bIsBingo == TRUE )
                    {
                        $sRegExp = ' AND ((`'.$sField.'` '.' REGEXP "(';
                        $sRegExp .= $sRegExpTmp.'){4,}" '; //相同号码有四个个以上
                        $sRegExp .= ' OR `'.$sField.'` '.' NOT REGEXP "('.$sRegExpTmp.'){1,}")';//没有相同的号码
                        switch ($iMaxCodeCount)
                        {
                            case 7://最多选择七个号码
                                $sRegExp .= ')';
                                break;
                            case 8://最多选择八个号码
                                //有一个相同的号码，并且选择号码为8个
                                $sRegExp .= ' OR ( `'.$sField.'` '.' REGEXP "('.$sRegExpTmp.'){1,}" AND `'.$sField.'` '.' NOT REGEXP "('.$sRegExpTmp.'){2,3}" AND `'.$sField.'` '.' REGEXP "^(.){23}$" ))';
                                break;
                            case 9://最多选择九个号码
                                //有一个或两个或四个以上的相同的号码，并且选择号码为9个
                                $sRegExp .= ' OR ( `'.$sField.'` '.' REGEXP "('.$sRegExpTmp.'){1,}" AND `'.$sField.'` '.' NOT REGEXP "('.$sRegExpTmp.'){2,3}" AND `'.$sField.'` '.' REGEXP "^(.){23}$" )';
                                $sRegExp .= ' OR ( `'.$sField.'` '.' REGEXP "('.$sRegExpTmp.'){2,}" AND `'.$sField.'` '.' NOT REGEXP "('.$sRegExpTmp.'){3}" AND `'.$sField.'` '.' REGEXP "^(.){26}$" ))';
                                break;
                            default://最多选择十个号码及以上
                                $sRegExp .= ' OR ( `'.$sField.'` '.' REGEXP "('.$sRegExpTmp.'){1,}" AND `'.$sField.'` '.' NOT REGEXP "('.$sRegExpTmp.'){2,3}" AND `'.$sField.'` '.' REGEXP "^(.){23}$" )';
                                $sRegExp .= ' OR ( `'.$sField.'` '.' REGEXP "('.$sRegExpTmp.'){2,}" AND `'.$sField.'` '.' NOT REGEXP "('.$sRegExpTmp.'){3}" AND `'.$sField.'` '.' REGEXP "^(.){26}$" )';
                                $sRegExp .= ' OR ( `'.$sField.'` '.' REGEXP "^(.){29,}$" ))';
                                break;
                        }
                    }
                    else 
                    {
                        $sRegExp = ' AND ((`'.$sField.'` '.' NOT REGEXP "(';
                        $sRegExp .= $sRegExpTmp.'){4,}" '; //相同号码没有四个个以上
                        switch ($iMaxCodeCount)
                        {
                            case 7://最多选择七个号码
                                $sRegExp .= ' AND `'.$sField.'` REGEXP "^(.){20}$" AND `'.$sField.'` '.' REGEXP "('.$sRegExpTmp.'){1,}"))';//有一个以上的相同号码
                                break;
                            case 8: //最多选择八个号码
                                //选择号码为7个
                                $sRegExp .= ' AND `'.$sField.'` REGEXP "^(.){20}$" AND `'.$sField.'` '.' REGEXP "('.$sRegExpTmp.'){1,}")';//有两个以上的相同号码
                                //选择号码为8个
                                $sRegExp .= ' OR ( `'.$sField.'` '.' REGEXP "^(.){23}$" AND `'.$sField.'` '.' NOT REGEXP "('.$sRegExpTmp.'){4,}"  AND `'.$sField.'` '.' REGEXP "('.$sRegExpTmp.'){2,}" ))';
                                break;
                            default://最多选择九个号码及以上
                                //选择号码为7个
                                $sRegExp .= ' AND `'.$sField.'` REGEXP "^(.){20}$" AND `'.$sField.'` '.' REGEXP "('.$sRegExpTmp.'){1,}")';//有两个以上的相同号码
                                //选择号码为8个
                                $sRegExp .= ' OR ( `'.$sField.'` '.' REGEXP "^(.){23}$" AND `'.$sField.'` '.' NOT REGEXP "('.$sRegExpTmp.'){4,}"  AND `'.$sField.'` '.' REGEXP "('.$sRegExpTmp.'){2,}" )';
                                //选择号码为9个
                                $sRegExp .= ' OR ( `'.$sField.'` '.' REGEXP "^(.){26}$" AND `'.$sField.'` '.' NOT REGEXP "('.$sRegExpTmp.'){4,}"  AND `'.$sField.'` '.' REGEXP "('.$sRegExpTmp.'){3}" ))';
                                break;
                        }
                    }
                }
                break;
           case 'bjkl_hedx'://和值大小
           case 'bjkl_heds'://和值单双
               $aCode = explode(' ', $sCode);
               $aCode = array_unique($aCode);
               if(count($aCode) != 20)
               {
                   return '';
               }
               $iAddCount = 0;
               foreach ($aCode as $iCode)
               {
                  $iAddCount +=intval($iCode); 
               }
               if($sTagName == 'bjkl_heds')
               {
                   $iFinalBonusCode = $iAddCount%2 == 0 ? 1 : 0;
               }
               elseif($sTagName == 'bjkl_hedx')
               {
                   $iFinalBonusCode = 0;
                   if($iAddCount < 810)
                   {
                       $iFinalBonusCode = 1;
                   }
                   if($iAddCount == 810)
                   {
                       $iFinalBonusCode = 2;
                   }
               }
               $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .' REGEXP "^.*'.$iFinalBonusCode.'.*$"';
               break;
           case 'bjkl_sxpan'://上下盘
               $aCode = explode(' ', $sCode);
               $aCode = array_unique($aCode);
               if(count($aCode) != 20)
               {
                   return '';
               }
               $iBigCount = 0;//大号个数
               $iSmallCount = 0;//小号个数
               foreach ($aCode as $iCode)
               {
                   $iCode = intval($iCode);
                   $iCode > 40 ? $iBigCount++ : $iSmallCount++;
               }
               $iFinalBonusCode = 0;
               if($iBigCount > $iSmallCount)
               {
                   $iFinalBonusCode = 1;//下盘
               }
               elseif($iBigCount == $iSmallCount)
               {
                   $iFinalBonusCode = 2;//和盘
               }
               $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .' REGEXP "^.*'.$iFinalBonusCode.'.*$"';
               break;
           case 'bjkl_jopan'://奇偶盘
               $aCode = explode(' ', $sCode);
               $aCode = array_unique($aCode);
               if(count($aCode) != 20)
               {
                   return '';
               }
               $iEevnCount = 0;//偶数号个数
               $iOddCount = 0;//奇数号个数
               foreach ($aCode as $iCode)
               {
                   $iCode = intval($iCode);
                   $iCode%2 == 0 ? $iEevnCount++ : $iOddCount++;
               }
               $iFinalBonusCode = 0;
               if($iEevnCount > $iOddCount)
               {
                   $iFinalBonusCode = 1;//偶盘
               }
               elseif($iEevnCount == $iOddCount)
               {
                   $iFinalBonusCode = 2;//和盘
               }
               $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .' REGEXP "^.*'.$iFinalBonusCode.'.*$"';
               break;
        }
        return $sRegExp;
    }


    /**
     * 根据正则表达式条件, 更新
     * @param string $iMethodId  // 玩法ID
     * @param string $sWhere     // 正则表达式
     * @param bool   $bIsBingo   // 标记位  中奖=1,  未中=2
     * @return mix
     */
    private function doUpdateProjectFlag( $iMethodId, $sWhereRegExp, $iIsGetPrizeFlag=1, $sCodeType = 'digital' )
    {
        $sWhere = " `issue`='$this->sIssue' AND `lotteryid`='$this->iLotteryId' AND `methodid`='$iMethodId' "
               . " AND `isgetprize`=0 AND `codetype`='".$sCodeType."'" . $sWhereRegExp ;
        if( $iIsGetPrizeFlag == 1 )
        { // 更新中奖
            $sFullSql = "UPDATE `projects` SET `updatetime`='".date("Y-m-d H:i:s")."',`isgetprize`=1 WHERE $sWhere ";
        }
        elseif( $iIsGetPrizeFlag == 2 )
        {
            $sFullSql = "UPDATE `projects` SET `updatetime`='".date("Y-m-d H:i:s")."',`isgetprize`=2 WHERE $sWhere ";
        }
        else 
        { // 参数错误
            return FALSE;
        }
        $this->oDB->query( $sFullSql );
        if( $this->oDB->errno() )
        { // update 出错
            return FALSE;
        }
        return $this->oDB->ar();
    }


    // 更新指令集封装
    private function doUpdateProjects( $sTagName='', $sCode='', $aData=array() )
    {
        if( empty($sTagName) || empty( $aData ) )
        {
            return FALSE;
        }
        $sRegexpYes = $this->makeRegExp( $sTagName, TRUE,  $sCode, 'code', $aData['codetype'], $aData['maxcodecount'] );  // 中奖正则
        $sRegexpNo  = $this->makeRegExp( $sTagName, FALSE, $sCode, 'code', $aData['codetype'], $aData['maxcodecount'] );  // 未中奖正则
        if( empty($sRegexpYes) || empty($sRegexpNo) )
        {
            echo "[w] [".date('Y-m-d H:i:s')."] makeRegExp() Failed! LotteryId='".$this->iLotteryId
                    ."' TagName='$sTagName' To Be Continue\n";
            return FALSE;
        }

        $iYesCount  = $this->doUpdateProjectFlag( $aData['methodid'], $sRegexpYes, 1, $aData['codetype'] );  // 更新中奖标记
        $iNoCount   = $this->doUpdateProjectFlag( $aData['methodid'], $sRegexpNo,  2, $aData['codetype'] );  // 更新未中标记
        if( FALSE===$iYesCount || FALSE===$iNoCount )
        {
            return FALSE;
        }
        return ($iYesCount+$iNoCount);
    }


    /**
     * 中奖判断函数: SSC
     * @author Mark
     * @param int $iMethodId
     * @return mix
     *   - 执行成功, 则返回受影响行数 (中奖+未中奖的总计更新量)
     *   - 执行失败, 则返回全等于的 FALSE
     */
    private function __fun__ssc_q3zhixuan( $aData = array() )
    {
        return $this->doUpdateProjects( 'n3_zhixuan', substr($this->sCode,0,3), $aData );
    }

    private function __fun__ssc_h3zhixuan( $aData = array() )
    {
        return $this->doUpdateProjects( 'n3_zhixuan', substr($this->sCode,2,3), $aData );
    }
    
    private function __fun__ssc_q3zhixuanhezhi( $aData = array() )
    {
        $sCode = substr($this->sCode,0,3);
        $iCodeHz = $sCode{0}+$sCode{1}+$sCode{2};
        return $this->doUpdateProjects( 'n3_zhixuanhezhi', $iCodeHz, $aData );
    }

    private function __fun__ssc_h3zhixuanhezhi( $aData = array() )
    {
        $sCode = substr($this->sCode,2,3);
        $iCodeHz = $sCode{0}+$sCode{1}+$sCode{2};
        return $this->doUpdateProjects( 'n3_zhixuanhezhi', $iCodeHz, $aData );
    }
    
    private function __fun__ssc_q3zusan( $aData = array() )
    {
        return $this->doUpdateProjects( 'n3_zusan', substr($this->sCode,0,3), $aData );
    }

    private function __fun__ssc_h3zusan( $aData = array() )
    {
        return $this->doUpdateProjects( 'n3_zusan', substr($this->sCode,2,3), $aData );
    }
    
    private function __fun__ssc_q3zuliu( $aData = array() )
    {
        return $this->doUpdateProjects( 'n3_zuliu', substr($this->sCode,0,3), $aData );
    }

    private function __fun__ssc_h3zuliu( $aData = array() )
    {
        return $this->doUpdateProjects( 'n3_zuliu', substr($this->sCode,2,3), $aData );
    }
    
    private function __fun__ssc_q3hunhezuxuan( $aData = array() )
    {
        return $this->doUpdateProjects( 'n3_hunhezuxuan', substr($this->sCode,0,3), $aData );
    }

    private function __fun__ssc_h3hunhezuxuan( $aData = array() )
    {
        return $this->doUpdateProjects( 'n3_hunhezuxuan', substr($this->sCode,2,3), $aData );
    }
    
    private function __fun__ssc_q3zuxuanhezhi( $aData = array() )
    {
        return $this->doUpdateProjects( 'n3_hezhi', substr($this->sCode,0,3), $aData );
    }

    private function __fun__ssc_h3zuxuanhezhi( $aData = array() )
    {
        return $this->doUpdateProjects( 'n3_hezhi', substr($this->sCode,2,3), $aData );
    }
    
    private function __fun__ssc_yimabudingwei( $aData = array() )
    {
        return $this->doUpdateProjects( 'n1_budingwei', substr($this->sCode,2,3), $aData );
    }

    private function __fun__ssc_ermabudingwei( $aData = array() )
    {
        return $this->doUpdateProjects( 'n2_budingwei', substr($this->sCode,2,3), $aData );
    }

    private function __fun__ssc_q2zhixuan( $aData = array() )
    {
        return $this->doUpdateProjects( 'n2_zhixuan', substr($this->sCode,0,2), $aData );
    }
    
    private function __fun__ssc_h2zhixuan( $aData = array() )
    {
        return $this->doUpdateProjects( 'n2_zhixuan', substr($this->sCode,3,2), $aData );
    }
    
    private function __fun__ssc_q2zuxuan( $aData = array() )
    {
        return $this->doUpdateProjects( 'n2_zuxuan', substr($this->sCode,0,2), $aData );
    }
    
    private function __fun__ssc_h2zuxuan( $aData = array() )
    { 
        return $this->doUpdateProjects( 'n2_zuxuan', substr($this->sCode,3,2), $aData );
    }
    
    private function __fun__ssc_wanwei( $aData = array() )
    {
        return $this->doUpdateProjects( 'n1_dan', substr($this->sCode,0,1), $aData );
    }
    
    private function __fun__ssc_qianwei( $aData = array() )
    {
        return $this->doUpdateProjects( 'n1_dan', substr($this->sCode,1,1), $aData );
    }
    
    private function __fun__ssc_baiwei( $aData = array() )
    {
        return $this->doUpdateProjects( 'n1_dan', substr($this->sCode,2,1), $aData );
    }
    
    private function __fun__ssc_shiwei( $aData = array() )
    {
        return $this->doUpdateProjects( 'n1_dan', substr($this->sCode,3,1), $aData );
    }
    
    private function __fun__ssc_gewei( $aData = array() )
    {
        return $this->doUpdateProjects( 'n1_dan', substr($this->sCode,4,1), $aData );
    }

    private function __fun__ssc_q2daxiaodanshuang( $aData = array() )
    {
        return $this->doUpdateProjects( 'n2_dxds', substr($this->sCode,0,2), $aData );
    }
    
    private function __fun__ssc_h2daxiaodanshuang( $aData = array() )
    {
        return $this->doUpdateProjects( 'n2_dxds', substr($this->sCode,3,2), $aData );
    }
    

    
    /**
     * 中奖判断函数: SSL
     * @author Mark
     * @param int $iMethodId
     * @return mix
     *   - 执行成功, 则返回受影响行数 (中奖+未中奖的总计更新量)
     *   - 执行失败, 则返回全等于的 FALSE
     */
    private function __fun__ssl_zhixuan( $aData = array() )
    {
        return $this->doUpdateProjects( 'n3_zhixuan', $this->sCode, $aData );
    }
   
    private function __fun__ssl_zhixuanhezhi( $aData = array() )
    {
        $sCode = $this->sCode;
        $iCodeHz = $sCode{0}+$sCode{1}+$sCode{2};
        return $this->doUpdateProjects( 'n3_zhixuanhezhi', $iCodeHz, $aData );
    }

    private function __fun__ssl_zusan( $aData = array() )
    {
        return $this->doUpdateProjects( 'n3_zusan', $this->sCode, $aData );
    }

    
    private function __fun__ssl_zuliu( $aData = array() )
    {
        return $this->doUpdateProjects( 'n3_zuliu', $this->sCode, $aData );
    }

    
    private function __fun__ssl_hunhezuxuan( $aData = array() )
    {
        return $this->doUpdateProjects( 'n3_hunhezuxuan', $this->sCode, $aData );
    }

    
    private function __fun__ssl_zuxuanhezhi( $aData = array() )
    {
        return $this->doUpdateProjects( 'n3_hezhi', $this->sCode, $aData );
    }

    
    private function __fun__ssl_budingwei( $aData = array() )
    {
        return $this->doUpdateProjects( 'n1_budingwei', $this->sCode, $aData );
    }

    
    private function __fun__ssl_q2zhixuan( $aData = array() )
    {
        return $this->doUpdateProjects( 'n2_zhixuan', substr($this->sCode,0,2), $aData );
    }
    
    private function __fun__ssl_h2zhixuan( $aData = array() )
    {
        return $this->doUpdateProjects( 'n2_zhixuan', substr($this->sCode,1,2), $aData );
    }
    
    private function __fun__ssl_q2zuxuan( $aData = array() )
    {
        return $this->doUpdateProjects( 'n2_zuxuan', substr($this->sCode,0,2), $aData );
    }
    
    private function __fun__ssl_h2zuxuan( $aData = array() )
    { 
        return $this->doUpdateProjects( 'n2_zuxuan', substr($this->sCode,1,2), $aData );
    }
    
    private function __fun__ssl_baiwei( $aData = array() )
    {
        return $this->doUpdateProjects( 'n1_dan', substr($this->sCode,0,1), $aData );
    }

    private function __fun__ssl_shiwei( $aData = array() )
    {
        return $this->doUpdateProjects( 'n1_dan', substr($this->sCode,1,1), $aData );
    }
    
    private function __fun__ssl_gewei( $aData = array() )
    {
        return $this->doUpdateProjects( 'n1_dan', substr($this->sCode,2,1), $aData );
    }
    
    
    /**
     * 中奖判断函数: SD11Y
     * @author Mark
     * @param int $iMethodId
     * @return mix
     *   - 执行成功, 则返回受影响行数 (中奖+未中奖的总计更新量)
     *   - 执行失败, 则返回全等于的 FALSE
     */
    private function __fun__sd11y_qszhixuan( $aData = array() )
    {
        $aCode = explode(" ", $this->sCode);
        $sCode = $aCode[0] . " " . $aCode[1] . " " . $aCode[2];
        return $this->doUpdateProjects( 'lotto_n3_zhixuan', $sCode, $aData );
    }
    
    private function __fun__sd11y_qszhuxuan( $aData = array() )
    {
        $aCode = explode(" ", $this->sCode);
        $sCode = $aCode[0] . " " . $aCode[1] . " " . $aCode[2];
        return $this->doUpdateProjects( 'lotto_n3_zhuxuan', $sCode, $aData );
    }
    
    private function __fun__sd11y_q2zhixuan( $aData = array() )
    {
        $aCode = explode(" ", $this->sCode);
        $sCode = $aCode[0] . " " . $aCode[1];
        return $this->doUpdateProjects( 'lotto_n2_zhixuan', $sCode, $aData );
    }
    
    private function __fun__sd11y_q2zhuxuan( $aData = array() )
    {
        $aCode = explode(" ", $this->sCode);
        $sCode = $aCode[0] . " " . $aCode[1];
        return $this->doUpdateProjects( 'lotto_n2_zhuxuan', $sCode, $aData );
    }
    
    private function __fun__sd11y_budingwei( $aData = array() )
    {
        $aCode = explode(" ", $this->sCode);
        $sCode = $aCode[0] . " " . $aCode[1] . " " . $aCode[2];
        return $this->doUpdateProjects( 'lotto_budingwei', $sCode, $aData );
    }
    
    private function __fun__sd11y_dingyiwei( $aData = array() )
    {
        $aCode = explode(" ", $this->sCode);
        return $this->doUpdateProjects( 'lotto_dingweidan', $aCode[0], $aData );
    }
    
    private function __fun__sd11y_dingerwei( $aData = array() )
    {
        $aCode = explode(" ", $this->sCode);
        return $this->doUpdateProjects( 'lotto_dingweidan', $aCode[1], $aData );
    }
    
    private function __fun__sd11y_dingshanwei( $aData = array() )
    {
        $aCode = explode(" ", $this->sCode);
        return $this->doUpdateProjects( 'lotto_dingweidan', $aCode[2], $aData );
    }
    
    private function __fun__sd11y_danshuang( $aData = array() )
    {
        return $this->doUpdateProjects( 'lotto_dingdanshuang', $this->sCode, $aData );
    }
    
    private function __fun__sd11y_zhongwei( $aData = array() )
    {
        return $this->doUpdateProjects( 'lotto_zhongwei', $this->sCode, $aData );
    }
    
    private function __fun__sd11y_rx1( $aData = array() )
    {
        return $this->doUpdateProjects( 'lotto_rx1', $this->sCode, $aData );
    }
    
    private function __fun__sd11y_rx2( $aData = array() )
    {
        return $this->doUpdateProjects( 'lotto_rx2', $this->sCode, $aData );
    }
    
    private function __fun__sd11y_rx3( $aData = array() )
    {
        return $this->doUpdateProjects( 'lotto_rx3', $this->sCode, $aData );
    }
    
    private function __fun__sd11y_rx4( $aData = array() )
    {
        return $this->doUpdateProjects( 'lotto_rx4', $this->sCode, $aData );
    }
    
    private function __fun__sd11y_rx5( $aData = array() )
    {
        return $this->doUpdateProjects( 'lotto_rx5', $this->sCode, $aData );
    }
    
    private function __fun__sd11y_rx6( $aData = array() )
    {
        return $this->doUpdateProjects( 'lotto_rx6', $this->sCode, $aData );
    }
    
    private function __fun__sd11y_rx7( $aData = array() )
    {
        return $this->doUpdateProjects( 'lotto_rx7', $this->sCode, $aData );
    }
    
    private function __fun__sd11y_rx8( $aData = array() )
    {
        return $this->doUpdateProjects( 'lotto_rx8', $this->sCode, $aData );
    }
    
    
    
    /**
     * 中奖判断函数: BJKL8
     * @author Mark
     * @param int $iMethodId
     * @return mix
     *   - 执行成功, 则返回受影响行数 (中奖+未中奖的总计更新量)
     *   - 执行失败, 则返回全等于的 FALSE
     */
    private function __fun__bjkl_rx1( $aData = array() )
    {
        return $this->doUpdateProjects( 'bjkl_rx1', $this->sCode, $aData );
    }
    private function __fun__bjkl_rx2( $aData = array() )
    {
        return $this->doUpdateProjects( 'bjkl_rx2', $this->sCode, $aData );
    }
    private function __fun__bjkl_rx3( $aData = array() )
    {
        return $this->doUpdateProjects( 'bjkl_rx3', $this->sCode, $aData );
    }
    private function __fun__bjkl_rx4( $aData = array() )
    {
        return $this->doUpdateProjects( 'bjkl_rx4', $this->sCode, $aData );
    }
    private function __fun__bjkl_rx5( $aData = array() )
    {
        return $this->doUpdateProjects( 'bjkl_rx5', $this->sCode, $aData );
    }
    private function __fun__bjkl_rx6( $aData = array() )
    {
        return $this->doUpdateProjects( 'bjkl_rx6', $this->sCode, $aData );
    }
    private function __fun__bjkl_rx7( $aData = array() )
    {
        return $this->doUpdateProjects( 'bjkl_rx7', $this->sCode, $aData );
    }
    private function __fun__bjkl_hedx( $aData = array() )
    {
        return $this->doUpdateProjects( 'bjkl_hedx', $this->sCode, $aData );
    }
    private function __fun__bjkl_heds( $aData = array() )
    {
        return $this->doUpdateProjects( 'bjkl_heds', $this->sCode, $aData );
    }
    private function __fun__bjkl_sxpan( $aData = array() )
    {
        return $this->doUpdateProjects( 'bjkl_sxpan', $this->sCode, $aData );
    }
    private function __fun__bjkl_jopan( $aData = array() )
    {
        return $this->doUpdateProjects( 'bjkl_jopan', $this->sCode, $aData );
    }
    
    // -----------------------------[ 内置方法 ]--------------------------------------
    /**
     * 检查中奖判断方法是否存在
     * @param string $sFunctionName
     * @return BOOL
     */
    private function checkFunctionExists( $sFunctionName='' )
    {
        if( empty($sFunctionName) )
        {
            return FALSE;
        }
        $sFunctionName = '__fun__' . $sFunctionName;
        return method_exists( $this, $sFunctionName );
    }

    public function setLoopMode( $bLoopMode=FALSE )
    {
        $this->bLoopMode = (BOOL)$bLoopMode;
    }
    
    /**
     * 指定循环运行的次数
     *
     * @param unknown_type $iRunTimes
     */
    public function setRunTimes( $iRunTimes = 1 )
    {
        $this->iRunTimes = $iRunTimes;
    }
    
    public function setSteps( $iStepCounts=0, $iStepSec=0 )
    {
        $this->iStepCounts = intval($iStepCounts);
        $this->iStepSec    = intval($iStepSec);
    }
    
    
    /**
     * 获取指定组合的所有可能性
     * 
     * 例子：5选3
     * $aBaseArray = array('01','02','03','04','05');
     * ----getCombination($aBaseArray,3)
     * 1.初始化一个字符串：11100;--------1的个数表示需要选出的组合
     * 2.将1依次向后移动造成不同的01字符串，构成不同的组合，1全部移动到最后面，移动完成：00111.
     * 3.移动方法：每次遇到第一个10字符串时，将其变成01,在此子字符串前面的字符串进行倒序排列,后面的不变：形成一个不同的组合.
     *            如：11100->11010->10110->01110->11001->10101->01101->10011->01011->00111
     *            一共形成十个不同的组合:每一个01字符串对应一个组合---如11100对应组合01 02 03;01101对应组合02 03 05
     * 
     * 
     * @param  array $aBaseArray 基数数组
     * @param  int   $iSelectNum 选数
     * @author mark
     *
     */
    private function getCombination( $aBaseArray, $iSelectNum )
    {
        $iBaseNum = count($aBaseArray);
        if($iSelectNum > $iBaseNum)
        {
            return array();
        }
        if( $iSelectNum == 1 )
        {
            return $aBaseArray;
        }
        if( $iBaseNum == $iSelectNum )
        {
            return array(implode(' ',$aBaseArray));
        }
        $sString = '';
        $sLastString = '';
        $sTempStr = '';
        $aResult = array();
        for ($i=0; $i<$iSelectNum; $i++)
        {
            $sString .='1';
            $sLastString .='1'; 
        }
        for ($j=0; $j<$iBaseNum-$iSelectNum; $j++)
        {
            $sString .='0';
        }
        for ($k=0; $k<$iSelectNum; $k++)
        {
            $sTempStr .= $aBaseArray[$k].' ';
        }
        $aResult[] = $sTempStr;
        $sTempStr = '';
        while (substr($sString, -$iSelectNum) != $sLastString)
        {
            $aString = explode('10',$sString,2);
            $aString[0] = $this->strOrder($aString[0], TRUE);
            $sString = $aString[0].'01'.$aString[1];
            for ($k=0; $k<$iBaseNum; $k++)
            {
                if( $sString{$k} == '1' )
                {
                    $sTempStr .= $aBaseArray[$k].' ';
                }
            }
            $aResult[] = substr($sTempStr, 0, -1);
            $sTempStr = '';
        }
        return $aResult;
    }
    
    
    /**
     * 字符串排序
     * @param string $sString 需要排序的字符串
     * @return string
     * @author mark
     */
    private function strOrder( $sString = '', $bDesc = FALSE )
    {
        if( $sString == '')
        {
            return $sString;
        }
        $aString = str_split($sString);
        if($bDesc)
        {
            rsort($aString);
        }
        else
        {
            sort($aString);
        }
        return implode('',$aString);
    }
}
?>