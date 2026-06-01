-- ==========================================
-- 04_pkg_appointments.sql
-- ==========================================

CREATE OR REPLACE PACKAGE pkg_appointments AS
    PROCEDURE book_appointment(
        p_user_id IN NUMBER,
        p_doctor_id IN NUMBER,
        p_department_id IN NUMBER,
        p_service_id IN NUMBER,
        p_patient_name IN VARCHAR2,
        p_patient_email IN VARCHAR2,
        p_patient_phone IN VARCHAR2,
        p_service_mode IN VARCHAR2,
        p_scheduled_for IN TIMESTAMP,
        p_reason IN VARCHAR2,
        p_appointment_id OUT NUMBER
    );
    
    PROCEDURE update_status(
        p_appointment_id IN NUMBER,
        p_status IN VARCHAR2
    );

    PROCEDURE attach_meeting_link(
        p_appointment_id IN NUMBER,
        p_link IN VARCHAR2
    );
END pkg_appointments;
/

CREATE OR REPLACE PACKAGE BODY pkg_appointments AS

    PROCEDURE book_appointment(
        p_user_id IN NUMBER,
        p_doctor_id IN NUMBER,
        p_department_id IN NUMBER,
        p_service_id IN NUMBER,
        p_patient_name IN VARCHAR2,
        p_patient_email IN VARCHAR2,
        p_patient_phone IN VARCHAR2,
        p_service_mode IN VARCHAR2,
        p_scheduled_for IN TIMESTAMP,
        p_reason IN VARCHAR2,
        p_appointment_id OUT NUMBER
    ) IS
    BEGIN
        INSERT INTO appointments (
            user_id, doctor_id, department_id, service_id,
            patient_name, patient_email, patient_phone,
            service_mode, scheduled_for, reason, status
        ) VALUES (
            p_user_id, p_doctor_id, p_department_id, p_service_id,
            p_patient_name, p_patient_email, p_patient_phone,
            p_service_mode, p_scheduled_for, p_reason, 'pending'
        ) RETURNING id INTO p_appointment_id;
        COMMIT;
    END book_appointment;

    PROCEDURE update_status(
        p_appointment_id IN NUMBER,
        p_status IN VARCHAR2
    ) IS
    BEGIN
        UPDATE appointments
        SET status = p_status
        WHERE id = p_appointment_id;
        COMMIT;
    END update_status;

    PROCEDURE attach_meeting_link(
        p_appointment_id IN NUMBER,
        p_link IN VARCHAR2
    ) IS
    BEGIN
        UPDATE appointments
        SET online_meeting_link = p_link
        WHERE id = p_appointment_id;
        COMMIT;
    END attach_meeting_link;

END pkg_appointments;
/
