@ECHO off

SET PHP_BIN=php
SET DEPLOY_DIR=%~dp0

SET DEPLOY_SCRIPT=%DEPLOY_DIR%\akt.php
"%PHP_BIN%" -d safe_mode=off -d html_errors=off -f "%DEPLOY_SCRIPT%" -- %*
