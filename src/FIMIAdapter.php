<?php
class FIMIAdapter extends Tym{
	public function __construct($cfg="config.ini") {
		$this->logger=Logger::getLogger(__CLASS__);
		$ini=parse_ini_file($cfg,true);
		$this->options['host']=isset($ini['FIMI']['host'])?$ini['FIMI']['host']:'http://127.0.0.1:15001';
		$this->options['InstName']=isset($ini['FIMI']['InstName'])?$ini['FIMI']['InstName']:'BBNK';
		$this->options['Channel']=isset($ini['FIMI']['Channel'])?$ini['FIMI']['Channel']:'Email';
		$this->options['Scheme']=isset($ini['FIMI']['Scheme'])?$ini['FIMI']['Scheme']:'Platfon';
		$this->options['location']=isset($ini['FIMI']['location'])?$ini['FIMI']['location']:'http://localhost:15001';
		$this->options['uri']=isset($ini['FIMI']['uri'])?$ini['FIMI']['uri']:'http://localhost:8080/mobi/wsdl/fimi.wsdl';
		$this->options['soap_version']=isset($ini['FIMI']['soap_version'])?$ini['FIMI']['soap_version']:SOAP_1_2;
		$this->options['exceptions']=isset($ini['FIMI']['exceptions'])?$ini['FIMI']['exceptions']:true;
		$this->options['Welcome message']='Welcome to PLATfon. You are registered.';
		$this->requestHeaders=array(
			"Ver"=>"3.5"
			,"Clerk"=>"PLATFON"
			,"Password"=>"PLATFON"
			,"Product"=>"FIMI"
		);
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
		$this->checkRequestParameters($arq,array('fio','personid','sex'),__METHOD__);
		$p=$this->makeFIMIRequest(__FUNCTION__,array(
			"InstName"=>$this->options['InstName']
			,"VIP"=>"0"
			,"FIO"=>$arq['fio']
			,"PersonId"=>$arq['personid']
			,'Sex'=>$arq['sex']
		));
    $responseStr=$this->postData($p);
    //$response=new DOMDocument('1.0','utf-8');
    //$response->loadXML($responseStr);
		return array();
	}
	public function CreateAccount($arq){
		$this->checkRequestParameters($arq,array('account','personid'),__METHOD__);
		$p=$this->makeFIMIRequest(__FUNCTION__,array(
			'AvailBalance'=>0
			,'Currency'=>'810'
			,'LedgerBalance'=>0
			,'Status'=>1
      ,'Type'=>1
			,'PersonID'=>$arq['personid']
			,'Account'=>$arq['account']
		));
		$responseStr=$this->postData($p);
		//$response=new DOMDocument('1.0','utf-8');
		//$response->loadXML($responseStr);
		return array();
	}
	public function CreateVCard($arq){
		$this->checkRequestParameters($arq,array('account','nameOnCard'),__METHOD__);
		$p=$this->makeFIMIRequest(__FUNCTION__,array(
			'CardProfile'=>1
			,'NameOnCard'=>$arq['nameOnCard']
			,'Account'=>$arq['account']
		));
		$responseStr=$this->postData($p);
		$response=new DOMDocument('1.0','utf-8');
		$response->loadXML($responseStr);
		$res=array(
			'pan'=>$response->getElementsByTagNameNS('http://schemas.compassplus.com/two/1.0/fimi_types.xsd','PAN')->item(0)->nodeValue
		);
		return $res;
	}
	public function CNSCardConfig($arq){
		$this->checkRequestParameters($arq,array('pan','phone','personid'),__METHOD__);
		$p=$this->makeFIMIRequest(__FUNCTION__,array(
			'AlternativeMessaging'=>array(
				'Row'=>array(
					'Channel'=>$this->options['Channel']
					,'Address'=>$arq['phone']
					,'Scheme'=>$this->options['Scheme']
					,'TestMessage'=>$this->options['Welcome message']
					,'UseForDynAuth'=>1
					,'IsDefault'=>1
					,'Name'=>'mobile'
					,'Title'=>'mobile'
				)
			)
			,'PAN'=>$arq['pan']
			,'MBR'=>0
			,'PersonId'=>$arq['personid']
		));
		$responseStr=$this->postData($p);
		return array();
	}
	protected $_wsdl;
	protected $_fimi;
	protected function makeFIMIRequest($f,$p=null){
		$recurseArray=function($k,$v)use (&$recurseArray){
			$res='<fimi1:'.$k.'>';
			foreach($v as $kk=>$vv){
				if(is_array($vv))$res.=$recurseArray($kk,$vv);
				else $res.='<fimi1:'.$kk.'>'.$vv.'</fimi1:'.$kk.'>';
			}
			$res.='</fimi1:'.$k.'>';
			return $res;
		};
		$res=array('func'=>$f,'data'=>'<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:fimi="http://schemas.compassplus.com/two/1.0/fimi.xsd" xmlns:fimi1="http://schemas.compassplus.com/two/1.0/fimi_types.xsd">
			<soap:Header/>
			<soap:Body>
				<fimi:'.$f.'Rq>');
		$res['data'].='<fimi:Request'.$this->arrayToAttrString($this->requestHeaders);
		if(!is_null($p)&&count($p)){
			$res['data'].='>';
			foreach($p as $k=>$v){
				if(is_array($v))$res['data'].=$recurseArray($k,$v);
				else $res['data'].='<fimi1:'.$k.'>'.$v.'</fimi1:'.$k.'>';
			}
			$res['data'].='</fimi:Request>';
		}else $res['data'].='/>';
		$res['data'].='</fimi:'.$f.'Rq>
			</soap:Body>
		</soap:Envelope>';
		return $res;
	}
	protected function postData($params){
		$this->debug('REQUEST:'.$params['data']);
		$s=curl_init();
		$headers = array(
				"Content-type: text/xml;charset=\"utf-8\"",
				"Accept: text/xml",
				"Cache-Control: no-cache",
				"Pragma: no-cache",
				"SOAPAction: \"{$params['func']}\"",
				"Content-length: ".strlen($params['data'])
		);
		curl_setopt($s,CURLOPT_URL,$this->options['host']);
		curl_setopt($s,CURLOPT_TIMEOUT,$this->options['timeout']);
		curl_setopt($s,CURLOPT_CONNECTTIMEOUT,$this->options['connectiontimeout']);
		curl_setopt($s,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($s,CURLOPT_HTTPHEADER,$headers);
		curl_setopt($s,CURLOPT_POST,true);
		curl_setopt($s,CURLOPT_POSTFIELDS,$params['data']);
		curl_setopt($s,CURLOPT_VERBOSE, TRUE);
		$resp=curl_exec($s);
		$status = curl_getinfo($s,CURLINFO_HTTP_CODE);
		$err = curl_error($s);
		curl_close($s);
		$this->debug('RESPONSE:'.$resp."\n");
		if($status!="200"){
			$e=new Exception($err,$status);
			$this->error("CURL ERROR[{$err}] STATUS[{$status}]",$e);
			throw $e;
		}
		return $this->parseResponse($resp);
	}
	protected function _callFimi($func,$params){
		$this->_fimi->$func(array("Request"=>$params));
	}
};
?>
