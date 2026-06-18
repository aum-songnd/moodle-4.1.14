<?php
// This file is NOT a part of Moodle - http://moodle.org/
//
// This client for Moodle 2 is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//

/**
 * REST client for Moodle 2
 * Return JSON or XML format
 *
 * @authorr Jerome Mouneyrac
 */

/// SETUP - NEED TO BE CHANGED
$token = '55418cfec87c72575438a5353ae403e5';
$domainname = 'http://192.168.1.214/';
$functionname = 'local_th_mobile_service_xet_dkdt';

// REST RETURNED VALUES FORMAT
$restformat = 'json'; //Also possible in Moodle 2.2 and later: 'json'
//Setting it to 'json' will fail all calls on earlier Moodle version

//////// moodle_user_create_users ////////

/// PARAMETERS - NEED TO BE CHANGED IF YOU CALL A DIFFERENT FUNCTION
$ma_mon = "PSST113-201025";

$dshv = [
	'20-2-7380107-1684',
	'20-2-7340301-1686',
	'20-2-7480201-1691',
	'20-2-7220201-1404',
	'20-2-7480201-1365',
	'20-2-7340301-1628',
	'20-2-7380107-1615',
	'20-2-7380107-1616',
];

$params = array('ma_mon' => $ma_mon, 'dshv' => $dshv);

/// REST CALL
header('Content-Type: text/json');
$serverurl = $domainname . 'webservice/rest/server.php' . '?wstoken=' . $token . '&wsfunction=' . $functionname;
require_once './curl.php';
$curl = new curl;
//if rest format == 'xml', then we do not add the param for backward compatibility with Moodle < 2.2
$restformat = ($restformat == 'json') ? '&moodlewsrestformat=' . $restformat : '';
$resp = $curl->post($serverurl . $restformat, $params);
print_r($resp);
print_r("<br>");
print_r("<br>");
$curl = curl_init();

$data_string = "ma_mon=PSST113-201025&dshv[0]=20-2-7380107-1684&dshv[1]=20-2-7340301-1686&dshv[2]=20-2-7480201-1691&dshv[3]=20-2-7220201-1404&dshv[4]=20-2-7480201-1365&dshv[5]=20-2-7340301-1628&dshv[6]=20-2-7380107-1615&dshv[7]=20-2-7380107-1616";

print_r($serverurl);
print_r("<br>");
print_r("<br>");
print_r($data_string);
print_r("<br>");
print_r("<br>");

$arr = array(
	CURLOPT_URL => "$serverurl" . "$restformat",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 0,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "POST",
	CURLOPT_POSTFIELDS => "json_encode($params)",
	CURLOPT_HTTPHEADER => array("Content-Type: application/json", 'Content-Length: ' . strlen($data_string)),
);

print_r($arr);
print_r("<br>");
print_r("<br>");

curl_setopt_array($curl, $arr);

$response = curl_exec($curl);

print_r($response);

curl_close($curl);
