<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityMerchantPortalGui\Dependency\Facade;

use Generated\Shared\Transfer\MessageTransfer;

interface SecurityMerchantPortalGuiToMessengerFacadeInterface
{
    public function addErrorMessage(MessageTransfer $message): void;
}
