<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_id_to_institution_attributes_table extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('institution_attributes')) {
            $this->dbforge->add_field(array(
                'id' => array('type' => 'BIGINT', 'unsigned' => TRUE, 'auto_increment' => TRUE),
                'institution_id' => array('type' => 'BIGINT', 'unsigned' => TRUE, 'null' => FALSE),
                'attribute_id' => array('type' => 'INT', 'unsigned' => TRUE, 'null' => FALSE)
            ));
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('institution_attributes', TRUE);
        } else {
            $has_id = $this->db->query("SHOW COLUMNS FROM institution_attributes LIKE 'id'")->num_rows() > 0;
            if (!$has_id) {
                $this->db->query('ALTER TABLE institution_attributes ADD COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
            }
        }

        $uniq_exists = $this->db->query("SHOW INDEX FROM institution_attributes WHERE Key_name = 'uniq_institution_attribute'")->num_rows() > 0;
        if (!$uniq_exists) {
            $this->db->query('ALTER TABLE institution_attributes ADD UNIQUE KEY uniq_institution_attribute (institution_id, attribute_id)');
        }

        $fk_inst_exists = $this->db->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'institution_attributes' AND CONSTRAINT_TYPE = 'FOREIGN KEY' AND CONSTRAINT_NAME = 'fk_ia_inst'")->num_rows() > 0;
        if (!$fk_inst_exists) {
            $this->db->query('ALTER TABLE institution_attributes ADD CONSTRAINT fk_ia_inst FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE ON UPDATE CASCADE');
        }

        $fk_attr_exists = $this->db->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'institution_attributes' AND CONSTRAINT_TYPE = 'FOREIGN KEY' AND CONSTRAINT_NAME = 'fk_ia_attr'")->num_rows() > 0;
        if (!$fk_attr_exists) {
            $this->db->query('ALTER TABLE institution_attributes ADD CONSTRAINT fk_ia_attr FOREIGN KEY (attribute_id) REFERENCES attributes(id) ON DELETE CASCADE ON UPDATE CASCADE');
        }
    }

    public function down()
    {
        if (!$this->db->table_exists('institution_attributes')) {
            return;
        }

        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->dbforge->drop_table('institution_attributes', TRUE);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}
