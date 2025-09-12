<?php

namespace Aslnbxrz\SimplePrefix\Tests\Fixtures;

use Aslnbxrz\SimplePrefix\Concerns\HasPrefix;
use Aslnbxrz\SimplePrefix\Contracts\UsesPrefix;
use Illuminate\Database\Eloquent\Model;

class Order extends Model implements UsesPrefix
{
    use HasPrefix;

    protected $table = 'orders';
    protected $guarded = [];
    protected $appends = ['prefix'];

    // Constants auto-read by trait boot
    public const string PREFIX = 'ORD';
    public const array PREFIX_FROM = ['id', 'user_name'];
    public const string PREFIX_SEPARATOR = '-';

    protected function definePrefixVia(): ?string
    {
        return $this->type === 'express' ? 'EXP' : null;
    }
}