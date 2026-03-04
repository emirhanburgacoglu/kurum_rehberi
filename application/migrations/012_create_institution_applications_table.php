<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_institution_applications_table extends CI_Migration
{

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array('type' => 'BIGINT', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'applicant_name' => array('type' => 'VARCHAR', 'constraint' => '100', 'null' => FALSE),
            'applicant_phone' => array('type' => 'VARCHAR', 'constraint' => '20', 'null' => FALSE),
            'applicant_email' => array('type' => 'VARCHAR', 'constraint' => '150', 'null' => FALSE),
            'category_id' => array('type' => 'INT', 'unsigned' => TRUE, 'null' => FALSE),
            'sub_category_id' => array('type' => 'INT', 'unsigned' => TRUE, 'null' => TRUE),
            'city_id' => array('type' => 'TINYINT', 'unsigned' => TRUE, 'null' => FALSE),
            'district_id' => array('type' => 'INT', 'unsigned' => TRUE, 'null' => FALSE),
            'message' => array('type' => 'TEXT', 'null' => TRUE),
            'role' => array('type' => 'TINYINT', 'null' => FALSE),
            'status' => array('type' => 'TINYINT', 'default' => 0),
            'institution_id' => array('type' => 'BIGINT', 'unsigned' => TRUE, 'null' => TRUE),
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('institution_applications', TRUE);

        $this->db->query('ALTER TABLE institution_applications ADD CONSTRAINT fk_app_cat FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE institution_applications ADD CONSTRAINT fk_app_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE RESTRICT ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE institution_applications ADD CONSTRAINT fk_app_dist FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE RESTRICT ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE institution_applications ADD CONSTRAINT fk_app_inst FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $this->dbforge->drop_table('institution_applications', TRUE);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
    }
}
