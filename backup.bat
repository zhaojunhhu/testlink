rem ---------------------------------------------------------------------------
rem Backup testlink to remote disk
rem Id: backup.bat 2016-01-15 16:09:12 create by ZhaoJun
rem ---------------------------------------------------------------------------

@Echo off

rem Modify the following line for your environment
set DBname=testlink
set DBuser=root
set DBpwd=root
set Ftpserver=182.125.102.211
set Ftpuser=user
set Ftppwd=user
set BACKUPDIR=D:\TestLink

rem Get today's date
For /f "tokens=1-3 delims=/ " %%a in ('date /t') do (set mydate=%%a%%b%%c)
For /f "tokens=1-2 delims=/:" %%a in ('time /t') do (set mytime=%%a%%b)
set date=%mydate%%mytime%

mysqldump -u%DBuser% -p%DBpwd% %DBname% > %BACKUPDIR%\%date%_testlink.sql

Echo open %Ftpserver% >ftp.up
Echo %Ftpuser%>>ftp.up
Echo %Ftppwd%>>ftp.up
Echo Cd .\ >>ftp.up
Echo binary>>ftp.up
Echo put "%BACKUPDIR%\%date%_testlink.sql">>ftp.up
Echo bye>>ftp.up
FTP -s:ftp.up
del ftp.up /q