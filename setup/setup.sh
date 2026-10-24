#!/bin/sh
# ============================================================
#  Cài đặt WordPress + theme Astra (từ wordpress.org) + nội dung portfolio mẫu
#  Chạy:  docker compose --profile setup run --rm wpcli
#  Script idempotent: chạy lại nhiều lần không bị nhân đôi dữ liệu.
# ============================================================
set -e
cd /var/www/html

log() { printf '\n\033[1;35m>> %s\033[0m\n' "$1"; }

# ---------- 1. Chờ WordPress tạo wp-config.php và MySQL sẵn sàng ----------
log "Chờ WordPress & MySQL sẵn sàng..."
i=0
until [ -f wp-config.php ] && php -r '
  $m = @mysqli_connect(getenv("WORDPRESS_DB_HOST"), getenv("WORDPRESS_DB_USER"),
                       getenv("WORDPRESS_DB_PASSWORD"), getenv("WORDPRESS_DB_NAME"));
  exit($m ? 0 : 1);'; do
  i=$((i+1))
  [ "$i" -gt 60 ] && { echo "Hết thời gian chờ!"; exit 1; }
  sleep 3
done

# ---------- 2. Cài WordPress core ----------
if ! wp core is-installed 2>/dev/null; then
  log "Cài đặt WordPress core..."
  wp core install \
    --url="$WP_URL" \
    --title="$WP_TITLE" \
    --admin_user="$WP_ADMIN_USER" \
    --admin_password="$WP_ADMIN_PASSWORD" \
    --admin_email="$WP_ADMIN_EMAIL" \
    --skip-email
  # Xoá bài/trang mặc định
  wp post delete 1 2 3 --force >/dev/null 2>&1 || true
else
  log "WordPress đã được cài, bỏ qua bước install."
fi

# ---------- 3. Ngôn ngữ & cấu hình chung ----------
log "Cấu hình tiếng Việt, múi giờ, permalink..."
wp language core install vi --activate >/dev/null 2>&1 || true
wp option update timezone_string "Asia/Ho_Chi_Minh"
wp option update siteurl "$WP_URL"
wp option update home "$WP_URL"
wp option update date_format "d/m/Y"
wp option update time_format "H:i"
wp option update blogdescription "Lập trình viên Web & DevOps"
wp rewrite structure '/%postname%/' >/dev/null
wp rewrite flush >/dev/null

# ---------- 4. Theme Astra từ kho vi.wordpress.org/themes ----------
log "Cài & kích hoạt theme Astra..."
if wp theme is-installed astra; then
  wp theme activate astra
else
  wp theme install astra --activate
fi
wp language theme install astra vi >/dev/null 2>&1 || true

# Dọn theme/plugin mặc định không dùng (giảm bề mặt tấn công)
for t in $(wp theme list --status=inactive --field=name); do wp theme delete "$t" || true; done
wp plugin delete akismet hello >/dev/null 2>&1 || true

# Cấu hình Astra toàn cục: không sidebar, container rộng
wp eval '
  $s = get_option("astra-settings", array());
  $s["site-sidebar-layout"]          = "no-sidebar";
  $s["single-page-sidebar-layout"]   = "no-sidebar";
  $s["single-post-sidebar-layout"]   = "no-sidebar";
  $s["archive-post-sidebar-layout"]  = "no-sidebar";
  $s["site-content-width"]           = 1200;
  $s["footer-copyright-editor"]      = "Bản quyền &copy; [current_year] [site_title] — Xây dựng với WordPress &amp; Astra";
  update_option("astra-settings", $s);
'

# ---------- 5. Nội dung portfolio mẫu (chỉ tạo 1 lần) ----------
if [ "$(wp option get portfolio_seeded 2>/dev/null)" = "1" ]; then
  log "Nội dung mẫu đã có, bỏ qua."
else
  log "Import ảnh..."
  import_img() { # $1=file $2=title -> in ra URL
    id=$(wp media import "/setup/assets/$1" --title="$2" --porcelain)
    wp eval "echo wp_get_attachment_url($id);"
  }
  AVATAR_URL=$(import_img avatar.jpg "Ảnh đại diện")
  P1_URL=$(import_img project-ecommerce.jpg "Dự án E-commerce")
  P2_URL=$(import_img project-mobile.jpg "Dự án Mobile App")
  P3_URL=$(import_img project-devops.jpg "Dự án DevOps Monitoring")

  render() { # thay placeholder trong file nội dung
    sed -e "s|{{AVATAR_URL}}|$AVATAR_URL|g" \
        -e "s|{{P1_URL}}|$P1_URL|g" \
        -e "s|{{P2_URL}}|$P2_URL|g" \
        -e "s|{{P3_URL}}|$P3_URL|g" \
        -e "s|{{SITE_URL}}|$WP_URL|g" \
        "/setup/content/$1" > "/tmp/$1"
    echo "/tmp/$1"
  }

  new_page() { # $1=title $2=slug $3=file
    wp post create "$(render "$3")" --post_type=page --post_status=publish \
      --post_title="$1" --post_name="$2" --porcelain
  }

  # Áp layout full-width của Astra cho từng trang
  astra_full() {
    wp post meta update "$1" site-sidebar-layout no-sidebar >/dev/null
    wp post meta update "$1" ast-site-content-layout full-width-container >/dev/null
    wp post meta update "$1" site-content-layout page-builder >/dev/null
    wp post meta update "$1" site-content-style unboxed >/dev/null
    [ "$2" = "notitle" ] && wp post meta update "$1" site-post-title disabled >/dev/null
    return 0
  }

  log "Tạo các trang..."
  HOME_ID=$(new_page "Trang chủ" "trang-chu" home.html);       astra_full "$HOME_ID" notitle
  ABOUT_ID=$(new_page "Giới thiệu" "gioi-thieu" about.html);   astra_full "$ABOUT_ID" notitle
  PROJ_ID=$(new_page "Dự án" "du-an" projects.html);           astra_full "$PROJ_ID" notitle
  CONTACT_ID=$(new_page "Liên hệ" "lien-he" contact.html);     astra_full "$CONTACT_ID" notitle
  BLOG_ID=$(wp post create --post_type=page --post_status=publish --post_title="Blog" --post_name="blog" --porcelain)

  wp option update show_on_front page
  wp option update page_on_front "$HOME_ID"
  wp option update page_for_posts "$BLOG_ID"

  log "Tạo bài viết blog mẫu..."
  CAT_ID=$(wp term create category "Công nghệ" --slug=cong-nghe --porcelain)
  wp post create "$(render post-docker.html)" --post_status=publish --post_category="$CAT_ID" \
    --post_title="Triển khai WordPress với Docker Compose" >/dev/null
  wp post create "$(render post-monitoring.html)" --post_status=publish --post_category="$CAT_ID" \
    --post_title="Giám sát hệ thống với Prometheus, Grafana và Loki" >/dev/null
  wp term delete category 1 >/dev/null 2>&1 || true   # "Chưa phân loại"

  log "Tạo menu chính..."
  MENU_ID=$(wp menu create "Menu chính" --porcelain)
  wp menu item add-post "$MENU_ID" "$HOME_ID"    --title="Trang chủ" >/dev/null
  wp menu item add-post "$MENU_ID" "$ABOUT_ID"   --title="Giới thiệu" >/dev/null
  wp menu item add-post "$MENU_ID" "$PROJ_ID"    --title="Dự án" >/dev/null
  wp menu item add-post "$MENU_ID" "$BLOG_ID"    --title="Blog" >/dev/null
  wp menu item add-post "$MENU_ID" "$CONTACT_ID" --title="Liên hệ" >/dev/null
  wp menu location assign "$MENU_ID" primary
  wp menu location assign "$MENU_ID" mobile_menu >/dev/null 2>&1 || true

  log "Thêm CSS tuỳ biến..."
  wp eval 'wp_update_custom_css_post(file_get_contents("/setup/content/custom.css"));'

  log "Cài đặt plugin giao diện đăng nhập (mu-plugins)..."
  mkdir -p /var/www/html/wp-content/mu-plugins
  cp -f /setup/mu-plugins/*.php /var/www/html/wp-content/mu-plugins/ 2>/dev/null || true

  wp option update portfolio_seeded 1 >/dev/null
fi

wp cache flush >/dev/null 2>&1 || true

log "HOÀN TẤT!"
echo "  Website   : $WP_URL"
echo "  Admin     : $WP_URL/wp-admin  (user: $WP_ADMIN_USER)"
echo "  Theme     : $(wp theme list --status=active --field=name) $(wp theme list --status=active --field=version)"
