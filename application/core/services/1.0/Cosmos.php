<?php

/**
 * Cosmos Core Services
 * 
 * @author Ben Youngblood (bx.youngblood@gmail.com)
 *
 */
class Cosmos
{
	/**
	 * Returns a list of currently enabled addons. 
	 * 
	 * @todo Get this information from a database
	 * 
	 * @return array
	 */
	public function listEnabledAddons()
	{
		return array(
			'csr_available',
			'sample',
			'test_theme',
			'test_service_provider',
			'calculator'
		);
	}
	
	/**
	 * Takes a URL an returns an array of information regarding what to do with the URL
	 * if it's a custom URL, otherwise it returns false.
	 * 
	 * @param string $url
	 * @return boolean|array
	 */
	public function checkUrl($url)
	{
	    
	}
}