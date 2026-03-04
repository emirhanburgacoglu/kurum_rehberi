<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_districts_table extends CI_Migration
{

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array('type' => 'INT', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'city_id' => array('type' => 'TINYINT', 'unsigned' => TRUE),
            'district_name' => array('type' => 'VARCHAR', 'constraint' => '80', 'null' => FALSE),
            'slug' => array('type' => 'VARCHAR', 'constraint' => '100', 'null' => FALSE),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('districts', TRUE);

        // Unique key: city_id + slug
        $this->db->query('ALTER TABLE districts ADD UNIQUE KEY uk_city_slug (city_id, slug)');
        $this->db->query('ALTER TABLE districts ADD CONSTRAINT fk_districts_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $this->dbforge->drop_table('districts', TRUE);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
    }
}
