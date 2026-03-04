<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$title?> | Kurum Rehberi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?=base_url('assets/css/style.css')?>">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-main">
            <div class="container">
                <a class="navbar-brand" href="<?=base_url()?>">
                    <img src="<?=base_url('assets/images/logo.png')?>" alt="Logo">
                </a>
                <button class="navbar-toggler border-0 px-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Menüyü aç">
                    <i class="bi bi-list text-white" style="font-size: 2.2rem;"></i>
                </button>
                <div class="offcanvas-lg offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                    <div class="offcanvas-header d-lg-none border-bottom">
                         <a class="navbar-brand m-0" href="<?=base_url()?>">
                            <img src="<?=base_url('assets/images/logo.png')?>" alt="Logo" style="max-height: 48px;">
                        </a>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#offcanvasNavbar" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <ul class="navbar-nav ms-auto align-items-lg-center">
                            <li class="nav-item">
                                <a class="nav-link" href="<?=base_url('istanbul')?>">
                                    <i class="bi bi-geo-alt"></i> İstanbul'da Okullar
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?=base_url('ankara')?>">
                                    <i class="bi bi-geo-alt"></i> Ankara'da Okullar
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?=base_url('izmir')?>">
                                    <i class="bi bi-geo-alt"></i> İzmir'de Okullar
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?=base_url('bilgi-al')?>">
                                    <i class="bi bi-info-circle"></i> Bilgi Al
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?=base_url('sehir-rehberi')?>">
                                    <i class="bi bi-book"></i> Şehir Rehberi
                                </a>
                            </li>
                            <li class="nav-item border-bottom d-lg-none"></li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#searchModal">
                                <i class="bi bi-search"></i> Okul Ara
                                </a>
                            </li>
                            <li class="nav-item border-bottom d-lg-none"></li>
                            <li class="nav-item d-none d-lg-block">
                                <span class="nav-globe"><i class="bi bi-globe2"></i></span>
                            </li>
                            <li class="nav-item d-lg-none mt-2">
                                 <div class="px-3 py-2">
                                     <label class="form-label text-dark fw-bold mb-1"><i class="bi bi-globe2"></i> Dil Seçin</label>
                                     <select class="form-select border-1" style="border-radius: 8px;">
                                         <option selected>Dil Seçin</option>
                                         <option value="tr">Türkçe</option>
                                         <option value="en">English</option>
                                     </select>
                                 </div>
                            </li>
                            <li class="nav-item border-bottom d-lg-none mt-2"></li>
                            <li class="nav-item ms-lg-2 mt-3 mt-lg-0">
                                <a class="btn-cta" href="<?=base_url('yurt-ekle')?>">
                                + Okulunu Listele / Yönet
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Search Modal -->
    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="searchModalLabel">Okul Ara</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <div class="modal-body pt-3 pb-4">
                    <form action="<?=base_url('arama')?>" method="GET">
                        <div class="mb-3">
                            <label for="searchQuery" class="form-label text-muted small mb-1">Okul Adı veya Kelime</label>
                            <input type="text" class="form-control form-control-lg" id="searchQuery" name="q" placeholder="Örn: Final Okulları..." style="font-size:0.95rem;">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label text-muted small mb-1">Şehir</label>
                                <select class="form-select" name="city">
                                    <option value="">Tüm Şehirler</option>
                                    <option value="34">İstanbul</option>
                                    <option value="6">Ankara</option>
                                    <option value="35">İzmir</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small mb-1">İlçe</label>
                                <select class="form-select" name="district">
                                    <option value="">Tüm İlçeler</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn w-100 py-2" style="background-color: var(--gold); color: var(--navy-dark); font-weight: 600;">Ara</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
