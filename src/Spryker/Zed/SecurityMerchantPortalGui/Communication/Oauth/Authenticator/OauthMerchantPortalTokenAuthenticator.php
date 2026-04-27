<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Authenticator;

use Generated\Shared\Transfer\MerchantUserTransfer;
use Generated\Shared\Transfer\OauthMerchantUserRestrictionRequestTransfer;
use Generated\Shared\Transfer\OauthMerchantUserRestrictionResponseTransfer;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Reader\ResourceOwnerReaderInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Resolver\OauthMerchantUserResolverInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Security\SecurityOauthMerchantUser;
use Spryker\Zed\SecurityMerchantPortalGui\Dependency\Facade\SecurityMerchantPortalGuiToMessengerFacadeInterface;
use Spryker\Zed\SecurityMerchantPortalGui\SecurityMerchantPortalGuiConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class OauthMerchantPortalTokenAuthenticator extends AbstractAuthenticator
{
    protected const string PARAMETER_ROUTE = '_route';

    protected const string EXCEPTION_MESSAGE_NO_RESOURCE_OWNER = 'No OAuth resource owner could be resolved.';

    protected const string ROUTE_NAME_OAUTH_MERCHANT_PORTAL_LOGIN = 'security-merchant-portal-gui:oauth-login';

    /**
     * @param \Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Reader\ResourceOwnerReaderInterface $resourceOwnerReader
     * @param \Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface $authenticationSuccessHandler
     * @param \Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface $authenticationFailureHandler
     * @param \Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Resolver\OauthMerchantUserResolverInterface $oauthMerchantUserResolver
     * @param \Spryker\Zed\SecurityMerchantPortalGui\SecurityMerchantPortalGuiConfig $securityMerchantPortalGuiConfig
     * @param array<\Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\OauthMerchantUserRestrictionPluginInterface> $oauthMerchantPortalRestrictionPlugins
     * @param \Spryker\Zed\SecurityMerchantPortalGui\Dependency\Facade\SecurityMerchantPortalGuiToMessengerFacadeInterface $messengerFacade
     */
    public function __construct(
        protected ResourceOwnerReaderInterface $resourceOwnerReader,
        protected AuthenticationSuccessHandlerInterface $authenticationSuccessHandler,
        protected AuthenticationFailureHandlerInterface $authenticationFailureHandler,
        protected OauthMerchantUserResolverInterface $oauthMerchantUserResolver,
        protected SecurityMerchantPortalGuiConfig $securityMerchantPortalGuiConfig,
        protected array $oauthMerchantPortalRestrictionPlugins,
        protected SecurityMerchantPortalGuiToMessengerFacadeInterface $messengerFacade,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get(static::PARAMETER_ROUTE) === static::ROUTE_NAME_OAUTH_MERCHANT_PORTAL_LOGIN;
    }

    /**
     * @throws \Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException
     */
    public function authenticate(Request $request): Passport
    {
        $resourceOwnerTransfer = $this->resourceOwnerReader->getResourceOwner($request);

        if ($resourceOwnerTransfer === null) {
            throw new CustomUserMessageAuthenticationException(static::EXCEPTION_MESSAGE_NO_RESOURCE_OWNER);
        }

        return new SelfValidatingPassport(
            new UserBadge($resourceOwnerTransfer->getEmailOrFail(), function () use ($resourceOwnerTransfer) {
                $merchantUserTransfer = $this->oauthMerchantUserResolver->resolveOauthMerchantUserByResourceOwner($resourceOwnerTransfer);

                if ($merchantUserTransfer === null) {
                    return null;
                }

                return $this->createSecurityUserFromMerchantUserTransfer($merchantUserTransfer);
            }),
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return $this->authenticationSuccessHandler->onAuthenticationSuccess($request, $token);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return $this->authenticationFailureHandler->onAuthenticationFailure($request, $exception);
    }

    protected function createSecurityUserFromMerchantUserTransfer(MerchantUserTransfer $merchantUserTransfer): SecurityOauthMerchantUser
    {
        $restrictionResponse = $this->isRestricted(
            (new OauthMerchantUserRestrictionRequestTransfer())->setMerchantUser($merchantUserTransfer),
        );

        if ($restrictionResponse->getIsRestricted()) {
            $this->addErrorMessages($restrictionResponse);

            throw new UnsupportedUserException(
                sprintf('Merchant user "%s" is restricted from OAuth login.', $merchantUserTransfer->getUserOrFail()->getUsernameOrFail()),
            );
        }

        return new SecurityOauthMerchantUser($merchantUserTransfer, [SecurityMerchantPortalGuiConfig::ROLE_MERCHANT_USER]);
    }

    protected function isRestricted(
        OauthMerchantUserRestrictionRequestTransfer $oauthMerchantUserRestrictionRequestTransfer
    ): OauthMerchantUserRestrictionResponseTransfer {
        foreach ($this->oauthMerchantPortalRestrictionPlugins as $oauthMerchantPortalRestrictionPlugin) {
            $oauthMerchantUserRestrictionResponseTransfer = $oauthMerchantPortalRestrictionPlugin->isRestricted($oauthMerchantUserRestrictionRequestTransfer);

            if ($oauthMerchantUserRestrictionResponseTransfer->getIsRestricted()) {
                return $oauthMerchantUserRestrictionResponseTransfer;
            }
        }

        return (new OauthMerchantUserRestrictionResponseTransfer())->setIsRestricted(false);
    }

    protected function addErrorMessages(OauthMerchantUserRestrictionResponseTransfer $oauthMerchantUserRestrictionResponseTransfer): void
    {
        foreach ($oauthMerchantUserRestrictionResponseTransfer->getMessages() as $messageTransfer) {
            $this->messengerFacade->addErrorMessage($messageTransfer);
        }
    }
}
