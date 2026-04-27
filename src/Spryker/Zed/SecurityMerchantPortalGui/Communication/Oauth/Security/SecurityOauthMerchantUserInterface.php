<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Security;

use Generated\Shared\Transfer\MerchantUserTransfer;
use Symfony\Component\Security\Core\User\UserInterface;

interface SecurityOauthMerchantUserInterface extends UserInterface
{
    public function getMerchantUserTransfer(): MerchantUserTransfer;
}
