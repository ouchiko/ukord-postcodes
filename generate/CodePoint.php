<?php
/**
 * Class and Function List:
 * Function list:
 * - readDirectoryFiles()
 * - dataFileRead()
 * - spaceFill()
 * - loadPostcodeData()
 * - (()
 * - loadStreetData()
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
        "areas" => "definitions/areas.tab",
        "streets" => "../pcdata/OS_Locator2015_1_OPEN.csv"
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
    
    public $street_references = array(
        "name",
        "classification",
        "centx",
        "centy",
        "minx",
        "maxx",
        "miny",
        "maxy",
        "settlement",
        "locality",
        "cou_unit",
        "localauth",
        "til10k",
        "tile25k",
        "source"
    );
    
    public $areacodes = array();
    public $areas = array();
    public $postcodes = array();
    public $streets = array();
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
            print "Loading " . count($data_rows) . " lines \n";
            foreach ($data_rows as $row) $callback($row);
        } 
        else {
            return false;
        }
    }
    
    /**
     * Space the postcode correctly.
     * @param type $str 
     * @return type
     */
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
     * Load street data
     * @return type
     */
    public function loadStreetData() {
        $count = 0 ; 
        $internal_counter = 0;

        $callback = function ($row) {     
            global $count, $internal_counter;       
            ////:A1:396293:659888:393510:397400:657043:661468::Chirnside and District:Scottish Borders:Scottish Borders:NT95NE:NT95:Roads
            $elements = explode(":", $row);
            $named_data = array();
            foreach ( $elements as $id => $element ) {
               $named_data[$this->street_references[$id]] = $element;
            }

            $osref_cent = new PHPCoord\OSRef($named_data["centx"], $named_data["centx"]); $latlng_cent = $osref_cent->toLatLng();
            $osref_min = new PHPCoord\OSRef($named_data["minx"], $named_data["miny"]); $latlng_min = $osref_min->toLatLng();
            $osref_max = new PHPCoord\OSRef($named_data["maxx"], $named_data["maxy"]); $latlng_max = $osref_max->toLatLng();

            $named_data['cent_lat'] = $latlng_cent -> lat;
            $named_data['cent_lon'] = $latlng_cent -> lng;        
            $named_data['min_lat'] = $latlng_min -> lat;
            $named_data['min_lon'] = $latlng_min -> lng; 
            $named_data['max_lat'] = $latlng_max -> lat;
            $named_data['max_lon'] = $latlng_max -> lng; 

            $count++;
            $internal_counter++;

            if ( $internal_counter == 100 ) {
                print chr(8).chr(8).chr(8).chr(8).chr(8).chr(8).chr(8).chr(8).chr(8).chr(8).chr(8).chr(8);
                print $count;
                $internal_counter = 0;
            }

            $this -> streets[] = $named_data;

            if ( count($this->streets) > 100000 ){

                $this -> save(
                    $this -> generateSql($this->streets, "CodePoint", "Streets"), "streets", true, true
                );
                $this -> streets = array();

                print " - Item Saved..";
            }
        };
        
        $this->dataFileRead($this->file_list['streets'], $callback);
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
    public function save($file_cache, $label, $append = false, $notime = false) {
        print "Saving " . $label . "\n";
        $fp = fopen("/tmp/sql-" . $label . "-" . (!$notime?time():'') . ".mysql.sql", (($append) ? "a+" : "w"));
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
