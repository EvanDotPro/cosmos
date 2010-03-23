<?php
class ZendC_Json_Client
{
    /**
     * Full address of the XML-RPC service
     * @var string
     * @example http://time.xmlrpc.com/RPC2
     */
    protected $_serverAddress;

    /**
     * HTTP Client to use for requests
     * @var Zend_Http_Client
     */
    protected $_httpClient = null;
    
    public function __construct($server, Zend_Http_Client $httpClient = null)
    {
        if ($httpClient === null) {
            $this->_httpClient = new Zend_Http_Client();
        } else {
            $this->_httpClient = $httpClient;
        }

        $this->_serverAddress = $server;
    }
    

    /**
     * Perform a JSON-RPC request and return a response.
     *
     * @param Zend_XmlRpc_Request $request
     * @param null|Zend_XmlRpc_Response $response
     * @return void
     * @throws Zend_XmlRpc_Client_HttpException
     */
    public function doRequest($request, $response = null)
    {
        $this->_lastRequest = $request;

        iconv_set_encoding('input_encoding', 'UTF-8');
        iconv_set_encoding('output_encoding', 'UTF-8');
        iconv_set_encoding('internal_encoding', 'UTF-8');

        $http = $this->getHttpClient();
        if($http->getUri() === null) {
            $http->setUri($this->_serverAddress);
        }

        $http->setHeaders(array(
            'Content-Type: text/xml; charset=utf-8',
            'Accept: text/xml',
        ));

        if ($http->getHeader('user-agent') === null) {
            $http->setHeaders(array('User-Agent: Zend_XmlRpc_Client'));
        }

        $xml = $this->_lastRequest->__toString();
        $http->setRawData($xml);
        $httpResponse = $http->request(Zend_Http_Client::POST);

        if (! $httpResponse->isSuccessful()) {
            /**
             * Exception thrown when an HTTP error occurs
             * @see Zend_XmlRpc_Client_HttpException
             */
            // require_once 'Zend/XmlRpc/Client/HttpException.php';
            throw new Zend_XmlRpc_Client_HttpException(
                                    $httpResponse->getMessage(),
                                    $httpResponse->getStatus());
        }

        if ($response === null) {
            $response = new Zend_XmlRpc_Response();
        }
        $this->_lastResponse = $response;
        $this->_lastResponse->loadXml($httpResponse->getBody());
    }

    /**
     * Send an XML-RPC request to the service (for a specific method)
     *
     * @param  string $method Name of the method we want to call
     * @param  array $params Array of parameters for the method
     * @return mixed
     * @throws Zend_XmlRpc_Client_FaultException
     */
    public function call($method, $params=array())
    {
        if (!$this->skipSystemLookup() && ('system.' != substr($method, 0, 7))) {
            // Ensure empty array/struct params are cast correctly
            // If system.* methods are not available, bypass. (ZF-2978)
            $success = true;
            try {
                $signatures = $this->getIntrospector()->getMethodSignature($method);
            } catch (Zend_XmlRpc_Exception $e) {
                $success = false;
            }
            if ($success) {
                $validTypes = array(
                    Zend_XmlRpc_Value::XMLRPC_TYPE_ARRAY,
                    Zend_XmlRpc_Value::XMLRPC_TYPE_BASE64,
                    Zend_XmlRpc_Value::XMLRPC_TYPE_BOOLEAN,
                    Zend_XmlRpc_Value::XMLRPC_TYPE_DATETIME,
                    Zend_XmlRpc_Value::XMLRPC_TYPE_DOUBLE,
                    Zend_XmlRpc_Value::XMLRPC_TYPE_I4,
                    Zend_XmlRpc_Value::XMLRPC_TYPE_INTEGER,
                    Zend_XmlRpc_Value::XMLRPC_TYPE_NIL,
                    Zend_XmlRpc_Value::XMLRPC_TYPE_STRING,
                    Zend_XmlRpc_Value::XMLRPC_TYPE_STRUCT,
                );

                if (!is_array($params)) {
                    $params = array($params);
                }
                foreach ($params as $key => $param) {

                    if ($param instanceof Zend_XmlRpc_Value) {
                        continue;
                    }

                    $type = Zend_XmlRpc_Value::AUTO_DETECT_TYPE;
                    foreach ($signatures as $signature) {
                        if (!is_array($signature)) {
                            continue;
                        }

                        if (isset($signature['parameters'][$key])) {
                            $type = $signature['parameters'][$key];
                            $type = in_array($type, $validTypes) ? $type : Zend_XmlRpc_Value::AUTO_DETECT_TYPE;
                        }
                    }

                    $params[$key] = Zend_XmlRpc_Value::getXmlRpcValue($param, $type);
                }
            }
        }

        $request = $this->_createRequest($method, $params);

        $this->doRequest($request);

        if ($this->_lastResponse->isFault()) {
            $fault = $this->_lastResponse->getFault();
            /**
             * Exception thrown when an XML-RPC fault is returned
             * @see Zend_XmlRpc_Client_FaultException
             */
            // require_once 'Zend/XmlRpc/Client/FaultException.php';
            throw new Zend_XmlRpc_Client_FaultException($fault->getMessage(),
                                                        $fault->getCode());
        }

        return $this->_lastResponse->getReturnValue();
    }

    /**
     * Create request object
     *
     * @return Zend_XmlRpc_Request
     */
    protected function _createRequest($method, $params)
    {
        return new Zend_XmlRpc_Request($method, $params);
    }
}