import ResourceIndex, { ResourceRow } from '@/components/resource-index';
interface Props { title: string; description: string; rows: ResourceRow[]; canManage: boolean; }
export default function LicensesIndex(props: Props) { return <ResourceIndex {...props} base="/software-licenses" createLabel="Add License" detail columns={[{key:'name',label:'License'},{key:'type',label:'Type',badge:true},{key:'seats',label:'Seats'},{key:'in_use',label:'In use'}]} />; }
