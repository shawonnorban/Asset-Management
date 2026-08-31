# Commercial Asset Management Implementation Plan

## Goal
This project should become a commercially viable asset lifecycle system for companies that need:
- maintenance tracking
- warranty management
- disposal and transfer controls
- reminders and notifications
- lifecycle traceability
- management reporting

The implementation follows a phase-based approach so the team can build and validate one module at a time.

---

## 1. Priority Modules

### Module A: Maintenance + Warranty
- asset maintenance requests
- scheduled maintenance
- maintenance history
- warranty expiry tracking
- vendor/service provider tracking
- maintenance cost and downtime impact

### Module B: Disposal + Transfer
- asset transfer between locations or employees
- asset disposal with reason and approval
- disposal approval workflow
- asset status history and evidence

### Module C: Notifications
- maintenance due alerts
- warranty expiry warnings
- transfer/disposal approval notifications
- dashboard alerts and email reminders

### Module D: Lifecycle Flow
- request -> approval -> purchase -> receive -> assign -> maintain -> transfer -> disposal
- full lifecycle events with timestamps and actor info

### Module E: Reporting
- asset health summary
- pending maintenance
- warranty expiry report
- disposal report
- transfer history
- utilization and downtime metrics

---

## 2. Core Architecture

### Database layer
Create dedicated tables for each business event, instead of overloading the asset table.

Recommended tables:
- maintenance_requests
- maintenance_activities
- warranties
- warranty_claims
- asset_transfers
- asset_disposals
- asset_lifecycle_logs
- notifications
- notification_templates
- approval_requests

### Application layer
Suggested files:
- app/Models/MaintenanceRequest.php
- app/Models/Warranty.php
- app/Models/AssetTransfer.php
- app/Models/AssetDisposal.php
- app/Models/AssetLifecycleLog.php
- app/Models/Notification.php
- app/Services/AssetLifecycleService.php
- app/Services/NotificationService.php
- app/Http/Controllers/Maintenance/MaintenanceController.php
- app/Http/Controllers/Transfer/TransferController.php
- app/Http/Controllers/Disposal/DisposalController.php
- app/Http/Controllers/NotificationController.php
- app/Http/Controllers/Reports/AssetReportController.php

### UI layer
- resources/js/pages/maintenance/index.tsx
- resources/js/pages/maintenance/show.tsx
- resources/js/pages/transfers/index.tsx
- resources/js/pages/disposals/index.tsx
- resources/js/pages/notifications/index.tsx
- resources/js/pages/reports/asset-health.tsx

---

## 3. Phase-by-Phase Implementation

## Phase 1 — Foundation (Day 1-2)
### Tasks
1. Define enum/status values:
   - maintenance_status: DRAFT, OPEN, IN_PROGRESS, COMPLETED, CANCELLED
   - warranty_status: ACTIVE, EXPIRED, CLAIMED, VOID
   - transfer_status: REQUESTED, APPROVED, IN_TRANSIT, COMPLETED, REJECTED
   - disposal_status: REQUESTED, APPROVED, DISPOSED, REJECTED
   - lifecycle_event: CREATED, ASSIGNED, MAINTENANCE, TRANSFER, DISPOSAL, WARRANTY

2. Add migration files for new tables.

3. Create models and relationships.

4. Add service classes for business logic.

5. Add route groups and permission entries.

### Acceptance criteria
- All new tables exist and align with asset records.
- Asset can be linked to lifecycle events.
- One asset may have multiple maintenance + transfer + disposal records.

---

## Phase 2 — Maintenance + Warranty (Day 3-5)
### Tasks
1. Create maintenance request form.
2. Add maintenance record creation and update flow.
3. Add service provider / vendor selection.
4. Add maintenance cost fields.
5. Add warranty linking to asset purchase and vendor.
6. Add expiry calculation and warnings.
7. Add maintenance history panel on asset detail page.

### Minimum fields
For maintenance_requests:
- id
- asset_id
- maintenance_type
- request_date
- scheduled_date
- assigned_to
- vendor_id
- description
- status
- priority
- cost
- completed_at

For warranties:
- id
- asset_id
- vendor_id
- warranty_start
- warranty_end
- warranty_type
- coverage_details
- claim_status

### Acceptance criteria
- Maintenance can be opened and resolved.
- Warranty expiry is visible on asset page.
- Expired or near-expiry assets show alert status.

---

## Phase 3 — Disposal + Transfer (Day 6-8)
### Tasks
1. Create transfer request form.
2. Add approval workflow for transfer.
3. Add transfer completion step and status tracking.
4. Add disposal request form with reason and approval.
5. Add disposal evidence fields (photo, user, note, date).
6. Update asset status after successful transfer/disposal.
7. Record lifecycle logs for every state change.

### Minimum fields
For asset_transfers:
- id
- asset_id
- from_location_id
- to_location_id
- from_employee_id
- to_employee_id
- requested_by
- approved_by
- transfer_date
- status
- reason
- notes

For asset_disposals:
- id
- asset_id
- requested_by
- approved_by
- disposal_date
- reason
- method
- value_recovered
- status
- notes

### Acceptance criteria
- No asset can be transferred without approval.
- Disposal cannot happen without valid reason and confirmation.
- Asset lifecycle history remains auditable.

---

## Phase 4 — Notifications (Day 9-10)
### Tasks
1. Create notification model and template system.
2. Build notification types:
   - maintenance_due
   - warranty_expiring
   - transfer_approved
   - transfer_rejected
   - disposal_requested
   - disposal_approved
3. Add email + in-app notifications.
4. Add unread badge and notification center.
5. Schedule automated checks for due dates.

### Services
- NotificationService
- ReminderService
- AssetAlertService

### Acceptance criteria
- User receives alert when warranty is near expiry.
- Dashboard shows unread notification count.
- Email reminder is sent for scheduled maintenance.

---

## Phase 5 — Lifecycle Flow + Audit Trail (Day 11-12)
### Tasks
1. Build AssetLifecycleService.
2. Record events whenever asset changes state.
3. Link each event to user, date, and old/new values.
4. Add lifecycle timeline view on asset detail page.
5. Add filters by event type and date.

### Event examples
- asset_created
- asset_assigned
- maintenance_requested
- maintenance_completed
- transferred
- disposed
- warranty_expired

### Acceptance criteria
- Every significant asset event is visible in history.
- Audit trail can answer: who, what, when, why.

---

## Phase 6 — Reporting (Day 13-14)
### Tasks
1. Create asset health report.
2. Create maintenance backlog report.
3. Create warranty expiry report.
4. Create transfer/disposal summary report.
5. Add export to PDF and Excel.
6. Add date filters and department filters.

### Key report sections
- Total active assets
- Assets under maintenance
- Assets with warranty expiry in next 30 days
- High-risk assets
- Disposal summary
- Transfer summary
- Cost impact of maintenance and disposal

### Acceptance criteria
- Management can read summary without opening asset records individually.
- Reports export in professional format.

---

## Phase 7 — QA + Commercial Readiness (Day 15-17)
### Tasks
1. Run feature tests for each module.
2. Validate permission gates.
3. Validate notifications and email sending.
4. Test lifecycle history and audit trail.
5. Check performance on large asset data.
6. Prepare admin demo data.
7. Finalize UI polish and branding.

### Acceptance criteria
- All critical flows work end-to-end.
- App is usable by admin/manager/employee roles.
- Business persona is ready for client demo.

---

## 4. Suggested Implementation Order

1. DB schema and models
2. Maintenance + warranty
3. Transfer + disposal
4. Lifecycle log
5. Notifications
6. Reporting
7. UX polish + QA

This order reduces risk and keeps core data models stable before adding business workflows.

---

## 5. Suggested File Creation Map

### Main files to create
- app/Models/MaintenanceRequest.php
- app/Models/Warranty.php
- app/Models/AssetTransfer.php
- app/Models/AssetDisposal.php
- app/Models/AssetLifecycleLog.php
- app/Models/Notification.php
- app/Services/AssetLifecycleService.php
- app/Services/NotificationService.php
- app/Http/Controllers/Maintenance/MaintenanceController.php
- app/Http/Controllers/Transfer/TransferController.php
- app/Http/Controllers/Disposal/DisposalController.php
- app/Http/Controllers/Reports/AssetReportController.php
- app/Notifications/MaintenanceDueNotification.php
- app/Notifications/WarrantyExpiringNotification.php

### Main migrations
- database/migrations/2026_09_01_000001_create_maintenance_requests_table.php
- database/migrations/2026_09_01_000002_create_warranties_table.php
- database/migrations/2026_09_01_000003_create_asset_transfers_table.php
- database/migrations/2026_09_01_000004_create_asset_disposals_table.php
- database/migrations/2026_09_01_000005_create_asset_lifecycle_logs_table.php
- database/migrations/2026_09_01_000006_create_notifications_table.php

### Main UI files
- resources/js/pages/maintenance/index.tsx
- resources/js/pages/maintenance/form.tsx
- resources/js/pages/transfers/index.tsx
- resources/js/pages/disposals/index.tsx
- resources/js/pages/notifications/index.tsx
- resources/js/pages/reports/index.tsx

---

## 6. Commercial Value
When these modules are complete, the project is no longer just a basic asset tracker. It becomes:
- a maintenance management system
- a warranty tracking tool
- a transfer and disposal workflow engine
- a lifecycle audit platform
- a management reporting dashboard

This is the level at which a client would consider it a practical business application.

---

## 7. Recommended Next Action
Start with these 3 deliverables first:
1. maintenance_requests + warranties + lifecycle_logs tables
2. maintenance request UI and warranty expiry dashboard card
3. notifications service + reminder rules

After that, build transfer/disposal and then reporting.
