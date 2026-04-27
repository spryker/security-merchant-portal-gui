<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Expander;

use Spryker\Service\Container\ContainerInterface;
use Spryker\Shared\SecurityExtension\Configuration\SecurityBuilderInterface;

interface OauthSecurityBuilderExpanderInterface
{
    public function extend(SecurityBuilderInterface $securityBuilder, ContainerInterface $container): SecurityBuilderInterface;
}
