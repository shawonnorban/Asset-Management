import AppLayout from '@/layouts/app-layout';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, Check, History, X } from 'lucide-react';

import AlertBanner from '@/components/alert-banner';
import StatusBadge from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';

interface Transfer {
    id: number;
    asset: { id: number; code: string; name: string } | null;
    from_location: { name: string } | null;
    to_location: { name: string } | null;
    from_employee: { name: string } | null;
    to_employee: { name: string } | null;
    status: string;
    status_label: string;
    reason: string | null;
    notes: string | null;
    requested_by: string | null;
    approved_by: string | null;
    requested_at: string | null;
    transferred_at: string | null;
}

export default function TransferShow({ title, transfer, canManage }: { title: string; transfer: Transfer; canManage: boolean }) {
    const { data, setData, post, processing, errors } = useForm({ notes: transfer.notes ?? '' });
    const isPending = transfer.status === 'REQUESTED';

    const submit = (action: 'approve' | 'reject') => {
        post(`/transfers/${transfer.id}/${action}`, { preserveScroll: true });
    };

    return (
        <AppLayout
            title={title}
            description="Transfer request details"
            actions={
                <div className="flex flex-wrap gap-2">
                    <Button variant="outline" asChild>
                        <Link href="/transfers">
                            <ArrowLeft className="size-4" /> Back
                        </Link>
                    </Button>
                    {transfer.asset && (
                        <Button variant="outline" asChild>
                            <Link href={`/inventory/${transfer.asset.id}/lifecycle`}>
                                <History className="size-4" /> Asset history
                            </Link>
                        </Button>
                    )}
                </div>
            }
        >
            <div className="mx-auto max-w-3xl space-y-6">
                {isPending && (
                    <AlertBanner tone="info" title="This transfer is waiting for a decision">
                        The asset record still shows its current location until the transfer is approved.
                    </AlertBanner>
                )}

                <Card>
                    <CardHeader className="flex flex-row items-start justify-between gap-4 space-y-0">
                        <div>
                            <CardTitle>Transfer #{transfer.id}</CardTitle>
                            <CardDescription>Requested {transfer.requested_at ?? '-'}</CardDescription>
                        </div>
                        <StatusBadge status={transfer.status} label={transfer.status_label} />
                    </CardHeader>
                    <CardContent className="space-y-6">
                        <div>
                            <p className="text-sm text-muted-foreground">Asset</p>
                            <p className="font-medium">
                                {transfer.asset?.code ?? '-'} · {transfer.asset?.name ?? '-'}
                            </p>
                        </div>

                        <div className="rounded-lg border bg-muted/30 p-4">
                            <div className="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center">
                                <div className="flex-1">
                                    <p className="text-xs uppercase tracking-wide text-muted-foreground">From</p>
                                    <p className="mt-1 font-medium">{transfer.from_location?.name ?? 'Unrecorded location'}</p>
                                    <p className="text-sm text-muted-foreground">{transfer.from_employee?.name ?? 'Unassigned'}</p>
                                </div>
                                <ArrowRight className="mx-auto size-5 shrink-0 rotate-90 text-muted-foreground sm:rotate-0" />
                                <div className="flex-1">
                                    <p className="text-xs uppercase tracking-wide text-muted-foreground">To</p>
                                    <p className="mt-1 font-medium">{transfer.to_location?.name ?? 'Unrecorded location'}</p>
                                    <p className="text-sm text-muted-foreground">{transfer.to_employee?.name ?? 'Unassigned'}</p>
                                </div>
                            </div>
                        </div>

                        <dl className="grid gap-4 md:grid-cols-2">
                            <div>
                                <dt className="text-sm text-muted-foreground">Requested by</dt>
                                <dd className="mt-0.5 font-medium">{transfer.requested_by ?? '-'}</dd>
                            </div>
                            <div>
                                <dt className="text-sm text-muted-foreground">Approved by</dt>
                                <dd className="mt-0.5 font-medium">{transfer.approved_by ?? 'Not yet approved'}</dd>
                            </div>
                            <div>
                                <dt className="text-sm text-muted-foreground">Settled on</dt>
                                <dd className="mt-0.5 font-medium">{transfer.transferred_at ?? '-'}</dd>
                            </div>
                            <div className="md:col-span-2">
                                <dt className="text-sm text-muted-foreground">Reason</dt>
                                <dd className="mt-0.5 font-medium">{transfer.reason || 'Not provided'}</dd>
                            </div>
                            <div className="md:col-span-2">
                                <dt className="text-sm text-muted-foreground">Notes</dt>
                                <dd className="mt-0.5 font-medium">{transfer.notes || 'No notes'}</dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                {canManage && isPending && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Approval decision</CardTitle>
                            <CardDescription>Approving moves the asset to the destination and records you as the approver.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <Label htmlFor="notes">Decision notes</Label>
                                <textarea
                                    id="notes"
                                    value={data.notes}
                                    onChange={(event) => setData('notes', event.target.value)}
                                    rows={3}
                                    placeholder="Why this transfer was approved or rejected"
                                    className="mt-1.5 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                />
                                {errors.notes && <p className="mt-1 text-sm text-destructive">{errors.notes}</p>}
                            </div>

                            <div className="flex flex-wrap gap-2">
                                <Button type="button" onClick={() => submit('approve')} disabled={processing}>
                                    <Check className="size-4" /> Approve transfer
                                </Button>
                                <Button type="button" variant="outline" onClick={() => submit('reject')} disabled={processing}>
                                    <X className="size-4" /> Reject
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
