<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_institution_inquiries_table extends CI_Migration
{

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array('type' => 'BIGINT', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'institution_id' => array('type' => 'BIGINT', 'unsigned' => TRUE),
            'name' => array('type' => 'VARCHAR', 'constraint' => '100', 'null' => FALSE),
            'email' => array('type' => 'VARCHAR', 'constraint' => '150', 'null' => FALSE),
            'phone' => array('type' => 'VARCHAR', 'constraint' => '20', 'null' => TRUE),
            'status' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 0),
            'message' => array('type' => 'TEXT', 'null' => FALSE),
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('institution_inquiries', TRUE);

        $this->db->query('ALTER TABLE institution_inquiries ADD CONSTRAINT fk_inq_inst FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $this->dbforge->drop_table('institution_inquiries', TRUE);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
    }
}
