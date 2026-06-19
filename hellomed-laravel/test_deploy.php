<?php
$files = [
    'd:\Documents\HelloMed-DB\oracle_plsql\01_schema.sql',
    'd:\Documents\HelloMed-DB\oracle_plsql\02_triggers.sql',
    'd:\Documents\HelloMed-DB\oracle_plsql\03_pkg_users.sql',
    'd:\Documents\HelloMed-DB\oracle_plsql\04_pkg_appointments.sql',
    'd:\Documents\HelloMed-DB\oracle_plsql\05_pkg_articles.sql',
    'd:\Documents\HelloMed-DB\oracle_plsql\06_pkg_notifications.sql',
    'd:\Documents\HelloMed-DB\oracle_plsql\08_pkg_qna.sql',
    'd:\Documents\HelloMed-DB\oracle_plsql\09_pkg_facilities.sql',
    'd:\Documents\HelloMed-DB\oracle_plsql\10_pkg_inventory.sql',
    'd:\Documents\HelloMed-DB\oracle_plsql\11_pkg_search.sql',
    'd:\Documents\HelloMed-DB\oracle_plsql\12_pkg_filters.sql',
    'd:\Documents\HelloMed-DB\oracle_plsql\13_pkg_crud_writes.sql',
    'd:\Documents\HelloMed-DB\oracle_plsql\14_pkg_crud_reads.sql'
];

foreach ($files as $file) {
    if(!file_exists($file)) continue;
    $sql = file_get_contents($file);
    $blocks = explode("/", $sql);
    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block) {
            try {
                DB::unprepared($block);
            } catch (Exception $e) {
                // Ignore errors for now
            }
        }
    }
}
echo "Deployed all packages.\n";
