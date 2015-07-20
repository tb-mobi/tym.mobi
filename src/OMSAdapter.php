<?php

class OMSAdapter extends Tym{
  public function __construct($cfg="config.ini"){
      $this->logger=Logger::getLogger(__CLASS__);
      $ini=parse_ini_file($cfg,true);
      $this->options['host']=isset($ini['OMS']['host'])?$ini['OMS']['host']:'http://127.0.0.1:1238';
  }
  public function PersonCreate($param){
    $p=array(
    'data'=>'<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
      <soap:Header>
        <ns2:RequestHeader xmlns:ns2="http://schemas.compassplus.com/twcms/1.0/omsi.xsd">
          <RType>Do</RType>
          <Cpimode timeout="59">Sync</Cpimode>
          <Branch>1</Branch>
          <Station>50</Station>
        </ns2:RequestHeader>
      </soap:Header>
      <soap:Body>
        <ns2:Request xmlns:ns2="http://schemas.compassplus.com/twcms/1.0/omsi.xsd" >
          <Request>
            <PERSON>
              <Command Action="Create" ResObjectInfoType="FullInfo" />
              <NAME>'.$param['sname'].' '.$param['fname'].' '.$param['mname'].'</NAME>
              <SEX>М</SEX>
              <Phone>'.$param['phone'].'</Phone>
              <RESIDENT>TRUE</RESIDENT>
              <CUSTOMATTRIBUTES>
                <ATTRIBUTE ID="IDENTITY">1</ATTRIBUTE>
              </CUSTOMATTRIBUTES>
            </PERSON>
          </Request>
        </ns2:Request>
      </soap:Body>
    </soap:Envelope>');
    return $this->postData($p);
  }
  public function UserRegistration($arq){
    $this->checkRequestParameters($arq,array('fio','phone','email'),__METHOD__);
    $p=array('data'=>'<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
    <soap:Header>
        <ns2:RequestHeader xmlns:ns2="http://schemas.compassplus.com/twcms/1.0/omsi.xsd">
            <RType>Do</RType>
            <Cpimode timeout="59">Sync</Cpimode>
            <Branch>1</Branch>
            <Station>50</Station>
        </ns2:RequestHeader>
    </soap:Header>
    <soap:Body>
        <ns2:Request xmlns:ns2="http://schemas.compassplus.com/twcms/1.0/omsi.xsd" >
            <Request>
                <UserDefinedCommand Id="UserRegistration">
                    <Command Action="Execute">
                    </Command>
                    <UserDefinedCommandParams Type="1">
                        <Command Action="Init"/>
                        <Param Id="Name">'.$arq['fio'].'</Param>
                        <Param Id="Phone">'.$arq['phone'].'</Param>
                        <Param Id="Email">'.$arq['email'].'</Param>
                    </UserDefinedCommandParams>
                </UserDefinedCommand>
            </Request>
        </ns2:Request>
    </soap:Body>
</soap:Envelope>');
    $responseStr=$this->postData($p);
    $response=new DOMDocument('1.0','utf-8');
    $response->loadXML($responseStr);

    $nodes=$response->getElementsByTagName('PARAM');
    $res=array(
      'account'=>$this->searchIn(array('nodes'=>$nodes,'name'=>'ACCOUNTNO'))
      ,'personid'=>$this->searchIn(array('nodes'=>$nodes,'name'=>'PERSON'))
    );
    return $res;
  }

}
?>
