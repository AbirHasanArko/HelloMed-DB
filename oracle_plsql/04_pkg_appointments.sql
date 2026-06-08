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

    PROCEDURE update_queue_status(
        p_appointment_id IN NUMBER,
        p_queue_status IN VARCHAR2
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
        v_slot_minutes NUMBER;
        v_conflict_count NUMBER;
        v_token_number VARCHAR2(50);
    BEGIN
        -- Get doctor's slot minutes
        SELECT slot_minutes INTO v_slot_minutes
        FROM doctors
        WHERE id = p_doctor_id;

        -- Check doctor schedule overlap
        SELECT COUNT(*) INTO v_conflict_count
        FROM appointments
        WHERE doctor_id = p_doctor_id
          AND status != 'cancelled'
          AND scheduled_for < p_scheduled_for + numtodsinterval(v_slot_minutes, 'MINUTE')
          AND scheduled_for + numtodsinterval(v_slot_minutes, 'MINUTE') > p_scheduled_for;
          
        IF v_conflict_count > 0 THEN
            RAISE_APPLICATION_ERROR(-20001, 'Time conflict: The doctor already has an appointment during this time slot.');
        END IF;

        -- Check patient schedule overlap
        IF p_user_id IS NOT NULL THEN
            SELECT COUNT(*) INTO v_conflict_count
            FROM appointments a
            JOIN doctors d ON a.doctor_id = d.id
            WHERE a.user_id = p_user_id
              AND a.status != 'cancelled'
              AND a.scheduled_for < p_scheduled_for + numtodsinterval(v_slot_minutes, 'MINUTE')
              AND a.scheduled_for + numtodsinterval(d.slot_minutes, 'MINUTE') > p_scheduled_for;
              
            IF v_conflict_count > 0 THEN
                RAISE_APPLICATION_ERROR(-20002, 'Time conflict: The patient already has an appointment overlapping this time slot.');
            END IF;
        END IF;

        -- Generate token number based on the day's total appointments
        SELECT COUNT(*) + 1 INTO v_conflict_count
        FROM appointments
        WHERE TRUNC(scheduled_for) = TRUNC(p_scheduled_for);
        
        v_token_number := 'TKN-' || TO_CHAR(p_scheduled_for, 'YYYYMMDD') || '-' || LPAD(v_conflict_count, 3, '0');

        INSERT INTO appointments (
            user_id, doctor_id, department_id, service_id,
            patient_name, patient_email, patient_phone,
            service_mode, scheduled_for, reason, status, token_number, queue_status
        ) VALUES (
            p_user_id, p_doctor_id, p_department_id, p_service_id,
            p_patient_name, p_patient_email, p_patient_phone,
            p_service_mode, p_scheduled_for, p_reason, 'pending', v_token_number, 'waiting'
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

    PROCEDURE update_queue_status(
        p_appointment_id IN NUMBER,
        p_queue_status IN VARCHAR2
    ) IS
    BEGIN
        UPDATE appointments
        SET queue_status = p_queue_status
        WHERE id = p_appointment_id;
        COMMIT;
    END update_queue_status;

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
