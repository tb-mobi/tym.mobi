<?php
$request=array(
  <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
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
  </soap:Envelope>
);
?>
