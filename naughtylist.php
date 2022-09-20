<?php
/***
 * 2022 ~ a monocul.us project _     _           __ _     _   
 *     _ __   __ _ _   _  __ _| |__ | |_ _   _  / /(_)___| |_ 
 *    | '_ \ / _` | | | |/ _` | '_ \| __| | | |/ / | / __| __|
 *    | | | | (_| | |_| | (_| | | | | |_| |_| / /__| \__ \ |_ 
 *    |_| |_|\__,_|\__,_|\__, |_| |_|\__|\__, \____/_|___/\__|
 *                       |___/           |___/                
 * You should not modify this file unless you know what you are doing;
 * settings can be found in the config.php file.
 */

require 'vendor/autoload.php';
include 'config.php';
use GeoIp2\Database\Reader;

function sendReport($name, $protocol, $port, $ip)
{
	$ch = curl_init(REMOTEDB);
	$postData = [
		'name' => $name,
		'protocol' => $protocol,
		'port' => $port,
		'ip' => $ip,
		'key' => REMOTESECRET
	];

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
	$response = curl_exec($ch);
	curl_close($ch);
}

function updateReport($db, $report_id)
{
	$QUERY = "UPDATE naughtylist set count=count+1 where id = :id";

	$db->prepare($QUERY)->execute(
		[
			'id' => $report_id
		]
	);
}

function addReport($db, $geoip, $name, $protocol, $port, $ip)
{
	$QUERY = "INSERT INTO naughtylist VALUES (:id, :name, :protocol, :port, :ip, :count, :longitude, :latitude, :countrycode, :geo)";
	$geoipRecord = $geoip->city($ip);

	$db->prepare($QUERY)->execute(
		['id' => 0,
		 'name' => $name, 
		 'protocol' => $protocol,
		 'port' => intval($port),
		 'ip' => $ip,
		 'count' => 1,
		 'longitude' => $geoipRecord->location->longitude,
		 'latitude' => $geoipRecord->location->latitude,
		 'countrycode' => $geoipRecord->country->isoCode,
		 'geo' => $geoipRecord->mostSpecificSubdivision->name . " " . $geoipRecord->city->name
		]
	);

}

function reportExists($db, $name, $protocol, $port, $ip)
{
	$QUERY = "SELECT id FROM naughtylist WHERE name = :name AND protocol = :protocol AND port = :port AND ip = :ip LIMIT 1";

	$stmt = $db->prepare($QUERY);
	$stmt->execute(
		['name' => $name, 
		 'protocol' => $protocol,
		 'port' => intval($port),
		 'ip' => $ip
		]
	);
	if(!$stmt) { return false; }
	return $stmt->fetchColumn();
}

function honeypot($name, $protocol, $port)
{
	/* Credits to https://stackoverflow.com/a/13646848 */
	/* This DOES NOT resolve VPNs. Only transparent HTTP proxy */
	if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
        if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')>0) {
            $addr = explode(",",$_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($addr[0]);
        } else {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
    }
    else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

	if(LOCALDB) { 
		$db = new PDO(MYSQL_PDO, MYSQL_USERNAME, MYSQL_PASSWORD); 
		$geoip = new Reader(PATH_GEOIPDB);

		$report = reportExists($db, $name, $protocol, $port, $ip);

		if($report != false)
		{
			updateReport($db, $report);
		} else {
			addReport($db, $geoip, $name, $protocol, $port, $ip);		
		}
	} else {
		sendReport($name, $protocol, $port, $ip);
	}
}

function serve()
{
	if(LOCALDB) { 
		$db = new PDO(MYSQL_PDO, MYSQL_USERNAME, MYSQL_PASSWORD); 
		$geoip = new Reader(PATH_GEOIPDB);
	}
	/* Running from console */
	if(isset($_SERVER["argv"][1]) && isset($_SERVER["argv"][2]) && isset($_SERVER["argv"][3]) && isset($_SERVER["argv"][4]))
	{
		$name = $_SERVER["argv"][1];
		$protocol = $_SERVER["argv"][2];
		$port = $_SERVER["argv"][3];
		$ip = $_SERVER["argv"][4];
		if(LOCALDB)
		{
			$report = reportExists($db, $name, $protocol, $port, $ip);

			if($report != false)
			{
				updateReport($db, $report);			
			} else {
				addReport($db, $geoip, $name, $protocol, $port, $ip);
			}
		} else {
			sendReport($name, $protocol, $port, $ip);
		}
	/* Remote query */ 
	} elseif (isset($_POST['name']) && isset($_POST['protocol']) && isset($_POST['port']) && isset($_POST['ip']) && isset($_POST['key']) && LOCALDB) {
		$name = $_POST['name'];
		$protocol = $_POST['protocol'];
		$port = $_POST['port'];
		$ip = $_POST['ip'];	

		if($_POST['key'] != REMOTESECRET) {die(0);}
		$report = reportExists($db, $name, $protocol, $port, $ip);
		if($report != -1)
		{
			updateReport($db, $report);
			echo json_encode(["status" => "updated"]);	
		} else {
			addReport($db, $geoip, $name, $protocol, $port, $ip);
			echo json_encode(["status" => "added"]);			
		}
	}

}

if(!defined("HONEYPOT"))
{
	serve();
}

