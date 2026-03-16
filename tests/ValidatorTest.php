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
use Qredex\Internal\Validator;

final class ValidatorTest extends TestCase
{
    private const VALID_UUID = '61abc354-dd8d-4a23-be02-ece77b1b4da6';

    public function testCreateCreatorRequiresHandle(): void
    {
        $this->expectException(RequestValidationError::class);
        Validator::createCreator([]);
    }

    public function testCreateCreatorAllowsOptionalFields(): void
    {
        Validator::createCreator([
            'handle' => 'amelia-rose',
            'display_name' => null,
            'email' => null,
        ]);

        $this->addToAssertionCount(1);
    }

    public function testCreateLinkRequiresStoreId(): void
    {
        $this->expectException(RequestValidationError::class);
        Validator::createLink([
            'creator_id' => self::VALID_UUID,
            'link_name' => 'Summer Sale',
            'destination_path' => '/collections/summer',
        ]);
    }

    public function testCreateLinkRequiresCreatorId(): void
    {
        $this->expectException(RequestValidationError::class);
        Validator::createLink([
            'store_id' => self::VALID_UUID,
            'link_name' => 'Summer Sale',
            'destination_path' => '/collections/summer',
        ]);
    }

    public function testCreateLinkRequiresLinkName(): void
    {
        $this->expectException(RequestValidationError::class);
        Validator::createLink([
            'store_id' => self::VALID_UUID,
            'creator_id' => self::VALID_UUID,
            'link_name' => '',
            'destination_path' => '/collections/summer',
        ]);
    }

    public function testCreateLinkRequiresDestinationPath(): void
    {
        $this->expectException(RequestValidationError::class);
        Validator::createLink([
            'store_id' => self::VALID_UUID,
            'creator_id' => self::VALID_UUID,
            'link_name' => 'Summer Sale',
        ]);
    }

    public function testCreateLinkDestinationPathMustStartWithSlash(): void
    {
        $this->expectException(RequestValidationError::class);
        Validator::createLink([
            'store_id' => self::VALID_UUID,
            'creator_id' => self::VALID_UUID,
            'link_name' => 'Summer Sale',
            'destination_path' => 'products/x',
        ]);
    }

    public function testCreateLinkAttributionWindowMustBeAllowed(): void
    {
        $this->expectException(RequestValidationError::class);
        Validator::createLink([
            'store_id' => self::VALID_UUID,
            'creator_id' => self::VALID_UUID,
            'link_name' => 'Summer Sale',
            'destination_path' => '/collections/summer',
            'attribution_window_days' => 5,
        ]);
    }

    public function testCreateLinkAttributionWindowAcceptsValidValue(): void
    {
        Validator::createLink([
            'store_id' => self::VALID_UUID,
            'creator_id' => self::VALID_UUID,
            'link_name' => 'Summer Sale',
            'destination_path' => '/collections/summer',
            'attribution_window_days' => 7,
        ]);

        $this->addToAssertionCount(1);
    }

    public function testUuidValidationRejectsInvalid(): void
    {
        $this->expectException(RequestValidationError::class);
        Validator::uuid('not-a-uuid', 'test_field');
    }

    public function testUuidValidationAcceptsValid(): void
    {
        Validator::uuid(self::VALID_UUID, 'test_field');
        $this->addToAssertionCount(1);
    }

    public function testIssueIitRequiresLinkId(): void
    {
        $this->expectException(RequestValidationError::class);
        Validator::issueInfluenceIntentToken([]);
    }

    public function testLockPitRequiresToken(): void
    {
        $this->expectException(RequestValidationError::class);
        Validator::lockPurchaseIntent([]);
    }

    public function testRecordPaidOrderRequiresStoreId(): void
    {
        $this->expectException(RequestValidationError::class);
        Validator::recordPaidOrder([
            'external_order_id' => 'order-100045',
            'currency' => 'USD',
        ]);
    }

    public function testRecordPaidOrderRequiresExternalOrderId(): void
    {
        $this->expectException(RequestValidationError::class);
        Validator::recordPaidOrder([
            'store_id' => self::VALID_UUID,
            'currency' => 'USD',
        ]);
    }

    public function testRecordPaidOrderRequiresCurrency(): void
    {
        $this->expectException(RequestValidationError::class);
        Validator::recordPaidOrder([
            'store_id' => self::VALID_UUID,
            'external_order_id' => 'order-100045',
        ]);
    }

    public function testRecordPaidOrderCurrencyMustBe3LetterUppercase(): void
    {
        $this->expectException(RequestValidationError::class);
        Validator::recordPaidOrder([
            'store_id' => self::VALID_UUID,
            'external_order_id' => 'order-100045',
            'currency' => 'usd',
        ]);
    }

    public function testRecordPaidOrderCurrencyAcceptsValid(): void
    {
        Validator::recordPaidOrder([
            'store_id' => self::VALID_UUID,
            'external_order_id' => 'order-100045',
            'currency' => 'USD',
        ]);

        $this->addToAssertionCount(1);
    }

    public function testRecordRefundRequiresStoreId(): void
    {
        $this->expectException(RequestValidationError::class);
        Validator::recordRefund([
            'external_order_id' => 'order-100045',
            'external_refund_id' => 'refund-001',
        ]);
    }

    public function testRecordRefundRequiresExternalOrderId(): void
    {
        $this->expectException(RequestValidationError::class);
        Validator::recordRefund([
            'store_id' => self::VALID_UUID,
            'external_refund_id' => 'refund-001',
        ]);
    }

    public function testRecordRefundRequiresExternalRefundId(): void
    {
        $this->expectException(RequestValidationError::class);
        Validator::recordRefund([
            'store_id' => self::VALID_UUID,
            'external_order_id' => 'order-100045',
        ]);
    }

    public function testListCreatorsValidatesNegativePage(): void
    {
        $this->expectException(RequestValidationError::class);
        Validator::listCreators(['page' => -1]);
    }

    public function testListCreatorsAcceptsNullPage(): void
    {
        Validator::listCreators([]);
        $this->addToAssertionCount(1);
    }

    public function testListLinksExpiredMustBeBoolean(): void
    {
        $this->expectException(RequestValidationError::class);
        Validator::listLinks(['expired' => 'true']);
    }

    public function testListLinksExpiredAcceptsBoolTrue(): void
    {
        Validator::listLinks(['expired' => true]);
        $this->addToAssertionCount(1);
    }
}
