<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_category_attributes_table extends CI_Migration
{

    public function up()
    {
        $this->dbforge->add_field(array(
            'category_id' => array('type' => 'INT', 'unsigned' => TRUE),
            'attribute_id' => array('type' => 'INT', 'unsigned' => TRUE),
        ));
        // Composite Primary Key
        $this->dbforge->add_key(array('category_id', 'attribute_id'), TRUE);
        $this->dbforge->create_table('category_attributes', TRUE);

        $this->db->query('ALTER TABLE category_attributes ADD CONSTRAINT fk_cat_attr_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE category_attributes ADD CONSTRAINT fk_cat_attr_attribute FOREIGN KEY (attribute_id) REFERENCES attributes(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $this->dbforge->drop_table('category_attributes', TRUE);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
    }
}
