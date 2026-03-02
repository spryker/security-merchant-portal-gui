<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Logger;

interface AuditLoggerInterface
{
    public function addFailedLoginAuditLog(): void;

    public function addSuccessfulLoginAuditLog(): void;

    public function addPasswordResetRequestedAuditLog(): void;

    public function addPasswordUpdatedAfterResetAuditLog(): void;
}
