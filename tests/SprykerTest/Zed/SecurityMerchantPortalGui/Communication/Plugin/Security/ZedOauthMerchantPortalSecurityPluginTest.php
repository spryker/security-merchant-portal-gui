<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\MerchantUserTransfer;
use Generated\Shared\Transfer\MultiFactorAuthValidationResponseTransfer;
use Generated\Shared\Transfer\ResourceOwnerResponseTransfer;
use Generated\Shared\Transfer\ResourceOwnerTransfer;
use Generated\Shared\Transfer\UserTransfer;
use ReflectionClass;
use Spryker\Shared\Security\Configuration\SecurityConfiguration;
use Spryker\Zed\Security\Communication\Configurator\SecurityConfigurator;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security\ZedMerchantUserSecurityPlugin;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security\ZedOauthMerchantPortalSecurityPlugin;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\SecurityMerchantPortalGui\ExistingMerchantUserAuthenticationStrategyPlugin;
use Spryker\Zed\SecurityMerchantPortalGui\SecurityMerchantPortalGuiDependencyProvider;
use Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\AuthenticationHandlerPluginInterface;
use Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\OauthMerchantUserClientStrategyPluginInterface;
use SprykerTest\Zed\SecurityMerchantPortalGui\SecurityMerchantPortalGuiCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SecurityMerchantPortalGui
 * @group Communication
 * @group Plugin
 * @group Security
 * @group ZedOauthMerchantPortalSecurityPluginTest
 * Add your own group annotations below this line
 */
class ZedOauthMerchantPortalSecurityPluginTest extends Unit
{
    /**
     * @uses \Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Expander\OauthSecurityBuilderExpander::SECURITY_OAUTH_MERCHANT_PORTAL_TOKEN_AUTHENTICATOR
     *
     * @var string
     */
    protected const string SECURITY_OAUTH_MERCHANT_PORTAL_TOKEN_AUTHENTICATOR = 'security.OauthMerchantPortal.token.authenticator';

    /**
     * @uses \Spryker\Zed\SecurityMerchantPortalGui\Communication\Expander\SecurityBuilderExpander::SECURITY_FIREWALL_NAME
     */
    protected const string SECURITY_MERCHANT_USER_FIREWALL_NAME = 'MerchantUser';

    /**
     * @uses \Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Expander\OauthSecurityBuilderExpander::OAUTH_MERCHANT_PORTAL_MULTI_FACTOR_AUTH_PATH_PATTERN
     */
    protected const string OAUTH_MULTI_FACTOR_AUTH_PATH_PATTERN = '^/multi-factor-auth-merchant-portal/merchant-user-oauth-multi-factor-auth-flow';

    /**
     * @uses \Spryker\Zed\SecurityMerchantPortalGui\Communication\Expander\SecurityBuilderExpander::ACCESS_MODE_PRE_AUTH
     */
    protected const string ACCESS_MODE_PRE_AUTH = 'ACCESS_MODE_PRE_AUTH';

    /**
     * @uses \Spryker\Zed\Session\Communication\Plugin\Application\SessionApplicationPlugin::SERVICE_SESSION
     */
    protected const string SERVICE_SESSION = 'session';

    protected const string SERVICE_SECURITY_TOKEN_STORAGE = 'security.token_storage';

    /**
     * @uses \Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Authenticator\OauthMerchantPortalTokenAuthenticator::ROUTE_NAME_OAUTH_MERCHANT_PORTAL_LOGIN
     */
    protected const string ROUTE_NAME_OAUTH_LOGIN = 'security-merchant-portal-gui:oauth-login';

    protected const string ROUTE_PATH_OAUTH_LOGIN = '/security-merchant-portal-gui/oauth-login';

    protected const string SOME_EMAIL = 'merchant-user@example.com';

    protected const string SOME_CODE = 'SOME_OAUTH_CODE';

    /**
     * @uses \Spryker\Shared\MultiFactorAuth\MultiFactorAuthConstants::CODE_BLOCKED
     */
    protected const int CODE_BLOCKED = 1;

    protected SecurityMerchantPortalGuiCommunicationTester $tester;

    protected function _before(): void
    {
        parent::_before();

        if ($this->tester->isSymfonyVersion5() === true) {
            $this->markTestSkipped('Compatible only with `symfony/security-core` package version >= 6. Will be enabled by default once Symfony 5 support is discontinued.');
        }

        $this->tester->mockSecurityDependencies();
    }

    public function testExtendRegistersOauthAuthenticatorWhenMerchantUserFirewallExists(): void
    {
        // Arrange
        $basePlugin = new ZedMerchantUserSecurityPlugin();
        $basePlugin->setFactory($this->tester->getFactory());
        $this->tester->addSecurityPlugin($basePlugin);

        $oauthPlugin = new ZedOauthMerchantPortalSecurityPlugin();
        $oauthPlugin->setFactory($this->tester->getFactory());
        $this->tester->addSecurityPlugin($oauthPlugin);

        // Act
        $this->tester->enableSecurityApplicationPlugin();
        $this->tester->getContainer()->get('security.access_map');

        // Assert
        $this->assertTrue(
            $this->tester->getContainer()->has(static::SECURITY_OAUTH_MERCHANT_PORTAL_TOKEN_AUTHENTICATOR),
            'Expected the OAuth merchant portal token authenticator to be registered after extend.',
        );
    }

    public function testExtendIsNoOpWhenMerchantUserFirewallDoesNotExist(): void
    {
        // Arrange
        $oauthPlugin = new ZedOauthMerchantPortalSecurityPlugin();
        $oauthPlugin->setFactory($this->tester->getFactory());
        $this->tester->addSecurityPlugin($oauthPlugin);

        // Act
        $this->tester->enableSecurityApplicationPlugin();
        $this->tester->getContainer()->get('security.access_map');

        // Assert
        $this->assertFalse(
            $this->tester->getContainer()->has(static::SECURITY_OAUTH_MERCHANT_PORTAL_TOKEN_AUTHENTICATOR),
            'Expected the OAuth authenticator to be absent when MerchantUser firewall does not exist.',
        );
    }

    public function testExtendCoversOauthMultiFactorAuthFlowRouteWithPreAuthAccess(): void
    {
        // Arrange
        $oauthPlugin = new ZedOauthMerchantPortalSecurityPlugin();
        $oauthPlugin->setFactory($this->tester->getFactory());

        $securityBuilder = (new SecurityConfiguration())
            ->addFirewall(static::SECURITY_MERCHANT_USER_FIREWALL_NAME, [
                'pattern' => '^/(.+)-merchant-portal-gui/',
                'users' => static fn () => null,
            ]);

        // Act
        $securityBuilder = $oauthPlugin->extend($securityBuilder, $this->tester->getContainer());

        // Assert
        $configuration = $securityBuilder->getConfiguration();
        $firewalls = $configuration->getFirewalls();

        $this->assertStringContainsString(
            static::OAUTH_MULTI_FACTOR_AUTH_PATH_PATTERN,
            $firewalls[static::SECURITY_MERCHANT_USER_FIREWALL_NAME]['pattern'],
            'Expected the MerchantUser firewall pattern to cover the OAuth Multi-Factor Authentication flow route.',
        );

        $hasPreAuthRule = false;

        foreach ($configuration->getAccessRules() as $accessRule) {
            if (($accessRule[0] ?? null) === static::OAUTH_MULTI_FACTOR_AUTH_PATH_PATTERN && ($accessRule[1] ?? null) === static::ACCESS_MODE_PRE_AUTH) {
                $hasPreAuthRule = true;
            }
        }

        $this->assertTrue(
            $hasPreAuthRule,
            'Expected a pre-auth access rule for the OAuth Multi-Factor Authentication flow route.',
        );
    }

    public function testOauthMerchantUserCanLoginWhenMultiFactorAuthNotRequired(): void
    {
        // Arrange
        $this->haveExistingMerchantUser();
        $this->bootOauthSecurity(
            [$this->createOauthClientStrategyPluginMock(true, static::SOME_EMAIL)],
            [],
        );
        $container = $this->tester->getContainer();
        $container->get(static::SERVICE_SESSION)->start();

        // Act
        $this->tester->getHttpKernelBrowser()->request(
            'get',
            static::ROUTE_PATH_OAUTH_LOGIN,
            ['code' => static::SOME_CODE, 'state' => static::SOME_EMAIL],
        );

        // Assert
        $token = $container->get(static::SERVICE_SECURITY_TOKEN_STORAGE)->getToken();
        $this->assertNotNull($token, 'Expected the merchant user to be authenticated via OAuth.');
        $this->assertNotContains(
            static::ACCESS_MODE_PRE_AUTH,
            $token->getRoleNames(),
            'Expected a full (non pre-auth) token when Multi-Factor Authentication is not required.',
        );
    }

    public function testOauthMerchantUserGetsPreAuthTokenWhenMultiFactorAuthRequired(): void
    {
        // Arrange
        $this->haveExistingMerchantUser();
        $this->bootOauthSecurity(
            [$this->createOauthClientStrategyPluginMock(true, static::SOME_EMAIL)],
            [$this->createMultiFactorAuthHandlerPluginMock(true)],
        );
        $container = $this->tester->getContainer();
        $container->get(static::SERVICE_SESSION)->start();

        // Act
        $this->tester->getHttpKernelBrowser()->request(
            'get',
            static::ROUTE_PATH_OAUTH_LOGIN,
            ['code' => static::SOME_CODE, 'state' => static::SOME_EMAIL],
        );

        // Assert
        $token = $container->get(static::SERVICE_SECURITY_TOKEN_STORAGE)->getToken();
        $this->assertNotNull($token, 'Expected a pre-auth token when Multi-Factor Authentication is required.');
        $this->assertContains(
            static::ACCESS_MODE_PRE_AUTH,
            $token->getRoleNames(),
            'Expected a pre-auth token (ACCESS_MODE_PRE_AUTH) when Multi-Factor Authentication is required.',
        );
    }

    public function testOauthMerchantUserGetsPreAuthTokenWhenMultiFactorAuthCodeIsBlocked(): void
    {
        // Arrange
        $this->haveExistingMerchantUser();
        $this->bootOauthSecurity(
            [$this->createOauthClientStrategyPluginMock(true, static::SOME_EMAIL)],
            [$this->createMultiFactorAuthHandlerPluginMock(false, static::CODE_BLOCKED)],
        );
        $container = $this->tester->getContainer();
        $container->get(static::SERVICE_SESSION)->start();

        // Act
        $this->tester->getHttpKernelBrowser()->request(
            'get',
            static::ROUTE_PATH_OAUTH_LOGIN,
            ['code' => static::SOME_CODE, 'state' => static::SOME_EMAIL],
        );

        // Assert
        $token = $container->get(static::SERVICE_SECURITY_TOKEN_STORAGE)->getToken();
        $this->assertNotNull($token, 'Expected a pre-auth token when the Multi-Factor Authentication code is blocked.');
        $this->assertContains(
            static::ACCESS_MODE_PRE_AUTH,
            $token->getRoleNames(),
            'Expected a pre-auth token (ACCESS_MODE_PRE_AUTH) when the Multi-Factor Authentication code is blocked.',
        );
    }

    public function testOauthMerchantUserCannotLoginWhenResourceOwnerNotResolved(): void
    {
        // Arrange
        $this->bootOauthSecurity(
            [$this->createOauthClientStrategyPluginMock(false)],
            [],
        );
        $container = $this->tester->getContainer();
        $container->get(static::SERVICE_SESSION)->start();

        // Act
        $this->tester->getHttpKernelBrowser()->request(
            'get',
            static::ROUTE_PATH_OAUTH_LOGIN,
            ['code' => static::SOME_CODE, 'state' => static::SOME_EMAIL],
        );

        // Assert
        $this->assertNull(
            $container->get(static::SERVICE_SECURITY_TOKEN_STORAGE)->getToken(),
            'Expected no authentication when the OAuth resource owner cannot be resolved.',
        );
    }

    public function testOauthMerchantUserCannotLoginWhenNoMerchantUserMatchesResourceOwner(): void
    {
        // Arrange
        $this->bootOauthSecurity(
            [$this->createOauthClientStrategyPluginMock(true, static::SOME_EMAIL)],
            [],
        );
        $container = $this->tester->getContainer();
        $container->get(static::SERVICE_SESSION)->start();

        // Act
        $this->tester->getHttpKernelBrowser()->request(
            'get',
            static::ROUTE_PATH_OAUTH_LOGIN,
            ['code' => static::SOME_CODE, 'state' => static::SOME_EMAIL],
        );

        // Assert
        $this->assertNull(
            $container->get(static::SERVICE_SECURITY_TOKEN_STORAGE)->getToken(),
            'Expected no authentication when no merchant user matches the resource owner (e.g. a customer identity).',
        );
    }

    /**
     * @param array<\Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\OauthMerchantUserClientStrategyPluginInterface> $oauthClientStrategyPlugins
     * @param array<\Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\AuthenticationHandlerPluginInterface> $multiFactorAuthHandlerPlugins
     */
    protected function bootOauthSecurity(array $oauthClientStrategyPlugins, array $multiFactorAuthHandlerPlugins): void
    {
        $this->tester->addRoute(static::ROUTE_NAME_OAUTH_LOGIN, static::ROUTE_PATH_OAUTH_LOGIN, function (): void {
            // Only exists so the router can match the OAuth callback route during the test.
        });

        $this->tester->setDependency(
            SecurityMerchantPortalGuiDependencyProvider::PLUGINS_OAUTH_MERCHANT_USER_CLIENT_STRATEGY,
            $oauthClientStrategyPlugins,
        );
        $this->tester->setDependency(
            SecurityMerchantPortalGuiDependencyProvider::PLUGINS_OAUTH_MERCHANT_USER_AUTHENTICATION_STRATEGY,
            [new ExistingMerchantUserAuthenticationStrategyPlugin()],
        );
        $this->tester->setDependency(
            SecurityMerchantPortalGuiDependencyProvider::PLUGINS_MERCHANT_USER_AUTHENTICATION_HANDLER,
            $multiFactorAuthHandlerPlugins,
        );

        $basePlugin = new ZedMerchantUserSecurityPlugin();
        $basePlugin->setFactory($this->tester->getFactory());
        $this->tester->addSecurityPlugin($basePlugin);

        $oauthPlugin = new ZedOauthMerchantPortalSecurityPlugin();
        $oauthPlugin->setFactory($this->tester->getFactory());
        $this->tester->addSecurityPlugin($oauthPlugin);

        $this->tester->enableSecurityApplicationPlugin();
    }

    protected function haveExistingMerchantUser(): MerchantUserTransfer
    {
        $merchantTransfer = $this->tester->haveMerchant();
        $userTransfer = $this->tester->haveUser([UserTransfer::USERNAME => static::SOME_EMAIL]);

        return $this->tester->haveMerchantUserWithAclEntities($merchantTransfer, $userTransfer);
    }

    protected function createOauthClientStrategyPluginMock(bool $isSuccessful, ?string $email = null): OauthMerchantUserClientStrategyPluginInterface
    {
        $pluginMock = $this->getMockBuilder(OauthMerchantUserClientStrategyPluginInterface::class)->getMock();
        $pluginMock->method('isApplicable')->willReturn(true);

        $resourceOwnerResponseTransfer = (new ResourceOwnerResponseTransfer())->setIsSuccessful($isSuccessful);

        if ($isSuccessful) {
            $resourceOwnerResponseTransfer->setResourceOwner((new ResourceOwnerTransfer())->setEmail($email));
        }

        $pluginMock->method('getResourceOwner')->willReturn($resourceOwnerResponseTransfer);

        return $pluginMock;
    }

    protected function createMultiFactorAuthHandlerPluginMock(
        bool $isRequired,
        ?int $status = null
    ): AuthenticationHandlerPluginInterface {
        $pluginMock = $this->getMockBuilder(AuthenticationHandlerPluginInterface::class)->getMock();
        $pluginMock->method('isApplicable')->willReturn(true);
        $pluginMock->method('validateMerchantUserMultiFactorStatus')->willReturn(
            (new MultiFactorAuthValidationResponseTransfer())
                ->setIsRequired($isRequired)
                ->setStatus($status),
        );

        return $pluginMock;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $reflection = new ReflectionClass(SecurityConfigurator::class);
        $property = $reflection->getProperty('securityConfiguration');
        $property->setValue(null);
    }
}
