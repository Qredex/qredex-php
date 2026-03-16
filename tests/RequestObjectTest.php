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

use PHPUnit\Framework\TestCase;
use Qredex\Error\RequestValidationError;
use Qredex\Request\CreateCreatorRequest;
use Qredex\Request\CreateLinkRequest;
use Qredex\Request\IssueInfluenceIntentTokenRequest;
use Qredex\Request\ListCreatorsFilter;
use Qredex\Request\ListLinksFilter;
use Qredex\Request\ListOrdersFilter;
use Qredex\Request\LockPurchaseIntentRequest;
use Qredex\Request\RecordPaidOrderRequest;
use Qredex\Request\RecordRefundRequest;

final class RequestObjectTest extends TestCase
{
    private const VALID_UUID = '61abc354-dd8d-4a23-be02-ece77b1b4da6';
    private const VALID_UUID_2 = '16fca3f2-b346-4f98-8e52-0895aac61c5b';

    public function testCreateCreatorRequestToArray(): void
    {
        $request = new CreateCreatorRequest(
            handle: 'amelia-rose',
            displayName: 'Amelia Rose',
            email: 'ops@example.com',
        );

        self::assertSame([
            'handle' => 'amelia-rose',
            'display_name' => 'Amelia Rose',
            'email' => 'ops@example.com',
        ], $request->toArray());
    }

    public function testCreateCreatorRequestFiltersNulls(): void
    {
        $request = new CreateCreatorRequest(handle: 'amelia-rose');
        $array = $request->toArray();

        self::assertSame(['handle' => 'amelia-rose'], $array);
        self::assertArrayNotHasKey('display_name', $array);
        self::assertArrayNotHasKey('email', $array);
    }

    public function testCreateLinkRequestToArray(): void
    {
        $request = new CreateLinkRequest(
            storeId: self::VALID_UUID,
            creatorId: self::VALID_UUID_2,
            linkName: 'Summer Sale',
            destinationPath: '/collections/summer',
            attributionWindowDays: 14,
            note: 'Campaign note',
        );

        $array = $request->toArray();

        self::assertSame(self::VALID_UUID, $array['store_id']);
        self::assertSame(self::VALID_UUID_2, $array['creator_id']);
        self::assertSame('Summer Sale', $array['link_name']);
        self::assertSame('/collections/summer', $array['destination_path']);
        self::assertSame(14, $array['attribution_window_days']);
        self::assertSame('Campaign note', $array['note']);
    }

    public function testIssueIitRequestToArray(): void
    {
        $request = new IssueInfluenceIntentTokenRequest(
            linkId: self::VALID_UUID,
            ipHash: 'hash_ip_123',
            referrer: 'https://social.example.com',
        );

        $array = $request->toArray();

        self::assertSame(self::VALID_UUID, $array['link_id']);
        self::assertSame('hash_ip_123', $array['ip_hash']);
        self::assertSame('https://social.example.com', $array['referrer']);
        self::assertArrayNotHasKey('user_agent_hash', $array);
    }

    public function testLockPitRequestToArray(): void
    {
        $request = new LockPurchaseIntentRequest(
            token: 'iit_token_abc123',
            source: 'checkout',
        );

        $array = $request->toArray();

        self::assertSame('iit_token_abc123', $array['token']);
        self::assertSame('checkout', $array['source']);
        self::assertArrayNotHasKey('integrity_version', $array);
    }

    public function testRecordPaidOrderRequestToArray(): void
    {
        $request = new RecordPaidOrderRequest(
            storeId: self::VALID_UUID,
            externalOrderId: 'order-100045',
            currency: 'USD',
            subtotalPrice: 99.99,
            totalPrice: 89.99,
        );

        $array = $request->toArray();

        self::assertSame(self::VALID_UUID, $array['store_id']);
        self::assertSame('order-100045', $array['external_order_id']);
        self::assertSame('USD', $array['currency']);
        self::assertSame(99.99, $array['subtotal_price']);
        self::assertSame(89.99, $array['total_price']);
    }

    public function testRecordRefundRequestToArray(): void
    {
        $request = new RecordRefundRequest(
            storeId: self::VALID_UUID,
            externalOrderId: 'order-100045',
            externalRefundId: 'refund-001',
            refundTotal: 25.50,
        );

        $array = $request->toArray();

        self::assertSame(self::VALID_UUID, $array['store_id']);
        self::assertSame('order-100045', $array['external_order_id']);
        self::assertSame('refund-001', $array['external_refund_id']);
        self::assertSame(25.50, $array['refund_total']);
    }

    public function testListCreatorsFilterToArray(): void
    {
        $filter = new ListCreatorsFilter(page: 0, size: 10, status: 'ACTIVE');
        $array = $filter->toArray();

        self::assertSame(0, $array['page']);
        self::assertSame(10, $array['size']);
        self::assertSame('ACTIVE', $array['status']);
    }

    public function testListCreatorsFilterFiltersNulls(): void
    {
        $filter = new ListCreatorsFilter();
        self::assertSame([], $filter->toArray());
    }

    public function testListLinksFilterToArray(): void
    {
        $filter = new ListLinksFilter(page: 1, expired: true);
        $array = $filter->toArray();

        self::assertSame(1, $array['page']);
        self::assertTrue($array['expired']);
        self::assertArrayNotHasKey('status', $array);
    }

    public function testListOrdersFilterToArray(): void
    {
        $filter = new ListOrdersFilter(page: 2, size: 50);
        $array = $filter->toArray();

        self::assertSame(2, $array['page']);
        self::assertSame(50, $array['size']);
    }

    public function testCreateCreatorRequestValidatesOnToArray(): void
    {
        $request = new CreateCreatorRequest(handle: '');

        $this->expectException(RequestValidationError::class);
        $request->toArray();
    }
}
