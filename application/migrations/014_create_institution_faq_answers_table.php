<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_institution_faq_answers_table extends CI_Migration
{

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array('type' => 'INT', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'faq_id' => array('type' => 'INT', 'unsigned' => TRUE),
            'institution_id' => array('type' => 'BIGINT', 'unsigned' => TRUE),
            'approved_answer' => array('type' => 'TEXT', 'null' => TRUE),
            'pending_answer' => array('type' => 'TEXT', 'null' => TRUE),
            'approval_status' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 0),
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ));
        $this->dbforge->add_key('id', TRUE);

        $this->dbforge->create_table('institution_faq_answers', TRUE);

        // Bir kurum bir soruya sadece bir kez cevap verebilir (Unique Key)
        $this->db->query('ALTER TABLE institution_faq_answers ADD UNIQUE KEY uk_inst_faq (institution_id, faq_id)');

        // Foreign Keys
        $this->db->query('ALTER TABLE institution_faq_answers ADD CONSTRAINT fk_faq_ans_faq FOREIGN KEY (faq_id) REFERENCES faqs(id) ON DELETE CASCADE ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE institution_faq_answers ADD CONSTRAINT fk_faq_ans_inst FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $this->dbforge->drop_table('institution_faq_answers', TRUE);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
    }
}
