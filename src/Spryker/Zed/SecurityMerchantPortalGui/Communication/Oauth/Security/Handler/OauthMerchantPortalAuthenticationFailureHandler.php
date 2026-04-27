<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Oauth\Security\Handler;

use Generated\Shared\Transfer\MessageTransfer;
use Spryker\Zed\SecurityMerchantPortalGui\Dependency\Facade\SecurityMerchantPortalGuiToMessengerFacadeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class OauthMerchantPortalAuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
 /**
  * @uses \Spryker\Zed\SecurityMerchantPortalGui\SecurityMerchantPortalGuiConfig::LOGIN_URL
  */
    protected const string LOGIN_URL = '/security-merchant-portal-gui/login';

    public function __construct(
        protected SecurityMerchantPortalGuiToMessengerFacadeInterface $messengerFacade,
    ) {
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $this->messengerFacade->addErrorMessage(
            (new MessageTransfer())->setValue(strtr($exception->getMessageKey(), $exception->getMessageData())),
        );

        return new RedirectResponse(static::LOGIN_URL);
    }
}
