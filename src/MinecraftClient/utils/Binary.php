<?php

/*
 *▪   ▄▄▄·       ▄▄· ▄ •▄ ▄▄▄ .▄▄▄▄▄
 *██ ▐█ ▄█▪     ▐█ ▌▪█▌▄▌▪▀▄.▀·•██
 *▐█· ██▀· ▄█▀▄ ██ ▄▄▐▀▀▄·▐▀▀▪▄ ▐█.▪
 *▐█▌▐█▪·•▐█▌.▐▌▐███▌▐█.█▌▐█▄▄▌ ▐█▌·
 *▀▀▀.▀    ▀█▄▀▪·▀▀▀ ·▀  ▀ ▀▀▀  ▀▀▀
 *
 *This program is free software:
 *PocketEdition Packet Analyze.
 *
*/

namespace MinecraftClient\utils;

if(!defined("ENDIANNESS")){
		define("ENDIANNESS", (pack("d", 1) === "\77\360\0\0\0\0\0\0" ? Binary::BIG_ENDIAN : Binary::LITTLE_ENDIAN));
}

class Binary{
	const BIG_ENDIAN = 0x00;
	const LITTLE_ENDIAN = 0x01;


	/**
	 * Reads a 3-byte big-endian number
	 *
	 * @param $str
	 *
	 * @return mixed
	 */
	public static function readTriad($str){
		return unpack("N", "\x00" . $str)[1];
	}

	/**
	 * Reads a 3-byte little-endian number
	 *
	 * @param $str
	 *
	 * @return mixed
	 */
	public static function readLTriad($str){
		return unpack("V", $str . "\x00")[1];
	}

	/**
	 * Writes a 3-byte little-endian number
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public static function writeLTriad($value){
		return substr(pack("V", $value), 0, -1);
	}

	/**
	 * Reads an unsigned/signed byte
	 *
	 * @param string $c
	 * @param bool	 $signed
	 *
	 * @return int
	 */
	public static function readByte($c, $signed = true){
		$b = ord($c{0});

		if($signed){
			if(PHP_INT_SIZE === 8){
				return $b << 56 >> 56;
			}else{
				return $b << 24 >> 24;
			}
		}else{
			return $b;
		}
	}

	/**
	 * Writes a 16-bit signed/unsigned big-endian number
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public static function writeShort($value){
			return pack("n", $value);
	}

	/**
	 * Reads a 16-bit unsigned big-endian number
	 *
	 * @param $str
	 *
	 * @return int
	 */
	public static function readShort($str){
		return unpack("n", $str)[1];
	}

	/**
	 * Reads a 16-bit signed big-endian number
	 *
	 * @param $str
	 *
	 * @return int
	 */
	public static function readSignedShort($str){
		if(PHP_INT_SIZE === 8){
			return unpack("n", $str)[1] << 48 >> 48;
		}else{
			return unpack("n", $str)[1] << 16 >> 16;
		}
	}

	/**
	 * Reads a 16-bit unsigned little-endian number
	 *
	 * @param			$str
	 *
	 * @return int
	 */
	public static function readLShort($str){
		return unpack("v", $str)[1];
	}

	/**
	 * Reads a 16-bit signed little-endian number
	 *
	 * @param			$str
	 *
	 * @return int
	 */
	public static function readSignedLShort($str){
		if(PHP_INT_SIZE === 8){
			return unpack("v", $str)[1] << 48 >> 48;
		}else{
			return unpack("v", $str)[1] << 16 >> 16;
		}
	}

	public static function readInt($str){
		if(PHP_INT_SIZE === 8){
			return unpack("N", $str)[1] << 32 >> 32;
		}else{
			return unpack("N", $str)[1];
		}
	}

	public static function writeInt($value){
			return pack("N", $value);
	}

	public static function readLInt($str){
		if(PHP_INT_SIZE === 8){
			return unpack("V", $str)[1] << 32 >> 32;
		}else{
			return unpack("V", $str)[1];
		}
	}

	public static function readFloat($str){
		return ENDIANNESS === self::BIG_ENDIAN ? unpack("f", $str)[1] : unpack("f", strrev($str))[1];
	}

	public static function readLFloat($str){
		return ENDIANNESS === self::BIG_ENDIAN ? unpack("f", strrev($str))[1] : unpack("f", $str)[1];
	}

	public static function printFloat($value){
		return preg_replace("/(\\.\\d+?)0+$/", "$1", sprintf("%F", $value));
	}

	public static function readDouble($str){
		return ENDIANNESS === self::BIG_ENDIAN ? unpack("d", $str)[1] : unpack("d", strrev($str))[1];
	}

	public static function readLDouble($str){
		return ENDIANNESS === self::BIG_ENDIAN ? unpack("d", strrev($str))[1] : unpack("d", $str)[1];
	}

	public static function readLong($x){
		if(PHP_INT_SIZE === 8){
			$int = unpack("N*", $x);
			return ($int[1] << 32) | $int[2];
		}else{
			$value = "0";
			for($i = 0; $i < 8; $i += 2){
				$value = bcmul($value, "65536", 0);
				$value = bcadd($value, self::readShort(substr($x, $i, 2)), 0);
			}

			if(bccomp($value, "9223372036854775807") == 1){
				$value = bcadd($value, "-18446744073709551616");
			}

			return $value;
		}
	}

	public static function writeLong($value){
		if(PHP_INT_SIZE === 8){
			return pack("NN", $value >> 32, $value & 0xFFFFFFFF);
		}else{
			$x = "";

			if(bccomp($value, "0") == -1){
				$value = bcadd($value, "18446744073709551616");
			}

			$x .= self::writeShort(bcmod(bcdiv($value, "281474976710656"), "65536"));
			$x .= self::writeShort(bcmod(bcdiv($value, "4294967296"), "65536"));
			$x .= self::writeShort(bcmod(bcdiv($value, "65536"), "65536"));
			$x .= self::writeShort(bcmod($value, "65536"));

			return $x;
		}
	}

	public static function readLLong($str){
		return self::readLong(strrev($str));
	}

	/**
	 * Reads a metadata coded string
	 *
	 * @param			$value
	 * @param bool $types
	 *
	 * @return array
	 */
	public static function readMetadata($value, $types = false, &$offset = 0){
		$offset = 0;
		$m = [];
		$b = ord($value{$offset});
		++$offset;
		while($b !== 127 and isset($value{$offset})){
			$bottom = $b & 0x1F;
			$type = $b >> 5;
			switch($type){
				case 0:
					$r = self::readByte($value{$offset});
					++$offset;
					break;
				case 1:
					$r = self::readLShort(substr($value, $offset, 2));
					$offset += 2;
					break;
				case 2:
					$r = self::readLInt(substr($value, $offset, 4));
					$offset += 4;
					break;
				case 3:
					$r = self::readLFloat(substr($value, $offset, 4));
					$offset += 4;
					break;
				case 4:
					$len = self::readLShort(substr($value, $offset, 2));
					$offset += 2;
					$r = substr($value, $offset, $len);
					$offset += $len;
					break;
				case 5:
					$r = [];
					$r[] = self::readLShort(substr($value, $offset, 2));
					$offset += 2;
					$r[] = ord($value{$offset});
					++$offset;
					$r[] = self::readLShort(substr($value, $offset, 2));
					$offset += 2;
					break;
				case 6:
					$r = [];
					for($i = 0; $i < 3; ++$i){
						$r[] = self::readLInt(substr($value, $offset, 4));
						$offset += 4;
					}
					break;
				case 8:
					$r = self::readLLong(substr($value, $offset, 4));
					$offset += 8;
					break;
				default:
					return [];

			}
			if($types === true){
				$m[$bottom] = [$r, $type];
			}else{
				$m[$bottom] = $r;
			}
			$b = ord($value{$offset});
			++$offset;
		}

		return $m;
	}

	public static function readVarInt($buffer, &$offset = 0){
		$number = 0;
		$shift = 0;

		while(true){
			$c = ord($buffer{$offset++});
			$number |= ($c & 0x7f) << $shift;
			$shift += 7;
			if(($c & 0x80) === 0x00){
				break;
			}
		}
		return $number;
	}

	public static function readVarIntSession($session, &$offset = 0){
		$number = 0;
		$shift = 0;

		while(true){
			$b = $session->read(1);
			$c = ord($b);
			$number |= ($c & 0x7f) << $shift;
			$shift += 7;
			++$offset;
			if($b === false){
				return false;
			}elseif(($c & 0x80) === 0x00){
				break;
			}
		}
		return $number;
	}

	public static function writeVarInt($number){
		$encoded = "";
		do{
			$next_byte = $number & 0x7f;
			$number >>= 7;

			if($number > 0){
				$next_byte |= 0x80;
			}

			$encoded .= chr($next_byte);
		}while($number > 0);

		return $encoded;
	}

}
