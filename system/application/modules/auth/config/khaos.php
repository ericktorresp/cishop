<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Khaos :: KhACL
 *
 * @package 	Khaos
 * @subpackage  Khacl
 * @author      David Cole <neophyte@sourcetutor.com>
 * @version     0.1-alpha5
 * @copyright   2008
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * ACL Config Array
 *
 * Contains any settings for the KhACL library
 *
 * @package		BackendPro
 * @subpackage 	Configurations
 * @author 		Adam Price
 */
$config['acl']['tables'] = array(
        'aros'           => 'acl_groups',
        'acos'           => 'acl_resources',
        'axos'           => 'acl_actions',
        'access'         => 'acl_permissions',
        'access_actions' => 'acl_permission_actions',
		'groups'		=> 'groups',
		'resources'		=>'resources'
        );

/* End of file khaos.php */
/* Location: ./modules/auth/config/khaos.php */