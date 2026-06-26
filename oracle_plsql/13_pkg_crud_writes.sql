-- ==========================================
-- 13_pkg_crud_writes.sql
-- ==========================================
CREATE OR REPLACE PACKAGE pkg_crud_writes AS
    -- Ambulance
    PROCEDURE update_ambulance_request(
        p_id IN NUMBER,
        p_staff_id IN NUMBER,
        p_status IN VARCHAR2,
        p_dispatched_at IN TIMESTAMP,
        p_resolved_at IN TIMESTAMP,
        p_notes IN VARCHAR2
    );

    PROCEDURE update_ambulance_location(
        p_id IN NUMBER,
        p_latitude IN NUMBER,
        p_longitude IN NUMBER
    );

    -- Departments
    PROCEDURE create_department(
        p_name IN VARCHAR2,
        p_description IN VARCHAR2,
        p_service_scope IN VARCHAR2,
        p_is_active IN NUMBER,
        p_is_featured IN NUMBER,
        p_featured_order IN NUMBER,
        p_image_path IN VARCHAR2,
        p_id OUT NUMBER
    );

    PROCEDURE update_department(
        p_id IN NUMBER,
        p_name IN VARCHAR2,
        p_description IN VARCHAR2,
        p_service_scope IN VARCHAR2,
        p_is_active IN NUMBER,
        p_is_featured IN NUMBER,
        p_featured_order IN NUMBER,
        p_image_path IN VARCHAR2
    );

    -- Doctors
    PROCEDURE create_doctor_user(
        p_name IN VARCHAR2,
        p_email IN VARCHAR2,
        p_password IN VARCHAR2,
        p_user_id OUT NUMBER
    );

    PROCEDURE create_doctor_profile(
        p_user_id IN NUMBER,
        p_department_id IN NUMBER,
        p_name IN VARCHAR2,
        p_slug IN VARCHAR2,
        p_specialty IN VARCHAR2,
        p_qualification IN VARCHAR2,
        p_experience_years IN NUMBER,
        p_consultation_fee IN NUMBER,
        p_about IN VARCHAR2,
        p_online_available_days IN VARCHAR2,
        p_offline_available_days IN VARCHAR2,
        p_available_days IN VARCHAR2,
        p_slot_minutes IN NUMBER,
        p_is_active IN NUMBER,
        p_is_featured IN NUMBER,
        p_featured_order IN NUMBER,
        p_photo_path IN VARCHAR2,
        p_online_available IN NUMBER,
        p_offline_available IN NUMBER,
        p_doctor_id OUT NUMBER
    );

    PROCEDURE update_doctor_profile(
        p_id IN NUMBER,
        p_department_id IN NUMBER,
        p_specialty IN VARCHAR2,
        p_qualification IN VARCHAR2,
        p_experience_years IN NUMBER,
        p_consultation_fee IN NUMBER,
        p_about IN VARCHAR2,
        p_online_available_days IN VARCHAR2,
        p_offline_available_days IN VARCHAR2,
        p_slot_minutes IN NUMBER,
        p_is_active IN NUMBER,
        p_is_featured IN NUMBER,
        p_featured_order IN NUMBER,
        p_photo_path IN VARCHAR2
    );

    PROCEDURE delete_doctor(
        p_id IN NUMBER
    );

    -- Facility Rooms
    PROCEDURE create_facility_room(
        p_room_number IN VARCHAR2,
        p_room_type IN VARCHAR2,
        p_capacity IN NUMBER,
        p_is_active IN NUMBER,
        p_id OUT NUMBER
    );

    PROCEDURE update_facility_room(
        p_id IN NUMBER,
        p_room_number IN VARCHAR2,
        p_room_type IN VARCHAR2,
        p_capacity IN NUMBER,
        p_is_active IN NUMBER
    );

    -- Medicines
    PROCEDURE create_medicine(
        p_name IN VARCHAR2,
        p_description IN VARCHAR2,
        p_medicine_group IN VARCHAR2,
        p_strength IN VARCHAR2,
        p_amount IN VARCHAR2,
        p_manufacturer IN VARCHAR2,
        p_price IN NUMBER,
        p_requires_prescription IN NUMBER,
        p_stock_quantity IN NUMBER,
        p_is_active IN NUMBER,
        p_image_path IN VARCHAR2,
        p_id OUT NUMBER
    );

    PROCEDURE update_medicine(
        p_id IN NUMBER,
        p_name IN VARCHAR2,
        p_description IN VARCHAR2,
        p_medicine_group IN VARCHAR2,
        p_strength IN VARCHAR2,
        p_amount IN VARCHAR2,
        p_manufacturer IN VARCHAR2,
        p_price IN NUMBER,
        p_requires_prescription IN NUMBER,
        p_stock_quantity IN NUMBER,
        p_is_active IN NUMBER,
        p_image_path IN VARCHAR2
    );

    -- Orders
    PROCEDURE update_order_payment(
        p_id IN NUMBER,
        p_payment_method IN VARCHAR2,
        p_payment_status IN VARCHAR2,
        p_payment_reference IN VARCHAR2
    );

    PROCEDURE update_medicine_order_status(
        p_id IN NUMBER,
        p_status IN VARCHAR2,
        p_payment_status IN VARCHAR2
    );

    PROCEDURE update_order_payment_token(
        p_id IN NUMBER,
        p_payment_callback_token IN VARCHAR2
    );

    PROCEDURE update_order_details(
        p_id IN NUMBER,
        p_delivery_address IN VARCHAR2,
        p_phone IN VARCHAR2,
        p_status IN VARCHAR2
    );

    -- Articles
    PROCEDURE create_article(
        p_article_category_id IN NUMBER,
        p_user_id IN NUMBER,
        p_title IN VARCHAR2,
        p_slug IN VARCHAR2,
        p_excerpt IN VARCHAR2,
        p_body IN CLOB,
        p_cover_image_path IN VARCHAR2,
        p_is_featured IN NUMBER,
        p_featured_order IN NUMBER,
        p_is_published IN NUMBER,
        p_publication_status IN VARCHAR2,
        p_published_at IN TIMESTAMP,
        p_article_id OUT NUMBER
    );

    PROCEDURE update_article(
        p_id IN NUMBER,
        p_article_category_id IN NUMBER,
        p_title IN VARCHAR2,
        p_slug IN VARCHAR2,
        p_excerpt IN VARCHAR2,
        p_body IN CLOB,
        p_cover_image_path IN VARCHAR2,
        p_is_featured IN NUMBER,
        p_featured_order IN NUMBER,
        p_is_published IN NUMBER,
        p_publication_status IN VARCHAR2,
        p_reviewed_by_user_id IN NUMBER,
        p_reviewed_at IN TIMESTAMP,
        p_published_at IN TIMESTAMP
    );

    -- Users
    PROCEDURE update_user(
        p_id IN NUMBER,
        p_name IN VARCHAR2,
        p_email IN VARCHAR2,
        p_password IN VARCHAR2
    );

    PROCEDURE update_user_password(
        p_user_id IN NUMBER,
        p_password IN VARCHAR2
    );

    PROCEDURE create_patient_user(
        p_name IN VARCHAR2,
        p_email IN VARCHAR2,
        p_password IN VARCHAR2,
        p_user_id OUT NUMBER
    );

    PROCEDURE update_patient_profile(
        p_user_id IN NUMBER,
        p_dob IN DATE,
        p_gender IN VARCHAR2,
        p_height IN NUMBER,
        p_weight IN NUMBER,
        p_conditions IN VARCHAR2,
        p_allergies IN VARCHAR2,
        p_notes IN VARCHAR2
    );

    -- QnA
    PROCEDURE update_question_status(
        p_id IN NUMBER,
        p_status IN VARCHAR2
    );

    PROCEDURE reschedule_appointment(
        p_id IN NUMBER,
        p_scheduled_for IN TIMESTAMP
    );

    PROCEDURE create_qna_question(
        p_user_id IN NUMBER,
        p_title IN VARCHAR2,
        p_question IN VARCHAR2,
        p_status IN VARCHAR2,
        p_id OUT NUMBER
    );

    PROCEDURE create_qna_answer(
        p_question_id IN NUMBER,
        p_user_id IN NUMBER,
        p_answer IN VARCHAR2,
        p_is_official IN NUMBER,
        p_id OUT NUMBER
    );

    -- Payments
    PROCEDURE create_payment(
        p_appointment_id IN NUMBER,
        p_user_id IN NUMBER,
        p_method IN VARCHAR2,
        p_amount IN NUMBER,
        p_status IN VARCHAR2,
        p_id OUT NUMBER
    );

    PROCEDURE update_appt_payment_status(
        p_id IN NUMBER,
        p_payment_status IN VARCHAR2
    );

    PROCEDURE update_appt_prescription(
        p_id IN NUMBER,
        p_doctor_prescription IN CLOB,
        p_prescription_diagnosis IN VARCHAR2,
        p_prescription_medicines IN VARCHAR2,
        p_prescription_advice IN VARCHAR2,
        p_prescription_safety_notes IN VARCHAR2,
        p_prescription_follow_up_date IN DATE,
        p_status IN VARCHAR2
    );

    PROCEDURE delete_prescription_items(
        p_appointment_id IN NUMBER
    );

    PROCEDURE create_prescription_item(
        p_appointment_id IN NUMBER,
        p_medicine_id IN NUMBER,
        p_medicine_name IN VARCHAR2,
        p_amount IN VARCHAR2,
        p_dosage IN VARCHAR2,
        p_intake_time IN VARCHAR2,
        p_instructions IN VARCHAR2,
        p_sort_order IN NUMBER,
        p_id OUT NUMBER
    );

    PROCEDURE create_appt_chat_message(
        p_appointment_id IN NUMBER,
        p_user_id IN NUMBER,
        p_message IN VARCHAR2,
        p_attachment_path IN VARCHAR2,
        p_attachment_name IN VARCHAR2,
        p_attachment_mime IN VARCHAR2,
        p_attachment_size IN NUMBER,
        p_id OUT NUMBER
    );

    PROCEDURE mark_appt_chat_messages_read(
        p_appointment_id IN NUMBER,
        p_user_id IN NUMBER,
        p_updated OUT NUMBER
    );

    PROCEDURE update_doctor_schedule(
        p_doctor_id IN NUMBER,
        p_clinic_address IN VARCHAR2,
        p_slot_minutes IN NUMBER,
        p_online_available IN NUMBER,
        p_offline_available IN NUMBER,
        p_online_available_days IN VARCHAR2,
        p_online_available_from IN VARCHAR2,
        p_online_available_to IN VARCHAR2,
        p_offline_available_days IN VARCHAR2,
        p_offline_available_from IN VARCHAR2,
        p_offline_available_to IN VARCHAR2,
        p_available_days IN VARCHAR2,
        p_available_from IN VARCHAR2,
        p_available_to IN VARCHAR2
    );

    PROCEDURE create_article_comment(
        p_article_id IN NUMBER,
        p_user_id IN NUMBER,
        p_rating IN NUMBER,
        p_comment IN VARCHAR2,
        p_id OUT NUMBER
    );

    PROCEDURE update_payment(
        p_id IN NUMBER,
        p_status IN VARCHAR2,
        p_paid_at IN TIMESTAMP,
        p_reference IN VARCHAR2,
        p_notes IN VARCHAR2
    );
    PROCEDURE create_audit_log(
        p_actor_user_id IN NUMBER,
        p_action IN VARCHAR2,
        p_entity_type IN VARCHAR2,
        p_entity_id IN NUMBER,
        p_old_values IN VARCHAR2,
        p_new_values IN VARCHAR2,
        p_meta IN VARCHAR2,
        p_ip_address IN VARCHAR2,
        p_user_agent IN VARCHAR2
    );

END pkg_crud_writes;
/

CREATE OR REPLACE PACKAGE BODY pkg_crud_writes AS

    -- Ambulance
    PROCEDURE update_ambulance_request(
        p_id IN NUMBER,
        p_staff_id IN NUMBER,
        p_status IN VARCHAR2,
        p_dispatched_at IN TIMESTAMP,
        p_resolved_at IN TIMESTAMP,
        p_notes IN VARCHAR2
    ) IS
    BEGIN
        UPDATE ambulance_requests
        SET staff_id = NVL(p_staff_id, staff_id),
            status = NVL(p_status, status),
            dispatched_at = NVL(p_dispatched_at, dispatched_at),
            resolved_at = NVL(p_resolved_at, resolved_at),
            notes = NVL(p_notes, notes)
        WHERE id = p_id;
        COMMIT;
    END update_ambulance_request;

    PROCEDURE update_ambulance_location(
        p_id IN NUMBER,
        p_latitude IN NUMBER,
        p_longitude IN NUMBER
    ) IS
    BEGIN
        UPDATE ambulance_requests
        SET latitude = p_latitude,
            longitude = p_longitude
        WHERE id = p_id;
        COMMIT;
    END update_ambulance_location;

    -- Payments
    PROCEDURE create_payment(
        p_appointment_id IN NUMBER,
        p_user_id IN NUMBER,
        p_method IN VARCHAR2,
        p_amount IN NUMBER,
        p_status IN VARCHAR2,
        p_id OUT NUMBER
    ) IS
    BEGIN
        INSERT INTO payments (appointment_id, user_id, method, amount, status)
        VALUES (p_appointment_id, p_user_id, p_method, p_amount, p_status)
        RETURNING id INTO p_id;
        COMMIT;
    END create_payment;

    PROCEDURE update_appt_payment_status(
        p_id IN NUMBER,
        p_payment_status IN VARCHAR2
    ) IS
    BEGIN
        UPDATE appointments
        SET payment_status = p_payment_status
        WHERE id = p_id;
        COMMIT;
    END update_appt_payment_status;

    PROCEDURE update_appt_prescription(
        p_id IN NUMBER,
        p_doctor_prescription IN CLOB,
        p_prescription_diagnosis IN VARCHAR2,
        p_prescription_medicines IN VARCHAR2,
        p_prescription_advice IN VARCHAR2,
        p_prescription_safety_notes IN VARCHAR2,
        p_prescription_follow_up_date IN DATE,
        p_status IN VARCHAR2
    ) IS
    BEGIN
        UPDATE appointments
        SET doctor_prescription = p_doctor_prescription,
            prescription_diagnosis = p_prescription_diagnosis,
            prescription_medicines = p_prescription_medicines,
            prescription_advice = p_prescription_advice,
            prescription_safety_notes = p_prescription_safety_notes,
            prescription_follow_up_date = p_prescription_follow_up_date,
            prescription_written_at = CURRENT_TIMESTAMP,
            status = p_status
        WHERE id = p_id;
        COMMIT;
    END update_appt_prescription;

    PROCEDURE delete_prescription_items(
        p_appointment_id IN NUMBER
    ) IS
    BEGIN
        DELETE FROM appointment_prescription_items WHERE appointment_id = p_appointment_id;
        COMMIT;
    END delete_prescription_items;

    PROCEDURE create_prescription_item(
        p_appointment_id IN NUMBER,
        p_medicine_id IN NUMBER,
        p_medicine_name IN VARCHAR2,
        p_amount IN VARCHAR2,
        p_dosage IN VARCHAR2,
        p_intake_time IN VARCHAR2,
        p_instructions IN VARCHAR2,
        p_sort_order IN NUMBER,
        p_id OUT NUMBER
    ) IS
    BEGIN
        INSERT INTO appointment_prescription_items (appointment_id, medicine_id, medicine_name, amount, dosage, intake_time, instructions, sort_order)
        VALUES (p_appointment_id, p_medicine_id, p_medicine_name, p_amount, p_dosage, p_intake_time, p_instructions, p_sort_order)
        RETURNING id INTO p_id;
        COMMIT;
    END create_prescription_item;

    PROCEDURE create_appt_chat_message(
        p_appointment_id IN NUMBER,
        p_user_id IN NUMBER,
        p_message IN VARCHAR2,
        p_attachment_path IN VARCHAR2,
        p_attachment_name IN VARCHAR2,
        p_attachment_mime IN VARCHAR2,
        p_attachment_size IN NUMBER,
        p_id OUT NUMBER
    ) IS
    BEGIN
        INSERT INTO appointment_chat_messages (appointment_id, user_id, message, attachment_path, attachment_name, attachment_mime, attachment_size)
        VALUES (p_appointment_id, p_user_id, p_message, p_attachment_path, p_attachment_name, p_attachment_mime, p_attachment_size)
        RETURNING id INTO p_id;
        COMMIT;
    END create_appt_chat_message;

    PROCEDURE mark_appt_chat_messages_read(
        p_appointment_id IN NUMBER,
        p_user_id IN NUMBER,
        p_updated OUT NUMBER
    ) IS
    BEGIN
        UPDATE appointment_chat_messages
        SET read_at = CURRENT_TIMESTAMP
        WHERE appointment_id = p_appointment_id
          AND user_id != p_user_id
          AND read_at IS NULL;
        p_updated := SQL%ROWCOUNT;
        COMMIT;
    END mark_appt_chat_messages_read;

    PROCEDURE update_doctor_schedule(
        p_doctor_id IN NUMBER,
        p_clinic_address IN VARCHAR2,
        p_slot_minutes IN NUMBER,
        p_online_available IN NUMBER,
        p_offline_available IN NUMBER,
        p_online_available_days IN VARCHAR2,
        p_online_available_from IN VARCHAR2,
        p_online_available_to IN VARCHAR2,
        p_offline_available_days IN VARCHAR2,
        p_offline_available_from IN VARCHAR2,
        p_offline_available_to IN VARCHAR2,
        p_available_days IN VARCHAR2,
        p_available_from IN VARCHAR2,
        p_available_to IN VARCHAR2
    ) IS
    BEGIN
        UPDATE doctors
        SET clinic_address = p_clinic_address,
            slot_minutes = p_slot_minutes,
            online_available = p_online_available,
            offline_available = p_offline_available,
            online_available_days = p_online_available_days,
            online_available_from = p_online_available_from,
            online_available_to = p_online_available_to,
            offline_available_days = p_offline_available_days,
            offline_available_from = p_offline_available_from,
            offline_available_to = p_offline_available_to,
            available_days = p_available_days,
            available_from = p_available_from,
            available_to = p_available_to
        WHERE id = p_doctor_id;
        COMMIT;
    END update_doctor_schedule;


    PROCEDURE create_article_comment(
        p_article_id IN NUMBER,
        p_user_id IN NUMBER,
        p_rating IN NUMBER,
        p_comment IN VARCHAR2,
        p_id OUT NUMBER
    ) IS
    BEGIN
        INSERT INTO article_comments (article_id, user_id, rating, comment)
        VALUES (p_article_id, p_user_id, p_rating, p_comment)
        RETURNING id INTO p_id;
        COMMIT;
    END create_article_comment;

    PROCEDURE update_payment(
        p_id IN NUMBER,
        p_status IN VARCHAR2,
        p_paid_at IN TIMESTAMP,
        p_reference IN VARCHAR2,
        p_notes IN VARCHAR2
    ) IS
    BEGIN
        UPDATE payments
        SET status = p_status,
            paid_at = p_paid_at,
            reference = NVL(p_reference, reference),
            notes = NVL(p_notes, notes)
        WHERE id = p_id;
        COMMIT;
    END update_payment;

    -- Departments
    PROCEDURE create_department(
        p_name IN VARCHAR2,
        p_description IN VARCHAR2,
        p_service_scope IN VARCHAR2,
        p_is_active IN NUMBER,
        p_is_featured IN NUMBER,
        p_featured_order IN NUMBER,
        p_image_path IN VARCHAR2,
        p_id OUT NUMBER
    ) IS
    BEGIN
        INSERT INTO departments (name, description, service_scope, is_active, is_featured, featured_order, image_path)
        VALUES (p_name, p_description, p_service_scope, p_is_active, p_is_featured, p_featured_order, p_image_path)
        RETURNING id INTO p_id;
        COMMIT;
    END create_department;

    PROCEDURE update_department(
        p_id IN NUMBER,
        p_name IN VARCHAR2,
        p_description IN VARCHAR2,
        p_service_scope IN VARCHAR2,
        p_is_active IN NUMBER,
        p_is_featured IN NUMBER,
        p_featured_order IN NUMBER,
        p_image_path IN VARCHAR2
    ) IS
    BEGIN
        UPDATE departments
        SET name = NVL(p_name, name),
            description = NVL(p_description, description),
            service_scope = NVL(p_service_scope, service_scope),
            is_active = NVL(p_is_active, is_active),
            is_featured = NVL(p_is_featured, is_featured),
            featured_order = NVL(p_featured_order, featured_order),
            image_path = NVL(p_image_path, image_path)
        WHERE id = p_id;
        COMMIT;
    END update_department;

    -- Doctors
    PROCEDURE create_doctor_user(
        p_name IN VARCHAR2,
        p_email IN VARCHAR2,
        p_password IN VARCHAR2,
        p_user_id OUT NUMBER
    ) IS
    BEGIN
        INSERT INTO users (name, email, password, role, is_active)
        VALUES (p_name, p_email, p_password, 'doctor', 1)
        RETURNING id INTO p_user_id;
        COMMIT;
    END create_doctor_user;

    PROCEDURE create_doctor_profile(
        p_user_id IN NUMBER,
        p_department_id IN NUMBER,
        p_name IN VARCHAR2,
        p_slug IN VARCHAR2,
        p_specialty IN VARCHAR2,
        p_qualification IN VARCHAR2,
        p_experience_years IN NUMBER,
        p_consultation_fee IN NUMBER,
        p_about IN VARCHAR2,
        p_online_available_days IN VARCHAR2,
        p_offline_available_days IN VARCHAR2,
        p_available_days IN VARCHAR2,
        p_slot_minutes IN NUMBER,
        p_is_active IN NUMBER,
        p_is_featured IN NUMBER,
        p_featured_order IN NUMBER,
        p_photo_path IN VARCHAR2,
        p_online_available IN NUMBER,
        p_offline_available IN NUMBER,
        p_doctor_id OUT NUMBER
    ) IS
    BEGIN
        INSERT INTO doctors (
            user_id, department_id, name, slug, specialty, qualification, experience_years,
            consultation_fee, bio, online_available_days, offline_available_days, available_days,
            slot_minutes, is_active, is_featured, featured_order, photo_path, online_available, offline_available
        ) VALUES (
            p_user_id, p_department_id, p_name, p_slug, p_specialty, p_qualification, p_experience_years,
            p_consultation_fee, p_about, p_online_available_days, p_offline_available_days, p_available_days,
            p_slot_minutes, p_is_active, p_is_featured, p_featured_order, p_photo_path, p_online_available, p_offline_available
        ) RETURNING id INTO p_doctor_id;
        COMMIT;
    END create_doctor_profile;

    PROCEDURE update_doctor_profile(
        p_id IN NUMBER,
        p_department_id IN NUMBER,
        p_specialty IN VARCHAR2,
        p_qualification IN VARCHAR2,
        p_experience_years IN NUMBER,
        p_consultation_fee IN NUMBER,
        p_about IN VARCHAR2,
        p_online_available_days IN VARCHAR2,
        p_offline_available_days IN VARCHAR2,
        p_slot_minutes IN NUMBER,
        p_is_active IN NUMBER,
        p_is_featured IN NUMBER,
        p_featured_order IN NUMBER,
        p_photo_path IN VARCHAR2
    ) IS
    BEGIN
        UPDATE doctors
        SET department_id = NVL(p_department_id, department_id),
            specialty = NVL(p_specialty, specialty),
            qualification = NVL(p_qualification, qualification),
            experience_years = NVL(p_experience_years, experience_years),
            consultation_fee = NVL(p_consultation_fee, consultation_fee),
            bio = NVL(p_about, bio),
            online_available_days = NVL(p_online_available_days, online_available_days),
            offline_available_days = NVL(p_offline_available_days, offline_available_days),
            slot_minutes = NVL(p_slot_minutes, slot_minutes),
            is_active = NVL(p_is_active, is_active),
            is_featured = NVL(p_is_featured, is_featured),
            featured_order = NVL(p_featured_order, featured_order),
            photo_path = NVL(p_photo_path, photo_path)
        WHERE id = p_id;
        COMMIT;
    END update_doctor_profile;

    PROCEDURE delete_doctor(
        p_id IN NUMBER
    ) IS
        v_user_id NUMBER;
    BEGIN
        SELECT user_id INTO v_user_id FROM doctors WHERE id = p_id;
        DELETE FROM doctors WHERE id = p_id;
        IF v_user_id IS NOT NULL THEN
            DELETE FROM users WHERE id = v_user_id AND role = 'doctor';
        END IF;
        COMMIT;
    END delete_doctor;

    -- Facility Rooms
    PROCEDURE create_facility_room(
        p_room_number IN VARCHAR2,
        p_room_type IN VARCHAR2,
        p_capacity IN NUMBER,
        p_is_active IN NUMBER,
        p_id OUT NUMBER
    ) IS
    BEGIN
        INSERT INTO facility_rooms (room_number, room_type, capacity, is_active)
        VALUES (p_room_number, p_room_type, p_capacity, NVL(p_is_active, 1))
        RETURNING id INTO p_id;
        COMMIT;
    END create_facility_room;

    PROCEDURE update_facility_room(
        p_id IN NUMBER,
        p_room_number IN VARCHAR2,
        p_room_type IN VARCHAR2,
        p_capacity IN NUMBER,
        p_is_active IN NUMBER
    ) IS
    BEGIN
        UPDATE facility_rooms
        SET room_number = NVL(p_room_number, room_number),
            room_type = NVL(p_room_type, room_type),
            capacity = NVL(p_capacity, capacity),
            is_active = NVL(p_is_active, is_active)
        WHERE id = p_id;
        COMMIT;
    END update_facility_room;

    -- Medicines
    PROCEDURE create_medicine(
        p_name IN VARCHAR2,
        p_description IN VARCHAR2,
        p_medicine_group IN VARCHAR2,
        p_strength IN VARCHAR2,
        p_amount IN VARCHAR2,
        p_manufacturer IN VARCHAR2,
        p_price IN NUMBER,
        p_requires_prescription IN NUMBER,
        p_stock_quantity IN NUMBER,
        p_is_active IN NUMBER,
        p_image_path IN VARCHAR2,
        p_id OUT NUMBER
    ) IS
    BEGIN
        INSERT INTO medicines (
            name, description, medicine_group, strength, amount, manufacturer,
            price, requires_prescription, stock_quantity, is_active, image_path
        ) VALUES (
            p_name, p_description, p_medicine_group, p_strength, p_amount, p_manufacturer,
            p_price, p_requires_prescription, p_stock_quantity, NVL(p_is_active, 1), p_image_path
        ) RETURNING id INTO p_id;
        COMMIT;
    END create_medicine;

    PROCEDURE update_medicine(
        p_id IN NUMBER,
        p_name IN VARCHAR2,
        p_description IN VARCHAR2,
        p_medicine_group IN VARCHAR2,
        p_strength IN VARCHAR2,
        p_amount IN VARCHAR2,
        p_manufacturer IN VARCHAR2,
        p_price IN NUMBER,
        p_requires_prescription IN NUMBER,
        p_stock_quantity IN NUMBER,
        p_is_active IN NUMBER,
        p_image_path IN VARCHAR2
    ) IS
    BEGIN
        UPDATE medicines
        SET name = NVL(p_name, name),
            description = NVL(p_description, description),
            medicine_group = NVL(p_medicine_group, medicine_group),
            strength = NVL(p_strength, strength),
            amount = NVL(p_amount, amount),
            manufacturer = NVL(p_manufacturer, manufacturer),
            price = NVL(p_price, price),
            requires_prescription = NVL(p_requires_prescription, requires_prescription),
            stock_quantity = NVL(p_stock_quantity, stock_quantity),
            is_active = NVL(p_is_active, is_active),
            image_path = NVL(p_image_path, image_path)
        WHERE id = p_id;
        COMMIT;
    END update_medicine;

    -- Orders
    PROCEDURE update_order_payment(
        p_id IN NUMBER,
        p_payment_method IN VARCHAR2,
        p_payment_status IN VARCHAR2,
        p_payment_reference IN VARCHAR2
    ) IS
    BEGIN
        UPDATE medicine_orders
        SET payment_method = NVL(p_payment_method, payment_method),
            payment_status = NVL(p_payment_status, payment_status),
            payment_reference = NVL(p_payment_reference, payment_reference)
        WHERE id = p_id;
        COMMIT;
    END update_order_payment;

    PROCEDURE update_medicine_order_status(
        p_id IN NUMBER,
        p_status IN VARCHAR2,
        p_payment_status IN VARCHAR2
    ) IS
    BEGIN
        UPDATE medicine_orders
        SET status = NVL(p_status, status),
            payment_status = NVL(p_payment_status, payment_status)
        WHERE id = p_id;
        COMMIT;
    END update_medicine_order_status;

    PROCEDURE update_order_payment_token(
        p_id IN NUMBER,
        p_payment_callback_token IN VARCHAR2
    ) IS
    BEGIN
        UPDATE medicine_orders
        SET payment_callback_token = p_payment_callback_token
        WHERE id = p_id;
        COMMIT;
    END update_order_payment_token;

    PROCEDURE update_order_details(
        p_id IN NUMBER,
        p_delivery_address IN VARCHAR2,
        p_phone IN VARCHAR2,
        p_status IN VARCHAR2
    ) IS
    BEGIN
        UPDATE medicine_orders
        SET delivery_address = NVL(p_delivery_address, delivery_address),
            phone = NVL(p_phone, phone),
            status = NVL(p_status, status)
        WHERE id = p_id;
        COMMIT;
    END update_order_details;

    -- Articles
    PROCEDURE create_article(
        p_article_category_id IN NUMBER,
        p_user_id IN NUMBER,
        p_title IN VARCHAR2,
        p_slug IN VARCHAR2,
        p_excerpt IN VARCHAR2,
        p_body IN CLOB,
        p_cover_image_path IN VARCHAR2,
        p_is_featured IN NUMBER,
        p_featured_order IN NUMBER,
        p_is_published IN NUMBER,
        p_publication_status IN VARCHAR2,
        p_published_at IN TIMESTAMP,
        p_article_id OUT NUMBER
    ) IS
    BEGIN
        INSERT INTO articles (
            article_category_id, user_id, title, slug, excerpt, body, 
            cover_image_path, is_featured, featured_order, is_published, 
            publication_status, published_at
        ) VALUES (
            p_article_category_id, p_user_id, p_title, p_slug, p_excerpt, p_body, 
            p_cover_image_path, NVL(p_is_featured, 0), NVL(p_featured_order, 0), NVL(p_is_published, 0), 
            NVL(p_publication_status, 'draft'), p_published_at
        ) RETURNING id INTO p_article_id;
        COMMIT;
    END create_article;

    PROCEDURE update_article(
        p_id IN NUMBER,
        p_article_category_id IN NUMBER,
        p_title IN VARCHAR2,
        p_slug IN VARCHAR2,
        p_excerpt IN VARCHAR2,
        p_body IN CLOB,
        p_cover_image_path IN VARCHAR2,
        p_is_featured IN NUMBER,
        p_featured_order IN NUMBER,
        p_is_published IN NUMBER,
        p_publication_status IN VARCHAR2,
        p_reviewed_by_user_id IN NUMBER,
        p_reviewed_at IN TIMESTAMP,
        p_published_at IN TIMESTAMP
    ) IS
    BEGIN
        UPDATE articles
        SET article_category_id = NVL(p_article_category_id, article_category_id),
            title = NVL(p_title, title),
            slug = NVL(p_slug, slug),
            excerpt = NVL(p_excerpt, excerpt),
            body = NVL(p_body, body),
            cover_image_path = NVL(p_cover_image_path, cover_image_path),
            is_featured = NVL(p_is_featured, is_featured),
            featured_order = NVL(p_featured_order, featured_order),
            is_published = NVL(p_is_published, is_published),
            publication_status = NVL(p_publication_status, publication_status),
            reviewed_by_user_id = NVL(p_reviewed_by_user_id, reviewed_by_user_id),
            reviewed_at = NVL(p_reviewed_at, reviewed_at),
            published_at = NVL(p_published_at, published_at)
        WHERE id = p_id;
        COMMIT;
    END update_article;

    -- Users
    PROCEDURE update_user(
        p_id IN NUMBER,
        p_name IN VARCHAR2,
        p_email IN VARCHAR2,
        p_password IN VARCHAR2
    ) IS
    BEGIN
        UPDATE users
        SET name = NVL(p_name, name),
            email = NVL(p_email, email),
            password = NVL(p_password, password)
        WHERE id = p_id;
        COMMIT;
    END update_user;

    PROCEDURE create_patient_user(
        p_name IN VARCHAR2,
        p_email IN VARCHAR2,
        p_password IN VARCHAR2,
        p_user_id OUT NUMBER
    ) IS
    BEGIN
        INSERT INTO users (name, email, password, role)
        VALUES (p_name, p_email, p_password, 'patient')
        RETURNING id INTO p_user_id;
        COMMIT;
    END create_patient_user;

    PROCEDURE update_patient_profile(
        p_user_id IN NUMBER,
        p_dob IN DATE,
        p_gender IN VARCHAR2,
        p_height IN NUMBER,
        p_weight IN NUMBER,
        p_conditions IN VARCHAR2,
        p_allergies IN VARCHAR2,
        p_notes IN VARCHAR2
    ) IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*) INTO v_count FROM patient_profiles WHERE user_id = p_user_id;
        IF v_count = 0 THEN
            INSERT INTO patient_profiles (user_id, date_of_birth, gender, height_cm, weight_kg, known_conditions, allergies, medical_notes)
            VALUES (p_user_id, p_dob, p_gender, p_height, p_weight, p_conditions, p_allergies, p_notes);
        ELSE
            UPDATE patient_profiles
            SET date_of_birth = p_dob,
                gender = p_gender,
                height_cm = p_height,
                weight_kg = p_weight,
                known_conditions = p_conditions,
                allergies = p_allergies,
                medical_notes = p_notes
            WHERE user_id = p_user_id;
        END IF;
        COMMIT;
    END update_patient_profile;

    -- QnA
    PROCEDURE update_question_status(
        p_id IN NUMBER,
        p_status IN VARCHAR2
    ) IS
    BEGIN
        UPDATE qna_questions
        SET status = p_status
        WHERE id = p_id;
        COMMIT;
    END update_question_status;

    PROCEDURE reschedule_appointment(
        p_id IN NUMBER,
        p_scheduled_for IN TIMESTAMP
    ) IS
    BEGIN
        UPDATE appointments
        SET scheduled_for = p_scheduled_for,
            status = 'pending'
        WHERE id = p_id;
        COMMIT;
    END reschedule_appointment;

    PROCEDURE create_qna_question(
        p_user_id IN NUMBER,
        p_title IN VARCHAR2,
        p_question IN VARCHAR2,
        p_status IN VARCHAR2,
        p_id OUT NUMBER
    ) IS
    BEGIN
        INSERT INTO qna_questions (user_id, title, question, status)
        VALUES (p_user_id, p_title, p_question, p_status)
        RETURNING id INTO p_id;
        COMMIT;
    END create_qna_question;

    PROCEDURE create_qna_answer(
        p_question_id IN NUMBER,
        p_user_id IN NUMBER,
        p_answer IN VARCHAR2,
        p_is_official IN NUMBER,
        p_id OUT NUMBER
    ) IS
    BEGIN
        INSERT INTO qna_answers (qna_question_id, user_id, answer, is_official)
        VALUES (p_question_id, p_user_id, p_answer, NVL(p_is_official, 0))
        RETURNING id INTO p_id;
        COMMIT;
    END create_qna_answer;

    -- Payments
    PROCEDURE update_payment(
        p_id IN NUMBER,
        p_status IN VARCHAR2,
        p_paid_at IN TIMESTAMP,
        p_reference IN VARCHAR2
    ) IS
    BEGIN
        UPDATE payments
        SET status = NVL(p_status, status),
            paid_at = NVL(p_paid_at, paid_at),
            reference = NVL(p_reference, reference)
        WHERE id = p_id;
        COMMIT;
    END update_payment;

    PROCEDURE create_audit_log(
        p_actor_user_id IN NUMBER,
        p_action IN VARCHAR2,
        p_entity_type IN VARCHAR2,
        p_entity_id IN NUMBER,
        p_old_values IN VARCHAR2,
        p_new_values IN VARCHAR2,
        p_meta IN VARCHAR2,
        p_ip_address IN VARCHAR2,
        p_user_agent IN VARCHAR2
    ) IS
    BEGIN
        INSERT INTO audit_logs (
            actor_user_id, action, entity_type, entity_id, 
            old_values, new_values, meta, ip_address, user_agent
        ) VALUES (
            p_actor_user_id, p_action, p_entity_type, p_entity_id, 
            p_old_values, p_new_values, p_meta, p_ip_address, p_user_agent
        );
        COMMIT;
    END create_audit_log;

    PROCEDURE update_user_password(
        p_user_id IN NUMBER,
        p_password IN VARCHAR2
    ) IS
    BEGIN
        UPDATE users
        SET password = p_password
        WHERE id = p_user_id;
        COMMIT;
    END update_user_password;

END pkg_crud_writes;
/
