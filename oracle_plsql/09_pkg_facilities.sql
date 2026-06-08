-- ==========================================
-- 09_pkg_facilities.sql
-- ==========================================

CREATE OR REPLACE PACKAGE pkg_facilities AS
    PROCEDURE book_facility(
        p_facility_room_id IN NUMBER,
        p_appointment_id IN NUMBER,
        p_user_id IN NUMBER,
        p_doctor_id IN NUMBER,
        p_start_time IN TIMESTAMP,
        p_end_time IN TIMESTAMP,
        p_booking_id OUT NUMBER
    );
    
    PROCEDURE update_booking_status(
        p_booking_id IN NUMBER,
        p_status IN VARCHAR2
    );
END pkg_facilities;
/

CREATE OR REPLACE PACKAGE BODY pkg_facilities AS

    PROCEDURE book_facility(
        p_facility_room_id IN NUMBER,
        p_appointment_id IN NUMBER,
        p_user_id IN NUMBER,
        p_doctor_id IN NUMBER,
        p_start_time IN TIMESTAMP,
        p_end_time IN TIMESTAMP,
        p_booking_id OUT NUMBER
    ) IS
        v_conflict_count NUMBER;
    BEGIN
        -- Check for overlapping bookings for the same room
        SELECT COUNT(*) INTO v_conflict_count
        FROM facility_bookings
        WHERE facility_room_id = p_facility_room_id
          AND status NOT IN ('cancelled', 'completed')
          AND start_time < p_end_time
          AND end_time > p_start_time;
          
        IF v_conflict_count > 0 THEN
            RAISE_APPLICATION_ERROR(-20003, 'Time conflict: The facility room is already booked during this time.');
        END IF;

        INSERT INTO facility_bookings (
            facility_room_id, appointment_id, user_id, doctor_id, start_time, end_time, status
        ) VALUES (
            p_facility_room_id, p_appointment_id, p_user_id, p_doctor_id, p_start_time, p_end_time, 'scheduled'
        ) RETURNING id INTO p_booking_id;
        
        COMMIT;
    END book_facility;

    PROCEDURE update_booking_status(
        p_booking_id IN NUMBER,
        p_status IN VARCHAR2
    ) IS
    BEGIN
        UPDATE facility_bookings
        SET status = p_status
        WHERE id = p_booking_id;
        COMMIT;
    END update_booking_status;

END pkg_facilities;
/
