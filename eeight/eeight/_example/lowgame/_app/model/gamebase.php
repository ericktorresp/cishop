<?php
/**
 * 文件 : /_app/model/gamebase.php
 * 功能 : 数据模型 - 游戏基础模型
 * 
 * - playInsertData()           用户投单数据处理流程[非事务-非追号]内部调用
 * - cancelUpdateData()         用户撤单数据处理流程[非事务-非追号]内部调用
 * - getUpdateLocksConditions() 根据最后的号码扩展表[到数据库的数据]获取更新封锁表条件和值
 * - getExtendCodeHHZX()        混合组选拆分组三组六并转直选
 * - getStampCode()             根据组三、组六、二码不定位、前二组选、后二组选、的原复式展开得特征码012345
 * - getExtendCodePrizeTX()     获取通选每个号码的奖金值情况
 * - getExpandDXDS()            对大小单双进行特殊形式展开[展开为两位直选型的多单形式]
 * - getCodeSendPrize()         根据一个号码和原始号码以及奖金计算如果开奖号码是本号码，最终会得多少奖金即实际派发奖金
 * 
 * @author     james    090915
 * @version    1.2.0
 * @package    lowgame   
 */

abstract class model_gamebase extends basemodel
{
	/**
     * 私有变量定义[玩法ID和内容对应关系]
     * @var array
     */
    protected $_aMethod_Config  = array(      //玩法ID和内容对应关系[用于号码展开]
                                   9=>'ZX',        //3D直选
                                   10=>'ZXHZ',     //3D直选和值
                                   11=>'TX',       //3D通选
                                   12=>'ZS',       //3D组三
                                   13=>'ZL',       //3D组六
                                   14=>'HHZX',     //3D混合组选
                                   15=>'ZUXHZ',    //3D组选和值
                                   16=>'YMBDW',    //3D一码不定位
                                   17=>'EMBDW',    //3D二码不定位
                                   18=>'QEZX',     //3D前二直选
                                   19=>'HEZX',     //3D后二直选
                                   20=>'QEZUX',    //3D前二组选
                                   21=>'HEZUX',    //3D后二组选
                                   22=>'DBW',      //3D定百位
                                   23=>'DSW',      //3D定十位
                                   24=>'DGW',      //3D定个位
                                   26=>'QEDXDS',   //3D前二大小单双
                                   27=>'HEDXDS',   //3D后二大小单双
                                   37=>'ZX',       //P3直选
                                   38=>'ZXHZ',     //P3直选和值
                                   39=>'TX',       //P3通选
                                   40=>'ZS',       //P3组三
                                   41=>'ZL',       //P3组六
                                   42=>'HHZX',     //P3混合组选
                                   43=>'ZUXHZ',    //P3组选和值
                                   44=>'YMBDW',    //P3一码不定位
                                   45=>'EMBDW',    //P3二码不定位
                                   46=>'QEZX',     //P3前二直选
                                   47=>'HEZX',     //P3后二直选
                                   48=>'QEZUX',    //P3前二组选
                                   49=>'HEZUX',    //P3后二组选
                                   50=>'P5DWW',    //P5定万位
                                   51=>'P5DQW',    //P5定千位
                                   52=>'P5DBW',    //P5定百位
                                   53=>'P5DSW',    //P5定十位
                                   54=>'P5DGW',    //P5定个位
                                   55=>'QEDXDS',   //P3前二大小单双
                                   56=>'HEDXDS',   //P3后二大小单双
                                   57=>'P5HEDXDS', //P5后二大小单双
                                   58=>'P5HEZX',   //P5后二直选
                                   59=>'P5HEZUX'); //P5后二组选
    /**
     * 私有变量[大小单双对应号码]
     * @var array
     */                           
    protected $_aBSAD = array(    //大小单双对应号码
                             'B' => array(5,6,7,8,9),
                             'S' => array(0,1,2,3,4),
                             'A' => array(1,3,5,7,9),
                             'D' => array(0,2,4,6,8)
                       );
                       
    /**
     * 构造函数
     */
    function __construct( $aDBO=array() )
    {
        parent::__construct( $aDBO );
    }
    
    
    /**
     * 用户投单数据处理流程[非事务-非追号]内部调用
     *
     * @author  james    090812
     * @access  private
     * @param   array    $aLocksData    //封锁表数据[传入更新值的SQL语句数组]
     * @param   array    $aProjectData  //方案表数据[必须]
     * @param   array    $aJoinData     //加入游戏帐变[必须]
     * @param   array    $aBackData     //用户本人返点帐变数据
     * @param   array    $aExpandData   //号码扩展表数据[必须]
     * @param   array    $aDiffData     //用户返点差表数据 
     * @param   array    $aSaleData     //销量表数据[必须]
     * @return  mixed   小于0为错误，全等于TRUE为成功
     */
    protected function playInsertData( $aLocksData, $aProjectData, $aJoinData, $aBackData, 
                                       $aExpandData, $aDiffData, $aSaleData )
    {
        //00:必要参数判断
        if( empty($aProjectData) || !is_array($aProjectData) )
        {//方案表记录必须插入
            return 0;
        }
        if( empty($aJoinData) || !is_array($aJoinData) )
        {//加入游戏帐变必须写入
            return 0;
        }
        if( empty($aExpandData) || !is_array($aExpandData) )
        {//号码扩展必须写入
            return 0;
        }
        if( empty($aSaleData) || !is_array($aSaleData) )
        {//销量表必须写入
            return 0;
        }
        /* @var $oProjects model_projects */
        /* @var $oOrders model_orders */
        /* @var $oLocks model_locks */
        $oProjects = A::singleton('model_projects'); //方案模型
        $oOrders   = A::singleton('model_orders');   //帐变模型
        $oLocks    = A::singleton('model_locks');    //封锁模型
        
        //01: 写入封锁表[在要封锁的时候才执行]--------------------------------------
        if( !empty($aLocksData) && is_array($aLocksData) )
        {//执行传入的SQL数组
            foreach( $aLocksData as $v )
            {
                $this->oDB->query($v);
                if( $this->oDB->errno() > 0 )
                {//执行失败
                    return -1;
                }
                /*if( $this->oDB->ar() == 0 )
                {//没有找到号码[可能当期封锁表数据未生成]
                    return -11;
                }*/
            }
        }
        
        //02 ：写入方案表-----------------------------------------------------------
        $iProjectId = $oProjects->projectsInsert( $aProjectData );
        if( $iProjectId <= 0 )
        {//写入方案失败
            return -2;
        }
        
        //03：写加入游戏帐变以及用户资金扣钱-----------------------------------------
        $aJoinData['iProjectId'] = $iProjectId;
        $mResult = $oOrders->addOrders( $aJoinData );
        if( $mResult === -1009  )
        {//资金不够
            return -33;
        }
        elseif( $mResult !== TRUE )
        {//其他帐变错误
            return -3;
        }
        
        //04：写本人返点帐变以及加钱[在本人返点大于0的情况才执行]---------------------
        if( !empty($aBackData) && is_array($aBackData) )
        {
           $aBackData['iProjectId'] = $iProjectId;
           $mResult = $oOrders->addOrders( $aBackData );
           if( $mResult !== TRUE )
           {//帐变错误
               return -4;
           }
        }
        
        //05：写入号码扩展表--------------------------------------------------------
        foreach( $aExpandData as &$vv )
        {
            $vv['projectid'] = $iProjectId;
        }
        if(  TRUE !== $oProjects->expandCodeInsert( $aExpandData ) )
        {//写入号码扩展失败
             return -5;
        }
        
        //06：写入返点差表[有数据则写入]---------------------------------------------
        if( !empty($aDiffData) && is_array($aDiffData) )
        {
            foreach( $aDiffData as &$v )
            {
                $v['projectid'] = $iProjectId;
            }
            if(  TRUE !== $oProjects->userDiffPointInsert( $aDiffData ) )
            {//写入返点失败
                return -6;
            }
        }
        
        //07：写入销量表------------------------------------------------------------
        if( TRUE !== $oLocks->salesUpdate( $aSaleData ) )
        {//写入销量表失败
            return -7;
        }
        
        //08：完成[返回TRUE]--------------------------------------------------------
        return TRUE;
    }
    
    
    /**
     * 用户撤单数据处理流程[非事务-非追号]内部调用
     *
     * @author  james    090827
     * @access  protected
     * --------------------------------------------------------
     * @param   array    $aLocksData     //封锁表更新
     * @param   array    $aSaleData      //销量表更新
     * @param   array    $aOrdersData    //帐变
     * @param   array    $aStatusData    //状态更新
     * @return  mixed   小于0为错误，全等于TRUE为成功
     */
    protected function cancelUpdateData( $aSaleData, $aOrdersData, $aStatusData, $aLocksData=array()  )
    {
        //00:必要参数判断
        if( empty($aOrdersData) || !is_array($aOrdersData) )
        {//资金、帐变数据 
            return 0;
        }
        if( empty($aOrdersData['fk']) || !is_array($aOrdersData['fk']) )
        {//撤单返款帐变必须有
        	return 0;
        }
        if( empty($aStatusData) || !is_array($aStatusData) )
        {//更改状态数据
            return 0;
        }
        /* @var $oOrders model_orders */
        /* @var $oLocks model_locks */
        $oOrders   = A::singleton('model_orders');   //帐变模型
        $oLocks    = A::singleton('model_locks');    //封锁模型
        
        //01: 写入封锁表[在要封锁的时候才执行]--------------------------------------
        if( !empty($aLocksData) && is_array($aLocksData) )
        {//执行传入的SQL数组
        	$iTempCount = 0;
            foreach( $aLocksData as $v )
            {
                $this->oDB->query($v);
                if( $this->oDB->errno() > 0 )
                {//执行失败
                    return -1;
                }
                $iTempCount += $this->oDB->ar();
            }
            if( $iTempCount == 0 )
            {//没有数据更新
            	return -1;
            }
        }
        
        //02：写入销量表[循环写入]---------------------------------------------------------
        if( !empty($aSaleData) && is_array($aSaleData) )
        {
	        foreach( $aSaleData as $v )
	        {
	            if( TRUE !== $oLocks->salesUpdate( $v ) )
	            {//写入销量表失败
	                return -2;
	            }
	        }
        }
        
        //03: 更改资金，写帐变数据------------------------------------------------
        foreach( $aOrdersData as $v )
        {
            $mResult = $oOrders->addOrders( $v );
	        if( $mResult === -1009  )
	        {//资金不够
	            return -33;
	        }
	        elseif( $mResult !== TRUE )
	        {//其他帐变错误
	            return -3;
	        }
        }
        
        //04: 更改状态-------------------------------------------------------------
        foreach( $aStatusData as $v )
        {
        	if( empty($v['sql']) )
        	{//执行的SQL语句[必须]
        		return -4;
        	}
            $this->oDB->query($v['sql']);
            if( isset($v['affected']) && intval($v['affected']) > 0 )
            {//指定必须影响的行数
	            if( $this->oDB->errno() > 0 || $this->oDB->ar() != intval($v['affected']) )
	            {//执行失败
	                return -4;
	            }
            }
            else
            {//没有指定则只要影响行数不为0即为成功
                if( $this->oDB->errno() > 0 || $this->oDB->ar() == 0 )
                {//执行失败
                    return -4;
                }
            }
        }
        
        //05：完成[返回TRUE]--------------------------------------------------------
        return TRUE;
    }
    
    
    /**
     * 根据最后的号码扩展表[到数据库的数据]获取更新封锁表条件和值
     *
     * @author  james   090827
     * @access  protected
     * @param   int         $iMethodId
     * @param   array       $aExpandData
     * @return  array
     */
    protected function getUpdateLocksConditions( $iMethodId, $aExpandData )
    {
        $aResult = array();
        if( empty($iMethodId) || !isset($this->_aMethod_Config[$iMethodId]) )
        {
            return $aResult;
        }
        $sMethod = $this->_aMethod_Config[$iMethodId];
        if( empty($aExpandData) || !is_array($aExpandData) )
        {
            return $aResult;
        }
        //把所有变价的号码列举出来
        $aAdjustNum    = array(); //所有变价号码
        $aPrizes       = array(); //奖金情况[用于通选特殊计算]
        $sAllNums      = "";      //购买的原式号码
        foreach( $aExpandData as $v )
        {
            if( $v['isspecial'] == 1 )
            {
                $aAdjustNum[]           = $v['expandcode'];
            }
            else 
            {
            	$aPrizes[$v['level']]   = $v['prize'];
                $sAllNums               = $v['expandcode']; 
            }
        }
        //普通的都要除去变价的号码
        $aAdjustNum = explode("|",implode("|",$aAdjustNum));
        $aAdjustNum = array_unique( $aAdjustNum );
        $aAdjustNum = implode(",",$aAdjustNum);
        $sOutCode   = empty($aAdjustNum) ? "" : " AND `code` NOT IN(".$aAdjustNum.") ";
        foreach( $aExpandData as $v )
        {
            if( $v['isspecial'] == 1 )
            {//是变价的号码[号码是全展开的]
            	if( $sMethod != 'TX' )
            	{//通选不变价
            		if( $sMethod == 'YMBDW' || $sMethod == 'EMBDW' )
            		{//一码不定位和二码不定位要计算中几单的情况
            			$fPrize  = array();
            			$aNumArr = explode("|", $v['expandcode']);
            			foreach( $aNumArr as $vv )
            			{
            				$fLastPrize = $this->getCodeSendPrize( $iMethodId, $sAllNums, $vv, array(1=>$v['prize']) );
            				$fPrize["".$fLastPrize][] = $vv; 
            			}
            			foreach( $fPrize as $kk=>$vv )
            			{
            				$aResult[] = array( 'prizes'    => floatval($kk),
                                                'condition' => " `code` IN(".implode(",", $vv).") "
                                   );
            			}
            		}
            		else
            		{
            			$aResult[] = array( 'prizes'    => $v['prize'],
                                            'condition' => " `code` IN(".str_replace("|",",",$v['expandcode']).") "
                                   );
            		}
            	}
            	else
            	{
            		$aResult = array(); 
            		return $aResult;
            	}
            }
            else
            {
                $sTmpStr   = "";    //条件
                $iTimes    = 1;     //中单数量[中多单时不为1，其他都为1]
                switch( $sMethod )
                {
                    case 'ZX'      : //同下
                    case 'ZXHZ'    : $sTmpStr = " `code` IN(" . str_replace("|", ",", $v['expandcode']) . ") ";
                                     break;
                    case 'TX'      : //只计算一次，算出每个号码的奖金情况
                    	             $aNumArr       = explode( "|", $v['expandcode'] );
                    	             $iTimes        = count($aNumArr);
                                     $aUniqueNumArr = array_unique($aNumArr);
                                     if( count($aUniqueNumArr) > 1 && count($aUniqueNumArr) < 1000 )
                                     {//如果购买号码超过
                                        $aTempPrize = $this->getExtendCodePrizeTX( $aNumArr, $aPrizes );
                                        foreach( $aTempPrize as $p=>$aNum )
                                        {
                                        	$aResult[] = array( 'prizes' => floatval($p), 
                                                       'condition' => " `code` IN(".implode(",",$aNum).") ".$sOutCode );
                                        }
	                                     break 2;
                                     }
                                     elseif( count($aUniqueNumArr) >= 1000 )
                                     {//全包
                                     	$aResult[] = array( 'prizes' => $aPrizes[1] + $aPrizes[2]*27 + $aPrizes[3]*243, 
                                                       'condition' => " 1 ".$sOutCode );
                                     	break 2;
                                     }
                                     else 
                                     {//只购买一注的特殊计算，提高效率
                                     	$sTempNum = $aUniqueNumArr[0];
                                        if( $v['level'] == 1 )
                                        {//更新一等奖的
                                           $sTmpStr = " `code`='".$sTempNum."' ";
                                        }
                                        
                                        elseif( $v['level'] == 2 )
                                        {//更新二等奖的号码
                                           $sTmpStr = " `code` REGEXP '(^".$sTempNum[0].$sTempNum[1].")|".
                                                                      "(^".$sTempNum[0]."[0-9]".$sTempNum[2]."$)|".
                                                                      "(".$sTempNum[1].$sTempNum[2]."$)'
                                                         AND `code`!='".$sTempNum."' ";
                                        }
                                        else
                                        {//更新三等奖的号码
                                           $sTmpStr = " `code` REGEXP '(^".$sTempNum[0].")|".
                                                                       "(^[0-9]".$sTempNum[1].")|".
                                                                       "(".$sTempNum[2]."$)'
                                                AND `code` NOT REGEXP '(^".$sTempNum[0].$sTempNum[1].")|".
                                                                      "(^".$sTempNum[0]."[0-9]".$sTempNum[2]."$)|".
                                                                      "(".$sTempNum[1].$sTempNum[2]."$)' ";
                                        }
                                     }
                                     break;
                                     
                    case 'ZS'      : $aNumArr = $this->getStampCode( 'ZS', $v['expandcode'] );//特征值+特征码判断
                                     $sTmpStr = " `stamp`='1' AND `stampvalue` REGEXP '(".implode("|",$aNumArr).")'";
                                     break;
                                     
                    case 'ZL'      : $aNumArr = $this->getStampCode( 'ZL', $v['expandcode'] );//特征值+特征码判断
                                     $sTmpStr = " `stamp`='2' AND `stampvalue` REGEXP '(".implode("|",$aNumArr).")'";
                                     break;
                    case 'HHZX'    : $aNumArr = $this->getExtendCodeHHZX( $v['expandcode'] );//转直选
                                     //一等奖组三，二等奖组六
                                     $aNumArr = $v['level'] == 1 ? $aNumArr['ZS'] : $aNumArr['ZL'];
                                     if( !empty($aNumArr) )
                                     {
                                     	$sTmpStr = " `code` IN(".implode(",",$aNumArr).") ";
                                     }
                                     break;
                                     
                    case 'ZUXHZ'   : $TempStamp = $v['level'] == 1 ? 1 : 2;//一等奖组三，二等奖组六
                    	             $sTmpStr = " `stamp`='".$TempStamp."' AND 
                    	                           `addvalue` REGEXP '^(".$v['expandcode'].")$' ";
                                     break;
                                     
                    case 'YMBDW'   : $aNumArr = explode( "|",$v['expandcode'] );
                                     foreach( $aNumArr as $num )
                                     {
                                     	$aResult[] = array( 'prizes' => $v['prize'], 
                                                            'condition' => " `code` REGEXP '[".$num."]' ".$sOutCode );
                                     }
                                     continue;
                                     break;
                                     
                    case 'EMBDW'   : $aNumArr = $this->getStampCode( 'EMBDW', $v['expandcode'] );//特征码判断
                                     foreach( $aNumArr as $num )
                                     {
                                        $aResult[] = array( 'prizes' => $v['prize'], 
                                                            'condition' => " `m2value` REGEXP '(".$num.")' ".$sOutCode );
                                     }
                                     continue;
                                     break;
                                     
                    case 'QEZX'    : $aNumArr = explode( "|",$v['expandcode'] );
                                     $sTmpStr = " `code` REGEXP '^[".$aNumArr[0]."][".$aNumArr[1]."]' ";
                                     break;
                                     
                    case 'HEZX'    : $aNumArr = explode( "|",$v['expandcode'] );
                                     $sTmpStr = " `code` REGEXP '[".$aNumArr[0]."][".$aNumArr[1]."]$' ";
                                     break;
                                     
                    case 'QEZUX'   : $aNumArr = $this->getStampCode( 'QEZUX', $v['expandcode'] );//特征码判断
                                     $sTmpStr = " `q2value` REGEXP '(".implode("|",$aNumArr).")' ";
                                     break;
                                     
                    case 'HEZUX'   : $aNumArr = $this->getStampCode( 'HEZUX', $v['expandcode'] );//特征码判断
                                     $sTmpStr = " `h2value` REGEXP '(".implode("|",$aNumArr).")' ";
                                     break;
                                     
                    case 'P5HEZX'  : $aNumArr = explode( "|",$v['expandcode'] );
                                     $sTmpStr = " `code` REGEXP '^[".$aNumArr[0]."][".$aNumArr[1]."]' ";
                                     break;
                                     
                    case 'P5HEZUX' : $aNumArr = $this->getStampCode( 'P5HEZUX', $v['expandcode'] );//特征码判断
                                     $sTmpStr = " `h2value` REGEXP '(".implode("|",$aNumArr).")' ";
                                     break;
                                     
                    case 'DBW'     : $aNumArr = explode( "|",$v['expandcode'] );
                                     $sTmpStr = " `code` REGEXP '^[".implode("",$aNumArr)."]' ";
                                     break;
                                     
                    case 'DSW'     : $aNumArr = explode( "|",$v['expandcode'] );
                                     $sTmpStr = " `code` REGEXP '^[0-9][".implode("",$aNumArr)."]' ";
                                     break;
                                     
                    case 'DGW'     : $aNumArr = explode( "|",$v['expandcode'] );
                                     $sTmpStr = " `code` REGEXP '[".implode("",$aNumArr)."]$' ";
                                     break;
                                     
                    case 'P5DWW'   : $aNumArr = explode( "|",$v['expandcode'] );
                                     $sTmpStr = " `code` REGEXP '^[".implode("",$aNumArr)."]' ";
                                     break;
                                     
                    case 'P5DQW'   : $aNumArr = explode( "|",$v['expandcode'] );
                                     $sTmpStr = " `code` REGEXP '^[0-9][".implode("",$aNumArr)."]' ";
                                     break;
                                     
                    case 'P5DBW'   : $aNumArr = explode( "|",$v['expandcode'] );
                                     $sTmpStr = " `code` REGEXP '[".implode("",$aNumArr)."]$' ";
                                     break;
                                     
                    case 'P5DSW'   : $aNumArr = explode( "|",$v['expandcode'] );
                                     $sTmpStr = " `code` REGEXP '^[".implode("",$aNumArr)."]' ";
                                     break;
                                     
                    case 'P5DGW'   : $aNumArr = explode( "|",$v['expandcode'] );
                                     $sTmpStr = " `code` REGEXP '[".implode("",$aNumArr)."]$' ";
                                     break;
                                     
                    case 'QEDXDS'  : //同下
                    case 'P5HEDXDS': //
                    case 'HEDXDS'  : $aNumArr = explode("|", $v['expandcode']);
                                     $aTemp_Seach = array();
                                     if( count($aNumArr) > 1 )
                                     {//多于一组 
                                        foreach( $aNumArr as $order )
                                        {//每一组相当于一单
                                            $aTemp_nums = explode("#",$order);
                                            $aTemp_Seach[] = "([".$aTemp_nums[0]."][".$aTemp_nums[1]."])";
                                        }
                                     }
                                     else 
                                     {//只有一组
                                        $aTemp_nums    = explode("#",$aNumArr[0]);
                                        $aTemp_Seach[] = "[".$aTemp_nums[0]."][".$aTemp_nums[1]."]";
                                     }
                                     $sTmpStr = count($aTemp_Seach) > 1 ? "(".implode("|",$aTemp_Seach).")"
                                                    : $aTemp_Seach[0];
                                     if( $sMethod == "HEDXDS" )
                                     {//后二取后两位
                                        $sTmpStr = " `code` REGEXP '".$sTmpStr."$'";
                                     }
                                     else
                                     {//取前两位
                                        $sTmpStr = " `code` REGEXP '^".$sTmpStr."'";
                                     }
                                     break;
                    default        : $aResult = array(); return $aResult; break;
                }
                if( !empty($sTmpStr) )
                {
                	$aResult[] = array( 'prizes' => $v['prize'] * $iTimes , 'condition' => $sTmpStr.$sOutCode );
                }
            }
        }
        return $aResult;
    }
    
    //混合组选拆分组三组六并转直选
    protected function & getExtendCodeHHZX( $sNums )
    {
        $aResult = array();
        if( $sNums == "" )
        {
            return $aResult;
        }
        $aNumArr = explode( "|",$sNums );
        $aZSArr  = array(); //组三数据
        $aZLArr  = array(); //组六数据
        foreach( $aNumArr as $sNum )
        {
            if( strlen($sNum) != 3 )
            {
                return $aResult;
            }
            $i = intval($sNum[0]);
            $j = intval($sNum[1]);
            $k = intval($sNum[2]);
            if( $i == $j && $j == $k )
            {//如果为豹子号则直接返回
                return $aResult;
            }
            elseif( $i==$j || $i==$k || $j==$k )
            {//组三
                if( $j==$k )
                {
                    $k = $i;
                    $i = $j;
                }
                elseif( $i==$k )
                {
                    $k = $j;
                }
                $aZSArr[] = $i.$i.$k;
                $aZSArr[] = $i.$k.$i;
                $aZSArr[] = $k.$i.$i;
            }
            else 
            {//组六
                $aZLArr[] = $i.$j.$k;
                $aZLArr[] = $i.$k.$j;
                $aZLArr[] = $j.$i.$k;
                $aZLArr[] = $j.$k.$i;
                $aZLArr[] = $k.$i.$j;
                $aZLArr[] = $k.$j.$i;
            }
        }
        sort($aZSArr);
        sort($aZLArr);
        $aResult = array( 'ZS'=>$aZSArr, 'ZL'=>$aZLArr );
        return $aResult;
    }
    
    //根据组三、组六、二码不定位、前二组选、后二组选、的原复式展开得特征码012345
    protected function & getStampCode( $sMethod, $sNum )
    {
        $aResult = array();
        if( empty($sMethod) )
        {
            return $aResult;
        }
        if( empty($sNum) )
        {
            return $aResult;
        }
        switch( $sMethod )
        {
            case 'QEZUX' ://同下
            case 'HEZUX' ://同下
            case 'EMBDW' ://同下
            case 'P5HEZUX' ://同下
            case 'ZS' : $iTempLen  = strlen($sNum);
                        if( $iTempLen < 2 )
                        {//最少两个号码
                            return $aResult;
                        }
                        for( $i=0; $i<$iTempLen-1; $i++ )
                        {
                            for( $j=$i+1; $j<$iTempLen; $j++ )
                            {
                                $aTempArr = array($sNum[$i],$sNum[$j]);
                                sort($aTempArr);//排序
                                $aResult[] = $aTempArr[0].$aTempArr[1];
                            }
                        }
                        break;
            case 'ZL' : $iTempLen  = strlen($sNum);
                        if( $iTempLen < 3 )
                        {//最少三个号码
                            return $aResult;
                        }
                        for( $i=0; $i<$iTempLen-2; $i++ )
                        {
                            for( $j=$i+1; $j<$iTempLen-1; $j++ )
                            {
                                for( $k=$j+1; $k<$iTempLen; $k++ )
                                {
                                    $aTempArr = array($sNum[$i],$sNum[$j],$sNum[$k]);
                                    sort($aTempArr);
                                    $aResult[] = $aTempArr[0].$aTempArr[1].$aTempArr[2];
                                }
                            }
                        }
                        break;
            default   : break;
        }
        return $aResult;
    }

    /**
     * 获取通选每个号码的奖金值情况
     *
     * @param   array $aNums    //购买号码数组
     * @param   array $aPrize   //奖金组信息
     * @return  array           //奖金对应号码组
     */
    protected function & getExtendCodePrizeTX( $aNums, $aPrize )
    {
    	$aResult = array();
		require('basetxcode.php');
    	$aTempResult = array();
    	foreach( $aNums as $v )
    	{
    		$aSecond         = array();
    		$aThird          = array();
    		$aTempResult[$v] = isset($aTempResult[$v]) ? ($aTempResult[$v]+$aPrize[1]) : $aPrize[1];
    		//二等奖(12*,1*3,*23)
    		//preg_match_all( "/(".$v[0].$v[1]."\d)|(".$v[0]."\d".$v[2].")|(\d".$v[1].$v[2].")/", $sAllNum, $aResult );
    		//$aSecond = $aResult[0];
			$aSecond = $aBaseTXcode[$v][2];
    		foreach( $aSecond as $num )
    		{
    			if( $num != $v )
    			{//除去一等奖的
    				$aTempResult[$num] = isset($aTempResult[$num]) ? ($aTempResult[$num]+$aPrize[2]) : $aPrize[2];
    			}
    		}
    		//三等奖(1**,*2*,**3)
    		//preg_match_all( "/(".$v[0]."(\d){2})|(\d".$v[1]."\d)|((\d){2}".$v[2].")/", $sAllNum, $aResult );
    		//$aThird = array_diff( $aResult[0], $aSecond ); //三等奖，除去二等奖和一等奖的号码
			$aThird = $aBaseTXcode[$v][3];
    	    foreach( $aThird as $num )
            {
                $aTempResult[$num] = isset($aTempResult[$num]) ? ($aTempResult[$num]+$aPrize[3]) : $aPrize[3];
            }
    	}
    	$aResult   = array();
    	foreach( $aTempResult as $sCode=>$fPrize )
    	{
    		$aResult["".$fPrize][] = $sCode;
    	}
    	return $aResult;
    }
    
    //对大小单双进行特殊形式展开[展开为两位直选型的多单形式]
    protected function & getExpandDXDS( $sNum )
    {
        $aNumArr = explode( "|", $sNum );
        $aFisrt  = array(); //买的号中第一位对应的号码
        $aSecond = array(); //买的号中第二位对应的号码
        $aFisrtDiff        = array();   //第一位中没有重复的号码
        $aFisrtIntersect   = array();   //第一位中重复的号码
        $aSecondDiff       = array();   //第二位中没有重复的号码
        $aSecondIntersect  = array();   //第二位中重复的号码
        for( $i=0; $i<strlen($aNumArr[0]); $i++ )
        {
           $aFisrt = array_merge($aFisrt, $this->_aBSAD[$aNumArr[0][$i]]);
        }
        for( $i=0; $i<strlen($aNumArr[1]); $i++ )
        {
           $aSecond = array_merge($aSecond, $this->_aBSAD[$aNumArr[1][$i]]);
        }
        $aFisrt  = array_count_values($aFisrt);
        $aSecond = array_count_values($aSecond);
        foreach( $aFisrt as $k=>$v )
        {
            if( $v > 1 )
            {//如果重复次数大于1[实际上最多为2] 则为重复的
                $aFisrtIntersect[] = $k;//交集
            }
            else 
            {//非重复的
                $aFisrtDiff[] = $k;//差集
            }
        }
        foreach( $aSecond as $k=>$v )
        {
            if( $v > 1 )
            {//如果重复次数大于1[实际上最多为2] 则为重复的
                $aSecondIntersect[] = $k;//交集
            }
            else 
            {//非重复的
                $aSecondDiff[] = $k;//差集
            }
        }
        unset( $aFisrt, $aSecond, $sNum );
        $aResult = array();
        if( !empty($aFisrtIntersect) && !empty($aSecondIntersect) )
        {//如果两个都存在交集
               $aResult[4][] = implode("", $aFisrtIntersect) . "#" . implode("", $aSecondIntersect); //4倍的号码
               if( !empty($aFisrtDiff) && !empty($aSecondDiff) )
               {//如果两个差集都有值
                    $aResult[2][] = implode("", $aFisrtIntersect)."#".implode("", $aSecondDiff); //2倍的号码
                    $aResult[2][] = implode("", $aFisrtDiff)."#".implode("", $aSecondIntersect); //2倍的号码
                    $aResult[1][] = implode("", $aFisrtDiff)."#".implode("", $aSecondDiff); //1倍的号码
               }
               elseif( !empty($aFisrtDiff) )
               {//只存在第一位差集
                    $aResult[2][] = implode("", $aFisrtDiff)."#".implode("", $aSecondIntersect); //2倍的号码
               }
               elseif( !empty($aSecondDiff) )
               {//只存在第二位的差集
                    $aResult[2][] = implode("", $aFisrtIntersect)."#".implode("", $aSecondDiff); //2倍的号码
               }
               else 
               {//不存在差集
                    
               }
        }
        elseif( !empty($aFisrtIntersect) )
        {//如果只存在第一位交集[第二位必为只有差集]
               $aResult[2][] = implode("", $aFisrtIntersect)."#".implode("", $aSecondDiff); //2倍的号码
            if( !empty($aFisrtDiff) )
            {//如果第一位还存在差集
                 $aResult[1][] = implode("", $aFisrtDiff)."#".implode("", $aSecondDiff); //1倍的号码
            }
        }
        elseif( !empty($aSecondIntersect) )
        {//如果只存在第二位交集[第一位必为只有差集]
            $aResult[2][] = implode("", $aFisrtDiff)."#".implode("", $aSecondIntersect); //2倍的号码
            if( !empty($aSecondDiff) )
            {//如果第二位还存在差集
                 $aResult[1][] = implode("", $aFisrtDiff)."#".implode("", $aSecondDiff); //1倍的号码
            }
        }
        else 
        {//没有交集存在，则只有差集
               $aResult[1][] = implode("", $aFisrtDiff)."#".implode("", $aSecondDiff); //1倍的号码
        }
        return $aResult;
    }
    
    /**
     * 根据一个号码和原始号码以及奖金计算如果开奖号码是本号码，最终会得多少奖金即实际派发奖金
     *
     * @author  james   090902
     * @access  protected
     * @param   int      $iMethod   //玩法ID
     * @param   string   $sNums     //原始购买号码，大小单双为转换后的值[123#123]形式
     * @param   string   $sCode     //如果开奖的号码
     * @param   array    $aPrizes   //最后奖金组情况 $aPrizes[1]一等奖奖金，$aPrizes[2]二等奖奖金, $aPrizes[3]三等奖奖金
     * @return  int //失败返回FLASE,成功返回倍数
     */
    protected function getCodeSendPrize( $iMethodId, $sNums, $sCode, $aPrizes=array() )
    {
    	//01: 数据简单检查
        if( empty($iMethodId) || !isset($this->_aMethod_Config[$iMethodId]) )
        {
            return FALSE;
        }
        $sMethod = $this->_aMethod_Config[$iMethodId];
        if( $sNums == "" || empty($sCode) || !is_array($aPrizes) || empty($aPrizes[1]) )
        {
        	return FALSE;
        }
        $aTemp = array();//匹配数组
        switch( $sMethod )
        {
            case 'TX'      : //一等奖只会中一个一等奖，但是可能会中多个二等奖，二等奖多单，三等奖多单
            	             //1: 123 
            	             //2: 12* 1*3 *23
            	             //3: 1** *2* **3
            	             if( empty($aPrizes[2]) || empty($aPrizes[3]) )
            	             {
            	             	return FALSE;
            	             }
            	             $aTempNums = explode( "|", $sNums );
            	             $aTempNums = array_unique($aTempNums);
            	             $sNums     = implode( "|", $aTempNums );
            	             unset($aTempNums);
            	             $sPartn1 = "/".$sCode."/";
            	             $sPartn2 = "/(".$sCode[0].$sCode[1]."[0-9])|(".$sCode[0]."[0-9]".$sCode[2].")|
                                            ([0-9]".$sCode[1].$sCode[2].")/";
            	             $sPartn3 = "/(".$sCode[0]."[0-9][0-9])|([0-9]".$sCode[1]."[0-9])|
                                            ([0-9][0-9]".$sCode[2].")/";
            	             $iTimes1 = preg_match_all( $sPartn1, $sNums, $aTemp );
            	             $iTimes2 = preg_match_all( $sPartn2, $sNums, $aTemp );
            	             $iTimes3 = preg_match_all( $sPartn3, $sNums, $aTemp );
            	             $fResult = ($aPrizes[1] * $iTimes1) + ($aPrizes[2] * ($iTimes2 - $iTimes1))
            	                        + ($aPrizes[3] * ($iTimes3 - $iTimes2));
                             break;
            case 'YMBDW'   : //如果购买号码1个只会中一单，2个可能会中两单，3个以上可能会中三单
            	             $iTimes = preg_match_all( "/[".$sCode."]/", $sNums, $aTemp );
            	             $fResult = $aPrizes[1] * $iTimes; 
            	             break;
            case 'EMBDW'   : //如果购买号码2个只会中一单，3个以上可能会中三单
            	             $iTimes = preg_match_all( "/[".$sCode."]/", $sNums, $aTemp );
            	             if( $iTimes == 1 )
            	             {
            	             	$iTimes = 0;
            	             }
            	             elseif( $iTimes == 2 )
            	             {
            	             	$iTimes = 1;
            	             }
            	             if( $iTimes <= 0 )
            	             {
            	             	return FALSE;
            	             }
            	             $fResult = $aPrizes[1] * $iTimes;
            	             break;
            default        : $fResult = $aPrizes[1]; break;//默认都是中一单，即一倍
        }
        return $fResult;
    }
}
?>