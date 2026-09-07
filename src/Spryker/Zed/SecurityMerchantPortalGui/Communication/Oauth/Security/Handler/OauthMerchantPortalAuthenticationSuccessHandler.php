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

    /**
     * @uses \Spryker\Zed\SecurityMerchantPortalGui\Communication\Expander\SecurityBuilderExpander::ACCESS_MODE_PRE_AUTH
     */
    protected const string ACCESS_MODE_PRE_AUTH = 'ACCESS_MODE_PRE_AUTH';

    /**
     * @uses \Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security\Handler\MerchantUserAuthenticationSuccessHandler::MULTI_FACTOR_AUTH_LOGIN_USER_EMAIL_SESSION_KEY
     */
    protected const string MULTI_FACTOR_AUTH_LOGIN_USER_EMAIL_SESSION_KEY = '_multi_factor_auth_login_user_email';

    /**
     * @uses \Spryker\Zed\MultiFactorAuthMerchantPortal\Communication\Controller\MerchantUserOauthMultiFactorAuthFlowController::getEnabledTypesAction()
     */
    protected const string ROUTE_MERCHANT_USER_OAUTH_MFA = '/multi-factor-auth-merchant-portal/merchant-user-oauth-multi-factor-auth-flow/get-enabled-types';

    public function __construct(
        protected SecurityMerchantPortalGuiToMerchantUserFacadeInterface $merchantUserFacade,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        /** @var \Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Security\SecurityOauthMerchantUserInterface $user */
        $user = $token->getUser();

        if (in_array(static::ACCESS_MODE_PRE_AUTH, $token->getRoleNames(), true)) {
            $request->getSession()->set(
                static::MULTI_FACTOR_AUTH_LOGIN_USER_EMAIL_SESSION_KEY,
                $user->getMerchantUserTransfer()->getUserOrFail()->getUsername(),
            );

            return new RedirectResponse(static::ROUTE_MERCHANT_USER_OAUTH_MFA);
        }

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
