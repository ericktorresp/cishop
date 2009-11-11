<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * BackendPro
 *
 * An open source development control panel written in PHP
 *
 * @package		BackendPro
 * @author		Adam Price
 * @copyright	Copyright (c) 2008, Adam Price
 * @license		http://www.gnu.org/licenses/lgpl.html
 * @link		http://www.kaydoo.co.uk/projects/backendpro
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Welcome
 *
 * The default welcome controller
 *
 * @package  	BackendPro
 * @subpackage  Controllers
 */
class Free_shipping extends Public_Controller
{
	function Free_shipping()
	{
		parent::Public_Controller();
	}

	function index()
	{
		$this->bep_assets->load_asset_group('FREE_SHIPPING');
		// Display Page
		$data['header'] = "Free shipping";
		$data['page'] = $this->config->item('backendpro_template_public') . 'free_shipping';
		//$data['module'] = 'explore';
		$this->load->view($this->_container,$data);
	}
}


/* End of file welcome.php */
/* Location: ./modules/welcome/controllers/welcome.php */