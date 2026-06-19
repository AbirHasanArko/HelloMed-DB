<?php

$files = [
    'd:\Documents\HelloMed-DB\oracle_plsql\09_pkg_search.sql',
    'd:\Documents\HelloMed-DB\oracle_plsql\10_pkg_filters.sql'
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Split by / on a line by itself
    $parts = preg_split('/^\s*\/\s*$/m', $content);
    foreach ($parts as $part) {
        $sql = trim($part);
        if (!empty($sql)) {
            try {
                DB::unprepared($sql);
                echo "Successfully executed a block from $file\n";
            } catch (\Exception $e) {
                echo "Error executing block: " . $e->getMessage() . "\n";
            }
        }
    }
}
