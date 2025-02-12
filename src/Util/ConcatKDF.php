<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Jose\Util;

use Base64Url\Base64Url;

/**
 * Class ConcatKDF.
 *
 * This class is used by the ECDH-ES algorithms
 *
 * @see https://tools.ietf.org/html/rfc7518#section-4.6.2
 */
final class ConcatKDF
{
    /**
     * Key Derivation Function.
     *
     * @param string $Z                   Shared secret
     * @param string $algorithm           Encryption algorithm
     * @param int    $encryption_key_size Size of the encryption key
     * @param string $apu                 Agreement PartyUInfo (information about the producer)
     * @param string $apv                 Agreement PartyVInfo (information about the recipient)
     *
     * @return string
     */
    public static function generate($Z, $algorithm, $encryption_key_size, $apu = '', $apv = '')
    {
        $apu = !empty($apu) ? Base64Url::decode($apu) : '';
        $apv = !empty($apv) ? Base64Url::decode($apv) : '';
        $encryption_segments = [
            self::toInt32Bits(1),                             // Round number 1
            $Z,                                               // Z (shared secret)
            self::toInt32Bits(strlen($algorithm)).$algorithm, // Size of algorithm's name and algorithm
            self::toInt32Bits(strlen($apu)).$apu,             // PartyUInfo
            self::toInt32Bits(strlen($apv)).$apv,             // PartyVInfo
            self::toInt32Bits($encryption_key_size),          // SuppPubInfo (the encryption key size)
            '',                                               // SuppPrivInfo
        ];

        $input = implode('', $encryption_segments);
        $hash = hash('sha256', $input, true);
        $kdf = substr($hash, 0, $encryption_key_size / 8);

        return $kdf;
    }

    /**
     * Convert an integer into a 32 bits string.
     *
     * @param int $value Integer to convert
     *
     * @return string
     */
    private static function toInt32Bits($value)
    {
        return hex2bin(str_pad(dechex($value), 8, '0', STR_PAD_LEFT));
    }
}
