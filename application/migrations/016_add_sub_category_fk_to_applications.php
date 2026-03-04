<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_sub_category_fk_to_applications extends CI_Migration
{

    public function up()
    {
        $this->db->query(
            'ALTER TABLE institution_applications
             ADD CONSTRAINT fk_app_subcat
             FOREIGN KEY (sub_category_id) REFERENCES sub_categories(id)
             ON DELETE SET NULL ON UPDATE CASCADE'
        );
    }

    public function down()
    {
        $this->db->query('ALTER TABLE institution_applications DROP FOREIGN KEY fk_app_subcat');
    }
}
