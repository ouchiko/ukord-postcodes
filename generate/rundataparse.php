<?php
/**
* Class and Function List:
* Function list:
* Classes list:
*/

/**
 * Generates the areas and postcodes
 */

require_once "PHPCoord/OSRef.php";
require_once "PHPCoord/LatLng.php";
require_once "PHPCoord/RefEll.php";
require_once "CodePoint.php";

$codepoint = new CodePoint();

$codepoint->loadAreaCodes();
$codepoint->loadAreaNames();
$codepoint->loadPostcodeData();

$codepoint->save($codepoint->generateSql($codepoint->areacodes, "CodePoint", "AreaCodes") , "areatypes");
$codepoint->save($codepoint->generateSql($codepoint->areas, "CodePoint", "Areas") , "areas");
?>