<?php

	error_reporting(E_ALL);
	ini_set("display_errors","on");
	ini_set("html_errors","On");

	require_once "PHPCoord/OSRef.php";
	require_once "PHPCoord/LatLng.php";
	require_once "PHPCoord/RefEll.php";
	require_once "CodePoint.php";

	$codepoint = new CodePoint();

	$codepoint -> loadStreetData();
	

	
	
	
?>