<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo html_escape(isset($title) ? $title : 'Kurum Rehberi'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #1f2937; }
        .container { max-width: 980px; margin: 0 auto; }
        h1 { margin-bottom: 8px; }
        .muted { color: #6b7280; margin-bottom: 20px; }
        .filters { padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 20px; }
        .filters input { padding: 8px; margin-right: 8px; margin-bottom: 8px; }
        .filters button { padding: 8px 12px; }
        .list { display: grid; gap: 12px; }
        .card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; }
        .card h3 { margin: 0 0 6px; }
        .empty { padding: 16px; background: #f9fafb; border: 1px dashed #d1d5db; border-radius: 8px; }
        .actions { margin-top: 20px; }
    </style>
</head>
<body>
<div class="container">
    <h1><?php echo html_escape(isset($title) ? $title : 'Kurum Rehberi'); ?></h1>
    <p class="muted">Toplam <?php echo (int) (isset($total) ? $total : 0); ?> aktif kurum bulundu.</p>

    <form method="get" class="filters">
        <input type="text" name="q" placeholder="Kurum adı ara" value="<?php echo html_escape(isset($filters['q']) ? $filters['q'] : ''); ?>">
        <input type="number" name="city_id" placeholder="Şehir ID" value="<?php echo html_escape(isset($filters['city_id']) ? $filters['city_id'] : ''); ?>">
        <input type="number" name="sub_category_id" placeholder="Alt kategori ID" value="<?php echo html_escape(isset($filters['sub_category_id']) ? $filters['sub_category_id'] : ''); ?>">
        <button type="submit">Filtrele</button>
    </form>

    <?php if (!empty($institutions)): ?>
        <div class="list">
            <?php foreach ($institutions as $item): ?>
                <article class="card">
                    <h3><?php echo html_escape(isset($item['display_name']) ? $item['display_name'] : (isset($item['name']) ? $item['name'] : 'Kurum')); ?></h3>
                    <div>ID: <?php echo (int) (isset($item['id']) ? $item['id'] : 0); ?></div>
                    <?php if (!empty($item['address'])): ?>
                        <div>Adres: <?php echo html_escape($item['address']); ?></div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty">Filtreye uygun kurum bulunamadı.</div>
    <?php endif; ?>

    <div class="actions">
        <a href="<?php echo site_url('kurumlar'); ?>">Tüm kurumları gör</a>
    </div>
</div>
</body>
</html>
