-- ==========================================
-- 09_pkg_search.sql
-- ==========================================

CREATE OR REPLACE PACKAGE pkg_search AS
    PROCEDURE search_patients(
        p_search IN VARCHAR2,
        p_limit IN NUMBER,
        p_offset IN NUMBER,
        p_total OUT NUMBER,
        p_cursor OUT SYS_REFCURSOR
    );
END pkg_search;
/

CREATE OR REPLACE PACKAGE BODY pkg_search AS
    PROCEDURE search_patients(
        p_search IN VARCHAR2,
        p_limit IN NUMBER,
        p_offset IN NUMBER,
        p_total OUT NUMBER,
        p_cursor OUT SYS_REFCURSOR
    ) IS
    BEGIN
        SELECT COUNT(*) INTO p_total 
        FROM users 
        WHERE role = 'patient' 
          AND (p_search IS NULL OR LOWER(name) LIKE '%' || LOWER(p_search) || '%' OR LOWER(email) LIKE '%' || LOWER(p_search) || '%');

        OPEN p_cursor FOR
        SELECT * FROM (
            SELECT a.*, ROWNUM rnum FROM (
                SELECT *
                FROM users
                WHERE role = 'patient'
                  AND (p_search IS NULL OR LOWER(name) LIKE '%' || LOWER(p_search) || '%' OR LOWER(email) LIKE '%' || LOWER(p_search) || '%')
                ORDER BY created_at DESC
            ) a WHERE ROWNUM <= NVL(p_offset, 0) + NVL(p_limit, 1000000)
        ) WHERE rnum > NVL(p_offset, 0);
    END search_patients;
END pkg_search;
/
