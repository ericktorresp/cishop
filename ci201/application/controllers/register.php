<?php
class Register extends CI_Controller {
	
	public function __construct() {
		parent::__construct();
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->lang->load('user');
	}
	/**
	 * user register init interface
	 */
	public function index() {
		if ($this->form_validation->run() == FALSE)
		{
			$this->load->view('register/index');
		}
		else
		{
			$data = array(
				'username'=>$this->input->post('username'),
				'password'=>$this->input->post('password'),
				'email'=>$this->input->post('email')
			);
			$this->session->set_userdata($data);
			redirect('register/step2');
		}
	}
	
	/**
	 * user register step 2 interface
	 */
	public function step2() {
		if(!$this->session->userdata('username') || !$this->session->userdata('password') || !$this->session->userdata('email'))
			redirect('register');
		if ($this->form_validation->run() == FALSE)
		{
			$this->load->model('Country_Model');
			foreach($this->Country_Model->getAll() as $result)
				$data['country'][$result->iso] = $result->printable_name;
			$this->load->view('register/register_step2', $data);
		}
		else
		{
			$data = array(
				'gender'=>$this->input->post('gender'),
				'fname'=>$this->input->post('fname'),
				'lname'=>$this->input->post('lname'),
				'phone'=>$this->input->post('phone'),
				'birth_month'=>$this->input->post('birth_month'),
				'birth_day'=>$this->input->post('birth_day'),
				'birth_year'=>$this->input->post('birth_year'),
				'street_addr'=>$this->input->post('street_addr'),
				'suite'=>$this->input->post('suite'),
				'city'=>$this->input->post('city'),
				'zip'=>$this->input->post('zip'),
				'state'=>$this->input->post('state'),
				'country'=>$this->input->post('country'),
				'suite'=>$this->input->post('suite'),
				'step'=>3
			);
			$this->session->set_userdata($data);
			redirect('register/confirm');
		}
	}
	
	/**
	 * user register step 3 interface
	 */
	public function confirm() {
		if($this->session->userdata('step') != 3)
			redirect('register');
		if ($this->form_validation->run() == FALSE)
		{
			$this->load->view('register/register_confirm');
		}
		else
		{
			//add user
			$this->load->model("user/User_Model");
			$user = array();
			$user['username'] = $this->session->userdata('username');
			$user['loginpwd'] = md5($this->config->item('encryption_key').$this->session->userdata('password'));
			$user['email'] = $this->session->userdata('email');
			$user['registerip'] = $this->input->ip_address();
			$user['registertime'] = date('Y-m-d H:i:s');
			$user['first_name'] = $this->session->userdata('fname');
			$user['last_name'] = $this->session->userdata('lname');
			$user['country'] = $this->session->userdata('country');
			$user['phone'] = $this->session->userdata('phone');
			$user['birthday'] = $this->session->userdata('birth_year').'-'.$this->session->userdata('birth_month').'-'.$this->session->userdata('birth_day');
			$user['address'] = $this->session->userdata('street_addr');
			$user['city'] = $this->session->userdata('city');
			$user['zip'] = $this->session->userdata('zip');
			$user['gender'] = $this->session->userdata('gender');
			$user['province'] = $this->session->userdata('state');
			
			if($this->User_Model->add($user))
			{
				$this->session->sess_destroy();
				$this->load->view('register/register_finish');
			}
			else
			{
				$this->load->view('register/register_confirm');
			}
			//maybe redirect to deposit page.
			//or just display register successed page, and provide link to deposit...

		}
	}
	
	/**
	 * username check
	 */
	public function username_check($str)
	{
		$this->load->model('user/User_Model');
		if((boolean)$this->User_Model->isExists($str))
		{
			$this->form_validation->set_message('username_check', 'The %s field: ' . $str .' token, please try another one.');
			return FALSE;
		}
		return TRUE;
	}
	/**
	 * email check
	 */
	public function email_check($email)
	{
		$this->load->model('user/User_Model');
		if((boolean)$this->User_Model->isExists($email))
		{
			$this->form_validation->set_message('email_check', 'The %s field: ' . $email .' token, please try another one.');
			return FALSE;
		}
		return TRUE;
	}
}