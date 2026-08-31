import { SimpleForm } from '@/components/simple-crud';
import { SelectField, TextField } from '@/components/field';
interface AssetOption { id: number; asset_code: string; asset_name: string; }
interface TaxGroup { id: number; name: string; }
interface Props { title:string; asset?: AssetOption; setting?: Record<string,unknown>|null; assets?: AssetOption[]; taxGroups?: TaxGroup[]; }
export default function DepreciationSettingForm({title,asset,setting,assets=[],taxGroups=[]}: Props) {
	const editing = Boolean(setting);
	return <SimpleForm title={title} base="/depreciation-settings" actionUrl={editing ? `/depreciation-settings/${asset?.id}` : undefined} httpMethod={editing ? 'put' : 'post'} field="acquisition_cost" fieldLabel="Acquisition cost" record={(editing ? setting : null) as {id:number}|null} initial={{asset_id: asset?.id ? String(asset.id) : '', tax_depreciation_group_id:'',method:'STRAIGHT_LINE',salvage_value:'',useful_life_months:'',in_service_date:''}}>
		{(data,setData,errors) => <>
			{!editing && <SelectField name="asset_id" label="Asset" required value={data.asset_id} error={errors.asset_id} choices={assets.map((item) => ({value:String(item.id),label:`${item.asset_code} - ${item.asset_name}`}))} onChange={(value) => setData('asset_id',value)} />}
			<SelectField name="tax_depreciation_group_id" label="Tax depreciation group" required value={data.tax_depreciation_group_id} error={errors.tax_depreciation_group_id} choices={taxGroups.map((item) => ({value:String(item.id),label:item.name}))} onChange={(value) => setData('tax_depreciation_group_id',value)} />
			<SelectField name="method" label="Method" required value={data.method} error={errors.method} choices={[{value:'STRAIGHT_LINE',label:'Straight line'},{value:'DECLINING_BALANCE',label:'Declining balance'}]} onChange={(value) => setData('method',value)} />
			<TextField name="salvage_value" label="Salvage value" type="number" value={data.salvage_value} error={errors.salvage_value} onChange={(value) => setData('salvage_value',value)} />
			<TextField name="useful_life_months" label="Useful life (months)" type="number" value={data.useful_life_months} error={errors.useful_life_months} onChange={(value) => setData('useful_life_months',value)} />
			<TextField name="in_service_date" label="In-service date" type="date" required value={data.in_service_date} error={errors.in_service_date} onChange={(value) => setData('in_service_date',value)} />
		</>}
	</SimpleForm>;
}
