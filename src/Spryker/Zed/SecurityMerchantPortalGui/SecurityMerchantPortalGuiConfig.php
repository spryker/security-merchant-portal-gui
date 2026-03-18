<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityMerchantPortalGui;

use Spryker\Shared\SecurityMerchantPortalGui\SecurityMerchantPortalGuiConstants;
use Spryker\Zed\Kernel\AbstractBundleConfig;
use Symfony\Component\HttpFoundation\Cookie;

class SecurityMerchantPortalGuiConfig extends AbstractBundleConfig
{
    /**
     * @var string
     */
    public const ROLE_MERCHANT_USER = 'ROLE_MERCHANT_USER';

    public const string STORAGE_TYPE_COOKIE = 'cookie';

    protected const string LAST_VISITED_PAGE_COOKIE_NAME = 'last-visited-page';

    protected const string LAST_VISITED_PAGE_COOKIE_PATH = '/';

    /**
     * @var string
     */
    protected const MERCHANT_USER_DEFAULT_URL = '/dashboard-merchant-portal-gui/dashboard';

    /**
     * @var string
     */
    protected const LOGIN_URL = '/security-merchant-portal-gui/login';

    /**
     * @var string
     */
    protected const MERCHANT_PORTAL_ROUTE_PATTERN = '^/(.+)-merchant-portal-gui/';

    /**
     * @var string
     */
    protected const IGNORABLE_PATH_PATTERN = '^/security-merchant-portal-gui';

    /**
     * @var string
     */
    protected const MERCHANT_PORTAL_SECURITY_BLOCKER_ENTITY_TYPE = 'customer';

    /**
     * @var bool
     */
    protected const MERCHANT_PORTAL_SECURITY_BLOCKER_ENABLED = false;

    /**
     * @var int
     */
    protected const MIN_LENGTH_MERCHANT_USER_PASSWORD = 12;

    /**
     * @var int
     */
    protected const MAX_LENGTH_MERCHANT_USER_PASSWORD = 128;

    /**
     * @var string
     */
    protected const PASSWORD_VALIDATION_PATTERN = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*()\_\-\=\+\[\]\{\}\|;:<>.,\/?\\~])[A-Za-z\d!@#$%^&*()\_\-\=\+\[\]\{\}\|;:<>.,\/?\\~]+$/';

    /**
     * @var string
     */
    protected const PASSWORD_VALIDATION_MESSAGE = 'Your password must include at least one uppercase letter, one lowercase letter, one number, and one special character from the following list: !@#$%^&*()_-+=[]{}|;:<>.,/?\~. Non-Latin and other special characters are not allowed.';

    /**
     * Specification:
     * - Checks if the security blocker is enabled.
     * - It is disabled by default.
     *
     * @api
     *
     * @return bool
     */
    public function isMerchantPortalSecurityBlockerEnabled(): bool
    {
        return static::MERCHANT_PORTAL_SECURITY_BLOCKER_ENABLED;
    }

    /**
     * Specification:
     * - Returns the entity identifier that is used to block the password resets.
     *
     * @api
     *
     * @return string
     */
    public function getMerchantPortalSecurityBlockerEntityType(): string
    {
        return static::MERCHANT_PORTAL_SECURITY_BLOCKER_ENTITY_TYPE;
    }

    /**
     * @api
     *
     * @return string
     */
    public function getDefaultTargetPath(): string
    {
        return static::MERCHANT_USER_DEFAULT_URL;
    }

    /**
     * @api
     *
     * @return string
     */
    public function getUrlLogin(): string
    {
        return static::LOGIN_URL;
    }

    /**
     * Specification:
     * - Returns the minimum length for merchant user password.
     *
     * @api
     *
     * @return int
     */
    public function getMerchantUserPasswordMinLength(): int
    {
        return static::MIN_LENGTH_MERCHANT_USER_PASSWORD;
    }

    /**
     * Specification:
     * - Returns the maximum length for merchant user password.
     *
     * @api
     *
     * @return int
     */
    public function getMerchantUserPasswordMaxLength(): int
    {
        return static::MAX_LENGTH_MERCHANT_USER_PASSWORD;
    }

    /**
     * Specification:
     * - Returns the pattern for merchant user password validation.
     *
     * @api
     *
     * @return string
     */
    public function getMerchantUserPasswordPattern(): string
    {
        return static::PASSWORD_VALIDATION_PATTERN;
    }

    /**
     * Specification:
     * - Returns the message for merchant user password validation.
     *
     * @api
     *
     * @return string
     */
    public function getPasswordValidationMessage(): string
    {
        return static::PASSWORD_VALIDATION_MESSAGE;
    }

    /**
     * Specification:
     * - Returns the ignorable security Merchant Portal path pattern.
     *
     * @api
     *
     * @return string
     */
    public function getIgnorablePathPattern(): string
    {
        return static::IGNORABLE_PATH_PATTERN;
    }

    /**
     * Specification:
     * - Returns the route pattern for the merchant portal.
     *
     * @api
     *
     * @return string
     */
    public function getMerchantPortalRoutePattern(): string
    {
        return static::MERCHANT_PORTAL_ROUTE_PATTERN;
    }

    /**
     * Specification:
     * - Returns the cookie name used to store the last visited page URL.
     *
     * @api
     *
     * @return string
     */
    public function getLastVisitedPageCookieName(): string
    {
        return static::LAST_VISITED_PAGE_COOKIE_NAME;
    }

    /**
     * Specification:
     * - Returns the cookie path for the last visited page cookie.
     *
     * @api
     *
     * @return string
     */
    public function getLastVisitedPageCookiePath(): string
    {
        return static::LAST_VISITED_PAGE_COOKIE_PATH;
    }

    /**
     * Specification:
     * - Returns the last visited page storage type used to select the storage strategy.
     *
     * @api
     *
     * @return string
     */
    public function getLastVisitedPageStorageType(): string
    {
        return static::STORAGE_TYPE_COOKIE;
    }

    /**
     * Specification:
     * - Returns the SameSite attribute for the last visited page cookie.
     * - Defaults to 'lax'. Can be set to 'strict' for stricter cross-site protections.
     *
     * @api
     *
     * @phpstan-return ''|'lax'|'none'|'strict'
     *
     * @return string
     */
    public function getLastVisitedPageCookieSameSite(): string
    {
        return Cookie::SAMESITE_LAX;
    }

    /**
     * Specification:
     * - Returns whether the last visited page cookie should be sent over HTTPS only.
     * - Enabled by default to prevent transmission over plain HTTP connections.
     *
     * @api
     *
     * @return bool
     */
    public function isLastVisitedPageCookieSecure(): bool
    {
        return $this->get(SecurityMerchantPortalGuiConstants::ZED_IS_SSL_ENABLED, false);
    }

    /**
     * Specification:
     * - Returns the expiration time for the last visited page cookie as a Unix timestamp.
     * - Returns 0 by default, which means the cookie expires when the browser session ends.
     *
     * @api
     *
     * @return int
     */
    public function getLastVisitedPageCookieExpires(): int
    {
        return 0;
    }
}
