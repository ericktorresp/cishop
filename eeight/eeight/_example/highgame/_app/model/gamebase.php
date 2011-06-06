<?php
/**
 * 文件 : /_app/model/gamebase.php
 * 功能 : 数据模型 - 游戏基础模型
 * 
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
    protected $_aMethod_Config  = array();
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
    protected $_aDSDX = array( '大'=>0,'小'=>1,'单'=>2,'双'=>3 );
    
    protected $_SDRXB = array('01 02 03 04 05','01 02 03 04 06','01 02 03 04 07','01 02 03 04 08','01 02 03 04 09',
                              '01 02 03 04 10','01 02 03 04 11','01 02 03 05 06','01 02 03 05 07','01 02 03 05 08',
                              '01 02 03 05 09','01 02 03 05 10','01 02 03 05 11','01 02 03 06 07','01 02 03 06 08',
                              '01 02 03 06 09','01 02 03 06 10','01 02 03 06 11','01 02 03 07 08','01 02 03 07 09',
                              '01 02 03 07 10','01 02 03 07 11','01 02 03 08 09','01 02 03 08 10','01 02 03 08 11',
                              '01 02 03 09 10','01 02 03 09 11','01 02 03 10 11','01 02 04 05 06','01 02 04 05 07',
                              '01 02 04 05 08','01 02 04 05 09','01 02 04 05 10','01 02 04 05 11','01 02 04 06 07',
                              '01 02 04 06 08','01 02 04 06 09','01 02 04 06 10','01 02 04 06 11','01 02 04 07 08',
                              '01 02 04 07 09','01 02 04 07 10','01 02 04 07 11','01 02 04 08 09','01 02 04 08 10',
                              '01 02 04 08 11','01 02 04 09 10','01 02 04 09 11','01 02 04 10 11','01 02 05 06 07',
                              '01 02 05 06 08','01 02 05 06 09','01 02 05 06 10','01 02 05 06 11','01 02 05 07 08',
                              '01 02 05 07 09','01 02 05 07 10','01 02 05 07 11','01 02 05 08 09','01 02 05 08 10',
                              '01 02 05 08 11','01 02 05 09 10','01 02 05 09 11','01 02 05 10 11','01 02 06 07 08',
                              '01 02 06 07 09','01 02 06 07 10','01 02 06 07 11','01 02 06 08 09','01 02 06 08 10',
                              '01 02 06 08 11','01 02 06 09 10','01 02 06 09 11','01 02 06 10 11','01 02 07 08 09',
                              '01 02 07 08 10','01 02 07 08 11','01 02 07 09 10','01 02 07 09 11','01 02 07 10 11',
                              '01 02 08 09 10','01 02 08 09 11','01 02 08 10 11','01 02 09 10 11','01 03 04 05 06',
                              '01 03 04 05 07','01 03 04 05 08','01 03 04 05 09','01 03 04 05 10','01 03 04 05 11',
                              '01 03 04 06 07','01 03 04 06 08','01 03 04 06 09','01 03 04 06 10','01 03 04 06 11',
                              '01 03 04 07 08','01 03 04 07 09','01 03 04 07 10','01 03 04 07 11','01 03 04 08 09',
                              '01 03 04 08 10','01 03 04 08 11','01 03 04 09 10','01 03 04 09 11','01 03 04 10 11',
                              '01 03 05 06 07','01 03 05 06 08','01 03 05 06 09','01 03 05 06 10','01 03 05 06 11',
                              '01 03 05 07 08','01 03 05 07 09','01 03 05 07 10','01 03 05 07 11','01 03 05 08 09',
                              '01 03 05 08 10','01 03 05 08 11','01 03 05 09 10','01 03 05 09 11','01 03 05 10 11',
                              '01 03 06 07 08','01 03 06 07 09','01 03 06 07 10','01 03 06 07 11','01 03 06 08 09',
                              '01 03 06 08 10','01 03 06 08 11','01 03 06 09 10','01 03 06 09 11','01 03 06 10 11',
                              '01 03 07 08 09','01 03 07 08 10','01 03 07 08 11','01 03 07 09 10','01 03 07 09 11',
                              '01 03 07 10 11','01 03 08 09 10','01 03 08 09 11','01 03 08 10 11','01 03 09 10 11',
                              '01 04 05 06 07','01 04 05 06 08','01 04 05 06 09','01 04 05 06 10','01 04 05 06 11',
                              '01 04 05 07 08','01 04 05 07 09','01 04 05 07 10','01 04 05 07 11','01 04 05 08 09',
                              '01 04 05 08 10','01 04 05 08 11','01 04 05 09 10','01 04 05 09 11','01 04 05 10 11',
                              '01 04 06 07 08','01 04 06 07 09','01 04 06 07 10','01 04 06 07 11','01 04 06 08 09',
                              '01 04 06 08 10','01 04 06 08 11','01 04 06 09 10','01 04 06 09 11','01 04 06 10 11',
                              '01 04 07 08 09','01 04 07 08 10','01 04 07 08 11','01 04 07 09 10','01 04 07 09 11',
                              '01 04 07 10 11','01 04 08 09 10','01 04 08 09 11','01 04 08 10 11','01 04 09 10 11',
                              '01 05 06 07 08','01 05 06 07 09','01 05 06 07 10','01 05 06 07 11','01 05 06 08 09',
                              '01 05 06 08 10','01 05 06 08 11','01 05 06 09 10','01 05 06 09 11','01 05 06 10 11',
                              '01 05 07 08 09','01 05 07 08 10','01 05 07 08 11','01 05 07 09 10','01 05 07 09 11',
                              '01 05 07 10 11','01 05 08 09 10','01 05 08 09 11','01 05 08 10 11','01 05 09 10 11',
                              '01 06 07 08 09','01 06 07 08 10','01 06 07 08 11','01 06 07 09 10','01 06 07 09 11',
                              '01 06 07 10 11','01 06 08 09 10','01 06 08 09 11','01 06 08 10 11','01 06 09 10 11',
                              '01 07 08 09 10','01 07 08 09 11','01 07 08 10 11','01 07 09 10 11','01 08 09 10 11',
                              '02 03 04 05 06','02 03 04 05 07','02 03 04 05 08','02 03 04 05 09','02 03 04 05 10',
                              '02 03 04 05 11','02 03 04 06 07','02 03 04 06 08','02 03 04 06 09','02 03 04 06 10',
                              '02 03 04 06 11','02 03 04 07 08','02 03 04 07 09','02 03 04 07 10','02 03 04 07 11',
                              '02 03 04 08 09','02 03 04 08 10','02 03 04 08 11','02 03 04 09 10','02 03 04 09 11',
                              '02 03 04 10 11','02 03 05 06 07','02 03 05 06 08','02 03 05 06 09','02 03 05 06 10',
                              '02 03 05 06 11','02 03 05 07 08','02 03 05 07 09','02 03 05 07 10','02 03 05 07 11',
                              '02 03 05 08 09','02 03 05 08 10','02 03 05 08 11','02 03 05 09 10','02 03 05 09 11',
                              '02 03 05 10 11','02 03 06 07 08','02 03 06 07 09','02 03 06 07 10','02 03 06 07 11',
                              '02 03 06 08 09','02 03 06 08 10','02 03 06 08 11','02 03 06 09 10','02 03 06 09 11',
                              '02 03 06 10 11','02 03 07 08 09','02 03 07 08 10','02 03 07 08 11','02 03 07 09 10',
                              '02 03 07 09 11','02 03 07 10 11','02 03 08 09 10','02 03 08 09 11','02 03 08 10 11',
                              '02 03 09 10 11','02 04 05 06 07','02 04 05 06 08','02 04 05 06 09','02 04 05 06 10',
                              '02 04 05 06 11','02 04 05 07 08','02 04 05 07 09','02 04 05 07 10','02 04 05 07 11',
                              '02 04 05 08 09','02 04 05 08 10','02 04 05 08 11','02 04 05 09 10','02 04 05 09 11',
                              '02 04 05 10 11','02 04 06 07 08','02 04 06 07 09','02 04 06 07 10','02 04 06 07 11',
                              '02 04 06 08 09','02 04 06 08 10','02 04 06 08 11','02 04 06 09 10','02 04 06 09 11',
                              '02 04 06 10 11','02 04 07 08 09','02 04 07 08 10','02 04 07 08 11','02 04 07 09 10',
                              '02 04 07 09 11','02 04 07 10 11','02 04 08 09 10','02 04 08 09 11','02 04 08 10 11',
                              '02 04 09 10 11','02 05 06 07 08','02 05 06 07 09','02 05 06 07 10','02 05 06 07 11',
                              '02 05 06 08 09','02 05 06 08 10','02 05 06 08 11','02 05 06 09 10','02 05 06 09 11',
                              '02 05 06 10 11','02 05 07 08 09','02 05 07 08 10','02 05 07 08 11','02 05 07 09 10',
                              '02 05 07 09 11','02 05 07 10 11','02 05 08 09 10','02 05 08 09 11','02 05 08 10 11',
                              '02 05 09 10 11','02 06 07 08 09','02 06 07 08 10','02 06 07 08 11','02 06 07 09 10',
                              '02 06 07 09 11','02 06 07 10 11','02 06 08 09 10','02 06 08 09 11','02 06 08 10 11',
                              '02 06 09 10 11','02 07 08 09 10','02 07 08 09 11','02 07 08 10 11','02 07 09 10 11',
                              '02 08 09 10 11','03 04 05 06 07','03 04 05 06 08','03 04 05 06 09','03 04 05 06 10',
                              '03 04 05 06 11','03 04 05 07 08','03 04 05 07 09','03 04 05 07 10','03 04 05 07 11',
                              '03 04 05 08 09','03 04 05 08 10','03 04 05 08 11','03 04 05 09 10','03 04 05 09 11',
                              '03 04 05 10 11','03 04 06 07 08','03 04 06 07 09','03 04 06 07 10','03 04 06 07 11',
                              '03 04 06 08 09','03 04 06 08 10','03 04 06 08 11','03 04 06 09 10','03 04 06 09 11',
                              '03 04 06 10 11','03 04 07 08 09','03 04 07 08 10','03 04 07 08 11','03 04 07 09 10',
                              '03 04 07 09 11','03 04 07 10 11','03 04 08 09 10','03 04 08 09 11','03 04 08 10 11',
                              '03 04 09 10 11','03 05 06 07 08','03 05 06 07 09','03 05 06 07 10','03 05 06 07 11',
                              '03 05 06 08 09','03 05 06 08 10','03 05 06 08 11','03 05 06 09 10','03 05 06 09 11',
                              '03 05 06 10 11','03 05 07 08 09','03 05 07 08 10','03 05 07 08 11','03 05 07 09 10',
                              '03 05 07 09 11','03 05 07 10 11','03 05 08 09 10','03 05 08 09 11','03 05 08 10 11',
                              '03 05 09 10 11','03 06 07 08 09','03 06 07 08 10','03 06 07 08 11','03 06 07 09 10',
                              '03 06 07 09 11','03 06 07 10 11','03 06 08 09 10','03 06 08 09 11','03 06 08 10 11',
                              '03 06 09 10 11','03 07 08 09 10','03 07 08 09 11','03 07 08 10 11','03 07 09 10 11',
                              '03 08 09 10 11','04 05 06 07 08','04 05 06 07 09','04 05 06 07 10','04 05 06 07 11',
                              '04 05 06 08 09','04 05 06 08 10','04 05 06 08 11','04 05 06 09 10','04 05 06 09 11',
                              '04 05 06 10 11','04 05 07 08 09','04 05 07 08 10','04 05 07 08 11','04 05 07 09 10',
                              '04 05 07 09 11','04 05 07 10 11','04 05 08 09 10','04 05 08 09 11','04 05 08 10 11',
                              '04 05 09 10 11','04 06 07 08 09','04 06 07 08 10','04 06 07 08 11','04 06 07 09 10',
                              '04 06 07 09 11','04 06 07 10 11','04 06 08 09 10','04 06 08 09 11','04 06 08 10 11',
                              '04 06 09 10 11','04 07 08 09 10','04 07 08 09 11','04 07 08 10 11','04 07 09 10 11',
                              '04 08 09 10 11','05 06 07 08 09','05 06 07 08 10','05 06 07 08 11','05 06 07 09 10',
                              '05 06 07 09 11','05 06 07 10 11','05 06 08 09 10','05 06 08 09 11','05 06 08 10 11',
                              '05 06 09 10 11','05 07 08 09 10','05 07 08 09 11','05 07 08 10 11','05 07 09 10 11',
                              '05 08 09 10 11','06 07 08 09 10','06 07 08 09 11','06 07 08 10 11','06 07 09 10 11',
                              '06 08 09 10 11','07 08 09 10 11');
                       
    /**
     * 构造函数
     */
    function __construct( $aDBO=array() )
    {
        parent::__construct( $aDBO );
		require_once(PDIR_HIGH_GAME. DS. '_tmp'.DS.'static_caches'.DS.'methods.php');
		$this->_aMethod_Config = $_METHODS;
    }
    
    
    /**
     * 获取封锁表的查询和更新条件。
     *
     * @param int    $iMethodeId    玩法ID
     * @param string $sType         号码选择类型,input,digital,dxds
     * @param string $sCode         号码
     * @return array  times:奖金倍数，condition:对应的条件
     */
    public function getLocksCondition( $iMethodeId, $sType, $sCode )
    {
        $aResult = array();
        if( empty($iMethodeId) || empty($sType) || strlen($sCode) == 0 )
        {
            return FALSE;
        }
        $sMethodeName = $this->_aMethod_Config[$iMethodeId]; //玩法对应的表达式
        $aCode      = array();
        $sCondition = ""; //封锁表条件
        if( $sType == 'input' )
        {//如果是输入型
            $aCode  =  explode( "|", $sCode );
            switch( $sMethodeName )
            {
                case 'QZX2' : //前2直选
                case 'HZX2' : //后2直选
                              $sCondition = " `code` IN(".implode(",",$aCode).") ";
                              $aResult[] = array('times'=>1,'condition'=>$sCondition);
                              break;
                case 'QZU2' : //前2组选
                case 'HZU2' : //后2组选
                              $aTemp = array();
                              foreach( $aCode as $v )
                              {
                                  if( strlen($v) != 2 )
                                  {
                                      return FALSE;
                                  }
                                  $aTemp[] = $v[0].$v[1];
                                  $aTemp[] = $v[1].$v[0];
                              }
                              $aCode = $aTemp;
                              unset($aTemp);
                              $sCondition = " `code` IN(".implode(",",$aCode).") ";
                              $aResult[] = array('times'=>1,'condition'=>$sCondition);
                              break;
                case 'SDZX3':
                case 'SDZX2': $sCondition = " `code` IN('".implode("','",$aCode)."') ";
                              $aResult[] = array('times'=>1,'condition'=>$sCondition);
                              break;
                case 'SDZU3':                
                case 'SDZU2': $sCondition = " `stamp` IN('".implode("','",$aCode)."') ";
                              $aResult[] = array('times'=>1,'condition'=>$sCondition);
                              break;
                case 'SDRX1'://任选一
                             foreach( $aCode as $sNum )
                             {
                                 $aResult[] = array('times'=>1,'condition'=>" `code` regexp '".$sNum."' ");
                             }
                             break;
                case 'SDRX2'://任选二
                             $aTemp = array();
                             foreach( $this->_SDRXB as $v )
                             {
                                 foreach( $aCode as $sNum )
                                 {
                                     $aTT = explode(" ",$sNum);
                                     if( strpos($v,$aTT[0]) !== FALSE && strpos($v,$aTT[1]) !== FALSE )
                                     {
                                         $aTemp[$v] = isset($aTemp[$v]) ? $aTemp[$v]+1 : 1;
                                     }
                                 }
                             }
                             $aCode = $this->_ArrayFlip( $aTemp );
                             foreach( $aCode as $k=>$v )
                             {
                                 $aResult[] = array('times'=>intval($k),'condition'=>" `code` IN('".implode("','",$v)."') ");
                             }
                             break;
                case 'SDRX3'://任选三
                             $aTemp = array();
                             foreach( $this->_SDRXB as $v )
                             {
                                 foreach( $aCode as $sNum )
                                 {
                                     $aTT = explode(" ",$sNum);
                                     if( strpos($v,$aTT[0]) !== FALSE && 
                                         strpos($v,$aTT[1]) !== FALSE && strpos($v,$aTT[2]) !== FALSE )
                                     {
                                         $aTemp[$v] = isset($aTemp[$v]) ? $aTemp[$v]+1 : 1;
                                     }
                                 }
                             }
                             $aCode = $this->_ArrayFlip( $aTemp );
                             foreach( $aCode as $k=>$v )
                             {
                                 $aResult[] = array('times'=>intval($k),'condition'=>" `code` IN('".implode("','",$v)."') ");
                             }
                             break;
                case 'SDRX4'://任选四
                             $aTemp = array();
                             foreach( $this->_SDRXB as $v )
                             {
                                 foreach( $aCode as $sNum )
                                 {
                                     $aTT = explode(" ",$sNum);
                                     if( strpos($v,$aTT[0]) !== FALSE && 
                                         strpos($v,$aTT[1]) !== FALSE && 
                                         strpos($v,$aTT[2]) !== FALSE && strpos($v,$aTT[3]) !== FALSE )
                                     {
                                         $aTemp[$v] = isset($aTemp[$v]) ? $aTemp[$v]+1 : 1;
                                     }
                                 }
                             }
                             $aCode = $this->_ArrayFlip( $aTemp );
                             foreach( $aCode as $k=>$v )
                             {
                                 $aResult[] = array('times'=>intval($k),'condition'=>" `code` IN('".implode("','",$v)."') ");
                             }
                             break;
                case 'SDRX5'://任选五
                             $aResult[] = array('times'=>1,'condition'=>" `code` IN('".implode("','",$aCode)."') ");
                             break;
                case 'SDRX6'://任选六
                case 'SDRX7'://任选七
                case 'SDRX8'://任选八
                             $aTemp = array();
                             foreach( $this->_SDRXB as $v )
                             {
                                 foreach( $aCode as $sNum )
                                 {
                                     $aTT = explode(" ",$sNum);
                                     $iTT = 0;
                                     foreach( $aTT as $tt )
                                     {
                                         if( strpos($v,$tt) !== FALSE )
                                         {
                                             $iTT ++;
                                         }
                                     }
                                     if( $iTT == 5 )
                                     {
                                         $aTemp[$v] = isset($aTemp[$v]) ? $aTemp[$v]+1 : 1;
                                     }
                                 }
                             }
                             $aCode = $this->_ArrayFlip( $aTemp );
                             foreach( $aCode as $k=>$v )
                             {
                                 $aResult[] = array('times'=>intval($k),'condition'=>" `code` IN('".implode("','",$v)."') ");
                             }
                             break;
                default     : $sCondition = " `code` IN(".implode(",",$aCode).") ";
                              $aResult[] = array('times'=>1,'condition'=>$sCondition);
                              break;
            }
        }
        elseif( $sType == 'digital' || $sType == 'dxds' || $sType == 'dds' )
        {
            switch( $sMethodeName )
            {
                case 'QZX3' :
                case 'HZX3' : //前三后三直选复式
				case 'ZU3BD' : 
					 $aCode = explode("|",$sCode);
					 $sCondition = " `code` regexp '[".implode("][",$aCode)."]' ";
					 $aResult[] = array('times'=>1,'condition'=>$sCondition);
					 break;
				case 'ZU2BD' : //组2包胆
					 $sCondition = " `code` regexp '[".$sCode."]' AND `code` != '$sCode$sCode'";
					 $aResult[] = array('times'=>1,'condition'=>$sCondition);
					 break;
                case 'QZXHZ' :
                case 'HZXHZ' : //直选和值
				case 'ZXHZ2':
                             $aCode =  explode("|",$sCode);
                             $sCondition = " `specialvalue` IN(".implode(",",$aCode).") ";
                             $aResult[] = array('times'=>1,'condition'=>$sCondition);
                             break;
				case 'ZUHZ2': 
                             $aCode =  explode("|",$sCode);
                             $sCondition = " `specialvalue` IN(".implode(",",$aCode).") ";
							 $aTmp = array();
							 foreach($aCode AS $code)
							 {
								 if($code % 2 == 0)
								 {
									$a = $code/2;
									$aTmp[] = $a.$a;
								 }
							 }
							 if($aTmp)
							 {
								$sCondition .= "AND `code` NOT IN(".implode(",", $aTmp).") ";
							 }
                             $aResult[] = array('times'=>1,'condition'=>$sCondition);
                             break;
				case 'ZXKD' : 
				case 'ZXKD2' : 
                             $aCode =  explode("|",$sCode);
                             $sCondition = " `stamp` IN(".implode(",",$aCode).") ";
                             $aResult[] = array('times'=>1,'condition'=>$sCondition);
					break;
				case 'WXZU120' : 
				case 'SXZU24' : 
				case 'SXZU6' : 
					$aTemp = explode("|",$sCode);
					$iLen  = count($aTemp);
					if($sMethodeName == 'WXZU120')
					{
						$min_chosen = 5;
					}
					elseif($sMethodeName == 'SXZU24')
					{
						$min_chosen = 4;
					}
					elseif($sMethodeName == 'SXZU6')
					{
						$min_chosen = 2;
					}
					if( $iLen < $min_chosen )
					{
						return FALSE;
					}
					sort($aTemp);
					$aCode = $this->getCombination( $aTemp, $min_chosen );
					if($sMethodeName == 'SXZU6')
					{
						$aCode = $this->getRepeat($aCode, 2);
					}
					$sCondition = " `code` IN(".str_replace(' ', '', implode(",",$aCode)).") ";
					$aResult[] = array('times'=>1,'condition'=>$sCondition);
					break;
				case 'QZUS'  ://前三后三组三1|2|3|4形式
                case 'HZUS'  :
                             $aTemp = explode("|",$sCode);
                             $iLen  = count($aTemp);
                             if( $iLen < 2 )
                             {
                                 return FALSE;
                             }
                             sort($aTemp);
                             for( $i=0; $i<$iLen; $i++ )
                             {
                                 for( $j=$i+1; $j<$iLen; $j++ )
                                 {
                                     $aCode[] = $aTemp[$i].$aTemp[$i].$aTemp[$j];
                                     $aCode[] = $aTemp[$i].$aTemp[$j].$aTemp[$j];
                                 }
                             }
                             $sCondition = " `code` IN(".implode(",",$aCode).") ";
                             $aResult[] = array('times'=>1,'condition'=>$sCondition);
                             break;
                case 'QZUL'  ://前三后三组六1|2|3|4形式
                case 'HZUL'  :
                             $aTemp = explode("|",$sCode);
                             $iLen  = count($aTemp);
                             if( $iLen < 3 )
                             {
                                 return FALSE;
                             }
                             sort($aTemp);
                             for( $i=0; $i<$iLen; $i++ )
                             {
                                 for( $j=$i+1; $j<$iLen; $j++ )
                                 {
                                     for( $k=$j+1; $k<$iLen; $k++ )
                                     {
                                         $aCode[] = $aTemp[$i].$aTemp[$j].$aTemp[$k];
                                     }
                                 }
                             }
                             $sCondition = " `code` IN(".implode(",",$aCode).") ";
                             $aResult[] = array('times'=>1,'condition'=>$sCondition);
                             break;
                case 'QHHZX' ://输入型，跳过
                case 'HHHZX' :break;//输入型，跳过
                case 'QZUHZ' :
                case 'HZUHZ' ://前三后三组选和值
                             $aCode =  explode("|",$sCode);
                             $sCondition = " `specialvalue` IN(".implode(",",$aCode).") ";
                             $aResult[] = array('times'=>1,'condition'=>$sCondition);
                             break;
                case 'BDW1':
				case 'HSCS': 
				case 'SXBX':
				case 'SJFC':
				case 'HBDW1' :
                             $aCode =  explode("|",$sCode);
                             foreach( $aCode as $sNum )
                             {
                                 $aResult[] = array('times'=>1,'condition'=>" `code` regexp '[".$sNum."]' ");
                             }
                             //$sCondition = " `code` IN(".implode(",",$aCode).") AND `methodid` = '".$iMethodeId."' ";
                             break;
                case 'HBDW2' :
                             $aTemp = explode("|",$sCode);
                             $iLen  = count($aTemp);
                             if( $iLen < 2 )
                             {
                                 return FALSE;
                             }
                             //sort($aTemp);
                             for( $i=0; $i<$iLen; $i++ )
                             {
                                 for( $j=$i+1; $j<$iLen; $j++ )
                                 {
                                     for($k=0; $k<10; $k++)
                                     {
                                         $aTT = array($aTemp[$i],$aTemp[$j],$k);
                                         sort($aTT,SORT_NUMERIC);
                                         $sTT = implode("",$aTT);
                                         if( isset($aCode[$sTT]) )
                                         {
                                             $aCode[$sTT] += 1;
                                         }
                                         else
                                         {
                                             $aCode[$sTT] = 1;
                                         }
                                     }
                                     //$aCode[] = $aTemp[$i].$aTemp[$j];
                                 }
                             }
                             $aCode = $this->_ArrayFlip( $aCode );
                             foreach( $aCode as $k=>$v )
                             {
                                 $aResult[] = array('times'=>intval($k),'condition'=>" `code` IN(".implode(",",$v).") ");
                             }
                             //$sCondition = " `code` IN(".implode(",",$aCode).") AND `methodid` = '".$iMethodeId."' ";
                             break;
                case 'QZX2'  :
                case 'HZX2'  :
                             $aCode = explode("|",$sCode);
                             $sCondition = " `code` regexp '[".implode("][",$aCode)."]' ";
                             $aResult[] = array('times'=>1,'condition'=>$sCondition);
                             break;
                case 'QZU2'  :
                case 'HZU2'  :
                             $aTemp = explode("|",$sCode);
                             $iLen  = count($aTemp);
                             if( $iLen < 2 )
                             {
                                 return FALSE;
                             }
                             sort($aTemp);
                             for( $i=0; $i<$iLen; $i++ )
                             {
                                 for( $j=$i+1; $j<$iLen; $j++ )
                                 {
                                     $aCode[] = $aTemp[$i].$aTemp[$j];
                                     $aCode[] = $aTemp[$j].$aTemp[$i];
                                 }
                             }
                             $sCondition = " `code` IN(".implode(",",$aCode).") ";
                             $aResult[] = array('times'=>1,'condition'=>$sCondition);
                             break;
                case 'DWD'   ://
                case 'DWD3'  ://
				case 'HZWS' : //和值尾数
							 $aCode =  explode("|",$sCode);
                             $sCondition = " `code` IN(".implode(",",$aCode).") AND `methodid` = '".$iMethodeId."' ";
                             $aResult[] = array('times'=>1,'condition'=>$sCondition);
                             break;
                case 'QDXDS' :
                case 'HDXDS' :
				case '3DXDS' : 
                             $aCode = explode("|",$sCode);
                             $sCondition = " `code` regexp '[".implode("][",$aCode)."]' AND `methodid` = '".$iMethodeId."' ";
                             $aResult[] = array('times'=>1,'condition'=>$sCondition);
                             break;
                //山东十一运
                case 'SDZX3': 
                case 'SDZX2'://直选
                             $aCode = explode("|",$sCode);
                             foreach( $aCode as $k=>$v )
                             {
                                 $aCode[$k] = preg_replace( "/ /", "|", $v );
                             }
                             $sCondition = " `code` regexp '(".implode(") (",$aCode).")' ";
                             $aResult[] = array('times'=>1,'condition'=>$sCondition);
                             break;
                case 'SDZU3': //组选
                             $aTemp = explode("|",$sCode);
                             $iLen  = count($aTemp);
                             if( $iLen < 3 )
                             {
                                 return FALSE;
                             }
                             sort($aTemp,SORT_NUMERIC);
                             for( $i=0; $i<$iLen; $i++ )
                             {
                                 for( $j=$i+1; $j<$iLen; $j++ )
                                 {
                                     for( $k=$j+1; $k<$iLen; $k++ )
                                     {
                                         $aCode[] = "'".$aTemp[$i]." ".$aTemp[$j]." ".$aTemp[$k]."'";
                                     }
                                 }
                             }
                             $sCondition = " `stamp` IN(".implode(",",$aCode).") ";
                             $aResult[] = array('times'=>1,'condition'=>$sCondition);
                             break;
                case 'SDZU2': //组选2
                             $aTemp = explode("|",$sCode);
                             $iLen  = count($aTemp);
                             if( $iLen < 2 )
                             {
                                 return FALSE;
                             }
                             sort($aTemp,SORT_NUMERIC);
                             for( $i=0; $i<$iLen; $i++ )
                             {
                                 for( $j=$i+1; $j<$iLen; $j++ )
                                 {
                                     $aCode[] = "'".$aTemp[$i]." ".$aTemp[$j]."'";
                                 }
                             }
                             $sCondition = " `stamp` IN(".implode(",",$aCode).") ";
                             $aResult[] = array('times'=>1,'condition'=>$sCondition);
                             break;
                case 'SDBDW': //不定位
                             $aCode =  explode("|",$sCode);
                             foreach( $aCode as $sNum )
                             {
                                 $aResult[] = array('times'=>1,'condition'=>" `code` regexp '".$sNum."' ");
                             }
                             break;
                case 'SDDWD'://定位胆
                             $aCode =  explode("|",$sCode);
                             $sCondition = " `code` IN('".implode("','",$aCode)."') AND `methodid` = '".$iMethodeId."' ";
                             $aResult[] = array('times'=>1,'condition'=>$sCondition);
                             break;
                case 'SDDDS'://定单双
                             $aCode =  explode("|",$sCode);
                             $sCondition = " `code` IN(".implode(",",$aCode).") AND `methodid` = '".$iMethodeId."' ";
                             $aResult[] = array('times'=>1,'condition'=>$sCondition);
                             break;
                case 'SDCZW'://猜中位
                             $aCode =  explode("|",$sCode);
                             $sCondition = " `code` IN('".implode("','",$aCode)."') AND `methodid` = '".$iMethodeId."' ";
                             $aResult[] = array('times'=>1,'condition'=>$sCondition);
                             break;
                case 'SDRX1'://任选一
                             $aCode =  explode("|",$sCode);
                             foreach( $aCode as $sNum )
                             {
                                 $aResult[] = array('times'=>1,'condition'=>" `code` regexp '".$sNum."' ");
                             }
                             //$sCondition = " `code` IN('".implode("','",$aCode)."') AND `methodid` = '".$iMethodeId."' ";
                             break;
                case 'SDRX2'://任选二
                             $aTemp = explode("|",$sCode);
                             $iLen  = count($aTemp);
                             if( $iLen < 2 )
                             {
                                 return FALSE;
                             }
                             sort($aTemp,SORT_NUMERIC);
                             $aCode = $this->getCombination( $aTemp, 2 );
                             $aTemp = array();
                             foreach( $this->_SDRXB as $v )
                             {
                                 foreach( $aCode as $sNum )
                                 {
                                     $aTT = explode(" ",$sNum);
                                     if( strpos($v,$aTT[0]) !== FALSE && strpos($v,$aTT[1]) !== FALSE )
                                     {
                                         $aTemp[$v] = isset($aTemp[$v]) ? $aTemp[$v]+1 : 1;
                                     }
                                 }
                             }
                             $aCode = $this->_ArrayFlip( $aTemp );
                             foreach( $aCode as $k=>$v )
                             {
                                 $aResult[] = array('times'=>intval($k),'condition'=>" `code` IN('".implode("','",$v)."') ");
                             }
                             //$sCondition = " `code` IN('".implode("','",$aCode)."') AND `methodid` = '".$iMethodeId."' ";
                             break;
                case 'SDRX3'://任选三
                             $aTemp = explode("|",$sCode);
                             $iLen  = count($aTemp);
                             if( $iLen < 3 )
                             {
                                 return FALSE;
                             }
                             sort($aTemp,SORT_NUMERIC);
                             $aCode = $this->getCombination( $aTemp, 3 );
                             $aTemp = array();
                             foreach( $this->_SDRXB as $v )
                             {
                                 foreach( $aCode as $sNum )
                                 {
                                     $aTT = explode(" ",$sNum);
                                     if( strpos($v,$aTT[0]) !== FALSE && 
                                         strpos($v,$aTT[1]) !== FALSE && strpos($v,$aTT[2]) !== FALSE )
                                     {
                                         $aTemp[$v] = isset($aTemp[$v]) ? $aTemp[$v]+1 : 1;
                                     }
                                 }
                             }
                             $aCode = $this->_ArrayFlip( $aTemp );
                             foreach( $aCode as $k=>$v )
                             {
                                 $aResult[] = array('times'=>intval($k),'condition'=>" `code` IN('".implode("','",$v)."') ");
                             }
                             //$sCondition = " `code` IN('".implode("','",$aCode)."') AND `methodid` = '".$iMethodeId."' ";
                             break;
                case 'SDRX4'://任选四
                             $aTemp = explode("|",$sCode);
                             $iLen  = count($aTemp);
                             if( $iLen < 4 )
                             {
                                 return FALSE;
                             }
                             sort($aTemp,SORT_NUMERIC);
                             $aCode = $this->getCombination( $aTemp, 4 );
                             $aTemp = array();
                             foreach( $this->_SDRXB as $v )
                             {
                                 foreach( $aCode as $sNum )
                                 {
                                     $aTT = explode(" ",$sNum);
                                     if( strpos($v,$aTT[0]) !== FALSE && 
                                         strpos($v,$aTT[1]) !== FALSE && 
                                         strpos($v,$aTT[2]) !== FALSE && strpos($v,$aTT[3]) !== FALSE )
                                     {
                                         $aTemp[$v] = isset($aTemp[$v]) ? $aTemp[$v]+1 : 1;
                                     }
                                 }
                             }
                             $aCode = $this->_ArrayFlip( $aTemp );
                             foreach( $aCode as $k=>$v )
                             {
                                 $aResult[] = array('times'=>intval($k),'condition'=>" `code` IN('".implode("','",$v)."') ");
                             }
                             //$sCondition = " `code` IN('".implode("','",$aCode)."') AND `methodid` = '".$iMethodeId."' ";
                             break;
                case 'SDRX5'://任选五
                             $aTemp = explode("|",$sCode);
                             $iLen  = count($aTemp);
                             if( $iLen < 5 )
                             {
                                 return FALSE;
                             }
                             sort($aTemp,SORT_NUMERIC);
                             $aCode = $this->getCombination( $aTemp, 5 );
                             //$sCondition = " `code` IN('".implode("','",$aCode)."') AND `methodid` = '".$iMethodeId."' ";
                             $aResult[] = array('times'=>1,'condition'=>" `code` IN('".implode("','",$aCode)."') ");
                             break;
                case 'SDRX6'://任选六
                             $aTemp = explode("|",$sCode);
                             $iLen  = count($aTemp);
                             if( $iLen < 6 )
                             {
                                 return FALSE;
                             }
                             sort($aTemp,SORT_NUMERIC);
                             $aCode = $this->getCombination( $aTemp, 5 );
                             $iT = $this->_GetCombinCount( ($iLen-5),1 );
                             $aResult[] = array('times'=>intval($iT),'condition'=>" `code` IN('".implode("','",$aCode)."') ");
                             //$sCondition = " `code` IN('".implode("','",$aCode)."') AND `methodid` = '".$iMethodeId."' ";
                             break;
                case 'SDRX7'://任选七
                             $aTemp = explode("|",$sCode);
                             $iLen  = count($aTemp);
                             if( $iLen < 7 )
                             {
                                 return FALSE;
                             }
                             sort($aTemp,SORT_NUMERIC);
                             $aCode = $this->getCombination( $aTemp, 5 );
                             $iT = $this->_GetCombinCount( ($iLen-5),2 );
                             $aResult[] = array('times'=>intval($iT),'condition'=>" `code` IN('".implode("','",$aCode)."') ");
                             //$sCondition = " `code` IN('".implode("','",$aCode)."') AND `methodid` = '".$iMethodeId."' ";
                             break;
                case 'SDRX8'://任选八
                             $aTemp = explode("|",$sCode);
                             $iLen  = count($aTemp);
                             if( $iLen < 8 )
                             {
                                 return FALSE;
                             }
                             sort($aTemp,SORT_NUMERIC);
                             $aCode = $this->getCombination( $aTemp, 5 );
                             $iT = $this->_GetCombinCount( ($iLen-5),3 );
                             $aResult[] = array('times'=>intval($iT),'condition'=>" `code` IN('".implode("','",$aCode)."') ");
                             //$sCondition = " `code` IN('".implode("','",$aCode)."') AND `methodid` = '".$iMethodeId."' ";
                             break;
				case 'BJRX1'://北京任选1
                             $aCode =  explode("|",$sCode);
                             $sCondition = " `code` IN('".implode("','",$aCode)."') AND `methodid` = '".$iMethodeId."' ";
                             $aResult[] = array('times'=>1,'condition'=>$sCondition);
							break;
				case 'BJHZDS'://北京和值单双
				case 'BJHZDX'://北京和值大小
				case 'BJSXP'://北京上下盘
 				case 'BJJOP'://北京奇偶盘
                            $aCode =  explode("|",$sCode);
                             $sCondition = " `code` IN('".implode("','",$aCode)."') AND `methodid` = '".$iMethodeId."' ";
                             $aResult[] = array('times'=>1,'condition'=>$sCondition);
							break;
				case 'WXZU60' : 
				case 'WXZU30' : 
				case 'WXZU20' : 
				case 'WXZU10' : 
				case 'WXZU5' : 
				case 'SXZU12' : 
				case 'SXZU4' : 
					$aTemp = explode("|",$sCode);
					$iLen  = count($aTemp);
					if( $iLen < 2 )
					{
						return FALSE;
					}
					if($sMethodeName == 'WXZU60')
					{
						$p1 = 1;
						$p2 = 3;
						$p1r = 2;
						$p2r = 1;
					}
					elseif($sMethodeName == 'WXZU30')
					{
						$p1 = 2;
						$p2 = 1;
						$p1r = 2;
						$p2r = 1;
					}
					elseif($sMethodeName == 'WXZU20')
					{
						$p1 = 1;
						$p2 = 2;
						$p1r = 3;
						$p2r = 1;
					}
					elseif($sMethodeName == 'WXZU10')
					{
						$p1 = 1;
						$p2 = 1;
						$p1r = 3;
						$p2r = 2;
					}
					elseif($sMethodeName == 'WXZU5')
					{
						$p1 = 1;
						$p2 = 1;
						$p1r = 4;
						$p2r = 1;
					}
					elseif($sMethodeName == 'SXZU12')
					{
						$p1 = 1;
						$p2 = 2;
						$p1r = 2;
						$p2r = 1;
					}
					elseif($sMethodeName == 'SXZU4')
					{
						$p1 = 1;
						$p2 = 1;
						$p1r = 3;
						$p2r = 1;
					}
					$aCode = array();
					$aP1 = $this->getCombination( str_split($aTemp[0]), $p1 );
					$aP2 = $this->getCombination( str_split($aTemp[1]), $p2 );
					for( $i=0; $i<sizeof($aP1); $i++)
					{
						foreach($aP2 as $s)
						{
							if(in_array($aP1[$i], str_split($s))) continue;
							$aCode[] = $this->strOrder(str_repeat($aP1[$i], $p1r) . str_repeat($s, $p2r));
						}
					}
					$sCondition = " `code` IN(".str_replace(' ', '', implode(",",$aCode)).") ";
					//var_dump($sCondition);die;
					$aResult[] = array('times'=>1,'condition'=>$sCondition);
					break;
				case 'TSH3': 
					 $aCode = explode("|",$sCode);
					 $sCondition = " `code` IN(".implode(",",$aCode).") AND `methodid` = '".$iMethodeId."' ";
					 $aResult[] = array('times'=>1,'condition'=>$sCondition);
					 break;
				case 'ZH3' :  //组合三
					 $aCode = explode("|",$sCode);
					 $aResult[] = array('times'=>1,'condition'=>" `code` regexp '[".implode("][",$aCode)."]' ");//一等奖
					 unset($aCode[0]);
					 $aResult[] = array('times'=>1,'condition'=>" `code` regexp '[0-9][".implode("][",$aCode)."]' ");//二等奖
					 unset($aCode[1]);
					 $aResult[] = array('times'=>1,'condition'=>" `code` regexp '[0-9][0-9][".implode("][",$aCode)."]' ");//三等奖
					 break;
				case '4BDW2' : 
				case '5BDW2': 
					 $aTemp = explode("|",$sCode);
					 $iLen  = count($aTemp);
					 if( $iLen < 2 )
					 {
						 return FALSE;
					 }
					$aCode = $this->getCombination( $aTemp, 2 );
					$aResult[] = array('times'=>1,'condition'=>" `code` IN(".str_replace(' ', '', implode(",",$aCode)).") ");
					break;
				case '5BDW3': 
					 $aTemp = explode("|",$sCode);
					 $iLen  = count($aTemp);
					 if( $iLen < 3 )
					 {
						 return FALSE;
					 }
					$aCode = $this->getCombination( $aTemp, 3 );
					$aResult[] = array('times'=>1,'condition'=>" `code` IN(".str_replace(' ', '', implode(",",$aCode)).") ");
					break;
				case '3QW':
				case '3QJ': //三码区间
					 $aCode = explode("|",$sCode);
					 $sCondition = " `code` regexp '[".implode("][",$aCode)."]' ";
					 $aResult[] = array('times'=>1,'condition'=>$sCondition);
					 $sCondition = " `code` regexp '[^".implode("][",$aCode)."]' ";
					 $aResult[] = array('times'=>1,'condition'=>$sCondition);
					 break;
				case 'TX3' : //通选三
					 $aCode = explode("|",$sCode);
					 $sCondition = " `code` regexp '[".implode("][",$aCode)."]' ";
					 $aResult[] = array('times'=>1,'condition'=>$sCondition); //一等奖

					 $sCondition = " `code` regexp '[".$aCode[0]."][".$aCode[1]."][^".$aCode[2]."]' ";
					 $aResult[] = array('times'=>1,'condition'=>$sCondition); //二等奖-前2

					 $sCondition = " `code` regexp '[".$aCode[0]."][^".$aCode[1]."][".$aCode[2]."]' ";
					 $aResult[] = array('times'=>1,'condition'=>$sCondition); //二等奖-1/3

					 $sCondition = " `code` regexp '[^".implode("][",$aCode)."]' ";
					 $aResult[] = array('times'=>1,'condition'=>$sCondition); //二等奖-后2

					 $sCondition = " `code` regexp '[".$aCode[0]."][^".$aCode[1]."][^".$aCode[2]."]' ";
					 $aResult[] = array('times'=>1,'condition'=>$sCondition); //三等奖-1
					 $sCondition = " `code` regexp '[^".$aCode[0]."][".$aCode[1]."][^".$aCode[2]."]' ";
					 $aResult[] = array('times'=>1,'condition'=>$sCondition); //三等奖-2
					 $sCondition = " `code` regexp '[^".$aCode[0]."][^".$aCode[1]."][".$aCode[2]."]' ";
					 $aResult[] = array('times'=>1,'condition'=>$sCondition); //三等奖-3

					break;
				default      : return FALSE; break;
            }
        }
        return $aResult;
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
	            if( TRUE !== $this->salesUpdate( $v ) )
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
	 * 更新某一期的销售量
	 *
	 * @author james   090812
	 * @access public  
	 * @param  array    $aArr
	 * @return boolean  TRUE OR FALSE
	 */
	public function salesUpdate( $aArr = array() )
	{
	    //01：先进行数据检查
        if( empty($aArr) || !is_array($aArr) )
        {
            return FALSE;
        }
        if( empty($aArr['issue']) )
        {
        	return FALSE;
        }
        if( empty($aArr['lotteryid']) || !is_numeric($aArr['lotteryid']) || $aArr['lotteryid'] < 1 )
        {
        	return FALSE;
        }
        $aArr['lotteryid'] = intval($aArr['lotteryid']);
        $aArr['issue']     = daddslashes($aArr['issue']);
        $aArr['lockname']  = empty($aArr['lockname']) ? "" : daddslashes($aArr['lockname']);
        $aArr['threadid']  = intval($this->oDB->getThreadId()) % 3; //获取当前线程ID[20个线程]
        if( empty($aArr['moneys']) || !is_numeric($aArr['moneys']) )
        {
        	return FALSE;
        }
        $aArr['moneys']   = round( $aArr['moneys'], 4 );
        if( $aArr['moneys'] == 0 )
        {//如果变动金额为0则直接返回TRUE
        	return TRUE;
        }
        //更新
    	$sSql = "UPDATE `salesbase` SET `moneys`=`moneys`+".$aArr['moneys'].", `pointmoney`=`pointmoney`+".
    	         $aArr['pointmoney']." WHERE `issue`='".$aArr['issue'].
    	         "' AND `lotteryid`='".$aArr['lotteryid']."' AND `lockname`='".$aArr['lockname'].
    	         "' AND `threadid`='".$aArr['threadid'] ."' ";
    	$this->oDB->query($sSql);
    	if( $this->oDB->errno() > 0 )
        {
            return FALSE;
        }
        return TRUE;
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
    protected function getCombination( $aBaseArray, $iSelectNum )
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
    protected function strOrder( $sString = '', $bDesc = FALSE )
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
     * 交换数组中的键和值，并把相同值的键名组合在一起形成数组，形成一个新的二维数组
     */
    protected function _ArrayFlip( $aArr )
    {
        if( empty($aArr) || !is_array($aArr) )
        {
            return $aArr;
        }
        $aNewArr = array();
        foreach( $aArr as $k=>$v )
        {
            $aNewArr[$v][] = $k;
        }
        return $aNewArr;
    }
    
    /**
        * 计算排列组合的个数
        *
        * @author mark
        * 
        * @param integer $iBaseNumber   基数
        * @param integer $iSelectNumber 选择数
        * 
        * @return mixed
        * 
    */
    protected function _GetCombinCount( $iBaseNumber, $iSelectNumber )
    {
        if ( $iBaseNumber == 0 || $iSelectNumber == 0 )
        {
            return FALSE;       // 无效
        }
        if( $iBaseNumber == $iSelectNumber )
        {
            return 1;//全选
        }
        if( $iSelectNumber == 1 )
        {
            return $iBaseNumber;//选一个数
        }
        $iNumerator = 1;//分子
        $iDenominator = 1;//分母
        for($i = 0; $i < $iSelectNumber; $i++)
        {
            $iNumerator *= $iBaseNumber - $i;//n*(n-1)...(n-m+1)
            $iDenominator *= $iSelectNumber - $i;//(n-m)....*2*1
        }
        return $iNumerator / $iDenominator;
    }

	public function getRepeat($aCode, $iRepeats=2)
	{
		$result = array();
		for($ii=0; $ii<sizeof($aCode);$ii++)
		{
			$tCode = explode(' ', $aCode[$ii]);
			$result[$ii] = '';
			for($iii=0;$iii<$iRepeats;$iii++)
			{
				$result[$ii] .= $tCode[$iii] .' ' . $tCode[$iii] . ' ';
			}
		}
		return $result;
	}
}
?>