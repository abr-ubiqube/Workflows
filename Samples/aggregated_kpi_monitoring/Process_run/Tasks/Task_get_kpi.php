<?php

/**
 * This file is necessary to include to use all the in-built libraries of /opt/fmc_repository/Reference/Common
 */
require_once '/opt/fmc_repository/Process/Reference/Common/common.php';

/**
 * List all the parameters required by the task
 */
function list_args()
{
 
}
$today = date("Y-m-d"); 

$index = "kpis-".$today;
$devices = $context["devices"];


$customer_id = $context['UBIQUBEID'];
  $customer_seq_num = substr($customer_id,4);

$updated_devices = array();

foreach ($devices as $device) {
  $id =  $device["id"];
  $dev_seq_num = substr($id,3);

  $kpi_name = $device["kpi_name"];
  $kpi_oid = $device["kpi_oid"];
 
  $cmd = "/opt/dms/bin/ncgetsnmp --id={$id} --oid={$kpi_oid}";
  $output_array = array();
  exec($cmd, $output_array);
  logToFile(debug_dump($output_array, "NCGETSNMP RES"));


  foreach ($output_array as $line) {
     logToFile("LINE: ".$line);
     $pos = strpos($line, $kpi_oid);
     logToFile("POS: <".$pos.">");
     if ($pos !== false) {

	$res_list = preg_split('@:@', $line, 0, PREG_SPLIT_NO_EMPTY);
	logToFile(debug_dump($res_list, "RES_LIST"));
	$value = trim($res_list[1]);

	$date = date("Y-m-d H:i:s"); 
        $es_doc = array("date" => $date, "customer_id"=> $customer_seq_num, "device_id" => $id, "kpi" => $kpi_name, "oid" => $kpi_oid, "value" => $value);
	$es_doc_json = json_encode($es_doc);

        $curl_cmd = "curl -s -XPOST -H 'Content-Type: application/json' http://localhost:9200/{$index}/_doc/ -d '{$es_doc_json}'";
	logToFile($curl_cmd);
	$curl_output_array = array();
	exec($curl_cmd, $curl_output_array);
	logToFile(debug_dump($curl_output_array, "CURL RES"));
     } else {
       logToFile($kpi_oid." not found in line: ".$line);
     }
  }
  sleep(2);
}

task_success('Task OK');

?>