<?php
/**
 * 文件 : /_app/model/admingroup.php
 * 功能 : 模型 - 管理员日志
 *  
 * @author	   Saul,Tom
 * @version   1.1.0
 * @package   passportadmin
 * @since     2009-06-15
 * 
 */
class model_adminlog extends basemodel 
{
    /* ---------------------------------------- CRUD ---------------------------------------- */
    /**
     * @author SAUL 050917
     * @param string $title
     * @param string $content
     * @param string $controller
     * @param string $action
     * @package int  $iTypeid   日志类型(0=系统自动日志, 1=特殊日志)
     * @return BOOL
     */
	function insert( $sTitle, $sContent, $sController, $sAction, $iTypeid=0 )
	{
		if( empty($sTitle) || empty($sController) || empty($sAction) )
		{
			return FALSE;
		}
		if( empty($sContent) )
		{
			$sContent = '';
		}
		$aInsertArr['typeid']       = intval($iTypeid);
		$aInsertArr['adminid']      = isset($_SESSION["admin"]) ? $_SESSION["admin"] : 0;
		$aInsertArr['clientip']     = getRealIP();
		$aInsertArr['proxyip']      = $_SERVER['REMOTE_ADDR'];
		$aInsertArr['times']        = date("Y-m-d H:i:s", time());
		$aInsertArr['querystring']  = getUrl();
		$aInsertArr['controller']   = daddslashes($sController);
		$aInsertArr['actioner']     = daddslashes($sAction);
		$aInsertArr['title']        = daddslashes($sTitle);
		$aInsertArr['content']      = daddslashes($sContent);
		$aInsertArr['requeststring']= addslashes( serialize( $_REQUEST ));
		$this->oDB->insert( 'adminlog', $aInsertArr );
		return TRUE;
	}



	/**
	 * 根据日志ID, 获取管理员日志详情
	 * @author tom
	 * @return -1 | array
	 */
	function getAdminLogInfo( $iLogId )
	{
	    $aResult = $this->oDB->getOne( "SELECT b.*,a.`adminname` FROM `adminuser` AS a LEFT JOIN `adminlog` ".
	                            " AS b ON a.`adminid`=b.`adminid`  WHERE b.`entry` = '".intval($iLogId)."' LIMIT 1" );
	    if( 0 == $this->oDB->ar() )
	    {
	        return -1; // 获取管理员日志详情失败
	    }
	    else
	    {
	        return $aResult;
	    }
	}



	/**
	 * 根据日志ID, 获取用户日志详情
	 * @author tom
	 * @return -1 | array
	 */
	function getUserLogInfo( $iLogId )
	{
	    $aResult = $this->oDB->getOne( "SELECT b.*,a.`username` FROM `usertree` AS a LEFT JOIN `userlog` ".
	                            " AS b ON a.`userid`=b.`userid`  WHERE b.`entry` = '".intval($iLogId)."' LIMIT 1" );
	    if( 0 == $this->oDB->ar() )
	    {
	        return -1; // 获取用户日志详情失败
	    }
	    else
	    {
	        return $aResult;
	    }
	}


	/**
	 * 清除日志
	 * @author Saul 090604
	 * @param int $day
	 * @return int
	 */
	function clearlog( $iDay )
	{
		if( !is_numeric($iDay) )
		{
			return FALSE;
		}
		$this->oDB->query("DELETE FROM `adminlog` WHERE `times`<'".date("Y-m-d 00:00:00",strtotime("-". intval($iDay)." days"))."'");
		return ($this->oDB->errno()==0);
	}



	/**
	 * 备份日志(分页机制)
	 * @author SAUL
	 * @param int $iDay
	 * @param string $sFile
	 */
	function baklog( $iDay, $sFile )
	{
		if( !is_numeric($iDay) ) 
		{
			return FALSE;
		}
		$numLog = $this->oDB->getOne("SELECT count(*) as `count_log` FROM `adminlog` WHERE `times`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		$num = $numLog['count_log'];
		$size = 50000;
		$pages = ceil($num/$size);
		
		$gz = gzopen($sFile,'w9');		
		for($page =0 ; $page < $pages; $page++)
		{
			$FileContent = "";
			$logs = $this->oDB->getAll("SELECT * FROM `adminlog` WHERE `times`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."' limit ".($page*$size).",".$size);		
			foreach($logs as $log)
			{
				$keys =array();
				$values =array();
				foreach( $log as $key=>$value )
				{
					$keys[] = "`".$key."`";
					if(is_null($value))
					{
						$values[] = 'NULL';
					}
					else 
					{
						$values[] = "'".$this->oDB->es($value)."'";	
					}				
				}
				$sql = "insert into `adminlog` (".join(",",$keys).") values (".join(",",$values).");";
				unset($keys);
				unset($values);
				$FileContent .= $sql."\n";
			}
			gzwrite($gz, $FileContent);
		}		
		gzclose($gz);
		unset($FileContent);
		$this->clearLog($iDay);
		return TRUE;
	}



	/**
	 * 获取管理员日志列表
	 * @author tom
	 * @return array
	 */
	public function & getAdminLogList( $sFields = "*" , $sCondition = "1", $iPageRecords = 25 , $iCurrPage = 1)
	{
	    $sTableName = ' `adminuser` b LEFT JOIN `adminlog` a ON a.`adminid` = b.`adminid` ';
	    $sFields    = ' a.`entry`, a.`typeid`, a.`clientip`, a.`proxyip`, a.`times`, a.`controller`, a.`actioner`, a.`title`, b.`adminname` ';
	    return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, $iCurrPage, ' ORDER BY a.`entry` DESC ' );
	}
	
	
	/**
	 * 获取用户日志列表
	 * @author tom
	 * @return array
	 */
	public function & getUserLogList( $sFields = "*" , $sCondition = "1", $iPageRecords = 25 , $iCurrPage = 1)
	{
	    $sTableName = ' `usertree` b LEFT JOIN `userlog` a ON a.`userid` = b.`userid` ';
	    $sFields    = ' a.`entry`, a.`clientip`, a.`proxyip`, a.`times`, a.`controller`, a.`actioner`, a.`title`, b.`username` ';
	    return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, $iCurrPage, ' ORDER BY a.`entry` DESC ' );
	}
	
	
	/**
	 * 获取支付接口分账户余额修正日志列表
	 * @return array
	 * 4/28/2010
	 */
	public function & getBanlanceChangeLogList( $sFields = "*" , $sCondition = "1", $iPageRecords = 25 , $iCurrPage = 1)
	{
	    $sTableName = ' `payport_acc_balance_logs` ';
	    $sFields    = ' * ';
	    return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, $iCurrPage, ' ORDER BY `id` DESC ' );
	}
	
	/**
	 * 获取在线充值所有操作记录日志列表
	 * @return array
	 * 4/28/2010
	 */
	public function & getOnlineLoadLogList( $sFields = "*" , $sCondition = "1", $iPageRecords = 25 , $iCurrPage = 1)
	{
	    $sTableName = ' `online_load_logs` ';
	    $sFields    = ' * ';
	    return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, $iCurrPage, ' ORDER BY `id` DESC ' );
	}
}
?>