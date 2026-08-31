import { Link } from '@inertiajs/react';
import { ArrowLeft, Eye, FileWarning } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

interface ReportRow {
    id: number;
    title: string;
    asset: string;
    status: string;
    created: string | null;
}

interface Props {
    title: string;
    description: string;
    rows: ReportRow[];
}

export default function MyReports({ title, description, rows }: Props) {
    return (
        <AppLayout title={title} description={description} actions={<Button variant="outline" asChild><Link href="/inventory"><ArrowLeft /> Assets</Link></Button>}>
            <Card>
                <CardHeader><CardTitle className="flex items-center gap-2"><FileWarning className="size-5 text-primary" /> My issue reports</CardTitle></CardHeader>
                <CardContent>
                    <div className="overflow-x-auto rounded-md border">
                        <Table>
                            <TableHeader><TableRow><TableHead>Report</TableHead><TableHead>Asset</TableHead><TableHead>Status</TableHead><TableHead>Created</TableHead><TableHead className="text-right">Action</TableHead></TableRow></TableHeader>
                            <TableBody>
                                {rows.length === 0 && <TableRow><TableCell colSpan={5} className="h-24 text-center text-muted-foreground">No issue reports yet.</TableCell></TableRow>}
                                {rows.map((row) => <TableRow key={row.id}><TableCell className="font-medium">{row.title}</TableCell><TableCell>{row.asset}</TableCell><TableCell><Badge variant="secondary">{row.status}</Badge></TableCell><TableCell>{row.created ?? '-'}</TableCell><TableCell className="text-right"><Button variant="ghost" size="icon" asChild title="View report"><Link href={`/my-reports/${row.id}`}><Eye className="size-4" /></Link></Button></TableCell></TableRow>)}
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </AppLayout>
    );
}