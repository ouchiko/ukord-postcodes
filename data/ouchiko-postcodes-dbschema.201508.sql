
CREATE TABLE `AreaCodes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `core_type` char(3) DEFAULT NULL,
  `core_text` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `key_area_core` (`core_type`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

CREATE TABLE `Areas` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `area_name` varchar(254) DEFAULT NULL,
  `area_code` varchar(25) DEFAULT NULL,
  `core_type` char(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `area_code_index` (`area_code`),
  KEY `area_core_index` (`core_type`)
) ENGINE=MyISAM AUTO_INCREMENT=9145 DEFAULT CHARSET=latin1;

CREATE TABLE `Postcodes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `postcode` varchar(10) DEFAULT NULL,
  `quality` int(11) DEFAULT NULL,
  `easting` int(11) DEFAULT NULL,
  `northing` int(11) DEFAULT NULL,
  `country_code` varchar(11) DEFAULT NULL,
  `nhs_region_ha_code` varchar(11) DEFAULT NULL,
  `nhs_ha_code` varchar(11) DEFAULT NULL,
  `admin_county_code` varchar(11) DEFAULT NULL,
  `admin_district_code` varchar(11) DEFAULT NULL,
  `admin_ward_code` varchar(11) DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `formatted` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `postcode_key` (`postcode`),
  KEY `coordinatesh` (`easting`,`northing`),
  KEY `latlon` (`latitude`,`longitude`)
) ENGINE=MyISAM AUTO_INCREMENT=451551 DEFAULT CHARSET=latin1;
