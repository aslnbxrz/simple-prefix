<?php

namespace Aslnbxrz\SimplePrefix\Tests;

use Aslnbxrz\SimplePrefix\Tests\Fixtures\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HasPrefixTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_builds_prefix_from_constants_and_fields()
    {
        $o = Order::create([
            'user_name' => 'Asliddin',
            'slug' => 'some-slug',
            'type' => null,
        ]);

        // PREFIX = ORD, FROM = ['id','user_name'], SEP='-'
        $this->assertSame("ORD-{$o->id}-Asliddin", $o->prefix);
    }

    /** @test */
    public function define_prefix_via_overrides_static_prefix_when_truthy()
    {
        $o = Order::create([
            'user_name' => 'Bek',
            'type' => 'express', // definePrefixVia => EXP
        ]);

        $this->assertSame("EXP-{$o->id}-Bek", $o->prefix);
    }

    /** @test */
    public function runtime_resolver_has_highest_priority()
    {
        // Runtime resolver: TEST
        Order::resolvePrefixUsing(fn(Order $m) => 'TEST');

        $o = Order::create([
            'user_name' => 'Runtime',
            'type' => 'express', // definePrefixVia => EXP, lekin resolver ustun
        ]);

        $this->assertSame("TEST-{$o->id}-Runtime", $o->prefix);

        // Clean up resolver (optional)
        Order::resolvePrefixUsing(fn(Order $m) => null);
    }

    /** @test */
    public function runtime_setters_change_behavior_on_the_fly()
    {
        Order::setPrefix('TMP');
        Order::setPrefixFrom(['slug']);
        Order::setPrefixSeparator(':');

        $o = Order::create([
            'slug' => 'draft-1',
            'user_name' => 'Ignored',
        ]);

        $this->assertSame("TMP:draft-1", $o->prefix);

        // restore defaults for other tests
        Order::setPrefix('ORD');
        Order::setPrefixFrom(['id', 'user_name']);
        Order::setPrefixSeparator('-');
    }

    /** @test */
    public function empty_or_null_fields_are_skipped()
    {
        $o = Order::create([
            'user_name' => '', // skip
            'type' => null, // no definePrefixVia
        ]);

        $this->assertSame("ORD-{$o->id}", $o->prefix);
    }

    /** @test */
    public function accessor_is_cached_per_instance_invocation()
    {
        $o = Order::create(['user_name' => 'Cache']);

        $first = $o->prefix; // builds and caches
        // change attribute after cache (same instance) – prefix remains cached
        $o->user_name = 'Mutated';
        $second = $o->prefix;

        $this->assertSame($first, $second);
        $this->assertSame("ORD-{$o->id}-Cache", $first);
    }

    /** @test */
    public function unsaved_models_work_with_available_fields()
    {
        $o = new Order([
            'user_name' => 'Draft',
            'slug' => 'draft-slug',
        ]);

        // id null => from = ['id','user_name'] => "ORD--Draft" (id bo'sh)
        $this->assertSame('ORD--Draft', $o->prefix);
    }
}