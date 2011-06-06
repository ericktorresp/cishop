<?php
/**
 * Description of withdraw_orders
 * 对帐变表的操作
 * @author jack
 */
class model_withdraworders extends basemodel
{
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
     * 读取帐变根据IDa
     */
    function getWithDraworders($paycard_id, $startDate = "", $endDate = "", $range = "all", $sOrderBy='', $iPageRecord = 0, $iCurrentPage = 0)
    {
        $sCondition = "paycard_id = ".intval($paycard_id);
        if ($startDate)
        {
            $sCondition .= " AND paydate >= '$startDate'";
        }
        if ($endDate)
        {
            $sCondition .= " AND paydate <= '$endDate'";
        }
        if ($range == "manual")
        {
            $sCondition .= " AND withdraw_id = 0";
        }
        elseif ($range == "auto")
        {
            $sCondition .= " AND withdraw_id > 0";
        }

        if( $iPageRecord==0 )
        {
            if( !empty($sOrderBy) )
            {
                $sOrderBy = $sOrderBy;
            }
            return $this->oDB->getAll("SELECT * FROM withdraw_orders WHERE ".$sCondition . $sOrderBy);
        }
        
        return $this->oDB->getPageResult( 'withdraw_orders', '*', $sCondition, $iPageRecord, $iCurrentPage, $sOrderBy );
    }
    
    /**
     * 得到指定付款卡的所有转帐笔数，转入金额总数，转出金额总数
     * @param <array> $paycard_ids
     * @param <string> $range 为manual表示手动充提记录数，为auto表示机器充提记录数，为all表示所有充提记录数
     * @return int
     */
    function getWithdrawsNum($paycard_ids, $range = "all")
    {
        if (!is_array($paycard_ids))
        {
            sysMessage('参数不正确', 1);
        }

        $sql = "SELECT paycard_id,count(*) AS count,sum(transfer_in) as sum_transfer_in, sum(transfer_out) as sum_transfer_out, sum(fee) as sum_fee".
            " FROM withdraw_orders WHERE paycard_id in(".implode(',', $paycard_ids). ")";
        if ($range == "manual")
        {
            $sql .= " AND withdraw_id = 0";
        }
        elseif ($range == "auto")
        {
            $sql .= " AND withdraw_id > 0";
        }
        $sql .= " GROUP BY paycard_id";
        $result = array();

        foreach ($this->oDB->getAll($sql) as $v)
        {
            $result[$v['paycard_id']] = $v;
        }

        foreach ($paycard_ids as $v)
        {
            if (!isset($result[$v]))
            {
                $result[$v] = array(
                    'count' => 0,
                    'sum_transfer_in' => 0,
                    'sum_transfer_out' => 0,
                );
            }
        }
//dump($result);
        return $result;
    }
    
    /**
     * 记录付款卡帐变信息
     */
    function insertWithOrders($aArr)
    {
        $sTableName = 'withdraw_orders';

        return $this->oDB->insert($sTableName, $aArr);
    }

    function editWithOrders($id, $aData)
    {
        if(empty($id) || !is_numeric($id) || !is_array($aData) || empty ($aData))
        {
            sysMessage('参数不正确', 1);
        }

        $sTableName = 'withdraw_orders';
        $sCondition = 'entry='.$id;
        $mResult = $this->oDB->update($sTableName, $aData, $sCondition);
        if($mResult === FALSE)
        {
            sysMessage("数据库更新错误");
        }

        return $mResult;
    }
}
?>
