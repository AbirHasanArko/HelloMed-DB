<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $action = $request->filled('action') ? '%' . $request->string('action')->toString() . '%' : null;
        $entityType = $request->filled('entity_type') ? $request->string('entity_type')->toString() : null;
        $criticalOnly = $request->boolean('critical_only') ? 1 : 0;
        
        $perPage = 25;
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $offset = ($page - 1) * $perPage;

        $pdo = \Illuminate\Support\Facades\DB::getPdo();
        $stmt = $pdo->prepare('BEGIN pkg_filters.filter_audit_logs(:action, :entity_type, :critical, :limit, :offset, :total, :cursor); END;');
        
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':entity_type', $entityType);
        $stmt->bindParam(':critical', $criticalOnly, \PDO::PARAM_INT);
        $stmt->bindParam(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $stmt->bindParam(':total', $totalCount, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        
        $cursor = null;
        $stmt->bindParam(':cursor', $cursor, \PDO::PARAM_STMT);
        
        $stmt->execute();
        
        oci_execute($cursor);
        $results = [];
        while ($row = oci_fetch_assoc($cursor)) {
            $lowerRow = [];
            foreach ($row as $k => $v) {
                if (is_object($v) && (get_class($v) === 'OCILob' || get_class($v) === 'OCI-Lob' || method_exists($v, 'load'))) {
                    $v = $v->load();
                }
                $lowerRow[strtolower($k)] = $v;
            }
            $results[] = $lowerRow;
        }

        $hydrated = AuditLog::hydrate($results);
        $hydrated->load('actor');

        $logs = new \Illuminate\Pagination\LengthAwarePaginator(
            $hydrated,
            $totalCount,
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $entityTypesResult = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_audit_log_entity_types(:cursor); END;");
        $entityTypes = collect($entityTypesResult)->pluck('entity_type');

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'entityTypes' => $entityTypes,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'audit-logs-'.now()->format('Ymd-His').'.csv';

        $callback = function () use ($request): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Time', 'Actor User ID', 'Action', 'Entity Type', 'Entity ID', 'Old Values', 'New Values', 'Meta', 'IP Address']);

            $action = $request->filled('action') ? '%' . $request->string('action')->toString() . '%' : null;
            $entityType = $request->filled('entity_type') ? $request->string('entity_type')->toString() : null;
            $criticalOnly = $request->boolean('critical_only') ? 1 : 0;
            
            $limit = null; // No limit
            $offset = null; // No offset
            
            $pdo = \Illuminate\Support\Facades\DB::getPdo();
            $stmt = $pdo->prepare('BEGIN pkg_filters.filter_audit_logs(:action, :entity_type, :critical, :limit, :offset, :total, :cursor); END;');
            
            $stmt->bindParam(':action', $action);
            $stmt->bindParam(':entity_type', $entityType);
            $stmt->bindParam(':critical', $criticalOnly, \PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit);
            $stmt->bindParam(':offset', $offset);
            $stmt->bindParam(':total', $totalCount, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
            
            $cursor = null;
            $stmt->bindParam(':cursor', $cursor, \PDO::PARAM_STMT);
            $stmt->execute();
            oci_execute($cursor);
            
            while ($row = oci_fetch_assoc($cursor)) {
                $lowerRow = [];
                foreach ($row as $k => $v) {
                if (is_object($v) && (get_class($v) === 'OCILob' || get_class($v) === 'OCI-Lob' || method_exists($v, 'load'))) {
                    $v = $v->load();
                }
                    $lowerRow[strtolower($k)] = $v;
                }
                
                fputcsv($handle, [
                    $lowerRow['created_at'] ?? '',
                    $lowerRow['actor_user_id'] ?? '',
                    $lowerRow['action'] ?? '',
                    $lowerRow['entity_type'] ?? '',
                    $lowerRow['entity_id'] ?? '',
                    $lowerRow['old_values'] ?? null,
                    $lowerRow['new_values'] ?? null,
                    $lowerRow['meta'] ?? null,
                    $lowerRow['ip_address'] ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
