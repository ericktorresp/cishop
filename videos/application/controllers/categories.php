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
		$this->load->model('CategoriesModel');
	}
	
	public function index()
	{
		$this->load->view('categories');
	}
}