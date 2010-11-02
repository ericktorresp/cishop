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
		$this->load->model('CategoriesModel');
	}
	
	public function index()
	{
		
	}
	
	public function add()
	{
		
	}
	
	public function edit($cid)
	{
		
	}
	
	public function delete($cid)
	{
		
	}
}