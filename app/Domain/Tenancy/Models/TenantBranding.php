<?php

namespace App\Domain\Tenancy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantBranding extends Model
{
    protected $table = 'tenant_branding';

    protected $fillable = [
        'tenant_id',
        'logo_path',
        'primary_color',
        'secondary_color',
        'accent_color',
        'dark_mode_enabled',
        'custom_css_allowed',
    ];

    protected $casts = [
        'dark_mode_enabled' => 'boolean',
        'custom_css_allowed' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
