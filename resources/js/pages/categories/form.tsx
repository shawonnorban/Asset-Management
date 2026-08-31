import { SimpleForm } from '@/components/simple-crud';
import { SelectField } from '@/components/field';

interface Props {
    title: string;
    base: string;
    field: string;
    fieldLabel: string;
    record: { id: number; category_name?: string; asset_type?: string } | null;
    assetTypes: Record<string, string>;
}

export default function CategoryForm({ title, base, field, fieldLabel, record, assetTypes }: Props) {
    return (
        <SimpleForm
            title={title}
            base={base}
            field={field}
            fieldLabel={fieldLabel}
            record={record}
            initial={{ asset_type: '' }}
        >
            {(data, setData, errors) => (
                <SelectField
                    name="asset_type"
                    label="Asset type"
                    required
                    value={data.asset_type}
                    error={errors.asset_type}
                    choices={Object.entries(assetTypes).map(([value, label]) => ({ value, label }))}
                    placeholder="Select an asset type"
                    onChange={(value) => setData('asset_type', value)}
                />
            )}
        </SimpleForm>
    );
}
