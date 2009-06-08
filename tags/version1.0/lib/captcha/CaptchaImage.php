<?php
/*
:::::::::::::::::::::::::::::::::::::::::::::::::
::                                             ::
::         CAPTCHA Validation projects         ::
::                                             ::
::             2007 02. 01. 18.24.             ::
::                                             ::
::                                             ::
::                                             ::
::                                             ::
:::::::::::::::::::::::::::::::::::::::::::::::::

:::::::::::::::::::::::::::::::::::::::::::::::::
::                                             ::
::          Include required classes           ::
::                                             ::
:::::::::::::::::::::::::::::::::::::::::::::::::
*/
require_once "Captcha.class.php";
require_once "Functions.php";
/*
:::::::::::::::::::::::::::::::::::::::::::::::::
::                                             ::
::   And turn the http header into image/gif   ::
::                                             ::
:::::::::::::::::::::::::::::::::::::::::::::::::
*/
Header ( 'Content-type: image/gif' );

if ( $dh = opendir ( "fonts/" ) ) {
	while ( false !== ( $dat = readdir ( $dh ) ) ) {
		if ( $dat != "." && $dat != ".." ) {
			$fonts [ ] = "fonts/$dat";
		}
	}
	closedir ( $dh );
}
if ( $_GET [ 'uid' ] ) {
	$UID = explode ( ";", $_GET [ 'uid' ] );
    $IMG = new Captcha ( md5_decrypt ( $UID [ 1 ] ), $fonts [ rand ( 0, ( count ( $fonts ) ) - 1 ) ], "8a8a8a" );

    echo $IMG->AnimatedOut ( );
}
?>
