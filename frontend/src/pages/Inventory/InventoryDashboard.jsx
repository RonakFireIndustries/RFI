import { useEffect, useMemo } from 'react';
import { useNavigate } from 'react-router-dom';
import { ChevronRight } from 'lucide-react';
import { useAuthStore } from '../../store/authStore';
import { can } from '../../utils/permissions';
import { useInventoryDashboardStore } from '../../store/inventoryDashboardStore';
import { usePriceVisibility } from '../../hooks/usePriceVisibility';
import DashboardCard from '../../components/Dashboard/DashboardCard';
import DashboardChart from '../../components/Dashboard/DashboardChart';
import DashboardQuickActions from '../../components/Dashboard/DashboardQuickActions';

const INFLOW_TYPES = ['opening_stock', 'purchase', 'purchase_return', 'transfer_in', 'sales_return'];

// `stock_by_location` rows are morph rows, so `location_type` arrives as a class
// name (e.g. "App\Models\Site"). Collapse it to a short label for rows/axes.
const locationLabel = (location) =>
  location.locationable?.name ||
  `${String(location.location_type || 'Location').split('\\').pop()} #${location.location_id}`;

const PageHeader = () => (
  <div>
    <h1 className="text-2xl font-bold text-foreground">Inventory Dashboard</h1>
    <p className="mt-1 text-sm text-muted-foreground">Stock levels, movements and locations at a glance</p>
  </div>
);

const Panel = ({ title, action, children }) => (
  <div className="bg-card rounded-xl shadow-sm border border-border p-6">
    <div className="flex items-center justify-between mb-4">
      <h2 className="text-lg font-bold text-foreground">{title}</h2>
      {action}
    </div>
    {children}
  </div>
);

export default function InventoryDashboard() {
  const { data, loading, error, fetch } = useInventoryDashboardStore();
  const navigate = useNavigate();
  const { money } = usePriceVisibility();
  const user = useAuthStore((state) => state.user);
  const permissions = useAuthStore((state) => state.permissions);

  const permissionCtx = useMemo(
    () => ({ user, permissions: permissions || [] }),
    [user, permissions]
  );

  useEffect(() => {
    // The store rethrows so callers can react; swallow it here because the
    // error panel below is the user-facing signal.
    fetch().catch(() => {});
  }, [fetch]);

  // A destination is only offered as a link when the user can actually open it,
  // otherwise a click would land on an empty/403 page.
  const canSeeStock = can(permissionCtx, ['stock.view']);
  const canSeeTransactions = can(permissionCtx, ['stock-transactions.view']);
  const canSeeProducts = can(permissionCtx, ['products.view']);
  const canSeeLocations = can(permissionCtx, ['inventory-locations.view']);

  const cards = [
    {
      key: 'total_products',
      name: 'Total Products',
      value: data?.total_products ?? 0,
      icon: 'Package',
      link: canSeeProducts ? '/dashboard/products' : null,
    },
    {
      key: 'total_locations',
      name: 'Locations',
      value: data?.total_locations ?? 0,
      icon: 'MapPin',
      link: canSeeLocations ? '/dashboard/inventory/locations' : null,
    },
    {
      key: 'low_stock_items',
      name: 'Low Stock Items',
      value: data?.low_stock_items ?? 0,
      icon: 'AlertTriangle',
      subtitle: data?.low_stock_items ? 'At or below reorder level' : 'All items above reorder level',
      link: canSeeStock ? '/dashboard/inventory/stock?low_stock=1' : null,
    },
    {
      key: 'inventory_value',
      name: 'Stock Value',
      value: money(data?.total_stock_value),
      prefix: '',
      icon: 'TrendingUp',
      link: canSeeStock ? '/dashboard/inventory/stock' : null,
    },
  ];

  const locations = data?.stock_by_location || [];
  const types = data?.transactions_by_type || [];
  const recentTransactions = (data?.recent_transactions || []).slice(0, 5);
  const topProducts = (data?.top_products || []).slice(0, 5);

  // Charts carry only { name, value } (the bar chart treats every other key as
  // a second series), so resolve the payload back to its source row on click.
  const locationByLabel = Object.fromEntries(
    locations.map((location) => [locationLabel(location), location])
  );
  const typeByLabel = Object.fromEntries(
    types.map((type) => [type.transaction_type?.replace(/_/g, ' '), type.transaction_type])
  );

  const openLocation = (label) => {
    const location = locationByLabel[label];
    if (!location || !canSeeStock) return;
    const params = new URLSearchParams({
      location_type: location.location_type,
      location_id: location.location_id,
    });
    navigate(`/dashboard/inventory/stock?${params.toString()}`);
  };

  const openTransactionType = (label) => {
    const type = typeByLabel[label];
    if (!type || !canSeeTransactions) return;
    navigate(`/dashboard/inventory/transactions?${new URLSearchParams({ transaction_type: type })}`);
  };

  // recharts passes Bar the datum under `payload` and spreads it onto the pie
  // sector itself, so accept either shape.
  const onChartClick = (handler) => (entry) => {
    const label = entry?.payload?.name ?? entry?.name;
    if (label) handler(label);
  };

  const openProductTransactions = (productId) => {
    if (canSeeTransactions) navigate(`/dashboard/inventory/transactions?${new URLSearchParams({ product_id: productId })}`);
  };

  const viewAll = (label, to) => (
    <button
      type="button"
      onClick={() => navigate(to)}
      className="flex items-center text-xs font-semibold text-primary/80 hover:text-primary transition-colors"
    >
      {label}
      <ChevronRight className="w-3.5 h-3.5 ml-0.5" />
    </button>
  );

  const quickActions = [
    can(permissionCtx, ['stock-transactions.create']) && {
      key: 'record_transaction',
      label: 'Record Transaction',
      icon: 'Plus',
      link: '/dashboard/inventory/record-transaction',
    },
    can(permissionCtx, ['stock-transfers.view']) && {
      key: 'transfer_stock',
      label: 'Transfer Stock',
      icon: 'ArrowLeftRight',
      link: '/dashboard/inventory/transfers',
    },
    can(permissionCtx, ['stock-requests.view']) && {
      key: 'stock_requests',
      label: 'Stock Requests',
      icon: 'FileText',
      link: '/dashboard/inventory/requests',
    },
    canSeeStock && {
      key: 'view_stock',
      label: 'View Stock',
      icon: 'Search',
      link: '/dashboard/inventory/stock',
    },
    canSeeProducts && {
      key: 'products',
      label: 'Products',
      icon: 'ClipboardList',
      link: '/dashboard/products',
    },
  ].filter(Boolean);

  if (loading) {
    return (
      <div className="bg-card rounded-xl shadow-sm border border-border p-8 text-center text-sm text-muted-foreground">
        Loading inventory overview...
      </div>
    );
  }

  if (error) {
    return (
      <div className="space-y-6 pb-10">
        <PageHeader />
        <div className="rounded-xl border border-destructive/30 bg-destructive/10 p-6 text-sm text-destructive">
          Inventory data could not be loaded. Refresh the page, or ask an administrator
          to grant you the <span className="font-semibold">inventory.view</span> permission.
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6 pb-10">
      <PageHeader />

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {cards.map((card) => (
          <DashboardCard key={card.key} card={card} />
        ))}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {locations.length > 0 && (
          <DashboardChart
            chart={{
              key: 'stock_by_location',
              name: 'Stock by Location',
              chart_type: 'bar',
              data: locations.map((location) => ({
                name: locationLabel(location),
                value: Number(location.total_qty) || 0,
              })),
            }}
            onElementClick={canSeeStock ? onChartClick(openLocation) : undefined}
            action={canSeeStock ? viewAll('View all', '/dashboard/inventory/stock') : null}
          />
        )}
        {types.length > 0 && (
          <DashboardChart
            chart={{
              key: 'transactions_by_type',
              name: 'Transactions by Type',
              chart_type: 'pie',
              data: types.map((type) => ({
                name: type.transaction_type?.replace(/_/g, ' ') || 'other',
                value: Number(type.count) || 0,
              })),
            }}
            onElementClick={canSeeTransactions ? onChartClick(openTransactionType) : undefined}
            action={canSeeTransactions ? viewAll('View all', '/dashboard/inventory/transactions') : null}
          />
        )}
      </div>

      {quickActions.length > 0 && <DashboardQuickActions quickActions={quickActions} />}

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Panel
          title="Recent Transactions"
          action={canSeeTransactions ? viewAll('View all', '/dashboard/inventory/transactions') : null}
        >
          {recentTransactions.length > 0 ? (
            <div>
              {recentTransactions.map((txn) => {
                const quantity = Number(txn.quantity) || 0;
                const isInflow = INFLOW_TYPES.includes(txn.transaction_type);
                return (
                  <button
                    key={txn.id}
                    type="button"
                    onClick={() => openProductTransactions(txn.product_id)}
                    disabled={!canSeeTransactions}
                    className={`flex w-full items-center justify-between gap-3 border-b border-border px-2 py-2 text-left last:border-0 last:pb-0 -mx-2 rounded ${
                      canSeeTransactions ? 'hover:bg-muted cursor-pointer' : ''
                    }`}
                  >
                    <div className="min-w-0 flex-1">
                      <p className="truncate text-sm font-medium text-foreground">
                        {txn.product?.name || `Product #${txn.product_id}`}
                      </p>
                      <p className="text-xs capitalize text-muted-foreground">
                        {txn.transaction_type?.replace(/_/g, ' ')}
                        {txn.locationable?.name ? ` · ${txn.locationable.name}` : ''}
                      </p>
                    </div>
                    <span
                      className={`inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-xs font-semibold ${
                        isInflow ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'
                      }`}
                    >
                      {quantity > 0 ? '+' : ''}{quantity.toLocaleString()}
                    </span>
                  </button>
                );
              })}
            </div>
          ) : (
            <p className="text-sm text-muted-foreground">No stock movements recorded yet.</p>
          )}
        </Panel>

        <Panel
          title="Top Products by Stock"
          action={canSeeStock ? viewAll('View all', '/dashboard/inventory/stock') : null}
        >
          {topProducts.length > 0 ? (
            <div>
              {topProducts.map((product) => {
                const canOpen = canSeeStock || canSeeProducts;
                return (
                  <button
                    key={`${product.product_id}`}
                    type="button"
                    onClick={() => {
                      if (canSeeStock) {
                        navigate(`/dashboard/inventory/stock?${new URLSearchParams({ product_id: product.product_id })}`);
                      } else {
                        navigate(`/dashboard/products/${product.product_id}`);
                      }
                    }}
                    disabled={!canOpen}
                    className={`flex w-full items-center justify-between gap-3 border-b border-border px-2 py-2 text-left last:border-0 last:pb-0 -mx-2 rounded ${
                      canOpen ? 'hover:bg-muted cursor-pointer' : ''
                    }`}
                  >
                    <div className="min-w-0 flex-1">
                      <p className="truncate text-sm font-medium text-foreground">
                        {product.product?.name || `Product #${product.product_id}`}
                      </p>
                      <p className="truncate text-xs text-muted-foreground">
                        {product.product?.sku || '—'}
                      </p>
                    </div>
                    <span className="flex shrink-0 items-center text-sm font-semibold text-foreground">
                      {Number(product.total_qty).toLocaleString()}
                      <span className="ml-1 text-xs font-normal text-muted-foreground">units</span>
                      {canOpen && <ChevronRight className="ml-1 h-3.5 w-3.5 text-muted-foreground" />}
                    </span>
                  </button>
                );
              })}
            </div>
          ) : (
            <p className="text-sm text-muted-foreground">No stock recorded yet.</p>
          )}
        </Panel>
      </div>
    </div>
  );
}
