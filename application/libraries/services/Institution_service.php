<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Institution_service
 *
 * Sorumluluk : İş kuralları, filtre normalizasyonu ve çıktı dekorasyonu.
 *              Veritabanına doğrudan erişmez; Institution_model üzerinden çalışır.
 *
 * Mimari kural: Controller → Service → Model
 *   Controller bu servisi çağırır.
 *   Service, modeli çağırır ve sonuçları işler.
 *   Model sadece SQL döner; iş kuralı içermez.
 *
 * İçindekiler:
 *  - get_active_count          : Ana sayfadaki "X kayıtlı kurum" sayacı
 *  - get_filtered_list         : Header arama kutusu + filtre formu için birleşik liste
 *  - get_paginated_list        : Sayfalama desteğiyle filtrelenmiş kurum listesi
 *  - get_premium_institutions  : Ana sayfadaki "Öne Çıkan Kurumlar" bölümü
 *  - get_detail                : Kurum detay sayfası
 *  - prepare_for_insert        : POST verisi → DB'ye hazır dizi
 *  - normalize_public_filters  : [private] Filtre güvenlik katmanı
 *  - decorate_list_item        : [private] Liste çıktısına thumbnail + display_name ekler
 *
 * @property CI_Controller     $CI
 * @property Institution_model $CI->institution_model
 */
class Institution_service
{
    /** @var CI_Controller CI super object (CI3 magic properties) */
    protected $CI;

    // -------------------------------------------------------------------------
    // KURUCU
    // -------------------------------------------------------------------------

    public function __construct()
    {
        // CI3'te super object referansını alarak tüm yüklü kütüphanelere erişim sağlanır.
        $this->CI = &get_instance();

        // Institution_model bu servis çalıştığı sürece her zaman hazır olsun.
        $this->CI->load->model('Institution_model');
         $this->institution_model = $this->CI->institution_model;

    }

    // =========================================================================
    // AKTİF KURUM SAYISI
    // =========================================================================

    /**
     * Veritabanındaki aktif (status=1) kurum sayısını döner.
     *
     * Kullanım yeri:
     *   Ana sayfadaki "X kayıtlı kurum" veya "Platformumuzda X kurum var"
     *   gibi istatistik widget'ları için.
     *
     * Örnek controller kullanımı:
     *   $data['active_count'] = $this->institution_service->get_active_count();
     *
     * @return int
     */
    public function get_active_count()
    {
        return $this->CI->institution_model->count_active();
    }

    // =========================================================================
    // FİLTRELİ LİSTE (sayfalama olmadan — arama kutusu / widget kullanımı)
    // =========================================================================

    /**
     * Arama kutusu ve filtre formu için birleşik kurum listesi döner.
     *
     * Akıllı yönlendirme mantığı:
     *   Sadece 'q' geldi, başka filtre yok
     *     → Institution_model::search_by_keyword() kullanılır.
     *       (Daha az JOIN, daha hızlı; autocomplete için idealdir.)
     *
     *   'q' ile birlikte ek filtre de var (il, ilçe, kategori vb.)
     *   veya hiç 'q' yok, sadece dropdown filtresi var
     *     → Institution_model::list() kullanılır.
     *       (Tüm koşullar tek sorguda uygulanır.)
     *
     * Çıktı dekorasyonu:
     *   Her kurum için decorate_list_item() çağrılır:
     *   → thumbnail : Görsel yoksa default-kurum.png atanır.
     *   → display_name : 50 karakteri aşan isimler kısaltılır (kart taşması önlemi).
     *
     * Sayfalama gerekmiyorsa bu metodu kullan.
     * Sayfalama gerekiyorsa get_paginated_list() metodunu kullan.
     *
     * @param  array $filters  Desteklenen anahtarlar: q, city_id, district_id,
     *                         category_id, sub_category_id, sort
     * @return array           Kurumların dizi listesi (dekorasyonlu)
     */
    public function get_filtered_list($filters = array())
    {
        // Güvenli ve tutarlı bir filtre dizisi oluştur (status=1 zorunlu olarak eklenir).
        $filters = $this->normalize_public_filters($filters);

        // Yalnızca anahtar kelime araması mı, yoksa ek dropdown filtresi de var mı?
        $hasOnlyKeyword = !empty($filters['q'])
            && empty($filters['city_id'])
            && empty($filters['district_id'])
            && empty($filters['category_id'])
            && empty($filters['sub_category_id']);

        if ($hasOnlyKeyword) {
            // Sadece kelime araması → optimize edilmiş keyword sorgusunu kullan.
            // Bu yol daha az JOIN içerdiğinden header autocomplete için çok daha hızlıdır.
            $data = $this->CI->institution_model->search_by_keyword($filters['q'], 50);
        } else {
            // Dropdown filtresi veya kombinasyon → genel list() sorgusunu kullan.
            // Tüm filtreler tek bir sorguda uygulanır.
            $data = $this->CI->institution_model->list($filters, 1000, 0);
        }

        // Her kurum kaydına UI/API için gerekli alanları ekle.
        foreach ($data as &$item) {
            $this->decorate_list_item($item);
        }
        unset($item); // Referans temizleme — foreach sonrası iyi pratik.

        return $data;
    }

    // =========================================================================
    // SAYFALAMALI LİSTE (filtre formu sonuç sayfası için)
    // =========================================================================

    /**
     * Sayfalama desteğiyle filtrelenmiş kurum listesi ve meta bilgilerini döner.
     *
     * Kullanım yeri:
     *   Ana sayfadaki veya arama sonuç sayfasındaki büyük kurum listesi için.
     *   Sonuç sayfası hem listeyi hem sayfalama meta verilerini (toplam, sayfa sayısı)
     *   tek bir çağrıda alır.
     *
     * Dönen yapı:
     *   [
     *     'items'        => [...],  // Kurum listesi (dekorasyonlu)
     *     'total'        => 87,     // Filtreyle eşleşen toplam kurum sayısı
     *     'per_page'     => 20,     // Sayfa başına kurum sayısı
     *     'current_page' => 2,      // Şu anki sayfa numarası
     *     'total_pages'  => 5,      // Toplam sayfa sayısı
     *   ]
     *
     * Örnek controller kullanımı:
     *   $page   = (int) $this->input->get('page') ?: 1;
     *   $result = $this->institution_service->get_paginated_list($filters, 20, $page);
     *   $data['institutions'] = $result['items'];
     *   $data['pagination']   = $result;
     *
     * @param  array $filters   Desteklenen anahtarlar: category_id, sub_category_id,
     *                          city_id, district_id, sort
     * @param  int   $perPage   Sayfa başına kurum sayısı (varsayılan 20)
     * @param  int   $page      Geçerli sayfa numarası, 1'den başlar (varsayılan 1)
     * @return array            items, total, per_page, current_page, total_pages
     */
    public function get_paginated_list($filters = array(), $perPage = 20, $page = 1)
    {
        // Güvenli filtre dizisi oluştur; ziyaretçi tarafında status=1 zorunludur.
        $filters = $this->normalize_public_filters($filters);

        // Sayfa numarası en az 1 olmalı; negatif veya sıfır değer gelirse düzelt.
        $page    = max(1, (int) $page);
        $perPage = max(1, (int) $perPage);
        $offset  = ($page - 1) * $perPage;

        // Toplam eşleşen kayıt sayısı (sayfa sayısı hesabı için).
        $total = $this->CI->institution_model->count_by_filters($filters);

        // Verilen sayfa ve limit ile kurumları getir.
        $items = $this->CI->institution_model->search_by_filters($filters, $perPage, $offset);

        // Her kurum kaydına thumbnail ve display_name ekle.
        foreach ($items as &$item) {
            $this->decorate_list_item($item);
        }
        unset($item);

        return array(
            'items'        => $items,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'total_pages'  => $total > 0 ? (int) ceil($total / $perPage) : 1,
        );
    }

    // =========================================================================
    // PREMİUM KURUMLAR
    // =========================================================================

    /**
     * Ana sayfadaki "Öne Çıkan / Premium Kurumlar" bölümü için veri döner.
     *
     * Kullanım yeri:
     *   Ana sayfada ayrı bir grid veya slider bölümünde yalnızca premium
     *   kurumları göstermek için. get_filtered_list()'ten farklı olarak bu
     *   metod yalnızca package_type_id = 2 olan kurumları getirir.
     *
     * Çıktı dekorasyonu:
     *   Her kurum için thumbnail ve display_name eklenir.
     *
     * Örnek controller kullanımı:
     *   $data['premium'] = $this->institution_service->get_premium_institutions(6);
     *
     * @param  int  $limit  Gösterilecek maksimum premium kurum sayısı (varsayılan 6)
     * @return array        Kurumların dizi listesi (dekorasyonlu)
     */
    public function get_premium_institutions($limit = 6)
    {
        $items = $this->CI->institution_model->get_premium_institutions((int) $limit);

        // Her kurum kaydına UI için gerekli alanları ekle.
        foreach ($items as &$item) {
            $this->decorate_list_item($item);
        }
        unset($item);

        return $items;
    }

    // =========================================================================
    // KURUM DETAYI
    // =========================================================================

    /**
     * Kurum detay sayfası için tek bir kurumun tüm bilgilerini getirir.
     *
     * Doğrulama adımları:
     *   1. $id pozitif tam sayı mı?  → Değilse hata döner.
     *   2. Aktif kurum var mı?       → Yoksa hata döner.
     *
     * Başarılı durumda dönen yapı:
     *   [
     *     'success'  => true,
     *     'message'  => 'Institution loaded.',
     *     'data'     => ['institution' => [..., 'thumbnail' => '...', 'display_name' => '...']],
     *     'errors'   => null,
     *   ]
     *
     * Hata durumunda dönen yapı:
     *   [
     *     'success'  => false,
     *     'message'  => 'Institution not found.',
     *     'data'     => null,
     *     'errors'   => ['id' => '...'],
     *   ]
     *
     * @param  mixed $id  Controller'dan gelen ham ID değeri
     * @return array
     */
    public function get_detail($id)
    {
        // ID sayısal ve pozitif olmalı; string, null veya negatif değer kabul edilmez.
        if (!is_numeric($id) || (int) $id <= 0) {
            return array(
                'success' => FALSE,
                'message' => 'Invalid institution id.',
                'data'    => NULL,
                'errors'  => array('id' => 'ID must be a positive number.')
            );
        }

        // Sadece aktif kurumlar detay sayfasında görünür (status=1).
        $item = $this->CI->institution_model->find_active_by_id((int) $id);

        if (!$item) {
            return array(
                'success' => FALSE,
                'message' => 'Institution not found.',
                'data'    => NULL,
                'errors'  => array('id' => 'No active institution for given id.')
            );
        }

        // Görsel yoksa default görsel ata; display_name ekle.
        $name = isset($item['name']) ? (string) $item['name'] : '';
        $item['thumbnail']    = !empty($item['image_path'])
            ? base_url($item['image_path'])
            : base_url('assets/img/default-kurum.png');
        $item['display_name'] = $name;

        return array(
            'success' => TRUE,
            'message' => 'Institution loaded.',
            'data'    => array('institution' => $item),
            'errors'  => NULL
        );
    }

    // =========================================================================
    // VERİ HAZIRLAMA
    // =========================================================================

    /**
     * Controller'dan gelen POST verisini DB insert'e hazır hale getirir.
     *
     * Yapılan dönüşümler:
     *   name       → Boşluklar temizlenir.
     *   slug       → url_title() ile URL dostu forma çevrilir (Örn: "Deneme Okulu" → "deneme-okulu").
     *   status     → 0 olarak ayarlanır; yeni eklenen kurum admin onayına kadar pasif kalır.
     *   created_at → Sunucu zamanı ile otomatik doldurulur.
     *
     * @param  array $post_data  $_POST veya $this->input->post() çıktısı
     * @return array             DB insert için hazırlanmış sütun => değer dizisi
     */
    public function prepare_for_insert($post_data)
    {
        $name    = isset($post_data['name'])    ? trim((string) $post_data['name'])    : '';
        $city_id = isset($post_data['city_id']) ? (int) $post_data['city_id']          : 0;

        return array(
            'name'       => $name,
            'slug'       => url_title($name, 'dash', TRUE), // CI3 helper: Türkçe karakter desteği için TRUE
            'city_id'    => $city_id,
            'status'     => 0,                              // Yeni kurum her zaman pasif başlar
            'created_at' => date('Y-m-d H:i:s')
        );
    }

    // =========================================================================
    // PRIVATE — FİLTRE GÜVENLİK KATMANI
    // =========================================================================

    /**
     * Public (web/api) listelerde yalnızca aktif kurumların görünmesini garanti eder.
     *
     * Bu metod iki görevi aynı anda yerine getirir:
     *   1. Güvenlik: status=1 filtresi her zaman eklenir.
     *                Controller veya dışarıdan gelen status değeri görmezden gelinir.
     *   2. Temizlik: Boş veya geçersiz filtre anahtarları modele taşınmaz.
     *                Model, gereksiz WHERE koşulları oluşturmaz.
     *
     * Neden gerekli?
     *   Controller doğrudan $_GET verisini servisе geçebilir.
     *   Bu metod, "status=0 geçilirse pasif kurumlar görünür" riskini ortadan kaldırır.
     *
     * @param  array $filters  Ham filtre dizisi (controller'dan gelen)
     * @return array           Temizlenmiş ve güvenli filtre dizisi
     */
    private function normalize_public_filters($filters)
    {
        $filters = is_array($filters) ? $filters : array();

        return array(
            // Public tarafta zorunlu kural: yalnızca aktif kurumlar listelenir.
            'status'          => 1,

            // Arama kelimesi; boş string gelirse model LIKE koşulu eklemez.
            'q'               => isset($filters['q'])               ? trim((string) $filters['q'])               : '',

            // Konum filtreleri; boş string gelirse model WHERE koşulu eklemez.
            'city_id'         => isset($filters['city_id'])         ? (string) $filters['city_id']         : '',
            'district_id'     => isset($filters['district_id'])     ? (string) $filters['district_id']     : '',

            // Kategori filtreleri; boş string gelirse model WHERE koşulu eklemez.
            'category_id'     => isset($filters['category_id'])     ? (string) $filters['category_id']     : '',
            'sub_category_id' => isset($filters['sub_category_id']) ? (string) $filters['sub_category_id'] : '',

            // Sıralama türü; varsayılan 'priority' (paket öncelik sırası).
            'sort'            => isset($filters['sort'])            ? (string) $filters['sort']            : 'priority',
        );
    }

    // =========================================================================
    // PRIVATE — ÇIKTI DEKORASYONU
    // =========================================================================

    /**
     * Tek bir kurum kaydını UI/API tüketimine hazır hale getirir.
     *
     * Eklenen alanlar:
     *
     *   thumbnail (string)
     *     Kurumun liste kartında gösterilecek görsel URL'si.
     *     image_path dolu ise → base_url() ile tam URL oluşturulur.
     *     image_path boş ise  → assets/img/default-kurum.png atanır.
     *
     *   display_name (string)
     *     Kart tasarımlarında taşmayı önlemek için kısaltılmış kurum adı.
     *     50 karakteri aşmıyorsa orijinal ad döner.
     *     50 karakteri aşıyorsa ilk 47 karakter alınır ve "..." eklenir.
     *     UTF-8 güvenliği: mb_ fonksiyonları öncelikli kullanılır;
     *     yoksa tek byte strlen/substr'e düşülür.
     *
     * Not: $item referans (&) ile alınır; çağıran taraftaki dizideki orijinal
     *      kayıt güncellenir, yeni bir kopya oluşturulmaz.
     *
     * @param  array $item  Kurum kaydı (referans)
     * @return void
     */
    private function decorate_list_item(&$item)
    {
        $name = isset($item['name']) ? (string) $item['name'] : '';

        // Görsel URL'si: kayıtlı görsel varsa kullan, yoksa varsayılan görseli ata.
        $item['thumbnail'] = !empty($item['image_path'])
            ? base_url($item['image_path'])
            : base_url('assets/img/default-kurum.png');

        // İsim kısaltma: mb_ fonksiyonları Türkçe karakter güvenliği sağlar.
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            $item['display_name'] = (mb_strlen($name, 'UTF-8') > 50)
                ? mb_substr($name, 0, 47, 'UTF-8') . '...'
                : $name;
            return;
        }

        // mb_ yoksa tek byte alternatifine düş (ASCII ortamlar için yedek).
        $item['display_name'] = (strlen($name) > 50)
            ? substr($name, 0, 47) . '...'
            : $name;
    }
}