import { Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Check, CheckCircle2, ClipboardCheck, ListChecks, Save } from 'lucide-react';
import { type FormEvent } from 'react';

import AppLayout from '@/layouts/app-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

interface AuditInfo {
    id: number;
    audit_name: string;
    audit_period: string;
    status: string;
    started_at: string | null;
    completed_at: string | null;
    progress: number;
    total_assignments: number;
    verified_count: number;
    missing_count: number;
    damaged_count: number;
    notes: string | null;
}

interface AssignmentRow {
    id: number;
    asset_id: number;
    asset_code: string;
    asset_name: string;
    category: string | null;
    employee_id: number;
    employee_name: string;
    employee_code: string;
    department: string | null;
    assigned_at: string;
    location: string | null;
}

interface VerificationRow {
    id: number;
    assignment_id: number;
    asset_id: number;
    employee_id: number;
    status: string;
    condition: string | null;
    verified_at: string | null;
    remarks: string | null;
}

interface Props {
    audit: AuditInfo;
    active_assignments: AssignmentRow[];
    verifications: Record<string, VerificationRow>;
}

const statusOptions = [
    { value: 'confirmed', label: 'Confirmed' },
    { value: 'missing', label: 'Missing' },
    { value: 'lost', label: 'Lost' },
    { value: 'damaged', label: 'Damaged' },
    { value: 'returned', label: 'Returned' },
    { value: 'transferred', label: 'Transferred' },
];

export default function AssignmentAuditShow({ audit, active_assignments, verifications }: Props) {
    const start = () => router.post(`/assignment-audits/${audit.id}/start`);
    const complete = () => router.post(`/assignment-audits/${audit.id}/complete`);
    const defaultAssignmentId = String(active_assignments[0]?.id ?? '');

    const { data, setData, post, processing, errors, reset } = useForm({
        assignment_id: defaultAssignmentId,
        verification_status: 'confirmed',
        condition_observed: 'good',
        remarks: '',
    });

    const submitVerification = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        post(`/assignment-audits/${audit.id}/verify`);
    };

    const selectedAssignment = active_assignments.find((item) => String(item.id) === data.assignment_id) ?? null;

    return (
        <AppLayout
            title={audit.audit_name}
            description={`${audit.audit_period} · ${audit.status}`}
            actions={
                <>
                    <Button variant="outline" asChild>
                        <Link href="/assignment-audits">
                            <ArrowLeft /> Back
                        </Link>
                    </Button>
                    {audit.status === 'pending' && (
                        <Button onClick={start}>
                            <CheckCircle2 /> Start audit
                        </Button>
                    )}
                    {audit.status === 'in_progress' && (
                        <Button onClick={complete}>
                            <CheckCircle2 /> Complete audit
                        </Button>
                    )}
                    <Button variant="outline" asChild>
                        <Link href={`/assignment-audits/${audit.id}/report`}>Report</Link>
                    </Button>
                </>
            }
        >
            <div className="space-y-6">
                <div className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                    <div className="flex items-center gap-2 font-medium">
                        <span className="flex size-5 items-center justify-center rounded-full bg-emerald-500 text-white">
                            <Check className="size-3" />
                        </span>
                        Verification recorded
                    </div>
                </div>

                <div className="grid gap-4 sm:grid-cols-4">
                    <div className="rounded-xl border bg-card p-4 shadow-sm">
                        <div className="text-3xl font-semibold tracking-tight text-foreground">{audit.total_assignments}</div>
                        <div className="mt-1 text-sm text-muted-foreground">Total</div>
                    </div>
                    <div className="rounded-xl border bg-card p-4 shadow-sm">
                        <div className="text-3xl font-semibold tracking-tight text-emerald-600">{audit.verified_count}</div>
                        <div className="mt-1 text-sm text-muted-foreground">Verified</div>
                    </div>
                    <div className="rounded-xl border bg-card p-4 shadow-sm">
                        <div className="text-3xl font-semibold tracking-tight text-amber-600">{audit.missing_count}</div>
                        <div className="mt-1 text-sm text-muted-foreground">Missing</div>
                    </div>
                    <div className="rounded-xl border bg-card p-4 shadow-sm">
                        <div className="text-3xl font-semibold tracking-tight text-red-600">{audit.damaged_count}</div>
                        <div className="mt-1 text-sm text-muted-foreground">Damaged</div>
                    </div>
                </div>

                {audit.status === 'in_progress' && (
                    <div className="rounded-xl border bg-card p-6 shadow-sm">
                        <div className="mb-5 flex items-center gap-3">
                            <span className="flex size-6 items-center justify-center rounded-md bg-primary/10 text-primary">
                                <ClipboardCheck className="size-4" />
                            </span>
                            <h2 className="text-2xl font-semibold tracking-tight text-foreground">Record verification</h2>
                        </div>

                        <form onSubmit={submitVerification} className="space-y-5">
                            <div className="grid gap-5 md:grid-cols-3">
                                <div className="space-y-2">
                                    <label className="block text-sm font-medium text-foreground">Assignment</label>
                                    <select
                                        value={data.assignment_id}
                                        onChange={(event) => setData('assignment_id', event.target.value)}
                                        className="h-12 w-full rounded-md border border-input bg-background px-3 text-base outline-none ring-0 transition focus:border-primary"
                                    >
                                        <option value="">Select assignment</option>
                                        {active_assignments.map((assignment) => (
                                            <option key={assignment.id} value={assignment.id}>
                                                {assignment.asset_code} · {assignment.employee_name}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.assignment_id && <p className="text-xs text-destructive">{errors.assignment_id}</p>}
                                </div>

                                <div className="space-y-2">
                                    <label className="block text-sm font-medium text-foreground">Verification</label>
                                    <select
                                        value={data.verification_status}
                                        onChange={(event) => setData('verification_status', event.target.value)}
                                        className="h-12 w-full rounded-md border border-input bg-background px-3 text-base outline-none transition focus:border-primary"
                                    >
                                        {statusOptions.map((option) => (
                                            <option key={option.value} value={option.value}>{option.label}</option>
                                        ))}
                                    </select>
                                </div>

                                <div className="space-y-2">
                                    <label className="block text-sm font-medium text-foreground">Condition observed</label>
                                    <select
                                        value={data.condition_observed}
                                        onChange={(event) => setData('condition_observed', event.target.value)}
                                        className="h-12 w-full rounded-md border border-input bg-background px-3 text-base outline-none transition focus:border-primary"
                                    >
                                        <option value="">Not recorded</option>
                                        <option value="good">Good</option>
                                        <option value="fair">Fair</option>
                                        <option value="poor">Poor</option>
                                        <option value="damaged">Damaged</option>
                                    </select>
                                </div>
                            </div>

                            <div className="space-y-2">
                                <label className="block text-sm font-medium text-foreground">Remarks</label>
                                <textarea
                                    value={data.remarks}
                                    onChange={(event) => setData('remarks', event.target.value)}
                                    rows={4}
                                    className="w-full rounded-md border border-input bg-background px-3 py-3 text-base outline-none transition focus:border-primary"
                                    placeholder=""
                                />
                            </div>

                            {selectedAssignment && (
                                <div className="rounded-md border border-border bg-muted/30 px-4 py-3 text-sm text-muted-foreground">
                                    Selected: {selectedAssignment.asset_code} · {selectedAssignment.employee_name} · {selectedAssignment.department ?? 'No department'}
                                </div>
                            )}

                            <div className="flex items-center gap-3 pt-2">
                                <Button type="submit" disabled={processing || !data.assignment_id}>
                                    <Save className="size-4" /> Save verification
                                </Button>
                                <Button type="button" variant="outline" onClick={() => reset()}>
                                    Reset
                                </Button>
                            </div>
                        </form>
                    </div>
                )}

                <div className="space-y-3 pt-2">
                    <h2 className="text-3xl font-bold tracking-tight text-foreground">Assignments under audit</h2>
                    <div className="overflow-x-auto rounded-xl border bg-card shadow-sm">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Asset</TableHead>
                                    <TableHead>Employee</TableHead>
                                    <TableHead>Department</TableHead>
                                    <TableHead>Location</TableHead>
                                    <TableHead>Status</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {active_assignments.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={5} className="h-24 text-center text-muted-foreground">
                                            No active assignments are available for this audit.
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    active_assignments.map((assignment) => {
                                        const verification = verifications[String(assignment.id)] ?? null;
                                        return (
                                            <TableRow key={assignment.id}>
                                                <TableCell>
                                                    <div className="font-medium text-foreground">{assignment.asset_code}</div>
                                                    <div className="text-xs text-muted-foreground">{assignment.asset_name}</div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="font-medium text-foreground">{assignment.employee_name}</div>
                                                    <div className="text-xs text-muted-foreground">{assignment.employee_code}</div>
                                                </TableCell>
                                                <TableCell>{assignment.department ?? '-'}</TableCell>
                                                <TableCell>{assignment.location ?? '-'}</TableCell>
                                                <TableCell>
                                                    {verification ? (
                                                        <Badge variant={verification.status === 'confirmed' ? 'default' : 'secondary'}>
                                                            {verification.status}
                                                        </Badge>
                                                    ) : (
                                                        <Badge variant="outline">Pending</Badge>
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        );
                                    })
                                )}
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
