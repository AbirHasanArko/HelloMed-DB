<?php
$files = glob('d:/Documents/HelloMed-DB/oracle_plsql/*.sql');
$proc = 0; $func = 0; $curs = 0; $loop = 0;

foreach($files as $f) {
    $content = strtolower(file_get_contents($f));
    $proc += substr_count($content, 'procedure ');
    $func += substr_count($content, 'function ');
    $curs += substr_count($content, 'sys_refcursor');
    $loop += substr_count($content, 'loop');
}
// since procedures are declared in spec and body, divide by 2 for the approximate number
echo "Total occurrences (spec + body):\n";
echo "Procedures: $proc\n";
echo "Functions: $func\n";
echo "Cursors: $curs\n";
echo "Loops: $loop\n";
