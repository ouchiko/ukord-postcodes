# ukord-postcodes

###Ordnance Survey Postcode Gazette in Mysql format. 

Feel free to use this data in Mysql format for your own stuff.  The database contains the full UK postcode set with accociated ward and district stuff.  Note that I have also included the latitude and longitude values for this.

###Structure

The directory structure is as follows. You'll most likely want to use the MysqlDataSet files.

Directory       | Description
-------------   | --------------------------------------
./data          | The Mysql data files and schema
./generate      | The code to generate the data

There is also an additional data directory for the raw Ordnance Survey files.  I did not include this into the repo.

###Example query

This will look into the data for the matching postcode(s) and return the information.

```sql
SELECT 

	postcode, easting, northing, latitude, longitude, formatted, 
	
	(SELECT CONCAT(area_name,', ',core_text) FROM CodePoint.Areas LEFT JOIN CodePoint.AreaCodes ON AreaCodes.core_type = Areas.`core_type` WHERE Areas.area_code = Postcodes.admin_ward_code) as admin_ward,
	(SELECT CONCAT(area_name,', ',core_text) FROM CodePoint.Areas LEFT JOIN CodePoint.AreaCodes ON AreaCodes.core_type = Areas.`core_type` WHERE Areas.area_code = Postcodes.admin_district_code) as admin_district



FROM
	CodePoint.Postcodes 

WHERE
	postcode LIKE 'AL53HG%'
```

###Acknowledgments

1. Ordnance Survey Open Data for the postcode data set
2. https://github.com/dvdoug/PHPCoord (dvdoug & John Stott) for PHPCoord

Thanks
ouchiko@gmail.com
@ouchy
