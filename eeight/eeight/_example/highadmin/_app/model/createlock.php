<?php
/**
 * 创建封锁表及生成初始数据模型
 * 
 * 1.每一个初始化函数针对特定类型的玩法封锁初始化,如需要维护，需要改动对应函数.
 * 2.如何需要扩展玩法，需要新增相应的函数，可以共用同一个基本数据操作函数[initLockBase].
 * 3.填写玩法封锁表初始化函数时，可以在函数后增加添加一个参数作为特征值.
 *   如：
 *      (1) initNumberTypeYiWeiLock,1 表示玩法使用函数initNumberTypeYiWeiLock进行封锁初始化，并指定特征值为1.
 *      (2) initNumberTypeThreeZhiXuanLock表示没有指定特征值,玩法使用initNumberTypeThreeZhiXuanLock进行封锁初始化.
 * 
 * @author     mark
 * @version    1.0.0
 * @package    highadmin
 * 
 */

class model_createlock extends basemodel
{

    /**
     * 创建封锁表
     * 
     * 检测封锁名是否已经存在，如果存在返回TRUE，
     * 如果不存在，则创建指定表名的封锁表.
     *
     * @param string $sTableName                封锁表名称
     * @param string $sSpecialValueDescription  用于描述特征值字段所表示的含义
     * @return boolean
     * @author mark
     * 
     */
    private function createLockTable( $sTableName = '', $sSpecialValueDescription = '特征值' )
    {
        $sTableName = isset($sTableName) && $sTableName != '' ? $sTableName : '';
        /*表名不正确*/
        if( $sTableName == '' )
        {
            return FALSE;
        }
        /*检测表是否已经存在*/
        $sCheckTableSql = " SHOW TABLES LIKE '" . $sTableName . "'";
        $aCheckResult = $this->oDB->getOne($sCheckTableSql);
        if( !empty($aCheckResult) )
        {
            return TRUE;
        }
        /*创建封锁表*/
        else
        {
            $sSql = "CREATE TABLE `". $sTableName ."` (
                `issue` varchar(20) NOT NULL COMMENT '奖期',
                `threadid` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '线程id',
                `methodid` int(8) NOT NULL DEFAULT '0' COMMENT '玩法ID',
                `code` varchar(100) NOT NULL COMMENT '号码',
                `specialvalue` varchar(100) NOT NULL DEFAULT ' ' COMMENT '".$sSpecialValueDescription."',
                `stamp` varchar(50)  NOT NULL DEFAULT ' ' COMMENT '组三组六特征(数字型：1组三，2组六，乐透型：号码排序值)',
                `prizes` decimal(14,4) NOT NULL DEFAULT '0.0000' COMMENT '奖金值',
                `isbonuscode` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否对应当期开奖号码(0:否,1:是)',
                UNIQUE KEY `idx_index` (`issue`,`methodid`,`code`),
                KEY `idx_code` (`code`),
                KEY `idx_specialvalue` (`specialvalue`),
                KEY `idx_stamp` (`stamp`),
                KEY `idx_issue` (`issue`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            $this->oDB->query($sSql);
            if( $this->oDB->errno() > 0 )
            {
                return FALSE;
            }
            return TRUE;
        }
    }

    /**
     * 初始化销量表数据函数
     * @param  int     $iLotteryId            彩种ID
     * @param  array   $aIssue                奖期
     * @param  string  $sLockTableName        封锁表名称
     * @author rojer
     */
    public function initSales($iLotteryId, $aIssue, $sLockTableName)
    {
        $sql = array();
        foreach ($aIssue as $v)
        {
            //每一期三个进程
            $sql[] = "('$iLotteryId', '{$v['issue']}', '$sLockTableName', '0', '0.00', '0.00')";
            $sql[] = "('$iLotteryId', '{$v['issue']}', '$sLockTableName', '1', '0.00', '0.00')";
            $sql[] = "('$iLotteryId', '{$v['issue']}', '$sLockTableName', '2', '0.00', '0.00')";
        }
        $sql = "INSERT IGNORE `salesbase` (lotteryid,issue,lockname,threadid,moneys,pointmoney) VALUES ".implode(',', $sql);
        $this->oDB->query($sql);
        
        return $this->oDB->ar();
    }

    /**
     * 初始化封锁基本函数
     * 
     * 根据传入的SQL数组插入数据，按不同的特征值进行区分。
     * 如果已经存在相同的数据，则忽略SQL不再重新插入数据。
     * 
     * @param string $sTableName                封锁表名称
     * @param array  $aSql                      sql语句
     * @param boolean $bIsHaveSpecialValue      是否有特征值
     * @param string  $sSpecialValueDescription 特征值描述
     * @param boolean $bIsHaveStamp             是否组三组六
     * @author mark
     */
    private function initLockBase($sTableName, &$aSql, $bIsHaveSpecialValue = FALSE, $sSpecialValueDescription = '特征值', $bIsHaveStamp = FALSE)
    {
        $bCreateTable = $this->createLockTable($sTableName, $sSpecialValueDescription );
        $iInsertCol = 0;
        if($bCreateTable)
        {
            if($bIsHaveSpecialValue)//是否含有特征值
            {
                if ( $bIsHaveStamp )//是否有组三组六特征
                {
                    $sSqlTop = "INSERT IGNORE `" . $sTableName . "` (`issue`,`methodid`,`code`,`specialvalue`,`stamp`) VALUES ";
                }
                else
                {
                    $sSqlTop = "INSERT IGNORE `" . $sTableName . "` (`issue`,`methodid`,`code`,`specialvalue`) VALUES ";
                }

            }
            elseif ( $bIsHaveStamp )//是否有组三组六特征
            {
                $sSqlTop = "INSERT IGNORE `" . $sTableName . "` (`issue`,`methodid`,`code`,`stamp`) VALUES ";
            }
            else//没有指定特征值的情况
            {
                $sSqlTop = "INSERT IGNORE `" . $sTableName . "` (`issue`,`methodid`,`code`) VALUES ";
            }
            
            $iSqlCount = count($aSql);
            //将SQL数组分组，每十段为一组，一次插入十期的封锁数据
            for ($i=0; $i<$iSqlCount; $i+=10)
            {
                $tmp = $sSqlTop.implode(',', array_slice($aSql, $i, 10));
                $this->oDB->query($tmp);
                $iInsertCol += $this->oDB->ar();
            }
        }
        
        return $iInsertCol;
    }


    /**
     * 字符串排序
     * @param string  $sString  需要排序的字符串
     * @param boolean $bDesc    默认按字符串从小到大排序，如$bDesc=TRUE,则字符串从大到小排序
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
            rsort($aString);//倒序排序，从大到小排序
        }
        else
        {
            sort($aString);//从小到大排序
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
            return array();//选择的个数不能大于基数数组的个数。
        }
        if( $iSelectNum == 1 )
        {
            return $aBaseArray;//如果只选一个数，则返回基数数组
        }
        if( $iBaseNum == $iSelectNum )
        {
            return array(implode(' ',$aBaseArray));//如果选择的个数刚好等于基数数组的个数，刚返回由数组构成的字符串
        }
        $sString = '';
        $sLastString = '';
        $sTempStr = '';
        $aResult = array();
        //构建01字符串
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
        //循环移动字符1
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
     * 初始化数字型三位直选封锁表数据
     *
     * @param string $sTableName        封锁表名称
     * @param array  $aIssue            生成奖期
     * @param array  $aNumberRule       游戏号码规则
     * @param int    $iMethodId         玩法ID
     * @return boolean
     * @author mark
     * 
     */
    public function initNumberTypeThreeZhiXuanLock( $sTableName = '', $aIssue = array(), $aNumberRule = array() ,$iMethodId = 0 )
    {
        if( $iMethodId == 0 )
        {
            echo '没有指定玩法ID';
            return FALSE;
        }
        $iMethodId = intval($iMethodId);
        $aValue = array();
        foreach ($aIssue as $aIssueDetail)
        {
            for( $i=0; $i<10; $i++ )
            {
                for( $j=0; $j<10; $j++ )
                {
                    for( $k=0; $k<10; $k++ )
                    {
                        $iHzValue = $i+$j+$k;//号码特征值记录号码的和值
                        $iKdValue = abs(max($i,$j,$k)-min($i,$j,$k));//号码特征值记录号码跨度值
                        $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$i.$j.$k."','".$iHzValue."','".$iKdValue."')";
                    }
                }
            }
            $aSql[]= implode(",", $aValue);
            $aValue = array();
        }
        $bIsHaveSpecialValue = TRUE;
        $bIsHaveStamp = TRUE;
        $sSpecialValueDescription = '特征值:号码和值';
        return $this->initLockBase($sTableName, $aSql, $bIsHaveSpecialValue, $sSpecialValueDescription,$bIsHaveStamp);
    }

    
    /**
     * 初始化数字型两位直选封锁表数据
     *
     * @param string $sTableName        封锁表名称
     * @param array  $aIssue            生成奖期
     * @param array  $aNumberRule       游戏号码规则
     * @param int    $iMethodId         玩法ID
     * @return boolean
     * @author mark
     * 
     */
    public function initNumberTypeTwoZhiXuanLock( $sTableName = '', $aIssue = array(), $aNumberRule = array() ,$iMethodId = 0 )
    {
        if( $iMethodId == 0 )
        {
            echo '没有指定玩法ID';
            return FALSE;
        }
        if(!isset($aNumberRule['specialvalue']))
        {
            echo '参数不正确';
            return FALSE;
        }
        $iMethodId = intval($iMethodId);
        $aValue = array();
        foreach ($aIssue as $aIssueDetail)
        {
            for( $i=0; $i<10; $i++ )
            {
                for( $j=0; $j<10; $j++ )
                {
                    $iKdValue = abs(max($i,$j)-min($i,$j));//号码特征值记录号码跨度值
                    $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$i.$j."','".intval(($i+$j))."','".$iKdValue."')";
                }
            }
            $aSql[]= implode(",", $aValue);
            $aValue = array();
        }
        $bIsHaveSpecialValue = TRUE;
        $bIsHaveStamp = TRUE;
        $sSpecialValueDescription = '特征值:和值';
        return $this->initLockBase($sTableName, $aSql, $bIsHaveSpecialValue, $sSpecialValueDescription,$bIsHaveStamp);
    }


    /**
     * 初始化数字型两位组选封锁表数据
     *
     * @param string $sTableName        封锁表名称
     * @param array  $aIssue            生成奖期
     * @param array  $aNumberRule       游戏号码规则
     * @param int    $iMethodId         玩法ID
     * @return boolean
     * @author mark
     * 
     */
    public function initNumberTypeTwoZhuXuanLock( $sTableName = '', $aIssue = array(), $aNumberRule = array() ,$iMethodId = 0 )
    {
        if( $iMethodId == 0 )
        {
            echo '没有指定玩法ID';
            return FALSE;
        }
        $iMethodId = intval($iMethodId);
        $aValue = array();
        foreach ($aIssue as $aIssueDetail)
        {
            for( $i=0; $i<9; $i++ )
            {
                for( $j=$i+1; $j<10; $j++ )
                {
                    if( $i != $j )
                    {
                        $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$i.$j."')";
                    }
                }
            }
            $aSql[]= implode(",", $aValue);
            $aValue = array();
        }
        return $this->initLockBase($sTableName, $aSql);
    }


    /**
     * 初始化数字型组三封锁表数据
     *
     * @param string $sTableName        封锁表名称
     * @param array  $aIssue            生成奖期
     * @param array  $aNumberRule       游戏号码规则
     * @param int    $iMethodId         玩法ID
     * @return boolean
     * @author mark
     * 
     */
    public function initNumberTypeZhuShanLock( $sTableName = '', $aIssue = array(), $aNumberRule = array() ,$iMethodId = 0 )
    {
        if( $iMethodId == 0 )
        {
            echo '没有指定玩法ID';
            return FALSE;
        }
        if(!isset($aNumberRule['specialvalue']))
        {
            echo '参数不正确';
            return FALSE;
        }
        $iMethodId = intval($iMethodId);
        $aValue = array();
        foreach ($aIssue as $aIssueDetail)
        {
            for( $i=0; $i<10; $i++ )
            {
                for( $j=0; $j<10; $j++ )
                {
                    if( $i != $j )
                    {
                        $sCode = $this->strOrder($i.$i.$j);
                        $iHzValue = $i+$i+$j;
                        $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$sCode."','".$iHzValue."','".$aNumberRule['specialvalue']."')";
                    }
                }
            }
            $aSql[]= implode(",", $aValue);
            $aValue = array();
        }
        $bIsHaveSpecialValue = TRUE;
        $bIsHaveStamp = TRUE;
        $sSpecialValueDescription = '特征值:号码和值';
        return $this->initLockBase($sTableName, $aSql, $bIsHaveSpecialValue, $sSpecialValueDescription, $bIsHaveStamp);
    }


    /**
     * 初始化数字型组六封锁表数据
     *
     * @param string $sTableName        封锁表名称
     * @param array  $aIssue            生成奖期
     * @param array  $aNumberRule       游戏号码规则
     * @param int    $iMethodId         玩法ID
     * @return boolean
     * @author mark
     * 
     */
    public function initNumberTypeZhuLiuLock( $sTableName = '', $aIssue = array(), $aNumberRule = array() ,$iMethodId = 0 )
    {
        if( $iMethodId == 0 )
        {
            echo '没有指定玩法ID';
            return FALSE;
        }
        if(!isset($aNumberRule['specialvalue']))
        {
            echo '参数不正确';
            return FALSE;
        }
        $iMethodId = intval($iMethodId);
        $aValue = array();
        foreach ($aIssue as $aIssueDetail)
        {
            for( $i=0; $i<8; $i++ )
            {
                for( $j=$i+1; $j<9; $j++ )
                {
                    for( $k=$j+1; $k<10; $k++ )
                    {
                        $iHzValue = $i+$j+$k;
                        $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$i.$j.$k."','".$iHzValue."','".$aNumberRule['specialvalue']."')";
                    }
                }
            }
            $aSql[]= implode(",", $aValue);
            $aValue = array();
        }
        $bIsHaveSpecialValue = TRUE;
        $bIsHaveStamp = TRUE;
        $sSpecialValueDescription = '特征值:号码和值';
        return $this->initLockBase($sTableName, $aSql, $bIsHaveSpecialValue, $sSpecialValueDescription, $bIsHaveStamp);
    }

    
    /**
     * 初始化数字型不定位数据封锁表数据
     *
     * @param string $sTableName        封锁表名称
     * @param array  $aIssue            生成奖期
     * @param array  $aNumberRule       游戏号码规则
     * @param int    $iMethodId         玩法ID
     * @return boolean
     * @author mark
     * 
     */
    public function initNumberTypeBudingWeiLock( $sTableName = '', $aIssue = array(), $aNumberRule = array() ,$iMethodId = 0 )
    {
        if( $iMethodId == 0 )
        {
            echo '没有指定玩法ID';
            return FALSE;
        }
        $bIsHaveSpecialValue = FALSE;
        $sSpecialValueDescription = '特征值';
        if(isset($aNumberRule['specialvalue']) && is_numeric($aNumberRule['specialvalue']))
        {
            $bIsHaveSpecialValue = TRUE;//指定特征值
            $sSpecialValueDescription = '特征值';
        }
        $iMethodId = intval($iMethodId);
        $aValue = array();
        foreach ($aIssue as $aIssueDetail)
        {
            for( $i=0; $i<10; $i++ )
            {
                for( $j=0; $j<10; $j++ )
                {
                    for( $k=0; $k<10; $k++ )
                    {
                        $sLastCode = $this->strOrder($i.$j.$k);
                        $tmpValue = "('".$aIssueDetail['issue']."','".$iMethodId."','".$sLastCode."')";
                        if(!in_array($tmpValue, $aValue))
                        {
                            $aValue[] = $tmpValue;
                        }
                    }
                }
            }
            $aSql[]= implode(",", $aValue);
            $aValue = array();
        }
        return $this->initLockBase($sTableName, $aSql);
    }
    
    
    /**
     * 初始化数字型一码定位数据封锁表数据
     *
     * @param string $sTableName        封锁表名称
     * @param array  $aIssue            生成奖期
     * @param array  $aNumberRule       游戏号码规则
     * @param int    $iMethodId         玩法ID
     * @return boolean
     * @author mark
     * 
     */
    public function initNumberTypeYiWeiLock( $sTableName = '', $aIssue = array(), $aNumberRule = array() ,$iMethodId = 0 )
    {
        if( $iMethodId == 0 )
        {
            echo '没有指定玩法ID';
            return FALSE;
        }
        $bIsHaveSpecialValue = FALSE;
        $sSpecialValueDescription = '特征值';
        if(isset($aNumberRule['specialvalue']) && is_numeric($aNumberRule['specialvalue']))
        {
            $bIsHaveSpecialValue = TRUE;//指定特征值
            $sSpecialValueDescription = '特征值:区分指定位：1万位，2千位，3百位，4十位，5个位';
        }
        $iMethodId = intval($iMethodId);
        $aValue = array();
        foreach ($aIssue as $aIssueDetail)
        {
            for( $i=0; $i<10; $i++ )
            {
                if( $bIsHaveSpecialValue )
                {
                    $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$i."','".$aNumberRule['specialvalue']."')";
                }
                else 
                {
                    $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$i."')";
                }
            }
            $aSql[]= implode(",", $aValue);
            $aValue = array();
        }
        return $this->initLockBase($sTableName, $aSql, $bIsHaveSpecialValue, $sSpecialValueDescription);
    }



    /**
     * 初始化数字型大小单双数据封锁表数据
     * 0:大，1小，2单，3双
     *
     * @param string $sTableName        封锁表名称
     * @param array  $aIssue            生成奖期
     * @param array  $aNumberRule       游戏号码规则
     * @param int    $iMethodId         玩法ID 
     * @return boolean
     * @author mark
     * 
     */
    public function initNumberTypeDXDSLock( $sTableName = '', $aIssue = array(), $aNumberRule = array() ,$iMethodId = 0 )
    {
        if( $iMethodId == 0 )
        {
            echo '没有指定玩法ID';
            return FALSE;
        }
        if(!isset($aNumberRule['specialvalue']))
        {
            echo '参数不正确';
            return FALSE;
        }
        $iMethodId = intval($iMethodId);
        $aValue = array();
        foreach ($aIssue as $aIssueDetail)
        {
            for( $i=0; $i<4; $i++ )
            {
                for( $j=0; $j<4; $j++ )
                {
                    $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$i.$j."','".$aNumberRule['specialvalue']."')";
                }
            }
            $aSql[]= implode(",", $aValue);
            $aValue = array();
        }
        $bIsHaveSpecialValue = TRUE;
        $sSpecialValueDescription = '特征值:区分前二与后二:1前二，2后二';
        return $this->initLockBase($sTableName, $aSql, $bIsHaveSpecialValue, $sSpecialValueDescription);
    }


    /**
     * 乐透同区任选初始化封锁数据
     *
     * @param string $sTableName  封锁表名称
     * @param array  $aIssue      生成奖期
     * @param array  $aNumberRule 号码规则
     * @param int    $iMethodId   玩法ID
     * @return boolean
     * @author mark
     * 
     */
    public function initLeTouTypeRXLock( $sTableName = '', $aIssue = array(), $aNumberRule = array() ,$iMethodId = 0 )
    {
        if( $iMethodId == 0 )
        {
            echo '没有指定玩法ID';
            return FALSE;
        }
        if(!isset($aNumberRule['specialvalue']))
        {
            echo '参数不正确';
            return FALSE;
        }
        $iMethodId = intval($iMethodId);
        if(empty($aNumberRule))
        {
            echo '彩种号码规则没有，请确认';
            return FALSE;
        }
        if( !isset($aNumberRule['startno']) || !isset($aNumberRule['endno']) || !isset($aNumberRule['len']) )
        {
            echo '彩种号码规则不正确，必须有开始号码、结束号码、号码长度三个参数,请确认';
            return FALSE;
        }
        $aBaseArray = array();
        $iStartNo = intval($aNumberRule['startno']);
        $iEndNo   = intval($aNumberRule['endno']);
        for( $i=$iStartNo; $i<=$iEndNo; $i++ )
        {
            $aBaseArray[] = strlen($i) < 2 ? str_pad($i, 2,'0', STR_PAD_LEFT ) : $i;
        }
        $iSelect = $aNumberRule['specialvalue'];
        $aCode = $this->getCombination($aBaseArray, $iSelect);
        $aValue = array();
        foreach ($aIssue as $aIssueDetail)
        {
            foreach ($aCode as $sCode)
            {
                $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$sCode."','".$aNumberRule['specialvalue']."')";
            }
            $aSql[]= implode(",", $aValue);
            $aValue = array();
        }
        $bIsHaveSpecialValue = TRUE;
        $sSpecialValueDescription = '特征值:区分任选个数,如1为任选1，2为任选2.....';
        return $this->initLockBase($sTableName, $aSql, $bIsHaveSpecialValue, $sSpecialValueDescription);
    }
    
    
    
    /**
     * 乐透同区猜中位初始化封锁数据
     *
     * @param string $sTableName  封锁表名称
     * @param array  $aIssue      生成奖期
     * @param array  $aNumberRule 号码规则
     * @param int    $iMethodId   玩法ID
     * @return boolean
     * @author mark
     * 
     */
    public function initLeTouTypeZhongWeiLock( $sTableName = '', $aIssue = array(), $aNumberRule = array() ,$iMethodId = 0 )
    {
        if( $iMethodId == 0 )
        {
            echo '没有指定玩法ID';
            return FALSE;
        }
        if(!isset($aNumberRule['specialvalue']))
        {
            echo '参数不正确';
            return FALSE;
        }
        $iMethodId = intval($iMethodId);
        if(empty($aNumberRule))
        {
            echo '彩种号码规则没有，请确认';
            return FALSE;
        }
        if( !isset($aNumberRule['startno']) || !isset($aNumberRule['endno']) || !isset($aNumberRule['len']) )
        {
            echo '彩种号码规则不正确，必须有开始号码、结束号码、号码长度三个参数,请确认';
            return FALSE;
        }
        $aCode = array();
        $iMidNumber = floor($aNumberRule['len']/2);
        $iStartNo = intval($aNumberRule['startno']) + $iMidNumber;
        $iEndNo   = intval($aNumberRule['endno']) - $iMidNumber;
        for( $i=$iStartNo; $i<=$iEndNo; $i++ )
        {
            $aCode[] = str_pad($i, 2, "0", STR_PAD_LEFT );
        }
        $aValue = array();
        foreach ($aIssue as $aIssueDetail)
        {
            foreach ($aCode as $sCode)
            {
                $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$sCode."','".$aNumberRule['specialvalue']."')";
            }
            $aSql[]= implode(",", $aValue);
            $aValue = array();
        }
        $bIsHaveSpecialValue = TRUE;
        $sSpecialValueDescription = '特征值:区分猜中位和猜单双,1猜单双，2猜中位';
        return $this->initLockBase($sTableName, $aSql, $bIsHaveSpecialValue, $sSpecialValueDescription);
    }
    
    
    /**
     * 乐透同区中单双初始化封锁数据
     *
     * @param string $sTableName  封锁表名称
     * @param array  $aIssue      生成奖期
     * @param array  $aNumberRule 号码规则
     * @param int    $iMethodId   玩法ID
     * @return boolean
     * @author mark
     * 
     */
    public function initLeTouTypeDSLock( $sTableName = '', $aIssue = array(), $aNumberRule = array() ,$iMethodId = 0 )
    {
        if( $iMethodId == 0 )
        {
            echo '没有指定玩法ID';
            return FALSE;
        }
        if(!isset($aNumberRule['specialvalue']))
        {
            echo '参数不正确';
            return FALSE;
        }
        $iMethodId = intval($iMethodId);
        if(empty($aNumberRule))
        {
            echo '彩种号码规则没有，请确认';
            return FALSE;
        }
        if( !isset($aNumberRule['startno']) || !isset($aNumberRule['endno']) || !isset($aNumberRule['len']) )
        {
            echo '彩种号码规则不正确，必须有开始号码、结束号码、号码长度三个参数,请确认';
            return FALSE;
        }
        $aCode = array();
        $iCodeCount = ceil($aNumberRule['endno']/2);
        for( $i=0; $i<$iCodeCount; $i++ )
        {
            $aCode[] = $i;
        }
        $aValue = array();
        foreach ($aIssue as $aIssueDetail)
        {
            foreach ($aCode as $sCode)
            {
                $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$sCode."','".$aNumberRule['specialvalue']."')";
            }
            $aSql[]= implode(",", $aValue);
            $aValue = array();
        }
        $bIsHaveSpecialValue = TRUE;
        $sSpecialValueDescription = '特征值:区分猜中位和猜单双,1猜单双，2猜中位';
        return $this->initLockBase($sTableName, $aSql, $bIsHaveSpecialValue, $sSpecialValueDescription);
    }
    
    
    /**
     * 乐透同区指定位初始化封锁数据
     *
     * @param string $sTableName  封锁表名称
     * @param array  $aIssue      生成奖期
     * @param array  $aNumberRule 号码规则
     * @param int    $iMethodId   玩法ID
     * @return boolean
     * @author mark
     * 
     */
    public function initLeTouTypeYiWeiLock( $sTableName = '', $aIssue = array(), $aNumberRule = array() ,$iMethodId = 0 )
    {
        if( $iMethodId == 0 )
        {
            echo '没有指定玩法ID';
            return FALSE;
        }
        $bIsHaveSpecialValue = FALSE;
        $sSpecialValueDescription = '特征值';
        if(isset($aNumberRule['specialvalue']) && is_numeric($aNumberRule['specialvalue']))
        {
            $bIsHaveSpecialValue = TRUE;//指定特征值
            $sSpecialValueDescription = '特征值:区分指定位：1第一位，2第二位，3第三位';
        }
        $iMethodId = intval($iMethodId);
        if(empty($aNumberRule))
        {
            echo '彩种号码规则没有，请确认';
            return FALSE;
        }
        if( !isset($aNumberRule['startno']) || !isset($aNumberRule['endno']) || !isset($aNumberRule['len']) )
        {
            echo '彩种号码规则不正确，必须有开始号码、结束号码、号码长度三个参数,请确认';
            return FALSE;
        }
        $aCode = array();
        $iStartNo = intval($aNumberRule['startno']);
        $iEndNo   = intval($aNumberRule['endno']);
        for( $i=$iStartNo; $i<=$iEndNo; $i++ )
        {
            $aCode[] = strlen($i) < 2 ? str_pad($i, 2, '0', STR_PAD_LEFT ) : $i;
        }
        $aValue = array();
        foreach ($aIssue as $aIssueDetail)
        {
            foreach ($aCode as $sCode)
            {
                if( $bIsHaveSpecialValue )
                {
                    $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$sCode."','".$aNumberRule['specialvalue']."')";
                }
                else 
                {
                    $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$sCode."')";
                }
            }
            $aSql[]= implode(",", $aValue);
            $aValue = array();
        }
        return $this->initLockBase($sTableName, $aSql, $bIsHaveSpecialValue, $sSpecialValueDescription);
    }
    
    
    /**
     * 乐透同区前三直选初始化封锁数据
     *
     * @param string $sTableName  封锁表名称
     * @param array  $aIssue      生成奖期
     * @param array  $aNumberRule 号码规则
     * @param int    $iMethodId   玩法ID
     * @return boolean
     * @author mark
     * 
     */
    public function initLeTouTypeShanZhiXuanLock( $sTableName = '', $aIssue = array(), $aNumberRule = array() ,$iMethodId = 0 )
    {
        if( $iMethodId == 0 )
        {
            echo '没有指定玩法ID';
            return FALSE;
        }
        $iMethodId = intval($iMethodId);
        if(empty($aNumberRule))
        {
            echo '彩种号码规则没有，请确认';
            return FALSE;
        }
        if( !isset($aNumberRule['startno']) || !isset($aNumberRule['endno']) || !isset($aNumberRule['len']) )
        {
            echo '彩种号码规则不正确，必须有开始号码、结束号码、号码长度三个参数,请确认';
            return FALSE;
        }
        $aCode = array();
        $iStartNo = intval($aNumberRule['startno']);
        $iEndNo   = intval($aNumberRule['endno']);
        for( $i=$iStartNo; $i<=$iEndNo; $i++ )
        {
            for( $j=$iStartNo; $j<=$iEndNo; $j++ )
            {
                for( $k=$iStartNo; $k<=$iEndNo; $k++ )
                {
                    if( $i != $j && $j != $k && $i != $k )
                    {
                        $i = strlen($i) < 2 ? str_pad($i, 2, '0', STR_PAD_LEFT ) : $i;
                        $j = strlen($j) < 2 ? str_pad($j, 2, '0', STR_PAD_LEFT ) : $j;
                        $k = strlen($k) < 2 ? str_pad($k, 2, '0', STR_PAD_LEFT ) : $k;
                        $aCode[] = $i . ' ' . $j . ' ' . $k;
                    }
                }
            }
        }
        $aValue = array();
        foreach ($aIssue as $aIssueDetail)
        {
            foreach ($aCode as $sCode)
            {
                //获取组选号码特征值
                $aTmpCode = explode(" ",$sCode);
                sort($aTmpCode);
                $sStampCode = implode(" ",$aTmpCode);
                $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$sCode."','".$sStampCode."')";
            }
            $aSql[]= implode(",", $aValue);
            $aValue = array();
        }
        return $this->initLockBase($sTableName, $aSql, FALSE, '', TRUE);
    }
    
    
    /**
     * 乐透同区前三组选初始化封锁数据
     *
     * @param string $sTableName  封锁表名称
     * @param array  $aIssue      生成奖期
     * @param array  $aNumberRule 号码规则
     * @param int    $iMethodId   玩法ID
     * @return boolean
     * @author mark
     * 
     */
    public function initLeTouTypeShanZhuXuanLock( $sTableName = '', $aIssue = array(), $aNumberRule = array() ,$iMethodId = 0 )
    {
        if( $iMethodId == 0 )
        {
            echo '没有指定玩法ID';
            return FALSE;
        }
        $iMethodId = intval($iMethodId);
        if(empty($aNumberRule))
        {
            echo '彩种号码规则没有，请确认';
            return FALSE;
        }
        if( !isset($aNumberRule['startno']) || !isset($aNumberRule['endno']) || !isset($aNumberRule['len']) )
        {
            echo '彩种号码规则不正确，必须有开始号码、结束号码、号码长度三个参数,请确认';
            return FALSE;
        }
        $aCode = array();
        $iStartNo = intval($aNumberRule['startno']);
        $iEndNo   = intval($aNumberRule['endno']);
        for( $i=$iStartNo; $i<=$iEndNo-2; $i++ )
        {
            for( $j=$iStartNo+1; $j<=$iEndNo-1; $j++ )
            {
                for( $k=$iStartNo+2; $k<=$iEndNo; $k++ )
                {
                    if( $i != $j && $j != $k && $i != $k )
                    {
                        $i = strlen($i) < 2 ? str_pad($i, 2, '0', STR_PAD_LEFT ) : $i;
                        $j = strlen($j) < 2 ? str_pad($j, 2, '0', STR_PAD_LEFT ) : $j;
                        $k = strlen($k) < 2 ? str_pad($k, 2, '0', STR_PAD_LEFT ) : $k;
                        $aLastCode = array($i, $j, $k);
                        sort($aLastCode);
                        if(!in_array(implode(" ",$aLastCode), $aCode))
                        {
                            $aCode[] = implode(" ", $aLastCode);
                        }
                    }
                }
            }
        }
        $aValue = array();
        foreach ($aIssue as $aIssueDetail)
        {
            foreach ($aCode as $sCode)
            {
                $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$sCode."')";
            }
            $aSql[]= implode(",", $aValue);
            $aValue = array();
        }
        return $this->initLockBase($sTableName, $aSql);
    }
    
    
    /**
     * 乐透同区前二直选初始化封锁数据
     *
     * @param string $sTableName  封锁表名称
     * @param array  $aIssue      生成奖期
     * @param array  $aNumberRule 号码规则
     * @param int    $iMethodId   玩法ID
     * @return boolean
     * @author mark
     * 
     */
    public function initLeTouTypeErZhiXuanLock( $sTableName = '', $aIssue = array(), $aNumberRule = array() ,$iMethodId = 0 )
    {
        if( $iMethodId == 0 )
        {
            echo '没有指定玩法ID';
            return FALSE;
        }
        $iMethodId = intval($iMethodId);
        if(empty($aNumberRule))
        {
            echo '彩种号码规则没有，请确认';
            return FALSE;
        }
        if( !isset($aNumberRule['startno']) || !isset($aNumberRule['endno']) || !isset($aNumberRule['len']) )
        {
            echo '彩种号码规则不正确，必须有开始号码、结束号码、号码长度三个参数,请确认';
            return FALSE;
        }
        $aCode = array();
        $iStartNo = intval($aNumberRule['startno']);
        $iEndNo   = intval($aNumberRule['endno']);
        for( $i=$iStartNo; $i<=$iEndNo; $i++ )
        {
            for( $j=$iStartNo; $j<=$iEndNo; $j++ )
            {               
                if( $i != $j )
                {
                    $i = strlen($i) < 2 ? str_pad($i, 2, '0', STR_PAD_LEFT ) : $i;
                    $j = strlen($j) < 2 ? str_pad($j, 2, '0', STR_PAD_LEFT ) : $j;
                    $aCode[] = $i . ' ' . $j;
                }
            }
        }
        $aValue = array();
        foreach ($aIssue as $aIssueDetail)
        {
            foreach ($aCode as $sCode)
            {
                //获取组选号码特征值
                $aTmpCode = explode(" ",$sCode);
                sort($aTmpCode);
                $sStampCode = implode(" ",$aTmpCode);
                $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$sCode."','".$sStampCode."')";
            }
            $aSql[]= implode(",", $aValue);
            $aValue = array();
        }
        return $this->initLockBase($sTableName, $aSql, FALSE, '', TRUE);
    }
    
    
    /**
     * 北京快乐八任选一封锁表数据初始化
     *
     * @param string $sTableName  封锁表名称
     * @param array  $aIssue      生成奖期
     * @param array  $aNumberRule 号码规则
     * @param int    $iMethodId   玩法ID
     * @return boolean
     * @author mark
     * 
     */
    public function initBJKLRX1Lock( $sTableName = '', $aIssue = array(), $aNumberRule = array() ,$iMethodId = 0 )
    {
        if( $iMethodId == 0 )
        {
            echo '没有指定玩法ID';
            return FALSE;
        }
        $iMethodId = intval($iMethodId);
        if(empty($aNumberRule))
        {
            echo '彩种号码规则没有，请确认';
            return FALSE;
        }
        if( !isset($aNumberRule['startno']) || !isset($aNumberRule['endno']) )
        {
            echo '彩种号码规则不正确，必须有开始号码、结束号码、号码长度三个参数,请确认';
            return FALSE;
        }
        $aCode = array();
        $iStartNo = intval($aNumberRule['startno']);
        $iEndNo   = intval($aNumberRule['endno']);
        for( $i=$iStartNo; $i<=$iEndNo; $i++ )
        {
            $i = strlen($i) < 2 ? str_pad($i, 2, '0', STR_PAD_LEFT ) : $i;
            $aCode[] = $i;
        }
        $aValue = array();
        foreach ($aIssue as $aIssueDetail)
        {
            foreach ($aCode as $sCode)
            {
                $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$sCode."')";
            }
            $aSql[]= implode(",", $aValue);
            $aValue = array();
        }
        return $this->initLockBase($sTableName, $aSql, FALSE, '', FALSE);
    } 
    
    
    /**
     * 北京快乐八和值单双封锁表数据初始化
     *
     * @param string $sTableName  封锁表名称
     * @param array  $aIssue      生成奖期
     * @param array  $aNumberRule 号码规则
     * @param int    $iMethodId   玩法ID
     * @return boolean
     * @author mark
     * 
     */
    public function initBJKLHEDSLock( $sTableName = '', $aIssue = array(), $aNumberRule = array() ,$iMethodId = 0 )
    {
        if( $iMethodId == 0 )
        {
            echo '没有指定玩法ID';
            return FALSE;
        }
        $iMethodId = intval($iMethodId);
        $aCode = array(0,1);
        $aValue = array();
        foreach ($aIssue as $aIssueDetail)
        {
            foreach ($aCode as $sCode)
            {
                $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$sCode."')";
            }
            $aSql[]= implode(",", $aValue);
            $aValue = array();
        }
        return $this->initLockBase($sTableName, $aSql, FALSE, '', FALSE);
    }
    
    
    /**
     * 北京快乐八和值大小，上下盘，奇偶盘封锁表数据初始化
     *
     * @param string $sTableName  封锁表名称
     * @param array  $aIssue      生成奖期
     * @param array  $aNumberRule 号码规则
     * @param int    $iMethodId   玩法ID
     * @return boolean
     * @author mark
     * 
     */
    public function initBJKLHEPANLock( $sTableName = '', $aIssue = array(), $aNumberRule = array() ,$iMethodId = 0 )
    {
        if( $iMethodId == 0 )
        {
            echo '没有指定玩法ID';
            return FALSE;
        }
        $iMethodId = intval($iMethodId);
        $aCode = array(0,1,2);
        $aValue = array();
        foreach ($aIssue as $aIssueDetail)
        {
            foreach ($aCode as $sCode)
            {
                $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$sCode."')";
            }
            $aSql[]= implode(",", $aValue);
            $aValue = array();
        }
        return $this->initLockBase($sTableName, $aSql, FALSE, '', FALSE);
    }
    
    
    

    /**
     * CCS五星、四星组选封锁表数据的生成
     *
     * @param string $sTableName  封锁表名称
     * @param array  $aIssue      生成奖期
     * @param array  $aNumberRule 号码规则
     * @param int    $iMethodId   玩法ID
     * @return boolean
     * @author mark
     * 
     */
    public function initNumberTypeZhuXuanLock( $sTableName = '', $aIssue = array(), $aNumberRule = array() ,$iMethodId = 0 )
    {
        $aALlCode[120] = array('01234','01235','01236','01237','01238','01239','01245','01246','01247','01248','01249','01256','01257','01258','01259','01267','01268','01269','01278','01279','01289','01345','01346','01347','01348','01349','01356','01357','01358','01359','01367','01368','01369','01378','01379','01389','01456','01457','01458','01459','01467','01468','01469','01478','01479','01489','01567','01568','01569','01578','01579','01589','01678','01679','01689','01789','02345','02346','02347','02348','02349','02356','02357','02358','02359','02367','02368','02369','02378','02379','02389','02456','02457','02458','02459','02467','02468','02469','02478','02479','02489','02567','02568','02569','02578','02579','02589','02678','02679','02689','02789','03456','03457','03458','03459','03467','03468','03469','03478','03479','03489','03567','03568','03569','03578','03579','03589','03678','03679','03689','03789','04567','04568','04569','04578','04579','04589','04678','04679','04689','04789','05678','05679','05689','05789','06789','12345','12346','12347','12348','12349','12356','12357','12358','12359','12367','12368','12369','12378','12379','12389','12456','12457','12458','12459','12467','12468','12469','12478','12479','12489','12567','12568','12569','12578','12579','12589','12678','12679','12689','12789','13456','13457','13458','13459','13467','13468','13469','13478','13479','13489','13567','13568','13569','13578','13579','13589','13678','13679','13689','13789','14567','14568','14569','14578','14579','14589','14678','14679','14689','14789','15678','15679','15689','15789','16789','23456','23457','23458','23459','23467','23468','23469','23478','23479','23489','23567','23568','23569','23578','23579','23589','23678','23679','23689','23789','24567','24568','24569','24578','24579','24589','24678','24679','24689','24789','25678','25679','25689','25789','26789','34567','34568','34569','34578','34579','34589','34678','34679','34689','34789','35678','35679','35689','35789','36789','45678','45679','45689','45789','46789','56789');
        $aALlCode[60]  = array('00123','00124','00125','00126','00127','00128','00129','00134','00135','00136','00137','00138','00139','00145','00146','00147','00148','00149','00156','00157','00158','00159','00167','00168','00169','00178','00179','00189','00234','00235','00236','00237','00238','00239','00245','00246','00247','00248','00249','00256','00257','00258','00259','00267','00268','00269','00278','00279','00289','00345','00346','00347','00348','00349','00356','00357','00358','00359','00367','00368','00369','00378','00379','00389','00456','00457','00458','00459','00467','00468','00469','00478','00479','00489','00567','00568','00569','00578','00579','00589','00678','00679','00689','00789','01123','01124','01125','01126','01127','01128','01129','01134','01135','01136','01137','01138','01139','01145','01146','01147','01148','01149','01156','01157','01158','01159','01167','01168','01169','01178','01179','01189','01223','01224','01225','01226','01227','01228','01229','01233','01244','01255','01266','01277','01288','01299','01334','01335','01336','01337','01338','01339','01344','01355','01366','01377','01388','01399','01445','01446','01447','01448','01449','01455','01466','01477','01488','01499','01556','01557','01558','01559','01566','01577','01588','01599','01667','01668','01669','01677','01688','01699','01778','01779','01788','01799','01889','01899','02234','02235','02236','02237','02238','02239','02245','02246','02247','02248','02249','02256','02257','02258','02259','02267','02268','02269','02278','02279','02289','02334','02335','02336','02337','02338','02339','02344','02355','02366','02377','02388','02399','02445','02446','02447','02448','02449','02455','02466','02477','02488','02499','02556','02557','02558','02559','02566','02577','02588','02599','02667','02668','02669','02677','02688','02699','02778','02779','02788','02799','02889','02899','03345','03346','03347','03348','03349','03356','03357','03358','03359','03367','03368','03369','03378','03379','03389','03445','03446','03447','03448','03449','03455','03466','03477','03488','03499','03556','03557','03558','03559','03566','03577','03588','03599','03667','03668','03669','03677','03688','03699','03778','03779','03788','03799','03889','03899','04456','04457','04458','04459','04467','04468','04469','04478','04479','04489','04556','04557','04558','04559','04566','04577','04588','04599','04667','04668','04669','04677','04688','04699','04778','04779','04788','04799','04889','04899','05567','05568','05569','05578','05579','05589','05667','05668','05669','05677','05688','05699','05778','05779','05788','05799','05889','05899','06678','06679','06689','06778','06779','06788','06799','06889','06899','07789','07889','07899','11234','11235','11236','11237','11238','11239','11245','11246','11247','11248','11249','11256','11257','11258','11259','11267','11268','11269','11278','11279','11289','11345','11346','11347','11348','11349','11356','11357','11358','11359','11367','11368','11369','11378','11379','11389','11456','11457','11458','11459','11467','11468','11469','11478','11479','11489','11567','11568','11569','11578','11579','11589','11678','11679','11689','11789','12234','12235','12236','12237','12238','12239','12245','12246','12247','12248','12249','12256','12257','12258','12259','12267','12268','12269','12278','12279','12289','12334','12335','12336','12337','12338','12339','12344','12355','12366','12377','12388','12399','12445','12446','12447','12448','12449','12455','12466','12477','12488','12499','12556','12557','12558','12559','12566','12577','12588','12599','12667','12668','12669','12677','12688','12699','12778','12779','12788','12799','12889','12899','13345','13346','13347','13348','13349','13356','13357','13358','13359','13367','13368','13369','13378','13379','13389','13445','13446','13447','13448','13449','13455','13466','13477','13488','13499','13556','13557','13558','13559','13566','13577','13588','13599','13667','13668','13669','13677','13688','13699','13778','13779','13788','13799','13889','13899','14456','14457','14458','14459','14467','14468','14469','14478','14479','14489','14556','14557','14558','14559','14566','14577','14588','14599','14667','14668','14669','14677','14688','14699','14778','14779','14788','14799','14889','14899','15567','15568','15569','15578','15579','15589','15667','15668','15669','15677','15688','15699','15778','15779','15788','15799','15889','15899','16678','16679','16689','16778','16779','16788','16799','16889','16899','17789','17889','17899','22345','22346','22347','22348','22349','22356','22357','22358','22359','22367','22368','22369','22378','22379','22389','22456','22457','22458','22459','22467','22468','22469','22478','22479','22489','22567','22568','22569','22578','22579','22589','22678','22679','22689','22789','23345','23346','23347','23348','23349','23356','23357','23358','23359','23367','23368','23369','23378','23379','23389','23445','23446','23447','23448','23449','23455','23466','23477','23488','23499','23556','23557','23558','23559','23566','23577','23588','23599','23667','23668','23669','23677','23688','23699','23778','23779','23788','23799','23889','23899','24456','24457','24458','24459','24467','24468','24469','24478','24479','24489','24556','24557','24558','24559','24566','24577','24588','24599','24667','24668','24669','24677','24688','24699','24778','24779','24788','24799','24889','24899','25567','25568','25569','25578','25579','25589','25667','25668','25669','25677','25688','25699','25778','25779','25788','25799','25889','25899','26678','26679','26689','26778','26779','26788','26799','26889','26899','27789','27889','27899','33456','33457','33458','33459','33467','33468','33469','33478','33479','33489','33567','33568','33569','33578','33579','33589','33678','33679','33689','33789','34456','34457','34458','34459','34467','34468','34469','34478','34479','34489','34556','34557','34558','34559','34566','34577','34588','34599','34667','34668','34669','34677','34688','34699','34778','34779','34788','34799','34889','34899','35567','35568','35569','35578','35579','35589','35667','35668','35669','35677','35688','35699','35778','35779','35788','35799','35889','35899','36678','36679','36689','36778','36779','36788','36799','36889','36899','37789','37889','37899','44567','44568','44569','44578','44579','44589','44678','44679','44689','44789','45567','45568','45569','45578','45579','45589','45667','45668','45669','45677','45688','45699','45778','45779','45788','45799','45889','45899','46678','46679','46689','46778','46779','46788','46799','46889','46899','47789','47889','47899','55678','55679','55689','55789','56678','56679','56689','56778','56779','56788','56799','56889','56899','57789','57889','57899','66789','67789','67889','67899');
        $aALlCode[30]  = array('00112','00113','00114','00115','00116','00117','00118','00119','00122','00133','00144','00155','00166','00177','00188','00199','00223','00224','00225','00226','00227','00228','00229','00233','00244','00255','00266','00277','00288','00299','00334','00335','00336','00337','00338','00339','00344','00355','00366','00377','00388','00399','00445','00446','00447','00448','00449','00455','00466','00477','00488','00499','00556','00557','00558','00559','00566','00577','00588','00599','00667','00668','00669','00677','00688','00699','00778','00779','00788','00799','00889','00899','01122','01133','01144','01155','01166','01177','01188','01199','02233','02244','02255','02266','02277','02288','02299','03344','03355','03366','03377','03388','03399','04455','04466','04477','04488','04499','05566','05577','05588','05599','06677','06688','06699','07788','07799','08899','11223','11224','11225','11226','11227','11228','11229','11233','11244','11255','11266','11277','11288','11299','11334','11335','11336','11337','11338','11339','11344','11355','11366','11377','11388','11399','11445','11446','11447','11448','11449','11455','11466','11477','11488','11499','11556','11557','11558','11559','11566','11577','11588','11599','11667','11668','11669','11677','11688','11699','11778','11779','11788','11799','11889','11899','12233','12244','12255','12266','12277','12288','12299','13344','13355','13366','13377','13388','13399','14455','14466','14477','14488','14499','15566','15577','15588','15599','16677','16688','16699','17788','17799','18899','22334','22335','22336','22337','22338','22339','22344','22355','22366','22377','22388','22399','22445','22446','22447','22448','22449','22455','22466','22477','22488','22499','22556','22557','22558','22559','22566','22577','22588','22599','22667','22668','22669','22677','22688','22699','22778','22779','22788','22799','22889','22899','23344','23355','23366','23377','23388','23399','24455','24466','24477','24488','24499','25566','25577','25588','25599','26677','26688','26699','27788','27799','28899','33445','33446','33447','33448','33449','33455','33466','33477','33488','33499','33556','33557','33558','33559','33566','33577','33588','33599','33667','33668','33669','33677','33688','33699','33778','33779','33788','33799','33889','33899','34455','34466','34477','34488','34499','35566','35577','35588','35599','36677','36688','36699','37788','37799','38899','44556','44557','44558','44559','44566','44577','44588','44599','44667','44668','44669','44677','44688','44699','44778','44779','44788','44799','44889','44899','45566','45577','45588','45599','46677','46688','46699','47788','47799','48899','55667','55668','55669','55677','55688','55699','55778','55779','55788','55799','55889','55899','56677','56688','56699','57788','57799','58899','66778','66779','66788','66799','66889','66899','67788','67799','68899','77889','77899','78899');
        $aALlCode[24]  = array('0123','0124','0125','0126','0127','0128','0129','0134','0135','0136','0137','0138','0139','0145','0146','0147','0148','0149','0156','0157','0158','0159','0167','0168','0169','0178','0179','0189','0234','0235','0236','0237','0238','0239','0245','0246','0247','0248','0249','0256','0257','0258','0259','0267','0268','0269','0278','0279','0289','0345','0346','0347','0348','0349','0356','0357','0358','0359','0367','0368','0369','0378','0379','0389','0456','0457','0458','0459','0467','0468','0469','0478','0479','0489','0567','0568','0569','0578','0579','0589','0678','0679','0689','0789','1234','1235','1236','1237','1238','1239','1245','1246','1247','1248','1249','1256','1257','1258','1259','1267','1268','1269','1278','1279','1289','1345','1346','1347','1348','1349','1356','1357','1358','1359','1367','1368','1369','1378','1379','1389','1456','1457','1458','1459','1467','1468','1469','1478','1479','1489','1567','1568','1569','1578','1579','1589','1678','1679','1689','1789','2345','2346','2347','2348','2349','2356','2357','2358','2359','2367','2368','2369','2378','2379','2389','2456','2457','2458','2459','2467','2468','2469','2478','2479','2489','2567','2568','2569','2578','2579','2589','2678','2679','2689','2789','3456','3457','3458','3459','3467','3468','3469','3478','3479','3489','3567','3568','3569','3578','3579','3589','3678','3679','3689','3789','4567','4568','4569','4578','4579','4589','4678','4679','4689','4789','5678','5679','5689','5789','6789');
        $aALlCode[20]  = array('00012','00013','00014','00015','00016','00017','00018','00019','00023','00024','00025','00026','00027','00028','00029','00034','00035','00036','00037','00038','00039','00045','00046','00047','00048','00049','00056','00057','00058','00059','00067','00068','00069','00078','00079','00089','01112','01113','01114','01115','01116','01117','01118','01119','01222','01333','01444','01555','01666','01777','01888','01999','02223','02224','02225','02226','02227','02228','02229','02333','02444','02555','02666','02777','02888','02999','03334','03335','03336','03337','03338','03339','03444','03555','03666','03777','03888','03999','04445','04446','04447','04448','04449','04555','04666','04777','04888','04999','05556','05557','05558','05559','05666','05777','05888','05999','06667','06668','06669','06777','06888','06999','07778','07779','07888','07999','08889','08999','11123','11124','11125','11126','11127','11128','11129','11134','11135','11136','11137','11138','11139','11145','11146','11147','11148','11149','11156','11157','11158','11159','11167','11168','11169','11178','11179','11189','12223','12224','12225','12226','12227','12228','12229','12333','12444','12555','12666','12777','12888','12999','13334','13335','13336','13337','13338','13339','13444','13555','13666','13777','13888','13999','14445','14446','14447','14448','14449','14555','14666','14777','14888','14999','15556','15557','15558','15559','15666','15777','15888','15999','16667','16668','16669','16777','16888','16999','17778','17779','17888','17999','18889','18999','22234','22235','22236','22237','22238','22239','22245','22246','22247','22248','22249','22256','22257','22258','22259','22267','22268','22269','22278','22279','22289','23334','23335','23336','23337','23338','23339','23444','23555','23666','23777','23888','23999','24445','24446','24447','24448','24449','24555','24666','24777','24888','24999','25556','25557','25558','25559','25666','25777','25888','25999','26667','26668','26669','26777','26888','26999','27778','27779','27888','27999','28889','28999','33345','33346','33347','33348','33349','33356','33357','33358','33359','33367','33368','33369','33378','33379','33389','34445','34446','34447','34448','34449','34555','34666','34777','34888','34999','35556','35557','35558','35559','35666','35777','35888','35999','36667','36668','36669','36777','36888','36999','37778','37779','37888','37999','38889','38999','44456','44457','44458','44459','44467','44468','44469','44478','44479','44489','45556','45557','45558','45559','45666','45777','45888','45999','46667','46668','46669','46777','46888','46999','47778','47779','47888','47999','48889','48999','55567','55568','55569','55578','55579','55589','56667','56668','56669','56777','56888','56999','57778','57779','57888','57999','58889','58999','66678','66679','66689','67778','67779','67888','67999','68889','68999','77789','78889','78999');
        $aALlCode[12]  = array('0012','0013','0014','0015','0016','0017','0018','0019','0023','0024','0025','0026','0027','0028','0029','0034','0035','0036','0037','0038','0039','0045','0046','0047','0048','0049','0056','0057','0058','0059','0067','0068','0069','0078','0079','0089','0112','0113','0114','0115','0116','0117','0118','0119','0122','0133','0144','0155','0166','0177','0188','0199','0223','0224','0225','0226','0227','0228','0229','0233','0244','0255','0266','0277','0288','0299','0334','0335','0336','0337','0338','0339','0344','0355','0366','0377','0388','0399','0445','0446','0447','0448','0449','0455','0466','0477','0488','0499','0556','0557','0558','0559','0566','0577','0588','0599','0667','0668','0669','0677','0688','0699','0778','0779','0788','0799','0889','0899','1123','1124','1125','1126','1127','1128','1129','1134','1135','1136','1137','1138','1139','1145','1146','1147','1148','1149','1156','1157','1158','1159','1167','1168','1169','1178','1179','1189','1223','1224','1225','1226','1227','1228','1229','1233','1244','1255','1266','1277','1288','1299','1334','1335','1336','1337','1338','1339','1344','1355','1366','1377','1388','1399','1445','1446','1447','1448','1449','1455','1466','1477','1488','1499','1556','1557','1558','1559','1566','1577','1588','1599','1667','1668','1669','1677','1688','1699','1778','1779','1788','1799','1889','1899','2234','2235','2236','2237','2238','2239','2245','2246','2247','2248','2249','2256','2257','2258','2259','2267','2268','2269','2278','2279','2289','2334','2335','2336','2337','2338','2339','2344','2355','2366','2377','2388','2399','2445','2446','2447','2448','2449','2455','2466','2477','2488','2499','2556','2557','2558','2559','2566','2577','2588','2599','2667','2668','2669','2677','2688','2699','2778','2779','2788','2799','2889','2899','3345','3346','3347','3348','3349','3356','3357','3358','3359','3367','3368','3369','3378','3379','3389','3445','3446','3447','3448','3449','3455','3466','3477','3488','3499','3556','3557','3558','3559','3566','3577','3588','3599','3667','3668','3669','3677','3688','3699','3778','3779','3788','3799','3889','3899','4456','4457','4458','4459','4467','4468','4469','4478','4479','4489','4556','4557','4558','4559','4566','4577','4588','4599','4667','4668','4669','4677','4688','4699','4778','4779','4788','4799','4889','4899','5567','5568','5569','5578','5579','5589','5667','5668','5669','5677','5688','5699','5778','5779','5788','5799','5889','5899','6678','6679','6689','6778','6779','6788','6799','6889','6899','7789','7889','7899');
        $aALlCode[10]  = array('00011','00022','00033','00044','00055','00066','00077','00088','00099','00111','00222','00333','00444','00555','00666','00777','00888','00999','11122','11133','11144','11155','11166','11177','11188','11199','11222','11333','11444','11555','11666','11777','11888','11999','22233','22244','22255','22266','22277','22288','22299','22333','22444','22555','22666','22777','22888','22999','33344','33355','33366','33377','33388','33399','33444','33555','33666','33777','33888','33999','44455','44466','44477','44488','44499','44555','44666','44777','44888','44999','55566','55577','55588','55599','55666','55777','55888','55999','66677','66688','66699','66777','66888','66999','77788','77799','77888','77999','88899','88999');
        $aALlCode[6]   = array('0011','0022','0033','0044','0055','0066','0077','0088','0099','1122','1133','1144','1155','1166','1177','1188','1199','2233','2244','2255','2266','2277','2288','2299','3344','3355','3366','3377','3388','3399','4455','4466','4477','4488','4499','5566','5577','5588','5599','6677','6688','6699','7788','7799','8899');
        $aALlCode[5]   = array('00001','00002','00003','00004','00005','00006','00007','00008','00009','01111','02222','03333','04444','05555','06666','07777','08888','09999','11112','11113','11114','11115','11116','11117','11118','11119','12222','13333','14444','15555','16666','17777','18888','19999','22223','22224','22225','22226','22227','22228','22229','23333','24444','25555','26666','27777','28888','29999','33334','33335','33336','33337','33338','33339','34444','35555','36666','37777','38888','39999','44445','44446','44447','44448','44449','45555','46666','47777','48888','49999','55556','55557','55558','55559','56666','57777','58888','59999','66667','66668','66669','67777','68888','69999','77778','77779','78888','79999','88889','89999');
        $aALlCode[4]   = array('0001','0002','0003','0004','0005','0006','0007','0008','0009','0111','0222','0333','0444','0555','0666','0777','0888','0999','1112','1113','1114','1115','1116','1117','1118','1119','1222','1333','1444','1555','1666','1777','1888','1999','2223','2224','2225','2226','2227','2228','2229','2333','2444','2555','2666','2777','2888','2999','3334','3335','3336','3337','3338','3339','3444','3555','3666','3777','3888','3999','4445','4446','4447','4448','4449','4555','4666','4777','4888','4999','5556','5557','5558','5559','5666','5777','5888','5999','6667','6668','6669','6777','6888','6999','7778','7779','7888','7999','8889','8999');
        if( $iMethodId == 0 )
        {
            echo '没有指定玩法ID';
            return FALSE;
        }
        if(!isset($aNumberRule['specialvalue']) || !in_array($aNumberRule['specialvalue'],array(120,60,30,24,20,12,10,6,5,4)))
        {
            echo '参数不正确';
            return FALSE;
        }
        $aCurrentCode = $aALlCode[$aNumberRule['specialvalue']];
        $iMethodId = intval($iMethodId);
        $aValue = array();
        $aTempNumber = array();
        foreach ($aIssue as $aIssueDetail)
        {
            foreach( $aCurrentCode as $iCode)
            {
                $aCode = str_split($iCode);
                $iHzValue = array_sum($aCode);
                $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$iCode."','".$iHzValue."')";
            }
            $aSql[]= implode(",", $aValue);
            $aValue = array();
        }
        $bIsHaveSpecialValue = TRUE;
        $bIsHaveStamp = FALSE;
        $sSpecialValueDescription = '特征值:号码和值';
        return $this->initLockBase($sTableName, $aSql, $bIsHaveSpecialValue, $sSpecialValueDescription);
    }
    
    
     /**
     * 初始化数字型特殊号数据封锁表数据
     *
     * @param string $sTableName        封锁表名称
     * @param array  $aIssue            生成奖期
     * @param array  $aNumberRule       游戏号码规则
     * @param int    $iMethodId         玩法ID
     * @return boolean
     * @author mark
     * 
     */
    public function initNumberTypeSpecialLock( $sTableName = '', $aIssue = array(), $aNumberRule = array() ,$iMethodId = 0 )
    {
        if( $iMethodId == 0 )
        {
            echo '没有指定玩法ID';
            return FALSE;
        }
        $bIsHaveSpecialValue = FALSE;
        $sSpecialValueDescription = '特征值';
        if(!isset($aNumberRule['specialvalue']) || !is_numeric($aNumberRule['specialvalue']))
        {
            echo '没有指定号码:'.$aNumberRule['specialvalue'];
            return FALSE;
        }
        $iMethodId = intval($iMethodId);
        $aValue = array();
        foreach ($aIssue as $aIssueDetail)
        {
            for( $i=0; $i<$aNumberRule['specialvalue']; $i++ )
            {
                if( $bIsHaveSpecialValue )
                {
                    $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$i."','".$aNumberRule['specialvalue']."')";
                }
                else
                {
                    $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$i."')";
                }
            }
            $aSql[]= implode(",", $aValue);
            $aValue = array();
        }
        return $this->initLockBase($sTableName, $aSql, $bIsHaveSpecialValue, $sSpecialValueDescription);
    }
    
    
    /**
     * 初始化数字型三码大小单双数据封锁表数据
     * 0:大，1小，2单，3双
     *
     * @param string $sTableName        封锁表名称
     * @param array  $aIssue            生成奖期
     * @param array  $aNumberRule       游戏号码规则
     * @param int    $iMethodId         玩法ID 
     * @return boolean
     * @author mark
     * 
     */
    public function initNumberTypeShanDXDSLock( $sTableName = '', $aIssue = array(), $aNumberRule = array() ,$iMethodId = 0 )
    {
        if( $iMethodId == 0 )
        {
            echo '没有指定玩法ID';
            return FALSE;
        }
        if(!isset($aNumberRule['specialvalue']))
        {
            echo '参数不正确';
            return FALSE;
        }
        $iMethodId = intval($iMethodId);
        $aValue = array();
        foreach ($aIssue as $aIssueDetail)
        {
            for( $i=0; $i<4; $i++ )
            {
                for( $j=0; $j<4; $j++ )
                {
                    for( $k=0; $k<4; $k++ )
                    {
                        $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$i.$j.$k."','".$aNumberRule['specialvalue']."')";
                    }
                }
            }
            $aSql[]= implode(",", $aValue);
            $aValue = array();
        }
        $bIsHaveSpecialValue = TRUE;
        $sSpecialValueDescription = '特征值:区分前三与后三:1前三，2后三';
        return $this->initLockBase($sTableName, $aSql, $bIsHaveSpecialValue, $sSpecialValueDescription);
    }
    
    
    /**
     * 初始化数字型三星趣味封锁表数据
     *
     * @param string $sTableName        封锁表名称
     * @param array  $aIssue            生成奖期
     * @param array  $aNumberRule       游戏号码规则
     * @param int    $iMethodId         玩法ID
     * @return boolean
     * @author mark
     * 
     */
    public function initNumberTypeThreeQuWeiLock( $sTableName = '', $aIssue = array(), $aNumberRule = array() ,$iMethodId = 0 )
    {
        if( $iMethodId == 0 )
        {
            echo '没有指定玩法ID';
            return FALSE;
        }
        $iMethodId = intval($iMethodId);
        $bIsHaveSpecialValue = FALSE;
        $sSpecialValueDescription = '特征值';
        if(!isset($aNumberRule['specialvalue']) || !is_numeric($aNumberRule['specialvalue']))
        {
            echo '没有指定号码:'.$aNumberRule['specialvalue'];
            return FALSE;
        }
        $aValue = array();
        foreach ($aIssue as $aIssueDetail)
        {
            for( $i=0; $i<$aNumberRule['specialvalue']; $i++ )
            {
                for( $j=0; $j<10; $j++ )
                {
                    for( $k=0; $k<10; $k++ )
                    {
                        $iHzValue = $i+$j+$k;//号码特征值记录号码的和值
                        $aValue[] = "('".$aIssueDetail['issue']."','".$iMethodId."','".$i.$j.$k."','".$iHzValue."')";
                    }
                }
            }
            $aSql[]= implode(",", $aValue);
            $aValue = array();
        }
        $bIsHaveSpecialValue = TRUE;
        $sSpecialValueDescription = '特征值:号码和值';
        return $this->initLockBase($sTableName, $aSql, $bIsHaveSpecialValue, $sSpecialValueDescription);
    }
}