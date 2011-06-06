<?php
/********
 文件: passportadmin/_app/model/gala.php
 功能: 首页插图自动更换 数据模型
		图片记录的维护
		图片的更换

 @name		gala.php
 @time		0.1 1/17/2011
 @package	Passport PassportAdmin
 @author 	jIM

	备注: 文件上传调用 plugin/filefunc.php 

[ERROR CODE]
	000 CLASS必须的属性中有为空的情况 或 异常
	001 提交数据不全
	002 无法设置状态
	003 指定日期没有记录
	004 数据库操作失败
	0041 插入数据库操作失败
	0042 更新数据库操作失败
	0043 删除数据行操作失败
	0044 更新状态值操作失败
	0045 更新图片数据记录失败
	005 图片上传失败
	006	删除旧图片失败
	007 添加节日已经被添加
	008 更新图片磁盘复制操作失败
	009 更新的日期与已有记录重叠
	OK  更新图片磁盘复制操作成功

 *********/
class model_gala extends basemodel 
{
		/**
		 * 访问模式
		 *
		 * @var int 0 一般, 1管理模式
		 */
		public $Mode = 0;
		/**
		 * 特殊参数 (脚本内控微调使用 0默认不生效 1 SQL生成时调整去除id=$id)
		 * @var int
		 */
		private $SpecParam = 0;
		/************* file **************/
		/*
		 * 图片文件 操作根目录
		 * @var string
		 */
		public $RootDir = '';
		
		/*
		 * 源目录 (自动更换时/上传时的目标目录)	
		 * @var string
		 */
		public $SourceDir = '';		
		
		/*
		 * 源文件 (自动更换时/上传时的目标文件)
		 * @var string
		 */
		public $SourceFile;			
		
		/*
		 * 目标目录 (自动更换时)
		 * @var string
		 */
		public $TargetDir = ''; 		
		
		/*
		 * 目标文件 (自动更换时)
		 * @var string
		 */
		public $TargetFile;	
		
		/**
		 * 文件上传时许可的METE类型
		 *
		 * @var unknown_type
		 */
		public $FileMime = 'image';
		/*
		 * 文件上传时接受的图片格式,以|符号分割 (当前只许可jpg, LOGIN页调用的该图片)
		 * @var string
		 */
		public $ImageFormat = 'jpg';	
		/**
		 * 文件上传的最小许可大小(字节)
		 * @var int
		 */
		public $ImageSizeMin = 1024;
		/**
		 * 文件上传的最大许可大小(字节)
		 * @var int
		 */
		public $ImageSizeMax = 204800; //200K
		/**
		 * 文件上传时图片的最小宽度限制 px
		 * @var int
		 */
		public $ImageWidthMin = 300;
		/**
		 * 文件上传时图片的最大宽度限制 px
		 * @var int
		 */
		public $ImageWidthMax = 2000;
		/*
		 *上传的临时文件名
		 * @var string 
		 */
		public $UploadTmp;
		
		/*********** gala *************/
		/**
		 * 数据表ID
		 * @var int
		 */
		public $Id;
		/**
		 * 纪念日名称
		 * @var string
		 */
		public $Gala;
		/**
		 * 日期 (开始日期)
		 * @var floor 00.00
		 */
		public $Day;
		/**
		 * 日期 (结束日期)
		 * @var floor 00.00
		 */
		public $Day2;
		/**
		 * 节日首页图片URL (与UPLOAD完成后的名称相同)
		 * @var string
		 */
		public $ImgUrl;
		/**
		 * 节日备注 
		 * @var string
		 */
		public $Comment;
		/**
		 * 此条目的状态 (Get中用于限定,默认1 添加即生效)
		 * @var string
		 */
		public $Status=1;
		 
		/**
		 * CLI命令行参数
		 * @var array
		 */
		public $Argv;
		
		/********** class ***********/
		/*
		 * 信息数据表名
		 * @var string 
		 */
		private $DbTable = 'galas';
		
		/*
		 * 数据表字段名定义
		 */
		private $DbId 		= 'id';
		private $DbGala 	= 'gala';
		private $DbDay 		= 'day';
		private $DbDay2		= 'day2';
		private $DbImgUrl 	= 'imgurl';
		private $DbComment 	= 'comment';
		private $DbStatus 	= 'status';
		private $DbUtime 	= 'utime';
		
		/*
		 * 日志目录
		 * @var string
		 */
		private $LogsDir = '';
		
		/**
		 * 日志文件
		 * 
		 * @var string
		 */
		private $LogsFile = '';
		
		/*
		 * 日志级别  (0不记录不显示, 1记录日志但不返回详情, 2记录且返回) 异常错误除外
		 * @var string 
		 */
		private $LogsLevel = 1;
		
		/**
		 * 当前时间
		 * 
		 */
		private $TimeNow;
		
		/**
		 * 自定义错误码
		 * @var string
		 */
		public $ErrorCode;
		
		/**
		 * 自定义错误信息
		 * @var string
		 */
		public $ErrorInfo;
		
		public $OrderBy;
		public $PageSize;
		public $Page;
		/**
		 * 检查各项属性参数，检查目标目录是否可写
		 *
		 */
		public function __construct()
		{
			parent::__construct();
			$this->RootDir 		= PDIR_USER .'/images/';
			$this->SourceDir	= PDIR_USER .'/images/day/';
			is_dir($this->SourceDir) OR @mkdir ($this->SourceDir, 0777);
			
			$this->TargetDir	= PDIR_USER.'/images/login/';
			is_dir($this->TargetDir) OR @mkdir ($this->TargetDir, 0777);
			
			$this->LogsDir		= PDIR_ADMIN.'/_tmp/logs/gala/';
			is_dir($this->LogsDir) OR @mkdir ($this->LogsDir, 0777);
			
			$this->LogsFile		= $this->LogsDir .'logs_'.date('Y_m_d');
			if ( is_file( $this->LogsFile) ) @chmod( $this->LogsFile, 0777);
			
			$this->TimeNow		=  date('Y-m-d H:i:s');
			
		}
		
		//__destroy		-- TODO:删除各个临时变量
		
		/**
		 * 错误回显
		 *
		 */
		public function error()
		{
			if ( empty($this->ErrorCode) && empty($this->ErrorInfo) )
			{
				$this->ErrorCode = '000';
				$this->ErrorInfo = 'empty info';
				//return FALSE;
				throw new Exception( ' --'.$this->TimeNow.'-- ['.$this->ErrorCode.'] '. $this->ErrorInfo );
			}
			
			switch ( $this->ErrorCode )
			{
				case '001':
					$this->ErrorInfo = '提交数据不全';
					break;
				case '002':
					$this->ErrorInfo = '无法设置状态';
					break;
				case '003':
					$this->ErrorInfo = '指定日期没有记录';
					break;
				case '004':
					$this->ErrorInfo = '数据库操作失败';
					break;
				case '0041':
					$this->ErrorInfo = '插入数据库操作失败';
					break;
				case '0042':
					$this->ErrorInfo = '更新数据库操作失败';
					break;
				case '0043':
					$this->ErrorInfo = '删除数据行操作失败';
					break;
				case '0044':
					$this->ErrorInfo = '更新状态值操作失败';
					break;
				case '0045':
					$this->ErrorInfo = '更新图片数据记录失败';
					break;
				case '005':
					$this->ErrorInfo = '图片上传失败'.$this->ErrorInfo;
					break;
				case '006':
					$this->ErrorInfo = '删除旧图片失败';
					break;
				case '007':
					$this->ErrorInfo = '所添加节日已经被添加';
					break;
				case '008':
					$this->ErrorInfo = '更新图片磁盘复制操作失败';
					break;
				case '009':
					$this->ErrorInfo = '更新的日期与已有记录重叠';
					break;
				case 'OK':
					$this->ErrorInfo = '更新图片,磁盘文件复制操作成功'.$this->ErrorInfo;
					break;
				default :
					$this->ErrorInfo = 'unknow error';
					break;	
			}
			
			if ( $this->LogsLevel == 2)
			{
				$this->_logs();
				return ' --'.$this->TimeNow.'-- ['.$this->ErrorCode.'] '. $this->ErrorInfo;
			}
			else if ( $this->LogsLevel == 1)
			{
				$this->_logs();
			}
		}
		
		/**
		 * 新增
		 *
		 */
		public function add()
		{
			if ( empty($this->Gala) || empty($this->ImgUrl) || empty($this->Day) )
			{
				$this->ErrorCode = '001';
				$this->error();
				return FALSE;
			}
			// check 重复添加
			$aTmpCheck = $this->getVacation();
			if ( $aTmpCheck  !== FALSE )
			{
				if ( $this->_delimg( 0, $this->ImgUrl ) === FALSE )
				{
					$this->ErrorCode = '006';
					$this->error();
				}
				
				$this->ErrorCode = '007';
				$this->error();
				return FALSE;
			}
			
			$aData = array( 
					$this->DbGala 		=> $this->Gala, 
					$this->DbDay 		=> $this->Day,
					$this->DbImgUrl 	=> $this->ImgUrl,
					$this->DbComment 	=> $this->Comment,
					$this->DbStatus		=> $this->Status
				);
			empty($this->Day2) OR $aData[$this->DbDay2] =  $this->Day2;
			
			if ( $this->_OpreationDb('insert', $aData ) === FALSE || $this->oDB->errno() > 0 )
			{
				// 对数据库保存失败时候，删除已上传的图片
				if ( $this->_delimg( 0, $this->ImgUrl ) === FALSE )
				{
					$this->ErrorCode = '006';
					$this->error();
				}
				$this->ErrorCode = '0041';
				$this->error();
				return FALSE;
			}
			else 
			{
				return TRUE;
			}
			
		}
		
		/**
		 * 更新(编辑)
		 * 
		 */
		public function update()
		{
			if ( empty($this->Id) || empty($this->Gala) || empty($this->Day) )
			{
				$this->ErrorCode = '001';
				$this->error();
				return FALSE;
			}
			
			// Get old Image
			$aTmp = $this->getGalaById();
			if (count($aTmp) > 0) $sOldImage = $aTmp[ $this->DbImgUrl ];
			
			$aData = array( 
					$this->DbGala 		=> $this->Gala, 
					$this->DbDay 		=> $this->Day,
					$this->DbComment 	=> $this->Comment
					);
			empty( $this->ImgUrl )  OR $aData[$this->DbImgUrl] 	=  $this->ImgUrl;
			empty( $this->Day2 ) 	OR $aData[$this->DbDay2] 	=  $this->Day2;
			
			// check 重复
			$this->SpecParam = 1;
			$aRe = $this->getVacation();
			//对微调进行复位
			$this->SpecParam = 0;
			$iNum = ($this->Day2 == '0000' && $aTmp['day2'] == '0000')  ? 0 : 1;
			if ( $aRe[3] > $iNum)
			{
				$this->ErrorCode = '009';
				$this->error();
				return FALSE;
			}
			
			if ( $this->_OpreationDb('update', $aData ) === FALSE || $this->oDB->errno() > 0 )
			{
				// 对数据库保存失败时候，删除已上传的图片
				if ( $this->_delimg( 0, $this->ImgUrl ) === FALSE )
				{
					$this->ErrorCode = '006';
					$this->error();
				}
				$this->ErrorCode = '0042';
				$this->error();
				return FALSE;
			}
			else 
			{
				// 如果新上传了图片 删除旧图片
				if ( !empty($this->ImgUrl) && $this->_delimg( 0, $sOldImage ) === FALSE )
				{
					$this->ErrorCode = '006';
					$this->error();
				}
				return TRUE;
			}
			
		}
		
		/**
		 * 删除 (删除图片磁盘与数据库信息)
		 *
		 */
		public function del()
		{
			if ( empty($this->Id) || intval( $this->Id ) < 1 )
			{
				$this->ErrorCode = '001';
				$this->error();
			}
			
			$aTmp 		= $this->getGalaById();
			$sOldImage 	= $aTmp[ $this->DbImgUrl ];
			
			if ( $this->_OpreationDb( 'delete' ) === FALSE  || $this->oDB->errno() > 0 )
			{
				$this->ErrorCode = '0043';
				$this->error();
				return FALSE;
			}
			else 
			{
				if ( $this->_delimg( 0, $sOldImage ) === FALSE)
				{
					$this->ErrorCode = '006';
					$this->error();
				}
				return TRUE;
			}
		}
		
		/**
		 * 状态设置
		 *
		 */
		public function set() 
		{
			if ( empty($this->Id) || intval( $this->Id ) < 1 )
			{
				$this->ErrorCode = '001';
				$this->error();
			}
			
			if ( $this->_OpreationDb( 'status', array($this->DbStatus => $this->Status ) ) === FALSE
				 || $this->oDB->errno() > 0 )
			{
				$this->ErrorCode = '0044';
				$this->error();
				return FALSE;
			}
			else 
			{
				return TRUE;
			}
		}
		
		/**
		 * 更换图片 
		 * 		images/day/?.jpg 复制到 images/login/login_main.jpg
		 *
		 * @return
		 * 			TRUE  复制文件成功
		 * 			FALSE 复制文件失败 
		 * 			-1 图片无需更换
		 * 			-2 备更换的图片后缀名不符合要求,当前是 jpg
		 * 			-3 源文件无效(可能已被删除)
		 * 			-4 目标文件不可写 (可能权限不足或没有)
		 * 			-5 目标目录不是0777权限
		 */
		public function changeimg()
		{
			$sTmp = $this->check();
			if ( $sTmp === FALSE ) 							return -1;
			if ( strtolower(substr($sTmp, -3)) != 'jpg' )	return -2;
			$this->SourceFile = $this->SourceDir.$sTmp;
			// 测试源文件存在
			if ( !is_file($this->SourceFile) )				return -3;
			$this->TargetFile = $this->TargetDir.'login_main.jpg';
			@chmod( $this->TargetFile , 0777);
			// 测试目标文件可写
			if ( is_file($this->TargetFile) 
				&& !is_writable($this->TargetFile) )		return -4;
			// 测试目录权限
			if ( substr(sprintf('%o', fileperms($this->TargetDir)), -4) 
				!= '0777' )									return -5;
			
			if ( @copy($this->SourceFile, $this->TargetFile) == TRUE )
			{
				$this->ErrorCode = 'OK';
				$this->error();
				return TRUE;
			}
			else 
			{
				$this->ErrorCode = '008';
				$this->error();
				return FALSE;
			}
			
		}
		
		
		/**
		 *  CLI调用 检查图片是否需要更新
		 * 	WEB调用检查 节日是否已经被添加
		 *  判断 argv 是否存在
		 */
		public function check()
		{
			// CLI 有参数  将参数日期的图片更新
			if ( !empty($this->Argv) && preg_match("/^[0-9]{1,2}\-[0-9]{1,2}$/", $this->Argv) )
			{
				$aTmp = explode('-', $this->Argv);
				if ( intval($aTmp[0])>0 && intval($aTmp[1])>0 )
				{
					$this->Day = $this->zerofill(intval($aTmp[0])).$this->zerofill(intval($aTmp[1]));
				}
				else 
				{
					return FALSE;
				}
			}
			else 
			{
				$this->Day = $this->zerofill( (int)date('md') , 4);
			}
			
			$aResult = $this->getgala();
			//昨日今日都没有记录,则使用默认图片
			if ( intval( $this->getLastgala() ) < 1 && intval($aResult['id']) < 1 ) 	return 'default.jpg';
			//昨日有记录，今日无记录则使用默认图片
			if ( intval( $this->getLastgala() ) > 0 && intval($aResult['id']) < 1 ) 	return 'default.jpg';
			//昨日今日记录不相同，使用今日的图片
			if ( $this->getLastgala() != $aResult['id'] )
			{
				return !empty($aResult['imgurl']) ? $aResult['imgurl'] : FALSE;
			}
			else 
			{
				return  FALSE;
			}
		}
		
		/**
		 * 获取昨日的gala id 用以比对今天是否需要更换
		 *
		 */
		public function getLastgala()
		{
			$sLastDay = date('md', strtotime('-1 day') );
			$sSql = "SELECT `{$this->DbId}` FROM `{$this->DbTable}` WHERE `{$this->DbDay}`<='".$sLastDay."' AND `{$this->DbDay2}`>='".$sLastDay."'";
			$aTmp = $this->_get('one', $sSql);
			return $aTmp['id'];
		}
		
		/**
		 * 获取某天应该使用的图片(根据提供的日期参数或时差数值,默认当天)
		 *
		 */
		public function getgala()
		{
			$sSql = "SELECT * FROM `{$this->DbTable}` WHERE 1";
			$this->Mode == 1 OR $sSql .= " AND `{$this->DbStatus}`=1 ";
			
			if (intval($this->Id) > 0 && $this->SpecParam == 0)
			 	$sSql .= " AND `{$this->DbId}`=".intval( $this->Id );
			 	
			if ( $this->Day2 != '0000' && $this->Day != '0000' && $this->Day != '' && $this->Day2 != '')
			{
				$sSql .= " AND (`{$this->DbDay}`='{$this->Day}' OR (`{$this->DbDay}`>='{$this->Day}' AND `{$this->DbDay}`<='{$this->Day2}') OR (`{$this->DbDay}`<='{$this->Day}' AND `{$this->DbDay2}`>='{$this->Day2}') )";
			}
			else if ( $this->Day2 == '0000' && $this->Day != '0000' )
			{
				$sSql .= " AND (`{$this->DbDay}`='{$this->Day}' OR (`{$this->DbDay}`<='{$this->Day}' AND `{$this->DbDay2}`>='{$this->Day}') )";	
			}
			else if ( $this->Day2 == '' && $this->Day != '0000'  && intval($this->Id) < 1)
			{
				$sSql .= " AND (`{$this->DbDay}`='{$this->Day}' OR (`{$this->DbDay}`<='{$this->Day}' AND `{$this->DbDay2}`>='{$this->Day}') )";
			}
			$sS = $this->SpecParam == 0 ? 'one' : 'all';
			//TODO: 使用PHP分析SQL语句是否正确
			return $this->_get($sS, $sSql);
		}
		
		
		public function getGalaById()
		{
			$sSql = "SELECT * FROM `{$this->DbTable}` WHERE `{$this->DbId}`=".intval( $this->Id );
			return $this->_get('one', $sSql);
		}
		
		/**
		 * 获取今天是什么节日
		 * 	默认系统时间当天，否则由 $oObj->Day 提供
		 *
		 * @return array(日期，节日名，节日介绍)
		 */
		public function getVacation()
		{
			!empty($this->Day) OR $this->Day = date('md');
			$aResult = $this->getgala();
			$this->ErrorInfo = count($aResult);
			if ( empty($aResult) || count($aResult) < 1 || $this->oDB->errno() > 0 )
			{
				return FALSE;
			}
			// 多个记录也只返回排序的第一条记录
			return array($this->Day, $aResult[$this->DbGala], $aResult[$this->DbComment], count($aResult) );
			//return array($this->Day, $aResult[0][$this->DbGala], $aResult[0][$this->DbComment], count($aResult) );
		}
		
		/**
		 * 读取记录 列表
		 *
		 */
		public function & getlist()
		{
			$sCond = "1";
			return $this->_get('list', $sCond);
		}
		
		/**
		 * 保存上传的图片(更新图片)（磁盘保存与信息保存到数据库）
		 * 
		 *
		 */
		public function save()
		{
			if ( empty($this->ImgUrl) && intval($this->Id) < 1) 
			{
				$this->ErrorCode = '001';
				$this->error();
				return FALSE;
			}
			
			$aData = array(
					$this->DbImgUrl => $this->ImgUrl
				);
				
			if ( $this->_OpreationDb('update', $aData) === FALSE || $this->oDB->error > 0)
			{
				$this->ErrorCode = '0045';
				$this->error();
				return FALSE;
			}
			else 
			{
				return TRUE;
			}
			
		}
		
		/**
		 * 上传图片
		 *	 使用 plugin filefunc functions
		 * 
		 * @return bool
		 * 
		 * 		-1	提交临时文件不存在或为0
		 * 
		 * 		TRUE 成功上传:
		 * 				$this->SourceFile 上传之后保存的文件名称
		 * 				$this->ImageFormat 上传的文件后缀名
		 * 		FALSE 失败:
		 * 			$this->ErrorInfo 包含文件上传函数返回的错误码以及错误信息
		 */
		public function upload()
		{
			if ( !$this->UploadTmp ) return -1;
			
			require A_DIR . DS . 'includes' . DS . 'plugin' . DS . 'filefunc.php';
		 	$aResult = saveUploadFile( 
		 			$this->UploadTmp,		// upload temp file 
		 			$this->SourceDir,		// target dir 
		 			$this->FileMime,		// allow for meta type of file
		 			$this->ImageFormat, 	// allow for file suffix name
		 			$this->ImageSizeMin, 	// size limit min
		 			$this->ImageSizeMax,	// size limit max
		 			$this->ImageWidthMin,	// image width limit min
		 			$this->ImageWidthMax 	// image width limit max
		 			);
		 	if ( $aResult['code'] === 0)
		 	{
		 		$this->SourceFile  = $aResult['name'];
		 		$this->ImageFormat = $aResult['ext'];
		 		return TRUE;
		 	}
		 	else 
		 	{
		 		$this->ErrorCode = '005';
		 		$this->ErrorInfo = $aResult['code'].' '.$aResult['err_msg'];
		 		$this->error();
		 		return FALSE;
		 	}
		 	
		}
		
		
		/**
		 * 操作数据库
		 * 
		 * @param string $sType	 insert, update, delete, status
		 */
		private function _OpreationDb($sType='insert', $aData=array() )
		{
			if ( count($aData) < 1 && $sType != 'delete') return FALSE;
			if ( $sType != 'insert' && intval($this->Id) < 1 ) return FALSE;
			
			switch ($sType)
			{
				case 'insert':
					return $this->oDB->insert( $this->DbTable, $aData );
					break;
				case 'update':
					return $this->oDB->update( $this->DbTable, $aData , "`{$this->DbId}`=".intval($this->Id));
					break;
				case 'delete':
					return $this->oDB->delete( $this->DbTable, "`{$this->DbId}`=".intval($this->Id) );
					break;
				default:
					continue;
					break;
			}
			
			if ( $sType == 'status' )
			{
				if ( $aData[$this->DbStatus] == 1 )
				{
					$iNewStatus = 1;
					$iOldStatus = 0;
				}
				else if ( $aData[$this->DbStatus] == 0 )
				{
					$iNewStatus = 0;
					$iOldStatus = 1;
				}
				$sSql = "UPDATE `{$this->DbTable}` SET `{$this->DbStatus}`=$iNewStatus WHERE `{$this->DbId}`={$this->Id} AND `{$this->DbStatus}`=$iOldStatus";
				return $this->oDB->query($sSql);
			}
			
			return FALSE;
		}
		
		
		/**
		 * 获取数据操作
		 *
		 * @param string $sType	one, all 
		 * @param string $sSql
		 */
		private function _get( $sType='one', $sSql )
		{
			switch ($this->OrderBy)
			{
				case 'status':
					$sOrderby = " ORDER BY `{$this->DbStatus}` DESC ";
					break;
				case 'gala':
					$sOrderby = " ORDER BY `{$this->DbDay}` DESC ";
					break;
				case 'time':
					$sOrderby = " ORDER BY `{$this->DbUtime}` DESC ";
					break;
				default:
					$sOrderby = " ORDER BY `{$this->DbId}` DESC ";
					break;		
			}
			
			
			switch ($sType)
			{
				case 'one':
					return $this->oDB->getOne( $sSql );
					break;
				case 'all':
					return $this->oDB->getAll( $sSql );
					break;
				case 'list':
					return $this->oDB->getPageResult( $this->DbTable, '*', $sSql, $this->PageSize, $this->Page, $sOrderby);
				default:
					return FALSE;
					break;
			}
			
		}
		
		
		/**
		 * 删除指定的gala ID对应的图片,不整理数据表
		 *
		 * @param unknown_type $iId
		 */
		private function _delimg( $iId=0, $sImage='' )
		{
			if ( intval($iId) < 1 && $sImage == '') return FALSE;
			if ( intval($iId) > 0) 
			{
				$aImg 	= $this->getGalaById();
				if ( count($aImg) < 1 || $this->oDB->errno() > 0 )	return FALSE; 
				$sImage = $aImg[ $this->DbImgUrl ];
			}
			if ( !empty( $sImage ) && preg_match("/^[0-9]\.jpg$/", $sImage) === FALSE )	return FALSE;
			$sDelFile = $this->SourceDir.$sImage;
			if ( !is_file( $sDelFile ) ) return FALSE;
			@unlink( $sDelFile );
			if ( is_file( $sDelFile ) ) return FALSE;
			return TRUE;
		}
		
		
		/**
		 * 记录错误日志到文本日志 example: _tmp/logs/gala/logs_[Y]_[m]_[d]
		 *
		 */
		private function _logs()
		{
			// 关闭日志
			if ( $this->LogsLevel == 0 ) return 1;
			
			if ( !is_writable( $this->LogsFile ) && is_file($this->LogsFile) )
			{
				$this->ErrorCode = '001';
				$this->ErrorInfo = $this->LogsFile.' Can not writable';
				throw new Exception( ' --'.$this->TimeNow.'-- ['.$this->ErrorCode.'] '. $this->ErrorInfo );
			}
			
			$sLogsContent = ' --'.$this->TimeNow.'-- ['.$this->ErrorCode.'] '. $this->ErrorInfo."\n";
			
			if ( file_put_contents($this->LogsFile, $sLogsContent, FILE_APPEND ) < 1 )
			{
				throw new Exception( ' --'.$this->TimeNow.'-- ['.$this->ErrorCode.'] '. $this->ErrorInfo );
			}
			else
			{
				return TRUE;
			}
		}
		
		/**
		 * 生成月 日的数据数组,用户模板内生成的下拉
		 *
		 * @param $sType mon/day  生成月份或日
		 */
		public function makeCalader( $sType )
		{
			$aArray = array();
			$iMax = ( $sType == 'day' ) ? 31 : 12;
			for ( $i=1; $i<=$iMax; $i++ )
			{
				$aArray[] = array( $i, $this->zerofill($i) );
			}
			
			return $aArray; 
		}
		
		/**
		 * 零填充
		 *
		 */
		public function zerofill($mStretch, $iLength = 2)
		{
    		$sPrintfString = '%0' . (int)$iLength . 's';
    		return sprintf($sPrintfString, $mStretch);
		}
		
}
?>