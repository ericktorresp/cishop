<?php
class User extends CI_Controller {
	
	public function __construct() {
		parent::__construct();
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->lang->load('user');
	}
	/**
	 * user register init interface
	 */
	public function register() {
		if ($this->form_validation->run() == FALSE)
		{
			$this->load->view('user/register');
		}
		else
		{
			$data = array(
				'username'=>$this->input->post('username'),
				'password'=>$this->input->post('password'),
				'email'=>$this->input->post('email'),
				'step'=>2
			);
			$this->session->set_userdata($data);
			redirect('user/step2');
		}
	}
	
	/**
	 * user register step 2 interface
	 */
	public function step2() {
		if(!$this->session->userdata('username') || !$this->session->userdata('password') || !$this->session->userdata('email') || $this->session->userdata('step')!=2)
			redirect('user/register');
		if ($this->form_validation->run() == FALSE)
		{
			$this->load->view('user/register_step2');
		}
		else
		{
			$data = array(
				'username'=>$this->input->post('username'),
				'password'=>$this->input->post('password'),
				'email'=>$this->input->post('email')
			);
			$this->session->set_userdata($data);
			redirect('user/step3');
		}
	}
	
	/**
	 * user register step 3 interface
	 */
	public function step3() {
		
	}
}