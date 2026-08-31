import ReportDetail from '@/components/report-detail';
interface Props { title:string; issueReport:Record<string,unknown>; feedback?:Record<string,unknown>|null; feedbackReply?:Record<string,unknown>|null; }
export default function ReviewReportDetail({title,issueReport,feedback,feedbackReply}: Props) { return <ReportDetail title={title} report={issueReport} feedback={feedback} feedbackReplies={feedbackReply ? [feedbackReply] : []} mode="review" />; }
