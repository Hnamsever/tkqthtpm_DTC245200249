@echo off
chcp 65001 >nul
echo ========================================================
echo   KHỞI ĐỘNG LẠI HỆ THỐNG WEBSITE PORTFOLIO (ĐỀ 13)
echo ========================================================
echo.
echo Đang khởi động lại 11 Container...
docker compose restart
if %ERRORLEVEL% equ 0 (
    echo.
    echo Đang kiểm tra trạng thái các phân hệ...
    python test_all.py
    echo.
    echo ========================================================
    echo   HỆ THỐNG ĐÃ ĐƯỢC KHỞI ĐỘNG LẠI THÀNH CÔNG!
    echo   - Website Portfolio: http://localhost:8000
    echo   - Trang Admin:       http://localhost:8000/wp-admin
    echo   - phpMyAdmin:        http://localhost:8000/pma/
    echo   - Grafana Dashboard: http://localhost:3001
    echo   - Prometheus UI:     http://localhost:9090
    echo ========================================================
) else (
    echo.
    echo [LỖI] Không thể khởi động lại hệ thống qua Docker.
)
pause
