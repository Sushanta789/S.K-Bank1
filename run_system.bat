@echo off
setlocal

set "ROOT=%~dp0"
set "XAMPP_DIR=C:\xampp"
set "TARGET_DIR=%XAMPP_DIR%\htdocs\sk-bank"

if exist "%XAMPP_DIR%\xampp-control.exe" (
    start "" "%XAMPP_DIR%\xampp-control.exe"
) else if exist "%XAMPP_DIR%\xampp_start.exe" (
    start "" "%XAMPP_DIR%\xampp_start.exe"
)

if not exist "%TARGET_DIR%" (
    mkdir "%TARGET_DIR%"
)

xcopy "%ROOT%*" "%TARGET_DIR%\" /E /Y /I >nul 2>&1

start "" "http://localhost/sk-bank/index.html"

echo.
echo S.K Bank system is starting...
echo Open the browser when XAMPP is ready.
echo MySQL password: Sushanta@1430
echo.

exit /b 0
