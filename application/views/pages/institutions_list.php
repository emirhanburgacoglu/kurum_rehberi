<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo html_escape(isset($title) ? $title : 'Kurumlar'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #1f2937; }
        .container { max-width: 1100px; margin: 0 auto; }
        h1 { margin-bottom: 8px; }
        .muted { color: #6b7280; margin-bottom: 20px; }
        .filters { display: flex; flex-wrap: wrap; gap: 8px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 20px; }
        .filters input { padding: 8px; min-width: 180px; }
        .filters button { padding: 8px 12px; }
        .list { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px; }
        .card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; background: #fff; }
        .thumb { width: 100%; height: 160px; object-fit: cover; border-radius: 6px; background: #f3f4f6; margin-bottom: 10px; }
        .title { margin: 0 0 8px; font-size: 18px; }
        .meta { font-size: 14px; color: #4b5563; }
        .empty { padding: 16px; background: #f9fafb; border: 1px dashed #d1d5db; border-radius: 8px; }
        .nav { margin-top: 20px; }
    </style>
</head>
<body>
<div class="container">
    <h1><?php echo html_escape(isset($title) ? $title : 'Kurumlar'); ?></h1>
    <p class="muted">Toplam <?php echo (int) (isset($total) ? $total : 0); ?> aktif kurum bulundu.</p>

    <form method="get" class="filters">
        <input type="text" name="q" placeholder="Kurum adı ara" value="<?php echo html_escape(isset($filters['q']) ? $filters['q'] : ''); ?>">
        <input type="number" name="city_id" placeholder="Şehir ID" value="<?php echo html_escape(isset($filters['city_id']) ? $filters['city_id'] : ''); ?>">
        <input type="number" name="sub_category_id" placeholder="Alt kategori ID" value="<?php echo html_escape(isset($filters['sub_category_id']) ? $filters['sub_category_id'] : ''); ?>">
        <button type="submit">Filtrele</button>
    </form>

    <?php if (!empty($institutions)): ?>
        <section class="list">
            <?php foreach ($institutions as $item): ?>
                <article class="card">
                    <img
                        class="thumb"
                        src="<?php echo html_escape(isset($item['thumbnail']) ? $item['thumbnail'] : ''); ?>"
                        alt="<?php echo html_escape(isset($item['name']) ? $item['name'] : 'Kurum'); ?>"
                    >
                    <h2 class="title"><?php echo html_escape(isset($item['display_name']) ? $item['display_name'] : (isset($item['name']) ? $item['name'] : 'Kurum')); ?></h2>
                    <div class="meta">ID: <?php echo (int) (isset($item['id']) ? $item['id'] : 0); ?></div>
                    <?php if (!empty($item['address'])): ?>
                        <div class="meta">Adres: <?php echo html_escape($item['address']); ?></div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    <?php else: ?>
        <div class="empty">Filtreye uygun kurum bulunamadı.</div>
    <?php endif; ?>

    <div class="nav">
        <a href="<?php echo site_url('web/home'); ?>">Ana sayfaya dön</a>
    </div>
</div>
</body>
</html>
