<?php

class Server extends Controller
{

	public function __construct()
	{
		$this->Server();
	}

	public function Server()
	{
		parent::Controller();
		if(!$this->session->userdata('uid') || !$this->session->userdata('is_admin'))
		{
			redirect('/login');
		}
		$this->load->model('ServersModel');
		$this->lang->load('video');
	}

	public function index()
	{
		$data['servers'] = $this->ServersModel->servers();
		$this->load->view('admin/server_list', $data);
	}

	public function add()
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
		if($this->form_validation->run() == FALSE)
		{
			$this->load->view('admin/server_add_form',$data);
			return;
		}
		$data = array(
			'domain'=>$this->input->post('domain'),
			'ip'=>$this->input->post('ip'),
			'actived'=>$this->input->post('actived'),
		);
		//insert into videos
		if($this->ServersModel->add($data))
		{
			$this->session->set_flashdata('infomation', $this->lang->line('server_add_success'));
		}
		else
		{
			$this->session->set_flashdata('error', $this->lang->line('server_add_failed'));
		}
		redirect('/admin/server');
	}

	public function edit($sid=0)
	{
		if(!$sid && !$this->input->post('sid'))	show_404();
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
		if($this->form_validation->run() == FALSE)
		{
			$data['server'] = $this->ServersModel->server($sid);
			$this->load->view('admin/server_edit_form',$data);
			return;
		}
		$sid = $this->input->post('sid');
		$data = array(
			'domain'=>$this->input->post('domain'),
			'ip'=>$this->input->post('ip'),
			'actived'=>$this->input->post('actived') ? 1 : 0
		);
		//insert into videos
		if($this->ServersModel->edit($sid, $data))
		{
			$this->session->set_flashdata('infomation', sprintf($this->lang->line('successed'),$this->lang->line('edit')));
		}
		else
		{
			$this->session->set_flashdata('error', sprintf($this->lang->line('failed'),$this->lang->line('edit')));
		}
		redirect('/admin/server');
	}

	public function delete($sid)
	{
		if($this->ServersModel->delete($sid))
		{
			$this->session->set_flashdata('infomation', sprintf($this->lang->line('successed'),$this->lang->line('delete')));
		}
		else
		{
			$this->session->set_flashdata('error', sprintf($this->lang->line('successed'),$this->lang->line('delete')));
		}
		redirect('admin/server');
	}
}