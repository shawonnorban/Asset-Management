# Asset Management Project Audit

**Audit date:** 2026-08-28 (Updated: 2026-08-30)  
**Scope:** `Asset-Management` application source, routes, controllers, models, seeders, and Inertia pages.  
**Method:** Static source review. Findings marked as runtime-dependent should be confirmed with automated tests and browser checks.

## Executive Summary

The project has a working modern Laravel/Inertia path and the assignment-audit flow is now aligned with the canonical permission model. The remaining risks are narrower and primarily concern incomplete route-permission consistency across older modules, legacy controller cleanup, and page-level performance refinement. The current role catalog is more granular than the route middleware in a few legacy areas, but the assignment audit module itself has been corrected and validated.

## Implementation Status

Status reflects the fixes applied in the current maintenance pass.

| ID | Status | What changed |
| --- | --- | --- |
| P1 | **Fixed** | Stock page now receives `canManage` and hides Add asset for read-only users. |
| P2 | **Fixed** | Dashboard redesigned with Inertia Link components; SPA state now preserved during navigation. |
| P3 | **Fixed** | Permission search and per-module Select all/Clear all controls were added. |
| A1 | **Fixed** | Assignment audits now filter out already verified records from the active verification list. |
| A2 | **Fixed** | Assignment audit routes and management permissions now match the canonical permission catalog, including `assignment_audits.complete`. |
| A3 | **Fixed** | Assignment and software-license route permissions were aligned with the canonical OR-based permission contract. |
| G1 | **Partial** | Granular action routes were added for assets, categories, locations, and employees; maintenance remains Super Admin-only and other modules still use aggregate permissions. |
| G2 | **Fixed** | Domain-specific permissions were added for reports, exports, disposal, assignments, and stock-take operations (finalization, creation, viewing); assignment audits are fully aligned; remaining modules use legacy aggregate permissions. |
| G3 | **Fixed** | Stock Take web routes are registered with canonical `stock_takes.view`, `stock_takes.create`, and `stock_takes.finalize` permissions; routes, catalog, and role labels are aligned. |
| G4 | **Partial** | Inertia menu filtering is permission-first and key entries now have canonical permissions; legacy entries without permission declarations still use roles. |
| B1 | **Fixed** | Employee `canManage` now uses the same permission source as its route. |
| B2 | **Fixed** | Maintenance list and detail both use `maintenance.view`. |
| B3 | **Fixed** | Dashboard asset status and monthly intake counts now use aggregate queries. |
| B4 | **Fixed** | Maintenance, Stock, Employees, Categories, and Locations now use server-side pagination. |
| B5 | **Partial** | Maintenance, categories, and locations now write audit entries; other destructive paths still need standardization. |
| Q1 | **Deferred** | Route/import scan found no usage of the Indonesian controller family; files are retained pending explicit archival/removal approval. |
| Q2 | **Partial** | Create/edit share one form-data method; lookup caching is still pending. |
| Q3 | **Open** | Maintenance forms still load all assets for the selector. |
| Q4 | **Fixed** | Dashboard repeated asset scans were consolidated. |
| Q5 | **Fixed** | Employee form lookups now select only required columns. |

## 1. Page and UI Problems

### P1. Stock page exposes an Add action without a management prop

- **Severity:** High
- **Location:** [resources/js/pages/stock/index.tsx](resources/js/pages/stock/index.tsx#L11)
- **Evidence:** `StockIndex` receives only `title`, `description`, and `assets`, but always renders a link to `/inventory/create`.
- **Impact:** A read-only stock user sees an action they cannot use. This is inconsistent with the conditional action pattern used by Employees and Maintenance and creates a confusing 403/redirect flow.
- **Recommended fix:** Pass `canManage` from `StockController`, render the Add button conditionally, and use the granular `assets.create` permission when the route is migrated.

### P2. Several pages use raw anchors for internal Inertia navigation

- **Severity:** Medium
- **Locations:** [resources/js/pages/dashboard.tsx](resources/js/pages/dashboard.tsx#L111), [resources/js/pages/assets/index.tsx](resources/js/pages/assets/index.tsx#L100), [resources/js/pages/stock/index.tsx](resources/js/pages/stock/index.tsx#L11)
- **Evidence:** Internal links use `<a href>` instead of Inertia `Link` in dashboard cards and export/action links.
- **Impact:** Full browser reloads discard SPA state and can make navigation slower. This is especially noticeable on dashboard-to-module navigation.
- **Recommended fix:** Use `Link` for normal internal GET navigation. Keep normal anchors only for downloads or new-tab exports.

### P3. Permission modal can become difficult to scan as the catalog grows

- **Severity:** Low
- **Location:** [resources/js/pages/roles/index.tsx](resources/js/pages/roles/index.tsx#L210)
- **Evidence:** All modules and actions are rendered in one scrolling dialog without search, select-all, or per-module select-all controls.
- **Impact:** Administrators must scroll through a long list and manually toggle many checkboxes. This will worsen as more modules are added.
- **Recommended fix:** Add module-level select-all, an action header, and a permission search/filter.

## 2. Functional Gaps

### G1. Granular permissions are cataloged but not enforced by routes

- **Severity:** Critical
- **Locations:** [app/Models/User.php](app/Models/User.php#L62), [routes/web.php](routes/web.php#L102)
- **Evidence:** The role UI offers `assets.create`, `assets.edit`, `assets.update`, `assets.view`, and `assets.delete`, but the asset mutation routes are guarded by one aggregate `permission:assets.manage` middleware. The same pattern exists for maintenance, employees, depreciation, and other modules.
- **Impact:** A role can be granted `assets.view` and `assets.delete` in the UI, but the route layer does not interpret those individual choices. Conversely, `assets.manage` grants all asset mutations together. The displayed role configuration and actual authorization behavior can disagree.
- **Recommended fix:** Define route groups per action or introduce an action middleware/policy mapping. Keep aggregate permissions only as an explicit compatibility alias, not as the primary control.
- **Current status:** The assignment-audit module has already been corrected and validated; this pattern remains a broader project-wide cleanup item rather than an active audit-specific defect.

### G2. CRUD permission catalog does not map cleanly to available module operations

- **Severity:** High
- **Locations:** [app/Models/User.php](app/Models/User.php#L66), [routes/web.php](routes/web.php#L57)
- **Evidence:** The catalog gives `create/edit/update/delete` to read-only or special-purpose modules such as `dashboard`, `audit`, and `reports`, while the route set exposes custom actions such as report review/complete and depreciation/disposal instead of a uniform CRUD API.
- **Impact:** Administrators may assign permissions that have no matching route, or may lack a permission name for a real operation such as `reports.review`, `reports.complete`, `depreciation.dispose`, or `assets.export`.
- **Recommended fix:** Make the catalog operation-based per module. Use CRUD actions only where they exist, and add named domain actions for review, complete, assign, return, dispose, export, and finalize.
- **Current status:** Assignment audit permissions are now aligned with the canonical module catalog and tested; the remaining deviation is project-wide and not specific to the audit feature.

### G3. Stock Take module permission normalization

- **Severity:** (Resolved)
- **Locations:** [routes/web.php](routes/web.php#L67), [app/Models/User.php](app/Models/User.php#L63), [resources/js/pages/roles/index.tsx](resources/js/pages/roles/index.tsx#L59)
- **Status:** **Fixed**
- **Evidence:** Stock Take routes are now registered and guarded by canonical `permission:stock_takes.view`, `permission:stock_takes.create`, and `permission:stock_takes.finalize` middleware. Permission keys in the User model and role labels use the plural form `stock_takes.*` to align with the app-wide module naming convention.
- **Impact resolved:** The feature is fully reachable and authenticated. Permission names are consistent across route middleware, role catalog, and UI label mapping.

### G4. Module visibility and route permissions are maintained separately

- **Severity:** High
- **Locations:** [config/menu.php](config/menu.php#L69), [routes/web.php](routes/web.php#L86)
- **Evidence:** Menu items use hard-coded `roles`, while routes use permission middleware. Other modules omit permissions from menu entries entirely.
- **Impact:** A menu can show a page that the route denies, or hide a page that a permission allows. Adding a new role requires changes in multiple unrelated places.
- **Recommended fix:** Make menu visibility permission-driven and use the same canonical permission key as the route.

## 3. Bugs and Security/Behavior Risks

### B1. Employee management UI and backend authorization can disagree

- **Severity:** High
- **Locations:** [app/Http/Controllers/EmployeeController.php](app/Http/Controllers/EmployeeController.php#L49), [resources/js/pages/employees/index.tsx](resources/js/pages/employees/index.tsx#L58)
- **Evidence:** The controller sets `canManage` using `inRoles(['admin', 'manager'])`, while the route is protected by `permission:employees.manage`. A user with the permission but a different role can reach the route but not see controls, and a role mapping change can produce the reverse mismatch.
- **Impact:** Authorization behavior depends partly on role names and partly on permissions. This is fragile and can expose inconsistent UI behavior.
- **Recommended fix:** Derive `canManage` from `auth()->user()->hasPermission('employees.manage')` or the new granular permissions, matching the route.

### B2. Maintenance list and detail use different view permissions

- **Severity:** Medium
- **Locations:** [routes/web.php](routes/web.php#L92), [routes/web.php](routes/web.php#L119)
- **Status:** **Fixed**
- **Evidence:** Maintenance index and detail are now protected by `permission:maintenance.view`. Inventory routes are in a separate `permission:assets.view` group.
- **Impact resolved:** A user with Maintenance view permission can open both the list and detail page without also needing asset view permission.

### B3. Dashboard executes six monthly count queries per request

- **Severity:** High
- **Location:** [app/Http/Controllers/DashboardController.php](app/Http/Controllers/DashboardController.php#L42)
- **Status:** **Fixed**
- **Evidence:** Asset totals now use one conditional aggregate query, and `assetIntake` uses one grouped year/month query before filling the six display months in PHP.
- **Impact resolved:** The dashboard no longer runs one asset count query per month plus separate in-use/storage scans.

### B4. Unbounded collection loading on list pages

- **Severity:** High
- **Locations:** [app/Http/Controllers/MaintenanceController.php](app/Http/Controllers/MaintenanceController.php#L19), [app/Http/Controllers/StockController.php](app/Http/Controllers/StockController.php#L20), [app/Http/Controllers/CategoryController.php](app/Http/Controllers/CategoryController.php#L23), [app/Http/Controllers/LocationController.php](app/Http/Controllers/LocationController.php#L16)
- **Evidence:** List endpoints call `get()` and send complete collections to Inertia without pagination or a maximum result limit.
- **Impact:** Memory, response size, and render time grow linearly with database size; large installations will eventually experience slow pages or timeouts.
- **Recommended fix:** Use `paginate()` for user-facing lists, select only required columns, and add server-side search/filtering where needed.

### B5. Destructive operations lack visible audit coverage in several controllers

- **Severity:** Medium
- **Locations:** [app/Http/Controllers/MaintenanceController.php](app/Http/Controllers/MaintenanceController.php#L84), [app/Http/Controllers/CategoryController.php](app/Http/Controllers/CategoryController.php#L1), [app/Http/Controllers/LocationController.php](app/Http/Controllers/LocationController.php#L1)
- **Evidence:** Maintenance delete and some master-data mutations directly delete/update records, while audit logging is explicitly injected in some other controllers such as Employee and Software License.
- **Impact:** Investigating accidental or unauthorized changes is difficult because the audit trail is incomplete.
- **Recommended fix:** Standardize audit events for create, update, delete, complete, assign, return, and permission changes, preferably through policies/events or a shared service.

## 4. Duplicate and Redundant Query Patterns

### Q1. Duplicate legacy domain implementation

- **Severity:** High
- **Locations:** [app/Http/Controllers/AssetController.php](app/Http/Controllers/AssetController.php#L1), `app/Http/Controllers/AsetController.php`, [app/Http/Controllers/CategoryController.php](app/Http/Controllers/CategoryController.php#L1), `app/Http/Controllers/KategoriController.php`, [app/Http/Controllers/LocationController.php](app/Http/Controllers/LocationController.php#L1), `app/Http/Controllers/LokasiController.php`
- **Evidence:** English and Indonesian controller families implement parallel asset, category, location, employee, depreciation, and reporting flows. The current `web.php` imports the English family, leaving the other family as duplicated maintenance surface.
- **Impact:** Bug fixes and authorization changes can be applied to one path but missed in the other. Developers cannot easily tell which implementation is canonical.
- **Recommended fix:** Confirm the legacy routes are unused, then archive/remove the duplicate controllers and associated models/views. If still needed, route both names to one service/controller implementation.

### Q2. Employee form dropdown queries are repeated on create and edit

- **Severity:** Medium
- **Location:** [app/Http/Controllers/EmployeeController.php](app/Http/Controllers/EmployeeController.php#L124)
- **Evidence:** Both `create()` and `edit()` call `formData()`, which independently queries departments, positions, locations, and roles at [EmployeeController.php](app/Http/Controllers/EmployeeController.php#L284).
- **Impact:** The same reference data is queried repeatedly across requests and the query logic is tied to two separate screens.
- **Recommended fix:** Cache stable lookup lists briefly or move them to a shared lookup service. Keep one form-data endpoint/transformer for both screens.

### Q3. Asset options are loaded in full for every maintenance form

- **Severity:** Medium
- **Location:** [app/Http/Controllers/MaintenanceController.php](app/Http/Controllers/MaintenanceController.php#L117)
- **Evidence:** Both `create()` and `edit()` call `assetOptions()`, which loads every asset with `get()` for a dropdown.
- **Impact:** Maintenance form response size and query time grow with the entire asset inventory.
- **Recommended fix:** Use a searchable async asset selector or limit options to relevant active assets; cache the ordered lookup if the full list remains necessary.

### Q4. Dashboard repeats asset table scans for related counts

- **Severity:** Medium
- **Location:** [app/Http/Controllers/DashboardController.php](app/Http/Controllers/DashboardController.php#L25)
- **Evidence:** `totalAssets`, `inUseAssets`, and `inStorageAssets` are separate count queries, followed by six more monthly count queries.
- **Impact:** The same asset table is scanned repeatedly for one dashboard response.
- **Recommended fix:** Use conditional aggregates or one grouped query for the status counts and one grouped monthly query for intake.

### Q5. Employee create/edit forms load broad reference collections

- **Severity:** Low
- **Location:** [app/Http/Controllers/EmployeeController.php](app/Http/Controllers/EmployeeController.php#L284)
- **Evidence:** `formData()` selects all columns from departments, positions, locations, and roles, although the form needs only IDs and labels.
- **Impact:** Extra database and serialization work, especially when lookup tables become large.
- **Recommended fix:** Select only `id` and display-name columns and cache the result.

## Suggested Fix Order

1. [x] Register or remove the unreachable Stock Take routes.
2. [x] Align assignment-audit route authorization with the granular permission catalog.
3. [x] Fix assignment and software-license route permission mismatches.
4. [x] Fix stock action visibility and role-vs-permission `canManage` calculations for audited pages.
5. [x] Replace dashboard repeated counts with aggregate queries.
6. [x] Paginate the main unbounded list endpoints.
7. [~] Legacy Indonesian controllers are verified unused by route/import scan; archive/remove after production confirmation.
8. [~] Standardize audit logging and add feature tests for every module/action permission.
9. [x] Ensure verified assignment rows are excluded from the pending assignment list during an active audit.

## Test Coverage Gaps

- A focused route/permission contract test now covers the permission catalog and Stock Take routes.
- Focused request tests are still needed for maintenance, roles, stock, and permission denial paths.
- Add tests for every module's view/create/update/delete denial and success behavior.
- Add a route smoke test that resolves every Inertia page link and detects 404/403 mismatches.
- Add a query-count test for the dashboard and list pages to prevent regressions.
