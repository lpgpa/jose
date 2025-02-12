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

/**
 * Interface RecipientInterface.
 */
interface RecipientInterface
{
    /**
     * @return array
     */
    public function getHeaders();

    /**
     * @param array $headers
     *
     * @return \Jose\Object\RecipientInterface
     */
    public function withHeaders(array $headers);

    /**
     * @param string     $key
     * @param mixed|null $value
     *
     * @return \Jose\Object\RecipientInterface
     */
    public function withHeader($key, $value);

    /**
     * Returns the value of the unprotected header of the specified key.
     *
     * @param string $key The key
     *
     * @return mixed|null Header value
     */
    public function getHeader($key);

    /**
     * @param string $key The key
     *
     * @return bool
     */
    public function hasHeader($key);

    /**
     * @return string
     */
    public function getEncryptedKey();

    /**
     * @param string $encrypted_key
     *
     * @return \Jose\Object\RecipientInterface
     */
    public function withEncryptedKey($encrypted_key);
}
