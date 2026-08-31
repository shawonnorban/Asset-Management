import AppLayout from '@/layouts/app-layout';
import { Link, router } from '@inertiajs/react';
import { ArrowRight, Check, CheckCheck, Eye, Hourglass, Plus, X } from 'lucide-react';

import Pagination from '@/components/pagination';
import StatCard from '@/components/stat-card';
import StatusBadge from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface TransferItem {
    id: number;
    asset_code: string | null;
    asset_name: string | null;
    from_location: string | null;
    to_location: string | null;
    status: string;
    status_label: string;
    requested_at: string | null;
    requested_by: string | null;
    approved_by: string | null;
    reason: string | null;
}

interface Props {
    title: string;
    description: string;
    transfers: TransferItem[];
    pagination: { links: { url: string | null; label: string; active: boolean }[]; from: number | null; to: number | null; total: number };
    statuses: Record<string, string>;
    filters: { status: string; asset_id: string };
    summary: { pending: number; approved: number; completed: number; rejected: number };
}

export default function TransfersIndex({ title, description, transfers, pagination, statuses, filters, summary }: Props) {
    const filterByStatus = (status: string) => {
        router.get('/transfers', status ? { status } : {}, { preserveScroll: true, preserveState: true, replace: true });
    };

    return (
        <AppLayout
            title={title}
            description={description}
            actions={
                <Button asChild>
                    <Link href="/transfers/create">
                        <Plus className="size-4" /> New transfer
                    </Link>
                </Button>
            }
        >
            <div className="space-y-6">
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <StatCard label="Pending approval" value={summary.pending} description="Requests awaiting a decision" tone={summary.pending > 0 ? 'warning' : 'success'} icon={Hourglass} />
                    <StatCard label="Approved" value={summary.approved} description="Approved and applied to the asset" tone="success" icon={Check} />
                    <StatCard label="Completed" value={summary.completed} description="Movement finished end to end" icon={CheckCheck} />
                    <StatCard label="Rejected" value={summary.rejected} description="Requests turned down" tone={summary.rejected > 0 ? 'danger' : 'neutral'} icon={X} />
                </div>

                <Card>
                    <CardHeader>
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <CardTitle className="text-base">Transfer requests</CardTitle>
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
                        {transfers.length === 0 ? (
                            <p className="py-10 text-center text-sm text-muted-foreground">
                                {filters.status ? 'No transfers match this status.' : 'No transfers yet.'}
                            </p>
                        ) : (
                            <div className="space-y-3">
                                {transfers.map((transfer) => (
                                    <div key={transfer.id} className="flex flex-col gap-3 rounded-lg border p-4 md:flex-row md:items-center md:justify-between">
                                        <div className="min-w-0">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <p className="font-semibold">
                                                    {transfer.asset_code ?? '-'} · {transfer.asset_name ?? '-'}
                                                </p>
                                                <StatusBadge status={transfer.status} label={transfer.status_label} />
                                            </div>

                                            <p className="mt-1.5 flex flex-wrap items-center gap-2 text-sm">
                                                <span>{transfer.from_location ?? 'Unrecorded'}</span>
                                                <ArrowRight className="size-3.5 shrink-0 text-muted-foreground" />
                                                <span>{transfer.to_location ?? 'Unrecorded'}</span>
                                            </p>

                                            <p className="mt-1 text-sm text-muted-foreground">{transfer.reason || 'No reason provided'}</p>
                                            <p className="mt-1 text-sm text-muted-foreground">
                                                Requested {transfer.requested_at ?? '-'} by {transfer.requested_by ?? 'unknown'}
                                                {transfer.approved_by ? ` · approved by ${transfer.approved_by}` : ''}
                                            </p>
                                        </div>

                                        <Button variant="outline" size="sm" asChild className="shrink-0">
                                            <Link href={`/transfers/${transfer.id}`}>
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
