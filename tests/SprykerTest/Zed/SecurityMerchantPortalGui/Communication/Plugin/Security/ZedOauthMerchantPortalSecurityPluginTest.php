<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security;

use Codeception\Test\Unit;
use ReflectionClass;
use Spryker\Zed\Security\Communication\Configurator\SecurityConfigurator;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security\ZedMerchantUserSecurityPlugin;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security\ZedOauthMerchantPortalSecurityPlugin;
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

    protected function tearDown(): void
    {
        parent::tearDown();

        $reflection = new ReflectionClass(SecurityConfigurator::class);
        $property = $reflection->getProperty('securityConfiguration');
        $property->setAccessible(true);
        $property->setValue(null);
    }
}
