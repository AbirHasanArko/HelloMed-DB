<?php
$cursors = DB::selectOne("SELECT COUNT(*) as cnt FROM user_source WHERE UPPER(text) LIKE '%SYS_REFCURSOR%' OR UPPER(text) LIKE '%CURSOR %'");
$loops = DB::selectOne("SELECT COUNT(*) as cnt FROM user_source WHERE UPPER(text) LIKE '%LOOP%'");
echo "Cursors: " . $cursors->cnt . "\n";
echo "Loops: " . $loops->cnt . "\n";

