<?php

namespace Aslnbxrz\SimplePrefix\Tests;

use Aslnbxrz\SimplePrefix\Tests\Fixtures\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HasPrefixTest extends TestCase
{
    use RefreshDatabase;

    public function test_builds_prefix_from_constants_and_fields(): void
    {
        $o = Order::create([
            'user_name' => 'Asliddin',
            'slug' => 'some-slug',
            'type' => null,
        ]);

        self::assertSame("ORD-{$o->id}-Asliddin", $o->prefix);
    }

    public function test_define_prefix_via_overrides_static_prefix_when_truthy(): void
    {
        $o = Order::create([
            'user_name' => 'Bek',
            'type' => 'express',
        ]);

        self::assertSame("EXP-{$o->id}-Bek", $o->prefix);
    }

    public function test_runtime_resolver_has_highest_priority(): void
    {
        Order::resolvePrefixUsing(fn(Order $m) => 'TEST');

        $o = Order::create([
            'user_name' => 'Runtime',
            'type' => 'express',
        ]);

        self::assertSame("TEST-{$o->id}-Runtime", $o->prefix);

        // clean
        Order::resolvePrefixUsing(fn(Order $m) => null);
    }

    public function test_runtime_setters_change_behavior_on_the_fly(): void
    {
        Order::setPrefix('TMP');
        Order::setPrefixFrom(['slug']);
        Order::setPrefixSeparator(':');

        $o = Order::create([
            'slug' => 'draft-1',
            'user_name' => 'Ignored',
        ]);

        self::assertSame("TMP:draft-1", $o->prefix);

        // restore
        Order::setPrefix('ORD');
        Order::setPrefixFrom(['id', 'user_name']);
        Order::setPrefixSeparator('-');
    }

    public function test_empty_or_null_fields_are_skipped(): void
    {
        $o = Order::create([
            'user_name' => '',
            'type' => null,
        ]);

        self::assertSame("ORD-{$o->id}", $o->prefix);
    }

    public function test_accessor_is_cached_per_instance_invocation(): void
    {
        $o = Order::create(['user_name' => 'Cache']);

        $first = $o->prefix;
        $o->user_name = 'Mutated';
        $second = $o->prefix;

        self::assertSame($first, $second);
        self::assertSame("ORD-{$o->id}-Cache", $first);
    }

    public function test_unsaved_models_work_with_available_fields(): void
    {
        $o = new Order([
            'user_name' => 'Draft',
            'slug' => 'draft-slug',
        ]);

        self::assertSame('ORD--Draft', $o->prefix);
    }
}