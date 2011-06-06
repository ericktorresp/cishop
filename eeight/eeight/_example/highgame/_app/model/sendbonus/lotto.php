<?php
/**
 * 文件 : /_app/model/sendbonus.php
 * 功能 : 数据模型 - 派奖:乐透型
 *
 * @author    tom,mark
 * @version   1.2.0
 * @package   highgame
 */

class model_sendbonus_lotto extends model_sendbonus_base
{
    /**
     * 根据 TagName 获取真实中奖金额
     * @param string $sTagName
     * @param string $sCode
     * @param int    $iProjectId
     * @return bool  成功返回浮点数的派奖金额, 失败则返回全等于的 FALSE
     */
    public function getRealMoneyByTagName( $sTagName='', $sCode='', $iProjectId=0 )
    {
        if( empty($sTagName) || 0==strlen($sCode) || empty($iProjectId) )
        {
            return FALSE;
        }

        switch ( $sTagName )
        {
            // ---------------------------------------------------------------------------------
            case 'lotto_n3_zhixuan' :
            { //乐透三位直选
                $aCode = explode( " ", $sCode );
                if( count($aCode) != 3 )
                {
                    return FALSE;
                }
                $aPorject = $this->oDB->getOne("SELECT `codetype` FROM `projects` WHERE `projectid` = '".$iProjectId."' LIMIT 1");
                if($aPorject['codetype'] == 'digital')
                {//复式
                    $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId LIMIT 1");
                }//单式
                else if($aPorject['codetype'] == 'input')
                {
                    $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `expandcode` REGEXP \"$sCode\" LIMIT 1");
                }
                else 
                {
                    return FALSE;
                }
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'lotto_n3_zhuxuan' :
            { //乐透三位组选
                $aCode = explode( " ", $sCode );
                if( count($aCode) != 3 )
                {
                    return FALSE;
                }
                sort($aCode);
                $sCode = implode( " ", $aCode );
                $aPorject = $this->oDB->getOne("SELECT `codetype` FROM `projects` WHERE `projectid` = '".$iProjectId."' LIMIT 1");
                if($aPorject['codetype'] == 'digital')
                {//复式
                    $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId LIMIT 1");
                }
                else if($aPorject['codetype'] == 'input')
                {//单式
                    $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `expandcode` REGEXP \"$sCode\" LIMIT 1");
                }
                else 
                {
                    return FALSE;
                }
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'lotto_n2_zhixuan' :
            { //乐透二位直选
                $aCode = explode( " ", $sCode );
                if( count($aCode) != 2 )
                {
                    return FALSE;
                }
                $aPorject = $this->oDB->getOne("SELECT `codetype` FROM `projects` WHERE `projectid` = '".$iProjectId."' LIMIT 1");
                if($aPorject['codetype'] == 'digital')
                {//复式
                    $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId LIMIT 1");
                }
                else if($aPorject['codetype'] == 'input')
                {//单式
                    $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `expandcode` REGEXP \"$sCode\" LIMIT 1");
                }
                else 
                {
                    return FALSE;
                }
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'lotto_n2_zhuxuan' :
            { //乐透二位组选
                $aCode = explode( " ", $sCode );
                if( count($aCode) != 2 )
                {
                    return FALSE;
                }
                sort($aCode);
                $sCode = implode( " ", $aCode );
                $aPorject = $this->oDB->getOne("SELECT `codetype` FROM `projects` WHERE `projectid` = '".$iProjectId."' LIMIT 1");
                if($aPorject['codetype'] == 'digital')
                {//复式
                    $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId LIMIT 1");
                }
                else if($aPorject['codetype'] == 'input')
                {//单式
                    $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `expandcode` REGEXP \"$sCode\" LIMIT 1");
                }
                else 
                {
                    return FALSE;
                }
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'lotto_budingwei' :
            { //乐透不定位
                $aCode = explode( " ", $sCode );
                if( count($aCode) != 3 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " LIMIT 1");
                if( count($aRow) == 0 )
                {
                    return FALSE;
                }
                $aExpandCode = explode("|",$aRow['expandcode']);
                $aCode = array_unique($aCode);
                $iRates = count(array_intersect($aExpandCode,$aCode)); // 匹配次数
                $fBingoMoney = floatval( $aRow['prize'] * $iRates );
                return ($this->oDB->ar() && $fBingoMoney>0) ? $fBingoMoney : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'lotto_dingweidan' :
            { //乐透定位胆
                $aCode = explode( " ", $sCode );
                if( count($aCode) != 1 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " LIMIT 1");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
             // ---------------------------------------------------------------------------------
            case 'lotto_dingdanshuang' :
            { //乐透定单双
                $aCode = explode( " ", $sCode );
                if( count($aCode) != 5 )
                {
                    return FALSE;
                }
                 //统计单双个数
                $iSingleCount = 0;//单号个数
                $iDoubleCount = 0;//双号码个数
                foreach ($aCode as $sCodeValue)
                {
                    $sCodeValue%2 == 0 ? $iDoubleCount++ : $iSingleCount++;
                }
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `expandcode` REGEXP \"$iSingleCount\" "
                                . " LIMIT 1");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
             // ---------------------------------------------------------------------------------
            case 'lotto_zhongwei' :
            { //乐透猜中位
                $aCode = explode( " ", $sCode );
                if( count($aCode) != 5 )
                {
                    return FALSE;
                }
                sort($aCode);
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `expandcode` REGEXP \"$aCode[2]\" "
                                . " LIMIT 1");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
             // ---------------------------------------------------------------------------------
            case 'lotto_rx1' :
            case 'lotto_rx2' :
            case 'lotto_rx3' :
            case 'lotto_rx4' :
            case 'lotto_rx5' :
            case 'lotto_rx6' :
            case 'lotto_rx7' :
            case 'lotto_rx8' :
            { //乐透任选
                $iParam = intval(substr($sTagName,-1));//选择号码个数
                $aCode = explode( " ", $sCode );
                if( count($aCode) != 5 )
                {
                    return FALSE;
                }
                $aPorject = $this->oDB->getOne("SELECT `codetype` FROM `projects` WHERE `projectid` = '".$iProjectId."' LIMIT 1");
                $aRow = $this->oDB->getOne("SELECT `expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " LIMIT 1");
                if( count($aRow) == 0 || count($aPorject) == 0 )
                {
                    return FALSE;
                }
                $aCode = array_unique($aCode);
                if($aPorject['codetype'] == 'digital')
                {//复式
                    $aExpandCode = explode("|", $aRow['expandcode']);
                    $iRates = count(array_intersect($aExpandCode,$aCode)); // 匹配次数
                    if( $iRates < $iParam && $iParam <= 5)
                    {//匹配到没有中奖的单子
                        return FALSE;
                    }
                    if( $iParam > 5 && $iRates != 5)
                    {//匹配到没有中奖的单子
                        return FALSE;
                    }
                    //计算中奖倍数，
                    $iBonusTimes = 0;
                    if( $iParam <= 5 )
                    {
                        //如：任选二中二，选择的号码与中奖号码交集个数为3，则中奖倍数为:C(3,2)=3.
                        $iBonusTimes = $this->GetCombinCount( $iRates, $iParam );
                    }
                    else if(in_array($iParam,array(6,7,8)))
                    {
                        //如任选八中五:C(n-5,8-5);
                        $iBonusTimes = $this->GetCombinCount( count($aExpandCode) - 5, $iParam - 5 );
                    }
                    else
                    {
                        return FALSE;
                    }
                }
                else if($aPorject['codetype'] == 'input')
                {//单式
                    //计算中奖倍数，
                    $iBonusTimes = 0;
                    sort($aCode);
                    $iSelect = $iParam > 5 ? 5 : $iParam;
                    $aTmpCode = $this->getCombination($aCode, $iSelect);//可能中奖的组合
                    sort($aTmpCode);
                    foreach ( $aTmpCode as $sCode )
                    {
                        $sCode = trim($sCode,' ');
                        if( $iParam > 5 )
                        {
                            $sCode = str_replace(' ','[^\\|]*',$sCode);
                        }
                        $aRegExp[] = '('.$sCode.')';
                    }
                    $sRegExpTmp = implode("|", $aRegExp);
                    $tmpArray = array();
                    $iBonusTimes = preg_match_all("/$sRegExpTmp/", $aRow['expandcode'], $tmpArray);// 匹配次数
                    unset($tmpArray);
                }
                else 
                {
                    return FALSE;
                }
                //计算最终的奖金
                $fBingoMoney = floatval( $aRow['prize'] * $iBonusTimes );
                return ($this->oDB->ar() && $fBingoMoney>0) ? $fBingoMoney : FALSE;
            }
           default:
               break;
        }
        return FALSE;
    }
}
?>