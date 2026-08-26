import { FormEvent } from 'react';
import { useForm } from '@inertiajs/react';
import { Plus, Undo2, UserCheck } from 'lucide-react';

import { SelectField, TextField, TextareaField, choicesFrom, choicesFromList } from '@/components/field';
import { Button } from '@/components/ui/button';

const CONDITIONS = { NEW: 'New', GOOD: 'Good', FAIR: 'Fair', POOR: 'Poor' };
const RETURN_STATUSES = {
    IN_STORAGE: 'In storage',
    UNDER_REPAIR: 'Under repair',
    RETIRED: 'Retired',
};

const today = () => new Date().toISOString().slice(0, 10);

interface Option {
    id: number;
    label: string;
}

/** Hand the asset to an employee. */
export function AssignForm({ assetId, employees }: { assetId: number; employees: Option[] }) {
    const { data, setData, post, processing, errors } = useForm({
        employee_id: '',
        assigned_at: today(),
        condition_on_assign: '',
        note: '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(`/inventory/${assetId}/assign`, { preserveScroll: true });
    };

    return (
        <form onSubmit={submit} className="space-y-4">
            <SelectField
                name="employee_id"
                label="Assign To"
                required
                value={data.employee_id}
                error={errors.employee_id}
                choices={choicesFromList(employees)}
                emptyLabel="Select employee"
                onChange={(v) => setData('employee_id', v)}
            />

            <TextField
                name="assigned_at"
                label="Assigned On"
                type="date"
                required
                value={data.assigned_at}
                error={errors.assigned_at}
                onChange={(v) => setData('assigned_at', v)}
            />

            <SelectField
                name="condition_on_assign"
                label="Condition At Handover"
                value={data.condition_on_assign}
                error={errors.condition_on_assign}
                choices={choicesFrom(CONDITIONS)}
                emptyLabel="Not recorded"
                onChange={(v) => setData('condition_on_assign', v)}
            />

            <TextareaField
                name="assign_note"
                label="Note"
                rows={2}
                value={data.note}
                error={errors.note}
                onChange={(v) => setData('note', v)}
            />

            <Button type="submit" className="w-full" disabled={processing}>
                <UserCheck /> Assign
            </Button>
        </form>
    );
}

/** Take the asset back. */
export function ReturnForm({ assetId, condition }: { assetId: number; condition: string }) {
    const { data, setData, put, processing, errors } = useForm({
        returned_at: today(),
        condition_on_return: condition ?? '',
        status: 'IN_STORAGE',
        note: '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        put(`/inventory/${assetId}/return`, { preserveScroll: true });
    };

    return (
        <form onSubmit={submit} className="space-y-4">
            <TextField
                name="returned_at"
                label="Returned On"
                type="date"
                required
                value={data.returned_at}
                error={errors.returned_at}
                onChange={(v) => setData('returned_at', v)}
            />

            <SelectField
                name="condition_on_return"
                label="Condition On Return"
                value={data.condition_on_return}
                error={errors.condition_on_return}
                choices={choicesFrom(CONDITIONS)}
                emptyLabel="Unchanged"
                onChange={(v) => setData('condition_on_return', v)}
            />

            <SelectField
                name="return_status"
                label="New Status"
                required
                value={data.status}
                error={errors.status}
                choices={choicesFrom(RETURN_STATUSES)}
                onChange={(v) => setData('status', v)}
            />

            <TextareaField
                name="return_note"
                label="Note"
                rows={2}
                value={data.note}
                error={errors.note}
                onChange={(v) => setData('note', v)}
            />

            <Button type="submit" variant="destructive" className="w-full" disabled={processing}>
                <Undo2 /> Record Return
            </Button>
        </form>
    );
}

/** Consume a licence seat on this asset. */
export function InstallSoftwareForm({
    assetId,
    licenses,
}: {
    assetId: number;
    licenses: Option[];
}) {
    const { data, setData, post, processing, errors } = useForm({
        software_license_id: '',
        installed_at: today(),
        note: '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(`/inventory/${assetId}/software`, { preserveScroll: true });
    };

    if (licenses.length === 0) {
        return <p className="text-sm text-muted-foreground">No license has a free seat right now.</p>;
    }

    return (
        <form onSubmit={submit} className="grid grid-cols-1 gap-4 sm:grid-cols-12 sm:items-end">
            <div className="sm:col-span-5">
                <SelectField
                    name="software_license_id"
                    label="Install License"
                    required
                    value={data.software_license_id}
                    error={errors.software_license_id}
                    choices={choicesFromList(licenses)}
                    emptyLabel="Select software"
                    onChange={(v) => setData('software_license_id', v)}
                />
            </div>

            <div className="sm:col-span-4">
                <TextField
                    name="installed_at"
                    label="Installed On"
                    type="date"
                    required
                    value={data.installed_at}
                    error={errors.installed_at}
                    onChange={(v) => setData('installed_at', v)}
                />
            </div>

            <div className="sm:col-span-3">
                <Button type="submit" className="w-full" disabled={processing}>
                    <Plus /> Install
                </Button>
            </div>
        </form>
    );
}
