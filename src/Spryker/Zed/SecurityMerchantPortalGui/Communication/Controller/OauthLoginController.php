<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Controller;

use LogicException;
use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\SecurityMerchantPortalGui\Communication\SecurityMerchantPortalGuiCommunicationFactory getFactory()
 */
class OauthLoginController extends AbstractController
{
    public function indexAction(Request $request): void
    {
        throw new LogicException(
            'This action must never execute — it is intercepted by OauthMerchantPortalTokenAuthenticator before reaching the controller. '
            . 'If you see this exception, the OAuth firewall is not configured correctly. Check that: '
            . '(1) ZedOauthMerchantPortalSecurityPlugin is registered in SecurityDependencyProvider, '
            . '(2) it is registered AFTER ZedMerchantUserSecurityPlugin so the MerchantUser firewall exists before being expanded, '
            . '(3) the MerchantUser firewall pattern covers "^/security-merchant-portal-gui".',
        );
    }
}
