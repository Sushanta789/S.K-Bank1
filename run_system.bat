@echo off
setlocal

set "ROOT=%~dp0"
set "XAMPP_DIR=C:\xampp"
set "TARGET_DIR=%XAMPP_DIR%\htdocs\sk-bank"

if exist "%XAMPP_DIR%\xampp_start.exe" (
    start "" "%XAMPP_DIR%\xampp_start.exe"
) else if exist "%XAMPP_DIR%\xampp-control.exe" (
    start "" "%XAMPP_DIR%\xampp-control.exe"
)

if not exist "%TARGET_DIR%" (
    mkdir "%TARGET_DIR%"
)

xcopy "%ROOT%*" "%TARGET_DIR%\" /E /Y /I >nul 2>&1

where python >nul 2>&1
if %errorlevel%==0 (
    start "S.K Bank Python API" /D "%ROOT%" python api.py
)

start "" "http://localhost/sk-bank/index.html"

echo.
echo S.K Bank system is starting...
echo Open the browser when XAMPP is ready.
echo Same Wi-Fi device URL: http://10.207.184.55/sk-bank/index.html
echo MySQL password: Sushanta@1430
echo.

exit /b 0
