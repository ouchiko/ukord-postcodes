<?php
/**
 * Class and Function List:
 * Function list:
 * - readDirectoryFiles()
 * - dataFileRead()
 * - spaceFill()
 * - loadPostcodeData()
 * - (()
 * - loadAreaCodes()
 * - (()
 * - save()
 * - loadAreaNames()
 * - (()
 * - generateSql()
 * Classes list:
 * - CodePoint
 */

class CodePoint {
    
    private $file_list = array(
        "areacodes" => "definitions/areacodes.tab",
        "areas" => "definitions/areas.tab"
    );
    
    public $postcode_reference = array(
        "postcode",
        "quality",
        "easting",
        "northing",
        "country_code",
        "nhs_region_ha_code",
        "nhs_ha_code",
        "admin_county_code",
        "admin_district_code",
        "admin_ward_code"
    );
    
    public $areacodes = array();
    public $areas = array();
    public $postcodes = array();
    private $current_base = "null";
    
    /**
     * Read in the directory files.
     * @param type $directory
     * @return type
     */
    private function readDirectoryFiles($directory) {
        if ($handle = opendir('pcdata')) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $files[] = $entry;
                }
            }
            closedir($handle);
        }
        return $files;
    }
    
    /**
     * Reads in a specified data file, breaks into lines and then
     * runs a callback function against that line of data.
     * @param type $filename
     * @param type $callback
     * @return type
     */
    private function dataFileRead($filename, $callback) {
        if (file_exists($filename)) {
            $content = file_get_contents($filename);
            $data_rows = explode("\n", $content);
            foreach ($data_rows as $row) $callback($row);
        } 
        else {
            return false;
        }
    }
    
    private function spaceFill($str) {
        return substr($str, 0, strlen($str) - 3) . " " . substr($str, strlen($str) - 3, strlen($str));
    }
    
    /**
     * Load postcode data sets
     * @return type
     */
    public function loadPostcodeData() {
        $callback = function ($row) {
            
            $reference = array();
            $parsed_array = str_getcsv($row, ",", '"');

            foreach ($parsed_array as $id => $value) $reference[$this->postcode_reference[$id]] = $value;

            if ($reference && isset($reference['easting'])) {
            	$reference['postcode'] = str_replace(" ", "", $reference['postcode']);
                $osref = new PHPCoord\OSRef($reference["easting"], $reference["northing"]);
                $latlng = $osref->toLatLng();
                $reference['latitude'] = $latlng->lat;
                $reference['longitude'] = $latlng->lng;
                $reference['formatted'] = $this->spaceFill($reference['postcode']);
                $this->postcodes[$reference['postcode']] = $reference;
            }
        };
        
        $files = $this->readDirectoryFiles("pcdata");

        foreach ($files as $file) {
            $this->postcodes = array();
            $this->dataFileRead("pcdata/" . $file, $callback);
            $this->save($this->generateSql($this->postcodes, "CodePoint", "Postcodes") , "postcodes-" . str_replace(".csv", "", $file));
        }
    }
    
    /**
     * Prepares the call back parser for the line and loads up the areas file.
     * @return type
     */
    public function loadAreaCodes() {
        $callback = function ($row) {
            $segments = explode("\t", $row);
            if (isset($segments[0]) && isset($segments[1])) $this->areacodes[] = array(
                "core_type" => trim(chop($segments[0])) ,
                "core_text" => trim(chop($segments[1]))
            );
        };
        
        $this->dataFileRead($this->file_list['areacodes'], $callback);
    }
    
    /**
     * Save the file
     * @param type $file_cache
     * @param type $label
     * @return type
     */
    public function save($file_cache, $label) {
        print "Saving " . $label . "\n";
        $fp = fopen("/tmp/sql-" . $label . "-" . time() . ".mysql.sql", "w");
        if ($fp) {
            fputs($fp, $file_cache);
            fclose($fp);
        }
    }
    
    /**
     * Prepares the call back parser for the line and loads up the area listing
     * @return type
     */
    public function loadAreaNames() {
        $callback = function ($row) {
            $base_area = "NULL";
            if (preg_match("/^@/", $row)) {
                $this->current_base = str_replace("@", "", $row);
            } 
            else {
                $segments = explode("\t", $row);
                if (isset($segments[0]) && isset($segments[1])) $this->areas[] = array(
                    "area_name" => trim(chop($segments[0])) ,
                    "area_code" => trim(chop($segments[1])) ,
                    "core_type" => $this->current_base
                );
            }
        };
        
        $this->dataFileRead($this->file_list['areas'], $callback);
    }
    
    /**
     * Generates the SQL query
     * @param type $blocks
     * @param type $database
     * @param type $table
     * @return type
     */
    public function generateSql($blocks, $database, $table) {
        $query_cache = "";
        foreach ($blocks as $block) {
            $query_block = sprintf("INSERT INTO %s.%s SET ", $database, $table);

            foreach ($block as $name => $value) $query_block.= sprintf("%s = '%s', ", $name, addslashes($value));

            $query_block = preg_replace("/, $/", "", $query_block);
            $query_cache.= $query_block . ";\n";
        }
        return $query_cache;
    }
}
?>
