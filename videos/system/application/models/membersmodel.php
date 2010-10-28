<?php

class MembersModel extends Model
{
	var $table = 'members';
	
	var $uid = 0;
	var $username = '';
	var $password = '';
	var $validate_start = '';
	var $validate_ends = '';
	
	function MembersModel()
	{
		parent::Model();
	}
	
	function get_user($uid=0, $username='')
	{
		if($uid)
		{
			$condition = array('uid'=>$uid);
		}
		elseif($username)
		{
			$condition = array('username'=>$username);
		}
		else
		{
			show_error('Plz provide UID or USERNAME.');
		}
		return $this->db->get_where($this->table, $condition)->row();
	}
	
	function login($data=array())
	{
		$query = $this->db->get_where($this->table, array('username'=>$data['username'],'password'=>$data['password']));
		if($query->num_rows())
		{
			$this->session->set_userdata('username', $data['username']);
			$this->session->set_userdata('logged_in', 1);
			/*@todo 保存当前登录用户信息到表，防止同一用户重复登陆*/
			return TRUE;
		}
		return FALSE;
	}
	
	function logout()
	{
		$this->session->sess_destroy();
	}
}