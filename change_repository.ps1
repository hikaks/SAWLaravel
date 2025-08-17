Write-Host "Current remote repositories:" -ForegroundColor Yellow
git remote -v

Write-Host "`nRemoving old origin..." -ForegroundColor Red
git remote remove origin

Write-Host "`nAdding new origin..." -ForegroundColor Green
git remote add origin https://github.com/hikaks/SAWLaravel.git

Write-Host "`nNew remote repositories:" -ForegroundColor Yellow
git remote -v

Write-Host "`nDone! Repository has been changed." -ForegroundColor Green
Read-Host "Press Enter to continue"
