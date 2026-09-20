<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Sync_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function sync_local_to_server()
    {
        $server_db = $this->load->database('server_db', TRUE);

        if (!$server_db->conn_id) {
            return array(
                'status'  => false,
                'message' => 'Unable to connect to the online server database. Please check application/config/database.php settings.'
            );
        }

        $tables = $this->db->list_tables();
        if (empty($tables)) {
            return array(
                'status'  => false,
                'message' => 'No local database tables found to sync.'
            );
        }

        $server_db->query('SET FOREIGN_KEY_CHECKS = 0;');

        $synced_tables = 0;
        foreach ($tables as $table) {
            $query = $this->db->get($table);
            if ($query && $query->num_rows() > 0) {
                $rows = $query->result_array();
                $server_db->truncate($table);
                
                $chunks = array_chunk($rows, 100);
                foreach ($chunks as $chunk) {
                    $server_db->insert_batch($table, $chunk);
                }
            } else {
                $server_db->truncate($table);
            }
            $synced_tables++;
        }

        $server_db->query('SET FOREIGN_KEY_CHECKS = 1;');

        return array(
            'status'  => true,
            'message' => 'Database successfully synced to online server! Total tables synced: ' . $synced_tables
        );
    }
}
