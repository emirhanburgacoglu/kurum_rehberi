<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_cities_table extends CI_Migration
{

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array('type' => 'TINYINT', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'city_name' => array('type' => 'VARCHAR', 'constraint' => '50', 'null' => FALSE),
            'slug' => array('type' => 'VARCHAR', 'constraint' => '60', 'unique' => TRUE, 'null' => FALSE),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('cities', TRUE);
    }

    public function down()
    {
        $this->dbforge->drop_table('cities', TRUE);
    }
}
