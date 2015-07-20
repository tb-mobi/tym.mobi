<?php
class FIMIAdapter extends Tym{
	public function __construct() {
		$this->wsdl='wsdl/fimi.wsdl';
		$this->_options['stream_context']=stream_context_create(array('http' => array('protocol_version' => 1.0)));
		$this->_fimi = new SoapClient($this->wsdl,$this->_options);
	}
	public function CardInfo($pan){
		$params=array(
			"PAN"=>$pan
			,"MBR"=>"0"
			,"RequiredData"=>"0"
		);
		$this->_request->_=$params;
		$fimiRequest=new SoapVar($request,SOAP_ENC_OBJECT,"GetCardInfoRq","http://schemas.compassplus.com/two/1.0/fimi_types.xsd");
		$this->_callFimi("GetCardInfoRq",$fimiRequest);
	}
	public function CreatePerson($arq){
		$this->checkRequestParameters($arq,array('fio','personid'),__FUNCTION__);
		$this->_request['_']=array(
			"InstName"=>$this->_options['InstName']
			,"VIP"=>"0"
			,"FIO"=>$arq['fio']
			,"PersonId"=>$arq['personid']
		);
		$fimiRequest=new SoapVar($this->_request,SOAP_ENC_OBJECT,__FUNCTION__."Rq");//,"http://schemas.compassplus.com/two/1.0/fimi_types.xsd");
		$soapFunc=__FUNCTION__."Rq";
		$this->_fimi->$soapFunc(array("Request"=>$fimiRequest));
		//$this->_callFimi(__FUNCTION__."Rq",$fimiRequest);
		return array();
	}
	public function CreateAccount($arq){
		$this->checkRequestParameters($arq,array('account','personid'),__FUNCTION__);
		$this->_request['_']=array(
			'AvailBalance'=>0
			,'Currency'=>'810'
			,'LedgerBalance'=>0
			,'Status'=>1
      ,'Type'=>1
			,'PersonID'=>$arq['personid']
			,'Account'=>$arq['account']
		);
		$fimiRequest=new SoapVar($this->_request,SOAP_ENC_OBJECT,__FUNCTION__."Rq","http://schemas.compassplus.com/two/1.0/fimi_types.xsd");
		$this->_callFimi(__FUNCTION__."Rq",$fimiRequest);
		return array();
	}
	public function CreateVCard($arq){
		$this->checkRequestParameters($arq,array('account','nameOnCard'),__FUNCTION__);
		$this->_request['_']=array(
			'CardProfile'=>1
			,'NameOnCard'=>$arq['nameOnCard']
			,'Account'=>$arq['account']
		);
		$fimiRequest=new SoapVar($this->_request,SOAP_ENC_OBJECT,__FUNCTION__."Rq","http://schemas.compassplus.com/two/1.0/fimi_types.xsd");
		$resp=$this->_callFimi(__FUNCTION__."Rq",$fimiRequest);
		var_dump($resp);
		$res=array(
			'AuthPAN'=>$resp->Body->CreateVCardRp->Response->PAN
		);
		return $res;
	}
	protected  $_request=array(
		"_"=>""
		,"Ver"=>"3.5"
		,"Clerk"=>"PLATFON"
		,"Password"=>"PLATFON"
		,"Product"=>"FIMI"
	);
	protected $_options = array(
		'location'=>'http://localhost:15001'
		,'uri'=>'http://localhost:8080/mobi/wsdl/fimi.wsdl'
		,'soap_version'=>SOAP_1_2
		,'exceptions'=>true
		,'trace'=>1

		,'InstName'=>'BBNK'
		//,'proxy_host'=>'10.0.1.46'
		//,'proxy_port'=>'8080'
	);
	protected $_wsdl;
	protected $_fimi;
	protected function _callFimi($func,$params){
		$this->_fimi->$func(array("Request"=>$params));
	}
};
?>
