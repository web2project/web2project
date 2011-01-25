<?php
/*
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
::
::	Captcha Version 2.0 by L�szl� Zsidi, http://gifs.hu
::
::	This class is a rewritten 'Captcha.class.php' version.
::
::  Modification:
::   - Simplified and easy code,
::	 - Stable working
::
::
::	Created at 2007. 02. 01. '07.47.AM'
::
*/

define ( "ANIM_FRAMES",  5 );
define ( "ANIM_DELAYS", 10 );

Class Captcha {
	var $image;

	function Captcha ( $text, $font, $color )
	{
		$C              = HexDec ( $color );
		$R              = floor ( $C / pow ( 256, 2 ) );
		$G              = floor ( ( $C % pow ( 256, 2 ) ) / pow ( 256, 1 ) );
		$B              = floor ( ( ( $C % pow ( 256, 2 ) ) % pow ( 256, 1 ) ) / pow ( 256, 0 ) );
		$fsize          = 32;
		$bound          = array ( );
		$bound          = imageTTFBbox ( $fsize, 0, $font, $text );
		$this->image    = imageCreateTrueColor ( $bound [ 4 ] + 5, abs($bound [ 5 ] ) + 15 );
		imageFill       ( $this->image, 0, 0, ImageColorAllocate ( $this->image, 255, 255, 255 ) );
		imagettftext    ( $this->image, $fsize, 0, 2, abs( $bound [ 5 ] ) + 5, ImageColorAllocate ( $this->image, $R, $G, $B ), $font, $text );
	}
	/*
	:::::::::::::::::::::::::::::::::::::::::::::::::::
	::
	::	DoNoise...
	::
	*/
	function DoNoise ( $image, $G0, $C0 ) {
		$W = imageSX ( $image );
		$H = imageSY ( $image );

		for ( $i = 0; $i < 768; $i++ ) {
			$arrLUT [ $i ] = $i < 512 ? ( $i < 255 ? 0 : ( $i - 256 ) ) : 255;
		}

		$G1 = $G0 / 2;
		$C1 = $C0 / 2;
		for ( $y = 0; $y < $H; $y++ ) {
			for ( $x = 0; $x < $W; $x++ ) {
				$P  = imageColorAt ( $image, $x, $y );
				$R  = ( $P >> 16 ) & 0xFF;
				$G  = ( $P >>  8 ) & 0xFF;
				$B  = ( $P >>  0 ) & 0xFF;
				$N  = rand ( 0, $G0 ) - $G1;
				$R += 255 + $N + mt_rand ( 0, $C0 ) - $C1;
				$G += 255 + $N + mt_rand ( 0, $C0 ) - $C1;
				$B += 255 + $N + mt_rand ( 0, $C0 ) - $C1;
				imageSetPixel ( $image, $x, $y, ( $arrLUT [ $R ] << 16 ) | ( $arrLUT [ $G ] << 8 ) | $arrLUT [ $B ] );
			}
		}
	}
	/*
	:::::::::::::::::::::::::::::::::::::::::::::::::::
	::
	::	AnimatedOut...
	::
	*/
	function AnimatedOut ( ) {

		for ( $i = 0; $i < ANIM_FRAMES; $i++ ) {
			$image = imageCreateTrueColor ( imageSX ( $this->image ), imageSY ( $this->image ) );

            if ( imageCopy ( $image, $this->image, 0, 0, 0, 0, imageSX ( $this->image ), imageSY ( $this->image ) ) ) {
            	Captcha::DoNoise ( $image, 200, 0 );

            	Ob_Start		(			);
            	imageGif		( $image	);
            	imageDestroy	( $image	);

            	$f_arr [ ] = Ob_Get_Contents ( );
            	$d_arr [ ] = ANIM_DELAYS;

            	Ob_End_Clean	(			);
            }
		}
        $GIF = new GIFEncoder ( $f_arr, $d_arr, 0, 2, -1, -1, -1, 'C_MEMORY' );
        return ( $GIF->GetAnimation ( ) );
	}
}
/*
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
::
::	GIFEncoder Version 2.0 by L�szl� Zsidi, http://gifs.hu
::
::	This class is a rewritten 'GifMerge.class.php' version.
::
::  Modification:
::   - Simplified and easy code,
::   - Ultra fast encoding,
::   - Built-in errors,
::	 - Stable working
::
::
::	Created at 2007. 02. 01. '07.47.AM'
::
*/
Class GIFEncoder extends Captcha {
	var $GIF = 'GIF89a';			/* GIF header 6 bytes	*/
	var $VER = 'GIFEncoder V2.0';   /* Encoder version		*/

	var $ERR = Array (
		"ERR00"=>"Does not supported function for only one image!",
		"ERR01"=>"Source is not a GIF image!",
        "ERR02"=>"Unintelligible flag ",
        "ERR03"=>"Missing image descriptor block!<br>First byte of this block "
	);

	function GIFEncoder (
							$GIF_src, $GIF_tim, $GIF_lop, $GIF_dis,
							$GIF_red, $GIF_grn, $GIF_blu, $GIF_mod
						) {

		/*
		:::::::::::::::::::::::::::::::::::::::::::::::::::
		::
		::	Set-up conditions & pre-processing frames...
		::
		*/
		if ( ! is_array ( $GIF_src ) && ! is_array ( $GIF_tim ) ) {
			printf	( "%s: %s", $this->VER, $this->ERR [ 'ERR00' ] );
			exit	( 0 );
		}
		if ( $GIF_red > -1 && $GIF_grn > -1 && $GIF_blu > -1 ) {
			$transColor = $GIF_red | ( $GIF_grn << 8 ) | ( $GIF_blu << 16 );
		}
		else {
			$transColor = -1;
		}
		if ( $GIF_mod == 'C_FILE' ) {
			for ( $i = 0; $i < count ( $GIF_src ); $i++ ) {
				$GIF_buf [ ] = fread ( fopen ( $GIF_src [ $i ], "rb" ), filesize ( $GIF_src [ $i ] ) );
				if ( substr ( $GIF_buf [ $i ], 0, 6 ) != 'GIF87a' && substr ( $GIF_buf [ $i ], 0, 6 ) != 'GIF89a' ) {
					printf	( "%s: %d %s", $this->VER, $i, $this->ERR [ 'ERR01' ] );
					exit	( 0 );
				}
			}
		}
		else if ( $GIF_mod == 'C_MEMORY' ) {
			for ( $i = 0; $i < count ( $GIF_src ); $i++ ) {
				$GIF_buf [ ] = $GIF_src [ $i ];
				if ( substr ( $GIF_buf [ $i ], 0, 6 ) != 'GIF87a' && substr ( $GIF_buf [ $i ], 0, 6 ) != 'GIF89a' ) {
					printf	( "%s: %d %s", $this->VER, $i, $this->ERR [ 'ERR01' ] );
					exit	( 0 );
				}
			}
		}
		else {
			printf	( "%s: %s ( %s )!", $this->VER, $this->ERR [ 'ERR02' ], $GIF_mod );
			exit	( 0 );
		}
		/*
		:::::::::::::::::::::::::::::::::::::::::::::::::::
		::
		::	Build & post-processing animation...
		::
		*/
		for ( $i = 0; $i < count ( $GIF_buf ); $i++ ) {
			$tranid = false;
			$length = strlen ( $GIF_buf [ $i ] );
			$seek_p = 14 + ( 3 * ( 2 << ( ord ( $GIF_buf [ $i ] { 10 } ) & 0x07 ) ) );

			if ( strlen ( $this->GIF ) == 6 ) {
                $this->GIF .= substr ( $GIF_buf [ $i ], 6, $seek_p - 7 );
                $this->GIF .= "\x21\xFF\x0B" . "NETSCAPE" . "\x32\x2E\x30\x03\x01" . chr ( $GIF_lop & 0xFF ) . chr ( $GIF_lop >> 8 ) . "\x00";
			}
			if ( ord ( substr ( $GIF_buf [ $i ], $seek_p - 1, 1 ) ) != 0x21 ) {
            	if ( $transColor > -1 ) {
            		$rgb =  substr ( $GIF_buf [ $i ], 14, 3 * ( 2 << ( ord ( $GIF_buf [ $i ] { 10 } ) & 0x07 ) ) );

                	for ( $j = 0; $j < ( 2 << ( ord ( $GIF_buf [ $i ] { 10 } ) & 0x07 ) ); $j++ ) {
						if	(
								ord ( $rgb { 3 * $j     } ) == ( ( $transColor >> 16 ) & 0xFF ) &&
								ord ( $rgb { 3 * $j + 1 } ) == ( ( $transColor >>  8 ) & 0xFF ) &&
								ord ( $rgb { 3 * $j + 2 } ) == ( ( $transColor >>  0 ) & 0xFF )
							) {
								$tranid = true;
								break;
						}
            		}
					if ( $tranid && $j < 256 ) {
							$GIF_buf [ $i ] .= chr ( 0x21 );
							$GIF_buf [ $i ] .= chr ( 0xf9 );
							$GIF_buf [ $i ] .= chr ( 0x04 );
							$GIF_buf [ $i ] .= chr ( ( $GIF_dis << 2 ) + 1  );
							$GIF_buf [ $i ] .= chr ( $GIF_tim [ $i ] & 0xFF );
							$GIF_buf [ $i ] .= chr ( $GIF_tim [ $i ] >> 8   );
							$GIF_buf [ $i ] .= chr ( $j );
							$GIF_buf [ $i ] .= chr ( 0 );
					}
					else {
							$GIF_buf [ $i ] .= chr ( 0x21 );
							$GIF_buf [ $i ] .= chr ( 0xf9 );
							$GIF_buf [ $i ] .= chr ( 0x04 );
							$GIF_buf [ $i ] .= chr ( ( $GIF_dis << 2 ) + 0  );
							$GIF_buf [ $i ] .= chr ( $GIF_tim [ $i ] & 0xFF );
							$GIF_buf [ $i ] .= chr ( $GIF_tim [ $i ] >> 8   );
							$GIF_buf [ $i ] .= chr ( 0 );
							$GIF_buf [ $i ] .= chr ( 0 );
					}
            	}
            	if ( ord ( $GIF_buf [ $i ] { $seek_p - 1 } ) != 0x2c ) {
					printf	( "%s: %s ( 0x%x )", $this->VER, $this->ERR [ 'ERR03' ], ord ( substr ( $GIF_buf [ $i ], $seek_p - 1, 1 ) ) );
					exit	( 0 );
            	}
			}
			else {
            	if ( $transColor > -1 ) {
            		$rgb =  substr ( $GIF_buf [ $i ], 14, 3 * ( 2 << ( ord ( $GIF_buf [ $i ] { 10 } ) & 0x07 ) ) );

                	for ( $j = 0; $j < ( 2 << ( ord ( $GIF_buf [ $i ] { 10 } ) & 0x07 ) ); $j++ ) {
						if	(
								ord ( $rgb { 3 * $j     } ) == ( ( $transColor >> 16 ) & 0xFF ) &&
								ord ( $rgb { 3 * $j + 1 } ) == ( ( $transColor >>  8 ) & 0xFF ) &&
								ord ( $rgb { 3 * $j + 2 } ) == ( ( $transColor >>  0 ) & 0xFF )
							) {
								$tranid = true;
								break;
						}
            		}
					if ( $tranid && $j < 256 ) {
							$GIF_buf [ $i ] { ( $seek_p - 1 ) + 3 } = chr ( ( $GIF_dis << 2 ) + 1  );
							$GIF_buf [ $i ] { ( $seek_p - 1 ) + 4 } = chr ( $GIF_tim [ $i ] & 0xFF );
							$GIF_buf [ $i ] { ( $seek_p - 1 ) + 5 } = chr ( $GIF_tim [ $i ] >> 8   );
							$GIF_buf [ $i ] { ( $seek_p - 1 ) + 6 } = chr ( $j );
					}
					else {
							$GIF_buf [ $i ] { ( $seek_p - 1 ) + 3 } = chr ( ( $GIF_dis << 2 ) + 0  );
							$GIF_buf [ $i ] { ( $seek_p - 1 ) + 4 } = chr ( $GIF_tim [ $i ] & 0xFF );
							$GIF_buf [ $i ] { ( $seek_p - 1 ) + 5 } = chr ( $GIF_tim [ $i ] >> 8   );
							$GIF_buf [ $i ] { ( $seek_p - 1 ) + 6 } = chr ( 0 );
					}
            	}
            	if ( ord ( $GIF_buf [ $i ] { ( $seek_p - 1 ) + 8 } ) != 0x2c ) {
					printf	( "%s: %s ( 0x%x )", $this->VER, $this->ERR [ 'ERR03' ], ord ( substr ( $GIF_buf [ $i ], $seek_p - 1, 1 ) ) );
					exit	( 0 );
            	}
            }
            $this->GIF	.= substr ( $GIF_buf [ $i ], $seek_p - 1, $length - $seek_p );
		}
		$this->GIF .= ";";
	}
	/*
	:::::::::::::::::::::::::::::::::::::::::::::::::::
	::
	::	GetAnimation...
	::
	*/
	function GetAnimation ( ) {
		return ( $this->GIF );
	}
} /* __END_OF_CLASS__ */
?>
