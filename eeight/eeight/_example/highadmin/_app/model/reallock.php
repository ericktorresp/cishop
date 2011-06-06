<?php
/**
 * 统计封锁表中号码转直后的真正封锁值
 * 如：
 * 将组三号码转直成1000注进封锁计算：真正封锁值=所有组三号码封锁值*3
 * 将组六号码转直成1000注进封锁计算：真正封锁值=所有组三号码封锁值*6
 * 将大小单双号码转直成100注进封锁计算：真正封锁值=所有大小单双号码封锁值*25
 * 将不定位号码转直成1000注进封锁计算：真正封锁值=所有组三号码封锁值*3+所有组三号码封锁值*6+所有豹子号码封锁值
 * 其余常规玩法：真正封锁值=所有号码封锁值
 * 
 * @author     mark
 * @version    1.0.0
 * @package    highadmin
 * 
 */

class model_reallock extends basemodel
{
    /**
     * 构造函数
     */
    function __construct( $aDBO=array() )
    {
        parent::__construct( $aDBO );
    }



    /**
     * 统计封锁表中号码转直后的真正封锁值
     * @param string $sLockTableName 封锁表名称
     * @param string $sIssue 游戏奖期
     * @return mix
     * 
     * @author mark
     */
    public function getRealLock( $sLockTableName = '', $sIssue = '' )
    {
        $aResult = array();
        if( $sLockTableName == '' || $sIssue == '' )
        {
            return FALSE;
        }
        $sSql = " SELECT * FROM `locktablename` WHERE `locktablename` ='" . $sLockTableName . "'";
        $aLock = $this->oDB->getOne($sSql);
        if( isset($aLock['codefunction']) && $aLock['codefunction'] != '')
        {
            if( method_exists($this,"__fun__".$aLock['codefunction']) )
            {//特殊玩法
                if(isset($aLock['param']) && $aLock['param'] != '')
                {//指定函数参数
                    $aResult = $this->{'__fun__'.$aLock['codefunction']}( $aLock['locktablename'], $sIssue,$aLock['param']);
                }
                else 
                {
                    $aResult = $this->{'__fun__'.$aLock['codefunction']}( $aLock['locktablename'], $sIssue);
                }
            }
            else
            {//其余常规玩法
                $aResult = $this->{'__fun__normal'}( $aLock['locktablename'], $sIssue);
            }
            if( empty($aResult) )
            {
                return FALSE;
            }
            return $aResult;
        }
        else
        {
            return FALSE;
        }
    }


    /**
     * 获取常规玩法真正封锁值
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    private function __fun__normal( $sLockTableName = '', $sIssue = '' )
    {
        if( !isset($sIssue)|| $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return array();
        }
        $sSql = " SELECT count(`code`) AS codenum,SUM(`prizes`) AS totallock FROM `". $sLockTableName . "` WHERE `issue` = '" . $sIssue . "'";
        $aCodeLock = $this->oDB->getOne($sSql);
        if( $this->oDB->errno() > 0 )
        {
            return array();
        }
        else
        {
            return $aCodeLock;
        }
    }

    /**
     * 获取三位数字组选玩法真正封锁值
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    public function getzhuxuanlock( $sLockTableName = '', $sIssue = '')
    {
        if(!isset($sIssue)|| $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return array();
        }
        $sSql = " SELECT * FROM `". $sLockTableName . "` WHERE `issue` = '" . $sIssue . "'";
        $aCodeLock = $this->oDB->getAll($sSql);
        if( $this->oDB->errno() > 0 )
        {
            return array();
        }
        else
        {
            $aResult = array();
            $aResult['codenum'] = 1000;
            $aResult['totallock'] = 0;
            foreach ( $aCodeLock as $aLock )
            {
                $aCode = array();
                $aCode[0] = substr($aLock['code'],0,1);
                $aCode[1] = substr($aLock['code'],1,1);
                $aCode[2] = substr($aLock['code'],2,1);
                $aCode = array_unique($aCode);
                if(count($aCode) == 2)
                {//组三号码
                    $aResult['totallock'] += $aLock['prizes']*3;
                }
                if(count($aCode) == 3)
                {//组六号码
                    $aResult['totallock'] += $aLock['prizes']*6;
                }
            }
            return $aResult;
        }
    }


    /**
     * 获取三位数字不定位玩法真正封锁值
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    public function getbudingweilock( $sLockTableName = '', $sIssue = '')
    {
        if(!isset($sIssue)|| $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return array();
        }
        $sSql = " SELECT * FROM `". $sLockTableName . "` WHERE `issue` = '" . $sIssue . "'";
        $aCodeLock = $this->oDB->getAll($sSql);
        if( $this->oDB->errno() > 0 )
        {
            return array();
        }
        else
        {
            $aResult = array();
            $aResult['codenum'] = 1000;
            $aResult['totallock'] = 0;
            foreach ( $aCodeLock as $aLock )
            {
                $aCode = array();
                $aCode[0] = substr($aLock['code'],0,1);
                $aCode[1] = substr($aLock['code'],1,1);
                $aCode[2] = substr($aLock['code'],2,1);
                $aCode = array_unique($aCode);
                if(count($aCode) == 2)
                {//组三号码
                    $aResult['totallock'] += $aLock['prizes']*3;
                }
                if(count($aCode) == 3)
                {//组六号码
                    $aResult['totallock'] += $aLock['prizes']*6;
                }
                if(count($aCode) == 1)
                {//豹子号码
                    $aResult['totallock'] += $aLock['prizes'];
                }
            }
            return $aResult;
        }
    }


    /**
     * 获取三位数字大小单双玩法真正封锁值
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    public function getdaxiaodanshuanglock( $sLockTableName = '', $sIssue = '')
    {
        if(!isset($sIssue)|| $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return array();
        }
        $sSql = " SELECT SUM(`prizes`) AS totallock FROM `". $sLockTableName . "` WHERE `issue` = '" . $sIssue . "'";
        $aCodeLock = $this->oDB->getOne($sSql);
        if( $this->oDB->errno() > 0 )
        {
            return array();
        }
        else
        {
            $aResult = array();
            $aResult['codenum'] = 100;
            $aResult['totallock'] = $aCodeLock['totallock']*25;
            return $aResult;
        }
    }


    /**
     * 获取时时彩前三组选玩法真正封锁值
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    private function __fun__ssc_qszhuxuan( $sLockTableName = '', $sIssue = '')
    {
        return $this->getzhuxuanlock($sLockTableName,$sIssue);
    }


    /**
     * 获取时时彩后三组选玩法真正封锁值
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    private function __fun__ssc_hszhuxuan( $sLockTableName = '', $sIssue = '')
    {
        return $this->getzhuxuanlock($sLockTableName,$sIssue);
    }


    /**
     * 获取时时彩后三不定位玩法真正封锁值
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    private function __fun__ssc_budingwei( $sLockTableName = '', $sIssue = '' )
    {
        return $this->getbudingweilock($sLockTableName,$sIssue);
    }


    /**
     * 获取时时彩大小单双[前二和后二]玩法真正封锁值
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    private function __fun__ssc_daxiaodanshuang( $sLockTableName = '', $sIssue = '')
    {
        return $this->getdaxiaodanshuanglock($sLockTableName,$sIssue);
    }


    /**
     * 获取时时乐组选玩法真正封锁值
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    private function __fun__ssl_zhuxuan( $sLockTableName = '', $sIssue = '')
    {
        return $this->getzhuxuanlock($sLockTableName,$sIssue);
    }


    /**
     * 获取时时乐不定位玩法真正封锁值
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    private function __fun__ssl_budingwei( $sLockTableName = '', $sIssue = '')
    {
        return $this->getbudingweilock($sLockTableName,$sIssue);
    }


    /**
     * 获取乐透型趣味型玩法真正封锁值
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    private function __fun__lotto_quwei( $sLockTableName = '', $sIssue = '')
    {
        if(!isset($sIssue)|| $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return array();
        }
        $sSql = " SELECT * FROM `". $sLockTableName . "` WHERE `issue` = '" . $sIssue . "'";
        $aCodeLock = $this->oDB->getAll($sSql);
        if( $this->oDB->errno() > 0 )
        {
            return array();
        }
        else
        {
            $aResult['codenum'] = 462;
            $aResult['totallock'] = 0;
            if(count($aCodeLock) == 6)
            {
                foreach ( $aCodeLock as $aLock )
                {
                    switch ($aLock['code'])
                    {
                        case '0':
                            $aResult['totallock'] += $aLock['prizes'];
                            break;
                        case '1':
                            $aResult['totallock'] += $aLock['prizes']*30;
                            break;
                        case '2':
                            $aResult['totallock'] += $aLock['prizes']*150;
                            break;
                        case '3':
                            $aResult['totallock'] += $aLock['prizes']*200;
                            break;
                        case '4':
                            $aResult['totallock'] += $aLock['prizes']*75;
                            break;
                        case '5':
                            $aResult['totallock'] += $aLock['prizes']*6;
                            break;
                        default:
                            break;
                    }
                }
            }
            else if ( count($aCodeLock) == 7 )
            {
                foreach ( $aCodeLock as $aLock )
                {
                    switch ($aLock['code'])
                    {
                        case '03':
                        case '09':
                            $aResult['totallock'] += $aLock['prizes']*28;
                            break;
                        case '04':
                        case '08':
                            $aResult['totallock'] += $aLock['prizes']*63;
                            break;
                        case '05':
                        case '07':
                            $aResult['totallock'] += $aLock['prizes']*90;
                            break;
                        case '06':
                            $aResult['totallock'] += $aLock['prizes']*100;
                            break;
                        default:
                            break;
                    }
                }
            }
            return $aResult;
        }
    }
    
    
    /**
     * 获取北京快乐八任选一玩法真正封锁值
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    private function __fun__bjkl8_rx1( $sLockTableName = '', $sIssue = '')
    {
        if(!isset($sIssue)|| $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return array();
        }
        $sSql = " SELECT SUM(`prizes`) AS totallock FROM `". $sLockTableName . "` WHERE `issue` = '" . $sIssue . "'";
        $aCodeLock = $this->oDB->getOne($sSql);
        if( $this->oDB->errno() > 0 )
        {
            return array();
        }
        else
        {
            $aResult['codenum'] = 3535316142212180000;//Combin(80,20)
            $aResult['totallock'] = $aCodeLock['totallock']*883829035553044000;//Combin(79,19)
        }
        return $aResult;
    }
    
    
    /**
     * 获取北京快乐八和和值单双玩法真正封锁值,以下各个相关号码倍数在玩法奖组表中有相应的设置
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    private function __fun__bjkl8_heids( $sLockTableName = '', $sIssue = '')
    {
        if(!isset($sIssue)|| $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return array();
        }
        $sSql = " SELECT * FROM `". $sLockTableName . "` WHERE `issue` = '" . $sIssue . "'";
        $aCodeLock = $this->oDB->getAll($sSql);
        if( $this->oDB->errno() > 0 )
        {
            return array();
        }
        else
        {
            $aResult['codenum'] = 3535316142212180000;//相关号码倍数在玩法奖组表中有相应的设置
            $aResult['totallock'] = 0;
            foreach ( $aCodeLock as $aLock )
            {
                switch ($aLock['code'])
                {
                    case '0':
                        $aResult['totallock'] += $aLock['prizes']*1767658070682260000;//相关号码倍数在玩法奖组表中有相应的设置
                        break;
                    case '1':
                        $aResult['totallock'] += $aLock['prizes']*1767658070682260000;//相关号码倍数在玩法奖组表中有相应的设置
                        break;
                    default:
                        break;
                }
            }
        }
        return $aResult;
    }
    
    
    /**
     * 获取北京快乐八和和值大小玩法真正封锁值,以下各个相关号码倍数在玩法奖组表中有相应的设置
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    private function __fun__bjkl8_heidx( $sLockTableName = '', $sIssue = '')
    {
        if(!isset($sIssue)|| $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return array();
        }
        $sSql = " SELECT * FROM `". $sLockTableName . "` WHERE `issue` = '" . $sIssue . "'";
        $aCodeLock = $this->oDB->getAll($sSql);
        if( $this->oDB->errno() > 0 )
        {
            return array();
        }
        else
        {
            $aResult['codenum'] = 9947;//按概率进行比例计算//相关号码倍数在玩法奖组表中有相应的设置
            $aResult['totallock'] = 0;
            foreach ( $aCodeLock as $aLock )
            {
                switch ($aLock['code'])
                {
                    case '0':
                        $aResult['totallock'] += $aLock['prizes']*4936;//相关号码倍数在玩法奖组表中有相应的设置
                        break;
                    case '1':
                        $aResult['totallock'] += $aLock['prizes']*4936;//相关号码倍数在玩法奖组表中有相应的设置
                        break;
                    case '2':
                        $aResult['totallock'] += $aLock['prizes']*75;//相关号码倍数在玩法奖组表中有相应的设置
                        break;
                    default:
                        break;
                }
            }
        }
        return $aResult;
    }
    
    
    /**
     * 获取北京快乐八和上下盘玩法真正封锁值,以下各个相关号码倍数在玩法奖组表中有相应的设置
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    private function __fun__bjkl8_sxpan( $sLockTableName = '', $sIssue = '')
    {
        if(!isset($sIssue)|| $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return array();
        }
        $sSql = " SELECT * FROM `". $sLockTableName . "` WHERE `issue` = '" . $sIssue . "'";
        $aCodeLock = $this->oDB->getAll($sSql);
        if( $this->oDB->errno() > 0 )
        {
            return array();
        }
        else
        {
            $aResult['codenum'] = 3535316142212180000;//Combin(80,20)//相关号码倍数在玩法奖组表中有相应的设置
            $aResult['totallock'] = 0;
            foreach ( $aCodeLock as $aLock )
            {
                switch ($aLock['code'])
                {
                    case '0':
                        $aResult['totallock'] += $aLock['prizes']*1408393885741470000;//相关号码倍数在玩法奖组表中有相应的设置
                        break;
                    case '1':
                        $aResult['totallock'] += $aLock['prizes']*1408393885741470000;//相关号码倍数在玩法奖组表中有相应的设置
                        break;
                    case '2':
                        $aResult['totallock'] += $aLock['prizes']*718528370729238000;//相关号码倍数在玩法奖组表中有相应的设置
                        break;
                    default:
                        break;
                }
            }
        }
        return $aResult;
    }
    
    
    /**
     * 获取北京快乐八和奇偶盘玩法真正封锁值,以下各个相关号码倍数在玩法奖组表中有相应的设置
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    private function __fun__bjkl8_jopan( $sLockTableName = '', $sIssue = '')
    {
        if(!isset($sIssue)|| $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return array();
        }
        $sSql = " SELECT * FROM `". $sLockTableName . "` WHERE `issue` = '" . $sIssue . "'";
        $aCodeLock = $this->oDB->getAll($sSql);
        if( $this->oDB->errno() > 0 )
        {
            return array();
        }
        else
        {
            $aResult['codenum'] = 3535316142212180000;//Combin(80,20)//相关号码倍数在玩法奖组表中有相应的设置
            $aResult['totallock'] = 0;
            foreach ( $aCodeLock as $aLock )
            {
                switch ($aLock['code'])
                {
                    case '0':
                        $aResult['totallock'] += $aLock['prizes']*1408393885741470000;//相关号码倍数在玩法奖组表中有相应的设置
                        break;
                    case '1':
                        $aResult['totallock'] += $aLock['prizes']*1408393885741470000;//相关号码倍数在玩法奖组表中有相应的设置
                        break;
                    case '2':
                        $aResult['totallock'] += $aLock['prizes']*718528370729238000;//相关号码倍数在玩法奖组表中有相应的设置
                        break;
                    default:
                        break;
                }
            }
        }
        return $aResult;
    }
    
    
    /**
     * 获取四星及五星数字组选玩法真正封锁值
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    public function __fun__ssc_siwuzhuxuan( $sLockTableName = '', $sIssue = '', $sParam = '')
    {
        if( !isset($sIssue)|| $sIssue == '' || !isset($sLockTableName) || 
            $sLockTableName == '' || !isset($sParam) || $sParam == '')
        {
            return array();
        }
        $aParam = explode(",",$sParam);
        if(count($aParam) < 2)
        {
            return array();
        }
        $iWei = intval($aParam[0]);
        $iNum = intval($aParam[1]);
        $aResult = array();
        $sSql = " SELECT count(`code`) AS codenum,SUM(`prizes`) AS totallock FROM `". $sLockTableName . "` WHERE `issue` = '" . $sIssue . "'";
        $aCodeLock = $this->oDB->getOne($sSql);
        if( $this->oDB->errno() > 0 )
        {
            return array();
        }
        else
        {
            switch ($iWei)
            {
                case 4:
                    $aResult['codenum'] = 10000;
                    break;
                case 5:
                    $aResult['codenum'] = 100000;
                    break;
                default:
                    $aCodeLock['codenum'];
                    break;
            }
            $aResult['totallock'] = $aCodeLock['totallock']*$iNum;
            return $aResult;
        }
    }
    
    
    /**
     * 获取时时彩三星特殊号的真实封锁值
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    private function __fun__ssc_shanxinteshu( $sLockTableName = '', $sIssue = '')
    {
        if(!isset($sIssue)|| $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return array();
        }
        $sSql = " SELECT * FROM `". $sLockTableName . "` WHERE `issue` = '" . $sIssue . "'";
        $aCodeLock = $this->oDB->getAll($sSql);
        if( $this->oDB->errno() > 0 )
        {
            return array();
        }
        else
        {
            $aResult['codenum'] = 1000;//三星数字总共一千个号码
            $aResult['totallock'] = 0;
            foreach ( $aCodeLock as $aLock )
            {
                switch ($aLock['code'])
                {
                    case '0':
                        $aResult['totallock'] += $aLock['prizes']*10;//相关号码倍数在玩法奖组表中有相应的设置
                        break;
                    case '1':
                        $aResult['totallock'] += $aLock['prizes']*60;//相关号码倍数在玩法奖组表中有相应的设置
                        break;
                    case '2':
                        $aResult['totallock'] += $aLock['prizes']*270;//相关号码倍数在玩法奖组表中有相应的设置
                        break;
                    default:
                        break;
                }
            }
        }
        return $aResult;
    }
    
    
    
    /**
     * 获取时时彩三星大小单双的真实封锁值
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    private function __fun__ssc_shanxindxds( $sLockTableName = '', $sIssue = '')
    {
        if(!isset($sIssue)|| $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return array();
        }
        $sSql = " SELECT SUM(`prizes`) AS totallock FROM `". $sLockTableName . "` WHERE `issue` = '" . $sIssue . "'";
        $aCodeLock = $this->oDB->getOne($sSql);
        if( $this->oDB->errno() > 0 )
        {
            return array();
        }
        else
        {
            $aResult = array();
            $aResult['codenum'] = 1000;
            $aResult['totallock'] = $aCodeLock['totallock']*125;
            return $aResult;
        }
    }
    
    
    /**
     * 获取时时彩三星趣味的真实封锁值
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    private function __fun__ssc_shanxinquwei( $sLockTableName = '', $sIssue = '')
    {
        if(!isset($sIssue)|| $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return array();
        }
        $sSql = " SELECT SUM(`prizes`) AS totallock FROM `". $sLockTableName . "` WHERE `issue` = '" . $sIssue . "'";
        $aCodeLock = $this->oDB->getOne($sSql);
        if( $this->oDB->errno() > 0 )
        {
            return array();
        }
        else
        {
            $aResult = array();
            $aResult['codenum'] = 1000;
            $aResult['totallock'] = $aCodeLock['totallock']*5;
            return $aResult;
        }
    }
    
    
    
    /**
     * 获取时时彩三星区间的真实封锁值
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    private function __fun__ssc_shanxinqujian( $sLockTableName = '', $sIssue = '')
    {
        if(!isset($sIssue)|| $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return array();
        }
        $sSql = " SELECT SUM(`prizes`) AS totallock FROM `". $sLockTableName . "` WHERE `issue` = '" . $sIssue . "'";
        $aCodeLock = $this->oDB->getOne($sSql);
        if( $this->oDB->errno() > 0 )
        {
            return array();
        }
        else
        {
            $aResult = array();
            $aResult['codenum'] = 1000;
            $aResult['totallock'] = $aCodeLock['totallock']*2;
            return $aResult;
        }
    }
    
    
    /**
     * 获取时时彩四星一码不定位的真实封锁值
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    private function __fun__ssc_sixinbudingwei( $sLockTableName = '', $sIssue = '')
    {
        if(!isset($sIssue)|| $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return array();
        }
        $sSql = " SELECT SUM(`prizes`) AS totallock FROM `". $sLockTableName . "` WHERE `issue` = '" . $sIssue . "'";
        $aCodeLock = $this->oDB->getOne($sSql);
        if( $this->oDB->errno() > 0 )
        {
            return array();
        }
        else
        {
            $aResult = array();
            $aResult['codenum'] = 10000;
            $aResult['totallock'] = $aCodeLock['totallock']*3439;
            return $aResult;
        }
    }
    
    
    
     /**
     * 获取时时彩四星二码不定位的真实封锁值
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
    private function __fun__ssc_sixinermabudingwei( $sLockTableName = '', $sIssue = '')
    {
        if(!isset($sIssue)|| $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
        {
            return array();
        }
        $sSql = " SELECT SUM(`prizes`) AS totallock FROM `". $sLockTableName . "` WHERE `issue` = '" . $sIssue . "'";
        $aCodeLock = $this->oDB->getOne($sSql);
        if( $this->oDB->errno() > 0 )
        {
            return array();
        }
        else
        {
            $aResult = array();
            $aResult['codenum'] = 10000;
            $aResult['totallock'] = $aCodeLock['totallock']*1646;
            return $aResult;
        }
    }
    
    
    
     /**
     * 获取时时彩五星二码不定位的真实封锁值
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
     private function __fun__ssc_wuxinermabudingwei( $sLockTableName = '', $sIssue = '')
     {
         if(!isset($sIssue)|| $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
         {
             return array();
         }
         $sSql = " SELECT SUM(`prizes`) AS totallock FROM `". $sLockTableName . "` WHERE `issue` = '" . $sIssue . "'";
         $aCodeLock = $this->oDB->getOne($sSql);
         if( $this->oDB->errno() > 0 )
         {
             return array();
         }
         else
         {
             $aResult = array();
             $aResult['codenum'] = 100000;
             $aResult['totallock'] = $aCodeLock['totallock']*51630;
             return $aResult;
         }
     }
     
     
     /**
     * 获取时时彩五星三码不定位的真实封锁值
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
     private function __fun__ssc_wuxinshanmabudingwei( $sLockTableName = '', $sIssue = '')
     {
         if(!isset($sIssue)|| $sIssue == '' || !isset($sLockTableName) || $sLockTableName == '')
         {
             return array();
         }
         $sSql = " SELECT SUM(`prizes`) AS totallock FROM `". $sLockTableName . "` WHERE `issue` = '" . $sIssue . "'";
         $aCodeLock = $this->oDB->getOne($sSql);
         if( $this->oDB->errno() > 0 )
         {
             return array();
         }
         else
         {
             $aResult = array();
             $aResult['codenum'] = 100000;
             $aResult['totallock'] = $aCodeLock['totallock']*6870;
             return $aResult;
         }
     }
     
     
     /**
     * 获取时时彩五星特殊玩法的真实封锁值
     *
     * @param string $sLockTableName    封锁表名
     * @param string $sIssue            奖期
     * @return array
     * 
     */
     private function __fun__ssc_wuxinteshu( $sLockTableName = '', $sIssue = '', $sParam = '')
     {
         if( !isset($sIssue)|| $sIssue == '' || !isset($sLockTableName) ||
         $sLockTableName == '' || !isset($sParam) || $sParam == '')
         {
             return array();
         }
         $iWei = intval($sParam);
         if($iWei == 0)
         {
             return array();
         }
         $sSql = " SELECT SUM(`prizes`) AS totallock FROM `". $sLockTableName . "` WHERE `issue` = '" . $sIssue . "'";
         $aCodeLock = $this->oDB->getOne($sSql);
         if( $this->oDB->errno() > 0 )
         {
             return array();
         }
         else
         {
             $aResult = array();
             $aResult['codenum'] = 100000;
             switch ($iWei)
             {
                 case 1:
                     $aResult['totallock'] = $aCodeLock['totallock']*40951;
                     break;
                 case 2:
                     $aResult['totallock'] = $aCodeLock['totallock']*8146;
                     break;
                 case 3:
                     $aResult['totallock'] = $aCodeLock['totallock']*856;
                     break;
                 case 4:
                     $aResult['totallock'] = $aCodeLock['totallock']*46;
                     break;
                 default:
                     break;
             }
             return $aResult;
         }
     }
}
?>