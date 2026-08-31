import ResourceDetail from '@/components/resource-detail';

interface Props {
    title: string;
    record: Record<string, unknown>;
}

export default function MaintenanceShow({ title, record }: Props) {
    return <ResourceDetail title={title} data={record} back="/maintenance" />;
}
