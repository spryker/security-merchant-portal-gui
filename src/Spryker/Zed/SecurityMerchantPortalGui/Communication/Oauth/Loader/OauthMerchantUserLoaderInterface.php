<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Loader;

use Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Security\SecurityOauthMerchantUser;

interface OauthMerchantUserLoaderInterface
{
    public function loadUserByIdentifier(string $identifier): SecurityOauthMerchantUser;
}
