# UI Components Library

Koleksi komponen UI yang konsisten dan dapat digunakan kembali untuk SPK SAW Laravel application.

## Komponen yang Tersedia

### 1. Button (`<x-ui.button>`)
Komponen button yang konsisten dengan berbagai varian dan ukuran.

**Props:**
- `variant`: primary, secondary, success, warning, danger, info, outline-*, ghost
- `size`: xs, sm, md, lg, xl
- `type`: button, submit, reset
- `loading`: boolean - menampilkan loading spinner
- `disabled`: boolean
- `icon`: class FontAwesome icon
- `iconPosition`: left, right
- `href`: untuk link button

**Contoh Penggunaan:**
```blade
<x-ui.button variant="primary" icon="fas fa-plus">
    Add New
</x-ui.button>

<x-ui.button variant="outline-secondary" size="sm" :loading="true">
    Processing...
</x-ui.button>

<x-ui.button href="/dashboard" variant="success">
    Go to Dashboard
</x-ui.button>
```

### 2. Alert (`<x-ui.alert>`)
Komponen alert untuk notifikasi dan pesan error yang user-friendly.

**Props:**
- `type`: success, error, warning, info
- `title`: judul alert (optional)
- `dismissible`: boolean - dapat ditutup
- `icon`: custom icon (optional)
- `actions`: slot untuk action buttons

**Contoh Penggunaan:**
```blade
<x-ui.alert type="success" :dismissible="true">
    Data berhasil disimpan!
</x-ui.alert>

<x-ui.alert type="error" title="Validation Error">
    <ul class="list-disc list-inside">
        <li>Email wajib diisi</li>
        <li>Password minimal 8 karakter</li>
    </ul>
</x-ui.alert>
```

### 3. Badge (`<x-ui.badge>`)
Komponen badge untuk status dan label.

**Props:**
- `variant`: primary, secondary, success, warning, danger, info
- `size`: xs, sm, md, lg
- `rounded`: boolean - bentuk bulat penuh
- `dot`: boolean - menampilkan dot indikator

**Contoh Penggunaan:**
```blade
<x-ui.badge variant="success">Active</x-ui.badge>
<x-ui.badge variant="warning" :dot="true">Pending</x-ui.badge>
```

### 4. Loading (`<x-ui.loading>`)
Komponen loading indicator dengan berbagai tipe.

**Props:**
- `type`: spinner, dots, pulse
- `size`: xs, sm, md, lg, xl
- `color`: primary, secondary, success, warning, danger, white
- `text`: teks loading (optional)
- `overlay`: boolean - full screen overlay

**Contoh Penggunaan:**
```blade
<x-ui.loading size="lg" text="Processing..." />
<x-ui.loading :overlay="true" text="Please wait..." />
```

### 5. Status Badge (`<x-ui.status-badge>`)
Badge khusus untuk status dengan konfigurasi predefined.

**Props:**
- `status`: active, inactive, pending, completed, failed, processing, draft, published
- `size`: xs, sm, md, lg
- `showIcon`: boolean

**Contoh Penggunaan:**
```blade
<x-ui.status-badge status="active" />
<x-ui.status-badge status="processing" :showIcon="false">
    Custom Text
</x-ui.status-badge>
```

### 6. Table (`<x-ui.table>`)
Wrapper untuk table yang responsive dan konsisten.

**Props:**
- `responsive`: boolean
- `striped`: boolean
- `hover`: boolean
- `bordered`: boolean
- `size`: sm, md, lg

**Contoh Penggunaan:**
```blade
<x-ui.table :responsive="true" :hover="true">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <!-- table content -->
    </tbody>
</x-ui.table>
```

## JavaScript Helpers

### UIHelpers Class
Class JavaScript untuk menangani loading states dan notifikasi.

**Methods:**
- `setButtonLoading(button, loading, loadingText)` - Set loading state untuk button
- `showNotification(message, type, duration)` - Tampilkan notifikasi toast
- `confirmAction(message, title, type)` - Konfirmasi action dengan SweetAlert
- `showLoadingOverlay(text)` - Tampilkan loading overlay
- `hideLoadingOverlay()` - Sembunyikan loading overlay
- `makeRequest(url, options)` - Enhanced AJAX dengan loading states

**Contoh Penggunaan:**
```javascript
// Set button loading
uiHelpers.setButtonLoading(document.getElementById('saveBtn'), true, 'Saving...');

// Show notification
uiHelpers.showNotification('Data saved successfully!', 'success');

// Confirm action
const confirmed = await uiHelpers.confirmAction('Are you sure?', 'Delete Item');

// Make AJAX request with loading
const data = await uiHelpers.makeRequest('/api/data', {
    method: 'POST',
    body: JSON.stringify({...}),
    showLoading: true,
    successMessage: 'Data updated!'
});
```

## Color Accessibility

Semua komponen menggunakan color palette yang telah dioptimasi untuk accessibility dengan contrast ratio yang memenuhi standar WCAG.

## Responsive Design

Semua komponen dirancang mobile-first dan responsive dengan breakpoints yang konsisten.