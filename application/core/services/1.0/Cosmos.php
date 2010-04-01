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

}