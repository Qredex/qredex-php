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
use Qredex\Model\Creator;
use Qredex\Model\InfluenceIntent;
use Qredex\Model\Link;
use Qredex\Model\LinkStats;
use Qredex\Model\OrderAttribution;
use Qredex\Model\OrderAttributionScoreBreakdown;
use Qredex\Model\Page;
use Qredex\Model\PurchaseIntent;
use Qredex\Model\Timing;

final class ModelTest extends TestCase
{
    public function testCreatorFromArray(): void
    {
        $creator = Creator::fromArray([
            'id' => '16fca3f2-b346-4f98-8e52-0895aac61c5b',
            'handle' => 'amelia-rose',
            'status' => 'ACTIVE',
            'display_name' => 'Amelia Rose',
            'email' => 'amelia@example.com',
            'socials' => ['instagram' => '@amelia'],
            'created_at' => '2026-03-15T09:00:00Z',
            'updated_at' => '2026-03-15T10:00:00Z',
        ]);

        self::assertSame('16fca3f2-b346-4f98-8e52-0895aac61c5b', $creator->id);
        self::assertSame('amelia-rose', $creator->handle);
        self::assertSame('ACTIVE', $creator->status);
        self::assertSame('Amelia Rose', $creator->displayName);
        self::assertSame('amelia@example.com', $creator->email);
        self::assertSame(['instagram' => '@amelia'], $creator->socials);
        self::assertSame('2026-03-15T09:00:00Z', $creator->createdAt);
        self::assertSame('2026-03-15T10:00:00Z', $creator->updatedAt);
    }

    public function testCreatorFromArrayHandlesNullables(): void
    {
        $creator = Creator::fromArray([
            'id' => '16fca3f2-b346-4f98-8e52-0895aac61c5b',
            'handle' => 'amelia-rose',
            'status' => 'ACTIVE',
            'display_name' => null,
            'email' => null,
            'socials' => [],
            'created_at' => '2026-03-15T09:00:00Z',
            'updated_at' => '2026-03-15T09:00:00Z',
        ]);

        self::assertNull($creator->displayName);
        self::assertNull($creator->email);
        self::assertNull($creator->linksCount);
        self::assertNull($creator->ordersCount);
        self::assertNull($creator->revenueTotal);
    }

    public function testLinkFromArray(): void
    {
        $link = Link::fromArray([
            'id' => 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
            'merchant_id' => '11111111-2222-3333-4444-555555555555',
            'store_id' => '61abc354-dd8d-4a23-be02-ece77b1b4da6',
            'creator_id' => '16fca3f2-b346-4f98-8e52-0895aac61c5b',
            'link_name' => 'Summer Sale',
            'link_code' => 'SUMMER2026',
            'public_link_url' => 'https://store.example.com/SUMMER2026',
            'destination_path' => '/collections/summer',
            'note' => 'Campaign note',
            'status' => 'ACTIVE',
            'attribution_window_days' => 14,
            'link_expiry_at' => '2026-12-31T23:59:59Z',
            'disabled_at' => null,
            'discount_code' => 'SAVE20',
            'created_at' => '2026-03-15T09:00:00Z',
            'updated_at' => '2026-03-15T10:00:00Z',
        ]);

        self::assertSame('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d', $link->id);
        self::assertSame('Summer Sale', $link->linkName);
        self::assertSame('SUMMER2026', $link->linkCode);
        self::assertSame('/collections/summer', $link->destinationPath);
        self::assertSame(14, $link->attributionWindowDays);
        self::assertSame('SAVE20', $link->discountCode);
        self::assertNull($link->disabledAt);
    }

    public function testInfluenceIntentFromArray(): void
    {
        $iit = InfluenceIntent::fromArray([
            'id' => 'aaa11111-2222-3333-4444-555555555555',
            'merchant_id' => '11111111-2222-3333-4444-555555555555',
            'link_id' => 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
            'token' => 'iit_token_abc123',
            'token_id' => 'tid_abc123',
            'issued_at' => '2026-06-01T12:00:00Z',
            'expires_at' => '2026-06-08T12:00:00Z',
            'status' => 'ACTIVE',
            'integrity_version' => 1,
            'ip_hash' => null,
            'user_agent_hash' => null,
            'referrer' => null,
            'landing_path' => '/collections/summer',
            'timing' => null,
        ]);

        self::assertSame('aaa11111-2222-3333-4444-555555555555', $iit->id);
        self::assertSame('iit_token_abc123', $iit->token);
        self::assertSame('ACTIVE', $iit->status);
        self::assertSame(1, $iit->integrityVersion);
        self::assertNull($iit->timing);
        self::assertSame('/collections/summer', $iit->landingPath);
    }

    public function testPurchaseIntentFromArray(): void
    {
        $pit = PurchaseIntent::fromArray([
            'id' => 'bbb11111-2222-3333-4444-555555555555',
            'merchant_id' => '11111111-2222-3333-4444-555555555555',
            'store_id' => '61abc354-dd8d-4a23-be02-ece77b1b4da6',
            'link_id' => 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
            'influence_intent_id' => 'aaa11111-2222-3333-4444-555555555555',
            'token' => 'pit_token_xyz789',
            'token_id' => 'tid_xyz789',
            'source' => 'checkout',
            'origin_match_status' => 'MATCHED',
            'window_status' => 'WITHIN_WINDOW',
            'attribution_window_days' => 14,
            'attribution_window_days_snapshot' => 14,
            'store_domain_snapshot' => 'store.example.com',
            'link_expiry_at_snapshot' => '2026-12-31T23:59:59Z',
            'discount_code_snapshot' => 'SAVE20',
            'issued_at' => '2026-06-01T12:05:00Z',
            'expires_at' => '2026-06-08T12:05:00Z',
            'locked_at' => '2026-06-01T12:05:00Z',
            'integrity_version' => 1,
            'eligible' => true,
        ]);

        self::assertSame('pit_token_xyz789', $pit->token);
        self::assertTrue($pit->eligible);
        self::assertSame('MATCHED', $pit->originMatchStatus);
        self::assertSame('WITHIN_WINDOW', $pit->windowStatus);
        self::assertSame('store.example.com', $pit->storeDomainSnapshot);
    }

    public function testOrderAttributionFromArray(): void
    {
        $oa = OrderAttribution::fromArray([
            'id' => 'ccc11111-2222-3333-4444-555555555555',
            'merchant_id' => '11111111-2222-3333-4444-555555555555',
            'order_source' => 'DIRECT_API',
            'external_order_id' => 'order-100045',
            'order_number' => '#1045',
            'paid_at' => '2026-06-01T14:00:00Z',
            'currency' => 'USD',
            'subtotal_price' => 99.99,
            'discount_total' => 10.00,
            'total_price' => 89.99,
            'purchase_intent_token' => 'pit_token_xyz789',
            'link_id' => 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
            'link_name' => 'Summer Sale',
            'link_code' => 'SUMMER2026',
            'creator_id' => '16fca3f2-b346-4f98-8e52-0895aac61c5b',
            'creator_handle' => 'amelia-rose',
            'creator_display_name' => 'Amelia Rose',
            'duplicate_suspect' => false,
            'duplicate_confidence' => null,
            'duplicate_reason' => null,
            'duplicate_of_order_attribution_id' => null,
            'attribution_locked_at' => '2026-06-01T14:00:00Z',
            'attribution_window_days' => 14,
            'window_status' => 'WITHIN_WINDOW',
            'token_integrity' => 'VALID',
            'integrity_reason' => 'FULL_MATCH',
            'origin_match_status' => 'MATCHED',
            'integrity_score' => 95,
            'integrity_band' => 'HIGH',
            'review_required' => false,
            'resolution_status' => 'APPROVED',
            'score_breakdown_json' => [
                'score_version' => 1,
                'base_score' => 100,
                'origin_adjustment' => -5,
                'duplicate_adjustment' => 0,
                'final_score' => 95,
                'token_integrity' => 'VALID',
                'integrity_reason' => 'FULL_MATCH',
                'window_status' => 'WITHIN_WINDOW',
                'resolution_status' => 'APPROVED',
                'origin_match_status' => 'MATCHED',
                'duplicate_confidence' => null,
                'review_required' => false,
                'review_reasons' => [],
            ],
            'timeline' => [
                ['event_type' => 'ORDER_CREATED', 'occurred_at' => '2026-06-01T14:00:00Z'],
                ['event_type' => 'ATTRIBUTION_RESOLVED', 'occurred_at' => '2026-06-01T14:00:01Z'],
            ],
            'created_at' => '2026-06-01T14:00:00Z',
            'updated_at' => '2026-06-01T14:00:01Z',
        ]);

        self::assertSame('ccc11111-2222-3333-4444-555555555555', $oa->id);
        self::assertSame('USD', $oa->currency);
        self::assertSame(89.99, $oa->totalPrice);
        self::assertSame('APPROVED', $oa->resolutionStatus);
        self::assertSame('VALID', $oa->tokenIntegrity);
        self::assertSame('FULL_MATCH', $oa->integrityReason);
        self::assertSame(95, $oa->integrityScore);
        self::assertFalse($oa->reviewRequired);
        self::assertCount(2, $oa->timeline);
        self::assertSame('ORDER_CREATED', $oa->timeline[0]->eventType);
        self::assertNotNull($oa->scoreBreakdown);
        self::assertSame(95, $oa->scoreBreakdown->finalScore);
    }

    public function testPageFromArray(): void
    {
        $page = Page::fromArray([
            'items' => [
                [
                    'id' => '16fca3f2-b346-4f98-8e52-0895aac61c5b',
                    'handle' => 'amelia-rose',
                    'status' => 'ACTIVE',
                    'display_name' => null,
                    'email' => null,
                    'socials' => [],
                    'created_at' => '2026-03-15T09:00:00Z',
                    'updated_at' => '2026-03-15T09:00:00Z',
                ],
            ],
            'page' => 0,
            'size' => 25,
            'total_elements' => 1,
            'total_pages' => 1,
        ], Creator::fromArray(...));

        self::assertCount(1, $page->items);
        self::assertSame(0, $page->page);
        self::assertSame(25, $page->size);
        self::assertSame(1, $page->totalElements);
        self::assertSame(1, $page->totalPages);
        self::assertInstanceOf(Creator::class, $page->items[0]);
    }

    public function testPageIsCountableAndIterable(): void
    {
        $page = new Page(
            items: ['a', 'b', 'c'],
            page: 0,
            size: 25,
            totalElements: 3,
            totalPages: 1,
        );

        self::assertCount(3, $page);

        $collected = [];
        foreach ($page as $item) {
            $collected[] = $item;
        }
        self::assertSame(['a', 'b', 'c'], $collected);
    }

    public function testPageCountMatchesItems(): void
    {
        $page = new Page(
            items: ['x', 'y'],
            page: 0,
            size: 10,
            totalElements: 2,
            totalPages: 1,
        );

        self::assertSame(count($page->items), count($page));
    }

    public function testOrderAttributionScoreBreakdownFromArray(): void
    {
        $breakdown = OrderAttributionScoreBreakdown::fromArray([
            'score_version' => 1,
            'base_score' => 100,
            'origin_adjustment' => -5,
            'duplicate_adjustment' => 0,
            'final_score' => 95,
            'token_integrity' => 'VALID',
            'integrity_reason' => 'FULL_MATCH',
            'window_status' => 'WITHIN_WINDOW',
            'resolution_status' => 'APPROVED',
            'origin_match_status' => 'MATCHED',
            'duplicate_confidence' => null,
            'review_required' => true,
            'review_reasons' => ['WINDOW_EDGE', 'DUPLICATE_SUSPECT'],
        ]);

        self::assertSame(1, $breakdown->scoreVersion);
        self::assertSame(100, $breakdown->baseScore);
        self::assertSame(-5, $breakdown->originAdjustment);
        self::assertSame(95, $breakdown->finalScore);
        self::assertSame('APPROVED', $breakdown->resolutionStatus);
        self::assertTrue($breakdown->reviewRequired);
        self::assertSame(['WINDOW_EDGE', 'DUPLICATE_SUSPECT'], $breakdown->reviewReasons);
    }

    public function testTimingFromArray(): void
    {
        $timing = Timing::fromArray([
            'created_at' => '2026-06-01T12:00:00Z',
            'updated_at' => '2026-06-01T12:05:00Z',
        ]);

        self::assertSame('2026-06-01T12:00:00Z', $timing->createdAt);
        self::assertSame('2026-06-01T12:05:00Z', $timing->updatedAt);
    }

    public function testLinkStatsFromArray(): void
    {
        $stats = LinkStats::fromArray([
            'link_id' => 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
            'clicks_count' => 1250,
            'sessions_count' => 980,
            'orders_count' => 42,
            'revenue_total' => 3850.50,
            'token_invalid_count' => 3,
            'token_missing_count' => 7,
            'last_click_at' => '2026-06-15T10:30:00Z',
            'last_order_at' => '2026-06-14T18:00:00Z',
        ]);

        self::assertSame('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d', $stats->linkId);
        self::assertSame(1250, $stats->clicksCount);
        self::assertSame(980, $stats->sessionsCount);
        self::assertSame(42, $stats->ordersCount);
        self::assertSame(3850.50, $stats->revenueTotal);
        self::assertSame(3, $stats->tokenInvalidCount);
        self::assertSame(7, $stats->tokenMissingCount);
        self::assertSame('2026-06-15T10:30:00Z', $stats->lastClickAt);
        self::assertSame('2026-06-14T18:00:00Z', $stats->lastOrderAt);
    }
}
