<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_institutions_table extends CI_Migration
{

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array('type' => 'BIGINT', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'user_id' => array('type' => 'BIGINT', 'unsigned' => TRUE, 'null' => TRUE),
            'sub_category_id' => array('type' => 'INT', 'unsigned' => TRUE),
            'city_id' => array('type' => 'TINYINT', 'unsigned' => TRUE),
            'district_id' => array('type' => 'INT', 'unsigned' => TRUE),
            'package_type_id' => array('type' => 'INT', 'unsigned' => TRUE, 'null' => TRUE),
            'name' => array('type' => 'VARCHAR', 'constraint' => '200', 'null' => FALSE),
            'slug' => array('type' => 'VARCHAR', 'constraint' => '220', 'unique' => TRUE, 'null' => FALSE),
            'address' => array('type' => 'TEXT', 'null' => TRUE),
            'package_starts_at' => array('type' => 'TIMESTAMP', 'null' => TRUE),
            'package_expires_at' => array('type' => 'TIMESTAMP', 'null' => TRUE),
            'landline_phone' => array('type' => 'TEXT', 'null' => TRUE), // JSON yerine TEXT
            'phone' => array('type' => 'TEXT', 'null' => TRUE),          // JSON yerine TEXT
            'whatsapp' => array('type' => 'TEXT', 'null' => TRUE),       // JSON yerine TEXT
            'email' => array('type' => 'TEXT', 'null' => TRUE),          // JSON yerine TEXT
            'website' => array('type' => 'VARCHAR', 'constraint' => '255', 'null' => TRUE),
            'description' => array('type' => 'LONGTEXT', 'null' => TRUE),
            'maps_url' => array('type' => 'VARCHAR', 'constraint' => '500', 'null' => TRUE),
            'latitude' => array('type' => 'DECIMAL', 'constraint' => '10,7', 'null' => TRUE),
            'longitude' => array('type' => 'DECIMAL', 'constraint' => '10,7', 'null' => TRUE),
            'price_min' => array('type' => 'INT', 'unsigned' => TRUE, 'null' => TRUE),
            'price_max' => array('type' => 'INT', 'unsigned' => TRUE, 'null' => TRUE),
            'price_period' => array('type' => 'ENUM("monthly","yearly")', 'default' => 'monthly', 'null' => TRUE),
            'status' => array('type' => 'TINYINT', 'default' => 0),
            'address_view_count' => array('type' => 'INT', 'unsigned' => TRUE, 'default' => 0),
            'view_count' => array('type' => 'INT', 'unsigned' => TRUE, 'default' => 0),
            'phone_view_count' => array('type' => 'INT', 'unsigned' => TRUE, 'default' => 0),
            'website_view_count' => array('type' => 'INT', 'unsigned' => TRUE, 'default' => 0),
            'mail_view_count' => array('type' => 'INT', 'unsigned' => TRUE, 'default' => 0),
            'wp_view_count' => array('type' => 'INT', 'unsigned' => TRUE, 'default' => 0),
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('institutions', TRUE);

        // Foreign Keys
        $this->db->query('ALTER TABLE institutions ADD CONSTRAINT fk_inst_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL');
        $this->db->query('ALTER TABLE institutions ADD CONSTRAINT fk_inst_subcat FOREIGN KEY (sub_category_id) REFERENCES sub_categories(id) ON DELETE RESTRICT ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE institutions ADD CONSTRAINT fk_inst_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE RESTRICT ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE institutions ADD CONSTRAINT fk_inst_dist FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE RESTRICT ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE institutions ADD CONSTRAINT fk_inst_pkg FOREIGN KEY (package_type_id) REFERENCES package_types(id) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $this->dbforge->drop_table('institutions', TRUE);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
    }
}
