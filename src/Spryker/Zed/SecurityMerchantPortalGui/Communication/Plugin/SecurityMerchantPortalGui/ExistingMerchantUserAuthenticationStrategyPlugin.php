<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\SecurityMerchantPortalGui;

use Generated\Shared\Transfer\MerchantUserCriteriaTransfer;
use Generated\Shared\Transfer\MerchantUserTransfer;
use Generated\Shared\Transfer\ResourceOwnerTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\OauthMerchantUserAuthenticationStrategyPluginInterface;

/**
 * Resolves an existing merchant user by the email address provided by the OAuth resource owner.
 * Register this plugin last in the authentication strategy stack as a catch-all fallback.
 * Only pre-existing merchant users can be resolved — no automatic account creation is performed.
 *
 * @method \Spryker\Zed\SecurityMerchantPortalGui\Communication\SecurityMerchantPortalGuiCommunicationFactory getFactory()
 * @method \Spryker\Zed\SecurityMerchantPortalGui\SecurityMerchantPortalGuiConfig getConfig()
 */
class ExistingMerchantUserAuthenticationStrategyPlugin extends AbstractPlugin implements OauthMerchantUserAuthenticationStrategyPluginInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function isApplicable(ResourceOwnerTransfer $resourceOwnerTransfer): bool
    {
        return $resourceOwnerTransfer->getEmail() !== null;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function resolveOauthMerchantUser(ResourceOwnerTransfer $resourceOwnerTransfer): ?MerchantUserTransfer
    {
        return $this->getFactory()->getMerchantUserFacade()->findMerchantUser(
            (new MerchantUserCriteriaTransfer())
                ->setUsername($resourceOwnerTransfer->getEmailOrFail())
                ->setWithUser(true),
        );
    }
}
