import ResourceIndex, { ResourceRow } from '@/components/resource-index';
interface Props { title: string; description: string; rows: ResourceRow[]; }
export default function CompletedReports(props: Props) { return <ResourceIndex {...props} base="/completed-reports" detailPath={(id) => `/completed-reports/print-report/${id}`} detail columns={[{key:'title',label:'Report'},{key:'asset',label:'Asset'},{key:'updated',label:'Completed'}]} />; }
