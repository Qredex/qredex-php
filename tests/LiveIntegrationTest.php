<?php

/**
 *    ▄▄▄▄
 *  ▄█▀▀███▄▄              █▄
 *  ██    ██ ▄             ██
 *  ██    ██ ████▄▄█▀█▄ ▄████ ▄█▀█▄▀██ ██▀
 *  ██  ▄ ██ ██   ██▄█▀ ██ ██ ██▄█▀  ███
 *   ▀█████▄▄█▀  ▄▀█▄▄▄▄█▀███▄▀█▄▄▄▄██ ██▄
 *        ▀█
 *
 *  Copyright (C) 2026 — 2026, Qredex, LTD. All Rights Reserved.
 *
 *  DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 *  Licensed under the Apache License, Version 2.0. See LICENSE for the full license text.
 *  You may not use this file except in compliance with that License.
 *  Unless required by applicable law or agreed to in writing, software distributed under the
 *  License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 *  either express or implied. See the License for the specific language governing permissions
 *  and limitations under the License.
 *
 *  If you need additional information or have any questions, please email: copyright@qredex.com
 */

declare(strict_types=1);

namespace Qredex\Tests;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Qredex\Auth\ClientCredentialsAuthentication;
use Qredex\Auth\QredexScope;
use Qredex\Config\QredexConfig;
use Qredex\Config\QredexEnvironment;
use Qredex\Qredex;
use Qredex\Request\CreateCreatorRequest;
use Qredex\Request\CreateLinkRequest;
use Qredex\Request\IssueInfluenceIntentTokenRequest;
use Qredex\Request\LockPurchaseIntentRequest;
use Qredex\Request\RecordPaidOrderRequest;
use Qredex\Request\RecordRefundRequest;

#[Group('live')]
final class LiveIntegrationTest extends TestCase
{
    private const REQUIRED_ENV = [
        'QREDEX_LIVE_CLIENT_ID',
        'QREDEX_LIVE_CLIENT_SECRET',
        'QREDEX_LIVE_STORE_ID',
    ];

    public static function setUpBeforeClass(): void
    {
        if (self::env('QREDEX_LIVE_ENABLED') !== '1') {
            self::markTestSkipped('Live tests are skipped unless QREDEX_LIVE_ENABLED=1.');
        }

        $missing = array_values(array_filter(
            self::REQUIRED_ENV,
            static fn (string $key): bool => self::env($key) === null,
        ));

        if ($missing !== []) {
            self::markTestSkipped('Live tests require: ' . implode(', ', $missing));
        }
    }

    public function testCanonicalIntegrationsFlowAgainstLiveApi(): void
    {
        $qredex = Qredex::init(new QredexConfig(
            auth: new ClientCredentialsAuthentication(
                clientId: self::requireEnv('QREDEX_LIVE_CLIENT_ID'),
                clientSecret: self::requireEnv('QREDEX_LIVE_CLIENT_SECRET'),
                scope: [
                    QredexScope::CREATORS_READ,
                    QredexScope::CREATORS_WRITE,
                    QredexScope::LINKS_READ,
                    QredexScope::LINKS_WRITE,
                    QredexScope::INTENTS_WRITE,
                    QredexScope::ORDERS_READ,
                    QredexScope::ORDERS_WRITE,
                ],
            ),
            environment: QredexEnvironment::fromString(self::env('QREDEX_LIVE_ENVIRONMENT') ?? 'production'),
            timeoutMs: 20_000,
        ));

        $suffix = gmdate('YmdHis') . '-' . substr(bin2hex(random_bytes(4)), 0, 8);
        $storeId = self::requireEnv('QREDEX_LIVE_STORE_ID');
        $externalOrderId = "php-live-order-{$suffix}";

        $creator = $qredex->creators()->create(new CreateCreatorRequest(
            handle: "php-live-{$suffix}",
            displayName: "PHP Live {$suffix}",
        ));

        $fetchedCreator = $qredex->creators()->get($creator->id);
        $listedCreators = $qredex->creators()->list(['status' => 'ACTIVE']);

        $link = $qredex->links()->create(new CreateLinkRequest(
            storeId: $storeId,
            creatorId: $creator->id,
            linkName: "php-live-link-{$suffix}",
            destinationPath: "/products/php-live-{$suffix}",
            attributionWindowDays: 30,
            status: 'ACTIVE',
        ));

        $fetchedLink = $qredex->links()->get($link->id);
        $listedLinks = $qredex->links()->list(['status' => 'ACTIVE']);

        $iit = $qredex->intents()->issueInfluenceIntentToken(new IssueInfluenceIntentTokenRequest(
            linkId: $link->id,
            landingPath: "/products/php-live-{$suffix}",
        ));

        $pit = $qredex->intents()->lockPurchaseIntent(new LockPurchaseIntentRequest(
            token: $iit->token,
            source: 'php-live-integration-test',
            integrityVersion: 2,
        ));

        $order = $qredex->orders()->recordPaidOrder(new RecordPaidOrderRequest(
            storeId: $storeId,
            externalOrderId: $externalOrderId,
            currency: 'USD',
            purchaseIntentToken: $pit->token,
            subtotalPrice: 100,
            totalPrice: 100,
        ));

        $listedOrders = $qredex->orders()->list([
            'page' => 0,
            'size' => 20,
        ]);
        $orderDetails = $qredex->orders()->getDetails($order->id);

        $refund = $qredex->refunds()->recordRefund(new RecordRefundRequest(
            storeId: $storeId,
            externalOrderId: $externalOrderId,
            externalRefundId: "php-live-refund-{$suffix}",
            refundTotal: 1,
        ));

        self::assertSame($creator->id, $fetchedCreator->id);
        self::assertTrue(self::pageContainsId($listedCreators->items, $creator->id));
        self::assertSame($link->id, $fetchedLink->id);
        self::assertTrue(self::pageContainsId($listedLinks->items, $link->id));
        self::assertNotSame('', $iit->token);
        self::assertNotSame('', $pit->token);
        self::assertSame($externalOrderId, $order->externalOrderId);
        self::assertTrue(self::pageContainsId($listedOrders->items, $order->id));
        self::assertSame($order->id, $orderDetails->id);
        self::assertSame($externalOrderId, $orderDetails->externalOrderId);
        self::assertSame($externalOrderId, $refund->externalOrderId);
    }

    private static function env(string $key): ?string
    {
        $value = getenv($key);

        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private static function requireEnv(string $key): string
    {
        $value = self::env($key);

        self::assertNotNull($value, "Missing required live test env var {$key}.");

        return $value;
    }

    /**
     * @param array<int, object> $items
     */
    private static function pageContainsId(array $items, string $expectedId): bool
    {
        foreach ($items as $item) {
            if (property_exists($item, 'id') && is_string($item->id) && $item->id === $expectedId) {
                return true;
            }
        }

        return false;
    }
}
