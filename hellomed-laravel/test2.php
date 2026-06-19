<?php

$files = [
    'd:\Documents\HelloMed-DB\oracle_plsql\11_pkg_search.sql',
    'd:\Documents\HelloMed-DB\oracle_plsql\12_pkg_filters.sql',
    'd:\Documents\HelloMed-DB\oracle_plsql\13_pkg_crud_writes.sql',
    'd:\Documents\HelloMed-DB\oracle_plsql\14_pkg_crud_reads.sql'
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
