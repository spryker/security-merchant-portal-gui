<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Reader;

use Generated\Shared\Transfer\ResourceOwnerRequestTransfer;
use Generated\Shared\Transfer\ResourceOwnerResponseTransfer;
use Generated\Shared\Transfer\ResourceOwnerTransfer;
use Symfony\Component\HttpFoundation\Request;

class ResourceOwnerReader implements ResourceOwnerReaderInterface
{
    protected const string REQUEST_PARAMETER_AUTHENTICATION_CODE = 'code';

    protected const string REQUEST_PARAMETER_AUTHENTICATION_STATE = 'state';

    /**
     * @param array<\Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\OauthMerchantUserClientStrategyPluginInterface> $oauthMerchantUserClientStrategyPlugins
     */
    public function __construct(
        protected array $oauthMerchantUserClientStrategyPlugins,
    ) {
    }

    public function getResourceOwner(Request $request): ?ResourceOwnerTransfer
    {
        $code = $request->query->get(static::REQUEST_PARAMETER_AUTHENTICATION_CODE);
        $state = $request->query->get(static::REQUEST_PARAMETER_AUTHENTICATION_STATE);

        if (!$code || !$state) {
            return null;
        }

        $resourceOwnerResponseTransfer = $this->executeResourceOwnerPlugins(
            (new ResourceOwnerRequestTransfer())->fromArray($request->query->all(), true),
        );

        if (!$resourceOwnerResponseTransfer->getIsSuccessful()) {
            return null;
        }

        return $resourceOwnerResponseTransfer->getResourceOwner();
    }

    public function executeResourceOwnerPlugins(ResourceOwnerRequestTransfer $resourceOwnerRequestTransfer): ResourceOwnerResponseTransfer
    {
        foreach ($this->oauthMerchantUserClientStrategyPlugins as $oauthMerchantUserClientStrategyPlugin) {
            if (!$oauthMerchantUserClientStrategyPlugin->isApplicable($resourceOwnerRequestTransfer)) {
                continue;
            }

            return $oauthMerchantUserClientStrategyPlugin->getResourceOwner($resourceOwnerRequestTransfer);
        }

        return (new ResourceOwnerResponseTransfer())->setIsSuccessful(false);
    }
}
