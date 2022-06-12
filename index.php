<?php


$apiRequestData = file_get_contents('php://input');
$apiRequestData = json_decode($apiRequestData, true);
foreach($apiRequestData as $country)
{
	$availableSlots = getAvailableSlots($country);
	print_r($availableSlots);
	die;
}



function getAvailableSlots($country)
{
	$timezone = \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $country['CC']);
	
	#setting timezone for given country
	if(isset($timezone[0]))
	{
		date_default_timezone_set($timezone[0]);
	}
	
	$givenStartTime = $country['from'];
	$givenEndTime = $country['to'];
	$workingHours = round((strtotime($givenEndTime) - strtotime($givenStartTime))/3600, 1);
	
	$currentStartTime = date('Y-m-d H:i:s');
	$currentEndTime = date('Y-m-d H:i:s', strtotime("$workingHours hour"));

	/*$givenStartTime = '9';
	$givenEndTime = '15';

	$currentStartTime = '11';
	$currentEndTime = '17';*/


	$availableSlotStartTime = ($currentStartTime > $givenStartTime ?  $currentStartTime : $givenStartTime);
	$availableSlotEndTime = ($currentEndTime > $givenEndTime ?  $givenEndTime : $currentEndTime);

	return [
		'availableSlotStartTime' => $availableSlotStartTime,
		'availableSlotEndTime' => $availableSlotEndTime
	];
} 


?>