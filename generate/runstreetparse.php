<?php

/**
 * Generate street SQL statements from origin file
 */

/**
 * Class and Function List:
 * Function list:
 * Classes list:
 */

require_once "PHPCoord/OSRef.php";
require_once "PHPCoord/LatLng.php";
require_once "PHPCoord/RefEll.php";
require_once "CodePoint.php";

$codepoint = new CodePoint();

$codepoint->loadStreetData();
?>