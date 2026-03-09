<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sub_category_model extends CI_Model
{
    // DB: sub_categories tablosuyla eşleşir
    protected $table = 'sub_categories';

    /**
     * ID'ye göre alt kategori getirir.
     */
    public function find($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->limit(1)
            ->get($this->table)
            ->row_array();
    }

    /**
     * Slug'a (URL adı) göre alt kategori getirir.
     * DB'de slug UNIQUE olduğu için tek sonuç döner.
     */
    public function find_by_slug($slug)
    {
        return $this->db
            ->where('slug', trim((string) $slug))
            ->limit(1)
            ->get($this->table)
            ->row_array();
    }

    /**
     * Yeni alt kategori oluşturur.
     * ÖNEMLİ: $data içerisinde 'category_id' mutlaka gönderilmelidir.
     */
    public function create($data)
    {
        $ok = $this->db->insert($this->table, $data);
        return $ok ? (int) $this->db->insert_id() : FALSE;
    }

    /**
     * Alt kategoriyi günceller.
     */
    public function update($id, $data)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, $data);
    }

    /**
     * Alt kategoriyi siler. 
     * DB Notu: Ana kategori silinirse 'ON DELETE CASCADE' sayesinde 
     * buradaki bağlı kayıtlar da otomatik silinir.
     */
    public function delete($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->delete($this->table);
    }

    /**
     * Alt kategorileri listeler. 
     * Filtre ile belirli bir ana kategoriye (category_id) ait olanları süzebilir.
     */
    public function list($filters = array())
    {
        $this->db->from($this->table);

        // Belirli bir ana kategoriye göre filtreleme (Örn: Sadece "Özel Okullar"ın altındakiler)
        if (!empty($filters['category_id']) && ctype_digit((string) $filters['category_id'])) {
            $this->db->where('category_id', (int) $filters['category_id']);
        }

        // İsim veya slug üzerinden arama (Search box için)
        if (!empty($filters['q'])) {
            $q = trim((string) $filters['q']);
            $this->db->group_start()
                ->like('sub_category_name', $q)
                ->or_like('slug', $q)
                ->group_end();
        }

        return $this->db
            ->order_by('sub_category_name', 'ASC')
            ->get()
            ->result_array();
    }

    /**
     * Yardımcı Metot: Sadece belirli bir ana kategoriye ait alt kategorileri döner.
     */
    public function list_by_category($categoryId)
    {
        return $this->db
            ->where('category_id', (int) $categoryId)
            ->order_by('sub_category_name', 'ASC')
            ->get($this->table)
            ->result_array();
    }

    /**
     * Sayfalama (Pagination) için toplam kayıt sayısını döner.
     */
    public function count($filters = array())
    {
        $this->db->from($this->table);

        if (!empty($filters['category_id']) && ctype_digit((string) $filters['category_id'])) {
            $this->db->where('category_id', (int) $filters['category_id']);
        }

        if (!empty($filters['q'])) {
            $q = trim((string) $filters['q']);
            $this->db->group_start()
                ->like('sub_category_name', $q)
                ->or_like('slug', $q)
                ->group_end();
        }

        return (int) $this->db->count_all_results();
    }

    /**
     * Slug'ın benzersiz olup olmadığını kontrol eder.
     * DB'de slug UNIQUE olduğu için kayıt öncesi bu kontrol hata almanı önler.
     */
    public function exists_by_slug($slug, $excludeId = NULL)
    {
        $this->db->from($this->table)->where('slug', trim((string) $slug));

        if ($excludeId !== NULL) {
            $this->db->where('id <>', (int) $excludeId);
        }

        return $this->db->count_all_results() > 0;
    }
}