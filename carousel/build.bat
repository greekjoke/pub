@echo off

rem ==================================================
rem Build files list and make index.html from template
rem ==================================================

setlocal EnableDelayedExpansion

set PWD=%~dp0
set DIRN=photos
set DIRD=%PWD%%DIRN%
set LF=

rem --------------------------------------------------
rem Read files list
rem --------------------------------------------------

set STR=
rem dir "%DIRD%" /B
for /f tokens^=* %%i in ('where %DIRD%:*') do (
  set STR=!STR!"%DIRN%/%%~nxi",
)

echo !STR! > index.txt

rem --------------------------------------------------
rem Read template
rem --------------------------------------------------

set TPL=
for /f "Tokens=* Delims=" %%x in (%PWD%tpl.html) do (
  set TPL=!TPL!%%x !LF!
)

rem --------------------------------------------------
rem Make index.html
rem --------------------------------------------------

set RES=!TPL:{PLACEHOLDER}=%STR%!
echo !RES! > index.html