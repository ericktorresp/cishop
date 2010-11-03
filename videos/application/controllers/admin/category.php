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
			$this->load->view('admin/category_form',$data);
			return;
		}
		$data = array(
			'ctitle'=>$this->input->post('ctitle'),
			'order'=>$this->input->post('order') ? $this->input->post('order') : 0,
			'ctime'=>time(),
			'count'=>0
		);
		//insert into videos
		$this->CategoriesModel->add($data);
		redirect('/admin/category');
	}

	public function edit($cid)
	{

	}

	public function delete($cid)
	{

	}
}