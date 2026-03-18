<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityMerchantPortalGui;

use Spryker\Service\Http\HttpServiceInterface;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\SecurityMerchantPortalGui\Dependency\Client\SecurityMerchantPortalGuiToSecurityBlockerClientBridge;
use Spryker\Zed\SecurityMerchantPortalGui\Dependency\Client\SecurityMerchantPortalGuiToSessionClientBridge;
use Spryker\Zed\SecurityMerchantPortalGui\Dependency\Facade\SecurityMerchantPortalGuiToMerchantUserFacadeBridge;
use Spryker\Zed\SecurityMerchantPortalGui\Dependency\Facade\SecurityMerchantPortalGuiToMessengerFacadeBridge;
use Spryker\Zed\SecurityMerchantPortalGui\Dependency\Facade\SecurityMerchantPortalGuiToRouterFacadeBridge;
use Spryker\Zed\SecurityMerchantPortalGui\Dependency\Facade\SecurityMerchantPortalGuiToSecurityFacadeBridge;

/**
 * @method \Spryker\Zed\SecurityMerchantPortalGui\SecurityMerchantPortalGuiConfig getConfig()
 */
class SecurityMerchantPortalGuiDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const FACADE_MERCHANT_USER = 'FACADE_MERCHANT_USER';

    /**
     * @var string
     */
    public const FACADE_MESSENGER = 'FACADE_MESSENGER';

    /**
     * @var string
     */
    public const FACADE_SECURITY = 'FACADE_SECURITY';

    /**
     * @var string
     */
    public const FACADE_ROUTER = 'FACADE_ROUTER';

    /**
     * @var string
     */
    public const PLUGINS_MERCHANT_USER_LOGIN_RESTRICTION = 'PLUGINS_MERCHANT_USER_LOGIN_RESTRICTION';

    /**
     * @var string
     */
    public const PLUGINS_MERCHANT_USER_CRITERIA_EXPANDER_PLUGIN = 'PLUGINS_MERCHANT_USER_CRITERIA_EXPANDER_PLUGIN';

    /**
     * @uses \Spryker\Zed\Security\Communication\Plugin\Application\SecurityApplicationPlugin::SERVICE_SECURITY_TOKEN_STORAGE
     *
     * @var string
     */
    public const SERVICE_SECURITY_TOKEN_STORAGE = 'security.token_storage';

    /**
     * @uses \Spryker\Zed\Security\Communication\Loader\Services\AuthorizationCheckerServiceLoader::SERVICE_SECURITY_AUTHORIZATION_CHECKER
     *
     * @var string
     */
    public const SERVICE_SECURITY_AUTHORIZATION_CHECKER = 'security.authorization_checker';

    /**
     * @var string
     */
    public const CLIENT_SECURITY_BLOCKER = 'CLIENT_SECURITY_BLOCKER';

    /**
     * @var string
     */
    public const PLUGINS_MERCHANT_USER_AUTHENTICATION_HANDLER = 'PLUGINS_MERCHANT_USER_AUTHENTICATION_HANDLER';

    /**
     * @var string
     */
    public const CLIENT_SESSION = 'CLIENT_SESSION';

    /**
     * @uses \Spryker\Zed\ZedUi\Communication\Plugin\Application\ZedUiApplicationPlugin::SERVICE_ZED_UI_FACTORY
     *
     * @var string
     */
    public const SERVICE_ZED_UI_FACTORY = 'SERVICE_ZED_UI_FACTORY';

    public const string PLUGINS_MERCHANT_PORTAL_USER_REDIRECT_STRATEGY = 'PLUGINS_MERCHANT_PORTAL_USER_REDIRECT_STRATEGY';

    public const string SERVICE_HTTP = 'SERVICE_HTTP';

    public function provideCommunicationLayerDependencies(Container $container): Container
    {
        $container = parent::provideCommunicationLayerDependencies($container);

        $container = $this->addMerchantUserFacade($container);
        $container = $this->addMessengerFacade($container);
        $container = $this->addSecurityFacade($container);
        $container = $this->addRouterFacade($container);
        $container = $this->addTokenStorage($container);
        $container = $this->addAuthorizationCheckerService($container);
        $container = $this->addMerchantUserLoginRestrictionPlugins($container);
        $container = $this->addMerchantUserCriteriaExpanderPlugins($container);
        $container = $this->addSecurityBlockerClient($container);
        $container = $this->addMerchantUserAuthenticationHandlerPlugins($container);
        $container = $this->addSessionClient($container);
        $container = $this->addZedUiFactory($container);
        $container = $this->addMerchantPortalUserRedirectStrategyPlugins($container);
        $container = $this->addHttpService($container);

        return $container;
    }

    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = parent::provideBusinessLayerDependencies($container);

        $container = $this->addMerchantUserFacade($container);

        return $container;
    }

    protected function addSecurityBlockerClient(Container $container): Container
    {
        $container->set(static::CLIENT_SECURITY_BLOCKER, function (Container $container) {
            return new SecurityMerchantPortalGuiToSecurityBlockerClientBridge($container->getLocator()->securityBlocker()->client());
        });

        return $container;
    }

    protected function addMerchantUserFacade(Container $container): Container
    {
        $container->set(static::FACADE_MERCHANT_USER, function (Container $container) {
            return new SecurityMerchantPortalGuiToMerchantUserFacadeBridge(
                $container->getLocator()->merchantUser()->facade(),
            );
        });

        return $container;
    }

    public function addMerchantUserLoginRestrictionPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_MERCHANT_USER_LOGIN_RESTRICTION, function () {
            return $this->getMerchantUserLoginRestrictionPlugins();
        });

        return $container;
    }

    public function addMerchantUserCriteriaExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_MERCHANT_USER_CRITERIA_EXPANDER_PLUGIN, function () {
            return $this->getMerchantUserCriteriaExpanderPlugins();
        });

        return $container;
    }

    protected function addMessengerFacade(Container $container): Container
    {
        $container->set(static::FACADE_MESSENGER, function (Container $container) {
            return new SecurityMerchantPortalGuiToMessengerFacadeBridge(
                $container->getLocator()->messenger()->facade(),
            );
        });

        return $container;
    }

    protected function addSecurityFacade(Container $container): Container
    {
        $container->set(static::FACADE_SECURITY, function (Container $container) {
            return new SecurityMerchantPortalGuiToSecurityFacadeBridge(
                $container->getLocator()->security()->facade(),
            );
        });

        return $container;
    }

    protected function addRouterFacade(Container $container): Container
    {
        $container->set(static::FACADE_ROUTER, function (Container $container) {
            return new SecurityMerchantPortalGuiToRouterFacadeBridge(
                $container->getLocator()->router()->facade(),
            );
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\MerchantUserLoginRestrictionPluginInterface>
     */
    protected function getMerchantUserLoginRestrictionPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\MerchantUserCriteriaExpanderPluginInterface>
     */
    protected function getMerchantUserCriteriaExpanderPlugins(): array
    {
        return [];
    }

    protected function addTokenStorage(Container $container): Container
    {
        $container->set(static::SERVICE_SECURITY_TOKEN_STORAGE, function (Container $container) {
            return $container->getApplicationService(static::SERVICE_SECURITY_TOKEN_STORAGE);
        });

        return $container;
    }

    protected function addAuthorizationCheckerService(Container $container): Container
    {
        $container->set(static::SERVICE_SECURITY_AUTHORIZATION_CHECKER, function (Container $container) {
            return $container->getApplicationService(static::SERVICE_SECURITY_AUTHORIZATION_CHECKER);
        });

        return $container;
    }

    protected function addMerchantUserAuthenticationHandlerPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_MERCHANT_USER_AUTHENTICATION_HANDLER, function () {
            return $this->getMerchantUserAuthenticationHandlerPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\AuthenticationHandlerPluginInterface>
     */
    protected function getMerchantUserAuthenticationHandlerPlugins(): array
    {
        return [];
    }

    protected function addSessionClient(Container $container): Container
    {
        $container->set(static::CLIENT_SESSION, function (Container $container) {
            return new SecurityMerchantPortalGuiToSessionClientBridge(
                $container->getLocator()->session()->client(),
            );
        });

        return $container;
    }

    protected function addZedUiFactory(Container $container): Container
    {
        $container->set(static::SERVICE_ZED_UI_FACTORY, function (Container $container) {
            return $container->getApplicationService(static::SERVICE_ZED_UI_FACTORY);
        });

        return $container;
    }

    protected function addMerchantPortalUserRedirectStrategyPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_MERCHANT_PORTAL_USER_REDIRECT_STRATEGY, function () {
            return $this->getMerchantPortalUserRedirectStrategyPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\MerchantPortalUserRedirectStrategyPluginInterface>
     */
    protected function getMerchantPortalUserRedirectStrategyPlugins(): array
    {
        return [];
    }

    protected function addHttpService(Container $container): Container
    {
        $container->set(static::SERVICE_HTTP, function (Container $container): HttpServiceInterface {
            return $container->getLocator()->http()->service();
        });

        return $container;
    }
}
