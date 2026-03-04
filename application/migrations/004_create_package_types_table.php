<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_package_types_table extends CI_Migration
{

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array('type' => 'INT', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'name' => array('type' => 'VARCHAR', 'constraint' => '100', 'unique' => TRUE, 'null' => FALSE),
            'description' => array('type' => 'TEXT', 'null' => TRUE),
            'price' => array('type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00),
            'duration_days' => array('type' => 'SMALLINT', 'unsigned' => TRUE, 'null' => FALSE),
            'features' => array('type' => 'JSON', 'null' => TRUE),
            'max_images' => array('type' => 'TINYINT', 'unsigned' => TRUE, 'default' => 5),
            'max_kurum' => array('type' => 'TINYINT', 'unsigned' => TRUE, 'default' => 1),
            'status' => array('type' => 'TINYINT', 'default' => 1),
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('package_types', TRUE);
    }

    public function down()
    {
        $this->dbforge->drop_table('package_types', TRUE);
    }
}
