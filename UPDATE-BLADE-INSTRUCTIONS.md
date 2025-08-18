# Update Blade Files Instructions

## LANGKAH-LANGKAH UNTUK MEMISAHKAN CSS DAN JAVASCRIPT

### 1. Update File resources/js/app.js

Ganti isi file `/workspace/resources/js/app.js` dengan:

```javascript
import './bootstrap';

// Import utilities and routes first
import './routes';
import './utils';

// Import page-specific modules
import './employees';
import './criterias';
import './evaluations';
import './admin-cache';

// Import existing modules
import './dashboard';
import './charts';

import Alpine from 'alpinejs';

// Import CSS files
import '../css/app.css';
import '../css/custom-styles.css';

window.Alpine = Alpine;

Alpine.start();
```

### 2. Update Blade Files - Hapus @push('styles') dan @push('scripts')

#### File: resources/views/employees/index.blade.php

**HAPUS SECTION INI:**
```blade
@push('scripts')
<script>
// ... semua JavaScript code ...
</script>
@endpush

@push('styles')
<style>
// ... semua CSS code ...
</style>
@endpush
```

**GANTI DENGAN:**
```blade
@section('page-scripts')
@vite(['resources/js/employees.js'])
@endsection
```

#### File: resources/views/criterias/index.blade.php

**HAPUS SECTION INI:**
```blade
@push('scripts')
<script>
// ... semua JavaScript code ...
</script>
@endpush

@push('styles')
<style>
// ... semua CSS code ...
</style>
@endpush
```

**GANTI DENGAN:**
```blade
@section('page-scripts')
@vite(['resources/js/criterias.js'])
@endsection
```

#### File: resources/views/evaluations/index.blade.php

**HAPUS SECTION INI:**
```blade
@push('scripts')
<script>
// ... semua JavaScript code ...
</script>
@endpush

@push('styles')
<style>
// ... semua CSS code ...
</style>
@endpush
```

**GANTI DENGAN:**
```blade
@section('page-scripts')
@vite(['resources/js/evaluations.js'])
@endsection
```

#### File: resources/views/admin/dashboard.blade.php

**HAPUS FAKE CACHE FUNCTIONS:**
```javascript
// Hapus function clearCache() dan warmupCache() yang fake
```

**GANTI DENGAN:**
```blade
@section('page-scripts')
@vite(['resources/js/admin-cache.js'])
@endsection
```

### 3. Update Layout File

#### File: resources/views/layouts/main.blade.php

**TAMBAHKAN DI HEAD:**
```blade
<head>
    <!-- ... existing head content ... -->
    
    <!-- Routes untuk JavaScript -->
    <script>
        window.appRoutes = @json([
            'employees' => [
                'index' => route('employees.index'),
                'show' => route('employees.show', ':id'),
                'edit' => route('employees.edit', ':id'),
                'destroy' => route('employees.destroy', ':id'),
                'restore' => route('employees.restore'),
                'forceDelete' => route('employees.force-delete'),
                'export' => route('employees.export')
            ],
            'criterias' => [
                'index' => route('criterias.index'),
                'show' => route('criterias.show', ':id'),
                'edit' => route('criterias.edit', ':id'),
                'destroy' => route('criterias.destroy', ':id'),
                'restore' => route('criterias.restore'),
                'forceDelete' => route('criterias.force-delete')
            ],
            'evaluations' => [
                'index' => route('evaluations.index'),
                'create' => route('evaluations.create'),
                'destroy' => route('evaluations.destroy', ':id')
            ],
            'results' => [
                'index' => route('results.index'),
                'exportPdf' => route('results.export-pdf'),
                'exportExcel' => route('results.export-excel')
            ],
            'admin' => [
                'cache' => [
                    'clear' => route('admin.cache.clear'),
                    'warmup' => route('admin.cache.warmup')
                ]
            ]
        ]);
        
        // Helper functions
        window.buildRoute = function(routePath, params = {}) {
            let url = routePath;
            Object.keys(params).forEach(key => {
                url = url.replace(`:${key}`, encodeURIComponent(params[key]));
            });
            return url;
        };
        
        window.getCsrfToken = function() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        };
    </script>
</head>
```

### 4. Update Controller untuk Real Cache Management

#### File: app/Http/Controllers/AdminController.php

**TAMBAHKAN IMPORT:**
```php
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
```

**GANTI METHOD clearCache:**
```php
public function clearCache()
{
    try {
        // Clear various caches
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        
        // Clear custom application caches
        Cache::flush();

        return response()->json([
            'success' => true,
            'message' => 'All caches cleared successfully.',
            'cleared' => [
                'application_cache' => true,
                'config_cache' => true,
                'route_cache' => true,
                'view_cache' => true,
                'custom_cache' => true
            ]
        ]);
    } catch (\Exception $e) {
        Log::error('Cache clear failed: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to clear cache: ' . $e->getMessage()
        ], 500);
    }
}
```

**GANTI METHOD warmupCache:**
```php
public function warmupCache()
{
    try {
        // Warmup essential caches
        Artisan::call('config:cache');
        Artisan::call('route:cache');

        return response()->json([
            'success' => true,
            'message' => 'System caches warmed up successfully.',
            'warmed' => [
                'config_cache' => true,
                'route_cache' => true
            ]
        ]);
    } catch (\Exception $e) {
        Log::error('Cache warmup failed: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to warmup cache: ' . $e->getMessage()
        ], 500);
    }
}
```

### 5. Update Routes

#### File: routes/web.php

**TAMBAHKAN ROUTES UNTUK ADMIN CACHE:**
```php
// Admin cache management routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // ... existing routes ...
    
    // Cache management
    Route::post('/cache/clear', [AdminController::class, 'clearCache'])->name('cache.clear');
    Route::post('/cache/warmup', [AdminController::class, 'warmupCache'])->name('cache.warmup');
});
```

### 6. Compile Assets

**JALANKAN PERINTAH INI:**
```bash
npm run build
# atau untuk development
npm run dev
```

### 7. Testing

**PASTIKAN SEMUA BERFUNGSI:**
1. ✅ Employee management (CRUD, restore, export)
2. ✅ Criteria management (CRUD, weight validation)
3. ✅ Evaluation system (create, delete, SAW calculation)
4. ✅ Admin cache management (real clear/warmup)
5. ✅ No JavaScript errors in console
6. ✅ All routes resolve correctly
7. ✅ XSS protection working (HTML escaped)

## KEUNTUNGAN SETELAH PEMISAHAN:

### ✅ **Performance:**
- CSS dan JS ter-minify dan ter-bundle
- Caching yang lebih baik
- Loading time lebih cepat

### ✅ **Maintainability:**
- Code terorganisir per module
- Easier debugging
- Reusable components

### ✅ **Tailwind Integration:**
- CSS classes dapat dioptimasi dengan Tailwind
- Purging unused styles
- Consistent design system

### ✅ **Security:**
- XSS protection dengan HTML escaping
- CSRF token management
- Secure route handling

### ✅ **Development Experience:**
- Hot reload dengan Vite
- Better IDE support
- Modular architecture

## STRUKTUR FILE HASIL:

```
resources/
├── css/
│   ├── app.css (main + imports)
│   ├── employees.css
│   ├── criterias.css
│   ├── evaluations.css
│   ├── results.css
│   └── custom-styles.css (existing)
├── js/
│   ├── app.js (main entry)
│   ├── routes.js (route helpers)
│   ├── utils.js (utilities)
│   ├── employees.js
│   ├── criterias.js
│   ├── evaluations.js
│   ├── admin-cache.js
│   ├── dashboard.js (existing)
│   └── charts.js (existing)
└── views/
    └── (blade files dengan @push dihapus)
```

Ikuti langkah-langkah ini secara berurutan untuk memisahkan CSS dan JavaScript dari blade files dengan sempurna!