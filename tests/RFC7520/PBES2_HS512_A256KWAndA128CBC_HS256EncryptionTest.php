<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Jose\Test\RFC7520;

use Base64Url\Base64Url;
use Jose\Factory\DecrypterFactory;
use Jose\Factory\EncrypterFactory;
use Jose\Factory\JWEFactory;
use Jose\Loader;
use Jose\Object\JWK;

/**
 * @see https://tools.ietf.org/html/rfc7520#section-5.3
 *
 * @group RFC7520
 */
class PBES2_HS512_A256KWAndA128CBC_HS256EncryptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Please note that we cannot the encryption and get the same result as the example (IV, TAG and other data are always different).
     * The output given in the RFC is used and only decrypted.
     */
    public function testPBES2_HS512_A256KWAndA128CBC_HS256Encryption()
    {
        $expected_payload = ['keys' => [
            [
                'kty' => 'oct',
                'kid' => '77c7e2b8-6e13-45cf-8672-617b5b45243a',
                'use' => 'enc',
                'alg' => 'A128GCM',
                'k'   => 'XctOhJAkA-pD9Lh7ZgW_2A',
            ], [
                'kty' => 'oct',
                'kid' => '81b20965-8332-43d9-a468-82160ad91ac8',
                'use' => 'enc',
                'alg' => 'A128KW',
                'k'   => 'GZy6sIZ6wl9NJOKB-jnmVQ',
            ], [
                'kty' => 'oct',
                'kid' => '18ec08e1-bfa9-4d95-b205-2b4dd1d4321d',
                'use' => 'enc',
                'alg' => 'A256GCMKW',
                'k'   => 'qC57l_uxcm7Nm3K-ct4GFjx8tM1U8CZ0NLBvdQstiS8',
            ],
        ]];

        $private_key = new JWK([
            'kty' => 'oct',
            'use' => 'enc',
            'k'   => Base64Url::encode("entrap_o\xe2\x80\x93peter_long\xe2\x80\x93credit_tun"),
        ]);

        $protected_headers = [
            'alg' => 'PBES2-HS512+A256KW',
            'p2s' => '8Q1SzinasR3xchYz6ZZcHA',
            'p2c' => 8192,
            'cty' => 'jwk-set+json',
            'enc' => 'A128CBC-HS256',
        ];

        $expected_compact_json = 'eyJhbGciOiJQQkVTMi1IUzUxMitBMjU2S1ciLCJwMnMiOiI4UTFTemluYXNSM3hjaFl6NlpaY0hBIiwicDJjIjo4MTkyLCJjdHkiOiJqd2stc2V0K2pzb24iLCJlbmMiOiJBMTI4Q0JDLUhTMjU2In0.d3qNhUWfqheyPp4H8sjOWsDYajoej4c5Je6rlUtFPWdgtURtmeDV1g.VBiCzVHNoLiR3F4V82uoTQ.23i-Tb1AV4n0WKVSSgcQrdg6GRqsUKxjruHXYsTHAJLZ2nsnGIX86vMXqIi6IRsfywCRFzLxEcZBRnTvG3nhzPk0GDD7FMyXhUHpDjEYCNA_XOmzg8yZR9oyjo6lTF6si4q9FZ2EhzgFQCLO_6h5EVg3vR75_hkBsnuoqoM3dwejXBtIodN84PeqMb6asmas_dpSsz7H10fC5ni9xIz424givB1YLldF6exVmL93R3fOoOJbmk2GBQZL_SEGllv2cQsBgeprARsaQ7Bq99tT80coH8ItBjgV08AtzXFFsx9qKvC982KLKdPQMTlVJKkqtV4Ru5LEVpBZXBnZrtViSOgyg6AiuwaS-rCrcD_ePOGSuxvgtrokAKYPqmXUeRdjFJwafkYEkiuDCV9vWGAi1DH2xTafhJwcmywIyzi4BqRpmdn_N-zl5tuJYyuvKhjKv6ihbsV_k1hJGPGAxJ6wUpmwC4PTQ2izEm0TuSE8oMKdTw8V3kobXZ77ulMwDs4p.0HlwodAhOCILG5SQ2LQ9dg';
        $expected_flattened_json = '{"protected":"eyJhbGciOiJQQkVTMi1IUzUxMitBMjU2S1ciLCJwMnMiOiI4UTFTemluYXNSM3hjaFl6NlpaY0hBIiwicDJjIjo4MTkyLCJjdHkiOiJqd2stc2V0K2pzb24iLCJlbmMiOiJBMTI4Q0JDLUhTMjU2In0","encrypted_key":"d3qNhUWfqheyPp4H8sjOWsDYajoej4c5Je6rlUtFPWdgtURtmeDV1g","iv":"VBiCzVHNoLiR3F4V82uoTQ","ciphertext":"23i-Tb1AV4n0WKVSSgcQrdg6GRqsUKxjruHXYsTHAJLZ2nsnGIX86vMXqIi6IRsfywCRFzLxEcZBRnTvG3nhzPk0GDD7FMyXhUHpDjEYCNA_XOmzg8yZR9oyjo6lTF6si4q9FZ2EhzgFQCLO_6h5EVg3vR75_hkBsnuoqoM3dwejXBtIodN84PeqMb6asmas_dpSsz7H10fC5ni9xIz424givB1YLldF6exVmL93R3fOoOJbmk2GBQZL_SEGllv2cQsBgeprARsaQ7Bq99tT80coH8ItBjgV08AtzXFFsx9qKvC982KLKdPQMTlVJKkqtV4Ru5LEVpBZXBnZrtViSOgyg6AiuwaS-rCrcD_ePOGSuxvgtrokAKYPqmXUeRdjFJwafkYEkiuDCV9vWGAi1DH2xTafhJwcmywIyzi4BqRpmdn_N-zl5tuJYyuvKhjKv6ihbsV_k1hJGPGAxJ6wUpmwC4PTQ2izEm0TuSE8oMKdTw8V3kobXZ77ulMwDs4p","tag":"0HlwodAhOCILG5SQ2LQ9dg"}';
        $expected_json = '{"recipients":[{"encrypted_key":"d3qNhUWfqheyPp4H8sjOWsDYajoej4c5Je6rlUtFPWdgtURtmeDV1g"}],"protected":"eyJhbGciOiJQQkVTMi1IUzUxMitBMjU2S1ciLCJwMnMiOiI4UTFTemluYXNSM3hjaFl6NlpaY0hBIiwicDJjIjo4MTkyLCJjdHkiOiJqd2stc2V0K2pzb24iLCJlbmMiOiJBMTI4Q0JDLUhTMjU2In0","iv":"VBiCzVHNoLiR3F4V82uoTQ","ciphertext":"23i-Tb1AV4n0WKVSSgcQrdg6GRqsUKxjruHXYsTHAJLZ2nsnGIX86vMXqIi6IRsfywCRFzLxEcZBRnTvG3nhzPk0GDD7FMyXhUHpDjEYCNA_XOmzg8yZR9oyjo6lTF6si4q9FZ2EhzgFQCLO_6h5EVg3vR75_hkBsnuoqoM3dwejXBtIodN84PeqMb6asmas_dpSsz7H10fC5ni9xIz424givB1YLldF6exVmL93R3fOoOJbmk2GBQZL_SEGllv2cQsBgeprARsaQ7Bq99tT80coH8ItBjgV08AtzXFFsx9qKvC982KLKdPQMTlVJKkqtV4Ru5LEVpBZXBnZrtViSOgyg6AiuwaS-rCrcD_ePOGSuxvgtrokAKYPqmXUeRdjFJwafkYEkiuDCV9vWGAi1DH2xTafhJwcmywIyzi4BqRpmdn_N-zl5tuJYyuvKhjKv6ihbsV_k1hJGPGAxJ6wUpmwC4PTQ2izEm0TuSE8oMKdTw8V3kobXZ77ulMwDs4p","tag":"0HlwodAhOCILG5SQ2LQ9dg"}';
        $expected_cek = 'uwsjJXaBK407Qaf0_zpcpmr1Cs0CC50hIUEyGNEt3m0';
        $expected_iv = 'VBiCzVHNoLiR3F4V82uoTQ';
        $expected_encrypted_key = 'd3qNhUWfqheyPp4H8sjOWsDYajoej4c5Je6rlUtFPWdgtURtmeDV1g';
        $expected_ciphertext = '23i-Tb1AV4n0WKVSSgcQrdg6GRqsUKxjruHXYsTHAJLZ2nsnGIX86vMXqIi6IRsfywCRFzLxEcZBRnTvG3nhzPk0GDD7FMyXhUHpDjEYCNA_XOmzg8yZR9oyjo6lTF6si4q9FZ2EhzgFQCLO_6h5EVg3vR75_hkBsnuoqoM3dwejXBtIodN84PeqMb6asmas_dpSsz7H10fC5ni9xIz424givB1YLldF6exVmL93R3fOoOJbmk2GBQZL_SEGllv2cQsBgeprARsaQ7Bq99tT80coH8ItBjgV08AtzXFFsx9qKvC982KLKdPQMTlVJKkqtV4Ru5LEVpBZXBnZrtViSOgyg6AiuwaS-rCrcD_ePOGSuxvgtrokAKYPqmXUeRdjFJwafkYEkiuDCV9vWGAi1DH2xTafhJwcmywIyzi4BqRpmdn_N-zl5tuJYyuvKhjKv6ihbsV_k1hJGPGAxJ6wUpmwC4PTQ2izEm0TuSE8oMKdTw8V3kobXZ77ulMwDs4p';
        $expected_tag = '0HlwodAhOCILG5SQ2LQ9dg';

        $decrypter = DecrypterFactory::createDecrypter(['PBES2-HS512+A256KW', 'A128CBC-HS256']);

        $loaded_compact_json = Loader::load($expected_compact_json);
        $this->assertTrue($decrypter->decryptUsingKey($loaded_compact_json, $private_key));

        $loaded_flattened_json = Loader::load($expected_flattened_json);
        $this->assertTrue($decrypter->decryptUsingKey($loaded_flattened_json, $private_key));

        $loaded_json = Loader::load($expected_json);
        $this->assertTrue($decrypter->decryptUsingKey($loaded_json, $private_key));

        $this->assertEquals($expected_ciphertext, Base64Url::encode($loaded_compact_json->getCiphertext()));
        $this->assertEquals($protected_headers, $loaded_compact_json->getSharedProtectedHeaders());
        $this->assertEquals($expected_cek, Base64Url::encode($loaded_compact_json->getContentEncryptionKey()));
        $this->assertEquals($expected_iv, Base64Url::encode($loaded_compact_json->getIV()));
        $this->assertEquals($expected_encrypted_key, Base64Url::encode($loaded_compact_json->getRecipient(0)->getEncryptedKey()));
        $this->assertEquals($expected_tag, Base64Url::encode($loaded_compact_json->getTag()));

        $this->assertEquals($expected_ciphertext, Base64Url::encode($loaded_flattened_json->getCiphertext()));
        $this->assertEquals($protected_headers, $loaded_flattened_json->getSharedProtectedHeaders());
        $this->assertEquals($expected_cek, Base64Url::encode($loaded_flattened_json->getContentEncryptionKey()));
        $this->assertEquals($expected_iv, Base64Url::encode($loaded_flattened_json->getIV()));
        $this->assertEquals($expected_encrypted_key, Base64Url::encode($loaded_flattened_json->getRecipient(0)->getEncryptedKey()));
        $this->assertEquals($expected_tag, Base64Url::encode($loaded_flattened_json->getTag()));

        $this->assertEquals($expected_ciphertext, Base64Url::encode($loaded_json->getCiphertext()));
        $this->assertEquals($protected_headers, $loaded_json->getSharedProtectedHeaders());
        $this->assertEquals($expected_cek, Base64Url::encode($loaded_json->getContentEncryptionKey()));
        $this->assertEquals($expected_iv, Base64Url::encode($loaded_json->getIV()));
        $this->assertEquals($expected_encrypted_key, Base64Url::encode($loaded_json->getRecipient(0)->getEncryptedKey()));
        $this->assertEquals($expected_tag, Base64Url::encode($loaded_json->getTag()));

        $this->assertEquals($expected_payload, $loaded_compact_json->getPayload());
        $this->assertEquals($expected_payload, $loaded_flattened_json->getPayload());
        $this->assertEquals($expected_payload, $loaded_json->getPayload());
    }

    /**
     * Same input as before, but we perform the encryption first.
     */
    public function testPBES2_HS512_A256KWAndA128CBC_HS256EncryptionBis()
    {
        $expected_payload = ['keys' => [
            [
                'kty' => 'oct',
                'kid' => '77c7e2b8-6e13-45cf-8672-617b5b45243a',
                'use' => 'enc',
                'alg' => 'A128GCM',
                'k'   => 'XctOhJAkA-pD9Lh7ZgW_2A',
            ], [
                'kty' => 'oct',
                'kid' => '81b20965-8332-43d9-a468-82160ad91ac8',
                'use' => 'enc',
                'alg' => 'A128KW',
                'k'   => 'GZy6sIZ6wl9NJOKB-jnmVQ',
            ], [
                'kty' => 'oct',
                'kid' => '18ec08e1-bfa9-4d95-b205-2b4dd1d4321d',
                'use' => 'enc',
                'alg' => 'A256GCMKW',
                'k'   => 'qC57l_uxcm7Nm3K-ct4GFjx8tM1U8CZ0NLBvdQstiS8',
            ],
        ]];

        $private_key = new JWK([
            'kty' => 'oct',
            'use' => 'enc',
            'k'   => Base64Url::encode("entrap_o\xe2\x80\x93peter_long\xe2\x80\x93credit_tun"),
        ]);

        $protected_headers = [
            'alg' => 'PBES2-HS512+A256KW',
            'p2s' => '8Q1SzinasR3xchYz6ZZcHA',
            'p2c' => 8192,
            'cty' => 'jwk-set+json',
            'enc' => 'A128CBC-HS256',
        ];

        $jwe = JWEFactory::createEmptyJWE($expected_payload, $protected_headers);
        $encrypter = EncrypterFactory::createEncrypter(['PBES2-HS512+A256KW', 'A128CBC-HS256']);

        $encrypter->addRecipient(
            $jwe,
            $private_key
        );

        $decrypter = DecrypterFactory::createDecrypter(['PBES2-HS512+A256KW', 'A128CBC-HS256']);

        $loaded_flattened_json = Loader::load($jwe->toFlattenedJSON(0));
        $this->assertTrue($decrypter->decryptUsingKey($loaded_flattened_json, $private_key));

        $loaded_json = Loader::load($jwe->toJSON());
        $this->assertTrue($decrypter->decryptUsingKey($loaded_json, $private_key));

        $this->assertEquals($protected_headers, $loaded_flattened_json->getSharedProtectedHeaders());

        $this->assertEquals($protected_headers, $loaded_json->getSharedProtectedHeaders());

        $this->assertEquals($expected_payload, $loaded_flattened_json->getPayload());
        $this->assertEquals($expected_payload, $loaded_json->getPayload());
    }
}
