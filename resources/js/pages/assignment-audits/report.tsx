import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

import AppLayout from '@/layouts/app-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

interface IssueRow {
    id: number;
    asset_code: string;
    asset_name: string;
    category: string | null;
    employee_name: string;
    employee_code: string;
    department: string | null;
    status: string;
    condition: string | null;
    remarks: string | null;
    verified_by: string | null;
}

interface AuditInfo {
    id: number;
    audit_name: string;
    audit_period: string;
    status: string;
    started_at: string | null;
    completed_at: string | null;
    total: number;
    verified: number;
    missing: number;
    damaged: number;
    notes: string | null;
}

interface Props {
    audit: AuditInfo;
    issues: IssueRow[];
}

export default function AssignmentAuditReport({ audit, issues }: Props) {
    return (
        <AppLayout
            title={`${audit.audit_name} report`}
            description="Audit exceptions and follow-up actions."
            actions={
                <Button variant="outline" asChild>
                    <Link href={`/assignment-audits/${audit.id}`}>
                        <ArrowLeft /> Back
                    </Link>
                </Button>
            }
        >
            <div className="space-y-6">
                <Card>
                    <CardContent className="space-y-3 p-6">
                        <div className="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p className="text-lg font-semibold">{audit.audit_name}</p>
                                <p className="text-sm text-muted-foreground">{audit.audit_period}</p>
                            </div>
                            <Badge variant="secondary">{audit.status}</Badge>
                        </div>
                        <div className="grid gap-3 sm:grid-cols-3 text-sm">
                            <div>Verified: <strong>{audit.verified}</strong></div>
                            <div>Missing: <strong>{audit.missing}</strong></div>
                            <div>Damaged: <strong>{audit.damaged}</strong></div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="pt-6">
                        <div className="overflow-x-auto rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Asset</TableHead>
                                        <TableHead>Employee</TableHead>
                                        <TableHead>Department</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Condition</TableHead>
                                        <TableHead>Remark</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {issues.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={6} className="h-24 text-center text-muted-foreground">
                                                No exceptions found in this audit.
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        issues.map((issue) => (
                                            <TableRow key={issue.id}>
                                                <TableCell>
                                                    <p className="font-medium">{issue.asset_code}</p>
                                                    <p className="text-xs text-muted-foreground">{issue.asset_name}</p>
                                                </TableCell>
                                                <TableCell>
                                                    <p>{issue.employee_name}</p>
                                                    <p className="text-xs text-muted-foreground">{issue.employee_code}</p>
                                                </TableCell>
                                                <TableCell>{issue.department ?? '-'}</TableCell>
                                                <TableCell>
                                                    <Badge variant="secondary">{issue.status}</Badge>
                                                </TableCell>
                                                <TableCell>{issue.condition ?? '-'}</TableCell>
                                                <TableCell>{issue.remarks ?? '-'}</TableCell>
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
