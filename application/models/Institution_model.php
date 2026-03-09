<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Institution_model
 *
 * Sorumluluk : Sadece veritabanı işlemleri. İş kuralı ve validasyon yok.
 * Mimari kural: Model → Service → Controller
 *
 * İçindekiler:
 *  - Tekil sorgular    : find, find_active_by_id, find_by_slug, get_recent_active
 *  - CRUD              : create, update, delete
 *  - Listeleme         : list, count
 *  - Arama (2 tip)     : search_by_keyword, search_by_filters, count_by_filters
 *  - Durum / Sayaçlar  : set_status, increment_* metodları
 *  - Paket / Sahip     : set_package, attach_owner
 *  - Private yardımcı  : _apply_sort
 *
 * SIRALAMA MANTIĞI (package_type_id önceliği):
 *   Klasik ASC/DESC yerine öncelik sırası kullanılır:
 *     1. package_type_id = 2  (en üst)
 *     2. package_type_id = 1
 *     3. package_type_id = NULL / diğer  (en alt)
 *
 *   Bunu sağlamak için MySQL'in FIELD() fonksiyonu kullanılır:
 *     FIELD(i.package_type_id, 2, 1)
 *     → package_type_id = 2  → FIELD değeri 1 (en küçük = önce gelir)
 *     → package_type_id = 1  → FIELD değeri 2
 *     → listede olmayan değer → FIELD değeri 0 → ASC'de başa gelir (istemiyoruz)
 *
 *   NULL ve listede olmayan değerleri sona atmak için ek koşul:
 *     (i.package_type_id IS NULL OR i.package_type_id NOT IN (2,1)) ASC
 *     → 0 = package_type_id 2 veya 1 olan → önce
 *     → 1 = NULL veya başka değer         → sona
 */
class Institution_model extends CI_Model
{
    protected $table = 'institutions';

    // =========================================================================
    // TEKİL SORGULAR
    // =========================================================================

    /**
     * ID ile kurumu getirir. Aktif/pasif fark etmez.
     * Admin paneli veya iç kontroller için kullanılır.
     *
     * @param  int        $id
     * @return array|null
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
     * ID ile sadece aktif (status=1) kurumu getirir.
     * Web ve API tarafı için uygundur; pasif kurum null döner.
     *
     * @param  int        $id
     * @return array|null
     */
    public function find_active_by_id($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->where('status', 1)
            ->limit(1)
            ->get($this->table)
            ->row_array();
    }

    /**
     * URL dostu slug ile kurumu getirir.
     * Kurum detay sayfasının açılmasında kullanılır.
     *
     * @param  string     $slug
     * @return array|null
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
     * Son eklenen aktif kurumları getirir.
     * Ana sayfadaki "Yeni Eklenenler" veya öneri widget'ları için kullanılır.
     *
     * @param  int   $limit  Kaç kurum getirileceği (varsayılan 10)
     * @return array
     */
    public function get_recent_active($limit = 10)
    {
        return $this->db
            ->where('status', 1)
            ->order_by('id', 'DESC')
            ->limit((int) $limit)
            ->get($this->table)
            ->result_array();
    }

    // =========================================================================
    // CRUD
    // =========================================================================

    /**
     * Yeni kurum kaydı oluşturur.
     * Başarılıysa eklenen kaydın ID'sini, başarısızsa FALSE döner.
     *
     * @param  array      $data  Sütun => değer çiftleri
     * @return int|false
     */
    public function create($data)
    {
        $ok = $this->db->insert($this->table, $data);
        return $ok ? (int) $this->db->insert_id() : FALSE;
    }

    /**
     * Mevcut kurumu günceller.
     * Sadece $data içinde gönderilen sütunlar güncellenir.
     *
     * @param  int   $id
     * @param  array $data  Güncellenecek sütun => değer çiftleri
     * @return bool
     */
    public function update($id, $data)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, $data);
    }

    /**
     * Kurumu fiziksel olarak siler.
     * Genellikle soft-delete (set_status) tercih edilir.
     * Bu metod yalnızca zorunlu durumlarda kullanılmalıdır.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->delete($this->table);
    }

    // =========================================================================
    // LİSTELEME — Admin paneli ve genel liste sayfası için
    // =========================================================================

    /**
     * Filtrelenmiş ve sıralanmış kurum listesi döner. Sayfalama desteklidir.
     * Institution_service::list() bu metodu çağırır.
     * Controller bu metodu doğrudan çağırmaz.
     *
     * Kabul edilen filtreler:
     *   status          → 0: pasif, 1: aktif (geçilmezse WHERE eklenmez)
     *   city_id         → İl ID'si
     *   district_id     → İlçe ID'si
     *   category_id     → Ana kategori (sub_categories üzerinden dolaylı bağlantı)
     *   sub_category_id → Alt kategori (doğrudan institutions.sub_category_id)
     *   q               → Kurum adında kelime araması (LIKE)
     *   sort            → Sıralama tipi (bkz. _apply_sort). Varsayılan: 'priority'
     *
     * @param  array $filters
     * @param  int   $limit
     * @param  int   $offset
     * @return array
     */
    public function list($filters = array(), $limit = 20, $offset = 0)
    {
        $this->db->select('i.*');
        $this->db->from($this->table . ' i');
        $this->db->join('sub_categories sc', 'sc.id = i.sub_category_id', 'left');

        if (isset($filters['status']) && ctype_digit((string) $filters['status'])) {
            $this->db->where('i.status', (int) $filters['status']);
        }

        if (!empty($filters['city_id']) && ctype_digit((string) $filters['city_id'])) {
            $this->db->where('i.city_id', (int) $filters['city_id']);
        }

        if (!empty($filters['district_id']) && ctype_digit((string) $filters['district_id'])) {
            $this->db->where('i.district_id', (int) $filters['district_id']);
        }

        if (!empty($filters['category_id']) && ctype_digit((string) $filters['category_id'])) {
            $this->db->where('sc.category_id', (int) $filters['category_id']);
        }

        if (!empty($filters['sub_category_id']) && ctype_digit((string) $filters['sub_category_id'])) {
            $this->db->where('i.sub_category_id', (int) $filters['sub_category_id']);
        }

        if (!empty($filters['q'])) {
            $this->db->like('i.name', trim((string) $filters['q']));
        }

        $this->_apply_sort(isset($filters['sort']) ? $filters['sort'] : 'priority');

        return $this->db
            ->limit((int) $limit, (int) $offset)
            ->get()
            ->result_array();
    }

    /**
     * list() ile aynı filtreler uygulanarak toplam kayıt sayısı döner.
     * Sayfalama hesabı için Institution_service içinde list() ile birlikte kullanılır.
     *
     * @param  array $filters  list() ile aynı filtre dizisi
     * @return int
     */
    public function count($filters = array())
    {
        $this->db->from($this->table . ' i');
        $this->db->join('sub_categories sc', 'sc.id = i.sub_category_id', 'left');

        if (isset($filters['status']) && ctype_digit((string) $filters['status'])) {
            $this->db->where('i.status', (int) $filters['status']);
        }

        if (!empty($filters['city_id']) && ctype_digit((string) $filters['city_id'])) {
            $this->db->where('i.city_id', (int) $filters['city_id']);
        }

        if (!empty($filters['district_id']) && ctype_digit((string) $filters['district_id'])) {
            $this->db->where('i.district_id', (int) $filters['district_id']);
        }

        if (!empty($filters['category_id']) && ctype_digit((string) $filters['category_id'])) {
            $this->db->where('sc.category_id', (int) $filters['category_id']);
        }

        if (!empty($filters['sub_category_id']) && ctype_digit((string) $filters['sub_category_id'])) {
            $this->db->where('i.sub_category_id', (int) $filters['sub_category_id']);
        }

        if (!empty($filters['q'])) {
            $this->db->like('i.name', trim((string) $filters['q']));
        }

        return (int) $this->db->count_all_results();
    }

    // =========================================================================
    // ARAMA — TİP 1: Kelime bazlı (header / arama kutusu)
    // =========================================================================

    /**
     * Kullanıcının yazdığı kelimeyi kurum adında arar.
     *
     * Kullanım yeri:
     *   Sitenin üst kısmındaki arama kutusunda hızlı/anlık sonuç göstermek için.
     *   Örnek: Kullanıcı "Atatürk" yazdı → adında "Atatürk" geçen kurumlar döner.
     *
     * search_by_filters'dan farkı:
     *   Dropdown filtresi yoktur, sadece kurum adına bakar.
     *   JOIN sayısı azdır, çok daha hızlıdır.
     *   Sonuç sayısı sınırlı tutulur (autocomplete / önizleme için).
     *
     * Sıralama:
     *   Paket öncelik sırası: package_type_id = 2 önce, 1 sonra, geri kalanlar en sona.
     *
     * @param  string $q      Aranan kelime
     * @param  int    $limit  Maksimum sonuç sayısı (varsayılan 10)
     * @return array          id, name, slug, city_name, sub_category_name sütunlarını döner
     */
    public function search_by_keyword($q, $limit = 10)
    {
        $this->db->select('i.id, i.name, i.slug, ci.city_name, sc.sub_category_name');
        $this->db->from($this->table . ' i');
        $this->db->join('sub_categories sc', 'sc.id = i.sub_category_id', 'left');
        $this->db->join('cities ci',         'ci.id = i.city_id',         'left');
        $this->db->where('i.status', 1);
        $this->db->like('i.name', trim((string) $q));

        $this->_apply_sort('priority');

        return $this->db
            ->limit((int) $limit)
            ->get()
            ->result_array();
    }

    // =========================================================================
    // ARAMA — TİP 2: Dropdown filtresiyle (ana sayfa filtre formu)
    // =========================================================================

    /**
     * Dropdown seçimlerine göre kurumları filtreler ve sıralar.
     *
     * Kullanım yeri:
     *   Ana sayfadaki filtre formunda. Kullanıcı kategori, alt kategori,
     *   şehir, ilçe seçer; bu metod sadece o kriterlere uyan aktif kurumları döner.
     *   Sayfalama desteklidir; count_by_filters() ile birlikte kullanılır.
     *
     * search_by_keyword'den farkı:
     *   Kelime araması yapmaz, dropdown değerlerine göre tam eşleşme filtreler.
     *   Daha fazla filtre parametresi kabul eder.
     *
     * Kabul edilen filtreler:
     *   category_id     → Ana kategori seçimi (sub_categories üzerinden dolaylı)
     *   sub_category_id → Alt kategori seçimi (doğrudan)
     *   city_id         → İl seçimi
     *   district_id     → İlçe seçimi
     *   sort            → Sıralama tipi (bkz. _apply_sort). Varsayılan: 'priority'
     *
     * @param  array $filters
     * @param  int   $limit
     * @param  int   $offset
     * @return array
     */
    public function search_by_filters($filters = array(), $limit = 20, $offset = 0)
    {
        $this->db->select('i.*');
        $this->db->from($this->table . ' i');
        $this->db->join('sub_categories sc', 'sc.id = i.sub_category_id', 'left');

        // Ziyaretçi her zaman yalnızca aktif kurumları görür
        $this->db->where('i.status', 1);

        if (!empty($filters['category_id']) && ctype_digit((string) $filters['category_id'])) {
            $this->db->where('sc.category_id', (int) $filters['category_id']);
        }

        if (!empty($filters['sub_category_id']) && ctype_digit((string) $filters['sub_category_id'])) {
            $this->db->where('i.sub_category_id', (int) $filters['sub_category_id']);
        }

        if (!empty($filters['city_id']) && ctype_digit((string) $filters['city_id'])) {
            $this->db->where('i.city_id', (int) $filters['city_id']);
        }

        if (!empty($filters['district_id']) && ctype_digit((string) $filters['district_id'])) {
            $this->db->where('i.district_id', (int) $filters['district_id']);
        }

        $this->_apply_sort(isset($filters['sort']) ? $filters['sort'] : 'priority');

        return $this->db
            ->limit((int) $limit, (int) $offset)
            ->get()
            ->result_array();
    }

    /**
     * search_by_filters() ile tamamen aynı WHERE koşullarını uygular,
     * sadece toplam eşleşen kayıt sayısını döner.
     *
     * Sayfalama için Institution_service içinde search_by_filters() ile
     * birlikte çağrılır:
     *   $total  = $this->institution_model->count_by_filters($filters);
     *   $result = $this->institution_model->search_by_filters($filters, $limit, $offset);
     *
     * @param  array $filters  search_by_filters() ile aynı filtre dizisi
     * @return int
     */
    public function count_by_filters($filters = array())
    {
        $this->db->from($this->table . ' i');
        $this->db->join('sub_categories sc', 'sc.id = i.sub_category_id', 'left');
        $this->db->where('i.status', 1);

        if (!empty($filters['category_id']) && ctype_digit((string) $filters['category_id'])) {
            $this->db->where('sc.category_id', (int) $filters['category_id']);
        }

        if (!empty($filters['sub_category_id']) && ctype_digit((string) $filters['sub_category_id'])) {
            $this->db->where('i.sub_category_id', (int) $filters['sub_category_id']);
        }

        if (!empty($filters['city_id']) && ctype_digit((string) $filters['city_id'])) {
            $this->db->where('i.city_id', (int) $filters['city_id']);
        }

        if (!empty($filters['district_id']) && ctype_digit((string) $filters['district_id'])) {
            $this->db->where('i.district_id', (int) $filters['district_id']);
        }

        return (int) $this->db->count_all_results();
    }

    // =========================================================================
    // DURUM YÖNETİMİ
    // =========================================================================

    /**
     * Kurumun aktif/pasif durumunu değiştirir.
     * Admin panelinde onay veya askıya alma işlemi için kullanılır.
     *
     * @param  int  $id
     * @param  int  $status  0: pasif, 1: aktif
     * @return bool
     */
    public function set_status($id, $status)
    {
        return $this->update((int) $id, array('status' => (int) $status));
    }

    // =========================================================================
    // GÖRÜNTÜLENME / TIKLANMA SAYAÇLARI
    //
    // Her metod ilgili sütunu atomik olarak 1 artırır.
    // Servis katmanı hangi sayacın artırılacağına karar verir;
    // model sadece SQL'i çalıştırır.
    // =========================================================================

    /**
     * Kurum profil sayfası görüntülenme sayısını 1 artırır.
     * Kurum detay sayfası her açıldığında tetiklenir.
     *
     * @param  int  $id
     * @return bool
     */
    public function increment_view($id)
    {
        return $this->db->set('view_count', 'view_count+1', FALSE)
            ->where('id', (int) $id)
            ->update($this->table);
    }

    /**
     * Adres bilgisi görüntülenme sayısını 1 artırır.
     * Kullanıcı "Adresi Göster" butonuna bastığında tetiklenir.
     *
     * @param  int  $id
     * @return bool
     */
    public function increment_address_view($id)
    {
        return $this->db->set('address_view_count', 'address_view_count+1', FALSE)
            ->where('id', (int) $id)
            ->update($this->table);
    }

    /**
     * Telefon numarası tıklanma sayısını 1 artırır.
     * Kullanıcı telefon numarasını görünür yaptığında veya aradığında tetiklenir.
     *
     * @param  int  $id
     * @return bool
     */
    public function increment_phone_view($id)
    {
        return $this->db->set('phone_view_count', 'phone_view_count+1', FALSE)
            ->where('id', (int) $id)
            ->update($this->table);
    }

    /**
     * Web sitesi bağlantısı tıklanma sayısını 1 artırır.
     * Kullanıcı kurumun web sitesi linkine tıkladığında tetiklenir.
     *
     * @param  int  $id
     * @return bool
     */
    public function increment_website_view($id)
    {
        return $this->db->set('website_view_count', 'website_view_count+1', FALSE)
            ->where('id', (int) $id)
            ->update($this->table);
    }

    /**
     * E-posta tıklanma sayısını 1 artırır.
     * Kullanıcı kurum e-posta adresine tıkladığında tetiklenir.
     *
     * @param  int  $id
     * @return bool
     */
    public function increment_mail_view($id)
    {
        return $this->db->set('mail_view_count', 'mail_view_count+1', FALSE)
            ->where('id', (int) $id)
            ->update($this->table);
    }

    /**
     * WhatsApp tıklanma sayısını 1 artırır.
     * Kullanıcı WhatsApp butonuna tıkladığında tetiklenir.
     *
     * @param  int  $id
     * @return bool
     */
    public function increment_wp_view($id)
    {
        return $this->db->set('wp_view_count', 'wp_view_count+1', FALSE)
            ->where('id', (int) $id)
            ->update($this->table);
    }

    // =========================================================================
    // PAKET VE SAHİP İŞLEMLERİ
    // =========================================================================

    /**
     * Kuruma paket atar veya mevcut paketi günceller.
     * $packageTypeId NULL geçilirse paket kaldırılır (ücretsiz/paketsiz duruma düşer).
     *
     * @param  int         $id
     * @param  int|null    $packageTypeId  Paket tipi ID'si (NULL = paketsiz)
     * @param  string|null $startsAt       Abonelik başlangıç tarihi (Y-m-d H:i:s)
     * @param  string|null $expiresAt      Abonelik bitiş tarihi (Y-m-d H:i:s)
     * @return bool
     */
    public function set_package($id, $packageTypeId, $startsAt, $expiresAt)
    {
        return $this->update((int) $id, array(
            'package_type_id'    => $packageTypeId !== NULL ? (int) $packageTypeId : NULL,
            'package_starts_at'  => $startsAt,
            'package_expires_at' => $expiresAt,
        ));
    }

    /**
     * Kuruma kullanıcı (kurum sahibi) bağlar.
     * Başvuru onaylandığında veya admin manuel atama yaptığında çağrılır.
     *
     * @param  int  $id      Kurum ID'si
     * @param  int  $userId  Bağlanacak kullanıcının ID'si
     * @return bool
     */
    public function attach_owner($id, $userId)
    {
        return $this->update((int) $id, array('user_id' => (int) $userId));
    }

    // =========================================================================
    // PRIVATE — SIRALAMA YARDIMCISI
    // =========================================================================

    /**
     * ORDER BY koşulunu uygular.
     *
     * Desteklenen sort değerleri:
     *
     *   'priority'  → package_type_id öncelik sırası (varsayılan)
     *                 1. package_type_id = 2  (en üst)
     *                 2. package_type_id = 1
     *                 3. NULL veya diğer değerler  (en alt)
     *
     *   'newest'    → En yeni eklenen kurum önce (created_at DESC)
     *   'oldest'    → En eski eklenen kurum önce (created_at ASC)
     *   'name_asc'  → Kurum adına göre A-Z
     *   'name_desc' → Kurum adına göre Z-A
     *
     * FIELD() fonksiyonu nasıl çalışır?
     *   FIELD(i.package_type_id, 2, 1)
     *   → package_type_id = 2  ise FIELD = 1  (en küçük değer, ASC'de önce gelir)
     *   → package_type_id = 1  ise FIELD = 2
     *   → listede olmayan değer ise FIELD = 0  (ASC'de başa gelir — istemiyoruz)
     *
     *   Bu yüzden FIELD()'den önce bir "eleme" koşulu daha ekliyoruz:
     *   (i.package_type_id IS NULL OR i.package_type_id NOT IN (2, 1)) ASC
     *   → 0 = package_type_id 2 veya 1 olan kurum → önce
     *   → 1 = NULL veya başka bir değer           → sona
     *
     *   Bu iki ORDER BY birlikte çalışınca sonuç şu olur:
     *   package_type_id = 2  → önce
     *   package_type_id = 1  → sonra
     *   NULL / diğer         → en sona
     *
     * @param  string $sort
     * @return void
     */
    private function _apply_sort($sort)
    {
        switch ($sort) {

            case 'priority':
                // Adım 1: NULL ve tanımsız paketleri sona at
                $this->db->order_by(
                    '(i.package_type_id IS NULL OR i.package_type_id NOT IN (2, 1))',
                    'ASC'
                );
                // Adım 2: 2 → 1 öncelik sırası uygula
                $this->db->order_by('FIELD(i.package_type_id, 2, 1)', 'ASC');
                break;

            case 'newest':
                $this->db->order_by('i.created_at', 'DESC');
                break;

            case 'oldest':
                $this->db->order_by('i.created_at', 'ASC');
                break;

            case 'name_asc':
                $this->db->order_by('i.name', 'ASC');
                break;

            case 'name_desc':
                $this->db->order_by('i.name', 'DESC');
                break;

            default:
                // Tanınmayan bir değer gelirse priority uygula
                $this->db->order_by(
                    '(i.package_type_id IS NULL OR i.package_type_id NOT IN (2, 1))',
                    'ASC'
                );
                $this->db->order_by('FIELD(i.package_type_id, 2, 1)', 'ASC');
                break;
        }
    }
}