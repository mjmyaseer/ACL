<?php

namespace Manager\Data\Repositories\Adapters;

interface HashAdapterInterface
{
    /**
     * HashAdapter constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = []);

    /**
     * Hash a string.
     *
     * @param string $string
     * @return string
     */
    public function hash($string);

    /**
     * Verify the password
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function verify($password, $hash);
}