php-mysql2pdo
==========

### About:
mysql_* functions are removed from PHP7+.

Nevertheless, this script allows usage of most needed mysql_*-functions in PHP7+.
It creates the most important mysql_*-functions, which use PDO.

#### Usage:
Just include this file at the beginning of your php-file, which uses the old mysql_*-functions. Example:
```
require_once '/www/php5-compatibility/mysql2pdo.php';
```

If you want, you can use same DB-connections with real PDO
```
require_once '/www/tools/mysql2pdo.php';

$mysql_connect = mysql_connect('localhost','my_user','my_password');
mysql_select_db('my_db', $mysql_connect);

$rRes = mysql_query('SELECT * FROM `my_table` WHERE `uid` = 1', $mysql_connect); // 2nd param optional
if( !mysql_error($mysql_connect) && mysql_num_rows($rRes) ) {
  while( $aRow = mysql_fetch_assoc($rRes) ) {
    print_r($aRow);
  }
}

// Use same connection with PDO and prepared statement now
$rPrepare = jhDb::getDb('my_db')->prepare('SELECT * FROM `my_table` WHERE `uid` = ?');
$rPrepare->execute(array(1));

while( $aRow = $rPrepare->fetch(PDO::FETCH_ASSOC) ) {
  print_r($aRow); // This should return same array as above
}
```

Feel free to contribute to this project and pull missing functions, you would like to use.

### Contact me
* [Contact form](https://www.herber-edevelopment.de/#contact)
