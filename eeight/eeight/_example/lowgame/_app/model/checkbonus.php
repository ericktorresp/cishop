<?php
/**
 * 文件 : /_app/model/checkbonus.php
 * 功能 : 数据模型 - 中奖判断
 * 
 * @author     Tom  090914
 * @version    1.2.0
 * @package    lowgame    
 */

class model_checkbonus extends basemodel
{
    // ---------------------------------[ 属性 ]-------------------------------------------
    // 如果 $iStepCounts, $iStepSec 任何一个为 0, 则不做任何限制, 程序将不会进入 SLEEP 状态
    private $iStepCounts = 0;     // [配合] 每次处理 n 条后, PHP 睡眠 $this->iStepSec 秒
    private $iStepSec    = 0;     // [配合] 每次处理$this->$iStepCounts 条后, PHP 睡眠 n 秒

    private $iTotalProcessed = 0; // 本次 '中奖判断' 的执行, 影响 projects 记录数
    private $bLoopMode = FALSE;   // 主要用于内部测试, 遇到无方案的奖期的情况,不中断程序.继续循环执行 

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
        //     2.6  为了按时间顺序线性执行,取最早一起符合以上要求的  ORDER BY A.`saleend` ASC
        $oIssue       = A::singleton("model_issueinfo");
        $sCurrentTime = date( "Y-m-d H:i:s", time() );
        $sFileds      = " A.`issueid`, A.`issue`, A.`code` ";
        $sCondition   = " A.`statuscode`=2 AND A.`statuscheckbonus`!=2 AND A.`lotteryid`='".$this->iLotteryId
                        ."' AND A.`saleend`<'".$sCurrentTime."' AND `code`!='' ORDER BY A.`saleend` ASC LIMIT 1 ";
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
        $aRes        = $this->oDB->getAll( "SELECT DISTINCT(p.`methodid`), m.`functionname` "
                        . " FROM `projects` p LEFT JOIN `method` m ON(p.`methodid`=m.`methodid`) " 
                        . " WHERE p.`issue`='$this->sIssue' AND p.`lotteryid`='$this->iLotteryId' "
                        . " AND p.`isgetprize`=0 ");
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
            if( $this->bLoopMode == TRUE )
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

        if( $this->bLoopMode == TRUE )
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
        // 1, 判断玩法中奖函数是否已定义.
        if( TRUE != $this->checkFunctionExists($aDatas['functionname']) )
        {
            echo "[w] [".date('Y-m-d H:i:s')."] Function not Exists! LotteryId='".$this->iLotteryId
                    ."' Methodid='".$aDatas['methodid']."' funName='".$aDatas['functionname'].
                    "' Code='$this->sCode' Ignored\n";
            return -1006;
        }
        // 调用中奖判断方法, 并传递 methodid
        $iTotalAffected = $this->{'__fun__'.$aDatas['functionname']}( $aDatas['methodid'] );
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
     *      $this->sCode       =>  官方开奖号码.  3D为3位, P5为5位
     */

    /**
     * 根据枚举的 TagName, 当前彩种ID, 获取中奖或未中奖相应的SQL正则表达式
     * @param string  $sTagName   
     * @param bool    $bIsBingo   TRUE=获取中奖正则, FALSE=未中奖正则
     * @param string  $sCode      用于正则判断的号码, 根据玩法的不同,传递的位数也不同
     *                             例: 定位胆 只传递1未数字(可能是0) 所以不能做 empty 判断
     */
    public function makeRegExp( $sTagName, $bIsBingo=TRUE, $sCode='', $sField='code')
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
                $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .' REGEXP "'.$sCode.'" ';
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

            // 3位数字.通选  [号码全展开, 多奖级] ---------------------------------
            case 'n3_tongxuan' :
            {
                if( 3 != strlen($sCode) )
                {
                    return '';
                }
                $aNumber_1 = substr($sCode,0,1);
                $aNumber_2 = substr($sCode,1,1);
                $aNumber_3 = substr($sCode,2,1);
                $sCode = $aNumber_1.'[0-9]{2}|[0-9]{1}'.$aNumber_2.'[0-9]{1}|[0-9]{2}'.$aNumber_3;
                $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .' REGEXP "'.$sCode.'" ';
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
                         'B' => array(5,6,7,8,9),  // 大
                         'S' => array(0,1,2,3,4),  // 小
                         'A' => array(1,3,5,7,9),  // 单
                         'D' => array(0,2,4,6,8)   // 双
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
                $iFristNumber  = substr($sCode,0,1);
                $iSecondNumber = substr($sCode,1,1);
                $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .
                        ' REGEXP "^.*'.$iFristNumber.'.*\\\|.*'.$iSecondNumber.'.*$" ';
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
                sort($aNumber);
                $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .
                        ' REGEXP ".*'.$aNumber[0].'.*'.$aNumber[1].'.*" ';
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
                if ($aNumber[0] == $aNumber[1] && $aNumber[1] == $aNumber[2])
                {
                	$sRegExp = (TRUE==$bIsBingo) ? ' AND 0 ' : ' AND 1 ';
                    BREAK;
                }
                $iCode = $aNumber[0] + $aNumber[1] + $aNumber[2];
                $sRegExp = ' AND `'.$sField.'` '. (TRUE==$bIsBingo?'':'NOT') .
                        ' REGEXP "(^'.$iCode.'$)|(^'.$iCode.'\\\|)|(\\\|'.$iCode.'\\\|)|(\\\|'.$iCode.'$)" ';
                BREAK;
            }
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
    private function doUpdateProjectFlag( $iMethodId, $sWhereRegExp, $iIsGetPrizeFlag=1 )
    {
        $sWhere = " `issue`='$this->sIssue' AND `lotteryid`='$this->iLotteryId' AND `methodid`='$iMethodId' "
               . " AND `isgetprize`=0 " . $sWhereRegExp ;
        if( $iIsGetPrizeFlag == 1 )
        { // 更新中奖
            $sFullSql = "UPDATE `projects` SET `isgetprize`=1, `updatetime`='".date('Y-m-d H:i:s')."' WHERE $sWhere ";
        }
        elseif( $iIsGetPrizeFlag == 2 )
        {
            $sFullSql = "UPDATE `projects` SET `isgetprize`=2, `updatetime`='".date('Y-m-d H:i:s')."' WHERE $sWhere ";
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
    private function doUpdateProjects( $sTagName='', $sCode='', $iMethodId='' )
    {
        if( empty($sTagName) || empty($iMethodId) )
        {
            return FALSE;
        }
        $sRegexpYes = $this->makeRegExp( $sTagName, TRUE,  $sCode );  // 中奖正则
        $sRegexpNo  = $this->makeRegExp( $sTagName, FALSE, $sCode );  // 未中奖正则
        if( empty($sRegexpYes) || empty($sRegexpNo) )
        {
            echo "[w] [".date('Y-m-d H:i:s')."] makeRegExp() Failed! LotteryId='".$this->iLotteryId
                    ."' TagName='$sTagName' To Be Continue\n";
            return FALSE;
        }

        $iYesCount  = $this->doUpdateProjectFlag( $iMethodId, $sRegexpYes, 1 );  // 更新中奖标记
        $iNoCount   = $this->doUpdateProjectFlag( $iMethodId, $sRegexpNo,  2 );  // 更新未中标记
        if( FALSE===$iYesCount || FALSE===$iNoCount )
        {
            return FALSE;
        }
        return ($iYesCount+$iNoCount);
    }


    /**
     * 中奖更新函数: 3D直选
     * @author Tom
     * @param int $iMethodId
     * @return mix
     *   - 执行成功, 则返回受影响行数 (中奖+未中奖的总计更新量)
     *   - 执行失败, 则返回全等于的 FALSE
     */
    private function __fun__3d_zhixuan( $iMethodId )
    {
        return $this->doUpdateProjects( 'n3_zhixuan', $this->sCode, $iMethodId );
    }

    private function __fun__3d_zhixuanhezhi( $iMethodId )
    {
        return $this->__fun__3d_zhixuan($iMethodId);
    }

    private function __fun__3d_tongxuan( $iMethodId )
    {
        return $this->doUpdateProjects( 'n3_tongxuan', $this->sCode, $iMethodId );
    }

    private function __fun__3d_zusan( $iMethodId )
    {
        return $this->doUpdateProjects( 'n3_zusan', $this->sCode, $iMethodId );
    }

    private function __fun__3d_zuliu( $iMethodId )
    {
        return $this->doUpdateProjects( 'n3_zuliu', $this->sCode, $iMethodId );
    }

    private function __fun__3d_hunhezuxuan( $iMethodId )
    {
        return $this->doUpdateProjects( 'n3_hunhezuxuan', $this->sCode, $iMethodId );
    }

    private function __fun__3d_zuxuanhezhi( $iMethodId )
    {
        return $this->doUpdateProjects( 'n3_hezhi', $this->sCode, $iMethodId );
    }

    private function __fun__3d_yimabudingwei( $iMethodId )
    {
        return $this->doUpdateProjects( 'n1_budingwei', $this->sCode, $iMethodId );
    }

    private function __fun__3d_ermabudingwei( $iMethodId )
    {
        return $this->doUpdateProjects( 'n2_budingwei', $this->sCode, $iMethodId );
    }

    private function __fun__3d_q2zhixuan( $iMethodId )
    {
        return $this->doUpdateProjects( 'n2_zhixuan', substr($this->sCode,0,2), $iMethodId );
    }
    
    private function __fun__3d_h2zhixuan( $iMethodId )
    {
        return $this->doUpdateProjects( 'n2_zhixuan', substr($this->sCode,1,2), $iMethodId );
    }
    
    private function __fun__3d_q2zuxuan( $iMethodId )
    {
        return $this->doUpdateProjects( 'n2_zuxuan', substr($this->sCode,0,2), $iMethodId );
    }
    
    private function __fun__3d_h2zuxuan( $iMethodId )
    { 
        return $this->doUpdateProjects( 'n2_zuxuan', substr($this->sCode,1,2), $iMethodId );
    }
    
    private function __fun__3d_baiwei( $iMethodId )
    {
        return $this->doUpdateProjects( 'n1_dan', substr($this->sCode,0,1), $iMethodId );
    }
    
    private function __fun__3d_shiwei( $iMethodId )
    {
        return $this->doUpdateProjects( 'n1_dan', substr($this->sCode,1,1), $iMethodId );
    }
    
    private function __fun__3d_gewei( $iMethodId )
    {
        return $this->doUpdateProjects( 'n1_dan', substr($this->sCode,2,1), $iMethodId );
    }

    private function __fun__3d_q2daxiaodanshuang( $iMethodId )
    {
        return $this->doUpdateProjects( 'n2_dxds', substr($this->sCode,0,2), $iMethodId );
    }
    
    private function __fun__3d_h2daxiaodanshuang( $iMethodId )
    {
        return $this->doUpdateProjects( 'n2_dxds', substr($this->sCode,1,2), $iMethodId );
    }
    
    
    
    /**
     * 中奖更新函数: P3直选
     * @author Tom
     * @param int $iMethodId
     * @return mix
     *   - 执行成功, 则返回受影响行数 (中奖+未中奖的总计更新量)
     *   - 执行失败, 则返回全等于的 FALSE
     */
    private function __fun__p3_zhixuan( $iMethodId )
    {
        return $this->doUpdateProjects( 'n3_zhixuan', substr($this->sCode,0,3), $iMethodId );
    }

    private function __fun__p3_zhixuanhezhi( $iMethodId )
    {
        return $this->__fun__p3_zhixuan($iMethodId);
    }

    private function __fun__p3_tongxuan( $iMethodId )
    {
        return $this->doUpdateProjects( 'n3_tongxuan', substr($this->sCode,0,3), $iMethodId );
    }

    private function __fun__p3_zusan( $iMethodId )
    {
        return $this->doUpdateProjects( 'n3_zusan', substr($this->sCode,0,3), $iMethodId );
    }

    private function __fun__p3_zuliu( $iMethodId )
    {
        return $this->doUpdateProjects( 'n3_zuliu', substr($this->sCode,0,3), $iMethodId );
    }

    private function __fun__p3_hunhezuxuan( $iMethodId )
    {
        return $this->doUpdateProjects( 'n3_hunhezuxuan', substr($this->sCode,0,3), $iMethodId );
    }

    private function __fun__p3_zuxuanhezhi( $iMethodId )
    {
        return $this->doUpdateProjects( 'n3_hezhi', substr($this->sCode,0,3), $iMethodId );
    }

    private function __fun__p3_yimabudingwei( $iMethodId )
    {
        return $this->doUpdateProjects( 'n1_budingwei', substr($this->sCode,0,3), $iMethodId );
    }

    private function __fun__p3_ermabudingwei( $iMethodId )
    {
        return $this->doUpdateProjects( 'n2_budingwei', substr($this->sCode,0,3), $iMethodId );
    }

    private function __fun__p3_q2zhixuan( $iMethodId )
    {
        return $this->doUpdateProjects( 'n2_zhixuan', substr($this->sCode,0,2), $iMethodId );
    }

    private function __fun__p3_h2zhixuan( $iMethodId )
    {
        return $this->doUpdateProjects( 'n2_zhixuan', substr($this->sCode,1,2), $iMethodId );
    }

    private function __fun__p3_q2zuxuan( $iMethodId )
    {
        return $this->doUpdateProjects( 'n2_zuxuan', substr($this->sCode,0,2), $iMethodId );
    }

    private function __fun__p3_h2zuxuan( $iMethodId )
    {
        return $this->doUpdateProjects( 'n2_zuxuan', substr($this->sCode,1,2), $iMethodId );
    }

    private function __fun__p5_wanwei( $iMethodId )
    {
        return $this->doUpdateProjects( 'n1_dan', substr($this->sCode,0,1), $iMethodId );
    }

    private function __fun__p5_qianwei( $iMethodId )
    {
        return $this->doUpdateProjects( 'n1_dan', substr($this->sCode,1,1), $iMethodId );
    }

    private function __fun__p5_baiwei( $iMethodId )
    {
        return $this->doUpdateProjects( 'n1_dan', substr($this->sCode,2,1), $iMethodId );
    }

    private function __fun__p5_shiwei( $iMethodId )
    {
        return $this->doUpdateProjects( 'n1_dan', substr($this->sCode,3,1), $iMethodId );
    }

    private function __fun__p5_gewei( $iMethodId )
    {
        return $this->doUpdateProjects( 'n1_dan', substr($this->sCode,4,1), $iMethodId );
    }

    private function __fun__p3_q2daxiaodanshuang( $iMethodId )
    {
        return $this->doUpdateProjects( 'n2_dxds', substr($this->sCode,0,2), $iMethodId );
    }

    private function __fun__p3_h2daxiaodanshuang( $iMethodId )
    {
        return $this->doUpdateProjects( 'n2_dxds', substr($this->sCode,1,2), $iMethodId );
    }

    private function __fun__p5_h2daxiaodanshuang( $iMethodId )
    {
        return $this->doUpdateProjects( 'n2_dxds', substr($this->sCode,3,2), $iMethodId );
    }

    private function __fun__p5_h2zhixuan( $iMethodId )
    {
        return $this->doUpdateProjects( 'n2_zhixuan', substr($this->sCode,3,2), $iMethodId );
    }

    private function __fun__p5_h2zuxuan( $iMethodId )
    {
        return $this->doUpdateProjects( 'n2_zuxuan', substr($this->sCode,3,2), $iMethodId );
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

    public function setSteps( $iStepCounts=0, $iStepSec=0 )
    {
        $this->iStepCounts = intval($iStepCounts);
        $this->iStepSec    = intval($iStepSec);
    }
}
?>