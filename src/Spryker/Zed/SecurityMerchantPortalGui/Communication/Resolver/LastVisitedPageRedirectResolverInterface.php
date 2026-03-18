<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Resolver;

use Symfony\Component\HttpFoundation\Request;

interface LastVisitedPageRedirectResolverInterface
{
    public function hasRedirectUrl(Request $request): bool;

    public function getRedirectUrl(Request $request): string;
}
