<?php
/**
 * 文件 : /_app/model/onlineloadclear.php
 * 功能 : 模型 - 在线充值相关表定期清理
 * 
 * @author    Jim    6/22/2010
 * @version   0.1
 * @package   passportadmin
 */

class model_paymentclear extends basemodel 
{
    // 构造函数
	function __construct( $aDBO=array() )
	{
		parent::__construct( $aDBO );
	}

    
	/**
	 * 保存与删除 
	 * @param integer $iDay
     * @param string  $sPath
     * @return bool
     * 
     * 涉及表 online_load online_load_data online_load_logs
     * 
     * 6/22/2010
	 */
	public function backandclear($iDay,$sPath)
	{
		if( !is_numeric($iDay) ) 
		{
			return FALSE;
		}
		$iDay = intval($iDay);
		
		if( $iDay < 5 )
		{
			$iDay = 5;
		}
		$sDay = date("Ymd");
		// bak & delete online_load
    	$numCodes = $this->oDB->getOne("SELECT COUNT(id) AS `numCodes` FROM `online_load` "
		                        ." WHERE `utime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		$num   = $numCodes['numCodes'];
		$size  = 50000;
		$pages = ceil( $num/$size );
		$sFile = $sPath.DS.$sDay."_onlineload.gz";
		makeDir(dirname($sFile));
		$gz    = gzopen($sFile,'w9');
		for( $page =0 ; $page < $pages; $page++ )
		{
			$FileContent = "";
			$aSales = $this->oDB->getAll("SELECT * FROM `online_load` "
		                                ." WHERE `utime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))
		                                ."' LIMIT ".($page*$size).",".$size);		
			foreach( $aSales as $aSale )
			{
				$keys   = array();
				$values = array();
				foreach( $aSale as $key=>$value )
				{
					$keys[] = "`".$key."`";
					if( is_null($value) )
					{
						$values[] = 'NULL';
					}
					else 
					{
						$values[] = "'".$this->oDB->es($value)."'";	
					}
				}
				$sql = "INSERT INTO `online_load` (".join(",",$keys).") VALUES (".join(",",$values).");";
				unset($keys);
				unset($values);
				$FileContent .= $sql."\n";
			}
			gzwrite($gz, $FileContent);
		}
		gzclose($gz);
		//删除
		$this->oDB->query("DELETE FROM `online_load` WHERE `utime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		
		
		// bak & delete online_load_data
    	$numCodes = $this->oDB->getOne("SELECT COUNT(id) AS `numCodes` FROM `online_load_data` "
		                        ." WHERE `utime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		$num   = $numCodes['numCodes'];
		$size  = 50000;
		$pages = ceil( $num/$size );
		$sFile = $sPath.DS.$sDay."_onlineloaddata.gz";
		makeDir(dirname($sFile));
		$gz    = gzopen($sFile,'w9');
		for( $page =0 ; $page < $pages; $page++ )
		{
			$FileContent = "";
			$aSales = $this->oDB->getAll("SELECT * FROM `online_load_data` "
		                                ." WHERE `utime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))
		                                ."' LIMIT ".($page*$size).",".$size);		
			foreach( $aSales as $aSale )
			{
				$keys   = array();
				$values = array();
				foreach( $aSale as $key=>$value )
				{
					$keys[] = "`".$key."`";
					if( is_null($value) )
					{
						$values[] = 'NULL';
					}
					else 
					{
						$values[] = "'".$this->oDB->es($value)."'";	
					}
				}
				$sql = "INSERT INTO `online_load_data` (".join(",",$keys).") VALUES (".join(",",$values).");";
				unset($keys);
				unset($values);
				$FileContent .= $sql."\n";
			}
			gzwrite($gz, $FileContent);
		}
		gzclose($gz);
		//删除
		$this->oDB->query("DELETE FROM `online_load_data` WHERE `utime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		
		
		// bak & delete online_load_logs
    	$numCodes = $this->oDB->getOne("SELECT COUNT(id) AS `numCodes` FROM `online_load_logs` "
		                        ." WHERE `log_time`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		$num   = $numCodes['numCodes'];
		$size  = 50000;
		$pages = ceil( $num/$size );
		$sFile = $sPath.DS.$sDay."_onlineloadlogs.gz";
		makeDir(dirname($sFile));
		$gz    = gzopen($sFile,'w9');
		for( $page =0 ; $page < $pages; $page++ )
		{
			$FileContent = "";
			$aSales = $this->oDB->getAll("SELECT * FROM `online_load_logs` "
		                                ." WHERE `log_time`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))
		                                ."' LIMIT ".($page*$size).",".$size);		
			foreach( $aSales as $aSale )
			{
				$keys   = array();
				$values = array();
				foreach( $aSale as $key=>$value )
				{
					$keys[] = "`".$key."`";
					if( is_null($value) )
					{
						$values[] = 'NULL';
					}
					else 
					{
						$values[] = "'".$this->oDB->es($value)."'";	
					}
				}
				$sql = "INSERT INTO `online_load_logs` (".join(",",$keys).") VALUES (".join(",",$values).");";
				unset($keys);
				unset($values);
				$FileContent .= $sql."\n";
			}
			gzwrite($gz, $FileContent);
		}
		gzclose($gz);
		//删除
		$this->oDB->query("DELETE FROM `online_load_logs` WHERE `log_time`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		
		
	}
	
	
	
	
	/**
	 * 保存与删除 
	 * @param integer $iDay
     * @param string  $sPath
     * @return bool
     * 
     * 涉及表 pay_out_details pay_out_operate_detail
     * 
     * 6/22/2010
	 */
	public function backandclearPayOut($iDay,$sPath)
	{
		if( !is_numeric($iDay) ) 
		{
			return FALSE;
		}
		$iDay = intval($iDay);
		
		if( $iDay < 5 )
		{
			$iDay = 5;
		}
		$sDay = date("Ymd");
		// bak & delete pay_out_details
    	$numCodes = $this->oDB->getOne("SELECT COUNT(id) AS `numCodes` FROM `pay_out_details` "
		                        ." WHERE `utime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		$num   = $numCodes['numCodes'];
		$size  = 50000;
		$pages = ceil( $num/$size );
		$sFile = $sPath.DS.$sDay."_payoutdetails.gz";
		makeDir(dirname($sFile));
		$gz    = gzopen($sFile,'w9');
		for( $page =0 ; $page < $pages; $page++ )
		{
			$FileContent = "";
			$aSales = $this->oDB->getAll("SELECT * FROM `pay_out_details` "
		                                ." WHERE `utime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))
		                                ."' LIMIT ".($page*$size).",".$size);		
			foreach( $aSales as $aSale )
			{
				$keys   = array();
				$values = array();
				foreach( $aSale as $key=>$value )
				{
					$keys[] = "`".$key."`";
					if( is_null($value) )
					{
						$values[] = 'NULL';
					}
					else 
					{
						$values[] = "'".$this->oDB->es($value)."'";	
					}
				}
				$sql = "INSERT INTO `pay_out_details` (".join(",",$keys).") VALUES (".join(",",$values).");";
				unset($keys);
				unset($values);
				$FileContent .= $sql."\n";
			}
			gzwrite($gz, $FileContent);
		}
		gzclose($gz);
		//删除
		$this->oDB->query("DELETE FROM `pay_out_details` WHERE `utime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		
		
		// bak & delete pay_out_operate_detail
    	$numCodes = $this->oDB->getOne("SELECT COUNT(id) AS `numCodes` FROM `pay_out_operate_detail` "
		                        ." WHERE `utime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		$num   = $numCodes['numCodes'];
		$size  = 50000;
		$pages = ceil( $num/$size );
		$sFile = $sPath.DS.$sDay."_payoutoperatedetail.gz";
		makeDir(dirname($sFile));
		$gz    = gzopen($sFile,'w9');
		for( $page =0 ; $page < $pages; $page++ )
		{
			$FileContent = "";
			$aSales = $this->oDB->getAll("SELECT * FROM `pay_out_operate_detail` "
		                                ." WHERE `utime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))
		                                ."' LIMIT ".($page*$size).",".$size);		
			foreach( $aSales as $aSale )
			{
				$keys   = array();
				$values = array();
				foreach( $aSale as $key=>$value )
				{
					$keys[] = "`".$key."`";
					if( is_null($value) )
					{
						$values[] = 'NULL';
					}
					else 
					{
						$values[] = "'".$this->oDB->es($value)."'";	
					}
				}
				$sql = "INSERT INTO `pay_out_operate_detail` (".join(",",$keys).") VALUES (".join(",",$values).");";
				unset($keys);
				unset($values);
				$FileContent .= $sql."\n";
			}
			gzwrite($gz, $FileContent);
		}
		gzclose($gz);
		//删除
		$this->oDB->query("DELETE FROM `pay_out_operate_detail` WHERE `utime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		
	}
	
	
}
?>