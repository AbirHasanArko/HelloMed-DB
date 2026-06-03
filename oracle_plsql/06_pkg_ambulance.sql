-- ==========================================
-- 06_pkg_ambulance.sql
-- ==========================================

CREATE OR REPLACE PACKAGE pkg_ambulance AS
    PROCEDURE request_ambulance(
        p_user_id IN NUMBER,
        p_patient_name IN VARCHAR2,
        p_patient_phone IN VARCHAR2,
        p_address IN VARCHAR2,
        p_request_id OUT NUMBER
    );
    
    PROCEDURE dispatch_ambulance(
        p_request_id IN NUMBER,
        p_staff_id IN NUMBER
    );

    PROCEDURE resolve_request(
        p_request_id IN NUMBER
    );
END pkg_ambulance;
/

CREATE OR REPLACE PACKAGE BODY pkg_ambulance AS

    PROCEDURE request_ambulance(
        p_user_id IN NUMBER,
        p_patient_name IN VARCHAR2,
        p_patient_phone IN VARCHAR2,
        p_address IN VARCHAR2,
        p_request_id OUT NUMBER
    ) IS
    BEGIN
        INSERT INTO ambulance_requests (
            user_id, patient_name, patient_phone, address, status
        ) VALUES (
            p_user_id, p_patient_name, p_patient_phone, p_address, 'pending'
        ) RETURNING id INTO p_request_id;
        COMMIT;
    END request_ambulance;

    PROCEDURE dispatch_ambulance(
        p_request_id IN NUMBER,
        p_staff_id IN NUMBER
    ) IS
    BEGIN
        UPDATE ambulance_requests
        SET status = 'dispatched',
            staff_id = p_staff_id,
            dispatched_at = SYSTIMESTAMP
        WHERE id = p_request_id;
        COMMIT;
    END dispatch_ambulance;

    PROCEDURE resolve_request(
        p_request_id IN NUMBER
    ) IS
    BEGIN
        UPDATE ambulance_requests
        SET status = 'resolved',
            resolved_at = SYSTIMESTAMP
        WHERE id = p_request_id;
        COMMIT;
    END resolve_request;

END pkg_ambulance;
/
