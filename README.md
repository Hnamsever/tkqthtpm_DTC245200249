# Đề 13 – Hệ Thống Website Portfolio Cá Nhân (Kèm Trang Admin Quản Lý Nội Dung)

Hệ thống Website Portfolio cá nhân tích hợp trang Admin quản lý nội dung, triển khai hoàn toàn bằng **Docker Compose**, tuân thủ đầy đủ các yêu cầu kỹ thuật và tiêu chí đánh giá môn học **Triển khai và Quản trị Hệ thống Phần mềm**.

> 📚 **Tài liệu hướng dẫn chuyên sâu & Bảng tổng hợp câu lệnh:** Xem chi tiết tại [HUONG_DAN_CHAY_VA_TONG_HOP_LENH.md](HUONG_DAN_CHAY_VA_TONG_HOP_LENH.md) (Hướng dẫn 1-click `start.bat`, `stop.bat`, `restart.bat`, toàn bộ lệnh Docker, MySQL, WP-CLI, Prometheus, Grafana, Loki, Nginx Hardening và kịch bản vấn đáp).

---

## 📑 Bảng Mục Lục
1. [Kiến Trúc Tổng Thể](#1-kiến-trúc-tổng-thể)
2. [Bảng Phân Bổ Cổng Dịch Vụ & Tài Khoản](#2-bảng-phân-bổ-cổng-dịch-vụ--tài-khoản)
3. [Hướng Dẫn Triển Khai Nhanh](#3-hướng-dẫn-triển-khai-nhanh)
4. [Cấu Trúc Thư Mục](#4-cấu-trúc-thư-mục)
5. [Chi Tiết Kỹ Thuật Các Thành Phần](#5-chi-tiết-kỹ-thuật-các-thành-phần)
   - [5.1. Nginx Reverse Proxy & Hardening](#51-nginx-reverse-proxy--hardening)
   - [5.2. WordPress & MySQL & phpMyAdmin](#52-wordpress--mysql--phpmyadmin)
   - [5.3. Prometheus & Grafana (Giám Sát)](#53-prometheus--grafana-giám-sát)
   - [5.4. Loki & Promtail (LogQL Tập Trung)](#54-loki--promtail-logql-tập-trung)
   - [5.5. Các Biện Pháp Hardening Bảo Mật](#55-các-biện-pháp-hardening-bảo-mật)
6. [Kịch Bản Kiểm Thử Tự Động (test_all.py)](#6-kịch-bản-kiểm-thử-tự-động-test_allpy)
7. [Lịch Sử Commit Git (Đáp Ứng Tiêu Chí Đánh Giá)](#7-lịch-sử-commit-git)

---

## 1. Kiến Trúc Tổng Thể

```
                             [ NGƯỜI DÙNG / TRÌNH DUYỆT ]
                                          |
                                   (Port 8000: HTTP)
                                          v
                +----------------------------------------------------+
                |            Nginx Reverse Proxy & Hardening         |
                |  - Security Headers (X-Frame, CSP, HSTS, Nosniff)  |
                |  - Rate Limiting (login_limit, general_limit)      |
                |  - Chặn truy cập nhạy cảm (xmlrpc, .env, wp-config)|
                |  - Ẩn Server Tokens (server_tokens off)            |
                +-------------------------+--------------------------+
                                          |
                   +----------------------+----------------------+
                   | (Route: /)                                  | (Route: /pma/)
                   v                                             v
        +----------------------+                      +----------------------+
        |      WordPress       |                      |      phpMyAdmin      |
        |  (PHP 8.3 + Apache)  |                      | (Quản lý Database)   |
        +----------+-----------+                      +----------+-----------+
                   |                                             |
                   +----------------------+----------------------+
                                          | (Mạng nội bộ backend)
                                          v
                               +----------------------+
                               |    MySQL 8.0 (DB)    |
                               |  Database: portfolio |
                               +----------------------+

================================ GIÁM SÁT & LOGGING ================================
  +------------------+     +--------------------+     +---------------------+
  |  Nginx Exporter  |     |  MySQL Exporter    |     |      cAdvisor       |
  | (nginx:stub_status)    | (metrics Database) |     |  (Metrics Container)|
  +--------+---------+     +---------+----------+     +----------+----------+
           |                         |                           |
           +-------------------------+---------------------------+
                                     | (Scrape metrics mỗi 10-15s)
                                     v
                          +---------------------+
                          |  Prometheus Server  | (Port 9090)
                          +----------+----------+
                                     |
                                     v
                          +---------------------+
                          |  Grafana Dashboard  | (Port 3001)
                          |  - Docker Overview  |
                          |  - Nginx Traffic    |
                          |  - MySQL Metrics    |
                          |  - Loki LogQL View  |
                          +----------+----------+
                                     ^
                                     | (Truy vấn LogQL)
                          +----------+----------+
                          |   Loki Log Server   | (Port 3101)
                          +----------+----------+
                                     ^
                                     | (Thu thập & đẩy log)
                          +----------+----------+
                          |  Promtail Collector |
                          |  - /var/log/nginx/* |
                          |  - Docker Containers|
                          +---------------------+
```

---

## 2. Bảng Phân Bổ Cổng Dịch Vụ & Tài Khoản

| Dịch Vụ | Cổng Host | URL Truy Cập | Mục Đích | Tài Khoản Mặc Định |
|---|---|---|---|---|
| **Website Portfolio (qua Nginx)** | **8000** | [http://localhost:8000](http://localhost:8000) | Trang chủ Portfolio cá nhân | Khách truy cập |
| **Trang Quản Trị WordPress** | **8000** | [http://localhost:8000/wp-admin](http://localhost:8000/wp-admin) | Quản lý nội dung, bài viết, theme | `admin` / `Admin@Portfolio2026` |
| **phpMyAdmin (qua Nginx)** | **8000** | [http://localhost:8000/pma/](http://localhost:8000/pma/) | Quản lý cơ sở dữ liệu MySQL | `portfolio_user` / `Db@Portfolio2026` *(hoặc root)* |
| **WordPress Trực Tiếp (Dev)** | **8090** | [http://localhost:8090](http://localhost:8090) | Debug trực tiếp Apache WordPress | – |
| **phpMyAdmin Trực Tiếp (Dev)** | **8091** | [http://localhost:8091](http://localhost:8091) | Debug trực tiếp phpMyAdmin | `portfolio_user` / `Db@Portfolio2026` |
| **Grafana Dashboard** | **3001** | [http://localhost:3001](http://localhost:3001) | Dashboard giám sát Container, Web, DB, Logs | `admin` / `admin123` |
| **Prometheus Web UI** | **9090** | [http://localhost:9090](http://localhost:9090) | Truy vấn PromQL, xem Targets health | Không yêu cầu |
| **Loki Log API** | **3101** | [http://localhost:3101/ready](http://localhost:3101/ready) | Endpoint log aggregator | API nội bộ |

---

## 3. Hướng Dẫn Triển Khai Nhanh

### Bước 1: Sao chép file môi trường
```powershell
copy .env.example .env
```
*(Bạn có thể tùy chỉnh mật khẩu và cổng trong file `.env` nếu cần)*

### Bước 2: Khởi động toàn bộ cụm dịch vụ (11 Container)
```powershell
docker compose up -d
```

### Bước 3: Khởi tạo dữ liệu WordPress tự động (Chạy 1 lần duy nhất)
```powershell
docker compose --profile setup run --rm wpcli
```
Lệnh này tự động cấu hình:
- Cài đặt WordPress tiếng Việt
- Cài & kích hoạt theme **Astra** chuẩn
- Tạo 4 trang chính: Trang chủ, Giới thiệu, Dự án, Liên hệ
- Tạo 2 bài viết blog kỹ thuật (Docker & Monitoring)
- Thiết lập Menu điều hướng và cấu hình URL chuẩn `http://localhost:8000`

### Bước 4: Kiểm tra trạng thái hệ thống
```powershell
docker compose ps
python test_all.py
```

---

## 4. Cấu Trúc Thư Mục

```
.
├── docker-compose.yml              # Định nghĩa 11 container, volume, network
├── .env.example                    # Biến môi trường mẫu
├── .env                            # Biến môi trường thực tế (bảo mật)
├── README.md                       # Hướng dẫn chi tiết hệ thống
├── test_all.py                     # Script kiểm tra tự động Prometheus, Loki, Grafana
├── config/
│   ├── nginx/
│   │   └── nginx.conf              # Cấu hình Reverse Proxy, Security Headers, Rate Limiting
│   ├── php/
│   │   └── uploads.ini             # Giới hạn kích thước upload, ẩn thông tin phiên bản PHP
│   ├── mysql/
│   │   └── exporter.cnf            # Cấu hình xác thực an toàn cho mysqld-exporter
│   ├── prometheus/
│   │   └── prometheus.yml          # Cấu hình scrape jobs (cadvisor, mysql, nginx, prometheus)
│   ├── loki/
│   │   └── loki-config.yml         # Cấu hình lưu trữ và nén log của Loki
│   ├── promtail/
│   │   └── promtail-config.yml     # Thu thập Nginx access/error log & Docker container logs
│   └── grafana/
│       ├── provisioning/
│       │   ├── datasources/        # Tự động nạp Prometheus và Loki làm nguồn dữ liệu
│       │   └── dashboards/         # Tự động nạp các dashboards
│       └── dashboards/
│           ├── nginx-metrics.json   # Dashboard giám sát lưu lượng Nginx Reverse Proxy
│           ├── mysql-metrics.json   # Dashboard giám sát hiệu năng MySQL Database
│           ├── docker-metrics.json  # Dashboard giám sát tài nguyên CPU/RAM/Network container
│           └── loki-logs.json       # Dashboard tra cứu LogQL thời gian thực
└── setup/
    ├── setup.sh                    # Kịch bản WP-CLI khởi tạo nội dung chuẩn
    ├── assets/                     # Hình ảnh đại diện và hình ảnh dự án mẫu
    └── content/                    # Nội dung các trang Gutenberg HTML & custom.css
```

---

## 5. Chi Tiết Kỹ Thuật Các Thành Phần

### 5.1. Nginx Reverse Proxy & Hardening
- **Reverse Proxy**: Tiếp nhận request tại cổng `8000`, phân phối:
  - Route `/` -> Forward tới dịch vụ `wordpress:80`
  - Route `/pma/` -> Forward tới dịch vụ `phpmyadmin:80`
  - Endpoint `/stub_status` -> Phục vụ `nginx-exporter` lấy metrics (chỉ cho phép nội bộ Docker).
- **Security Headers**:
  - `X-Frame-Options: SAMEORIGIN` (Chống Clickjacking)
  - `X-Content-Type-Options: nosniff` (Chống MIME-type sniffing)
  - `X-XSS-Protection: 1; mode=block` (Chống Cross-Site Scripting)
  - `Referrer-Policy: strict-origin-when-cross-origin`
  - `Permissions-Policy: geolocation=(), camera=(), microphone=()`
  - `Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' https: data:;`
- **Rate Limiting**:
  - `general_limit`: Giới hạn 20 req/s cho toàn bộ website (burst 40)
  - `login_limit`: Giới hạn 2 req/s cho `/wp-login.php` (burst 5) chống Brute-force mật khẩu.
- **Access Control & Chặn mã độc**:
  - Chặn truy cập file ẩn: `location ~ /\.(?!well-known) { deny all; }`
  - Chặn chạy script PHP trong thư mục tải lên: `location ~* /(?:uploads|files)/.*\.php$ { deny all; }`
  - Chặn XML-RPC (chống DDoS pingback & dò mật khẩu): `location = /xmlrpc.php { deny all; }`
  - Chặn đọc trực tiếp file cấu hình: `wp-config.php`, `readme.html`, `license.txt`.

### 5.2. WordPress & MySQL & phpMyAdmin
- **WordPress 6.x (PHP 8.3)**: 
  - Giao diện Portfolio tối giản, thanh lịch, tốc độ cao với theme **Astra** bản quyền mở từ WordPress.org.
  - Hỗ trợ đầy đủ khối Gutenberg tùy biến, responsive tốt trên máy tính và điện thoại.
- **MySQL 8.0**:
  - Mã hóa ký tự chuẩn `utf8mb4` và collation `utf8mb4_unicode_ci`.
  - Dữ liệu lưu bền vững qua Docker volume `db_data`.
  - Cấu hình Healthcheck kiểm tra `mysqladmin ping`.
- **phpMyAdmin**:
  - Được bảo vệ phía sau Nginx tại URL `/pma/`, đồng thời mở cổng trực tiếp cho môi trường phát triển.

### 5.3. Prometheus & Grafana (Giám Sát)
- **Prometheus** thu thập chỉ số định kỳ (10s - 15s) từ 4 thành phần:
  1. `cadvisor:8080`: Đo mức sử dụng CPU, RAM, I/O mạng của từng container.
  2. `nginx-exporter:9113`: Đo số lượng kết nối đang xử lý, tỉ lệ request/s, số trạng thái HTTP.
  3. `mysqld-exporter:9104`: Đo số kết nối MySQL, số truy vấn SELECT/INSERT/UPDATE, buffer pool.
  4. `localhost:9090`: Tự giám sát tiến trình Prometheus.
- **Grafana 10.4**:
  - Tự động nạp cấu hình Datasources (Prometheus & Loki) mà không cần nhập tay.
  - Tự động nạp 4 Dashboards trực quan:
    - `Portfolio - Docker Containers Overview`
    - `Portfolio - Nginx Reverse Proxy & Traffic`
    - `Portfolio - MySQL Database Performance`
    - `Portfolio - Centralized Logs & LogQL Explorer`

### 5.4. Loki & Promtail (LogQL Tập Trung)
Promtail quét log và đẩy lên Loki. Sinh viên và quản trị viên có thể truy vấn bằng ngôn ngữ **LogQL**:

#### 3 Truy Vấn LogQL Cơ Bản (Minh Chứng Tiêu Chí Đánh Giá):
1. **Truy vấn toàn bộ access log của Nginx Reverse Proxy:**
   ```logql
   {job="nginx"}
   ```
2. **Truy vấn log các container thuộc dự án Portfolio:**
   ```logql
   {container=~"portfolio-.*"}
   ```
3. **Lọc các request HTTP tới Nginx trả về mã lỗi 4xx hoặc 5xx:**
   ```logql
   {job="nginx"} |= "status=4" or "status=5"
   ```
4. **Đếm tần suất request Nginx theo khoảng thời gian 1 phút:**
   ```logql
   rate({job="nginx"}[1m])
   ```

### 5.5. Các Biện Pháp Hardening Bảo Mật (Áp Dụng Tối Thiểu 6 Biện Pháp)
1. **Non-Root & Privilege Escalation Prevention**: Tất cả container chạy với tùy chọn `security_opt: - no-new-privileges:true`. Container WP-CLI chạy với user chuẩn `33:33 (www-data)`.
2. **Network Isolation (Phân Vùng Mạng)**:
   - `frontend`: Chỉ Nginx, WordPress và phpMyAdmin giao tiếp.
   - `backend`: Chỉ MySQL, WordPress, phpMyAdmin và mysqld-exporter giao tiếp (cơ sở dữ liệu hoàn toàn không mở cổng public ra Internet).
   - `monitoring`: Cô lập Prometheus, Loki, Promtail, cAdvisor, Exporters và Grafana.
3. **Ẩn Thông Tin Phiên Bản (Information Disclosure Prevention)**:
   - Nginx: `server_tokens off;`
   - PHP: `expose_php = Off` trong `uploads.ini`.
4. **Rate Limiting Chống Tấn Công DoS & Brute-Force**:
   - `limit_req_zone` 20r/s trên toàn bộ site và 2r/s tại `wp-login.php`.
5. **Chặn Thực Thi File Script Trong Thư Mục Tải Lên (Upload Protection)**:
   - Ngăn kẻ tấn công upload shell `.php` vào thư mục `wp-content/uploads/` rồi thực thi.
6. **Mật Khẩu Phức Tạp & Quản Lý Biến Môi Trường**:
   - Không hardcode mật khẩu trong source code; tách biệt hoàn toàn qua `.env`.
   - Mật khẩu MySQL root và người dùng đáp ứng tiêu chuẩn độ dài và độ phức tạp cao.

---

## 6. Kịch Bản Kiểm Thử Tự Động (test_all.py)

Kho mã nguồn đi kèm script `test_all.py` kiểm tra toàn bộ stack chỉ với 1 lệnh:
```powershell
python test_all.py
```

Kết quả mẫu:
```text
=== PROMETHEUS TARGETS ===
  [UP] cadvisor -> http://cadvisor:8080/metrics
  [UP] mysql -> http://mysqld-exporter:9104/metrics
  [UP] nginx -> http://nginx-exporter:9113/metrics
  [UP] prometheus -> http://localhost:9090/metrics

=== LOKI & LOGQL QUERIES ===
  LogQL: {container="portfolio-nginx"} -> Found 1 stream(s)
  LogQL: {container=~"portfolio-.*"} -> Found 3 stream(s)
  LogQL: {container="portfolio-wordpress"} -> Found 1 stream(s)
  LogQL: {job="nginx"} -> Found 1 stream(s)

=== GRAFANA DASHBOARDS & HEALTH ===
  Grafana Health: {"database": "ok", "version": "10.4.0"}
  Dashboard: Portfolio - Centralized Logs & LogQL Explorer -> /d/portfolio-loki-logs/...
  Dashboard: Portfolio - Docker Containers Overview -> /d/portfolio-docker-overview/...
  Dashboard: Portfolio - MySQL Database Performance -> /d/portfolio-mysql-performance/...
  Dashboard: Portfolio - Nginx Reverse Proxy & Traffic -> /d/portfolio-nginx-traffic/...
```

---

## 7. Lịch Sử Commit Git

Tuân thủ đúng yêu cầu của Đề bài thực hành (tối thiểu 03 commit có ý nghĩa rõ ràng):
1. **Commit 1**: `feat(core): setup Nginx reverse proxy, WordPress app and MySQL database with security headers`
2. **Commit 2**: `feat(monitoring): integrate Prometheus and Grafana with cadvisor, nginx and mysql exporters`
3. **Commit 3**: `feat(logging-hardening): implement Loki and Promtail centralized logging, LogQL queries and system hardening`
