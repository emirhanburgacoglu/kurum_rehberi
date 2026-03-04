<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Migration $migration
 */
class Migrate extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Block web access. Run migrations only via CLI.
        if (!is_cli()) {
            show_404();
        }
    }

    public function index()
    {
        $this->load->library('migration');

        if ($this->migration->current() === FALSE) {
            show_error($this->migration->error_string());
            return;
        }

        echo "System and database are synchronized.\n";
    }
}
