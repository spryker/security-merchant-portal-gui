<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SecurityMerchantPortalGui\Communication\Plugin\SecurityMerchantPortalGui;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\MerchantTransfer;
use Generated\Shared\Transfer\ResourceOwnerTransfer;
use Generated\Shared\Transfer\UserTransfer;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\SecurityMerchantPortalGui\ExistingMerchantUserAuthenticationStrategyPlugin;
use SprykerTest\Zed\SecurityMerchantPortalGui\SecurityMerchantPortalGuiCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SecurityMerchantPortalGui
 * @group Communication
 * @group Plugin
 * @group SecurityMerchantPortalGui
 * @group ExistingMerchantUserAuthenticationStrategyPluginTest
 * Add your own group annotations below this line
 */
class ExistingMerchantUserAuthenticationStrategyPluginTest extends Unit
{
    protected const string MERCHANT_STATUS_APPROVED = 'approved';

    protected SecurityMerchantPortalGuiCommunicationTester $tester;

    public function testIsApplicableReturnsTrueWhenEmailIsProvided(): void
    {
        // Arrange
        $plugin = new ExistingMerchantUserAuthenticationStrategyPlugin();
        $plugin->setFactory($this->tester->getFactory());

        $resourceOwnerTransfer = (new ResourceOwnerTransfer())
            ->setEmail('merchant@spryker.com');

        // Act
        $isApplicable = $plugin->isApplicable($resourceOwnerTransfer);

        // Assert
        $this->assertTrue($isApplicable, 'Expected true when resource owner has an email address.');
    }

    public function testIsApplicableReturnsFalseWhenEmailIsNull(): void
    {
        // Arrange
        $plugin = new ExistingMerchantUserAuthenticationStrategyPlugin();
        $plugin->setFactory($this->tester->getFactory());

        $resourceOwnerTransfer = new ResourceOwnerTransfer();

        // Act
        $isApplicable = $plugin->isApplicable($resourceOwnerTransfer);

        // Assert
        $this->assertFalse($isApplicable, 'Expected false when resource owner has no email address.');
    }

    public function testResolveOauthMerchantUserReturnsMerchantUserWhenEmailMatchesExistingUser(): void
    {
        // Arrange
        $userTransfer = $this->tester->haveUser([UserTransfer::USERNAME => 'merchant-user@spryker.com']);
        $merchantTransfer = $this->tester->haveMerchant([MerchantTransfer::STATUS => static::MERCHANT_STATUS_APPROVED]);
        $this->tester->haveMerchantUser($merchantTransfer, $userTransfer);

        $plugin = new ExistingMerchantUserAuthenticationStrategyPlugin();
        $plugin->setFactory($this->tester->getFactory());

        $resourceOwnerTransfer = (new ResourceOwnerTransfer())
            ->setEmail($userTransfer->getUsername());

        // Act
        $merchantUserTransfer = $plugin->resolveOauthMerchantUser($resourceOwnerTransfer);

        // Assert
        $this->assertNotNull($merchantUserTransfer, 'Expected merchant user to be resolved when email matches an existing merchant user.');
        $this->assertSame($userTransfer->getUsername(), $merchantUserTransfer->getUserOrFail()->getUsername());
    }

    public function testResolveOauthMerchantUserReturnsNullWhenNoUserWithEmailExists(): void
    {
        // Arrange
        $plugin = new ExistingMerchantUserAuthenticationStrategyPlugin();
        $plugin->setFactory($this->tester->getFactory());

        $resourceOwnerTransfer = (new ResourceOwnerTransfer())
            ->setEmail('nonexistent-user@spryker.com');

        // Act
        $merchantUserTransfer = $plugin->resolveOauthMerchantUser($resourceOwnerTransfer);

        // Assert
        $this->assertNull($merchantUserTransfer, 'Expected null when no merchant user exists with the given email.');
    }
}
