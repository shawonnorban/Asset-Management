import { FormEvent } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save, UserRound } from 'lucide-react';

import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';

interface Option { id: number; name?: string; location_name?: string; }
interface Employee {
    id: number; employee_code: string; name: string; department_id: number | null; position_id: number | null;
    location_id: number | null; user?: { id: number; email: string; role_id: number } | null; father_name: string; mother_name: string | null; nid_number: string | null;
    present_address: string | null; permanent_address: string | null; mail_address: string | null; mobile: string | null; join_date: string | null; image_url?: string | null;
}
interface Props { employee: Employee | null; departments: Option[]; positions: Option[]; locations: Option[]; roles: { id: number; label: string }[]; }
type FormData = Record<string, string | File | null>;

const fields = [
    ['employee_code', 'Employee Code', 'text', true], ['name', 'Employee Name', 'text', true],
    ['father_name', "Father's Name", 'text', true], ['mother_name', "Mother's Name", 'text', false],
    ['nid_number', 'NID Number', 'text', false], ['mobile', 'Mobile', 'text', false],
    ['mail_address', 'Mail Address', 'email', false], ['join_date', 'Join Date', 'date', false],
] as const;

export default function EmployeeForm({ employee, departments, positions, locations, roles }: Props) {
    const editing = Boolean(employee);
    const { data, setData, post, processing, errors } = useForm<FormData>({
        employee_code: employee?.employee_code ?? '', name: employee?.name ?? '', father_name: employee?.father_name ?? '',
        mother_name: employee?.mother_name ?? '', nid_number: employee?.nid_number ?? '', mobile: employee?.mobile ?? '',
        mail_address: employee?.mail_address ?? '', join_date: employee?.join_date ?? '', present_address: employee?.present_address ?? '',
        permanent_address: employee?.permanent_address ?? '', department_id: employee?.department_id ? String(employee.department_id) : '',
        position_id: employee?.position_id ? String(employee.position_id) : '', location_id: employee?.location_id ? String(employee.location_id) : '', image: null,
        create_user: employee?.user ? '1' : '0', account_email: employee?.user?.email ?? '', account_role_id: employee?.user?.role_id ? String(employee.user.role_id) : '', account_password: '', account_password_confirmation: '', _method: editing ? 'PUT' : null,
    });
    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (editing) {
            post(`/employees/${employee!.id}`, { forceFormData: true });
            return;
        }
        post('/employees', { forceFormData: true });
    };
    const input = (name: string) => ({ value: (data[name] as string) ?? '', onChange: (event: React.ChangeEvent<HTMLInputElement>) => setData(name, event.target.value) });
    const error = (name: string) => errors[name];
    return <AppLayout title={editing ? 'Edit Employee' : 'Add Employee'} actions={<Button variant="outline" asChild><Link href="/employees"><ArrowLeft /> Back</Link></Button>}>
        <form onSubmit={submit} className="grid gap-6 lg:grid-cols-[1fr_320px]">
            <div className="space-y-6">
                <Card><CardHeader><CardTitle>Identity</CardTitle></CardHeader><CardContent className="grid gap-4 sm:grid-cols-2">
                    {fields.slice(0, 5).map(([name, label, type, required]) => <label key={name} className="space-y-2 text-sm font-medium"><span>{label}{required && ' *'}</span><Input {...input(name)} type={type} required={required} /><span className="text-xs text-destructive">{error(name)}</span></label>)}
                    <label className="space-y-2 text-sm font-medium"><span>Department *</span><select className="flex h-10 w-full rounded-md border bg-background px-3 text-sm" value={(data.department_id as string) ?? ''} onChange={(e) => setData('department_id', e.target.value)} required><option value="">Select department</option>{departments.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}</select><span className="text-xs text-destructive">{error('department_id')}</span></label>
                    <label className="space-y-2 text-sm font-medium"><span>Position *</span><select className="flex h-10 w-full rounded-md border bg-background px-3 text-sm" value={(data.position_id as string) ?? ''} onChange={(e) => setData('position_id', e.target.value)} required><option value="">Select position</option>{positions.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}</select><span className="text-xs text-destructive">{error('position_id')}</span></label>
                    <label className="space-y-2 text-sm font-medium"><span>Location</span><select className="flex h-10 w-full rounded-md border bg-background px-3 text-sm" value={(data.location_id as string) ?? ''} onChange={(e) => setData('location_id', e.target.value)}><option value="">Not set</option>{locations.map((item) => <option key={item.id} value={item.id}>{item.location_name}</option>)}</select></label>
                </CardContent></Card>
                <Card><CardHeader><CardTitle>Address</CardTitle></CardHeader><CardContent className="grid gap-4 sm:grid-cols-2"><label className="space-y-2 text-sm font-medium"><span>Present Address</span><textarea className="min-h-28 w-full rounded-md border bg-background px-3 py-2 text-sm" value={(data.present_address as string) ?? ''} onChange={(e) => setData('present_address', e.target.value)} /></label><label className="space-y-2 text-sm font-medium"><span>Permanent Address</span><textarea className="min-h-28 w-full rounded-md border bg-background px-3 py-2 text-sm" value={(data.permanent_address as string) ?? ''} onChange={(e) => setData('permanent_address', e.target.value)} /></label></CardContent></Card>
            </div>
            <div className="space-y-6"><Card><CardHeader><CardTitle>Contact</CardTitle></CardHeader><CardContent className="space-y-4">{fields.slice(5).map(([name, label, type, required]) => <label key={name} className="space-y-2 text-sm font-medium"><span>{label}</span><Input {...input(name)} type={type} /><span className="text-xs text-destructive">{error(name)}</span></label>)}</CardContent></Card><Card><CardHeader><CardTitle className="flex items-center gap-2"><UserRound className="size-4 text-primary" /> Login account</CardTitle></CardHeader><CardContent className="space-y-4"><label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={data.create_user === '1'} onChange={(event) => setData('create_user', event.target.checked ? '1' : '0')} /> Enable login for this employee</label>{data.create_user === '1' && <><label className="space-y-2 text-sm font-medium"><span>Login email *</span><Input type="email" value={data.account_email as string} onChange={(event) => setData('account_email', event.target.value)} required /><span className="text-xs text-destructive">{error('account_email')}</span></label><label className="space-y-2 text-sm font-medium"><span>Role *</span><select className="flex h-10 w-full rounded-md border bg-background px-3 text-sm" value={data.account_role_id as string} onChange={(event) => setData('account_role_id', event.target.value)} required><option value="">Select role</option>{roles.map((role) => <option key={role.id} value={role.id}>{role.label}</option>)}</select><span className="text-xs text-destructive">{error('account_role_id')}</span></label><label className="space-y-2 text-sm font-medium"><span>Password {employee?.user ? '' : '*'}</span><Input type="password" value={data.account_password as string} onChange={(event) => setData('account_password', event.target.value)} required={!employee?.user} /><span className="text-xs text-destructive">{error('account_password')}</span></label><label className="space-y-2 text-sm font-medium"><span>Confirm password</span><Input type="password" value={data.account_password_confirmation as string} onChange={(event) => setData('account_password_confirmation', event.target.value)} required={!employee?.user} /></label></>}</CardContent></Card><Card><CardHeader><CardTitle>Photo</CardTitle></CardHeader><CardContent className="space-y-4">{employee?.image_url && <img src={employee.image_url} alt={employee.name} className="mx-auto size-40 rounded-full object-cover" />}<Input type="file" accept="image/png,image/jpeg" onChange={(e) => setData('image', e.target.files?.[0] ?? null)} /><p className="text-xs text-muted-foreground">JPEG or PNG, maximum 4MB.</p><span className="text-xs text-destructive">{error('image')}</span></CardContent></Card><Button type="submit" disabled={processing} className="w-full"><Save /> {editing ? 'Update Employee' : 'Save Employee'}</Button></div>
        </form>
    </AppLayout>;
}
