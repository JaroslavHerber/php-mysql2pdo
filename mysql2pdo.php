<?php

/*
	Database-Object
*/
class jhDb {

	private static $_instance = null;

	private static $_aCachedDbs = array();

	private static $_sHost = 'localhost';
	private static $_sUser = 'user';
	private static $_sPass = 'password';
	private static $_sCharset = 'utf8';


	public static function getInstance() {
		if(!self::$_instance instanceof jhDb) {
			self::$_instance = new jhDb();
		}

		return self::$_instance;
	}


	public static function getDb( $sDbName = 'stats' ) {

		if( $sDbName ) {

			if( !isset(self::$_aCachedDbs[$sDbName]) ) {
				self::$_aCachedDbs[$sDbName] = self::createConnection($sDbName);
			}

			return self::$_aCachedDbs[$sDbName];

		}

	}


	protected static function createConnection( $sDbName ) {

		try {

			$aOptions = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING, PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true);
			return new PDO('mysql:host='.self::$_sHost.';dbname='.$sDbName.';charset='.self::$_sCharset, self::$_sUser, self::$_sPass, $aOptions);

		} catch(PDOException $e) {

			debug('Could not create database-object<br>Error code: '.$e->getCode());
			die();

		}

		return false;

	}

}


if( PHP_MAJOR_VERSION >= 7 ) {

	$GLOBALS['mysql_connection'] = array();

	define('MYSQL_BOTH', PDO::FETCH_BOTH);
	define('MYSQL_NUM', PDO::FETCH_NUM);
	define('MYSQL_ASSOC', PDO::FETCH_ASSOC);

	function mysql_connect( $sHost, $sDatabase, $sPassword ) {
		// TODO: add setter in jhDb
		return $sDatabase;
	}

	function mysql_select_db( $sDbName, $oConnection ) {
		$GLOBALS['mysql_connection'][$sDbName] = jhDb::getDb($sDbName);
		return true;
	}

	function mysql_real_escape_string( $sSting ) {
		return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $sSting);
	}

	function mysql_query( $sQuery, $sDb = false ) {

		$oDb = false;

		if( $sDb && isset($GLOBALS['mysql_connection'][$sDb]) ) {
			$oDb = $GLOBALS['mysql_connection'][$sDb];
		} elseif( count($GLOBALS['mysql_connection']) ) {
			$oDb = end($GLOBALS['mysql_connection']);
		}

		if( !$oDb ) {
			debug('No database-object!');
			return false;
		}

		try {
			$rRes = $oDb->query($sQuery);
		} catch( PDOException $oEx ) {
			debug($oEx->getMessage());
		}

		return $rRes;

	}

	function mysql_fetch_array( &$rRes, $sFetchType = PDO::FETCH_NUM ) {

		if( $aRow = $rRes->fetch($sFetchType) ) {
			return $aRow;
		}

		return false;

	}

	function mysql_fetch_assoc( &$rRes ) {

		if( $aRow = $rRes->fetch(PDO::FETCH_ASSOC) ) {
			return $aRow;
		}

		return false;

	}

	function mysql_fetch_row( $rRes ) {

		if( $aRow = $rRes->fetch(PDO::FETCH_NUM) ) {
			return $aRow;
		}

		return false;

	}

	function mysql_num_rows( $rRes ) {
		return $rRes->rowCount();
	}

	function mysql_set_charset( $sCharSet, $oConnection ) {
		if( $sCharSet !== 'utf8' ) {
			debug('Only utf8 is allowed');
      return false;
		}
		return true;
	}

	function mysql_error( $rRes ) {
		// TODO: catch and return PDO-errors
    //return $rRes->errorInfo();

		return false;
	}

	function mysql_free_result( $rRes ) {
		return true;
	}

}


if( !function_exists('debug') ) {
	function debug( $mVar, $sLogFile = '/tmp/debug.log' ) {
		
		$rLogFile = fopen($sLogFile, 'a');
		$sString = var_export($mVar, true);

		$sExecutionFile = '';
		$aBacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
		$sOriginFunction = array_shift($aBacktrace);
		if( $sOriginFunction['file'] ) {
			$sExecutionFile = basename($sOriginFunction['file']);
		}

		fputs($rLogFile, "\n---- " . date('Y-m-d H:i:s') . ' / ' . $sExecutionFile . " ----\n" . $sString . "\n");
		fclose($rLogFile);
		
	}
}

?>
