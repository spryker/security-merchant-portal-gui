<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security\Handler;

use Codeception\Test\Unit;
use Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security\Handler\AccessDeniedHandler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
 * @group AccessDeniedHandlerTest
 * Add your own group annotations below this line
 */
class AccessDeniedHandlerTest extends Unit
{
    protected const string LOGIN_URL = '/security-gui/login';

    protected const string ACCESS_MODE_PRE_AUTH = 'ACCESS_MODE_PRE_AUTH';

    public function testHandleReturnsNullWhenTokenIsMissing(): void
    {
        // Arrange
        $tokenStorageMock = $this->createTokenStorageMock(null);
        $accessDeniedHandler = new AccessDeniedHandler($tokenStorageMock, static::LOGIN_URL);

        // Act
        $response = $accessDeniedHandler->handle($this->createRequest(), new AccessDeniedException());

        // Assert
        $this->assertNull($response);
    }

    public function testHandleReturnsNullForFullyAuthenticatedToken(): void
    {
        // Arrange
        $tokenStorageMock = $this->createTokenStorageMock($this->createTokenMock(['ROLE_USER']));
        $tokenStorageMock->expects($this->never())->method('setToken');
        $accessDeniedHandler = new AccessDeniedHandler($tokenStorageMock, static::LOGIN_URL);

        // Act
        $response = $accessDeniedHandler->handle($this->createRequest(), new AccessDeniedException());

        // Assert
        $this->assertNull($response);
    }

    public function testHandleResetsPreAuthenticatedTokenAndRedirectsToLogin(): void
    {
        // Arrange
        $tokenStorageMock = $this->createTokenStorageMock($this->createTokenMock([static::ACCESS_MODE_PRE_AUTH]));
        $tokenStorageMock->expects($this->once())->method('setToken')->with(null);
        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->expects($this->once())->method('invalidate');
        $accessDeniedHandler = new AccessDeniedHandler($tokenStorageMock, static::LOGIN_URL);

        // Act
        $response = $accessDeniedHandler->handle($this->createRequest($sessionMock), new AccessDeniedException());

        // Assert
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(static::LOGIN_URL, $response->getTargetUrl());
    }

    protected function createTokenStorageMock(?TokenInterface $token): TokenStorageInterface
    {
        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $tokenStorageMock->method('getToken')->willReturn($token);

        return $tokenStorageMock;
    }

    /**
     * @param list<string> $roleNames
     */
    protected function createTokenMock(array $roleNames): TokenInterface
    {
        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock->method('getRoleNames')->willReturn($roleNames);

        return $tokenMock;
    }

    protected function createRequest(?SessionInterface $sessionMock = null): Request
    {
        $request = new Request();
        $request->setSession($sessionMock ?? $this->createMock(SessionInterface::class));

        return $request;
    }
}
