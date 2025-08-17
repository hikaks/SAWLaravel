@echo off
echo Current remote repositories:
git remote -v

echo.
echo Removing old origin...
git remote remove origin

echo.
echo Adding new origin...
git remote add origin https://github.com/hikaks/SAWLaravel.git

echo.
echo New remote repositories:
git remote -v

echo.
echo Done! Repository has been changed.
pause
