<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity',
        'grade',
        'harga_aktual',
        'notes',
    ];

    protected $casts = [
        'harga_aktual' => 'decimal:2',
    ];

    /**
     * Get the product associated with the transaction.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who made the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the financial ledger record associated with the transaction.
     */
    public function financial(): HasOne
    {
        return $this->hasOne(Financial::class);
    }
}
