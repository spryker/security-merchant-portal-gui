<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Loader;

use Generated\Shared\Transfer\MerchantUserCriteriaTransfer;
use Generated\Shared\Transfer\OauthMerchantUserRestrictionRequestTransfer;
use Generated\Shared\Transfer\OauthMerchantUserRestrictionResponseTransfer;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Security\SecurityOauthMerchantUser;
use Spryker\Zed\SecurityMerchantPortalGui\Dependency\Facade\SecurityMerchantPortalGuiToMerchantUserFacadeInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Dependency\Facade\SecurityMerchantPortalGuiToMessengerFacadeInterface;
use Spryker\Zed\SecurityMerchantPortalGui\SecurityMerchantPortalGuiConfig;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class OauthMerchantUserLoader implements OauthMerchantUserLoaderInterface
{
    /**
     * @param \Spryker\Zed\SecurityMerchantPortalGui\Dependency\Facade\SecurityMerchantPortalGuiToMerchantUserFacadeInterface $merchantUserFacade
     * @param array<\Spryker\Zed\SecurityMerchantPortalGuiExtension\Dependency\Plugin\OauthMerchantUserRestrictionPluginInterface> $oauthMerchantPortalRestrictionPlugins
     * @param \Spryker\Zed\SecurityMerchantPortalGui\Dependency\Facade\SecurityMerchantPortalGuiToMessengerFacadeInterface $messengerFacade
     */
    public function __construct(
        protected SecurityMerchantPortalGuiToMerchantUserFacadeInterface $merchantUserFacade,
        protected array $oauthMerchantPortalRestrictionPlugins,
        protected SecurityMerchantPortalGuiToMessengerFacadeInterface $messengerFacade,
    ) {
    }

    public function loadUserByIdentifier(string $identifier): SecurityOauthMerchantUser
    {
        $merchantUserTransfer = $this->merchantUserFacade->findMerchantUser(
            (new MerchantUserCriteriaTransfer())
                ->setUsername($identifier)
                ->setWithUser(true),
        );

        if ($merchantUserTransfer === null) {
            throw new UserNotFoundException(sprintf('Merchant user with username "%s" not found.', $identifier));
        }

        $oauthMerchantUserRestrictionResponseTransfer = $this->isRestricted(
            (new OauthMerchantUserRestrictionRequestTransfer())->setMerchantUser($merchantUserTransfer),
        );

        if ($oauthMerchantUserRestrictionResponseTransfer->getIsRestricted()) {
            $this->addErrorMessages($oauthMerchantUserRestrictionResponseTransfer);

            throw new UnsupportedUserException(sprintf('Merchant user "%s" is restricted from OAuth login.', $identifier));
        }

        return new SecurityOauthMerchantUser($merchantUserTransfer, [SecurityMerchantPortalGuiConfig::ROLE_MERCHANT_USER]);
    }

    public function isRestricted(
        OauthMerchantUserRestrictionRequestTransfer $oauthMerchantUserRestrictionRequestTransfer
    ): OauthMerchantUserRestrictionResponseTransfer {
        foreach ($this->oauthMerchantPortalRestrictionPlugins as $oauthMerchantPortalRestrictionPlugin) {
            $oauthMerchantUserRestrictionResponseTransfer = $oauthMerchantPortalRestrictionPlugin->isRestricted($oauthMerchantUserRestrictionRequestTransfer);

            if ($oauthMerchantUserRestrictionResponseTransfer->getIsRestricted()) {
                return $oauthMerchantUserRestrictionResponseTransfer;
            }
        }

        return (new OauthMerchantUserRestrictionResponseTransfer())->setIsRestricted(false);
    }

    protected function addErrorMessages(OauthMerchantUserRestrictionResponseTransfer $oauthMerchantUserRestrictionResponseTransfer): void
    {
        foreach ($oauthMerchantUserRestrictionResponseTransfer->getMessages() as $messageTransfer) {
            $this->messengerFacade->addErrorMessage($messageTransfer);
        }
    }
}
