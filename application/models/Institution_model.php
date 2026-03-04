<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Institution_model extends CI_Model
{
    protected $table = 'institutions';

    public function get_recent_active($limit = 10)
    {
        return $this->db
            ->where('status', 1)
            ->order_by('id', 'DESC')
            ->limit((int) $limit)
            ->get($this->table)
            ->result_array();
    }

    public function search($filters = array())
    {
        $this->db->from($this->table);
        $this->db->where('status', 1);

        if (!empty($filters['city_id']) && ctype_digit((string) $filters['city_id'])) {
            $this->db->where('city_id', (int) $filters['city_id']);
        }

        if (!empty($filters['sub_category_id']) && ctype_digit((string) $filters['sub_category_id'])) {
            $this->db->where('sub_category_id', (int) $filters['sub_category_id']);
        }

        if (!empty($filters['q'])) {
            $this->db->like('name', trim($filters['q']));
        }

        return $this->db
            ->order_by('id', 'DESC')
            ->get()
            ->result_array();
    }
    public function find_active_by_id($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->where('status', 1)
            ->limit(1)
            ->get($this->table)
            ->row_array();
    }
}
