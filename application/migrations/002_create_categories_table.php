<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_categories_table extends CI_Migration
{

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array('type' => 'INT', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'category_name' => array('type' => 'VARCHAR', 'constraint' => '100', 'null' => FALSE),
            'slug' => array('type' => 'VARCHAR', 'constraint' => '120', 'unique' => TRUE, 'null' => FALSE),
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('categories', TRUE);
    }

    public function down()
    {
        $this->dbforge->drop_table('categories', TRUE);
    }
}
