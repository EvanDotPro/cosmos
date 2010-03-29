<?php
class Cosmos_Api
{
    public static function get()
    {
        return Zend_Registry::get('api');
    }
    
	public static function getApiKey($apiKey)
    {
    	 $query = Zend_Registry::get('dbr')
            ->select()
            ->from('api_key')
            ->where('api_key.oauth_consumer_key = ?', $apiKey);
        $return = Zend_Registry::get('dbr')->fetchRow($query);
        return $return;
    }
    
    public function createNew($companyID)
    {
        $data = array();
        $data['company_id'] = $companyID;
        $data['oauth_consumer_key'] = $this->generateKey();
        $data['oauth_consumer_secret'] = $this->generateKey();
        Zend_Registry::get('dbw')->insert('api_key', $data);
        $return = Zend_Registry::get('dbw')->lastInsertId();
        return $return;
    }
    
    public function generateKey()
    {
        return sha1(microtime().'salty'.rand());
    }
}