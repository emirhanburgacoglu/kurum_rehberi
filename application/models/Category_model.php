<?php
// Bu satır, dosyanın doğrudan tarayıcıdan erişilmesini engeller, sadece CodeIgniter üzerinden çalışmasını sağlar.
defined('BASEPATH') or exit('No direct script access allowed');

class Category_model extends CI_Model
{
    // İşlem yapılacak varsayılan tablo adı
    protected $table = 'categories';

    /**
     * ID değerine göre tek bir kategori getirir.
     */
    public function find($id)
    {
        return $this->db
            ->where('id', (int) $id) // ID'yi integer'a zorlayarak güvenliği artırır
            ->limit(1)               // Sadece 1 kayıt getir
            ->get($this->table)      // Sorguyu çalıştır
            ->row_array();           // Sonucu tek boyutlu bir dizi olarak döndür
    }

    /**
     * URL dostu isme (slug) göre tek bir kategori getirir.
     */
    public function find_by_slug($slug)
    {
        return $this->db
            ->where('slug', trim((string) $slug)) // Boşlukları temizle ve string olarak ara
            ->limit(1)
            ->get($this->table)
            ->row_array();
    }

    /**
     * Yeni bir kategori ekler.
     */
    public function create($data)
    {
        // $data dizisindeki verileri tabloya ekler
        $ok = $this->db->insert($this->table, $data);
        // İşlem başarılıysa yeni eklenen kaydın ID'sini, başarısızsa FALSE döner
        return $ok ? (int) $this->db->insert_id() : FALSE;
    }

    /**
     * Mevcut bir kategoriyi günceller.
     */
    public function update($id, $data)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, $data); // Belirtilen ID'li kaydı $data ile güncelle
    }

    /**
     * Bir kategoriyi siler.
     */
    public function delete($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->delete($this->table); // Belirtilen ID'li kaydı siler
    }

    /**
     * Kategorileri listeler (Filtreleme desteği ile).
     */
    public function list($filters = array())
    {
        $this->db->from($this->table);

        // Eğer 'q' parametresi (arama terimi) varsa
        if (!empty($filters['q'])) {
            $q = trim((string) $filters['q']);
            $this->db->group_start()            // Parantez aç: (
                ->like('category_name', $q)     // kategori adı içinde ara
                ->or_like('slug', $q)           // VEYA slug içinde ara
                ->group_end();                  // Parantezi kapat: )
        }

        return $this->db
            ->order_by('category_name', 'ASC') // Alfabetik olarak sırala
            ->get()
            ->result_array();                  // Tüm sonuçları dizi olarak döndür
    }

    /**
     * Toplam kategori sayısını döndürür (Arama filtresine uyumlu).
     */
    public function count($filters = array())
    {
        $this->db->from($this->table);

        if (!empty($filters['q'])) {
            $q = trim((string) $filters['q']);
            //group_start(): SQL'deki ( parantezini açar.
            $this->db->group_start()
                ->like('category_name', $q)
                ->or_like('slug', $q)
                ->group_end();
                //group_end(): Parantezi ) kapatır.
        }

        return (int) $this->db->count_all_results(); // Toplam satır sayısını sayı olarak döndür
    }

    /**
     * Bir slug'ın veritabanında zaten var olup olmadığını kontrol eder.
     * Güncelleme yaparken kendi ID'sini hariç tutmak için $excludeId kullanılır.
     */
    public function exists_by_slug($slug, $excludeId = NULL)
    {
        $this->db->from($this->table)->where('slug', trim((string) $slug));

        // Eğer bir ID hariç tutulacaksa (güncelleme işlemi sırasında yararlıdır)
        if ($excludeId !== NULL) {
            $this->db->where('id <>', (int) $excludeId);
        }

        // Eğer sonuç 0'dan büyükse TRUE (yani var), değilse FALSE döner
        return $this->db->count_all_results() > 0;
    }
}