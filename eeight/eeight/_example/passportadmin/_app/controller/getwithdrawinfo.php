<?php
/**
 * ***********************************************************************************************
 * 功能：为自动提现汇款的客户端提供提现申请的数据
 * ***********************************************************************************************
 * //接口访问地址
 * //$url = http://localusadmin.client/?controller=getwithdrawinfo&action=savewithdraw 接收数据
 * //$url = http://localusadmin.client/?controller=getwithdrawinfo&action=getwithdraw 发送数据
 * 
 * 需要客户端提供参数:
 * notes 汇款处理信息 
 * status 成功与否 1:失败2:成功
 * entry  当前处理的记录ID
 * poundage 手续费
 * ***********************************************************************************************
 * +++savewithdraw  保存汇款操作处理结果
 * +++getwithdraw	返回给客户端发送的数据
 * +++getavailablecard	返回网银登录帐号和密码及余额给客户端
 * +++getsysconfig	返回配置信息 ：网银的金额
 * @author jack
 * 
 */
class controller_getwithdrawinfo extends basecontroller
{
    static $encryptKey = '123456789';
    
    /**
     * 保存汇款操作处理结果
     * @param string $_POST['inputdata']
     * @return array
     */
    public function actionSavewithdraw()
    {
        if (empty($_POST['inputdata']))
        {
            echo self::encrypt(array('errno'=>1,'errstr'=>'接受的数据为空'));//数据错误
            return;
        }
		//把接收到的数据解密
        $decrypt = self::decrypt($_POST['inputdata']);
        if (!isset($decrypt['entry']) || !isset($decrypt['message']) || !isset($decrypt['errno']) || !isset($decrypt['paycard_id']))
        {
            echo self::encrypt(array('errno'=>1,'errstr'=>'接受的数据不完整'));//数据错误
            return;
        }

        $model_autopay =new model_autopay();
        $entry = intval($decrypt['entry']);
        $notes = daddslashes(trim($decrypt['message']));
        $errno = intval($decrypt['errno']);
        $fee = !$errno ? $decrypt['boundage'] : '0.00';
        $errstr = $decrypt['errstr'];
        $paycard_id = $decrypt['paycard_id'];//当前正在操作的银行卡的卡号
        if( !$entry || !$notes || !$paycard_id)
        {
            echo self::encrypt(array('errno'=>1,'errstr'=>'数据不完整'));
            return;
        }

        //对于机器，cashier_id为0,cashier为空串
        try
        {
            $model_autopay->saveWithdraw($entry, $notes, $paycard_id, $fee, $errno, 0, '');
            $arr = array('errno'=>0,'errstr'=>'');
        }
        catch (Exception $e)
        {
            $arr = array('errno'=>$e->getCode(),'errstr'=>$e->getMessage());
        }

        echo self::encrypt($arr);
    }
    
    /**
     * 返回给客户端收款卡的信息
     * 客户端POST过来的数据：$bankid 银行id 6为工行
     * 如：银行帐号、收款人姓名、金额...
     */
    function actionGetwithdraw()
    {
        $model_autopay =new model_autopay();
        $allBanks = $model_autopay->getAllBanks();
        if (!isset($_POST['inputdata']) || !$data = self::decrypt($_POST['inputdata']))
        {
            echo self::encrypt(array('errno'=>255,'errstr'=>'没有指定付款卡'));
            return;
        }

        $paycard_id = intval($data['paycard_id']);
        if ($paycard_id <= 0)
        {
            echo self::encrypt(array('errno'=>255,'errstr'=>"非法的付款卡id:$paycard_id"));
            return;
        }

        if (isset($data['bankid']))
        {
            $bankId = intval($data['bankid']);
        }
        else
        {
            $bankId = 6;    //默认工行
        }

        if (!isset($allBanks[$bankId]))
        {
            echo self::encrypt(array('errno'=>255,'errstr'=>'不支持的银行'));
            return;
        }
        
        //查卡的金额是否低于系统配置的最小金额
        $oConfig   = A::singleton('model_config');
    	$aConfig   = $oConfig->getConfigs(array('minamount', 'minbalance', 'systemavailable'));
        $result = array('errno' => 0, 'errstr' => '');
        $model_netbank   = A::singleton('model_netbank');
        $paycard = $model_netbank->getPayCardById($paycard_id);
        //如果后台强制换卡，这里需判断该卡是否被禁用
        if (!$paycard)
        {
            echo self::encrypt(array('errno' => 255,'errstr' => '严重错误：找不到卡信息'));
            return;
        }
        if(!$aConfig['systemavailable'])
        {
            echo self::encrypt(array('errno' => 255,'errstr' => '严重错误：系统暂不可用'));
            return;
        }
        if ($paycard['status'] != 2)
        {
            echo self::encrypt(array('errno' => 3,'errstr' => '当前卡不在使用的状态，系统已经强制下线，请重新选择卡'));
            return;
        }
        
        if( $paycard['amount'] < $aConfig['minamount'] )
        {
            //卡的余额低于最低系统配置,
            echo self::encrypt(array('errno' => 2,'errstr' => '付款卡余额已经低于最低的付款下限'.$aConfig['minamount'].'，需要换卡'));
            return;
        }
        
        if( $paycard['amount'] < $aConfig['minbalance'] )
        {
            //卡的余额低于支付卡最低余额配置,
            echo self::encrypt(array('errno' => 2,'errstr' => '付款卡余额已经低于最低的余额配置'.$aConfig['minbalance'].'，需要换卡'));
            return;
        }

    	//获取提现申请者的信息
        /* FIFO策略 */
        $withdraw = $model_autopay->getWithdraw($bankId);
        /* 灵活策略
        if (!$withdraws = $model_autopay->getWithdraws($bankId))
        {
            echo self::encrypt(array('errno'=>1,'errstr'=>'没有任何待付款项'));
            return;
        }
        
        $withdraw = array();
        foreach ($withdraws as $v)
        {
            if ($v['amount'] < $paycard['amount'])
            {
                $withdraw = $v;
                break;
            }
        }
         */
        
        if (!$withdraw)
        {
            echo self::encrypt(array('errno'=>1, 'errstr'=>'没有任何待付款项'));
            return;
        }

        // 更新为正在处理状态
        $model_autopay->updateItem($withdraw['entry'], array('status' => 5, 'pay_time' => date('Y-m-d H:i:s')));
        $result += array(
            'entry' => $withdraw['entry'],
            'bankcard' => $withdraw['bankcard'],
            'realname' => $withdraw['realname'],
            'amount' => $withdraw['amount'],
            );

        echo self::encrypt($result);
    }

    /**
     * 获取网银登录帐号密码
     */
    function actionGetavailablecard()
    {
        $model_netbank   = A::singleton('model_netbank');
        if(isset($_POST['inputdata']))
        {
            $newArr = self::decrypt($_POST['inputdata']);
            if(!empty ($newArr['last_paycard_id']))
            {
                if(!$model_netbank->editNetBank($newArr['last_paycard_id'], array('status'=>1),1))
                {
                    echo self::encrypt(array('errno'=>1,'errstr'=>'更改银行卡状态失败'));
                    return;
                }
            }
        }

        if(!$aBank = $model_netbank->getAvailableCard())
        {
            echo self::encrypt(array('errno'=>1,'errstr'=>'获取数据失败'));
            return;
        }

        $bank = array(
            'paycard_id' => $aBank['paycard_id'],
            'bankcard'=>$aBank['card_num'],
            'money'=>$aBank['amount'],
            'password'=>$aBank['passwd'],
            'errno'=>0,
            'errstr'=>''
            );
        echo self::encrypt($bank);
        return;
    }
    
    /**
     * 客户端关闭时更改银行卡状态为可用
     */
    function actionEnduse()
    {
        $model_netbank   = A::singleton('model_netbank');
        if(!isset($_POST['inputdata']) || empty($_POST['inputdata']))
        {
           echo self::encrypt(array('errno'=>1,'errstr'=>'接收的数据为空'));
           return;
        }
        
        $newArr = self::decrypt($_POST['inputdata']);
        if(!isset ($newArr['last_paycard_id']))
        {
           echo self::encrypt(array('errno'=>1,'errstr'=>'传递的参数错误'));
           return;
        }
        
        if(!$model_netbank->editNetBank($newArr['last_paycard_id'], array('status'=>1),1))
        {
            echo self::encrypt(array('errno'=>1,'errstr'=>'更改付款卡状态失败'));
            return;
        }
        
        echo self::encrypt(array('errno'=>0,'errstr'=>'更改银行卡状态成功'));
        return;
    }
    
    /**
     * 当付款过程中关闭客户端，将重置该笔汇款为未处理
     */
    function actionResetwithdraw()
    {
        $model_autopay   = new model_autopay();
        if(!isset($_POST['inputdata']) || empty($_POST['inputdata']))
        {
           echo self::encrypt(array('errno'=>1,'errstr'=>'接收的数据为空'));
           return;
        }
        
        $newArr = self::decrypt($_POST['inputdata']);
        if(!isset ($newArr['entry']) || !isset ($newArr['paycard_id']))
        {
           echo self::encrypt(array('errno'=>1,'errstr'=>'传递的参数错误'));
           return;
        }
        
        if(!$model_autopay->updateItem($newArr['entry'], array('status'=>0),'status=5'))
        {
            echo self::encrypt(array('errno'=>1,'errstr'=>'更改支付记录状态失败'));
            return;
        }
        
        echo self::encrypt(array('errno'=>0,'errstr'=>'更改支付记录状态成功'));
        return;
    }
    
 	/**
     * 从后台系统配置获取网银金额及刷新时间
     */
    function actionGetsysconfig()
    {
        $oConfig   = A::singleton('model_config');
        $aConfig   = $oConfig->getConfigs(array('minamount', 'maxamount', 'refreshtime', 'minbalance'));
        $result = array('errno' => 0, 'errstr' => '');
        $result += array(
            'minamount' => $aConfig['minamount'],
            'maxamount' => $aConfig['maxamount'],
            'refreshtime' => $aConfig['refreshtime'],
            'minbalance' => $aConfig['minbalance'],
        );

    	echo self::encrypt($result);
    }
    
    /**
     * 加密方法
     * @param array $arr
     * @return str
     */
    static function encrypt($arr)
    {
        return self::us_authcode(http_build_query($arr), 'ENCODE', self::$encryptKey);
    }
    
    /**
     * 解密方法
     * @param string $str
     * @return str 
     */
    static function decrypt($str)
    {
        $result = array();
        parse_str(urldecode(self::us_authcode( $str,'DECODE',self::$encryptKey )), $result);
        
        return $result;
    }
    
    /**
     * 加解密函数
     */
	static function us_authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
	{
	    $ckey_length = 4;
	
	    $key = md5($key ? $key : US_KEY);
	    $keya = md5(substr($key, 0, 16));
	    $keyb = md5(substr($key, 16, 16));
	    $keyc = $ckey_length ? ( $operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length) ) : '';
	
	    $cryptkey = $keya . md5($keya . $keyc);
	    $key_length = strlen($cryptkey);
	
	    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
	    $string_length = strlen($string);
	
	    $result = '';
	    $box = range(0, 255);
	
	    $rndkey = array();
	    for ($i = 0; $i <= 255; $i++)
	    {
	        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
	    }
	
	    for ($j = $i = 0; $i < 256; $i++)
	    {
	        $j = ( $j + $box[$i] + $rndkey[$i] ) % 256;
	        $tmp = $box[$i];
	        $box[$i] = $box[$j];
	        $box[$j] = $tmp;
	    }
	
	    for ($a = $j = $i = 0; $i < $string_length; $i++)
	    {
	        $a = ( $a + 1 ) % 256;
	        $j = ( $j + $box[$a] ) % 256;
	        $tmp = $box[$a];
	        $box[$a] = $box[$j];
	        $box[$j] = $tmp;
	        $result .= chr(ord($string[$i]) ^ ( $box[( $box[$a] + $box[$j] ) % 256] ));
	    }
	
	    if ($operation == 'DECODE')
	    {
	        if (( substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0 )
	                && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16))
	        {
	            return substr($result, 26);
	        }
	        else
	        {
	            return '';
	        }
	    }
	    else
	    {
	        return $keyc . base64_encode($result);
	    }
	}
}
?>
