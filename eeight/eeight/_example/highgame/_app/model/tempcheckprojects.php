<?php
class model_tempcheckprojects extends model_gamebase
{
    function check()
    {
        $oCDB = new db( $GLOBALS['aSysDbServer']['report'] );
        $isContinue = TRUE;
        $iStart = 0;
        $aErrorId = array();
        while( $isContinue )
        {
            $sSql = "SELECT `projectid`,`codetype`,`methodid`,`code`,`multiple`,`modes`,`totalprice` FROM `projects` LIMIT ".$iStart.",".($iStart+1000);
            $aProjects = $oCDB->getAll($sSql);
            if( empty($aProjects) )
            {
                $isContinue = FALSE;
            }
            foreach( $aProjects as $v )
            {
                $aProject = array('type'=>$v['codetype'],'methodid'=>$v['methodid'],'codes'=>$v['code'],
                                  'times'=>$v['multiple'],'rate'=>$GLOBALS['config']['modes'][$v['modes']]['rate'],
                                  'money'=>$v['totalprice']);
                if( TRUE !== $this->_ReCheckNum($aProject) )
                {
                    $aErrorId[] = $v['projectid'];
                    //echo $v['projectid'].",";
                }
            }
            $iStart += 999;
        }
        echo "\r\n===================ErrorID: \r\n".implode(",",$aErrorId)."\r\n================END";
        return true;
    }
    
    private function _ReCheckNum( $aProject )
    {
        $sMethodeName = $this->_aMethod_Config[$aProject['methodid']]; //玩法对应的表达式
        $_tmpNums = 0;
        $_tmpaCode = array();
        $_tmpArr = array();
        if( $aProject['type'] == 'input' )
        {//输入型
            $_tmpaCode = explode("|",$aProject['codes']);
            $_tmpNums = count($_tmpaCode);
        }
        elseif( $aProject['type'] == 'digital' || $aProject['type'] == 'dxds' || $aProject['type'] == 'dds' )
        {
            switch( $sMethodeName )
            {
                case 'QZX3'  :
                case 'HZX3'  :
                case 'QZX2'  :
                case 'HZX2'  :
                case 'QDXDS' :
                case 'HDXDS' :
                              $_tmpaCode = explode("|",$aProject['codes']);
                              $_tmpNums = 1;
                              foreach( $_tmpaCode as $v )
                              {
                                  $_tmpNums *= strlen($v);
                              }
                              break;
                case 'QZXHZ': //直選和值
                case 'HZXHZ': $_tmpArr = array(0=>1,1=>3,2=>6,3=>10,4=>15,5=>21,6=>28,7=>36,8=>45,9=>55,10=>63,11=>69,
                                               12=>73,13=>75,14=>75,15=>73,16=>69,17=>63,18=>55,19=>45,20=>36,21=>28,
                                               22=>21,23=>15,24=>10,25=>6,26=>3,27=>1);
                case 'QZUHZ': //組選和值
                case 'HZUHZ': if( $sMethodeName == 'QZUHZ' || $sMethodeName == 'HZUHZ' )
                              {
                                  $_tmpArr = array(1=>1,2=>2,3=>2,4=>4,5=>5,6=>6,7=>8,8=>10,9=>11,10=>13,11=>14,12=>14,
                                                   13=>15,14=>15,15=>14,16=>14,17=>13,18=>11,19=>10,20=>8,21=>6,22=>5,
                                                   23=>4,24=>2,25=>2,26=>1);
                              }
                              $_tmpaCode = explode("|",$aProject['codes']);
                              $_tmpNums  = 0;
                              foreach( $_tmpaCode as $v )
                              {
                                  if( !isset($_tmpArr[$v]) )
                                  {
                                      return FALSE;
                                  }
                                  $_tmpNums += $_tmpArr[$v];
                              }
                              break;
                case 'QZUS' : //組三
                case 'HZUS' : //組三
                              $_tmpaCode = explode("|",$aProject['codes']);
                              $sss = count($_tmpaCode);
                              $_tmpNums = $sss*($sss-1);
                              break;
                case 'QZUL' : //組六
                case 'HZUL' : //組六
                              $_tmpaCode = explode("|",$aProject['codes']);
                              $sss = count($_tmpaCode);
                              $_tmpNums = $sss*($sss-1)*($sss-2)/6;
                              break;
                case 'QHHZX':
                case 'HHHZX': return FALSE;break;
                case 'HBDW1': 
                              $_tmpaCode = explode("|",$aProject['codes']);
                              $_tmpNums = count($_tmpaCode);
                              break;
                case 'HBDW2':
                case 'QZU2' : 
                case 'HZU2' : $_tmpaCode = explode("|",$aProject['codes']);
                              $sss = count($_tmpaCode);
                              $_tmpNums = $sss*($sss-1)/2;
                              break;
                
                case 'DWD'  : 
                case 'DWD3' : $_tmpaCode = explode("|",$aProject['codes']);
                              $_tmpNums = count($_tmpaCode);
                              break;
                              
                //山东十一运
                case 'SDZX3': 
                              $_tmpaCode = explode("|",$aProject['codes']);
                              if( count($_tmpaCode) != 3 )
                              {
                                  return FALSE;
                              }
                              $_t1 = explode(" ",$_tmpaCode[0]);
                              $_t2 = explode(" ",$_tmpaCode[1]);
                              $_t3 = explode(" ",$_tmpaCode[2]);
                              for( $i=0; $i<count($_t1); $i++ ){
                                    for( $j=0; $j<count($_t2); $j++ ){
                                        for( $k=0; $k<count($_t3); $k++ ){
                                            if( $_t1[$i] != $_t2[$j] && $_t1[$i] != $_t3[$k] && $_t2[$j] != $_t3[$k] ){
                                                $_tmpNums++;
                                            }
                                        }
                                    }
                              }
                              break;
                case 'SDZU3': 
                              $_tmpaCode = explode("|",$aProject['codes']);
                              $sss = count($_tmpaCode);
                              if( count($_tmpaCode) < 3 )
                              {
                                  return FALSE;
                              }
                              $_tmpNums = $sss*($sss-1)*($sss-2)/6;
                              break;
                case 'SDZX2': 
                              $_tmpaCode = explode("|",$aProject['codes']);
                              if( count($_tmpaCode) != 2 )
                              {
                                  return FALSE;
                              }
                              $_t1 = explode(" ",$_tmpaCode[0]);
                              $_t2 = explode(" ",$_tmpaCode[1]);
                              for( $i=0; $i<count($_t1); $i++ ){
                                    for( $j=0; $j<count($_t2); $j++ ){
                                        if( $_t1[$i] != $_t2[$j] ){
                                            $_tmpNums++;
                                        }
                                    }
                              }
                              break;
                case 'SDZU2': 
                              $_tmpaCode = explode("|",$aProject['codes']);
                              $sss = count($_tmpaCode);
                              if( count($_tmpaCode) < 2 )
                              {
                                  return FALSE;
                              }
                              $_tmpNums = $sss*($sss-1)/2;
                              break;
                case 'SDBDW': 
                case 'SDDWD': 
                case 'SDDDS':
                case 'SDCZW': 
                case 'SDRX1': 
                              $_tmpaCode = explode("|",$aProject['codes']);
                              if( count($_tmpaCode) < 1 )
                              {
                                  return FALSE;
                              }
                              $_tmpNums = $this->_GetCombinCount( count($_tmpaCode),1 );
                              break;
                case 'SDRX2': 
                              $_tmpaCode = explode("|",$aProject['codes']);
                              if( count($_tmpaCode) < 2 )
                              {
                                  return FALSE;
                              }
                              $_tmpNums = $this->_GetCombinCount( count($_tmpaCode),2 );
                              break;
                case 'SDRX3':
                              $_tmpaCode = explode("|",$aProject['codes']);
                              if( count($_tmpaCode) < 3 )
                              {
                                  return FALSE;
                              }
                              $_tmpNums = $this->_GetCombinCount( count($_tmpaCode),3 );
                              break;
                case 'SDRX4':
                              $_tmpaCode = explode("|",$aProject['codes']);
                              if( count($_tmpaCode) < 4 )
                              {
                                  return FALSE;
                              }
                              $_tmpNums = $this->_GetCombinCount( count($_tmpaCode),4 );
                              break;
                case 'SDRX5':
                              $_tmpaCode = explode("|",$aProject['codes']);
                              if( count($_tmpaCode) < 5 )
                              {
                                  return FALSE;
                              }
                              $_tmpNums = $this->_GetCombinCount( count($_tmpaCode),5 );
                              break;
                case 'SDRX6':
                              $_tmpaCode = explode("|",$aProject['codes']);
                              if( count($_tmpaCode) < 6 )
                              {
                                  return FALSE;
                              }
                              $_tmpNums = $this->_GetCombinCount( count($_tmpaCode),6 );
                              break;
                case 'SDRX7':
                              $_tmpaCode = explode("|",$aProject['codes']);
                              if( count($_tmpaCode) < 7 )
                              {
                                  return FALSE;
                              }
                              $_tmpNums = $this->_GetCombinCount( count($_tmpaCode),7 );
                              break;
                case 'SDRX8': 
                              $_tmpaCode = explode("|",$aProject['codes']);
                              if( count($_tmpaCode) < 8 )
                              {
                                  return FALSE;
                              }
                              $_tmpNums = $this->_GetCombinCount( count($_tmpaCode),8 );
                              break;
                default     : $_tmpNums = 0;
                              break;
            }
        }
        $_tmpMoney = $_tmpNums * 2 * $aProject['times'] * $aProject['rate'];
        if( abs($_tmpMoney-$aProject['money']) > 0.00001 )
        {
            return FALSE;
        }
        return TRUE;
    }
}
?>