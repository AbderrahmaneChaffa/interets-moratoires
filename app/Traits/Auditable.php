<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    /**
     * Log an audit event
     */
    public static function logAudit(
        string $action,
        ?int $modelId = null,
        ?string $field = null,
        $oldValue = null,
        $newValue = null,
        ?string $description = null,
        ?array $metadata = null
    ): void {
        $userId = Auth::id();
        
        $data = [
            'user_id' => $userId,
            'model_type' => static::class,
            'model_id' => $modelId,
            'action' => $action,
            'field' => $field,
            'old_value' => $oldValue !== null ? (is_string($oldValue) ? $oldValue : json_encode($oldValue)) : null,
            'new_value' => $newValue !== null ? (is_string($newValue) ? $newValue : json_encode($newValue)) : null,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ];

        AuditLog::create($data);
    }

    /**
     * Log a change for this model instance
     */
    public function logChange(string $action, ?string $field = null, $oldValue = null, $newValue = null, ?string $description = null, ?array $metadata = null): void
    {
        static::logAudit($action, $this->id, $field, $oldValue, $newValue, $description, $metadata);
    }

    /**
     * Boot the trait
     */
    public static function bootAuditable(): void
    {
        // Log when a model is created
        static::created(function ($model) {
            $model->logChange('created', null, null, null, 
                sprintf('%s créé(e)', class_basename($model)),
                ['attributes' => $model->getAttributes()]
            );
        });

        // Log when a model is updated
        static::updated(function ($model) {
            $changes = $model->getChanges();
            $original = $model->getOriginal();
            
            foreach ($changes as $field => $newValue) {
                if ($field === 'updated_at') {
                    continue; // Skip timestamp fields
                }
                
                $oldValue = $original[$field] ?? null;
                
                $model->logChange('updated', $field, $oldValue, $newValue,
                    sprintf('%s mis(e) à jour: %s', class_basename($model), $field),
                    ['changes' => $changes]
                );
            }
        });

        // Log when a model is deleted
        static::deleted(function ($model) {
            // Get attributes before deletion (they might not be available after)
            $attributes = method_exists($model, 'getAttributes') ? $model->getAttributes() : [];
            $model->logChange('deleted', null, null, null,
                sprintf('%s supprimé(e)', class_basename($model)),
                ['attributes' => $attributes]
            );
        });
    }
}

