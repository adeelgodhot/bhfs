<?php
 
 class ClfDistribution {

    /**
     * An indentifier for test and live mode
     * @var string
     */
    private $clf_mode;
    /**
     * A reference to clf API url
     * @var string
     */
    private $clf_services_url;

    public function __construct($clf_mode) {

    	// default is test mode
    	$this->clf_services_url = "http://services.clfdistribution.com:8080/CLFWebOrdering_Test/WebOrdering.asmx?WSDL";

    	// overwrite only if specificly requested 
    	if ( $clf_mode == "LIVE" ) {
    			
    		$this->clf_services_url = "http://services.clfdistribution.com:8080/CLFWebOrdering/WebOrdering.asmx?WSDL";

    	}
        
    }

    // method declaration
    private function clfGetAuthenticationToken() {

        // Specify url
		 $soapClient = new SoapClient($this->clf_services_url, array('trace' => 1));

		// Prepare SoapHeader parameters
		 $sh_param = array('AuthenticationToken' => '', 'ErrorMessage' => '');
		 $headers = new SoapHeader('http://services.clfdistribution.com/CLFWebOrdering',
		'WebServiceHeader', $sh_param);
		 // Prepare Soap Client
		 $soapClient->__setSoapHeaders(array($headers));
		 // Setup the GetAuthenticationToken parameters
		 $ap_param = array(
			 'Username' => 'besthealthfoodshopworthing',
			 'Password' => 'XFRy8atUvK');
		 // Call GetAuthenticationToken
		 $error = 0;
		 try
		 {
		 	$res = $soapClient->GetAuthenticationToken($ap_param);
		 }
		 catch (SoapFault $fault)
		 {
			 $error = 1;
			 // echo 'GetAuthenticationToken returned the following error:<br />' . $fault->faultcode . '-' . $fault->faultstring;

			 return $fault->faultcode;
		 }
		 if ($error == 0)
		 {
		 	return $res->GetAuthenticationTokenResult;

			 // Output authentication token
			 // echo '<p>Authentication Token: '. $res->GetAuthenticationTokenResult . '</p>';
			 // Output error returned in the message
			 // $sxe = new SimpleXMLElement($soapClient->__getLastResponse());
			 // $sxe->registerXPathNamespace('c','http://services.clfdistribution.com/CLFWebOrdering');
			 // $result = $sxe->xpath('//c:ErrorMessage');
			 // echo '<p>Error Message: ' . $result[0] . '</p>';
		 }
    }


    public function clfGetData($action){

    	// Specify url
		 $soapClient = new SoapClient($this->clf_services_url, array('trace' => 1));
		 $results_path = $action . "Result";

		// Prepare SoapHeader parameters
		 $sh_param = array('AuthenticationToken' => $this->clfGetAuthenticationToken(), 'ErrorMessage' => '');
		 $headers = new SoapHeader('http://services.clfdistribution.com/CLFWebOrdering', 'WebServiceHeader', $sh_param);
		 // Prepare Soap Client
		 $soapClient->__setSoapHeaders(array($headers));

		 if ( $action != "GetProductCodes" ) {
		 	$xml = file_get_contents('ProductCodesData.xml');

			 // Setup the GetProductData parameters
			 $ap_param = array(
				 'productCodesXml' => $xml);
		 } else {
		 	$ap_param = "";
		 }
		 
		 // Call GetAuthenticationToken
		 $error = 0;
		 try
		 {
		 	$res = $soapClient->$action($ap_param);
		 }
		 catch (SoapFault $fault)
		 {
			 $error = 1;
			 return $fault->faultcode;
		 }
		 if ($error == 0)
		 {

		 	return $res->$results_path;
		 }

    }

}

?>