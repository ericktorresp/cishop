<?php
class CategoriesModel extends Model
{
	var $table = 'categories';
	
	var $cid;
	var $ctitle;
	var $ctime;
	var $count;
	
	public function __construct()
	{
		$this->CategoriesModel();
	}
	
	public function CategoriesModel()
	{
		parent::Model();
	}
	
	/**
	 * 添加类别
	 * @param	string	$title
	 *
	 * @return	int		-1:exists
	 */
	public function add($data)
	{
		if(!$this->_check_title($data['ctitle']))
		{
			return -1;
		}
		return $this->db->insert($this->table, $data);
	}
	
	/**
	 * 类别列表
	 *
	 * @return	array
	 */
	public function categories()
	{
		return $this->db->order_by('order ASC')->get($this->table)->result();
	}
	
	/**
	 * 类别选择下拉框列表
	 */
	public function categories_for_dropdown()
	{
		$cats = array();
		foreach($this->categories() AS $cat)
		{
			$cats[$cat->cid] = $cat->ctitle;
		}
		return $cats;
	}
	/**
	 * 编辑分类
	 * @param	int		$cid
	 * @param	array	$data
	 *
	 * @return	boolean
	 */
	public function edit_category($cid, $data)
	{
		if(!$cid)
		{
			return false;
		}
		$this->db->where('cid', $cid);
		return $this->db->update($this->table, $data);
	}
	
	/**
	 * 删除分类
	 * @param	int		$cid
	 */
	public function delete($cid)
	{
		return $this->db->delete($this->table, array('cid'=>$cid));
	}
	
	/**
	 * 检查分类名称
	 * @param	string	$title
	 */
	private function _check_title($title)
	{
		if(!$title)
		{
			return FALSE;
		}
		if($this->db->get_where($this->table, array('ctitle' => $title))->num_rows()>0)
		{
			return FALSE;
		}
		return TRUE;
	}
}