<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kiosk extends Model
{
    use HasFactory;

    protected $table = 'kiosks';

    protected $fillable = [
        'store_id',
        'product_id',
        'kiosk',
        'file_path',
        'quantity',
        'price',
        'total_amount',
        'currency',
        'printer_tray',
        'print_size',
    ];

    protected function casts(): array
    {
        return [
            'kiosk' => 'boolean',
            'quantity' => 'integer',
            'price' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
