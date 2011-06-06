<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of withdraw_paycards
 *
 * @author jack
 */
class model_netbank extends basemodel
{
    static $encryptKey = '1QAZ2WSX3EDC4RFV5TB';
    /**
     * 构造函数
     * @access  public
     * @return  void
     */
    function __construct( $aDBO = array() )
    {
        parent::__construct( $aDBO );
    }

    /**
     * 获取网银信息列表
     * 
     */
    function getNetBankList()
    {
        $sSql = "SELECT * FROM `withdraw_paycards` where 1";
        return $this->oDB->getAll($sSql);
    }

    /**
     * 获取一张可用的付款卡
     */
    function getAvailableCard()
    {
        $sSql = 'SELECT paycard_id,card_num,amount,passwd FROM withdraw_paycards WHERE status=1 ORDER BY amount DESC LIMIT 1';
        if ($aResult = $this->oDB->getOne($sSql))
        {
            $aResult['passwd'] = $this->us_authcode( $aResult['passwd'],'DECODE', self::$encryptKey );
            $sTableName = 'withdraw_paycards';
            $aData = array('status'=>2, 'start_time'=>date('Y-m-d H:i:s'));
            $sCondition = 'paycard_id='.$aResult['paycard_id'];
            if (!$this->oDB->update($sTableName, $aData, $sCondition))
            {
                return FALSE;//更新状态失败
            }
        }

        return $aResult;
    }
    
    /**
     * 存取网银信息
     * @param str $bankcard 网银帐号
     * @param str $password 网银密码
     * @param boolean 0:禁用 1：启用
     * @return mix 
     */
    function addNetBank($finance_name, $bank_id, $cardNum, $password, $status, $init_amount, $name, $area)
    {
        if(empty($finance_name) || empty($cardNum) || empty($password)
           || empty ($name) || empty ($area)
          )
        {
            return -1;//参数不正确
        }
    	if(!preg_match('/\d/',$cardNum) || (strlen($cardNum) != 16 && strlen($cardNum) != 19) )
    	{
            return -1;//卡号不正确
        }
        $sTableName = 'withdraw_paycards';
        $aData = array(
                        'finance_name'=>$finance_name,
                        'bank_id'=>$bank_id,
                        'card_num'=>$cardNum,
                        'passwd'=>$this->us_authcode($password, 'ENCODE', self::$encryptKey),
                        'amount'=>$init_amount,
                        'init_amount'=>$init_amount,
                        'name'=>$name,
                        'area'=>$area,
                        'status'=>intval($status)
           );
        $mResult = $this->oDB->insert($sTableName, $aData);
        if( $mResult == FALSE )
        {
            return -2;//网银信息保存失败
        }
        
        return $mResult;
    }
    
    /**
     *  修改网银信息
     * $clientmodel 0 为非客户端请求 1为客户端请求
     */
    function editNetBank($id,$aData,$clientmodel=0)
    {
        if(empty($id) || !is_numeric($id) || !is_array($aData) || empty ($aData))
        {
            if($clientmodel)
            {
                return FALSE;
            }
            sysMessage('参数不正确', 1);
        }
        if(isset($aData['passwd']) && $aData['passwd'])
        {
            $aData['passwd'] = $this->us_authcode($aData['passwd'], 'ENCODE', self::$encryptKey);
        }
        $sTableName = 'withdraw_paycards';
        $sCondition = 'paycard_id='.$id;
        $mResult = $this->oDB->update($sTableName, $aData, $sCondition);
        if($mResult === FALSE && $clientmodel == 0)
        {
            sysMessage("数据库更新错误");
        }
        
        return $mResult;
    }

    /**
     * 更新付款卡余额（仅手动充值或特殊转钱之用，机器汇款不能调用此方法，因为还需要更新另外的字段
     * @param <type> $id
     * @param int $transferIn
     * @param int $transferOut
     * @param int $transferOutFee
     * @return <type>
     */
    function updateAmount_Tran($paycard_id, $transferIn, $transferOut, $transferOutFee, $remark, $cashier, $withdraw_id = 0, $userid = 0, $bankcard = '')
    {
        if (!$this->oDB->doTransaction())
        {
            throw new Exception("开始事务失败");
        }

        $result = self::updateAmount($paycard_id, $transferIn, $transferOut, $transferOutFee, $remark, $cashier, $withdraw_id, $userid, $bankcard);
        if ($result !== true)
        {
            if (!$this->oDB->doRollback())
            {
                throw new Exception("回滚事务失败");
            }
            throw new Exception($result);
        }

        if (!$this->oDB->doCommit())
        {
            throw new Exception("提交事务错误");
        }
        
        return true;
    }

    function updateAmount($paycard_id, $transferIn, $transferOut, $transferOutFee, $remark, $cashier, $withdraw_id = 0, $userid = 0, $bankcard = '')
    {
        if(empty($paycard_id) || ($transferIn <= 0 && $transferOut <= 0) ||
                ($transferIn > 0 && $transferOut > 0) ||
                ($transferOut > 0 && $transferOutFee <0))
        {
            return '参数不正确:必须且只能指定一个转入或者转出金额';
        }

        if ($transferIn > 0)
        {
            $amount = $transferIn;
            $transferOut = 0;
            $transferOutFee = 0;
        }
        else
        {
            $transferIn = 0;
            $amount = -($transferOut + $transferOutFee);
        }

        $sql = "UPDATE withdraw_paycards SET amount = amount + ".floatval($amount)." WHERE `paycard_id` = ".$paycard_id;
        $mResult = $this->oDB->query($sql);
        if($mResult === FALSE)
        {
            return "更新卡余额错误";
        }

        $model_withdraworders = new model_withdraworders();
        $data = array(
                'paycard_id' => $paycard_id,
                'cashier' => $cashier,
                'transfer_in' => $transferIn,
                'transfer_out' => $transferOut,
                'fee' => $transferOutFee,
                'remark' => $remark,
                'paydate' => date("Y-m-d H:i:s"),
            );
        if ($withdraw_id && $userid && $bankcard)
        {
            $data['withdraw_id'] = intval($withdraw_id);
            $data['userid'] = intval($userid);
            $data['bankcard'] = $bankcard;
        }

        if (!$model_withdraworders->insertWithOrders($data))
        {
            return "更新帐变表失败";
        }

        return true;
    }

    /**
     * 删除网银信息
     */
    function delNetBank($id)
    {
        if(empty($id) || !is_numeric($id))
        {
            return -1;//ID不合法
        }
        $sTablename = 'withdraw_paycards';
        $sCondition = 'paycard_id='.$id;
        $mResult = $this->oDB->delete($sTablename, $sCondition);
        if($mResult == FALSE)
        {
            return -2;//删除网银信息失败
        }
        
        return $mResult;
    }
    
    function getPayCardById($id, $status = NULL)
    {
        if (!is_numeric($id) || $id <= 0)
        {
            return array();//ID不合法
        }

        $sql ='SELECT * FROM withdraw_paycards where paycard_id='.intval($id);
        if ($status !== NULL)
        {
            if (is_array($status))
            {
                $sql .= " AND status IN(".implode(",", $status).")";
            }
            else
            {
                $sql .= " AND status = " . intval($status);
            }
        }
        
        if ($aResult = $this->oDB->getOne($sql))
        {
            $aResult['passwd'] = $this->us_authcode($aResult['passwd'],'DECODE',self::$encryptKey);
        }
        
        return $aResult;
    }

    /**
     * 读取当前网银的状态根据ID
     */
    function getPayCardByCardNum($cardNum)
    {
        if(!$cardNum)
        {
            return -1;
        }
        $sSql ='SELECT * FROM withdraw_paycards where card_num="'.$cardNum.'"';
        $aResult = $this->oDB->getOne($sSql);
        $aResult['passwd'] = $this->us_authcode($aResult['passwd'],'DECODE',self::$encryptKey);

        return $aResult;
    }
    
    /**
     * 检测登陆密码是否合法
     * @author  jack jader
     * @return 合法返回TRUE，不合法返回FALSE
     */
    function checkUserPass( $sUserPass )
    {
		//die($sUserPass);
		$strlen = strlen($sUserPass);
		if($strlen<6 || $strlen>30)
		{
			return false;
		}
		elseif( !preg_match("/^(([0-9]+[a-z]+[0-9]*)|([a-z]+[0-9]+[a-zA-Z]*))$/i",$sUserPass))
        {
            return false;
        }
		else
		{
            return true;
        }
    }    
    /**
     * 加/解密函数
     */
    function us_authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        $ckey_length = 4;

        $key = md5($key ? $key : UC_KEY);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if($operation == 'DECODE') {
            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                    return '';
                }
        } else {
            return $keyc.base64_encode($result);
        }

    }
}
?>
