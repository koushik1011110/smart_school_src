<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat();?>
<style type="text/css">
    .page-break { display: block; page-break-before: always; }
    @media print {
        .page-break { display: block; page-break-before: always; }
        .col-sm-1, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-sm-10, .col-sm-11, .col-sm-12 {
            float: left;
        }
        .col-sm-12 { width: 100%; }
        .col-sm-11 { width: 91.66666667%; }
        .col-sm-10 { width: 83.33333333%; }
        .col-sm-9 { width: 75%; }
        .col-sm-8 { width: 66.66666667%; }
        .col-sm-7 { width: 58.33333333%; }
        .col-sm-6 { width: 50%; }
        .col-sm-5 { width: 41.66666667%; }
        .col-sm-4 { width: 33.33333333%; }
        .col-sm-3 { width: 25%; }
        .col-sm-2 { width: 16.66666667%; }
        .col-sm-1 { width: 8.33333333%; }
    }
</style>

<html lang="en">
    <head>         
        <title><?php echo $this->lang->line('fees_receipt'); ?></title>    
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/bootstrap/css/bootstrap.min.css"> 
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/AdminLTE.min.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/print_fee_receipt.css">
    </head>
    <body>       
        <?php 
            $inv_list = array();
            $collected_by_names = array();
            if (!empty($feeList->amount_detail)) {
                $dep = json_decode($feeList->amount_detail);
                if (is_object($dep) || is_array($dep)) {
                    foreach ($dep as $d_val) {
                        if (!empty($feeList->student_fees_deposite_id) && !empty($d_val->inv_no)) {
                            $inv_list[] = $feeList->student_fees_deposite_id . "/" . $d_val->inv_no;
                        }
                        if (!empty($d_val->collected_by)) {
                            $collected_by_names[] = $d_val->collected_by;
                        }
                    }
                }
            }
            $inv_list = array_unique($inv_list);
            $collected_by_names = array_unique($collected_by_names);
        ?>
        <?php
            $print_copy = isset($sch_setting->is_duplicate_fees_invoice) ? explode(',', $sch_setting->is_duplicate_fees_invoice) : array('0', '1');
            $show_office = in_array('0', $print_copy);
            $show_student = in_array('1', $print_copy);
            if (!$show_office && !$show_student) {
                $show_office = true;
                $show_student = true;
            }

            $copies = array();
            if ($show_office) {
                $copies[] = array('type' => 'office', 'label' => $this->lang->line('office_copy'));
            }
            if ($show_student) {
                $copies[] = array('type' => 'student', 'label' => $this->lang->line('student_copy'));
            }
            $is_dual = (count($copies) > 1);
        ?>

        <div class="container"> 
            <?php if ($is_dual) { ?><div class="row fee-receipt-dual-row"><?php } ?>
            <?php foreach ($copies as $copy_item) { ?>
                <div class="<?php echo $is_dual ? 'col-sm-6 fee-receipt-pane fee-receipt-pane--' . $copy_item['type'] : 'col-lg-12 col-sm-12'; ?>">
                    <div class="row">
                        <div id="content" class="col-lg-12 col-sm-12">
                            <div class="invoice">
                                <div class="row header">
                                    <div class="col-sm-12">                               
                                        <img class="receipt-header-img" src="<?php echo $this->media_storage->getImageURL('/uploads/print_headerfooter/student_receipt/'.$this->setting_model->get_receiptheader()); ?>" style="height: 100px; width: 100%;">
                                    </div>
                                </div> 
                             
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <div class="fee-copy-badge"><?php echo $copy_item['label']; ?></div>
                                    </div>
                                </div>
                                
                                <div class="row">                           
                                    <div class="col-xs-6 text-left">
                                        <br/>
                                        <address>
                                            <strong><?php echo $this->customlib->getFullName($feeList->firstname, $feeList->middlename, $feeList->lastname, $sch_setting->middlename, $sch_setting->lastname); ?></strong><?php echo " (" . $feeList->admission_no . ")"; ?> <br>
                                            <?php echo $this->lang->line('father_name'); ?>: <?php echo $feeList->father_name; ?><br>
                                            <?php echo $this->lang->line('class'); ?>: <?php echo $feeList->class . " (" . $feeList->section . ")"; ?>
                                        </address>
                                    </div>
                                    <div class="col-xs-6 text-right">
                                        <br/>
                                        <address>
                                            <strong>Date : <?php
                                                $date = date('d-m-Y');
                                                echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($date));
                                                ?></strong><br/>
                                            <?php if (!empty($inv_list)) { ?>
                                                <strong><?php echo $this->lang->line('invoice_no'); ?> / <?php echo $this->lang->line('payment_id'); ?>: <?php echo implode(', ', $inv_list); ?></strong><br/>
                                            <?php } ?>
                                            <?php if (!empty($collected_by_names)) { ?>
                                                <strong><?php echo $this->lang->line('collected_by'); ?>: <?php echo implode(', ', $collected_by_names); ?></strong><br/>
                                            <?php } ?>
                                        </address>                               
                                    </div>
                                </div>
                                <hr style="margin-top: 0px; margin-bottom: 0px;" />
                                <div class="row">
                                    <?php if (!empty($feeList)) { ?>
                                        <table class="table table-striped table-responsive" style="font-size: 8pt;">
                                            <thead>
                                                <th><?php echo $this->lang->line('fees_group'); ?></th>
                                                <th><?php echo $this->lang->line('fees_code'); ?></th>
                                                <th><?php echo $this->lang->line('due_date'); ?></th>
                                                <th><?php echo $this->lang->line('status'); ?></th>
                                                <th class="text text-right"><?php echo $this->lang->line('amount'); ?></th>
                                                <th class="text text-center"><?php echo $this->lang->line('payment_id'); ?></th>
                                                <th class="text text-center"><?php echo $this->lang->line('mode'); ?></th>
                                                <th><?php echo $this->lang->line('date'); ?></th>
                                                <th class="text text-right"><?php echo $this->lang->line('paid'); ?></th>
                                                <th class="text text-right"><?php echo $this->lang->line('fine'); ?></th>
                                                <th class="text text-right"><?php echo $this->lang->line('discount'); ?></th>
                                                <th class="text text-right"><?php echo $this->lang->line('balance'); ?></th>
                                                <th></th>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $fee_discount = 0;
                                                $fee_paid = 0;
                                                $fee_fine = 0;
                                                if (!empty($feeList->amount_detail)) {
                                                    $fee_deposits = json_decode($feeList->amount_detail);
                                                    if (is_object($fee_deposits) || is_array($fee_deposits)) {
                                                        foreach ($fee_deposits as $dep_val) {
                                                            $fee_paid += (float) $dep_val->amount;
                                                            $fee_discount += (float) (isset($dep_val->amount_discount) ? $dep_val->amount_discount : 0);
                                                            $fee_fine += (float) (isset($dep_val->amount_fine) ? $dep_val->amount_fine : 0);
                                                        }
                                                    }
                                                }
                                                $feetype_balance = $feeList->amount - ($fee_paid + $fee_discount);
                                                ?>
                                                <tr class="dark-gray">
                                                    <td><?php echo $feeList->name; ?></td>
                                                    <td><?php echo $feeList->code; ?></td>
                                                    <td>
                                                        <?php
                                                        if ($feeList->due_date && $feeList->due_date != "0000-00-00") {
                                                            echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($feeList->due_date));
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($feetype_balance <= 0 && $fee_paid > 0) {
                                                            echo $this->lang->line('paid');
                                                        } else if (!empty($feeList->amount_detail)) {
                                                            echo $this->lang->line('partial');
                                                        } else {
                                                            echo $this->lang->line('unpaid');
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="text text-right"><?php echo $currency_symbol . amountFormat($feeList->amount); ?></td>
                                                    <td class="text text-center"></td>
                                                    <td class="text text-center"></td>
                                                    <td></td>
                                                    <td class="text text-right"><?php echo $currency_symbol . amountFormat($fee_paid); ?></td>
                                                    <td class="text text-right"><?php echo $currency_symbol . amountFormat($fee_fine); ?></td>
                                                    <td class="text text-right"><?php echo $currency_symbol . amountFormat($fee_discount); ?></td>
                                                    <td class="text text-right">
                                                        <?php echo ($feetype_balance > 0) ? $currency_symbol . amountFormat($feetype_balance) : $currency_symbol . '0.00'; ?>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <?php
                                                if (!empty($feeList->amount_detail)) {
                                                    $fee_deposits = json_decode($feeList->amount_detail);
                                                    if (is_object($fee_deposits) || is_array($fee_deposits)) {
                                                        foreach ($fee_deposits as $dep_key => $dep_val) {
                                                ?>
                                                <tr class="white-td">
                                                    <td colspan="5" class="text-right"><img src="<?php echo base_url(); ?>backend/images/table-arrow.png" alt="" /></td>
                                                    <td class="text text-center"><?php echo $feeList->student_fees_deposite_id . "/" . $dep_val->inv_no; ?></td>
                                                    <td class="text text-center"><?php echo $this->lang->line(strtolower($dep_val->payment_mode)); ?></td>
                                                    <td class="text text-center"><?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($dep_val->date)); ?></td>
                                                    <td class="text text-right"><?php echo $currency_symbol . amountFormat($dep_val->amount); ?></td>
                                                    <td class="text text-right"><?php echo $currency_symbol . amountFormat($dep_val->amount_fine); ?></td>
                                                    <td class="text text-right"><?php echo $currency_symbol . amountFormat($dep_val->amount_discount); ?></td>
                                                    <td></td>
                                                </tr>
                                                <?php
                                                        }
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="row header">
                            <div class="col-sm-12">
                                <?php echo $this->setting_model->get_receiptfooter(); ?>
                            </div>
                        </div>  
                    </div>
                </div>
            <?php } ?>
            <?php if ($is_dual) { ?></div><?php } ?>
        </div>
        <div class="clearfix"></div>
        <footer></footer>
    </body>
</html>
