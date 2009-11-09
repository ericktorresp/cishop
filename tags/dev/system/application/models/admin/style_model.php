<?php
/**
 * product style model
 * @author floyd
 */

class Style_model extends Model {
	var $code = '';
	var $name = '';
	var $aid = 0;
	var $time = '';
	
	function Stylemodel()
	{
		parent::Model();
	}
	
	function add()
	{
		$this->code = $this->input->post('code');
		$this->name = $this->input->post('name');
		$this->aid = $this->input->post('aid');
		$this->time = gmdate('Y-m-d H:i:s');
		return $this->db->insert('style',$this);
	}
	
	function update()
	{
		$this->code = $this->input->post('code');
		$this->name = $this->input->post('name');
		$this->db->update('style',$this,array('id'=>$this->input->post('id')));
	}
	
	function get_styles($page=0,$per_page=20)
	{
		$query = $this->db->get('cs_style',$per_page,$page*$per_page);
		return array('rows'=>$query->result(),'count'=>$this->db->count_all_results('cs_style'));
	}
}