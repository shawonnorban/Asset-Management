import { SimpleIndex, SimpleRow } from '@/components/simple-crud';

interface Props {
    title: string;
    description: string;
    rows: (SimpleRow & { asset_type: string })[];
    canManage: boolean;
    pagination: { links: { url: string | null; label: string; active: boolean }[]; from?: number; to?: number; total: number };
}

export default function CategoriesIndex({ title, description, rows, canManage, pagination }: Props) {
    return (
        <SimpleIndex
            title={title}
            description={description}
            rows={rows}
            base="/categories"
            nameHeading="Category"
            detailHeading="Asset type"
            detailKey="asset_type"
            usageHeading="Assets"
            canManage={canManage}
            pagination={pagination}
        />
    );
}
