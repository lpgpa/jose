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

interface JWKSetInterface extends \Countable, \Iterator, \JsonSerializable
{
    /**
     * @param $index
     *
     * @throws
     *
     * @return \Jose\Object\JWKInterface
     */
    public function getKey($index);

    /**
     * Returns all keys in the key set.
     *
     * @return \Jose\Object\JWKInterface[] An array of keys stored in the key set
     */
    public function getKeys();

    /**
     * Add key in the key set.
     *
     * @param \Jose\Object\JWKInterface A key to store in the key set
     *
     * @return \Jose\Object\JWKSetInterface
     */
    public function addKey(JWKInterface $key);

    /**
     * Remove key from the key set.
     *
     * @param int $index Key to remove from the key set
     *
     * @return \Jose\Object\JWKSetInterface
     */
    public function removeKey($index);
}
