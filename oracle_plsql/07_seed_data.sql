-- ==========================================
-- 07_seed_data.sql
-- ==========================================

-- Users
-- Note: In a real system, passwords should be hashed using bcrypt or similar.
INSERT INTO users (name, email, password, role) VALUES ('Admin User', 'admin@hellomed.test', '$2y$12$zEPg4pmkNqFW7CKIMbNcN.HJJnnZ3CZuCPoETD87qONV60j9o1HWS', 'admin');
INSERT INTO users (name, email, password, role) VALUES ('Staff User', 'staff@hellomed.test', '$2y$12$zEPg4pmkNqFW7CKIMbNcN.HJJnnZ3CZuCPoETD87qONV60j9o1HWS', 'staff');
INSERT INTO users (name, email, password, role) VALUES ('Pharmacist User', 'pharmacist@hellomed.test', '$2y$12$zEPg4pmkNqFW7CKIMbNcN.HJJnnZ3CZuCPoETD87qONV60j9o1HWS', 'pharmacist');
INSERT INTO users (name, email, password, role) VALUES ('Patient User', 'patient@hellomed.test', '$2y$12$zEPg4pmkNqFW7CKIMbNcN.HJJnnZ3CZuCPoETD87qONV60j9o1HWS', 'patient');
INSERT INTO users (name, email, password, role) VALUES ('Doctor User', 'doctor@hellomed.test', '$2y$12$zEPg4pmkNqFW7CKIMbNcN.HJJnnZ3CZuCPoETD87qONV60j9o1HWS', 'doctor');

-- Departments
INSERT INTO departments (name, slug, description, service_scope) VALUES ('Cardiology', 'cardiology', 'Heart and blood vessel diseases.', 'both');
INSERT INTO departments (name, slug, description, service_scope) VALUES ('Neurology', 'neurology', 'Disorders of the nervous system.', 'both');

-- Doctors
INSERT INTO doctors (department_id, user_id, name, slug, specialty, experience_years, consultation_fee)
VALUES (
    (SELECT id FROM departments WHERE slug = 'cardiology'),
    (SELECT id FROM users WHERE email = 'doctor@hellomed.test'),
    'Dr. John Doe', 'dr-john-doe', 'Cardiologist', 10, 50.00
);

-- Medicines
INSERT INTO medicines (name, slug, description, price, stock_quantity, requires_prescription)
VALUES ('Paracetamol', 'paracetamol', 'Fever and pain relief', 5.00, 1000, 0);

INSERT INTO medicines (name, slug, description, price, stock_quantity, requires_prescription)
VALUES ('Amoxicillin', 'amoxicillin', 'Antibiotic', 15.00, 500, 1);

-- Inventory
INSERT INTO inventory_items (name, category, quantity, unit, location, status) 
VALUES ('Surgical Masks', 'PPE', 5000, 'boxes', 'Main Storage', 'available');
INSERT INTO inventory_items (name, category, quantity, unit, location, status) 
VALUES ('Syringes', 'Medical Supplies', 200, 'boxes', 'Storage B', 'low_stock');

-- Facility Rooms
INSERT INTO facility_rooms (room_number, room_type, capacity, is_active) 
VALUES ('LAB-01', 'Lab', 5, 1);
INSERT INTO facility_rooms (room_number, room_type, capacity, is_active) 
VALUES ('OT-A', 'Operation Theatre', 1, 1);
INSERT INTO facility_rooms (room_number, room_type, capacity, is_active) 
VALUES ('OT-B', 'Operation Theatre', 1, 1);
INSERT INTO facility_rooms (room_number, room_type, capacity, is_active) 
VALUES ('ICU-01', 'ICU', 1, 1);

COMMIT;
