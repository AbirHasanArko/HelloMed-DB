-- =========================================================================
-- HelloMed PL/SQL Execution Demonstration
--
-- This script demonstrates how a developer (or a backend framework)
-- interacts directly with the PL/SQL packages to perform business logic.
-- =========================================================================

SET SERVEROUTPUT ON;

DECLARE
    v_new_user_id NUMBER;
    v_appointment_id NUMBER;
    v_order_id NUMBER;
BEGIN
    DBMS_OUTPUT.PUT_LINE('--- Starting HelloMed Workflow Demonstration ---');

    -- ==========================================================
    -- 1. Register a New Patient
    -- ==========================================================
    DBMS_OUTPUT.PUT_LINE('1. Registering new patient: Jane Doe');
    
    pkg_users.register_user(
        p_name => 'Jane Doe',
        p_email => 'jane@example.com',
        p_password => 'hashed_password_123',
        p_role => 'patient',
        p_user_id => v_new_user_id
    );
    
    DBMS_OUTPUT.PUT_LINE('   -> Success! New User ID: ' || v_new_user_id);

    -- ==========================================================
    -- 2. Book an Appointment
    -- ==========================================================
    DBMS_OUTPUT.PUT_LINE('2. Booking an appointment for Jane');
    
    pkg_appointments.book_appointment(
        p_user_id => v_new_user_id,
        p_doctor_id => 1,          -- Assuming Dr. Smith is ID 1
        p_department_id => 1,      -- Assuming Cardiology is ID 1
        p_service_id => NULL,
        p_patient_name => 'Jane Doe',
        p_patient_email => 'jane@example.com',
        p_patient_phone => '555-0199',
        p_service_mode => 'online',
        p_scheduled_for => SYSTIMESTAMP + INTERVAL '1' DAY, -- Tomorrow
        p_reason => 'Routine Heart Checkup',
        p_appointment_id => v_appointment_id
    );
    
    DBMS_OUTPUT.PUT_LINE('   -> Success! Appointment ID: ' || v_appointment_id);

    -- ==========================================================
    -- 3. Create a Pharmacy Order
    -- ==========================================================
    DBMS_OUTPUT.PUT_LINE('3. Creating a Pharmacy Order for Jane');
    
    pkg_pharmacy.create_order(
        p_user_id => v_new_user_id,
        p_patient_name => 'Jane Doe',
        p_patient_phone => '555-0199',
        p_shipping_address => '123 Health St.',
        p_payment_method => 'cash_on_delivery',
        p_order_id => v_order_id
    );
    
    DBMS_OUTPUT.PUT_LINE('   -> Order created. Order ID: ' || v_order_id);
    
    -- Add items to the order (This will trigger the trigger to auto-deduct stock)
    DBMS_OUTPUT.PUT_LINE('   -> Adding Paracetamol (Medicine ID: 1, Qty: 2)');
    pkg_pharmacy.add_order_item(
        p_order_id => v_order_id,
        p_medicine_id => 1,
        p_quantity => 2,
        p_unit_price => 5.00
    );
    
    DBMS_OUTPUT.PUT_LINE('   -> Order processing complete!');
    DBMS_OUTPUT.PUT_LINE('----------------------------------------------');

EXCEPTION
    WHEN OTHERS THEN
        DBMS_OUTPUT.PUT_LINE('ERROR OCCURRED: ' || SQLERRM);
        ROLLBACK;
END;
/
