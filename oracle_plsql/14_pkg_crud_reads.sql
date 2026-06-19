-- ==========================================
-- 14_pkg_crud_reads.sql
-- ==========================================
CREATE OR REPLACE PACKAGE pkg_crud_reads AS
    PROCEDURE get_active_departments(p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_active_categories(p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_active_facility_rooms(p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_all_active_doctors(p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_ambulance_requests(p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_ambulance_request_by_id(p_id IN NUMBER, p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_paginated_medicine_orders(p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_medicine_order_by_id(p_id IN NUMBER, p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_medicine_order_items(p_order_id IN NUMBER, p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_medicine_by_id(p_id IN NUMBER, p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_all_active_medicines(p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_paginated_medicines(p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_paginated_qna_questions(p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_qna_question_by_id(p_id IN NUMBER, p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_qna_answers(p_question_id IN NUMBER, p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_admin_staff_users(p_cursor OUT SYS_REFCURSOR);
    
    PROCEDURE get_homepage_data(
        p_dept_cursor OUT SYS_REFCURSOR,
        p_doc_cursor OUT SYS_REFCURSOR,
        p_art_cursor OUT SYS_REFCURSOR,
        p_patient_count OUT NUMBER,
        p_dept_count OUT NUMBER,
        p_doc_count OUT NUMBER
    );

    PROCEDURE get_user_by_email(
        p_email IN VARCHAR2,
        p_cursor OUT SYS_REFCURSOR
    );
    
    PROCEDURE get_user_by_id(
        p_id IN NUMBER,
        p_cursor OUT SYS_REFCURSOR
    );
    
    PROCEDURE get_medicines_by_ids(
        p_ids IN VARCHAR2,
        p_cursor OUT SYS_REFCURSOR
    );

    PROCEDURE get_prescription_cart_items(
        p_appointment_id IN NUMBER,
        p_cursor OUT SYS_REFCURSOR
    );

    PROCEDURE get_order_cart_items(
        p_order_id IN NUMBER,
        p_cursor OUT SYS_REFCURSOR
    );
    
    PROCEDURE get_doctor_by_id(
        p_doctor_id IN NUMBER,
        p_cursor OUT SYS_REFCURSOR
    );

    PROCEDURE get_paginated_patient_appts(p_user_id IN NUMBER, p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_appointment_payments(p_appointment_id IN NUMBER, p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_appointment_by_id(
        p_appointment_id IN NUMBER,
        p_cursor OUT SYS_REFCURSOR
    );
    
    PROCEDURE get_paginated_doctors(p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR);
    
    PROCEDURE get_paginated_all_appointments(p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR);

    PROCEDURE get_recent_patient_appts(
        p_user_id IN NUMBER,
        p_limit IN NUMBER,
        p_cursor OUT SYS_REFCURSOR
    );

    PROCEDURE get_appt_prescription_items(p_appointment_id IN NUMBER, p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_appointment_chat_messages(p_appointment_id IN NUMBER, p_cursor OUT SYS_REFCURSOR);
    PROCEDURE check_slot_availability(p_doctor_id IN NUMBER, p_scheduled_for IN TIMESTAMP, p_exclude_id IN NUMBER, p_count OUT NUMBER);
    PROCEDURE get_doctor_calendar_summary(p_doctor_id IN NUMBER, p_cursor OUT SYS_REFCURSOR);
    
    PROCEDURE get_article_by_id(p_id IN NUMBER, p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_article_comments(p_article_id IN NUMBER, p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_paginated_doctor_articles(p_user_id IN NUMBER, p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_paginated_admin_articles(p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_active_article_categories(p_cursor OUT SYS_REFCURSOR);
    
    PROCEDURE get_paginated_payments(p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_payment_by_id(p_id IN NUMBER, p_cursor OUT SYS_REFCURSOR);

    PROCEDURE get_department_by_id(
        p_department_id IN NUMBER,
        p_cursor OUT SYS_REFCURSOR
    );
    
    PROCEDURE get_paginated_departments(p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR);
    
    PROCEDURE get_paginated_facility_rooms(p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_facility_room_by_id(p_id IN NUMBER, p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_all_active_facility_rooms(p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_all_facility_rooms(p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_future_facility_bookings(p_room_id IN NUMBER, p_cursor OUT SYS_REFCURSOR);
    
    PROCEDURE get_paginated_inventory_items(p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR);
    PROCEDURE get_inventory_item_by_id(p_id IN NUMBER, p_cursor OUT SYS_REFCURSOR);
    
    PROCEDURE get_admin_reports(
        p_start_date IN TIMESTAMP,
        p_end_date IN TIMESTAMP,
        p_total_appointments OUT NUMBER,
        p_total_revenue OUT NUMBER,
        p_medicine_sales OUT NUMBER
    );
    
    PROCEDURE get_doctor_reports(
        p_doctor_id IN NUMBER,
        p_start_date IN TIMESTAMP,
        p_end_date IN TIMESTAMP,
        p_total_appointments OUT NUMBER,
        p_completed_appointments OUT NUMBER
    );
    
    PROCEDURE get_audit_log_entity_types(p_cursor OUT SYS_REFCURSOR);
    
    PROCEDURE get_staff_dashboard_stats(
        p_pending_appointments OUT NUMBER,
        p_today_appointments OUT NUMBER,
        p_doctor_count OUT NUMBER,
        p_published_articles OUT NUMBER,
        p_pending_ambulance OUT NUMBER
    );
    
    PROCEDURE get_queue_appointments_by_date(
        p_date IN VARCHAR2,
        p_cursor OUT SYS_REFCURSOR
    );
END pkg_crud_reads;
/

CREATE OR REPLACE PACKAGE BODY pkg_crud_reads AS

    PROCEDURE get_active_departments(p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM departments 
            WHERE is_active = 1 
            ORDER BY name;
    END get_active_departments;

    PROCEDURE get_active_categories(p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM article_categories 
            WHERE is_active = 1 
            ORDER BY name;
    END get_active_categories;

    PROCEDURE get_active_facility_rooms(p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM facility_rooms 
            WHERE is_active = 1 
            ORDER BY room_number;
    END get_active_facility_rooms;

    PROCEDURE get_all_active_doctors(p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM doctors 
            WHERE is_active = 1 
            ORDER BY name;
    END get_all_active_doctors;

    PROCEDURE get_ambulance_requests(p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM ambulance_requests
            ORDER BY 
                CASE status
                    WHEN 'pending' THEN 1
                    WHEN 'dispatched' THEN 2
                    WHEN 'resolved' THEN 3
                    WHEN 'cancelled' THEN 4
                    ELSE 5
                END ASC,
                created_at DESC;
    END get_ambulance_requests;

    PROCEDURE get_ambulance_request_by_id(p_id IN NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM ambulance_requests WHERE id = p_id;
    END get_ambulance_request_by_id;

    PROCEDURE get_paginated_medicine_orders(p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        SELECT COUNT(*) INTO p_total FROM medicine_orders;
        OPEN p_cursor FOR
            SELECT * FROM (
                SELECT a.*, ROWNUM rnum FROM (
                    SELECT * FROM medicine_orders ORDER BY created_at DESC
                ) a WHERE ROWNUM <= p_offset + p_limit
            ) WHERE rnum > p_offset;
    END get_paginated_medicine_orders;

    PROCEDURE get_medicine_order_by_id(p_id IN NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM medicine_orders WHERE id = p_id;
    END get_medicine_order_by_id;

    PROCEDURE get_medicine_order_items(p_order_id IN NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM medicine_order_items WHERE medicine_order_id = p_order_id;
    END get_medicine_order_items;

    PROCEDURE get_medicine_by_id(p_id IN NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM medicines WHERE id = p_id;
    END get_medicine_by_id;

    PROCEDURE get_all_active_medicines(p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT id, name, power, amount FROM medicines WHERE is_active = 1 ORDER BY name;
    END get_all_active_medicines;

    PROCEDURE get_paginated_medicines(p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        SELECT COUNT(*) INTO p_total FROM medicines;
        OPEN p_cursor FOR
            SELECT * FROM (
                SELECT a.*, ROWNUM rnum FROM (
                    SELECT * FROM medicines ORDER BY created_at DESC
                ) a WHERE ROWNUM <= p_offset + p_limit
            ) WHERE rnum > p_offset;
    END get_paginated_medicines;

    PROCEDURE get_paginated_qna_questions(p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        SELECT COUNT(*) INTO p_total FROM qna_questions;
        OPEN p_cursor FOR
            SELECT * FROM (
                SELECT a.*, ROWNUM rnum FROM (
                    SELECT * FROM qna_questions ORDER BY created_at DESC
                ) a WHERE ROWNUM <= p_offset + p_limit
            ) WHERE rnum > p_offset;
    END get_paginated_qna_questions;

    PROCEDURE get_qna_question_by_id(p_id IN NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM qna_questions WHERE id = p_id;
    END get_qna_question_by_id;

    PROCEDURE get_qna_answers(p_question_id IN NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM qna_answers WHERE qna_question_id = p_question_id ORDER BY created_at ASC;
    END get_qna_answers;

    PROCEDURE get_admin_staff_users(p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM users WHERE role IN ('admin', 'staff');
    END get_admin_staff_users;

    PROCEDURE get_homepage_data(
        p_dept_cursor OUT SYS_REFCURSOR,
        p_doc_cursor OUT SYS_REFCURSOR,
        p_art_cursor OUT SYS_REFCURSOR,
        p_patient_count OUT NUMBER,
        p_dept_count OUT NUMBER,
        p_doc_count OUT NUMBER
    ) IS
    BEGIN
        SELECT COUNT(*) INTO p_patient_count FROM users WHERE role = 'patient';
        SELECT COUNT(*) INTO p_dept_count FROM departments WHERE is_active = 1;
        SELECT COUNT(*) INTO p_doc_count FROM doctors WHERE is_active = 1;

        OPEN p_dept_cursor FOR
            SELECT * FROM (
                SELECT * FROM departments 
                WHERE is_active = 1 
                ORDER BY is_featured DESC, featured_order ASC, created_at DESC
            ) WHERE ROWNUM <= 6;

        OPEN p_doc_cursor FOR
            SELECT * FROM (
                SELECT d.*, u.name as user_name, dept.name as department_name 
                FROM doctors d
                JOIN users u ON d.id = u.id
                JOIN departments dept ON d.department_id = dept.id
                WHERE d.is_active = 1 
                ORDER BY d.is_featured DESC, d.featured_order ASC, d.created_at DESC
            ) WHERE ROWNUM <= 8;

        OPEN p_art_cursor FOR
            SELECT * FROM (
                SELECT a.*, ac.name as category_name, u.name as author_name 
                FROM articles a
                JOIN article_categories ac ON a.article_category_id = ac.id
                JOIN users u ON a.user_id = u.id
                WHERE a.is_published = 1 
                ORDER BY a.is_featured DESC, a.featured_order ASC, a.published_at DESC
            ) WHERE ROWNUM <= 3;
    END get_homepage_data;

    PROCEDURE get_user_by_email(
        p_email IN VARCHAR2,
        p_cursor OUT SYS_REFCURSOR
    ) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM users WHERE email = p_email;
    END get_user_by_email;

    PROCEDURE get_medicines_by_ids(
        p_ids IN VARCHAR2,
        p_cursor OUT SYS_REFCURSOR
    ) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM medicines 
            WHERE ',' || p_ids || ',' LIKE '%,' || id || ',%';
    END get_medicines_by_ids;

    PROCEDURE get_prescription_cart_items(
        p_appointment_id IN NUMBER,
        p_cursor OUT SYS_REFCURSOR
    ) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT pi.*, m.name as medicine_name, m.price as medicine_price 
            FROM appointment_prescription_items pi
            JOIN medicines m ON pi.medicine_id = m.id
            WHERE pi.appointment_id = p_appointment_id;
    END get_prescription_cart_items;

    PROCEDURE get_order_cart_items(
        p_order_id IN NUMBER,
        p_cursor OUT SYS_REFCURSOR
    ) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT oi.*, m.name as medicine_name, m.price as medicine_price, m.image_path
            FROM medicine_order_items oi
            JOIN medicines m ON oi.medicine_id = m.id
            WHERE oi.medicine_order_id = p_order_id;
    END get_order_cart_items;

    PROCEDURE get_doctor_by_id(
        p_doctor_id IN NUMBER,
        p_cursor OUT SYS_REFCURSOR
    ) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT d.*, u.name as user_name, u.email, dept.name as department_name 
            FROM doctors d
            JOIN users u ON d.id = u.id
            JOIN departments dept ON d.department_id = dept.id
            WHERE d.id = p_doctor_id;
    END get_doctor_by_id;

    PROCEDURE get_paginated_patient_appts(p_user_id IN NUMBER, p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        SELECT COUNT(*) INTO p_total FROM appointments WHERE user_id = p_user_id;
        OPEN p_cursor FOR
            SELECT * FROM (
                SELECT a.*, ROWNUM rnum FROM (
                    SELECT * FROM appointments WHERE user_id = p_user_id ORDER BY scheduled_for DESC
                ) a WHERE ROWNUM <= p_offset + p_limit
            ) WHERE rnum > p_offset;
    END get_paginated_patient_appts;

    PROCEDURE get_appointment_payments(p_appointment_id IN NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM payments WHERE appointment_id = p_appointment_id;
    END get_appointment_payments;

    PROCEDURE get_appointment_by_id(
        p_appointment_id IN NUMBER,
        p_cursor OUT SYS_REFCURSOR
    ) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT a.*, 
                   d.id as doctor_id, du.name as doctor_name, 
                   dept.name as department_name, s.name as service_name
            FROM appointments a
            JOIN doctors d ON a.doctor_id = d.id
            JOIN users du ON d.id = du.id
            JOIN departments dept ON a.department_id = dept.id
            LEFT JOIN services s ON a.service_id = s.id
            WHERE a.id = p_appointment_id;
    END get_appointment_by_id;

    PROCEDURE get_recent_patient_appts(
        p_user_id IN NUMBER,
        p_limit IN NUMBER,
        p_cursor OUT SYS_REFCURSOR
    ) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM (
                SELECT * FROM appointments
                WHERE user_id = p_user_id
                ORDER BY scheduled_for DESC
            ) WHERE ROWNUM <= p_limit;
    END get_recent_patient_appts;

    PROCEDURE get_paginated_all_appointments(p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        SELECT COUNT(*) INTO p_total FROM appointments;
        OPEN p_cursor FOR
            SELECT * FROM (
                SELECT a.*, ROWNUM rnum FROM (
                    SELECT * FROM appointments ORDER BY created_at DESC
                ) a WHERE ROWNUM <= p_offset + p_limit
            ) WHERE rnum > p_offset;
    END get_paginated_all_appointments;

    PROCEDURE get_paginated_doctors(p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        SELECT COUNT(*) INTO p_total FROM doctors;
        OPEN p_cursor FOR
            SELECT * FROM (
                SELECT a.*, ROWNUM rnum FROM (
                    SELECT * FROM doctors ORDER BY created_at DESC
                ) a WHERE ROWNUM <= p_offset + p_limit
            ) WHERE rnum > p_offset;
    END get_paginated_doctors;

    PROCEDURE get_appt_prescription_items(p_appointment_id IN NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM appointment_prescription_items WHERE appointment_id = p_appointment_id;
    END get_appt_prescription_items;

    PROCEDURE get_appointment_chat_messages(p_appointment_id IN NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM appointment_chat_messages WHERE appointment_id = p_appointment_id ORDER BY created_at ASC;
    END get_appointment_chat_messages;

    PROCEDURE check_slot_availability(p_doctor_id IN NUMBER, p_scheduled_for IN TIMESTAMP, p_exclude_id IN NUMBER, p_count OUT NUMBER) IS
    BEGIN
        SELECT COUNT(*) INTO p_count FROM appointments
        WHERE doctor_id = p_doctor_id
        AND id != p_exclude_id
        AND scheduled_for = p_scheduled_for
        AND status IN ('pending', 'confirmed');
    END check_slot_availability;

    PROCEDURE get_doctor_calendar_summary(p_doctor_id IN NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT TO_CHAR(scheduled_for, 'YYYY-MM-DD') as appointment_date, COUNT(*) as total
            FROM appointments
            WHERE doctor_id = p_doctor_id
              AND scheduled_for >= TRUNC(SYSDATE)
              AND scheduled_for < TRUNC(SYSDATE) + 31
            GROUP BY TO_CHAR(scheduled_for, 'YYYY-MM-DD')
            ORDER BY TO_CHAR(scheduled_for, 'YYYY-MM-DD');
    END get_doctor_calendar_summary;

    PROCEDURE get_article_by_id(p_id IN NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM articles WHERE id = p_id;
    END get_article_by_id;

    PROCEDURE get_article_comments(p_article_id IN NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM article_comments WHERE article_id = p_article_id ORDER BY created_at DESC;
    END get_article_comments;

    PROCEDURE get_paginated_doctor_articles(p_user_id IN NUMBER, p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        SELECT COUNT(*) INTO p_total FROM articles WHERE user_id = p_user_id;
        OPEN p_cursor FOR
            SELECT * FROM (
                SELECT a.*, ROWNUM rnum FROM (
                    SELECT * FROM articles WHERE user_id = p_user_id ORDER BY created_at DESC
                ) a WHERE ROWNUM <= p_offset + p_limit
            ) WHERE rnum > p_offset;
    END get_paginated_doctor_articles;

    PROCEDURE get_paginated_admin_articles(p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        SELECT COUNT(*) INTO p_total FROM articles;
        OPEN p_cursor FOR
            SELECT * FROM (
                SELECT a.*, ROWNUM rnum FROM (
                    SELECT * FROM articles ORDER BY created_at DESC
                ) a WHERE ROWNUM <= p_offset + p_limit
            ) WHERE rnum > p_offset;
    END get_paginated_admin_articles;

    PROCEDURE get_active_article_categories(p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM article_categories WHERE is_active = 1 ORDER BY name;
    END get_active_article_categories;

    PROCEDURE get_paginated_payments(p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        SELECT COUNT(*) INTO p_total FROM payments;
        OPEN p_cursor FOR
            SELECT * FROM (
                SELECT a.*, ROWNUM rnum FROM (
                    SELECT * FROM payments ORDER BY created_at DESC
                ) a WHERE ROWNUM <= p_offset + p_limit
            ) WHERE rnum > p_offset;
    END get_paginated_payments;

    PROCEDURE get_payment_by_id(p_id IN NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM payments WHERE id = p_id;
    END get_payment_by_id;

    PROCEDURE get_department_by_id(
        p_department_id IN NUMBER,
        p_cursor OUT SYS_REFCURSOR
    ) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM departments WHERE id = p_department_id;
    END get_department_by_id;

    PROCEDURE get_paginated_departments(p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        SELECT COUNT(*) INTO p_total FROM departments;
        OPEN p_cursor FOR
            SELECT * FROM (
                SELECT a.*, ROWNUM rnum FROM (
                    SELECT d.*, (SELECT COUNT(*) FROM doctors doc WHERE doc.department_id = d.id) AS doctors_count FROM departments d ORDER BY created_at DESC
                ) a WHERE ROWNUM <= p_offset + p_limit
            ) WHERE rnum > p_offset;
    END get_paginated_departments;

    PROCEDURE get_paginated_facility_rooms(p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        SELECT COUNT(*) INTO p_total FROM facility_rooms;
        OPEN p_cursor FOR
            SELECT * FROM (
                SELECT a.*, ROWNUM rnum FROM (
                    SELECT * FROM facility_rooms ORDER BY created_at DESC
                ) a WHERE ROWNUM <= p_offset + p_limit
            ) WHERE rnum > p_offset;
    END get_paginated_facility_rooms;

    PROCEDURE get_facility_room_by_id(p_id IN NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM facility_rooms WHERE id = p_id;
    END get_facility_room_by_id;

    PROCEDURE get_all_active_facility_rooms(p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM facility_rooms WHERE is_active = 1 ORDER BY room_number ASC;
    END get_all_active_facility_rooms;

    PROCEDURE get_all_facility_rooms(p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM facility_rooms ORDER BY room_number ASC;
    END get_all_facility_rooms;

    PROCEDURE get_future_facility_bookings(p_room_id IN NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM facility_bookings 
            WHERE facility_room_id = p_room_id 
            AND start_time >= TRUNC(SYSDATE)
            ORDER BY start_time ASC;
    END get_future_facility_bookings;

    PROCEDURE get_paginated_inventory_items(p_limit IN NUMBER, p_offset IN NUMBER, p_total OUT NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        SELECT COUNT(*) INTO p_total FROM inventory_items;
        OPEN p_cursor FOR
            SELECT * FROM (
                SELECT a.*, ROWNUM rnum FROM (
                    SELECT * FROM inventory_items ORDER BY name ASC
                ) a WHERE ROWNUM <= p_offset + p_limit
            ) WHERE rnum > p_offset;
    END get_paginated_inventory_items;

    PROCEDURE get_inventory_item_by_id(p_id IN NUMBER, p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM inventory_items WHERE id = p_id;
    END get_inventory_item_by_id;

    PROCEDURE get_admin_reports(
        p_start_date IN TIMESTAMP,
        p_end_date IN TIMESTAMP,
        p_total_appointments OUT NUMBER,
        p_total_revenue OUT NUMBER,
        p_medicine_sales OUT NUMBER
    ) IS
    BEGIN
        SELECT COUNT(*) INTO p_total_appointments 
        FROM appointments 
        WHERE scheduled_for BETWEEN p_start_date AND p_end_date;

        SELECT NVL(SUM(amount), 0) INTO p_total_revenue 
        FROM payments 
        WHERE status = 'paid' AND paid_at BETWEEN p_start_date AND p_end_date;

        SELECT NVL(SUM(total_amount), 0) INTO p_medicine_sales 
        FROM medicine_orders 
        WHERE status = 'completed' AND created_at BETWEEN p_start_date AND p_end_date;
    END get_admin_reports;

    PROCEDURE get_doctor_reports(
        p_doctor_id IN NUMBER,
        p_start_date IN TIMESTAMP,
        p_end_date IN TIMESTAMP,
        p_total_appointments OUT NUMBER,
        p_completed_appointments OUT NUMBER
    ) IS
    BEGIN
        SELECT COUNT(*) INTO p_total_appointments 
        FROM appointments 
        WHERE doctor_id = p_doctor_id AND scheduled_for BETWEEN p_start_date AND p_end_date;

        SELECT COUNT(*) INTO p_completed_appointments 
        FROM appointments 
        WHERE doctor_id = p_doctor_id AND status = 'completed' AND scheduled_for BETWEEN p_start_date AND p_end_date;
    END get_doctor_reports;

    PROCEDURE get_audit_log_entity_types(p_cursor OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT DISTINCT entity_type FROM audit_logs ORDER BY entity_type;
    END get_audit_log_entity_types;

    PROCEDURE get_staff_dashboard_stats(
        p_pending_appointments OUT NUMBER,
        p_today_appointments OUT NUMBER,
        p_doctor_count OUT NUMBER,
        p_published_articles OUT NUMBER,
        p_pending_ambulance OUT NUMBER
    ) IS
    BEGIN
        SELECT COUNT(*) INTO p_pending_appointments FROM appointments WHERE status = 'pending';
        SELECT COUNT(*) INTO p_today_appointments FROM appointments WHERE TRUNC(scheduled_for) = TRUNC(SYSDATE);
        SELECT COUNT(*) INTO p_doctor_count FROM doctors WHERE is_active = 1;
        SELECT COUNT(*) INTO p_published_articles FROM articles WHERE is_published = 1;
        SELECT COUNT(*) INTO p_pending_ambulance FROM ambulance_requests WHERE status = 'pending';
    END get_staff_dashboard_stats;

    PROCEDURE get_queue_appointments_by_date(
        p_date IN VARCHAR2,
        p_cursor OUT SYS_REFCURSOR
    ) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM appointments 
            WHERE TRUNC(scheduled_for) = TO_DATE(p_date, 'YYYY-MM-DD')
            AND status IN ('pending', 'confirmed')
            ORDER BY scheduled_for ASC;
    END get_queue_appointments_by_date;

    PROCEDURE get_user_by_id(
        p_id IN NUMBER,
        p_cursor OUT SYS_REFCURSOR
    ) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM users WHERE id = p_id;
    END get_user_by_id;

END pkg_crud_reads;
/
