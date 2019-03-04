php-mysql2pdo
==========

### About:
mysql_* functions are removed from PHP7+.

Nevertheless, this script allows usage of mysql_*-functions in PHP7+.
It provides all needed functions, which use the PDO-object.

#### Usage:
1. Just include this file at the beginning of your php-file, which uses the old mysql_*-functions. Example:
    - require_once '/www/php5-compatibility/mysql2pdo.php';

2. Insert DB-credentials in jhDB-Class. The db-user must have access to the tables which you want to use with the mysql_*-functions
    - This step will be obsolete in future version

Feel free to contribute to this project.

### Contact me
 * [Contact form](https://www.herber-edevelopment.de/#contact)
