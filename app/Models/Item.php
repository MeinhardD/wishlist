<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * the attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'wishlist_id',
    ];

    public function wishlist()
    {
        return $this->belongsTo(Wishlist::class);
    }
}
