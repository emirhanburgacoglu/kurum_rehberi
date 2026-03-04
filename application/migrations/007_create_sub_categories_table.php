<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_sub_categories_table extends CI_Migration
{

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array('type' => 'INT', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'category_id' => array('type' => 'INT', 'unsigned' => TRUE),
            'sub_category_name' => array('type' => 'VARCHAR', 'constraint' => '100', 'null' => FALSE),
            'slug' => array('type' => 'VARCHAR', 'constraint' => '120', 'unique' => TRUE, 'null' => FALSE),
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('sub_categories', TRUE);

        // Foreign Key
        $this->db->query('ALTER TABLE sub_categories ADD CONSTRAINT fk_sub_categories_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $this->dbforge->drop_table('sub_categories', TRUE);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
    }
}
