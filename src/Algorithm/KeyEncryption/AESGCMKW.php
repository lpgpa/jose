<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Jose\Algorithm\KeyEncryption;

use Base64Url\Base64Url;
use Crypto\Cipher;
use Jose\Object\JWKInterface;
use Jose\Util\GCM;
use Jose\Util\StringUtil;

/**
 * Class AESGCMKW.
 */
abstract class AESGCMKW implements KeyWrappingInterface
{
    /**
     * {@inheritdoc}
     */
    public function wrapKey(JWKInterface $key, $cek, array $complete_headers, array &$additional_headers)
    {
        $this->checkKey($key);
        $kek = Base64Url::decode($key->get('k'));
        $iv = StringUtil::generateRandomBytes(96 / 8);
        $additional_headers['iv'] = Base64Url::encode($iv);

        if (class_exists('\Crypto\Cipher')) {
            $cipher = Cipher::aes(Cipher::MODE_GCM, $this->getKeySize());
            $cipher->setAAD(null);
            $encrypted_cek = $cipher->encrypt($cek, $kek, $iv);

            $additional_headers['tag'] = Base64Url::encode($cipher->getTag());
        } elseif (version_compare(PHP_VERSION, '7.1.0') >= 0) {
            $tag = null;
            $encrypted_cek = openssl_encrypt($cek, $this->getMode($kek), $kek, OPENSSL_RAW_DATA, $iv, $tag, null, 16);
            $additional_headers['tag'] = Base64Url::encode($tag);
        } else {
            list($encrypted_cek, $tag) = GCM::encrypt($kek, $iv, $cek, null);
            $additional_headers['tag'] = Base64Url::encode($tag);
        }

        return $encrypted_cek;
    }

    /**
     * {@inheritdoc}
     */
    public function unwrapKey(JWKInterface $key, $encrypted_cek, array $header)
    {
        $this->checkKey($key);
        $this->checkAdditionalParameters($header);

        $kek = Base64Url::decode($key->get('k'));
        $tag = Base64Url::decode($header['tag']);
        $iv = Base64Url::decode($header['iv']);

        if (class_exists('\Crypto\Cipher')) {
            $cipher = Cipher::aes(Cipher::MODE_GCM, $this->getKeySize());
            $cipher->setTag($tag);
            $cipher->setAAD(null);

            $cek = $cipher->decrypt($encrypted_cek, $kek, $iv);

            return $cek;
        } elseif (version_compare(PHP_VERSION, '7.1.0') >= 0) {
            return openssl_decrypt($encrypted_cek, $this->getMode($kek), $kek, OPENSSL_RAW_DATA, $iv, $tag, null);
        }

        return GCM::decrypt($kek, $iv, $encrypted_cek, null, $tag);
    }

    /**
     * @param string $k
     *
     * @return string
     */
    private function getMode($k)
    {
        return 'aes-'.(8 *  strlen($k)).'-gcm';
    }

    /**
     * {@inheritdoc}
     */
    public function getKeyManagementMode()
    {
        return self::MODE_WRAP;
    }

    /**
     * @param JWKInterface $key
     */
    protected function checkKey(JWKInterface $key)
    {
        if ('oct' !== $key->get('kty') || !$key->has('k')) {
            throw new \InvalidArgumentException('The key is not valid');
        }
    }

    /**
     * @param array $header
     */
    protected function checkAdditionalParameters(array $header)
    {
        if (!array_key_exists('iv', $header) || !array_key_exists('tag', $header)) {
            throw new \InvalidArgumentException("Missing parameters 'iv' or 'tag'.");
        }
    }

    /**
     * @return int
     */
    abstract protected function getKeySize();
}
