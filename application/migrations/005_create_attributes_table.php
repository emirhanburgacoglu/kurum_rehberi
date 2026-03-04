<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_attributes_table extends CI_Migration
{

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array('type' => 'INT', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'attribute_name' => array('type' => 'VARCHAR', 'constraint' => '100', 'null' => FALSE),
            'icon' => array('type' => 'VARCHAR', 'constraint' => '100', 'null' => TRUE),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('attributes', TRUE);
    }

    public function down()
    {
        $this->dbforge->drop_table('attributes', TRUE);
    }
}
