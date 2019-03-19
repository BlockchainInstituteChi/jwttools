<?php

namespace Blockchaininstitute;

use Tuupola\Base58;

class didResolver
{

    /**
     * Create a new Skeleton Instance
     */
    public function __construct()
    {
    }

    /**
     * Friendly welcome
     *
     * @param string $phrase Phrase to return
     *
     * @return string Returns the phrase passed in
     */
    public function resolve_did($mnid)
    {
        return $mnid;
    }
}

?>