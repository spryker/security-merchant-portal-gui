<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Security;

use Generated\Shared\Transfer\MerchantUserTransfer;

class SecurityOauthMerchantUser implements SecurityOauthMerchantUserInterface
{
    protected string $username;

    /**
     * @param array<string> $roles
     */
    public function __construct(
        protected MerchantUserTransfer $merchantUserTransfer,
        protected array $roles = [],
    ) {
        $this->username = $merchantUserTransfer->getUserOrFail()->getUsernameOrFail();
    }

    public function getMerchantUserTransfer(): MerchantUserTransfer
    {
        return $this->merchantUserTransfer;
    }

    /**
     * @return array<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function eraseCredentials(): void
    {
    }
}
