echo off
set mydir=%CD%
for %%a in (".") do set CURRENT_DIR_NAME=%%~na
cd C:\php
rem Put your path to xRefCoreCompiler.phar
php C:\Users\Semyon\Documents\PHPStorm\xRefCoreCompiler\xRefCoreCompiler\xRefCoreCompiler.phar --projectdir "%mydir%" --skip 1