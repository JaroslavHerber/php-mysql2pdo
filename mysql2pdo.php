<?php

/*
	Database-Object
*/
class jhDb {

	private static $_oInstance = null;

	private static $_aCachedDbs = array();
	private static $_oLastException = false;

	private static $_sHost = 'localhost';
	private static $_sUser = 'user';	// default-user
	private static $_sPass = 'password';	// default-password
	private static $_sCharset = 'utf8';


	public static function getInstance() {

		if( !self::$_oInstance instanceof jhDb ) {
			self::$_oInstance = new jhDb();
		}

		return self::$_oInstance;

	}


	public static function getDb( $sDbName ) {

		if( $sDbName ) {

			if( !isset(self::$_aCachedDbs[$sDbName]) ) {
				self::$_aCachedDbs[$sDbName] = self::createConnection($sDbName);
			}

			return self::$_aCachedDbs[$sDbName];

		}

	}


	public static function setConnection( $sHost, $sUser, $sPass ) {

		self::$_sHost = $sHost;
		self::$_sUser = $sUser;
		self::$_sPass = $sPass;

	}


	protected static function createConnection( $sDbName ) {

		try {

			$aOptions = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING, PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true);
			return new PDO('mysql:host='.self::$_sHost.';dbname='.$sDbName.';charset='.self::$_sCharset, self::$_sUser, self::$_sPass, $aOptions);

		} catch( PDOException $oEx ) {

			self::setLastException($oEx);

		}

		return false;

	}


	public static function setLastException( $sError ) {
		self::$_oLastException = $sError;
	}


	public static function getLastException() {
		return self::$_oLastException;
	}


}


if( PHP_MAJOR_VERSION >= 7 ) {

	$GLOBALS['mysql_connections'] = array();

	define('MYSQL_BOTH', PDO::FETCH_BOTH);
	define('MYSQL_NUM', PDO::FETCH_NUM);
	define('MYSQL_ASSOC', PDO::FETCH_ASSOC);


	function mysql_connect( $sHost, $sUser, $sPassword ) {
		jhDb::setConnection($sHost, $sUser, $sPassword);
		return $sUser;
	}

	function mysql_select_db( $sDbName, $sUser ) {
		$GLOBALS['mysql_connections'][$sUser] = jhDb::getDb($sDbName);
		return true;
	}

	function mysql_real_escape_string( $sString ) {
		return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $sString);
	}

	function mysql_query( $sQuery, $sUser = false ) {

		$oDb = false;

		if( $sUser && isset($GLOBALS['mysql_connections'][$sUser]) ) {
			$oDb = $GLOBALS['mysql_connections'][$sUser];
		} elseif( count($GLOBALS['mysql_connections']) ) {
			$oDb = end($GLOBALS['mysql_connections']);
		}

		if( !$oDb ) {
			debug('No database-object!');
			return false;
		}

		try {
			$rRes = $oDb->query($sQuery);
		} catch( PDOException $oEx ) {
			jhDb::setLastException($oEx);
		}

		return $rRes;

	}

	function mysql_result( &$rRes, $iRow, $mField = 0 ) {

		$iCountRow = 0;

		$sFetchType = PDO::FETCH_NUM;
		if( !is_numeric($mField) ) {
			$sFetchType = PDO::FETCH_ASSOC;
		}

		while( $aRow = $rRes->fetch($sFetchType) ) {

			if( $iRow === $iCountRow && isset($aRow[$mField]) ) {
				return $aRow[$mField];
			}

			$iCountRow++;

		}

		return false;

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
			debug('Only utf8 is supported');
			return false;
		}

		return true;

	}

	function mysql_error( $rRes ) {

		if( $oException = jhDb::getLastException() ) {
			return $oException->getMessage();
		}

		return false;

	}

	function mysql_errno( $rRes ) {

		if( $oException = jhDb::getLastException() ) {
			return $oException->getCode();
		}

		return 0;

	}

	function mysql_free_result( $rRes ) {
		// No need to delete RAM in year 2019
		return true;
	}

	function mysql_close( $rRes ) {
		// No need for closing connection
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
