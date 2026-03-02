<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityMerchantPortalGui\Dependency\Facade;

use Generated\Shared\Transfer\MerchantUserCriteriaTransfer;
use Generated\Shared\Transfer\MerchantUserTransfer;
use Generated\Shared\Transfer\UserPasswordResetRequestTransfer;

class SecurityMerchantPortalGuiToMerchantUserFacadeBridge implements SecurityMerchantPortalGuiToMerchantUserFacadeInterface
{
    /**
     * @var \Spryker\Zed\MerchantUser\Business\MerchantUserFacadeInterface
     */
    protected $merchantUserFacade;

    /**
     * @param \Spryker\Zed\MerchantUser\Business\MerchantUserFacadeInterface $merchantUserFacade
     */
    public function __construct($merchantUserFacade)
    {
        $this->merchantUserFacade = $merchantUserFacade;
    }

    public function authorizeMerchantUser(MerchantUserTransfer $merchantUserTransfer): void
    {
        $this->merchantUserFacade->authenticateMerchantUser($merchantUserTransfer);
    }

    public function findMerchantUser(MerchantUserCriteriaTransfer $merchantUserCriteriaTransfer): ?MerchantUserTransfer
    {
        return $this->merchantUserFacade->findMerchantUser($merchantUserCriteriaTransfer);
    }

    public function requestPasswordReset(UserPasswordResetRequestTransfer $userPasswordResetRequestTransfer): bool
    {
        return $this->merchantUserFacade->requestPasswordReset($userPasswordResetRequestTransfer);
    }

    public function isValidPasswordResetToken(string $token): bool
    {
        return $this->merchantUserFacade->isValidPasswordResetToken($token);
    }

    public function setNewPassword(string $token, string $password): bool
    {
        return $this->merchantUserFacade->setNewPassword($token, $password);
    }
}
