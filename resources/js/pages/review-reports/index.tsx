import ResourceIndex, { ResourceRow } from '@/components/resource-index';
interface Props { title: string; description: string; rows: ResourceRow[]; }
export default function ReviewReports(props: Props) { return <ResourceIndex {...props} base="/review-reports" detailPath={(id) => `/review-reports/detail/${id}`} detail columns={[{key:'title',label:'Report'},{key:'status',label:'Status',badge:true},{key:'created',label:'Created'}]} />; }
