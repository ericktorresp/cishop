<?php
/**
 * 数据模型: 获取方案真正奖金值
 * 
 * @author     mark
 * @version    1.0.0
 * @package    highadmin
 */

class model_getprize extends basemodel
{
    function __construct( $aDBO = array())
    {
        parent::__construct( $aDBO );
    }


    /**
     * 获取方案真正奖金值
     * @param int       $iProjectId 方案ID
     * @param int       $iMethodId 方案的玩法ID
     * @param array     $aPrizelevel 扩展号码详情
     * @param string    $sBounusCode 开奖号码
     * @return array
     * 
     * @author mark
     * 
     */
    public function getProjectPrize($iProjectId = 0, $iMethodId = 0, $aPrizelevel = array(), $sBounusCode = '')
    {
        $aResult     = array();
        $aLastResult = array();
        if($iMethodId ==0 || $iProjectId ==0 || count($aPrizelevel) <= 1)
        {
            return $aResult;
        }
        $sSql = " SELECT `functionrule` FROM `method` WHERE `methodid` = '".$iMethodId."'";
        $aMethod = $this->oDB->getOne($sSql);
        $aFuntionRule = unserialize($aMethod['functionrule']);
        switch ($aFuntionRule['tagbonus'])
        {
            case 'n5_zhuhe'://五星组合
            case 'n4_zhuhe'://四星组合
            case 'n3_zhuhe'://三星组合
                $fBingoMoney = 0.00;//最终奖金
                $aCode = str_split(substr($sBounusCode,$aFuntionRule['startposition'],$aFuntionRule['codecount']));
                foreach ($aPrizelevel as $aExpandCode)
                {
                    $aReg = array();
                    $iNumCount = intval($aExpandCode['level'])-1;//多个奖级
                    for ($j = 0; $j < $iNumCount; $j++)
                    {
                        $aReg[] = ".*";
                    }
                    for($i = $iNumCount; $i < $aFuntionRule['codecount']; $i++)
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
                        $aResult[$aExpandCode['level']]['times'] = $iTimes;
                        $aResult[$aExpandCode['level']]['prize'] = $aExpandCode['prize']*$iTimes;
                    }
                }
                break;
            //五星通选
            case 'n5_tongxuan';
                $fBingoMoney = 0.00;//最终奖金
                $aCode = str_split(substr($sBounusCode,$aFuntionRule['startposition'],$aFuntionRule['codecount']));
                $aProjectCode = explode("|",$aPrizelevel[0]['expandcode']);
                for ($i = 0; $i < 5; $i++)
                {//判断各个位上的交集
                    $aSameCodeCount[$i] = strpos($aProjectCode[$i],$aCode[$i]) !== FALSE ? 1 :0;
                }
                foreach ($aPrizelevel as $aExpandCode)
                {
                    switch ($aExpandCode['level'])
                    {
                        case 1://一等奖号码,五个号码全中
                            if( $aSameCodeCount[0] == 1 && $aSameCodeCount[1] == 1
                            && $aSameCodeCount[2] == 1 && $aSameCodeCount[3] == 1 && $aSameCodeCount[4] == 1)
                            {//五位全中
                                $fBingoMoney += $aExpandCode['prize'];//获取奖金
                                $aResult[1]['times'] = 1;
                                $aResult[1]['prize'] = $aExpandCode['prize'];
                            }
                            break;
                        case 2://二等奖号码
                            $aResult[2]['times'] = 0;
                            $aResult[2]['prize'] = 0.00;
                            if( $aSameCodeCount[0] == 1 && $aSameCodeCount[1] == 1 && $aSameCodeCount[2] == 1 )
                            {//中前三位：只需要确定前三位相同，第五位不相同
                                $iTimes = strlen($aProjectCode[3])*(strlen($aProjectCode[4]) -$aSameCodeCount[4]);
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                                $aResult[2]['times'] += $iTimes;
                                $aResult[2]['prize'] += $aExpandCode['prize']*$iTimes;
                            }
                            if( $aSameCodeCount[2] == 1 && $aSameCodeCount[3] == 1 && $aSameCodeCount[4] == 1 )
                            {//中后三位：只需要确定后三位相同，第一位不相同
                                $iTimes = (strlen($aProjectCode[0])-1)*strlen($aProjectCode[1]);
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                                $aResult[2]['times'] += $iTimes;
                                $aResult[2]['prize'] += $aExpandCode['prize']*$iTimes;
                            }
                            if($aResult[2]['times'] == 0)
                            {
                                unset($aResult[2]);
                            }
                            break;
                        case 3://三等奖号码
                            $aResult[3]['times'] = 0;
                            $aResult[3]['prize'] = 0.00;
                            if( $aSameCodeCount[0] == 1 && $aSameCodeCount[1] == 1)
                            {//中前两位：只需要确定前两位相同，第三位不相同
                                $iTimes = (strlen($aProjectCode[2])-$aSameCodeCount[2])*strlen($aProjectCode[3])*strlen($aProjectCode[4]);
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                                $aResult[3]['times'] += $iTimes;
                                $aResult[3]['prize'] += $aExpandCode['prize']*$iTimes;
                            }
                            if( $aSameCodeCount[3] == 1 && $aSameCodeCount[4] == 1)
                            {//中后两位：只需要确定后两位相同，第三位不相同
                                $iTimes = strlen($aProjectCode[0])*strlen($aProjectCode[1])*(strlen($aProjectCode[2])-1);
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                                $aResult[3]['times'] += $iTimes;
                                $aResult[3]['prize'] += $aExpandCode['prize']*$iTimes;
                            }
                            if($aResult[3]['times'] == 0)
                            {
                                unset($aResult[3]);
                            }
                            break;
                        default:
                            break;
                    }
                }
                break;
            //四星通选
            case 'n4_tongxuan';
                $fBingoMoney = 0.00;//最终奖金
                $aCode = str_split(substr($sBounusCode,$aFuntionRule['startposition'],$aFuntionRule['codecount']));
                $aProjectCode = explode("|",$aPrizelevel[0]['expandcode']);
                for ($i = 0; $i < 4; $i++)
                {//判断各个位上的交集
                    $aSameCodeCount[$i] = strpos($aProjectCode[$i],$aCode[$i]) !== FALSE ? 1 :0;
                }
                foreach ($aPrizelevel as $aExpandCode)
                {
                    switch ($aExpandCode['level'])
                    {
                        case 1://一等奖号码,四个号码全中
                            if( $aSameCodeCount[0] == 1 && $aSameCodeCount[1] == 1 
                                && $aSameCodeCount[2] == 1 && $aSameCodeCount[3] == 1)
                            {//四位全中
                                $fBingoMoney += $aExpandCode['prize'];//获取奖金
                                $aResult[1]['times'] = 1;
                                $aResult[1]['prize'] = $aExpandCode['prize'];
                            }
                            break;
                        case 2://二等奖号码
                            $aResult[2]['times'] = 0;
                            $aResult[2]['prize'] = 0.00;
                            if( $aSameCodeCount[0] == 1 && $aSameCodeCount[1] == 1 && $aSameCodeCount[2] == 1 )
                            {//中前三位
                                $iTimes = strlen($aProjectCode[3]) -$aSameCodeCount[3];
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                                $aResult[2]['times'] += $iTimes;
                                $aResult[2]['prize'] += $aExpandCode['prize']*$iTimes;
                            }
                            if( $aSameCodeCount[1] == 1 && $aSameCodeCount[2] == 1 && $aSameCodeCount[3] == 1 )
                            {//中后三位
                                $iTimes = strlen($aProjectCode[0]) -$aSameCodeCount[0];
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                                $aResult[2]['times'] += $iTimes;
                                $aResult[2]['prize'] += $aExpandCode['prize']*$iTimes;
                            }
                            if($aResult[2]['times'] == 0)
                            {
                                unset($aResult[2]);
                            }
                            break;
                        case 3://三等奖号码
                            $aResult[3]['times'] = 0;
                            $aResult[3]['prize'] = 0.00;
                            if( $aSameCodeCount[0] == 1 && $aSameCodeCount[1] == 1)
                            {//中前两位：只需要确定前两位相同，第三位不相同
                                $iTimes = (strlen($aProjectCode[2])-$aSameCodeCount[2])*strlen($aProjectCode[3]);
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                                $aResult[3]['times'] += $iTimes;
                                $aResult[3]['prize'] += $aExpandCode['prize']*$iTimes;
                            }
                            if( $aSameCodeCount[2] == 1 && $aSameCodeCount[3] == 1)
                            {//中后两位：只需要确定后两位相同，第二位不相同
                                $iTimes = strlen($aProjectCode[0])*(strlen($aProjectCode[1])-$aSameCodeCount[1]);
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                                $aResult[3]['times'] += $iTimes;
                                $aResult[3]['prize'] += $aExpandCode['prize']*$iTimes;
                            }
                            if($aResult[3]['times'] == 0)
                            {
                                unset($aResult[3]);
                            }
                            break;
                        default:
                            break;
                    }
                }
                break;
            //三星通选
            case 'n4_tongxuan';
                $fBingoMoney = 0.00;//最终奖金
                $aCode = str_split(substr($sBounusCode,$aFuntionRule['startposition'],$aFuntionRule['codecount']));
                $aProjectCode = explode("|",$aPrizelevel[0]['expandcode']);
                for ($i = 0; $i < 4; $i++)
                {//判断各个位上的交集
                    $aSameCodeCount[$i] = strpos($aProjectCode[$i],$aCode[$i]) !== FALSE ? 1 :0;
                }
                foreach ($aPrizelevel as $aExpandCode)
                {
                    switch ($aExpandCode['level'])
                    {
                        case 1://一等奖号码,四个号码全中
                            if( $aSameCodeCount[0] == 1 && $aSameCodeCount[1] == 1 
                                && $aSameCodeCount[2] == 1 && $aSameCodeCount[3] == 1)
                            {//四位全中
                                $fBingoMoney += $aExpandCode['prize'];//获取奖金
                                $aResult[1]['times'] = 1;
                                $aResult[1]['prize'] = $aExpandCode['prize'];
                            }
                            break;
                        case 2://二等奖号码
                            $aResult[2]['times'] = 0;
                            $aResult[2]['prize'] = 0.00;
                            if( $aSameCodeCount[1] == 1 && $aSameCodeCount[2] == 1 )
                            {//中二、三位
                                $iTimes = strlen($aProjectCode[0]) -$aSameCodeCount[0];
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                                $aResult[2]['times'] = 1;
                                $aResult[2]['prize'] = $aExpandCode['prize'];
                            }
                            if( $aSameCodeCount[0] == 1 && $aSameCodeCount[2] == 1)
                            {//中一、三位
                                $iTimes = strlen($aProjectCode[1]) -$aSameCodeCount[1];
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                                $aResult[2]['times'] = 1;
                                $aResult[2]['prize'] = $aExpandCode['prize'];
                            }
                            if( $aSameCodeCount[0] == 1 && $aSameCodeCount[1] == 1)
                            {//中一、二位
                                $iTimes = strlen($aProjectCode[2]) -$aSameCodeCount[2];
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                                $aResult[2]['times'] = 1;
                                $aResult[2]['prize'] = $aExpandCode['prize'];
                            }
                            if($aResult[2]['times'] == 0)
                            {
                                unset($aResult[2]);
                            }
                            break;
                        case 3://三等奖号码
                            $aResult[3]['times'] = 0;
                            $aResult[3]['prize'] = 0.00;
                            if( $aSameCodeCount[2] == 1 )
                            {//中第三位
                                $iTimes = (strlen($aProjectCode[0])-$aSameCodeCount[0])*(strlen($aProjectCode[1])-$aSameCodeCount[1]);
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                                $aResult[3]['times'] = 1;
                                $aResult[3]['prize'] = $aExpandCode['prize'];
                            }
                            if( $aSameCodeCount[1] == 1)
                            {//中第二位
                                $iTimes = (strlen($aProjectCode[0])-$aSameCodeCount[0])*(strlen($aProjectCode[2])-$aSameCodeCount[2]);
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                                $aResult[3]['times'] = 1;
                                $aResult[3]['prize'] = $aExpandCode['prize'];
                            }
                            if( $aSameCodeCount[0] == 1)
                            {//中第一位
                                $iTimes = (strlen($aProjectCode[1])-$aSameCodeCount[1])*(strlen($aProjectCode[2])-$aSameCodeCount[2]);
                                $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取奖金
                                $aResult[3]['times'] = 1;
                                $aResult[3]['prize'] = $aExpandCode['prize'];
                            }
                            if($aResult[3]['times'] == 0)
                            {
                                unset($aResult[3]);
                            }
                            break;
                        default:
                            break;
                    }
                }
                break;
            //五码趣味型玩法派奖
            case 'n5_quwei':
                $fBingoMoney = 0.00;//最终奖金
                $aCode = str_split(substr($sBounusCode,$aFuntionRule['startposition'],$aFuntionRule['codecount']));
                $aCode[0] = $aCode[0] > 4 ? 1 : 0;
                $aCode[1] = $aCode[1] > 4 ? 1 : 0;
                $sReg = "/^.*".$aCode[0].".*\|.*".$aCode[1].".*\|.*".$aCode[2].".*\|.*".$aCode[3].".*\|.*".$aCode[4].".*$/";
                $iLevel = preg_match($sReg,$aPrizelevel[0]['expandcode']) == 1 ? 1: 2;
                $aTheBuyCode = explode("|" , $aPrizelevel[0]['expandcode']);
                foreach ($aPrizelevel as $aExpandCode)
                {
                    if($iLevel == 1)
                    {//中一等奖的情况下,可能中多注其它的二等奖号码
                        if($aExpandCode['level'] == $iLevel)
                        {
                            $fBingoMoney += $aExpandCode['prize'];//获取各个奖级的奖金
                            $aResult[1]['times'] = 1;
                            $aResult[1]['prize'] = $aExpandCode['prize'];
                        }
                        else
                        {
                            $iTimes = (strlen($aTheBuyCode[0])*strlen($aTheBuyCode[1])-1);
                            $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取各个奖级的奖金
                            $aResult[2]['times'] = $iTimes;
                            $aResult[2]['prize'] = $aExpandCode['prize']*$iTimes;
                        }
                    }
                    elseif ($iLevel == 2 && $aExpandCode['level'] == $iLevel)
                    {//所有都是二等奖号码
                        $iTimes = strlen($aTheBuyCode[0])*strlen($aTheBuyCode[1]);
                        $fBingoMoney += $aExpandCode['prize']*strlen($aTheBuyCode[0])*strlen($aTheBuyCode[1]);//获取各个奖级的奖金
                        $aResult[2]['times'] = $iTimes;
                        $aResult[2]['prize'] = $aExpandCode['prize']*$iTimes;
                    }
                }
                break;
            //(21)四码趣味型玩法派奖
            case 'n4_quwei':
                $fBingoMoney = 0.00;//最终奖金
                $aCode = str_split(substr($sBounusCode,$aFuntionRule['startposition'],$aFuntionRule['codecount']));
                $aCode[0] = $aCode[0] > 4 ? 1 : 0;
                $sReg = "/^.*".$aCode[0].".*\|.*".$aCode[1].".*\|.*".$aCode[2].".*\|.*".$aCode[3].".*$/";
                $iLevel = preg_match($sReg,$aPrizelevel[0]['expandcode']) == 1 ? 1: 2;
                $aTheBuyCode = explode("|" , $aPrizelevel[0]['expandcode']);
                foreach ($aPrizelevel as $aExpandCode)
                {
                    if($iLevel == 1)
                    {//中一等奖的情况下,可能中多注其它的二等奖号码
                        if($aExpandCode['level'] == $iLevel)
                        {
                            $fBingoMoney += $aExpandCode['prize'];//获取各个奖级的奖金
                            $aResult[1]['times'] = 1;
                            $aResult[1]['prize'] = $aExpandCode['prize'];
                        }
                        else 
                        {
                            $iTimes = (strlen($aTheBuyCode[0])-1);
                            $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取各个奖级的奖金
                            $aResult[2]['times'] = $iTimes;
                            $aResult[2]['prize'] = $aExpandCode['prize']*$iTimes;    
                        }
                    }
                    elseif ($iLevel == 2 && $aExpandCode['level'] == $iLevel)
                    {//所有都是二等奖号码
                        $iTimes = strlen($aTheBuyCode[0]);
                        $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取各个奖级的奖金
                        $aResult[2]['times'] = $iTimes;
                        $aResult[2]['prize'] = $aExpandCode['prize']*$iTimes;
                    }
                }
                break;
            //(22)三码趣味型玩法派奖
            case 'n3_quwei':
                $fBingoMoney = 0.00;//最终奖金
                $aCode = str_split(substr($sBounusCode,$aFuntionRule['startposition'],$aFuntionRule['codecount']));
                $aCode[0] = $aCode[0] > 4 ? 1 : 0;
                $sReg = "/^.*".$aCode[0].".*\|.*".$aCode[1].".*\|.*".$aCode[2].".*$/";
                $iLevel = preg_match($sReg,$aPrizelevel[0]['expandcode']) == 1 ? 1: 2;
                $aTheBuyCode = explode("|" , $aPrizelevel[0]['expandcode']);
                foreach ($aPrizelevel as $aExpandCode)
                {
                    if($iLevel == 1)
                    {//中一等奖的情况下,可能中多注其它的二等奖号码
                        if($aExpandCode['level'] == $iLevel)
                        {
                            $fBingoMoney += $aExpandCode['prize'];//获取各个奖级的奖金
                            $aResult[1]['times'] = 1;
                            $aResult[1]['prize'] = $aExpandCode['prize'];
                        }
                        else
                        {
                            $iTimes = (strlen($aTheBuyCode[0])-1);
                            $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取各个奖级的奖金
                            $aResult[2]['times'] = $iTimes;
                            $aResult[2]['prize'] = $aExpandCode['prize']*$iTimes;
                        }
                    }
                    elseif ($iLevel == 2 && $aExpandCode['level'] == $iLevel)
                    {//所有都是二等奖号码
                        $iTimes = strlen($aTheBuyCode[0]);
                        $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取各个奖级的奖金
                        $aResult[2]['times'] = $iTimes;
                        $aResult[2]['prize'] = $aExpandCode['prize']*$iTimes;
                    }
                }
                break;
            //(23)五码区间型玩法派奖
            case 'n5_qujian':
                $fBingoMoney = 0.00;//最终奖金
                $aCode = str_split(substr($sBounusCode,$aFuntionRule['startposition'],$aFuntionRule['codecount']));
                $aCode[0] = intval($aCode[0]/2);
                $aCode[1] = intval($aCode[1]/2);
                $sReg = "/^.*".$aCode[0].".*\|.*".$aCode[1].".*\|.*".$aCode[2].".*\|.*".$aCode[3].".*\|.*".$aCode[4].".*$/";
                $iLevel = preg_match($sReg,$aPrizelevel[0]['expandcode']) == 1 ? 1: 2;
                $aTheBuyCode = explode("|" , $aPrizelevel[0]['expandcode']);
                foreach ($aPrizelevel as $aExpandCode)
                {
                    if($iLevel == 1)
                    {//中一等奖的情况下,可能中多注其它的二等奖号码
                        if($aExpandCode['level'] == $iLevel)
                        {
                            $fBingoMoney += $aExpandCode['prize'];//获取各个奖级的奖金
                            $aResult[1]['times'] = 1;
                            $aResult[1]['prize'] = $aExpandCode['prize'];
                        }
                        else
                        {
                            $iTimes = (strlen($aTheBuyCode[0])*strlen($aTheBuyCode[1])-1);
                            $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取各个奖级的奖金
                            $aResult[2]['times'] = $iTimes;
                            $aResult[2]['prize'] = $aExpandCode['prize']*$iTimes;
                        }
                    }
                    elseif ($iLevel == 2 && $aExpandCode['level'] == $iLevel)
                    {//所有都是二等奖号码
                        $iTimes = strlen($aTheBuyCode[0])*strlen($aTheBuyCode[1]);
                        $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取各个奖级的奖金
                        $aResult[2]['times'] = $iTimes;
                        $aResult[2]['prize'] = $aExpandCode['prize']*$iTimes;
                    }
                }
                break;
            //四码区间型玩法派奖
            case 'n4_qujian':
                $fBingoMoney = 0.00;//最终奖金
                $aCode = str_split(substr($sBounusCode,$aFuntionRule['startposition'],$aFuntionRule['codecount']));
                $aCode[0] = intval($aCode[0]/2);
                $sReg = "/^.*".$aCode[0].".*\|.*".$aCode[1].".*\|.*".$aCode[2].".*\|.*".$aCode[3].".*$/";
                $iLevel = preg_match($sReg,$aPrizelevel[0]['expandcode']) == 1 ? 1: 2;
                $aTheBuyCode = explode("|" , $aPrizelevel[0]['expandcode']);
                foreach ($aPrizelevel as $aExpandCode)
                {
                    if($iLevel == 1)
                    {//中一等奖的情况下,可能中多注其它的二等奖号码
                        if($aExpandCode['level'] == $iLevel)
                        {
                            $fBingoMoney += $aExpandCode['prize'];//获取各个奖级的奖金
                            $aResult[1]['times'] = 1;
                            $aResult[1]['prize'] = $aExpandCode['prize'];
                        }
                        else
                        {
                            $iTimes = (strlen($aTheBuyCode[0])-1);
                            $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取各个奖级的奖金
                            $aResult[2]['times'] = $iTimes;
                            $aResult[2]['prize'] = $aExpandCode['prize']*$iTimes;
                        }
                    }
                    elseif ($iLevel == 2 && $aExpandCode['level'] == $iLevel)
                    {//所有都是二等奖号码
                        $iTimes = strlen($aTheBuyCode[0]);
                        $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取各个奖级的奖金
                        $aResult[2]['times'] = $iTimes;
                        $aResult[2]['prize'] = $aExpandCode['prize']*$iTimes;
                    }
                }
                break;
            //三码区间型玩法派奖
            case 'n3_qujian':
                $fBingoMoney = 0.00;//最终奖金
                $aCode = str_split(substr($sBounusCode,$aFuntionRule['startposition'],$aFuntionRule['codecount']));
                $aCode[0] = intval($aCode[0]/2);
                $sReg = "/^.*".$aCode[0].".*\|.*".$aCode[1].".*\|.*".$aCode[2].".*$/";
                $iLevel = preg_match($sReg,$aPrizelevel[0]['expandcode']) == 1 ? 1: 2;
                $aTheBuyCode = explode("|" , $aPrizelevel[0]['expandcode']);
                foreach ($aPrizelevel as $aExpandCode)
                {
                    if($iLevel == 1)
                    {//中一等奖的情况下,可能中多注其它的二等奖号码
                        if($aExpandCode['level'] == $iLevel)
                        {
                            $fBingoMoney += $aExpandCode['prize'];//获取各个奖级的奖金
                            $aResult[1]['times'] = 1;
                            $aResult[1]['prize'] = $aExpandCode['prize'];
                        }
                        else
                        {
                            $iTimes = (strlen($aTheBuyCode[0])-1);
                            $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取各个奖级的奖金
                            $aResult[2]['times'] = $iTimes;
                            $aResult[2]['prize'] = $aExpandCode['prize']*$iTimes;
                        }
                    }
                    elseif ($iLevel == 2 && $aExpandCode['level'] == $iLevel)
                    {//所有都是二等奖号码
                        $iTimes = strlen($aTheBuyCode[0]);
                        $fBingoMoney += $aExpandCode['prize']*$iTimes;//获取各个奖级的奖金
                        $aResult[1]['times'] = $iTimes;
                        $aResult[1]['prize'] = $aExpandCode['prize']*$iTimes;
                    }
                }
                break;
            default:
                break;
        }
        $aLastResult['detail'] = $aResult;
        $aLastResult['totalprize'] = $fBingoMoney;
        return $aLastResult;
    }
}
?>