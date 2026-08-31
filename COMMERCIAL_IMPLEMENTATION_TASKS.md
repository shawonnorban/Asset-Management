# Commercial Feature Implementation Tasks

## Epic 1: Foundation & Data Model

### Task 1.1 — Define statuses and enums
- [x] Add maintenance statuses
- [x] Add warranty statuses
- [x] Add transfer statuses
- [x] Add disposal statuses
- [x] Add lifecycle event types

### Task 1.2 — Create migration files
- [x] maintenance_requests
- [x] warranties
- [x] asset_transfers
- [x] asset_disposals
- [x] asset_lifecycle_logs
- [x] notifications
- [x] notification_templates

### Task 1.3 — Build models and relationships
- [x] Asset -> many maintenance records
- [x] Asset -> many warranty records
- [x] Asset -> many transfers
- [x] Asset -> many disposals
- [x] Asset -> many lifecycle logs
- [x] User -> many notifications

### Task 1.4 — Add service layer
- [x] AssetLifecycleService
- [x] NotificationService
- [x] ReminderService
- [x] ReportService

### Task 1.5 — Add routes and permissions
- [x] maintenance.* permissions
- [x] transfers.* permissions
- [x] disposals.* permissions
- [x] notifications.* permissions
- [x] reports.* permissions

---

## Epic 2: Maintenance + Warranty

### Task 2.1 — Create maintenance request form
- [x] asset selector
- [x] type and priority
- [x] description and notes
- [x] vendor/service provider
- [x] scheduled date

### Task 2.2 — Create maintenance list page
- [x] open requests
- [x] in-progress requests
- [x] completed maintenance
- [x] filter by asset and status

### Task 2.3 — Create maintenance detail page
- [x] timeline
- [x] cost and downtime info
- [x] action buttons
- [x] update status

### Task 2.4 — Add warranty module
- [x] attach warranty to asset
- [x] warranty expiry date
- [x] vendor and claim status
- [x] warranty card in asset detail page

### Task 2.5 — Add alert logic
- [x] near-expiry warning
- [x] expired warranty flag
- [x] maintenance due reminder

---

## Epic 3: Disposal + Transfer

### Task 3.1 — Transfer request flow
- [x] request transfer form
- [x] from/to location or employee
- [x] approval action
- [x] rejection action
- [x] status tracking

### Task 3.2 — Disposal request flow
- [x] request disposal reason
- [x] disposal approval
- [x] disposal rejection
- [x] final disposal record
- [x] asset status change

### Task 3.3 — Asset status sync
- [x] update asset location
- [x] update asset assignment status
- [x] update asset status to disposed/retired

### Task 3.4 — Transfer/disposal history
- [x] verified approval workflow in feature tests
- [x] asset status updated after approval
- [x] show timeline of all asset movements
- [x] add reason and notes
- [x] record who approved

---

## Epic 4: Notifications

### Task 4.1 — Build notification system
- [x] Notification model
- [x] Notification template model
- [x] mark as read/unread
- [x] mark all as read

### Task 4.2 — Notification types
- [x] maintenance due
- [x] maintenance overdue
- [x] warranty expiring
- [x] warranty expired
- [x] transfer approved/rejected
- [x] disposal approved/rejected

### Task 4.3 — In-app notifications
- [x] notification center UI
- [x] unread badge in header
- [x] list and filter pages
- [x] deep link from a notification to its record

### Task 4.4 — Email notifications
- [x] send notification via mail
- [x] queue for delayed reminders
- [x] email template design

### Task 4.5 — Reminder scheduler
- [x] daily expiry check
- [x] maintenance due check
- [x] scheduled job registration

---

## Epic 5: Lifecycle Flow & Audit Trail

### Task 5.1 — Create lifecycle log model
- [x] event type
- [x] event date
- [x] asset id
- [x] user id
- [x] old and new values
- [x] notes

### Task 5.2 — Register lifecycle events
- [x] asset created
- [x] assigned
- [x] maintenance requested
- [x] maintenance completed
- [x] transfer completed
- [x] disposed
- [x] warranty registered
- [x] warranty expired

### Task 5.3 — Show lifecycle timeline in UI
- [x] asset detail page timeline
- [x] filter by event type
- [x] export history if needed

---

## Epic 6: Reports & Analytics

### Task 6.1 — Maintenance report
- [x] open maintenance count
- [x] overdue jobs
- [x] monthly maintenance cost

### Task 6.2 — Warranty report
- [x] items expiring in 30 days
- [x] expired warranty items
- [x] vendor-wise claim tracking

### Task 6.3 — Disposal / transfer report
- [x] total transferred assets
- [x] total disposed assets
- [x] reason summary
- [x] value recovered

### Task 6.4 — Executive dashboard cards
- [x] active assets
- [x] assets under maintenance
- [x] warranty alerts
- [x] overdue transfers
- [x] disposal stats

### Task 6.5 — Export options
- [x] PDF export
- [x] Excel export
- [x] CSV export

---

## Epic 7: QA and Commercial Readiness

### Task 7.1 — Functional tests
- [x] maintenance creation
- [x] warranty expiry
- [x] transfer approval
- [x] disposal approval
- [x] notification sending
- [x] lifecycle logging

### Task 7.2 — Permission tests
- [x] admin permissions
- [x] manager permissions
- [x] department head permissions
- [x] employee permissions

### Task 7.3 — UI polish
- [x] modern card layout
- [x] alert banners
- [x] summary widgets
- [x] responsive mobile view

### Task 7.4 — Demo data and staging
- [x] sample maintenance records
- [x] sample transfer records
- [x] sample disposal records
- [x] sample notification entries
- [x] notification templates seeded in every environment
- [x] all of it wired into `DatabaseSeeder`, so `migrate:fresh --seed` gives a
      fully populated app in one command

---

## Validation notes

Suite: `php artisan test` — **72 passed, 447 assertions**, plus the pre-existing
`ExampleTest` failure described under Known issues.

| Area | Test file |
| --- | --- |
| Maintenance creation, warranty expiry, reminders, notifications, lifecycle logging | `tests/Feature/CommercialLifecycleFunctionalTest.php` |
| Reports 6.1–6.4 and the PDF/Excel/CSV exports | `tests/Feature/CommercialReportExportTest.php` |
| Admin / manager / department head / employee access matrix | `tests/Feature/CommercialPermissionMatrixTest.php` |
| Demo data and notification templates | `tests/Feature/CommercialDemoDataTest.php` |
| Transfer and disposal approval | `tests/Feature/CommercialTransferDisposalModuleTest.php` |

Front end: `npm run build` succeeds; `npx tsc --noEmit` reports no new errors
(the remaining ones pre-date this work — see Known issues).

### Defects found and fixed while completing these tasks

- `MaintenanceRequest`, `AssetTransfer`, and `AssetDisposal` creation never set
  `requested_at`, which is `NOT NULL`. Every create through the UI would have
  failed; only the tests that inserted models directly were passing.
- `TransferController::create()` ordered locations by a `name` column that does
  not exist — `asset_locations` uses `location_name`. The transfer form threw.
- `transfers/show.tsx` read `transfer.from_location.name`, but the controller
  only ever sent `from_location_id`, so the detail page showed blanks.
- Creating a warranty logged the event type `WARRANTY_EXPIRED`.
- The executive `warranty_alerts` figure missed any warranty already flagged
  `EXPIRED`, because the scope only matched not-yet-flagged ones.
- The monthly cost chart mislabelled months: `Carbon::createFromFormat('Y-m', …)`
  keeps today's day number, so a 31st rolls a 30-day month into the next one.
  Fixed with the `!` format reset.

### Known issues (pre-existing, not part of this work)

- `tests/Feature/ExampleTest.php` asserts `GET /` returns 200, but the app
  redirects guests to the login screen. The stock Laravel test does not match
  this app's behaviour and has been failing since before this work.
- `npx tsc --noEmit` reports type errors in `report-detail.tsx`,
  `assets/index.tsx`, `disposals/form.tsx`, `report-issue/index.tsx`, and
  `stock/index.tsx`. None are in the commercial modules.
- The test suite runs against the `asset` development database, and
  `RefreshDatabase` wipes it on every run. Consider adding a dedicated testing
  connection in `phpunit.xml` before running tests against real data.

---

## Operating the new features

```bash
# nightly sweeps (already registered in app/Console/Kernel.php at 07:00 / 07:15)
php artisan reminders:warranty-expiry
php artisan reminders:maintenance-due

# preview without sending mail
php artisan reminders:warranty-expiry --no-email
php artisan reminders:maintenance-due --days=7

# full reset: schema, reference data, and the commercial demo set
php artisan migrate:fresh --seed

# demo data on its own (safe to re-run - it will not duplicate records)
php artisan db:seed --class=CommercialDemoSeeder
```

Reminder mail is queued, so a worker must be running (`php artisan queue:work`)
unless `QUEUE_CONNECTION=sync`.
