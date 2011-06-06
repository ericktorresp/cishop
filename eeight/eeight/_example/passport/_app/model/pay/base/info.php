<?php
/**
 * 通用信息类
 * 
 * 此类使用于要使用数据库接口的任何类,基于新的basemodel类
 * 
 * @author  Frank
 * @version  1.0 utf8 2008-05-13
 * @version  1.1 utf8 2009-11-24	getDb 方法的返回值修改，不再使用引用
 * @version  1.2 utf8 2010-03-10	改为由继承于新的basemodel类，整合进新的系统，但原有属性变化，待完全整合
 */

class model_pay_base_info extends model_pay_base_common {
	/**
	 * 主数据表名
	 *
	 * @var string
	 */
	protected $Table;
	
	/**
	 * 构造方法
	 * 根据给定的数据表名初始化属性
	 *
	 * @param integer $sTable
	 */
	function __construct($sTable = ''){
		parent::__construct();
		!$sTable or $this->Table = $sTable;
	}
}