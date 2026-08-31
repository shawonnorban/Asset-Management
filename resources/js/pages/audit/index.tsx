import ResourceIndex, { ResourceRow } from '@/components/resource-index';
interface Props { title: string; description: string; logs: {data: ResourceRow[]}; }
export default function AuditIndex({ title, description, logs }: Props) { return <ResourceIndex title={title} description={description} rows={logs.data} base="/audit" detail columns={[{key:'action',label:'Action'},{key:'table_name',label:'Table'},{key:'user',label:'User'},{key:'occurred_at',label:'Occurred'}]} />; }
