import ResourceDetail from '@/components/resource-detail';
interface Props { title: string; asset: Record<string, unknown>; setting: Record<string, unknown> | null; history: unknown[]; }
export default function DepreciationShow({title,asset}: Props) { return <ResourceDetail title={title} data={asset} back="/depreciation" />; }
