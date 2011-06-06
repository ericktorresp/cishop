<?php
/**
 * 文件 : /_app/model/sendbonus.php
 * 功能 : 数据模型 - 派奖:基诺型
 *
 * @author    tom,mark
 * @version   1.2.0
 * @package   highgame
 */

class model_sendbonus_keno extends model_sendbonus_base
{
    /**
     * 根据 TagName 获取真实中奖金额
     * @param string $sTagName
     * @param string $sCode
     * @param int    $iProjectId
     * @param int    $iParam 指定参数
     * @return bool  成功返回浮点数的派奖金额, 失败则返回全等于的 FALSE
     */
    public function getRealMoneyByTagName( $sTagName='', $sCode='', $iProjectId=0, $iParam = 0 )
    {
        if( empty($sTagName) || 0==strlen($sCode) || empty($iProjectId) )
        {
            return FALSE;
        }

        switch ( $sTagName )
        {            
            //北京快乐八奖金发放
            /**
             * 
             * 任选玩法奖金计算方法:n所选复式号与中奖号码的交集个数，m所选号码个数,thelevelBouns对应奖级奖金
             * 任选一:C(n,1)    [n>=1]
             * 任选二:C(n,2)    [n>=2]
             * 任选三:C(n,2)*C(m-n,1)*thelevelBouns+C(n,3)*thelevelBouns   [n>=2]
             * 任选四:C(n,2)*C(m-n,2)*thelevelBouns+C(n,3)*C(m-n,1)*thelevelBouns+C(n,4)*thelevelBouns [n>=2]
             * 任选五:C(n,3)*C(m-n,2)*thelevelBouns+C(n,4)*C(m-n,1)*thelevelBouns+C(n,5)*thelevelBouns [n>=3]
             * 任选六:C(n,3)*C(m-n,3)*thelevelBouns+C(n,4)*C(m-n,2)*thelevelBouns+C(n,5)*C(m-n,1)*thelevelBouns+C(n,6)*thelevelBouns [n>=3]
             * 任选七:C(n,4)*C(m-n,3)*thelevelBouns+C(n,5)*C(m-n,2)*thelevelBouns+C(n,6)*C(m-n,1)*thelevelBouns+C(n,7)*thelevelBouns [n>=4]-----[n=1,m=8] times=1-------[n=0] times=C(n,7)五等奖
             * 
             * 以上计算方法的规律采用循环计算
             * 
             * $fBingoMoney = 0;/最终奖金
             * for($i=最小中奖号码个数[选7中0单独计算];$i<最大中奖号码个数;$i++)
             * {
             *   $iLevel = 最大中奖号码个数+1-($i > 最大中奖号码个数 ? 最大中奖号码个数 : $i);//对应奖级
             *   $iBonusTimes = Combin(所选号码与开奖号码交集个数,$i)*Combin(所选号码个数-所选号码与开奖号码交集个数,最大中奖号码个数-$i);//对应奖级中奖注数
             *   $fBingoMoney += floatval( 当前奖级对应奖金 * $iBonusTimes(中奖注数) );//对应奖级的奖金
             * }
             * 
             */
            case 'bjkl_rx1':
            case 'bjkl_rx2':
            case 'bjkl_rx3':
            case 'bjkl_rx4':
            case 'bjkl_rx5':
            case 'bjkl_rx6':
            case 'bjkl_rx7':
                $aCode = explode( " ", $sCode );
                $aCode = array_unique($aCode);
                if( count($aCode) != 20 )
                {
                    return FALSE;
                }
                $aPorject = $this->oDB->getOne("SELECT `code` FROM `projects` WHERE `projectid` = '".$iProjectId."' LIMIT 1");
                if(empty($aPorject))
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getAll("SELECT `level`,`prize` FROM `expandcode` WHERE `projectid`='".$iProjectId."'");
                if(empty($aRow))
                {
                    return FALSE;
                }
                $iSelNum = intval(substr($sTagName,-1));//玩法最少选择的选择号码个数
                $aLevelCount = array(1=>1,2=>1,3=>2,4=>3,5=>3,6=>4,7=>5);//各个玩法奖级个数
                if(count($aRow) != $aLevelCount[$iSelNum])
                {
                    return FALSE;
                }
                $aLevelBonus = array();
                foreach ($aRow as $aLevel)
                {
                    $aLevelBonus[$aLevel['level']] = $aLevel['prize'];//获取各个奖级的奖金
                }
                $aProjectCode = explode("|",$aPorject['code']);
                $iInterCount = count(array_intersect($aCode,$aProjectCode));
                $iCodeCount = count($aProjectCode);
                $aMinNumCount = array(1=>1,2=>2,3=>2,4=>2,5=>3,6=>3,7=>4);//各个玩法最少中奖号码个数,7中0单独计算
                $fBingoMoney = 0.00;//最终奖金
                if( ($iSelNum == 1 && $iInterCount < 1)
                    ||(in_array($iSelNum,array(2,3,4)) && $iInterCount < 2) 
                    || (in_array($iSelNum,array(5,6)) && $iInterCount < 3)
                 )
                {
                    return FALSE;
                }
                if($iSelNum == 7 && in_array($iInterCount,array(0,1,2,3)))
                {
                    if( ($iCodeCount == 7 && in_array($iInterCount,array(1,2,3)))
                        || ($iCodeCount == 8 && in_array($iInterCount,array(2,3)))
                        || ($iCodeCount == 9 && $iInterCount == 3)
                    )
                    {
                        return FALSE;
                    }
                    $iLevel = 5;//任选七中零
                    $iBonusTimes = $iCodeCount > $iSelNum ? $this->GetCombinCount($iCodeCount-$iInterCount,$iSelNum): 1;
                    $fBingoMoney = floatval( $aLevelBonus[$iLevel] * $iBonusTimes );
                }
                else 
                {
                    //累积各个奖级下的号码奖金
                    for($i = $aMinNumCount[$iSelNum]; $i<=$iSelNum; $i++ )
                    {
                        $iLevel = $iSelNum+1-$i;//对应奖级
                        $iBonusTimes = $this->GetCombinCount($iInterCount,$i)*$this->GetCombinCount($iCodeCount-$iInterCount,$iSelNum-$i);//对应奖级中奖注数
                        $fBingoMoney += floatval( $aLevelBonus[$iLevel] * $iBonusTimes );//对应奖级的奖金
                    }
                }
                return $fBingoMoney > 0 ? $fBingoMoney : FALSE;
            case 'bjkl_hedx'://和值大小
            case 'bjkl_heds'://和值单双
            case 'bjkl_sxpan'://上下盘
            case 'bjkl_jopan'://奇偶盘
                $aPorject = $this->oDB->getOne("SELECT `codetype` FROM `projects` WHERE `projectid` = '".$iProjectId."' AND `code` REGEXP '^.*".$this->aSpecailCode[$sTagName]['code'].".*$' LIMIT 1");
                $aRow = $this->oDB->getOne("SELECT `expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                . " AND `level` = '".$this->aSpecailCode[$sTagName]['level']."' LIMIT 1");
                if( count($aRow) == 0 || count($aPorject) == 0 )
                {
                    return FALSE;
                }
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
                break;
        }
        return FALSE;
    }
    
    
    /**
     * 开奖号码特殊处理
     *
     * @param string  $sCode 开奖号码
     * @param int $iLotteryId 彩种ID
     * 
     * @return  array
     * 
     */
    public function GetSepcailCode( $sCode, $iLotteryId = 1 )
    {
        $aFinalBonusCode = array();
        if( $iLotteryId == 9 )
        {
            $aCode = explode(' ', $sCode);
            if(count($aCode) != 20)
            {
                return '';
            }
            $iAddCount = 0;
            $iBigCount = 0;//大号个数
            $iSmallCount = 0;//小号个数
            $iEevnCount = 0;//偶数号个数
            $iOddCount = 0;//奇数号个数
            foreach ($aCode as $iCode)
            {
                $iCode = intval($iCode);
                $iAddCount += $iCode;
                $iCode%2 == 0 ? $iEevnCount++ : $iOddCount++;
                $iCode > 40 ? $iBigCount++ : $iSmallCount++;
            }
            if($iAddCount % 2 == 0)
            {
                $aFinalBonusCode['bjkl_heds']['code'] =1;
                $aFinalBonusCode['bjkl_heds']['level'] =2;
            }
            else 
            {
                $aFinalBonusCode['bjkl_heds']['code'] =0;
                $aFinalBonusCode['bjkl_heds']['level'] =1;
            }
            $aFinalBonusCode['bjkl_hedx']['code'] = 0;
            $aFinalBonusCode['bjkl_hedx']['level'] = 2;
            if($iAddCount < 810)
            {
                $aFinalBonusCode['bjkl_hedx']['code'] = 1;
                $aFinalBonusCode['bjkl_hedx']['level'] = 3;
            }
            if($iAddCount == 810)
            {
                $aFinalBonusCode['bjkl_hedx']['code'] = 2;
                $aFinalBonusCode['bjkl_hedx']['level'] = 1;
            }
            $aFinalBonusCode['bjkl_sxpan']['code'] = 0;
            $aFinalBonusCode['bjkl_sxpan']['level'] = 2;
            if($iBigCount > $iSmallCount)
            {
                $aFinalBonusCode['bjkl_sxpan']['code'] = 1;//下盘
                $aFinalBonusCode['bjkl_sxpan']['level'] = 3;
            }
            elseif($iBigCount == $iSmallCount)
            {
                $aFinalBonusCode['bjkl_sxpan']['code'] = 2;//和盘
                $aFinalBonusCode['bjkl_sxpan']['level'] = 1;
            }
            $aFinalBonusCode['bjkl_jopan']['code'] = 0;
            $aFinalBonusCode['bjkl_jopan']['level'] = 2;
            if($iEevnCount > $iOddCount)
            {
                $aFinalBonusCode['bjkl_jopan']['code'] = 1;//偶盘
                $aFinalBonusCode['bjkl_jopan']['level'] = 3;
            }
            elseif($iEevnCount == $iOddCount)
            {
                $aFinalBonusCode['bjkl_jopan']['code'] = 2;//和盘
                $aFinalBonusCode['bjkl_jopan']['level'] = 1;
            }
        }
        return $aFinalBonusCode;
    }
}
?>