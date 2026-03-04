# 🏢 Kurum Rehberi - Güncel Klasör ve Mimari Yapısı

Bu doküman, "Kurum Rehberi" projesinin **fiziksel olarak şu anda var olan** dosya ve klasörlerini yansıtmaktadır. Proje çok katmanlı (N-Tier) web ve mobil API destekli şekilde geliştirilmektedir.

Aşağıdaki ağaç yapısı **doğrudan projenin mevcut durumunu** gösterir:


📁 kurum_rehberi/               -> PROJE ANA DİZİNİ
│
├── 📁 application/             # 🛠 PROJENİN KALBİ (Bütün kodlarımız burada)
│   │
│   ├── 📁 controllers/         # 🚪 KONTROLCÜLER (Ziyaretçiyi karşılayan kapıcılar)
│   │   │
│   │   ├── � api/             # 📱 MOBİL UYGULAMA İÇİN API BÖLÜMÜ (JSON döner)
│   │   │   └── 📁 v1/
│   │   │       └── 📄 Institutions.php  -> Mobilden kurumları listeleme / detay API'si
│   │   │
│   │   └── 📁 web/             # 💻 WEB SİTESİ İÇİN BÖLÜM (Ekrana HTML/Tasarım çizer)
│   │       ├── 📄 Home.php              -> Web tarafı ana sayfa arama ve akışı
│   │       └── 📄 Institutions.php      -> Web tarafı kurum listeleme/detay ekranları
│   │
│   ├── � core/                # ⚙️ ÇEKİRDEK UZANTILARI
│   │   └── 📄 MY_Controller.php         -> CodeIgniter'ın kendi yapısını genişlettiğimiz ana kontrolcü
│   │
│   ├── 📁 libraries/           # 🧩 KÜTÜPHANELER
│   │   │
│   │   └── 📁 services/        # 🧠 SERVİSLER (Projenin beyni, hesaplamalar ve mantık merkezi)
│   │       └── 📄 Institution_service.php -> Kurum işlemlerinin (filtreleme, listeleme) yapıldığı asıl iş kuralı dosyası
│   │
│   ├── 📁 models/              # 💾 MODELLER (Veritabanı işlemleri - Sadece SQL atar)
│   │   └── 📄 Institution_model.php     -> Veritabanındaki Institutions tablosu ile konuşur
│   │
│   ├── 📁 migrations/          # � VERİTABANI KURULUM DOSYALARI (Toplam 17 Tablo)
│   │   ├── � 001_create_users_table.php
│   │   ├── � 002_create_categories_table.php
│   │   ├── � 003_create_cities_table.php
│   │   ├── 📄 ... (package_types, attributes, faqs, districts, institutions vb. tabloların kurulum kodları)
│   │   └── 📄 017_alter_institutions_price_period.php
│   │
│   ├── 📁 views/               # 🎨 GÖRÜNÜMLER (Sadece Web İçin HTML Ekranlar)
│   │   ├── 📄 welcome_message.php       -> Karşılama ekranı
│   │   └── � errors/                   -> Hata sayfaları klasörü (404, 500 hataları vb.)
│   │
│   ├── 📁 config/              # ⚙️ PROJE AYARLARI
│   │   ├── 📄 routes.php                -> Yönlendirmeler
│   │   ├── 📄 database.php              -> DB bağlantı bilgileri
│   │   ├── � constants.php, config.php, autoload.php vs.
│   │
│   └── 📁 cache/, 📁 helpers/, 📁 hooks/, 📁 language/, 📁 logs/, 📁 third_party/ -> Diğer CI3 klasörleri
│
├── 📁 system/                  # 🔒 Framework'ün kendi kodları (CodeIgniter Core)
│
├── 📄 gel_tam.txt              -> Projenin planlamalarının ve sprint görevlerinin tutulduğu dosya
├── 📄 mimari.txt               -> Hedeflenen mimari blueprint (kavramsal kural tablosu)
├── 📄 KURUM_REHBERI_MIMARI_YAPISI.md -> Şu an Okuduğunuz Dosya
├── 📄 read_docx.py             -> Word (docx) dosyalarını okumak için Python aracı
├── 📄 read_docx.ps1            -> Okuma aracını çalıştıran PowerShell betiği
├── 📄 generate_docx.py         -> Klasör ağacını DOCX'e çevirme script'i
├── 📄 index.php                -> 🚀 Projenin ilk tetiklendiği kapı
├── 📄 composer.json            -> Composer (Paket yöneticisi) dosyası
└── 📄 .htaccess, .gitignore, .editorconfig -> Sunucu ve Git geliştirme ayarları

## Servis Katmanı Standartları

Servisler tek klasörde toplanır:

- `application/libraries/services/`

Controller içinden servis yükleme standardı:

```php
$this->load->library('services/Institution_service');
```

Bu yaklaşımda:

1. Controller sadece request/response yönetir.
2. İş kuralları servislerde olur.
3. Veritabanı sorguları modelde kalır.

## Zorunlu Geliştirme Sırası (Servislerden ve Methodlardan Önce)

Bu projede standart geliştirme sırası aşağıdaki gibidir.  
Servis isimlerine ve method detaylarına geçmeden önce bu sıra uygulanır.

1. Migration ve tablo yapısı netleştirilir.
2. İlgili `Model` dosyası yazılır (`X_model`).
3. Modelde temel veri erişim methodları yazılır (`find`, `list`, `create`, `update`, `delete`, `count`).
4. Model test edilmeden `Service` yazımına geçilmez.
5. Model hazır olduktan sonra `Service` yazılır (`X_service`).
6. Service içinde iş kuralları, validasyon ve dönüşüm kuralları yazılır.
7. Service hazır olduktan sonra `Controller` yazılır.
8. Önce Web Controller, sonra gerekiyorsa API Controller yazılır.
9. Route tanımları yapılır.
10. Son olarak başarılı/hatalı senaryolar test edilir.

Kısa kural:

`Önce Model -> Sonra Service -> Sonra Controller (Web/API)`

## Modeller ve Method Kapsamı

Bu bölüm, servislerden önce yazılacak model katmanının net kapsamını tanımlar.

### 1) User_model (`users`)

1. `find(int $id): ?array`
2. `find_by_email(string $email): ?array`
3. `find_by_reset_token(string $token): ?array`
4. `create(array $data): int|false`
5. `update(int $id, array $data): bool`
6. `delete(int $id): bool`
7. `list(array $filters = [], int $limit = 20, int $offset = 0): array`
8. `count(array $filters = []): int`
9. `set_password(int $id, string $hash): bool`
10. `set_status(int $id, int $status): bool`
11. `set_role(int $id, int $role): bool`
12. `set_email_verified_at(int $id, ?string $datetime): bool`
13. `set_reset_token(int $id, ?string $token, ?string $expiresAt): bool`
14. `set_remember_token(int $id, ?string $token): bool`

### 2) Category_model (`categories`)

1. `find(int $id): ?array`
2. `find_by_slug(string $slug): ?array`
3. `create(array $data): int|false`
4. `update(int $id, array $data): bool`
5. `delete(int $id): bool`
6. `list(array $filters = []): array`
7. `count(array $filters = []): int`
8. `exists_by_slug(string $slug, ?int $excludeId = null): bool`

### 3) City_model (`cities`)

1. `find(int $id): ?array`
2. `find_by_slug(string $slug): ?array`
3. `create(array $data): int|false`
4. `update(int $id, array $data): bool`
5. `delete(int $id): bool`
6. `list(array $filters = []): array`
7. `exists_by_slug(string $slug, ?int $excludeId = null): bool`

### 4) Package_type_model (`package_types`)

1. `find(int $id): ?array`
2. `find_by_name(string $name): ?array`
3. `create(array $data): int|false`
4. `update(int $id, array $data): bool`
5. `delete(int $id): bool`
6. `list(array $filters = []): array`
7. `list_active(): array`
8. `set_status(int $id, int $status): bool`

### 5) Attribute_model (`attributes`)

1. `find(int $id): ?array`
2. `find_many(array $ids): array`
3. `create(array $data): int|false`
4. `update(int $id, array $data): bool`
5. `delete(int $id): bool`
6. `list(array $filters = []): array`

### 6) Faq_model (`faqs`)

1. `find(int $id): ?array`
2. `create(array $data): int|false`
3. `update(int $id, array $data): bool`
4. `delete(int $id): bool`
5. `list(array $filters = []): array`
6. `list_active(): array`
7. `list_by_attribute(int $attributeId): array`
8. `set_status(int $id, int $status): bool`

### 7) Sub_category_model (`sub_categories`)

1. `find(int $id): ?array`
2. `find_by_slug(string $slug): ?array`
3. `create(array $data): int|false`
4. `update(int $id, array $data): bool`
5. `delete(int $id): bool`
6. `list(array $filters = []): array`
7. `list_by_category(int $categoryId): array`
8. `count(array $filters = []): int`
9. `exists_by_slug(string $slug, ?int $excludeId = null): bool`

### 8) District_model (`districts`)

1. `find(int $id): ?array`
2. `find_by_city_and_slug(int $cityId, string $slug): ?array`
3. `create(array $data): int|false`
4. `update(int $id, array $data): bool`
5. `delete(int $id): bool`
6. `list(array $filters = []): array`
7. `list_by_city(int $cityId): array`
8. `exists_city_slug(int $cityId, string $slug, ?int $excludeId = null): bool`

### 9) Category_attribute_model (`category_attributes`)

1. `attach(int $categoryId, int $attributeId): bool`
2. `detach(int $categoryId, int $attributeId): bool`
3. `sync(int $categoryId, array $attributeIds): bool`
4. `list_by_category(int $categoryId): array`
5. `list_by_attribute(int $attributeId): array`
6. `exists(int $categoryId, int $attributeId): bool`
7. `delete_by_category(int $categoryId): bool`

### 10) Institution_model (`institutions`)

1. `find(int $id): ?array`
2. `find_active_by_id(int $id): ?array`
3. `find_by_slug(string $slug): ?array`
4. `get_recent_active(int $limit = 10): array`
5. `search(array $filters = []): array`
6. `create(array $data): int|false`
7. `update(int $id, array $data): bool`
8. `delete(int $id): bool`
9. `list(array $filters = [], int $limit = 20, int $offset = 0): array`
10. `count(array $filters = []): int`
11. `set_status(int $id, int $status): bool`
12. `increment_view(int $id): bool`
13. `increment_address_view(int $id): bool`
14. `increment_phone_view(int $id): bool`
15. `increment_website_view(int $id): bool`
16. `increment_mail_view(int $id): bool`
17. `increment_wp_view(int $id): bool`
18. `set_package(int $id, ?int $packageTypeId, ?string $startsAt, ?string $expiresAt): bool`
19. `attach_owner(int $id, int $userId): bool`

### 11) Institution_image_model (`institution_images`)

1. `find(int $id): ?array`
2. `create(array $data): int|false`
3. `update(int $id, array $data): bool`
4. `delete(int $id): bool`
5. `list_by_institution(int $institutionId): array`
6. `set_main(int $institutionId, int $imageId): bool`
7. `clear_main(int $institutionId): bool`
8. `reorder(int $institutionId, array $imageIdsInOrder): bool`
9. `count_by_institution(int $institutionId): int`
10. `delete_by_institution(int $institutionId): bool`

### 12) Institution_application_model (`institution_applications`)

1. `find(int $id): ?array`
2. `create(array $data): int|false`
3. `update(int $id, array $data): bool`
4. `delete(int $id): bool`
5. `list(array $filters = [], int $limit = 20, int $offset = 0): array`
6. `count(array $filters = []): int`
7. `set_status(int $id, int $status): bool`
8. `assign_institution(int $id, ?int $institutionId): bool`

### 13) Institution_inquiry_model (`institution_inquiries`)

1. `find(int $id): ?array`
2. `create(array $data): int|false`
3. `update(int $id, array $data): bool`
4. `delete(int $id): bool`
5. `list(array $filters = [], int $limit = 20, int $offset = 0): array`
6. `list_by_institution(int $institutionId, int $limit = 20, int $offset = 0): array`
7. `count(array $filters = []): int`
8. `set_status(int $id, int $status): bool`

### 14) Institution_faq_answer_model (`institution_faq_answers`)

1. `find(int $id): ?array`
2. `find_by_institution_and_faq(int $institutionId, int $faqId): ?array`
3. `create(array $data): int|false`
4. `update(int $id, array $data): bool`
5. `delete(int $id): bool`
6. `upsert_answer(int $institutionId, int $faqId, ?string $pendingAnswer): bool`
7. `approve(int $id, ?string $approvedAnswer = null): bool`
8. `reject(int $id): bool`
9. `list(array $filters = [], int $limit = 20, int $offset = 0): array`
10. `list_by_institution(int $institutionId): array`
11. `list_pending(int $limit = 20, int $offset = 0): array`

### 15) Institution_attribute_model (`institution_attributes`)

1. `attach(int $institutionId, int $attributeId): bool`
2. `detach(int $institutionId, int $attributeId): bool`
3. `sync(int $institutionId, array $attributeIds): bool`
4. `list_by_institution(int $institutionId): array`
5. `exists(int $institutionId, int $attributeId): bool`
6. `delete_by_institution(int $institutionId): bool`

## Servisler ve Method Kapsamı

### 1) Auth_service

1. `register(array $payload): array`
2. `login(string $email, string $password): array`
3. `attempt(array $credentials): array`
4. `logout(): void`
5. `is_logged_in(): bool`
6. `current_user_id(): ?int`
7. `current_user(): ?array`
8. `require_auth(): void`
9. `guest_only(): void`
10. `refresh_session_user(): void`
11. `set_remember_me(int $userId): string`
12. `login_with_remember_token(string $token): array`
13. `clear_remember_me(int $userId): void`
14. `create_reset_token(string $email): array`
15. `reset_password(string $token, string $newPassword): array`

### 2) User_service

1. `create(array $data): int|false`
2. `update(int $id, array $data): bool`
3. `delete(int $id): bool`
4. `find(int $id): ?array`
5. `find_by_email(string $email): ?array`
6. `find_by_reset_token(string $token): ?array`
7. `exists_by_email(string $email, ?int $excludeId = null): bool`
8. `list(array $filters = [], int $limit = 20, int $offset = 0): array`
9. `count(array $filters = []): int`
10. `set_password(int $id, string $plainPassword): bool`
11. `verify_password(string $plainPassword, string $passwordHash): bool`
12. `set_status(int $id, int $status): bool`
13. `set_role(int $id, int $role): bool`
14. `set_email_verified_at(int $id, ?string $datetime): bool`
15. `set_reset_token(int $id, ?string $token, ?string $expiresAt): bool`
16. `set_remember_token(int $id, ?string $token): bool`

### 3) Institution_service

1. `create(array $data): int|false`
2. `update(int $id, array $data): bool`
3. `delete(int $id): bool`
4. `find(int $id): ?array`
5. `find_active_by_id(int $id): ?array`
6. `find_by_slug(string $slug): ?array`
7. `list(array $filters = [], int $limit = 20, int $offset = 0): array`
8. `search(array $filters = []): array`
9. `count(array $filters = []): int`
10. `get_filtered_list(array $filters = []): array`
11. `get_detail(int $id): array`
12. `prepare_for_insert(array $postData): array`
13. `set_status(int $id, int $status): bool`
14. `increment_view(int $id): bool`
15. `increment_address_view(int $id): bool`
16. `increment_phone_view(int $id): bool`
17. `increment_website_view(int $id): bool`
18. `increment_mail_view(int $id): bool`
19. `increment_wp_view(int $id): bool`
20. `attach_owner(int $institutionId, int $userId): bool`
21. `set_package(int $institutionId, ?int $packageTypeId, ?string $startsAt, ?string $expiresAt): bool`
22. `normalize_contact_fields(array $data): array`
23. `validate_price_range(?int $min, ?int $max): bool`

### 4) Category_service

1. `create(array $data): int|false`
2. `update(int $id, array $data): bool`
3. `delete(int $id): bool`
4. `find(int $id): ?array`
5. `find_by_slug(string $slug): ?array`
6. `list(array $filters = []): array`
7. `count(array $filters = []): int`
8. `exists_by_slug(string $slug, ?int $excludeId = null): bool`
9. `ensure_slug(string $categoryName, ?int $excludeId = null): string`

### 5) Sub_category_service

1. `create(array $data): int|false`
2. `update(int $id, array $data): bool`
3. `delete(int $id): bool`
4. `find(int $id): ?array`
5. `find_by_slug(string $slug): ?array`
6. `list(array $filters = []): array`
7. `list_by_category(int $categoryId): array`
8. `count(array $filters = []): int`
9. `exists_by_slug(string $slug, ?int $excludeId = null): bool`
10. `ensure_slug(string $name, ?int $excludeId = null): string`

### 6) City_service

1. `create(array $data): int|false`
2. `update(int $id, array $data): bool`
3. `delete(int $id): bool`
4. `find(int $id): ?array`
5. `find_by_slug(string $slug): ?array`
6. `list(array $filters = []): array`
7. `exists_by_slug(string $slug, ?int $excludeId = null): bool`
8. `ensure_slug(string $cityName, ?int $excludeId = null): string`

### 7) District_service

1. `create(array $data): int|false`
2. `update(int $id, array $data): bool`
3. `delete(int $id): bool`
4. `find(int $id): ?array`
5. `find_by_city_and_slug(int $cityId, string $slug): ?array`
6. `list(array $filters = []): array`
7. `list_by_city(int $cityId): array`
8. `exists_city_slug(int $cityId, string $slug, ?int $excludeId = null): bool`
9. `ensure_slug(string $districtName, int $cityId, ?int $excludeId = null): string`

### 8) Package_type_service

1. `create(array $data): int|false`
2. `update(int $id, array $data): bool`
3. `delete(int $id): bool`
4. `find(int $id): ?array`
5. `find_by_name(string $name): ?array`
6. `list(array $filters = []): array`
7. `list_active(): array`
8. `set_status(int $id, int $status): bool`
9. `calculate_expiry_date(string $startsAt, int $durationDays): string`
10. `can_upload_images(int $packageTypeId, int $currentCount): bool`
11. `max_images(int $packageTypeId): int`
12. `max_institution(int $packageTypeId): int`

### 9) Attribute_service

1. `create(array $data): int|false`
2. `update(int $id, array $data): bool`
3. `delete(int $id): bool`
4. `find(int $id): ?array`
5. `list(array $filters = []): array`
6. `find_many(array $ids): array`

### 10) Faq_service

1. `create(array $data): int|false`
2. `update(int $id, array $data): bool`
3. `delete(int $id): bool`
4. `find(int $id): ?array`
5. `list(array $filters = []): array`
6. `list_active(): array`
7. `list_by_attribute(int $attributeId): array`
8. `set_status(int $id, int $status): bool`

### 11) Category_attribute_service

1. `attach(int $categoryId, int $attributeId): bool`
2. `detach(int $categoryId, int $attributeId): bool`
3. `sync(int $categoryId, array $attributeIds): bool`
4. `list_by_category(int $categoryId): array`
5. `list_by_attribute(int $attributeId): array`
6. `exists(int $categoryId, int $attributeId): bool`
7. `delete_by_category(int $categoryId): bool`

### 12) Institution_attribute_service

1. `attach(int $institutionId, int $attributeId): bool`
2. `detach(int $institutionId, int $attributeId): bool`
3. `sync(int $institutionId, array $attributeIds): bool`
4. `list_by_institution(int $institutionId): array`
5. `exists(int $institutionId, int $attributeId): bool`
6. `delete_by_institution(int $institutionId): bool`

### 13) Institution_image_service

1. `create(array $data): int|false`
2. `update(int $id, array $data): bool`
3. `delete(int $id): bool`
4. `find(int $id): ?array`
5. `list_by_institution(int $institutionId): array`
6. `set_main(int $institutionId, int $imageId): bool`
7. `clear_main(int $institutionId): bool`
8. `reorder(int $institutionId, array $imageIdsInOrder): bool`
9. `count_by_institution(int $institutionId): int`
10. `delete_by_institution(int $institutionId): bool`

### 14) Institution_application_service

1. `create(array $data): int|false`
2. `update(int $id, array $data): bool`
3. `delete(int $id): bool`
4. `find(int $id): ?array`
5. `list(array $filters = [], int $limit = 20, int $offset = 0): array`
6. `count(array $filters = []): int`
7. `set_status(int $id, int $status): bool`
8. `assign_institution(int $id, ?int $institutionId): bool`

### 15) Institution_inquiry_service

1. `create(array $data): int|false`
2. `update(int $id, array $data): bool`
3. `delete(int $id): bool`
4. `find(int $id): ?array`
5. `list(array $filters = [], int $limit = 20, int $offset = 0): array`
6. `list_by_institution(int $institutionId, int $limit = 20, int $offset = 0): array`
7. `count(array $filters = []): int`
8. `set_status(int $id, int $status): bool`

### 16) Institution_faq_answer_service

1. `create(array $data): int|false`
2. `update(int $id, array $data): bool`
3. `delete(int $id): bool`
4. `find(int $id): ?array`
5. `find_by_institution_and_faq(int $institutionId, int $faqId): ?array`
6. `upsert_answer(int $institutionId, int $faqId, ?string $pendingAnswer): bool`
7. `approve(int $id, ?string $approvedAnswer = null): bool`
8. `reject(int $id): bool`
9. `list(array $filters = [], int $limit = 20, int $offset = 0): array`
10. `list_by_institution(int $institutionId): array`
11. `list_pending(int $limit = 20, int $offset = 0): array`

## Controller ve Method Kapsamı

Controller katmanında kural:

1. Controller iş kuralı yazmaz, sadece akış yönetir.
2. Web ve API controller aynı service katmanını kullanır.
3. Web controller `view`, API controller `JSON` döner.

### 1) Web Auth Controller (`application/controllers/web/Auth.php`)

1. `login()` - GET form göster, POST ise `Auth_service->login`.
2. `register()` - GET form göster, POST ise `Auth_service->register`.
3. `logout()` - `Auth_service->logout` çağır ve yönlendir.
4. `forgot_password()` - reset token sürecini başlat.
5. `reset_password($token)` - token ile şifre yenile.

### 2) API Auth Controller (`application/controllers/api/v1/Auth.php`)

1. `login()` - JSON credentials al, token/session üret.
2. `register()` - JSON kayıt al, kullanıcı oluştur.
3. `logout()` - aktif oturumu kapat.
4. `forgot_password()` - reset token oluştur.
5. `reset_password()` - token ile şifre yenile.

### 3) Web Categories Controller (`application/controllers/web/Categories.php`)

1. `index()` - kategori listesi.
2. `show($id)` - tek kategori detayı.
3. `create()` - kategori oluşturma formu + submit.
4. `update($id)` - kategori güncelleme.
5. `delete($id)` - kategori silme.

### 4) API Categories Controller (`application/controllers/api/v1/Categories.php`)

1. `index()` - kategori listesi (JSON).
2. `show($id)` - kategori detayı.
3. `store()` - kategori ekleme.
4. `update($id)` - kategori güncelleme.
5. `destroy($id)` - kategori silme.

### 5) Web Sub Categories Controller (`application/controllers/web/Sub_categories.php`)

1. `index()` - alt kategori listesi.
2. `show($id)` - alt kategori detayı.
3. `create()` - oluşturma.
4. `update($id)` - güncelleme.
5. `delete($id)` - silme.
6. `by_category($categoryId)` - kategoriye göre alt kategori.

### 6) API Sub Categories Controller (`application/controllers/api/v1/Sub_categories.php`)

1. `index()` - liste.
2. `show($id)` - detay.
3. `store()` - oluştur.
4. `update($id)` - güncelle.
5. `destroy($id)` - sil.
6. `by_category($categoryId)` - kategoriye göre filtre.

### 7) Web Cities Controller (`application/controllers/web/Cities.php`)

1. `index()` - şehir listesi.
2. `show($id)` - şehir detayı.
3. `create()` - oluşturma.
4. `update($id)` - güncelleme.
5. `delete($id)` - silme.

### 8) API Cities Controller (`application/controllers/api/v1/Cities.php`)

1. `index()` - liste.
2. `show($id)` - detay.
3. `store()` - oluştur.
4. `update($id)` - güncelle.
5. `destroy($id)` - sil.

### 9) Web Districts Controller (`application/controllers/web/Districts.php`)

1. `index()` - ilçe listesi.
2. `show($id)` - ilçe detayı.
3. `create()` - oluşturma.
4. `update($id)` - güncelleme.
5. `delete($id)` - silme.
6. `by_city($cityId)` - şehre göre ilçe listesi.

### 10) API Districts Controller (`application/controllers/api/v1/Districts.php`)

1. `index()` - liste.
2. `show($id)` - detay.
3. `store()` - oluştur.
4. `update($id)` - güncelle.
5. `destroy($id)` - sil.
6. `by_city($cityId)` - şehre göre filtre.

### 11) Web Package Types Controller (`application/controllers/web/Package_types.php`)

1. `index()` - paket listesi.
2. `show($id)` - paket detayı.
3. `create()` - oluşturma.
4. `update($id)` - güncelleme.
5. `delete($id)` - silme.
6. `set_status($id)` - aktif/pasif.

### 12) API Package Types Controller (`application/controllers/api/v1/Package_types.php`)

1. `index()` - liste.
2. `show($id)` - detay.
3. `store()` - oluştur.
4. `update($id)` - güncelle.
5. `destroy($id)` - sil.
6. `set_status($id)` - durum değiştir.

### 13) Web Attributes Controller (`application/controllers/web/Attributes.php`)

1. `index()` - özellik listesi.
2. `show($id)` - detay.
3. `create()` - oluşturma.
4. `update($id)` - güncelleme.
5. `delete($id)` - silme.

### 14) API Attributes Controller (`application/controllers/api/v1/Attributes.php`)

1. `index()` - liste.
2. `show($id)` - detay.
3. `store()` - oluştur.
4. `update($id)` - güncelle.
5. `destroy($id)` - sil.

### 15) Web Faqs Controller (`application/controllers/web/Faqs.php`)

1. `index()` - SSS listesi.
2. `show($id)` - detay.
3. `create()` - oluşturma.
4. `update($id)` - güncelleme.
5. `delete($id)` - silme.
6. `by_attribute($attributeId)` - attribute filtreli liste.

### 16) API Faqs Controller (`application/controllers/api/v1/Faqs.php`)

1. `index()` - liste.
2. `show($id)` - detay.
3. `store()` - oluştur.
4. `update($id)` - güncelle.
5. `destroy($id)` - sil.
6. `by_attribute($attributeId)` - filtreli liste.

### 17) Web Institutions Controller (`application/controllers/web/Institutions.php`)

1. `index()` - kurum listeleme/filtreleme.
2. `show($id)` - kurum detay sayfası.
3. `create()` - kurum oluşturma.
4. `update($id)` - kurum güncelleme.
5. `delete($id)` - kurum silme.
6. `increment_view($id)` - görüntülenme tetikleme (opsiyonel endpoint).

### 18) API Institutions Controller (`application/controllers/api/v1/Institutions.php`)

1. `index()` - kurum listesi (filtreli).
2. `show($id)` - kurum detayı.
3. `store()` - kurum oluştur.
4. `update($id)` - kurum güncelle.
5. `destroy($id)` - kurum sil.
6. `increment_view($id)` - sayaç güncelle.

### 19) Web Institution Images Controller (`application/controllers/web/Institution_images.php`)

1. `index($institutionId)` - kurum görselleri.
2. `upload($institutionId)` - görsel ekleme.
3. `set_main($institutionId, $imageId)` - ana görsel seçimi.
4. `reorder($institutionId)` - sıralama güncelleme.
5. `delete($institutionId, $imageId)` - görsel silme.

### 20) API Institution Images Controller (`application/controllers/api/v1/Institution_images.php`)

1. `index($institutionId)` - liste.
2. `store($institutionId)` - ekleme.
3. `set_main($institutionId, $imageId)` - ana görsel.
4. `reorder($institutionId)` - sıralama.
5. `destroy($institutionId, $imageId)` - sil.

### 21) Web Institution Applications Controller (`application/controllers/web/Institution_applications.php`)

1. `index()` - başvuru listesi.
2. `show($id)` - başvuru detayı.
3. `create()` - başvuru formu/submit.
4. `set_status($id)` - durum güncelle.
5. `assign_institution($id)` - kuruma bağla.

### 22) API Institution Applications Controller (`application/controllers/api/v1/Institution_applications.php`)

1. `index()` - liste.
2. `show($id)` - detay.
3. `store()` - oluştur.
4. `update($id)` - güncelle.
5. `set_status($id)` - durum değiştir.
6. `assign_institution($id)` - bağlama.

### 23) Web Institution Inquiries Controller (`application/controllers/web/Institution_inquiries.php`)

1. `index()` - gelen mesajlar listesi.
2. `show($id)` - mesaj detayı.
3. `create()` - kurum iletişim formu.
4. `set_status($id)` - okundu/işlendi durumu.
5. `delete($id)` - kayıt silme.

### 24) API Institution Inquiries Controller (`application/controllers/api/v1/Institution_inquiries.php`)

1. `index()` - liste.
2. `show($id)` - detay.
3. `store()` - oluştur.
4. `set_status($id)` - durum güncelle.
5. `destroy($id)` - sil.

### 25) Web Institution Faq Answers Controller (`application/controllers/web/Institution_faq_answers.php`)

1. `index($institutionId)` - cevap listesi.
2. `upsert($institutionId, $faqId)` - cevap ekle/güncelle.
3. `approve($id)` - onay.
4. `reject($id)` - red.
5. `delete($id)` - sil.

### 26) API Institution Faq Answers Controller (`application/controllers/api/v1/Institution_faq_answers.php`)

1. `index($institutionId)` - liste.
2. `upsert($institutionId, $faqId)` - ekle/güncelle.
3. `approve($id)` - onay.
4. `reject($id)` - red.
5. `destroy($id)` - sil.

### 27) Web Home Controller (`application/controllers/web/Home.php`)

1. `index()` - ana sayfa kurum listeleme/arama.

### 28) API Health/Meta Controller (opsiyonel)

1. `status()` - API canlılık kontrolü.
2. `version()` - API sürüm bilgisi.

## Uygulama Sırası

1. Faz 1: `Auth_service`, `User_service`, `Institution_service`
2. Faz 2: `Category_service`, `Sub_category_service`, `City_service`, `District_service`
3. Faz 3: `Package_type_service`, `Attribute_service`, `Faq_service`, `Category_attribute_service`
4. Faz 4: `Institution_attribute_service`, `Institution_image_service`, `Institution_application_service`, `Institution_inquiry_service`, `Institution_faq_answer_service`

## Geliştirme Akış Sırası (Service mi, Controller mı, Model mi, API mi?)

Kısa cevap: Önce `Model`, sonra `Service`, sonra `Controller` (Web/API).

Önerilen standart sıra:

1. Veritabanı ve migration netleştirme
- Tablo/alan/FK yapısı kesinleşir.

2. Model yazımı
- Ham query ve CRUD burada yazılır.
- Model, iş kuralı değil veri erişimi yapar.

3. Service yazımı
- İş kuralları, validasyon, dönüşüm, akış kontrolü burada kurulur.
- Birden fazla modeli burada birleştirebilirsin.

4. Controller yazımı
- Service çağırır, request alır, response/view döner.
- Web controller: HTML sayfa akışı.
- API controller: JSON response akışı.

5. Route ve middleware benzeri kontroller
- Auth gereken endpointlerde kontrol.
- Yetki/rol kontrolleri.

6. Test ve doğrulama
- En azından happy-path + validation + hata akışları doğrulanır.

## Feature Bazlı Uygulama Checklist

Her yeni özellikte şu sırayı takip et:

1. Migration/table hazır mı?
2. İlgili model methodları yazıldı mı?
3. Service methodu yazıldı mı?
4. Web controller endpointi yazıldı mı?
5. API controller endpointi gerekiyorsa yazıldı mı?
6. Route eklendi mi?
7. Başarılı/hatalı senaryolar test edildi mi?

## Örnek: Login Özelliği

1. `users` tablosu hazır.
2. `User_model`: `find_by_email`.
3. `Auth_service`: `login`, `logout`, `is_logged_in`.
4. Web `Auth` controller: form + submit + yönlendirme.
5. API login endpointi gerekiyorsa `api/v1/Auth`.
6. Route ekleme.
7. Yanlış şifre, pasif kullanıcı, başarılı giriş testleri.

## Servis Bazlı Net Uygulama Sırası (Model -> Service -> Controller)

Bu bölümde her domain için aynı şablon uygulanır. Amaç, ekipte herkesin aynı sırayla ilerlemesini sağlamaktır.

Genel şablon:

1. `application/models/X_model.php`
2. `application/libraries/services/X_service.php`
3. `application/controllers/web/X.php`
4. `application/controllers/api/v1/X.php` (ihtiyaç varsa)

### 1) User + Auth (ilk faz, zorunlu)

1. Model
- `User_model`:
- `find($id)`
- `find_by_email($email)`
- `create($data)`
- `update($id, $data)`
- `set_reset_token(...)`
- `set_remember_token(...)`

2. Service
- `User_service`:
- kullanıcı CRUD ve şifre işlemleri
- `Auth_service`:
- `register`, `login`, `logout`, `is_logged_in`, `current_user`

3. Controller (Web)
- `web/Auth.php`:
- `login`, `register`, `logout`
- form validasyon + service çağrısı + redirect

4. Controller (API)
- `api/v1/Auth.php` (gerekiyorsa):
- `login`, `register`, `logout`
- JSON response standardı

### 2) Category

1. Model: `Category_model` (`find`, `find_by_slug`, `list`, `create`, `update`, `delete`)
2. Service: `Category_service` (slug üretimi, tekrar kontrolü, validasyon)
3. Web Controller: `web/Categories.php` (liste, oluştur, güncelle)
4. API Controller: `api/v1/Categories.php` (liste/detay)

### 3) Sub_category

1. Model: `Sub_category_model` (`list_by_category`, CRUD)
2. Service: `Sub_category_service` (kategori bağlı kural kontrolleri)
3. Web Controller: `web/Sub_categories.php`
4. API Controller: `api/v1/Sub_categories.php`

### 4) City + District

1. Model:
- `City_model`
- `District_model` (`list_by_city`, `find_by_city_and_slug`)

2. Service:
- `City_service`
- `District_service` (city+slug unique kontrolü)

3. Web Controller:
- `web/Cities.php`
- `web/Districts.php`

4. API Controller:
- `api/v1/Cities.php`
- `api/v1/Districts.php`

### 5) Package_type

1. Model: `Package_type_model`
2. Service: `Package_type_service` (paket süresi, max görsel, max kurum kuralları)
3. Web Controller: `web/Package_types.php`
4. API Controller: `api/v1/Package_types.php`

### 6) Attribute + Faq + Category_attribute

1. Model:
- `Attribute_model`
- `Faq_model`
- `Category_attribute_model`

2. Service:
- `Attribute_service`
- `Faq_service`
- `Category_attribute_service` (`attach`, `detach`, `sync`)

3. Web Controller:
- `web/Attributes.php`
- `web/Faqs.php`
- `web/Category_attributes.php`

4. API Controller:
- `api/v1/Attributes.php`
- `api/v1/Faqs.php`

### 7) Institution (ana domain)

1. Model:
- `Institution_model` (search, detail, CRUD, count)

2. Service:
- `Institution_service` (filtreleme, detay dönüşümü, görüntülenme sayaçları, package bağlama)

3. Web Controller:
- `web/Institutions.php`
- `web/Home.php` (liste/arama)

4. API Controller:
- `api/v1/Institutions.php`

### 8) Institution ilişkili alt domainler

1. Model:
- `Institution_attribute_model`
- `Institution_image_model`
- `Institution_application_model`
- `Institution_inquiry_model`
- `Institution_faq_answer_model`

2. Service:
- `Institution_attribute_service`
- `Institution_image_service`
- `Institution_application_service`
- `Institution_inquiry_service`
- `Institution_faq_answer_service`

3. Web Controller:
- `web/Institution_attributes.php`
- `web/Institution_images.php`
- `web/Institution_applications.php`
- `web/Institution_inquiries.php`
- `web/Institution_faq_answers.php`

4. API Controller:
- `api/v1/Institution_images.php`
- `api/v1/Institution_inquiries.php`
- `api/v1/Institution_applications.php`
- `api/v1/Institution_faq_answers.php`

## Her Domain İçin "Bitti" Kriteri

Bir domain tamamlandı sayılması için:

1. Model methodları çalışıyor.
2. Service methodları validasyon + iş kuralını uyguluyor.
3. Web controller akışı çalışıyor.
4. API controller (varsa) doğru HTTP kodu ve JSON dönüyor.
5. Route tanımları tamam.
6. Başarılı ve hatalı senaryolar test edildi.

## View Katmanı Kapsamı

Projenin ön yüzünü (HTML/CSS/JS) oluşturan View dosyaları `application/views/` klasörü altında modüler olarak organize edilir. Controller katmanından gönderilen veriler (data) burada ekrana basılır. İş kuralları veya veritabanı sorguları View içinde kesinlikle yer almaz.

### 1) Layouts ve Ortak Parçalar (Partials)
1. `layouts/main.php` - Sitenin genel HTML iskeleti (`<head>`, `<body>`).
2. `layouts/admin.php` - Admin paneli genel iskeleti.
3. `layouts/panel.php` - Kurum sahibi/Kullanıcı paneli genel iskeleti.
4. `partials/navbar.php` - Üst navigasyon menüsü, arama çubuğu ve kullanıcı butonları.
5. `partials/footer.php` - Sayfa altı linkler ve iletişim bilgileri.
6. `partials/admin_sidebar.php` - Admin paneli sol menüsü.
7. `partials/panel_sidebar.php` - Kullanıcı paneli sol menüsü.

### 2) Auth (Kullanıcı Giriş ve Sistem Kayıt)
1. `auth/login.php` - Sisteme giriş formu.
2. `auth/register.php` - Yeni kullanıcı kayıt formu.
3. `auth/forgot_password.php` - Şifre sıfırlama talep formu.
4. `auth/reset_password.php` - Yeni şifre belirleme ekranı.

### 3) Home (Ana Sayfa)
1. `home/index.php` - Karşılama, arama motoru, kategoriler ve öne çıkan kurumlar.

### 4) Institutions (Ziyaretçi Arayüzü - Kurum Keşfi)
1. `institutions/index.php` - Kurum listeleme, detaylı filtreleme (sol menü) ve sayfalama.
2. `institutions/show.php` - Kurum detay sayfası (galeri, harita, özellikler, SSS, iletişim).

### 5) Panel (Kullanıcı/Kurum Sahibi İşlemleri)
1. `panel/dashboard.php` - Kullanıcı özet ekranı.
2. `panel/institutions/index.php` - Sahip olunan kurumlar listesi.
3. `panel/institutions/create.php` - Yeni kurum ekleme (çok adımlı form).
4. `panel/institutions/edit.php` - Kurum bilgilerini güncelleme formu.
5. `panel/institution_images/index.php` - Kurum fotoğraf galerisi yönetimi (yükleme, sıralama, ana resim).
6. `panel/institution_inquiries/index.php` - Kuruma gelen mesajlar/talepler listesi.
7. `panel/institution_faq_answers/index.php` - Kuruma özel SSS soru ve cevaplarını yönetme.

### 6) Admin (Sistem Yönetimi ve Moderasyon)
1. `admin/dashboard.php` - Genel platform istatistikleri ve özet.
2. `admin/users/index.php` - Üye yönetimi listesi.
3. `admin/institutions/index.php` - Platformdaki tüm kurumların yönetimi ve onay statüleri.
4. `admin/institution_applications/index.php` - Kurum sahiplenme veya yeni kayıt başvuruları onay ekranı.
5. `admin/institution_applications/show.php` - Başvuru detaylarını inceleme ekranı.

### 7) Admin (Referans Verileri - CRUD Ekranları)
Controllerlarda tanımlı olan sistem yönetimi ayarları:
1. `admin/categories/` - `index.php`, `form.php` (Kategori yönetimi)
2. `admin/sub_categories/` - `index.php`, `form.php` (Alt kategori yönetimi)
3. `admin/cities/` - `index.php`, `form.php` (Şehir yönetimi)
4. `admin/districts/` - `index.php`, `form.php` (İlçe yönetimi)
5. `admin/package_types/` - `index.php`, `form.php` (Paket ve üyelik tipleri yönetimi)
6. `admin/attributes/` - `index.php`, `form.php` (Kurum özellikleri yönetimi - örn: Havuz, Yemek)
7. `admin/faqs/` - `index.php`, `form.php` (Genel sıkça sorulan sorular yönetimi)
