<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityMerchantPortalGui\Dependency\Facade;

use Generated\Shared\Transfer\MerchantUserCriteriaTransfer;
use Generated\Shared\Transfer\MerchantUserTransfer;
use Generated\Shared\Transfer\UserPasswordResetRequestTransfer;

interface SecurityMerchantPortalGuiToMerchantUserFacadeInterface
{
    public function authorizeMerchantUser(MerchantUserTransfer $merchantUserTransfer): void;

    public function findMerchantUser(MerchantUserCriteriaTransfer $merchantUserCriteriaTransfer): ?MerchantUserTransfer;

    public function requestPasswordReset(UserPasswordResetRequestTransfer $userPasswordResetRequestTransfer): bool;

    public function isValidPasswordResetToken(string $token): bool;

    public function setNewPassword(string $token, string $password): bool;
}
