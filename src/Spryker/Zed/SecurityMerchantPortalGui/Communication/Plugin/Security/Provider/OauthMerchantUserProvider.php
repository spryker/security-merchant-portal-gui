<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security\Provider;

use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Security\SecurityOauthMerchantUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @method \Spryker\Zed\SecurityMerchantPortalGui\Communication\SecurityMerchantPortalGuiCommunicationFactory getFactory()
 * @method \Spryker\Zed\SecurityMerchantPortalGui\SecurityMerchantPortalGuiConfig getConfig()
 */
class OauthMerchantUserProvider extends AbstractPlugin implements UserProviderInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->getFactory()
            ->createOauthMerchantUserLoader()
            ->loadUserByIdentifier($identifier);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof SecurityOauthMerchantUser) {
            return $user;
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function supportsClass(string $class): bool
    {
        return is_a($class, SecurityOauthMerchantUser::class, true);
    }
}
