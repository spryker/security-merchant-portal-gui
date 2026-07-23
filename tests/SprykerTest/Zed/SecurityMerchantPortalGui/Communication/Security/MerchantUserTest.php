<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SecurityMerchantPortalGui\Communication\Security;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\MerchantUserTransfer;
use Generated\Shared\Transfer\UserTransfer;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Security\MerchantUser;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SecurityMerchantPortalGui
 * @group Communication
 * @group Security
 * @group MerchantUserTest
 * Add your own group annotations below this line
 */
class MerchantUserTest extends Unit
{
    protected const string PASSWORD_HASH = '$2y$10$examplehashvalue1234567890';

    protected const string STATUS_ACTIVE = 'active';

    public function testMerchantUsersWithSameStateAreEqual(): void
    {
        // Arrange, Act, Assert
        $this->assertTrue($this->createMerchantUser()->isEqualTo($this->createMerchantUser()));
    }

    public function testMerchantUsersWithDifferentPasswordAreNotEqual(): void
    {
        // Arrange
        $merchantUser = $this->createMerchantUser();
        $changedPasswordMerchantUser = $this->createMerchantUser([UserTransfer::PASSWORD => 'another-hash']);

        // Act, Assert
        $this->assertFalse($merchantUser->isEqualTo($changedPasswordMerchantUser));
    }

    public function testMerchantUsersWithDifferentStatusAreNotEqual(): void
    {
        // Arrange
        $merchantUser = $this->createMerchantUser();
        $blockedMerchantUser = $this->createMerchantUser([UserTransfer::STATUS => 'blocked']);

        // Act, Assert
        $this->assertFalse($merchantUser->isEqualTo($blockedMerchantUser));
    }

    public function testMerchantUsersWithDifferentRolesAreNotEqual(): void
    {
        // Arrange
        $merchantUser = $this->createMerchantUser();
        $preAuthMerchantUser = $this->createMerchantUser([], ['ACCESS_MODE_PRE_AUTH']);

        // Act, Assert
        $this->assertFalse($merchantUser->isEqualTo($preAuthMerchantUser));
    }

    public function testSerializationStripsPasswordAndPreservesEquality(): void
    {
        // Arrange
        $merchantUser = $this->createMerchantUser();

        // Act
        $serializedMerchantUser = serialize($merchantUser);
        $unserializedMerchantUser = unserialize($serializedMerchantUser);

        // Assert
        $this->assertStringNotContainsString(static::PASSWORD_HASH, $serializedMerchantUser);
        $this->assertNull($unserializedMerchantUser->getPassword());
        $this->assertTrue($unserializedMerchantUser->isEqualTo($this->createMerchantUser()));
    }

    public function testUnserializeAcceptsSessionWrittenBeforePasswordRemoval(): void
    {
        // Arrange
        $legacySerializedMerchantUser = $this->createLegacySerializedMerchantUser();

        // Act
        $unserializedMerchantUser = unserialize($legacySerializedMerchantUser);

        // Assert
        $this->assertInstanceOf(MerchantUser::class, $unserializedMerchantUser);
        $this->assertNull($unserializedMerchantUser->getPassword());
        // The legacy transfer still carries the hash until the session is written again;
        // the next serialization must strip it.
        $this->assertStringNotContainsString(static::PASSWORD_HASH, serialize($unserializedMerchantUser));
        $this->assertTrue($unserializedMerchantUser->isEqualTo($this->createMerchantUser()));
    }

    public function testLegacySessionMerchantUserWithChangedPasswordIsNotEqual(): void
    {
        // Arrange
        $legacySerializedMerchantUser = $this->createLegacySerializedMerchantUser();

        // Act
        $unserializedMerchantUser = unserialize($legacySerializedMerchantUser);

        // Assert
        $this->assertFalse($unserializedMerchantUser->isEqualTo($this->createMerchantUser([UserTransfer::PASSWORD => 'another-hash'])));
    }

    /**
     * Builds the exact payload the default object serialization produced before `__serialize()` existed:
     * protected property names prefixed with "\0*\0" and no `stateHash` entry.
     */
    protected function createLegacySerializedMerchantUser(): string
    {
        $userTransfer = (new UserTransfer())->fromArray([
            UserTransfer::USERNAME => 'merchant@spryker.com',
            UserTransfer::PASSWORD => static::PASSWORD_HASH,
            UserTransfer::STATUS => static::STATUS_ACTIVE,
        ], true);

        $merchantUserTransfer = (new MerchantUserTransfer())
            ->setIdMerchantUser(7)
            ->setUser($userTransfer);

        $propertyTable = [
            "\0*\0merchantUserTransfer" => $merchantUserTransfer,
            "\0*\0username" => $userTransfer->getUsername(),
            "\0*\0password" => $userTransfer->getPassword(),
            "\0*\0roles" => ['ROLE_MERCHANT_USER'],
        ];
        $serializedPropertyTable = serialize($propertyTable);

        return sprintf(
            'O:%d:"%s":%d:{%s',
            strlen(MerchantUser::class),
            MerchantUser::class,
            count($propertyTable),
            substr($serializedPropertyTable, strpos($serializedPropertyTable, '{') + 1),
        );
    }

    /**
     * @param array<string, mixed> $userData
     * @param list<string> $roles
     */
    protected function createMerchantUser(array $userData = [], array $roles = ['ROLE_MERCHANT_USER']): MerchantUser
    {
        $userTransfer = (new UserTransfer())->fromArray($userData + [
            UserTransfer::USERNAME => 'merchant@spryker.com',
            UserTransfer::PASSWORD => static::PASSWORD_HASH,
            UserTransfer::STATUS => static::STATUS_ACTIVE,
        ], true);

        $merchantUserTransfer = (new MerchantUserTransfer())
            ->setIdMerchantUser(7)
            ->setUser($userTransfer);

        return new MerchantUser($merchantUserTransfer, $roles);
    }
}
