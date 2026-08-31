import { SimpleIndex, SimpleRow } from '@/components/simple-crud';

interface Props {
    title: string;
    description: string;
    rows: SimpleRow[];
    canManage: boolean;
    pagination: { links: { url: string | null; label: string; active: boolean }[]; from?: number; to?: number; total: number };
}

export default function LocationsIndex({ title, description, rows, canManage, pagination }: Props) {
    return (
        <SimpleIndex
            title={title}
            description={description}
            rows={rows}
            base="/locations"
            nameHeading="Location"
            canManage={canManage}
            pagination={pagination}
        />
    );
}
