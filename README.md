# 🎅🏻 NaughtyList

Naughtlist is a SQL logger for fail2ban inspired by [fail2sql](http://fail2sql.sourceforge.net/).

## Features: 

* PHP 7/8 Compatible (PDO)
* Uses the new Maxmind [GEOIP2 API](https://github.com/maxmind/GeoIP2-php) for IP geolocation
* Compatible with older fail2sql databases & command syntax
* Can be used as a standalone honeypot
* Can report data to a remote server (if you want to setup multiple honeypots or want to use a centralized logging server)
* Easy to mod and integrate into other IDS via command line or HTTP

## Install

#### Requirements 
* A MySQL database
* A WebServer (optional; this is if you want to use the HTTP API for reciving reports)
* PHP 7/8 with PDO and curl (if you want to send the reports to a remote script)
* A Maxmind `mmdb` IP database (Free edition is fine) 
* Composer if you are installing from the git repo

### Script Setup

Clone the repository or download the release zip file [if available]

If you cloned the repository you will also need to run the command `composer install` to install the GEOIP2 dependecy. 

We suggest to place the naughtylist folder outside /var/www; In this example our installation directory will be `/opt/naughtylist`

* Set `LOCALDB` to true if you are installing this on yor master server (default)
* Create a mysql user and database and import the `naughtylist.sql` file to create the database
* Edit `config.php` and set the database connection parameters
* If you plan to expose the script to the outside world make sure to set a secure string in `REMOTESECRET`
* Set the GEOIPDB file name/path if yours is different from the default
* If this is your slave server, set `LOCALDB` to false and set `REMOTESECRET` and `REMOTEDB` with the url and secret of the master script

### Fail2ban integration

Fail2ban uses "action" config file to trigger external programs; you can find an example inside `examples/naughtylist.conf`

* Copy the example file to `/etc/fail2ban/action.d/naughtylist.conf` 
* (optionally) change the path and settings inside the config file to match your configuration
* Edit the main jail file; this can vary from distro to distro, but it's usually the only file inside `/etc/fail2ban/jail.d`

Example of my jail config:

	[sshd]
	enabled = true
	action = naughtylist

* For more infos check the fai2ban documentation. 

### Honeypot mode

Naughtylist can be included inside HTML and PHP pages to act as a honeypot logger. 

Example:


	<?php
		include '../path/to/naughtlist/naughtylist.php';
		// Define HONEYPOT to true to enable honeypot mode.
		define('HONEYPOT', true);

		/* Optionally, add glue logic before calling the honeypot method;
		 * for example you check if some data is posted.
		 */

		// Call the honeypot(name, protocol, port) method to log the incident.
		honeypot("wordpress-login", "https", 443);

		//DONE!
	?>

<!-- INSERT FAKE HONEYPOT LURING PAGE --> 


### Remote mode / HTTP API

This script can act as a client and as server to store and recive reports from HTTP;

If you have the script exposed on a webserver you can call it via HTTP and add reports, simply make a `POST` request with the following parameters:

- name ~ the service name
- protocol ~ the service protocol
- port ~ the service port
- ip ~ the offender IP
- key ~ your secret key set in config.php

This API is also used when you want to use the script in remote database mode, where it will call via curl your remote server, make sure to set the same SECRET on both machines.





_Made in 🇮🇹 with ❤️ to keep the folks at [monoculus](monocul.us) safe!_