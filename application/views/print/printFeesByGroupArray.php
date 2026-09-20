<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat(); ?>
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
            if (!function_exists('is_month_feetype_label')) {
                function is_month_feetype_label($str) {
                    $month_pattern = '/\b(jan|january|feb|february|mar|march|apr|april|may|jun|june|jul|july|aug|august|sep|sept|september|oct|october|nov|november|dec|december)\b/i';
                    return (bool) preg_match($month_pattern, trim($str));
                }
            }

            $invoice_numbers = array();
            $collected_by_names = array();
            $grouped_fees = array();

            if (!empty($feearray)) {
                foreach ($feearray as $feeList) {
                    $is_transport = (isset($feeList->fee_category) && $feeList->fee_category == "transport");

                    $fee_amount = 0;
                    if ($is_transport) {
                        $fee_amount = (float) $feeList->fees;
                        $type_label = $feeList->month;
                        $group_label = $this->lang->line("transport_fees");
                        $group_key = 'transport_months';
                    } else {
                        $fee_amount = $feeList->is_system ? (float) $feeList->student_fees_master_amount : (float) $feeList->amount;
                        $type_label = $feeList->type;
                        $group_label = $feeList->name;

                        $fsg_id = isset($feeList->fee_session_group_id) ? $feeList->fee_session_group_id : (isset($feeList->fee_groups_id) ? $feeList->fee_groups_id : '0');
                        if (is_month_feetype_label($type_label)) {
                            // Consolidate all months of this fee group into a single row
                            $group_key = 'group_' . $fsg_id . '_months';
                        } else {
                            $ft_id = isset($feeList->feetype_id) ? $feeList->feetype_id : $type_label;
                            $group_key = 'group_' . $fsg_id . '_ft_' . $ft_id;
                        }
                    }

                    $fee_discount = 0;
                    $fee_paid = 0;
                    $fee_fine = 0;
                    $dep_payment_ids = array();
                    $dep_modes = array();

                    if (!empty($feeList->amount_detail) && $feeList->amount_detail != '0') {
                        $deposits = json_decode($feeList->amount_detail);
                        if (is_object($deposits) || is_array($deposits)) {
                            foreach ($deposits as $dep) {
                                $fee_paid += (float) $dep->amount;
                                $fee_fine += (float) (isset($dep->amount_fine) ? $dep->amount_fine : 0);
                                $fee_discount += (float) (isset($dep->amount_discount) ? $dep->amount_discount : 0);

                                if (!empty($feeList->student_fees_deposite_id) && !empty($dep->inv_no)) {
                                    $inv_str = $feeList->student_fees_deposite_id . "/" . $dep->inv_no;
                                    $dep_payment_ids[] = $inv_str;
                                    $invoice_numbers[] = $inv_str;
                                }
                                if (!empty($dep->payment_mode)) {
                                    $dep_modes[] = $this->lang->line(strtolower($dep->payment_mode)) ?: $dep->payment_mode;
                                }
                                if (!empty($dep->collected_by)) {
                                    $collected_by_names[] = $dep->collected_by;
                                }
                            }
                        }
                    }

                    $feetype_balance = $fee_amount - ($fee_paid + $fee_discount);

                    if (!isset($grouped_fees[$group_key])) {
                        $grouped_fees[$group_key] = array(
                            'group_name'   => $group_label,
                            'types'        => array($type_label),
                            'amount'       => $fee_amount,
                            'paid'         => $fee_paid,
                            'fine'         => $fee_fine,
                            'discount'     => $fee_discount,
                            'balance'      => $feetype_balance,
                            'payment_ids'  => $dep_payment_ids,
                            'modes'        => $dep_modes,
                        );
                    } else {
                        $grouped_fees[$group_key]['types'][] = $type_label;
                        $grouped_fees[$group_key]['amount'] += $fee_amount;
                        $grouped_fees[$group_key]['paid'] += $fee_paid;
                        $grouped_fees[$group_key]['fine'] += $fee_fine;
                        $grouped_fees[$group_key]['discount'] += $fee_discount;
                        $grouped_fees[$group_key]['balance'] += $feetype_balance;
                        $grouped_fees[$group_key]['payment_ids'] = array_merge($grouped_fees[$group_key]['payment_ids'], $dep_payment_ids);
                        $grouped_fees[$group_key]['modes'] = array_merge($grouped_fees[$group_key]['modes'], $dep_modes);
                    }
                }
                $invoice_numbers = array_unique($invoice_numbers);
                $collected_by_names = array_unique($collected_by_names);
            }
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
                                            <strong><?php echo $this->customlib->getFullName($feearray[0]->firstname, $feearray[0]->middlename, $feearray[0]->lastname, $sch_setting->middlename, $sch_setting->lastname); ?></strong><?php echo " (" . $feearray[0]->admission_no . ")"; ?> <br>
                                            <?php echo $this->lang->line('father_name'); ?>: <?php echo $feearray[0]->father_name; ?><br>
                                            <?php echo $this->lang->line('class'); ?>: <?php echo $feearray[0]->class . " (" . $feearray[0]->section . ")"; ?>
                                        </address>
                                    </div>
                                    <div class="col-xs-6 text-right">
                                        <br/>
                                        <address>
                                            <strong>Date : <?php
                                                $date = date('d-m-Y');
                                                echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($date));
                                                ?></strong><br/>
                                            <?php if (!empty($invoice_numbers)) { ?>
                                                <strong><?php echo $this->lang->line('invoice_no'); ?> / <?php echo $this->lang->line('payment_id'); ?>: <?php echo implode(', ', $invoice_numbers); ?></strong><br/>
                                            <?php } ?>
                                            <?php if (!empty($collected_by_names)) { ?>
                                                <strong><?php echo $this->lang->line('collected_by'); ?>: <?php echo implode(', ', $collected_by_names); ?></strong><br/>
                                            <?php } ?>
                                        </address>                               
                                    </div>
                                </div>
                                <hr style="margin-top: 0px; margin-bottom: 0px;" />
                                <div class="row">
                                    <?php if (!empty($grouped_fees)) { ?>
                                        <table class="table table-striped table-responsive" style="font-size: 8pt;">
                                            <thead>
                                                <th><?php echo $this->lang->line('fees_group'); ?></th>
                                                <th class=""><?php echo $this->lang->line('status'); ?></th>
                                                <th class="text text-right"><?php echo $this->lang->line('amount'); ?></th>
                                                <th class="text text-center"><?php echo $this->lang->line('payment_id'); ?></th>
                                                <th class="text text-center"><?php echo $this->lang->line('mode'); ?></th>
                                                <th class="text text-right"><?php echo $this->lang->line('paid'); ?></th>
                                                <th class="text text-right"><?php echo $this->lang->line('fine'); ?></th>
                                                <th class="text text-right"><?php echo $this->lang->line('balance'); ?></th>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $grand_total_amount = 0;
                                                $grand_total_paid = 0;
                                                $grand_total_fine = 0;
                                                $grand_total_discount = 0;
                                                $grand_total_balance = 0;

                                                foreach ($grouped_fees as $g_key => $g_val) {
                                                    $grand_total_amount   += $g_val['amount'];
                                                    $grand_total_paid     += $g_val['paid'];
                                                    $grand_total_fine     += $g_val['fine'];
                                                    $grand_total_discount += $g_val['discount'];
                                                    $grand_total_balance  += $g_val['balance'];

                                                    $unique_types = array_unique($g_val['types']);
                                                    $types_display = implode(', ', $unique_types);
                                                    $fee_title = $g_val['group_name'] . " (" . $types_display . ")";

                                                    $row_payment_ids = implode(', ', array_unique($g_val['payment_ids']));
                                                    $row_modes = implode(', ', array_unique($g_val['modes']));

                                                    if ($g_val['balance'] <= 0 && $g_val['paid'] > 0) {
                                                        $status_text = $this->lang->line('paid');
                                                    } else if ($g_val['paid'] > 0) {
                                                        $status_text = $this->lang->line('partial');
                                                    } else {
                                                        $status_text = $this->lang->line('unpaid');
                                                    }
                                                ?>
                                                <tr class="dark-gray">
                                                    <td><?php echo $fee_title; ?></td>
                                                    <td class=""><?php echo $status_text; ?></td>
                                                    <td class="text text-right"><?php echo $currency_symbol . amountFormat($g_val['amount']); ?></td>
                                                    <td class="text text-center"><?php echo $row_payment_ids; ?></td>
                                                    <td class="text text-center"><?php echo $row_modes; ?></td>
                                                    <td class="text text-right"><?php echo $currency_symbol . amountFormat($g_val['paid']); ?></td>
                                                    <td class="text text-right"><?php echo $currency_symbol . amountFormat($g_val['fine']); ?></td>
                                                    <td class="text text-right">
                                                        <?php echo ($g_val['balance'] > 0) ? $currency_symbol . amountFormat($g_val['balance']) : $currency_symbol . '0.00'; ?>
                                                    </td>
                                                </tr>
                                                <?php } ?>
                                                <tr class="success">
                                                    <td align="left"></td>
                                                    <td align="left" class="text text-left">
                                                        <b><?php echo $this->lang->line('grand_total'); ?></b>
                                                    </td>
                                                    <td class="text text-right">
                                                        <b><?php echo $currency_symbol . amountFormat($grand_total_amount); ?></b>
                                                    </td>
                                                    <td class="text text-center"></td>
                                                    <td class="text text-center"></td>
                                                    <td class="text text-right">
                                                        <b><?php echo $currency_symbol . amountFormat($grand_total_paid); ?></b>
                                                    </td>
                                                    <td class="text text-right">
                                                        <b><?php echo $currency_symbol . amountFormat($grand_total_fine); ?></b>
                                                    </td>
                                                    <td class="text text-right">
                                                        <b><?php echo $currency_symbol . amountFormat($grand_total_balance); ?></b>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    <?php } else { ?>
                                        <div class="text-danger text-center" style="padding: 20px;">
                                            <?php echo $this->lang->line('no_transaction_found'); ?>
                                        </div>
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
