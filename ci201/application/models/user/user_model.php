<?php
Class User_Model extends CI_Model {
	var $username = '';
	var $email = '';
	var $loginpwd = '';
	var $securitypwd = '';
	var $nickname = '';
	var $language = '';
	var $skin = '';
	var $authtoparent = 0;
	var $addcount = 0;
	var $authadd = 0;
	var $lastip = '';
	var $lasttime = '0000-00-00 00:00:00';
	var $registerip = '';
	var $registertime = '0000-00-00 00:00:00';
	
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * CRUD - create
	 */
	public function add($user) {
		$this->db->trans_start();
		$this->db->insert('user', $user);
		$this->db->insert('user_fund', array());
		return $this->db->trans_complete();
	}
	
	/**
	 * CRUD - update
	 */
	public function update() {
		
	}
	
	/**
	 * CRUD - delete
	 */
	public function delete() {
		
	}
	
	/**
	 * CRUD - read
	 */
	public function read() {
		
	}
	
	/**
	 * Check username or email exists
	 * Enter description here ...
	 */
	public function isExists($str) {
		if(!$str) return FALSE;
		if(strpos($str, '@')){
			$query = $this->db->get_where('user', array('email' => $str));
		}
		else
		{
			$query = $this->db->get_where('user', array('username' => $str));
		}
		return $query->result();
	}
	
	
	public function getUserList()
	{
		return $this->db->get('user', 25)->result();
	}
}