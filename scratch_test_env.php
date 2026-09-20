<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
ob_start();
require_once('index.php');
ob_end_clean();

$CI =& get_instance();
$CI->load->model('setting_model');
$setting = $CI->setting_model->getSetting();

echo "Database host: " . $CI->db->hostname . "\n";
echo "Database db: " . $CI->db->database . "\n";
echo "is_duplicate_fees_invoice: " . $setting->is_duplicate_fees_invoice . "\n";
