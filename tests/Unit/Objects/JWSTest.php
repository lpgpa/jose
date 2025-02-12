<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

use Base64Url\Base64Url;
use Jose\Factory\CheckerManagerFactory;
use Jose\Factory\JWSFactory;
use Jose\Object\Signature;

/**
 * Class JWSTest.
 *
 * @group JWS
 * @group Unit
 */
class JWSTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage  One or more claims are marked as critical, but they are missing or have not been checked (["iss"])
     */
    public function testJWS()
    {
        $claims = [
            'nbf' => time(),
            'iat' => time(),
            'exp' => time() + 3600,
            'iss' => 'Me',
            'aud' => 'You',
            'sub' => 'My friend',
        ];
        $jws = JWSFactory::createEmptyJWS($claims);

        $this->assertTrue($jws->hasClaims());
        $this->assertTrue($jws->hasClaim('nbf'));
        $this->assertTrue($jws->hasClaim('iss'));
        $this->assertEquals('Me', $jws->getClaim('iss'));
        $this->assertEquals($claims, json_decode(Base64Url::decode($jws->getEncodedPayload()), true));
        $this->assertEquals($claims, $jws->getPayload());
        $this->assertEquals($claims, $jws->getClaims());
        $this->assertEquals(0, $jws->countSignatures());

        $signature = new Signature();
        $signature = $signature->withProtectedHeader('crit', ['nbf', 'iat', 'exp', 'iss']);
        $jws = $jws->addSignature($signature);
        $this->assertEquals(1, $jws->countSignatures());

        $checker_manager = CheckerManagerFactory::createClaimCheckerManager();
        $checker_manager->checkJWS($jws, 0);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The signature does not exist.
     */
    public function testToCompactJSONFailed()
    {
        $jws = JWSFactory::createEmptyJWS([
            'nbf' => time(),
            'iat' => time(),
            'exp' => time() + 3600,
            'iss' => 'Me',
            'aud' => 'You',
            'sub' => 'My friend',
        ]);

        $jws->toCompactJSON(0);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The signature does not exist.
     */
    public function testToFlattenedJSONFailed()
    {
        $jws = JWSFactory::createEmptyJWS([
            'nbf' => time(),
            'iat' => time(),
            'exp' => time() + 3600,
            'iss' => 'Me',
            'aud' => 'You',
            'sub' => 'My friend',
        ]);

        $jws->toFlattenedJSON(0);
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage No signature.
     */
    public function testToJSONFailed()
    {
        $jws = JWSFactory::createEmptyJWS([
            'nbf' => time(),
            'iat' => time(),
            'exp' => time() + 3600,
            'iss' => 'Me',
            'aud' => 'You',
            'sub' => 'My friend',
        ]);

        $jws->toJSON();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The payload does not contain claims.
     */
    public function testNoClaims()
    {
        $jws = JWSFactory::createEmptyJWS('Hello');

        $jws->getClaims();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The payload does not contain claim "foo".
     */
    public function testClaimDoesNotExist()
    {
        $jws = JWSFactory::createEmptyJWS([
            'nbf' => time(),
            'iat' => time(),
            'exp' => time() + 3600,
            'iss' => 'Me',
            'aud' => 'You',
            'sub' => 'My friend',
        ]);

        $jws->getClaim('foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The signature contains unprotected headers and cannot be converted into compact JSON
     */
    public function testSignatureContainsUnprotectedHeaders()
    {
        $jws = JWSFactory::createEmptyJWS('Hello');
        $signature = new Signature();
        $signature = $signature->withHeader('foo', 'bar');

        $this->assertEquals('bar', $signature->getHeader('foo'));

        $jws = $jws->addSignature($signature);

        $jws->toCompactJSON(0);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The header "foo" does not exist
     */
    public function testSignatureDoesNotContainHeader()
    {
        $signature = new Signature();

        $signature->getHeader('foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The protected header "foo" does not exist
     */
    public function testSignatureDoesNotContainProtectedHeader()
    {
        $signature = new Signature();

        $signature->getProtectedHeader('foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The argument does not contain valid encoded protected headers.
     */
    public function testBadEncodedProtectedHeader()
    {
        $signature = new Signature();

        $signature->withEncodedProtectedHeaders('foo');
    }

    public function testEmptyEncodedProtectedHeader()
    {
        $signature = new Signature();

        $signature->withEncodedProtectedHeaders('');

        $this->assertEquals([], $signature->getProtectedHeaders());
    }

    public function testEncodedProtectedHeader()
    {
        $signature = new Signature();

        $signature = $signature->withEncodedProtectedHeaders(Base64Url::encode(json_encode(['foo' => 'bar'])));
        $signature = $signature->withProtectedHeader('plic', 'ploc');

        $this->assertEquals(['foo' => 'bar', 'plic' => 'ploc'], $signature->getProtectedHeaders());
    }
}
