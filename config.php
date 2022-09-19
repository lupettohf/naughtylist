<?php
/***
 * 2022 ~ a monocul.us project _     _           __ _     _   
 *     _ __   __ _ _   _  __ _| |__ | |_ _   _  / /(_)___| |_ 
 *    | '_ \ / _` | | | |/ _` | '_ \| __| | | |/ / | / __| __|
 *    | | | | (_| | |_| | (_| | | | | |_| |_| / /__| \__ \ |_ 
 *    |_| |_|\__,_|\__,_|\__, |_| |_|\__|\__, \____/_|___/\__|
 *                       |___/           |___/                
 *
 * Set LOCALDB to true if this is your main/only server to monitor.
 * The GEOIPDB is propietary and you must download it whith a Maxmind account (free is enough).
 * If this script can be executed from the outside world (ex placed in your www root) make sure 
 *   to set REMOTESECRET with a good secure string (no spaces!).
 */

define("LOCALDB", true);
define("MYSQL_PDO", "mysql:host=127.0.0.1;dbname=naughylist");
define("MYSQL_USERNAME", "");
define("MYSQL_PASSWORD", "");
define("PATH_GEOIPDB","GeoLite2-City.mmdb");
define("REMOTEDB", "https://example.com/fail2sql.php");
define("REMOTESECRET", "put-long-secure-string-here");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>