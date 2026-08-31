import { SimpleIndex, SimpleRow } from '@/components/simple-crud';

interface Props {
    title: string;
    description: string;
    rows: SimpleRow[];
    canManage: boolean;
}

export default function DepartmentsIndex({ title, description, rows, canManage }: Props) {
    return (
        <SimpleIndex
            title={title}
            description={description}
            rows={rows}
            base="/departments"
            nameHeading="Department"
            usageHeading="Employees"
            canManage={canManage}
        />
    );
}
