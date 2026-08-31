import { SimpleForm } from '@/components/simple-crud';

interface Props {
    title: string;
    base: string;
    field: string;
    fieldLabel: string;
    record: { id: number; location_name?: string } | null;
}

export default function LocationForm(props: Props) {
    return <SimpleForm {...props} />;
}
