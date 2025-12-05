<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'prefix',
        'description',
        'layout_class',
        'css_file',
        'view_path',
        'preview_image',
        'is_active',
        'is_default',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Companies using this template.
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    /**
     * Scope to get only active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get default template.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    /**
     * Get the full path to the CSS file.
     */
    public function getCssFilePathAttribute(): ?string
    {
        if (! $this->css_file) {
            return null;
        }

        return public_path("css/invoice-templates/{$this->css_file}");
    }

    /**
     * Get the preview image URL.
     */
    public function getPreviewImageUrlAttribute(): ?string
    {
        if (! $this->preview_image) {
            return null;
        }

        // Check if it's a full path or just filename
        if (str_starts_with($this->preview_image, 'http') || str_starts_with($this->preview_image, '/')) {
            return $this->preview_image;
        }

        // Check if stored in storage
        if (str_starts_with($this->preview_image, 'invoice-templates/')) {
            return asset("storage/{$this->preview_image}");
        }

        // Default to images directory
        return asset("images/invoice-templates/{$this->preview_image}");
    }

    /**
     * Get the default template.
     */
    public static function getDefault(): ?self
    {
        return static::default()->first() ?? static::active()->first();
    }
}
