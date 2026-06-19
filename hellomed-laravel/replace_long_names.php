<?php

$replacements = [
    'update_appointment_payment_status' => 'update_appt_payment_status',
    'UPDATE_APPOINTMENT_PAYMENT_STATUS' => 'UPDATE_APPT_PAYMENT_STATUS',
    'update_appointment_prescription' => 'update_appt_prescription',
    'UPDATE_APPOINTMENT_PRESCRIPTION' => 'UPDATE_APPT_PRESCRIPTION',
    'create_appointment_chat_message' => 'create_appt_chat_message',
    'CREATE_APPOINTMENT_CHAT_MESSAGE' => 'CREATE_APPT_CHAT_MESSAGE',
    'mark_appointment_chat_messages_read' => 'mark_appt_chat_messages_read',
    'MARK_APPOINTMENT_CHAT_MESSAGES_READ' => 'MARK_APPT_CHAT_MESSAGES_READ',
    'get_paginated_patient_appointments' => 'get_paginated_patient_appts',
    'GET_PAGINATED_PATIENT_APPOINTMENTS' => 'GET_PAGINATED_PATIENT_APPTS',
    'get_recent_patient_appointments' => 'get_recent_patient_appts',
    'GET_RECENT_PATIENT_APPOINTMENTS' => 'GET_RECENT_PATIENT_APPTS',
    'get_appointment_prescription_items' => 'get_appt_prescription_items',
    'GET_APPOINTMENT_PRESCRIPTION_ITEMS' => 'GET_APPT_PRESCRIPTION_ITEMS',
    'get_all_active_article_categories' => 'get_active_article_categories',
    'GET_ALL_ACTIVE_ARTICLE_CATEGORIES' => 'GET_ACTIVE_ARTICLE_CATEGORIES',
    'get_future_facility_bookings_by_room_id' => 'get_future_facility_bookings',
    'GET_FUTURE_FACILITY_BOOKINGS_BY_ROOM_ID' => 'GET_FUTURE_FACILITY_BOOKINGS',
    'get_distinct_audit_log_entity_types' => 'get_audit_log_entity_types',
    'GET_DISTINCT_AUDIT_LOG_ENTITY_TYPES' => 'GET_AUDIT_LOG_ENTITY_TYPES',
];

$dirs = [
    'd:/Documents/HelloMed-DB/oracle_plsql',
    'd:/Documents/HelloMed-DB/hellomed-laravel/app/Http/Controllers',
];

function processDir($dir, $replacements) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            processDir($path, $replacements);
        } else {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if ($ext === 'sql' || $ext === 'php') {
                $content = file_get_contents($path);
                $newContent = str_replace(array_keys($replacements), array_values($replacements), $content);
                
                // Fix the syntax error in 14_pkg_crud_reads.sql
                if (basename($path) === '14_pkg_crud_reads.sql') {
                    $newContent = str_replace("    PROCEDURE get_appointment_by_id(\r\n    PROCEDURE get_paginated_patient_appts", "    PROCEDURE get_paginated_patient_appts", $newContent);
                    $newContent = str_replace("    PROCEDURE get_appointment_by_id(\n    PROCEDURE get_paginated_patient_appts", "    PROCEDURE get_paginated_patient_appts", $newContent);
                }

                if ($content !== $newContent) {
                    file_put_contents($path, $newContent);
                    echo "Updated: $path\n";
                }
            }
        }
    }
}

foreach ($dirs as $dir) {
    processDir($dir, $replacements);
}
echo "Done.\n";
