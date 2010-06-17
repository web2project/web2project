<?php
/*
:::::::::::::::::::::::::::::::::::::::::::::::::
::                                             ::
::  Functions for CAPTCHA Validation projects  ::
::                                             ::
::             2007 02. 01. 18.24.             ::
::                                             ::
::                                             ::
::                                             ::
::                                             ::
:::::::::::::::::::::::::::::::::::::::::::::::::
*/

/*
:::::::::::::::::::::::::::::::::::::::::::::::::::
::
::	Definitions...
::
*/

define ( PASSWORD, "captcha" );
define ( CHARSLEN, 5 );

/*
:::::::::::::::::::::::::::::::::::::::::::::::::::
::
::	get_rnd_iv...
::
*/
function get_rnd_iv ( $iv_len ) {
    $iv = '';
    while ( $iv_len-- > 0 ) {
        $iv .= chr ( mt_rand ( ) & 0xFF );
    }
    return $iv;
}

/*
:::::::::::::::::::::::::::::::::::::::::::::::::::
::
::	md5_encrypt...
::
*/
function md5_encrypt ( $plain_text, $password = PASSWORD, $iv_len = 16 ) {
    $plain_text .= "\x13";
    $n = strlen ( $plain_text );
    if ( $n % 16 ) $plain_text .= str_repeat ( "\0", 16 - ( $n % 16 ) );
    $i = 0;
    $enc_text = get_rnd_iv ( $iv_len );
    $iv = substr ( $password ^ $enc_text, 0, 512 );
    while ( $i < $n ) {
        $block = substr ( $plain_text, $i, 16 ) ^ pack ( 'H*', md5 ( $iv ) );
        $enc_text .= $block;
        $iv = substr ( $block . $iv, 0, 512 ) ^ $password;
        $i += 16;
    }
    return base64_encode ( $enc_text );
}

/*
:::::::::::::::::::::::::::::::::::::::::::::::::::
::
::	md5_decrypt...
::
*/
function md5_decrypt ( $enc_text, $password = PASSWORD, $iv_len = 16 ) {
    $enc_text = base64_decode ( $enc_text );
    $n = strlen ( $enc_text );
    $i = $iv_len;
    $plain_text = '';
    $iv = substr ( $password ^ substr ( $enc_text, 0, $iv_len ), 0, 512 );
    while ( $i < $n ) {
        $block = substr ( $enc_text, $i, 16 );
        $plain_text .= $block ^ pack ( 'H*', md5 ( $iv ) );
        $iv = substr ( $block . $iv, 0, 512 ) ^ $password;
        $i += 16;
    }
    return preg_replace ( '/\\x13\\x00*$/', '', $plain_text );
}

/*
:::::::::::::::::::::::::::::::::::::::::::::::::::
::
::	rnd_string...
::
*/
function rnd_string ( $len = CHARSLEN ) {
	$str = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$val = '';

	for ( $i = 0; $i < $len; $i++ ) {
		$val .= $str { rand ( 0, strlen ( $str ) - 1 ) };
	}
    return $val;
}
?>
