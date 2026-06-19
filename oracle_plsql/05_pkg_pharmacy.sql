-- ==========================================
-- 05_pkg_pharmacy.sql
-- ==========================================

CREATE OR REPLACE PACKAGE pkg_pharmacy AS
    PROCEDURE create_order(
        p_user_id IN NUMBER,
        p_delivery_address IN VARCHAR2,
        p_phone IN VARCHAR2,
        p_payment_method IN VARCHAR2,
        p_payment_callback_token IN VARCHAR2,
        p_payment_status IN VARCHAR2,
        p_notes IN VARCHAR2,
        p_prescription_path IN VARCHAR2,
        p_contains_prescription_items IN NUMBER,
        p_inventory_committed_at IN TIMESTAMP,
        p_order_id OUT NUMBER,
        p_order_number OUT VARCHAR2
    );
    
    PROCEDURE add_order_item(
        p_order_id IN NUMBER,
        p_medicine_id IN NUMBER,
        p_quantity IN NUMBER,
        p_deduct_stock IN NUMBER
    );

    PROCEDURE update_order_status(
        p_order_id IN NUMBER,
        p_status IN VARCHAR2
    );
END pkg_pharmacy;
/

CREATE OR REPLACE PACKAGE BODY pkg_pharmacy AS

    PROCEDURE create_order(
        p_user_id IN NUMBER,
        p_delivery_address IN VARCHAR2,
        p_phone IN VARCHAR2,
        p_payment_method IN VARCHAR2,
        p_payment_callback_token IN VARCHAR2,
        p_payment_status IN VARCHAR2,
        p_notes IN VARCHAR2,
        p_prescription_path IN VARCHAR2,
        p_contains_prescription_items IN NUMBER,
        p_inventory_committed_at IN TIMESTAMP,
        p_order_id OUT NUMBER,
        p_order_number OUT VARCHAR2
    ) IS
        v_order_number VARCHAR2(255);
    BEGIN
        -- Generate a simple order number using sysdate and user_id
        v_order_number := 'MED-' || TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS') || '-' || TRUNC(DBMS_RANDOM.VALUE(100,999));
        p_order_number := v_order_number;

        INSERT INTO medicine_orders (
            user_id, order_number, status, delivery_address, phone, total_amount,
            payment_method, payment_callback_token, payment_status, notes,
            prescription_path, contains_prescription_items, inventory_committed_at
        ) VALUES (
            p_user_id, v_order_number, 'pending', p_delivery_address, p_phone, 0,
            p_payment_method, p_payment_callback_token, p_payment_status, p_notes,
            p_prescription_path, p_contains_prescription_items, p_inventory_committed_at
        ) RETURNING id INTO p_order_id;
        COMMIT;
    END create_order;

    PROCEDURE add_order_item(
        p_order_id IN NUMBER,
        p_medicine_id IN NUMBER,
        p_quantity IN NUMBER,
        p_deduct_stock IN NUMBER
    ) IS
        v_price NUMBER(10,2);
        v_line_total NUMBER(10,2);
    BEGIN
        -- Get medicine price
        SELECT price INTO v_price FROM medicines WHERE id = p_medicine_id;
        
        v_line_total := v_price * p_quantity;

        INSERT INTO medicine_order_items (
            medicine_order_id, medicine_id, quantity, unit_price, line_total
        ) VALUES (
            p_order_id, p_medicine_id, p_quantity, v_price, v_line_total
        );
        
        -- Update order total amount
        UPDATE medicine_orders
        SET total_amount = total_amount + v_line_total
        WHERE id = p_order_id;
        
        -- Deduct stock
        IF p_deduct_stock = 1 THEN
            UPDATE medicines
            SET stock_quantity = stock_quantity - p_quantity
            WHERE id = p_medicine_id;
        END IF;
        
        COMMIT;
    END add_order_item;

    PROCEDURE update_order_status(
        p_order_id IN NUMBER,
        p_status IN VARCHAR2
    ) IS
    BEGIN
        UPDATE medicine_orders
        SET status = p_status
        WHERE id = p_order_id;
        COMMIT;
    END update_order_status;

END pkg_pharmacy;
/
