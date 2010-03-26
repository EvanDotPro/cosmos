<?php

class CosmosTest extends PHPUnit_Framework_TestCase {	
	public function testListEnabledPlugins() {
		$cosmos = new Cosmos();
		$item = array_pop($cosmos->listEnabledPlugins());
		$this->assertEquals($item, 'calculator');
		$this->assertEquals(true, true);
	}
}

?>