-- ==========================================
-- 03_pkg_users.sql
-- ==========================================

CREATE OR REPLACE PACKAGE pkg_users AS
    PROCEDURE register_user(
        p_name IN VARCHAR2,
        p_email IN VARCHAR2,
        p_password IN VARCHAR2,
        p_role IN VARCHAR2,
        p_user_id OUT NUMBER
    );
    
    PROCEDURE update_role(
        p_user_id IN NUMBER,
        p_role IN VARCHAR2
    );

    PROCEDURE deactivate_user(
        p_user_id IN NUMBER
    );
END pkg_users;
/

CREATE OR REPLACE PACKAGE BODY pkg_users AS

    PROCEDURE register_user(
        p_name IN VARCHAR2,
        p_email IN VARCHAR2,
        p_password IN VARCHAR2,
        p_role IN VARCHAR2,
        p_user_id OUT NUMBER
    ) IS
    BEGIN
        INSERT INTO users (name, email, password, role)
        VALUES (p_name, p_email, p_password, NVL(p_role, 'patient'))
        RETURNING id INTO p_user_id;
        COMMIT;
    END register_user;

    PROCEDURE update_role(
        p_user_id IN NUMBER,
        p_role IN VARCHAR2
    ) IS
    BEGIN
        UPDATE users
        SET role = p_role
        WHERE id = p_user_id;
        COMMIT;
    END update_role;

    PROCEDURE deactivate_user(
        p_user_id IN NUMBER
    ) IS
    BEGIN
        UPDATE users
        SET is_active = 0
        WHERE id = p_user_id;
        COMMIT;
    END deactivate_user;

END pkg_users;
/
