import { Link } from '@inertiajs/react';
import { LockKeyhole, Pencil, Plus, Users } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import ConfirmDelete from '@/components/confirm-delete';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
interface AccountRow { id: number; name: string; email: string; role: string; role_key: string; employee: string|null; image_url: string|null; protected: boolean; }
interface Props { title: string; description: string; rows: AccountRow[]; canManage: boolean; }

export default function UsersIndex({ title, description, rows, canManage }: Props) {
	return <AppLayout title={title} description={description} actions={canManage ? <><Button variant="outline" asChild><Link href="/roles">Manage roles</Link></Button><Button asChild><Link href="/users/create"><Plus /> Add account</Link></Button></> : undefined}>
		<Card><CardHeader className="flex-row items-center justify-between space-y-0"><div><CardTitle className="flex items-center gap-2"><Users className="size-5 text-primary" /> Accounts</CardTitle><CardDescription>{rows.length} accounts in this workspace.</CardDescription></div><Badge variant="outline">Admin access</Badge></CardHeader><CardContent><div className="overflow-x-auto rounded-md border"><Table><TableHeader><TableRow><TableHead className="w-16">No</TableHead><TableHead>User</TableHead><TableHead>Email</TableHead><TableHead>Employee</TableHead><TableHead>Role</TableHead><TableHead className="w-32 text-right">Actions</TableHead></TableRow></TableHeader><TableBody>
				{rows.length === 0 && <TableRow><TableCell colSpan={6} className="h-24 text-center text-muted-foreground">No accounts found.</TableCell></TableRow>}
				{rows.map((account, index) => <TableRow key={account.id}><TableCell className="text-muted-foreground">{index + 1}</TableCell><TableCell><div className="flex items-center gap-3">{account.image_url ? <img src={account.image_url} alt="" className="size-9 rounded-full object-cover" /> : <span className="flex size-9 items-center justify-center rounded-full bg-muted text-xs font-semibold">{account.name.slice(0, 1).toUpperCase()}</span>}<div><p className="font-medium">{account.name}</p>{account.protected && <p className="flex items-center gap-1 text-xs text-muted-foreground"><LockKeyhole className="size-3" /> Protected account</p>}</div></div></TableCell><TableCell className="text-muted-foreground">{account.email}</TableCell><TableCell>{account.employee ?? <span className="text-muted-foreground">Not linked</span>}</TableCell><TableCell><Badge variant={account.protected ? 'default' : 'secondary'}>{account.role}</Badge></TableCell><TableCell><div className="flex justify-end gap-1"><Button variant="ghost" size="icon" asChild title="Edit"><Link href={`/users/${account.id}/edit`}><Pencil className="size-4" /></Link></Button>{!account.protected && <ConfirmDelete url={`/users/${account.id}`} title={`Delete ${account.name}?`} description="This account will lose access to the system." />}</div></TableCell></TableRow>)}
			</TableBody></Table></div></CardContent></Card>
	</AppLayout>;
}
