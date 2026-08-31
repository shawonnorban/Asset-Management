import AppLayout from '@/layouts/app-layout';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Check, History, X } from 'lucide-react';

import AlertBanner from '@/components/alert-banner';
import StatusBadge from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Disposal {
    id: number;
    asset: { id: number; code: string; name: string } | null;
    status: string;
    status_label: string;
    reason: string | null;
    method: string | null;
    value_recovered: number | string | null;
    notes: string | null;
    requested_by: string | null;
    approved_by: string | null;
    requested_at: string | null;
    disposed_at: string | null;
}

export default function DisposalShow({ title, disposal, canManage }: { title: string; disposal: Disposal; canManage: boolean }) {
    const { data, setData, post, processing, errors } = useForm({
        notes: disposal.notes ?? '',
        value_recovered: String(disposal.value_recovered ?? ''),
    });
    const isPending = disposal.status === 'REQUESTED';

    const submit = (action: 'approve' | 'reject') => {
        post(`/disposals/${disposal.id}/${action}`, { preserveScroll: true });
    };

    return (
        <AppLayout
            title={title}
            description="Disposal request details"
            actions={
                <div className="flex flex-wrap gap-2">
                    <Button variant="outline" asChild>
                        <Link href="/disposals">
                            <ArrowLeft className="size-4" /> Back
                        </Link>
                    </Button>
                    {disposal.asset && (
                        <Button variant="outline" asChild>
                            <Link href={`/inventory/${disposal.asset.id}/lifecycle`}>
                                <History className="size-4" /> Asset history
                            </Link>
                        </Button>
                    )}
                </div>
            }
        >
            <div className="mx-auto max-w-3xl space-y-6">
                {isPending && (
                    <AlertBanner tone="warning" title="This disposal is waiting for a decision">
                        Approving retires the asset permanently and clears its assignment.
                    </AlertBanner>
                )}

                <Card>
                    <CardHeader className="flex flex-row items-start justify-between gap-4 space-y-0">
                        <div>
                            <CardTitle>Disposal #{disposal.id}</CardTitle>
                            <CardDescription>Requested {disposal.requested_at ?? '-'}</CardDescription>
                        </div>
                        <StatusBadge status={disposal.status} label={disposal.status_label} />
                    </CardHeader>
                    <CardContent>
                        <dl className="grid gap-4 md:grid-cols-2">
                            <div className="md:col-span-2">
                                <dt className="text-sm text-muted-foreground">Asset</dt>
                                <dd className="mt-0.5 font-medium">
                                    {disposal.asset?.code ?? '-'} · {disposal.asset?.name ?? '-'}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm text-muted-foreground">Method</dt>
                                <dd className="mt-0.5 font-medium">{disposal.method || 'Not provided'}</dd>
                            </div>
                            <div>
                                <dt className="text-sm text-muted-foreground">Value recovered</dt>
                                <dd className="mt-0.5 font-medium tabular-nums">{disposal.value_recovered ?? 0}</dd>
                            </div>
                            <div>
                                <dt className="text-sm text-muted-foreground">Requested by</dt>
                                <dd className="mt-0.5 font-medium">{disposal.requested_by ?? '-'}</dd>
                            </div>
                            <div>
                                <dt className="text-sm text-muted-foreground">Approved by</dt>
                                <dd className="mt-0.5 font-medium">{disposal.approved_by ?? 'Not yet approved'}</dd>
                            </div>
                            <div>
                                <dt className="text-sm text-muted-foreground">Disposed on</dt>
                                <dd className="mt-0.5 font-medium">{disposal.disposed_at ?? '-'}</dd>
                            </div>
                            <div className="md:col-span-2">
                                <dt className="text-sm text-muted-foreground">Reason</dt>
                                <dd className="mt-0.5 font-medium">{disposal.reason || 'Not provided'}</dd>
                            </div>
                            <div className="md:col-span-2">
                                <dt className="text-sm text-muted-foreground">Notes</dt>
                                <dd className="mt-0.5 font-medium">{disposal.notes || 'No notes'}</dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                {canManage && isPending && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Approval decision</CardTitle>
                            <CardDescription>Approving marks the asset disposed and records you as the approver.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <Label htmlFor="value_recovered">Value recovered</Label>
                                <Input
                                    id="value_recovered"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    value={data.value_recovered}
                                    onChange={(event) => setData('value_recovered', event.target.value)}
                                    className="mt-1.5"
                                />
                                {errors.value_recovered && <p className="mt-1 text-sm text-destructive">{errors.value_recovered}</p>}
                            </div>

                            <div>
                                <Label htmlFor="notes">Decision notes</Label>
                                <textarea
                                    id="notes"
                                    value={data.notes}
                                    onChange={(event) => setData('notes', event.target.value)}
                                    rows={3}
                                    placeholder="Why this disposal was approved or rejected"
                                    className="mt-1.5 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                />
                                {errors.notes && <p className="mt-1 text-sm text-destructive">{errors.notes}</p>}
                            </div>

                            <div className="flex flex-wrap gap-2">
                                <Button type="button" onClick={() => submit('approve')} disabled={processing}>
                                    <Check className="size-4" /> Approve disposal
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
