<?php
	$db=oci_connect("mobile","n0hsH2J3Fs","10.106.1.113:1521/platfon");
    $dir="/home/bushuev/";
	$daysAgo=1;
	if (!$db) {
		$e = oci_error();
		trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
	}
	$stid = oci_parse($db, 'declare vdate date:=(sysdate-'.$daysAgo.');begin repo_dailydeals(vdate); end;');
	oci_execute($stid);
	oci_free_statement($stid);
	echo date("Y-m-d H:i:s")." executed.\n";
	$stid = oci_parse($db,"select to_char(rdate,'dd.mm.yyyy') as rdate,mdeals,loyalty,wdeals from vsb_report_1 where rdate>=trunc(sysdate)-{$daysAgo} order by rdate");
	oci_execute($stid);
	$data="";
	oci_fetch_all($stid, $res);
	$colNames=array_keys($res);
	foreach($colNames as $colName){$data.=(isset($res[$colName][0])?$res[$colName][0]:"-")."\t\t";}
	$data.="\r\n";
	file_put_contents($dir."report_daily.txt",$data,FILE_APPEND);
        //mail("v.bushuev@gmail.com","repo",$data);
	oci_free_statement($stid);
	oci_close($db);
?>
