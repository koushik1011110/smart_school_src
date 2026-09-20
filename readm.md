1/ Studentfeemaster_model.php 
   C:\xampp\htdocs\smart_school_src\application\libraries\Smsgateway.php
   C:\xampp\htdocs\smart_school_src\application\libraries\Mailsmsconf.php
 (sms  api)

2/for acsending format  use student_model.php


3/Separate “Select All” Option for Each Fee Group & Fix Date Selection Issue

Description:

On the fees add page (http://localhost/smart_school_src/studentfee/addfee/4), while adding student fees, there is currently a single “Select All” option within the fee group section. Please update this functionality so that if multiple fee groups exist, each group has its own independent “Select All” option. This will allow users to select all fee types within a specific group without affecting other groups.
After implementing the above change, an issue has been observed where the date selection is not working while collecting fees. This needs to be fixed so that users can properly select a date during fee collection.


4/ Report: Fee Type Collection Summary (for overall fees ammount in one pdf)
  
   application/views/financereports/_finance.php
   application/models/Studentfeemaster_model.php +1
   application/controllers/Financereports.php
   application/views/financereports/feetype_collection_summary.php +1


5/ Print receipt 2 row
    backend/dist/css/print_fee_receipt.css
    application/views/studentfee/studentAddfee.php
    application/views/print/printFeesByGroup.php
    application/views/print/printFeesByGroupArray.php


6/ Fees Ammmount . remove / convert to integer like 55.00 to 55
   
   application/helper/custom_helper.php
   application/views/studentfee/reportByClass.php
   application/views/print/printFeesByGroupArray.php


7/ Fake paid print receipt for teachers son/daughters

   application/views/studentfee/studentAddfee.php
   application/controllers/Studentfee.php
   application/views/print/schoolerPrintFeesByGroupArray.php

8/ Fees MAster Copy Session

   application/models/Feesessiongroup_model.php
   application/controllers/admin/feesmaster.php
   application/views/admin/feemaster/feemasterList.php
   application/language/English/app_files/system_lang.php


9/ STudent Report Cash/Upi
   
   application/models/studentfeemaster_model.php
   application/views/financereports/feetype_collection_summary.php


10/ Staff Attendance report exact time show

   application/models/Staffattendancemodel.php
   application/views/attendancereports/staffattendancereport.php


11/ password change for print receipt 

   application/views/studentfee/studentAddfee.php


12/ Staff Checkout Update
   
   application/controllers/admin/Staffattendance.php
   application/models/Staffattendancemodel.php
   application/views/admin/staffattendance/staffattendancelist.php

   sql: ALTER TABLE `staff_attendance`
        ADD COLUMN `check_out_time` TIME DEFAULT NULL;

  *For Chekout Report*
  application/views/attendencereports/staffattendancereport.php



 13/ The goal of this change is to update the student fee collection pages (main search page and due fees search page) to:

        *1* Replace the "Admission Number" column with the "Roll Number" column.
        *2* Automatically sort student records by Roll Number in ascending order.
        
        application/model/Student_model.php
        application/controller/Studentfee.php
        application/views/studentfee/studentfeeSearch.php
        application/views/studentfee/studentfeeSearchFee.php

14/ IN  fees Invoice powered by kkwebmart add

      application/models/Setting_model.php
            changes:    
            
            line no: 303-311

            public function get_receiptfooter() {
            $image = $this->db->select('footer_content')->from('print_headerfooter')->where('print_type', 'student_receipt')->get()->row_array();
            return $image['footer_content'];
            $footer = isset($image['footer_content']) ? $image['footer_content'] : '';
            $logo_url = base_url('uploads/school_content/logo/kkwebmart_logo.png');
            $branding = '<div style="text-align: center; margin-top: 10px; margin-bottom: 5px; font-size: 11px; color: #555; font-family: Arial, sans-serif;"><img src="' . $logo_url . '" alt="KKWEBMART Logo" style="height: 20px; vertical-align: middle; margin-right: 5px; display: inline-block;"> Powered by <strong>KKWEBMART</strong></div>';
            return $footer . $branding;
            }
      
      application/views/user/fees/printFeesByGroupArray.php
            439

             <?php $this->setting_model->get_receiptfooter();?>
             replcace to-
             <?php echo $this->setting_model->get_receiptfooter();?>

      application/views/users/student
              same as  application/views/user/fees/printFeesByGroupArray.php 
   



15/  Exam Marks BUlk Upload for jagiroad 

           application/views/admin/examgroup/_getSubjectByExam.php
           application/views/admin/examgroup/addexam.php
           application/views/admin/examgroup/_partialexamList.php
           application/controllers/admin/Examgroup.php


16/  Sync Database offline for software
    
           \application\models\Sync_model.php
           \application\controllers\admin\Sync.php
           \application\views\layout\header.php

17/  Send Reminder SMS

           \application\language\English\app_files\system_lang.php
           \application\controllers\admin\Mailsms.php
           \application\views\admin\mailsms\fee_reminder.php
           \application\models\Studentfeemaster_model.php



           INSERT INTO `sidebar_sub_menus` 
            (`sidebar_menu_id`, `menu`, `key`, `lang_key`, `url`, `level`, `access_permissions`, `permission_group_id`, `activate_controller`, `activate_methods`, `addon_permission`, `is_active`, `created_at`) 
            SELECT id, 'Reminder', NULL, 'fee_reminder', 'admin/mailsms/fee_reminder', 9, "('email', 'can_view')", NULL, 'mailsms', 'fee_reminder', NULL, 1, NOW() 
            FROM `sidebar_menus` 
            WHERE `lang_key` = 'communicate' OR `menu` LIKE '%Communicate%' 
            LIMIT 1;

18/ Fix Fee Receipt Blank Print Issue (Chrome / Edge Print Preview Blank Fix)

    Karan / Reason for Edit:
    - Fee receipt print karne par Chrome aur Edge browser me print preview completely blank (khali) aa raha tha.
    - Root Causes:
      1. Purane `Popup()` function me `window.open` popup window use hoti thi jisme `frameDoc.onafterprint = closeAfterPrint` (`window.close()`) laga hua tha. Modern Chrome/Edge me `onafterprint` event print preview render hone se pehle hi fire ho jata hai, jisse window destroy ho jati thi aur receipt preview bilkul blank dikhta tha.
      2. Dusre views me `frame.remove()` `setTimeout(..., 500)` ke sath tha jo print preview open hote hi DOM se iframe delete kar deta tha, jisse preview white/blank ho jata tha.
      3. AJAX response se aane wale receipt HTML me already complete `<!DOCTYPE html><html>...` structure tha, jise code dobara extra `<html><body>` tags me wrap kar raha tha.
    - Solution:
      - Sabhi fee views ke print function ko replace karke persistent hidden off-screen `<iframe>` (`#printFrame`) method implement kiya gaya.
      - Full HTML doctype validation aur `print_fee_receipt.css` auto-injection ensure kiya gaya.
      - Print dialog khulne ke dauran iframe ko DOM me barkarar rakha gaya taaki Chrome preview bina kisi issue ke clearly render ho.

    Modified Files Path:
    - \application\views\studentfee\studentAddfee.php
    - \application\views\studentfee\studentAddfee.md
    - \application\views\studentfee\studentSearchFee.php
    - \application\views\user\student\getfees.php
    - \application\views\user\student\fees.php
    - \application\views\user\fees\getfees.php

19/ Environment Setting & Print Selected Invoice Number Fix

    - Environment set to 'production' in index.php to remove the dashboard warning banner.
    - Added Invoice Number (student_fees_deposite_id / inv_no) and Collected By metadata to fee receipt header.
    - Added Payment ID column to fee receipt table and displayed deposit invoice numbers under Payment ID in Office, Student, and Bank copies.

    Modified Files Path:
    \smart_school_src\index.php
    \smart_school_src\application\views\print\printFeesByGroupArray.php

20/ Fee Receipt Dual Copy (Office Copy & Student Copy) & Combined Monthly Total in Single Row

    - Fee Receipt me "Office Copy" aur "Student Copy" dono side-by-side print hoti hain (Bank Copy removed).
    - A4 sheet par dono copies (Office Copy & Student Copy) 2-column side-by-side layout me dashed divider ke sath print hoti hain taaki paper waste na ho aur aasani se cut kiya ja sake.
    - Jab multiple months ka fee payment kiya jata hai (jaise jan, feb, march...), to alag-alag month ki alag-alag rows aane ke bajaye, sabhi months ek hi row me consolidate hokar aate hain (jaise `1 (jan, feb, march)`).
    - Consolidated row ke andar hi Amount, Payment IDs, Payment Mode, Paid Amount, Fine aur Balance display hota hai aur neeche Grand Total row aati hai.

    Modified Files Path:
    - \backend\dist\css\print_fee_receipt.css
    - \application\views\print\printFeesByGroupArray.php
    - \application\views\print\schoolerPrintFeesByGroupArray.php
    - \application\views\print\printFeesByGroup.php
    - \application\views\print\printFeesByName.php
    - \application\views\print\printTransportFeesByGroup.php
    - \application\views\print\printTransportFeesByName.php
    - \application\views\user\student\printFeesByGroupArray.php
    - \application\views\user\fees\printFeesByGroupArray.php
    - Database `sch_settings.is_duplicate_fees_invoice = '0,1'`

