<?php
/**
 * 文件 : /_app/model/sendbonus.php
 * 功能 : 数据模型 - 派奖:数字型
 *
 * @author    tom,mark
 * @version   1.2.0
 * @package   highgame
 */

class model_sendbonus_digital extends model_sendbonus_base
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
            case 'n3_zhixuan' :
            { // 3位数字直选的中奖金额判断
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aNumber[0] = substr($sCode,0,1);
                $aNumber[1] = substr($sCode,1,1);
                $aNumber[2] = substr($sCode,2,1);
                $aPorject = $this->oDB->getOne("SELECT `codetype` FROM `projects` WHERE `projectid` = '".$iProjectId."' LIMIT 1");
                if($aPorject['codetype'] == 'digital')
                {
                    $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId LIMIT 1");
                }
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
            case 'n3_zhixuanhezhi' :
            { // 3位数字直选和值的中奖金额判断
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId LIMIT 1");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n3_zusan' :
            { // 3位数字组三中奖金额判断
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `level`=1 LIMIT 1");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n3_zuliu' :
            { // 3位数字组六中奖金额判断
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `level`=2 LIMIT 1");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n3_hunhezuxuan' :
            { // 3位数字混合组选
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aNumber[0] = substr($sCode,0,1);
                $aNumber[1] = substr($sCode,1,1);
                $aNumber[2] = substr($sCode,2,1);
                $aNumber = array_unique($aNumber);
                $iLevel = 1;
                if( 2 == count($aNumber) )
                { // 当前期为组3号
                    $iLevel = 1;
                }
                elseif( 3 == count($aNumber) )
                {
                    $iLevel = 2;
                }
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `level`=$iLevel LIMIT 1"); 
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n3_hezhi' :
            { // 3位数字组选和值
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aNumber[0] = substr($sCode,0,1);
                $aNumber[1] = substr($sCode,1,1);
                $aNumber[2] = substr($sCode,2,1);
                $aNumber = array_unique($aNumber);
                $iLevel = 1;
                if( 2 == count($aNumber) )
                { // 当前期为组3号
                    $iLevel = 1;
                }
                elseif( 3 == count($aNumber) )
                {
                    $iLevel = 2;
                }
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `level`='".$iLevel."' LIMIT 1");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n1_dingwei' :
            {
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `expandcode` REGEXP \"$sCode\" "
                                . " LIMIT 1");
                //print_r($aRow);exit;
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n2_common' :
            { // 2位数字通用
                if( strlen($sCode) != 2 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId LIMIT 1");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n2_dxds' :
            { // 3位数字. 大小单双
                if( strlen($sCode) != 2 )
                {
                    return FALSE;
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
                $aFristString = array();
                $aSecondString = array();
                foreach( $aBSAD AS $k=>$v )
                {
                    if(in_array( $iFristNumber,  $v ))
                    {
                        $aFristString[]  = $k;
                    }
                    if(in_array( $iSecondNumber,  $v ))
                    {
                        $aSecondString[] = $k;
                    }
                }
                unset( $iFristNumber, $iSecondNumber );
                $aRow = $this->oDB->getOne("SELECT `expandcode`,`prize` FROM `expandcode`"
                                            ." WHERE `projectid`=$iProjectId  LIMIT 1");
                if( count($aRow) == 0 )
                {
                    return FALSE;
                }
                $aExpandCode = explode('|',$aRow['expandcode']);
                $aFirstExpandCode = str_split($aExpandCode[0],1);
                $aSecodExpandCode = str_split($aExpandCode[1],1);
                //求取可能中奖的注数
                $iBonusTimes = count(array_intersect($aFirstExpandCode,$aFristString)) * count(array_intersect($aSecodExpandCode,$aSecondString));
                return $this->oDB->ar() ? $aRow['prize']*$iBonusTimes : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n3_1mbudingwei' :
            { // 3位数字. 后三不定位玩法(1码)
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " LIMIT 1");
                if( count($aRow) == 0 )
                {
                    return FALSE;
                }
                $aExpandCode = explode('|',$aRow['expandcode']);
                $aCode = str_split($sCode,1);
                $aCode = array_unique($aCode);
                $iRates = count(array_intersect($aExpandCode,$aCode)); // 匹配次数
                $fBingoMoney = floatval( $aRow['prize'] * $iRates );
                return ($this->oDB->ar() && $fBingoMoney>0) ? $fBingoMoney : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n3_2mbudingwei' :
            { // 3位数字. 后三不定位玩法(2码)
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " LIMIT 1");
                if( count($aRow) == 0 )
                {
                    return FALSE;
                }
                $aExpandCode = explode('|',$aRow['expandcode']);
                $aCode = str_split($sCode,1);
                $aCode = array_unique($aCode);
                $iRates = count(array_intersect($aExpandCode,$aCode)) == 2 ? 1 : 3; // 匹配次数
                $fBingoMoney = floatval( $aRow['prize'] * $iRates );
                return ($this->oDB->ar() && $fBingoMoney>0) ? $fBingoMoney : FALSE;
            }
            /* ==========--------------------------数字型新增数字型玩法派奖程序----------------------------------========*/
            /*------------------------------五星玩法--------------------------------------------*/
            //(1)五星直选玩法派奖
            case 'n5_zhixuan':
                if( strlen($sCode) != 5 )
                {
                    return FALSE;
                }
                $aPorject = $this->oDB->getOne("SELECT `codetype` FROM `projects` WHERE `projectid` = '".$iProjectId."' LIMIT 1");
                if($aPorject['codetype'] == 'digital')
                {
                    $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId LIMIT 1");
                }
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
                break;
            //(2)五星直选_组合玩法派奖
            case 'n5_zhuhe':
                if( strlen($sCode) != 5 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getAll("SELECT `level`,`prize`,`expandcode` FROM `expandcode` WHERE `projectid`=".$iProjectId);
                if(count($aRow) != 5)
                {
                    return FALSE;
                }
                $fBingoMoney = 0.00;//最终奖金
                $aCode = str_split($sCode);
                foreach ($aRow as $aExpandCode)
                {
                    $aReg = array();
                    $iNumCount = intval($aExpandCode['level'])-1;//多个奖级,五星组合从一星到五星一共五个奖级
                    for ($j = 0; $j < $iNumCount; $j++)
                    {
                        $aReg[] = ".*";
                    }
                    for($i = $iNumCount; $i < 5; $i++)
                    {
                        $aReg[] = ".*".$aCode[$i].".*";
                    }
                    $sReg = "/^".implode("\|",$aReg)."$/";
                    $aBuyCode = explode("|",$aExpandCode['expandcode']);
                    $iTimes = 1;
                    for($i = 0; $i < $aExpandCode['level']-1; $i++)
                    {
                        $iTimes *= strlen($aBuyCode[$i]);
                    }
                    if(preg_match($sReg,$aExpandCode['expandcode']) == 1)
                    {
                        $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取各个奖级的奖金
                    }
                }
                return $this->oDB->ar() ? $fBingoMoney : FALSE;
                break;
            //(3)五星通选玩法派奖
            case 'n5_tongxuan':
                if( strlen($sCode) != 5 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getAll("SELECT `level`,`prize`,`expandcode` FROM `expandcode` WHERE `projectid`=".$iProjectId);
                if(count($aRow) != 3)
                {
                    return FALSE;
                }
                $fBingoMoney = 0.00;//最终奖金
                $aCode = str_split($sCode);
                $aProjectCode = explode("|",$aRow[0]['expandcode']);
                for ($i = 0; $i < 5; $i++)
                {//判断各个位上的交集
                    $aSameCodeCount[$i] = strpos($aProjectCode[$i],$aCode[$i]) !== FALSE ? 1 :0;
                }
                foreach ($aRow as $aExpandCode)
                {
                    switch ($aExpandCode['level'])
                    {
                        case 1://一等奖号码,五个号码全中
                            if( $aSameCodeCount[0] == 1 && $aSameCodeCount[1] == 1 
                                && $aSameCodeCount[2] == 1 && $aSameCodeCount[3] == 1 && $aSameCodeCount[4] == 1)
                            {//五位全中
                                $fBingoMoney += $aExpandCode['prize'];//获取奖金
                            }
                            break;
                        case 2://二等奖号码
                            if( $aSameCodeCount[0] == 1 && $aSameCodeCount[1] == 1 && $aSameCodeCount[2] == 1 )
                            {//中前三位：只需要确定前三位相同，第五位不相同
                                $iTimes = strlen($aProjectCode[3])*(strlen($aProjectCode[4]) -$aSameCodeCount[4]);
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                            }
                            if( $aSameCodeCount[2] == 1 && $aSameCodeCount[3] == 1 && $aSameCodeCount[4] == 1 )
                            {//中后三位：只需要确定后三位相同，第一位不相同
                                $iTimes = (strlen($aProjectCode[0])-1)*strlen($aProjectCode[1]);
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                            }
                            break;
                        case 3://三等奖号码
                            if( $aSameCodeCount[0] == 1 && $aSameCodeCount[1] == 1)
                            {//中前两位：只需要确定前两位相同，第三位不相同
                                $iTimes = (strlen($aProjectCode[2])-$aSameCodeCount[2])*strlen($aProjectCode[3])*strlen($aProjectCode[4]);
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                            }
                            if( $aSameCodeCount[3] == 1 && $aSameCodeCount[4] == 1)
                            {//中后两位：只需要确定后两位相同，第三位不相同
                                $iTimes = strlen($aProjectCode[0])*strlen($aProjectCode[1])*(strlen($aProjectCode[2])-1);
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                            }
                            break;
                        default:
                            return FALSE;
                            break;
                    }
                }
                return $this->oDB->ar() ? $fBingoMoney : FALSE;
                break;
            //(4)五星组选玩法派奖
            case 'n5_zhuxuan':
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId LIMIT 1");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
                break;
            /*------------------------------四星玩法--------------------------------------------*/
            //(5)四星直选玩法派奖
            case 'n4_zhixuan':
                if( strlen($sCode) != 4 )
                {
                    return FALSE;
                }
                $aPorject = $this->oDB->getOne("SELECT `codetype` FROM `projects` WHERE `projectid` = '".$iProjectId."' LIMIT 1");
                if($aPorject['codetype'] == 'digital')
                {
                    $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId LIMIT 1");
                }
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
                break;
            //(6)四星直选_组合玩法派奖
            case 'n4_zhuhe':
                if( strlen($sCode) != 4 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getAll("SELECT `level`,`prize`,`expandcode` FROM `expandcode` WHERE `projectid`=".$iProjectId);
                if(count($aRow) != 4)
                {
                    return FALSE;
                }
                $fBingoMoney = 0.00;//最终奖金
                $aCode = str_split($sCode);
                foreach ($aRow as $aExpandCode)
                {
                    $aReg = array();
                    $iNumCount = intval($aExpandCode['level'])-1;//多个奖级,四星组合从一星到四星一共四个奖级
                    for ($j = 0; $j < $iNumCount; $j++)
                    {
                        $aReg[] = ".*";
                    }
                    for($i = $iNumCount; $i < 4; $i++)
                    {
                        $aReg[] = ".*".$aCode[$i].".*";
                    }
                    $sReg = "/^".implode("\|",$aReg)."$/";
                    $aBuyCode = explode("|",$aExpandCode['expandcode']);
                    $iTimes = 1;
                    for($i = 0; $i < $aExpandCode['level']-1; $i++)
                    {
                        $iTimes *= strlen($aBuyCode[$i]);
                    }
                    if(preg_match($sReg,$aExpandCode['expandcode']) == 1)
                    {
                        $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取各个奖级的奖金
                    }
                }
                return $this->oDB->ar() ? $fBingoMoney : FALSE;
                break;
            //(7)四星通选玩法派奖
            case 'n4_tongxuan':
                if( strlen($sCode) != 4 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getAll("SELECT `level`,`prize`,`expandcode` FROM `expandcode` WHERE `projectid`=".$iProjectId);
                if(count($aRow) != 3)
                {
                    return FALSE;
                }
                $fBingoMoney = 0.00;//最终奖金
                $aCode = str_split($sCode);
                $aProjectCode = explode("|",$aRow[0]['expandcode']);
                for ($i = 0; $i < 4; $i++)
                {//判断各个位上的交集
                    $aSameCodeCount[$i] = strpos($aProjectCode[$i],$aCode[$i]) !== FALSE ? 1 :0;
                }
                foreach ($aRow as $aExpandCode)
                {
                    switch ($aExpandCode['level'])
                    {
                        case 1://一等奖号码,四个号码全中
                            if( $aSameCodeCount[0] == 1 && $aSameCodeCount[1] == 1 
                                && $aSameCodeCount[2] == 1 && $aSameCodeCount[3] == 1)
                            {//四位全中
                                $fBingoMoney += $aExpandCode['prize'];//获取奖金
                            }
                            break;
                        case 2://二等奖号码
                            if( $aSameCodeCount[0] == 1 && $aSameCodeCount[1] == 1 && $aSameCodeCount[2] == 1 )
                            {//中前三位
                                $iTimes = strlen($aProjectCode[3]) -$aSameCodeCount[3];
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                            }
                            if( $aSameCodeCount[1] == 1 && $aSameCodeCount[2] == 1 && $aSameCodeCount[3] == 1 )
                            {//中后三位
                                $iTimes = strlen($aProjectCode[0]) -$aSameCodeCount[0];
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                            }
                            break;
                        case 3://三等奖号码
                            if( $aSameCodeCount[0] == 1 && $aSameCodeCount[1] == 1)
                            {//中前两位：只需要确定前两位相同，第三位不相同
                                $iTimes = (strlen($aProjectCode[2])-$aSameCodeCount[2])*strlen($aProjectCode[3]);
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                            }
                            if( $aSameCodeCount[2] == 1 && $aSameCodeCount[3] == 1)
                            {//中后两位：只需要确定后两位相同，第二位不相同
                                $iTimes = strlen($aProjectCode[0])*(strlen($aProjectCode[1])-$aSameCodeCount[1]);
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                            }
                            break;
                        default:
                            return FALSE;
                            break;
                    }
                }
                return $this->oDB->ar() ? $fBingoMoney : FALSE;
                break;
            //(8)四星组选玩法派奖
            case 'n4_zhuxuan':
            /*------------------------------三星玩法--------------------------------------------*/
            //(9)三星直选跨度玩法派奖
            case 'n3_kuandu':
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId LIMIT 1");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
                break;
           //(10)三星组选包胆玩法派奖
            case 'n3_baodan':
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aNumber = str_split($sCode);
                $aNumber = array_unique($aNumber);
                $iLevel = 1;
                if( count($aNumber) == 2 )
                {//当前期开奖号码为组三号码
                    $iLevel = 1;
                }
                elseif( 3 == count($aNumber) )
                {//当前期开奖号码为组六号码
                    $iLevel = 2;
                }
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                . " AND `level`=$iLevel LIMIT 1");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
                break;
           //(11)三星组合玩法派奖
            case 'n3_zhuhe':
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getAll("SELECT `level`,`prize`,`expandcode` FROM `expandcode` WHERE `projectid`=".$iProjectId);
                if(count($aRow) != 3)
                {
                    return FALSE;
                }
                $fBingoMoney = 0.00;//最终奖金
                $aCode = str_split($sCode);
                foreach ($aRow as $aExpandCode)
                {
                    $aReg = array();
                    $iNumCount = intval($aExpandCode['level'])-1;//多个奖级,三星组合从一星到三星一共三个奖级
                    for ($j = 0; $j < $iNumCount; $j++)
                    {
                        $aReg[] = ".*";
                    }
                    for($i = $iNumCount; $i < 3; $i++)
                    {
                        $aReg[] = ".*".$aCode[$i].".*";
                    }
                    $sReg = "/^".implode("\|",$aReg)."$/";
                    $aBuyCode = explode("|",$aExpandCode['expandcode']);
                    $iTimes = 1;
                    for($i = 0; $i < $aExpandCode['level']-1; $i++)
                    {
                        $iTimes *= strlen($aBuyCode[$i]);
                    }
                    if(preg_match($sReg,$aExpandCode['expandcode']) == 1)
                    {
                        $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取各个奖级的奖金
                    }
                }
                return $this->oDB->ar() ? $fBingoMoney : FALSE;
                break;
           //(12)三星通选玩法玩法派奖
            case 'n3_tongxuan':
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getAll("SELECT `level`,`prize`,`expandcode` FROM `expandcode` WHERE `projectid`=".$iProjectId);
                if(count($aRow) != 3)
                {
                    return FALSE;
                }
                $fBingoMoney = 0.00;//最终奖金
                $aCode = str_split($sCode);
                $aProjectCode = explode("|",$aRow[0]['expandcode']);
                for ($i = 0; $i < 3; $i++)
                {//判断各个位上的交集
                    $aSameCodeCount[$i] = strpos($aProjectCode[$i],$aCode[$i]) !== FALSE ? 1 :0; 
                }
                foreach ($aRow as $aExpandCode)
                {
                    switch ($aExpandCode['level'])
                    {
                        case 1://一等奖号码
                            if( $aSameCodeCount[0] == 1 && $aSameCodeCount[1] == 1 && $aSameCodeCount[2] == 1)
                            {//三位全中
                                $fBingoMoney += $aExpandCode['prize'];//获取奖金
                            }
                            break;
                        case 2://二等奖号码
                            if( $aSameCodeCount[1] == 1 && $aSameCodeCount[2] == 1 )
                            {//中二、三位
                                $iTimes = strlen($aProjectCode[0]) -$aSameCodeCount[0];
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                            }
                            if( $aSameCodeCount[0] == 1 && $aSameCodeCount[2] == 1)
                            {//中一、三位
                                $iTimes = strlen($aProjectCode[1]) -$aSameCodeCount[1];
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                            }
                            if( $aSameCodeCount[0] == 1 && $aSameCodeCount[1] == 1)
                            {//中一、二位
                                $iTimes = strlen($aProjectCode[2]) -$aSameCodeCount[2];
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                            }
                            break;
                        case 3://三等奖号码
                            if( $aSameCodeCount[2] == 1 )
                            {//中第三位
                                $iTimes = (strlen($aProjectCode[0])-$aSameCodeCount[0])*(strlen($aProjectCode[1])-$aSameCodeCount[1]);
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                            }
                            if( $aSameCodeCount[1] == 1)
                            {//中第二位
                                $iTimes = (strlen($aProjectCode[0])-$aSameCodeCount[0])*(strlen($aProjectCode[2])-$aSameCodeCount[2]);
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                            }
                            if( $aSameCodeCount[0] == 1)
                            {//中第一位
                                $iTimes = (strlen($aProjectCode[1])-$aSameCodeCount[1])*(strlen($aProjectCode[2])-$aSameCodeCount[2]);
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                            }
                            break;
                        default:
                            return FALSE;
                            break;
                    }
                }
                return $this->oDB->ar() ? $fBingoMoney : FALSE;
                break;
           //(13)三星和值尾数玩法派奖
            case 'n3_hezhiweishu':
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId LIMIT 1");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
                break;
            //(14)三星特殊玩法派奖
            case 'n3_teshu':
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aTmpCode = str_split($sCode);
                if($aTmpCode[0] == $aTmpCode[1] && $aTmpCode[1] == $aTmpCode[2])
                {
                    $iSpecialCode = 0;//豹子
                }
                elseif( ($aTmpCode[0]+1 == $aTmpCode[1] && $aTmpCode[1]+1 == $aTmpCode[2])
                || ($aTmpCode[0]-1 == $aTmpCode[1] && $aTmpCode[1]-1 == $aTmpCode[2] )
                || implode("",$aTmpCode) == '901' || implode("",$aTmpCode) == '109')
                {
                    $iSpecialCode = 1;//顺子
                }elseif (count(array_unique($aTmpCode)) == 2)
                {
                    $iSpecialCode = 2;//对子
                }
                else
                {
                    return FALSE;//非豹子、对子、顺子号
                }
                $iLevel = $iSpecialCode + 1;//奖级
                $aRow = $this->oDB->getOne("SELECT `level`,`prize`,`expandcode` FROM `expandcode` 
                    WHERE `projectid`=".$iProjectId." AND `expandcode` REGEXP '".$iSpecialCode ."' AND `level` = '".$iLevel."'");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
                break;
            /*------------------------------二星玩法--------------------------------------------*/
            //(15)二星直选和值玩法派奖
            case 'n2_zhixuanhezhi':
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId LIMIT 1");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
                break;
            /*------------------------------不定位玩法------------------------------------------*/
            //(16)四星一码不定位派奖
            case 'n4_yimabudingwei':
                if( strlen($sCode) != 4 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `expandcode`,`prize` FROM `expandcode` WHERE `projectid`= ".$iProjectId." LIMIT 1");
                if( count($aRow) == 0 )
                {
                    return FALSE;
                }
                $aExpandCode = explode('|',$aRow['expandcode']);
                $aCode = str_split($sCode);
                $aCode = array_unique($aCode);
                $iRates = count(array_intersect($aExpandCode,$aCode)); // 匹配次数
                $fBingoMoney = floatval( $aRow['prize'] * $iRates );
                return ($this->oDB->ar() && $fBingoMoney>0) ? $fBingoMoney : FALSE;
                break;
            //(17)四星二码不定位派奖
            case 'n4_ermabudingwei':
                if( strlen($sCode) != 4 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                . " LIMIT 1");
                if( count($aRow) == 0 )
                {
                    return FALSE;
                }
                $aExpandCode = explode('|',$aRow['expandcode']);
                $aCode = str_split($sCode,1);
                $aCode = array_unique($aCode);
                $iIntersectCount = count(array_intersect($aExpandCode,$aCode));
                $iRates = $this->GetCombinCount($iIntersectCount,2);//中奖注数
                $fBingoMoney = floatval( $aRow['prize'] * $iRates );
                return ($this->oDB->ar() && $fBingoMoney>0) ? $fBingoMoney : FALSE;
                break;
            //(18)五星二码不定位派奖
            case 'n5_ermabudingwei':
                if( strlen($sCode) != 5 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                . " LIMIT 1");
                if( count($aRow) == 0 )
                {
                    return FALSE;
                }
                $aExpandCode = explode('|',$aRow['expandcode']);
                $aCode = str_split($sCode,1);
                $aCode = array_unique($aCode);
                $iIntersectCount = count(array_intersect($aExpandCode,$aCode));
                $iRates = $this->GetCombinCount($iIntersectCount,2);//中奖注数
                $fBingoMoney = floatval( $aRow['prize'] * $iRates );
                return ($this->oDB->ar() && $fBingoMoney>0) ? $fBingoMoney : FALSE;
                break;
            //(19)五星三码不定位派奖
            case 'n5_shanmabudingwei':
                if( strlen($sCode) != 5 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                . " LIMIT 1");
                if( count($aRow) == 0 )
                {
                    return FALSE;
                }
                $aExpandCode = explode('|',$aRow['expandcode']);
                $aCode = str_split($sCode,1);
                $aCode = array_unique($aCode);
                $iIntersectCount = count(array_intersect($aExpandCode,$aCode));
                $iRates = $this->GetCombinCount($iIntersectCount,3);//中奖注数
                $fBingoMoney = floatval( $aRow['prize'] * $iRates );
                return ($this->oDB->ar() && $fBingoMoney>0) ? $fBingoMoney : FALSE;
                break;
            /*------------------------------大小单双玩法-----------------------------------------*/
            //(20)三码大小单双玩法派奖
            case 'n3_dxds':
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                static $aBSAD = array(    // 大小单双对应号码
                '0' => array(5,6,7,8,9),  // 大
                '1' => array(0,1,2,3,4),  // 小
                '2' => array(1,3,5,7,9),  // 单
                '3' => array(0,2,4,6,8)   // 双
                );
                $iFristNumber  = substr($sCode,0,1);
                $iSecondNumber = substr($sCode,1,1);
                $iThirdNumber  = substr($sCode,2,1);
                $aFristString  = array();
                $aSecondString = array();
                $aThirdString  = array();
                foreach( $aBSAD AS $k=>$v )
                {
                    if(in_array( $iFristNumber,  $v ))
                    {
                        $aFristString[]  = $k;
                    }
                    if(in_array( $iSecondNumber,  $v ))
                    {
                        $aSecondString[] = $k;
                    }
                    if(in_array( $iThirdNumber,  $v ))
                    {
                        $aThirdString[] = $k;
                    }
                }
                unset( $iFristNumber, $iSecondNumber,$iThirdNumber );
                $aRow = $this->oDB->getOne("SELECT `expandcode`,`prize` FROM `expandcode`"
                ." WHERE `projectid`=$iProjectId  LIMIT 1");
                if( count($aRow) == 0 )
                {
                    return FALSE;
                }
                $aExpandCode = explode('|',$aRow['expandcode']);
                $aFirstExpandCode = str_split($aExpandCode[0],1);
                $aSecodExpandCode = str_split($aExpandCode[1],1);
                $aThirdExpandCode = str_split($aExpandCode[2],1);
                //求取可能中奖的注数
                $iBonusTimes = count(array_intersect($aFirstExpandCode,$aFristString)) * count(array_intersect($aSecodExpandCode,$aSecondString));
                $iBonusTimes *= count(array_intersect($aThirdExpandCode,$aThirdString));
                return $this->oDB->ar() ? $aRow['prize']*$iBonusTimes : FALSE;
                break;
            /*------------------------------趣味型玩法-------------------------------------------*/
            //(20)五码趣味型玩法派奖
            case 'n5_quwei':
                if( strlen($sCode) != 5 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getAll("SELECT `level`,`expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId");
                if( count($aRow) != 2 )
                {
                    return FALSE;
                }
                $fBingoMoney = 0.00;//最终奖金
                $aCode = str_split($sCode);
                $aCode[0] = $aCode[0] > 4 ? 1 : 0;
                $aCode[1] = $aCode[1] > 4 ? 1 : 0;
                $sReg = "/^.*".$aCode[0].".*\|.*".$aCode[1].".*\|.*".$aCode[2].".*\|.*".$aCode[3].".*\|.*".$aCode[4].".*$/";
                $iLevel = preg_match($sReg,$aRow[0]['expandcode']) == 1 ? 1: 2;
                $aTheBuyCode = explode("|" , $aRow[0]['expandcode']);
                foreach ($aRow as $aExpandCode)
                {
                    if($iLevel == 1)
                    {//中一等奖的情况下,可能中多注其它的二等奖号码
                        if($aExpandCode['level'] == $iLevel)
                        {
                            $fBingoMoney += $aExpandCode['prize'];//获取各个奖级的奖金
                        }
                        else
                        {
                            $fBingoMoney += $aExpandCode['prize']*(strlen($aTheBuyCode[0])*strlen($aTheBuyCode[1])-1);//获取各个奖级的奖金
                        }
                    }
                    elseif ($iLevel == 2 && $aExpandCode['level'] == $iLevel)
                    {//所有都是二等奖号码
                        $fBingoMoney += $aExpandCode['prize']*strlen($aTheBuyCode[0])*strlen($aTheBuyCode[1]);//获取各个奖级的奖金
                    }
                }
                return $this->oDB->ar() ? $fBingoMoney : FALSE;
                break;
            //(21)四码趣味型玩法派奖
            case 'n4_quwei':
                if( strlen($sCode) != 4 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getAll("SELECT `level`,`expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId");
                if( count($aRow) != 2 )
                {
                    return FALSE;
                }
                $fBingoMoney = 0.00;//最终奖金
                $aCode = str_split($sCode);
                $aCode[0] = $aCode[0] > 4 ? 1 : 0;
                $sReg = "/^.*".$aCode[0].".*\|.*".$aCode[1].".*\|.*".$aCode[2].".*\|.*".$aCode[3].".*$/";
                $iLevel = preg_match($sReg,$aRow[0]['expandcode']) == 1 ? 1: 2;
                $aTheBuyCode = explode("|" , $aRow[0]['expandcode']);
                foreach ($aRow as $aExpandCode)
                {
                    if($iLevel == 1)
                    {//中一等奖的情况下,可能中多注其它的二等奖号码
                        if($aExpandCode['level'] == $iLevel)
                        {
                            $fBingoMoney += $aExpandCode['prize'];//获取各个奖级的奖金
                        }
                        else 
                        {
                            $fBingoMoney += $aExpandCode['prize']*(strlen($aTheBuyCode[0])-1);//获取各个奖级的奖金    
                        }
                    }
                    elseif ($iLevel == 2 && $aExpandCode['level'] == $iLevel)
                    {//所有都是二等奖号码
                        $fBingoMoney += $aExpandCode['prize']*strlen($aTheBuyCode[0]);//获取各个奖级的奖金    
                    }
                }
                return $this->oDB->ar() ? $fBingoMoney : FALSE;
                break;
            //(22)三码趣味型玩法派奖
            case 'n3_quwei':
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getAll("SELECT `level`,`expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId");
                if( count($aRow) != 2 )
                {
                    return FALSE;
                }
                $fBingoMoney = 0.00;//最终奖金
                $aCode = str_split($sCode);
                $aCode[0] = $aCode[0] > 4 ? 1 : 0;
                $sReg = "/^.*".$aCode[0].".*\|.*".$aCode[1].".*\|.*".$aCode[2].".*$/";
                $iLevel = preg_match($sReg,$aRow[0]['expandcode']) == 1 ? 1: 2;
                $aTheBuyCode = explode("|" , $aRow[0]['expandcode']);
                foreach ($aRow as $aExpandCode)
                {
                    if($iLevel == 1)
                    {//中一等奖的情况下,可能中多注其它的二等奖号码
                        if($aExpandCode['level'] == $iLevel)
                        {
                            $fBingoMoney += $aExpandCode['prize'];//获取各个奖级的奖金
                        }
                        else
                        {
                            $fBingoMoney += $aExpandCode['prize']*(strlen($aTheBuyCode[0])-1);//获取各个奖级的奖金
                        }
                    }
                    elseif ($iLevel == 2 && $aExpandCode['level'] == $iLevel)
                    {//所有都是二等奖号码
                        $fBingoMoney += $aExpandCode['prize']*strlen($aTheBuyCode[0]);//获取各个奖级的奖金
                    }
                }
                return $this->oDB->ar() ? $fBingoMoney : FALSE;
                break;
            //(23)五码区间型玩法派奖
            case 'n5_qujian':
                if( strlen($sCode) != 5 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getAll("SELECT `level`,`expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId");
                if( count($aRow) != 2 )
                {
                    return FALSE;
                }
                $fBingoMoney = 0.00;//最终奖金
                $aCode = str_split($sCode);
                $aCode[0] = intval($aCode[0]/2);
                $aCode[1] = intval($aCode[1]/2);
                $sReg = "/^.*".$aCode[0].".*\|.*".$aCode[1].".*\|.*".$aCode[2].".*\|.*".$aCode[3].".*\|.*".$aCode[4].".*$/";
                $iLevel = preg_match($sReg,$aRow[0]['expandcode']) == 1 ? 1: 2;
                $aTheBuyCode = explode("|" , $aRow[0]['expandcode']);
                foreach ($aRow as $aExpandCode)
                {
                    if($iLevel == 1)
                    {//中一等奖的情况下,可能中多注其它的二等奖号码
                        if($aExpandCode['level'] == $iLevel)
                        {
                            $fBingoMoney += $aExpandCode['prize'];//获取各个奖级的奖金
                        }
                        else
                        {
                            $fBingoMoney += $aExpandCode['prize']*(strlen($aTheBuyCode[0])*strlen($aTheBuyCode[1])-1);//获取各个奖级的奖金
                        }
                    }
                    elseif ($iLevel == 2 && $aExpandCode['level'] == $iLevel)
                    {//所有都是二等奖号码
                        $fBingoMoney += $aExpandCode['prize']*strlen($aTheBuyCode[0])*strlen($aTheBuyCode[1]);//获取各个奖级的奖金
                    }
                }
                return $this->oDB->ar() ? $fBingoMoney : FALSE;
                break;
            //(24)四码区间型玩法派奖
            case 'n4_qujian':
                if( strlen($sCode) != 4 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getAll("SELECT `level`,`expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId");
                if( count($aRow) != 2 )
                {
                    return FALSE;
                }
                $fBingoMoney = 0.00;//最终奖金
                $aCode = str_split($sCode);
                $aCode[0] = intval($aCode[0]/2);
                $sReg = "/^.*".$aCode[0].".*\|.*".$aCode[1].".*\|.*".$aCode[2].".*\|.*".$aCode[3].".*$/";
                $iLevel = preg_match($sReg,$aRow[0]['expandcode']) == 1 ? 1: 2;
                $aTheBuyCode = explode("|" , $aRow[0]['expandcode']);
                foreach ($aRow as $aExpandCode)
                {
                    if($iLevel == 1)
                    {//中一等奖的情况下,可能中多注其它的二等奖号码
                        if($aExpandCode['level'] == $iLevel)
                        {
                            $fBingoMoney += $aExpandCode['prize'];//获取各个奖级的奖金
                        }
                        else
                        {
                            $fBingoMoney += $aExpandCode['prize']*(strlen($aTheBuyCode[0])-1);//获取各个奖级的奖金
                        }
                    }
                    elseif ($iLevel == 2 && $aExpandCode['level'] == $iLevel)
                    {//所有都是二等奖号码
                        $fBingoMoney += $aExpandCode['prize']*strlen($aTheBuyCode[0]);//获取各个奖级的奖金
                    }
                }
                return $this->oDB->ar() ? $fBingoMoney : FALSE;
                break;
            //(25)三码区间型玩法派奖
            case 'n3_qujian':
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getAll("SELECT `level`,`expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId");
                if( count($aRow) != 2 )
                {
                    return FALSE;
                }
                $fBingoMoney = 0.00;//最终奖金
                $aCode = str_split($sCode);
                $aCode[0] = intval($aCode[0]/2);
                $sReg = "/^.*".$aCode[0].".*\|.*".$aCode[1].".*\|.*".$aCode[2].".*$/";
                $iLevel = preg_match($sReg,$aRow[0]['expandcode']) == 1 ? 1: 2;
                $aTheBuyCode = explode("|" , $aRow[0]['expandcode']);
                foreach ($aRow as $aExpandCode)
                {
                    if($iLevel == 1)
                    {//中一等奖的情况下,可能中多注其它的二等奖号码
                        if($aExpandCode['level'] == $iLevel)
                        {
                            $fBingoMoney += $aExpandCode['prize'];//获取各个奖级的奖金
                        }
                        else 
                        {
                            $fBingoMoney += $aExpandCode['prize']*(strlen($aTheBuyCode[0])-1);//获取各个奖级的奖金    
                        }
                    }
                    elseif ($iLevel == 2 && $aExpandCode['level'] == $iLevel)
                    {//所有都是二等奖号码
                        $fBingoMoney += $aExpandCode['prize']*strlen($aTheBuyCode[0]);//获取各个奖级的奖金    
                    }
                }
                return $this->oDB->ar() ? $fBingoMoney : FALSE;
                break;
            //(26)五星特殊玩法派奖
            case 'n5_teshu':
                if( strlen($sCode) != 5 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `level`,`expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId LIMIT 1");
                $iLevel = $aRow['level'];
                $aExpandCode = explode('|',$aRow['expandcode']);
                $aCode = str_split($sCode,1);
                $iIntersectCount = 0;//交集个数
                $fBingoMoney = 0.00;//最终奖金
                switch ($iLevel)
                {
                    case 1:
                        $aCode = array_unique($aCode);
                        $iIntersectCount = count(array_intersect($aExpandCode,$aCode));
                        break;
                    case 2:
                    case 3:
                    case 4:
                        $aNumberCount = array_count_values($aCode);
                        $aDoubleCode = array();
                        foreach ($aNumberCount as $iCode => $iCount)
                        {
                            if( $iCount >= $iLevel )
                            {
                                $aDoubleCode[] = $iCode;//双数及以上
                            }
                        }
                        $iIntersectCount = count(array_intersect($aExpandCode,$aDoubleCode));
                        break;
                    default:
                        break;
                }
                $iRates = $this->GetCombinCount($iIntersectCount,1);//中奖注数
                $fBingoMoney = floatval( $aRow['prize'] * $iRates );
                return ($this->oDB->ar() && $fBingoMoney>0) ? $fBingoMoney : FALSE;
                break;
            default:
                break;
        }
        return FALSE;
    }
}
?>