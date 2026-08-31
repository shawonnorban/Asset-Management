import { Activity, ArrowRight, ArrowUpRight, Boxes, Building2, CheckCircle2, ClipboardList, PackageOpen, Plus, ShieldCheck, TrendingUp, UserCheck, Users, WalletCards, Wrench, AlertCircle, AlertTriangle, Info, History, ShieldAlert, ArrowLeftRight, Trash2, BellRing, BarChart3 } from 'lucide-react';
import { Link } from '@inertiajs/react';

import AppLayout from '@/layouts/app-layout';
import StatCard, { type Accent, type Tone } from '@/components/stat-card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { PageProps } from '@/types';

interface Stats {
    assets: number;
    locations: number;
    categories: number;
    accounts: number;
    depreciations: number | null;
    in_use: number;
    in_storage: number;
    open_maintenance: number;
    completed_maintenance: number;
}

interface AuditLog {
    id: number;
    user: string;
    action: string;
    time: string | null;
}

interface UserStatus {
    id: number;
    name: string;
    role: string;
    online: boolean;
    last_login: string | null;
}

interface Depreciation {
    id: number;
    name: string;
    code: string;
    period: string | null;
}

interface Assignment {
    id: number;
    asset_code: string | null;
    employee: string | null;
    date: string | null;
}

interface Intake {
    label: string;
    value: number;
}

interface Can {
    maintenance: boolean;
    transfers: boolean;
    disposals: boolean;
    notifications: boolean;
    reports: boolean;
}

interface Lifecycle {
    maintenance: { open: number; overdue: number; legacy_open: number } | null;
    warranty: { expiring: number; expired: number } | null;
    transfers: { pending: number; overdue: number } | null;
    disposals: { pending: number; disposed: number; value_recovered: number } | null;
}

interface AttentionItem {
    tone: 'danger' | 'warning' | 'info';
    count: number;
    label: string;
    href: string;
}

interface LifecycleEvent {
    id: number;
    asset_id: number;
    asset_code: string | null;
    event_type: string;
    event_label: string;
    description: string | null;
    user: string | null;
    time: string | null;
}

interface Breakdown {
    key?: string;
    id?: number;
    label: string;
    count: number;
}

interface Portfolio {
    status: Breakdown[];
    condition: Breakdown[];
    categories: Breakdown[];
    locations: Breakdown[];
    uncovered: number;
}

interface UpcomingJob {
    id: number;
    title: string;
    asset_code: string | null;
    priority: string;
    assigned_to: string | null;
    scheduled_at: string | null;
    days_away: number;
}

interface Props extends PageProps {
    role: string;
    can: Can;
    portfolio: Portfolio;
    upcomingMaintenance: UpcomingJob[];
    lifecycle: Lifecycle;
    attention: AttentionItem[];
    recentLifecycle: LifecycleEvent[];
    unreadNotifications: number;
    stats: Stats;
    auditLogs: AuditLog[];
    usersStatus: UserStatus[];
    latestDepreciations: Depreciation[];
    recentAssignments: Assignment[];
    assetIntake: Intake[];
}

const number = (value: number | null) => (value ?? 0).toLocaleString();

/** Colour and icon per severity for the "needs attention" strip. */
const attentionTones = {
    danger: { box: 'border-rose-200 bg-rose-50 text-rose-900 hover:bg-rose-100 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-200 dark:hover:bg-rose-950/60', icon: AlertCircle },
    warning: { box: 'border-amber-200 bg-amber-50 text-amber-900 hover:bg-amber-100 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200 dark:hover:bg-amber-950/60', icon: AlertTriangle },
    info: { box: 'border-blue-200 bg-blue-50 text-blue-900 hover:bg-blue-100 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-200 dark:hover:bg-blue-950/60', icon: Info },
} as const;

/** Colour per asset working state, shared by the bar and its legend. */
const statusColours: Record<string, string> = {
    IN_USE: 'bg-emerald-500',
    IN_STORAGE: 'bg-blue-500',
    UNDER_REPAIR: 'bg-amber-500',
    RETIRED: 'bg-slate-400',
    DISPOSED: 'bg-rose-500',
};

const conditionColours: Record<string, string> = {
    NEW: 'bg-emerald-500',
    GOOD: 'bg-blue-500',
    FAIR: 'bg-amber-500',
    POOR: 'bg-rose-500',
};

/** A single stacked bar with a legend underneath - used for status and condition. */
function BreakdownBar({ rows, colours }: { rows: Breakdown[]; colours: Record<string, string> }) {
    const present = rows.filter((row) => row.count > 0);
    const total = present.reduce((sum, row) => sum + row.count, 0);

    if (total === 0) {
        return <Empty text="Nothing recorded yet" />;
    }

    return (
        <div className="space-y-4">
            <div className="flex h-2.5 overflow-hidden rounded-full bg-muted">
                {present.map((row) => (
                    <div
                        key={row.label}
                        className={colours[row.key ?? ''] ?? 'bg-slate-400'}
                        style={{ width: `${(row.count / total) * 100}%` }}
                        title={`${row.label}: ${row.count}`}
                    />
                ))}
            </div>

            <ul className="grid gap-2 sm:grid-cols-2">
                {present.map((row) => (
                    <li key={row.label} className="flex items-center justify-between gap-3 text-sm">
                        <span className="flex min-w-0 items-center gap-2">
                            <span className={`size-2.5 shrink-0 rounded-full ${colours[row.key ?? ''] ?? 'bg-slate-400'}`} />
                            <span className="truncate text-muted-foreground">{row.label}</span>
                        </span>
                        <span className="shrink-0 font-semibold tabular-nums">{number(row.count)}</span>
                    </li>
                ))}
            </ul>
        </div>
    );
}

/** Ranked list with a proportional bar behind each row. */
function RankedList({ rows, emptyText }: { rows: Breakdown[]; emptyText: string }) {
    if (rows.length === 0) {
        return <Empty text={emptyText} />;
    }

    const peak = Math.max(...rows.map((row) => row.count), 1);

    return (
        <ul className="space-y-3">
            {rows.map((row) => (
                <li key={row.label}>
                    <div className="mb-1 flex items-center justify-between gap-3 text-sm">
                        <span className="truncate">{row.label}</span>
                        <span className="shrink-0 font-semibold tabular-nums">{number(row.count)}</span>
                    </div>
                    <div className="h-1.5 overflow-hidden rounded-full bg-muted">
                        <div className="h-full rounded-full bg-primary/70" style={{ width: `${(row.count / peak) * 100}%` }} />
                    </div>
                </li>
            ))}
        </ul>
    );
}

function Empty({ text }: { text: string }) {
    return <p className="py-8 text-center text-sm text-muted-foreground">{text}</p>;
}

export default function Dashboard({ 
    role, 
    can,
    portfolio,
    upcomingMaintenance,
    lifecycle,
    attention,
    recentLifecycle,
    unreadNotifications,
    stats, 
    auditLogs, 
    usersStatus, 
    latestDepreciations, 
    recentAssignments, 
    assetIntake 
}: Props) {
    // canonicalRole() emits super_admin / management / department_head / employee.
    // The aliases are kept so a legacy role value still lands in the right branch.
    const isEmployee = role === 'employee' || role === 'staff';
    const isAdmin = role === 'super_admin' || role === 'admin';
    const isManager = role === 'management' || role === 'manager';
    const isDepartmentHead = role === 'department_head';
    const roleName = isAdmin
        ? 'Administrator'
        : isManager
          ? 'Manager'
          : isDepartmentHead
            ? 'Department head'
            : isEmployee
              ? 'Employee'
              : 'User';

    // Role-specific stat cards
    const statCards = isEmployee
        ? [
            { label: 'Assigned assets', value: stats.assets, icon: Boxes, accent: 'blue' as Accent, href: '/inventory', delta: stats.in_use },
        ]
        : isAdmin
            ? [
                { label: 'Total assets', value: stats.assets, icon: Boxes, accent: 'blue' as Accent, href: '/inventory', delta: `${stats.in_use} in use` },
                { label: 'Locations', value: stats.locations, icon: Building2, accent: 'emerald' as Accent, href: '/locations' },
                { label: 'User accounts', value: stats.accounts, icon: Users, accent: 'amber' as Accent, href: '/users' },
                { label: 'Asset categories', value: stats.categories, icon: ClipboardList, accent: 'purple' as Accent, href: '/categories' },
            ]
            : [
                { label: 'Total assets', value: stats.assets, icon: Boxes, accent: 'blue' as Accent, href: '/inventory', delta: `${stats.in_use} in use` },
                { label: 'In storage', value: stats.in_storage, icon: PackageOpen, accent: 'cyan' as Accent, href: '/inventory' },
                { label: 'Categories', value: stats.categories, icon: ClipboardList, accent: 'purple' as Accent, href: '/categories' },
                { label: 'Locations', value: stats.locations, icon: Building2, accent: 'emerald' as Accent, href: '/locations' },
            ];

    // Quick action buttons
    const actions = isEmployee
        ? [
            { label: 'My assets', href: '/inventory', icon: Boxes, variant: 'default' as const },
            { label: 'Report issue', href: '/report-issue', icon: AlertCircle, variant: 'outline' as const },
        ]
        : isAdmin
            ? [
                { label: 'Add asset', href: '/inventory/create', icon: Plus, variant: 'default' as const },
                { label: 'New assignment', href: '/assignments/create', icon: UserCheck, variant: 'default' as const },
                { label: 'Maintenance', href: '/maintenance', icon: Wrench, variant: 'outline' as const },
                { label: 'Depreciation', href: '/depreciation', icon: WalletCards, variant: 'outline' as const },
            ]
            : [
                { label: 'View assignments', href: '/assignments', icon: Boxes, variant: 'default' as const },
                { label: 'Depreciation', href: '/depreciation', icon: WalletCards, variant: 'default' as const },
                { label: 'Assignment audits', href: '/assignment-audits', icon: CheckCircle2, variant: 'outline' as const },
            ];

    return (
        <AppLayout title="Dashboard" description="Asset management command center">
            {/* Needs attention - only rendered when something is actually wrong */}
            {attention.length > 0 && (
                <section className="mt-8">
                    <h2 className="mb-4 text-lg font-semibold">Needs attention</h2>
                    <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        {attention.map((item) => {
                            const tone = attentionTones[item.tone];

                            return (
                                <Link
                                    key={item.label}
                                    href={item.href}
                                    className={`group flex items-center justify-between gap-3 rounded-lg border px-4 py-3 transition-colors ${tone.box}`}
                                >
                                    <span className="flex items-center gap-3">
                                        <tone.icon className="size-4 shrink-0" />
                                        <span className="text-sm">
                                            <span className="font-semibold tabular-nums">{item.count}</span> {item.label}
                                        </span>
                                    </span>
                                    <ArrowRight className="size-4 shrink-0 opacity-60 transition-transform group-hover:translate-x-0.5" />
                                </Link>
                            );
                        })}
                    </div>
                </section>
            )}

            {/* Key Metrics Grid */}
            <section className="mt-8">
                <h2 className="mb-4 text-lg font-semibold">Key metrics</h2>
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {statCards.map((card) => (
                        <StatCard key={card.label} {...card} />
                    ))}
                </div>
            </section>

            {/* Asset portfolio - how the estate actually breaks down */}
            <section className="mt-8">
                <h2 className="mb-4 text-lg font-semibold">Asset portfolio</h2>
                <div className="grid gap-6 xl:grid-cols-3">
                    <Card>
                        <CardHeader className="pb-4">
                            <CardTitle className="flex items-center gap-2 text-base">
                                <Boxes className="size-4 text-blue-600" />
                                By status
                            </CardTitle>
                            <p className="mt-1 text-sm text-muted-foreground">Working state on the asset record</p>
                        </CardHeader>
                        <CardContent>
                            <BreakdownBar rows={portfolio.status} colours={statusColours} />
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-4">
                            <CardTitle className="flex items-center gap-2 text-base">
                                <CheckCircle2 className="size-4 text-emerald-600" />
                                By condition
                            </CardTitle>
                            <p className="mt-1 text-sm text-muted-foreground">Physical condition on last inspection</p>
                        </CardHeader>
                        <CardContent>
                            <BreakdownBar rows={portfolio.condition} colours={conditionColours} />
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-4">
                            <CardTitle className="flex items-center gap-2 text-base">
                                <ShieldAlert className="size-4 text-amber-600" />
                                Warranty coverage
                            </CardTitle>
                            <p className="mt-1 text-sm text-muted-foreground">Assets in service with no warranty on file</p>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <div>
                                    <p className="text-3xl font-semibold tabular-nums">{number(portfolio.uncovered)}</p>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        of {number(stats.assets)} assets carry no warranty record
                                    </p>
                                </div>

                                <div className="h-2 overflow-hidden rounded-full bg-muted">
                                    <div
                                        className="h-full rounded-full bg-amber-500"
                                        style={{ width: `${stats.assets ? Math.min((portfolio.uncovered / stats.assets) * 100, 100) : 0}%` }}
                                    />
                                </div>

                                {can.maintenance && (
                                    <Link
                                        href="/warranties/create"
                                        className="inline-flex items-center gap-1 text-sm font-semibold text-primary hover:underline"
                                    >
                                        Record a warranty <ArrowRight className="size-3.5" />
                                    </Link>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </section>

            {/* Where the assets sit */}
            {(portfolio.categories.length > 0 || portfolio.locations.length > 0) && (
                <section className="mt-8 grid gap-6 xl:grid-cols-2">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-4">
                            <div>
                                <CardTitle className="flex items-center gap-2 text-base">
                                    <ClipboardList className="size-4 text-purple-600" />
                                    Top categories
                                </CardTitle>
                                <p className="mt-1 text-sm text-muted-foreground">Where the estate is concentrated</p>
                            </div>
                            <Link href="/categories" className="text-xs font-semibold text-primary hover:underline">
                                View all &rarr;
                            </Link>
                        </CardHeader>
                        <CardContent>
                            <RankedList rows={portfolio.categories} emptyText="No categories in use yet" />
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-4">
                            <div>
                                <CardTitle className="flex items-center gap-2 text-base">
                                    <Building2 className="size-4 text-emerald-600" />
                                    Assets by location
                                </CardTitle>
                                <p className="mt-1 text-sm text-muted-foreground">Distribution across sites</p>
                            </div>
                            <Link href="/locations" className="text-xs font-semibold text-primary hover:underline">
                                View all &rarr;
                            </Link>
                        </CardHeader>
                        <CardContent>
                            <RankedList rows={portfolio.locations} emptyText="No locations recorded yet" />
                        </CardContent>
                    </Card>
                </section>
            )}

            {/* Charts Section */}
            <section className="mt-8 grid gap-6 xl:grid-cols-3">
                <Card className="xl:col-span-2">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="flex items-center gap-2">
                                    <TrendingUp className="size-5 text-cyan-600" />
                                    Asset intake
                                </CardTitle>
                                <p className="mt-1 text-sm text-muted-foreground">Last 6 months of new acquisitions</p>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="flex h-48 items-end gap-2 rounded-lg border border-dashed border-muted-foreground/30 bg-muted/30 px-4 py-6">
                            {assetIntake.map((month) => {
                                const max = Math.max(...assetIntake.map((item) => item.value), 1);
                                return (
                                    <div key={month.label} className="flex h-full flex-1 flex-col items-center justify-end gap-2">
                                        <span className="text-xs font-medium text-foreground">{month.value}</span>
                                        <div 
                                            className="w-full rounded-t-lg bg-gradient-to-t from-cyan-600 to-cyan-500 shadow-md transition-all duration-300 hover:shadow-lg hover:from-cyan-500 hover:to-cyan-400" 
                                            style={{ height: `${Math.max(12, (month.value / max) * 100)}%` }} 
                                        />
                                        <span className="text-xs font-medium text-muted-foreground">{month.label}</span>
                                    </div>
                                );
                            })}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <BarChart3 className="size-5 text-rose-600" />
                            Assignment split
                        </CardTitle>
                        <p className="mt-1 text-sm text-muted-foreground">Whether an asset is held by an employee</p>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-6">
                            <div className="flex items-center gap-4">
                                <div 
                                    className="relative flex size-28 items-center justify-center rounded-full shadow-lg" 
                                    style={{ 
                                        background: `conic-gradient(#3b82f6 0 ${stats.assets ? (stats.in_storage / stats.assets) * 360 : 0}deg, #ec4899 ${stats.assets ? (stats.in_storage / stats.assets) * 360 : 0}deg 360deg)` 
                                    }}
                                >
                                    <div className="flex size-20 flex-col items-center justify-center rounded-full bg-background font-semibold shadow-inner">
                                        <span className="text-xl">{number(stats.assets)}</span>
                                        <span className="text-xs text-muted-foreground">Total</span>
                                    </div>
                                </div>
                            </div>
                            <div className="space-y-3">
                                <div className="flex items-center justify-between rounded-lg bg-blue-50 p-3 dark:bg-blue-950/30">
                                    <div className="flex items-center gap-2">
                                        <div className="size-3 rounded-full bg-blue-600" />
                                        <span className="text-sm font-medium">Unassigned</span>
                                    </div>
                                    <span className="font-semibold">{number(stats.in_storage)}</span>
                                </div>
                                <div className="flex items-center justify-between rounded-lg bg-pink-50 p-3 dark:bg-pink-950/30">
                                    <div className="flex items-center gap-2">
                                        <div className="size-3 rounded-full bg-pink-600" />
                                        <span className="text-sm font-medium">Assigned</span>
                                    </div>
                                    <span className="font-semibold">{number(stats.in_use)}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </section>

            {/* Quick Actions Section */}
            <section className="mt-8">
                <h2 className="mb-4 text-lg font-semibold">Quick actions</h2>
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    {actions.map(({ label, href, icon: Icon, variant }) => (
                        <Link
                            key={label}
                            href={href}
                            className={`group flex items-center justify-between rounded-lg border-2 px-4 py-3 transition-all ${
                                variant === 'default'
                                    ? 'border-primary bg-primary/5 hover:border-primary hover:bg-primary/10'
                                    : 'border-muted hover:border-foreground/30 hover:bg-muted'
                            }`}
                        >
                            <span className="flex items-center gap-3">
                                <Icon className="size-4" />
                                <span className="font-medium text-sm">{label}</span>
                            </span>
                            <ArrowRight className="size-4 text-muted-foreground opacity-0 transition-all group-hover:opacity-100 group-hover:translate-x-1" />
                        </Link>
                    ))}
                </div>
            </section>

            {/* Asset lifecycle - maintenance, warranty, transfer, disposal at a glance */}
            {(lifecycle.maintenance || lifecycle.transfers || lifecycle.disposals) && (
                <section className="mt-8">
                    <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <h2 className="text-lg font-semibold">Asset lifecycle</h2>
                        {can.reports && (
                            <Link href="/reports" className="inline-flex items-center gap-1 text-sm font-semibold text-primary hover:underline">
                                Full reports <ArrowRight className="size-3.5" />
                            </Link>
                        )}
                    </div>

                    <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        {lifecycle.maintenance && (
                            <StatCard
                                label="Open maintenance"
                                value={lifecycle.maintenance.open + lifecycle.maintenance.legacy_open}
                                description={lifecycle.maintenance.overdue > 0 ? `${lifecycle.maintenance.overdue} past the scheduled date` : 'Nothing overdue'}
                                href="/maintenance-requests"
                                linkLabel="Open queue"
                                icon={Wrench}
                                tone={lifecycle.maintenance.overdue > 0 ? 'danger' : 'success'}
                            />
                        )}

                        {lifecycle.warranty && (
                            <StatCard
                                label="Warranty alerts"
                                value={lifecycle.warranty.expiring + lifecycle.warranty.expired}
                                description={`${lifecycle.warranty.expired} lapsed, ${lifecycle.warranty.expiring} expiring in 30 days`}
                                href="/warranties"
                                linkLabel="Review cover"
                                icon={ShieldAlert}
                                tone={lifecycle.warranty.expired > 0 ? 'danger' : 'success'}
                            />
                        )}

                        {lifecycle.transfers && (
                            <StatCard
                                label="Transfers pending"
                                value={lifecycle.transfers.pending}
                                description={lifecycle.transfers.overdue > 0 ? `${lifecycle.transfers.overdue} waiting over a week` : 'All within a week'}
                                href="/transfers"
                                linkLabel="Open transfers"
                                icon={ArrowLeftRight}
                                tone={lifecycle.transfers.overdue > 0 ? 'warning' : 'success'}
                            />
                        )}

                        {lifecycle.disposals && (
                            <StatCard
                                label="Disposals"
                                value={lifecycle.disposals.disposed}
                                description={`${lifecycle.disposals.pending} awaiting approval · ${number(lifecycle.disposals.value_recovered)} recovered`}
                                href="/disposals"
                                linkLabel="Open disposals"
                                icon={Trash2}
                                tone="neutral"
                            />
                        )}
                    </div>
                </section>
            )}

            {/* Work booked in for the coming week */}
            {upcomingMaintenance.length > 0 && (
                <section className="mt-8">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-4">
                            <div>
                                <CardTitle className="flex items-center gap-2">
                                    <Wrench className="size-5 text-orange-600" />
                                    Scheduled this week
                                </CardTitle>
                                <p className="mt-1 text-sm text-muted-foreground">Maintenance booked in over the next 7 days</p>
                            </div>
                            <Link href="/maintenance-requests" className="text-xs font-semibold text-primary hover:underline">
                                View all &rarr;
                            </Link>
                        </CardHeader>
                        <CardContent>
                            <div className="divide-y">
                                {upcomingMaintenance.map((job) => (
                                    <Link
                                        key={job.id}
                                        href={`/maintenance-requests/${job.id}`}
                                        className="flex items-center justify-between gap-4 py-3 transition-colors hover:text-primary"
                                    >
                                        <div className="min-w-0">
                                            <p className="truncate text-sm font-medium">{job.title}</p>
                                            <p className="text-xs text-muted-foreground">
                                                {job.asset_code ?? 'Unlinked asset'} &middot; {job.assigned_to ?? 'Unassigned'}
                                            </p>
                                        </div>
                                        <div className="shrink-0 text-right">
                                            <Badge variant="secondary" className="text-xs">
                                                {job.days_away === 0 ? 'Today' : job.days_away === 1 ? 'Tomorrow' : `In ${job.days_away} days`}
                                            </Badge>
                                            <p className="mt-1 text-xs text-muted-foreground">{job.scheduled_at}</p>
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </section>
            )}

            {/* Lifecycle audit trail */}
            {recentLifecycle.length > 0 && (
                <section className="mt-8">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-4">
                            <div>
                                <CardTitle className="flex items-center gap-2">
                                    <History className="size-5 text-violet-600" />
                                    Lifecycle activity
                                </CardTitle>
                                <p className="mt-1 text-sm text-muted-foreground">Latest recorded events across all assets</p>
                            </div>
                            {can.notifications && unreadNotifications > 0 && (
                                <Link href="/notifications" className="inline-flex items-center gap-1.5 text-xs font-semibold text-primary hover:underline">
                                    <BellRing className="size-3.5" /> {unreadNotifications} unread
                                </Link>
                            )}
                        </CardHeader>
                        <CardContent>
                            <div className="divide-y">
                                {recentLifecycle.map((event) => (
                                    <Link
                                        key={event.id}
                                        href={`/inventory/${event.asset_id}/lifecycle`}
                                        className="flex items-start justify-between gap-3 py-3 transition-colors hover:text-primary"
                                    >
                                        <div className="min-w-0">
                                            <p className="text-sm font-medium">
                                                {event.asset_code ?? 'Asset'} · {event.event_label}
                                            </p>
                                            <p className="truncate text-xs text-muted-foreground">{event.description}</p>
                                        </div>
                                        <div className="shrink-0 text-right">
                                            <p className="text-xs text-muted-foreground">{event.time ?? '-'}</p>
                                            <p className="text-xs text-muted-foreground">{event.user ?? 'System'}</p>
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </section>
            )}

            {/* Recent Activity Section */}
            <section className="mt-8 grid gap-6 xl:grid-cols-2">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-4">
                        <div>
                            <CardTitle className="flex items-center gap-2">
                                <CheckCircle2 className="size-5 text-emerald-600" />
                                Recent assignments
                            </CardTitle>
                            <p className="mt-1 text-sm text-muted-foreground">Latest asset allocations</p>
                        </div>
                        <Link href="/assignments" className="text-xs font-semibold text-primary hover:underline">
                            View all →
                        </Link>
                    </CardHeader>
                    <CardContent>
                        {recentAssignments.length === 0 ? (
                            <Empty text="No assignments yet" />
                        ) : (
                            <div className="divide-y">
                                {recentAssignments.map((assignment) => (
                                    <div key={assignment.id} className="flex items-center justify-between gap-4 py-3">
                                        <div>
                                            <p className="font-medium text-sm">{assignment.asset_code || '—'}</p>
                                            <p className="text-xs text-muted-foreground">{assignment.employee || '—'}</p>
                                        </div>
                                        <div className="text-right">
                                            <Badge variant="secondary" className="text-xs">In Use</Badge>
                                            <p className="mt-1 text-xs text-muted-foreground">{assignment.date || '—'}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-4">
                        <div>
                            <CardTitle className="flex items-center gap-2">
                                <Activity className="size-5 text-blue-600" />
                                System activity
                            </CardTitle>
                            <p className="mt-1 text-sm text-muted-foreground">Recent actions and changes</p>
                        </div>
                        <Link href="/audit" className="text-xs font-semibold text-primary hover:underline">
                            View all →
                        </Link>
                    </CardHeader>
                    <CardContent>
                        {auditLogs.length === 0 ? (
                            <Empty text="No activity yet" />
                        ) : (
                            <div className="divide-y">
                                {auditLogs.map((log) => (
                                    <div key={log.id} className="flex items-start justify-between gap-3 py-3">
                                        <div className="flex gap-3">
                                            <div className="mt-0.5 flex size-8 items-center justify-center rounded-full bg-muted">
                                                <Activity className="size-3.5 text-muted-foreground" />
                                            </div>
                                            <div>
                                                <p className="text-sm font-medium">{log.user}</p>
                                                <p className="text-xs text-muted-foreground">{log.time || 'Recently'}</p>
                                            </div>
                                        </div>
                                        <Badge variant="outline" className="shrink-0 text-xs">{log.action}</Badge>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </section>

            {/* Admin: User Status & System Insights */}
            {isAdmin && (
                <section className="mt-8 grid gap-6 xl:grid-cols-2">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-4">
                            <div>
                                <CardTitle className="flex items-center gap-2">
                                    <Users className="size-5 text-amber-600" />
                                    User accounts
                                </CardTitle>
                                <p className="mt-1 text-sm text-muted-foreground">Active user status</p>
                            </div>
                            <Link href="/user/status" className="text-xs font-semibold text-primary hover:underline">
                                Manage →
                            </Link>
                        </CardHeader>
                        <CardContent>
                            {usersStatus.length === 0 ? (
                                <Empty text="No users found" />
                            ) : (
                                <div className="divide-y">
                                    {usersStatus.map((user) => (
                                        <div key={user.id} className="flex items-center justify-between gap-3 py-3">
                                            <div>
                                                <p className="text-sm font-medium">{user.name}</p>
                                                <p className="text-xs text-muted-foreground">{user.role}</p>
                                            </div>
                                            <Badge variant={user.online ? 'default' : 'secondary'} className="text-xs">
                                                {user.online ? 'Online' : 'Offline'}
                                            </Badge>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Wrench className="size-5 text-orange-600" />
                                Maintenance status
                            </CardTitle>
                            <p className="mt-1 text-sm text-muted-foreground">Current work orders</p>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <div className="flex items-center justify-between rounded-lg bg-orange-50 p-4 dark:bg-orange-950/30">
                                    <div>
                                        <p className="text-sm font-medium">Open tasks</p>
                                        <p className="text-xs text-muted-foreground">Scheduled & In progress</p>
                                    </div>
                                    <span className="text-2xl font-bold text-orange-600">{number(stats.open_maintenance)}</span>
                                </div>
                                <div className="flex items-center justify-between rounded-lg bg-emerald-50 p-4 dark:bg-emerald-950/30">
                                    <div>
                                        <p className="text-sm font-medium">Completed</p>
                                        <p className="text-xs text-muted-foreground">This period</p>
                                    </div>
                                    <span className="text-2xl font-bold text-emerald-600">{number(stats.completed_maintenance)}</span>
                                </div>
                                <Link 
                                    href="/maintenance" 
                                    className="block rounded-lg border-2 border-muted px-4 py-2 text-center text-sm font-medium transition-all hover:border-foreground/30 hover:bg-muted"
                                >
                                    View maintenance →
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </section>
            )}

            {/* Manager: Depreciation & Audits */}
            {isManager && (
                <section className="mt-8 grid gap-6 xl:grid-cols-2">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-4">
                            <div>
                                <CardTitle className="flex items-center gap-2">
                                    <WalletCards className="size-5 text-purple-600" />
                                    Latest depreciations
                                </CardTitle>
                                <p className="mt-1 text-sm text-muted-foreground">Recent depreciation entries</p>
                            </div>
                            <Link href="/depreciation" className="text-xs font-semibold text-primary hover:underline">
                                View all →
                            </Link>
                        </CardHeader>
                        <CardContent>
                            {latestDepreciations.length === 0 ? (
                                <Empty text="No depreciation records" />
                            ) : (
                                <div className="divide-y">
                                    {latestDepreciations.map((asset) => (
                                        <Link
                                            key={asset.id}
                                            href={`/depreciation/${asset.id}`}
                                            className="flex items-center justify-between gap-3 py-3 transition-colors hover:text-primary"
                                        >
                                            <div>
                                                <p className="text-sm font-medium">{asset.name}</p>
                                                <p className="text-xs text-muted-foreground">{asset.code}</p>
                                            </div>
                                            <span className="text-xs text-muted-foreground">{asset.period || '—'}</span>
                                        </Link>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <CheckCircle2 className="size-5 text-cyan-600" />
                                Assignment audits
                            </CardTitle>
                            <p className="mt-1 text-sm text-muted-foreground">Verification workflows</p>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3">
                                <p className="text-sm text-muted-foreground">
                                    Monitor and verify asset assignments to ensure accurate inventory records.
                                </p>
                                <Link 
                                    href="/assignment-audits" 
                                    className="block rounded-lg border-2 border-primary bg-primary/5 px-4 py-2 text-center text-sm font-medium transition-all hover:bg-primary/10"
                                >
                                    Start audit workflow →
                                </Link>
                                <Link 
                                    href="/assignments" 
                                    className="block rounded-lg border-2 border-muted px-4 py-2 text-center text-sm font-medium transition-all hover:border-foreground/30"
                                >
                                    Review assignments &rarr;
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </section>
            )}
        </AppLayout>
    );
}
