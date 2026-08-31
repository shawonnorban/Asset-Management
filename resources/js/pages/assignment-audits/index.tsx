import { Link } from '@inertiajs/react';
import { CheckCircle2, ClipboardCheck, Eye, Plus } from 'lucide-react';

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

interface AuditRow {
    id: number;
    audit_name: string;
    audit_period: string;
    status: string;
    started_at: string | null;
    completed_at: string | null;
    progress: number;
    total: number;
    verified: number;
    missing: number;
    damaged: number;
}

interface Props {
    audits: { data: AuditRow[] };
    canManage?: boolean;
}

const statusBadge = (status: string) => {
    const map: Record<string, 'default' | 'secondary' | 'outline'> = {
        pending: 'secondary',
        in_progress: 'default',
        completed: 'outline',
    };

    return map[status] ?? 'secondary';
};

export default function AssignmentAuditsIndex({ audits, canManage = true }: Props) {
    const rows = audits?.data ?? [];

    return (
        <AppLayout
            title="Assignment Audits"
            description="Verify which employee currently holds each assigned asset."
            actions={
                canManage ? (
                    <Button asChild>
                        <Link href="/assignment-audits/create">
                            <Plus /> New audit
                        </Link>
                    </Button>
                ) : undefined
            }
        >
            <div className="space-y-6">
                <div className="grid gap-4 sm:grid-cols-3">
                    <Card>
                        <CardContent className="flex items-center gap-4 p-5">
                            <ClipboardCheck className="size-5 text-primary" />
                            <div>
                                <p className="text-2xl font-semibold">{rows.length}</p>
                                <p className="text-sm text-muted-foreground">Audit cycles</p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex items-center gap-4 p-5">
                            <CheckCircle2 className="size-5 text-emerald-600" />
                            <div>
                                <p className="text-2xl font-semibold">{rows.filter((row) => row.status === 'completed').length}</p>
                                <p className="text-sm text-muted-foreground">Completed</p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex items-center gap-4 p-5">
                            <Eye className="size-5 text-amber-600" />
                            <div>
                                <p className="text-2xl font-semibold">{rows.filter((row) => row.status !== 'completed').length}</p>
                                <p className="text-sm text-muted-foreground">Open</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Assignment audit history</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-3">
                        <div className="overflow-x-auto rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Audit</TableHead>
                                        <TableHead>Period</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Progress</TableHead>
                                        <TableHead>Verified</TableHead>
                                        <TableHead>Issues</TableHead>
                                        <TableHead className="text-right">Action</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {rows.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={7} className="h-24 text-center text-muted-foreground">
                                                No audit records created yet.
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        rows.map((row) => (
                                            <TableRow key={row.id}>
                                                <TableCell>
                                                    <p className="font-medium">{row.audit_name}</p>
                                                    <p className="text-xs text-muted-foreground">{row.started_at ?? 'Not started yet'}</p>
                                                </TableCell>
                                                <TableCell>{row.audit_period}</TableCell>
                                                <TableCell>
                                                    <Badge variant={statusBadge(row.status)}>{row.status.replace('_', ' ')}</Badge>
                                                </TableCell>
                                                <TableCell>{row.progress}%</TableCell>
                                                <TableCell>{row.verified}/{row.total}</TableCell>
                                                <TableCell>
                                                    {row.missing + row.damaged}
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <Button variant="ghost" size="sm" asChild>
                                                        <Link href={`/assignment-audits/${row.id}`}>
                                                            Open
                                                        </Link>
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
