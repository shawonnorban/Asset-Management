import ResourceIndex, { ResourceRow } from '@/components/resource-index';
interface Props { title: string; description: string; rows: ResourceRow[]; detail: boolean; }
export default function DepreciationIndex(props: Props) { return <ResourceIndex {...props} base="/depreciation" columns={[{key:'code',label:'Asset Code'},{key:'name',label:'Asset'},{key:'cost',label:'Acquisition Cost'},{key:'status',label:'Setting',badge:true}]} />; }
