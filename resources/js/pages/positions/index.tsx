import { SimpleIndex, SimpleRow } from '@/components/simple-crud';

interface Props {
    title: string;
    description: string;
    rows: SimpleRow[];
    canManage: boolean;
}

export default function PositionsIndex({ title, description, rows, canManage }: Props) {
    return (
        <SimpleIndex
            title={title}
            description={description}
            rows={rows}
            base="/positions"
            nameHeading="Position"
            usageHeading="Employees"
            canManage={canManage}
        />
    );
}
