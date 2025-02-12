<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Jose\Object;

use Base64Url\Base64Url;

/**
 * Class JWE.
 */
final class JWE implements JWEInterface
{
    use JWT;

    /**
     * @var \Jose\Object\RecipientInterface[]
     */
    private $recipients = [];

    /**
     * @var string|null
     */
    private $ciphertext = null;

    /**
     * @var string|null
     */
    private $iv = null;

    /**
     * @var string|null
     */
    private $aad = null;

    /**
     * @var string|null
     */
    private $tag = null;

    /**
     * @var array
     */
    private $shared_headers = [];

    /**
     * @var array
     */
    private $shared_protected_headers = [];

    /**
     * @var string|null
     */
    private $encoded_shared_protected_headers = null;

    /**
     * @var string|null
     */
    private $content_encryption_key = null;

    /**
     * {@inheritdoc}
     */
    public function countRecipients()
    {
        return count($this->recipients);
    }

    /**
     * {@inheritdoc}
     */
    public function addRecipient(RecipientInterface $recipient)
    {
        $jwe = clone $this;
        $jwe->recipients[] = $recipient;

        return $jwe;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipient($id)
    {
        if (!isset($this->recipients[$id])) {
            throw new \InvalidArgumentException('The recipient does not exist.');
        }

        return $this->recipients[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function getCiphertext()
    {
        return $this->ciphertext;
    }

    /**
     * {@inheritdoc}
     */
    public function withCiphertext($ciphertext)
    {
        $jwe = clone $this;
        $jwe->ciphertext = $ciphertext;

        return $jwe;
    }

    /**
     * {@inheritdoc}
     */
    public function getAAD()
    {
        return $this->aad;
    }

    /**
     * {@inheritdoc}
     */
    public function withAAD($aad)
    {
        $jwe = clone $this;
        $jwe->aad = $aad;

        return $jwe;
    }

    /**
     * {@inheritdoc}
     */
    public function getIV()
    {
        return $this->iv;
    }

    /**
     * {@inheritdoc}
     */
    public function withIV($iv)
    {
        $jwe = clone $this;
        $jwe->iv = $iv;

        return $jwe;
    }

    /**
     * {@inheritdoc}
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * {@inheritdoc}
     */
    public function withTag($tag)
    {
        $jwe = clone $this;
        $jwe->tag = $tag;

        return $jwe;
    }

    /**
     * {@inheritdoc}
     */
    public function getEncodedSharedProtectedHeaders()
    {
        return $this->encoded_shared_protected_headers;
    }

    /**
     * {@inheritdoc}
     */
    public function withEncodedSharedProtectedHeaders($encoded_shared_protected_headers)
    {
        $jwe = clone $this;
        $jwe->encoded_shared_protected_headers = $encoded_shared_protected_headers;

        return $jwe;
    }

    /**
     * {@inheritdoc}
     */
    public function getSharedProtectedHeaders()
    {
        return $this->shared_protected_headers;
    }

    /**
     * {@inheritdoc}
     */
    public function withSharedProtectedHeaders(array $shared_protected_headers)
    {
        $jwe = clone $this;
        $jwe->shared_protected_headers = $shared_protected_headers;

        return $jwe;
    }

    /**
     * {@inheritdoc}
     */
    public function withSharedProtectedHeader($key, $value)
    {
        $jwe = clone $this;
        $jwe->shared_protected_headers[$key] = $value;

        return $jwe;
    }

    /**
     * {@inheritdoc}
     */
    public function getSharedProtectedHeader($key)
    {
        if ($this->hasSharedProtectedHeader($key)) {
            return $this->shared_protected_headers[$key];
        }
        throw new \InvalidArgumentException(sprintf('The shared protected header "%s" does not exist.', $key));
    }

    /**
     * {@inheritdoc}
     */
    public function hasSharedProtectedHeader($key)
    {
        return array_key_exists($key, $this->shared_protected_headers);
    }

    /**
     * {@inheritdoc}
     */
    public function withSharedHeaders(array $shared_headers)
    {
        $jwe = clone $this;
        $jwe->shared_headers = $shared_headers;

        return $jwe;
    }

    /**
     * {@inheritdoc}
     */
    public function withSharedHeader($key, $value)
    {
        $jwe = clone $this;
        $jwe->shared_headers[$key] = $value;

        return $jwe;
    }

    /**
     * {@inheritdoc}
     */
    public function getSharedHeaders()
    {
        return $this->shared_headers;
    }

    /**
     * {@inheritdoc}
     */
    public function getSharedHeader($key)
    {
        if ($this->hasSharedHeader($key)) {
            return $this->shared_headers[$key];
        }
        throw new \InvalidArgumentException(sprintf('The shared header "%s" does not exist.', $key));
    }

    /**
     * {@inheritdoc}
     */
    public function hasSharedHeader($key)
    {
        return array_key_exists($key, $this->shared_headers);
    }

    /**
     * {@inheritdoc}
     */
    public function toCompactJSON($id)
    {
        $recipient = $this->getRecipient($id);

        $this->checkHasNoAAD();
        $this->checkHasSharedProtectedHeaders();
        $this->checkRecipientHasNoHeaders($id);

        return sprintf(
            '%s.%s.%s.%s.%s',
            $this->getEncodedSharedProtectedHeaders(),
            Base64Url::encode(null === $recipient->getEncryptedKey() ? '' : $recipient->getEncryptedKey()),
            Base64Url::encode(null === $this->getIV() ? '' : $this->getIV()),
            Base64Url::encode($this->getCiphertext()),
            Base64Url::encode(null === $this->getTag() ? '' : $this->getTag())
        );
    }

    private function checkHasNoAAD()
    {
        if (!empty($this->getAAD())) {
            throw new \InvalidArgumentException('This JWE has AAD and cannot be converted into Compact JSON.');
        }
    }

    /**
     * @param int $id
     */
    private function checkRecipientHasNoHeaders($id)
    {
        if (!empty($this->getSharedHeaders()) || !empty($this->getRecipient($id)->getHeaders())) {
            throw new \InvalidArgumentException('This JWE has shared headers or recipient headers and cannot be converted into Compact JSON.');
        }
    }

    private function checkHasSharedProtectedHeaders()
    {
        if (empty($this->getSharedProtectedHeaders())) {
            throw new \InvalidArgumentException('This JWE does not have shared protected headers and cannot be converted into Compact JSON.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toFlattenedJSON($id)
    {
        $recipient = $this->getRecipient($id);

        $json = $this->getJSONBase();

        if (!empty($recipient->getHeaders())) {
            $json['header'] = $recipient->getHeaders();
        }
        if (!empty($recipient->getEncryptedKey())) {
            $json['encrypted_key'] = Base64Url::encode($recipient->getEncryptedKey());
        }

        return json_encode($json);
    }

    /**
     * {@inheritdoc}
     */
    public function toJSON()
    {
        $json = $this->getJSONBase();
        $json['recipients'] = [];

        foreach ($this->getRecipients() as $recipient) {
            $temp = [];
            if (!empty($recipient->getHeaders())) {
                $temp['header'] = $recipient->getHeaders();
            }
            if (!empty($recipient->getEncryptedKey())) {
                $temp['encrypted_key'] = Base64Url::encode($recipient->getEncryptedKey());
            }
            $json['recipients'][] = $temp;
        }

        return json_encode($json);
    }

    /**
     * @return array
     */
    private function getJSONBase()
    {
        $json = [
            'ciphertext' => Base64Url::encode($this->getCiphertext()),
        ];
        if (null !== $this->getIV()) {
            $json['iv'] = Base64Url::encode($this->getIV());
        }
        if (null !== $this->getTag()) {
            $json['tag'] = Base64Url::encode($this->getTag());
        }
        if (null !== $this->getAAD()) {
            $json['aad'] = $this->getAAD();
        }
        if (!empty($this->getSharedProtectedHeaders())) {
            $json['protected'] = $this->getEncodedSharedProtectedHeaders();
        }
        if (!empty($this->getSharedHeaders())) {
            $json['unprotected'] = $this->getSharedHeaders();
        }

        return $json;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentEncryptionKey()
    {
        return $this->content_encryption_key;
    }

    /**
     * {@inheritdoc}
     */
    public function withContentEncryptionKey($content_encryption_key)
    {
        $jwe = clone $this;
        $jwe->content_encryption_key = $content_encryption_key;

        return $jwe;
    }
}
