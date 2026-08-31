import ResourceDetail from '@/components/resource-detail';
interface Props { title: string; auditLog: Record<string, unknown>; }
export default function AuditShow({ title, auditLog }: Props) { return <ResourceDetail title={title} data={auditLog} back="/audit" />; }
