<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security;

use Spryker\Service\Container\ContainerInterface;
use Spryker\Shared\SecurityExtension\Configuration\SecurityBuilderInterface;
use Spryker\Shared\SecurityExtension\Dependency\Plugin\SecurityPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * This plugin must be registered after {@link \Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security\ZedMerchantUserSecurityPlugin}
 * so that the MerchantUser firewall exists before it is expanded.
 *
 * @method \Spryker\Zed\SecurityMerchantPortalGui\Communication\SecurityMerchantPortalGuiCommunicationFactory getFactory()
 * @method \Spryker\Zed\SecurityMerchantPortalGui\SecurityMerchantPortalGuiConfig getConfig()
 */
class ZedOauthMerchantPortalSecurityPlugin extends AbstractPlugin implements SecurityPluginInterface
{
    /**
     * {@inheritDoc}
     * - Expands the MerchantUser firewall with the OAuth merchant user token authenticator.
     * - Uses a ChainUserProvider combining the existing merchant user provider and the OAuth merchant user provider.
     *
     * @api
     *
     * @param \Spryker\Shared\SecurityExtension\Configuration\SecurityBuilderInterface $securityBuilder
     * @param \Spryker\Service\Container\ContainerInterface $container
     *
     * @return \Spryker\Shared\SecurityExtension\Configuration\SecurityBuilderInterface
     */
    public function extend(SecurityBuilderInterface $securityBuilder, ContainerInterface $container): SecurityBuilderInterface
    {
        return $this->getFactory()->createOauthSecurityBuilderExpander()->extend($securityBuilder, $container);
    }
}
