import AppLayout from '@/layouts/app-layout';
import { Link, router } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, Download, History, Route } from 'lucide-react';

import StatusBadge from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface AssetLog {
    id: number;
    event_type: string;
    event_label: string;
    description: string | null;
    event_at: string | null;
    user: { name: string } | null;
    old_values: Record<string, unknown> | null;
    new_values: Record<string, unknown> | null;
}

interface Movement {
    id: string;
    kind: string;
    url: string;
    from: string | null;
    to: string | null;
    status: string;
    status_label: string;
    reason: string | null;
    notes: string | null;
    requested_by: string | null;
    approved_by: string | null;
    date: string | null;
}

interface Props {
    asset: { id: number; asset_code: string; asset_name: string; status: string; location: string | null; employee: string | null };
    logs: AssetLog[];
    movements: Movement[];
    eventTypes: Record<string, string>;
    filters: { event_type: string };
    exportUrl: string;
}

export default function AssetLifecyclePage({ asset, logs, movements, eventTypes, filters, exportUrl }: Props) {
    const applyFilter = (eventType: string) => {
        router.get(
            `/inventory/${asset.id}/lifecycle`,
            eventType ? { event_type: eventType } : {},
            { preserveScroll: true, preserveState: true, replace: true },
        );
    };

    return (
        <AppLayout
            title="Asset lifecycle"
            description="Every recorded event and movement for this asset"
            actions={
                <div className="flex flex-wrap gap-2">
                    <Button variant="outline" asChild>
                        <Link href={`/inventory/${asset.id}`}>
                            <ArrowLeft className="size-4" /> Back
                        </Link>
                    </Button>
                    <Button variant="outline" asChild>
                        <a href={exportUrl}>
                            <Download className="size-4" /> Export history
                        </a>
                    </Button>
                </div>
            }
        >
            <div className="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">
                            {asset.asset_code} · {asset.asset_name}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <dl className="grid gap-4 sm:grid-cols-3">
                            <div>
                                <dt className="text-sm text-muted-foreground">Status</dt>
                                <dd className="mt-1"><StatusBadge status={asset.status} /></dd>
                            </div>
                            <div>
                                <dt className="text-sm text-muted-foreground">Location</dt>
                                <dd className="mt-1 font-medium">{asset.location ?? '-'}</dd>
                            </div>
                            <div>
                                <dt className="text-sm text-muted-foreground">Assigned to</dt>
                                <dd className="mt-1 font-medium">{asset.employee ?? 'Unassigned'}</dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <div className="flex flex-col gap-2">
                            <div className="flex items-center gap-2">
                                <Route className="size-4 text-muted-foreground" />
                                <CardTitle className="text-base">Movement history</CardTitle>
                            </div>
                            <CardDescription>Transfers and disposals, with the reason, notes, and who approved each one.</CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent>
                        {movements.length === 0 ? (
                            <p className="py-8 text-center text-sm text-muted-foreground">This asset has never been transferred or put up for disposal.</p>
                        ) : (
                            <ol className="space-y-3">
                                {movements.map((movement) => (
                                    <li key={movement.id} className="rounded-lg border p-4">
                                        <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                            <div className="min-w-0">
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <span className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">{movement.kind}</span>
                                                    <StatusBadge status={movement.status} label={movement.status_label} />
                                                </div>

                                                <p className="mt-2 flex flex-wrap items-center gap-2 font-medium">
                                                    <span>{movement.from ?? 'Unrecorded'}</span>
                                                    <ArrowRight className="size-4 shrink-0 text-muted-foreground" />
                                                    <span>{movement.to ?? 'Unrecorded'}</span>
                                                </p>

                                                <p className="mt-1 text-sm text-muted-foreground">Reason: {movement.reason || 'Not stated'}</p>
                                                {movement.notes && <p className="mt-1 text-sm text-muted-foreground">Notes: {movement.notes}</p>}
                                            </div>

                                            <div className="shrink-0 text-sm md:text-right">
                                                <p className="text-muted-foreground">{movement.date ?? '-'}</p>
                                                <p className="mt-1">Requested by {movement.requested_by ?? 'unknown'}</p>
                                                <p className="mt-0.5">
                                                    {movement.approved_by ? `Approved by ${movement.approved_by}` : 'Not yet approved'}
                                                </p>
                                                <Link href={movement.url} className="mt-2 inline-flex items-center gap-1 font-medium text-primary">
                                                    View record <ArrowRight className="size-3.5" />
                                                </Link>
                                            </div>
                                        </div>
                                    </li>
                                ))}
                            </ol>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div className="flex items-center gap-2">
                                <History className="size-4 text-muted-foreground" />
                                <CardTitle className="text-base">Lifecycle timeline</CardTitle>
                            </div>

                            <div className="flex items-center gap-2">
                                <label htmlFor="event-filter" className="whitespace-nowrap text-sm text-muted-foreground">
                                    Event type
                                </label>
                                <select
                                    id="event-filter"
                                    value={filters.event_type}
                                    onChange={(event) => applyFilter(event.target.value)}
                                    className="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                >
                                    <option value="">All events</option>
                                    {Object.entries(eventTypes).map(([value, label]) => (
                                        <option key={value} value={value}>
                                            {label}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        {logs.length === 0 ? (
                            <p className="py-8 text-center text-sm text-muted-foreground">
                                {filters.event_type ? 'No events of this type recorded for this asset.' : 'No lifecycle events recorded yet.'}
                            </p>
                        ) : (
                            <ol className="relative space-y-4 border-l pl-6">
                                {logs.map((log) => (
                                    <li key={log.id} className="relative">
                                        <span className="absolute -left-[1.9rem] top-1.5 size-2.5 rounded-full border-2 border-background bg-primary" aria-hidden="true" />
                                        <div className="rounded-lg border p-4">
                                            <div className="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                                                <div className="min-w-0">
                                                    <p className="font-semibold">{log.event_label}</p>
                                                    <p className="mt-0.5 text-sm text-muted-foreground">{log.description}</p>
                                                </div>
                                                <div className="shrink-0 text-sm text-muted-foreground md:text-right">
                                                    <div>{log.event_at ?? '-'}</div>
                                                    <div>{log.user?.name ?? 'System'}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                ))}
                            </ol>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
