<?php

namespace App\Models;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Favorite;
use App\Models\Rating;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class MenuItem extends Model
{
    protected $guarded = ['id'];
    protected $appends = ['image_url'];


    public function scopeSearch($query, $term)
    {
        return $query->when($term, function ($query, $term) {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhereHas('category', function ($q2) use ($term) {
                        $q2->where('name', 'like', "%{$term}%");
                    });
            });
        });
    }

    public function getImageUrlAttribute()
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return asset('storage/' . $this->image);
        }
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function discounts()
    {
        return $this->hasMany(Discount::class);
    }
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('value') ?? 0;
    }
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'menu_item_order', 'menu_item_id', 'order_id')
            ->withPivot('harga', 'jumlah', 'potongan', 'total', 'nama', 'image')
            ->withTimestamps();
    }
    public function discount()
    {
        return $this->hasOne(Discount::class, 'menu_item_id');
    }
}
