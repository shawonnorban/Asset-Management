import ResourceDetail from '@/components/resource-detail';
interface Props { title: string; license: Record<string, unknown>; }
export default function LicenseShow({title,license}: Props) { return <ResourceDetail title={title} data={license} back="/software-licenses" />; }
