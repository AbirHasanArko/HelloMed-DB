-- ==========================================
-- 10_pkg_filters.sql
-- ==========================================

CREATE OR REPLACE PACKAGE pkg_filters AS
    PROCEDURE filter_audit_logs(
        p_action IN VARCHAR2,
        p_entity_type IN VARCHAR2,
        p_critical IN NUMBER,
        p_limit IN NUMBER,
        p_offset IN NUMBER,
        p_total OUT NUMBER,
        p_cursor OUT SYS_REFCURSOR
    );

    PROCEDURE filter_doctor_appointments(
        p_doctor_id IN NUMBER,
        p_filter IN VARCHAR2,
        p_limit IN NUMBER,
        p_offset IN NUMBER,
        p_total OUT NUMBER,
        p_cursor OUT SYS_REFCURSOR
    );

    PROCEDURE get_admin_dashboard_metrics(
        p_since_hours IN NUMBER,
        p_failed_logins OUT NUMBER,
        p_failed_payments OUT NUMBER,
        p_freq_status_changes OUT NUMBER
    );

    PROCEDURE get_financial_report(
        p_start_date IN TIMESTAMP,
        p_end_date IN TIMESTAMP,
        p_total_revenue OUT NUMBER,
        p_medicine_sales OUT NUMBER
    );

    PROCEDURE get_doctor_report(
        p_doctor_id IN NUMBER,
        p_total OUT NUMBER,
        p_completed OUT NUMBER,
        p_cancelled OUT NUMBER
    );

    PROCEDURE filter_doctors(
        p_department_slug IN VARCHAR2,
        p_limit IN NUMBER,
        p_offset IN NUMBER,
        p_total OUT NUMBER,
        p_cursor OUT SYS_REFCURSOR
    );

    PROCEDURE filter_articles(
        p_category_slug IN VARCHAR2,
        p_limit IN NUMBER,
        p_offset IN NUMBER,
        p_total OUT NUMBER,
        p_cursor OUT SYS_REFCURSOR
    );

    PROCEDURE filter_admin_doctors(
        p_search IN VARCHAR2,
        p_department_id IN NUMBER,
        p_limit IN NUMBER,
        p_offset IN NUMBER,
        p_total OUT NUMBER,
        p_cursor OUT SYS_REFCURSOR
    );
END pkg_filters;
/

CREATE OR REPLACE PACKAGE BODY pkg_filters AS
    PROCEDURE filter_audit_logs(
        p_action IN VARCHAR2,
        p_entity_type IN VARCHAR2,
        p_critical IN NUMBER,
        p_limit IN NUMBER,
        p_offset IN NUMBER,
        p_total OUT NUMBER,
        p_cursor OUT SYS_REFCURSOR
    ) IS
    BEGIN
        SELECT COUNT(*) INTO p_total
        FROM audit_logs
        WHERE (p_action IS NULL OR LOWER(action) LIKE '%' || LOWER(p_action) || '%')
          AND (p_entity_type IS NULL OR entity_type = p_entity_type)
          AND (p_critical = 0 OR (
              action IN ('auth.login_failed', 'auth.login_locked', 'auth.password_changed', 'appointment.status_updated')
              OR (action = 'medicine_order.payment_callback' AND meta LIKE '%"callback_status":"failed"%')
          ));

        OPEN p_cursor FOR
        SELECT * FROM (
            SELECT a.*, ROWNUM rnum FROM (
                SELECT *
                FROM audit_logs
                WHERE (p_action IS NULL OR LOWER(action) LIKE '%' || LOWER(p_action) || '%')
                  AND (p_entity_type IS NULL OR entity_type = p_entity_type)
                  AND (p_critical = 0 OR (
                      action IN ('auth.login_failed', 'auth.login_locked', 'auth.password_changed', 'appointment.status_updated')
                      OR (action = 'medicine_order.payment_callback' AND meta LIKE '%"callback_status":"failed"%')
                  ))
                ORDER BY created_at DESC
            ) a WHERE ROWNUM <= NVL(p_offset, 0) + NVL(p_limit, 1000000)
        ) WHERE rnum > NVL(p_offset, 0);
    END filter_audit_logs;

    PROCEDURE filter_doctor_appointments(
        p_doctor_id IN NUMBER,
        p_filter IN VARCHAR2,
        p_limit IN NUMBER,
        p_offset IN NUMBER,
        p_total OUT NUMBER,
        p_cursor OUT SYS_REFCURSOR
    ) IS
    BEGIN
        SELECT COUNT(*) INTO p_total
        FROM appointments
        WHERE doctor_id = p_doctor_id
          AND (
              (p_filter = 'today' AND TRUNC(scheduled_for) = TRUNC(SYSDATE))
              OR (p_filter = 'next' AND scheduled_for >= SYSDATE)
              OR (p_filter = 'past' AND scheduled_for < SYSDATE)
              OR (p_filter = 'all')
          );

        OPEN p_cursor FOR
        SELECT * FROM (
            SELECT a.*, ROWNUM rnum FROM (
                SELECT *
                FROM appointments
                WHERE doctor_id = p_doctor_id
                  AND (
                      (p_filter = 'today' AND TRUNC(scheduled_for) = TRUNC(SYSDATE))
                      OR (p_filter = 'next' AND scheduled_for >= SYSDATE)
                      OR (p_filter = 'past' AND scheduled_for < SYSDATE)
                      OR (p_filter = 'all')
                  )
                ORDER BY scheduled_for DESC
            ) a WHERE ROWNUM <= NVL(p_offset, 0) + NVL(p_limit, 1000000)
        ) WHERE rnum > NVL(p_offset, 0);
    END filter_doctor_appointments;

    PROCEDURE get_admin_dashboard_metrics(
        p_since_hours IN NUMBER,
        p_failed_logins OUT NUMBER,
        p_failed_payments OUT NUMBER,
        p_freq_status_changes OUT NUMBER
    ) IS
        v_since TIMESTAMP;
    BEGIN
        v_since := SYSTIMESTAMP - NUMTODSINTERVAL(NVL(p_since_hours, 24), 'HOUR');
        
        SELECT COUNT(*) INTO p_failed_logins
        FROM audit_logs
        WHERE action = 'auth.login_failed' AND created_at >= v_since;

        SELECT COUNT(*) INTO p_failed_payments
        FROM audit_logs
        WHERE action = 'medicine_order.payment_callback' AND meta LIKE '%"callback_status":"failed"%' AND created_at >= v_since;

        SELECT COUNT(*) INTO p_freq_status_changes
        FROM (
            SELECT entity_type, entity_id
            FROM audit_logs
            WHERE action = 'appointment.status_updated' AND created_at >= v_since
            GROUP BY entity_type, entity_id
            HAVING COUNT(*) >= 3
        );
    END get_admin_dashboard_metrics;

    PROCEDURE get_financial_report(
        p_start_date IN TIMESTAMP,
        p_end_date IN TIMESTAMP,
        p_total_revenue OUT NUMBER,
        p_medicine_sales OUT NUMBER
    ) IS
    BEGIN
        SELECT NVL(SUM(amount), 0) INTO p_total_revenue
        FROM payments
        WHERE status = 'paid'
          AND paid_at BETWEEN NVL(p_start_date, TO_TIMESTAMP('2000-01-01', 'YYYY-MM-DD')) 
                          AND NVL(p_end_date, SYSTIMESTAMP);

        SELECT NVL(SUM(total_amount), 0) INTO p_medicine_sales
        FROM medicine_orders
        WHERE status = 'completed'
          AND created_at BETWEEN NVL(p_start_date, TO_TIMESTAMP('2000-01-01', 'YYYY-MM-DD')) 
                             AND NVL(p_end_date, SYSTIMESTAMP);
    END get_financial_report;

    PROCEDURE get_doctor_report(
        p_doctor_id IN NUMBER,
        p_total OUT NUMBER,
        p_completed OUT NUMBER,
        p_cancelled OUT NUMBER
    ) IS
    BEGIN
        SELECT COUNT(*) INTO p_total
        FROM appointments
        WHERE doctor_id = p_doctor_id;

        SELECT COUNT(*) INTO p_completed
        FROM appointments
        WHERE doctor_id = p_doctor_id AND status = 'completed';

        SELECT COUNT(*) INTO p_cancelled
        FROM appointments
        WHERE doctor_id = p_doctor_id AND status = 'cancelled';
    END get_doctor_report;

    PROCEDURE filter_doctors(
        p_department_slug IN VARCHAR2,
        p_limit IN NUMBER,
        p_offset IN NUMBER,
        p_total OUT NUMBER,
        p_cursor OUT SYS_REFCURSOR
    ) IS
    BEGIN
        SELECT COUNT(*) INTO p_total
        FROM doctors d
        LEFT JOIN departments dep ON d.department_id = dep.id
        WHERE d.is_active = 1
          AND (p_department_slug IS NULL OR dep.slug = p_department_slug);

        OPEN p_cursor FOR
        SELECT * FROM (
            SELECT a.*, ROWNUM rnum FROM (
                SELECT d.*
                FROM doctors d
                LEFT JOIN departments dep ON d.department_id = dep.id
                WHERE d.is_active = 1
                  AND (p_department_slug IS NULL OR dep.slug = p_department_slug)
                ORDER BY d.created_at DESC
            ) a WHERE ROWNUM <= NVL(p_offset, 0) + NVL(p_limit, 1000000)
        ) WHERE rnum > NVL(p_offset, 0);
    END filter_doctors;

    PROCEDURE filter_articles(
        p_category_slug IN VARCHAR2,
        p_limit IN NUMBER,
        p_offset IN NUMBER,
        p_total OUT NUMBER,
        p_cursor OUT SYS_REFCURSOR
    ) IS
    BEGIN
        SELECT COUNT(*) INTO p_total
        FROM articles a
        LEFT JOIN article_categories c ON a.article_category_id = c.id
        WHERE a.is_published = 1
          AND (p_category_slug IS NULL OR c.slug = p_category_slug);

        OPEN p_cursor FOR
        SELECT * FROM (
            SELECT a.*, ROWNUM rnum FROM (
                SELECT art.*
                FROM articles art
                LEFT JOIN article_categories c ON art.article_category_id = c.id
                WHERE art.is_published = 1
                  AND (p_category_slug IS NULL OR c.slug = p_category_slug)
                ORDER BY art.is_featured DESC, NULLIF(art.featured_order, 0) ASC NULLS LAST, art.published_at DESC
            ) a WHERE ROWNUM <= NVL(p_offset, 0) + NVL(p_limit, 1000000)
        ) WHERE rnum > NVL(p_offset, 0);
    END filter_articles;

    PROCEDURE filter_admin_doctors(
        p_search IN VARCHAR2,
        p_department_id IN NUMBER,
        p_limit IN NUMBER,
        p_offset IN NUMBER,
        p_total OUT NUMBER,
        p_cursor OUT SYS_REFCURSOR
    ) IS
    BEGIN
        SELECT COUNT(*) INTO p_total
        FROM doctors d
        WHERE (p_search IS NULL OR LOWER(d.name) LIKE '%' || LOWER(p_search) || '%')
          AND (p_department_id IS NULL OR d.department_id = p_department_id);

        OPEN p_cursor FOR
        SELECT * FROM (
            SELECT a.*, ROWNUM rnum FROM (
                SELECT d.*
                FROM doctors d
                WHERE (p_search IS NULL OR LOWER(d.name) LIKE '%' || LOWER(p_search) || '%')
                  AND (p_department_id IS NULL OR d.department_id = p_department_id)
                ORDER BY d.created_at DESC
            ) a WHERE ROWNUM <= NVL(p_offset, 0) + NVL(p_limit, 1000000)
        ) WHERE rnum > NVL(p_offset, 0);
    END filter_admin_doctors;

END pkg_filters;
/
