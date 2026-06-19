<?php
$errors = DB::select("SELECT line, text FROM user_errors WHERE name IN ('PKG_FILTERS', 'PKG_SEARCH')");
foreach ($errors as $e) {
    echo "Line " . $e->line . ": " . $e->text . "\n";
}
