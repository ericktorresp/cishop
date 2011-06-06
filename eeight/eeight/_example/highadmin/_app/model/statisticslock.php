<?php
/**
 * 统计封锁表
 * 
 * 每一个封锁表指定一个统计封锁表中奖号码的函数
 * 函数用途：更新当期开奖号码在封锁表中的对应号码的是否中奖状态：isbonuscode＝1
 * 
 * --updateLockBonusCode        更新封锁表中奖号码状态
 * --getLotteryTotalLock        获取游戏封锁总值
 *
 * --__fun__ssc_qszhixuan       时时彩前三直选中奖号码判断函数
 * 如：开奖号码12345，前三直选对应的中奖号码123
 * 
 * --__fun__ssc_hszhixuan       时时彩后三直选中奖号码判断函数
 * 如：开奖号码12345，后三直选对应的中奖号码345
 * 
 * --__fun__ssc_qszhuxuan       时时彩前三组选中奖号码判断函数
 * 如：开奖号码12345，前三组选对应的中奖号码123
 * 
 * --__fun__ssc_hszhixuan       时时彩后三组选中奖号码判断函数
 * 如：开奖号码12345，后三组选对应的中奖号码345
 * 
 * --__fun__ssc_dingweidan      时时彩定位胆中奖号码判断函数  
 * 如：开奖号码12345，中奖号码分别对应各位的 万位1 千位2 百位3 十位4 个位5
 * 
 * --__fun__ssc_budingwei       时时彩后三不定位中奖号码判断函数，
 * 如：开奖号码12345，中奖号码 一码不定位对应的中奖号码1 2 3 二码不定位对应的中奖号码12 13 23
 * 
 * --__fun__ssc_daxiaodanshuang 时时彩大小单双[前二和后二]中奖号码判断函数
 * 如：开奖号码12345，中奖号码 前二大小单双对应的中奖号码12 11 22 21,后二大小单双对应的中奖号码 32 30 12 10 
 * 
 * --__fun__ssc_erma  时时彩二码[前二和后二]中奖号码判断函数
 * 如：开奖号码12345 前二对应的中奖号码12,后二对应的中奖号码45
 * 
 * @author     mark
 * @version    1.0.0
 * @package    highadmin
 * 
 */

class model_statisticslock extends basemodel
{
    /**
     * 构造函数
     */
    function __construct( $aDBO=array() )
    {
        parent::__construct( $aDBO );
    }
    
    
    
    /**
     * 更新封锁表中奖号码状态
     * @param int $iLotteryId 游戏ID
     * @param string $sIssue 游戏奖期
     * @param string $sCode   游戏中奖号码
     * @return boolean
     * @author mark
     */
    public function updateLockBonusCode( $iLotteryId = 0, $sIssue = '', $sCode = '' )
    {
        $oLock = new model_locks();
        $aLockTable = $oLock->getAllLockTable($iLotteryId);
        $bResult = TRUE;
        foreach ( $aLockTable as $aLock )
        {
            if( isset($aLock['codefunction']) && $aLock['codefunction'] != ''
                 && method_exists($this,"__fun__".$aLock['codefunction']))
            {
                if(isset($aLock['param']) && $aLock['param'] != '')
                {//指定函数参数
                    $mFlag = $this->{'__fun__'.$aLock['codefunction']}( $aLock['locktablename'], $sIssue, $sCode, $aLock['param'] );
                }
                else
                {
                    $mFlag = $this->{'__fun__'.$aLock['codefunction']}( $aLock['locktablename'], $sIssue, $sCode );
                }
                if(!$mFlag)
                {
                    $bResult = FALSE;
                }
            }
            else
            {
                return FALSE;
            }
        }
        return $bResult;
    }
    
    
    /**
     * 重置封锁表中奖号码状态
     * @param int $iLotteryId 游戏ID
     * @param string $sIssue 游戏奖期
     * @return boolean
     * @author mark
     */
    public function resetLockBonusCode( $iLotteryId = 0, $sIssue = '' )
    {
        $oLock = new model_locks();
        $aLockTable = $oLock->getAllLockTable($iLotteryId);
        $bResult = TRUE;
        foreach ( $aLockTable as $aLock )
        {
            $sWhere = " `issue` = '".$sIssue."' AND `isbonuscode` = '1'";
            $bResult = $bResult && $this->oDB->update( $aLock['locktablename'], array('isbonuscode' => 0), $sWhere );
        }
        return $bResult;
    }
    
    
    /**
     * 获取游戏封锁总值
     * 累加同一游戏的所有封锁表中与开奖号码相对应的号码封锁值：得到游戏在指定期的销售总封锁值
     * 
     * @param int $iLotteryId 游戏ID
     * @param string $sIssue 游戏奖期
     * @return boolean
     * @author mark
     */
    public function getLotteryTotalLock( $iLotteryId = 0, $sIssue = '' )
    {
        $oLock = new model_locks();
        $aLockTable = $oLock->getAllLockTable($iLotteryId);
        $fTotalLockPrize = 0;
        foreach ( $aLockTable as $aLock )
        {
            $aLockPrize = $oLock->getAllLockPrize( $aLock['locktablename'], $sIssue);
            $fTotalLockPrize += $aLockPrize['totlaprize'];
        }
        return $fTotalLockPrize;
    }
    
    
    /**
     * 时时彩前三直选中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssc_qszhixuan( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        if( strlen($sCode) != 5 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sWhere = " `issue` = '".$sIssue."' AND `code` = '".substr($sCode,0,3)."'";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    /**
     * 时时彩后三直选中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssc_hszhixuan( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        if( strlen($sCode) != 5 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sWhere = " `issue` = '".$sIssue."' AND `code` = '".substr($sCode,2,3)."'";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    /**
     * 时时彩前三组选中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssc_qszhuxuan( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        if( strlen($sCode) != 5 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sCode = $this->strOrder(substr($sCode,0,3));
        $sWhere = " `issue` = '".$sIssue."' AND `code` = '".$sCode."'";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    /**
     * 时时彩后三组选中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssc_hszhuxuan( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        if( strlen($sCode) != 5 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sCode = $this->strOrder(substr($sCode,2,3));
        $sWhere = " `issue` = '".$sIssue."' AND `code` = '".$sCode."'";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    /**
     * 时时彩定位胆中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssc_dingweidan( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        if( strlen($sCode) != 5 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $aCode = str_split($sCode,1);
        $sWhere = " `issue` = '".$sIssue."' AND ";
        $aTempWhere = array();
        foreach($aCode as $iKey => $sTempCode)
        {
            $sSpecialValue = $iKey + 1;
            $aTempWhere [] = "(`code` = '".$sTempCode."' AND `specialvalue` = '".$sSpecialValue."')";
        }
        $sTempWhere = implode(" OR ", $aTempWhere);
        $sWhere = $sWhere ."(".$sTempWhere.")";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    /**
     * 时时彩后三不定位中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssc_budingwei( $sLockTableName = '', $sIssue = '', $sCode = '', $sParam = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '' || !isset($sParam) || $sParam == '')
        {
            return FALSE;
        }
        if( strlen($sCode) != 5 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sCode = intval($sParam) == 1 ? substr($sCode,0,3) : substr($sCode,2,3);
        $sCode = $this->strOrder($sCode);
        $sWhere = " `issue` = '".$sIssue."' AND `code` ='".$sCode."'";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    /**
     * 时时彩大小单双[前二和后二]中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssc_daxiaodanshuang( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        if( strlen($sCode) != 5 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sWhere = " `issue` = '".$sIssue."'";
        static $aBSAD = array(    // 大小单双对应号码
            '0' => array(5,6,7,8,9),  // 大
            '1' => array(0,1,2,3,4),  // 小
            '2' => array(1,3,5,7,9),  // 单
            '3' => array(0,2,4,6,8)   // 双
        );
        //前二大小单双
        $iQFristNumber  = substr($sCode,0,1);
        $iQSecondNumber = substr($sCode,1,1);
        $sQFristString  = '';
        $sQSecondString = '';
        foreach( $aBSAD AS $k=>$v )
        {
            $sQFristString  .= in_array( $iQFristNumber,  $v ) ? $k : '';
            $sQSecondString .= in_array( $iQSecondNumber, $v ) ? $k : '';
        }
        unset( $iQFristNumber, $iQSecondNumber );
        $sWhere .= " AND (( `code` REGEXP '^([$sQFristString]{1})([$sQSecondString]{1})$' AND `specialvalue` = '1') OR ";
        ///后二大小单双
        $iHFristNumber  = substr($sCode,3,1);
        $iHSecondNumber = substr($sCode,4,1);
        $sHFristString  = '';
        $sHSecondString = '';
        foreach( $aBSAD AS $k=>$v )
        {
            $sHFristString  .= in_array( $iHFristNumber,  $v ) ? $k : '';
            $sHSecondString .= in_array( $iHSecondNumber, $v ) ? $k : '';
        }
        unset( $iHFristNumber, $iHSecondNumber );
        $sWhere .= "( `code` REGEXP '^([$sHFristString]{1})([$sHSecondString]{1})$' AND `specialvalue` = '2'))";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    /**
     * 时时彩二码[前二和后二]中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssc_erma( $sLockTableName = '', $sIssue = '', $sCode = '', $sParam = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '' || !isset($sParam) || $sParam == '')
        {
            return FALSE;
        }
        $iParam = intval($sParam);
        if( strlen($sCode) != 5 || $iParam == 0)
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sWhere = " `issue` = '".$sIssue."'";
        if($iParam == 1)
        {
            $sWhere .= " AND  `code` = '".substr($sCode,0,2)."'";
        }
        elseif ($iParam == 2)
        {
            $sWhere .= " AND  `code` = '".substr($sCode,3,2)."'";
        }
        else
        {
            return FALSE;
        }
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    
     /**
     * 时时乐直选中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssl_zhixuan( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        if( strlen($sCode) != 3 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sWhere = " `issue` = '".$sIssue."' AND `code` = '".$sCode."'";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
     /**
     * 时时乐组选中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssl_zhuxuan( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        if( strlen($sCode) != 3 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sCode = $this->strOrder($sCode);
        $sWhere = " `issue` = '".$sIssue."' AND `code` = '".$sCode."'";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    /**
     * 时时乐定位胆中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssl_dingweidan( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        if( strlen($sCode) != 3 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $aCode = str_split($sCode,1);
        $sWhere = " `issue` = '".$sIssue."' AND ";
        $aTempWhere = array();
        foreach($aCode as $iKey => $sTempCode)
        {
            $sSpecialValue = $iKey + 1;
            $aTempWhere [] = "(`code` = '".$sTempCode."' AND `specialvalue` = '".$sSpecialValue."')";
        }
        $sTempWhere = implode(" OR ", $aTempWhere);
        $sWhere = $sWhere ."(".$sTempWhere.")";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
     /**
     * 时时乐不定位中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssl_budingwei( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        if( strlen($sCode) != 3 )
        {
            return FALSE;
        }
        $sCode = $this->strOrder($sCode);
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sWhere = " `issue` = '".$sIssue."' AND `code` = '".$sCode."'";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
     /**
     * 时时乐二码[前二和后二]中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssl_erma( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        if( strlen($sCode) != 3 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sWhere = " `issue` = '".$sIssue."'";
        $sWhere .= " AND (( `code` = '".substr($sCode,0,2)."' AND `specialvalue` = '1')
         OR ( `code` = '".substr($sCode,1,2)."' AND `specialvalue` = '2'))";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    /**
     * 乐透型三码中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__lotto_shanma( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        $aCode = explode(" ", $sCode);
        if( count($aCode) != 5 )
        {
            return FALSE;
        }
        $sCurrentBonusCode = $aCode[0] . " " . $aCode[1] . " " . $aCode[2];
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sWhere = " `issue` = '".$sIssue."'";
        $sWhere .= " AND `code` = '".$sCurrentBonusCode."'";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    /**
     * 乐透型二码中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__lotto_erma( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        $aCode = explode(" ", $sCode);
        if( count($aCode) != 5 )
        {
            return FALSE;
        }
        $sCurrentBonusCode = $aCode[0] . " " . $aCode[1];
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sWhere = " `issue` = '".$sIssue."'";
        $sWhere .= " AND `code` = '".$sCurrentBonusCode."'";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    /**
     * 乐透型定位胆中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__lotto_dingweidan( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        $aCode = explode(" ", $sCode);
        if( count($aCode) != 5 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sWhere = " `issue` = '".$sIssue."'";
        $sWhere .= " AND ((`code`=$aCode[0] AND `specialvalue`='1') OR ";
        $sWhere .= " (`code`=$aCode[1] AND `specialvalue`='2') OR ";
        $sWhere .= " (`code`=$aCode[2] AND `specialvalue`='3') )";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    /**
     * 乐透型不定位中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__lotto_budingwei( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        $aCode = explode(" ", $sCode);
        if( count($aCode) != 5 )
        {
            return FALSE;
        }
        $aCode = array_slice($aCode, 0, 3);
        sort($aCode);
        $sCode = implode(" ", $aCode);
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sWhere = " `issue` = '".$sIssue."' AND `code` = '".$sCode."'";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    /**
     * 乐透型趣味型玩法中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__lotto_quwei( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        $aCode = explode(" ", $sCode);
        if( count($aCode) != 5 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sWhere = " `issue` = '".$sIssue."'";
        //统计单双个数
        $iSingleCount = 0;//单号个数
        $iDoubleCount = 0;//双号码个数
        foreach ($aCode as $sCodeValue)
        {
            $sCodeValue%2 == 0 ? $iDoubleCount++ : $iSingleCount++;
        }
        $sWhere .= " AND ((`code`=$iSingleCount AND `specialvalue`='1') OR ";
        //找出中奖号码的中位
        sort($aCode);
        $sWhere .= " (`code`=$aCode[2] AND `specialvalue`='2') )";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    /**
     * 乐透型任选型玩法中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__lotto_renxuan( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        $aCode = explode(" ", $sCode);
        if( count($aCode) != 5 )
        {
            return FALSE;
        }
        sort($aCode);
        $sCode = implode(" ", $aCode);
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sWhere = " `issue` = '".$sIssue."' AND `code` = '".$sCode."'";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    /**
     * BJKL8任选一玩法中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__bjkl8_rx1( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        $aCode = explode(" ", $sCode);
        if( count($aCode) != 20 )
        {
            return FALSE;
        }
        $sCode = implode(",", $aCode);
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sWhere = " `issue` = '".$sIssue."' AND `code` IN (".$sCode.")";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    /**
     * BJKL8和值大小玩法中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__bjkl8_heidx( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        $aCode = explode(" ", $sCode);
        if( count($aCode) != 20 )
        {
            return FALSE;
        }
        $iAddCount = 0;
        foreach ($aCode as $iCode)
        {
            $iAddCount +=intval($iCode);
        }
        $iFinalBonusCode = 0;
        if($iAddCount < 810)
        {
            $iFinalBonusCode = 1;
        }
        if($iAddCount == 810)
        {
            $iFinalBonusCode = 2;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sWhere = " `issue` = '".$sIssue."' AND `code` = '".$iFinalBonusCode."'";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    /**
     * BJKL8和值单双玩法中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__bjkl8_heids( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
        || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        $aCode = explode(" ", $sCode);
        if( count($aCode) != 20 )
        {
            return FALSE;
        }
        $iAddCount = 0;
        foreach ($aCode as $iCode)
        {
            $iAddCount +=intval($iCode);
        }
        $iFinalBonusCode = $iAddCount%2 == 0 ? 1 : 0;
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sWhere = " `issue` = '".$sIssue."' AND `code` = '".$iFinalBonusCode."'";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    /**
     * BJKL8上下盘玩法中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__bjkl8_sxpan( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
        || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        $aCode = explode(" ", $sCode);
        if( count($aCode) != 20 )
        {
            return FALSE;
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
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sWhere = " `issue` = '".$sIssue."' AND `code` = '".$iFinalBonusCode."'";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    /**
     * BJKL8奇偶盘玩法中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__bjkl8_jopan( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
         if( !isset($sCode) || $sCode == '' || !isset($sIssue)
        || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        $aCode = explode(" ", $sCode);
        if( count($aCode) != 20 )
        {
            return FALSE;
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
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sWhere = " `issue` = '".$sIssue."' AND `code` = '".$iFinalBonusCode."'";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    /**
     * 时时彩四星五星组选玩法的中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssc_siwuzhuxuan( $sLockTableName = '', $sIssue = '', $sCode = '', $sParam = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '' || !isset($sParam) || $sParam == '')
        {
            return FALSE;
        }
        $aParam = explode(",",$sParam);
        if(!isset($aParam[0]) || !is_numeric($aParam[0]))
        {
            return FALSE;
        }
        if( strlen($sCode) != 5 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $iNumCount = intval($aParam[0]);
        $sRealCode = $iNumCount == 4 ? substr($sCode,1,4) : $sCode;
        $sRealCode = $this->strOrder($sRealCode);
        $sWhere = " `issue` = '".$sIssue."' AND `code` = '".$sRealCode."'";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
     /**
     * 时时彩三星组合玩法的中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssc_shanxinzuhe( $sLockTableName = '', $sIssue = '', $sCode = '', $sParam = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '' || !isset($sParam) || $sParam == '')
        {
            return FALSE;
        }
        if( strlen($sCode) != 5 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $iNumCount = intval($sParam);
        $sRealCode = $iNumCount == 1 ? substr($sCode,0,3) : substr($sCode,2,3);
        $sWhere = " `issue` = '".$sIssue."' AND `code` = '".$sRealCode."'";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    /**
     * 时时彩三星通选玩法的中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssc_shanxintongxuan( $sLockTableName = '', $sIssue = '', $sCode = '', $sParam = '')
    {
        return $this->__fun__ssc_shanxinzuhe( $sLockTableName, $sIssue, $sCode, $sParam);
    }
    
    
    /**
     * 时时彩三星和值尾数玩法的中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssc_shanxinhzws( $sLockTableName = '', $sIssue = '', $sCode = '', $sParam = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '' || !isset($sParam) || $sParam == '')
        {
            return FALSE;
        }
        if( strlen($sCode) != 5 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $iNumCount = intval($sParam);
        $aTmpCode = $iNumCount == 1 ? str_split(substr($sCode,0,3)) : str_split(substr($sCode,2,3));
        $iCodeSum = array_sum($aTmpCode);
        $sRealCode = $iCodeSum%10;
        $sWhere = " `issue` = '".$sIssue."' AND `code` = '".$sRealCode."'";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    
    /**
     * 时时彩三星特殊玩法的中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssc_shanxinteshu( $sLockTableName = '', $sIssue = '', $sCode = '', $sParam = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '' || !isset($sParam) || $sParam == '')
        {
            return FALSE;
        }
        if( strlen($sCode) != 5 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $iNumCount = intval($sParam);
        $aTmpCode = $iNumCount == 1 ? str_split(substr($sCode,0,3)) : str_split(substr($sCode,2,3));
        if($aTmpCode[0] == $aTmpCode[1] && $aTmpCode[1] == $aTmpCode[2])
        {
            $iRealCode = 0;
        }
        elseif( ($aTmpCode[0]+1 == $aTmpCode[1] && $aTmpCode[1]+1 == $aTmpCode[2])
         || ($aTmpCode[0]-1 == $aTmpCode[1] && $aTmpCode[1]-1 == $aTmpCode[2] )
         || implode("",$aTmpCode) == '901' || implode("",$aTmpCode) == '109') 
        {
            $iRealCode = 1;
        }elseif (count(array_unique($aTmpCode)) == 2)
        {
            $iRealCode = 2;
        }
        else 
        {
            return TRUE;
        }
        $sWhere = " `issue` = '".$sIssue."' AND `code` = '".$iRealCode."'";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    /**
     * 时时彩四星一码不定位玩法的中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssc_sixinbudingwei( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        if( strlen($sCode) != 5 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $aTmpCode = str_split(substr($sCode,1,4));
        $aTmpCode = array_unique($aTmpCode);
        $sRealCode = implode(",",$aTmpCode);
        $sWhere = " `issue` = '".$sIssue."' AND `code` IN (".$sRealCode.")";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    /**
     * 时时彩四星二码不定位玩法的中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssc_sixinermabudingwei( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
        || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        if( strlen($sCode) != 5 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $aTmpCode = str_split(substr($sCode,1,4));
        $aTmpCode = array_unique($aTmpCode);
        sort($aTmpCode);
        $aTmpCode = explode(",",implode(",",$aTmpCode));
        $aAllCode = $this->getCombination($aTmpCode,2);
        if(empty($aAllCode))
        {
            return TRUE;
        }
        foreach ($aAllCode as &$sTmpcode)
        {
            $sTmpcode = "'".str_replace(" ","",trim($sTmpcode))."'";
        }
        $sAllcode = implode(",", $aAllCode);
        $sWhere = " `issue` = '".$sIssue."' AND `code` IN (".$sAllcode.")";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    /**
     * 时时彩五星二码不定位玩法的中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssc_wuxinermabudingwei( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
        || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        if( strlen($sCode) != 5 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $aTmpCode = str_split($sCode);
        $aTmpCode = array_unique($aTmpCode);
        sort($aTmpCode);
        $aTmpCode = explode(",",implode(",",$aTmpCode));
        $aAllCode = $this->getCombination($aTmpCode,2);
        if(empty($aAllCode))
        {
            return TRUE;
        }
        foreach ($aAllCode as &$sTmpcode)
        {
            $sTmpcode = "'".str_replace(" ","",trim($sTmpcode))."'";
        }
        $sAllcode = implode(",", $aAllCode);
        $sWhere = " `issue` = '".$sIssue."' AND `code` IN (".$sAllcode.")";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    /**
     * 时时彩五星三码不定位玩法的中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssc_wuxinshanmabudingwei( $sLockTableName = '', $sIssue = '', $sCode = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
        || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return FALSE;
        }
        if( strlen($sCode) != 5 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $aTmpCode = str_split($sCode);
        $aTmpCode = array_unique($aTmpCode);
        sort($aTmpCode);
        $aTmpCode = explode(",",implode(",",$aTmpCode));
        $aAllCode = $this->getCombination($aTmpCode,3);
        if(empty($aAllCode))
        {
            return TRUE;
        }
        foreach ($aAllCode as &$sTmpcode)
        {
            $sTmpcode = "'".str_replace(" ","",trim($sTmpcode))."'";
        }
        $sAllcode = implode(",", $aAllCode);
        $sWhere = " `issue` = '".$sIssue."' AND `code` IN (".$sAllcode.")";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    /**
     * 时时彩三星大小单双[前三和后三]中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssc_shanxindxds( $sLockTableName = '', $sIssue = '', $sCode = '', $sParam = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '' || !isset($sParam) || $sParam == '')
        {
            return FALSE;
        }
        if( strlen($sCode) != 5 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sWhere = " `issue` = '".$sIssue."'";
        static $aBSAD = array(    // 大小单双对应号码
            '0' => array(5,6,7,8,9),  // 大
            '1' => array(0,1,2,3,4),  // 小
            '2' => array(1,3,5,7,9),  // 单
            '3' => array(0,2,4,6,8)   // 双
        );
        $sCode = intval($sParam) == 1 ? substr($sCode,0,3) : substr($sCode,2,3);
        //三星大小单双
        $iFristNumber  = substr($sCode,0,1);
        $iSecondNumber = substr($sCode,1,1);
        $iThirdNumber  = substr($sCode,2,1);
        $sFristString  = '';
        $sSecondString = '';
        $sThirdString = '';
        foreach( $aBSAD AS $k=>$v )
        {
            $sFristString  .= in_array( $iFristNumber,  $v ) ? $k : '';
            $sSecondString .= in_array( $iSecondNumber, $v ) ? $k : '';
            $sThirdString  .= in_array( $iThirdNumber, $v ) ? $k : '';
        }
        unset( $iFristNumber, $iSecondNumber,$iThirdNumber );
        $sWhere .= " AND  `code` REGEXP '^([$sFristString]{1})([$sSecondString]{1})([$sThirdString]{1})$' ";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    /**
     * 时时彩三星趣味玩法的中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssc_shanxinquwei( $sLockTableName = '', $sIssue = '', $sCode = '', $sParam = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '' || !isset($sParam) || $sParam == '')
        {
            return FALSE;
        }
        if( strlen($sCode) != 5 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $iParam = intval($sParam);
        if($iParam == 1)
        {
            $sRealCode = substr($sCode,1,2);
            $sRealCode = (substr($sCode,0,1) < 5 ? '0' : '1').$sRealCode;
        }
        elseif ($iParam == 2)
        {
            $sRealCode = substr($sCode,3,2);
            $sRealCode = (substr($sCode,2,1) < 5 ? '0' : '1').$sRealCode;
        }
        else 
        {
            return TRUE;
        }
        $sWhere = " `issue` = '".$sIssue."' AND `code` = '".$sRealCode."'";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
    
     /**
     * 时时彩三星区间玩法的中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssc_shanxinqujian( $sLockTableName = '', $sIssue = '', $sCode = '', $sParam = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '' || !isset($sParam) || $sParam == '')
        {
            return FALSE;
        }
        if( strlen($sCode) != 5 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $iParam = intval($sParam);
        if($iParam == 1)
        {
            $sRealCode = substr($sCode,1,2);
            $sRealCode = intval(substr($sCode,0,1)/2).$sRealCode;
        }
        elseif ($iParam == 2)
        {
            $sRealCode = substr($sCode,3,2);
            $sRealCode = intval(substr($sCode,2,1)/2).$sRealCode;
        }
        else 
        {
            return TRUE;
        }
        $sWhere = " `issue` = '".$sIssue."' AND `code` = '".$sRealCode."'";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
     /**
     * 时时彩五星特殊玩法的中奖号码判断函数
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @param string $sCode             当期的彩种开奖号码
     * @return boolean
     * 
     */
    private function __fun__ssc_wuxinteshu( $sLockTableName = '', $sIssue = '', $sCode = '', $sParam = '')
    {
        if( !isset($sCode) || $sCode == '' || !isset($sIssue)
         || $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '' || !isset($sParam) || $sParam == '')
        {
            return FALSE;
        }
        $iParam = intval($sParam);
        if( strlen($sCode) != 5 || $iParam == 0 )
        {
            return FALSE;
        }
        $aData =  array();
        $aData['isbonuscode'] = 1;
        $sCode = $this->strOrder($sCode);
        $aCode = str_split($sCode);
        $aCodeCount = array_count_values($aCode);
        switch ($iParam)
        {
            case 1://一帆风顺
                $sCode = implode(",",$aCode);
                break;
            case 2://好事成双
                if(max($aCodeCount) < 2)
                {
                    return TRUE;
                }
                $aTmpCode = array();
                foreach ($aCodeCount as $iCode => $iTimes)
                {
                    if($iTimes >=2 )
                    {
                        $aTmpCode[] = $iCode;
                    }
                }
                $sCode = implode(",",$aTmpCode);
                break;
            case 3://三星报喜
                if(max($aCodeCount) < 3)
                {
                    return TRUE;
                }
                $aTmpCode = array();
                foreach ($aCodeCount as $iCode => $iTimes)
                {
                    if($iTimes >=3 )
                    {
                        $aTmpCode[] = $iCode;
                    }
                }
                $sCode = implode(",",$aTmpCode);
                break;
            case 4://四季发财
                if(max($aCodeCount) < 4)
                {
                    return TRUE;
                }
                $aTmpCode = array();
                foreach ($aCodeCount as $iCode => $iTimes)
                {
                    if($iTimes >=4 )
                    {
                        $aTmpCode[] = $iCode;
                    }
                }
                $sCode = implode(",",$aTmpCode);
                break;
            default:
                return TRUE;
                break;
        }
        $sWhere = " `issue` = '".$sIssue."' AND `code` IN (".$sCode.")";
        $mFlag = $this->oDB->update($sLockTableName, $aData, $sWhere);
        return $this->oDB->errno() > 0 ? FALSE : TRUE;
    }
    
    
     /**
     * 字符串排序
     * @param string    $sString    需要排序的字符串
     * @param boolean   $bDesc      默认按字符串从小到大排序，如$bDesc=TRUE,则字符串从大到小排序
     * @return string 排序好的字符串
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
}
?>