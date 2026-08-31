import AppLayout from '@/layouts/app-layout';
import { Link, router } from '@inertiajs/react';
import { Eye, Hourglass, Plus, Trash2, Wallet, X } from 'lucide-react';

import Pagination from '@/components/pagination';
import StatCard from '@/components/stat-card';
import StatusBadge from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface DisposalItem {
    id: number;
    asset_code: string | null;
    asset_name: string | null;
    status: string;
    status_label: string;
    reason: string | null;
    method: string | null;
    value_recovered: number | string | null;
    requested_at: string | null;
    requested_by: string | null;
    approved_by: string | null;
}

interface Props {
    title: string;
    description: string;
    disposals: DisposalItem[];
    pagination: { links: { url: string | null; label: string; active: boolean }[]; from: number | null; to: number | null; total: number };
    statuses: Record<string, string>;
    filters: { status: string; asset_id: string };
    summary: { pending: number; approved: number; disposed: number; rejected: number; value_recovered: number };
}

export default function DisposalsIndex({ title, description, disposals, pagination, statuses, filters, summary }: Props) {
    const filterByStatus = (status: string) => {
        router.get('/disposals', status ? { status } : {}, { preserveScroll: true, preserveState: true, replace: true });
    };

    return (
        <AppLayout
            title={title}
            description={description}
            actions={
                <Button asChild>
                    <Link href="/disposals/create">
                        <Plus className="size-4" /> New disposal
                    </Link>
                </Button>
            }
        >
            <div className="space-y-6">
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <StatCard label="Pending approval" value={summary.pending} description="Requests awaiting a decision" tone={summary.pending > 0 ? 'warning' : 'success'} icon={Hourglass} />
                    <StatCard label="Disposed" value={summary.disposed} description="Assets retired from the estate" icon={Trash2} />
                    <StatCard label="Rejected" value={summary.rejected} description="Requests turned down" tone={summary.rejected > 0 ? 'danger' : 'neutral'} icon={X} />
                    <StatCard
                        label="Value recovered"
                        value={summary.value_recovered.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                        description="Proceeds from disposed assets"
                        tone="success"
                        icon={Wallet}
                    />
                </div>

                <Card>
                    <CardHeader>
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <CardTitle className="text-base">Disposal requests</CardTitle>
                                <CardDescription>{pagination.total} records in total.</CardDescription>
                            </div>

                            <div className="flex items-center gap-2">
                                <label htmlFor="status-filter" className="whitespace-nowrap text-sm text-muted-foreground">
                                    Status
                                </label>
                                <select
                                    id="status-filter"
                                    value={filters.status}
                                    onChange={(event) => filterByStatus(event.target.value)}
                                    className="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                >
                                    <option value="">All statuses</option>
                                    {Object.entries(statuses).map(([value, label]) => (
                                        <option key={value} value={value}>
                                            {label}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        {disposals.length === 0 ? (
                            <p className="py-10 text-center text-sm text-muted-foreground">
                                {filters.status ? 'No disposals match this status.' : 'No disposals yet.'}
                            </p>
                        ) : (
                            <div className="space-y-3">
                                {disposals.map((disposal) => (
                                    <div key={disposal.id} className="flex flex-col gap-3 rounded-lg border p-4 md:flex-row md:items-center md:justify-between">
                                        <div className="min-w-0">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <p className="font-semibold">
                                                    {disposal.asset_code ?? '-'} · {disposal.asset_name ?? '-'}
                                                </p>
                                                <StatusBadge status={disposal.status} label={disposal.status_label} />
                                            </div>

                                            <p className="mt-1.5 text-sm text-muted-foreground">
                                                {disposal.reason || 'No reason provided'} · {disposal.method || 'No method recorded'}
                                            </p>
                                            <p className="mt-1 text-sm text-muted-foreground">
                                                Recovered {disposal.value_recovered ?? 0} · requested {disposal.requested_at ?? '-'} by {disposal.requested_by ?? 'unknown'}
                                                {disposal.approved_by ? ` · approved by ${disposal.approved_by}` : ''}
                                            </p>
                                        </div>

                                        <Button variant="outline" size="sm" asChild className="shrink-0">
                                            <Link href={`/disposals/${disposal.id}`}>
                                                <Eye className="size-4" /> View
                                            </Link>
                                        </Button>
                                    </div>
                                ))}
                            </div>
                        )}

                        <Pagination links={pagination.links} from={pagination.from} to={pagination.to} total={pagination.total} />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
