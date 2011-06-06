<?php
/**
 * 用户卡号绑定信息类
 * 
 * @version 	v1.0	2010-04-18
 * @author 		louis
 */
class model_withdraw_UserBank extends model_pay_base_info {
	
	/**
	 * ID
	 *
	 * @var int
	 */
	public $Id;
	
	/**
	 * 银行卡信息呢称
	 *
	 * @var string
	 */
	public $Nickname;
	
	/**
	 * 用户ID
	 *
	 * @var int
	 */
	public $UserId;
	
	/**
	 * 用户名
	 *
	 * @var string
	 */
	public $UserName;
	
	/**
	 * 邮箱地址
	 *
	 * @var string
	 */
	public $Email;
	
	/**
	 * 银行ID
	 *
	 * @var int
	 */
	public $BankId;
	
	/**
	 * 银行名称
	 *
	 * @var string
	 */
	public $BankName;
	
	/**
	 * 省份ID
	 *
	 * @var int
	 */
	public $ProvinceId;
	
	/**
	 * 省份
	 *
	 * @var string
	 */
	public $Province;
	
	/**
	 * 城市ID
	 *
	 * @var int
	 */
	public $CityId;
	
	/**
	 * 市
	 *
	 * @var string
	 */
	public $City;
	
	/**
	 * 支行名称
	 *
	 * @var string
	 */
	public $Branch;
	
	/**
	 * 开户人姓名
	 *
	 * @var string
	 */
	public $AccountName;
	
	/**
	 * 开户账号
	 *
	 * @var string
	 */
	public $Account;
	
	/**
	 * 状态
	 *
	 * @var string
	 */
	public $Status;
	
	/**
	 * 最近一次更新时间
	 *
	 * @var datetime
	 */
	public $UpdateTime;
	
	/**
	 * 添加时间
	 *
	 * @var datetime
	 */
	public $AddTime;
	
	
	
	/**
	 * 构造函数，获取指定用户的银行账户信息
	 *
	 * @version 	v1.0	2010-04-10
	 * @author 		louis
	 */
	public function __construct($id = 0){
		parent::__construct('user_bank_info');
		if ($id > 0){
			$sSql = "select * from $this->Table where id = '$id'";
			$aResult = $this->oDB->getOne( $sSql );
			$this->Id		= $aResult['id'];
			$this->Nickname		= $aResult['nickname'];
			$this->UserId		= $aResult['user_id'];
			$this->UserName		= $aResult['user_name'];
			$this->Email		= $aResult['email'];
			$this->BankId		= $aResult['bank_id'];
			$this->BankName		= $aResult['bank_name'];
			$this->ProvinceId	= $aResult['province_id'];
			$this->Province		= $aResult['province'];
			$this->CityId		= $aResult['city_id'];
			$this->City		= $aResult['city'];
			$this->Branch		= $aResult['branch'];
			$this->AccountName	= $aResult['account_name'];
			$this->Account		= $aResult['account'];
			$this->Status		= $aResult['status'];
			$this->UpdateTime	= $aResult['utime'];
			$this->AddTime		= $aResult['atime'];
		} else {
			$this->Id = 0;
		}
	}
	
	
	/**
	 * 根据用户银行卡信息id的有无，来判断是调用信息增加方法还是信息修改方法
	 *
	 * @version 	v1.0	2010-04-10
	 * @author 		louis
	 * 
	 * @return 		integer or boolean
	 */
	public function save(){
		if ($this->Id){
			return $this->_set();
		} else {
			return $this->_add();
		}
	}
	
	
	/**
	 * 增加一条用户银行卡信息
	 *
	 * @version 	v1.0	2010-04-10
	 * @author 		louis
	 * 
	 * @return 		integer or boolean
	 */
	private function _add(){
		// 数据检查
		if (intval($this->UserId) <= 0 || empty($this->UserName) || intval($this->BankId) <= 0 || empty($this->BankName) ||
			intval($this->ProvinceId) < 0 || empty($this->Province) || intval($this->CityId) < 0 || empty($this->City) ||
			empty($this->Branch) || empty($this->AccountName) || empty($this->Account) || empty($this->AddTime))
			return false;
		// 获取系统配置信息，得到用户可绑定银行卡数量。
    	$oConfig = new model_config();
    	$iMaxCard = $oConfig->getConfigs("kahaobangding");
    	if ($this->getCount() >= $iMaxCard){
			return -1;
    	}
    	
    	// 插入前最后一次确认该卡没有存在,不监测逻辑删除的卡
    	/*
    	$sSql = "SELECT user_id FROM {$this->Table} WHERE account = '{$this->Account}' AND status != 2";
		$aResult = $this->oDB->getOne($sSql);
		if ( $aResult['user_id'] > 0 ) return FALSE;
		*/
		$aData = array(
			'user_id'	=> $this->UserId,
			'nickname'	=> $this->Nickname,
			'user_name'	=> $this->UserName,
			'bank_id'	=> $this->BankId,
			'bank_name'	=> $this->BankName,
			'province_id'	=> $this->ProvinceId,
			'province'	=> $this->Province,
			'city_id'	=> $this->CityId,
			'city'		=> $this->City,
			'branch'	=> $this->Branch,
			'account_name'	=> $this->AccountName,
			'account'	=> $this->Account,
			'utime' 	=> date('Y-m-d H:i:s'),
			'atime'		=> $this->AddTime
		);
		return $this->oDB->insert( $this->Table, $aData );
	}
	
	
	/**
	 * 修改指定的用户银行卡信息
	 *
	 * @version 	v1.0	2010-04-11
	 * @author 		louis
	 * 
	 * @return 		integer or boolean
	 */
	private function _set(){
		// 数据检查
		if (intval($this->UserId) <= 0 || empty($this->UserName) || intval($this->BankId) <= 0 || empty($this->BankName) ||
			intval($this->ProvinceId) <= 0 || empty($this->Province) || intval($this->CityId) <= 0 || empty($this->City) ||
			empty($this->Branch) || empty($this->AccountName) || empty($this->Account) || isset($this->Status))
			return false;
		$aData = array(
			'user_id'	=> $this->UserId,
			'nickname'	=> $this->Nickname,
			'user_name'	=> $this->UserName,
			'bank_id'	=> $this->BankId,
			'bank_name'	=> $this->BankName,
			'province_id'	=> $this->ProvinceId,
			'province'	=> $this->Province,
			'city_id'	=> $this->CityId,
			'city'		=> $this->City,
			'branch'	=> $this->Branch,
			'account_name'	=> $this->AccountName,
			'account'	=> $this->Account,
			'status'	=> $this->Status,
			'utime' => date('Y-m-d H:i:s')
		);
		return $this->oDB->update($this->Table, $aData, "id = {$this->Id}");
	}
	
	
	/**
	 * 重新绑定银行卡时，将旧卡置为删除
	 *
	 * @version 	v1.0	2020-04-25
	 * @author 		louis
	 */
	public function setStatus(){
		if (!$this->Id || $this->Status != 2)	return false;
		$aData = array(
			'status' => $this->Status,
			'utime' => date('Y-m-d H:i:s')
		);
		return $this->oDB->update($this->Table, $aData, "id = {$this->Id} AND status != 2");
	}
	
	
	/**
	 * 删除一条用户银行卡信息
	 *
	 * @version 	v1.0	2010-04-11
	 * @author 		louis
	 * 
	 * @return 		integer or boolean
	 */
	public function erase(){
		// id检查
		if (!$this->Id) return false;
		if ( $_SESSION['admin'] )
		{
			$sCond = "id = {$this->Id}";
		}
		else 
		{
			$sCond = "user_id = {$_SESSION['userid']} AND id = {$this->Id}";
		}
		return $this->oDB->delete( $this->Table, $sCond );
	}
	
	
	/**
	 * 计算用户已绑定的并在使用中的银行卡个数
	 *
	 * @version 	v1.0	2010-04-12
	 * @author 		louis
	 * 
	 * @return 		int
	 */
	public function getCount(){
		if (!$this->UserId)	return false;
		$sSql = "SELECT COUNT(id) as sum FROM {$this->Table} WHERE user_id = {$this->UserId} AND status = 1";
		$aResult = $this->oDB->getOne($sSql);
		return $aResult['sum'];
	}
	
	
	/**
	 * 通过呢称查询指定用户下的可用记录是否存在
	 *
	 * @version 	v1.0	2010-04-12
	 * @author 		louis
	 * 
	 * @return 		boolean
	 */
	public function infoExistsByNickname(){
		if (!$this->UserId || empty($this->Nickname)) return true;
		$sSql = "SELECT * FROM {$this->Table} WHERE nickname = '{$this->Nickname}' AND user_id = {$this->UserId} AND status = 1";
		$aResult = $this->oDB->getOne($sSql);
		return empty($aResult) ? false : true;
	}
	
	
	/**
	 * 通过银行卡号检查用户是否已经绑定了相同的银行卡
	 *
	 * @version 	v1.0	2010-04-20
	 * @author 		louis
	 * 
	 * @return 		boolean
	 */
	public function accountIsExists(){
		if (intval($this->UserId) <= 0 || empty($this->Account))	return true;
		$sSql = "SELECT * FROM {$this->Table} WHERE user_id = {$this->UserId} AND account = '{$this->Account}' AND status = 1";
		$aResult = $this->oDB->getOne($sSql);
		return empty($aResult) ? false : true;
	}
	
	
	
	/**
	 * 重新绑定银行卡信息
	 *
	 * @version 	v1.0	2010-04-25
	 * @author 		louis
	 */
	public function rebinding($oldBankId){
		$this->oDB->doTransaction();
		
		// 先将旧卡删除，判断用户绑定卡号数量后再添加新卡信息
		$this->Id = $oldBankId;
		// 物理删除
		// $this->Status = 2;
		if ( $this->erase() ){
			// 检查用户卡号数量
	    	// 获取系统配置信息，得到用户可绑定银行卡数量。
	    	$oConfig = new model_config();
	    	$iMaxCard = $oConfig->getConfigs("kahaobangding");
	    	if ($this->getCount() >= $iMaxCard){
	    		$this->oDB->doRollback(); // 回滚事务
				return false;
	    	}
	    		
			if ($this->_add()){
				$this->oDB->doCommit(); // 事务提交
				return true;
			} else {
				$this->oDB->doRollback(); // 回滚事务
				return false;
			}
		} else {
			$this->oDB->doRollback(); // 回滚事务
			return false;
		}
	}
	
	
	/**
	 * 发送邮件方法
	 *
	 */
	public function sendEmail(){
		
	}
	
	/**
	 *  检查客户提交银行账户唯一性， 删除的不计
	 * 
	 * @param  int  userid
	 * @param  int  bankcount
	 * 
	 * @return bool
	 */
	public function checkAccountOnly($iUserId, $iAccount)
	{
		$sSql = "SELECT user_id,account,status FROM {$this->Table} WHERE account = '$iAccount' AND status != 2";
		$aResult = $this->oDB->getOne($sSql);
		return $aResult['user_id'] > 0 && !empty($aResult['account'])  ?  TRUE  :  FALSE;
	}
	
	/**
	 *  MYSQL名称锁操作 (锁或释放锁)
	 *  
	 * @param int 		$iAccount		需要唯一的值 字串	当前使用于建设银行 客户银行账户 唯一性保证
	 * @param string	$sAction		lock / release 锁/释放锁 默认获取锁
	 * @param int 		$iTimeOut		获取锁超时时间设置 默认2秒
	 * @param string 	$sFix			锁名称前缀 默认无
	 * @return bool 
	 */
	public function getMySQLNameLock( $iAccount , $sAction='lock', $iTimeOut=2, $sFix='')
	{
		if ( empty( $iAccount ) ) return FALSE;
		if ( $sAction == 'lock' )
		{
			$sSql	= "SELECT GET_LOCK('".$iAccount."',$iTimeOut ) AS status";
		}
		else if ( $sAction == 'release' )
		{
			$sSql	= "SELECT RELEASE_LOCK('".$iAccount."') AS status";
		}
		$aResult 	= $this->oDB->getOne($sSql);
		//print_rr( $aResult,1,1);
		if ( intval($aResult['status']) === 1 )
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
		
	}
	
    
    /**
     * 账号反查，通过卡号查询用户的信息
     * 
     * @author      louis
     * @version     v1.0
     * @since       2010-12-21
     * @package     passport
     * 
     * @return      array
     * 
     */
    public function getUserByCard(){
        // 数据检查
        if (empty($this->Account)){
            return false;
        }
        $sSql = "SELECT ut.`username`,ut.`isfrozen`,ut.`frozentype`,ut.`istester`,ubi.`bank_name`,
        ubi.`province`,ubi.`city`,ubi.`utime`,ubi.`atime`,ute.`username` AS topproxyname
        FROM `user_bank_info` AS ubi 
        LEFT JOIN `usertree` AS ut ON(ubi.`user_id` = ut.`userid`) 
        LEFT JOIN `usertree` AS ute ON (ut.`lvtopid` = ute.`userid`)
        WHERE ubi.`account` = '{$this->Account}' AND  ut.`isdeleted` = 0 AND ubi.`status` = 1 ";
        
        $aResult = $this->oDB->getAll($sSql);
        return $aResult;
    }

    /**
     * 随机获取用户绑定的银行卡
     *
     * @time    2/22/2011
     * @param <type> $iUserId   前台操作用户ID
     * @param <type> $iStatus   获取银行卡状态类型,默认启用的
     *
     * @return array(   cardid 随机出的卡ID,
     *                  nickname别名,
     *                  cardnum随机出的卡号前四位,
     *                  cardnum随机出的卡号后四位
     *          )
     *          或 FALSE
     */
    public function getRandomCardByUser($iUserId,$iStatus=1)
    {
        if ( $iUserId < 0 || $iStatus < 0 || $iStatus > 2 ) return FALSE;

        $sSql = "SELECT `id`,`nickname`,`account` FROM {$this->Table} WHERE `user_id`=$iUserId and `status`=$iStatus ORDER BY RAND() LIMIT 1";
        $aResult = $this->oDB->getOne($sSql);
        if ( $aResult['id'] > 0 &&  strlen($aResult['account']) > 10 )
        {
            return array( $aResult['id'], $aResult['nickname'], substr($aResult['account'],0,4), substr($aResult['account'],-4) );
        }
        else
        {
            return FALSE;
        }
        
    }
}