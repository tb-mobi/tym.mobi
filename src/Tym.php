<?php

class Tym{
  public function json(){
		if(is_null($this->_rawObject)){$this->logger->warn("response is null");return;}
		$this->_toStr=json_encode($this->_rawObject);
		$this->logger->debug($this->_toStr);
		return $this->_toStr;
	}
	public function xml(){
		if(is_null($this->_rawObject)){$this->warn("response is null");return;}
		$xml = new SimpleXMLElement();
		array_walk_recursive($this->_rawObject, array ($xml));
		$this->_toStr=$xml->asXML();
		$this->debug($this->_toStr);
		return $this->_toStr;
	}
	public static function toXml($arr,$root='Envelope'){
		if(is_null($this->_rawObject)||!is_array($arr)){$this->warn("input object {$arr} is null");return;}
		$xml = new SimpleXMLElement();
		array_walk_recursive($this->_rawObject, array ($xml));
		$this->_toStr=$xml->asXML();
		$this->debug($this->_toStr);
		return $this->_toStr;
	}
	public static function StrtoHex($str=""){
		$res="";
		for($i=0;$i<strlen($str);$i++){
			$chr=ord($str[$i]);
			$chrh=strtoupper(dechex((int)$chr));
			$chrh=(strlen($chrh)<2?'0':'').$chrh;
			$res.=$chrh;
		}
		return $res;
	}
	public static function HexToStr($str=""){
		$res="";
		for($i=0;$i<strlen($str);$i+=2){
			$res.=chr(hexdec($str[$i].$str[$i+1]));
		}
		return $res;
	}
  public function _get(){
    //return $this->object2Array($this);
    $res=array();
    if(property_exists(get_called_class(),"_user_data")){
      foreach($this->_user_data as $item){
        array_push($res,$item->get());
      }
      return $res;
    }
    $res=array();
    if(property_exists(get_called_class(),"_list")){
      foreach($this->_list as $item){
        array_push($res,$item->get());
      }
      return $res;
    }
    foreach(get_class_vars(get_called_class()) as $p=>$v){
      if(is_object($this->$p)){
        if(class_exists(get_class($this->$p)))$res[$p]=$this->$p->_get();//other object of TymLib
      }
      else if(is_array($this->$p)){
        $arr=$this->$p;
        if(isset($arr["year"])&&isset($arr["month"]))
          $res[$p]=$arr["year"].'-'.$arr["mon"].'-'.$arr["mday"].'T'.$arr["hours"].':'.$arr["minutes"].':'.$arr["seconds"].'.99'.date('O');//datetime object
      }
      else $res[$p]=$this->$p;
    }
    return $res;
  }

	protected $options=array(
    'host'=>'http://localhost:1238'
    ,'timeout'=>24
    ,'connectiontimeout'=>16
    ,'station'=>50
		,'trace'=>0
  );
	protected $requestHeaders=array(
		"Ver"=>"4"
		,"Product"=>"TB"
		,'STAN'=>0
    ,'RetAddress'=>'172.17.31.25'
	);
  protected $curlObj;
	protected $_rawObject;
	protected $logger;
	protected function info($str){
		$this->logger->info($str);
	}
	protected function debug($str){
		$this->logger->debug($str);
	}
	protected function warn($str){
		$this->logger->warn($str);
	}
	protected function error($str,$ex){
		$this->logger->error($str,$ex);
	}
	protected function checkRequestParameters($arq,$needed,$func=__METHOD__){
		foreach($needed as $need){
			if(!isset($arq[$need])){
				$e=new Exception('Parameter '.$need.' is requered for '.$func);
				$this->error($e->getMessage(),$e);
				throw $e;
			}
		}
		$this->info($func.'['.$this->arrayToAttrString($arq).']');
		return true;
	}
	protected function arrayToAttrString($arr){
		$res="";
		foreach($arr as $k=>$v){
			if($k=="STAN"){
				$v+=1;
				$this->requestHeaders['STAN']+=1;
			}
			$res.=" {$k}=\"{$v}\"";
		}
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
        "SOAPAction: \"Request\"",
        "Content-length: ".strlen($params['data'])
    );
    curl_setopt($s,CURLOPT_URL,$this->options['host']);
    curl_setopt($s,CURLOPT_TIMEOUT,$this->options['timeout']);
    curl_setopt($s,CURLOPT_CONNECTTIMEOUT,$this->options['connectiontimeout']);
    curl_setopt($s,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($s,CURLOPT_HTTPHEADER,$headers);
    curl_setopt($s,CURLOPT_POST,true);
    curl_setopt($s,CURLOPT_POSTFIELDS,$params['data']);
    curl_setopt($s,CURLOPT_VERBOSE, $this->options['trace']);
    $this->response=curl_exec($s);
		if(curl_errno($s)==CURLE_COULDNT_CONNECT){
			throw new TymException('Couldnt connect.');
		}
    $status = curl_getinfo($s,CURLINFO_HTTP_CODE);
    $err = curl_error($s);
    curl_close($s);
    $this->debug('RESPONSE:'.$this->response."\n");
    $this->readFault($this->response);
    return $this->parseResponse($this->response);
  }
	protected function readFault($str){
		$fault=new DOMDocument('1.0','utf-8');
    $fault->loadXML($str);
		if($fault->getElementsByTagName('Fault')->length){
			list($code,$message)=array(-1,'');
			$code=$this->searchIn(array('nodes'=>$fault->getElementsByTagName('Value'),'name'=>'Value','type'=>'element'));
			$message=$this->searchIn(array('nodes'=>$fault->getElementsByTagName('Text'),'name'=>'Text','type'=>'element'));
			$code=is_string($code)?-1:$code;
			$e=new TymException($message,$code);
			$this->error('['.$code.']'.$message,$e);
			throw $e;
		}
	}
	protected function parseResponse($rsp){
		$res=$rsp;
		return $res;
	}
	protected function searchIn($pars){
		if(!isset($pars['nodes'])){
			$e=new TymException('No node list, cant find parameter.');
			$this->error('No node list, cant find parameter.',$e);
			throw $e;
		}
			if(!isset($pars['name'])){
				$e=new Exception('No name, cant find parameter.');
				$this->error('No name, cant find parameter.',$e);
				throw $e;
			}
		$nodes=$pars['nodes'];
		$name=$pars['name'];
		$type=isset($pars['type'])?$pars['type']:"byid";
		for($i=0;$i<$nodes->length;++$i){
			$node=$nodes->item($i);
			//var_dump($node->attributes->getNamedItem("ID"));
			if($type==="element"){
				if($node->nodeType==XML_ELEMENT_NODE)return $node->nodeValue;
			}
			else if($type==="byid"){
				if($node->hasAttributes()
					&&$node->attributes->getNamedItem("ID")!=null
					&&$node->attributes->getNamedItem("ID")->nodeValue==$name
					){
					return $node->nodeValue;
				}
			}
		}
		$e=new Exception('Parameter '.$name.' not found.');
		$this->error($e->getMessage(),$e);
		throw $e;
	}
};
?>
