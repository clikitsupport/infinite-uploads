<?php

namespace ClikIT\Infinite_Uploads\Aws\Auth;

use ClikIT\Infinite_Uploads\Aws\Identity\IdentityInterface;

/**
 * An AuthSchemeResolver object determines which auth scheme will be used for request signing.
 */
interface AuthSchemeResolverInterface
{
    /**
     * Selects an auth scheme for request signing.
     *
     * @param array $authSchemes a priority-ordered list of authentication schemes.
     * @param IdentityInterface $identity Credentials to be used in request signing.
     *
     * @return string
     */
    public function selectAuthScheme(
        array $authSchemes,
        array $args
    ): ?string;
}
