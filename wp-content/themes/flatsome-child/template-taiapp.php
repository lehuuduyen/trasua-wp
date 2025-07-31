<?php
get_header();

$code = get_query_var('taiapp_code'); // Lấy mã từ URL
?>

<div class="wrap">
    <h1>Tải app với mã: <?php echo esc_html($code); ?></h1>
    <p>Link tải dành riêng cho bạn: <a href="https://example.com/app/<?php echo esc_attr($code); ?>">Tải ngay</a></p>
</div>

<?php
get_footer();
