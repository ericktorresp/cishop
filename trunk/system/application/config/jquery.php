<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|------------------------------------------------------------------------------
| Base Class Configuration
|------------------------------------------------------------------------------
| 
| You can choose if you always want to load the main jquery library when the
| class is loaded or not. If not, when you need the library you must call the
| add_jquery() method.
| You must set the path to the library, it can be stored on the filesystem
| (the absolute path from the base url must be specified, without the first slash)
| or from a web repository such as Google AJAX Api
|
| example: $config['jquery']['main_library_path'] = "http://ajax.googleapis.com/ajax/libs/jquery/1.3.1/jquery.min.js"
|
| If you wish to include some js library from the local filesystem you can specify
| a prefix (relative to the document root) that is insered before the library name
| The library could use the jsmin-php class to minimize the script output, only on PHP5
|
*/
$config['jquery']['auto_insert_jquery']		= TRUE;
$config['jquery']['minimize_output']		= TRUE;
$config['jquery']['main_library_path']		= "http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js";		// without the first slash if is a local path.
$config['jquery']['libraries_prefix']		= "javascript/";


/*
|------------------------------------------------------------------------------
| Plugin and libraries configuration
|------------------------------------------------------------------------------
| 
| If you wish to use jquery plugins or generic javascript libraries you must
| configure them like the following lines.
| You must add an array in the config['jquery']['libraries'][your plugin name]
| specifing the needed informations like the plugin configured
|
*/

$config['jquery']['libraries']['jquery_ui'] = array(
													"path"	  => "jquery/plugins/",
													"files"   => array (	"jquery-ui-personalized-1.6rc6.min.js",
																			"theme/ui.all.css"
																		)
													);
													
/*
|------------------------------------------------------------------------------
| Autoload libraries
|------------------------------------------------------------------------------
| 
| You can load some libraries every time you load the Jquery Class.
| Just add the name in the autoload array, the plugin must be configured properly
| in $config['jquery']['libraries']
|
*/

$config['jquery']['autoload'] = array('jquery_ui');

/*
|------------------------------------------------------------------------------
| Jquery Functions Dictionary
|------------------------------------------------------------------------------
| 
| Here you can add some functions with the %s where you want the code to be inserted
| Useful to have multiple insertion using the add_script function
|
*/

$config['jquery']['functions'] = array(
										"general"		 => "%s",
										"document_ready" => "$(function (){ %s });"
										
										);
