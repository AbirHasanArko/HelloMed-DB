<?php
$files = glob('d:/Documents/HelloMed-DB/oracle_plsql/*.sql');
$all_procs = [];
foreach($files as $f) {
    $content = file_get_contents($f);
    preg_match_all('/PROCEDURE\s+([a-zA-Z0-9_]+)/i', $content, $matches);
    if (!empty($matches[1])) {
        echo "\n" . basename($f) . ":\n";
        $procs = array_unique(array_map('strtolower', $matches[1]));
        foreach($procs as $p) {
            echo "  - $p\n";
            $all_procs[] = $p;
        }
    }
}

echo "\n--- Searching Laravel controllers for usage ---\n";
$all_procs = array_unique($all_procs);
foreach($all_procs as $p) {
    $output = shell_exec("findstr /S /I /C:\"$p\" d:\\Documents\\HelloMed-DB\\hellomed-laravel\\app\\Http\\Controllers\\*.php");
    if (!$output) {
        echo "NOT FOUND IN CONTROLLERS: $p\n";
    }
}
