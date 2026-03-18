<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\SecurityMerchantPortalGui;

/**
 * Declares global environment configuration keys. Do not use it for other class constants.
 */
interface SecurityMerchantPortalGuiConstants
{
    /**
     * Specification:
     * - Defines whether the last visited page cookie should be sent over HTTPS only.
     *
     * @api
     *
     * @var string
     */
    public const string ZED_IS_SSL_ENABLED = 'SECURITY_MERCHANT_PORTAL_GUI:ZED_IS_SSL_ENABLED';
}
