<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication;

use Generated\Shared\Transfer\MerchantUserTransfer;
use Spryker\Service\Http\HttpServiceInterface;
use Spryker\Shared\Kernel\StrategyResolver;
use Spryker\Shared\Kernel\StrategyResolverInterface;
use Spryker\Shared\ZedUi\ZedUiFactoryInterface;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Authenticator\MerchantLoginFormAuthenticator;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Badge\MultiFactorAuthBadge;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Builder\OptionsBuilder;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Builder\OptionsBuilderInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Checker\LastVisitedPageUrlChecker;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Checker\LastVisitedPageUrlCheckerInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\EventSubscriber\LastVisitedPageEventSubscriber;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Expander\SecurityBuilderExpander;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Expander\SecurityBuilderExpanderInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Form\MerchantLoginForm;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Form\MerchantResetPasswordForm;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Form\MerchantResetPasswordRequestForm;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Logger\AuditLogger;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Logger\AuditLoggerInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Authenticator\OauthMerchantPortalTokenAuthenticator;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Expander\OauthSecurityBuilderExpander;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Expander\OauthSecurityBuilderExpanderInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Loader\OauthMerchantUserLoader;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Loader\OauthMerchantUserLoaderInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Reader\ResourceOwnerReader;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Reader\ResourceOwnerReaderInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Resolver\OauthMerchantUserResolver;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Resolver\OauthMerchantUserResolverInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Security\Handler\OauthMerchantPortalAuthenticationFailureHandler;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Security\Handler\OauthMerchantPortalAuthenticationSuccessHandler;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security\Handler\MerchantUserAuthenticationFailureHandler;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security\Handler\MerchantUserAuthenticationSuccessHandler;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security\MerchantUserSecurityPlugin;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security\Provider\MerchantUserProvider;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security\Provider\OauthMerchantUserProvider;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Resolver\LastVisitedPageRedirectResolver;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Resolver\LastVisitedPageRedirectResolverInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Security\MerchantUser;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Security\MerchantUserInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Storage\LastVisitedPageCookieStorage;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Storage\LastVisitedPageStorageInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Updater\SecurityTokenUpdater;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Updater\SecurityTokenUpdaterInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Dependency\Client\SecurityMerchantPortalGuiToSecurityBlockerClientInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Dependency\Client\SecurityMerchantPortalGuiToSessionClientInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Dependency\Facade\SecurityMerchantPortalGuiToMerchantUserFacadeInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Dependency\Facade\SecurityMerchantPortalGuiToMessengerFacadeInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Dependency\Facade\SecurityMerchantPortalGuiToRouterFacadeInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Dependency\Facade\SecurityMerchantPortalGuiToSecurityFacadeInterface;
use Spryker\Zed\SecurityMerchantPortalGui\SecurityMerchantPortalGuiConfig;
use Spryker\Zed\SecurityMerchantPortalGui\SecurityMerchantPortalGuiDependencyProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;

/**
 * @method \Spryker\Zed\SecurityMerchantPortalGui\SecurityMerchantPortalGuiConfig getConfig()
 */
class SecurityMerchantPortalGuiCommunicationFactory extends AbstractCommunicationFactory
{
    public function createMerchantUserProvider(): UserProviderInterface
    {
        return new MerchantUserProvider(
            $this->getMerchantUserLoginRestrictionPlugins(),
            $this->getMerchantUserCriteriaExpanderPlugins(),
        );
    }

    /**
     * @return \Symfony\Component\Form\FormInterface<mixed>
     */
    public function createLoginForm(): FormInterface
    {
        return $this->getFormFactory()->create(MerchantLoginForm::class);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface<mixed>
     */
    public function createResetPasswordRequestForm(): FormInterface
    {
        return $this->getFormFactory()->create(MerchantResetPasswordRequestForm::class);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface<mixed>
     */
    public function createResetPasswordForm(): FormInterface
    {
        return $this->getFormFactory()->create(MerchantResetPasswordForm::class);
    }

    public function createSecurityUser(MerchantUserTransfer $merchantUserTransfer): MerchantUserInterface
    {
        return new MerchantUser(
            $merchantUserTransfer,
            [SecurityMerchantPortalGuiConfig::ROLE_MERCHANT_USER],
        );
    }

    public function createMerchantUserAuthenticationSuccessHandler(): AuthenticationSuccessHandlerInterface
    {
        return new MerchantUserAuthenticationSuccessHandler();
    }

    public function createMerchantUserAuthenticationFailureHandler(): AuthenticationFailureHandlerInterface
    {
        return new MerchantUserAuthenticationFailureHandler();
    }

    public function createSecurityTokenUpdater(): SecurityTokenUpdaterInterface
    {
        return new SecurityTokenUpdater(
            $this->getTokenStorageService(),
            $this->getAuthorizationCheckerService(),
        );
    }

    public function createAuditLogger(): AuditLoggerInterface
    {
        return new AuditLogger();
    }

    public function getMerchantUserFacade(): SecurityMerchantPortalGuiToMerchantUserFacadeInterface
    {
        return $this->getProvidedDependency(SecurityMerchantPortalGuiDependencyProvider::FACADE_MERCHANT_USER);
    }

    public function getMessengerFacade(): SecurityMerchantPortalGuiToMessengerFacadeInterface
    {
        return $this->getProvidedDependency(SecurityMerchantPortalGuiDependencyProvider::FACADE_MESSENGER);
    }

    public function getSecurityFacade(): SecurityMerchantPortalGuiToSecurityFacadeInterface
    {
        return $this->getProvidedDependency(SecurityMerchantPortalGuiDependencyProvider::FACADE_SECURITY);
    }

    public function getRouterFacade(): SecurityMerchantPortalGuiToRouterFacadeInterface
    {
        return $this->getProvidedDependency(SecurityMerchantPortalGuiDependencyProvider::FACADE_ROUTER);
    }

    public function getTokenStorageService(): TokenStorageInterface
    {
        return $this->getProvidedDependency(SecurityMerchantPortalGuiDependencyProvider::SERVICE_SECURITY_TOKEN_STORAGE);
    }

    /**
     * @return array<\Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\MerchantUserLoginRestrictionPluginInterface>
     */
    public function getMerchantUserLoginRestrictionPlugins(): array
    {
        return $this->getProvidedDependency(SecurityMerchantPortalGuiDependencyProvider::PLUGINS_MERCHANT_USER_LOGIN_RESTRICTION);
    }

    /**
     * @return array<\Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\MerchantUserCriteriaExpanderPluginInterface>
     */
    public function getMerchantUserCriteriaExpanderPlugins(): array
    {
        return $this->getProvidedDependency(SecurityMerchantPortalGuiDependencyProvider::PLUGINS_MERCHANT_USER_CRITERIA_EXPANDER_PLUGIN);
    }

    public function createMechantLoginFormAuthenticator(): AuthenticatorInterface
    {
        return new MerchantLoginFormAuthenticator(
            $this->createMerchantUserProvider(),
            $this->createMerchantUserAuthenticationSuccessHandler(),
            $this->createMerchantUserAuthenticationFailureHandler(),
            $this->getConfig(),
            $this->createMultiFactorAuthBadge(),
        );
    }

    public function createSecurityBuilderExpander(): SecurityBuilderExpanderInterface
    {
        if (class_exists(AuthenticationProviderManager::class) === true) {
            return new MerchantUserSecurityPlugin();
        }

        return new SecurityBuilderExpander(
            $this->createOptionsBuilder(),
            $this->createMechantLoginFormAuthenticator(),
            $this->getConfig(),
        );
    }

    public function createOptionsBuilder(): OptionsBuilderInterface
    {
        return new OptionsBuilder(
            $this->createMerchantUserProvider(),
        );
    }

    public function getSecurityBlockerClient(): SecurityMerchantPortalGuiToSecurityBlockerClientInterface
    {
        return $this->getProvidedDependency(SecurityMerchantPortalGuiDependencyProvider::CLIENT_SECURITY_BLOCKER);
    }

    public function getAuthorizationCheckerService(): AuthorizationCheckerInterface
    {
        return $this->getProvidedDependency(SecurityMerchantPortalGuiDependencyProvider::SERVICE_SECURITY_AUTHORIZATION_CHECKER);
    }

    public function createMultiFactorAuthBadge(): MultiFactorAuthBadge
    {
        return new MultiFactorAuthBadge($this->getMerchantUserMultiFactorAuthenticationHandlerPlugins());
    }

    /**
     * @return array<\Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\AuthenticationHandlerPluginInterface>
     */
    public function getMerchantUserMultiFactorAuthenticationHandlerPlugins(): array
    {
        return $this->getProvidedDependency(SecurityMerchantPortalGuiDependencyProvider::PLUGINS_MERCHANT_USER_AUTHENTICATION_HANDLER);
    }

    public function getSessionClient(): SecurityMerchantPortalGuiToSessionClientInterface
    {
        return $this->getProvidedDependency(SecurityMerchantPortalGuiDependencyProvider::CLIENT_SESSION);
    }

    public function getZedUiFactory(): ZedUiFactoryInterface
    {
        return $this->getProvidedDependency(SecurityMerchantPortalGuiDependencyProvider::SERVICE_ZED_UI_FACTORY);
    }

    public function createLastVisitedPageEventSubscriber(): EventSubscriberInterface
    {
        return new LastVisitedPageEventSubscriber(
            $this->createLastVisitedPageUrlChecker(),
            $this->createLastVisitedPageStorageResolver()->get($this->getConfig()->getLastVisitedPageStorageType()),
        );
    }

    public function createLastVisitedPageUrlChecker(): LastVisitedPageUrlCheckerInterface
    {
        return new LastVisitedPageUrlChecker(
            $this->getSecurityFacade(),
            $this->getHttpService(),
        );
    }

    /**
     * @return \Spryker\Shared\Kernel\StrategyResolverInterface<\Spryker\Zed\SecurityMerchantPortalGui\Communication\Storage\LastVisitedPageStorageInterface>
     */
    public function createLastVisitedPageStorageResolver(): StrategyResolverInterface
    {
        return new StrategyResolver(
            [SecurityMerchantPortalGuiConfig::STORAGE_TYPE_COOKIE => $this->createLastVisitedPageCookieStorage()],
            SecurityMerchantPortalGuiConfig::STORAGE_TYPE_COOKIE,
        );
    }

    public function createLastVisitedPageCookieStorage(): LastVisitedPageStorageInterface
    {
        return new LastVisitedPageCookieStorage($this->getConfig());
    }

    public function getHttpService(): HttpServiceInterface
    {
        return $this->getProvidedDependency(SecurityMerchantPortalGuiDependencyProvider::SERVICE_HTTP);
    }

    public function createLastVisitedPageRedirectResolver(): LastVisitedPageRedirectResolverInterface
    {
        return new LastVisitedPageRedirectResolver(
            $this->createLastVisitedPageStorageResolver()->get($this->getConfig()->getLastVisitedPageStorageType()),
            $this->getHttpService(),
        );
    }

    /**
     * @return array<\Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\MerchantPortalUserRedirectStrategyPluginInterface>
     */
    public function getMerchantPortalUserRedirectStrategyPlugins(): array
    {
        return $this->getProvidedDependency(SecurityMerchantPortalGuiDependencyProvider::PLUGINS_MERCHANT_PORTAL_USER_REDIRECT_STRATEGY);
    }

    /**
     * @return array<\Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\MerchantUserAuthenticationLinkPluginInterface>
     */
    public function getMerchantPortalAuthenticationLinkPlugins(): array
    {
        return $this->getProvidedDependency(SecurityMerchantPortalGuiDependencyProvider::PLUGINS_MERCHANT_PORTAL_AUTHENTICATION_LINK);
    }

    public function createOauthSecurityBuilderExpander(): OauthSecurityBuilderExpanderInterface
    {
        return new OauthSecurityBuilderExpander(
            $this->createOauthMerchantUserProvider(),
            $this->createOauthMerchantPortalTokenAuthenticator(),
        );
    }

    public function createOauthMerchantPortalTokenAuthenticator(): OauthMerchantPortalTokenAuthenticator
    {
        return new OauthMerchantPortalTokenAuthenticator(
            $this->createResourceOwnerReader(),
            $this->createOauthMerchantPortalAuthenticationSuccessHandler(),
            $this->createOauthMerchantPortalAuthenticationFailureHandler(),
            $this->createOauthMerchantUserResolver(),
            $this->getConfig(),
            $this->getOauthMerchantUserRestrictionPlugins(),
            $this->getMessengerFacade(),
        );
    }

    public function createOauthMerchantUserProvider(): OauthMerchantUserProvider
    {
        return new OauthMerchantUserProvider();
    }

    public function createOauthMerchantUserLoader(): OauthMerchantUserLoaderInterface
    {
        return new OauthMerchantUserLoader(
            $this->getMerchantUserFacade(),
            $this->getOauthMerchantUserRestrictionPlugins(),
            $this->getMessengerFacade(),
        );
    }

    public function createResourceOwnerReader(): ResourceOwnerReaderInterface
    {
        return new ResourceOwnerReader($this->getOauthMerchantUserClientStrategyPlugins());
    }

    public function createOauthMerchantPortalAuthenticationSuccessHandler(): OauthMerchantPortalAuthenticationSuccessHandler
    {
        return new OauthMerchantPortalAuthenticationSuccessHandler(
            $this->getMerchantUserFacade(),
        );
    }

    public function createOauthMerchantPortalAuthenticationFailureHandler(): OauthMerchantPortalAuthenticationFailureHandler
    {
        return new OauthMerchantPortalAuthenticationFailureHandler(
            $this->getMessengerFacade(),
        );
    }

    /**
     * @return array<\Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\OauthMerchantUserClientStrategyPluginInterface>
     */
    public function getOauthMerchantUserClientStrategyPlugins(): array
    {
        return $this->getProvidedDependency(SecurityMerchantPortalGuiDependencyProvider::PLUGINS_OAUTH_MERCHANT_USER_CLIENT_STRATEGY);
    }

    /**
     * @return array<\Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\OauthMerchantUserAuthenticationStrategyPluginInterface>
     */
    public function getOauthMerchantUserAuthenticationStrategyPlugins(): array
    {
        return $this->getProvidedDependency(SecurityMerchantPortalGuiDependencyProvider::PLUGINS_OAUTH_MERCHANT_USER_AUTHENTICATION_STRATEGY);
    }

    /**
     * @return array<\Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\OauthMerchantUserPostResolvePluginInterface>
     */
    public function getOauthMerchantUserPostResolvePlugins(): array
    {
        return $this->getProvidedDependency(SecurityMerchantPortalGuiDependencyProvider::PLUGINS_OAUTH_MERCHANT_USER_POST_RESOLVE);
    }

    /**
     * @return array<\Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\OauthMerchantUserRestrictionPluginInterface>
     */
    public function getOauthMerchantUserRestrictionPlugins(): array
    {
        return $this->getProvidedDependency(SecurityMerchantPortalGuiDependencyProvider::PLUGINS_OAUTH_MERCHANT_USER_RESTRICTION);
    }

    public function createOauthMerchantUserResolver(): OauthMerchantUserResolverInterface
    {
        return new OauthMerchantUserResolver(
            $this->getOauthMerchantUserAuthenticationStrategyPlugins(),
            $this->getOauthMerchantUserPostResolvePlugins(),
        );
    }
}
