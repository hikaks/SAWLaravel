@echo off
echo Checking current branch...
git branch

echo.
echo Checking remote...
git remote -v

echo.
echo Creating and switching to main branch...
git checkout -b main

echo.
echo Adding all files...
git add .

echo.
echo Committing changes...
git commit -m "Initial commit: Laravel SAW project"

echo.
echo Pushing to GitHub...
git push -u origin main

echo.
echo Done! Project has been pushed to GitHub.
pause
