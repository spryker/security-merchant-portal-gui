<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Expander;

use Spryker\Service\Container\ContainerInterface;
use Spryker\Shared\SecurityExtension\Configuration\SecurityBuilderInterface;
use Symfony\Component\Security\Core\User\ChainUserProvider;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;

class OauthSecurityBuilderExpander implements OauthSecurityBuilderExpanderInterface
{
    /**
     * @uses \Spryker\Zed\SecurityMerchantPortalGui\Communication\Expander\SecurityBuilderExpander::SECURITY_FIREWALL_NAME
     */
    protected const string SECURITY_MERCHANT_USER_FIREWALL_NAME = 'MerchantUser';

    /**
     * @uses \Spryker\Zed\SecurityMerchantPortalGui\Communication\Expander\SecurityBuilderExpander::SECURITY_MERCHANT_PORTAL_LOGIN_FORM_AUTHENTICATOR
     */
    protected const string SECURITY_MERCHANT_PORTAL_LOGIN_FORM_AUTHENTICATOR = 'security.MerchantUser.login_form.authenticator';

    protected const string SECURITY_OAUTH_MERCHANT_PORTAL_TOKEN_AUTHENTICATOR = 'security.OauthMerchantPortal.token.authenticator';

    protected const string USERS = 'users';

    // The OAuth callback route is outside the standard *-merchant-portal-gui/ pattern,
    // so we must extend the firewall's pattern to cover it — otherwise the User firewall ('^/')
    // intercepts and redirects to the back-office login page.
    // The initiate route (/security-oauth-knpu/oauth-merchant-user-initiate) does not match
    // any MP access rule, so it is accessible without explicit PUBLIC_ACCESS configuration.
    protected const string OAUTH_MERCHANT_PORTAL_PATH_PATTERN = '^/security-merchant-portal-gui';

    protected const string ACCESS_MODE_PUBLIC = 'PUBLIC_ACCESS';

    public function __construct(
        protected UserProviderInterface $userProvider,
        protected AuthenticatorInterface $authenticator,
    ) {
    }

    public function extend(SecurityBuilderInterface $securityBuilder, ContainerInterface $container): SecurityBuilderInterface
    {
        if ($this->findFirewall(static::SECURITY_MERCHANT_USER_FIREWALL_NAME, $securityBuilder) === null) {
            return $securityBuilder;
        }

        $securityBuilder = $this->expandMerchantUserFirewall($securityBuilder);
        $securityBuilder = $this->addAccessRules($securityBuilder);
        $this->addAuthenticator($container);

        return $securityBuilder;
    }

    protected function expandMerchantUserFirewall(SecurityBuilderInterface $securityBuilder): SecurityBuilderInterface
    {
        $merchantUserFirewallConfiguration = $this->findFirewall(static::SECURITY_MERCHANT_USER_FIREWALL_NAME, $securityBuilder);

        if ($merchantUserFirewallConfiguration === null) {
            return $securityBuilder;
        }

        $updatedConfiguration = [
            'form' => array_merge(
                $merchantUserFirewallConfiguration['form'] ?? [],
                [
                    'authenticators' => [
                        static::SECURITY_OAUTH_MERCHANT_PORTAL_TOKEN_AUTHENTICATOR,
                        static::SECURITY_MERCHANT_PORTAL_LOGIN_FORM_AUTHENTICATOR,
                    ],
                ],
            ),
            static::USERS => function () use ($merchantUserFirewallConfiguration) {
                return new ChainUserProvider([
                    $merchantUserFirewallConfiguration[static::USERS](),
                    $this->userProvider,
                ]);
            },
        ] + $merchantUserFirewallConfiguration;

        $updatedConfiguration['pattern'] = $this->buildExtendedFirewallPattern(
            $merchantUserFirewallConfiguration['pattern'] ?? '',
        );

        $securityBuilder->addFirewall(static::SECURITY_MERCHANT_USER_FIREWALL_NAME, $updatedConfiguration);

        // When the MerchantUser firewall shares its context with another firewall (e.g. AgentMerchantUser),
        // the ContextListener is created once for that context using the first-processed firewall's user
        // providers. We must also extend that firewall's user providers so the shared ContextListener
        // can refresh SecurityOauthMerchantUser tokens on any request matched by the MerchantUser firewall.
        $sharedContext = $merchantUserFirewallConfiguration['context'] ?? null;

        if ($sharedContext !== null && $sharedContext !== static::SECURITY_MERCHANT_USER_FIREWALL_NAME) {
            $securityBuilder = $this->expandSharedContextFirewallUserProvider($sharedContext, $securityBuilder);
        }

        return $securityBuilder;
    }

    protected function expandSharedContextFirewallUserProvider(
        string $contextFirewallName,
        SecurityBuilderInterface $securityBuilder,
    ): SecurityBuilderInterface {
        $contextFirewallConfiguration = $this->findFirewall($contextFirewallName, $securityBuilder);

        if ($contextFirewallConfiguration === null) {
            return $securityBuilder;
        }

        $updatedConfig = [
            static::USERS => function () use ($contextFirewallConfiguration) {
                return new ChainUserProvider([
                    $contextFirewallConfiguration[static::USERS](),
                    $this->userProvider,
                ]);
            },
        ] + $contextFirewallConfiguration;

        $securityBuilder->addFirewall($contextFirewallName, $updatedConfig);

        return $securityBuilder;
    }

    protected function buildExtendedFirewallPattern(string $existingPattern): string
    {
        return sprintf('%s|%s', $existingPattern, static::OAUTH_MERCHANT_PORTAL_PATH_PATTERN);
    }

    protected function addAccessRules(SecurityBuilderInterface $securityBuilder): SecurityBuilderInterface
    {
        return $securityBuilder->addAccessRules([
            [
                static::OAUTH_MERCHANT_PORTAL_PATH_PATTERN,
                static::ACCESS_MODE_PUBLIC,
            ],
        ]);
    }

    protected function addAuthenticator(ContainerInterface $container): void
    {
        $container->set(static::SECURITY_OAUTH_MERCHANT_PORTAL_TOKEN_AUTHENTICATOR, function () {
            return $this->authenticator;
        });
    }

    /**
     * @param string $firewallName
     * @param \Spryker\Shared\SecurityExtension\Configuration\SecurityBuilderInterface $securityBuilder
     *
     * @return array<mixed>|null
     */
    protected function findFirewall(string $firewallName, SecurityBuilderInterface $securityBuilder): ?array
    {
        $firewalls = (clone $securityBuilder)->getConfiguration()->getFirewalls();

        return $firewalls[$firewallName] ?? null;
    }
}
