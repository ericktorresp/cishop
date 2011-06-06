<?php
/**
 * 文件 : /_app/model/common.php
 * 功能 : 数据模型 - 公用模型仅用于某些要求高效的 CLI 程序
 * 
 * @author      Tom
 * @version    1.0.0
 * @package    lowgame
 */

class model_common extends basemodel 
{
    /**
     * 构造函数
     */
    function __construct( $aDBO=array() )
    {
        parent::__construct( $aDBO );
    }


    /**
     * 获取一条记录
     * @author  Tom
     */
    public function & commonGetOne( $sSql )
    {
        return $this->oDB->getOne( $sSql );
    }


    /**
     * 获取全部记录
     * @author  Tom
     */
    public function & commonGetAll( $sSql )
    {
        return $this->oDB->getAll( $sSql );
    }


    /**
     * 执行 SQL
     * @author  Tom
     */
    public function commonQuery( $sSql )
    {
        return $this->oDB->query( $sSql );
    }


    public function commonAr()
    {
        return $this->oDB->ar();
    }
}
?>