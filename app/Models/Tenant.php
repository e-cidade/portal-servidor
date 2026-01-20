<?php

namespace App\Models;

use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use HasDatabase, HasDomains;

    // Ensure JSON data attribute is cast to array when retrieved
    protected $casts = [
        'data' => 'array',
    ];
}
