# 📖 HƯỚNG DẪN CHẠY VÀ TỔNG HỢP CÂU LỆNH HỆ THỐNG
## Đề Tài 13: Website Portfolio Cá Nhân (Kèm Trang Admin Quản Lý Nội Dung)
### Môn học: Triển khai và Quản trị Hệ thống Phần mềm

---

## 📌 MỤC LỤC
1. [Bảng Tra Cứu Nhanh URL & Tài Khoản](#1-bảng-tra-cứu-nhanh-url--tài-khoản)
2. [Yêu Cầu Môi Trường Trước Khi Chạy](#2-yêu-cầu-môi-trường-trước-khi-chạy)
3. [Hướng Dẫn Chạy Hệ Thống](#3-hướng-dẫn-chạy-hệ-thống)
   - [Cách 1: Khởi động nhanh bằng 1 click (Khuyên dùng)](#cách-1-khởi-động-nhanh-bằng-1-click-khuyên-dùng)
   - [Cách 2: Chạy từng bước bằng dòng lệnh (Terminal / PowerShell)](#cách-2-chạy-từng-bước-bằng-dòng-lệnh-terminal--powershell)
4. [Tổng Hợp Toàn Bộ Câu Lệnh Thường Dùng](#4-tổng-hợp-toàn-bộ-câu-lệnh-thường-dùng)
   - [Nhóm 1: Quản lý vòng đời Docker Compose (Bật, Tắt, Khởi động lại)](#nhóm-1-quản-lý-vòng-đời-docker-compose)
   - [Nhóm 2: Kiểm tra trạng thái & Tài nguyên Container](#nhóm-2-kiểm-tra-trạng-thái--tài-nguyên-container)
   - [Nhóm 3: Xem Logs chi tiết từng Container](#nhóm-3-xem-logs-chi-tiết-từng-container)
   - [Nhóm 4: Truy cập trực tiếp vào Shell bên trong Container](#nhóm-4-truy-cập-trực-tiếp-vào-shell-bên-trong-container)
   - [Nhóm 5: Thao tác & Sao lưu / Phục hồi Database MySQL](#nhóm-5-thao-tác--sao-lưu--phục-hồi-database-mysql)
   - [Nhóm 6: Quản trị WordPress bằng dòng lệnh (WP-CLI)](#nhóm-6-quản-trị-wordpress-bằng-dòng-lệnh-wp-cli)
   - [Nhóm 7: Kiểm tra Giám sát (Prometheus, Grafana, Loki)](#nhóm-7-kiểm-tra-giám-sát-prometheus-grafana-loki)
   - [Nhóm 8: Kiểm tra Bảo mật & Hardening Nginx](#nhóm-8-kiểm-tra-bảo-mật--hardening-nginx)
   - [Nhóm 9: Lệnh Git và Sinh Báo Cáo Tự Động](#nhóm-9-lệnh-git-và-sinh-báo-cáo-tự-động)
5. [Quy Trình Biểu Diễn & Vấn Đáp Trước Thầy Cô (Demo Walkthrough)](#5-quy-trình-biểu-diễn--vấn-đáp-trước-thầy-cô)
6. [Xử Lý Các Lỗi Thường Gặp (Troubleshooting)](#6-xử-lý-các-lỗi-thường-gặp-troubleshooting)

---

## 1. Bảng Tra Cứu Nhanh URL & Tài Khoản

| Phân hệ / Dịch vụ | Đường dẫn (URL) | Mục đích | Tài khoản / Thông tin xác thực |
|---|---|---|---|
| **Website Portfolio** | [http://localhost:8000](http://localhost:8000) | Giao diện giới thiệu cá nhân, dự án, blog, liên hệ | Người dùng truy cập tự do |
| **Trang Quản trị Admin (WordPress)** | [http://localhost:8000/wp-admin](http://localhost:8000/wp-admin) | Viết bài blog, quản lý dự án, trang, menu, theme Astra | **User:** `admin`<br>**Pass:** `Admin@Portfolio2026` |
| **phpMyAdmin** | [http://localhost:8000/pma/](http://localhost:8000/pma/) | Quản trị CSDL MySQL trên nền Web | **User:** `portfolio_user` (hoặc `root`)<br>**Pass:** `Db@Portfolio2026` (hoặc `Root@Portfolio2026`) |
| **Grafana Dashboard** | [http://localhost:3001](http://localhost:3001) | Theo dõi toàn cảnh: Docker, Nginx, MySQL, Loki Logs | **User:** `admin`<br>**Pass:** `admin123` |
| **Prometheus UI** | [http://localhost:9090](http://localhost:9090) | Truy vấn PromQL, xem trạng thái các Targets | Không cần đăng nhập |
| **Loki API Ready Check** | [http://localhost:3101/ready](http://localhost:3101/ready) | Endpoint kiểm tra sức khỏe của Log Server | Trả về `ready` |

---

## 2. Yêu Cầu Môi Trường Trước Khi Chạy

1. **Docker Desktop**: Đã được cài đặt và **đang bật (Engine running - icon cá voi màu xanh)**.
2. **Python 3.x**: Dùng để chạy script kiểm thử tự động `test_all.py` và sinh báo cáo `tao_bao_cao.py`.
3. **Cổng mạng (Ports) cần trống trên máy tính**:
   - `8000`: Dành cho Nginx Reverse Proxy (điểm vào chính của hệ thống).
   - `3001`: Dành cho Grafana.
   - `9090`: Dành cho Prometheus.
   - `3101`: Dành cho Loki.
   - `8090`, `8091`: Dành cho cổng nội bộ WordPress và phpMyAdmin.

---

## 3. Hướng Dẫn Chạy Hệ Thống

### Cách 1: Khởi động nhanh bằng 1 click (Khuyên dùng)

Trong thư mục dự án đã có sẵn file script tự động hóa:
- **Khởi động hệ thống:** Nhấp đúp chuột vào file `start.bat`.
  - Script sẽ tự động:
    1. Bật toàn bộ 11 container trong nền (`docker compose up -d`).
    2. Khởi tạo nội dung mẫu Portfolio & Theme Astra qua WP-CLI (`docker compose --profile setup run --rm wpcli`).
    3. Chạy kiểm tra tự động các phân hệ (`python test_all.py`).
    4. In ra danh sách các liên kết để bạn truy cập ngay.
- **Tắt hệ thống:** Nhấp đúp chuột vào file `stop.bat` (hoặc gõ lệnh `docker compose down`).

---

### Cách 2: Chạy từng bước bằng dòng lệnh (Terminal / PowerShell)

Mở PowerShell hoặc Command Prompt tại thư mục dự án (`c:\Users\Hoang\Desktop\KtraTtht`):

#### Bước 1: Chuẩn bị file biến môi trường (nếu chưa có)
```powershell
# Sao chép từ file mẫu nếu file .env chưa tồn tại
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
```

#### Bước 2: Khởi động toàn bộ 11 Container
```powershell
docker compose up -d
```
> *Lệnh này sẽ tải ảnh (pull images nếu chưa có) và khởi động 11 container: Nginx, WordPress, MySQL, phpMyAdmin, Prometheus, Grafana, Loki, Promtail, cAdvisor, Nginx-Exporter, MySQL-Exporter.*

#### Bước 3: Khởi tạo dữ liệu WordPress tự động (Chạy khi mới cài lần đầu)
```powershell
docker compose --profile setup run --rm wpcli
```
> *Lệnh này kích hoạt profile setup: cài đặt WordPress, active theme Astra, tạo các trang (Trang chủ, Dự án, Giới thiệu, Liên hệ), tạo bài viết mẫu và thiết lập menu.*

#### Bước 4: Kiểm tra tự động toàn hệ thống
```powershell
python test_all.py
```
> *Script sẽ kiểm tra các target Prometheus (cadvisor, mysql, nginx, prometheus), kiểm tra truy vấn LogQL từ Loki và xác nhận 4 Dashboard trong Grafana.*

---

## 4. Tổng Hợp Toàn Bộ Câu Lệnh Thường Dùng

### Nhóm 1: Quản lý vòng đời Docker Compose

| Hành động | Câu lệnh | Giải thích chi tiết |
|---|---|---|
| **Bật toàn bộ hệ thống** | `docker compose up -d` | Chạy 11 container ở chế độ chạy ngầm (detached mode). |
| **Bật và ép tải lại ảnh mới nhất** | `docker compose up -d --force-recreate` | Tái tạo container theo cấu hình mới mà không mất dữ liệu volume. |
| **Tắt toàn bộ hệ thống** | `docker compose down` | Dừng và xóa toàn bộ container, giữ nguyên dữ liệu trong Docker Volumes. |
| **Tắt và XÓA TOÀN BỘ dữ liệu (Reset sạch)** | `docker compose down -v` | ⚠️ **Cảnh báo:** Xóa cả volumes (Database, WordPress uploads, Logs). Dùng khi muốn thiết lập lại từ đầu. |
| **Dừng tạm thời** | `docker compose stop` | Dừng các container mà không xóa container. |
| **Tiếp tục chạy sau khi stop** | `docker compose start` | Tiếp tục chạy các container đã tạm dừng. |
| **Khởi động lại toàn bộ** | `docker compose restart` | Khởi động lại tất cả các container. |
| **Khởi động lại 1 dịch vụ cụ thể** | `docker compose restart nginx` | Chỉ khởi động lại service `nginx` (hoặc `wordpress`, `db`, `grafana`...). |

---

### Nhóm 2: Kiểm tra trạng thái & Tài nguyên Container

| Hành động | Câu lệnh | Giải thích chi tiết |
|---|---|---|
| **Xem danh sách & trạng thái container** | `docker compose ps` | Hiển thị tên, lệnh chạy, trạng thái (`healthy`, `Up`), các cổng đang mở. |
| **Xem trạng thái chi tiết kèm kích thước** | `docker compose ps -a` | Xem cả các container đã dừng hoặc container tạm thời. |
| **Xem mức tiêu thụ CPU/RAM theo thời gian thực** | `docker stats` | Xem trực tiếp % CPU, dung lượng RAM, I/O mạng của từng container. |
| **Kiểm tra thông tin healthcheck MySQL** | `docker inspect --format='{{json .State.Health}}' portfolio-db` | Xem nhật ký kiểm tra sức khỏe của database MySQL. |
| **Xem cấu trúc mạng Docker** | `docker network ls` | Liệt kê các mạng ảo (`portfolio_frontend`, `portfolio_backend`, `portfolio_monitoring`). |
| **Xem các ổ cứng ảo (Volumes)** | `docker volume ls` | Liệt kê các volume lưu trữ lâu dài (`db_data`, `wp_data`, `grafana_data`...). |

---

### Nhóm 3: Xem Logs chi tiết từng Container

| Hành động | Câu lệnh | Giải thích chi tiết |
|---|---|---|
| **Xem log của tất cả container cùng lúc** | `docker compose logs` | In log của toàn bộ hệ thống. |
| **Theo dõi log trực tiếp (Live tail)** | `docker compose logs -f` | Chế độ theo dõi liên tục, có sự kiện mới sẽ in ra ngay. |
| **Xem log của riêng Nginx** | `docker compose logs -f nginx` | Theo dõi traffic truy cập, mã HTTP (200, 404, 502) và rate-limiting. |
| **Xem log của WordPress** | `docker compose logs -f wordpress` | Theo dõi log Apache và mã lỗi PHP của website. |
| **Xem 50 dòng log gần nhất của MySQL** | `docker compose logs --tail=50 db` | Xem tiến trình nạp CSDL và kiểm tra lỗi kết nối MySQL. |
| **Xem log của Promtail (Bộ thu thập log)** | `docker compose logs -f promtail` | Kiểm tra xem Promtail có gửi log thành công lên Loki hay không. |

---

### Nhóm 4: Truy cập trực tiếp vào Shell bên trong Container

| Hành động | Câu lệnh | Giải thích chi tiết |
|---|---|---|
| **Vào Shell của Nginx (Alpine Linux)** | `docker exec -it portfolio-nginx sh` | Dùng để kiểm tra file cấu hình `/etc/nginx/nginx.conf`, file log. |
| **Vào Shell của WordPress (Debian)** | `docker exec -it portfolio-wordpress bash` | Kiểm tra mã nguồn WordPress tại `/var/www/html/`. |
| **Vào Shell của MySQL Database** | `docker exec -it portfolio-db bash` | Kiểm tra các file dữ liệu tại `/var/lib/mysql`. |
| **Vào Shell của Grafana** | `docker exec -it portfolio-grafana sh` | Kiểm tra file provisioning dashboard. |
| **Thoát khỏi Shell container** | `exit` | Gõ `exit` để quay trở lại PowerShell/CMD của máy chủ. |

---

### Nhóm 5: Thao tác & Sao lưu / Phục hồi Database MySQL

| Hành động | Câu lệnh | Giải thích chi tiết |
|---|---|---|
| **Mở trực tiếp MySQL CLI** | `docker exec -it portfolio-db mysql -u portfolio_user -pDb@Portfolio2026 portfolio` | Đăng nhập dòng lệnh CSDL `portfolio` bằng user chuyên biệt. |
| **Mở MySQL CLI bằng quyền root** | `docker exec -it portfolio-db mysql -u root -pRoot@Portfolio2026` | Đăng nhập tài khoản tối cao của MySQL. |
| **Sao lưu CSDL ra file .sql (Backup)** | `docker exec portfolio-db mysqldump -u root -pRoot@Portfolio2026 portfolio > backup_portfolio.sql` | Xuất toàn bộ cấu trúc và dữ liệu bảng ra file `backup_portfolio.sql`. |
| **Phục hồi CSDL từ file .sql (Restore)** | `Get-Content backup_portfolio.sql \| docker exec -i portfolio-db mysql -u root -pRoot@Portfolio2026 portfolio` | Nạp lại dữ liệu từ file backup vào CSDL. |
| **Xem danh sách các bảng trong DB** | `docker exec portfolio-db mysql -u root -pRoot@Portfolio2026 -e "USE portfolio; SHOW TABLES;"` | Liệt kê nhanh các bảng `wp_posts`, `wp_users`, `wp_options`... |

---

### Nhóm 6: Quản trị WordPress bằng dòng lệnh (WP-CLI)

| Hành động | Câu lệnh | Giải thích chi tiết |
|---|---|---|
| **Kiểm tra thông tin cài đặt WordPress** | `docker compose --profile setup run --rm wpcli wp core version` | Xem phiên bản WordPress đang chạy. |
| **Xem danh sách người dùng quản trị** | `docker compose --profile setup run --rm wpcli wp user list` | Liệt kê các tài khoản: ID, Login Name, Display Name, Email, Role. |
| **Đổi mật khẩu tài khoản admin** | `docker compose --profile setup run --rm wpcli wp user update admin --user_pass="MatKhauMoi123"` | Đặt lại mật khẩu admin ngay lập tức nếu bị quên. |
| **Xem danh sách Theme đang cài** | `docker compose --profile setup run --rm wpcli wp theme list` | Xem các theme, trạng thái `active` của theme `astra`. |
| **Xem danh sách Plugin** | `docker compose --profile setup run --rm wpcli wp plugin list` | Xem các plugin đang kích hoạt. |
| **Xóa bộ nhớ đệm (Cache Flush)** | `docker compose --profile setup run --rm wpcli wp cache flush` | Làm mới cache WordPress khi có thay đổi nội dung. |
| **Chạy lại toàn bộ script setup mẫu** | `docker compose --profile setup run --rm wpcli` | Chạy lại kịch bản khởi tạo trang chủ, dự án, blog, menu. |

---

### Nhóm 7: Kiểm tra Giám sát (Prometheus, Grafana, Loki)

| Hành động | Câu lệnh | Giải thích chi tiết |
|---|---|---|
| **Chạy toàn bộ bài test kiểm thử tự động** | `python test_all.py` | Kiểm tra cả 3 phân hệ Prometheus, Loki, Grafana trong 5 giây. |
| **Kiểm tra Prometheus Targets bằng cURL** | `curl -s http://localhost:9090/api/v1/targets` | Xem danh sách target và trạng thái `"health":"up"`. |
| **Kiểm tra Metrics của Nginx Exporter** | `curl -s http://localhost:8000/stub_status` | Xem kết nối Nginx: `Active connections`, `server accepts handled requests`. |
| **Kiểm tra sức khỏe Grafana API** | `curl -s http://localhost:3001/api/health` | Kiểm tra database Grafana có `ok` không. |
| **Truy vấn LogQL trực tiếp từ Loki** | `curl -G -s "http://localhost:3101/loki/api/v1/query" --data-urlencode "query={container=\"portfolio-nginx\"}"` | Truy vấn dòng log của container Nginx qua API LogQL. |

---

### Nhóm 8: Kiểm tra Bảo mật & Hardening Nginx

| Hành động | Câu lệnh | Mục tiêu kiểm chứng |
|---|---|---|
| **Kiểm tra Security Headers** | `curl -I http://localhost:8000` | Kiểm tra các header: `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Content-Security-Policy`. |
| **Kiểm tra chặn file nhạy cảm `.env`** | `curl -I http://localhost:8000/.env` | Phải trả về mã **`403 Forbidden`** (Chặn truy cập file bí mật). |
| **Kiểm tra chặn file `wp-config.php`** | `curl -I http://localhost:8000/wp-config.php` | Phải trả về mã **`403 Forbidden`** (Bảo vệ thông tin kết nối CSDL). |
| **Kiểm tra chặn `xmlrpc.php` (Chống Brute Force)** | `curl -I http://localhost:8000/xmlrpc.php` | Phải trả về mã **`403 Forbidden`** (Chặn tấn công dò mật khẩu qua XML-RPC). |
| **Kiểm tra giấu phiên bản Nginx** | `curl -I http://localhost:8000` | Header `Server: nginx` không được lộ số phiên bản cụ thể (chống lộ lỗ hổng CVE). |
| **Kiểm tra Rate Limiting (Giới hạn tần suất)** | Chạy vòng lặp 20 request liên tục vào `http://localhost:8000/wp-login.php`: sau một số request sẽ nhận mã **`503 Service Temporarily Unavailable`**. |

---

### Nhóm 9: Lệnh Git và Sinh Báo Cáo Tự Động

| Hành động | Câu lệnh | Giải thích chi tiết |
|---|---|---|
| **Xem lịch sử Git Commit (Chuẩn tiêu chí chấm điểm)** | `git log --oneline -n 10` | Hiển thị 10 commit gần nhất theo chuẩn Conventional Commits (`feat:`, `fix:`, `docs:`, `perf:`). |
| **Đẩy code lên GitHub** | `.\push_to_github.bat` | Script đẩy nhánh và commit lên repository từ xa. |
| **Sinh lại file báo cáo Word (.docx)** | `python tao_bao_cao.py` | Tự động biên dịch `BAO_CAO_THUC_HANH_DE_13.md` thành file Word nộp bài đẹp mắt. |
| **Sinh tài liệu ôn thi vấn đáp** | `python tao_tai_lieu_on_thi.py` | Sinh file `TAI_LIEU_ON_THI_VAN_DAP_GIAI_THICH_CODE.docx` để ôn tập lý thuyết và code. |

---

## 5. Quy Trình Biểu Diễn & Vấn Đáp Trước Thầy Cô

Khi bảo vệ đề tài hoặc kiểm tra thực hành, bạn nên thực hiện tuần tự theo kịch bản 6 bước chuyên nghiệp sau:

```
[BƯỚC 1: KHỞI ĐỘNG HỆ THỐNG]
  Nhấp đúp "start.bat" hoặc chạy: docker compose up -d
  Trình bày: Hệ thống gồm 11 Container microservices phân bổ trên 3 mạng nội bộ (frontend, backend, monitoring).

[BƯỚC 2: BIỂU DIỄN WEBSITE PORTFOLIO & ADMIN]
  Mở trình duyệt: http://localhost:8000
  - Giới thiệu trang chủ cá nhân: Hero banner, Kỹ năng công nghệ, Dự án tiêu biểu, Form liên hệ.
  - Mở http://localhost:8000/wp-admin: Đăng nhập tài khoản admin / Admin@Portfolio2026.
  - Biểu diễn tạo bài viết blog mới hoặc chỉnh sửa dự án.

[BƯỚC 3: BIỂU DIỄN QUẢN TRỊ CSDL QUA PHPMYADMIN]
  Mở http://localhost:8000/pma/
  - Đăng nhập user: portfolio_user / Db@Portfolio2026.
  - Cho thầy cô xem Database "portfolio", các bảng wp_posts, wp_users.
  - Nhấn mạnh: phpMyAdmin được định tuyến an toàn qua Nginx (/pma/) và nằm trên mạng nội bộ backend.

[BƯỚC 4: BIỂU DIỄN BẢO MẬT & HARDENING]
  Mở terminal, chạy lệnh chứng minh:
  - curl -I http://localhost:8000/.env -> Mã 403 Forbidden (Chặn rò rỉ biến môi trường).
  - curl -I http://localhost:8000/xmlrpc.php -> Mã 403 Forbidden (Chống DDoS/Brute Force).
  - curl -I http://localhost:8000 -> Có X-Frame-Options, CSP, Server Tokens ẩn.

[BƯỚC 5: BIỂU DIỄN GIÁM SÁT PROMETHEUS & GRAFANA]
  Mở http://localhost:9090: Xem Targets (cadvisor, mysql, nginx, prometheus) đều UP xanh.
  Mở http://localhost:3001 (Grafana):
  - Dashboard 1: "Portfolio - Docker Containers Overview" (CPU, RAM, Network I/O của 11 container từ cAdvisor).
  - Dashboard 2: "Portfolio - Nginx Reverse Proxy & Traffic" (Request rate, Active connections từ Nginx Exporter).
  - Dashboard 3: "Portfolio - MySQL Database Performance" (Queries/sec, Connections, Buffer Pool từ MySQL Exporter).
  - Dashboard 4: "Portfolio - Centralized Logs & LogQL Explorer" (Tra cứu log tập trung qua Loki).

[BƯỚC 6: BIỂU DIỄN KIỂM THỬ TỰ ĐỘNG]
  Chạy lệnh: python test_all.py
  - Toàn bộ kết quả in ra màn hình: Prometheus targets UP, Loki LogQL streams OK, Grafana Health OK.
```

---

## 6. Xử Lý Các Lỗi Thường Gặp (Troubleshooting)

### 🔴 Lỗi 1: `failed to connect to the docker API at npipe:... The system cannot find the file specified`
- **Nguyên nhân:** Phần mềm Docker Desktop chưa được mở hoặc Docker Engine đang trong quá trình khởi động (chưa hoàn tất).
- **Cách khắc phục:**
  1. Mở menu Start của Windows, gõ tìm kiếm **Docker Desktop** và nhấn mở.
  2. Quan sát biểu tượng Docker Desktop ở thanh Taskbar dưới góc phải màn hình, đợi đến khi hiện chữ **"Docker Desktop is running"** (biểu tượng cá voi đứng yên).
  3. Chạy lại `start.bat` hoặc lệnh `docker compose up -d`.

---

### 🔴 Lỗi 2: `bind: address already in use` (Trùng cổng kết nối)
- **Nguyên nhân:** Có một ứng dụng khác trên máy tính đang chiếm dụng cổng `8000`, `3001`, `9090` hoặc cổng dịch vụ khác.
- **Cách khắc phục:**
  1. Mở PowerShell và kiểm tra tiến trình nào đang chiếm cổng (ví dụ cổng 8000):
     ```powershell
     netstat -ano | findstr :8000
     ```
  2. Lấy số PID ở cột cuối cùng và tắt tiến trình đó:
     ```powershell
     taskkill /F /PID <Số_PID>
     ```
  3. Hoặc bạn có thể mở file `.env`, đổi cổng sang giá trị khác (ví dụ `NGINX_PORT=8080`, `GRAFANA_PORT=3002`), sau đó chạy lại `docker compose up -d`.

---

### 🔴 Lỗi 3: "Error establishing a database connection" khi vào WordPress
- **Nguyên nhân:** Container MySQL (`portfolio-db`) đang trong vài giây khởi tạo dữ liệu đầu tiên nên chưa kịp mở cổng tiếp nhận kết nối từ WordPress.
- **Cách khắc phục:**
  - Chờ khoảng 10 - 20 giây để MySQL hoàn thành healthcheck.
  - Kiểm tra trạng thái bằng lệnh:
    ```powershell
    docker compose ps
    ```
    Khi thấy `portfolio-db` chuyển sang trạng thái `(healthy)`, tải lại trang `http://localhost:8000` trên trình duyệt.

---

### 🔴 Lỗi 4: Muốn làm mới toàn bộ hệ thống từ đầu (Clean Reset)
Nếu bạn lỡ chỉnh sửa sai cấu hình hoặc muốn chạy lại bản cài đặt trắng sạch nhất:
```powershell
# 1. Dừng và xóa toàn bộ container và ổ cứng ảo (Volumes)
docker compose down -v

# 2. Khởi động lại toàn bộ
docker compose up -d

# 3. Chạy lệnh nạp nội dung mẫu Portfolio & Theme
docker compose --profile setup run --rm wpcli

# 4. Kiểm tra lại
python test_all.py
```

---
*Tài liệu được soạn thảo hoàn chỉnh cho đề tài Đề 13 - Môn học Triển khai và Quản trị Hệ thống Phần mềm.*
