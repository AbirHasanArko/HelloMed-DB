-- ==========================================
-- 05_pkg_pharmacy.sql
-- ==========================================

CREATE OR REPLACE PACKAGE pkg_pharmacy AS
    PROCEDURE create_order(
        p_user_id IN NUMBER,
        p_delivery_address IN VARCHAR2,
        p_phone IN VARCHAR2,
        p_order_id OUT NUMBER
    );
    
    PROCEDURE add_order_item(
        p_order_id IN NUMBER,
        p_medicine_id IN NUMBER,
        p_quantity IN NUMBER
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
        p_order_id OUT NUMBER
    ) IS
        v_order_number VARCHAR2(255);
    BEGIN
        -- Generate a simple order number using sysdate and user_id
        v_order_number := 'ORD-' || TO_CHAR(SYSDATE, 'YYYYMMDDHH24MISS') || '-' || p_user_id;

        INSERT INTO medicine_orders (
            user_id, order_number, status, delivery_address, phone, total_amount
        ) VALUES (
            p_user_id, v_order_number, 'pending', p_delivery_address, p_phone, 0
        ) RETURNING id INTO p_order_id;
        COMMIT;
    END create_order;

    PROCEDURE add_order_item(
        p_order_id IN NUMBER,
        p_medicine_id IN NUMBER,
        p_quantity IN NUMBER
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
        UPDATE medicines
        SET stock_quantity = stock_quantity - p_quantity
        WHERE id = p_medicine_id;
        
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
