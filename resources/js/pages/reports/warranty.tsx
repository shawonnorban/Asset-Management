import AppLayout from '@/layouts/app-layout';
import { Link } from '@inertiajs/react';
import { ArrowLeft, CalendarClock, FileCheck2, ShieldAlert, ShieldCheck } from 'lucide-react';

import AlertBanner from '@/components/alert-banner';
import ExportMenu from '@/components/export-menu';
import StatCard from '@/components/stat-card';
import StatusBadge from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

interface WarrantyRow {
    id: number;
    asset_code: string | null;
    asset_name: string | null;
    vendor_name: string | null;
    warranty_type: string | null;
    start_date: string | null;
    end_date: string | null;
    days_remaining: number | null;
    status: string;
    claim_status: string | null;
}

interface VendorRow {
    vendor_name: string;
    total: number;
    active: number;
    expired: number;
    claims_open: number;
    claims_settled: number;
}

interface Props {
    title: string;
    description: string;
    exportBase: string;
    report: {
        summary: { total: number; active: number; expiring_soon: number; expired: number; claimed: number };
        expiring: WarrantyRow[];
        expired: WarrantyRow[];
        vendors: VendorRow[];
        rows: WarrantyRow[];
    };
}

function WarrantyTable({ rows, emptyMessage }: { rows: WarrantyRow[]; emptyMessage: string }) {
    if (rows.length === 0) {
        return <p className="py-8 text-center text-sm text-muted-foreground">{emptyMessage}</p>;
    }

    return (
        <div className="overflow-x-auto">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Asset</TableHead>
                        <TableHead>Vendor</TableHead>
                        <TableHead>Type</TableHead>
                        <TableHead>Expires</TableHead>
                        <TableHead className="text-right">Days left</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Claim</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {rows.map((row) => (
                        <TableRow key={row.id}>
                            <TableCell className="font-medium">
                                {row.asset_code ?? '-'}
                                <span className="block text-xs text-muted-foreground">{row.asset_name ?? ''}</span>
                            </TableCell>
                            <TableCell>{row.vendor_name ?? '-'}</TableCell>
                            <TableCell>{row.warranty_type ?? '-'}</TableCell>
                            <TableCell>{row.end_date ?? '-'}</TableCell>
                            <TableCell
                                className={`text-right font-semibold tabular-nums ${
                                    row.days_remaining !== null && row.days_remaining < 0
                                        ? 'text-rose-600 dark:text-rose-400'
                                        : row.days_remaining !== null && row.days_remaining <= 30
                                          ? 'text-amber-600 dark:text-amber-400'
                                          : ''
                                }`}
                            >
                                {row.days_remaining ?? '-'}
                            </TableCell>
                            <TableCell><StatusBadge status={row.status} /></TableCell>
                            <TableCell>{row.claim_status ?? '-'}</TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>
        </div>
    );
}

export default function WarrantyReport({ title, description, report, exportBase }: Props) {
    const { summary, expiring, expired, vendors, rows } = report;

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
                {summary.expired > 0 && (
                    <AlertBanner tone="danger" title={`${summary.expired} ${summary.expired === 1 ? 'warranty has' : 'warranties have'} already lapsed`}>
                        Repairs on these assets are now billed at full cost.
                    </AlertBanner>
                )}

                {summary.expiring_soon > 0 && (
                    <AlertBanner tone="warning" title={`${summary.expiring_soon} ${summary.expiring_soon === 1 ? 'warranty expires' : 'warranties expire'} within 30 days`}>
                        Renew the cover or plan a replacement before it runs out.
                    </AlertBanner>
                )}

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <StatCard label="Active cover" value={summary.active} description={`${summary.total} warranties on record`} tone="success" icon={ShieldCheck} />
                    <StatCard label="Expiring in 30 days" value={summary.expiring_soon} description="Still covered, but not for long" tone={summary.expiring_soon > 0 ? 'warning' : 'success'} icon={CalendarClock} />
                    <StatCard label="Expired" value={summary.expired} description="Cover has run out" tone={summary.expired > 0 ? 'danger' : 'success'} icon={ShieldAlert} />
                    <StatCard label="Claims settled" value={summary.claimed} description="Warranties claimed against a vendor" icon={FileCheck2} accent="purple" />
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Expiring in the next 30 days</CardTitle>
                        <CardDescription>Act on these before cover lapses.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <WarrantyTable rows={expiring} emptyMessage="Nothing expires in the next 30 days." />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Expired warranties</CardTitle>
                        <CardDescription>Assets no longer covered by a vendor.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <WarrantyTable rows={expired} emptyMessage="No expired warranties." />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Vendor-wise claim tracking</CardTitle>
                        <CardDescription>Who covers what, and how often it is claimed.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {vendors.length === 0 ? (
                            <p className="py-8 text-center text-sm text-muted-foreground">No vendors recorded yet.</p>
                        ) : (
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Vendor</TableHead>
                                            <TableHead className="text-right">Warranties</TableHead>
                                            <TableHead className="text-right">Active</TableHead>
                                            <TableHead className="text-right">Expired</TableHead>
                                            <TableHead className="text-right">Claims open</TableHead>
                                            <TableHead className="text-right">Claims settled</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {vendors.map((vendor) => (
                                            <TableRow key={vendor.vendor_name}>
                                                <TableCell className="font-medium">{vendor.vendor_name}</TableCell>
                                                <TableCell className="text-right tabular-nums">{vendor.total}</TableCell>
                                                <TableCell className="text-right tabular-nums">{vendor.active}</TableCell>
                                                <TableCell className="text-right tabular-nums">{vendor.expired}</TableCell>
                                                <TableCell className="text-right tabular-nums">{vendor.claims_open}</TableCell>
                                                <TableCell className="text-right tabular-nums">{vendor.claims_settled}</TableCell>
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
                        <CardTitle className="text-base">All warranties</CardTitle>
                        <CardDescription>{rows.length} records.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <WarrantyTable rows={rows} emptyMessage="No warranties recorded yet." />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
