<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'tenant_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
        'device',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user that performed the activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tenant associated with the activity
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the model that was affected
     */
    public function model()
    {
        if (!$this->model_type || !$this->model_id) {
            return null;
        }

        return $this->model_type::find($this->model_id);
    }

    /**
     * Log an activity
     *
     * @param string $action
     * @param string|null $description
     * @param Model|null $model
     * @param array $properties
     * @return static
     */
    public static function log(
        string $action,
        ?string $description = null,
        ?Model $model = null,
        array $properties = []
    ): static {
        $user = auth()->user();

        return self::create([
            'user_id' => $user?->id,
            'tenant_id' => $user?->tenant_id,
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'device' => self::parseUserAgent(request()->userAgent()),
        ]);
    }

    /**
     * Parse user agent to extract device information
     *
     * @param string|null $userAgent
     * @return string|null
     */
    protected static function parseUserAgent(?string $userAgent): ?string
    {
        if (!$userAgent) {
            return null;
        }

        $browser = 'Unknown';
        $os = 'Unknown';

        // Detect browser
        if (str_contains($userAgent, 'Firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($userAgent, 'Chrome')) {
            $browser = 'Chrome';
        } elseif (str_contains($userAgent, 'Safari')) {
            $browser = 'Safari';
        } elseif (str_contains($userAgent, 'Edge')) {
            $browser = 'Edge';
        } elseif (str_contains($userAgent, 'Opera') || str_contains($userAgent, 'OPR')) {
            $browser = 'Opera';
        }

        // Detect OS
        if (str_contains($userAgent, 'Windows')) {
            $os = 'Windows';
        } elseif (str_contains($userAgent, 'Mac')) {
            $os = 'MacOS';
        } elseif (str_contains($userAgent, 'Linux')) {
            $os = 'Linux';
        } elseif (str_contains($userAgent, 'Android')) {
            $os = 'Android';
        } elseif (str_contains($userAgent, 'iOS') || str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            $os = 'iOS';
        }

        return "$browser on $os";
    }

    /**
     * Get recent activities for a user
     *
     * @param int $userId
     * @param int $days
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getRecentActivities(int $userId, int $days = 30, int $limit = 50)
    {
        return self::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get login history for a user
     *
     * @param int $userId
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLoginHistory(int $userId, int $days = 30)
    {
        return self::where('user_id', $userId)
            ->whereIn('action', ['login', 'logout'])
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Clean old activity logs
     *
     * @param int $days
     * @return int
     */
    public static function cleanOldLogs(int $days = 90): int
    {
        return self::where('created_at', '<', now()->subDays($days))->delete();
    }
}
