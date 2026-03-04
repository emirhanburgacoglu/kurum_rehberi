<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_users_table extends CI_Migration
{

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array('type' => 'BIGINT', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'name' => array('type' => 'VARCHAR', 'constraint' => '100'),
            'email' => array('type' => 'VARCHAR', 'constraint' => '150', 'unique' => TRUE),
            'password' => array('type' => 'VARCHAR', 'constraint' => '255'),
            'phone' => array('type' => 'VARCHAR', 'constraint' => '20', 'null' => TRUE),
            'role' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 0),
            'email_verified_at' => array('type' => 'TIMESTAMP', 'null' => TRUE),
            'remember_token' => array('type' => 'VARCHAR', 'constraint' => '100', 'null' => TRUE),
            'status' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 0),
            'reset_token' => array('type' => 'VARCHAR', 'constraint' => '100', 'null' => TRUE),
            'reset_expires' => array('type' => 'TIMESTAMP', 'null' => TRUE),
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('users', TRUE);
    }

    public function down()
    {
        $this->dbforge->drop_table('users', TRUE);
    }
}
