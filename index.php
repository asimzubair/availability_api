<?php

#Getting json post values
$apiRequestData = file_get_contents('php://input');
$apiRequestData = json_decode($apiRequestData, true);
$processedData = calculateAvailability($apiRequestData);
echo $processedData;


#Calculate availability based on available slots in each countrycode
function calculateAvailability($apiRequestData)
{
	$allAvailableSlots = [];
	$responseDate = '';
	foreach($apiRequestData as $country)
	{
		$responseDate = date('Y-m-d', strtotime($country['from']));
		$availableSlots = getAvailableSlots($country);
		$allAvailableSlots[] = $availableSlots;
	}

	$result = array_intersect($allAvailableSlots[0], $allAvailableSlots[1], $allAvailableSlots[2]);
	$meetingSlots = [];
	if(count($result) > 1)
	{
		$meetingSlots = [
			'from' => $responseDate.' '.current($result).':00:00',
			'to' => $responseDate.' '.(end($result)+1).':00:00'
		];
	}
	return json_encode(array_values($meetingSlots));
}

#Available slots for single country
function getAvailableSlots($country)
{
	$timezone = \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $country['CC']);
	
	#setting timezone for given country
	if(isset($timezone[0]))
	{
		date_default_timezone_set($timezone[0]);
	}
	
	$givenStartTime = date('Y-m-d H:i:s', strtotime($country['from']));
	$givenEndTime = date('Y-m-d H:i:s', strtotime($country['to']));
	$workingHours = round((strtotime($givenEndTime) - strtotime($givenStartTime))/3600, 1);
	$allGivenSlots = getHourBreakup($givenStartTime, $givenEndTime);

	$currentStartTime = date('Y-m-d H:i:s');
	$currentEndTime = date('Y-m-d H:i:s', strtotime("$workingHours hour"));
	$allCurrentSlots = getHourBreakup($currentStartTime, $currentEndTime);

	$availableSlots = array_intersect($allGivenSlots, $allCurrentSlots);

	return $availableSlots;
} 

#breaky it into array of hours
function getHourBreakup($startTime, $endTime)
{
	$timeSlots = [];
    while ($startTime != $endTime)
    {
        $timeSlots[] = date('H', strtotime($startTime));
    	$startTime = date('Y-m-d H:i:s', strtotime('+60 minutes', strtotime($startTime)));
    }

    return $timeSlots;
}

?>