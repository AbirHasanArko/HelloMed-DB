<?php
$files = [
    'app/Http/Controllers/Frontend/DoctorController.php',
    'app/Http/Controllers/Frontend/ArticleController.php',
    'app/Http/Controllers/Doctor/DashboardController.php',
    'app/Http/Controllers/Admin/AuditLogController.php'
];

foreach ($files as $f) {
    $c = file_get_contents($f);
    $c = str_replace(
        'foreach ($row as $k => $v) {',
        'foreach ($row as $k => $v) {
                if (is_object($v) && (get_class($v) === \'OCILob\' || get_class($v) === \'OCI-Lob\' || method_exists($v, \'load\'))) {
                    $v = $v->load();
                }',
        $c
    );
    file_put_contents($f, $c);
}

$h = file_get_contents('app/Helpers/OracleHelper.php');
$h = str_replace(
    'if (is_object($v) && get_class($v) == \'OCI-Lob\') {',
    'if (is_object($v) && (get_class($v) === \'OCILob\' || get_class($v) === \'OCI-Lob\' || method_exists($v, \'load\'))) {',
    $h
);
file_put_contents('app/Helpers/OracleHelper.php', $h);
