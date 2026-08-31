import { FormEvent, useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import {
    ArrowLeft,
    Check,
    KeyRound,
    LockKeyhole,
    Pencil,
    Plus,
    ShieldCheck,
    UsersRound,
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import ConfirmDelete from '@/components/confirm-delete';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';

interface Role {
    id: number;
    key: string;
    label: string;
    protected: boolean;
    permissions: string[];
}

interface Props {
    title: string;
    description: string;
    roles: Role[];
    permissionOptions: string[];
    permissionCatalog: Record<string, string[]>;
}

const permissionLabels: Record<string, string> = {
    '*': 'All system permissions',
    'dashboard.view': 'View dashboard',
    'assets.view': 'View assets',
    'assets.manage': 'Manage assets',
    'stock.view': 'View stock and inventory',
    'employees.view': 'View employees',
    'employees.manage': 'Manage employees',
    'master_data.manage': 'Manage master data',
    'stock_takes.manage': 'Manage stock takes',
    'stock_takes.view': 'View stock takes',
    'licenses.manage': 'Manage software licenses',
    'depreciation.view': 'View depreciation',
    'depreciation.manage': 'Manage depreciation',
    'categories.view': 'View asset categories',
    'locations.view': 'View locations and branches',
    'users.manage': 'Manage accounts and roles',
    'audit.view': 'View account activity',
    'reports.view': 'View incoming reports',
    'reports.manage': 'Manage report resolutions',
    'reports.create': 'Create issue reports',
    'reports.view_own': 'View own issue reports',
};

const actionLabels: Record<string, string> = {
    create: 'Can create',
    edit: 'Can edit',
    update: 'Can update',
    view: 'Can view',
    delete: 'Can delete',
};

export default function RolesIndex({
    title,
    description,
    roles,
    permissionOptions,
    permissionCatalog,
}: Props) {
    const [open, setOpen] = useState(false);
    const [editingRole, setEditingRole] = useState<Role | null>(null);
    const [permissionSearch, setPermissionSearch] = useState('');

    const { data, setData, post, put, processing, errors, reset } = useForm<{
        name: string;
        permissions: string[];
    }>({
        name: '',
        permissions: [],
    });

    const togglePermission = (permission: string) => {
        setData(
            'permissions',
            data.permissions.includes(permission)
                ? data.permissions.filter((item) => item !== permission)
                : [...data.permissions, permission]
        );
    };

    const toggleModule = (module: string, actions: string[]) => {
        const permissions = actions.map((action) => `${module}.${action}`);
        const allSelected = permissions.every((permission) => data.permissions.includes(permission));
        setData('permissions', allSelected
            ? data.permissions.filter((permission) => !permissions.includes(permission))
            : Array.from(new Set([...data.permissions, ...permissions])));
    };

    const startCreate = () => {
        setEditingRole(null);
        setPermissionSearch('');
        reset();
        setOpen(true);
    };

    const startEdit = (role: Role) => {
        setEditingRole(role);
        setData({
            name: role.key,
            permissions: role.permissions.filter((permission) => permission !== '*'),
        });
        setPermissionSearch('');
        setOpen(true);
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const options = {
            onSuccess: () => {
                reset();
                setEditingRole(null);
                setOpen(false);
            },
        };

        if (editingRole) {
            put(`/roles/${editingRole.id}`, options);
        } else {
            post('/roles', options);
        }
    };

    const customRoles = roles.filter((role) => !role.protected).length;
    const assignedPermissions = new Set(roles.flatMap((role) => role.permissions)).size;
    const catalogPermissions = new Set(
        Object.entries(permissionCatalog).flatMap(([module, actions]) =>
            actions.map((action) => `${module}.${action}`),
        ),
    );
    const otherPermissions = permissionOptions.filter(
        (permission) => !catalogPermissions.has(permission),
    );

    const renderHeaderActions = () => (
        <>
            <Button variant="outline" asChild>
                <Link href="/users">
                    <ArrowLeft className="size-4" />
                    <span>Accounts</span>
                </Link>
            </Button>

            <Button onClick={startCreate}>
                <Plus className="size-4" />
                <span>Create role</span>
            </Button>

            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-3xl">
                    <form onSubmit={submit}>
                        <DialogHeader>
                            <DialogTitle>
                                {editingRole ? 'Edit role' : 'Create role'}
                            </DialogTitle>
                            <DialogDescription>
                                {editingRole
                                    ? 'Update the role key and its permissions.'
                                    : 'Choose a unique role key and assign its permissions.'}
                            </DialogDescription>
                        </DialogHeader>

                        <div className="space-y-5 py-4">
                            <div>
                                <label
                                    htmlFor="role-name"
                                    className="mb-2 block text-sm font-medium"
                                >
                                    Role key <span className="text-destructive">*</span>
                                </label>
                                <Input
                                    id="role-name"
                                    value={data.name}
                                    placeholder="asset_manager"
                                    onChange={(event) => setData('name', event.target.value)}
                                    aria-invalid={Boolean(errors.name)}
                                />
                                {errors.name && (
                                    <p className="mt-1 text-xs text-destructive">
                                        {errors.name}
                                    </p>
                                )}
                            </div>

                            <div>
                                <p className="mb-2 text-sm font-medium">Permissions</p>
                                <Input
                                    value={permissionSearch}
                                    placeholder="Search permissions..."
                                    onChange={(event) => setPermissionSearch(event.target.value)}
                                    className="mb-3"
                                />
                                <div className="max-h-96 space-y-4 overflow-y-auto rounded-md border p-3">
                                    {Object.entries(permissionCatalog).filter(([module, actions]) => {
                                        const query = permissionSearch.toLowerCase();
                                        return !query || module.includes(query) || actions.some((action) => `${module}.${action}`.includes(query) || (actionLabels[action] ?? action).toLowerCase().includes(query));
                                    }).map(([module, actions]) => (
                                        <div key={module} className="rounded-md border bg-muted/20 p-3">
                                            <div className="mb-2 flex items-center justify-between gap-2">
                                                <p className="text-sm font-semibold capitalize">{module.replace(/_/g, ' ')}</p>
                                                <Button type="button" variant="outline" size="sm" onClick={() => toggleModule(module, actions)}>
                                                    {actions.every((action) => data.permissions.includes(`${module}.${action}`)) ? 'Clear all' : 'Select all'}
                                                </Button>
                                            </div>
                                            <div className="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
                                                {actions.map((action) => {
                                                    const permission = `${module}.${action}`;
                                                    return (
                                                        <label key={permission} className="flex min-w-0 items-center gap-2 text-sm">
                                                            <input
                                                                type="checkbox"
                                                                checked={data.permissions.includes(permission)}
                                                                onChange={() => togglePermission(permission)}
                                                                className="mt-1 rounded"
                                                            />
                                                            <span className="whitespace-nowrap">{actionLabels[action] ?? action}</span>
                                                        </label>
                                                    );
                                                })}
                                            </div>
                                        </div>
                                    ))}
                                    {otherPermissions.length > 0 && (
                                        <div className="rounded-md border bg-muted/20 p-3">
                                            <p className="mb-2 text-sm font-semibold">Other permissions</p>
                                            <div className="grid gap-2 sm:grid-cols-2">
                                                {otherPermissions.map((permission) => (
                                                    <label key={permission} className="flex items-start gap-2 text-sm">
                                                        <input
                                                            type="checkbox"
                                                            checked={data.permissions.includes(permission)}
                                                            onChange={() => togglePermission(permission)}
                                                            className="mt-1 rounded"
                                                        />
                                                        <span>{permissionLabels[permission] ?? permission}</span>
                                                    </label>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                </div>
                                {errors.permissions && (
                                    <p className="mt-1 text-xs text-destructive">
                                        {errors.permissions}
                                    </p>
                                )}
                            </div>
                        </div>

                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setOpen(false)}
                            >
                                Cancel
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing
                                    ? editingRole
                                        ? 'Saving...'
                                        : 'Creating...'
                                    : editingRole
                                    ? 'Save changes'
                                    : 'Create role'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );

    return (
        <AppLayout
            title={title}
            description={description}
            actions={renderHeaderActions()}
        >
            {/* Summary Statistics */}
            <div className="mb-6 grid gap-4 sm:grid-cols-3">
                <Card>
                    <CardContent className="flex items-center gap-4 p-5">
                        <span className="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <ShieldCheck className="size-5" />
                        </span>
                        <div>
                            <p className="text-2xl font-semibold">{roles.length}</p>
                            <p className="text-sm text-muted-foreground">Total roles</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="flex items-center gap-4 p-5">
                        <span className="flex size-10 items-center justify-center rounded-lg bg-accent text-accent-foreground">
                            <KeyRound className="size-5" />
                        </span>
                        <div>
                            <p className="text-2xl font-semibold">{assignedPermissions}</p>
                            <p className="text-sm text-muted-foreground">Permissions in use</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="flex items-center gap-4 p-5">
                        <span className="flex size-10 items-center justify-center rounded-lg bg-secondary text-secondary-foreground">
                            <UsersRound className="size-5" />
                        </span>
                        <div>
                            <p className="text-2xl font-semibold">{customRoles}</p>
                            <p className="text-sm text-muted-foreground">Custom roles</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Access Profiles Grid */}
            <Card>
                <CardHeader className="border-b sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                    <div>
                        <CardTitle className="flex items-center gap-2">
                            <ShieldCheck className="size-5 text-primary" /> Access profiles
                        </CardTitle>
                        <CardDescription>
                            Define what each account can see and manage.
                        </CardDescription>
                    </div>
                    <p className="text-xs text-muted-foreground">
                        {roles.length} profiles configured
                    </p>
                </CardHeader>

                <CardContent className="p-4 sm:p-6">
                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        {roles.map((role) => (
                            <div
                                key={role.id}
                                className="flex min-h-[260px] flex-col rounded-lg border bg-background p-5 transition-shadow hover:shadow-md"
                            >
                                <div className="flex items-start justify-between gap-3">
                                    <div className="flex min-w-0 items-start gap-3">
                                        <span
                                            className={`flex size-10 shrink-0 items-center justify-center rounded-lg ${
                                                role.protected
                                                    ? 'bg-primary text-primary-foreground'
                                                    : 'bg-muted text-primary'
                                            }`}
                                        >
                                            {role.protected ? (
                                                <LockKeyhole className="size-4" />
                                            ) : (
                                                <ShieldCheck className="size-4" />
                                            )}
                                        </span>
                                        <div className="min-w-0">
                                            <p className="truncate font-semibold">{role.label}</p>
                                            <p className="mt-1 truncate font-mono text-xs text-muted-foreground">
                                                {role.key}
                                            </p>
                                        </div>
                                    </div>
                                    <Badge variant={role.protected ? 'default' : 'secondary'}>
                                        {role.permissions.length}
                                    </Badge>
                                </div>

                                <div className="mt-5 flex-1">
                                    <p className="mb-3 text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Granted permissions
                                    </p>
                                    <div className="flex flex-wrap gap-2">
                                        {role.permissions.slice(0, 6).map((permission) => (
                                            <span
                                                key={permission}
                                                className="inline-flex items-center gap-1 rounded-full bg-muted px-2.5 py-1 text-xs text-muted-foreground"
                                            >
                                                <Check className="size-3 text-emerald-600" />
                                                {permissionLabels[permission] ?? permission}
                                            </span>
                                        ))}
                                        {role.permissions.length > 6 && (
                                            <span className="rounded-full bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary">
                                                +{role.permissions.length - 6} more
                                            </span>
                                        )}
                                        {role.permissions.length === 0 && (
                                            <span className="text-sm text-muted-foreground">
                                                No permissions assigned
                                            </span>
                                        )}
                                    </div>
                                </div>

                                {!role.protected && (
                                    <div className="mt-5 flex justify-end gap-1 border-t pt-3">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            title="Edit role"
                                            onClick={() => startEdit(role)}
                                        >
                                            <Pencil className="size-4" />
                                        </Button>
                                        <ConfirmDelete
                                            url={`/roles/${role.id}`}
                                            title={`Delete ${role.label}?`}
                                            description="This role can only be deleted when it is not assigned to any account."
                                        />
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                </CardContent>
            </Card>
        </AppLayout>
    );
}
