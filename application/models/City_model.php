<?php
// Güvenlik kontrolü: Dosyaya doğrudan URL üzerinden erişimi engeller.
defined('BASEPATH') or exit('No direct script access allowed');

class City_model extends CI_Model
{
    // Modelin işlem yapacağı ana tablo adı tanımlanıyor.
    protected $table = 'cities';

    /**
     * ID numarasına göre tek bir şehri getirir.
     */
    public function find($id)
    {
        return $this->db
            ->where('id', (int) $id) // Gelen ID'yi tam sayıya çevirerek (SQL Injection koruması) filtrele
            ->limit(1)               // Sadece 1 adet kayıt getir
            ->get($this->table)      // 'cities' tablosundan veriyi çek
            ->row_array();           // Sonucu tek bir dizi (row) olarak döndür
    }

    /**
     * URL dostu isme (slug) göre şehri bulur. (Örn: 'istanbul', 'ankara')
     */
    public function find_by_slug($slug)
    {
        return $this->db
            ->where('slug', trim((string) $slug)) // Sağındaki solundaki boşlukları temizle
            ->limit(1)
            ->get($this->table)
            ->row_array();
    }

    /**
     * Veritabanına yeni bir şehir ekler.
     */
    public function create($data)
    {
        // $data dizisindeki bilgileri tabloya gönderir (city_name, slug vb.)
        $ok = $this->db->insert($this->table, $data);
        
        // İşlem başarılıysa yeni oluşan ID'yi döndürür, başarısızsa FALSE döner.
        return $ok ? (int) $this->db->insert_id() : FALSE;
    }

    /**
     * Mevcut bir şehrin bilgilerini ID'sine göre günceller.
     */
    public function update($id, $data)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, $data); // Tablodaki satırı $data içeriğiyle değiştirir.
    }

    /**
     * Belirtilen ID'ye sahip şehri veritabanından tamamen siler.
     */
    public function delete($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->delete($this->table);
    }

    /**
     * Şehirleri listeler ve arama (filtreleme) yapılmasını sağlar.
     */
    public function list($filters = array())
    {
        $this->db->from($this->table);

        // Eğer filtre dizisinde 'q' (arama terimi) anahtarı boş değilse
        if (!empty($filters['q'])) {
            $q = trim((string) $filters['q']);
            
            // SQL'de parantez açar: WHERE (city_name LIKE '%...%' OR slug LIKE '%...%')
            $this->db->group_start()
                ->like('city_name', $q) // Şehir adında ara
                ->or_like('slug', $q)    // VEYA slug içinde ara
                ->group_end();          // Parantezi kapat
        }

        return $this->db
            ->order_by('city_name', 'ASC') // Şehirleri alfabetik sıraya dizer (A-Z)
            ->get()
            ->result_array();              // Tüm sonuçları içeren bir dizi döndürür.
    }

    /**
     * Bir slug'ın (URL adının) veritabanında başka bir şehir tarafından 
     * kullanılıp kullanılmadığını kontrol eder.
     */
    public function exists_by_slug($slug, $excludeId = NULL)
    {
        // Belirtilen slug'ı ara
        $this->db->from($this->table)->where('slug', trim((string) $slug));

        // Eğer bir ID verilmişse, o ID'ye sahip şehri kontrol dışı bırakır.
        // Bu, bir şehri güncellerken "kendi ismin başkasıyla çakışıyor" dememek için kullanılır.
        if ($excludeId !== NULL) {
            $this->db->where('id <>', (int) $excludeId); // ID, gelen ID'den farklı olmalı
        }

        // Eğer sonuç sayısı 0'dan büyükse TRUE (evet, bu isim kullanılıyor) döner.
        return $this->db->count_all_results() > 0;
    }
}