<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoiceNumber',
        'bankSlipUrl',
        'invoiceUrl',
        'externalReference',
        'description',
        'status',
        'pixTransaction',
        'canBePaidAfterDueDate',
        'billingType',
        'value',
        'dueDate',
        'paymentCreated'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('description', 'like', '%'.$search.'%')
                    ->orWhere('value', 'like', '%'.$search.'%')
                    ->orWhere('billingType', 'like', '%'.$search.'%');
            });
        });
    }
}
