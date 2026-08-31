import { SimpleForm } from '@/components/simple-crud';

interface Props {
    title: string;
    base: string;
    field: string;
    fieldLabel: string;
    record: { id: number; name?: string } | null;
}

export default function DepartmentForm(props: Props) {
    return <SimpleForm {...props} />;
}
