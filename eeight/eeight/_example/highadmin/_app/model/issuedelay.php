<?php

class model_issuedelay extends basemodel
{
    function __construct( $aDBO=array() )
    {
        parent::__construct($aDBO);
    }

    /**
     * [通用方法]得到单条记录
     * @param <int> $itemId
     * @return <array>
     * @author Rojer
     * `issueid`='4829' and `issue`='100129029' and `lotteryid`='1'
     */
    public function getItem()
    {
        
    }

    /**
     * 得到延时奖期列表
     * @param <type> $lotteryId
     * @author  Rojer
     */
    function getItems($lotteryId)
    {
        $sql = "SELECT * FROM `issuedelay` WHERE lotteryid = ".intval($lotteryId).' ORDER BY `date` DESC';

        return $this->oDB->getAll($sql);
    }

    /**
     * 插入记录
     * @param <integer> $lotteryId
     * @param <string> $startIssue
     * @param <string> $endIssue
     * @param <integer> $delay
     * @return <type>
     * @authoer Rojer
     */
    public function addItem($lotteryId, $startIssue, $endIssue, $delay)
    {
        if (empty($lotteryId) || empty($startIssue) || empty($endIssue) || empty($delay))
        {
            sysMessage("无效的参数", 1);
        }
        $data = array(
            'lotteryid' => $lotteryId,
            'startIssue' => $startIssue,
            'endIssue' => $endIssue,
            'delay' => $delay,
            'date'  => date("Y-m-d H:i:s")
        );
        $this->oDB->insert( 'issuedelay', $data );
        return $this->oDB->ar();
    }
}

?>