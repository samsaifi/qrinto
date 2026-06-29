<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDesign extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'product_id',
        'design_data',
        'preview_image_path'
    ];

    protected $casts = [
        'design_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
