<?php
class Categories extends Controller
{
	public function __construct()
	{
		$this->Categories();
	}

	public function Categories()
	{
		parent::Controller();
		$this->load->model(array('CategoriesModel','VideoModel'));
	}

	public function index($cid=0, $offset=0)
	{
		$this->load->library('pagination');
		$config['base_url'] = "/categories/".$cid."/";
		$config['total_rows'] = $result['total'];
		$config['per_page'] = '20';
		$config['page_query_string'] = FALSE;
		$config['uri_segment'] = 3;
		$this->pagination->initialize($config);
		
		$data['categories'] = $this->CategoriesModel->categories();	//左侧分类列表
		$cid = $cid ? $cid : $data['categories'][0]->cid;
		$data['videos'] = $this->VideoModel->videos($cid, $offset, 20);	//视频列表
		$data['pagination']=$this->pagination->create_links();
		
		$this->load->view('categories',$data);
	}
}