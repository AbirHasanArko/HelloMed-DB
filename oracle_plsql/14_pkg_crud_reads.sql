-- ==========================================
-- 14_pkg_crud_reads.sql
-- ==========================================
create or replace package pkg_crud_reads as
   procedure get_active_departments (
      p_cursor out sys_refcursor
   );
   procedure get_active_categories (
      p_cursor out sys_refcursor
   );
   procedure get_active_facility_rooms (
      p_cursor out sys_refcursor
   );
   procedure get_all_active_doctors (
      p_cursor out sys_refcursor
   );
   procedure get_ambulance_requests (
      p_cursor out sys_refcursor
   );
   procedure get_ambulance_request_by_id (
      p_id     in number,
      p_cursor out sys_refcursor
   );
   procedure get_paginated_medicine_orders (
      p_limit  in number,
      p_offset in number,
      p_total  out number,
      p_cursor out sys_refcursor
   );
   procedure get_medicine_order_by_id (
      p_id     in number,
      p_cursor out sys_refcursor
   );
   procedure get_medicine_order_items (
      p_order_id in number,
      p_cursor   out sys_refcursor
   );
   procedure get_medicine_by_id (
      p_id     in number,
      p_cursor out sys_refcursor
   );
   procedure get_all_active_medicines (
      p_cursor out sys_refcursor
   );
   procedure get_paginated_medicines (
      p_limit  in number,
      p_offset in number,
      p_total  out number,
      p_cursor out sys_refcursor
   );
   procedure get_paginated_qna_questions (
      p_limit  in number,
      p_offset in number,
      p_total  out number,
      p_cursor out sys_refcursor
   );
   procedure get_qna_question_by_id (
      p_id     in number,
      p_cursor out sys_refcursor
   );
   procedure get_qna_answers (
      p_question_id in number,
      p_cursor      out sys_refcursor
   );
   procedure get_admin_staff_users (
      p_cursor out sys_refcursor
   );

   procedure get_homepage_data (
      p_dept_cursor   out sys_refcursor,
      p_doc_cursor    out sys_refcursor,
      p_art_cursor    out sys_refcursor,
      p_patient_count out number,
      p_dept_count    out number,
      p_doc_count     out number
   );

   procedure get_user_by_email (
      p_email  in varchar2,
      p_cursor out sys_refcursor
   );

   procedure get_user_by_id (
      p_id     in number,
      p_cursor out sys_refcursor
   );

   procedure get_medicines_by_ids (
      p_ids    in varchar2,
      p_cursor out sys_refcursor
   );

   procedure get_prescription_cart_items (
      p_appointment_id in number,
      p_cursor         out sys_refcursor
   );

   procedure get_order_cart_items (
      p_order_id in number,
      p_cursor   out sys_refcursor
   );

   procedure get_doctor_by_id (
      p_doctor_id in number,
      p_cursor    out sys_refcursor
   );

   procedure get_paginated_patient_appts (
      p_user_id in number,
      p_limit   in number,
      p_offset  in number,
      p_total   out number,
      p_cursor  out sys_refcursor
   );
   procedure get_appointment_payments (
      p_appointment_id in number,
      p_cursor         out sys_refcursor
   );
   procedure get_appointment_by_id (
      p_appointment_id in number,
      p_cursor         out sys_refcursor
   );

   procedure get_paginated_doctors (
      p_limit  in number,
      p_offset in number,
      p_total  out number,
      p_cursor out sys_refcursor
   );

   procedure get_paginated_all_appointments (
      p_limit  in number,
      p_offset in number,
      p_total  out number,
      p_cursor out sys_refcursor
   );

   procedure get_recent_patient_appts (
      p_user_id in number,
      p_limit   in number,
      p_cursor  out sys_refcursor
   );

   procedure get_appt_prescription_items (
      p_appointment_id in number,
      p_cursor         out sys_refcursor
   );
   procedure get_appointment_chat_messages (
      p_appointment_id in number,
      p_cursor         out sys_refcursor
   );
   procedure check_slot_availability (
      p_doctor_id     in number,
      p_scheduled_for in timestamp,
      p_exclude_id    in number,
      p_count         out number
   );
   procedure get_doctor_calendar_summary (
      p_doctor_id in number,
      p_cursor    out sys_refcursor
   );

   procedure get_article_by_id (
      p_id     in number,
      p_cursor out sys_refcursor
   );
   procedure get_article_comments (
      p_article_id in number,
      p_cursor     out sys_refcursor
   );
   procedure get_paginated_doctor_articles (
      p_user_id in number,
      p_limit   in number,
      p_offset  in number,
      p_total   out number,
      p_cursor  out sys_refcursor
   );
   procedure get_paginated_admin_articles (
      p_limit  in number,
      p_offset in number,
      p_total  out number,
      p_cursor out sys_refcursor
   );
   procedure get_active_article_categories (
      p_cursor out sys_refcursor
   );

   procedure get_paginated_payments (
      p_limit  in number,
      p_offset in number,
      p_total  out number,
      p_cursor out sys_refcursor
   );
   procedure get_payment_by_id (
      p_id     in number,
      p_cursor out sys_refcursor
   );

   procedure get_department_by_id (
      p_department_id in number,
      p_cursor        out sys_refcursor
   );

   procedure get_paginated_departments (
      p_limit  in number,
      p_offset in number,
      p_total  out number,
      p_cursor out sys_refcursor
   );

   procedure get_paginated_facility_rooms (
      p_limit  in number,
      p_offset in number,
      p_total  out number,
      p_cursor out sys_refcursor
   );
   procedure get_facility_room_by_id (
      p_id     in number,
      p_cursor out sys_refcursor
   );
   procedure get_all_active_facility_rooms (
      p_cursor out sys_refcursor
   );
   procedure get_all_facility_rooms (
      p_cursor out sys_refcursor
   );
   procedure get_future_facility_bookings (
      p_room_id in number,
      p_cursor  out sys_refcursor
   );

   procedure get_paginated_inventory_items (
      p_limit  in number,
      p_offset in number,
      p_total  out number,
      p_cursor out sys_refcursor
   );
   procedure get_inventory_item_by_id (
      p_id     in number,
      p_cursor out sys_refcursor
   );

   procedure get_admin_reports (
      p_start_date         in timestamp,
      p_end_date           in timestamp,
      p_total_appointments out number,
      p_total_revenue      out number,
      p_medicine_sales     out number
   );

   procedure get_doctor_reports (
      p_doctor_id              in number,
      p_start_date             in timestamp,
      p_end_date               in timestamp,
      p_total_appointments     out number,
      p_completed_appointments out number,
      p_total_revenue          out number
   );

   procedure get_audit_log_entity_types (
      p_cursor out sys_refcursor
   );

   procedure get_staff_dashboard_stats (
      p_pending_appointments out number,
      p_today_appointments   out number,
      p_doctor_count         out number,
      p_published_articles   out number,
      p_pending_ambulance    out number
   );

   procedure get_queue_appointments_by_date (
      p_date   in varchar2,
      p_cursor out sys_refcursor
   );

   procedure get_doctor_by_doc_id (
      p_id in number,
      p_cursor    out sys_refcursor
   );

   procedure get_patient_profile (
      p_user_id in number,
      p_cursor  out sys_refcursor
   );

   procedure get_doctor_by_user_id (
      p_user_id in number,
      p_cursor  out sys_refcursor
   );
   
   procedure get_user_medicine_orders (
      p_user_id in number,
      p_cursor  out sys_refcursor
   );
end pkg_crud_reads;
/

create or replace package body pkg_crud_reads as

   procedure get_active_departments (
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from departments
                         where is_active = 1
                         order by name;
   end get_active_departments;

   procedure get_active_categories (
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from article_categories
                         where is_active = 1
                         order by name;
   end get_active_categories;

   procedure get_active_facility_rooms (
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from facility_rooms
                         where is_active = 1
                         order by room_number;
   end get_active_facility_rooms;

   procedure get_all_active_doctors (
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from doctors
                         where is_active = 1
                         order by name;
   end get_all_active_doctors;

   procedure get_ambulance_requests (
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from ambulance_requests
                         order by
                           case status
                              when 'pending'    then
                                 1
                              when 'dispatched' then
                                 2
                              when 'resolved'   then
                                 3
                              when 'cancelled'  then
                                 4
                              else
                                 5
                           end
                        asc,
                           created_at desc;
   end get_ambulance_requests;

   procedure get_ambulance_request_by_id (
      p_id     in number,
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from ambulance_requests
                         where id = p_id;
   end get_ambulance_request_by_id;

   procedure get_paginated_medicine_orders (
      p_limit  in number,
      p_offset in number,
      p_total  out number,
      p_cursor out sys_refcursor
   ) is
   begin
      select count(*)
        into p_total
        from medicine_orders;
      open p_cursor for select *
                                          from (
                                           select a.*,
                                                  rownum rnum
                                             from (
                                              select *
                                                from medicine_orders
                                               order by created_at desc
                                           ) a
                                            where rownum <= p_offset + p_limit
                                        )
                        where rnum > p_offset;
   end get_paginated_medicine_orders;

   procedure get_medicine_order_by_id (
      p_id     in number,
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from medicine_orders
                         where id = p_id;
   end get_medicine_order_by_id;

   procedure get_medicine_order_items (
      p_order_id in number,
      p_cursor   out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from medicine_order_items
                         where medicine_order_id = p_order_id;
   end get_medicine_order_items;

   procedure get_medicine_by_id (
      p_id     in number,
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from medicines
                         where id = p_id;
   end get_medicine_by_id;

   procedure get_all_active_medicines (
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select id,
                               name,
                               power,
                               amount
                                            from medicines
                         where is_active = 1
                         order by name;
   end get_all_active_medicines;

   procedure get_paginated_medicines (
      p_limit  in number,
      p_offset in number,
      p_total  out number,
      p_cursor out sys_refcursor
   ) is
   begin
      select count(*)
        into p_total
        from medicines;
      open p_cursor for select *
                                          from (
                                           select a.*,
                                                  rownum rnum
                                             from (
                                              select *
                                                from medicines
                                               order by created_at desc
                                           ) a
                                            where rownum <= p_offset + p_limit
                                        )
                        where rnum > p_offset;
   end get_paginated_medicines;

   procedure get_paginated_qna_questions (
      p_limit  in number,
      p_offset in number,
      p_total  out number,
      p_cursor out sys_refcursor
   ) is
   begin
      select count(*)
        into p_total
        from qna_questions;
      open p_cursor for select *
                                          from (
                                           select a.*,
                                                  rownum rnum
                                             from (
                                              select *
                                                from qna_questions
                                               order by created_at desc
                                           ) a
                                            where rownum <= p_offset + p_limit
                                        )
                        where rnum > p_offset;
   end get_paginated_qna_questions;

   procedure get_qna_question_by_id (
      p_id     in number,
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from qna_questions
                         where id = p_id;
   end get_qna_question_by_id;

   procedure get_qna_answers (
      p_question_id in number,
      p_cursor      out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from qna_answers
                         where qna_question_id = p_question_id
                         order by created_at asc;
   end get_qna_answers;

   procedure get_admin_staff_users (
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from users
                         where role in ( 'admin',
                                         'staff' );
   end get_admin_staff_users;

   procedure get_homepage_data (
      p_dept_cursor   out sys_refcursor,
      p_doc_cursor    out sys_refcursor,
      p_art_cursor    out sys_refcursor,
      p_patient_count out number,
      p_dept_count    out number,
      p_doc_count     out number
   ) is
   begin
      select count(*)
        into p_patient_count
        from users
       where role = 'patient';
      select count(*)
        into p_dept_count
        from departments
       where is_active = 1;
      select count(*)
        into p_doc_count
        from doctors
       where is_active = 1;

      open p_dept_cursor for select *
                                                    from (
                                                     select *
                                                       from departments
                                                      where is_active = 1
                                                      order by is_featured desc,
                                                               NULLIF(featured_order, 0) asc nulls last,
                                                               created_at desc
                                                  )
                             where rownum <= 6;

      open p_doc_cursor for select *
                                                  from (
                                                   select d.*,
                                                          u.name as user_name,
                                                          dept.name as department_name
                                                     from doctors d
                                                     join users u
                                                   on d.user_id = u.id
                                                     join departments dept
                                                   on d.department_id = dept.id
                                                    where d.is_active = 1
                                                    order by d.is_featured desc,
                                                             NULLIF(d.featured_order, 0) asc nulls last,
                                                             d.created_at desc
                                                )
                            where rownum <= 8;

      open p_art_cursor for select *
                                                  from (
                                                   select a.*,
                                                          ac.name as category_name,
                                                          u.name as author_name
                                                     from articles a
                                                     join article_categories ac
                                                   on a.article_category_id = ac.id
                                                     join users u
                                                   on a.user_id = u.id
                                                    where a.is_published = 1
                                                    order by a.is_featured desc,
                                                             NULLIF(a.featured_order, 0) asc nulls last,
                                                             a.published_at desc
                                                )
                            where rownum <= 3;
   end get_homepage_data;

   procedure get_user_by_email (
      p_email  in varchar2,
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from users
                         where email = p_email;
   end get_user_by_email;

   procedure get_medicines_by_ids (
      p_ids    in varchar2,
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from medicines
                         where ','
                               || p_ids
                               || ',' like '%,'
                                           || id
                                           || ',%';
   end get_medicines_by_ids;

   procedure get_prescription_cart_items (
      p_appointment_id in number,
      p_cursor         out sys_refcursor
   ) is
   begin
      open p_cursor for select pi.*,
                               m.name as medicine_name,
                               m.price as medicine_price,
                               m.is_active,
                               m.stock_quantity
                                            from appointment_prescription_items pi
                                            join medicines m
                                          on pi.medicine_id = m.id
                         where pi.appointment_id = p_appointment_id;
   end get_prescription_cart_items;

   procedure get_order_cart_items (
      p_order_id in number,
      p_cursor   out sys_refcursor
   ) is
   begin
      open p_cursor for select oi.*,
                               m.name as medicine_name,
                               m.price as medicine_price,
                               m.image_path
                                            from medicine_order_items oi
                                            join medicines m
                                          on oi.medicine_id = m.id
                         where oi.medicine_order_id = p_order_id;
   end get_order_cart_items;

   procedure get_doctor_by_id (
      p_doctor_id in number,
      p_cursor    out sys_refcursor
   ) is
   begin
      open p_cursor for select d.*,
                               u.name as user_name,
                               u.email,
                               dept.name as department_name
                                            from doctors d
                                            join users u
                                          on d.user_id = u.id
                                            join departments dept
                                          on d.department_id = dept.id
                         where d.id = p_doctor_id;
   end get_doctor_by_id;

   procedure get_paginated_patient_appts (
      p_user_id in number,
      p_limit   in number,
      p_offset  in number,
      p_total   out number,
      p_cursor  out sys_refcursor
   ) is
   begin
      select count(*)
        into p_total
        from appointments
       where user_id = p_user_id;
      open p_cursor for select *
                                          from (
                                           select a.*,
                                                  rownum rnum
                                             from (
                                              select *
                                                from appointments
                                               where user_id = p_user_id
                                               order by scheduled_for desc
                                           ) a
                                            where rownum <= p_offset + p_limit
                                        )
                        where rnum > p_offset;
   end get_paginated_patient_appts;

   procedure get_appointment_payments (
      p_appointment_id in number,
      p_cursor         out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from payments
                         where appointment_id = p_appointment_id;
   end get_appointment_payments;

   procedure get_appointment_by_id (
      p_appointment_id in number,
      p_cursor         out sys_refcursor
   ) is
   begin
      open p_cursor for select a.*,
                               d.id as doctor_id,
                               du.name as doctor_name,
                               dept.name as department_name,
                               s.name as service_name
                                            from appointments a
                                            join doctors d
                                          on a.doctor_id = d.id
                                            join users du
                                          on d.user_id = du.id
                                            join departments dept
                                          on a.department_id = dept.id
                                            left join services s
                                          on a.service_id = s.id
                         where a.id = p_appointment_id;
   end get_appointment_by_id;

   procedure get_recent_patient_appts (
      p_user_id in number,
      p_limit   in number,
      p_cursor  out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from (
                                             select *
                                               from appointments
                                              where user_id = p_user_id
                                              order by scheduled_for desc
                                          )
                         where rownum <= p_limit;
   end get_recent_patient_appts;

   procedure get_paginated_all_appointments (
      p_limit  in number,
      p_offset in number,
      p_total  out number,
      p_cursor out sys_refcursor
   ) is
   begin
      select count(*)
        into p_total
        from appointments;
      open p_cursor for select *
                                          from (
                                           select a.*,
                                                  rownum rnum
                                             from (
                                              select *
                                                from appointments
                                               order by created_at desc
                                           ) a
                                            where rownum <= p_offset + p_limit
                                        )
                        where rnum > p_offset;
   end get_paginated_all_appointments;

   procedure get_paginated_doctors (
      p_limit  in number,
      p_offset in number,
      p_total  out number,
      p_cursor out sys_refcursor
   ) is
   begin
      select count(*)
        into p_total
        from doctors;
      open p_cursor for select *
                                          from (
                                           select a.*,
                                                  rownum rnum
                                             from (
                                              select *
                                                from doctors
                                               order by created_at desc
                                           ) a
                                            where rownum <= p_offset + p_limit
                                        )
                        where rnum > p_offset;
   end get_paginated_doctors;

   procedure get_appt_prescription_items (
      p_appointment_id in number,
      p_cursor         out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from appointment_prescription_items
                         where appointment_id = p_appointment_id;
   end get_appt_prescription_items;

   procedure get_appointment_chat_messages (
      p_appointment_id in number,
      p_cursor         out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from appointment_chat_messages
                         where appointment_id = p_appointment_id
                         order by created_at asc;
   end get_appointment_chat_messages;

   procedure check_slot_availability (
      p_doctor_id     in number,
      p_scheduled_for in timestamp,
      p_exclude_id    in number,
      p_count         out number
   ) is
   begin
      select count(*)
        into p_count
        from appointments
       where doctor_id = p_doctor_id
         and id != p_exclude_id
         and scheduled_for = p_scheduled_for
         and status in ( 'pending',
                         'confirmed' );
   end check_slot_availability;

   procedure get_doctor_calendar_summary (
      p_doctor_id in number,
      p_cursor    out sys_refcursor
   ) is
   begin
      open p_cursor for select to_char(
                                             scheduled_for,
                                             'YYYY-MM-DD'
                                          ) as appointment_date,
                               count(*) as total
                                            from appointments
                         where doctor_id = p_doctor_id
                           and scheduled_for >= trunc(sysdate)
                           and scheduled_for < trunc(sysdate) + 31
                         group by to_char(
                           scheduled_for,
                           'YYYY-MM-DD'
                        )
                         order by to_char(
                           scheduled_for,
                           'YYYY-MM-DD'
                        );
   end get_doctor_calendar_summary;

   procedure get_article_by_id (
      p_id     in number,
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from articles
                         where id = p_id;
   end get_article_by_id;

   procedure get_article_comments (
      p_article_id in number,
      p_cursor     out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from article_comments
                         where article_id = p_article_id
                         order by created_at desc;
   end get_article_comments;

   procedure get_paginated_doctor_articles (
      p_user_id in number,
      p_limit   in number,
      p_offset  in number,
      p_total   out number,
      p_cursor  out sys_refcursor
   ) is
   begin
      select count(*)
        into p_total
        from articles
       where user_id = p_user_id;
      open p_cursor for select *
                                          from (
                                           select a.*,
                                                  rownum rnum
                                             from (
                                              select *
                                                from articles
                                               where user_id = p_user_id
                                               order by created_at desc
                                           ) a
                                            where rownum <= p_offset + p_limit
                                        )
                        where rnum > p_offset;
   end get_paginated_doctor_articles;

   procedure get_paginated_admin_articles (
      p_limit  in number,
      p_offset in number,
      p_total  out number,
      p_cursor out sys_refcursor
   ) is
   begin
      select count(*)
        into p_total
        from articles;
      open p_cursor for select *
                                          from (
                                           select a.*,
                                                  rownum rnum
                                             from (
                                              select *
                                                from articles
                                               order by created_at desc
                                           ) a
                                            where rownum <= p_offset + p_limit
                                        )
                        where rnum > p_offset;
   end get_paginated_admin_articles;

   procedure get_active_article_categories (
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from article_categories
                         where is_active = 1
                         order by name;
   end get_active_article_categories;

   procedure get_paginated_payments (
      p_limit  in number,
      p_offset in number,
      p_total  out number,
      p_cursor out sys_refcursor
   ) is
   begin
      select count(*)
        into p_total
        from payments;
      open p_cursor for select *
                                          from (
                                           select a.*,
                                                  rownum rnum
                                             from (
                                              select *
                                                from payments
                                               order by created_at desc
                                           ) a
                                            where rownum <= p_offset + p_limit
                                        )
                        where rnum > p_offset;
   end get_paginated_payments;

   procedure get_payment_by_id (
      p_id     in number,
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from payments
                         where id = p_id;
   end get_payment_by_id;

   procedure get_department_by_id (
      p_department_id in number,
      p_cursor        out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from departments
                         where id = p_department_id;
   end get_department_by_id;

   procedure get_paginated_departments (
      p_limit  in number,
      p_offset in number,
      p_total  out number,
      p_cursor out sys_refcursor
   ) is
   begin
      select count(*)
        into p_total
        from departments;
      open p_cursor for select *
                                          from (
                                           select a.*,
                                                  rownum rnum
                                             from (
                                              select d.*,
                                                     (
                                                        select count(*)
                                                          from doctors doc
                                                         where doc.department_id = d.id
                                                     ) as doctors_count
                                                from departments d
                                               order by created_at desc
                                           ) a
                                            where rownum <= p_offset + p_limit
                                        )
                        where rnum > p_offset;
   end get_paginated_departments;

   procedure get_paginated_facility_rooms (
      p_limit  in number,
      p_offset in number,
      p_total  out number,
      p_cursor out sys_refcursor
   ) is
   begin
      select count(*)
        into p_total
        from facility_rooms;
      open p_cursor for select *
                                          from (
                                           select a.*,
                                                  rownum rnum
                                             from (
                                              select *
                                                from facility_rooms
                                               order by created_at desc
                                           ) a
                                            where rownum <= p_offset + p_limit
                                        )
                        where rnum > p_offset;
   end get_paginated_facility_rooms;

   procedure get_facility_room_by_id (
      p_id     in number,
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from facility_rooms
                         where id = p_id;
   end get_facility_room_by_id;

   procedure get_all_active_facility_rooms (
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from facility_rooms
                         where is_active = 1
                         order by room_number asc;
   end get_all_active_facility_rooms;

   procedure get_all_facility_rooms (
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from facility_rooms
                         order by room_number asc;
   end get_all_facility_rooms;

   procedure get_future_facility_bookings (
      p_room_id in number,
      p_cursor  out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from facility_bookings
                         where facility_room_id = p_room_id
                           and start_time >= trunc(sysdate)
                         order by start_time asc;
   end get_future_facility_bookings;

   procedure get_paginated_inventory_items (
      p_limit  in number,
      p_offset in number,
      p_total  out number,
      p_cursor out sys_refcursor
   ) is
   begin
      select count(*)
        into p_total
        from inventory_items;
      open p_cursor for select *
                                          from (
                                           select a.*,
                                                  rownum rnum
                                             from (
                                              select *
                                                from inventory_items
                                               order by name asc
                                           ) a
                                            where rownum <= p_offset + p_limit
                                        )
                        where rnum > p_offset;
   end get_paginated_inventory_items;

   procedure get_inventory_item_by_id (
      p_id     in number,
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from inventory_items
                         where id = p_id;
   end get_inventory_item_by_id;

   procedure get_admin_reports (
      p_start_date         in timestamp,
      p_end_date           in timestamp,
      p_total_appointments out number,
      p_total_revenue      out number,
      p_medicine_sales     out number
   ) is
   begin
      select count(*)
        into p_total_appointments
        from appointments
       where scheduled_for between p_start_date and p_end_date;

      select nvl(
         sum(amount),
         0
      )
        into p_total_revenue
        from payments
       where status = 'paid'
         and paid_at between p_start_date and p_end_date;

      select nvl(
         sum(total_amount),
         0
      )
        into p_medicine_sales
        from medicine_orders
       where status = 'completed'
         and created_at between p_start_date and p_end_date;
   end get_admin_reports;

   procedure get_doctor_reports (
      p_doctor_id              in number,
      p_start_date             in timestamp,
      p_end_date               in timestamp,
      p_total_appointments     out number,
      p_completed_appointments out number,
      p_total_revenue          out number
   ) is
   begin
      select count(*)
        into p_total_appointments
        from appointments
       where doctor_id = p_doctor_id
         and scheduled_for between p_start_date and p_end_date;

      select count(*)
        into p_completed_appointments
        from appointments
       where doctor_id = p_doctor_id
         and status = 'completed'
         and scheduled_for between p_start_date and p_end_date;

      select nvl(sum(p.amount), 0)
        into p_total_revenue
        from payments p
        join appointments a on a.id = p.appointment_id
       where a.doctor_id = p_doctor_id
         and p.status = 'paid'
         and p.paid_at between p_start_date and p_end_date;
   end get_doctor_reports;

   procedure get_audit_log_entity_types (
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select distinct entity_type
                                            from audit_logs
                         order by entity_type;
   end get_audit_log_entity_types;

   procedure get_staff_dashboard_stats (
      p_pending_appointments out number,
      p_today_appointments   out number,
      p_doctor_count         out number,
      p_published_articles   out number,
      p_pending_ambulance    out number
   ) is
   begin
      select count(*)
        into p_pending_appointments
        from appointments
       where status = 'pending';
      select count(*)
        into p_today_appointments
        from appointments
       where trunc(scheduled_for) = trunc(sysdate);
      select count(*)
        into p_doctor_count
        from doctors
       where is_active = 1;
      select count(*)
        into p_published_articles
        from articles
       where is_published = 1;
      select count(*)
        into p_pending_ambulance
        from ambulance_requests
       where status = 'pending';
   end get_staff_dashboard_stats;

   procedure get_queue_appointments_by_date (
      p_date   in varchar2,
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from appointments
                         where trunc(scheduled_for) = to_date(p_date,
                             'YYYY-MM-DD')
                           and status in ( 'pending',
                                           'confirmed' )
                         order by scheduled_for asc;
   end get_queue_appointments_by_date;

   procedure get_user_by_id (
      p_id     in number,
      p_cursor out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                                            from users
                         where id = p_id;
   end get_user_by_id;

   procedure get_doctor_by_doc_id (
      p_id in number,
      p_cursor    out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                          from doctors
                         where id = p_id;
   end get_doctor_by_doc_id;

   procedure get_patient_profile (
      p_user_id in number,
      p_cursor  out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                          from patient_profiles
                         where user_id = p_user_id;
   end get_patient_profile;

   procedure get_doctor_by_user_id (
      p_user_id in number,
      p_cursor  out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                          from doctors
                         where user_id = p_user_id;
   end get_doctor_by_user_id;

   procedure get_user_medicine_orders (
      p_user_id in number,
      p_cursor  out sys_refcursor
   ) is
   begin
      open p_cursor for select *
                          from medicine_orders
                         where user_id = p_user_id
                         order by created_at desc;
   end get_user_medicine_orders;

end pkg_crud_reads;
/