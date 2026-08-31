import { Link } from '@inertiajs/react';
import { ArrowRight, Boxes, ClipboardCheck, Plus } from 'lucide-react';

import AppLayout from '@/layouts/app-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

interface Assignment { id: number; asset_id: number; asset_code: string | null; asset_name: string | null; category: string | null; employee_id: number | null; employee: string | null; employee_code: string | null; department: string | null; location: string | null; assigned_at: string | null; condition: string | null; handler: string | null; }
interface Props { assignments: Assignment[]; }
const text = (value: string | null) => value || '-';
const humanise = (value: string | null) => value ? value.replace(/_/g, ' ').toLowerCase().replace(/\b\w/g, (c) => c.toUpperCase()) : '-';

export default function AssignmentsIndex({ assignments }: Props) {
    return <AppLayout title="Asset Assignments" description={`${assignments.length} open handovers`} actions={<><Button variant="outline" asChild><Link href="/inventory"><Boxes /> All Assets</Link></Button><Button asChild><Link href="/assignments/create"><Plus /> New assignment</Link></Button></>}>
        <Card><CardContent className="pt-6"><div className="mb-5 flex items-center gap-3 rounded-md border bg-muted/30 p-4"><ClipboardCheck className="size-5 text-primary" /><div><p className="font-medium">Open handovers</p><p className="text-sm text-muted-foreground">Assets currently assigned to employees.</p></div><Badge className="ml-auto">{assignments.length} assigned</Badge></div><div className="overflow-x-auto rounded-md border"><Table><TableHeader><TableRow><TableHead>Asset</TableHead><TableHead>Category</TableHead><TableHead>Employee</TableHead><TableHead>Department</TableHead><TableHead>Location</TableHead><TableHead>Assigned On</TableHead><TableHead>Condition</TableHead><TableHead>Handed By</TableHead><TableHead className="text-right">Details</TableHead></TableRow></TableHeader><TableBody>{assignments.length === 0 ? <TableRow><TableCell colSpan={9} className="h-24 text-center text-muted-foreground">No asset is currently assigned.</TableCell></TableRow> : assignments.map((row) => <TableRow key={row.id}><TableCell><Link className="font-medium hover:underline" href={`/inventory/${row.asset_id}`}>{text(row.asset_code)}</Link><br /><span className="text-xs text-muted-foreground">{text(row.asset_name)}</span></TableCell><TableCell>{text(row.category)}</TableCell><TableCell>{row.employee_id ? <Link className="font-medium hover:underline" href={`/employees/${row.employee_id}`}>{text(row.employee)}</Link> : text(row.employee)}<br /><span className="text-xs text-muted-foreground">{text(row.employee_code)}</span></TableCell><TableCell>{text(row.department)}</TableCell><TableCell>{text(row.location)}</TableCell><TableCell>{text(row.assigned_at)}</TableCell><TableCell><Badge variant="outline">{humanise(row.condition)}</Badge></TableCell><TableCell>{text(row.handler)}</TableCell><TableCell className="text-right"><Button variant="ghost" size="icon" asChild title="View asset"><Link href={`/inventory/${row.asset_id}`}><ArrowRight className="size-4" /></Link></Button></TableCell></TableRow>)}</TableBody></Table></div></CardContent></Card>
    </AppLayout>;
}
