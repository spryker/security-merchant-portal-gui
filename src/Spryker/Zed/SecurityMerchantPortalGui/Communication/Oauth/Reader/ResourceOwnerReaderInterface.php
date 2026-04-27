<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Reader;

use Generated\Shared\Transfer\ResourceOwnerTransfer;
use Symfony\Component\HttpFoundation\Request;

interface ResourceOwnerReaderInterface
{
    public function getResourceOwner(Request $request): ?ResourceOwnerTransfer;
}
