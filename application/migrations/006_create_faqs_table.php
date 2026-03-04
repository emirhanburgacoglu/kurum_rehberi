<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_faqs_table extends CI_Migration
{

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array('type' => 'INT', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'question' => array('type' => 'VARCHAR', 'constraint' => '500', 'null' => FALSE),
            'default_answer' => array('type' => 'TEXT', 'null' => TRUE),
            'attribute_id' => array('type' => 'INT', 'unsigned' => TRUE, 'null' => TRUE),
            'status' => array('type' => 'TINYINT', 'default' => 1),
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('faqs', TRUE);

        // CI3'te Foreign Key ekleme:
        $this->db->query('ALTER TABLE faqs ADD CONSTRAINT fk_faqs_attribute FOREIGN KEY (attribute_id) REFERENCES attributes(id) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        $this->dbforge->drop_table('faqs', TRUE);
    }
}
