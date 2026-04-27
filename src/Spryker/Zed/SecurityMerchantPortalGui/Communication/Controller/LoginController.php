<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityMerchantPortalGui\Communication\Controller;

use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\SecurityMerchantPortalGui\Communication\SecurityMerchantPortalGuiCommunicationFactory getFactory()
 */
class LoginController extends AbstractController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array<string, mixed>
     */
    public function indexAction(Request $request)
    {
        if ($this->getFactory()->getSecurityFacade()->isUserLoggedIn()) {
            return $this->redirectResponse(
                $this->getFactory()->getConfig()->getDefaultTargetPath(),
            );
        }

        return $this->viewResponse([
            'form' => $this
                ->getFactory()
                ->createLoginForm()
                ->handleRequest($request)
                ->createView(),
            'authenticationLinkCollection' => $this->executeAuthenticationLinkPlugins(),
        ]);
    }

    /**
     * @return array<\Generated\Shared\Transfer\OauthAuthenticationLinkTransfer>
     */
    protected function executeAuthenticationLinkPlugins(): array
    {
        $authenticationLinks = [];

        foreach ($this->getFactory()->getMerchantPortalAuthenticationLinkPlugins() as $merchantPortalAuthenticationLinkPlugin) {
            $authenticationLinks = array_merge($authenticationLinks, $merchantPortalAuthenticationLinkPlugin->getAuthenticationLinks());
        }

        return $authenticationLinks;
    }
}
