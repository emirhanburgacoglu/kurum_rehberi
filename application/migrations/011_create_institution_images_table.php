<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_institution_images_table extends CI_Migration
{

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array('type' => 'BIGINT', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'institution_id' => array('type' => 'BIGINT', 'unsigned' => TRUE),
            'image_path' => array('type' => 'VARCHAR', 'constraint' => '255', 'null' => FALSE),
            'is_main' => array('type' => 'TINYINT', 'default' => 0),
            'sort_order' => array('type' => 'TINYINT', 'default' => 0),
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('institution_images', TRUE);

        $this->db->query('ALTER TABLE institution_images ADD CONSTRAINT fk_images_inst FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $this->dbforge->drop_table('institution_images', TRUE);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
    }
}
