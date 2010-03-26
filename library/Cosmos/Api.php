<?php
class Cosmos_Api
{
    public static function get()
    {
        return Zend_Registry::get('api');
    }
    
	public static function getApiKey($apiKey)
    {
    	 $query = Zend_Registry::get('db')
            ->select()
            ->from('api_key')
            ->where('api_key.api_key = ?', $apiKey);
        $return = Zend_Registry::get('db')->fetchRow($query);
        return $return;
    }
    
    public function createNew($companyID)
    {
        $data = array();
        $data['company_id'] = $companyID;
        $data['api_key'] = $this->generateKey();
        $data['private_key'] = $this->generateKey();
        Zend_Registry::get('db')->insert('api_key', $data);
        $return = Zend_Registry::get('db')->lastInsertId();
        return $return;
    }
    
    public function generateKey()
    {
        return sha1(microtime().'salty'.rand());
    }
}