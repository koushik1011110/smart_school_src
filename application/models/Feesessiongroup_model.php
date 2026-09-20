<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Feesessiongroup_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    public function add($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $parentid                     = $this->group_exists($data['fee_groups_id']);
        $data['fee_session_group_id'] = $parentid;
        $this->db->insert('fee_groups_feetype', $data);
        $id        = $this->db->insert_id();
        $message   = INSERT_RECORD_CONSTANT . " On  fee groups feetype id " . $id;
        $action    = "Insert";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        //======================Code End==============================

        $this->db->trans_complete(); # Completing transaction
        /* Optional */

        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            //return $return_value;
        }
    }

    public function getFeesByGroupByStudent($student_session_id)
    {
        $this->db->select('fee_session_groups.*,fee_groups.name as `group_name`,IFNULL(student_fees_master.id,0) as `student_fees_master_id`');
        $this->db->from('fee_session_groups');
        $this->db->join('fee_groups', 'fee_groups.id = fee_session_groups.fee_groups_id');
        $this->db->join('student_fees_master', 'student_fees_master.student_session_id=' . $student_session_id . ' and student_fees_master.fee_session_group_id=fee_session_groups.id', 'LEFT');
        $this->db->where('fee_session_groups.session_id', $this->current_session);
        $this->db->where('fee_groups.is_system', 0);
        $this->db->order_by('student_fees_master_id', 'desc');
        $query  = $this->db->get();
        $result = $query->result();
        foreach ($result as $key => $value) {
            $value->feetypes = $this->getfeeTypeByGroup($value->id, $value->fee_groups_id);
        }
        return $result;
    }

    public function getFeesByGroup($id = null,$display_system=NULL)
    {
        $this->db->select('fee_session_groups.*,fee_groups.name as `group_name`,fee_groups.is_system');
        $this->db->from('fee_session_groups');
        $this->db->join('fee_groups', 'fee_groups.id = fee_session_groups.fee_groups_id');
        $this->db->where('fee_session_groups.session_id', $this->current_session);

         if ($display_system !== NULL) {
               $this->db->where('fee_groups.is_system', $display_system);
        }

     
        if ($id != null) {
            $this->db->where('fee_session_groups.id', $id);
        }
            $this->db->order_by('fee_groups.id', 'asc');
        $query = $this->db->get();
        $result = $query->result();
        foreach ($result as $key => $value) {
            $value->feetypes = $this->getfeeTypeByGroup($value->id, $value->fee_groups_id);
        }
        return $result;
    }

    public function getfeeTypeByGroup($fee_session_group_id, $id = null)
    {
        $this->db->select('fee_groups_feetype.*,feetype.type,feetype.code');
        $this->db->from('fee_groups_feetype');
        $this->db->join('feetype', 'feetype.id=fee_groups_feetype.feetype_id');
        $this->db->where('fee_groups_feetype.fee_groups_id', $id);
        $this->db->where('fee_groups_feetype.fee_session_group_id', $fee_session_group_id);
        $this->db->order_by('fee_groups_feetype.id', 'asc');
        $query = $this->db->get();
        return $query->result();
    }

    public function group_exists($fee_groups_id)
    {
        $this->db->where('fee_groups_id', $fee_groups_id);
        $this->db->where('session_id', $this->current_session);
        $query = $this->db->get('fee_session_groups');
        if ($query->num_rows() > 0) {
            return $query->row()->id;
        } else {
            $data = array('fee_groups_id' => $fee_groups_id, 'session_id' => $this->current_session);
            $this->db->insert('fee_session_groups', $data);
            return $this->db->insert_id();
        }
    }

    public function remove($id)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $sql = "delete fee_groups_feetype.* FROM fee_groups_feetype JOIN fee_session_groups ON fee_session_groups.id = fee_groups_feetype.fee_session_group_id WHERE fee_session_groups.id = ?";
        $this->db->query($sql, array($id));
        $this->db->where('id', $id);
        $this->db->delete('fee_session_groups');

        $message   = DELETE_RECORD_CONSTANT . " On fee session groups id " . $id;
        $action    = "Delete";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        //======================Code End==============================
        $this->db->trans_complete(); # Completing transaction
        /* Optional */
        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            //return $return_value;
        }
    }

    public function checkExists($data)
    {
        $this->db->where('fee_session_group_id', $data['fee_session_group_id']);
        $this->db->where('fee_groups_id', $data['fee_groups_id']);
        $this->db->where('feetype_id', $data['feetype_id']);
        $this->db->where('session_id', $this->current_session);
        $q = $this->db->get('fee_groups_feetype');

        if ($q->num_rows() > 0) {
            return $q->row()->id;
        } else {
            return false;
        }
    }

    public function valid_check_exists($str)
    {
        $fee_groups_id = $this->input->post('fee_groups_id');
        $feetype_id    = $this->input->post('feetype_id');
        $id            = $this->input->post('id');

        if (!isset($id)) {
            $id = 0;
        }

        if ($this->check_data_exists($fee_groups_id, $feetype_id, $id)) {
            $this->form_validation->set_message('check_exists', $this->lang->line('feegroup_combination_already_exists'));
            return false;
        } else {
            return true;
        }
    }

    public function check_data_exists($fee_groups_id, $feetype_id, $id)
    {
        $this->db->where('fee_groups_id', $fee_groups_id);
        $this->db->where('session_id', $this->current_session);
        $query = $this->db->get('fee_session_groups');

        if ($query->num_rows() > 0) {
            $fee_session_group_id = $query->row()->id;
            $this->db->where('fee_session_group_id', $fee_session_group_id);
            $this->db->where('fee_groups_id', $fee_groups_id);
            $this->db->where('feetype_id', $feetype_id);
            $this->db->where('id !=', $id);
            $query = $this->db->get('fee_groups_feetype');
            if ($query->num_rows() > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function copyToSession($source_session_id, $target_session_id)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);

        // Get all fee session groups from source session
        $this->db->where('session_id', $source_session_id);
        $source_groups = $this->db->get('fee_session_groups')->result();

        $group_mapping = array();

        foreach ($source_groups as $group) {
            // Insert new fee session group for target session
            $new_group_data = array(
                'fee_groups_id' => $group->fee_groups_id,
                'session_id' => $target_session_id,
                'is_active' => $group->is_active
            );
            $this->db->insert('fee_session_groups', $new_group_data);
            $new_group_id = $this->db->insert_id();
            $group_mapping[$group->id] = $new_group_id;
        }

        // Get all fee types from source session
        $this->db->where('session_id', $source_session_id);
        $source_feetypes = $this->db->get('fee_groups_feetype')->result();

        foreach ($source_feetypes as $feetype) {
            // Insert new fee type for target session
            $new_feetype_data = array(
                'fee_session_group_id' => $group_mapping[$feetype->fee_session_group_id],
                'fee_groups_id' => $feetype->fee_groups_id,
                'feetype_id' => $feetype->feetype_id,
                'session_id' => $target_session_id,
                'amount' => $feetype->amount,
                'fine_type' => $feetype->fine_type,
                'due_date' => $feetype->due_date,
                'fine_percentage' => $feetype->fine_percentage,
                'fine_amount' => $feetype->fine_amount,
                'is_active' => $feetype->is_active
            );
            $this->db->insert('fee_groups_feetype', $new_feetype_data);
        }

        $message = INSERT_RECORD_CONSTANT . " Copy fee master from session " . $source_session_id . " to session " . $target_session_id;
        $action = "Copy";
        $record_id = $target_session_id;
        $this->log($message, $record_id, $action);

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        } else {
            return true;
        }
    }

}
