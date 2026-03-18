<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SecurityMerchantPortalGui\Communication\Plugin\EventDispatcher;

use Codeception\Stub;
use Codeception\Test\Unit;
use Spryker\Service\Container\Container;
use Spryker\Shared\EventDispatcher\EventDispatcher;
use Spryker\Shared\EventDispatcherExtension\Dependency\Plugin\EventDispatcherPluginInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Checker\LastVisitedPageUrlCheckerInterface;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\EventSubscriber\LastVisitedPageEventSubscriber;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\EventDispatcher\LastVisitedPageEventDispatcherPlugin;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\SecurityMerchantPortalGuiCommunicationFactory;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Storage\LastVisitedPageCookieStorage;
use SprykerTest\Zed\SecurityMerchantPortalGui\SecurityMerchantPortalGuiCommunicationTester;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SecurityMerchantPortalGui
 * @group Communication
 * @group Plugin
 * @group EventDispatcher
 * @group LastVisitedPageEventDispatcherPluginTest
 * Add your own group annotations below this line
 */
class LastVisitedPageEventDispatcherPluginTest extends Unit
{
    protected SecurityMerchantPortalGuiCommunicationTester $tester;

    protected const string COOKIE_LAST_VISITED_PAGE = 'last-visited-page';

    protected const string URL_MERCHANT_PORTAL_DASHBOARD = '/merchant-portal/dashboard';

    public function testGivenLoggedInMerchantUserAndEligibleRequestWhenResponseDispatchedThenCookieIsSet(): void
    {
        // Arrange
        $plugin = $this->createPlugin(isEligible: true);
        $request = Request::create(static::URL_MERCHANT_PORTAL_DASHBOARD);
        $event = $this->createResponseEvent($request);

        // Act
        $this->dispatchEvent($plugin, $event);

        // Assert
        $this->assertTrue($event->getResponse()->headers->has('Set-Cookie'));
        $this->assertStringContainsString(static::COOKIE_LAST_VISITED_PAGE, (string)$event->getResponse()->headers->get('Set-Cookie'));
    }

    public function testGivenMerchantUserNotEligibleWhenResponseDispatchedThenCookieIsNotSet(): void
    {
        // Arrange
        $plugin = $this->createPlugin(isEligible: false);
        $request = Request::create(static::URL_MERCHANT_PORTAL_DASHBOARD);
        $event = $this->createResponseEvent($request);

        // Act
        $this->dispatchEvent($plugin, $event);

        // Assert
        $this->assertFalse($event->getResponse()->headers->has('Set-Cookie'));
    }

    public function testGivenLogoutEventWithResponseWhenDispatchedThenCookieIsCleared(): void
    {
        // Arrange
        $plugin = $this->createPlugin(isEligible: true);
        $response = new Response();
        $event = $this->createLogoutEvent($response);

        // Act
        $this->dispatchLogoutEvent($plugin, $event);

        // Assert
        $this->assertTrue($response->headers->has('Set-Cookie'));
        $this->assertStringContainsString(static::COOKIE_LAST_VISITED_PAGE, (string)$response->headers->get('Set-Cookie'));
    }

    public function testGivenLogoutEventWithoutResponseWhenDispatchedThenNothingHappens(): void
    {
        // Arrange
        $plugin = $this->createPlugin(isEligible: true);
        $event = $this->createLogoutEvent(null);

        // Act
        $this->dispatchLogoutEvent($plugin, $event);

        // Assert
        $this->assertNull($event->getResponse());
    }

    protected function createPlugin(bool $isEligible): LastVisitedPageEventDispatcherPlugin
    {
        $urlCheckerMock = $this->getMockBuilder(LastVisitedPageUrlCheckerInterface::class)->getMock();
        $urlCheckerMock->method('isEligibleForPostLoginRedirect')->willReturn($isEligible);

        $factoryMock = $this->getMockBuilder(SecurityMerchantPortalGuiCommunicationFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factoryMock->method('createLastVisitedPageEventSubscriber')
            ->willReturn(new LastVisitedPageEventSubscriber($urlCheckerMock, new LastVisitedPageCookieStorage($this->tester->getModuleConfig())));

        $plugin = new LastVisitedPageEventDispatcherPlugin();
        $plugin->setFactory($factoryMock);

        return $plugin;
    }

    protected function dispatchEvent(EventDispatcherPluginInterface $plugin, ResponseEvent $event): void
    {
        $eventDispatcher = new EventDispatcher();
        $plugin->extend($eventDispatcher, new Container());

        $eventDispatcher->dispatch($event, KernelEvents::RESPONSE);
    }

    protected function createResponseEvent(Request $request): ResponseEvent
    {
        /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $kernelMock */
        $kernelMock = Stub::makeEmpty(HttpKernelInterface::class);

        return new ResponseEvent($kernelMock, $request, HttpKernelInterface::MAIN_REQUEST, new Response());
    }

    protected function createLogoutEvent(?Response $response): LogoutEvent
    {
        $event = new LogoutEvent(Request::create('/merchant-portal/logout'), null);

        if ($response !== null) {
            $event->setResponse($response);
        }

        return $event;
    }

    protected function dispatchLogoutEvent(EventDispatcherPluginInterface $plugin, LogoutEvent $event): void
    {
        $eventDispatcher = new EventDispatcher();
        $plugin->extend($eventDispatcher, new Container());

        $eventDispatcher->dispatch($event, LogoutEvent::class);
    }
}
