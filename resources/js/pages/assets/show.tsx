import { Link } from '@inertiajs/react';
import { ArrowLeft, Pencil } from 'lucide-react';

import AppLayout from '@/layouts/app-layout';
import { AssignForm, InstallSoftwareForm, ReturnForm } from '@/components/asset-actions';
import ConfirmDelete from '@/components/confirm-delete';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

const STATUS_VARIANTS: Record<string, string> = {
    IN_USE: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
    IN_STORAGE: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    UNDER_REPAIR: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
    RETIRED: 'bg-zinc-200 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300',
    DISPOSED: 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300',
};

const humanise = (v: string | null | undefined) =>
    v ? v.replace(/_/g, ' ').toLowerCase().replace(/\b\w/g, (c) => c.toUpperCase()) : '-';

function Row({ label, children }: { label: string; children: React.ReactNode }) {
    return (
        <div className="flex gap-4 py-2 text-sm">
            <dt className="w-44 shrink-0 text-muted-foreground">{label}</dt>
            <dd className="flex-1">{children ?? '-'}</dd>
        </div>
    );
}

interface SpecEntry {
    label: string;
    value: string;
}

interface Props {
    asset: {
        id: number;
        asset_code: string;
        asset_name: string;
        brand: string | null;
        model: string | null;
        serial_number: string | null;
        description: string | null;
        status: string;
        condition: string;
        category: string | null;
        asset_type: string;
        location: string | null;
        added_date: string | null;
        vendor: string | null;
        invoice_no: string | null;
        purchase_date: string | null;
        purchase_cost: string | null;
        warranty_start: string | null;
        warranty_end: string | null;
        under_warranty: boolean;
        image_url: string | null;
        qr_url: string | null;
        parent: { id: number; asset_code: string; asset_name: string } | null;
    };
    specGroups: { heading: string; entries: SpecEntry[] }[];
    children: { id: number; asset_code: string; asset_name: string; category: string | null; status: string }[];
    assignments: {
        id: number;
        employee: string | null;
        department: string | null;
        assigned_at: string | null;
        returned_at: string | null;
        handler: string | null;
    }[];
    software: { id: number; name: string; installed_at: string | null; removed_at: string | null }[];
    employees: { id: number; label: string }[];
    availableLicenses: { id: number; label: string }[];
}

export default function AssetShow({
    asset,
    specGroups,
    children,
    assignments,
    software,
    employees,
    availableLicenses,
}: Props) {
    const current = assignments.find((a) => !a.returned_at);

    return (
        <AppLayout
            title={asset.asset_name}
            description={asset.asset_code}
            actions={
                <>
                    <Button variant="outline" asChild>
                        <Link href={`/inventory/${asset.id}/edit`}>
                            <Pencil /> Edit
                        </Link>
                    </Button>
                    <Button variant="outline" asChild>
                        <Link href="/inventory">
                            <ArrowLeft /> Back
                        </Link>
                    </Button>
                </>
            }
        >
            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div className="space-y-6 lg:col-span-2">
                    <Card>
                        <CardHeader className="flex-row items-center justify-between space-y-0">
                            <CardTitle>Overview</CardTitle>
                            <Badge variant="secondary" className={STATUS_VARIANTS[asset.status] ?? ''}>
                                {humanise(asset.status)}
                            </Badge>
                        </CardHeader>
                        <CardContent>
                            <dl className="divide-y">
                                <Row label="Asset Code">{asset.asset_code}</Row>
                                <Row label="Serial Number">{asset.serial_number ?? '-'}</Row>
                                <Row label="Brand">{asset.brand ?? '-'}</Row>
                                <Row label="Model">{asset.model ?? '-'}</Row>
                                <Row label="Category">
                                    {asset.category ?? '-'}{' '}
                                    <Badge variant="outline" className="ml-1">
                                        {humanise(asset.asset_type)}
                                    </Badge>
                                </Row>
                                <Row label="Location">{asset.location ?? '-'}</Row>
                                <Row label="Condition">{humanise(asset.condition)}</Row>
                                <Row label="Date Added">{asset.added_date ?? '-'}</Row>
                                {asset.parent && (
                                    <Row label="Attached To">
                                        <Link
                                            href={`/inventory/${asset.parent.id}`}
                                            className="text-primary underline-offset-4 hover:underline"
                                        >
                                            {asset.parent.asset_code} &ndash; {asset.parent.asset_name}
                                        </Link>
                                    </Row>
                                )}
                                {asset.description && <Row label="Description">{asset.description}</Row>}
                            </dl>
                        </CardContent>
                    </Card>

                    {specGroups.length > 0 && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Specification</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-5">
                                {specGroups.map((group, i) => (
                                    <div key={group.heading}>
                                        {i > 0 && <Separator className="mb-5" />}
                                        <h4 className="mb-2 text-sm font-medium text-muted-foreground">
                                            {group.heading}
                                        </h4>
                                        <dl className="grid grid-cols-1 gap-x-8 sm:grid-cols-2">
                                            {group.entries.map((entry) => (
                                                <div key={entry.label} className="flex gap-3 py-1.5 text-sm">
                                                    <dt className="w-40 shrink-0 text-muted-foreground">
                                                        {entry.label}
                                                    </dt>
                                                    <dd className="flex-1 break-words">{entry.value}</dd>
                                                </div>
                                            ))}
                                        </dl>
                                    </div>
                                ))}
                            </CardContent>
                        </Card>
                    )}

                    {children.length > 0 && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Attached Peripherals</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="rounded-md border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Code</TableHead>
                                                <TableHead>Name</TableHead>
                                                <TableHead>Category</TableHead>
                                                <TableHead>Status</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {children.map((child) => (
                                                <TableRow key={child.id}>
                                                    <TableCell>
                                                        <Link
                                                            href={`/inventory/${child.id}`}
                                                            className="text-primary underline-offset-4 hover:underline"
                                                        >
                                                            {child.asset_code}
                                                        </Link>
                                                    </TableCell>
                                                    <TableCell>{child.asset_name}</TableCell>
                                                    <TableCell>{child.category ?? '-'}</TableCell>
                                                    <TableCell>{humanise(child.status)}</TableCell>
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
                            <CardTitle>Installed Software</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Software</TableHead>
                                            <TableHead>Installed</TableHead>
                                            <TableHead>Removed</TableHead>
                                            <TableHead className="w-24 text-right">Action</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {software.length === 0 && (
                                            <TableRow>
                                                <TableCell colSpan={4} className="h-20 text-center text-muted-foreground">
                                                    No software recorded yet.
                                                </TableCell>
                                            </TableRow>
                                        )}
                                        {software.map((row) => (
                                            <TableRow key={row.id}>
                                                <TableCell>{row.name}</TableCell>
                                                <TableCell>{row.installed_at ?? '-'}</TableCell>
                                                <TableCell>
                                                    {row.removed_at ?? (
                                                        <Badge variant="secondary">active</Badge>
                                                    )}
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    {!row.removed_at && (
                                                        <ConfirmDelete
                                                            url={'/inventory/' + asset.id + '/software/' + row.id}
                                                            title="Remove this license?"
                                                            description="The seat is freed for another machine."
                                                        />
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>

                            <div className="mt-4">
                                <InstallSoftwareForm assetId={asset.id} licenses={availableLicenses} />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* ---------- sidebar ---------- */}
                <div className="space-y-6">
                    <Card>
                        <CardContent className="space-y-4 pt-6 text-center">
                            {asset.image_url ? (
                                <img
                                    src={asset.image_url}
                                    alt=""
                                    className="mx-auto max-h-48 rounded-md object-contain"
                                />
                            ) : (
                                <p className="text-sm text-muted-foreground">No image</p>
                            )}

                            {asset.qr_url && (
                                <div>
                                    <img src={asset.qr_url} alt="QR code" className="mx-auto w-36" />
                                    <p className="mt-1 text-xs text-muted-foreground">{asset.asset_code}</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Assignment</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {current ? (
                                <dl className="divide-y">
                                    <Row label="Holder">
                                        <div className="font-medium">{current.employee}</div>
                                        <div className="text-xs text-muted-foreground">
                                            {current.department ?? '-'}
                                        </div>
                                    </Row>
                                    <Row label="Since">{current.assigned_at ?? '-'}</Row>
                                    <Row label="Handed by">{current.handler ?? '-'}</Row>
                                </dl>
                            ) : (
                                <p className="text-sm text-muted-foreground">
                                    This asset is not assigned to anyone.
                                </p>
                            )}

                            <p className="mt-4 text-xs text-muted-foreground">
                                Handover is still recorded on the classic page.{' '}
                                <a
                                    href={`/inventory/${asset.id}`}
                                    className="text-primary underline-offset-4 hover:underline"
                                >
                                    Open it
                                </a>
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Handover History</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Employee</TableHead>
                                            <TableHead>From</TableHead>
                                            <TableHead>To</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {assignments.length === 0 && (
                                            <TableRow>
                                                <TableCell colSpan={3} className="h-20 text-center text-muted-foreground">
                                                    No handover recorded yet.
                                                </TableCell>
                                            </TableRow>
                                        )}
                                        {assignments.map((row) => (
                                            <TableRow key={row.id}>
                                                <TableCell>{row.employee ?? '-'}</TableCell>
                                                <TableCell>{row.assigned_at ?? '-'}</TableCell>
                                                <TableCell>
                                                    {row.returned_at ?? (
                                                        <Badge variant="secondary">current</Badge>
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Purchase &amp; Warranty</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <dl className="divide-y">
                                <Row label="Vendor">{asset.vendor ?? '-'}</Row>
                                <Row label="Invoice">{asset.invoice_no ?? '-'}</Row>
                                <Row label="Purchased">{asset.purchase_date ?? '-'}</Row>
                                <Row label="Cost">{asset.purchase_cost ?? '-'}</Row>
                                <Row label="Warranty">
                                    {asset.warranty_end ? (
                                        <span className="flex flex-wrap items-center gap-2">
                                            {asset.warranty_start ?? '?'} &rarr; {asset.warranty_end}
                                            <Badge variant={asset.under_warranty ? 'secondary' : 'destructive'}>
                                                {asset.under_warranty ? 'active' : 'expired'}
                                            </Badge>
                                        </span>
                                    ) : (
                                        '-'
                                    )}
                                </Row>
                            </dl>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
