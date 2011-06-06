<?php

/* 请按实际情况修改以下信息 */
$GLOBALS['aSysDbServer']['master'] = array(
	'DBHOST' => '127.0.0.1',
	'DBPORT' => '3306',
	'DBUSER' => 'root',
	'DBPASS' => '123456',
	'DBNAME' => 'passport',
	'DBCHAR' => 'UTF8',
);



















$db = new db($GLOBALS['aSysDbServer']['master']);

class db extends mysqli
{
	// 默认配置参数
	private $sDbHost = "localhsot";	// 数据库地址  [string]
	private $iDbPort = 3306;		// 数据库端口  [int]
	private $sDbUser = "root";		// 数据库用户  [string]
	private $sDbPass = '';			// 数据库密码  [string]
	private $sDbName = '';			// 数据库名称  [string]
	private $sDbChar = "utf8";		// 数据库字符集  [string]

	// 对象结果集
	private $oResults;				         // 数据查询结果集合 [object] mysqli_result 
	private $bTransactionInProgress = FALSE; // 是否有事务未被提交

	// Mysqli 底层相关调试参数 (可用于分析用户行为, 做性能调试&分析)
	private $bDevelopMode = FALSE;           // 开发模式, 记录显示SQL错误
	
	private $iProcessedSqlCount = 0;         // 已执行的 SQL 数量
	private $bRecordProcessTime = FALSE;     // 是否记录执行 SQL 的总计时间
	private $fProcessedTime = 0.000000;      // 执行 SQL 的总计时间, 8 位精度

	private $sDbHash = '';                   // hash 值 md5(...)
	private $bDbDiffTimeEnable = FALSE;      // 触发器缓存时, 是否与 DB 服务器进行时间效验
	private $iWebTimeDiffSqlTime = '--';     // WEB 服务器与 MYSQL 服务器的时间差 (秒)
	private $sCacheBasePath = '';            // 查询缓存文件存放基本路径


	/**
	 * 构造函数
	 * 
	 * /---code php
	 *    $aDBO = array( "DBHOST" => 'localhost', 
     *                   "DBPORT" => 3306, 
     *                   "DBUSER" => 'root', 
     *                   "DBPASS" => '121212',
     *                   "DBNAME" => 'cpdgdb_090405',
     *                );
     *    $oDb = new db($aDBO);
     * \---
	 * 
	 * @param array $aDBO 数据库连接设置数组
	 * 
	 */
	public function __construct( $aDBO=array() )
	{
	    //echo '-----> 实例化, 用于调试 DB 类被实例化多少次<br/>';
		$this->sDbHost = isset($aDBO["DBHOST"]) ? $aDBO["DBHOST"] : "localhost";
		$this->iDbPort = isset($aDBO["DBPORT"]) ? $aDBO["DBPORT"] : 3306;
		$this->sDbUser = isset($aDBO["DBUSER"]) ? $aDBO["DBUSER"] : "root";
		$this->sDbPass = isset($aDBO["DBPASS"]) ? $aDBO["DBPASS"] : "";
		$this->sDbName = isset($aDBO["DBNAME"]) ? $aDBO["DBNAME"] : "";
		$this->sDbChar = isset($aDBO["DBCHAR"]) ? $aDBO["DBCHAR"] : "UTF8";
		// 查询缓存存放路径  /tmp/query_caches/
		//$this->sCacheBasePath = isset($aDBO["CACHEDIR"]) ? $aDBO["CACHEDIR"] : A_DIR.DS.'tmp'.DS.'query_caches'.DS;
		try
		{
			parent::connect($this->sDbHost, $this->sDbUser, $this->sDbPass, $this->sDbName, $this->iDbPort);
		}
		catch( Exception $e )
		{
			die('db.Connect Failed #1: '. $e->getMessage() .' '. mysqli_connect_error() );
		}
		if( mysqli_connect_errno() <> 0 )
		{
			 die( "db.Connect Failed #2: ". mysqli_connect_error() );
			 exit;
		}
		parent::set_charset( $this->sDbChar );

        // 考虑到 CLI 模式下没有这些变量, 屏蔽其错误
        $sTmpHash = isset($_SERVER['SERVER_ADDR'])&&$_SERVER['SERVER_PORT'] ? 
                        ($_SERVER['SERVER_ADDR']. $_SERVER['SERVER_PORT']) : '';
		$this->sDbHash = md5( $sTmpHash. $this->sDbHost . $this->iDbPort . $this->sDbUser . $this->sDbPass . $this->sDbName );
		unset($sTmpHash);
		$this->bRecordProcessTime = false;
		$this->bDevelopMode       = TRUE;
		if( $this->bDevelopMode == TRUE )
		{
		    mysqli_report( MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX );
		}
	}


	// 析构函数
	function __destruct()
	{
	    // 检查未被处理的事务, 未处理事务将被 RollBack
	    if( $this->bTransactionInProgress == TRUE )
	    {
	        $this->doRollback();
	    }

        if( isset($this->thread_id) )
        { // 只有成功连接才进行释放, (避免连接失败时,导致的错误释放)
            $this->close();
        }
		unset($this->sDbHost);
		unset($this->sDbUser);
		unset($this->sDbPass);
		unset($this->sDbName);
		unset($this->iDbPort);
	}


	/* 基本功能函数 ********************************************************/
	/**
	 * 返回受影响的行数 (SELECT,INSERT,UPDATE,REPLACE,DELETE)
	 * http://cn.php.net/manual/en/mysqli.affected-rows.php
	 * @return int
	 */
	public function affectedRows()
	{
		return $this->affected_rows;
	}

	/**
	 * affectedRows 函数的简写, 返回受影响行数
	 * @return int
	 */
	public function ar()
	{
	    return $this->affected_rows;
	}

	/**
	 * Mysqli 出错编号
	 * @return int
	 */ 
	public function errno()
	{
		return $this->errno;
	}

	/**
	 * Mysqli 出错内容
	 * @return string
	 */ 
	public function error()
	{
		return $this->error;
	}

	/**
	 * 获取本次执行 SQL 的总计时间
	 * @return double
	 */
	public function getProcessTime()
	{
	    return $this->fProcessedTime;
	}

	/**
	 * 获取本次执行 SQL 的数量
	 * @return int
	 */
    public function getProcessCount()
    {
        return $this->iProcessedSqlCount;
    }

	/**
	 * 获取线程ID
	 * @return int
	 */
	public function getThreadId()
	{
		return $this->thread_id;
	}

	/**
	 * 安全编码
	 * @return string
	 */
	public function escapeString( $sString )
	{
	    if( MAGIC_QUOTES_GPC == TRUE )
	    {// 如果已被自动转义, 则直接返回
	        return $sString;
	    }
	    else
	    {
		    return parent::real_escape_string($sString);
	    }
	}

	/**
	 *  安全编码的简写
	 * @return string
	 */
    public function es( $sString )
	{
		return $this->escapeString($sString);
	}



	/**
	 * Mysqli 的执行命令函数
	 * @return object
	 */
	public function query( $sSql )
	{    
	    // step 01, SQL 日志记录操作, 只有开启全局日志开关,并且开启日志记录到文件开关, 才生效
	    if( isset($GLOBALS['oLogs']) && is_object($GLOBALS['oLogs']) && 
	        (bool)(intval(A::getIni('error/trigger_error')) & APPLE_ON_ERROR_LOG) &&
	        (bool)(intval(A::getIni('error/trigger_error')) & APPLE_LOGS_SQL_TO_FILE)  )
	    {
	        /* @var $GLOBALS['oLogs'] logs */
            //tom 091209 关闭所有SQL记录
            //$GLOBALS['oLogs']->addDebug( $sSql, 'ALLSQL' );
	    }

	    // step 02, 执行 SQL
	    $oResult = NULL;
		try
		{
		    if( $this->bRecordProcessTime == TRUE )
			{
			    $dTimeStart = getMicroTime();
			}
			$oResult = parent::query( trim($sSql) );
			if( $this->errno() > 0 )
			{
			    // TODO: 加入错误日志记录 090728
			}
			//echo 'SQL='.trim($sSql).'<br/>';
			$this->iProcessedSqlCount += 1; // 处理SQL数量 +1
			if( $this->bRecordProcessTime == TRUE )
			{
			    $this->fProcessedTime += getTimeDiff( getMicroTime() - $dTimeStart , 8 );
			}

			if( is_object($oResult) )
			{
				$this->oResults = $oResult;
			}
		}
		catch( Exception $e )
		{
    		// 日志记录操作, 只有开启全局日志开关,并且开启日志记录到文件开关, 才生效
    	    if( isset($GLOBALS['oLogs']) && is_object($GLOBALS['oLogs']) && 
    	        (bool)(intval(A::getIni('error/trigger_error')) & APPLE_ON_ERROR_LOG) &&
    	        (bool)(intval(A::getIni('error/trigger_error')) & APPLE_LOGS_SQL_TO_FILE)  )
    	    {
    	        /* @var $GLOBALS['oLogs'] logs */
    	        $sSql .= ' | Exception: Errno=' .$this->errno().' Error='.$this->error();
                $GLOBALS['oLogs']->addDebug( $sSql, 'SqlException' );
    	    }
    	    // 如果为开发者模式, 则显示错误信息
    	    if( TRUE == (bool)$this->bDevelopMode )
    	    {
    	        //A::halt('From Class.Db.Exception: Errno=' .$this->errno().' Error='.$this->error());
    	        echo 'From Class.Db.Exception: Errno=' .$this->errno().' Error='.$this->error();
    	    }
		}
		return $oResult;
	}

	/**
	 * 将结果集合转化为数组
	 * $sResultType => MYSQLI_NUM | MYSQLI_ASSOC | MYSQLI_BOTH
	 * @return array
	 */
	public function fetchArray( $sResultType = NULL )
	{
		$sResultType = is_null($sResultType) ? MYSQLI_ASSOC : $sResultType;
		return $this->oResults->fetch_array($sResultType);
	}


	/**
	 * 将结果集合转化为下标数组 MYSQLI_ASSOC
	 * @return array
	 */
	public function fetchAssoc()
	{
		return $this->oResults->fetch_assoc();
	}

	/**
	 * 从结果集中取得列信息并作为对象返回,一次显示1列的信息
	 */
	public function fetchField() 
	{
		return $this->oResults->fetch_field();
	}

	/**
	 * 从结果集中取得所有的列信息并作为数组返回
	 */
	public function fetchFields()
	{
		return $this->oResults->fetch_fields();
	}

	/**
	 * 将结果集合转化为对象
	 *    while ($obj = $result->fetch_object()) {
	 *         printf ("%s (%s)\n", $obj->Name, $obj->CountryCode);
	 *    }
	 */
	public function fetchObject()
	{
		return $this->oResults->fetch_object();
	}

	/**
	 * 将结果集合转化为数字数组
	 */
	public function fetchRow()
	{
		return $this->oResults->fetch_row();
	}

	/**
	 * 返回当前结果集的列个数
	 */
	public function fieldCount()
	{
		return $this->oResults->field_count;
	}

	/**
	 * 列偏移量
	 */ 
	public function fieldSeek( $offset = 1 )
	{
		$offset = is_numeric($offset) ? $offset : 0;
		return $this->oResults->field_seek( $offset );
	}

	// 返回当前结果在结果集的偏移量
	public function fieldTell()
	{
		return $this->oResults->current_field;
	}

	// 获取MYSQL客户端信息
	public function getClientInfo() 
	{
    	return mysqli_get_client_info();
    }

    // 获取MYSQL客户端版本号
    public function getClientVersion()
    {
    	return mysqli_get_client_version();
    }

    // 获取MYSQL主机信息
    public function getHostInfo()
    {
		return $this->host_info;
    }

    // 获取MYSQL协议版本信息
    public function getProtoInfo()
    {
        return $this->protocol_version;
    }

    // 获取MYSQL服务端信息
    public function getServerInfo() 
    {
    	return $this->server_info;
    }

    // 获取MYSQL服务端版本号
    public function getServerVersion()
    {
    	return $this->server_version;
    }

    // 获取MYSQL所有执行信息
    public function info()
    {
    	return $this->info;
    }

	// 取得上一步 INSERT 操作产生的 ID 
	public function insertId()
	{
		return $this->insert_id;
	}

	// 返回在一个结果集中字段的个数
	public function numFields()
	{
		return $this->oResults->field_count;
	}

	/*
	 * 返回在一个结果集中所有的行数, 仅用于 SELECT
	 */
	public function numRows()
	{
		return $this->oResults->num_rows;
	}

	/**
	 * 是否开启MYSQL内部调试函数
	 *     - MYSQLI_REPORT_OFF
	 *     - MYSQLI_REPORT_ERROR
	 *     - MYSQLI_REPORT_STRICT
	 *     - MYSQLI_REPORT_INDEX
	 *     - MYSQLI_REPORT_ALL
	 */ 
	public function report( $flags )
	{
		if( in_array( $flags,
					  array( MYSQLI_REPORT_OFF,MYSQLI_REPORT_ERROR,
					         MYSQLI_REPORT_STRICT,MYSQLI_REPORT_INDEX,MYSQLI_REPORT_ALL )
					 ) )
		{
			$flags = MYSQLI_REPORT_OFF;
		}
		return mysqli_report($flags);
	}





	/**************************************************************
	 * 事务处理 (事务开始，回滚，提交)
	 * ************************************************************
	 */
	/**
	 * 开始事务
	 * @return BOOL
	 */ 
	public function doTransaction()
	{
	    $bFlag = $this->autocommit(FALSE);
	    $this->bTransactionInProgress = TRUE; // 有事务未被提交
	    register_shutdown_function( array($this, "__do_shutdown_check") );
	    return $bFlag;
	}

    public function __do_shutdown_check()
    {
        if( $this->bTransactionInProgress )
        {
            $this->doRollback();
        }
    }

	/**
	 * 回滚事务
	 * @return BOOL
	 */ 
	public function doRollback()
	{
	    $bFlag = $this->rollback();
	    $this->autocommit(TRUE);
	    $this->bTransactionInProgress = FALSE;
	    return $bFlag;
	}
	
	/**
	 * 提交事务
	 * @return BOOL
	 */ 
	public function doCommit()
	{
	    $bFlag = $this->commit();
	    $this->autocommit(TRUE);
	    $this->bTransactionInProgress = FALSE;
	    return $bFlag;
	}





	/**************************************************************
	 * CRUD 模式
	 *    [C]  -  create   (insert)
	 *    [R]  -  read     (select)
	 *    [U]  -  update
	 *    [D]  -  delete
	 * ************************************************************
	 */
	
	/**
	 * C insert data 插入数据
	 * 
	 * /---code php
	 *    $aData = array( 
     *		  'user_id'  => '100',
     *        'username' => 'tom100',
     *           );
     *     $oDb->insert( 'users', $aData);
     * \---
     * 
     * @param string $sTableName 表名
     * @param array  $aData  关联数组 array( 'uid'=>100, 'uname'=>'tom' )
     * @return int insertId | FALSE
	 */ 
	public function insert( $sTableName, $aData )
	{
		$this->query( $this->buildSqlString( $sTableName, $aData, 'insert' ) );
		if( $this->errno() > 0 )
		{
		    return FALSE;
		}
		else 
		{
		    return $this->insertId();
		}
	}


	/**
	 * R select Data 获取一条数据
	 * 
	 *  /---code php
	 *     $array = $oDb->getOne( 'select * from users where user_id = 100' );
     *     print_rr($array);
     *  \---
     * 
     * @param string $sSql SQL语句
     * @return array
	 */ 
	public function & getOne( $sSql )
	{
	    $aReturn = array();
		$this->query( $sSql );
		if( $this->ar() && $this->errno()<=0 )
		{
		    $aReturn = $this->fetchArray();
		}
		return $aReturn;
	}


	/**
	 * 获取结果集的所有数据
	 * 
	 *  /---code php
	 *     $array = $oDb->getAll( 'select * from users where 1>0' );
     *     print_rr($array);
	 *  \---
	 * 
	 * @param string $sSql SQL语句
	 * @return array | NULL
	 */ 
	public function & getAll( $sSql )
	{
		$aResults = array();
		$aRow = array();
		$this->query( $sSql );
		if( !$this->ar() || $this->errno()>0 )
		{
		    return $aResults;
		}
		while( $aRow = $this->oResults->fetch_assoc() ) 
		{
			$aResults[] = $aRow;
		}
		unset($aRow);
		return $aResults;
	}

	
	/**
	 * U update data 修改数据
     *
	 *  /---code php
	 *  // UPDATE `users` SET `user_id` = '100', `username` = 'tom199' WHERE user_id = 100
	 *       $aData = array( 
     *		     'user_id' => '100',
     *           'username' => 'tom199',
     *       );
     *      $array = $oDb->update( 'users', $aData, 'user_id = 100' );
	 *  \---
	 *  
	 * @param string $sTableName 表名
	 * @param array  $aData      关联数组 array( 'uid'=>100, 'uname'=>'tom199' )
	 * @param string $sCondition 条件语句(不含WHERE关键字)
	 * @return int   affectedRows
	 */ 
	public function update( $sTableName, $aData, $sCondition = '1<0' )
	{
//logdump($this->buildSqlString( $sTableName, $aData, 'update', $sCondition ));
		$this->query( $this->buildSqlString( $sTableName, $aData, 'update', $sCondition ) );
	    if( $this->errno() > 0 )
		{
		    return FALSE;
		}
		else 
		{
		    return $this->affectedRows();
		}
	}


	/**
	 * D delete data 删除数据
	 * 
	 * /---code php
	 * // DELETE FROM `users` WHERE username='tom199'
	 * $oDb->delete( 'users', 'username=\'tom199\'' );
	 * \---
	 * 
	 * @param string $sTableName 表名
	 * @param string $sCondition 条件语句(不含WHERE关键字)
	 * @return int   affectedRows
	 */ 
	public function delete( $sTableName, $sCondition = '1' )
	{
		$temp_sSql = $this->buildSqlString( $sTableName, '', 'delete', $sCondition );
		$this->query( $temp_sSql );
	    if( $this->errno() > 0 )
		{
		    return FALSE;
		}
		else 
		{
		    return $this->affectedRows();
		}
	}


	// 构建SQL语句
	private function buildSqlString( $sTableName, $aData = '', $sAction = 'insert', $sParameters = '1' )
	{		
		// 构建插入的SQL语句
        if( $sAction == 'insert' ) 
        {
	        // 检测Data是否为合法参数
			if( FALSE == is_array($aData) || TRUE == empty($aData) ) 
			{
				A::halt("class.db.buildSqlString.insert.parameter data must be a Array");
				exit;
			}
            $temp_sQuery = 'INSERT INTO `' . $sTableName . '` (';
            $temp_sKeystr = '';
            $temp_sValstr = '';
            while( list( $columns, $value ) = each( $aData ) ) 
            {
                $temp_sKeystr .= '`' . $columns . '`, ';
                switch( ( string )$value ) 
                {
                    case 'now()':
                        $temp_sValstr .= 'now(), ';
                        break;
                    case 'NULL':
                        $temp_sValstr .= 'NULL, ';
                        break;
                    default:
                        $temp_sValstr .= '\'' . $value  . '\', ';
                        break;
                }
            }
            $temp_sQuery .= substr( $temp_sKeystr, 0, -2 ) . ') VALUES (' . substr( $temp_sValstr, 0, -2 ) . ')';
        } 
        
        // 构建修改的SQL语句
        elseif( $sAction == 'update' )
        {
        	// 检测Data是否为合法参数
			if( FALSE == is_array($aData) || TRUE == empty($aData) ) 
			{
				A::halt("db.class.buildSqlString.update.parameter data must be a Array");
				exit;
			}
            $temp_sQuery = 'UPDATE `' . $sTableName . '` SET ';
            while( list( $columns, $value ) = each( $aData ) ) {
                switch( ( string )$value ) {
                    case 'now()':
                        $temp_sQuery .= '`' . $columns . '` = now(), ';
                        break;
                    case 'NULL':
                        $temp_sQuery .= '`' . $columns .= '` = NULL, ';
                        break;
                    default:
                        $temp_sQuery .= '`' . $columns . '` = \'' . $value  . '\', ';
                        break;
                }
            }
            $temp_sQuery = substr( $temp_sQuery, 0, -2 ) . ' WHERE ' . $sParameters;
        }

        // 构建删除的SQL语句
        elseif( $sAction == 'delete' )
        {
        	$temp_sQuery = 'DELETE FROM `'. $sTableName .'` WHERE ' . $sParameters;
        }

		// 返回构建后的SQL语句
		//echo 'sql = '.$temp_sQuery.'<br/>';exit;
        return $temp_sQuery ;
	}


    /**
     * 创建像这样的查询: "IN('a','b')";
     *
     * @access   public
     * @param    mix      $mItemList      列表数组或字符串
     * @param    string   $sFieldName     字段名称
     *
     * @return   string
     */
    public function CreateIn( $mItemList, $sFieldName = '')
    {
        if( empty($mItemList) )
        {
            return $sFieldName . " IN ('') ";
        }
        else
        {
            if( !is_array($mItemList) ) // 非数组
            {
                $mItemList = explode(',', $mItemList);
            }
            $mItemList = array_unique($mItemList);
            $item_list_tmp = '';
            foreach( $mItemList AS $item )
            {
                if( $item !== '' )
                {
                    $item = daddslashes($item);
                    $item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
                }
            }
            if( empty($item_list_tmp) )
            {
                return $sFieldName . " IN ('') ";
            }
            else
            {
                return $sFieldName . ' IN (' . $item_list_tmp . ') ';
            }
        }
    }


	/**
	 * getSingleTableResult() 获取单个表的结果集
	 *
	 * @param string   $sTableName     数据库表名称,可以多个,例: "`user` a left join `userlog` b on a.id=b.id"
     * @param int 		$sFields        选择字段,可以多个,例: "a.loginpwd as pwd, b.action"
     * @param string 	$sCondition     WHERE 语句条件,例: a.username like 'T%'
     * @param int 		$iCurrPage      当前页数
	 * @param string   $iPageRecords   每页显示数据条数
	 * @param string   $sUseIndex      强制使用索引,例: FORCE INDEX( idx_search )
	 * @param string	$sCountSql		强制使用的COUNT查询语句（效率优化)
	 * @author Tom 090523
	 */
    public function & getPageResult( $sTableName , $sFields = "*" , $sCondition = "1", $iPageRecords = 25 , 
    								 $iCurrPage = 1, $sOrderby='', $sUseIndex = '', $sCountSql='' )
    {
        // Step 1: 查询所有符合 WHERE 条件的结果行数
        if( empty($sCountSql) )
        {//如果没有传指定的COUNT SQL语句，则生成默认的
			$sSql   = "SELECT count(*) AS TOMCOUNT FROM ".$sTableName." ".$sUseIndex." WHERE ".$sCondition;
        }
        else 
        {
        	$sSql	= $sCountSql;
        }

		$rs     = $this->getOne( $sSql );
		$iAffectRow = $rs['TOMCOUNT'];
		if( $iAffectRow == 0 )
		{
		    $aReturn['affects'] = 0;
		    $aReturn['results'] = array();
		    return $aReturn;
		}

        // Step 3:分页对当前页的判断
        $iCurrPage = (is_numeric($iCurrPage) && $iCurrPage>0) ? intval($iCurrPage) : 1; // 默认第一页
        $iPageRecords =(is_numeric($iPageRecords) && $iPageRecords>0 && $iPageRecords<10000 ) 
                            ? intval($iPageRecords) : 25; // 最高支持每页200条数据
        // Step 4:构建真实的SQL,并查询
        if( $sFields == "*" )
        {
           $sSql = "SELECT * FROM ".$sTableName." ".$sUseIndex;
        }
        else
        {
           $sSql = "SELECT ". $sFields . " FROM ".$sTableName." ".$sUseIndex;
        }
        $sSql .= " WHERE ".$sCondition." ". $sOrderby ." LIMIT ".(($iCurrPage-1)*$iPageRecords).",".$iPageRecords;
//dump($sSql);
        $aReturn['affects'] = $iAffectRow;
        $aReturn['results'] = $this->getAll($sSql);
        return $aReturn;
    }







	/************************** Cache 部分 [ 解放 MYSQL ] ****************************
	 * 实现过程:
	 *   1, 根据 SQL 语句HASH 后的标识符, 获取缓存文件数据 (fileCaches)
	 *      1.1  如果查询缓存不存在, 或过期等原因失败, 则更新数据
	 *      1.2  如果查询缓存存在,未过期, 则直接返回缓存数据作为结果集.
	 *   2, 对拥有触发器的处理
	 *      2.1  如果拥有SQL触发器. 
	 * 
	 * -----------------------------------------------------------------
	 *   1, 利用触发器, 对本地表进行最后更新时间记录 `caches`
	 *   2, PHP 文件取 Cache 数据时, 先判断本地WEB服务器与 MYSQL 服务器的时间差
	 *   3, 根据 MYSQL 服务器的最后更新时间
	 *     3.1   若 MYSQL 已有新更新, 则取最新数据, 并将数据缓存[用户指定的]时间
	 *     3.2   若 MYSQL 并无更新, 则直接在本地获取缓存数据
	 * 
	 *   getDataCached( $sql, $iTimerSec = 90 )
	 */

	/**
	 * 获取缓存数据, 如果未被缓存,则向MYSQL请求实时数据
	 *
	 * @param string $sSql
	 * @param string $iTimerSec  缓存时间(秒), 设为0则忽略本地过期时间,依赖触发器
	 * @return array
	 */
    public function getDataCached( $sSql, $iTimerSec = 0 )
    {
        if( 0 == $iTimerSec )
        { // 使用触发器缓存
            //echo "使用触发器缓存<br/>";
            return $this->_getDataCachedByTrigger( $sSql );
        }
        else if( is_numeric($iTimerSec) && $iTimerSec > 0 )
        { // 使用本地web服务器文件缓存
            //echo "使用本地web服务器文件缓存<br/>";
            return $this->_getDataCachedByFile( $sSql, $iTimerSec );
        }
        else
        {
            // TODO : 记录日志LOG, 错误的调用参数导致连接DB直接获取数据, 不做缓存处理
            return $this->getAll($sSql);
        }
    }



    /**
     * 根据参数, 实现从本地文件缓存中读取数据
     *
     * @param string $sSql
     * @param int $iTimerSec
     * @return array
     */
    private function _getDataCachedByFile( $sSql, $iTimerSec )
    {
        $aRes = $this->getAll( $sSql );
        return $aRes;
    }



    /**
     * 根据参数, 实现从本地文件缓存中读取数据
     *
     * @param string $sSql
     * @return array
     */
    private function _getDataCachedByTrigger( $sSql )
    {
        //echo 'class.db : <font color=blue> _getDataCachedByTrigger()</font> 使用触发器缓存<br/>';
        // 1, 判断 SQL 中涉及的表, 是否允许使用触发器
        //$sSql = 'select * from config left join notices where 1>0';
        $aTriggerTableName = array();
        if( FALSE == ($aTriggerTableName=$this->_tableCanUseTrigger($sSql)) )
        { // SQL 中涉及的所有表, 是否允许使用触发器
            // TODO: 进行跟踪记录, 记录下错误使用触发器的 SQL 语句
            return $this->getAll($sSql);
        }
        
        // 2, 判断本地触发器缓存是否存在
        $oFileCaches = new filecaches();
        $mResult = $oFileCaches->readCache( $sSql, 0 );
        if( $mResult == FALSE )
        { // 2.1 不存在缓存文件, 则创建缓存
            //echo '<font color=red>1031 触发器缓存结果集 不存在</font> => <br/>';
            $aRes['mdb_last_timestamp'] = time(); // 默认使用 WEB 服务器本地时间
            //print_rr($aRes);exit;
            if( $this->bDbDiffTimeEnable == TRUE )
            { // 使用 DB 服务器时间
                $aRes['mdb_last_timestamp'] = $this->getOne('select now() as tomtime');
                $aRes['mdb_last_timestamp'] = strtotime( $aRes['mdb_last_timestamp']['tomtime'] );
            }

            $aRes['data'] = $this->getAll( $sSql );
            //echo '<pre>'; print_rr($aRes);
            $oFileCaches->writeCache( $aRes );
            return $aRes['data'];
        }
        else 
        { // 2.2 缓存结果集存在, 判断是否过期
            //echo '<br/><font color=#669900>1032 触发器缓存结果集 有效 => </font><br/>';
            $mResult = $oFileCaches->readCache( $sSql, 0 );
            $iWebFileCacheTime = $mResult['mdb_last_timestamp'];
            $iDbLastUptime = $this->getTableLastUpdate($aTriggerTableName); // SQL 语句涉及表的最后更新时间
            //echo 'DB最后更新时间: => ' . $iDbLastUptime .' WEB更新时间 '.$iWebFileCacheTime.'<br/>';
            if( $iDbLastUptime < $iWebFileCacheTime )
            {
                //echo '使用了WEB服务器本地缓存<br/>';
                return $mResult['data'];
            }
            else 
            { // 如果 DB 触发器被更新, 则更新本地缓存
                // TODO: 做日志跟踪, 被频繁重建的 SQL 次数
                //echo '<font color=red>1031 触发器缓存结果过期, 数据重建</font> => <br/>';
                $aRes['mdb_last_timestamp'] = time(); // 默认使用 WEB 服务器本地时间
                if( $this->bDbDiffTimeEnable == TRUE )
                { // 使用 DB 服务器时间
                    $aRes['mdb_last_timestamp'] = $this->getOne('select now() as tomtime');
                    $aRes['mdb_last_timestamp'] = strtotime( $aRes['mdb_last_timestamp']['tomtime'] );
                }
                $aRes['data'] = $this->getAll( $sSql );
                //echo '<pre>'; print_rr($aRes);
                $oFileCaches->writeCache( $aRes );
                return $aRes['data'];
            }
        }
    }



    /**
     * 根据传递的SQL语句, 判断其中的关联表名,是否允许使用触发器缓存
     * @param string $aTableArray
     */
    private function _tableCanUseTrigger( $sSql )
    {
        $aTableArray = $this->getTableNameBySql($sSql);
        if( empty($aTableArray) || !is_array($aTableArray) )
        {
            return FALSE;
        }
        //print_rr($aTableArray);exit;
        $aTableArray = array_unique( str_replace( '`', '', $aTableArray ));
        foreach( $aTableArray as $sTableName )
        {
            if( empty($sTableName) || !isset($GLOBALS['aSysDbServer']['trigger']) ||!in_array( $sTableName, $GLOBALS['aSysDbServer']['trigger'] ) )
            {
                //echo 'Error in tablename = '. $sTableName .'<br/>';
                return FALSE;
            }
        }
        return $aTableArray;
    }



	/**
	 * 获取 SQL 语句中最后更新的表的时间，有多个表的情况下，返回最新的表的时间
	 * 
	 * @param array $aTables
	 * @return timestamp 返回表最新时间戳(MYSQL服务器时间戳)
	 */
    function getTableLastUpdate( $aTables )
    {
        $lastupdatetime = '2037-12-31 23:59:59';
        $aTableName = array();
        foreach( $aTables AS $table )
        {
            $aTableName[] = '\''.trim($table).'\'';
        }
        //print_rr($aTableName);

        if( count($aTableName) == 1 )
        {
            $sWhere = ' AND `tablename` = '. $aTableName[0];
        }
        else
        {
            $sWhere = ' AND `tablename` IN ('. join( ',' , $aTableName) .')';
        }
        //echo  'where = '. $sWhere.'<br/>';exit;
        $result = $this->getAll( 'SELECT `lastupdatetime` from `caches` WHERE 1 '. $sWhere . 
        						 ' ORDER BY `lastupdatetime` DESC' );
        // 若获取表名失败, 或传递进来的表数与实际缓存表的不符(即:有表不在缓存表caches中), 直接返回
        if( empty($aTableName) || count($aTables) != count($result) )
        {
            return '2037-12-31 23:59:59';
        }
        $lastupdatetime = strtotime($result[0]['lastupdatetime']);
        //echo '$lastupdatetime='.$lastupdatetime.' date= '. date('Y-m-d H:i:s',$lastupdatetime) .'<br/>';
        //echo "<pre>"; print_rr($result);exit;
        return $lastupdatetime;
    }


	/**
	 * 获取 SQL 语句中的表名, 作为数组返回
	 *
	 * @param string $sSql
	 * @return array
	 */
    public function getTableNameBySql( $sSql )
    {
        $sSql = trim($sSql);
        $aTableNames = array();

        if (stristr($sSql, ' JOIN ') == '')
        {// 如果语句中没有出现 JOIN
            // 解析一般的 SELECT FROM 语句
            if (preg_match('/^SELECT.*?FROM\s*((?:`?\w+`?\s*\.\s*)?`?\w+`?(?:(?:\s*AS)?\s*`?\w+`?)?(?:\s*,\s*(?:`?\w+`?\s*\.\s*)?`?\w+`?(?:(?:\s*AS)?\s*`?\w+`?)?)*)/is', $sSql, $aTableNames))
            {
                $aTableNames = preg_replace('/((?:`?\w+`?\s*\.\s*)?`?\w+`?)[^,]*/', '\1', $aTableNames[1]);
                return preg_split('/\s*,\s*/', $aTableNames);
            }
        }
        else
        {
			// 对含有 JOIN 的语句进行解析
            if (preg_match('/^SELECT.*?FROM\s*((?:`?\w+`?\s*\.\s*)?`?\w+`?)(?:(?:\s*AS)?\s*`?\w+`?)?.*?JOIN.*$/is', $sSql, $aTableNames))
            {
                $aOtherTableNames = array();
                preg_match_all('/JOIN\s*((?:`?\w+`?\s*\.\s*)?`?\w+`?)\s*/i', $sSql, $aOtherTableNames);
                return array_merge(array($aTableNames[1]), $aOtherTableNames[1]);
            }
        }
        return $aTableNames;
    }
}