@echo off 
start http://fan.ruishengkj.net/crontab/index.php?act=pointsfanyong
ping -n 5 127.1 >nul 5>nul 
taskkill /f /im IEXPLORE.exe 
exit