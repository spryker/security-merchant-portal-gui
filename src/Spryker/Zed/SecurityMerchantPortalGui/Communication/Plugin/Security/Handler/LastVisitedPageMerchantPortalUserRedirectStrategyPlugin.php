<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security\Handler;

use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\MerchantPortalUserRedirectStrategyPluginInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\SecurityMerchantPortalGui\Communication\SecurityMerchantPortalGuiCommunicationFactory getFactory()
 * @method \Spryker\Zed\SecurityMerchantPortalGui\SecurityMerchantPortalGuiConfig getConfig()
 */
class LastVisitedPageMerchantPortalUserRedirectStrategyPlugin extends AbstractPlugin implements MerchantPortalUserRedirectStrategyPluginInterface
{
    /**
     * {@inheritDoc}
     * - Returns `true` if a valid last visited page URL is found in the last visited page storage.
     * - The storage strategy is configurable and defaults to cookie-based storage.
     *
     * @api
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return bool
     */
    public function isApplicable(Request $request): bool
    {
        return $this->getFactory()->createLastVisitedPageRedirectResolver()->hasRedirectUrl($request);
    }

    /**
     * {@inheritDoc}
     * - Returns the last visited Merchant Portal page URL from the last visited page storage.
     * - The storage strategy is configurable and defaults to cookie-based storage.
     *
     * @api
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return string
     */
    public function getRedirectUrl(Request $request): string
    {
        return $this->getFactory()->createLastVisitedPageRedirectResolver()->getRedirectUrl($request);
    }
}
