@echo off

rem -------------------------------------------------------------
rem  Builder console bootstrap file for windows
rem  @author Vladimir Kozhin <affka@affka.ru>
rem -------------------------------------------------------------

set RIABUILDER_PATH=%~dp0

@setlocal
if "%PHP_COMMAND%" == "" set PHP_COMMAND=php.exe
"%PHP_COMMAND%" "%RIABUILDER_PATH%console.php" %*
@endlocal