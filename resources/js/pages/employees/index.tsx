import { FormEvent, useState } from 'react';
import { Link, router } from '@inertiajs/react';
import { Building2, Eye, Filter, Pencil, Plus, Trash2, UserRound } from 'lucide-react';

import AppLayout from '@/layouts/app-layout';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import Pagination from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

interface EmployeeRow {
    id: number;
    employee_code: string;
    name: string;
    image_url: string | null;
    department: string | null;
    position: string | null;
    location: string | null;
    mobile: string | null;
    mail_address: string | null;
    join_date: string | null;
    user_email: string | null;
}

interface Option { id: number; name?: string; location_name?: string; }

interface Props {
    employees: EmployeeRow[];
    departments: Option[];
    locations: Option[];
    filters: { department_id: string | null; location_id: string | null };
    canManage: boolean;
    pagination: { links: { url: string | null; label: string; active: boolean }[]; from?: number; to?: number; total: number };
}

const ALL = '__all__';
const valueOrDash = (value: string | null) => value || '-';

export default function EmployeesIndex({ employees, departments, locations, filters, canManage, pagination }: Props) {
    const [department, setDepartment] = useState(filters.department_id ?? ALL);
    const [location, setLocation] = useState(filters.location_id ?? ALL);

    const apply = (event?: FormEvent) => {
        event?.preventDefault();
        const params = Object.fromEntries([
            ['department_id', department === ALL ? null : department],
            ['location_id', location === ALL ? null : location],
        ].filter(([, value]) => value));
        router.get('/employees', params, { preserveState: true, replace: true });
    };

    return (
        <AppLayout
            title="Employees"
            description={`${employees.length} employees on record`}
            actions={canManage ? <Button asChild><Link href="/employees/create"><Plus /> Add Employee</Link></Button> : undefined}
        >
            <div className="mb-6 flex flex-wrap gap-3">
                {canManage && <Button variant="outline" asChild><a href="/departments"><Building2 /> Departments</a></Button>}
                {canManage && <Button variant="outline" asChild><a href="/positions"><UserRound /> Positions</a></Button>}
            </div>

            <Card>
                <CardContent className="pt-6">
                    <form onSubmit={apply} className="mb-5 flex flex-wrap items-end gap-3">
                        <div className="min-w-[220px] flex-1 space-y-2">
                            <label className="text-sm font-medium">Department</label>
                            <Select value={department} onValueChange={setDepartment}>
                                <SelectTrigger><SelectValue placeholder="All departments" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={ALL}>All departments</SelectItem>
                                    {departments.map((item) => <SelectItem key={item.id} value={String(item.id)}>{item.name}</SelectItem>)}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="min-w-[220px] flex-1 space-y-2">
                            <label className="text-sm font-medium">Location</label>
                            <Select value={location} onValueChange={setLocation}>
                                <SelectTrigger><SelectValue placeholder="All locations" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={ALL}>All locations</SelectItem>
                                    {locations.map((item) => <SelectItem key={item.id} value={String(item.id)}>{item.location_name}</SelectItem>)}
                                </SelectContent>
                            </Select>
                        </div>
                        <Button type="submit"><Filter /> Filter</Button>
                        <Button type="button" variant="outline" onClick={() => { setDepartment(ALL); setLocation(ALL); router.get('/employees'); }}>Reset</Button>
                    </form>

                    <div className="overflow-x-auto rounded-md border">
                        <Table>
                            <TableHeader><TableRow><TableHead>Photo</TableHead><TableHead>Code</TableHead><TableHead>Name</TableHead><TableHead>Department</TableHead><TableHead>Position</TableHead><TableHead>Location</TableHead><TableHead>Login account</TableHead><TableHead>Mobile</TableHead><TableHead>Mail</TableHead><TableHead>Join Date</TableHead><TableHead className="text-right">Options</TableHead></TableRow></TableHeader>
                            <TableBody>
                                {employees.length === 0 && <TableRow><TableCell colSpan={11} className="h-24 text-center text-muted-foreground">No employees match these filters.</TableCell></TableRow>}
                                {employees.map((employee) => <TableRow key={employee.id}>
                                    <TableCell>{employee.image_url ? <img src={employee.image_url} alt={employee.name} className="size-10 rounded-full object-cover" /> : <div className="flex size-10 items-center justify-center rounded-full bg-muted text-muted-foreground"><UserRound className="size-4" /></div>}</TableCell>
                                    <TableCell className="font-medium">{employee.employee_code}</TableCell>
                                    <TableCell>{employee.name}</TableCell>
                                    <TableCell>{valueOrDash(employee.department)}</TableCell>
                                    <TableCell>{valueOrDash(employee.position)}</TableCell>
                                    <TableCell>{valueOrDash(employee.location)}</TableCell>
                                    <TableCell>{employee.user_email ? <Badge variant="secondary">Enabled</Badge> : <span className="text-muted-foreground">Not linked</span>}</TableCell><TableCell>{valueOrDash(employee.mobile)}</TableCell>
                                    <TableCell>{valueOrDash(employee.mail_address)}</TableCell>
                                    <TableCell>{valueOrDash(employee.join_date)}</TableCell>
                                    <TableCell><div className="flex justify-end gap-1"><Button variant="ghost" size="icon" asChild title="Detail"><Link href={`/employees/${employee.id}`}><Eye className="size-4" /></Link></Button>{canManage && <><Button variant="ghost" size="icon" asChild title="Edit"><Link href={`/employees/${employee.id}/edit`}><Pencil className="size-4" /></Link></Button><AlertDialog><AlertDialogTrigger asChild><Button variant="ghost" size="icon" title="Delete" className="text-destructive hover:text-destructive"><Trash2 className="size-4" /></Button></AlertDialogTrigger><AlertDialogContent><AlertDialogHeader><AlertDialogTitle>Delete {employee.name}?</AlertDialogTitle><AlertDialogDescription>The employee record will be removed. Return assigned assets first.</AlertDialogDescription></AlertDialogHeader><AlertDialogFooter><AlertDialogCancel>Cancel</AlertDialogCancel><AlertDialogAction className="bg-destructive text-destructive-foreground" onClick={() => router.delete(`/employees/${employee.id}`)}>Delete</AlertDialogAction></AlertDialogFooter></AlertDialogContent></AlertDialog></>}</div></TableCell>
                                </TableRow>)}
                            </TableBody>
                        </Table>
                    </div>
                    <Pagination {...pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    );
}