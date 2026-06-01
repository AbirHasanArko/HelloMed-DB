# HelloMed Entity-Relationship Diagrams

This document contains the complete graphical entity-relationship (ER) models for the HelloMed Oracle Database, mapping directly to every table and column found in `01_schema.sql`.
The system is divided into five logical modules for easier visualization.

---

## 1. Core & Authentication System
Handles user identities, sessions, extended profiles, and system-wide logging.

```mermaid
erDiagram
    USERS {
        NUMBER id PK
        VARCHAR2 name
        VARCHAR2 email
        TIMESTAMP email_verified_at
        VARCHAR2 password
        VARCHAR2 role
        NUMBER is_active
        VARCHAR2 remember_token
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }
    SESSIONS {
        VARCHAR2 id PK
        NUMBER user_id FK
        VARCHAR2 ip_address
        VARCHAR2 user_agent
        CLOB payload
        NUMBER last_activity
    }
    PATIENT_PROFILES {
        NUMBER id PK
        NUMBER user_id FK
        VARCHAR2 allergies
        VARCHAR2 medical_notes
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }
    AUDIT_LOGS {
        NUMBER id PK
        NUMBER actor_user_id FK
        VARCHAR2 action
        VARCHAR2 entity_type
        NUMBER entity_id
        CLOB old_values
        CLOB new_values
        CLOB meta
        VARCHAR2 ip_address
        VARCHAR2 user_agent
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }
    NOTIFICATION_LOGS {
        NUMBER id PK
        NUMBER user_id FK
        VARCHAR2 recipient_email
        VARCHAR2 channel
        VARCHAR2 event_key
        VARCHAR2 status
        NUMBER attempts
        CLOB last_error
        VARCHAR2 notifiable_type
        NUMBER notifiable_id
        CLOB payload
        TIMESTAMP sent_at
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }

    USERS ||--o{ SESSIONS : "authenticates via"
    USERS ||--|| PATIENT_PROFILES : "has extended"
    USERS ||--o{ AUDIT_LOGS : "performs action"
    USERS ||--o{ NOTIFICATION_LOGS : "receives"
```

---

## 2. Medical Services & Appointments
The backbone of the hospital's operations, connecting doctors, patients, departments, and scheduling.

```mermaid
erDiagram
    DEPARTMENTS {
        NUMBER id PK
        VARCHAR2 name
        VARCHAR2 slug
        VARCHAR2 description
        VARCHAR2 image_path
        VARCHAR2 service_scope
        NUMBER is_active
        NUMBER is_featured
        NUMBER featured_order
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }
    DOCTORS {
        NUMBER id PK
        NUMBER department_id FK
        NUMBER user_id FK
        VARCHAR2 name
        VARCHAR2 slug
        VARCHAR2 specialty
        VARCHAR2 bio
        VARCHAR2 qualification
        NUMBER experience_years
        NUMBER consultation_fee
        NUMBER online_fee
        NUMBER offline_fee
        NUMBER online_available
        NUMBER offline_available
        VARCHAR2 clinic_address
        VARCHAR2 photo_path
        VARCHAR2 available_days
        VARCHAR2 online_available_days
        VARCHAR2 online_available_from
        VARCHAR2 online_available_to
        VARCHAR2 offline_available_days
        VARCHAR2 offline_available_from
        VARCHAR2 offline_available_to
        VARCHAR2 available_from
        VARCHAR2 available_to
        NUMBER slot_minutes
        NUMBER is_featured
        NUMBER featured_order
        NUMBER is_active
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }
    SERVICES {
        NUMBER id PK
        NUMBER department_id FK
        NUMBER doctor_id FK
        VARCHAR2 name
        VARCHAR2 slug
        VARCHAR2 description
        VARCHAR2 service_mode
        NUMBER duration_minutes
        NUMBER price
        NUMBER is_active
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }
    APPOINTMENTS {
        NUMBER id PK
        NUMBER user_id FK "Patient"
        NUMBER doctor_id FK
        NUMBER department_id FK
        NUMBER service_id FK
        VARCHAR2 patient_name
        VARCHAR2 patient_email
        VARCHAR2 patient_phone
        VARCHAR2 service_mode
        TIMESTAMP scheduled_for
        VARCHAR2 status
        VARCHAR2 payment_method
        VARCHAR2 payment_status
        VARCHAR2 online_meeting_link
        VARCHAR2 reason
        VARCHAR2 notes
        CLOB doctor_prescription
        CLOB prescription_diagnosis
        CLOB prescription_medicines
        CLOB prescription_advice
        CLOB prescription_safety_notes
        DATE prescription_follow_up_date
        TIMESTAMP prescription_written_at
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }
    PAYMENTS {
        NUMBER id PK
        NUMBER appointment_id FK
        NUMBER user_id FK
        VARCHAR2 method
        NUMBER amount
        VARCHAR2 status
        VARCHAR2 reference
        VARCHAR2 notes
        TIMESTAMP paid_at
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }
    APPOINTMENT_CHAT_MESSAGES {
        NUMBER id PK
        NUMBER appointment_id FK
        NUMBER user_id FK
        CLOB message
        TIMESTAMP read_at
        VARCHAR2 attachment_path
        VARCHAR2 attachment_name
        VARCHAR2 attachment_mime
        NUMBER attachment_size
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }
    APPOINTMENT_PRESCRIPTION_ITEMS {
        NUMBER id PK
        NUMBER appointment_id FK
        NUMBER medicine_id FK
        VARCHAR2 medicine_name
        VARCHAR2 amount
        VARCHAR2 dosage
        VARCHAR2 intake_time
        VARCHAR2 instructions
        NUMBER sort_order
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }
    DOCTOR_REVIEWS {
        NUMBER id PK
        NUMBER doctor_id FK
        NUMBER user_id FK
        NUMBER rating
        CLOB COMMENT
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }

    DEPARTMENTS ||--o{ DOCTORS : "employs"
    DEPARTMENTS ||--o{ SERVICES : "offers"
    DOCTORS ||--o{ SERVICES : "performs"
    DOCTORS ||--o{ APPOINTMENTS : "conducts"
    USERS ||--o{ APPOINTMENTS : "books"
    APPOINTMENTS ||--o{ PAYMENTS : "billed via"
    APPOINTMENTS ||--o{ APPOINTMENT_CHAT_MESSAGES : "has messages"
    APPOINTMENTS ||--o{ APPOINTMENT_PRESCRIPTION_ITEMS : "results in"
    USERS ||--o{ DOCTOR_REVIEWS : "writes"
    DOCTORS ||--o{ DOCTOR_REVIEWS : "receives"
```

---

## 3. Digital E-Pharmacy
Inventory management and digital medicine ordering.

```mermaid
erDiagram
    MEDICINES {
        NUMBER id PK
        VARCHAR2 name
        VARCHAR2 medicine_group
        VARCHAR2 slug
        VARCHAR2 description
        VARCHAR2 power
        VARCHAR2 amount
        VARCHAR2 strength
        VARCHAR2 image_path
        VARCHAR2 manufacturer
        NUMBER price
        NUMBER stock_quantity
        NUMBER requires_prescription
        NUMBER is_active
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }
    MEDICINE_ORDERS {
        NUMBER id PK
        NUMBER user_id FK
        VARCHAR2 order_number
        VARCHAR2 status
        NUMBER total_amount
        VARCHAR2 payment_method
        VARCHAR2 payment_callback_token
        VARCHAR2 payment_status
        VARCHAR2 payment_reference
        VARCHAR2 delivery_address
        VARCHAR2 phone
        VARCHAR2 notes
        VARCHAR2 prescription_path
        NUMBER contains_prescription_items
        TIMESTAMP inventory_committed_at
        TIMESTAMP inventory_released_at
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }
    MEDICINE_ORDER_ITEMS {
        NUMBER id PK
        NUMBER medicine_order_id FK
        NUMBER medicine_id FK
        NUMBER quantity
        NUMBER unit_price
        NUMBER line_total
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }

    USERS ||--o{ MEDICINE_ORDERS : "places"
    MEDICINE_ORDERS ||--o{ MEDICINE_ORDER_ITEMS : "contains"
    MEDICINES ||--o{ MEDICINE_ORDER_ITEMS : "added as"
```

---

## 4. Content CMS & Community Q&A
Public health articles authored by doctors and patient questions.

```mermaid
erDiagram
    ARTICLE_CATEGORIES {
        NUMBER id PK
        VARCHAR2 name
        VARCHAR2 slug
        VARCHAR2 description
        NUMBER is_active
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }
    ARTICLES {
        NUMBER id PK
        NUMBER article_category_id FK
        NUMBER user_id FK "Doctor/Admin"
        VARCHAR2 title
        VARCHAR2 slug
        VARCHAR2 excerpt
        CLOB body
        VARCHAR2 cover_image_path
        NUMBER is_featured
        NUMBER featured_order
        NUMBER is_published
        VARCHAR2 publication_status
        NUMBER reviewed_by_user_id FK
        TIMESTAMP reviewed_at
        TIMESTAMP published_at
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }
    ARTICLE_COMMENTS {
        NUMBER id PK
        NUMBER article_id FK
        NUMBER user_id FK
        NUMBER rating
        CLOB COMMENT
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }
    QNA_QUESTIONS {
        NUMBER id PK
        NUMBER user_id FK
        VARCHAR2 title
        CLOB question
        VARCHAR2 status
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }
    QNA_ANSWERS {
        NUMBER id PK
        NUMBER qna_question_id FK
        NUMBER user_id FK "Doctor"
        CLOB answer
        NUMBER is_official
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }

    ARTICLE_CATEGORIES ||--o{ ARTICLES : "categorizes"
    USERS ||--o{ ARTICLES : "authors"
    ARTICLES ||--o{ ARTICLE_COMMENTS : "has"
    USERS ||--o{ QNA_QUESTIONS : "asks"
    QNA_QUESTIONS ||--o{ QNA_ANSWERS : "receives"
    USERS ||--o{ QNA_ANSWERS : "provides"
```

---

## 5. Emergency Dispatch
Real-time tracking of ambulance requests.

```mermaid
erDiagram
    AMBULANCE_REQUESTS {
        NUMBER id PK
        NUMBER user_id FK "Nullable"
        VARCHAR2 patient_name
        VARCHAR2 patient_phone
        NUMBER latitude
        NUMBER longitude
        VARCHAR2 address
        VARCHAR2 status
        TIMESTAMP dispatched_at
        TIMESTAMP resolved_at
        NUMBER staff_id FK "Dispatcher"
        VARCHAR2 notes
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }

    USERS ||--o{ AMBULANCE_REQUESTS : "requests"
    USERS ||--o{ AMBULANCE_REQUESTS : "dispatches"
```
