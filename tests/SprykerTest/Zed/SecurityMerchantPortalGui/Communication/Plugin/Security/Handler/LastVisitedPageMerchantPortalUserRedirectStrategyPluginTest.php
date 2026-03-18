<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security\Handler;

use Codeception\Test\Unit;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security\Handler\LastVisitedPageMerchantPortalUserRedirectStrategyPlugin;
use Symfony\Component\HttpFoundation\Request;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SecurityMerchantPortalGui
 * @group Communication
 * @group Plugin
 * @group Security
 * @group Handler
 * @group LastVisitedPageMerchantPortalUserRedirectStrategyPluginTest
 * Add your own group annotations below this line
 */
class LastVisitedPageMerchantPortalUserRedirectStrategyPluginTest extends Unit
{
    protected const string LAST_VISITED_URL = '/merchant-portal/offers';

    protected const string URL_MERCHANT_PORTAL_DASHBOARD = '/merchant-portal/dashboard';

    public function testGivenLastVisitedCookiePresentWhenIsApplicableCalledThenReturnsTrue(): void
    {
        // Arrange
        $plugin = new LastVisitedPageMerchantPortalUserRedirectStrategyPlugin();
        $request = $this->createRequestWithCookie(static::LAST_VISITED_URL);

        // Act
        $result = $plugin->isApplicable($request);

        // Assert
        $this->assertTrue($result);
    }

    public function testGivenNoLastVisitedCookieWhenIsApplicableCalledThenReturnsFalse(): void
    {
        // Arrange
        $plugin = new LastVisitedPageMerchantPortalUserRedirectStrategyPlugin();
        $request = Request::create(static::URL_MERCHANT_PORTAL_DASHBOARD);

        // Act
        $result = $plugin->isApplicable($request);

        // Assert
        $this->assertFalse($result);
    }

    public function testGivenLastVisitedCookiePresentWhenGetRedirectUrlCalledThenReturnsCookieValue(): void
    {
        // Arrange
        $plugin = new LastVisitedPageMerchantPortalUserRedirectStrategyPlugin();
        $request = $this->createRequestWithCookie(static::LAST_VISITED_URL);

        // Act
        $result = $plugin->getRedirectUrl($request);

        // Assert
        $this->assertSame(static::LAST_VISITED_URL, $result);
    }

    protected function createRequestWithCookie(string $url): Request
    {
        return Request::create(static::URL_MERCHANT_PORTAL_DASHBOARD, Request::METHOD_GET, [], ['last-visited-page' => $url]);
    }
}
