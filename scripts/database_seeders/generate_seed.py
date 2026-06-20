import csv
import os

def format_val(val, col_type='string'):
    if val is None or val == '':
        return 'NULL'
    if col_type == 'string' or col_type == 'clob':
        # escape single quotes
        val = val.replace("'", "''")
        return f"'{val}'"
    elif col_type == 'date':
        return f"TO_TIMESTAMP('{val}', 'YYYY-MM-DD HH24:MI:SS')"
    else: # number
        return str(val)

col_types = {
    'created_at': 'date', 'updated_at': 'date', 'reviewed_at': 'date', 'published_at': 'date',
    'id': 'number', 'department_id': 'number', 'user_id': 'number', 'article_category_id': 'number',
    'experience_years': 'number', 'consultation_fee': 'number', 'online_fee': 'number', 'offline_fee': 'number',
    'online_available': 'number', 'offline_available': 'number', 'slot_minutes': 'number',
    'is_featured': 'number', 'is_active': 'number', 'featured_order': 'number',
    'price': 'number', 'stock_quantity': 'number', 'requires_prescription': 'number',
    'is_published': 'number', 'reviewed_by_user_id': 'number',
    'body': 'clob', 'excerpt': 'clob', 'description': 'clob', 'bio': 'clob'
}

def generate_inserts(filepath, table_name, exclude_cols=[]):
    sql_statements = []
    if not os.path.exists(filepath):
        print(f"File {filepath} not found.")
        return []
    with open(filepath, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        for row in reader:
            cols = []
            vals = []
            for k, v in row.items():
                if k in exclude_cols:
                    continue
                cols.append(f'"{k.upper()}"')
                ctype = col_types.get(k, 'string')
                vals.append(format_val(v, ctype))
            
            cols_str = ', '.join(cols)
            vals_str = ', '.join(vals)
            sql = f"INSERT INTO {table_name} ({cols_str}) VALUES ({vals_str});"
            sql_statements.append(sql)
    return sql_statements

def main():
    base_dir = r"d:\Documents\HelloMed-DB"
    
    # 1. Users, Inventory, Facility Rooms (Static)
    static_sql = """-- ==========================================
-- 07_seed_data.sql (Mega Seeding File)
-- ==========================================

SET DEFINE OFF;

-- Disable constraints temporarily if needed, or assume empty schema.
-- (Oracle doesn't have a simple disable all, so we assume careful insertion order)

-- Static Users
INSERT INTO users (id, name, email, password, role) VALUES (1, 'Admin User', 'admin@hellomed.test', '$2y$12$zEPg4pmkNqFW7CKIMbNcN.HJJnnZ3CZuCPoETD87qONV60j9o1HWS', 'admin');
INSERT INTO users (id, name, email, password, role) VALUES (2, 'Staff User', 'staff@hellomed.test', '$2y$12$zEPg4pmkNqFW7CKIMbNcN.HJJnnZ3CZuCPoETD87qONV60j9o1HWS', 'staff');
INSERT INTO users (id, name, email, password, role) VALUES (3, 'Pharmacist User', 'pharmacist@hellomed.test', '$2y$12$zEPg4pmkNqFW7CKIMbNcN.HJJnnZ3CZuCPoETD87qONV60j9o1HWS', 'pharmacist');
INSERT INTO users (id, name, email, password, role) VALUES (4, 'Patient User', 'patient@hellomed.test', '$2y$12$zEPg4pmkNqFW7CKIMbNcN.HJJnnZ3CZuCPoETD87qONV60j9o1HWS', 'patient');
INSERT INTO users (id, name, email, password, role) VALUES (5, 'Doctor User', 'doctor@hellomed.test', '$2y$12$zEPg4pmkNqFW7CKIMbNcN.HJJnnZ3CZuCPoETD87qONV60j9o1HWS', 'doctor');

-- Dummy users to satisfy doctor/article FKs (since we don't have users.csv)
"""
    doctor_users = {}
    with open(os.path.join(base_dir, 'doctors.csv'), 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        for row in reader:
            if row['user_id']:
                doctor_users[row['user_id']] = row['name']

    for i in range(6, 20):
        name = doctor_users.get(str(i), f'Dummy User {i}')
        name = name.replace("'", "''")
        static_sql += f"INSERT INTO users (id, name, email, password, role) VALUES ({i}, '{name}', 'dummy{i}@hellomed.test', '$2y$12$zEPg4pmkNqFW7CKIMbNcN.HJJnnZ3CZuCPoETD87qONV60j9o1HWS', 'doctor');\n"

    static_sql += "\n-- Dummy Article Categories\n"
    for i in range(1, 10):
        static_sql += f"INSERT INTO article_categories (id, name, slug, description) VALUES ({i}, 'Category {i}', 'category-{i}', 'Description');\n"

    static_sql += "\n-- Departments\n"
    
    depts_sql = generate_inserts(os.path.join(base_dir, 'departments.csv'), 'departments')
    
    docs_sql = generate_inserts(os.path.join(base_dir, 'doctors.csv'), 'doctors')
    
    meds_sql = generate_inserts(os.path.join(base_dir, 'medicines.csv'), 'medicines', exclude_cols=['buying_price'])
    
    arts_sql = generate_inserts(os.path.join(base_dir, 'articles.csv'), 'articles')
    
    inventory_sql = """
-- Inventory
INSERT INTO inventory_items (id, name, category, quantity, unit, location, status) VALUES (1, 'Surgical Masks', 'PPE', 5000, 'boxes', 'Main Storage', 'available');
INSERT INTO inventory_items (id, name, category, quantity, unit, location, status) VALUES (2, 'Syringes', 'Medical Supplies', 200, 'boxes', 'Storage B', 'low_stock');

-- Facility Rooms
INSERT INTO facility_rooms (id, room_number, room_type, capacity, is_active) VALUES (1, 'LAB-01', 'Lab', 5, 1);
INSERT INTO facility_rooms (id, room_number, room_type, capacity, is_active) VALUES (2, 'OT-A', 'Operation Theatre', 1, 1);
INSERT INTO facility_rooms (id, room_number, room_type, capacity, is_active) VALUES (3, 'OT-B', 'Operation Theatre', 1, 1);
INSERT INTO facility_rooms (id, room_number, room_type, capacity, is_active) VALUES (4, 'ICU-01', 'ICU', 1, 1);

COMMIT;
"""
    
    out_path = os.path.join(base_dir, 'oracle_plsql', '07_seed_data.sql')
    with open(out_path, 'w', encoding='utf-8') as out:
        out.write(static_sql)
        out.write("\n")
        out.write('\n'.join(depts_sql))
        out.write("\n\n-- Doctors\n")
        out.write('\n'.join(docs_sql))
        out.write("\n\n-- Medicines\n")
        out.write('\n'.join(meds_sql))
        out.write("\n\n-- Articles\n")
        out.write('\n'.join(arts_sql))
        out.write("\n")
        out.write(inventory_sql)
        
    print(f"Successfully generated {out_path}")

if __name__ == '__main__':
    main()
