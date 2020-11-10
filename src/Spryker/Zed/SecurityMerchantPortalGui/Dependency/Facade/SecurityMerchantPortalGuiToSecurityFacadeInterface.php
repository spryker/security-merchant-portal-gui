<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityMerchantPortalGui\Dependency\Facade;

interface SecurityMerchantPortalGuiToSecurityFacadeInterface
{
    /**
     * @return bool
     */
    public function isUserLoggedIn(): bool;
}
