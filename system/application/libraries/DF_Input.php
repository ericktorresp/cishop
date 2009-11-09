<?php
class DF_Input extends CI_Input
{
	var $CSRF_protect = FALSE;

    function DF_Input()
    {

		$CFG =& load_class('Config');
	    	    
		$this->CSRF_protect		= ($CFG->item('CSRF_protect') === TRUE) ? TRUE : FALSE;		
		$this->CSRF_domain 		= $CFG->item('CSRF_domain');
		$this->CSRF_expire 		= $CFG->item('CSRF_expire');
		$this->CSRF_redirect	= ($CFG->item('CSRF_redirect') === FALSE) ? FALSE : $CFG->item('CSRF_redirect');		
		$this->_verify_CSRF_token();
		$this->_create_CSRF_token();
	    
        parent::CI_Input();
	}
    
	/**
	 * Verify CSRF Token
	 *
	 * This function does the following:
	 *
	 * Verifies that the CSRF token is correct, if: this is a POST request, 
	 *		- CSRF Protection is turned on
	 * 		- This is a POST request
	 *		- At least one cookie is set
	 *
	 * @access	private
	 * @return	void
	 */
	function _verify_CSRF_token()
	{
		// ensure CSRF protection is on, that this is a post request
		// and that at least one cookie is being sent
		if($this->CSRF_protect)
		{
			// Ensure this is a post request
			if(!empty($_POST))
			{
				// Verify the POST token (make sure it exists, is not empty and matches the cookie token)
				if(!isset($_POST['ci_token']) || empty($_POST['ci_token']) || $_COOKIE['ci_token'] != $_POST['ci_token'])
				{
					if($this->CSRF_redirect)
						redirect($this->CSRF_redirect);
					else
						show_error('The CSRF token could not be verified.');
				}
			}

			log_message('debug', "Verified CSRF token");
		}
	}		
	
	/**
	 * Create CSRF Token
	 *
	 * This function does the following:
	 *
	 * Creates a CSRF token (if CSRF protection is enabled). 
	 *
	 * @access	private
	 * @return	void
	 */
	function _create_CSRF_token()
	{

		if($this->CSRF_protect)
		{
			// If there's a token, get it
			// If not, create one
			if(isset($_COOKIE['ci_token'])){
				$ci_token = $_COOKIE['ci_token'];
			}else{
				$ci_token = sha1(uniqid(rand(), true));
			}
		
			$domain = $this->CSRF_domain;
			if(empty($domain))
			{
				$domain = $_SERVER['HTTP_HOST'];
					
				// special case if domain is localhost
				if($domain == 'localhost')
					$domain =  FALSE;		
			}
	
			setcookie('ci_token', $ci_token, time()+$this->CSRF_expire, '/', $domain);
								
			$this->ci_token = $ci_token;			
			
			log_message('debug', "Created CSRF token");
		}
	}		    
	
	function _sanitize_globals()
	{
	        parent::_sanitize_globals();	
	        
	        if($this->CSRF_protect)
	   			$GLOBALS['ci_token'] = $this->ci_token;
	}

    
}

/*
	This function can be used by AJAX calls to insert the token
	into javascript

 */
function getCSRFToken()
{
	return $GLOBALS['ci_token'];
}

?>
