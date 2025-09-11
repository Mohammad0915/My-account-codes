<?php

// 1. Using hooks
// include('functions-woohooks.php');
// 2. Using template override
remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open');
remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close');
add_filter('woocommerce_enqueue_styles', '__return_false');

function mytheme_setup()
{
  add_theme_support('post-thumbnails');
  add_theme_support('title-tag');

  add_theme_support('custom-logo');

  add_theme_support('woocommerce');

  add_theme_support('wc-product-gallery-zoom');
  add_theme_support('wc-product-gallery-lightbox');
  add_theme_support('wc-product-gallery-slider');

  add_theme_support('woocommerce', array(
    'thumbnail_image_width' => 350,
    'single_image_width'    => 500,
  ));

  register_nav_menus(["Header" => "Header Menu"]);
}
add_action('after_setup_theme', 'mytheme_setup');

add_action('customize_register', function ($wp_customize) {
  // Section
  $wp_customize->add_section('hodcode_social_links', [
    'title'    => __('Social Media Links', 'hodcode'),
    'priority' => 30,
  ]);

  // Whatsapp
  $wp_customize->add_setting('hodcode_whatsapp', [
    'default'   => '',
    'transport' => 'refresh',
    'sanitize_callback' => 'esc_url_raw',
  ]);
  $wp_customize->add_control('hodcode_whatsapp', [
    'label'   => __('Whatsapp URL', 'hodcode'),
    'section' => 'hodcode_social_links',
    'type'    => 'url',
  ]);

  // Telegram
  $wp_customize->add_setting('hodcode_telegram', [
    'default'   => '',
    'transport' => 'refresh',
    'sanitize_callback' => 'esc_url_raw',
  ]);
  $wp_customize->add_control('hodcode_telegram', [
    'label'   => __('Telegram URL', 'hodcode'),
    'section' => 'hodcode_social_links',
    'type'    => 'url',
  ]);

  // LinkedIn
  $wp_customize->add_setting('hodcode_linkedin', [
    'default'   => '',
    'transport' => 'refresh',
    'sanitize_callback' => 'esc_url_raw',
  ]);
  $wp_customize->add_control('hodcode_linkedin', [
    'label'   => __('LinkedIn URL', 'hodcode'),
    'section' => 'hodcode_social_links',
    'type'    => 'url',
  ]);
});




function hodcode_enqueue_styles()
{
  wp_enqueue_style(
    'hodcode-style', // Handle name
    get_stylesheet_uri(), // This gets style.css in the root of the theme

  );
  wp_enqueue_style(
    'hodcode-webfont', // Handle name
    get_template_directory_uri() . "/assets/fontiran.css", // This gets style.css in the root of the theme

  );
  wp_enqueue_script(
    'tailwind', // Handle name
    "https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4", // This gets style.css in the root of the theme

  );
}
add_action('wp_enqueue_scripts', 'hodcode_enqueue_styles');


add_action('init', function () {

  // register_taxonomy('product_category', ['product'], [
  //   'hierarchical'      => true,
  //   'labels'            => [
  //     'name'          => ('Product Categories'),
  //     'singular_name' => 'Product Category'
  //   ],
  //   'rewrite'           => ['slug' => 'product-category'],
  //   'show_in_rest' => true,

  // ]);

  // register_post_type('product', [
  //   'public' => true,
  //   'label'  => 'Products',

  // //   'rewrite' => ['slug' => 'product'],
  // //   'taxonomies' => ['product_category'],

  //   'supports' => [
  //     'title',
  //     'editor',
  //     'thumbnail',
  //     'excerpt',
  //     'custom-fields',
  //   ],

  //   'show_in_rest' => true,
  // ]);
});

// hodcode_add_custom_field("price","product","Price (Final)");
// hodcode_add_custom_field("old_price","product","Price (Before)");

// add_action('pre_get_posts', function ($query) {
//   if ($query->is_home() && $query->is_main_query() && !is_admin()) {
//     $query->set('post_type', 'product');
//   }
// });

function toPersianNumerals($input)
{
  // English digits
  $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

  // Persian digits
  $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

  // Replace and return
  return str_replace($english, $persian, (string) $input);
}

function hodcode_add_custom_field($fieldName, $postType, $title)
{
  add_action('add_meta_boxes', function () use ($fieldName, $postType, $title) {
    add_meta_box(
      $fieldName . '_bx`ox',
      $title,
      function ($post) use ($fieldName) {
        $value = get_post_meta($post->ID, $fieldName, true);
        wp_nonce_field($fieldName . '_nonce', $fieldName . '_nonce_field');
        echo '<input type="text" style="width:100%"
         name="' . esc_attr($fieldName) . '" value="' . esc_attr($value) . '">';
      },
      $postType,
      'normal',
      'default'
    );
  });

  add_action('save_post', function ($post_id) use ($fieldName) {
    // checks
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!isset($_POST[$fieldName . '_nonce_field'])) return;
    if (!wp_verify_nonce($_POST[$fieldName . '_nonce_field'], $fieldName . '_nonce')) return;
    if (!current_user_can('edit_post', $post_id)) return;
    // save
    if (isset($_POST[$fieldName])) {
      $san = sanitize_text_field(wp_unslash($_POST[$fieldName]));
      update_post_meta($post_id, $fieldName, $san);
    } else {
      delete_post_meta($post_id, $fieldName);
    }
  });
}
// تابع برای بارگذاری JS بنر
function enqueue_hero_banner_script()
{
  wp_enqueue_script(
    'hero-banner', // نام شناسه برای اسکریپت
    get_template_directory_uri() . '/js/hero-banner.js', // مسیر فایل JS
    array(), // وابستگی‌ها (مثلاً jQuery اگر لازم بود)
    '1.0', // نسخه فایل
    true // true باعث می‌شود قبل از بسته شدن </body> بارگذاری شود
  );
}
add_action('wp_enqueue_scripts', 'enqueue_hero_banner_script');
// کوپن ها 
function display_active_coupons()
{
  if (! class_exists('WC_Coupon')) {
    return 'ووکامرس فعال نیست.';
  }

  $args = array(
    'posts_per_page' => -1,
    'post_type'      => 'shop_coupon',
    'post_status'    => 'publish',
  );

  $coupons = get_posts($args);

  if (empty($coupons)) {
    return '<p>کوپنی یافت نشد.</p>';
  }

  ob_start();
?>

  <div class="coupons-list">
    <?php
    foreach ($coupons as $coupon_post) {
      $coupon = new WC_Coupon($coupon_post->post_title);

      $expiry_date = $coupon->get_date_expires();
      if ($expiry_date && $expiry_date->getTimestamp() < time()) {
        continue;
      }

      $code = $coupon->get_code();
      $amount = $coupon->get_amount();
      $discount_type = $coupon->get_discount_type();
      $minimum_amount = $coupon->get_minimum_amount();

      switch ($discount_type) {
        case 'percent':
          $discount_text = $amount . '% تخفیف';
          break;
        case 'fixed_cart':
          $discount_text = wc_price($amount) . ' تخفیف کل سفارش';
          break;
        case 'fixed_product':
          $discount_text = wc_price($amount) . ' تخفیف محصول';
          break;
        default:
          $discount_text = '';
      }

      $condition_text = '';
      if ($minimum_amount) {
        $condition_text = ' برای سفارش بالای ' . wc_price($minimum_amount);
      }

      if ($expiry_date) {
        $expires_timestamp = $expiry_date->getTimestamp() + 86399;
      } else {
        $expires_timestamp = null;
      }
    ?>
      <div class="coupon-item">
        <h3>کد: <strong><?php echo esc_html($code); ?></strong></h3>
        <p class="discount-text"><?php echo $discount_text . $condition_text; ?></p>
        <button class="copy-btn" onclick="copyCoupon('<?php echo esc_js($code); ?>')">کپی کد</button>
        <?php if ($expires_timestamp): ?>
          <div class="countdown-timer" data-expire="<?php echo esc_attr($expires_timestamp); ?>">در حال بارگذاری...</div>
        <?php endif; ?>
      </div>
    <?php
    }
    ?>
  </div>

  <script>
    function copyCoupon(code) {
      navigator.clipboard.writeText(code).then(() => {
        alert('کد کوپن کپی شد: ' + code);
      }).catch(() => {
        alert('مشکلی در کپی کردن کد رخ داد');
      });
    }

    document.addEventListener("DOMContentLoaded", function() {
      const timers = document.querySelectorAll('.countdown-timer');

      timers.forEach(timer => {
        const expireTime = parseInt(timer.dataset.expire) * 1000;

        function updateCountdown() {
          const now = new Date().getTime();
          const diff = expireTime - now;

          if (diff < 0) {
            timer.innerHTML = '⛔ این کوپن منقضی شده';
            timer.style.color = '#c0392b';
            return;
          }

          // حذف نمایش روز
          const hours = Math.floor(diff / (1000 * 60 * 60));
          const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
          const seconds = Math.floor((diff % (1000 * 60)) / 1000);

          let output = '🕒 باقی‌مانده: ';
          output += `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
          timer.innerHTML = output;
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
      });
    });
  </script>
<?php
  return ob_get_clean();
}
add_shortcode('show_coupons', 'display_active_coupons');

