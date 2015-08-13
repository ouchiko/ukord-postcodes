tar -zxvf 1.tar
tar -zxvf 2.tar
tar -zxvf 3.tar
mysql -e "CREATE DATABASE CodePoint;"
mysql CodePoint < ouchiko-2015-08-installdbschema.sql
mysql CodePoint < ouchiko-2015-08-postcodes.sql
mysql CodePoint < ouchiko-2015-08-streets.sql
