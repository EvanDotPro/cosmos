<?php
class Cosmos_Sso
{
    public static function initiate($namespace)
    {
        $request = new Zend_Controller_Request_Http();
        $sso = false;
        
        if($request->getPathInfo() == '/sso'){
            $sso = true;
            if(isset($_GET['sid'])){
                Zend_Session::setId($_GET['sid']);
                $referer = $request->getHeader('Referer');
            } elseif(isset($_GET['csid']) && !Zend_Session::sessionExists()){
                Zend_Session::setId($_GET['csid']);
                $dieGotIt = true;
            }
        }
        
    	Zend_Registry::set('csession', new Zend_Session_Namespace('cosmosclient'));
        Zend_Registry::set('cartsess', new Zend_Session_Namespace($namespace));
        
    	$sessionID = Zend_Session::getId();
    	
    	if(isset($dieGotIt) && $dieGotIt == true){
    	    die("// Got it: {$sessionID}");
    	}
    	
    	// Invalid session ID somehow.... Give them one.
    	if(Zend_Session::sessionExists() && !Zend_Registry::get('csession')->sessionExists){
    	    unset($_COOKIE[session_name()]);
    	    Zend_Session::regenerateId();
    	    Zend_Registry::get('csession')->sessionExists = true;
    	}
    	
    	if(Zend_Session::sessionExists()){
    	    if(isset($referer)){
                header("Location: {$referer}");die();
            } elseif($sso == true && isset($_GET['csid'])){
                if($sessionID == $_GET['csid']){
                    die('// No SID update needed.');
                }
                $cookieName = session_name();
                $js = <<<js
window.stop();
function setCookie(c_name,value,expiredays)
{
var exdate=new Date();
exdate.setDate(exdate.getDate()+expiredays);
document.cookie=c_name+ "=" +escape(value)+
((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
}
function getCookie(c_name)
{
if (document.cookie.length>0)
  {
  c_start=document.cookie.indexOf(c_name + "=");
  if (c_start!=-1)
    {
    c_start=c_start + c_name.length+1;
    c_end=document.cookie.indexOf(";",c_start);
    if (c_end==-1) c_end=document.cookie.length;
    return unescape(document.cookie.substring(c_start,c_end));
    }
  }
return "";
}
setCookie("{$cookieName}","{$sessionID}");
cookieValue = getCookie("{$cookieName}");
if(cookieValue == "{$sessionID}"){
location.reload(true);
} else {
window.location = '/sso?sid={$sessionID}';
}
js;
                die($js);
            }
    	} else {
    	    Zend_Registry::get('csession')->sessionExists = true;
    	}
    }
}