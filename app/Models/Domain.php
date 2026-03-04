<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use HasFactory;

    /** @use HasFactory<\Database\Factories\DomainFactory> */
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'domain',
        'instituicao',
        'tenant_id',
    ];

    /**
     * Get the tenant that owns the domain.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Tenant, \App\Models\Domain>
     */
    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }
}
