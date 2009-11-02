<?php
if (! defined('BASEPATH')) exit('No direct script access');
/**
 * shop style manage controller
 *
 * @author darkmoon
 * @version $Id$
 * @copyright darkmoon, 2 November, 2009
 * @package default
 **/
class Style extends Controller {

	function Style()
	{
		parent::Controller();
		$this->lang->load('admin');
	}

	function index($page=0)
	{
		$per_page = 1;
		$this->load->library(array('pagination','jquery'));
		$this->load->model('Stylemodel');
		$styles = $this->Stylemodel->get_styles($page,$per_page);
		$data['style_list'] = $styles['rows'];
		$config['base_url'] = $this->config->item('base_url').'admin/style/index/';
		$config['total_rows'] = $styles['count'];
		$config['per_page'] = $per_page;
		$config['uri_segment'] = 4;

		$this->pagination->initialize($config);

		$data['paginate'] = $this->pagination->create_links();
		
		$this->load->view('admin/header');
		$this->load->view('admin/style_list',$data);
		$this->load->view('admin/footer');
	}
	
	function add()
	{
		$this->load->library('form_validation');
		$this->load->helper('form');
		
		$this->load->view('admin/header');
		$this->load->view('admin/style_form');
		$this->load->view('admin/footer');
	}
	
	function del()
	{
		
	}
	
	function edit()
	{
		
	}
}