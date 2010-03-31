<?php
class Custom_Url {
    
	/**
	 * Check if the given path 
	 *
	 * @param string $path
	 * @return mixed
	 */
	public function check($path)
	{
	    switch($path){
	        case '/asdf':
	            $return = array('module'        => 'storefront',
	                            'controller'    => 'account',
	                            'action'        => 'login');
	            break;
	        default:
	            $return = false;
	            break;
	    }
		return $return;
	}
}