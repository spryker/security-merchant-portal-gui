<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Resolver;

use Generated\Shared\Transfer\MerchantUserTransfer;
use Generated\Shared\Transfer\ResourceOwnerTransfer;

class OauthMerchantUserResolver implements OauthMerchantUserResolverInterface
{
    /**
     * @param array<\Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\OauthMerchantUserAuthenticationStrategyPluginInterface> $oauthMerchantUserAuthenticationStrategyPlugins
     * @param array<\Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\OauthMerchantUserPostResolvePluginInterface> $oauthMerchantUserPostResolvePlugins
     */
    public function __construct(
        protected array $oauthMerchantUserAuthenticationStrategyPlugins,
        protected array $oauthMerchantUserPostResolvePlugins,
    ) {
    }

    public function resolveOauthMerchantUserByResourceOwner(ResourceOwnerTransfer $resourceOwnerTransfer): ?MerchantUserTransfer
    {
        $merchantUserTransfer = $this->doResolve($resourceOwnerTransfer);

        if ($merchantUserTransfer !== null) {
            $this->runPostResolvePlugins($merchantUserTransfer, $resourceOwnerTransfer);
        }

        return $merchantUserTransfer;
    }

    protected function doResolve(ResourceOwnerTransfer $resourceOwnerTransfer): ?MerchantUserTransfer
    {
        foreach ($this->oauthMerchantUserAuthenticationStrategyPlugins as $plugin) {
            if (!$plugin->isApplicable($resourceOwnerTransfer)) {
                continue;
            }

            $merchantUserTransfer = $plugin->resolveOauthMerchantUser($resourceOwnerTransfer);

            if ($merchantUserTransfer !== null) {
                return $merchantUserTransfer;
            }
        }

        return null;
    }

    protected function runPostResolvePlugins(MerchantUserTransfer $merchantUserTransfer, ResourceOwnerTransfer $resourceOwnerTransfer): void
    {
        foreach ($this->oauthMerchantUserPostResolvePlugins as $oauthMerchantUserPostResolvePlugin) {
            $oauthMerchantUserPostResolvePlugin->postResolve($merchantUserTransfer, $resourceOwnerTransfer);
        }
    }
}
