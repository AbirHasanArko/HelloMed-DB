<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function log(string $action, Model $entity, array $oldValues = [], array $newValues = [], array $meta = []): void
    {
        $actor = Auth::user();

        $bindings = [
            'p_actor_user_id' => $actor?->id,
            'p_action' => $action,
            'p_entity_type' => class_basename($entity),
            'p_entity_id' => $entity->getKey(),
            'p_old_values' => $oldValues === [] ? null : json_encode($oldValues),
            'p_new_values' => $newValues === [] ? null : json_encode($newValues),
            'p_meta' => $meta === [] ? null : json_encode($meta),
            'p_ip_address' => request()?->ip(),
            'p_user_agent' => request()?->userAgent(),
        ];

        \App\Helpers\OracleHelper::executeProcedure(
            'BEGIN pkg_crud_writes.create_audit_log(:p_actor_user_id, :p_action, :p_entity_type, :p_entity_id, :p_old_values, :p_new_values, :p_meta, :p_ip_address, :p_user_agent); END;',
            $bindings
        );
    }
}
