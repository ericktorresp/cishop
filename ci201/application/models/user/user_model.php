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
	public function add() {
		$this->username = $this->input->post('username');
		$this->email = $this->input->post('email');
		$this->loginpwd = md5($this->input->post('loginpwd'));
		$this->nickname = $this->input->post('nickname');
		$this->language = $this->input->post('language');
		$this->skin = $this->input->post('skin');
		return $this->db->insert('user', $this);
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
	
	public function getUserList()
	{
		return $this->db->get('user', 25)->result();
	}
}