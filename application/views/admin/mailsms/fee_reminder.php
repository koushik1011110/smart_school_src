<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-bullhorn"></i> <?php echo $this->lang->line('communicate'); ?>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if ($this->session->flashdata('msg')) { ?>
                    <?php echo $this->session->flashdata('msg');
                    $this->session->unset_userdata('msg'); ?>
                <?php } ?>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i>
                            <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <div class="box-body">
                        <form action="<?php echo site_url('admin/mailsms/fee_reminder'); ?>" method="post"
                            class="class_search_form">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label><small class="req">
                                            *</small>
                                        <select autofocus="" id="class_id" name="class_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php foreach ($classlist as $class) { ?>
                                                <option value="<?php echo $class['id']; ?>" <?php if (set_value('class_id', $class_id) == $class['id']) {
                                                       echo "selected=selected";
                                                   } ?>>
                                                    <?php echo $class['class']; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group" style="margin-top: 25px;">
                                        <button type="submit" class="btn btn-primary btn-sm pull-right" name="search"
                                            value="search">
                                            <i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if (isset($resultlist)) { ?>
                    <form action="<?php echo site_url('admin/mailsms/send_fee_reminder_sms'); ?>" method="post"
                        id="send_reminder_form">
                        <?php echo $this->customlib->getCSRF(); ?>
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-users"></i>
                                    <?php echo $this->lang->line('student_list'); ?></h3>
                                <div class="box-tools pull-right">
                                    <button type="submit" class="btn btn-success btn-sm" id="send_btn">
                                        <i class="fa fa-paper-plane"></i> Send SMS
                                    </button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="table-responsive mailbox-messages overflow-visible">
                                    <table class="table table-striped table-bordered table-hover example">
                                        <thead>
                                            <tr>
                                                <th width="40"><input type="checkbox" id="checkAll"></th>
                                                <th><?php echo $this->lang->line('admission_no'); ?></th>
                                                <th><?php echo $this->lang->line('student_name'); ?></th>
                                                <th><?php echo $this->lang->line('class'); ?>
                                                    (<?php echo $this->lang->line('section'); ?>)</th>
                                                <th><?php echo $this->lang->line('father_name'); ?> /
                                                    <?php echo $this->lang->line('guardian_name'); ?></th>
                                                <th><?php echo $this->lang->line('mobile_number'); ?></th>
                                                <th>Paid Fees</th>
                                                <th>Pending Fees</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($resultlist)) { ?>
                                                <tr>
                                                    <td colspan="8" class="text-center text-danger">No students found.</td>
                                                </tr>
                                            <?php } else { ?>
                                                <?php foreach ($resultlist as $student) {
                                                    $student_name = trim(($student['firstname'] ?? '') . ' ' . ($student['middlename'] ?? '') . ' ' . ($student['lastname'] ?? ''));
                                                    $mobile = !empty($student['mobileno']) ? $student['mobileno'] : $student['guardian_phone'];
                                                    $guardian = !empty($student['father_name']) ? $student['father_name'] : $student['guardian_name'];
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" name="student_session_id[]"
                                                                class="checkbox student_checkbox"
                                                                value="<?php echo $student['student_session_id']; ?>">
                                                        </td>
                                                        <td><?php echo $student['admission_no']; ?></td>
                                                        <td>
                                                            <a
                                                                href="<?php echo base_url(); ?>student/view/<?php echo $student['id']; ?>">
                                                                <?php echo $student_name; ?>
                                                            </a>
                                                        </td>
                                                        <td><?php echo $student['class'] . " (" . $student['section'] . ")"; ?></td>
                                                        <td><?php echo $guardian; ?></td>
                                                        <td><?php echo $mobile; ?></td>
                                                        <td><span
                                                                class="label label-success"><?php echo $currency_symbol . number_format($student['paid_fee'], 2); ?></span>
                                                        </td>
                                                        <td><span
                                                                class="label label-danger"><?php echo $currency_symbol . number_format($student['pending_fee'], 2); ?></span>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php if (!empty($resultlist)) { ?>
                                <div class="box-footer">
                                    <button type="submit" class="btn btn-success btn-sm pull-right" id="send_btn_footer">
                                        <i class="fa fa-paper-plane"></i> Send SMS
                                    </button>
                                </div>
                            <?php } ?>
                        </div>
                    </form>
                <?php } ?>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var class_id = '<?php echo set_value('class_id', $class_id ?? 0); ?>';
        var section_id = '<?php echo set_value('section_id', $section_id ?? 0); ?>';

        if (class_id) {
            getSectionByClass(class_id, section_id);
        }

        $(document).on('change', '#class_id', function (e) {
            $('#section_id').html("");
            var class_id = $(this).val();
            getSectionByClass(class_id, 0);
        });

        $("#checkAll").change(function () {
            $(".student_checkbox").prop('checked', $(this).prop("checked"));
        });

        $('.student_checkbox').change(function () {
            if ($('.student_checkbox:checked').length == $('.student_checkbox').length) {
                $('#checkAll').prop('checked', true);
            } else {
                $('#checkAll').prop('checked', false);
            }
        });
    });

    function getSectionByClass(class_id, section_id) {
        if (class_id != "") {
            $('#section_id').html("");
            var base_url = '<?php echo base_url(); ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                type: "GET",
                url: base_url + "sections/getByClass",
                data: { 'class_id': class_id },
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj) {
                        var sel = "";
                        if (section_id == obj.section_id) {
                            sel = "selected='selected'";
                        }
                        div_data += "<option value='" + obj.section_id + "' " + sel + ">" + obj.section + "</option>";
                    });
                    $('#section_id').append(div_data);
                }
            });
        }
    }
</script>