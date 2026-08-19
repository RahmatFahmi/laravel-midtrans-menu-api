<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    protected $table = 'orders';
    protected $appends = ['time_ago'];

    protected $fillable = [
        'user_id',
        'table_id',
        'status',
        'payment_method',
        'payment_status',
        'total_price',
        'snap_token'
    ];

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function menuItems()
    {
        return $this->belongsToMany(MenuItem::class, 'menu_item_order', 'order_id', 'menu_item_id')
            ->withPivot('harga', 'jumlah', 'potongan', 'total', 'nama', 'image')
            ->withTimestamps();
    }
}
