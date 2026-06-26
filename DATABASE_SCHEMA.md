# 🗄️ HelloMed Database Schema Data Dictionary

This document provides a formal, Data Dictionary-style specification of the HelloMed Oracle Database schema, detailing exact data types, constraints, defaults, and referential integrity. All tables utilize Oracle `SEQUENCES` and `BEFORE INSERT TRIGGERS` for primary key generation and timestamp tracking.

---

## 1. `USERS`
**Description:** Core user identity and Role-Based Access Control (RBAC).

| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `NAME` | `VARCHAR2(255)` | No | | |
| `EMAIL` | `VARCHAR2(255)` | No | | **UNIQUE** |
| `EMAIL_VERIFIED_AT` | `TIMESTAMP` | Yes | | |
| `PASSWORD` | `VARCHAR2(255)` | No | | |
| `ROLE` | `VARCHAR2(50)` | No | `'patient'` | |
| `IS_ACTIVE` | `NUMBER(1)` | No | `1` | `CHECK (is_active IN (0, 1))` |
| `REMEMBER_TOKEN` | `VARCHAR2(100)` | Yes | | |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |

---

## 2. `SESSIONS`
**Description:** Web application session state storage.

| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `VARCHAR2(255)` | No | | **PK** |
| `USER_ID` | `NUMBER` | Yes | | **FK** &rarr; `USERS(ID)` `ON DELETE SET NULL` |
| `IP_ADDRESS` | `VARCHAR2(45)` | Yes | | |
| `USER_AGENT` | `VARCHAR2(4000)`| Yes | | |
| `PAYLOAD` | `CLOB` | No | | |
| `LAST_ACTIVITY` | `NUMBER` | No | | |

---

## 3. `DEPARTMENTS`
**Description:** Hospital clinical departments and specialties.

| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `NAME` | `VARCHAR2(255)` | No | | |
| `SLUG` | `VARCHAR2(255)` | No | | **UNIQUE** |
| `DESCRIPTION` | `VARCHAR2(4000)`| Yes | | |
| `IMAGE_PATH` | `VARCHAR2(255)` | Yes | | |
| `SERVICE_SCOPE`| `VARCHAR2(50)` | No | `'both'` | |
| `IS_ACTIVE` | `NUMBER(1)` | No | `1` | `CHECK (is_active IN (0, 1))` |
| `IS_FEATURED` | `NUMBER(1)` | No | `0` | `CHECK (is_featured IN (0, 1))` |
| `FEATURED_ORDER`| `NUMBER` | No | `0` | |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |

---

## 4. `DOCTORS`
**Description:** Doctor profiles linked to user accounts and departments.

| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `DEPARTMENT_ID`| `NUMBER` | No | | **FK** &rarr; `DEPARTMENTS(ID)` `ON DELETE CASCADE` |
| `USER_ID` | `NUMBER` | Yes | | **FK** &rarr; `USERS(ID)` `ON DELETE SET NULL` |
| `NAME` | `VARCHAR2(255)` | No | | |
| `SLUG` | `VARCHAR2(255)` | No | | **UNIQUE** |
| `SPECIALTY` | `VARCHAR2(255)` | No | | |
| `BIO` | `VARCHAR2(4000)`| Yes | | |
| `QUALIFICATION`| `VARCHAR2(255)` | Yes | | |
| `EXPERIENCE_YEARS`| `NUMBER` | No | `0` | |
| `CONSULTATION_FEE`| `NUMBER(10,2)`| No | `0` | |
| `ONLINE_FEE` | `NUMBER(10,2)`| Yes | | |
| `OFFLINE_FEE` | `NUMBER(10,2)`| Yes | | |
| `ONLINE_AVAILABLE`| `NUMBER(1)` | No | `1` | `CHECK (online_available IN (0, 1))` |
| `OFFLINE_AVAILABLE`| `NUMBER(1)` | No | `1` | `CHECK (offline_available IN (0, 1))` |
| `CLINIC_ADDRESS`| `VARCHAR2(1000)`| Yes | | |
| `PHOTO_PATH` | `VARCHAR2(255)` | Yes | | |
| `AVAILABLE_DAYS`| `VARCHAR2(4000)`| Yes | | |
| `ONLINE_AVAILABLE_DAYS`| `VARCHAR2(4000)`| Yes | | |
| `ONLINE_AVAILABLE_FROM`| `VARCHAR2(20)` | Yes | | |
| `ONLINE_AVAILABLE_TO`| `VARCHAR2(20)` | Yes | | |
| `OFFLINE_AVAILABLE_DAYS`| `VARCHAR2(4000)`| Yes | | |
| `OFFLINE_AVAILABLE_FROM`| `VARCHAR2(20)` | Yes | | |
| `OFFLINE_AVAILABLE_TO`| `VARCHAR2(20)` | Yes | | |
| `AVAILABLE_FROM`| `VARCHAR2(20)` | Yes | | |
| `AVAILABLE_TO` | `VARCHAR2(20)` | Yes | | |
| `SLOT_MINUTES` | `NUMBER` | No | `30` | |
| `IS_FEATURED` | `NUMBER(1)` | No | `0` | `CHECK (is_featured IN (0, 1))` |
| `FEATURED_ORDER`| `NUMBER` | No | `0` | |
| `IS_ACTIVE` | `NUMBER(1)` | No | `1` | `CHECK (is_active IN (0, 1))` |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |

---

## 5. `SERVICES`
**Description:** Specific medical services offered by departments or individual doctors.

| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `DEPARTMENT_ID`| `NUMBER` | No | | **FK** &rarr; `DEPARTMENTS(ID)` `ON DELETE CASCADE` |
| `DOCTOR_ID` | `NUMBER` | Yes | | **FK** &rarr; `DOCTORS(ID)` `ON DELETE SET NULL` |
| `NAME` | `VARCHAR2(255)` | No | | |
| `SLUG` | `VARCHAR2(255)` | No | | **UNIQUE** |
| `DESCRIPTION` | `VARCHAR2(4000)`| Yes | | |
| `SERVICE_MODE` | `VARCHAR2(20)` | No | `'both'`| `CHECK (service_mode IN ('online', 'offline', 'both'))` |
| `DURATION_MINUTES`| `NUMBER` | No | `30` | |
| `PRICE` | `NUMBER(10,2)`| No | `0` | |
| `IS_ACTIVE` | `NUMBER(1)` | No | `1` | `CHECK (is_active IN (0, 1))` |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |

---

## 6. `APPOINTMENTS`
**Description:** Patient consultation bookings.

| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `USER_ID` | `NUMBER` | Yes | | **FK** &rarr; `USERS(ID)` `ON DELETE SET NULL` |
| `DOCTOR_ID` | `NUMBER` | No | | **FK** &rarr; `DOCTORS(ID)` `ON DELETE CASCADE` |
| `DEPARTMENT_ID`| `NUMBER` | No | | **FK** &rarr; `DEPARTMENTS(ID)` `ON DELETE CASCADE` |
| `SERVICE_ID` | `NUMBER` | Yes | | **FK** &rarr; `SERVICES(ID)` `ON DELETE SET NULL` |
| `PATIENT_NAME` | `VARCHAR2(255)` | No | | |
| `PATIENT_EMAIL`| `VARCHAR2(255)` | No | | |
| `PATIENT_PHONE`| `VARCHAR2(255)` | No | | |
| `SERVICE_MODE` | `VARCHAR2(20)` | No | | `CHECK (service_mode IN ('online', 'offline'))` |
| `SCHEDULED_FOR`| `TIMESTAMP` | No | | |
| `STATUS` | `VARCHAR2(50)` | No | `'pending'`| `CHECK (status IN ('pending', 'confirmed', 'completed', 'cancelled'))` |
| `PAYMENT_METHOD`| `VARCHAR2(100)` | No | `'none'`| |
| `PAYMENT_STATUS`| `VARCHAR2(100)` | No | `'not_required'`| |
| `TOKEN_NUMBER` | `VARCHAR2(50)` | Yes | | |
| `QUEUE_STATUS` | `VARCHAR2(50)` | No | `'waiting'`| `CHECK (queue_status IN ('waiting', 'in_progress', 'completed', 'cancelled'))` |
| `ONLINE_MEETING_LINK`| `VARCHAR2(1000)`| Yes | | |
| `REASON` | `VARCHAR2(4000)`| No | | |
| `NOTES` | `VARCHAR2(4000)`| Yes | | |
| `DOCTOR_PRESCRIPTION`| `CLOB` | Yes | | |
| `PRESCRIPTION_DIAGNOSIS`| `CLOB`| Yes | | |
| `PRESCRIPTION_MEDICINES`| `CLOB`| Yes | | |
| `PRESCRIPTION_ADVICE`| `CLOB` | Yes | | |
| `PRESCRIPTION_SAFETY_NOTES`| `CLOB`| Yes | | |
| `PRESCRIPTION_FOLLOW_UP_DATE`| `DATE`| Yes | | |
| `PRESCRIPTION_WRITTEN_AT`| `TIMESTAMP`| Yes| | |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |

---

## 7. `ARTICLE_CATEGORIES` & `ARTICLES`
**Description:** Health blog content management system.

### `ARTICLE_CATEGORIES`
| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `NAME` | `VARCHAR2(255)` | No | | |
| `SLUG` | `VARCHAR2(255)` | No | | **UNIQUE** |
| `DESCRIPTION` | `VARCHAR2(4000)`| Yes | | |
| `IS_ACTIVE` | `NUMBER(1)` | No | `1` | `CHECK (is_active IN (0, 1))` |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |

### `ARTICLES`
| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `ARTICLE_CATEGORY_ID`| `NUMBER`| No | | **FK** &rarr; `ARTICLE_CATEGORIES(ID)` `ON DELETE CASCADE` |
| `USER_ID` | `NUMBER` | Yes | | **FK** &rarr; `USERS(ID)` `ON DELETE SET NULL` |
| `TITLE` | `VARCHAR2(255)` | No | | |
| `SLUG` | `VARCHAR2(255)` | No | | **UNIQUE** |
| `EXCERPT` | `VARCHAR2(4000)`| No | | |
| `BODY` | `CLOB` | No | | |
| `COVER_IMAGE_PATH`| `VARCHAR2(255)`| Yes | | |
| `IS_FEATURED` | `NUMBER(1)` | No | `0` | `CHECK (is_featured IN (0, 1))` |
| `FEATURED_ORDER`| `NUMBER` | No | `0` | |
| `IS_PUBLISHED` | `NUMBER(1)` | No | `0` | `CHECK (is_published IN (0, 1))` |
| `PUBLICATION_STATUS`| `VARCHAR2(30)` | No | `'draft'`| |
| `REVIEWED_BY_USER_ID`| `NUMBER` | Yes | | **FK** &rarr; `USERS(ID)` `ON DELETE SET NULL` |
| `REVIEWED_AT` | `TIMESTAMP` | Yes | | |
| `PUBLISHED_AT` | `TIMESTAMP` | Yes | | |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |

---

## 8. `PAYMENTS`
**Description:** Appointment consultation fee transactions.

| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `APPOINTMENT_ID`| `NUMBER` | No | | **FK** &rarr; `APPOINTMENTS(ID)` `ON DELETE CASCADE` |
| `USER_ID` | `NUMBER` | Yes | | **FK** &rarr; `USERS(ID)` `ON DELETE SET NULL` |
| `METHOD` | `VARCHAR2(255)` | No | | |
| `AMOUNT` | `NUMBER(10,2)`| No | `0` | |
| `STATUS` | `VARCHAR2(50)` | No | `'pending'`| `CHECK (status IN ('pending', 'paid', 'failed', 'refunded'))` |
| `REFERENCE` | `VARCHAR2(255)` | Yes | | |
| `NOTES` | `VARCHAR2(4000)`| Yes | | |
| `PAID_AT` | `TIMESTAMP` | Yes | | |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |

---

## 9. `MEDICINES` & E-PHARMACY ORDERS
**Description:** Pharmaceutical catalog and e-commerce transactions.

### `MEDICINES`
| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `NAME` | `VARCHAR2(255)` | No | | |
| `MEDICINE_GROUP`| `VARCHAR2(255)` | Yes | | |
| `SLUG` | `VARCHAR2(255)` | No | | **UNIQUE** |
| `DESCRIPTION` | `VARCHAR2(4000)`| Yes | | |
| `POWER` | `VARCHAR2(255)` | Yes | | |
| `AMOUNT` | `VARCHAR2(255)` | Yes | | |
| `STRENGTH` | `VARCHAR2(255)` | Yes | | |
| `IMAGE_PATH` | `VARCHAR2(255)` | Yes | | |
| `MANUFACTURER` | `VARCHAR2(255)` | Yes | | |
| `PRICE` | `NUMBER(10,2)`| No | | |
| `STOCK_QUANTITY`| `NUMBER` | No | `0` | |
| `REQUIRES_PRESCRIPTION`| `NUMBER(1)`| No | `0` | `CHECK (requires_prescription IN (0, 1))` |
| `IS_ACTIVE` | `NUMBER(1)` | No | `1` | `CHECK (is_active IN (0, 1))` |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |

### `MEDICINE_ORDERS`
| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `USER_ID` | `NUMBER` | No | | **FK** &rarr; `USERS(ID)` `ON DELETE CASCADE` |
| `ORDER_NUMBER` | `VARCHAR2(255)` | No | | **UNIQUE** |
| `STATUS` | `VARCHAR2(50)` | No | `'pending'`| `CHECK (status IN ('pending', 'processing', 'completed', 'cancelled'))` |
| `TOTAL_AMOUNT` | `NUMBER(10,2)`| No | `0` | |
| `PAYMENT_METHOD`| `VARCHAR2(255)` | No | `'cash-on-delivery'`| |
| `PAYMENT_CALLBACK_TOKEN`| `VARCHAR2(100)`| Yes | | |
| `PAYMENT_STATUS`| `VARCHAR2(255)` | No | `'pending'`| |
| `PAYMENT_REFERENCE`| `VARCHAR2(255)`| Yes | | |
| `DELIVERY_ADDRESS`| `VARCHAR2(4000)`| No | | |
| `PHONE` | `VARCHAR2(30)` | No | | |
| `NOTES` | `VARCHAR2(4000)`| Yes | | |
| `PRESCRIPTION_PATH`| `VARCHAR2(255)`| Yes | | |
| `CONTAINS_PRESCRIPTION_ITEMS`| `NUMBER(1)`| No| `0` | `CHECK (contains_prescription_items IN (0, 1))` |
| `INVENTORY_COMMITTED_AT`| `TIMESTAMP`| Yes | | |
| `INVENTORY_RELEASED_AT`| `TIMESTAMP` | Yes | | |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |

### `MEDICINE_ORDER_ITEMS`
| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `MEDICINE_ORDER_ID`| `NUMBER` | No | | **FK** &rarr; `MEDICINE_ORDERS(ID)` `ON DELETE CASCADE` |
| `MEDICINE_ID` | `NUMBER` | No | | **FK** &rarr; `MEDICINES(ID)` |
| `QUANTITY` | `NUMBER` | No | | |
| `UNIT_PRICE` | `NUMBER(10,2)`| No | | |
| `LINE_TOTAL` | `NUMBER(10,2)`| No | | |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |

---

## 10. `APPOINTMENT_CHAT_MESSAGES`
**Description:** Encrypted real-time messaging between doctors and patients.

| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `APPOINTMENT_ID`| `NUMBER` | No | | **FK** &rarr; `APPOINTMENTS(ID)` `ON DELETE CASCADE` |
| `USER_ID` | `NUMBER` | No | | **FK** &rarr; `USERS(ID)` `ON DELETE CASCADE` |
| `MESSAGE` | `CLOB` | Yes | | |
| `READ_AT` | `TIMESTAMP` | Yes | | |
| `ATTACHMENT_PATH`| `VARCHAR2(255)` | Yes | | |
| `ATTACHMENT_NAME`| `VARCHAR2(255)` | Yes | | |
| `ATTACHMENT_MIME`| `VARCHAR2(120)` | Yes | | |
| `ATTACHMENT_SIZE`| `NUMBER` | Yes | | |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |
*Index:* `idx_apm_chat_created(appointment_id, created_at)`

---

## 11. `APPOINTMENT_PRESCRIPTION_ITEMS`
**Description:** Structured tabular medicines attached to an appointment prescription.

| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `APPOINTMENT_ID`| `NUMBER` | No | | **FK** &rarr; `APPOINTMENTS(ID)` `ON DELETE CASCADE` |
| `MEDICINE_ID` | `NUMBER` | Yes | | **FK** &rarr; `MEDICINES(ID)` `ON DELETE SET NULL` |
| `MEDICINE_NAME` | `VARCHAR2(255)` | No | | |
| `AMOUNT` | `VARCHAR2(255)` | Yes | | |
| `DOSAGE` | `VARCHAR2(255)` | Yes | | |
| `INTAKE_TIME` | `VARCHAR2(255)` | Yes | | |
| `INSTRUCTIONS` | `VARCHAR2(4000)`| Yes | | |
| `SORT_ORDER` | `NUMBER` | No | `1` | |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |

---

## 12. `AUDIT_LOGS` & `NOTIFICATION_LOGS`
**Description:** Application-wide logging and transactional notification tracking.

### `AUDIT_LOGS`
| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `ACTOR_USER_ID` | `NUMBER` | Yes | | **FK** &rarr; `USERS(ID)` `ON DELETE SET NULL` |
| `ACTION` | `VARCHAR2(120)` | No | | |
| `ENTITY_TYPE` | `VARCHAR2(120)` | No | | |
| `ENTITY_ID` | `NUMBER` | Yes | | |
| `OLD_VALUES` | `CLOB` | Yes | | |
| `NEW_VALUES` | `CLOB` | Yes | | |
| `META` | `CLOB` | Yes | | |
| `IP_ADDRESS` | `VARCHAR2(45)` | Yes | | |
| `USER_AGENT` | `VARCHAR2(4000)`| Yes | | |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |

### `NOTIFICATION_LOGS`
| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `USER_ID` | `NUMBER` | Yes | | **FK** &rarr; `USERS(ID)` `ON DELETE SET NULL` |
| `RECIPIENT_EMAIL`| `VARCHAR2(255)` | No | | |
| `CHANNEL` | `VARCHAR2(40)` | No | `'email'`| |
| `EVENT_KEY` | `VARCHAR2(120)` | No | | |
| `STATUS` | `VARCHAR2(30)` | No | `'pending'`| |
| `ATTEMPTS` | `NUMBER` | No | `0` | |
| `LAST_ERROR` | `CLOB` | Yes | | |
| `NOTIFIABLE_TYPE`| `VARCHAR2(120)` | Yes | | |
| `NOTIFIABLE_ID` | `NUMBER` | Yes | | |
| `PAYLOAD` | `CLOB` | Yes | | |
| `SENT_AT` | `TIMESTAMP` | Yes | | |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |

---

## 13. `PATIENT_PROFILES`
**Description:** Extended medical information for patients.

| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `USER_ID` | `NUMBER` | No | | **UNIQUE**, **FK** &rarr; `USERS(ID)` `ON DELETE CASCADE` |
| `DATE_OF_BIRTH` | `DATE` | Yes | | |
| `GENDER` | `VARCHAR2(20)` | Yes | | |
| `HEIGHT_CM` | `NUMBER(5,2)` | Yes | | |
| `WEIGHT_KG` | `NUMBER(5,2)` | Yes | | |
| `KNOWN_CONDITIONS` | `VARCHAR2(4000)`| Yes | | |
| `ALLERGIES` | `VARCHAR2(4000)`| Yes | | |
| `MEDICAL_NOTES` | `VARCHAR2(4000)`| Yes | | |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |

---

## 14. `DOCTOR_REVIEWS`
**Description:** Ratings left by patients post-consultation.

| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `DOCTOR_ID` | `NUMBER` | No | | **FK** &rarr; `DOCTORS(ID)` `ON DELETE CASCADE` |
| `USER_ID` | `NUMBER` | No | | **FK** &rarr; `USERS(ID)` `ON DELETE CASCADE` |
| `RATING` | `NUMBER` | No | | |
| `COMMENT` | `CLOB` | Yes | | |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |
*Constraint:* `UNIQUE (doctor_id, user_id)`

---

## 15. `ARTICLE_COMMENTS`
**Description:** Community feedback on health articles.

| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `ARTICLE_ID` | `NUMBER` | No | | **FK** &rarr; `ARTICLES(ID)` `ON DELETE CASCADE` |
| `USER_ID` | `NUMBER` | No | | **FK** &rarr; `USERS(ID)` `ON DELETE CASCADE` |
| `RATING` | `NUMBER` | Yes | | |
| `COMMENT` | `CLOB` | No | | |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |

---

## 16. `QNA_QUESTIONS` & `QNA_ANSWERS`
**Description:** Community forum for medical questions.

### `QNA_QUESTIONS`
| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `USER_ID` | `NUMBER` | No | | **FK** &rarr; `USERS(ID)` `ON DELETE CASCADE` |
| `TITLE` | `VARCHAR2(255)` | No | | |
| `QUESTION` | `CLOB` | No | | |
| `STATUS` | `VARCHAR2(20)` | No | `'open'` | |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |

### `QNA_ANSWERS`
| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `QNA_QUESTION_ID`| `NUMBER` | No | | **FK** &rarr; `QNA_QUESTIONS(ID)` `ON DELETE CASCADE` |
| `USER_ID` | `NUMBER` | No | | **FK** &rarr; `USERS(ID)` `ON DELETE CASCADE` |
| `ANSWER` | `CLOB` | No | | |
| `IS_OFFICIAL` | `NUMBER(1)` | No | `1` | `CHECK (is_official IN (0, 1))` |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |

---

## 17. `AMBULANCE_REQUESTS`
**Description:** Emergency dispatch module.

### `AMBULANCE_REQUESTS`
| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `USER_ID` | `NUMBER` | Yes | | **FK** &rarr; `USERS(ID)` `ON DELETE SET NULL` |
| `PATIENT_NAME` | `VARCHAR2(255)` | No | | |
| `PATIENT_PHONE` | `VARCHAR2(255)` | No | | |
| `LATITUDE` | `NUMBER(10,8)`| Yes | | |
| `LONGITUDE` | `NUMBER(11,8)`| Yes | | |
| `ADDRESS` | `VARCHAR2(4000)`| Yes | | |
| `STATUS` | `VARCHAR2(50)` | No | `'pending'`| `CHECK (status IN ('pending', 'dispatched', 'resolved', 'cancelled'))` |
| `DISPATCHED_AT` | `TIMESTAMP` | Yes | | |
| `RESOLVED_AT` | `TIMESTAMP` | Yes | | |
| `STAFF_ID` | `NUMBER` | Yes | | **FK** &rarr; `USERS(ID)` `ON DELETE SET NULL` |
| `NOTES` | `VARCHAR2(4000)`| Yes | | |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |

---

## 18. `INVENTORY_ITEMS`
**Description:** Hospital medical supplies management.

| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `NAME` | `VARCHAR2(255)` | No | | |
| `CATEGORY` | `VARCHAR2(255)` | Yes | | |
| `QUANTITY` | `NUMBER` | No | `0` | |
| `UNIT` | `VARCHAR2(50)` | Yes | | |
| `LOCATION` | `VARCHAR2(255)` | Yes | | |
| `STATUS` | `VARCHAR2(50)` | No | `'available'`| `CHECK (status IN ('available', 'low_stock', 'out_of_stock'))` |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |

---

## 19. `FACILITY_ROOMS`
**Description:** Master table for hospital infrastructure and physical rooms.

| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `ROOM_NUMBER` | `VARCHAR2(100)` | No | | |
| `ROOM_TYPE` | `VARCHAR2(100)` | No | | `CHECK (room_type IN ('Lab', 'Operation Theatre', 'General Ward', 'ICU'))` |
| `CAPACITY` | `NUMBER` | No | `1` | |
| `IS_ACTIVE` | `NUMBER(1)` | No | `1` | `CHECK (is_active IN (0, 1))` |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |

---

## 20. `FACILITY_BOOKINGS`
**Description:** Scheduling blocks mapped against Facility Rooms.

| Column Name | Data Type | Nullable | Default | Constraints / Keys |
|---|---|---|---|---|
| `ID` | `NUMBER` | No | | **PK** |
| `FACILITY_ROOM_ID`| `NUMBER` | No | | **FK** &rarr; `FACILITY_ROOMS(ID)` `ON DELETE CASCADE` |
| `APPOINTMENT_ID`| `NUMBER` | Yes | | **FK** &rarr; `APPOINTMENTS(ID)` `ON DELETE SET NULL` |
| `USER_ID` | `NUMBER` | Yes | | **FK** &rarr; `USERS(ID)` `ON DELETE SET NULL` |
| `DOCTOR_ID` | `NUMBER` | Yes | | **FK** &rarr; `DOCTORS(ID)` `ON DELETE SET NULL` |
| `START_TIME` | `TIMESTAMP` | No | | |
| `END_TIME` | `TIMESTAMP` | No | | |
| `STATUS` | `VARCHAR2(50)` | No | `'scheduled'`| `CHECK (status IN ('scheduled', 'in_progress', 'completed', 'cancelled'))` |
| `CREATED_AT` | `TIMESTAMP` | Yes | | |
| `UPDATED_AT` | `TIMESTAMP` | Yes | | |
