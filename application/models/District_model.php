<?php
defined('BASEPATH') or exit('No direct script access allowed');

class District_model extends CI_Model
{
    protected $table = 'districts';

    public function find($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->limit(1)
            ->get($this->table)
            ->row_array();
    }

    public function find_by_city_and_slug($cityId, $slug)
    {
        return $this->db
            ->where('city_id', (int) $cityId)
            ->where('slug', trim((string) $slug))
            ->limit(1)
            ->get($this->table)
            ->row_array();
    }

    public function create($data)
    {
        $ok = $this->db->insert($this->table, $data);
        return $ok ? (int) $this->db->insert_id() : FALSE;
    }

    public function update($id, $data)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, $data);
    }

    public function delete($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->delete($this->table);
    }

    public function list($filters = array())
    {
        $this->db->from($this->table);

        if (!empty($filters['city_id']) && ctype_digit((string) $filters['city_id'])) {
            $this->db->where('city_id', (int) $filters['city_id']);
        }

        if (!empty($filters['q'])) {
            $q = trim((string) $filters['q']);
            $this->db->group_start()
                ->like('district_name', $q)
                ->or_like('slug', $q)
                ->group_end();
        }

        return $this->db
            ->order_by('district_name', 'ASC')
            ->get()
            ->result_array();
    }

    public function list_by_city($cityId)
    {
        return $this->db
            ->where('city_id', (int) $cityId)
            ->order_by('district_name', 'ASC')
            ->get($this->table)
            ->result_array();
    }

    public function exists_city_slug($cityId, $slug, $excludeId = NULL)
    {
        $this->db->from($this->table)
            ->where('city_id', (int) $cityId)
            ->where('slug', trim((string) $slug));

        if ($excludeId !== NULL) {
            $this->db->where('id <>', (int) $excludeId);
        }

        return $this->db->count_all_results() > 0;
    }
}

