<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content-header"></section>
    <section class="content">
        <?php $this->load->view('financereports/_finance'); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-file-text-o"></i> Fee Type Collection Summary</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-default btn-xs" id="print_report" onclick="printDiv()">
                                <i class="fa fa-print"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <form action="<?php echo site_url('financereports/feetype_collection_summary'); ?>" method="post">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('search_duration'); ?></label>
                                        <select class="form-control" name="period">
                                            <option value="today" <?php echo (isset($search_type) && $search_type == 'today') ? 'selected' : ''; ?>>Today</option>
                                            <option value="last_week" <?php echo (isset($search_type) && $search_type == 'last_week') ? 'selected' : ''; ?>>Last Week</option>
                                            <option value="last_month" <?php echo (isset($search_type) && $search_type == 'last_month') ? 'selected' : ''; ?>>Last Month</option>
                                            <option value="last_year" <?php echo (isset($search_type) && $search_type == 'last_year') ? 'selected' : ''; ?>>Last Year</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3" style="padding-top:25px;">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                </div>
                                <div class="col-sm-6 text-right" style="padding-top:25px;">
                                    <?php if (isset($start_date) && isset($end_date)) { ?>
                                        <b><?php echo $this->lang->line('date'); ?>:</b>
                                        <?php echo $this->customlib->dateformat($start_date); ?> - <?php echo $this->customlib->dateformat($end_date); ?>
                                    <?php } ?>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="box-body" id="summary_report_block">
                        <div id="printhead" style="display:none;">
                            <center>
                                <?php if (isset($start_date) && isset($end_date)) { ?>
                                    <h4><b>Fee Type Collection Summary (<?php echo $this->customlib->dateformat($start_date); ?> - <?php echo $this->customlib->dateformat($end_date); ?>)</b></h4>
                                <?php } else { ?>
                                    <h4><b>Fee Type Collection Summary</b></h4>
                                <?php } ?>
                            </center>
                        </div>
                        <?php if (empty($report_data)) { ?>
                            <div class="alert alert-info"><?php echo $this->lang->line('no_record_found'); ?></div>
                        <?php } else { ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th><?php echo $this->lang->line('fee_type'); ?></th>
                                            <th class="text text-right">Cash (<?php echo $currency_symbol; ?>)</th>
                                            <th class="text text-right">UPI (<?php echo $currency_symbol; ?>)</th>
                                            <th class="text text-right">Total (<?php echo $currency_symbol; ?>)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $grand_total_cash = 0; ?>
                                        <?php $grand_total_upi = 0; ?>
                                        <?php $grand_total = 0; ?>
                                        <?php foreach ($report_data as $row) { ?>
                                            <?php $grand_total_cash += $row['cash_amount']; ?>
                                            <?php $grand_total_upi += $row['upi_amount']; ?>
                                            <?php $grand_total += $row['total_amount']; ?>
                                            <tr>
                                                <td><?php echo $row['fee_type']; ?></td>
                                                <td class="text text-right"><?php echo amountFormat($row['cash_amount']); ?></td>
                                                <td class="text text-right"><?php echo amountFormat($row['upi_amount']); ?></td>
                                                <td class="text text-right"><?php echo amountFormat($row['total_amount']); ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th class="text text-right"><?php echo $this->lang->line('grand_total'); ?></th>
                                            <th class="text text-right"><?php echo amountFormat($grand_total_cash); ?></th>
                                            <th class="text text-right"><?php echo amountFormat($grand_total_upi); ?></th>
                                            <th class="text text-right"><?php echo amountFormat($grand_total); ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function printDiv() {
    document.getElementById("printhead").style.display = "block";
    document.getElementById("print_report").style.display = "none";
    var divElements = document.getElementById('summary_report_block').innerHTML;
    var oldPage = document.body.innerHTML;
    document.body.innerHTML = "<html><head><title>Fee Type Collection Summary</title></head><body>" + divElements + "</body>";
    window.print();
    document.body.innerHTML = oldPage;
    location.reload(true);
}
</script>
