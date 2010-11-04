<?php
/**
 * 后台分类管理控制器
 * @author Floyd
 *
 */
class Category extends Controller
{
	public function __construct()
	{
		$this->Category();
	}

	public function Category()
	{
		parent::Controller();
		if(!$this->session->userdata('uid'))
		{
			redirect('/login');
		}
		$this->load->model('CategoriesModel');
		$this->lang->load('video');
	}

	public function index()
	{
		$data = array('cats'=>$this->CategoriesModel->categories());
		$this->load->view('admin/category_list', $data);
	}

	public function add()
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
		if($this->form_validation->run() == FALSE)
		{
			$this->load->view('admin/category_add_form',$data);
			return;
		}
		$data = array(
			'ctitle'=>$this->input->post('ctitle'),
			'order'=>$this->input->post('order') ? $this->input->post('order') : 0,
			'ctime'=>time(),
			'count'=>0
		);
		//insert into videos
		if($this->CategoriesModel->add($data))
		{
			$this->session->set_flashdata('infomation', $this->lang->line('category_add_success'));
		}
		else
		{
			$this->session->set_flashdata('error', $this->lang->line('category_add_failed'));
		}
		redirect('/admin/category');
	}

	public function edit($cid=0)
	{
		if(!$cid && !$this->input->post('cid'))	show_404();
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
		if($this->form_validation->run() == FALSE)
		{
			$data['category'] = $this->CategoriesModel->category($cid);
			$this->load->view('admin/category_edit_form',$data);
			return;
		}
		$cid = $this->input->post('cid');
		$data = array(
			'ctitle'=>$this->input->post('ctitle'),
			'order'=>$this->input->post('order') ? $this->input->post('order') : 0,
		);
		//insert into videos
		$this->CategoriesModel->edit($cid, $data);
		redirect('/admin/category');
	}

	public function delete($cid)
	{
		if($this->CategoriesModel->delete($cid))
		{
			$this->session->set_flashdata('infomation', $this->lang->line('category_delete_info_success'));
		}
		else
		{
			$this->session->set_flashdata('error', $this->lang->line('category_delete_error_videos'));
		}
		redirect('admin/category');
	}
}