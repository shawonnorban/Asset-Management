import { SimpleForm } from '@/components/simple-crud';
import { SelectField, TextField } from '@/components/field';
interface Props { title: string; record?: Record<string, unknown> | null; license?: Record<string, unknown> | null; licenseTypes: string[]; }
export default function LicenseForm({title,record,license,licenseTypes}: Props) { const current = record ?? license ?? null; return <SimpleForm title={title} base="/software-licenses" field="name" fieldLabel="License name" record={current as {id:number}|null} initial={{publisher:'',version:'',license_type:'',seats_total:'',expiry_date:''}}>
	{(data,setData,errors) => <>
		<TextField name="publisher" label="Publisher" value={data.publisher} onChange={(value) => setData('publisher',value)} />
		<TextField name="version" label="Version" value={data.version} onChange={(value) => setData('version',value)} />
		<SelectField name="license_type" label="License type" required value={data.license_type} error={errors.license_type} choices={licenseTypes.map((type) => ({value:type,label:type}))} onChange={(value) => setData('license_type',value)} />
		<TextField name="seats_total" label="Total seats" type="number" required value={data.seats_total} error={errors.seats_total} onChange={(value) => setData('seats_total',value)} />
		<TextField name="expiry_date" label="Expiry date" type="date" value={data.expiry_date} onChange={(value) => setData('expiry_date',value)} />
	</>}
	</SimpleForm>; }
