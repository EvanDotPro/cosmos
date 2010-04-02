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
			'test_service_provider',
			'calculator',
		    'debug',
		    'custom_urls'
		);
	}

	/**
	 * Returns a store to use based on the host and path in the request.
	 *
	 * @param string $host
	 * @param string $path
	 */
	public function getStoreByHostPath($host, $path)
	{
	    if($host == 'cosmos' && $path == 'store1')
	    {
	        return array(
	           'store_id' => 1,
	           'host'  => 'cosmos',
	           'path' => 'store1',
	           'group' => 1
	        );
	    }
	   if($host == 'cosmos' && $path == '')
        {
            return array(
                'store_id' => 2,
                'host'  => 'cosmos',
                'path' => '',
                'group' => 2
            );
        }
	}

}