import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Package, MapPin, AlertTriangle, TrendingUp, ArrowUpDown, Layers } from 'lucide-react';
import { useInventoryDashboardStore } from '../../store/inventoryDashboardStore';

export default function InventoryDashboard() {
  const { data, loading, fetch } = useInventoryDashboardStore();
  const navigate = useNavigate();

  useEffect(() => {
    fetch();
  }, [fetch]);

  const cards = [
    {
      label: 'Total Products',
      value: data?.total_products ?? 0,
      icon: Package,
      color: 'text-blue-600',
      bg: 'bg-blue-50',
      link: '/dashboard/products',
    },
    {
      label: 'Locations',
      value: data?.total_locations ?? 0,
      icon: MapPin,
      color: 'text-green-600',
      bg: 'bg-green-50',
      link: '/dashboard/inventory/locations',
    },
    {
      label: 'Low Stock Items',
      value: data?.low_stock_items ?? 0,
      icon: AlertTriangle,
      color: 'text-red-600',
      bg: 'bg-red-50',
      link: '/dashboard/inventory/stock?low_stock=1',
    },
    {
      label: 'Stock Value',
      value: data?.total_stock_value ? `₹${Number(data.total_stock_value).toLocaleString()}` : '₹0',
      icon: TrendingUp,
      color: 'text-purple-600',
      bg: 'bg-purple-50',
      link: '/dashboard/inventory/stock',
    },
  ];

  if (loading) {
    return <div className="rounded-lg border border-gray-200 bg-white p-8 text-center text-sm text-gray-500">Loading...</div>;
  }

  return (
    <div className="space-y-6 pb-10">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Inventory Dashboard</h1>
        <p className="mt-1 text-sm text-gray-500">Overview of your inventory management system</p>
      </div>

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {cards.map((card) => (
          <button
            key={card.label}
            onClick={() => navigate(card.link)}
            className="rounded-lg border border-gray-200 bg-white p-5 text-left hover:shadow-md transition-shadow"
          >
            <div className={`inline-flex rounded-lg ${card.bg} p-3`}>
              <card.icon className={`h-6 w-6 ${card.color}`} />
            </div>
            <p className="mt-3 text-2xl font-bold text-gray-900">{card.value}</p>
            <p className="text-sm text-gray-500">{card.label}</p>
          </button>
        ))}
      </div>

      <div className="grid gap-6 lg:grid-cols-2">
        <div className="rounded-lg border border-gray-200 bg-white p-5">
          <h2 className="text-lg font-semibold text-gray-900">Recent Transactions</h2>
          <div className="mt-3 space-y-2">
            {data?.recent_transactions?.length > 0 ? (
              data.recent_transactions.slice(0, 5).map((txn) => (
                <div key={txn.id} className="flex items-center justify-between border-b border-gray-100 pb-2 last:border-0">
                  <div>
                    <span className="text-sm font-medium text-gray-900">{txn.product?.name}</span>
                    <span className={`ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${
                      ['opening_stock', 'purchase', 'purchase_return', 'transfer_in', 'sales_return'].includes(txn.transaction_type)
                        ? 'bg-green-50 text-green-700'
                        : 'bg-red-50 text-red-700'
                    }`}>
                      {txn.transaction_type?.replace(/_/g, ' ')}
                    </span>
                  </div>
                  <span className="text-sm text-gray-500">{txn.quantity}</span>
                </div>
              ))
            ) : (
              <p className="text-sm text-gray-400">No transactions yet</p>
            )}
          </div>
        </div>

        <div className="rounded-lg border border-gray-200 bg-white p-5">
          <h2 className="text-lg font-semibold text-gray-900">Stock by Location</h2>
          <div className="mt-3 space-y-2">
            {data?.stock_by_location?.length > 0 ? (
              data.stock_by_location.map((item) => (
                <div key={item.location_id} className="flex items-center justify-between border-b border-gray-100 pb-2 last:border-0">
                  <span className="text-sm font-medium text-gray-900">{item.locationable?.name || `${item.location_type} #${item.location_id}`}</span>
                  <span className="text-sm text-gray-500">{Number(item.total_qty).toLocaleString()} units</span>
                </div>
              ))
            ) : (
              <p className="text-sm text-gray-400">No stock data</p>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
