@echo off
chcp 65001 >nul
echo ========================================================
echo   DỪNG HỆ THỐNG WEBSITE PORTFOLIO (ĐỀ 13)
echo ========================================================
echo.
echo Đang dừng và gỡ các container...
docker compose down
if %ERRORLEVEL% equ 0 (
    echo.
    echo ========================================================
    echo   ĐÃ DỪNG HỆ THỐNG THÀNH CÔNG!
    echo   Dữ liệu (Database, WordPress, Logs) vẫn được bảo toàn.
    echo ========================================================
) else (
    echo.
    echo [LỖI] Không thể dừng hệ thống qua Docker.
)
pause
