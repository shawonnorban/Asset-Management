import { SimpleForm } from '@/components/simple-crud';
import { SelectField, TextareaField } from '@/components/field';
interface Props { title:string; asset:{id:number;asset_name?:string}; setting:{id:number}|null; }
export default function Disposal({title,asset}:Props) { return <SimpleForm title={title} base="/depreciation" actionUrl={`/depreciation/${asset.id}/disposal`} field="disposal_reason" fieldLabel="Disposal reason" record={{id:asset.id}} initial={{disposal_note:''}} httpMethod="put" fieldChoices={['DAMAGED','SOLD','DONATED','LOST','OTHER'].map((v)=>({value:v,label:v}))}>{(data,setData,errors)=><TextareaField name="disposal_note" label="Disposal note" value={data.disposal_note} error={errors.disposal_note} onChange={(v)=>setData('disposal_note',v)} />}</SimpleForm>; }
