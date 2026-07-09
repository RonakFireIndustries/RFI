import { useEffect } from 'react';
import { Link, useParams } from 'react-router-dom';
import { useStockRequestStore } from '../../../store/stockRequestStore';

const statusColors = {
  requested: 'bg-yellow-50 text-yellow-700',
  approved: 'bg-blue-50 text-blue-700',
  issued: 'bg-purple-50 text-purple-700',
  received: 'bg-green-50 text-green-700',
};

export default function StockRequestDetail() {
  const { id } = useParams();
  const { selected, loading, fetchItem, approve, issue, receive, clearSelected } = useStockRequestStore();

  useEffect(() => {
    fetchItem(id);
    return () => clearSelected();
  }, [fetchItem, id, clearSelected]);

  if (loading || !selected) {
    return <div className="rounded-lg border border-gray-200 bg-white p-8 text-center text-sm text-gray-500">Loading...</div>;
  }

  const fields = [
    { label: 'Request #', value: selected.request_number },
    { label: 'Product', value: selected.product?.name },
    { label: 'From Location', value: selected.from_locationable?.name },
    { label: 'To Location', value: selected.to_locationable?.name },
    { label: 'Quantity', value: Number(selected.quantity).toLocaleString() },
    { label: 'Approved Qty', value: Number(selected.approved_quantity).toLocaleString() },
    { label: 'Issued Qty', value: Number(selected.issued_quantity).toLocaleString() },
    { label: 'Received Qty', value: Number(selected.received_quantity).toLocaleString() },
    { label: 'Requested By', value: selected.requester || '-' },
    { label: 'Approved By', value: selected.approver || '-' },
    { label: 'Issued By', value: selected.issuer || '-' },
    { label: 'Received By', value: selected.receiver || '-' },
    { label: 'Status', value: selected.status },
    { label: 'Notes', value: selected.notes || '-' },
    { label: 'Created', value: selected.created_at ? new Date(selected.created_at).toLocaleString() : '-' },
  ];

  return (
    <div className="space-y-5 pb-10">
      <div>
        <Link to="/dashboard/inventory/requests" className="text-sm font-medium text-primary hover:underline">← Back to Requests</Link>
        <h1 className="mt-2 text-2xl font-bold text-gray-900">Stock Request #{selected.request_number}</h1>
      </div>

      <div className="mb-4">
        <span className={`inline-flex items-center rounded-full px-3 py-1 text-sm font-medium capitalize ${statusColors[selected.status]}`}>
          {selected.status}
        </span>
        {selected.status === 'requested' && (
          <button onClick={() => approve(selected.id)} className="ml-3 rounded-md bg-blue-600 px-3 py-1 text-sm font-medium text-white hover:bg-blue-700">Approve</button>
        )}
        {selected.status === 'approved' && (
          <button onClick={() => issue(selected.id)} className="ml-3 rounded-md bg-purple-600 px-3 py-1 text-sm font-medium text-white hover:bg-purple-700">Issue</button>
        )}
        {selected.status === 'issued' && (
          <button onClick={() => receive(selected.id)} className="ml-3 rounded-md bg-green-600 px-3 py-1 text-sm font-medium text-white hover:bg-green-700">Receive</button>
        )}
      </div>

      <div className="grid gap-4 rounded-lg border border-gray-200 bg-white p-5 sm:grid-cols-2 lg:grid-cols-3">
        {fields.map((f) => (
          <div key={f.label}>
            <div className="text-xs font-semibold uppercase tracking-wider text-gray-500">{f.label}</div>
            <div className="mt-1 text-sm font-medium text-gray-900">{f.value || '-'}</div>
          </div>
        ))}
      </div>
    </div>
  );
}
