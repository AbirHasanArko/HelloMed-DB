-- ==========================================
-- 10_pkg_inventory.sql
-- ==========================================

CREATE OR REPLACE PACKAGE pkg_inventory AS
    PROCEDURE add_item(
        p_name IN VARCHAR2,
        p_category IN VARCHAR2,
        p_quantity IN NUMBER,
        p_unit IN VARCHAR2,
        p_location IN VARCHAR2,
        p_item_id OUT NUMBER
    );
    
    PROCEDURE get_inventory_alerts(
        p_threshold IN NUMBER,
        p_cursor OUT SYS_REFCURSOR
    );

    PROCEDURE release_inventory_for_order(
        p_order_id IN NUMBER
    );

    PROCEDURE commit_inventory_for_order(
        p_order_id IN NUMBER
    );
    
    PROCEDURE update_stock(
        p_item_id IN NUMBER,
        p_quantity_change IN NUMBER
    );
END pkg_inventory;
/

CREATE OR REPLACE PACKAGE BODY pkg_inventory AS

    PROCEDURE add_item(
        p_name IN VARCHAR2,
        p_category IN VARCHAR2,
        p_quantity IN NUMBER,
        p_unit IN VARCHAR2,
        p_location IN VARCHAR2,
        p_item_id OUT NUMBER
    ) IS
    BEGIN
        INSERT INTO inventory_items (
            name, category, quantity, unit, location, status
        ) VALUES (
            p_name, p_category, p_quantity, p_unit, p_location,
            CASE WHEN p_quantity <= 0 THEN 'out_of_stock' ELSE 'available' END
        ) RETURNING id INTO p_item_id;
        COMMIT;
    END add_item;

    PROCEDURE get_inventory_alerts(
        p_threshold IN NUMBER,
        p_cursor OUT SYS_REFCURSOR
    ) IS
    BEGIN
        OPEN p_cursor FOR
            SELECT * FROM medicines
            WHERE stock_quantity <= p_threshold
            ORDER BY stock_quantity ASC;
    END get_inventory_alerts;

    PROCEDURE release_inventory_for_order(
        p_order_id IN NUMBER
    ) IS
    BEGIN
        FOR rec IN (SELECT medicine_id, quantity FROM medicine_order_items WHERE medicine_order_id = p_order_id) LOOP
            UPDATE medicines
            SET stock_quantity = stock_quantity + rec.quantity
            WHERE id = rec.medicine_id;
        END LOOP;
        
        UPDATE medicine_orders
        SET inventory_released_at = CURRENT_TIMESTAMP
        WHERE id = p_order_id;
        
        COMMIT;
    END release_inventory_for_order;

    PROCEDURE commit_inventory_for_order(
        p_order_id IN NUMBER
    ) IS
        v_stock NUMBER;
        v_name VARCHAR2(255);
    BEGIN
        FOR rec IN (SELECT m.id, m.name, m.stock_quantity, i.quantity 
                    FROM medicine_order_items i 
                    JOIN medicines m ON i.medicine_id = m.id 
                    WHERE i.medicine_order_id = p_order_id) LOOP
            IF rec.stock_quantity < rec.quantity THEN
                RAISE_APPLICATION_ERROR(-20003, 'Insufficient stock for ' || rec.name || ' during payment confirmation.');
            END IF;
            
            UPDATE medicines
            SET stock_quantity = stock_quantity - rec.quantity
            WHERE id = rec.id;
        END LOOP;
        
        UPDATE medicine_orders
        SET inventory_committed_at = CURRENT_TIMESTAMP
        WHERE id = p_order_id;
        
        COMMIT;
    END commit_inventory_for_order;

    PROCEDURE update_stock(
        p_item_id IN NUMBER,
        p_quantity_change IN NUMBER
    ) IS
        v_current_qty NUMBER;
        v_new_qty NUMBER;
    BEGIN
        SELECT quantity INTO v_current_qty
        FROM inventory_items
        WHERE id = p_item_id
        FOR UPDATE;
        
        v_new_qty := v_current_qty + p_quantity_change;
        IF v_new_qty < 0 THEN
            v_new_qty := 0;
        END IF;
        
        UPDATE inventory_items
        SET quantity = v_new_qty,
            status = CASE 
                WHEN v_new_qty <= 0 THEN 'out_of_stock'
                WHEN v_new_qty <= 10 THEN 'low_stock'
                ELSE 'available'
            END
        WHERE id = p_item_id;
        
        COMMIT;
    END update_stock;

END pkg_inventory;
/
