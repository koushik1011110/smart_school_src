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
                                        <img class="receipt-header-img" src="<?php echo $this->media_storage->getImageURL('/uploads/print_headerfooter/student_receipt/'.$this->setting_model->get_receiptheader());?>" style="height: 100px; width: 100%;">
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
                                            <?php echo $this->lang->line('father_name'); ?>: <?php echo $student['father_name']; ?><br>
                                            <?php echo $this->lang->line('class'); ?>: <?php echo $feeList->class . " (" . $feeList->section . ")"; ?>
                                        </address>
                                    </div>
                                    <div class="col-xs-6 text-right">
                                        <br/>
                                        <address>
                                            <strong>Date: <?php
                                                $date = date('d-m-Y');
                                                echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($date));
                                            ?></strong><br/>
                                            <strong><?php echo $this->lang->line('invoice_no'); ?> / <?php echo $this->lang->line('payment_id'); ?>: <?php echo $feeList->id . "/" . $sub_invoice_id; ?></strong><br/>
                                            <strong><?php echo $this->lang->line('collected_by'); ?>:
                                                <?php  
                                                if (isJSON($feeList->amount_detail)) {
                                                    $fee = json_decode($feeList->amount_detail);
                                                    $record = $fee->{$sub_invoice_id};
                                                    if (!empty($record->received_by)) {
                                                        echo $record->collected_by;
                                                    }
                                                }
                                                ?>                    
                                            </strong>
                                        </address>
                                    </div>
                                </div>
                                <hr style="margin-top: 0px; margin-bottom: 0px;" />
                                <div class="row">
                                    <?php if (!empty($feeList)) { ?>
                                        <table class="table table-striped table-responsive" style="font-size: 8pt;">
                                            <thead>
                                                <tr>
                                                    <th><?php echo $this->lang->line('date'); ?></th>
                                                    <th><?php echo $this->lang->line('fees_group'); ?></th>
                                                    <th><?php echo $this->lang->line('fees_code'); ?></th>
                                                    <th><?php echo $this->lang->line('mode'); ?></th>
                                                    <th class="text text-right"><?php echo $this->lang->line('amount'); ?></th>
                                                    <th class="text text-right"><?php echo $this->lang->line('discount'); ?></th>
                                                    <th class="text text-right"><?php echo $this->lang->line('fine'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $fee = json_decode($feeList->amount_detail);
                                                $record = $fee->{$sub_invoice_id};
                                                ?>
                                                <tr>
                                                    <td>
                                                        <?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($record->date)); ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($feeList->is_system) {
                                                            echo $this->lang->line($feeList->name) . " (" . $this->lang->line($feeList->type) . ")";
                                                        } else {
                                                            echo $feeList->name . " (" . $feeList->type . ")";
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($feeList->is_system) {
                                                            echo $this->lang->line($feeList->code);
                                                        } else {
                                                            echo $feeList->code;
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $this->lang->line(strtolower($record->payment_mode)); ?>
                                                    </td>
                                                    <td class="text text-right">
                                                        <?php echo $currency_symbol . amountFormat($record->amount); ?>
                                                    </td>
                                                    <td class="text text-right">
                                                        <?php echo $currency_symbol . amountFormat($record->amount_discount); ?>
                                                    </td>
                                                    <td class="text text-right">
                                                        <?php echo $currency_symbol . amountFormat($record->amount_fine); ?>
                                                    </td>
                                                </tr>
                                                <tr class="success">
                                                    <td align="left"></td>
                                                    <td align="left"></td>
                                                    <td align="left"></td>
                                                    <td align="left" class="text text-left">
                                                        <b><?php echo $this->lang->line('grand_total'); ?></b>
                                                    </td>
                                                    <td class="text text-right">
                                                        <b><?php echo $currency_symbol . amountFormat($record->amount); ?></b>
                                                    </td>
                                                    <td class="text text-right">
                                                        <b><?php echo $currency_symbol . amountFormat($record->amount_discount); ?></b>
                                                    </td>
                                                    <td class="text text-right">
                                                        <b><?php echo $currency_symbol . amountFormat($record->amount_fine); ?></b>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    <?php } ?>
                                </div>
                                <div class="row header">
                                    <div class="col-sm-12">
                                        <?php echo $this->setting_model->get_receiptfooter(); ?>
                                    </div>
                                </div>
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