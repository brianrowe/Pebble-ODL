<?php

/******************************************************************************
 *
 * Pebble Watch Open Device Locator App
 * pebble.js Framework
 * @author Brian Rowe - Fort Wayne Open Device Lab
 *
 * @url http://fwdevicelab.com
 * @email: brian.rowe@fwdevicelab.com
 *
 * Open Device Lab Directory API - http://opendevicelab.com/
 * 
 * For More information about Open Device Labs visit
 * 
 * http://opendevicelab.com/
 * http://lab-up.org/
 * 
 ******************************************************************************/
/******************************************************************************
 * 
 * Usage
 * 
 * @call http://yourserver.com/Pebble-ODL/index.php?action=list&lat=xx.xxxxxxx&lng=-xx.xxxxxxx&dist=200&unit=miles
 * 
 * @actions
 * action = [ list / detail / import ]
 * 
 * @parameters
 * lat = [ xx.xxxxxxx ]
 * lng = [ -xx.xxxxxxx ]
 * dist = [ radius to search]
 * unit = [ km or miles ]
 * id = [ ID of selected lab ]
 *
 ******************************************************************************/

include('db.php');

if( isset($_POST['action']) && !empty($_POST['action']) || isset($_GET['action']) && !empty($_GET['action'])) {
  if (isset($_POST['action'])){
    $action = $_POST['action'];
  }
  if (isset($_GET['action'])){
    $action = $_GET['action'];
  }
  switch($action) {
    case 'list' : GetList(); break;
    case 'detail' : GetDetail(); break;
    case 'import' : ImportData(); break;
  }
};

function ImportData(){
	echo 'Clear Data';
	mysql_query('TRUNCATE TABLE locations;');

	echo 'Import Data';
	$jsonurl = "http://api.opendevicelab.com/";
	$json = file_get_contents($jsonurl);
	$json_output = json_decode($json);

	foreach ($json_output  as $result) {
		$devices = implode(",", $result->brands_available);
    	mysql_query("INSERT INTO locations (name,description,type,status,city,organization,street_address,state,country,zip,lat,lng,url,number_of_devices,brands_available) VALUES ( '".$result->name."', '".$result->description."', '".$result->type."', '".$result->status."', '".$result->loc->city."', '".$result->loc->organization."', '".$result->loc->street_adress."', '".$result->loc->state."', '".$result->loc->country."', '".$result->loc->zip."', '".$result->loc->latlng->lat."', '".$result->loc->latlng->lng."', '".$result->urls[0]->url."', '".$result->number_of_devices."', '".$devices."')");
	}
};

function GetDetail(){
	$Clat = $_GET['lat'];
	$Clng = $_GET['lng'];
	$LabID = $_GET['id'];
	if($_GET['unit'] == 'km'){
		$CUnit = $_GET['unit'];
		$dispUnit = ' km';
	} else {
		$CUnit = $_GET['unit'];
		$dispUnit = ' miles';
	}	
	
	if ($Clat && $Clng) {
		$GetLocations = mysql_query('SELECT id,name,description,type,status,city,organization,street_address,state,country,zip,lat,lng,url,number_of_devices,brands_available, ( 3959 * acos( cos( radians('.$Clat.') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians('.$Clng.') ) + sin( radians('.$Clat.') ) * sin( radians( lat ) ) ) ) as distance FROM locations WHERE id = '.$LabID. '');
	} else {
		$GetLocations = mysql_query('SELECT * FROM locations WHERE id = '.$LabID.'');
	}

	$JSON = '';
	if ($GetLocations) {
		$num_LocationRows = mysql_num_rows($GetLocations);
		$JSON .= '{';
		$JSON .= '"status": "' . 'ok' . '",';
		$JSON .= '"count": "' . $num_LocationRows . '",';
		$JSON .= '"lab": [';
		
		$LabCount = 1;
		while($LocationsRow = mysql_fetch_array($GetLocations)) {
			$JSON .= '{';
			if($_GET['unit'] == 'km'){
				$dist = round($LocationsRow['distance'] * 1.609344, 0);
			} else {
				$dist = round($LocationsRow['distance'], 0);
			}
			$JSON .= '"id":' . json_encode($LocationsRow['id']) . ',';
			$JSON .= '"name":' . json_encode($LocationsRow['name']) . ',';
			$JSON .= '"distance":' . json_encode($dist . $dispUnit) . ',';
			$JSON .= '"description": ' . json_encode($LocationsRow['description']) . ',';
			$JSON .= '"type":' . json_encode($LocationsRow['type']) . ',';
			$JSON .= '"status":' . json_encode($LocationsRow['status']) . ',';
			$JSON .= '"organization":' . json_encode($LocationsRow['organization']) . ',';
			$JSON .= '"street_address":' . json_encode($LocationsRow['street_address']) . ',';
			$JSON .= '"city":' . json_encode($LocationsRow['city']) . ',';
			$JSON .= '"state":' . json_encode($LocationsRow['state']) . ',';
			$JSON .= '"zip":' . json_encode($LocationsRow['zip']) . ',';
			$JSON .= '"country":' . json_encode($LocationsRow['country']) . ',';
			$JSON .= '"lat":' . json_encode($LocationsRow['lat']) . ',';
			$JSON .= '"lng":' . json_encode($LocationsRow['lng']) . ',';
			$JSON .= '"url":' . json_encode($LocationsRow['url']) . ',';
			$JSON .= '"number_of_devices":' . json_encode($LocationsRow['number_of_devices']) . ',';

			$brands = explode(',',$LocationsRow['brands_available']);
			foreach($brands as $key=>$value) {$brands[$key]=array('brand'=>$value);}
			$JSON .= '"brands_available":' . json_encode($brands);

			if ($LabCount < $num_LocationRows){
			       $JSON .= '},';
			 } else {
			 	 $JSON .= '}';
			 }
			$LabCount ++;
		}

		$JSON .= ']}';	
	} else {
		$JSON .= '{';
		$JSON .= '"status": "' . 'error' . '"';
		$JSON .= '}';
	}

	echo $JSON;
};

function GetList(){
	$Clat = $_GET['lat'];
	$Clng = $_GET['lng'];
	$RadiusDist = $_GET['dist'];
	if($_GET['unit'] == 'km'){
		$CUnit = $_GET['unit'];
		$dispUnit = ' km';
	} else {
		$CUnit = $_GET['unit'];
		$dispUnit = ' miles';
	}	
	
	if ($Clat && $Clng) {
		$GetLocations = mysql_query('SELECT id,name, ( 3959 * acos( cos( radians('.$Clat.') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians('.$Clng.') ) + sin( radians('.$Clat.') ) * sin( radians( lat ) ) ) ) as distance FROM locations HAVING distance < '.$RadiusDist.' ORDER BY distance');
	} else {
		$GetLocations = mysql_query('SELECT id,name FROM locations ORDER BY name');
	}

	$JSON = '';
	if ($GetLocations) {
		$num_LocationRows = mysql_num_rows($GetLocations);
		$JSON .= '{';
		$JSON .= '"status": "' . 'ok' . '",';
		$JSON .= '"count": "' . $num_LocationRows . '",';
		$JSON .= '"labs": [';
		
		$LabCount = 1;
		while($LocationsRow = mysql_fetch_array($GetLocations)) {
			$JSON .= '{';
			if($_GET['unit'] == 'km'){
				$dist = round($LocationsRow['distance'] * 1.609344, 0);
			} else {
				$dist = round($LocationsRow['distance'], 0);
			}
			$JSON .= '"id":' . json_encode($LocationsRow['id']) . ',';
			$JSON .= '"name":' . json_encode($LocationsRow['name']) . ',';
			$JSON .= '"distance":' . json_encode($dist . $dispUnit);
			
			if ($LabCount < $num_LocationRows){
			       $JSON .= '},';
			 } else {
			 	 $JSON .= '}';
			 }
			$LabCount ++;
		}

		$JSON .= ']}';	
	} else {
		$JSON .= '{';
		$JSON .= '"status": "' . 'error' . '"';
		$JSON .= '}';
	}

	echo $JSON;
};

?>