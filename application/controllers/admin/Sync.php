<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Sync extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('sync_model');
    }

    public function sync_now()
    {
        $result = $this->sync_model->sync_local_to_server();
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }
}
