<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Jose;

use Jose\Algorithm\JWAManagerInterface;
use Jose\Algorithm\SignatureAlgorithmInterface;
use Jose\Behaviour\HasJWAManager;
use Jose\Behaviour\HasKeyChecker;
use Jose\Behaviour\HasLogger;
use Jose\Object\JWKInterface;
use Jose\Object\JWKSet;
use Jose\Object\JWKSetInterface;
use Jose\Object\JWSInterface;
use Jose\Object\SignatureInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class Verifier implements VerifierInterface
{
    use HasKeyChecker;
    use HasJWAManager;
    use HasLogger;

    /**
     * Verifier constructor.
     *
     * @param \Jose\Algorithm\JWAManagerInterface $jwa_manager
     * @param \Psr\Log\LoggerInterface|null       $logger
     */
    public function __construct(JWAManagerInterface $jwa_manager,
                                LoggerInterface $logger = null
    ) {
        $this->setJWAManager($jwa_manager);

        if (null !== $logger) {
            $this->setLogger($logger);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function verifyWithKey(JWSInterface $jws, JWKInterface $jwk, $detached_payload = null, &$recipient_index = null)
    {
        $this->log(LogLevel::DEBUG, 'Trying to verify the JWS with the key', ['jws' => $jws, 'jwk' => $jwk, 'detached_payload' => $detached_payload]);
        $jwk_set = new JWKSet();
        $jwk_set = $jwk_set->addKey($jwk);

        return $this->verifySignatures($jws, $jwk_set, $detached_payload, $recipient_index);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function verifyWithKeySet(JWSInterface $jws, JWKSetInterface $jwk_set, $detached_payload = null, &$recipient_index = null)
    {
        $this->log(LogLevel::DEBUG, 'Trying to verify the JWS with the key set', ['jwk' => $jws, 'jwk_set' => $jwk_set, 'detached_payload' => $detached_payload]);

        return $this->verifySignatures($jws, $jwk_set, $detached_payload, $recipient_index);
    }

    /**
     * @param \Jose\Object\JWSInterface       $jws
     * @param \Jose\Object\JWKSetInterface    $jwk_set
     * @param \Jose\Object\SignatureInterface $signature
     * @param string|null                     $detached_payload
     *
     * @return bool
     */
    private function verifySignature(JWSInterface $jws, JWKSetInterface $jwk_set, SignatureInterface $signature, $detached_payload = null)
    {
        $input = $signature->getEncodedProtectedHeaders().'.'.(null === $detached_payload ? $jws->getEncodedPayload() : $detached_payload);

        foreach ($jwk_set->getKeys() as $jwk) {
            $algorithm = $this->getAlgorithm($signature);
            try {
                $this->checkKeyUsage($jwk, 'verification');
                $this->checkKeyAlgorithm($jwk, $algorithm->getAlgorithmName());
                if (true === $algorithm->verify($jwk, $input, $signature->getSignature())) {
                    return true;
                }
            } catch (\Exception $e) {
                //We do nothing, we continue with other keys
                continue;
            }
        }

        return false;
    }

    /**
     * @param \Jose\Object\JWSInterface    $jws
     * @param \Jose\Object\JWKSetInterface $jwk_set
     * @param string|null                  $detached_payload
     * @param int|null                     $recipient_index
     *
     * @return bool
     */
    private function verifySignatures(JWSInterface $jws, JWKSetInterface $jwk_set, $detached_payload = null, &$recipient_index = null)
    {
        $this->checkPayload($jws, $detached_payload);
        $this->checkJWKSet($jwk_set);
        $this->checkSignatures($jws);

        $nb_signatures = $jws->countSignatures();

        for ($i = 0; $i < $nb_signatures; $i++) {
            $signature = $jws->getSignature($i);
            $result = $this->verifySignature($jws, $jwk_set, $signature, $detached_payload);

            if (true === $result) {
                $recipient_index = $i;

                return true;
            }
        }

        return false;
    }

    /**
     * @param \Jose\Object\JWSInterface $jws
     */
    private function checkSignatures(JWSInterface $jws)
    {
        if (0 === $jws->countSignatures()) {
            $this->log(LogLevel::ERROR, 'There is no signature in the JWS', ['jws' => $jws]);
            throw new \InvalidArgumentException('The JWS does not contain any signature.');
        }
        $this->log(LogLevel::INFO, 'The JWS contains {nb} signature(s)', ['nb' => $jws->countSignatures()]);
    }

    /**
     * @param \Jose\Object\JWKSetInterface $jwk_set
     */
    private function checkJWKSet(JWKSetInterface $jwk_set)
    {
        if (0 === count($jwk_set)) {
            $this->log(LogLevel::ERROR, 'There is no key in the key set', ['jwk_set' => $jwk_set]);
            throw new \InvalidArgumentException('No key in the key set.');
        }
        $this->log(LogLevel::INFO, 'The JWK Set contains {nb} key(s)', ['nb' => count($jwk_set)]);
    }

    /**
     * @param \Jose\Object\JWSInterface $jws
     * @param null|string               $detached_payload
     */
    private function checkPayload(JWSInterface $jws, $detached_payload = null)
    {
        if (null !== $detached_payload && !empty($jws->getEncodedPayload())) {
            $this->log(LogLevel::ERROR, 'A detached payload is set, but the JWS already has a payload');
            throw new \InvalidArgumentException('A detached payload is set, but the JWS already has a payload.');
        }
    }

    /**
     * @param \Jose\Object\SignatureInterface $signature
     *
     * @return \Jose\Algorithm\SignatureAlgorithmInterface
     */
    private function getAlgorithm(SignatureInterface $signature)
    {
        $complete_headers = array_merge(
            $signature->getProtectedHeaders(),
            $signature->getHeaders()
        );
        if (!array_key_exists('alg', $complete_headers)) {
            $this->log(LogLevel::ERROR, 'No "alg" parameter set in the header.');
            throw new \InvalidArgumentException('No "alg" parameter set in the header.');
        }

        $algorithm = $this->getJWAManager()->getAlgorithm($complete_headers['alg']);
        if (!$algorithm instanceof SignatureAlgorithmInterface) {
            throw new \RuntimeException(sprintf('The algorithm "%s" is not supported or does not implement SignatureInterface.', $complete_headers['alg']));
        }

        return $algorithm;
    }
}
