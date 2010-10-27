<?php
require_once(FCPATH . './client/client'.EXT);

class User_model extends Model
{
	var $uid = 0;
	var $username = '';
	var $password = '';
	
    function User_model()
    {
        parent::Model();
    }
    
    function get_row($uid)
    {
    	return $this->db->get_where('users', array('uid' => $uid), 1, 0);
    }
    
    function login()
    {
		list($uid, $username, $password, $email) = uc_user_login($this->input->post('username'), $this->input->post('password'));
    }
}