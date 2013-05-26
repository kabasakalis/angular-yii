@echo off

rem -------------------------------------------------------------
rem  Push to origin
rem -------------------------------------------------------------



@if not exist "%HOME%" @set HOME=%HOMEDRIVE%%HOMEPATH%
@if not exist "%HOME%" @set HOME=%USERPROFILE%

echo Pushing to Origin  %DATE% %hour%:%min%:%secs%

SET /P msg=Enter Commit Message : 

git add . &
git commit -am "%msg%"  & 
git  push --all origin

echo Done.
pause