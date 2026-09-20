import re

# 1. Update printFeesByGroup.php
with open('application/views/print/printFeesByGroup.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace Payment ID header with Invoice No / Payment ID
content = content.replace(
    "<th class=\"text text-center\"><?php echo $this->lang->line('payment_id'); ?></th>",
    "<th class=\"text text-center\"><?php echo $this->lang->line('invoice_no'); ?> / <?php echo $this->lang->line('payment_id'); ?></th>"
)

# In Office Copy address
addr_search = """                                    <strong><?php echo $this->lang->line('date') ; ?>: <?php
                                        $date = date('d-m-Y');

                                        echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($date));
                                        ?></strong><br/>"""

addr_replace = """                                    <strong><?php echo $this->lang->line('date') ; ?>: <?php
                                        $date = date('d-m-Y');

                                        echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($date));
                                        ?></strong><br/>
<?php
$inv_list = array();
if (!empty($feeList->amount_detail)) {
    $dep = json_decode($feeList->amount_detail);
    if (is_object($dep)) {
        foreach ($dep as $d_val) {
            $inv_list[] = $feeList->student_fees_deposite_id . "/" . $d_val->inv_no;
        }
    }
}
if (!empty($inv_list)) {
?>
                                    <strong><?php echo $this->lang->line('invoice_no'); ?> / <?php echo $this->lang->line('payment_id'); ?>: <?php echo implode(', ', array_unique($inv_list)); ?></strong><br/>
<?php } ?>"""

if addr_search in content:
    content = content.replace(addr_search, addr_replace)
else:
    # Try with \r\n normalized
    content = re.sub(
        r"<strong>\s*<\?php\s*echo\s*\$this->lang->line\('date'\)\s*;\s*\?>\s*:\s*<\?php[\s\S]*?dateyyyymmddTodateformat\(\$date\)\);\s*\?>\s*</strong>\s*<br\s*/>",
        lambda m: m.group(0) + """\n<?php
$inv_list = array();
if (!empty($feeList->amount_detail)) {
    $dep = json_decode($feeList->amount_detail);
    if (is_object($dep)) {
        foreach ($dep as $d_val) {
            $inv_list[] = $feeList->student_fees_deposite_id . "/" . $d_val->inv_no;
        }
    }
}
if (!empty($inv_list)) {
?>
                                    <strong><?php echo $this->lang->line('invoice_no'); ?> / <?php echo $this->lang->line('payment_id'); ?>: <?php echo implode(', ', array_unique($inv_list)); ?></strong><br/>
<?php } ?>""",
        content,
        count=1
    )

# For student copy and bank copy in printFeesByGroup.php
content = re.sub(
    r"(<strong>Date:\s*<\?php[\s\S]*?dateyyyymmddTodateformat\(\$date\)\);\s*\?>\s*</strong>\s*<br\s*/>)",
    r"""\1
<?php
if (!empty($inv_list)) {
?>
                                    <strong><?php echo $this->lang->line('invoice_no'); ?> / <?php echo $this->lang->line('payment_id'); ?>: <?php echo implode(', ', array_unique($inv_list)); ?></strong><br/>
<?php } ?>""",
    content
)

with open('application/views/print/printFeesByGroup.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated printFeesByGroup.php")
