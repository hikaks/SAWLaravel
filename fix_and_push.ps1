Write-Host "Checking current branch..." -ForegroundColor Yellow
git branch

Write-Host "`nChecking remote..." -ForegroundColor Yellow
git remote -v

Write-Host "`nCreating and switching to main branch..." -ForegroundColor Green
git checkout -b main

Write-Host "`nAdding all files..." -ForegroundColor Green
git add .

Write-Host "`nCommitting changes..." -ForegroundColor Green
git commit -m "Initial commit: Laravel SAW project"

Write-Host "`nPushing to GitHub..." -ForegroundColor Green
git push -u origin main

Write-Host "`nDone! Project has been pushed to GitHub." -ForegroundColor Green
Read-Host "Press Enter to continue"
