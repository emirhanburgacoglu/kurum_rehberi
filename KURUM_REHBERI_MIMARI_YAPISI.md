# 🏢 Kurum Rehberi - Güncel Klasör ve Mimari Yapısı

Bu doküman, "Kurum Rehberi" projesinin **fiziksel olarak şu anda var olan** dosya ve klasörlerini yansıtmaktadır. Proje çok katmanlı (N-Tier) web ve mobil API destekli şekilde geliştirilmektedir.

Aşağıdaki ağaç yapısı **doğrudan projenin mevcut durumunu** gösterir:

```text
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
```
