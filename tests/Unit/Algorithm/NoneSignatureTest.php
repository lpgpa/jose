<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

use Jose\Algorithm\Signature\None;
use Jose\Factory\SignerFactory;
use Jose\Loader;
use Jose\Object\JWK;
use Jose\Object\JWSInterface;
use Jose\Test\TestCase;

/**
 * Class NoneSignatureTest.
 *
 * @group None
 * @group Unit
 */
class NoneSignatureTest extends TestCase
{
    public function testNoneSignAndVerifyAlgorithm()
    {
        $key = new JWK([
            'kty' => 'none',
        ]);

        $none = new None();
        $data = 'Je suis Charlie';

        $signature = $none->sign($key, $data);

        $this->assertEquals($signature, '');
        $this->assertTrue($none->verify($key, $data, $signature));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The key is not valid
     */
    public function testInvalidKey()
    {
        $key = new JWK([
            'kty' => 'EC',
        ]);

        $none = new None();
        $data = 'Je suis Charlie';

        $none->sign($key, $data);
    }

    public function testNoneSignAndVerifyComplete()
    {
        $jwk = new JWK([
            'kty' => 'none',
        ]);

        $signer = SignerFactory::createSigner(['none']);

        $jws = \Jose\Factory\JWSFactory::createEmptyJWS('Je suis Charlie');

        $signer->addSignature($jws, $jwk, ['alg' => 'none']);

        $this->assertEquals(1, $jws->countSignatures());

        $compact = $jws->toCompactJSON(0);
        $this->assertTrue(is_string($compact));

        $result = Loader::load($compact);

        $this->assertInstanceOf(JWSInterface::class, $result);

        $this->assertEquals('Je suis Charlie', $result->getPayload());
        $this->assertEquals(1, $result->countSignatures());
        $this->assertTrue($result->getSignature(0)->hasProtectedHeader('alg'));
        $this->assertEquals('none', $result->getSignature(0)->getProtectedHeader('alg'));
    }
}
