<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security\Handler;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    protected const string ACCESS_MODE_PRE_AUTH = 'ACCESS_MODE_PRE_AUTH';

    public function __construct(
        protected TokenStorageInterface $tokenStorage,
        protected string $loginUrl,
    ) {
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?RedirectResponse
    {
        $token = $this->tokenStorage->getToken();

        if ($token === null || !in_array(static::ACCESS_MODE_PRE_AUTH, $token->getRoleNames(), true)) {
            return null;
        }

        $this->tokenStorage->setToken(null);
        $request->getSession()->invalidate();

        return new RedirectResponse($this->loginUrl);
    }
}
