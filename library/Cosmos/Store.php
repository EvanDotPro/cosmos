<?php
class Cosmos_Store
{
	public static function loadStore($host, $path = false)
	{
		$query = Zend_Registry::get('dbr')
            ->select()
            ->from('store')
            ->where('host = ?', $host);
        if($path){
        	$query->where('path = ?', $path);
        } else {
        	$query->where('path IS NULL');
        }
        $return = Zend_Registry::get('dbr')->fetchRow($query);
        $addons = array();
        foreach(self::getEnabledAddons($return['store_id']) as $addon){
            $addons[] = $addon['name'];
        }
        $return['addons'] = $addons;
        return $return;
    }

    public static function getEnabledAddons($storeID)
    {
        $query = Zend_Registry::get('dbr')
            ->select()
            ->from('store_addon')
            ->where('store_id = ?', $storeID)
            ->joinUsing('addon', 'addon_id', array('name'))
            ->order('store_addon.weight');
        $return = Zend_Registry::get('dbr')->fetchAll($query);
        return $return;
    }
}
