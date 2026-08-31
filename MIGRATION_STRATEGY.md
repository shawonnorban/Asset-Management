# Laravel Indonesian to English Refactoring Strategy

**Project:** Asset Management System  
**Last Updated:** 2026-08-30  
**Status:** Planning Phase  
**Scope:** Model, Controller, and Database Migration

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Dependency Analysis](#dependency-analysis)
3. [Model Migration Strategy](#model-migration-strategy)
4. [Database Migration Strategy](#database-migration-strategy)
5. [Controller Migration Strategy](#controller-migration-strategy)
6. [View Migration Strategy](#view-migration-strategy)
7. [Import and Reference Updates](#import-and-reference-updates)
8. [Implementation Order](#implementation-order)
9. [Risk Assessment](#risk-assessment)
10. [Rollback Strategy](#rollback-strategy)

---

## Executive Summary

This is a comprehensive refactoring to rename Indonesian-named entities to English across the entire application. The application shows a **hybrid state** with some English models already existing alongside Indonesian ones, plus duplicate table migrations.

### Current State Analysis
- **Status:** In-progress partial migration
- **Duplicate Models:** Aset/Asset, Opname/StockTake, etc. exist side-by-side
- **Duplicate Tables:** Database has both old (aset, opname) and new (assets, stock_takes) tables
- **Controllers:** Mix of Indonesian (Pelaporan/, Penyusutan/Opname/) and English (StockTake/, Depreciation/)
- **Views:** Similar mix with some English (assets/, stock-takes/) and Indonesian (pelaporan/, opname/)

### Migration Scope
- **8 Models** to consolidate
- **8 Controllers** to reorganize
- **Multiple Views** to reorganize
- **Database tables** to reconcile
- **Services** using Indonesian models
- **Routes** to verify
- **Tests** to update

---

## Dependency Analysis

### Model Dependency Graph

```
Asset Dependency Tree:
├── Asset (Aset) - PRIMARY CORE
│   ├── AssetCategory (KategoriAset)
│   ├── AssetLocation (LokasiAset)
│   ├── Employee (Karyawan)
│   ├── AssetDepreciationSetting (AsetPenyusutanSetting)
│   │   └── TaxDepreciationGroup (DjpKelompok)
│   ├── MonthlyDepreciation (PenyusutanBulanan)
│   └── Report (Pelaporan) - ONE WAY REFERENCE
│
StockTake (Opname) - INDEPENDENT
├── User
└── StockTakeDetail (OpnameDetail)
    ├── Asset (Aset)
    ├── AssetLocation (LokasiAset)
    └── Employee (Karyawan)

Report (Pelaporan) - DEPENDENT
├── Asset (Aset)
├── User
└── Feedback
    └── FeedbackReply (potentially)
```

### Critical Relationships

| Model | Current | Target | Dependencies | Dependents |
|-------|---------|--------|--------------|-----------|
| Aset | `app/Models/Aset.php` | **Keep for now** | kategori_id → KategoriAset | Pelaporan, PenyusutanBulanan, OpnameDetail |
| Asset | `app/Models/Asset.php` | Migrate here | category_id → AssetCategory | New system users |
| KategoriAset | `app/Models/KategoriAset.php` | **Keep for now** | Has many Aset | Aset::kategori() |
| AssetCategory | `app/Models/AssetCategory.php` | Migrate here | has many Asset | Asset::category() |
| Opname | `app/Models/Opname.php` | **Keep for now** | has many OpnameDetail | StockTakeDetail |
| StockTake | `app/Models/StockTake.php` | Migrate here | has many StockTakeDetail | - |
| Pelaporan | `app/Models/Pelaporan.php` | **Needs Action** | belongs to Aset | Feedback |
| Report | Does not exist | **Create new** | belongs to Asset | - |
| PenyusutanBulanan | `app/Models/PenyusutanBulanan.php` | **Keep for now** | Aset, AsetPenyusutanSetting | - |
| MonthlyDepreciation | `app/Models/MonthlyDepreciation.php` | Migrate here | Asset, AssetDepreciationSetting | - |

---

## Model Migration Strategy

### Phase 1: Models Already Completed (No Action Needed)

The following models have already been created in English versions:

1. **Asset** (`app/Models/Asset.php`)
   - Table: `assets`
   - Status: ✅ Complete
   - Action: Verify relationships point to English models

2. **AssetCategory** (`app/Models/AssetCategory.php`)
   - Table: `asset_categories`
   - Status: ✅ Complete
   - Action: Verify relationships point to English models

3. **AssetLocation** (`app/Models/AssetLocation.php`)
   - Table: `asset_locations`
   - Status: ✅ Complete
   - Action: Verify relationships point to English models

4. **StockTake** (`app/Models/StockTake.php`)
   - Table: `stock_takes`
   - Status: ✅ Complete
   - Action: Verify relationships point to English models

5. **StockTakeDetail** (`app/Models/StockTakeDetail.php`)
   - Table: `stock_take_details`
   - Status: ✅ Complete
   - Action: Verify relationships point to StockTake

6. **MonthlyDepreciation** (`app/Models/MonthlyDepreciation.php`)
   - Table: `depreciation_monthly`
   - Status: ✅ Complete
   - Action: Verify relationships point to Asset

7. **AssetDepreciationSetting** (`app/Models/AssetDepreciationSetting.php`)
   - Table: `asset_depreciation_settings`
   - Status: ✅ Complete
   - Action: Verify relationships point to Asset

### Phase 2: Models Still Using Indonesian (Critical - Need Action)

These models must be either updated or migrated off:

#### 1. Aset → Asset (MIGRATION REQUIRED)

**Current State:**
```php
// app/Models/Aset.php
protected $table = 'aset';
public function kategori() => KategoriAset
public function lokasi() => LokasiAset
public function karyawan() => Karyawan
public function penyusutanSetting() => AsetPenyusutanSetting
public function penyusutanBulanan() => PenyusutanBulanan
```

**Target State:**
Use `Asset` model which already exists with `assets` table

**Action Items:**
1. ✅ Asset model exists - verify it has all relationships from Aset
2. Update relationships to use English model names:
   - `kategori()` → `category()`
   - `lokasi()` → `location()`
   - `karyawan()` → `employee()`
   - `penyusutanSetting()` → `depreciationSetting()`
   - `penyusutanBulanan()` → `monthlyDepreciations()`
3. Verify Asset relates to correct English models (Category, Location, Employee)

#### 2. KategoriAset → AssetCategory (MIGRATION REQUIRED)

**Current State:**
```php
// app/Models/KategoriAset.php
protected $table = 'kategori_aset';
public function asets() => Aset
```

**Target State:**
Use existing `AssetCategory` model

**Action Items:**
1. ✅ AssetCategory model exists
2. Verify it has `hasMany(Asset)` relationship
3. Update relationship method names if needed

#### 3. LokasiAset → AssetLocation (MIGRATION REQUIRED)

**Current State:**
```php
// app/Models/LokasiAset.php
protected $table = 'lokasi_aset';
public function assets() => Aset
```

**Target State:**
Use existing `AssetLocation` model

**Action Items:**
1. ✅ AssetLocation model exists
2. Verify it has `hasMany(Asset)` relationship

#### 4. Opname → StockTake (MIGRATION REQUIRED)

**Current State:**
```php
// app/Models/Opname.php
protected $table = 'opname';
public function user() => User
public function details() => OpnameDetail
```

**Target State:**
Use existing `StockTake` model with updated relationships

**Action Items:**
1. ✅ StockTake model exists
2. Verify `details()` returns `StockTakeDetail` instead of `OpnameDetail`
3. Test relationship loading

#### 5. OpnameDetail → StockTakeDetail (MIGRATION REQUIRED)

**Current State:**
```php
// app/Models/OpnameDetail.php
protected $table = 'opname_detail';
public function opname() => Opname
public function aset() => Aset
public function lokasi() => LokasiAset
public function karyawan() => Karyawan
```

**Target State:**
Use existing `StockTakeDetail` model

**Action Items:**
1. ✅ StockTakeDetail model exists
2. Verify relationships:
   - `opname()` → `stockTake()` (points to StockTake)
   - `aset()` → `asset()` (points to Asset)
   - `lokasi()` → `location()` (points to AssetLocation)
   - `karyawan()` → `employee()` (points to Employee)

#### 6. Pelaporan → Report (MODEL CREATION REQUIRED)

**Current State:**
```php
// app/Models/Pelaporan.php
protected $table = 'pelaporan';
protected $fillable = ['judul', 'deskripsi', 'status', 'aset_id', 'user_id'];
public function aset() => Aset
public function user() => User
public function feedbacks() => Feedback (potentially - needs verification)
```

**Target State:**
Create new `Report` model at `app/Models/Report.php`

**Action Items:**
1. Create new model file
2. Set `$table = 'reports'`
3. Define fillable properties with English names
4. Create relationships pointing to English models (Asset, User)
5. Handle feedback relationship if used

**New Model Template:**
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;
    
    protected $table = 'reports';
    
    protected $fillable = [
        'title',
        'description',
        'status',
        'asset_id',
        'user_id',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Report belongs to one asset
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
    
    // Report is created by a user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

#### 7. PenyusutanBulanan → MonthlyDepreciation (MIGRATION REQUIRED)

**Current State:**
```php
// app/Models/PenyusutanBulanan.php
protected $table = 'penyusutan_bulanan';
public function aset() => Aset
public function user() => User
```

**Target State:**
Use existing `MonthlyDepreciation` model

**Action Items:**
1. ✅ MonthlyDepreciation model exists
2. Verify relationships point to Asset (not Aset) and User
3. Verify all fillable properties are correct

#### 8. AsetPenyusutanSetting → AssetDepreciationSetting (MIGRATION REQUIRED)

**Current State:**
```php
// app/Models/AsetPenyusutanSetting.php
protected $table = 'aset_penyusutan_setting';
public function aset() => Aset
public function djpKelompok() => DjpKelompok
```

**Target State:**
Use existing `AssetDepreciationSetting` model

**Action Items:**
1. ✅ AssetDepreciationSetting model exists
2. Verify relationships point to Asset and TaxDepreciationGroup
3. Verify relationship method names are correct

---

## Database Migration Strategy

### Current Database State

The database shows a **dual-schema pattern** with both old and new tables:

```
Old Tables (Indonesian)          New Tables (English)
├── aset                        ├── assets
├── kategori_aset               ├── asset_categories
├── lokasi_aset                 ├── asset_locations
├── opname                       ├── stock_takes
├── opname_detail                ├── stock_take_details
├── pelaporan                    ├── reports (exists in migration, needs data migration)
├── penyusutan_bulanan           ├── depreciation_monthly
├── aset_penyusutan_setting      └── asset_depreciation_settings
```

### Migration Order (Critical for Foreign Keys)

**Level 1 - Independent Tables (No Dependencies)**
1. `asset_categories` ← `kategori_aset`
2. `asset_locations` ← `lokasi_aset`
3. `tax_depreciation_groups` ← `djp_kelompok` (if not already done)

**Level 2 - Reference Level 1**
4. `assets` ← `aset` (references categories, locations)
5. `asset_depreciation_settings` ← `aset_penyusutan_setting` (references assets)

**Level 3 - Reference Level 2**
6. `depreciation_monthly` ← `penyusutan_bulanan` (references assets)
7. `stock_takes` ← `opname` (references users)
8. `stock_take_details` ← `opname_detail` (references stock_takes, assets, locations)
9. `reports` ← `pelaporan` (references assets, users)

### Data Migration Approach

**Option A: Parallel Running (RECOMMENDED - Lower Risk)**

Maintain both old and new tables running in parallel:
1. Keep old tables as-is
2. Keep new tables populated
3. Use application logic to sync data where needed
4. Gradually migrate controllers to use new tables
5. Once all code updated, archive old tables
6. **Timeline:** 2-3 sprint cycles

**Option B: Big-Bang Migration (Higher Risk)**

1. Create new tables
2. Write comprehensive data migration scripts
3. Update all code at once
4. Switch to new tables in single deployment
5. Archive old tables after verification period
6. **Timeline:** 1 sprint cycle + careful testing

**Recommended Approach: Option A (Parallel Running)**

### Data Migration Scripts Needed

Create Laravel migration files to:

```
1. Verify data integrity in old tables
2. Copy data with ID mapping where necessary
3. Create necessary indexes on new tables
4. Validate referential integrity
5. Handle edge cases (deleted soft-delete records, etc.)
```

### Special Considerations

**1. Foreign Key Constraints**
- Disable foreign key checks during data migration
- Verify constraints after migration
- Test cascade behaviors (update/delete)

**2. ID Preservation**
- Preserve original IDs to maintain foreign key relationships
- Document ID mapping if needed
- Test relationship loading

**3. Timestamps**
- Preserve `created_at` and `updated_at` values
- Document migration timestamp if needed

**4. Soft Deletes**
- Check if any models use `SoftDeletes` trait
- Ensure `deleted_at` is handled in migration

---

## Controller Migration Strategy

### Current Controller Structure

```
Current (Mixed):
├── Opname/
│   └── OpnameDetailController.php
├── Pelaporan/
│   ├── CekPelaporanController.php
│   ├── PelaporanMasukController.php
│   ├── PelaporanSelesaiController.php
│   └── TambahPelaporanController.php
├── Penyusutan/
│   ├── PenyusutanController.php
│   ├── PenyusutanDisposalController.php
│   └── PenyusutanSettingController.php

Already Refactored (English):
├── StockTake/
│   ├── StockTakeController.php
│   └── StockTakeDetailController.php
├── Depreciation/
│   ├── DepreciationController.php
│   ├── DepreciationSettingController.php
│   └── DisposalController.php
```

### Controllers Requiring Migration

#### 1. Opname Controllers → StockTake (MOSTLY COMPLETE)

**Current:**
- `App\Http\Controllers\Opname\OpnameDetailController`

**Target:**
- `App\Http\Controllers\StockTake\StockTakeDetailController` ✅ (Already exists)

**Action Items:**
- [ ] Verify `StockTakeDetailController` has all methods from `OpnameDetailController`
- [ ] Update model references: `OpnameDetail` → `StockTakeDetail`, `Opname` → `StockTake`
- [ ] Update view references: `opname.input` → `stock-takes.input`
- [ ] Verify route integration in `routes/web.php`
- [ ] Test all CRUD operations
- [ ] Remove old `Opname\` directory

#### 2. Pelaporan Controllers → Reports (MAJOR REFACTORING)

**Current:**
```
App\Http\Controllers\Pelaporan\{
    - CekPelaporanController       → App\Http\Controllers\Reports\CheckReportController
    - PelaporanMasukController     → App\Http\Controllers\Reports\IncomingReportController
    - PelaporanSelesaiController   → App\Http\Controllers\Reports\CompletedReportController
    - TambahPelaporanController    → App\Http\Controllers\Reports\CreateReportController
}
```

**Action Items for Each:**

**A. CekPelaporanController → CheckReportController**
- [ ] Create new file: `app/Http/Controllers/Reports/CheckReportController.php`
- [ ] Copy methods from old controller
- [ ] Update class name to `CheckReportController`
- [ ] Update namespace to `App\Http\Controllers\Reports`
- [ ] Replace model references:
  ```
  Pelaporan → Report
  Feedback → Feedback (keep as-is)
  FeedbackReply → FeedbackReply (keep as-is)
  ```
- [ ] Update view paths: `cek-pelaporan.*` → `reports.check.*`
- [ ] Update method names if they reference Indonesian patterns
- [ ] Update route registration

**B. PelaporanMasukController → IncomingReportController**
- [ ] Create: `app/Http/Controllers/Reports/IncomingReportController.php`
- [ ] Update model references: `Pelaporan` → `Report`
- [ ] Update view paths: `pelaporan-masuk.*` → `reports.incoming.*`
- [ ] Update relationship method calls: `aset()` → `asset()`

**C. PelaporanSelesaiController → CompletedReportController**
- [ ] Create: `app/Http/Controllers/Reports/CompletedReportController.php`
- [ ] Update model references: `Pelaporan` → `Report`
- [ ] Update view paths: `pelaporan-selesai.*` → `reports.completed.*`

**D. TambahPelaporanController → CreateReportController**
- [ ] Create: `app/Http/Controllers/Reports/CreateReportController.php`
- [ ] Update model references: `Pelaporan` → `Report`
- [ ] Update view paths: `tambah-pelaporan.*` → `reports.create.*`

#### 3. Penyusutan Controllers → Depreciation (MOSTLY COMPLETE)

**Current:**
```
App\Http\Controllers\Penyusutan\{
    - PenyusutanController         → App\Http\Controllers\Depreciation\DepreciationController ✅
    - PenyusutanDisposalController → App\Http\Controllers\Depreciation\DisposalController ✅
    - PenyusutanSettingController  → App\Http\Controllers\Depreciation\SettingController ✅
}
```

**Status:** Already refactored, verify:
- [ ] Controllers exist in `Depreciation/` directory ✅
- [ ] Model references: `PenyusutanBulanan` → `MonthlyDepreciation`, `AsetPenyusutanSetting` → `AssetDepreciationSetting`
- [ ] View paths updated
- [ ] Routes in `web.php` updated
- [ ] Services updated (see below)

### Controller Migration Checklist Template

For each controller:

```markdown
## ControllerName Migration

**Files Involved:**
- Old: `app/Http/Controllers/Folder/OldController.php`
- New: `app/Http/Controllers/NewFolder/NewController.php`

**Model References to Update:**
- [ ] Model imports updated
- [ ] Model instantiation updated
- [ ] Relationship calls updated (e.g., aset() → asset())

**View References to Update:**
- [ ] All view('folder.file') paths updated
- [ ] View data variable names updated

**Route References:**
- [ ] route() helper calls updated
- [ ] Route names match routes/web.php

**Method-Level Changes:**
- [ ] Method names Indonesianed → English (if applicable)
- [ ] Variable names inside methods updated
- [ ] Comments/docblocks translated or updated

**Testing:**
- [ ] Unit tests exist and pass
- [ ] Feature tests exist and pass
- [ ] Manual testing in browser
```

---

## View Migration Strategy

### Current View Structure Analysis

```
Indonesian Views                  English Views (Parallel)
├── aset/                        ├── assets/
├── kategori/                    ├── categories/
├── lokasi/                      ├── locations/
├── opname/                      ├── stock-takes/
├── penyusutan/                  ├── depreciation/
├── pelaporan-masuk/             ├── incoming-reports/
├── pelaporan-selesai/           ├── completed-reports/
├── cek-pelaporan/               (No direct equivalent yet)
├── tambah-pelaporan/            (No direct equivalent yet)
└── karyawan/                    ├── employees/
```

### View Migration Plan

#### Phase 1: Create Missing Report Views

Create new view directories for Report (Pelaporan) controllers:

1. **`resources/views/reports/`** (New directory)
   ```
   reports/
   ├── check/
   │   ├── index.blade.php          (from cek-pelaporan/index.blade.php)
   │   └── detail.blade.php         (from cek-pelaporan/detail.blade.php)
   ├── incoming/
   │   ├── index.blade.php          (from pelaporan-masuk/index.blade.php)
   │   └── detail.blade.php         (from pelaporan-masuk/detail.blade.php)
   ├── completed/
   │   ├── index.blade.php          (from pelaporan-selesai/index.blade.php)
   │   └── detail.blade.php         (from pelaporan-selesai/detail.blade.php)
   └── create/
       ├── index.blade.php          (from tambah-pelaporan/index.blade.php)
       └── form.blade.php           (shared form component)
   ```

#### Phase 2: Update View References in Controllers

For each controller being migrated:

1. **Audit current view() calls**
   ```bash
   grep -r "view(" app/Http/Controllers/Pelaporan/
   grep -r "view(" app/Http/Controllers/Penyusutan/
   grep -r "view(" app/Http/Controllers/Opname/
   ```

2. **Update view paths** in new controllers
   ```php
   // Old
   return view('pelaporan-masuk.index', ...);
   
   // New
   return view('reports.incoming.index', ...);
   ```

3. **Update data variable names** if they reference Indonesian models
   ```php
   // Old
   return view('...', ['pelaporans' => ...]);
   
   // New
   return view('...', ['reports' => ...]);
   ```

#### Phase 3: Update View Content

Within each view file:

1. **Update model/controller references**
   ```blade
   <!-- Old -->
   {{ $pelaporan->aset->nama_aset }}
   
   <!-- New -->
   {{ $report->asset->asset_name }}
   ```

2. **Update route() calls**
   ```blade
   <!-- Old -->
   {{ route('pelaporan.update', $pelaporan->id) }}
   
   <!-- New -->
   {{ route('reports.update', $report->id) }}
   ```

3. **Update form paths**
   ```blade
   <!-- Old -->
   action="{{ route('pelaporan.store') }}"
   
   <!-- New -->
   action="{{ route('reports.store') }}"
   ```

4. **Update variable names in loops**
   ```blade
   <!-- Old -->
   @foreach($pelaporans as $pelaporan)
   
   <!-- New -->
   @foreach($reports as $report)
   ```

#### Phase 4: Keep Old Views Temporarily

- Keep original views during transition
- Add deprecation comments at top of old view files
- Document when they'll be removed
- Allows rollback if issues arise

---

## Import and Reference Updates

### High-Risk Areas Requiring Import Updates

#### 1. Service Layer

**Files to Update:**

1. **`app/Services/PenyusutanService.php`** ⚠️ CRITICAL
   - Current imports:
     ```php
     use App\Models\Aset;
     use App\Models\PenyusutanBulanan;
     use App\Models\AsetPenyusutanSetting;
     ```
   - Must update to:
     ```php
     use App\Models\Asset;
     use App\Models\MonthlyDepreciation;
     use App\Models\AssetDepreciationSetting;
     ```
   - All method references to these models must be updated
   - Complex business logic inside service - needs thorough testing

2. **`app/Services/DepreciationService.php`** ✅
   - Already updated (confirmed in grep results)
   - Verify it works with new models

3. **`app/Services/AssetSpecService.php`**
   - Check if it references Indonesian models
   - Update imports if needed

4. **`app/Services/AuditTrailService.php`**
   - Check if it references Indonesian models
   - Audit trail may need to track model name changes

#### 2. Database Seeders

**Files to Update:**

1. **`database/seeders/AssetSeeder.php`** ⚠️ CRITICAL
   - Current: Creates `Aset` records
   - Target: Create `Asset` records with new column names
   - May need to handle data mapping
   - Script:
     ```php
     // Old
     Aset::create([
         'kode_aset' => '...',
         'nama_aset' => '...',
         'kategori_id' => ...,
     ]);
     
     // New
     Asset::create([
         'asset_code' => '...',
         'asset_name' => '...',
         'category_id' => ...,
     ]);
     ```

2. **`database/seeders/AssetCategorySeeder.php`** ⚠️
   - Current: Creates `KategoriAset` records
   - Target: Create `AssetCategory` records
   - Update column names: `nama_kategori` → `category_name`

3. **`database/seeders/AssetLocationSeeder.php`** ⚠️
   - Current: Creates `LokasiAset` records
   - Target: Create `AssetLocation` records
   - Update column names: `nama_lokasi` → `location_name`

4. **Other seeders:**
   - `EmployeeSeeder` (check for Karyawan references)
   - `RoleSeeder`, `UserSeeder`, etc.

#### 3. Database Factories

**Files to Check:**

1. **`database/factories/UserFactory.php`** ✅
   - Already exists and is English

**Factories to Create (If Needed):**
- `AssetFactory`
- `AssetCategoryFactory`
- `AssetLocationFactory`
- `StockTakeFactory`
- `ReportFactory`

#### 4. Routes

**File: `routes/web.php`**

**Current State:**
- Most routes already use English names
- Routes exist for: `stock-takes.*`, `depreciation.*`

**Items to Verify/Update:**

1. Search for Indonesian route groups:
   ```bash
   grep -i "opname\|pelaporan\|penyusutan" routes/web.php
   ```

2. Expected updated routes:
   ```php
   // Old (if any remain)
   Route::group(['prefix' => 'pelaporan'], function () { ... });
   
   // New
   Route::group(['prefix' => 'reports'], function () { ... });
   ```

#### 5. Configuration Files

**Files to Check:**

1. **`config/permission.php`** (if using Spatie Permission)
   - Check if permission names are hardcoded
   - Example: Look for 'pelaporan.create' → 'reports.create'

2. **`config/menu.php`** (if using custom menu config)
   - Update menu item references to models/controllers
   - Search for Indonesian names

#### 6. Middleware & Policies

**Files to Check:**

1. **Policy Classes** in `app/Policies/`
   - Check for references to Indonesian models
   - Update imports and method parameters

2. **Custom Middleware**
   - Look for hardcoded Indonesian model/controller references

#### 7. Tests

**Files to Update:**

1. **`tests/Feature/PermissionAndRouteContractTest.php`**
   - Currently has:
     ```php
     $this->assertFalse(Route::has('aset.index'));
     ```
   - Status: Already testing for removed routes ✅

2. **Other feature tests** (if any)
   - Search for tests using Indonesian model names
   - Update to use English model names

#### 8. Global Search Patterns

Use these search patterns to find all references:

```bash
# Search for imports
grep -r "use App\\Models\\Aset[^a-zA-Z]" app/
grep -r "use App\\Models\\Opname[^a-zA-Z]" app/
grep -r "use App\\Models\\Pelaporan[^a-zA-Z]" app/
grep -r "use App\\Models\\Penyusutan" app/
grep -r "use App\\Models\\KategoriAset[^a-zA-Z]" app/
grep -r "use App\\Models\\LokasiAset[^a-zA-Z]" app/

# Search for model instantiation in views
grep -r "Aset::" resources/views/
grep -r "Opname::" resources/views/
grep -r "Pelaporan::" resources/views/

# Search for relationship method calls
grep -r "->aset(" app/
grep -r "->kategori(" app/
grep -r "->lokasi(" app/
grep -r "->opname(" app/
grep -r "->pelaporan(" app/
```

---

## Implementation Order

### Sprint Structure (Recommended Timeline)

#### Sprint 1: Foundation (Week 1-2)

**Goal:** Ensure English models are properly configured

**Tasks:**

1. **[Day 1-2] Audit English Models** ⚠️ CRITICAL
   ```
   - [ ] Asset: verify all relationships from Aset exist
   - [ ] AssetCategory: verify belongsTo/hasMany correct
   - [ ] AssetLocation: verify belongsTo/hasMany correct
   - [ ] StockTake: verify relationships to User and StockTakeDetail
   - [ ] StockTakeDetail: verify relationships updated
   - [ ] MonthlyDepreciation: verify relationships updated
   - [ ] AssetDepreciationSetting: verify relationships updated
   
   Expected: All English models should have relationships pointing to
             other English models, not Indonesian ones
   ```

2. **[Day 2-3] Create Report Model**
   ```
   - [ ] Create app/Models/Report.php
   - [ ] Define relationships (belongsTo Asset, User)
   - [ ] Add timestamps
   - [ ] Verify Report table exists in migrations
   ```

3. **[Day 3-4] Audit Service Layer**
   ```
   - [ ] Review PenyusutanService.php for Indonesian model usage
   - [ ] Review DepreciationService.php for consistency
   - [ ] Review AssetSpecService.php
   - [ ] Document all service method calls using Indonesian models
   ```

4. **[Day 4-5] Plan Database Migration**
   ```
   - [ ] Review existing migrations for both schemas
   - [ ] Identify data mapping needs
   - [ ] Document ID relationships
   - [ ] Create data migration scripts
   ```

5. **[Day 5] Testing Foundation**
   ```
   - [ ] Run existing tests to get baseline
   - [ ] Document failing tests
   - [ ] Set up CI/CD for test automation
   ```

**Validation:**
```bash
# Run tests to ensure no regressions
php artisan test

# Verify models load correctly
php artisan tinker
> App\Models\Asset::first();
> App\Models\StockTake::first();
```

---

#### Sprint 2: Controller Migration (Week 3-4)

**Goal:** Migrate all controllers to English and new namespaces

**Tasks:**

1. **[Day 1-2] Migrate Depreciation Controllers** ⚠️ CRITICAL
   ```
   - [ ] Verify Depreciation/DepreciationController exists
   - [ ] Verify Depreciation/SettingController exists
   - [ ] Verify Depreciation/DisposalController exists
   - [ ] Remove/archive Penyusutan/ directory
   - [ ] Test all depreciation routes
   - [ ] Verify service layer integration
   ```

2. **[Day 2-3] Migrate StockTake Controllers**
   ```
   - [ ] Verify StockTake/StockTakeController exists
   - [ ] Verify StockTake/StockTakeDetailController exists and has all methods
   - [ ] Remove/archive Opname/ directory
   - [ ] Test all stock take routes
   - [ ] Verify model relationships work
   ```

3. **[Day 4-5] Create Report Controllers**
   ```
   - [ ] Create Reports/CheckReportController
   - [ ] Create Reports/IncomingReportController
   - [ ] Create Reports/CompletedReportController
   - [ ] Create Reports/CreateReportController
   - [ ] Update model references: Pelaporan → Report
   - [ ] Copy methods from Pelaporan/* controllers
   - [ ] Archive Pelaporan/ directory
   ```

4. **[Day 6-7] Update Routes**
   ```
   - [ ] Update route prefixes
   - [ ] Update controller namespaces in routes/web.php
   - [ ] Verify all routes still work
   - [ ] Test permission middleware
   - [ ] Document route name changes for frontend
   ```

**Validation:**
```bash
# List all routes
php artisan route:list | grep -E "stock|depreciation|report"

# Test route access
php artisan test Feature/PermissionAndRouteContractTest

# Manual browser testing
# - Test each major feature
# - Verify redirects work
# - Check error messages
```

---

#### Sprint 3: Service & Database Migration (Week 5-6)

**Goal:** Update service layer and handle database schema

**Tasks:**

1. **[Day 1-2] Update PenyusutanService**
   ```
   - [ ] Update imports: Aset → Asset, etc.
   - [ ] Update all method calls
   - [ ] Update variable names
   - [ ] Create/update unit tests for service
   - [ ] Test with real data
   ```

2. **[Day 2-3] Update Database Seeders**
   ```
   - [ ] Update AssetSeeder to use Asset model
   - [ ] Update AssetCategorySeeder
   - [ ] Update AssetLocationSeeder
   - [ ] Update any other seeders using Indonesian models
   - [ ] Test seeding produces correct data
   ```

3. **[Day 4-5] Data Migration to English Tables**
   ```
   - [ ] Create data migration scripts
   - [ ] Test migration with backup database
   - [ ] Verify data integrity
   - [ ] Verify foreign key relationships
   - [ ] Test cascade behaviors
   ```

4. **[Day 6-7] Parallel Running Tests**
   ```
   - [ ] Set application to read from both old and new tables
   - [ ] Verify sync between schemas
   - [ ] Test all features
   - [ ] Stress test with realistic data volumes
   ```

**Validation:**
```bash
# Test services
php artisan tinker
> $service = app('App\Services\DepreciationService');
> $result = $service->depreciate($asset);

# Verify database
sqlite3 database/database.sqlite
sqlite> SELECT COUNT(*) FROM assets;
sqlite> SELECT COUNT(*) FROM aset;

# Compare data
SELECT id, COUNT(*) FROM assets GROUP BY id;
SELECT id, COUNT(*) FROM aset GROUP BY id;
```

---

#### Sprint 4: Views & Final Migration (Week 7-8)

**Goal:** Update views and complete final migration

**Tasks:**

1. **[Day 1-3] Migrate Report Views**
   ```
   - [ ] Create resources/views/reports/ directory
   - [ ] Create check/, incoming/, completed/, create/ subdirectories
   - [ ] Copy and update view files from Pelaporan/*
   - [ ] Update variable names in views
   - [ ] Update route() calls in views
   - [ ] Update model references
   ```

2. **[Day 3-4] Update Old Views (Deprecated)**
   ```
   - [ ] Add deprecation notices to old view files
   - [ ] Document which new views replace them
   - [ ] Set removal date in comments
   ```

3. **[Day 5-6] Final Controller Updates**
   ```
   - [ ] Search for any missed controller references
   - [ ] Update all remaining imports
   - [ ] Update view paths in controllers
   - [ ] Update variable names passed to views
   ```

4. **[Day 7-8] Comprehensive Testing**
   ```
   - [ ] Run full test suite
   - [ ] Test all user workflows
   - [ ] Test all permission scenarios
   - [ ] Performance testing
   - [ ] Check error logging
   ```

**Validation:**
```bash
# View files check
ls -la resources/views/reports/
ls -la resources/views/stock-takes/
ls -la resources/views/depreciation/

# Verify view references in controllers
grep -r "view(" app/Http/Controllers/Reports/
grep -r "view(" app/Http/Controllers/StockTake/
grep -r "view(" app/Http/Controllers/Depreciation/

# Test all views render
php artisan test Feature/ViewRenderTest
```

---

### Risk Mitigation Checkpoints

After each sprint, verify:

| Checkpoint | Check | Pass Criteria |
|-----------|-------|--------------|
| **Model Consistency** | All models have correct relationships | No broken relationships |
| **Controller Loading** | All controllers instantiate correctly | No namespace errors |
| **Route Access** | All routes accessible | 200 OK responses |
| **Database Integrity** | Foreign keys valid | No orphaned records |
| **Service Calls** | Services execute correctly | No model not found errors |
| **View Rendering** | All views render | No undefined variable notices |
| **Permission Checks** | Auth middleware works | Correct redirects |
| **Data Sync** | Old and new tables match | Record counts equal |

---

## Risk Assessment

### Critical Risks

#### 1. **Foreign Key Constraint Violations** 🔴 CRITICAL

**Risk:** When switching from old to new tables, foreign key constraints may fail

**Impact:** 
- Application crash
- Data corruption
- Lost transactions

**Mitigation:**
```php
// In migration: Temporarily disable foreign key checks
DB::statement('SET FOREIGN_KEY_CHECKS=0');
// ... data migration code ...
DB::statement('SET FOREIGN_KEY_CHECKS=1');

// Verify after
DB::statement('SELECT COUNT(*) FROM assets WHERE category_id NOT IN (SELECT id FROM asset_categories)');
```

**Rollback:** Disable the foreign key check again and restore from backup

---

#### 2. **Service Layer Model Dependencies** 🔴 CRITICAL

**Risk:** `PenyusutanService` hardcodes Indonesian model names

**Current Code:**
```php
class PenyusutanService
{
    public function generateBulanan(Aset $aset, AsetPenyusutanSetting $setting)
    {
        $last = PenyusutanBulanan::where('aset_id', $aset->id)->first();
        // ...
    }
}
```

**Impact:**
- Service breaks if models are deleted
- Service creates duplicate records if switching tables
- Depreciation calculation failures

**Mitigation:**
1. Create wrapper/adapter pattern for transition
2. Test service extensively before deployment
3. Use feature flags to switch model sources
4. Keep both service implementations during transition

**Rollback:** Easy - revert service file

---

#### 3. **Controllers Using Wrong Models** 🟠 HIGH

**Risk:** Controllers continue using Indonesian models after migration

**Impact:**
- Data inconsistency between old and new tables
- Confusion about which model is authoritative
- Difficult to track data flow

**Mitigation:**
```bash
# Automated check
grep -r "Opname\|Pelaporan\|Penyusutan\|Aset\|Kategori" \
    app/Http/Controllers/StockTake/ \
    app/Http/Controllers/Reports/ \
    app/Http/Controllers/Depreciation/
```

Use linting/static analysis:
```bash
php artisan code:analyze # if available
phpstan analyze app/Http/Controllers/
```

**Rollback:** Update affected controllers

---

#### 4. **Database Schema Sync Issues** 🟠 HIGH

**Risk:** Old and new tables get out of sync during parallel running

**Impact:**
- Reports show conflicting data
- Audit trails incomplete
- Difficult to identify source of truth

**Mitigation:**
1. Create syncing mechanism:
   ```php
   // After creating old record, sync to new table
   $oldRecord = Aset::create($data);
   Asset::create(array_merge($data, ['id' => $oldRecord->id]));
   ```

2. Create monitoring dashboard to compare counts:
   ```php
   // Monitor script
   $asetCount = Aset::count();
   $assetCount = Asset::count();
   assert($asetCount === $assetCount, "Sync mismatch");
   ```

3. Scheduled job to reconcile differences

**Rollback:** Manual sync from trusted table

---

#### 5. **View References Breaking** 🟠 HIGH

**Risk:** Views reference undefined variables or methods after controller migration

**Impact:**
- 500 errors in user interfaces
- Partial page rendering failures
- Poor user experience

**Mitigation:**
```php
// In controller before migration
return view('old-view', ['pelaporan' => $model]);

// After migration - test both work
return view('new-view', ['report' => $model]);

// Or use blade view composition
View::composer('reports.*', function ($view) {
    // Ensure all needed data exists
});
```

**Rollback:** Keep old views running in parallel

---

#### 6. **Permission Middleware Issues** 🟠 HIGH

**Risk:** Routes with old permission names stop working

**Impact:**
- Users can't access certain features
- Permission denied errors
- Admin can't manage resources

**Mitigation:**
```php
// Map old permissions to new
'pelaporan.view' => 'reports.view',
'pelaporan.create' => 'reports.create',

// Or add both during transition
Route::middleware('permission:pelaporan.view|reports.view')->group(...)
```

**Rollback:** Simple route update

---

#### 7. **Relationship Loading Failures** 🟡 MEDIUM

**Risk:** Eloquent relationships break if pointing to wrong models

**Impact:**
- N+1 query problems
- Undefined method errors
- Slow page loads

**Mitigation:**
```php
// Test relationships work
$asset = Asset::first();
$asset->category;      // Should not be null
$asset->depreciation;  // Should return MonthlyDepreciation
$asset->depreciationSetting; // Should return AssetDepreciationSetting

// Add tests
$this->assertNotNull($asset->category);
$this->assertInstanceOf(AssetCategory::class, $asset->category);
```

**Rollback:** Revert model relationship definitions

---

#### 8. **Test Suite Failures** 🟡 MEDIUM

**Risk:** Tests fail after migration, indicating bugs

**Impact:**
- Reduced confidence in code quality
- Late discovery of bugs
- Extended deployment timeline

**Mitigation:**
1. Maintain test suite throughout migration
2. Test both old and new models
3. Create migration-specific tests

```php
class MigrationTest extends TestCase
{
    public function test_asset_can_be_created_and_matches_aset()
    {
        $asset = Asset::create(['asset_code' => 'TEST-001']);
        $aset = Aset::find($asset->id);
        
        $this->assertEquals($asset->asset_code, $aset->kode_aset);
    }
}
```

**Rollback:** Fix failing tests before moving forward

---

### Data Loss Risks

#### Risk: Lost Records During Migration

**Scenario:**
- Data missing from new tables
- Cascade deletes remove related records
- Transaction rollback loses data

**Prevention:**
1. Full database backup before migration
2. Compare record counts before/after
3. Verify critical relationships intact
4. Test with copy of production database first

```sql
-- Verify counts
SELECT COUNT(*) as aset_count FROM aset;
SELECT COUNT(*) as asset_count FROM assets;

-- Verify no orphans in new schema
SELECT asset_id FROM assets 
WHERE asset_id NOT IN (SELECT id FROM assets);
```

**Recovery:**
- Restore from backup
- Re-run migration with logging
- Manually verify data integrity

---

### Performance Impacts

#### During Parallel Running

**Concern:** Running both schemas simultaneously

**Impact:**
- Increased disk I/O
- More database connections
- Slower query performance
- Increased memory usage

**Mitigation:**
```php
// Lazy load syncing - only sync when needed
if (!Asset::find($id)) {
    $aset = Aset::find($id);
    Asset::create($aset->toArray());
}

// Use database caching for frequent queries
Cache::remember("asset.{$id}", 3600, function () {
    return Asset::find($id);
});
```

**Expected Impact:** ~5-10% performance degradation during transition

**Resolution:** Remove old tables after migration complete

---

## Rollback Strategy

### Immediate Rollback (If Critical Issues)

**Timeline:** < 30 minutes

**Steps:**

1. **Identify Issue**
   ```
   - Check error logs
   - Identify affected feature/component
   - Assess impact (one user vs. all users)
   ```

2. **Quick Fix or Rollback Decision**
   ```
   - If fixable in < 15 minutes: fix it
   - If requires code change: rollback
   ```

3. **Rollback Procedure**
   ```bash
   # Revert code changes
   git revert <commit-hash>
   git push origin main
   
   # Restart application
   php artisan cache:clear
   php artisan view:clear
   
   # Restore from database backup
   # (if data was corrupted)
   ```

4. **Post-Rollback**
   ```
   - Notify users
   - Run tests to verify
   - Document issue for post-mortem
   ```

---

### Phased Rollback (If Partial Deployment)

**Timeline:** 1-2 hours

**Scenario:** Report controllers migrated but Depreciation controllers not yet

```
Rollback:
1. Revert Report controllers to Pelaporan
2. Restore old route mappings
3. Keep Depreciation as-is (already working)
4. Verify all features work
```

---

### Data Rollback (If Data Corrupted)

**Timeline:** 2-4 hours

**Steps:**

1. **Identify Corruption**
   ```bash
   # Check data integrity
   SELECT * FROM assets WHERE created_at > NOW() - INTERVAL 1 HOUR;
   SELECT COUNT(*) FROM assets WHERE category_id IS NULL;
   ```

2. **Restore Backup**
   ```bash
   # Stop application
   php artisan down
   
   # Restore database
   mysql -u root asset_db < backup_2026-08-30.sql
   
   # Restart application
   php artisan up
   ```

3. **Verify Restoration**
   ```bash
   # Check counts match
   php artisan tinker
   > Asset::count() === Aset::count()
   ```

---

### Keep Safe Practices

**During Migration:**
1. Daily backups
2. Test backup restoration daily
3. Keep old code branches available
4. Document all changes with timestamps
5. Have runbook for each rollback scenario
6. Alert team of any anomalies
7. Have migration communication plan

---

## Summary Checklist

### Before Starting Migration

- [ ] Database backup taken
- [ ] All tests passing
- [ ] Team trained on migration plan
- [ ] Deployment window scheduled
- [ ] Rollback procedure documented
- [ ] Monitoring set up
- [ ] Communication plan ready

### During Migration

- [ ] Each sprint has validation checkpoints
- [ ] Tests run after each change
- [ ] Logging enabled for debugging
- [ ] Performance monitoring active
- [ ] Data integrity checks run

### After Migration

- [ ] All features tested end-to-end
- [ ] Performance meets baseline
- [ ] Data consistency verified
- [ ] Old code archived (not deleted)
- [ ] Documentation updated
- [ ] Team trained on new structure
- [ ] Post-mortem of any issues

---

## Appendix: Quick Reference

### Search & Replace Patterns

```bash
# Find all Indonesian model usages
grep -r "\\bAset\\b" app/
grep -r "\\bOpname\\b" app/
grep -r "\\bPelaporan\\b" app/
grep -r "\\bPenyusutan" app/
grep -r "\\bKategoriAset\\b" app/
grep -r "\\bLokasiAset\\b" app/

# Find all relationship calls
grep -r "->aset(" app/
grep -r "->kategori(" app/
grep -r "->lokasi(" app/
grep -r "->opname(" app/
grep -r "->pelaporan(" app/
```

### Key Model Files

**Indonesian Models (To Be Deprecated):**
- `app/Models/Aset.php`
- `app/Models/KategoriAset.php`
- `app/Models/LokasiAset.php`
- `app/Models/Opname.php`
- `app/Models/OpnameDetail.php`
- `app/Models/Pelaporan.php`
- `app/Models/PenyusutanBulanan.php`
- `app/Models/AsetPenyusutanSetting.php`

**English Models (To Use Going Forward):**
- `app/Models/Asset.php`
- `app/Models/AssetCategory.php`
- `app/Models/AssetLocation.php`
- `app/Models/StockTake.php`
- `app/Models/StockTakeDetail.php`
- `app/Models/Report.php` (needs creation)
- `app/Models/MonthlyDepreciation.php`
- `app/Models/AssetDepreciationSetting.php`

### Key Service Files to Update

- `app/Services/PenyusutanService.php` → Must update imports
- `app/Services/DepreciationService.php` → Verify compatibility
- `app/Services/AssetSpecService.php` → Check for Indonesian refs
- `app/Services/AuditTrailService.php` → Update model names

### Testing Commands

```bash
# Run all tests
php artisan test

# Run specific test class
php artisan test tests/Feature/PermissionAndRouteContractTest.php

# Run with coverage
php artisan test --coverage

# Run in parallel
php artisan test --parallel

# Check routes
php artisan route:list

# Validate models load
php artisan tinker
> App\Models\Asset::first();
```

---

## Document History

| Date | Version | Author | Changes |
|------|---------|--------|---------|
| 2026-08-30 | 1.0 | Strategy Builder | Initial comprehensive strategy document |

---

**Last Review:** 2026-08-30  
**Next Review:** After Sprint 1 completion
