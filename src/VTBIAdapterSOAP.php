<?php
class VTBIAdapterSOAP extends Tym{
	public function __construct() {
		$streamContext= stream_context_create(array('http' => array('protocol_version' => 1.1)));
		$this->options['stream_context']=$streamContext;
		$this->vtbi = new SoapClient('wsdl/telebank.wsdl',$this->options);
		//$this->vtbi = new SoapClient(null,$this->options);
	}
	public function GetKey($pan){
		$params=array(
			"PAN"=>$pan
		);
		$request=array_merge($this->requestHeaders,$params);
		$vtbiRequest=new SoapVar($request,SOAP_ENC_OBJECT,"GetKeyRq","http://schemas.compassplus.com/two/1.0/telebank.xsd");
		var_dump($vtbiRequest);
		//$this->_callService("GetKeyRq",$vtbiRequest);
		//$response=$this->vtbi->GetKeyRq(array("Request"=>$vtbiRequest));
		return $response;
	}
	protected  $requestHeaders=array(
		"Ver"=>"4"
		,"Product"=>"TB"
		,'STAN'=>0
		);
	protected $options = array(
		'location'=>'http://localhost:15003/telebank/'
		//,'uri'=>'http://localhost:8080/mobi/wsdl/telebank.wsdl'
		,'uri'=>'http://localhost:15003/'
		,'style'=>'document'
		,'use'=>'literal'
		,'soap_version'=>SOAP_1_2
		,'exceptions'=>1
		,'trace'=>1
		//,'features'=>SOAP_SINGLE_ELEMENT_ARRAYS
		//,'proxy_host'=>'10.0.1.46'
		//,'proxy_port'=>'8080'
	);
	protected $wsdl;
	protected $vtbi;
	protected $sessionKey="";
	protected $sessionKeyId="";
	protected function _callService($func,$params){
		$this->vtbi->$func(array("Request"=>$params));
	}
};
?>
