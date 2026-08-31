import ResourceIndex, { ResourceRow } from '@/components/resource-index';
interface Props { title: string; description: string; rows: ResourceRow[]; }
export default function IncomingReports(props: Props) { return <ResourceIndex {...props} base="/incoming-reports" detailPath={(id) => `/incoming-reports/detail/${id}`} detail columns={[{key:'title',label:'Report'},{key:'asset',label:'Asset'},{key:'status',label:'Status',badge:true},{key:'created',label:'Created'}]} />; }
