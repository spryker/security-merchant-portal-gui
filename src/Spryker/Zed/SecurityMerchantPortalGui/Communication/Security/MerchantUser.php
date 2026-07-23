<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Security;

use Generated\Shared\Transfer\MerchantUserTransfer;
use Generated\Shared\Transfer\UserTransfer;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

class MerchantUser implements MerchantUserInterface, PasswordAuthenticatedUserInterface, EquatableInterface
{
    protected const string SERIALIZATION_KEY_MERCHANT_USER_TRANSFER = 'merchantUserTransfer';

    protected const string SERIALIZATION_KEY_USERNAME = 'username';

    protected const string SERIALIZATION_KEY_PASSWORD = 'password';

    protected const string SERIALIZATION_KEY_ROLES = 'roles';

    protected const string SERIALIZATION_KEY_STATE_HASH = 'stateHash';

    /**
     * Sessions written before `__serialize()` was introduced used default object serialization,
     * where protected property names are prefixed with "\0*\0".
     */
    protected const string LEGACY_PROTECTED_PROPERTY_PREFIX = "\0*\0";

    /**
     * @var \Generated\Shared\Transfer\MerchantUserTransfer
     */
    protected MerchantUserTransfer $merchantUserTransfer;

    /**
     * @var string
     */
    protected string $username;

    /**
     * @var string|null
     */
    protected ?string $password;

    /**
     * @var array<string>
     */
    protected array $roles = [];

    protected ?string $stateHash = null;

    /**
     * @param \Generated\Shared\Transfer\MerchantUserTransfer $merchantUserTransfer
     * @param array<string> $roles
     */
    public function __construct(MerchantUserTransfer $merchantUserTransfer, array $roles = [])
    {
        $this->merchantUserTransfer = $merchantUserTransfer;

        $userTransfer = $merchantUserTransfer->getUserOrFail();
        $this->username = $userTransfer->getUsernameOrFail();
        $this->password = $userTransfer->getPassword();
        $this->roles = $roles;
        $this->stateHash = $this->computeStateHash($this->password);
    }

    /**
     * @return array<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function eraseCredentials(): void
    {
    }

    public function isEqualTo(SymfonyUserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        return $user->getStateHash() === $this->stateHash;
    }

    public function getStateHash(): ?string
    {
        return $this->stateHash;
    }

    public function getMerchantUserTransfer(): MerchantUserTransfer
    {
        return $this->merchantUserTransfer;
    }

    public function __serialize(): array
    {
        $merchantUserTransfer = clone $this->merchantUserTransfer;
        $userTransfer = $merchantUserTransfer->getUser();

        if ($userTransfer !== null) {
            $cleanUserTransfer = (new UserTransfer())->fromArray(
                array_diff_key($userTransfer->modifiedToArray(), [UserTransfer::PASSWORD => true]),
                true,
            );
            $merchantUserTransfer->setUser($cleanUserTransfer);
        }

        return [
            static::SERIALIZATION_KEY_MERCHANT_USER_TRANSFER => $merchantUserTransfer,
            static::SERIALIZATION_KEY_USERNAME => $this->username,
            static::SERIALIZATION_KEY_ROLES => $this->roles,
            static::SERIALIZATION_KEY_STATE_HASH => $this->stateHash,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        $data = $this->normalizeLegacySessionData($data);

        $this->merchantUserTransfer = $data[static::SERIALIZATION_KEY_MERCHANT_USER_TRANSFER];
        $this->username = $data[static::SERIALIZATION_KEY_USERNAME];
        $this->roles = $data[static::SERIALIZATION_KEY_ROLES];
        $this->password = null;

        $this->stateHash = $data[static::SERIALIZATION_KEY_STATE_HASH]
            ?? $this->computeStateHash($data[static::SERIALIZATION_KEY_PASSWORD] ?? null);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function normalizeLegacySessionData(array $data): array
    {
        $normalizedData = [];

        foreach ($data as $key => $value) {
            $normalizedData[str_replace(static::LEGACY_PROTECTED_PROPERTY_PREFIX, '', $key)] = $value;
        }

        return $normalizedData;
    }

    protected function computeStateHash(?string $password): string
    {
        $userTransfer = $this->merchantUserTransfer->getUser();

        return hash('md5', implode('|', [
            $password ?? '',
            $userTransfer ? ($userTransfer->getStatus() ?? '') : '',
            implode(',', $this->roles),
        ]));
    }
}
