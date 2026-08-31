import ResourceIndex, { ResourceRow } from '@/components/resource-index';
interface Props { title: string; description: string; rows: ResourceRow[]; }
export default function UserStatus(props: Props) { return <ResourceIndex {...props} base="/user/status" columns={[{key:'name',label:'Name'},{key:'email',label:'Email'},{key:'role',label:'Role'},{key:'status',label:'Status',badge:true}]} />; }
