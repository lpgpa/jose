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
 * @see https://tools.ietf.org/html/rfc7520#section-5.12
 *
 * @group RFC7520
 */
class A128KWAndA128GCMEncryptionProtectedContentOnlyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Please note that we cannot the encryption and get the same result as the example (IV, TAG and other data are always different).
     * The output given in the RFC is used and only decrypted.
     */
    public function testA128KWAndA128GCMEncryptionProtectedContentOnly()
    {
        $expected_payload = "You can trust us to stick with you through thick and thin\xe2\x80\x93to the bitter end. And you can trust us to keep any secret of yours\xe2\x80\x93closer than you keep it yourself. But you cannot trust us to let you face trouble alone, and go off without a word. We are your friends, Frodo.";

        $private_key = new JWK([
            'kty' => 'oct',
            'kid' => '81b20965-8332-43d9-a468-82160ad91ac8',
            'use' => 'enc',
            'alg' => 'A128KW',
            'k'   => 'GZy6sIZ6wl9NJOKB-jnmVQ',
        ]);

        $protected_headers = [
        ];

        $headers = [
            'enc' => 'A128GCM',
            'alg' => 'A128KW',
            'kid' => '81b20965-8332-43d9-a468-82160ad91ac8',
        ];

        $expected_flattened_json = '{"unprotected":{"alg":"A128KW","kid":"81b20965-8332-43d9-a468-82160ad91ac8","enc":"A128GCM"},"encrypted_key":"244YHfO_W7RMpQW81UjQrZcq5LSyqiPv","iv":"YihBoVOGsR1l7jCD","ciphertext":"qtPIMMaOBRgASL10dNQhOa7Gqrk7Eal1vwht7R4TT1uq-arsVCPaIeFwQfzrSS6oEUWbBtxEasE0vC6r7sphyVziMCVJEuRJyoAHFSP3eqQPb4Ic1SDSqyXjw_L3svybhHYUGyQuTmUQEDjgjJfBOifwHIsDsRPeBz1NomqeifVPq5GTCWFo5k_MNIQURR2Wj0AHC2k7JZfu2iWjUHLF8ExFZLZ4nlmsvJu_mvifMYiikfNfsZAudISOa6O73yPZtL04k_1FI7WDfrb2w7OqKLWDXzlpcxohPVOLQwpA3mFNRKdY-bQz4Z4KX9lfz1cne31N4-8BKmojpw-OdQjKdLOGkC445Fb_K1tlDQXw2sBF","tag":"e2m0Vm7JvjK2VpCKXS-kyg"}';
        $expected_json = '{"recipients":[{"encrypted_key":"244YHfO_W7RMpQW81UjQrZcq5LSyqiPv"}],"unprotected":{"alg":"A128KW","kid":"81b20965-8332-43d9-a468-82160ad91ac8","enc":"A128GCM"},"iv":"YihBoVOGsR1l7jCD","ciphertext":"qtPIMMaOBRgASL10dNQhOa7Gqrk7Eal1vwht7R4TT1uq-arsVCPaIeFwQfzrSS6oEUWbBtxEasE0vC6r7sphyVziMCVJEuRJyoAHFSP3eqQPb4Ic1SDSqyXjw_L3svybhHYUGyQuTmUQEDjgjJfBOifwHIsDsRPeBz1NomqeifVPq5GTCWFo5k_MNIQURR2Wj0AHC2k7JZfu2iWjUHLF8ExFZLZ4nlmsvJu_mvifMYiikfNfsZAudISOa6O73yPZtL04k_1FI7WDfrb2w7OqKLWDXzlpcxohPVOLQwpA3mFNRKdY-bQz4Z4KX9lfz1cne31N4-8BKmojpw-OdQjKdLOGkC445Fb_K1tlDQXw2sBF","tag":"e2m0Vm7JvjK2VpCKXS-kyg"}';
        $expected_cek = 'KBooAFl30QPV3vkcZlXnzQ';
        $expected_iv = 'YihBoVOGsR1l7jCD';
        $expected_encrypted_key = '244YHfO_W7RMpQW81UjQrZcq5LSyqiPv';
        $expected_ciphertext = 'qtPIMMaOBRgASL10dNQhOa7Gqrk7Eal1vwht7R4TT1uq-arsVCPaIeFwQfzrSS6oEUWbBtxEasE0vC6r7sphyVziMCVJEuRJyoAHFSP3eqQPb4Ic1SDSqyXjw_L3svybhHYUGyQuTmUQEDjgjJfBOifwHIsDsRPeBz1NomqeifVPq5GTCWFo5k_MNIQURR2Wj0AHC2k7JZfu2iWjUHLF8ExFZLZ4nlmsvJu_mvifMYiikfNfsZAudISOa6O73yPZtL04k_1FI7WDfrb2w7OqKLWDXzlpcxohPVOLQwpA3mFNRKdY-bQz4Z4KX9lfz1cne31N4-8BKmojpw-OdQjKdLOGkC445Fb_K1tlDQXw2sBF';
        $expected_tag = 'e2m0Vm7JvjK2VpCKXS-kyg';

        $decrypter = DecrypterFactory::createDecrypter(['A128KW', 'A128GCM']);

        $loaded_flattened_json = Loader::load($expected_flattened_json);
        $this->assertTrue($decrypter->decryptUsingKey($loaded_flattened_json, $private_key));

        $loaded_json = Loader::load($expected_json);
        $this->assertTrue($decrypter->decryptUsingKey($loaded_json, $private_key));

        $this->assertEquals($expected_ciphertext, Base64Url::encode($loaded_flattened_json->getCiphertext()));
        $this->assertEquals($protected_headers, $loaded_flattened_json->getSharedProtectedHeaders());
        $this->assertEquals($expected_iv, Base64Url::encode($loaded_flattened_json->getIV()));
        $this->assertEquals($expected_encrypted_key, Base64Url::encode($loaded_flattened_json->getRecipient(0)->getEncryptedKey()));
        $this->assertEquals($headers, $loaded_flattened_json->getSharedHeaders());
        $this->assertEquals($expected_tag, Base64Url::encode($loaded_flattened_json->getTag()));
        $this->assertEquals($expected_cek, Base64Url::encode($loaded_flattened_json->getContentEncryptionKey()));

        $this->assertEquals($expected_ciphertext, Base64Url::encode($loaded_json->getCiphertext()));
        $this->assertEquals($protected_headers, $loaded_json->getSharedProtectedHeaders());
        $this->assertEquals($expected_iv, Base64Url::encode($loaded_json->getIV()));
        $this->assertEquals($expected_encrypted_key, Base64Url::encode($loaded_json->getRecipient(0)->getEncryptedKey()));
        $this->assertEquals($headers, $loaded_json->getSharedHeaders());
        $this->assertEquals($expected_tag, Base64Url::encode($loaded_json->getTag()));
        $this->assertEquals($expected_cek, Base64Url::encode($loaded_json->getContentEncryptionKey()));

        $this->assertEquals($expected_payload, $loaded_flattened_json->getPayload());
        $this->assertEquals($expected_payload, $loaded_json->getPayload());
    }

    /**
     * Same input as before, but we perform the encryption first.
     */
    public function testA128KWAndA128GCMEncryptionProtectedContentOnlyBis()
    {
        $expected_payload = "You can trust us to stick with you through thick and thin\xe2\x80\x93to the bitter end. And you can trust us to keep any secret of yours\xe2\x80\x93closer than you keep it yourself. But you cannot trust us to let you face trouble alone, and go off without a word. We are your friends, Frodo.";

        $private_key = new JWK([
            'kty' => 'oct',
            'kid' => '81b20965-8332-43d9-a468-82160ad91ac8',
            'use' => 'enc',
            'alg' => 'A128KW',
            'k'   => 'GZy6sIZ6wl9NJOKB-jnmVQ',
        ]);

        $protected_headers = [
        ];

        $headers = [
            'enc' => 'A128GCM',
            'alg' => 'A128KW',
            'kid' => '81b20965-8332-43d9-a468-82160ad91ac8',
        ];

        $jwe = JWEFactory::createEmptyJWE($expected_payload, $protected_headers, $headers);
        $encrypter = EncrypterFactory::createEncrypter(['A128KW', 'A128GCM']);

        $encrypter->addRecipient(
            $jwe,
            $private_key
        );

        $decrypter = DecrypterFactory::createDecrypter(['A128KW', 'A128GCM']);

        $loaded_flattened_json = Loader::load($jwe->toFlattenedJSON(0));
        $this->assertTrue($decrypter->decryptUsingKey($loaded_flattened_json, $private_key));

        $loaded_json = Loader::load($jwe->toJSON());
        $this->assertTrue($decrypter->decryptUsingKey($loaded_json, $private_key));

        $this->assertEquals($protected_headers, $loaded_flattened_json->getSharedProtectedHeaders());
        $this->assertEquals($headers, $loaded_flattened_json->getSharedHeaders());

        $this->assertEquals($protected_headers, $loaded_json->getSharedProtectedHeaders());
        $this->assertEquals($headers, $loaded_json->getSharedHeaders());

        $this->assertEquals($expected_payload, $loaded_flattened_json->getPayload());
        $this->assertEquals($expected_payload, $loaded_json->getPayload());
    }
}
