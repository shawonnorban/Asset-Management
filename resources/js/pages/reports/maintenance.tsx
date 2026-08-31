import AppLayout from '@/layouts/app-layout';
import { Link } from '@inertiajs/react';
import { AlarmClock, ArrowLeft, CheckCircle2, Wallet, Wrench } from 'lucide-react';

import AlertBanner from '@/components/alert-banner';
import ExportMenu from '@/components/export-menu';
import StatCard from '@/components/stat-card';
import StatusBadge from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

interface MonthlyCost {
    month: string;
    key: string;
    cost: number;
}

interface OverdueRow {
    id: number;
    title: string;
    asset_code: string | null;
    scheduled_at: string | null;
    days_overdue: number;
    priority: string;
    assigned_to: string | null;
}

interface MaintenanceRow {
    id: number;
    title: string;
    asset_code: string | null;
    asset_name: string | null;
    maintenance_type: string | null;
    priority: string;
    status: string;
    vendor_name: string | null;
    requested_at: string | null;
    scheduled_at: string | null;
    completed_at: string | null;
    estimated_cost: number;
    actual_cost: number;
}

interface Props {
    title: string;
    description: string;
    exportBase: string;
    report: {
        summary: {
            open: number;
            in_progress: number;
            overdue: number;
            completed: number;
            total_cost: number;
            average_cost: number;
        };
        monthly_cost: MonthlyCost[];
        overdue: OverdueRow[];
        rows: MaintenanceRow[];
    };
}

const money = (value: number) => value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

export default function MaintenanceReport({ title, description, report, exportBase }: Props) {
    const { summary, monthly_cost: monthlyCost, overdue, rows } = report;
    const peakCost = Math.max(...monthlyCost.map((month) => month.cost), 1);

    return (
        <AppLayout
            title={title}
            description={description}
            actions={
                <div className="flex flex-wrap gap-2">
                    <Button variant="outline" asChild>
                        <Link href="/reports">
                            <ArrowLeft className="size-4" /> Reports
                        </Link>
                    </Button>
                    <ExportMenu baseUrl={exportBase} />
                </div>
            }
        >
            <div className="space-y-6">
                {summary.overdue > 0 && (
                    <AlertBanner tone="warning" title={`${summary.overdue} maintenance ${summary.overdue === 1 ? 'job is' : 'jobs are'} past the scheduled date`}>
                        These assets are still down and the scheduled service date has passed.
                    </AlertBanner>
                )}

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <StatCard label="Open maintenance" value={summary.open} description="Requests open or in progress" tone={summary.open > 0 ? 'warning' : 'success'} icon={Wrench} />
                    <StatCard label="Overdue jobs" value={summary.overdue} description="Past the scheduled service date" tone={summary.overdue > 0 ? 'danger' : 'success'} icon={AlarmClock} />
                    <StatCard label="Completed" value={summary.completed} description="Requests closed out" tone="success" icon={CheckCircle2} />
                    <StatCard label="Total cost" value={money(summary.total_cost)} description={`Average ${money(summary.average_cost)} per completed job`} icon={Wallet} accent="purple" />
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Monthly maintenance cost</CardTitle>
                        <CardDescription>Last 12 months, by completion date.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <div className="flex min-w-[560px] items-end gap-2" style={{ height: 160 }}>
                                {monthlyCost.map((month) => (
                                    <div key={month.key} className="flex flex-1 flex-col items-center justify-end gap-2">
                                        <span className="text-[11px] font-medium tabular-nums text-muted-foreground">
                                            {month.cost > 0 ? money(month.cost) : ''}
                                        </span>
                                        <div
                                            className="w-full rounded-t bg-primary/80 transition-all"
                                            style={{ height: `${Math.max((month.cost / peakCost) * 110, month.cost > 0 ? 4 : 2)}px` }}
                                            title={`${month.month}: ${money(month.cost)}`}
                                        />
                                        <span className="whitespace-nowrap text-[11px] text-muted-foreground">{month.month}</span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {overdue.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Overdue jobs</CardTitle>
                            <CardDescription>Ordered by the longest wait.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Asset</TableHead>
                                            <TableHead>Job</TableHead>
                                            <TableHead>Priority</TableHead>
                                            <TableHead>Scheduled</TableHead>
                                            <TableHead className="text-right">Days overdue</TableHead>
                                            <TableHead>Assigned to</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {overdue.map((row) => (
                                            <TableRow key={row.id}>
                                                <TableCell className="font-medium">{row.asset_code ?? '-'}</TableCell>
                                                <TableCell>{row.title}</TableCell>
                                                <TableCell><StatusBadge status={row.priority} /></TableCell>
                                                <TableCell>{row.scheduled_at ?? '-'}</TableCell>
                                                <TableCell className="text-right font-semibold tabular-nums text-rose-600 dark:text-rose-400">{row.days_overdue}</TableCell>
                                                <TableCell>{row.assigned_to ?? 'Unassigned'}</TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        </CardContent>
                    </Card>
                )}

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">All maintenance requests</CardTitle>
                        <CardDescription>{rows.length} records.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {rows.length === 0 ? (
                            <p className="py-8 text-center text-sm text-muted-foreground">No maintenance requests recorded yet.</p>
                        ) : (
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Asset</TableHead>
                                            <TableHead>Job</TableHead>
                                            <TableHead>Type</TableHead>
                                            <TableHead>Status</TableHead>
                                            <TableHead>Vendor</TableHead>
                                            <TableHead>Scheduled</TableHead>
                                            <TableHead>Completed</TableHead>
                                            <TableHead className="text-right">Actual cost</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {rows.map((row) => (
                                            <TableRow key={row.id}>
                                                <TableCell className="font-medium">
                                                    {row.asset_code ?? '-'}
                                                    <span className="block text-xs text-muted-foreground">{row.asset_name ?? ''}</span>
                                                </TableCell>
                                                <TableCell>{row.title}</TableCell>
                                                <TableCell>{row.maintenance_type ?? '-'}</TableCell>
                                                <TableCell><StatusBadge status={row.status} /></TableCell>
                                                <TableCell>{row.vendor_name ?? '-'}</TableCell>
                                                <TableCell>{row.scheduled_at ?? '-'}</TableCell>
                                                <TableCell>{row.completed_at ?? '-'}</TableCell>
                                                <TableCell className="text-right tabular-nums">{money(row.actual_cost)}</TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
