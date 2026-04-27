<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Security\Handler;

use Spryker\Zed\SecurityMerchantPortalGui\Dependency\Facade\SecurityMerchantPortalGuiToMerchantUserFacadeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class OauthMerchantPortalAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    use TargetPathTrait;

    /**
     * @uses \Spryker\Zed\SecurityMerchantPortalGui\SecurityMerchantPortalGuiConfig::MERCHANT_USER_DEFAULT_URL
     */
    protected const string HOME_URL = '/dashboard-merchant-portal-gui/dashboard';

    /**
     * @uses \Spryker\Zed\SecurityMerchantPortalGui\Communication\Expander\SecurityBuilderExpander::SECURITY_FIREWALL_NAME
     */
    protected const string SECURITY_FIREWALL_NAME = 'MerchantUser';

    public function __construct(
        protected SecurityMerchantPortalGuiToMerchantUserFacadeInterface $merchantUserFacade,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        /** @var \Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Security\SecurityOauthMerchantUserInterface $user */
        $user = $token->getUser();

        $this->merchantUserFacade->setCurrentMerchantUser($user->getMerchantUserTransfer());

        return $this->createRedirectResponse($request);
    }

    protected function createRedirectResponse(Request $request): RedirectResponse
    {
        $targetUrl = $this->getTargetPath($request->getSession(), static::SECURITY_FIREWALL_NAME);

        if ($targetUrl !== null) {
            return new RedirectResponse($targetUrl);
        }

        return new RedirectResponse(static::HOME_URL);
    }
}
