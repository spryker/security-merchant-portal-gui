<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Checker;

use Spryker\Service\Http\HttpServiceInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Dependency\Facade\SecurityMerchantPortalGuiToSecurityFacadeInterface;
use Symfony\Component\HttpFoundation\Request;

class LastVisitedPageUrlChecker implements LastVisitedPageUrlCheckerInterface
{
    public function __construct(
        protected SecurityMerchantPortalGuiToSecurityFacadeInterface $securityFacade,
        protected HttpServiceInterface $httpService,
    ) {
    }

    public function isEligibleForPostLoginRedirect(Request $request): bool
    {
        if (!$this->securityFacade->isUserLoggedIn()) {
            return false;
        }

        return $this->httpService->isRequestEligibleForRedirect($request);
    }
}
