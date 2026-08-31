import ResourceIndex, { ResourceRow } from '@/components/resource-index';
interface Props { title: string; description: string; rows: ResourceRow[]; canManage: boolean; }
export default function DepreciationSettingsIndex(props: Props) { return <ResourceIndex {...props} base="/depreciation-settings" createLabel="Add Setting" columns={[{key:'code',label:'Asset Code'},{key:'name',label:'Asset'},{key:'method',label:'Method'},{key:'status',label:'Status',badge:true}]} />; }
