<?php
include './uc_client/client.php';
class Members extends Controller
{
	public function __constuct()
	{
		$this->Members();
	}

	public function Members()
	{
		parent::Controller();
		$this->load->model('MembersModel');
		$this->lang->load('members', 'english');
	}

	public function index()
	{
		$this->login();
	}
	
	public function login()
	{
		list($uid, $username, $password, $email) = uc_user_login($this->input->post('username'), $this->input->post('password'));
		$this->load->view('header');
		$data['title'] = $this->config->item('site_title');
		if($uid > 0)
		{
			if(!$this->db->get_where('members', array('uid'=>$uid))->num_rows())
			{
				$data['message_title'] = $this->lang->line('members_active_descript');
				$data['link']['title'] = $this->lang->line('members_active');
				$data['link']['url'] = '/activation?auth=';
				$data['link']['url'] .= rawurlencode(uc_authcode("$username\t".time()."\t".uc_authcode($password, 'ENCODE'), 'ENCODE'));
			}
			else
			{
				//生成同步登录的代码
				$ucsynlogin = uc_user_synlogin($uid);
				$data['message_title'] = $this->lang->line('members_login_success').$ucsynlogin;
				$data['link']['title'] = $this->lang->line('members_continue');
				$data['link']['url'] = $this->config->item('base_url');
				$this->session->set_userdata('uid', $uid);
			}
		}
		elseif($uid == -1)
		{
			$data['message_title'] = $this->lang->line('members_does_not_exist');
		}
		elseif($uid == -2)
		{
			$data['message_title'] = $this->lang->line('members_wrong_password');
		}
		else
		{
			$data['message_title'] = $this->lang->line('members_unknown_error');
		}
		$this->load->view('message', $data);
		$this->load->view('footer');
	}

	/**
	 * 用户激活
	 */
	function activation()
	{
		$data['title'] = $this->config->item('site_title') . ' : ' .$this->lang->line('members_active');
		$this->load->view('header');
		if($this->input->post('activation') && ($activeuser = uc_get_user($this->input->post('activation'))))
		{
			list($uid, $username, $email) = $activeuser;
			$password = $this->input->post('password');
		}
		else
		{
			list($activeuser,$time,$password) = explode("\t", uc_authcode($this->input->get('auth'), 'DECODE', UC_KEY));
			$data['activeuser'] = $activeuser;
			$data['password'] = $password;
			$this->load->view('members/activation', $data);
		}
		if($username)
		{
			$member = array(
            	'uid' => $uid,
            	'username' => $username,
				'password' => md5(uc_authcode($password, 'DECODE')),
            	'is_admin' => '0'
            	);
            	$this->db->insert("members", $member);
            	$this->session->set_userdata('uid', $uid);
            	$data['message_title'] = $this->lang->line('members_register_success') . uc_user_synlogin($uid);
            	$this->load->view('header');
            	$this->load->view('message', $data);
            	$this->load->view('footer');
		}
		$this->load->view('footer');
	}

	/**
	 * 用户注册
	 */
	function register()
	{
		$data['title'] = $this->config->item('site_title') . ' : ' .$this->lang->line('members_register');
	}

	/**
	 * 用户登出
	 */
	function logout()
	{
		$data['title'] = $this->config->item('site_title') . ' : ' .$this->lang->line('members_logout');
		$this->session->sess_destroy();
		$data['message_title'] = $this->lang->line('members_logout_success').uc_user_synlogout();
		$data['link']['title'] = $this->lang->line('members_continue');
		$data['link']['url'] = $this->config->item('base_url');
		$data['title'] = $this->config->item('site_title');
		$this->load->view('header', $data);
		$this->load->view('message');
		$this->load->view('footer');
	}
}