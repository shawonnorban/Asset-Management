import AppLayout from '@/layouts/app-layout';
import { Link } from '@inertiajs/react';
import { ArrowLeft, ArrowLeftRight, Hourglass, Trash2, Wallet } from 'lucide-react';

import ExportMenu from '@/components/export-menu';
import StatCard from '@/components/stat-card';
import StatusBadge from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

interface ReasonRow {
    reason: string;
    count: number;
}

interface TransferRow {
    id: number;
    asset_code: string | null;
    asset_name: string | null;
    from: string | null;
    to: string | null;
    status: string;
    reason: string | null;
    requested_by: string | null;
    approved_by: string | null;
    requested_at: string | null;
    transferred_at: string | null;
}

interface DisposalRow {
    id: number;
    asset_code: string | null;
    asset_name: string | null;
    status: string;
    reason: string | null;
    method: string | null;
    value_recovered: number;
    requested_by: string | null;
    approved_by: string | null;
    requested_at: string | null;
    disposed_at: string | null;
}

interface Props {
    title: string;
    description: string;
    exportBase: string;
    report: {
        summary: {
            transfers_total: number;
            transfers_completed: number;
            transfers_pending: number;
            disposals_total: number;
            disposals_completed: number;
            disposals_pending: number;
            value_recovered: number;
        };
        transfer_reasons: ReasonRow[];
        disposal_reasons: ReasonRow[];
        transfers: TransferRow[];
        disposals: DisposalRow[];
    };
}

const money = (value: number) => value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

function ReasonBreakdown({ rows, emptyMessage }: { rows: ReasonRow[]; emptyMessage: string }) {
    if (rows.length === 0) {
        return <p className="py-6 text-center text-sm text-muted-foreground">{emptyMessage}</p>;
    }

    const peak = Math.max(...rows.map((row) => row.count), 1);

    return (
        <ul className="space-y-3">
            {rows.map((row) => (
                <li key={row.reason}>
                    <div className="mb-1 flex items-center justify-between gap-3 text-sm">
                        <span className="truncate">{row.reason}</span>
                        <span className="shrink-0 font-semibold tabular-nums">{row.count}</span>
                    </div>
                    <div className="h-1.5 overflow-hidden rounded-full bg-muted">
                        <div className="h-full rounded-full bg-primary/70" style={{ width: `${(row.count / peak) * 100}%` }} />
                    </div>
                </li>
            ))}
        </ul>
    );
}

export default function MovementReport({ title, description, report, exportBase }: Props) {
    const { summary, transfer_reasons: transferReasons, disposal_reasons: disposalReasons, transfers, disposals } = report;

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
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <StatCard label="Assets transferred" value={summary.transfers_completed} description={`${summary.transfers_total} transfer requests in total`} tone="success" icon={ArrowLeftRight} />
                    <StatCard label="Transfers pending" value={summary.transfers_pending} description="Awaiting an approval decision" tone={summary.transfers_pending > 0 ? 'warning' : 'success'} icon={Hourglass} />
                    <StatCard label="Assets disposed" value={summary.disposals_completed} description={`${summary.disposals_total} disposal requests in total`} icon={Trash2} />
                    <StatCard label="Value recovered" value={money(summary.value_recovered)} description="Proceeds from disposed assets" tone="success" icon={Wallet} />
                </div>

                <div className="grid gap-4 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Why assets moved</CardTitle>
                            <CardDescription>Transfer reason summary.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ReasonBreakdown rows={transferReasons} emptyMessage="No transfers recorded yet." />
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Why assets left</CardTitle>
                            <CardDescription>Disposal reason summary.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ReasonBreakdown rows={disposalReasons} emptyMessage="No disposals recorded yet." />
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Transfers</CardTitle>
                        <CardDescription>{transfers.length} records.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {transfers.length === 0 ? (
                            <p className="py-8 text-center text-sm text-muted-foreground">No transfers recorded yet.</p>
                        ) : (
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Asset</TableHead>
                                            <TableHead>From</TableHead>
                                            <TableHead>To</TableHead>
                                            <TableHead>Status</TableHead>
                                            <TableHead>Reason</TableHead>
                                            <TableHead>Approved by</TableHead>
                                            <TableHead>Settled</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {transfers.map((row) => (
                                            <TableRow key={row.id}>
                                                <TableCell className="font-medium">
                                                    {row.asset_code ?? '-'}
                                                    <span className="block text-xs text-muted-foreground">{row.asset_name ?? ''}</span>
                                                </TableCell>
                                                <TableCell>{row.from ?? '-'}</TableCell>
                                                <TableCell>{row.to ?? '-'}</TableCell>
                                                <TableCell><StatusBadge status={row.status} /></TableCell>
                                                <TableCell className="max-w-[220px] truncate">{row.reason ?? '-'}</TableCell>
                                                <TableCell>{row.approved_by ?? 'Pending'}</TableCell>
                                                <TableCell>{row.transferred_at ?? '-'}</TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Disposals</CardTitle>
                        <CardDescription>{disposals.length} records.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {disposals.length === 0 ? (
                            <p className="py-8 text-center text-sm text-muted-foreground">No disposals recorded yet.</p>
                        ) : (
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Asset</TableHead>
                                            <TableHead>Method</TableHead>
                                            <TableHead>Status</TableHead>
                                            <TableHead>Reason</TableHead>
                                            <TableHead>Approved by</TableHead>
                                            <TableHead>Disposed</TableHead>
                                            <TableHead className="text-right">Value recovered</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {disposals.map((row) => (
                                            <TableRow key={row.id}>
                                                <TableCell className="font-medium">
                                                    {row.asset_code ?? '-'}
                                                    <span className="block text-xs text-muted-foreground">{row.asset_name ?? ''}</span>
                                                </TableCell>
                                                <TableCell>{row.method ?? '-'}</TableCell>
                                                <TableCell><StatusBadge status={row.status} /></TableCell>
                                                <TableCell className="max-w-[220px] truncate">{row.reason ?? '-'}</TableCell>
                                                <TableCell>{row.approved_by ?? 'Pending'}</TableCell>
                                                <TableCell>{row.disposed_at ?? '-'}</TableCell>
                                                <TableCell className="text-right tabular-nums">{money(row.value_recovered)}</TableCell>
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
