<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Warranty extends Model
{
    use HasFactory;

    /** Days before expiry at which a warranty starts raising an alert. */
    public const WARNING_DAYS = 30;

    protected $table = 'warranties';

    protected $fillable = [
        'asset_id',
        'vendor_name',
        'warranty_type',
        'start_date',
        'end_date',
        'status',
        'coverage_details',
        'claim_status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Warranty state derived from the expiry date, so an alert never depends on
     * somebody remembering to flip the status by hand.
     */
    public static function deriveStatus(string|\DateTimeInterface|null $endDate, int $warningDays = self::WARNING_DAYS): string
    {
        if (! $endDate) {
            return 'ACTIVE';
        }

        $end = Carbon::parse($endDate)->endOfDay();

        if ($end->isPast()) {
            return 'EXPIRED';
        }

        return $end->lessThanOrEqualTo(now()->addDays($warningDays)) ? 'EXPIRING_SOON' : 'ACTIVE';
    }

    /**
     * Warranties still live but inside the warning window. Driven by the date
     * rather than the stored status, so a report is right even if the nightly
     * sweep has not run yet.
     */
    public function scopeExpiringWithin(Builder $query, int $days = self::WARNING_DAYS): Builder
    {
        return $query->where('status', '!=', 'VOID')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '>=', today())
            ->whereDate('end_date', '<=', today()->addDays($days));
    }

    /** Every warranty whose end date has passed, flagged as expired or not. */
    public function scopeExpiredCover(Builder $query): Builder
    {
        return $query->where('status', '!=', 'VOID')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', today());
    }

    /**
     * The subset the nightly sweep still has work to do on: past the end date
     * but not yet flagged EXPIRED.
     */
    public function scopeLapsed(Builder $query): Builder
    {
        return $query->expiredCover()->where('status', '!=', 'EXPIRED');
    }

    /** Days left before expiry: negative once the warranty has lapsed. */
    public function getDaysRemainingAttribute(): ?int
    {
        return $this->end_date ? today()->diffInDays($this->end_date, false) : null;
    }
}
