<?php
namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class OracleHelper
{
    public static $lastOutParams = [];

    /**
     * Executes a PL/SQL procedure that returns a single SYS_REFCURSOR 
     * and maps it to a collection of Eloquent models.
     */
    public static function fetchCursor($procedure, $bindings = [], $modelClass = null)
    {
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare($procedure);
        
        foreach ($bindings as $key => &$value) {
            if (str_starts_with($key, 'out_')) {
                $bindKey = ':' . substr($key, 4);
                $stmt->bindParam($bindKey, $value, \PDO::PARAM_STR, 4000);
            } else {
                $bindKey = str_starts_with($key, ':') ? $key : ':' . $key;
                $stmt->bindParam($bindKey, $value);
            }
        }
        
        $cursor = null;
        $stmt->bindParam(':cursor', $cursor, \PDO::PARAM_STMT);
        $stmt->execute();
        oci_execute($cursor);
        
        self::$lastOutParams = $bindings;
        
        $results = [];
        while ($row = oci_fetch_assoc($cursor)) {
            // Convert Oracle UPPERCASE keys to lowercase
            $lowercaseRow = [];
            foreach ($row as $k => $v) {
                // If it's a LOB/CLOB, read the contents
                if (is_object($v) && (get_class($v) === 'OCILob' || get_class($v) === 'OCI-Lob' || method_exists($v, 'load'))) {
                    $v = $v->load();
                }
                $lowercaseRow[strtolower($k)] = $v;
            }
            
            if ($modelClass) {
                $model = new $modelClass();
                // Set raw attributes directly to bypass mass-assignment issues
                $model->setRawAttributes($lowercaseRow, true);
                $model->exists = true; // Mark as existing in DB
                $results[] = $model;
            } else {
                $results[] = (object) $lowercaseRow;
            }
        }
        
        return collect($results);
    }
    
    /**
     * Executes a PL/SQL procedure for DML (INSERT/UPDATE/DELETE)
     * To use OUT parameters, prefix the key with 'out_' and pass by reference.
     */
    public static function executeProcedure($procedure, $bindings = [])
    {
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare($procedure);
        
        foreach ($bindings as $key => &$value) {
            if (str_starts_with($key, 'out_')) {
                // It's an OUT parameter, strip the 'out_' prefix for binding
                $bindKey = ':' . substr($key, 4);
                $stmt->bindParam($bindKey, $value, \PDO::PARAM_STR, 4000);
            } else {
                $stmt->bindParam(':' . $key, $value);
            }
        }
        
        $stmt->execute();
        return $bindings;
    }
}
