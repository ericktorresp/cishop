<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CodeIgniter JQuery Class
 *
 * This class enables you to insert JQuery Library, Scripts, Plugins and
 * general javascript functions into your projects.
 * You can load the library and the related plugins from an external source 
 * such as Google or include it into your project folder.
 * You can also add personalized functions or scripts.
 * At the end the scripts and the functions can be minimized for better
 * performance (only if you use PHP5)
 * 
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Alberto Sabena
 * @link		http://code.google.com/p/codeigniter-jquery/
 */
class CI_Jquery {
	
	var $config 		= array();
	var $libraries		= array();
	var $scripts		= array();
	var $css			= array();
	var $CI;	
	
	/**
	 * JQuery Constructor
	 * 
	 * Loads the config form ./system/application/config/jquery.php
	 *
	 * @return CI_Jquery
	 */
	function CI_Jquery()
	{
		log_message('debug', "Jquery Class Initialized");
		
		// Set the super object to a local variable for use throughout the class
		$this->CI =& get_instance();

		// Loads the jquery config (jquery.php under ./system/application/config/)
		$this->CI->load->config('jquery');
		
		$tmp_config =& get_config();
		
		if (count($tmp_config['jquery'])>0)
		{
			$this->config = $tmp_config['jquery'];	// stores the jquery class configuration in a class variable
			unset ($tmp_config);
		}
		else
			$this->_error('jquery_configuration_error');
			
		if ($this->config['auto_insert_jquery'])	// loads the jquery library if set in the config file
			$this->add_jquery();
			
		if (count($this->config['autoload']))		// loads the plugins specified into the autoload array in config file
		{
			foreach($this->config['autoload'] as $v)
				$this->add_plugin($v);
		}
	
		return;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Sends the output to the browser output
	 *
	 */
	function output()
	{
		// first it loads the libraries
		echo $this->_build_output_libraries();
		
		// then loads the scripts
		echo $this->_build_output_scripts();
		
		// at the end loads the css
		echo $this->_build_output_css();
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Loads the main jquery library adding it in the libraries array.
	 *
	 */
	function add_jquery()
	{
		$this->libraries[] = $this->_get_full_URL($this->config['main_library_path']);
		return;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Adds a library inclusion
	 * The library can be on the local filesystem or not (if not a complete URL
	 * must be specified)
	 *
	 * @param string $library
	 */
	function add_library($library)
	{
		if (!$library)
		{
			log_message('debug', "Jquery Class: nothing to load");
			return;
		}
		
		$this->libraries[] = $this->_get_full_URL($library);
		return;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Adds a css inclusion
	 * The css can be on the local filesystem or not (if not a complete URL
	 * must be specified)
	 *
	 * @param string $css
	 */
	function add_css($css)
	{
		if (!$css)
		{
			log_message('debug', "Jquery Class: nothing to load");
			return;
		}
		
		$this->css[] = $this->_get_full_URL($css);
		
		return;
	}	
	
	// ------------------------------------------------------------------------
	
	/**
	 * Loads a plugin or a generic js function using the configuration in the 
	 * config file.
	 * Loads all file specified which end with .js or .css including them by 
	 * format.
	 * If the plugin is not configured it's not loaded
	 *
	 * @param unknown_type $plugin
	 */
	function add_plugin($plugin)
	{
		if (!$plugin || !array_key_exists($plugin,$this->config['libraries']))
		{
			log_message('debug', "Jquery Class: add_plugin, plugin not set or not configured.");
			return;
		}		
	
		foreach($this->config['libraries'][$plugin]['files'] as $v)
		{
			if (substr($v,strlen($v)-3) == ".js")
			{
				if ($this->config['libraries'][$plugin]['path'])
					$this->add_library($this->config['libraries'][$plugin]['path'] . $v);	
				else
					$this->add_library($v);
			}
			elseif (substr($v,strlen($v)-4) == ".css")
			{
				if ($this->config['libraries'][$plugin]['path'])
					$this->add_css($this->config['libraries'][$plugin]['path'] . $v);	
				else
					$this->add_css($v);				
			}
		}
		
		return;
	}
	
	// ------------------------------------------------------------------------
	
	function add_script($script,$function = "document_ready")
	{
		if (array_key_exists($function,$this->config['functions']))
			$this->scripts[$function][] = $script;
		else
			$this->_error("jquery_function_not_set",$function);
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Returns a string with all the libraries inclusions
	 *
	 * @return string
	 */
	function _build_output_libraries()
	{
		$output = '';
		
		foreach ($this->libraries as $library)
		{
			$output .= "<script type=\"text/javascript\" src=\"" . $library . "\"></script>" . "\n";
		}
		
		return $output;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Returns a string with all the css inclusions
	 *
	 * @return string
	 */
	function _build_output_css()
	{
		$output = '';
		
		foreach ($this->css as $css)
		{
			$output .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $css . "\" />" . "\n";
		}
		
		return $output;
	}
	
	// ------------------------------------------------------------------------	
	
	function _build_output_scripts()
	{
		$full_output = "";
		
		if (count($this->scripts))
		{
			foreach($this->scripts as $k => $v)
			{
				$output = "";
				
				foreach($this->scripts[$k] as $script)
					$output .= "\n" . $script;
					
				$output = sprintf($this->config['functions'][$k], "\n" . $output . "\n");
				
				$full_output .= $output;
			}
			
			if ($this->config['minimize_output'] && version_compare(PHP_VERSION, '5.0.0', '>'))
				$full_output = JSMin::minify($full_output);
	
			return "<script type=\"text/javascript\">" . $full_output . "\n</script>" . "\n";
		}
		else
			return $full_output;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Adds the base_url before the library path if not present
	 *
	 * @param string $library
	 * @return string
	 */
	function _get_full_URL($library)
	{
		if (strpos($library,"http") === false)
			return $this->CI->config->item('base_url') . $this->config['libraries_prefix'] . $library;
		else
			return $library;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Display error message
	 *
	 * @access	private
	 * @param	string
	 * @return	bool
	 */	
	function _error($line, $param = false)
	{
		$CI =& get_instance();
		$CI->lang->load('jquery');
		if ($param)
			show_error(sprintf($CI->lang->line($line),$param));
		else
			show_error($CI->lang->line($line));		
	}

}

// END CI_Jquery class


/**
 * jsmin.php - PHP implementation of Douglas Crockford's JSMin.
 *
 * This is pretty much a direct port of jsmin.c to PHP with just a few
 * PHP-specific performance tweaks. Also, whereas jsmin.c reads from stdin and
 * outputs to stdout, this library accepts a string as input and returns another
 * string as output.
 *
 * PHP 5 or higher is required.
 *
 * Permission is hereby granted to use this version of the library under the
 * same terms as jsmin.c, which has the following license:
 *
 * --
 * Copyright (c) 2002 Douglas Crockford  (www.crockford.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * The Software shall be used for Good, not Evil.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * --
 *
 * @package JSMin
 * @author Ryan Grove <ryan@wonko.com>
 * @copyright 2002 Douglas Crockford <douglas@crockford.com> (jsmin.c)
 * @copyright 2008 Ryan Grove <ryan@wonko.com> (PHP port)
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @version 1.1.1 (2008-03-02)
 * @link http://code.google.com/p/jsmin-php/
 */

class JSMin {
  const ORD_LF    = 10;
  const ORD_SPACE = 32;

  protected $a           = '';
  protected $b           = '';
  protected $input       = '';
  protected $inputIndex  = 0;
  protected $inputLength = 0;
  protected $lookAhead   = null;
  protected $output      = '';

  // -- Public Static Methods --------------------------------------------------

  public static function minify($js) {
    $jsmin = new JSMin($js);
    return $jsmin->min();
  }

  // -- Public Instance Methods ------------------------------------------------

  public function __construct($input) {
    $this->input       = str_replace("\r\n", "\n", $input);
    $this->inputLength = strlen($this->input);
  }

  // -- Protected Instance Methods ---------------------------------------------

  protected function action($d) {
    switch($d) {
      case 1:
        $this->output .= $this->a;

      case 2:
        $this->a = $this->b;

        if ($this->a === "'" || $this->a === '"') {
          for (;;) {
            $this->output .= $this->a;
            $this->a       = $this->get();

            if ($this->a === $this->b) {
              break;
            }

            if (ord($this->a) <= self::ORD_LF) {
              throw new JSMinException('Unterminated string literal.');
            }

            if ($this->a === '\\') {
              $this->output .= $this->a;
              $this->a       = $this->get();
            }
          }
        }

      case 3:
        $this->b = $this->next();

        if ($this->b === '/' && (
            $this->a === '(' || $this->a === ',' || $this->a === '=' ||
            $this->a === ':' || $this->a === '[' || $this->a === '!' ||
            $this->a === '&' || $this->a === '|' || $this->a === '?')) {

          $this->output .= $this->a . $this->b;

          for (;;) {
            $this->a = $this->get();

            if ($this->a === '/') {
              break;
            } elseif ($this->a === '\\') {
              $this->output .= $this->a;
              $this->a       = $this->get();
            } elseif (ord($this->a) <= self::ORD_LF) {
              throw new JSMinException('Unterminated regular expression '.
                  'literal.');
            }

            $this->output .= $this->a;
          }

          $this->b = $this->next();
        }
    }
  }

  protected function get() {
    $c = $this->lookAhead;
    $this->lookAhead = null;

    if ($c === null) {
      if ($this->inputIndex < $this->inputLength) {
        $c = $this->input[$this->inputIndex];
        $this->inputIndex += 1;
      } else {
        $c = null;
      }
    }

    if ($c === "\r") {
      return "\n";
    }

    if ($c === null || $c === "\n" || ord($c) >= self::ORD_SPACE) {
      return $c;
    }

    return ' ';
  }

  protected function isAlphaNum($c) {
    return ord($c) > 126 || $c === '\\' || preg_match('/^[\w\$]$/', $c) === 1;
  }

  protected function min() {
    $this->a = "\n";
    $this->action(3);

    while ($this->a !== null) {
      switch ($this->a) {
        case ' ':
          if ($this->isAlphaNum($this->b)) {
            $this->action(1);
          } else {
            $this->action(2);
          }
          break;

        case "\n":
          switch ($this->b) {
            case '{':
            case '[':
            case '(':
            case '+':
            case '-':
              $this->action(1);
              break;

            case ' ':
              $this->action(3);
              break;

            default:
              if ($this->isAlphaNum($this->b)) {
                $this->action(1);
              }
              else {
                $this->action(2);
              }
          }
          break;

        default:
          switch ($this->b) {
            case ' ':
              if ($this->isAlphaNum($this->a)) {
                $this->action(1);
                break;
              }

              $this->action(3);
              break;

            case "\n":
              switch ($this->a) {
                case '}':
                case ']':
                case ')':
                case '+':
                case '-':
                case '"':
                case "'":
                  $this->action(1);
                  break;

                default:
                  if ($this->isAlphaNum($this->a)) {
                    $this->action(1);
                  }
                  else {
                    $this->action(3);
                  }
              }
              break;

            default:
              $this->action(1);
              break;
          }
      }
    }

    return $this->output;
  }

  protected function next() {
    $c = $this->get();

    if ($c === '/') {
      switch($this->peek()) {
        case '/':
          for (;;) {
            $c = $this->get();

            if (ord($c) <= self::ORD_LF) {
              return $c;
            }
          }

        case '*':
          $this->get();

          for (;;) {
            switch($this->get()) {
              case '*':
                if ($this->peek() === '/') {
                  $this->get();
                  return ' ';
                }
                break;

              case null:
                throw new JSMinException('Unterminated comment.');
            }
          }

        default:
          return $c;
      }
    }

    return $c;
  }

  protected function peek() {
    $this->lookAhead = $this->get();
    return $this->lookAhead;
  }
}

// -- Exceptions ---------------------------------------------------------------
class JSMinException extends Exception {}


/* End of file Jquery.php */
/* Location: ./system/application/libraries/Jquery.php */