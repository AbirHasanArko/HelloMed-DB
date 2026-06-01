-- ==========================================
-- 01_schema.sql
-- Oracle 11g DDL for HelloMed Database
-- ==========================================

-- 1. users
CREATE TABLE users (
    id NUMBER PRIMARY KEY,
    name VARCHAR2(255) NOT NULL,
    email VARCHAR2(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP,
    password VARCHAR2(255) NOT NULL,
    role VARCHAR2(50) DEFAULT 'patient' NOT NULL,
    is_active NUMBER(1) DEFAULT 1 NOT NULL CHECK (is_active IN (0, 1)),
    remember_token VARCHAR2(100),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- sessions
CREATE TABLE sessions (
    id VARCHAR2(255) PRIMARY KEY,
    user_id NUMBER REFERENCES users(id) ON DELETE SET NULL,
    ip_address VARCHAR2(45),
    user_agent VARCHAR2(4000),
    payload CLOB NOT NULL,
    last_activity NUMBER NOT NULL
);

-- 2. departments
CREATE TABLE departments (
    id NUMBER PRIMARY KEY,
    name VARCHAR2(255) NOT NULL,
    slug VARCHAR2(255) NOT NULL UNIQUE,
    description VARCHAR2(4000),
    image_path VARCHAR2(255),
    service_scope VARCHAR2(50) DEFAULT 'both' NOT NULL,
    is_active NUMBER(1) DEFAULT 1 NOT NULL CHECK (is_active IN (0, 1)),
    is_featured NUMBER(1) DEFAULT 0 NOT NULL CHECK (is_featured IN (0, 1)),
    featured_order NUMBER DEFAULT 0 NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- 3. doctors
CREATE TABLE doctors (
    id NUMBER PRIMARY KEY,
    department_id NUMBER NOT NULL REFERENCES departments(id) ON DELETE CASCADE,
    user_id NUMBER REFERENCES users(id) ON DELETE SET NULL,
    name VARCHAR2(255) NOT NULL,
    slug VARCHAR2(255) NOT NULL UNIQUE,
    specialty VARCHAR2(255) NOT NULL,
    bio VARCHAR2(4000),
    qualification VARCHAR2(255),
    experience_years NUMBER DEFAULT 0 NOT NULL,
    consultation_fee NUMBER(10,2) DEFAULT 0 NOT NULL,
    online_fee NUMBER(10,2),
    offline_fee NUMBER(10,2),
    online_available NUMBER(1) DEFAULT 1 NOT NULL CHECK (online_available IN (0, 1)),
    offline_available NUMBER(1) DEFAULT 1 NOT NULL CHECK (offline_available IN (0, 1)),
    clinic_address VARCHAR2(1000),
    photo_path VARCHAR2(255),
    available_days VARCHAR2(4000),
    online_available_days VARCHAR2(4000),
    online_available_from VARCHAR2(20),
    online_available_to VARCHAR2(20),
    offline_available_days VARCHAR2(4000),
    offline_available_from VARCHAR2(20),
    offline_available_to VARCHAR2(20),
    available_from VARCHAR2(20),
    available_to VARCHAR2(20),
    slot_minutes NUMBER DEFAULT 30 NOT NULL,
    is_featured NUMBER(1) DEFAULT 0 NOT NULL CHECK (is_featured IN (0, 1)),
    featured_order NUMBER DEFAULT 0 NOT NULL,
    is_active NUMBER(1) DEFAULT 1 NOT NULL CHECK (is_active IN (0, 1)),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- 4. services
CREATE TABLE services (
    id NUMBER PRIMARY KEY,
    department_id NUMBER NOT NULL REFERENCES departments(id) ON DELETE CASCADE,
    doctor_id NUMBER REFERENCES doctors(id) ON DELETE SET NULL,
    name VARCHAR2(255) NOT NULL,
    slug VARCHAR2(255) NOT NULL UNIQUE,
    description VARCHAR2(4000),
    service_mode VARCHAR2(20) DEFAULT 'both' NOT NULL CHECK (service_mode IN ('online', 'offline', 'both')),
    duration_minutes NUMBER DEFAULT 30 NOT NULL,
    price NUMBER(10,2) DEFAULT 0 NOT NULL,
    is_active NUMBER(1) DEFAULT 1 NOT NULL CHECK (is_active IN (0, 1)),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- 5. appointments
CREATE TABLE appointments (
    id NUMBER PRIMARY KEY,
    user_id NUMBER REFERENCES users(id) ON DELETE SET NULL,
    doctor_id NUMBER NOT NULL REFERENCES doctors(id) ON DELETE CASCADE,
    department_id NUMBER NOT NULL REFERENCES departments(id) ON DELETE CASCADE,
    service_id NUMBER REFERENCES services(id) ON DELETE SET NULL,
    patient_name VARCHAR2(255) NOT NULL,
    patient_email VARCHAR2(255) NOT NULL,
    patient_phone VARCHAR2(255) NOT NULL,
    service_mode VARCHAR2(20) NOT NULL CHECK (service_mode IN ('online', 'offline')),
    scheduled_for TIMESTAMP NOT NULL,
    status VARCHAR2(50) DEFAULT 'pending' NOT NULL CHECK (status IN ('pending', 'confirmed', 'completed', 'cancelled')),
    payment_method VARCHAR2(100) DEFAULT 'none' NOT NULL,
    payment_status VARCHAR2(100) DEFAULT 'not_required' NOT NULL,
    online_meeting_link VARCHAR2(1000),
    reason VARCHAR2(4000) NOT NULL,
    notes VARCHAR2(4000),
    doctor_prescription CLOB,
    prescription_diagnosis CLOB,
    prescription_medicines CLOB,
    prescription_advice CLOB,
    prescription_safety_notes CLOB,
    prescription_follow_up_date DATE,
    prescription_written_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- 6. article_categories
CREATE TABLE article_categories (
    id NUMBER PRIMARY KEY,
    name VARCHAR2(255) NOT NULL,
    slug VARCHAR2(255) NOT NULL UNIQUE,
    description VARCHAR2(4000),
    is_active NUMBER(1) DEFAULT 1 NOT NULL CHECK (is_active IN (0, 1)),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- 7. articles
CREATE TABLE articles (
    id NUMBER PRIMARY KEY,
    article_category_id NUMBER NOT NULL REFERENCES article_categories(id) ON DELETE CASCADE,
    user_id NUMBER REFERENCES users(id) ON DELETE SET NULL,
    title VARCHAR2(255) NOT NULL,
    slug VARCHAR2(255) NOT NULL UNIQUE,
    excerpt VARCHAR2(4000) NOT NULL,
    body CLOB NOT NULL,
    cover_image_path VARCHAR2(255),
    is_featured NUMBER(1) DEFAULT 0 NOT NULL CHECK (is_featured IN (0, 1)),
    featured_order NUMBER DEFAULT 0 NOT NULL,
    is_published NUMBER(1) DEFAULT 0 NOT NULL CHECK (is_published IN (0, 1)),
    publication_status VARCHAR2(30) DEFAULT 'draft' NOT NULL,
    reviewed_by_user_id NUMBER REFERENCES users(id) ON DELETE SET NULL,
    reviewed_at TIMESTAMP,
    published_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- 8. payments
CREATE TABLE payments (
    id NUMBER PRIMARY KEY,
    appointment_id NUMBER NOT NULL REFERENCES appointments(id) ON DELETE CASCADE,
    user_id NUMBER REFERENCES users(id) ON DELETE SET NULL,
    method VARCHAR2(255) NOT NULL,
    amount NUMBER(10,2) DEFAULT 0 NOT NULL,
    status VARCHAR2(50) DEFAULT 'pending' NOT NULL CHECK (status IN ('pending', 'paid', 'failed', 'refunded')),
    reference VARCHAR2(255),
    notes VARCHAR2(4000),
    paid_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- 9. medicines
CREATE TABLE medicines (
    id NUMBER PRIMARY KEY,
    name VARCHAR2(255) NOT NULL,
    medicine_group VARCHAR2(255),
    slug VARCHAR2(255) NOT NULL UNIQUE,
    description VARCHAR2(4000),
    power VARCHAR2(255),
    amount VARCHAR2(255),
    strength VARCHAR2(255),
    image_path VARCHAR2(255),
    manufacturer VARCHAR2(255),
    price NUMBER(10,2) NOT NULL,
    stock_quantity NUMBER DEFAULT 0 NOT NULL,
    requires_prescription NUMBER(1) DEFAULT 0 NOT NULL CHECK (requires_prescription IN (0, 1)),
    is_active NUMBER(1) DEFAULT 1 NOT NULL CHECK (is_active IN (0, 1)),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- 10. medicine_orders
CREATE TABLE medicine_orders (
    id NUMBER PRIMARY KEY,
    user_id NUMBER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    order_number VARCHAR2(255) NOT NULL UNIQUE,
    status VARCHAR2(50) DEFAULT 'pending' NOT NULL CHECK (status IN ('pending', 'processing', 'completed', 'cancelled')),
    total_amount NUMBER(10,2) DEFAULT 0 NOT NULL,
    payment_method VARCHAR2(255) DEFAULT 'cash-on-delivery' NOT NULL,
    payment_callback_token VARCHAR2(100),
    payment_status VARCHAR2(255) DEFAULT 'pending' NOT NULL,
    payment_reference VARCHAR2(255),
    delivery_address VARCHAR2(4000) NOT NULL,
    phone VARCHAR2(30) NOT NULL,
    notes VARCHAR2(4000),
    prescription_path VARCHAR2(255),
    contains_prescription_items NUMBER(1) DEFAULT 0 NOT NULL CHECK (contains_prescription_items IN (0, 1)),
    inventory_committed_at TIMESTAMP,
    inventory_released_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- 11. medicine_order_items
CREATE TABLE medicine_order_items (
    id NUMBER PRIMARY KEY,
    medicine_order_id NUMBER NOT NULL REFERENCES medicine_orders(id) ON DELETE CASCADE,
    medicine_id NUMBER NOT NULL,
    quantity NUMBER NOT NULL,
    unit_price NUMBER(10,2) NOT NULL,
    line_total NUMBER(10,2) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
-- We use a separate foreign key statement for medicine_id to simulate restrictOnDelete (often default behavior if no cascade, but we explicitly enforce)
ALTER TABLE medicine_order_items ADD CONSTRAINT fk_moi_medicine FOREIGN KEY (medicine_id) REFERENCES medicines(id);

-- 12. appointment_chat_messages
CREATE TABLE appointment_chat_messages (
    id NUMBER PRIMARY KEY,
    appointment_id NUMBER NOT NULL REFERENCES appointments(id) ON DELETE CASCADE,
    user_id NUMBER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    message CLOB,
    read_at TIMESTAMP,
    attachment_path VARCHAR2(255),
    attachment_name VARCHAR2(255),
    attachment_mime VARCHAR2(120),
    attachment_size NUMBER,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
CREATE INDEX idx_apm_chat_created ON appointment_chat_messages(appointment_id, created_at);

-- 13. appointment_prescription_items
CREATE TABLE appointment_prescription_items (
    id NUMBER PRIMARY KEY,
    appointment_id NUMBER NOT NULL REFERENCES appointments(id) ON DELETE CASCADE,
    medicine_id NUMBER REFERENCES medicines(id) ON DELETE SET NULL,
    medicine_name VARCHAR2(255) NOT NULL,
    amount VARCHAR2(255),
    dosage VARCHAR2(255),
    intake_time VARCHAR2(255),
    instructions VARCHAR2(4000),
    sort_order NUMBER DEFAULT 1 NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- 14. audit_logs
CREATE TABLE audit_logs (
    id NUMBER PRIMARY KEY,
    actor_user_id NUMBER REFERENCES users(id) ON DELETE SET NULL,
    action VARCHAR2(120) NOT NULL,
    entity_type VARCHAR2(120) NOT NULL,
    entity_id NUMBER,
    old_values CLOB,
    new_values CLOB,
    meta CLOB,
    ip_address VARCHAR2(45),
    user_agent VARCHAR2(4000),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
CREATE INDEX idx_audit_entity ON audit_logs(entity_type, entity_id);
CREATE INDEX idx_audit_action ON audit_logs(action);
CREATE INDEX idx_audit_created ON audit_logs(created_at);

-- 15. notification_logs
CREATE TABLE notification_logs (
    id NUMBER PRIMARY KEY,
    user_id NUMBER REFERENCES users(id) ON DELETE SET NULL,
    recipient_email VARCHAR2(255) NOT NULL,
    channel VARCHAR2(40) DEFAULT 'email' NOT NULL,
    event_key VARCHAR2(120) NOT NULL,
    status VARCHAR2(30) DEFAULT 'pending' NOT NULL,
    attempts NUMBER DEFAULT 0 NOT NULL,
    last_error CLOB,
    notifiable_type VARCHAR2(120),
    notifiable_id NUMBER,
    payload CLOB,
    sent_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
CREATE INDEX idx_notif_event_status ON notification_logs(event_key, status);
CREATE INDEX idx_notif_notifiable ON notification_logs(notifiable_type, notifiable_id);

-- 16. patient_profiles
CREATE TABLE patient_profiles (
    id NUMBER PRIMARY KEY,
    user_id NUMBER NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    allergies VARCHAR2(4000),
    medical_notes VARCHAR2(4000),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- 17. doctor_reviews
CREATE TABLE doctor_reviews (
    id NUMBER PRIMARY KEY,
    doctor_id NUMBER NOT NULL REFERENCES doctors(id) ON DELETE CASCADE,
    user_id NUMBER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    rating NUMBER NOT NULL,
    "COMMENT" CLOB,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    CONSTRAINT uq_doctor_user_review UNIQUE (doctor_id, user_id)
);

-- 18. article_comments
CREATE TABLE article_comments (
    id NUMBER PRIMARY KEY,
    article_id NUMBER NOT NULL REFERENCES articles(id) ON DELETE CASCADE,
    user_id NUMBER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    rating NUMBER,
    "COMMENT" CLOB NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- 19. qna_questions
CREATE TABLE qna_questions (
    id NUMBER PRIMARY KEY,
    user_id NUMBER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR2(255) NOT NULL,
    question CLOB NOT NULL,
    status VARCHAR2(20) DEFAULT 'open' NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
CREATE INDEX idx_qna_status ON qna_questions(status);

-- 20. qna_answers
CREATE TABLE qna_answers (
    id NUMBER PRIMARY KEY,
    qna_question_id NUMBER NOT NULL REFERENCES qna_questions(id) ON DELETE CASCADE,
    user_id NUMBER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    answer CLOB NOT NULL,
    is_official NUMBER(1) DEFAULT 1 NOT NULL CHECK (is_official IN (0, 1)),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- 21. ambulance_requests
CREATE TABLE ambulance_requests (
    id NUMBER PRIMARY KEY,
    user_id NUMBER REFERENCES users(id) ON DELETE SET NULL,
    patient_name VARCHAR2(255) NOT NULL,
    patient_phone VARCHAR2(255) NOT NULL,
    latitude NUMBER(10,8),
    longitude NUMBER(11,8),
    address VARCHAR2(4000),
    status VARCHAR2(50) DEFAULT 'pending' NOT NULL CHECK (status IN ('pending', 'dispatched', 'resolved', 'cancelled')),
    dispatched_at TIMESTAMP,
    resolved_at TIMESTAMP,
    staff_id NUMBER REFERENCES users(id) ON DELETE SET NULL,
    notes VARCHAR2(4000),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
