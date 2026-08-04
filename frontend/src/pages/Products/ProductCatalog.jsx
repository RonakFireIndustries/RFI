import { useEffect, useMemo, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { ChevronDown, ChevronRight, ChevronsDownUp, ChevronsUpDown, Edit2, Filter, FolderTree, Layers, Package, Trash2, X } from 'lucide-react';
import ModuleListPage from '../ERP/ModuleListPage';
import api from '../../services/api';
import { unwrapList } from '../../services/resourceHelpers';
import { useProductsStore } from '../../store/productsStore';
import { useAuthStore } from '../../store/authStore';

const FINANCE_ROLES = ['Admin', 'Accountant'];

const getPlacement = (product, categoryById) => {
  const sub = product.category;
  if (!sub) return { parentBucket: 'Uncategorized', subName: null };
  const parent = sub.parent_id ? categoryById.get(sub.parent_id) : null;
  if (parent) return { parentBucket: parent.name, subName: sub.name };
  return { parentBucket: sub.name, subName: null };
};

const buildCategoryTree = (products, getPlacementFor) => {
  const tree = {};

  const push = (parentName, subName, product) => {
    if (!tree[parentName]) tree[parentName] = { subs: {}, direct: [] };
    if (subName) {
      if (!tree[parentName].subs[subName]) tree[parentName].subs[subName] = [];
      tree[parentName].subs[subName].push(product);
    } else {
      tree[parentName].direct.push(product);
    }
  };

  products.forEach((product) => {
    const { parentBucket, subName } = getPlacementFor(product);
    push(parentBucket, subName, product);
  });

  const sortByName = (a, b) => String(a.name || a).localeCompare(String(b.name || b));

  return Object.keys(tree)
    .sort((a, b) => a.localeCompare(b))
    .map((parentName) => ({
      parentName,
      direct: tree[parentName].direct.sort(sortByName),
      subs: Object.keys(tree[parentName].subs)
        .sort((a, b) => a.localeCompare(b))
        .map((subName) => ({ subName, products: tree[parentName].subs[subName].sort(sortByName) })),
    }));
};

export default function ProductCatalog() {
  const { items } = useProductsStore();
  const [lookups, setLookups] = useState({ categories: [], suppliers: [], sites: [] });
  const userRoles = useAuthStore((s) => s.roles);
  const canFinance = userRoles.some((r) => FINANCE_ROLES.includes(r));
  const [searchParams] = useSearchParams();
  const categoryId = searchParams.get('category_id');

  const [filters, setFilters] = useState({ parent: '', sub: '', supplier: '', status: '' });
  const [showFilters, setShowFilters] = useState(false);
  const [collapsedParents, setCollapsedParents] = useState(() => new Set());
  const [collapsedSubs, setCollapsedSubs] = useState(() => new Set());

  const fetchParams = useMemo(() => {
    if (categoryId) return { category_id: categoryId };
    return undefined;
  }, [categoryId]);

  useEffect(() => {
    const fetchLookups = async () => {
      const results = await Promise.allSettled([
        api.get('/categories'),
        api.get('/suppliers'),
        api.get('/sites?per_page=1000'),
      ]);
      const get = (idx) => results[idx].status === 'fulfilled' ? unwrapList(results[idx].value.data) : [];
      setLookups({ categories: get(0), suppliers: get(1), sites: get(2) });
    };
    fetchLookups();
  }, []);

  const parentOptions = useMemo(() => {
    const names = new Set(['Uncategorized']);
    lookups.categories.forEach((c) => { if (!c.parent_id) names.add(c.name); });
    return [...names].sort((a, b) => a.localeCompare(b));
  }, [lookups.categories]);

  const subOptions = useMemo(() => {
    let subs = lookups.categories.filter((c) => c.parent_id);
    if (filters.parent && filters.parent !== 'Uncategorized') {
      const parentId = lookups.categories.find((c) => !c.parent_id && c.name === filters.parent)?.id;
      if (parentId) subs = subs.filter((c) => c.parent_id === parentId);
    }
    return [...new Set(subs.map((c) => c.name))].sort((a, b) => a.localeCompare(b));
  }, [lookups.categories, filters.parent]);

  const activeFilterCount = Object.values(filters).filter((v) => v !== '').length;

  const categoryTitle = useMemo(() => {
    if (!categoryId) return 'Products';
    const cat = lookups.categories.find((c) => String(c.id) === categoryId);
    return cat ? `Products · ${cat.name}` : `Products (Category #${categoryId})`;
  }, [categoryId, lookups.categories]);

  const setFilter = (key, value) => setFilters((current) => ({ ...current, [key]: value }));

  const clearFilters = () => setFilters({ parent: '', sub: '', supplier: '', status: '' });

  const toggleParent = (name) => {
    setCollapsedParents((prev) => {
      const next = new Set(prev);
      if (next.has(name)) next.delete(name); else next.add(name);
      return next;
    });
  };

  const toggleSub = (key) => {
    setCollapsedSubs((prev) => {
      const next = new Set(prev);
      if (next.has(key)) next.delete(key); else next.add(key);
      return next;
    });
  };

  const expandAll = () => { setCollapsedParents(new Set()); setCollapsedSubs(new Set()); };

  const collapseAll = () => {
    const categoryById = new Map(lookups.categories.map((c) => [c.id, c]));
    const parents = new Set();
    const subs = new Set();
    items.forEach((product) => {
      const { parentBucket, subName } = getPlacement(product, categoryById);
      parents.add(parentBucket);
      if (subName) subs.add(`${parentBucket}|${subName}`);
    });
    setCollapsedParents(parents);
    setCollapsedSubs(subs);
  };

  const columns = useMemo(() => {
    const cols = [
      { header: 'SKU', accessor: 'sku' },
      { header: 'Product', accessor: 'name' },
      { header: 'Dimension', accessor: 'dimension' },
      { header: 'Category', accessor: 'category.name' },
      { header: 'Supplier', accessor: 'supplier.name' },
      { header: 'Stock', cellValue: (row) => row.total_stock ?? 0 },
      { header: 'Status', accessor: 'status' },
    ];
    if (canFinance) {
      cols.splice(5, 0, { header: 'Selling Price', cellValue: (row) => Number(row.selling_price || 0).toFixed(2) });
    }
    return cols;
  }, [canFinance]);

  const fields = useMemo(() => {
    const flds = [
      { name: 'sku', label: 'SKU (leave empty for auto-generate)' },
      { name: 'name', label: 'Product Name', required: true },
      { name: 'hsn_code', label: 'HSN Code' },
      { name: 'dimension', label: 'Dimension' },
      { name: 'category_id', label: 'Category', type: 'select', optionsKey: 'categories', emptyAsNull: true },
      { name: 'supplier_id', label: 'Supplier', type: 'select', optionsKey: 'suppliers', emptyAsNull: true },
    ];
    if (canFinance) {
      flds.push({ name: 'purchase_price', label: 'Purchase Price', type: 'number', step: '0.01', required: true });
      flds.push({ name: 'selling_price', label: 'Selling Price', type: 'number', step: '0.01', required: true });
    }
    flds.push(
      { name: 'opening_stock', label: 'Opening Stock', type: 'number', step: '1', defaultValue: 0 },
      { name: 'site_id', label: 'Store At (Site)', type: 'select', optionsKey: 'sites', emptyAsNull: true, placeholder: 'Select site (required if opening stock > 0)' },
      { name: 'status', label: 'Status', type: 'select', defaultValue: 'active', options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
    );
    return flds;
  }, [canFinance]);

  const renderItems = (products, { openModal, deleteItem, detailBasePath }) => {
    const categoryById = new Map(lookups.categories.map((c) => [c.id, c]));

    const filtered = products.filter((product) => {
      const { parentBucket, subName } = getPlacement(product, categoryById);
      if (filters.parent && parentBucket !== filters.parent) return false;
      if (filters.sub && subName !== filters.sub) return false;
      if (filters.supplier && String(product.supplier_id) !== filters.supplier) return false;
      if (filters.status && product.status !== filters.status) return false;
      return true;
    });

    if (filtered.length === 0) {
      return (
        <div className="rounded-lg border border-gray-200 bg-white p-10 text-center">
          <Package className="mx-auto h-10 w-10 text-gray-300" />
          <p className="mt-3 text-sm text-gray-500">
            {products.length === 0 ? 'No products found.' : 'No products match the selected filters.'}
          </p>
          {products.length > 0 && (
            <button type="button" onClick={clearFilters} className="mt-3 inline-flex items-center gap-1 rounded-md border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
              <X className="h-3.5 w-3.5" /> Clear filters
            </button>
          )}
        </div>
      );
    }

    const groups = buildCategoryTree(filtered, (product) => getPlacement(product, categoryById));

    const selectClass = "w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring";

    return (
      <div className="space-y-4">
        <div className="flex flex-wrap items-center justify-between gap-3">
          <div className="flex items-center gap-3">
            <button
              type="button"
              onClick={() => setShowFilters((v) => !v)}
              className="inline-flex items-center gap-2 rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              <Filter className="h-4 w-4" />
              Filters
              {activeFilterCount > 0 && (
                <span className="rounded-full bg-primary px-2 py-0.5 text-xs font-semibold text-white">{activeFilterCount}</span>
              )}
            </button>
            <p className="text-sm text-gray-500">{filtered.length} product{filtered.length === 1 ? '' : 's'}</p>
          </div>
          <div className="flex items-center gap-2">
            <button
              type="button"
              onClick={expandAll}
              className="inline-flex items-center gap-1.5 rounded-md border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50"
            >
              <ChevronsDownUp className="h-4 w-4" /> Expand All
            </button>
            <button
              type="button"
              onClick={collapseAll}
              className="inline-flex items-center gap-1.5 rounded-md border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50"
            >
              <ChevronsUpDown className="h-4 w-4" /> Collapse All
            </button>
          </div>
        </div>

        {showFilters && (
          <div className="grid grid-cols-1 gap-3 rounded-lg border border-gray-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-5">
            <label className="block">
              <span className="mb-1 block text-xs font-medium text-gray-600">Parent Category</span>
              <select value={filters.parent} onChange={(e) => setFilters({ ...filters, parent: e.target.value, sub: '' })} className={selectClass}>
                <option value="">All</option>
                {parentOptions.map((name) => <option key={name} value={name}>{name}</option>)}
              </select>
            </label>
            <label className="block">
              <span className="mb-1 block text-xs font-medium text-gray-600">Sub Category</span>
              <select value={filters.sub} onChange={(e) => setFilter('sub', e.target.value)} className={selectClass}>
                <option value="">All</option>
                {subOptions.map((name) => <option key={name} value={name}>{name}</option>)}
              </select>
            </label>
            <label className="block">
              <span className="mb-1 block text-xs font-medium text-gray-600">Supplier</span>
              <select value={filters.supplier} onChange={(e) => setFilter('supplier', e.target.value)} className={selectClass}>
                <option value="">All</option>
                {lookups.suppliers.map((supplier) => <option key={supplier.id} value={supplier.id}>{supplier.name}</option>)}
              </select>
            </label>
            <label className="block">
              <span className="mb-1 block text-xs font-medium text-gray-600">Status</span>
              <select value={filters.status} onChange={(e) => setFilter('status', e.target.value)} className={selectClass}>
                <option value="">All</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </label>
            <div className="flex items-end">
              <button
                type="button"
                onClick={clearFilters}
                disabled={activeFilterCount === 0}
                className="inline-flex w-full items-center justify-center gap-1 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
              >
                <X className="h-4 w-4" /> Clear
              </button>
            </div>
          </div>
        )}

        <div className="space-y-4">
          {groups.map((group) => {
            const isParentCollapsed = collapsedParents.has(group.parentName);
            const total = group.direct.length + group.subs.reduce((sum, sub) => sum + sub.products.length, 0);

            return (
              <section key={group.parentName} className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <button
                  type="button"
                  onClick={() => toggleParent(group.parentName)}
                  className="flex w-full items-center justify-between gap-3 px-4 py-3 text-left hover:bg-gray-50"
                >
                  <span className="flex min-w-0 items-center gap-2">
                    {isParentCollapsed ? <ChevronRight className="h-4 w-4 shrink-0 text-gray-400" /> : <ChevronDown className="h-4 w-4 shrink-0 text-gray-400" />}
                    <FolderTree className="h-4 w-4 shrink-0 text-primary" />
                    <span className="truncate text-base font-bold text-gray-900">{group.parentName}</span>
                    <span className="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">{total}</span>
                  </span>
                </button>

                {!isParentCollapsed && (
                  <div className="space-y-6 border-t border-gray-100 px-4 py-4">
                    {group.direct.length > 0 && (
                      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        {group.direct.map((product) => renderCard(product, { openModal, deleteItem, detailBasePath, canFinance }))}
                      </div>
                    )}

                    {group.subs.map((sub) => {
                      const subKey = `${group.parentName}|${sub.subName}`;
                      const isSubCollapsed = collapsedSubs.has(subKey);

                      return (
                        <div key={subKey}>
                          <button
                            type="button"
                            onClick={() => toggleSub(subKey)}
                            className="flex w-full items-center gap-2 text-left text-sm font-semibold text-gray-700 hover:text-primary"
                          >
                            {isSubCollapsed ? <ChevronRight className="h-4 w-4 shrink-0 text-gray-400" /> : <ChevronDown className="h-4 w-4 shrink-0 text-gray-400" />}
                            <Layers className="h-3.5 w-3.5 shrink-0 text-gray-400" />
                            <span className="truncate">{sub.subName}</span>
                            <span className="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">{sub.products.length}</span>
                          </button>
                          {!isSubCollapsed && (
                            <div className="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                              {sub.products.map((product) => renderCard(product, { openModal, deleteItem, detailBasePath, canFinance }))}
                            </div>
                          )}
                        </div>
                      );
                    })}
                  </div>
                )}
              </section>
            );
          })}
        </div>
      </div>
    );
  };

  return (
    <ModuleListPage
      title={categoryTitle}
      description="Manage catalog items with category, supplier, and warehouse inventory relationships."
      store={useProductsStore}
      detailBasePath="/dashboard/products"
      searchPlaceholder="Search products or SKUs..."
      lookups={lookups}
      columns={columns}
      fields={fields}
      fetchParams={fetchParams}
      renderItems={renderItems}
    />
  );
}

const renderCard = (product, { openModal, deleteItem, detailBasePath, canFinance }) => (
  <div key={product.id} className="flex flex-col rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
    <div className="flex items-start justify-between gap-3">
      <div className="min-w-0">
        <Link to={`${detailBasePath}/${product.id}`} className="line-clamp-2 font-semibold text-gray-900 hover:text-primary">
          {product.name}
        </Link>
        <p className="mt-0.5 text-xs text-gray-500">SKU: {product.sku || '-'}</p>
      </div>
      <span className={`inline-flex shrink-0 items-center rounded-full border px-2.5 py-1 text-xs font-medium ${
        product.status === 'active'
          ? 'border-green-200 bg-green-50 text-green-700'
          : 'border-red-200 bg-red-50 text-red-700'
      }`}>
        {product.status}
      </span>
    </div>

    <dl className="mt-3 flex-1 space-y-1 text-sm">
      {canFinance && (
        <div className="flex items-center justify-between">
          <dt className="text-gray-500">Price</dt>
          <dd className="font-semibold text-gray-900">{Number(product.selling_price || 0).toFixed(2)}</dd>
        </div>
      )}
      <div className="flex items-center justify-between">
        <dt className="text-gray-500">Stock</dt>
        <dd className="font-medium text-gray-900">{product.total_stock ?? 0}</dd>
      </div>
      {product.supplier?.name && (
        <div className="flex items-center justify-between gap-2">
          <dt className="text-gray-500">Supplier</dt>
          <dd className="truncate text-gray-900">{product.supplier.name}</dd>
        </div>
      )}
      {product.dimension && (
        <div className="flex items-center justify-between gap-2">
          <dt className="text-gray-500">Dimension</dt>
          <dd className="truncate text-gray-900">{product.dimension}</dd>
        </div>
      )}
    </dl>

    <div className="mt-4 flex items-center justify-between gap-2 border-t border-gray-100 pt-3">
      <Link to={`${detailBasePath}/${product.id}`} className="rounded-md border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
        View
      </Link>
      <div className="flex items-center gap-1">
        <button type="button" className="rounded-md p-1.5 text-blue-600 hover:bg-blue-50" onClick={() => openModal(product)} title="Edit">
          <Edit2 className="h-4 w-4" />
        </button>
        <button
          type="button"
          className="rounded-md p-1.5 text-red-600 hover:bg-red-50"
          onClick={() => {
            if (window.confirm(`Delete ${product.name}?`)) deleteItem(product.id);
          }}
          title="Delete"
        >
          <Trash2 className="h-4 w-4" />
        </button>
      </div>
    </div>
  </div>
);
