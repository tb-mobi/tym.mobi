<?php
class MClient extends Tym{
  public function __construct(){
    $this->logger=Logger::getLogger(__CLASS__);
    $this->oms=new OMSAdapter();
    $this->fimi=new FIMIAdapter();
    $this->vtbi=new VTBIAdapter();
  }
  public function Register($arq){
    $this->checkRequestParameters($arq,array('fname','mname','sname','phone','email'),__METHOD__);
    $landing=$arq;
    $landing['login']=$landing['phone'];
    $landing['fio']=$landing['sname'].' '.$landing['fname'].' '.$landing['mname'];
    $landing['nameOnCard']=strtoupper($landing['fname'].' '.$landing['sname']);
    $landing['sex']='M';
    $landing['pin']='1111';
    try{
      $this->_user_data=array_merge($landing,$this->oms->UserRegistration($landing));
      $this->_user_data=array_merge($this->_user_data,$this->fimi->CreatePerson($this->_user_data));
      $this->_user_data=array_merge($this->_user_data,$this->fimi->CreateAccount($this->_user_data));
      $this->_user_data=array_merge($this->_user_data,$this->fimi->CreateVCard($this->_user_data));
      $this->_user_data=array_merge($this->_user_data,$this->fimi->CNSCardConfig($this->_user_data));
      $this->_user_data=array_merge($this->_user_data,$this->vtbi->CreateTBCustomer($this->_user_data));
      $this->_user_data=array_merge($this->_user_data,$this->vtbi->Logon($this->_user_data));
    }
    catch(TymException $e){
      $logger->error($e->getMessage());
    }
    catch(Exception $e){
      $logger->error($e->getMessage());
    }

  }
  protected $oms=null;
  protected $fim=nulli;
  protected $vtb=nulli;
  protected $_user_data=null;
}
?>
