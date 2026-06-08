# 🗄️ HelloMed Database Schema

This document outlines the complete relational database architecture for the HelloMed platform, running on Oracle Database (11g/19c compatible).

The schema consists of **24 tables** grouped by functional domains. All tables use Oracle auto-increment `SEQUENCES` and `BEFORE INSERT TRIGGERS` for primary keys and `TIMESTAMP` columns for `created_at` / `updated_at`.

---

## 1. Authentication & Users

### `users`
Core user identity and Role-Based Access Control (RBAC).
- `id` (NUMBER, PK)
- `name` (VARCHAR2)
- `email` (VARCHAR2, UNIQUE)
- `password` (VARCHAR2)
- `role` (VARCHAR2) - Enum: `patient`, `doctor`, `staff`, `admin`, `pharmacist`
- `is_active` (NUMBER(1))

### `sessions`
Web application session data.
- `id` (VARCHAR2, PK)
- `user_id` (NUMBER, FK to users)
- `ip_address`, `user_agent`, `payload`, `last_activity`

---

## 2. Hospital Administration & Staff

### `departments`
Hospital clinical departments.
- `id` (NUMBER, PK)
- `name`, `slug`, `description`, `image_path`
- `service_scope` (VARCHAR2) - Enum: `online`, `offline`, `both`
- `is_active`, `is_featured`, `featured_order`

### `doctors`
Doctor profiles linked to users and departments.
- `id` (NUMBER, PK)
- `department_id` (NUMBER, FK to departments)
- `user_id` (NUMBER, FK to users)
- `name`, `slug`, `specialty`, `qualifications`
- `experience_years`, `consultation_fee`
- `schedule_info`, `is_active`

### `facility_rooms`
Physical hospital rooms (Labs, Operation Theatres, ICUs).
- `id` (NUMBER, PK)
- `room_number` (VARCHAR2)
- `room_type` (VARCHAR2) - Enum: `Lab`, `Operation Theatre`, `General Ward`, `ICU`
- `capacity` (NUMBER)
- `is_active` (NUMBER(1))

### `inventory_items`
Hospital supplies and PPE tracking.
- `id` (NUMBER, PK)
- `name`, `category`, `unit`, `location`
- `quantity` (NUMBER)
- `status` (VARCHAR2) - Enum: `available`, `low_stock`, `out_of_stock`

---

## 3. Consultations & Medical Services

### `appointments`
Core scheduling table for online/offline consultations.
- `id` (NUMBER, PK)
- `patient_id` (NUMBER, FK to users)
- `doctor_id` (NUMBER, FK to doctors)
- `appointment_date` (TIMESTAMP)
- `status` (VARCHAR2) - Enum: `pending`, `confirmed`, `completed`, `cancelled`
- `type` (VARCHAR2) - Enum: `online`, `offline`
- `token_number` (VARCHAR2) - Smart Queue token
- `queue_status` (VARCHAR2) - Enum: `waiting`, `in_progress`, `completed`, `cancelled`
- `meeting_link` (VARCHAR2)
- `prescription_text` (CLOB)
- `symptoms` (VARCHAR2)

### `facility_bookings`
Schedule mapping for physical facility rooms.
- `id` (NUMBER, PK)
- `facility_room_id` (NUMBER, FK to facility_rooms)
- `appointment_id`, `user_id`, `doctor_id` (Optional FKs)
- `start_time`, `end_time` (TIMESTAMP)
- `status` (VARCHAR2) - Enum: `scheduled`, `in_progress`, `completed`, `cancelled`

### `payments`
Consultation fee transactions.
- `id` (NUMBER, PK)
- `appointment_id` (NUMBER, FK to appointments)
- `user_id` (NUMBER, FK to users)
- `amount` (NUMBER)
- `method` (VARCHAR2)
- `status` (VARCHAR2) - Enum: `pending`, `paid`, `failed`, `refunded`
- `reference`, `paid_at`

### `doctor_reviews`
Post-consultation patient feedback.
- `id` (NUMBER, PK)
- `doctor_id` (NUMBER, FK to doctors)
- `patient_id` (NUMBER, FK to users)
- `appointment_id` (NUMBER, FK to appointments)
- `rating` (NUMBER)
- `review_text` (VARCHAR2)

---

## 4. E-Pharmacy

### `medicines`
Digital catalog of pharmaceutical products.
- `id` (NUMBER, PK)
- `name`, `medicine_group`, `slug`, `power`, `manufacturer`
- `price` (NUMBER)
- `stock_quantity` (NUMBER)
- `requires_prescription` (NUMBER(1))
- `is_active` (NUMBER(1))

### `medicine_orders`
Patient medicine purchase orders.
- `id` (NUMBER, PK)
- `user_id` (NUMBER, FK to users)
- `order_number` (VARCHAR2, UNIQUE)
- `status` (VARCHAR2) - Enum: `pending`, `processing`, `completed`, `cancelled`
- `total_amount` (NUMBER)
- `shipping_address`, `payment_method`, `payment_status`

### `medicine_order_items`
Line items for medicine orders.
- `id` (NUMBER, PK)
- `order_id` (NUMBER, FK to medicine_orders)
- `medicine_id` (NUMBER, FK to medicines)
- `quantity` (NUMBER)
- `unit_price` (NUMBER)

---

## 5. Emergency Services

### `ambulances`
Hospital fleet management.
- `id` (NUMBER, PK)
- `vehicle_number` (VARCHAR2, UNIQUE)
- `driver_name`, `driver_phone`
- `type` (VARCHAR2) - Enum: `basic`, `advanced`, `icu`
- `status` (VARCHAR2) - Enum: `available`, `dispatched`, `maintenance`

### `ambulance_requests`
Emergency dispatch requests.
- `id` (NUMBER, PK)
- `user_id` (NUMBER, FK to users, Optional)
- `patient_name`, `patient_phone`
- `pickup_address`, `emergency_type`
- `status` (VARCHAR2) - Enum: `pending`, `dispatched`, `completed`, `cancelled`
- `ambulance_id` (NUMBER, FK to ambulances)

---

## 6. CMS & Community

### `article_categories`
Health blog categorization.
- `id` (NUMBER, PK)
- `name`, `slug`

### `articles`
Doctor-authored health articles.
- `id` (NUMBER, PK)
- `article_category_id` (NUMBER, FK to article_categories)
- `user_id` (NUMBER, FK to users)
- `title`, `slug`, `excerpt`
- `body` (CLOB)
- `is_published` (NUMBER(1))
- `publication_status` (VARCHAR2) - Enum: `draft`, `pending_review`, `published`

### `article_comments`
Patient comments on articles.
- `id` (NUMBER, PK)
- `article_id` (NUMBER, FK to articles)
- `user_id` (NUMBER, FK to users)
- `comment_text` (VARCHAR2)

### `qna_questions`
Community health queries.
- `id` (NUMBER, PK)
- `patient_id` (NUMBER, FK to users)
- `title`, `body` (CLOB)
- `is_resolved` (NUMBER(1))

### `qna_answers`
Doctor responses to Q&A.
- `id` (NUMBER, PK)
- `question_id` (NUMBER, FK to qna_questions)
- `doctor_id` (NUMBER, FK to doctors)
- `answer_text` (CLOB)
- `is_accepted` (NUMBER(1))

---

## 7. System & Security

### `audit_logs`
System-wide activity tracking.
- `id` (NUMBER, PK)
- `user_id` (NUMBER, FK to users)
- `action` (VARCHAR2)
- `entity_type` (VARCHAR2)
- `entity_id` (NUMBER)
- `old_values`, `new_values` (CLOB)
- `ip_address`

### `password_reset_tokens`
- `email` (VARCHAR2, PK)
- `token`, `created_at`

### `personal_access_tokens`
API Access control.
- `id` (NUMBER, PK)
- `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`

### `notifications`
System notifications for users.
- `id` (VARCHAR2, PK)
- `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`

---
*Generated by the HelloMed Database Architecture Team.*
