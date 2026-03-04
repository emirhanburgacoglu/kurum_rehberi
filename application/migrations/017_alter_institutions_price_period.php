<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Alter_institutions_price_period extends CI_Migration
{

    public function up()
    {
        $this->db->query("ALTER TABLE institutions MODIFY price_period ENUM('monthly','yearly') NULL DEFAULT NULL");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE institutions MODIFY price_period ENUM('monthly','yearly') NOT NULL DEFAULT 'monthly'");
    }
}
