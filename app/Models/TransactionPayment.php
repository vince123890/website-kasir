<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionPayment extends Model
{
    protected $fillable = [
        'transaction_id',
        'payment_method',
        'amount',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function isCash(): bool
    {
        return $this->payment_method === 'cash';
    }

    public function isCard(): bool
    {
        return $this->payment_method === 'card';
    }

    public function isTransfer(): bool
    {
        return $this->payment_method === 'transfer';
    }

    public function isEwallet(): bool
    {
        return $this->payment_method === 'ewallet';
    }
}
