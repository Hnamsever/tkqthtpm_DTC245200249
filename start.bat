@echo off
chcp 65001 >nul
echo ========================================================
echo   KHỞI ĐỘNG HỆ THỐNG WEBSITE PORTFOLIO (ĐỀ 13)
echo ========================================================
echo.

echo 1. Khởi động 11 Container qua Docker Compose...
docker compose up -d
if %ERRORLEVEL% neq 0 (
    echo.
    echo [LỖI] Không thể kết nối với Docker.
    echo Vui lòng đảm bảo Docker Desktop đã được mở và đang chạy (Engine running).
    pause
    exit /b %ERRORLEVEL%
)

echo.
echo 2. Khởi tạo nội dung Portfolio & Theme Astra tự động...
docker compose --profile setup run --rm wpcli

echo.
echo 3. Kiểm tra tự động các phân hệ (Prometheus, Loki, Grafana)...
python test_all.py

echo.
echo ========================================================
echo   HỆ THỐNG ĐÃ SẴN SÀNG!
echo   - Website Portfolio: http://localhost:8000
echo   - Trang Admin:       http://localhost:8000/wp-admin
echo   - phpMyAdmin:        http://localhost:8000/pma/
echo   - Grafana Dashboard: http://localhost:3001
echo   - Prometheus UI:     http://localhost:9090
echo ========================================================
pause
