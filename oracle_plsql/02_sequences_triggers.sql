-- ==========================================
-- 02_sequences_triggers.sql
-- Oracle 11g Sequences and Triggers
-- ==========================================

-- users
CREATE SEQUENCE users_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER users_trg
BEFORE INSERT OR UPDATE ON users
FOR EACH ROW
BEGIN
    IF INSERTING THEN
        IF :NEW.id IS NULL THEN
            :NEW.id := users_seq.NEXTVAL;
        END IF;
        IF :NEW.created_at IS NULL THEN
            :NEW.created_at := SYSTIMESTAMP;
        END IF;
        IF :NEW.updated_at IS NULL THEN
            :NEW.updated_at := SYSTIMESTAMP;
        END IF;
    END IF;
    IF UPDATING THEN
        :NEW.updated_at := SYSTIMESTAMP;
    END IF;
END;
/

-- departments
CREATE SEQUENCE departments_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER departments_trg
BEFORE INSERT OR UPDATE ON departments
FOR EACH ROW
BEGIN
    IF INSERTING THEN
        IF :NEW.id IS NULL THEN
            :NEW.id := departments_seq.NEXTVAL;
        END IF;
        IF :NEW.created_at IS NULL THEN
            :NEW.created_at := SYSTIMESTAMP;
        END IF;
        IF :NEW.updated_at IS NULL THEN
            :NEW.updated_at := SYSTIMESTAMP;
        END IF;
    END IF;
    IF UPDATING THEN
        :NEW.updated_at := SYSTIMESTAMP;
    END IF;
END;
/

-- doctors
CREATE SEQUENCE doctors_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER doctors_trg
BEFORE INSERT OR UPDATE ON doctors
FOR EACH ROW
BEGIN
    IF INSERTING THEN
        IF :NEW.id IS NULL THEN
            :NEW.id := doctors_seq.NEXTVAL;
        END IF;
        IF :NEW.created_at IS NULL THEN
            :NEW.created_at := SYSTIMESTAMP;
        END IF;
        IF :NEW.updated_at IS NULL THEN
            :NEW.updated_at := SYSTIMESTAMP;
        END IF;
    END IF;
    IF UPDATING THEN
        :NEW.updated_at := SYSTIMESTAMP;
    END IF;
END;
/

-- services
CREATE SEQUENCE services_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER services_trg
BEFORE INSERT OR UPDATE ON services
FOR EACH ROW
BEGIN
    IF INSERTING THEN
        IF :NEW.id IS NULL THEN
            :NEW.id := services_seq.NEXTVAL;
        END IF;
        IF :NEW.created_at IS NULL THEN
            :NEW.created_at := SYSTIMESTAMP;
        END IF;
        IF :NEW.updated_at IS NULL THEN
            :NEW.updated_at := SYSTIMESTAMP;
        END IF;
    END IF;
    IF UPDATING THEN
        :NEW.updated_at := SYSTIMESTAMP;
    END IF;
END;
/

-- appointments
CREATE SEQUENCE appointments_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER appointments_trg
BEFORE INSERT OR UPDATE ON appointments
FOR EACH ROW
BEGIN
    IF INSERTING THEN
        IF :NEW.id IS NULL THEN
            :NEW.id := appointments_seq.NEXTVAL;
        END IF;
        IF :NEW.created_at IS NULL THEN
            :NEW.created_at := SYSTIMESTAMP;
        END IF;
        IF :NEW.updated_at IS NULL THEN
            :NEW.updated_at := SYSTIMESTAMP;
        END IF;
    END IF;
    IF UPDATING THEN
        :NEW.updated_at := SYSTIMESTAMP;
    END IF;
END;
/

-- article_categories
CREATE SEQUENCE article_categories_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER article_categories_trg
BEFORE INSERT OR UPDATE ON article_categories
FOR EACH ROW
BEGIN
    IF INSERTING THEN
        IF :NEW.id IS NULL THEN
            :NEW.id := article_categories_seq.NEXTVAL;
        END IF;
        IF :NEW.created_at IS NULL THEN
            :NEW.created_at := SYSTIMESTAMP;
        END IF;
        IF :NEW.updated_at IS NULL THEN
            :NEW.updated_at := SYSTIMESTAMP;
        END IF;
    END IF;
    IF UPDATING THEN
        :NEW.updated_at := SYSTIMESTAMP;
    END IF;
END;
/

-- articles
CREATE SEQUENCE articles_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER articles_trg
BEFORE INSERT OR UPDATE ON articles
FOR EACH ROW
BEGIN
    IF INSERTING THEN
        IF :NEW.id IS NULL THEN
            :NEW.id := articles_seq.NEXTVAL;
        END IF;
        IF :NEW.created_at IS NULL THEN
            :NEW.created_at := SYSTIMESTAMP;
        END IF;
        IF :NEW.updated_at IS NULL THEN
            :NEW.updated_at := SYSTIMESTAMP;
        END IF;
    END IF;
    IF UPDATING THEN
        :NEW.updated_at := SYSTIMESTAMP;
    END IF;
END;
/

-- payments
CREATE SEQUENCE payments_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER payments_trg
BEFORE INSERT OR UPDATE ON payments
FOR EACH ROW
BEGIN
    IF INSERTING THEN
        IF :NEW.id IS NULL THEN
            :NEW.id := payments_seq.NEXTVAL;
        END IF;
        IF :NEW.created_at IS NULL THEN
            :NEW.created_at := SYSTIMESTAMP;
        END IF;
        IF :NEW.updated_at IS NULL THEN
            :NEW.updated_at := SYSTIMESTAMP;
        END IF;
    END IF;
    IF UPDATING THEN
        :NEW.updated_at := SYSTIMESTAMP;
    END IF;
END;
/

-- medicines
CREATE SEQUENCE medicines_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER medicines_trg
BEFORE INSERT OR UPDATE ON medicines
FOR EACH ROW
BEGIN
    IF INSERTING THEN
        IF :NEW.id IS NULL THEN
            :NEW.id := medicines_seq.NEXTVAL;
        END IF;
        IF :NEW.created_at IS NULL THEN
            :NEW.created_at := SYSTIMESTAMP;
        END IF;
        IF :NEW.updated_at IS NULL THEN
            :NEW.updated_at := SYSTIMESTAMP;
        END IF;
    END IF;
    IF UPDATING THEN
        :NEW.updated_at := SYSTIMESTAMP;
    END IF;
END;
/

-- medicine_orders
CREATE SEQUENCE medicine_orders_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER medicine_orders_trg
BEFORE INSERT OR UPDATE ON medicine_orders
FOR EACH ROW
BEGIN
    IF INSERTING THEN
        IF :NEW.id IS NULL THEN
            :NEW.id := medicine_orders_seq.NEXTVAL;
        END IF;
        IF :NEW.created_at IS NULL THEN
            :NEW.created_at := SYSTIMESTAMP;
        END IF;
        IF :NEW.updated_at IS NULL THEN
            :NEW.updated_at := SYSTIMESTAMP;
        END IF;
    END IF;
    IF UPDATING THEN
        :NEW.updated_at := SYSTIMESTAMP;
    END IF;
END;
/

-- medicine_order_items
CREATE SEQUENCE medicine_order_items_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER medicine_order_items_trg
BEFORE INSERT OR UPDATE ON medicine_order_items
FOR EACH ROW
BEGIN
    IF INSERTING THEN
        IF :NEW.id IS NULL THEN
            :NEW.id := medicine_order_items_seq.NEXTVAL;
        END IF;
        IF :NEW.created_at IS NULL THEN
            :NEW.created_at := SYSTIMESTAMP;
        END IF;
        IF :NEW.updated_at IS NULL THEN
            :NEW.updated_at := SYSTIMESTAMP;
        END IF;
    END IF;
    IF UPDATING THEN
        :NEW.updated_at := SYSTIMESTAMP;
    END IF;
END;
/

-- appointment_chat_messages
CREATE SEQUENCE appointment_chat_messages_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER appointment_chat_messages_trg
BEFORE INSERT OR UPDATE ON appointment_chat_messages
FOR EACH ROW
BEGIN
    IF INSERTING THEN
        IF :NEW.id IS NULL THEN
            :NEW.id := appointment_chat_messages_seq.NEXTVAL;
        END IF;
        IF :NEW.created_at IS NULL THEN
            :NEW.created_at := SYSTIMESTAMP;
        END IF;
        IF :NEW.updated_at IS NULL THEN
            :NEW.updated_at := SYSTIMESTAMP;
        END IF;
    END IF;
    IF UPDATING THEN
        :NEW.updated_at := SYSTIMESTAMP;
    END IF;
END;
/

-- appointment_prescription_items
CREATE SEQUENCE appointment_prescription_i_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER appointment_prescription_i_trg
BEFORE INSERT OR UPDATE ON appointment_prescription_items
FOR EACH ROW
BEGIN
    IF INSERTING THEN
        IF :NEW.id IS NULL THEN
            :NEW.id := appointment_prescription_i_seq.NEXTVAL;
        END IF;
        IF :NEW.created_at IS NULL THEN
            :NEW.created_at := SYSTIMESTAMP;
        END IF;
        IF :NEW.updated_at IS NULL THEN
            :NEW.updated_at := SYSTIMESTAMP;
        END IF;
    END IF;
    IF UPDATING THEN
        :NEW.updated_at := SYSTIMESTAMP;
    END IF;
END;
/

-- audit_logs
CREATE SEQUENCE audit_logs_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER audit_logs_trg
BEFORE INSERT OR UPDATE ON audit_logs
FOR EACH ROW
BEGIN
    IF INSERTING THEN
        IF :NEW.id IS NULL THEN
            :NEW.id := audit_logs_seq.NEXTVAL;
        END IF;
        IF :NEW.created_at IS NULL THEN
            :NEW.created_at := SYSTIMESTAMP;
        END IF;
        IF :NEW.updated_at IS NULL THEN
            :NEW.updated_at := SYSTIMESTAMP;
        END IF;
    END IF;
    IF UPDATING THEN
        :NEW.updated_at := SYSTIMESTAMP;
    END IF;
END;
/

-- notification_logs
CREATE SEQUENCE notification_logs_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER notification_logs_trg
BEFORE INSERT OR UPDATE ON notification_logs
FOR EACH ROW
BEGIN
    IF INSERTING THEN
        IF :NEW.id IS NULL THEN
            :NEW.id := notification_logs_seq.NEXTVAL;
        END IF;
        IF :NEW.created_at IS NULL THEN
            :NEW.created_at := SYSTIMESTAMP;
        END IF;
        IF :NEW.updated_at IS NULL THEN
            :NEW.updated_at := SYSTIMESTAMP;
        END IF;
    END IF;
    IF UPDATING THEN
        :NEW.updated_at := SYSTIMESTAMP;
    END IF;
END;
/

-- patient_profiles
CREATE SEQUENCE patient_profiles_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER patient_profiles_trg
BEFORE INSERT OR UPDATE ON patient_profiles
FOR EACH ROW
BEGIN
    IF INSERTING THEN
        IF :NEW.id IS NULL THEN
            :NEW.id := patient_profiles_seq.NEXTVAL;
        END IF;
        IF :NEW.created_at IS NULL THEN
            :NEW.created_at := SYSTIMESTAMP;
        END IF;
        IF :NEW.updated_at IS NULL THEN
            :NEW.updated_at := SYSTIMESTAMP;
        END IF;
    END IF;
    IF UPDATING THEN
        :NEW.updated_at := SYSTIMESTAMP;
    END IF;
END;
/

-- doctor_reviews
CREATE SEQUENCE doctor_reviews_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER doctor_reviews_trg
BEFORE INSERT OR UPDATE ON doctor_reviews
FOR EACH ROW
BEGIN
    IF INSERTING THEN
        IF :NEW.id IS NULL THEN
            :NEW.id := doctor_reviews_seq.NEXTVAL;
        END IF;
        IF :NEW.created_at IS NULL THEN
            :NEW.created_at := SYSTIMESTAMP;
        END IF;
        IF :NEW.updated_at IS NULL THEN
            :NEW.updated_at := SYSTIMESTAMP;
        END IF;
    END IF;
    IF UPDATING THEN
        :NEW.updated_at := SYSTIMESTAMP;
    END IF;
END;
/

-- article_comments
CREATE SEQUENCE article_comments_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER article_comments_trg
BEFORE INSERT OR UPDATE ON article_comments
FOR EACH ROW
BEGIN
    IF INSERTING THEN
        IF :NEW.id IS NULL THEN
            :NEW.id := article_comments_seq.NEXTVAL;
        END IF;
        IF :NEW.created_at IS NULL THEN
            :NEW.created_at := SYSTIMESTAMP;
        END IF;
        IF :NEW.updated_at IS NULL THEN
            :NEW.updated_at := SYSTIMESTAMP;
        END IF;
    END IF;
    IF UPDATING THEN
        :NEW.updated_at := SYSTIMESTAMP;
    END IF;
END;
/

-- qna_questions
CREATE SEQUENCE qna_questions_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER qna_questions_trg
BEFORE INSERT OR UPDATE ON qna_questions
FOR EACH ROW
BEGIN
    IF INSERTING THEN
        IF :NEW.id IS NULL THEN
            :NEW.id := qna_questions_seq.NEXTVAL;
        END IF;
        IF :NEW.created_at IS NULL THEN
            :NEW.created_at := SYSTIMESTAMP;
        END IF;
        IF :NEW.updated_at IS NULL THEN
            :NEW.updated_at := SYSTIMESTAMP;
        END IF;
    END IF;
    IF UPDATING THEN
        :NEW.updated_at := SYSTIMESTAMP;
    END IF;
END;
/

-- qna_answers
CREATE SEQUENCE qna_answers_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER qna_answers_trg
BEFORE INSERT OR UPDATE ON qna_answers
FOR EACH ROW
BEGIN
    IF INSERTING THEN
        IF :NEW.id IS NULL THEN
            :NEW.id := qna_answers_seq.NEXTVAL;
        END IF;
        IF :NEW.created_at IS NULL THEN
            :NEW.created_at := SYSTIMESTAMP;
        END IF;
        IF :NEW.updated_at IS NULL THEN
            :NEW.updated_at := SYSTIMESTAMP;
        END IF;
    END IF;
    IF UPDATING THEN
        :NEW.updated_at := SYSTIMESTAMP;
    END IF;
END;
/

-- ambulance_requests
CREATE SEQUENCE ambulance_requests_seq START WITH 1 INCREMENT BY 1;

CREATE OR REPLACE TRIGGER ambulance_requests_trg
BEFORE INSERT OR UPDATE ON ambulance_requests
FOR EACH ROW
BEGIN
    IF INSERTING THEN
        IF :NEW.id IS NULL THEN
            :NEW.id := ambulance_requests_seq.NEXTVAL;
        END IF;
        IF :NEW.created_at IS NULL THEN
            :NEW.created_at := SYSTIMESTAMP;
        END IF;
        IF :NEW.updated_at IS NULL THEN
            :NEW.updated_at := SYSTIMESTAMP;
        END IF;
    END IF;
    IF UPDATING THEN
        :NEW.updated_at := SYSTIMESTAMP;
    END IF;
END;
/

