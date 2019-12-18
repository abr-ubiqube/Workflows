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
 
  create_var_def('devices.0.id', 'String');
  create_var_def('devices.0.kpi_name', 'String');
  create_var_def('devices.0.kpi_oid', 'String');
  create_var_def('devices.0.community', 'String');
}

$devices = $context["devices"];

$updated_devices = array();
$index = 0;
foreach ($devices as $device) {
  $id =  $device["id"];
  $dev_seq_num = substr($id,3);

  $response = _device_read_by_id ($dev_seq_num);
  $response = json_decode($response, true);
  if ($response['wo_status'] !== ENDED) {
	$response = json_encode($response);
	echo $response;
	exit;
  }
  logToFile(debug_dump($response['wo_newparams'], "RESPONSE"));
  $community = $response['wo_newparams']['snmpCommunity'];
  $device["community"] = $community;

  $updated_devices[$index]["id"] = $id;
  $updated_devices[$index]["kpi_name"] = $device['kpi_name'];
  $updated_devices[$index]["kpi_oid"] = $device['kpi_oid'];;
  $updated_devices[$index]["community"] = $community;
  $index++;
}

$context["devices"] = $updated_devices;

task_success('Task OK');

?>