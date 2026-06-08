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
