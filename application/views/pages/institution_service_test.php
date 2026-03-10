<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo html_escape(isset($title) ? $title : 'Institution Service Test'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #1f2937; background: #f8fafc; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { margin: 0 0 8px; }
        h2 { margin: 24px 0 8px; font-size: 18px; }
        .muted { color: #6b7280; margin-bottom: 16px; }
        .grid { display: grid; gap: 12px; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); }
        .card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; background: #fff; }
        .thumb { width: 100%; height: 160px; object-fit: cover; border-radius: 6px; background: #f3f4f6; margin-bottom: 10px; }
        .filters { display: grid; gap: 8px; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; }
        .filters input, .filters select { padding: 8px; }
        .filters button { padding: 8px 12px; }
        .pill { display: inline-block; padding: 3px 8px; background: #eef2ff; border-radius: 999px; font-size: 12px; }
        .empty { padding: 12px; background: #fff; border: 1px dashed #d1d5db; border-radius: 8px; }
        .meta { font-size: 13px; color: #4b5563; }
        .nav { margin-top: 24px; }
        .row { display: flex; gap: 12px; flex-wrap: wrap; }
        .row .card { flex: 1 1 280px; }
        .detail { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; }
        .code { background: #0f172a; color: #e2e8f0; padding: 8px; border-radius: 6px; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace; font-size: 12px; overflow: auto; }
    </style>
</head>
<body>
<div class="container">
    <h1><?php echo html_escape(isset($title) ? $title : 'Institution Service Test'); ?></h1>
    <p class="muted">Bu sayfa Institution_service metodlarini hizli test etmek icin olusturuldu.</p>

    <div class="row">
        <div class="card">
            <h2>Aktif Kurum Sayisi</h2>
            <div class="pill"><?php echo (int) (isset($active_count) ? $active_count : 0); ?></div>
        </div>
        <div class="card">
            <h2>Premium Limit</h2>
            <div class="pill"><?php echo (int) (isset($premium_limit) ? $premium_limit : 0); ?></div>
        </div>
        <div class="card">
            <h2>Filtreli Toplam</h2>
            <div class="pill"><?php echo (int) (isset($filtered_total) ? $filtered_total : 0); ?></div>
        </div>
    </div>

    <h2>Filtreler</h2>
    <form method="get" class="filters">
        <input type="text" name="q" placeholder="Anahtar kelime" value="<?php echo html_escape(isset($filters['q']) ? $filters['q'] : ''); ?>">
        <input type="number" name="city_id" placeholder="Sehir ID" value="<?php echo html_escape(isset($filters['city_id']) ? $filters['city_id'] : ''); ?>">
        <input type="number" name="district_id" placeholder="Ilce ID" value="<?php echo html_escape(isset($filters['district_id']) ? $filters['district_id'] : ''); ?>">
        <input type="number" name="category_id" placeholder="Kategori ID" value="<?php echo html_escape(isset($filters['category_id']) ? $filters['category_id'] : ''); ?>">
        <input type="number" name="sub_category_id" placeholder="Alt Kategori ID" value="<?php echo html_escape(isset($filters['sub_category_id']) ? $filters['sub_category_id'] : ''); ?>">
        <input type="text" name="sort" placeholder="Siralama (priority, name...)" value="<?php echo html_escape(isset($filters['sort']) ? $filters['sort'] : ''); ?>">
        <input type="number" name="per_page" placeholder="Sayfa basina" value="<?php echo html_escape(isset($per_page) ? $per_page : 10); ?>">
        <input type="number" name="page" placeholder="Sayfa no" value="<?php echo html_escape(isset($page) ? $page : 1); ?>">
        <input type="number" name="premium_limit" placeholder="Premium limit" value="<?php echo html_escape(isset($premium_limit) ? $premium_limit : 6); ?>">
        <input type="number" name="detail_id" placeholder="Detay ID" value="<?php echo html_escape(isset($detail_id) ? $detail_id : ''); ?>">
        <button type="submit">Test Et</button>
    </form>

    <h2>Premium Kurumlar</h2>
    <?php if (!empty($premium)): ?>
        <section class="grid">
            <?php foreach ($premium as $item): ?>
                <article class="card">
                    <img class="thumb" src="<?php echo html_escape(isset($item['thumbnail']) ? $item['thumbnail'] : ''); ?>" alt="<?php echo html_escape(isset($item['name']) ? $item['name'] : 'Kurum'); ?>">
                    <div><strong><?php echo html_escape(isset($item['display_name']) ? $item['display_name'] : (isset($item['name']) ? $item['name'] : 'Kurum')); ?></strong></div>
                    <div class="meta">ID: <?php echo (int) (isset($item['id']) ? $item['id'] : 0); ?></div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php else: ?>
        <div class="empty">Premium kurum bulunamadi.</div>
    <?php endif; ?>

    <h2>Filtreli Liste (get_filtered_list)</h2>
    <?php if (!empty($filtered_list)): ?>
        <section class="grid">
            <?php foreach ($filtered_list as $item): ?>
                <article class="card">
                    <img class="thumb" src="<?php echo html_escape(isset($item['thumbnail']) ? $item['thumbnail'] : ''); ?>" alt="<?php echo html_escape(isset($item['name']) ? $item['name'] : 'Kurum'); ?>">
                    <div><strong><?php echo html_escape(isset($item['display_name']) ? $item['display_name'] : (isset($item['name']) ? $item['name'] : 'Kurum')); ?></strong></div>
                    <div class="meta">ID: <?php echo (int) (isset($item['id']) ? $item['id'] : 0); ?></div>
                    <?php if (!empty($item['address'])): ?>
                        <div class="meta">Adres: <?php echo html_escape($item['address']); ?></div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    <?php else: ?>
        <div class="empty">Filtreye uygun kurum bulunamadi.</div>
    <?php endif; ?>

    <h2>Sayfali Liste (get_paginated_list)</h2>
    <?php if (!empty($paginated) && !empty($paginated['items'])): ?>
        <div class="muted">
            Toplam: <?php echo (int) $paginated['total']; ?> |
            Sayfa: <?php echo (int) $paginated['current_page']; ?> /
            <?php echo (int) $paginated['total_pages']; ?> |
            Sayfa basina: <?php echo (int) $paginated['per_page']; ?>
        </div>
        <section class="grid">
            <?php foreach ($paginated['items'] as $item): ?>
                <article class="card">
                    <img class="thumb" src="<?php echo html_escape(isset($item['thumbnail']) ? $item['thumbnail'] : ''); ?>" alt="<?php echo html_escape(isset($item['name']) ? $item['name'] : 'Kurum'); ?>">
                    <div><strong><?php echo html_escape(isset($item['display_name']) ? $item['display_name'] : (isset($item['name']) ? $item['name'] : 'Kurum')); ?></strong></div>
                    <div class="meta">ID: <?php echo (int) (isset($item['id']) ? $item['id'] : 0); ?></div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php else: ?>
        <div class="empty">Sayfali liste bos.</div>
    <?php endif; ?>

    <h2>Detay (get_detail)</h2>
    <?php if (!empty($detail)): ?>
        <div class="detail">
            <div class="meta">Success: <?php echo !empty($detail['success']) ? 'true' : 'false'; ?></div>
            <div class="meta">Message: <?php echo html_escape(isset($detail['message']) ? $detail['message'] : ''); ?></div>
            <div class="code"><?php echo html_escape(print_r($detail, TRUE)); ?></div>
        </div>
    <?php else: ?>
        <div class="empty">Detay testi icin "Detay ID" girip yeniden gonderin.</div>
    <?php endif; ?>

    <div class="nav">
        <a href="<?php echo site_url('web/home'); ?>">Ana sayfaya don</a>
    </div>
</div>
</body>
</html>
