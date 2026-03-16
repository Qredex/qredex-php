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
use Qredex\Auth\ClientCredentialsAuthentication;
use Qredex\Auth\QredexScope;
use Qredex\Config\QredexConfig;
use Qredex\Http\TransportResponse;
use Qredex\Qredex;
use Qredex\Request\CreateLinkRequest;
use Qredex\Request\IssueInfluenceIntentTokenRequest;
use Qredex\Request\LockPurchaseIntentRequest;
use Qredex\Request\RecordPaidOrderRequest;
use Qredex\Request\RecordRefundRequest;

final class CanonicalFlowTest extends TestCase
{
    public function testCanonicalFlowUsesTypedRequestsAndCanonicalPayloads(): void
    {
        $transport = new FakeTransport();
        $transport->push(new TransportResponse(200, [], json_encode([
            'access_token' => 'token-123',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'scope' => 'direct:links:write direct:intents:write direct:orders:write',
        ], JSON_THROW_ON_ERROR)));
        $transport->push(new TransportResponse(201, [], json_encode([
            'id' => '9f1d8242-3a8b-4867-8f93-eab6412fddab',
            'merchant_id' => 'merchant-1',
            'store_id' => '61abc354-dd8d-4a23-be02-ece77b1b4da6',
            'creator_id' => '16fca3f2-b346-4f98-8e52-0895aac61c5b',
            'link_name' => 'spring-launch',
            'link_code' => 'spring-launch',
            'public_link_url' => 'https://qredex.example/l/spring-launch',
            'destination_path' => '/products/spring-launch',
            'note' => null,
            'status' => 'ACTIVE',
            'attribution_window_days' => 30,
            'link_expiry_at' => null,
            'disabled_at' => null,
            'discount_code' => null,
            'created_at' => '2026-03-15T09:00:00Z',
            'updated_at' => '2026-03-15T09:00:00Z',
        ], JSON_THROW_ON_ERROR)));
        $transport->push(new TransportResponse(201, [], json_encode([
            'id' => '7e0125b7-cb36-43df-8795-34e7a37a9050',
            'merchant_id' => 'merchant-1',
            'link_id' => '9f1d8242-3a8b-4867-8f93-eab6412fddab',
            'token' => 'iit-token',
            'token_id' => '13118c20-e539-4f0a-8155-35f34d9d4d8d',
            'issued_at' => '2026-03-15T09:01:00Z',
            'expires_at' => '2026-03-15T09:31:00Z',
            'status' => 'ISSUED',
            'integrity_version' => 1,
            'ip_hash' => null,
            'user_agent_hash' => null,
            'referrer' => 'https://creator.example/post/123',
            'landing_path' => '/products/spring-launch',
            'timing' => null,
        ], JSON_THROW_ON_ERROR)));
        $transport->push(new TransportResponse(200, [], json_encode([
            'id' => '76f0857d-cb0b-4c34-b749-439f4dbe1402',
            'merchant_id' => 'merchant-1',
            'store_id' => '61abc354-dd8d-4a23-be02-ece77b1b4da6',
            'link_id' => '9f1d8242-3a8b-4867-8f93-eab6412fddab',
            'influence_intent_id' => '7e0125b7-cb36-43df-8795-34e7a37a9050',
            'token' => 'pit-token',
            'token_id' => '54c48525-3327-40d4-bf88-2a7b14486b91',
            'source' => 'backend-cart',
            'origin_match_status' => 'MATCHED',
            'window_status' => 'OPEN',
            'attribution_window_days' => 30,
            'attribution_window_days_snapshot' => 30,
            'store_domain_snapshot' => 'store.example',
            'link_expiry_at_snapshot' => null,
            'discount_code_snapshot' => null,
            'issued_at' => '2026-03-15T09:01:00Z',
            'expires_at' => '2026-03-15T09:31:00Z',
            'locked_at' => '2026-03-15T09:02:00Z',
            'integrity_version' => 1,
            'eligible' => true,
            'timing' => null,
            'created_at' => '2026-03-15T09:01:00Z',
            'updated_at' => '2026-03-15T09:02:00Z',
        ], JSON_THROW_ON_ERROR)));
        $transport->push(new TransportResponse(201, [], json_encode([
            'id' => '53f87935-fc8f-4ff6-91a8-51fc9e7653a7',
            'merchant_id' => 'merchant-1',
            'order_source' => 'DIRECT',
            'external_order_id' => 'order-100045',
            'order_number' => '100045',
            'paid_at' => '2026-03-15T09:03:00Z',
            'currency' => 'USD',
            'subtotal_price' => 100.0,
            'discount_total' => 0.0,
            'total_price' => 110.0,
            'purchase_intent_token' => 'pit-token',
            'link_id' => '9f1d8242-3a8b-4867-8f93-eab6412fddab',
            'link_name' => 'spring-launch',
            'link_code' => 'spring-launch',
            'creator_id' => '16fca3f2-b346-4f98-8e52-0895aac61c5b',
            'creator_handle' => 'amelia-rose',
            'creator_display_name' => 'Amelia Rose',
            'duplicate_suspect' => false,
            'duplicate_confidence' => null,
            'duplicate_reason' => null,
            'duplicate_of_order_attribution_id' => null,
            'attribution_locked_at' => '2026-03-15T09:02:00Z',
            'attribution_window_days' => 30,
            'window_status' => 'OPEN',
            'token_integrity' => 'VALID',
            'integrity_reason' => null,
            'origin_match_status' => 'MATCHED',
            'integrity_score' => 95,
            'integrity_band' => 'HIGH',
            'review_required' => false,
            'resolution_status' => 'ATTRIBUTED',
            'score_breakdown_json' => null,
            'timeline' => [],
            'created_at' => '2026-03-15T09:03:00Z',
            'updated_at' => '2026-03-15T09:03:00Z',
        ], JSON_THROW_ON_ERROR)));
        $transport->push(new TransportResponse(201, [], json_encode([
            'id' => '53f87935-fc8f-4ff6-91a8-51fc9e7653a7',
            'merchant_id' => 'merchant-1',
            'order_source' => 'DIRECT',
            'external_order_id' => 'order-100045',
            'order_number' => '100045',
            'paid_at' => '2026-03-15T09:03:00Z',
            'currency' => 'USD',
            'subtotal_price' => 100.0,
            'discount_total' => 0.0,
            'total_price' => 110.0,
            'purchase_intent_token' => 'pit-token',
            'link_id' => '9f1d8242-3a8b-4867-8f93-eab6412fddab',
            'link_name' => 'spring-launch',
            'link_code' => 'spring-launch',
            'creator_id' => '16fca3f2-b346-4f98-8e52-0895aac61c5b',
            'creator_handle' => 'amelia-rose',
            'creator_display_name' => 'Amelia Rose',
            'duplicate_suspect' => false,
            'duplicate_confidence' => null,
            'duplicate_reason' => null,
            'duplicate_of_order_attribution_id' => null,
            'attribution_locked_at' => '2026-03-15T09:02:00Z',
            'attribution_window_days' => 30,
            'window_status' => 'OPEN',
            'token_integrity' => 'VALID',
            'integrity_reason' => null,
            'origin_match_status' => 'MATCHED',
            'integrity_score' => 95,
            'integrity_band' => 'HIGH',
            'review_required' => false,
            'resolution_status' => 'PARTIALLY_REFUNDED',
            'score_breakdown_json' => null,
            'timeline' => [],
            'created_at' => '2026-03-15T09:03:00Z',
            'updated_at' => '2026-03-15T09:05:00Z',
        ], JSON_THROW_ON_ERROR)));

        $sdk = Qredex::init(new QredexConfig(
            auth: new ClientCredentialsAuthentication(
                clientId: 'client-id',
                clientSecret: 'client-secret',
                scope: [
                    QredexScope::LINKS_WRITE,
                    QredexScope::INTENTS_WRITE,
                    QredexScope::ORDERS_WRITE,
                ],
            ),
            transport: $transport,
        ));

        $link = $sdk->links()->create(new CreateLinkRequest(
            storeId: '61abc354-dd8d-4a23-be02-ece77b1b4da6',
            creatorId: '16fca3f2-b346-4f98-8e52-0895aac61c5b',
            linkName: 'spring-launch',
            destinationPath: '/products/spring-launch',
            attributionWindowDays: 30,
        ));
        $iit = $sdk->intents()->issueInfluenceIntentToken(new IssueInfluenceIntentTokenRequest(
            linkId: $link->id,
            referrer: 'https://creator.example/post/123',
            landingPath: '/products/spring-launch',
        ));
        $pit = $sdk->intents()->lockPurchaseIntent(new LockPurchaseIntentRequest(
            token: $iit->token,
            source: 'backend-cart',
        ));
        $order = $sdk->orders()->recordPaidOrder(new RecordPaidOrderRequest(
            storeId: '61abc354-dd8d-4a23-be02-ece77b1b4da6',
            externalOrderId: 'order-100045',
            currency: 'USD',
            orderNumber: '100045',
            paidAt: '2026-03-15T09:03:00Z',
            subtotalPrice: 100.0,
            discountTotal: 0.0,
            totalPrice: 110.0,
            purchaseIntentToken: $pit->token,
        ));
        $refund = $sdk->refunds()->recordRefund(new RecordRefundRequest(
            storeId: '61abc354-dd8d-4a23-be02-ece77b1b4da6',
            externalOrderId: 'order-100045',
            externalRefundId: 'refund-100045-1',
            refundTotal: 25.0,
            refundedAt: '2026-03-15T09:05:00Z',
        ));

        self::assertSame('spring-launch', $link->linkName);
        self::assertSame('iit-token', $iit->token);
        self::assertSame('pit-token', $pit->token);
        self::assertSame('ATTRIBUTED', $order->resolutionStatus);
        self::assertSame('PARTIALLY_REFUNDED', $refund->resolutionStatus);
        self::assertSame('direct:links:write direct:intents:write direct:orders:write', $transport->requests[0]->body['scope']);

        self::assertSame([
            'store_id' => '61abc354-dd8d-4a23-be02-ece77b1b4da6',
            'creator_id' => '16fca3f2-b346-4f98-8e52-0895aac61c5b',
            'link_name' => 'spring-launch',
            'destination_path' => '/products/spring-launch',
            'attribution_window_days' => 30,
        ], $transport->requests[1]->body);
        self::assertSame([
            'token' => 'iit-token',
            'source' => 'backend-cart',
        ], $transport->requests[3]->body);
        self::assertSame([
            'store_id' => '61abc354-dd8d-4a23-be02-ece77b1b4da6',
            'external_order_id' => 'order-100045',
            'external_refund_id' => 'refund-100045-1',
            'refund_total' => 25.0,
            'refunded_at' => '2026-03-15T09:05:00Z',
        ], $transport->requests[5]->body);
    }
}
