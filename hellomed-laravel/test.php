<?php print_r(DB::select("SELECT name, type, line, text FROM user_errors WHERE type LIKE '%PACKAGE%'"));
