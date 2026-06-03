-- ==========================================
-- 07_seed_data.sql
-- ==========================================

-- Users
-- Note: In a real system, passwords should be hashed using bcrypt or similar.
INSERT INTO users (name, email, password, role) VALUES ('Admin User', 'admin@hellomed.test', 'password123', 'admin');
INSERT INTO users (name, email, password, role) VALUES ('Staff User', 'staff@hellomed.test', 'password123', 'staff');
INSERT INTO users (name, email, password, role) VALUES ('Pharmacist User', 'pharmacist@hellomed.test', 'password123', 'pharmacist');
INSERT INTO users (name, email, password, role) VALUES ('Patient User', 'patient@hellomed.test', 'password123', 'patient');
INSERT INTO users (name, email, password, role) VALUES ('Doctor User', 'doctor@hellomed.test', 'password123', 'doctor');

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

COMMIT;
