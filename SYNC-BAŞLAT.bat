@echo off
title WCS Auto Sync — bykaraca.local
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0sync-to-local.ps1"
pause
