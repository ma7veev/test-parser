Requirements
* PHP 7.3 +
* Mysql 8 + 

If you run parse first time, specify database connection in .env.locale file, then run:

        composer install
        php bin/console doctrine:migrations:migrate

To start parsing run command:

    
        php bin/console app:parse http://localhost/public/parse%20me.csv csv 3
        
Where:

* http://localhost/public/parse%20me.csv - file url
* csv - type of parsing data (now supported only csv, but one can create other services implemented ParserInterface)
* 3 - (optional) is limit of records we want to crawl during one session. Default is 20 for test purposes

After running command, we get displaying parsing results, which also is stored in parsing_result table. If we reach the end of rows, we start from beginning on next session. If product date_updated param is changed, parser tries to update data.

