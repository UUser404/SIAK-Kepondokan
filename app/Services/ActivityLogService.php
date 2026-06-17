<?php
// ============================================================
// ActivityLogService.php
// ============================================================
namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    public static function log(
        string $action,
        ?Model $model = null,
        array $oldValues = [],
        array $newValues = []
    ): void {
        try {
            ActivityLog::create([
                'user_id'    => Auth::id(),
                'action'     => $action,
                'model_type' => $model ? get_class($model) : null,
                'model_id'   => $model?->getKey(),
                'old_values' => !empty($oldValues) ? $oldValues : null,
                'new_values' => !empty($newValues) ? $newValues : null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Exception $e) {
            // Jangan hentikan proses utama karena gagal log
            \Log::warning('ActivityLog gagal: ' . $e->getMessage());
        }
    }

    public static function logCreate(Model $model): void
    {
        $modelName = class_basename($model);
        self::log(
            strtolower($modelName) . '.created',
            $model,
            [],
            $model->toArray()
        );
    }

    public static function logUpdate(Model $model, array $oldValues): void
    {
        $modelName = class_basename($model);
        self::log(
            strtolower($modelName) . '.updated',
            $model,
            $oldValues,
            $model->getChanges()
        );
    }

    public static function logDelete(Model $model): void
    {
        $modelName = class_basename($model);
        self::log(
            strtolower($modelName) . '.deleted',
            $model,
            $model->toArray(),
            []
        );
    }
}