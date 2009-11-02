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
		$this->load->model('Stylemodel');
	}

	function index($page=0)
	{
		$per_page = 1;
		$this->load->library(array('pagination','jquery'));
		$styles = $this->Stylemodel->get_styles($page,$per_page);
		$data['style_list'] = $styles['rows'];
		$config['base_url'] = $this->config->item('base_url').'admin/style/index/';
		$config['total_rows'] = $styles['count'];
		$config['per_page'] = $per_page;
		$config['uri_segment'] = 4;
		$config['first_link'] = $this->lang->line('ui_first');
		$config['last_link'] = $this->lang->line('ui_last');

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
		if ($this->form_validation->run() == FALSE)
		{
			$this->load->view('admin/header');
			$this->load->view('admin/style_form');
			$this->load->view('admin/footer');
		}
		else
		{
			if($this->Stylemodel->add())
			{
				$this->load->view('global_message');
			}
		}
	}
	
	function edit($id)
	{
		
	}
	
	function delete($id)
	{
		
	}
}