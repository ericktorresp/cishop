<?php
/**
 * Description of autopay
 * +--saveWithdraw  更新处理提现汇款信息
 * +--getWithdraw	获取待处理的提现申请数据
 * +--getAvailableCard 获取一张可用的卡
 * +--editBankStatus 修改银行状态
 * @author jack
 */
class model_autopay extends basemodel
{
    /**
     * 存储客户端返回的异常信息
     * @param string $sMessage	notes提现处理信息
     * @param int $iSatus	提现处理的状态
     * @param int $withdraw_id	当前处理的记录ID
     * @param float $fee 手续费
     * @param str $sCardnum 付款卡号
     * @param int $iErrno 错误编号
     * @return int 
     */
    function saveWithdraw($withdraw_id, $sMessage, $paycard_id, $fee, $iErrno, $cashier_id, $cashier)
    {
        if( !$sMessage || !$withdraw_id || !is_numeric($withdraw_id) || !$paycard_id)
        {
            throw new Exception('提现汇款信息不完整', -2);
        }
        
        $this->oDB->doTransaction();

        $iStatus = $iErrno > 0 ? 1 : 2;
        $aData = array( 'notes' => $sMessage,
        				'finishtime'=> date('Y-m-d H:i:s'),
                        'fee' => $fee,
        				'status' => 3,//无论成功失败都更新状态为正在处理中
                        'errno' => $iErrno,
                        'paycard_id' => $paycard_id,
                        'cashier_id' => $cashier_id,
                        'cashier' => $cashier,
        				);
        $sCondition = "entry= $withdraw_id AND status=5 AND dealing_user_id=0";
        if( !$this->oDB->update('withdrawel', $aData,$sCondition))
        {
            throw new Exception('数据库更新失败', 255);
        }
        
        // 如果付款成功，更新余额
        if($iStatus == 2)
        {
            $sSql = "SELECT amount,bankcard,userid FROM withdrawel WHERE entry=".$withdraw_id;
            $aWithdraw = $this->oDB->getOne($sSql);

            // 更新付款卡的余额
            $model_netbank   = A::singleton('model_netbank');
            $result = $model_netbank->updateAmount($paycard_id, 0, $aWithdraw['amount'], $fee, '', 0, $withdraw_id, $aWithdraw['userid'], $aWithdraw['bankcard']);
            if ($result !== true)
            {
                // 回滚，抛异常
                if (!$this->oDB->doRollback())
                {
                    throw new Exception("回滚事务失败", 255);
                }
                throw new Exception($result, 255);
            }
        }
        $this->oDB->doCommit();
        
        return TRUE;
    }
    
    /**
     * 按时间顺序获取一条待付款信息
     * @param <type> $bankId
     * @return <type> 
     */
    function getWithdraw($bankId)
    {
        $aConfig   = A::singleton('model_config')->getConfigs(array('minamount', 'maxamount'));

        $sSQL = "SELECT * FROM `withdrawel` WHERE `status`=0 AND `bank_id` = ".intval($bankId).
            " AND amount >= {$aConfig['minamount']} AND amount <= {$aConfig['maxamount']}" . " ORDER BY `accepttime` ASC LIMIT 1";
        return $this->oDB->getOne($sSQL);
    }
    /**
     * 取出提现申请表的相关数据
     * 代付款的status 状态0：没有处理 1：失败 2：成功 3：正在处理
     * 
     * @return array 
     */
    function getWithdraws($bankId)
    {
        $sSQL = "SELECT * FROM `withdrawel` WHERE `status`=0 AND `bank_id` = ".intval($bankId). " ORDER BY `accepttime` ASC";
        return $this->oDB->getAll($sSQL);
    }

    function updateItem($id, $data,$where = '')
    {
        if ($where != '')
        {
            $where = " AND ".$where;
        }
        return $this->oDB->update("withdrawel", $data, "entry=".intval($id).$where);
    }
 
    function getAllBanks()
    {
        $sql = "select * from withdraw_bank_list";
        $result = $this->oDB->getAll($sql);
        $finalResult = array();
        foreach ($result as $v)
        {
            $finalResult[$v['bank_id']] = $v;
        }

        return $finalResult;
    }
    
}
?>