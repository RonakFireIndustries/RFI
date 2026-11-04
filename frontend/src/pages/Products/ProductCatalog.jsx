import { useCallback, useEffect, useMemo, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { ChevronDown, ChevronRight, ChevronsDownUp, ChevronsUpDown, Edit2, Filter, FolderTree, Layers, Package, Trash2, X } from 'lucide-react';
import ModuleListPage from '../ERP/ModuleListPage';
import PermissionGate from '../../components/Guards/PermissionGate';
import api from '../../services/api';
import { unwrapList } from '../../services/resourceHelpers';
import { useProductsStore } from '../../store/productsStore';
import Money from '../../components/Shared/Money';
import UnitConversionHint from '../../components/Shared/UnitConversionHint';
import { usePriceVisibility } from '../../hooks/usePriceVisibility';

const MODULE = 'products';

const stockTotals = (product) => {
  const rows = Array.isArray(product.stock) ? product.stock : [];
  const total = Number(product.total_stock ?? rows.reduce((sum, r) => sum + Number(r.quantity || 0), 0));
  const available = rows.reduce((sum, r) => sum + Number(r.available_quantity ?? r.quantity ?? 0), 0);
  return { total, available, rows };
};

const isLowStock = (product) => {
  const reorder = Number(product.reorder_level || 0);
  if (reorder <= 0) return false;
  return stockTotals(product).total <= reorder;
};

const isOutOfStock = (product) => stockTotals(product).total <= 0;

// The product row itself has no site: stock lives in product_stock rows, one
// per location. When the edit form opens, reuse the site that already holds
// this product's stock so saving does not silently drop it.
const getStockSiteId = (product) => {
  const rows = Array.isArray(product.stock) ? product.stock : [];
  const siteRow = rows.find((row) => String(row.location_type || '').endsWith('Site'));
  if (siteRow?.location_id) return String(siteRow.location_id);
  return product.site_id ? String(product.site_id) : '';
};

function ProductStockSection({ product }) {
  const [expanded, setExpanded] = useState(false);
  const { total, available, rows } = stockTotals(product);
  const low = isLowStock(product);
  const out = total <= 0;
  const unit = product.unit?.code || product.unit?.name || '';

  return (
    <div className="rounded-md border border-gray-100 bg-gray-50/60 px-2.5 py-2">
      <div className="flex items-center justify-between gap-2">
        <div className="flex items-center gap-2">
          <span className={`inline-block h-2 w-2 rounded-full ${out ? 'bg-red-500' : low ? 'bg-amber-500' : 'bg-green-500'}`} />
          <span className="text-xs font-medium text-gray-500">Stock</span>
        </div>
        <div className="text-right">
          <span className={`text-sm font-semibold ${out ? 'text-red-600' : 'text-gray-900'}`}>
            {total}{unit ? ` ${unit}` : ''}
          </span>
          <span className="ml-2 text-xs text-gray-500">avail. {available}</span>
        </div>
      </div>
      {low && !out && (
        <p className="mt-1 text-[11px] font-medium text-amber-700">Low stock — at/below reorder level ({product.reorder_level})</p>
      )}
      {out && (
        <p className="mt-1 text-[11px] font-medium text-red-600">Out of stock</p>
      )}
      {rows.length > 0 && (
        <>
          <button
            type="button"
            onClick={() => setExpanded((v) => !v)}
            className="mt-1.5 inline-flex items-center gap-1 text-[11px] font-medium text-primary hover:underline"
          >
            {expanded ? <ChevronDown className="h-3 w-3" /> : <ChevronRight className="h-3 w-3" />}
            {rows.length} location{rows.length === 1 ? '' : 's'}
          </button>
          {expanded && (
            <ul className="mt-1 space-y-0.5 border-t border-gray-200 pt-1">
              {rows.map((row) => (
                <li key={row.id} className="flex items-center justify-between gap-2 text-[11px] text-gray-600">
                  <span className="truncate">{row.locationable?.name || `${row.location_type?.split('\\').pop() || 'Location'} #${row.location_id}`}</span>
                  <span className="font-medium tabular-nums">{Number(row.quantity)}</span>
                </li>
              ))}
            </ul>
          )}
        </>
      )}
    </div>
  );
}

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
  const [lookups, setLookups] = useState({ categories: [], suppliers: [], sites: [], units: [] });
  const { canSeePrices } = usePriceVisibility();
  const [searchParams] = useSearchParams();
  const categoryId = searchParams.get('category_id');

  const [filters, setFilters] = useState({ parent: '', sub: '', supplier: '', status: '', stock: '' });
  const [showFilters, setShowFilters] = useState(false);
  const [collapsedParents, setCollapsedParents] = useState(() => new Set());
  const [collapsedSubs, setCollapsedSubs] = useState(() => new Set());
  const [selectedParentId, setSelectedParentId] = useState('');

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
        api.get('/units'),
      ]);
      const get = (idx) => results[idx].status === 'fulfilled' ? unwrapList(results[idx].value.data) : [];
      setLookups({ categories: get(0), suppliers: get(1), sites: get(2), units: get(3) });
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

  const parentCategories = useMemo(
    () => lookups.categories.filter((c) => !c.parent_id).sort((a, b) => a.name.localeCompare(b.name)),
    [lookups.categories]
  );

  const subCategories = useMemo(
    () => lookups.categories.filter((c) => c.parent_id && String(c.parent_id) === String(selectedParentId)).sort((a, b) => a.name.localeCompare(b.name)),
    [lookups.categories, selectedParentId]
  );

  const formLookups = useMemo(() => ({
    parentCategories,
    subCategories,
    suppliers: lookups.suppliers,
    sites: lookups.sites,
    units: (lookups.units || []).map((u) => ({ ...u, name: u.code ? `${u.name} (${u.code})` : u.name })),
  }), [parentCategories, subCategories, lookups.suppliers, lookups.sites, lookups.units]);

  const activeFilterCount = Object.values(filters).filter((v) => v !== '').length;

  const categoryTitle = useMemo(() => {
    if (!categoryId) return 'Products';
    const cat = lookups.categories.find((c) => String(c.id) === categoryId);
    return cat ? `Products · ${cat.name}` : `Products (Category #${categoryId})`;
  }, [categoryId, lookups.categories]);

  const setFilter = (key, value) => setFilters((current) => ({ ...current, [key]: value }));

  const clearFilters = () => setFilters({ parent: '', sub: '', supplier: '', status: '', stock: '' });

  const handleFieldChange = useCallback((fieldName, value) => {
    if (fieldName === 'parent_category_id') {
      setSelectedParentId(value);
    }
  }, []);

  const transformProductPayload = useCallback((payload) => {
    const out = { ...payload };
    const subId = out.subcategory_id ? Number(out.subcategory_id) : null;
    const parentId = out.parent_category_id ? Number(out.parent_category_id) : null;
    out.category_id = subId || parentId || null;
    delete out.parent_category_id;
    delete out.subcategory_id;
    return out;
  }, []);

  const getProductInitialValues = useCallback((item) => {
    const catId = item.category_id;
    const cat = lookups.categories.find((c) => c.id === catId);
    let parentId = '';
    let subId = '';
    if (cat) {
      if (cat.parent_id) {
        parentId = String(cat.parent_id);
        subId = String(cat.id);
      } else {
        parentId = String(cat.id);
      }
    }
    setSelectedParentId(parentId);
    return {
      sku: item.sku ?? '',
      name: item.name ?? '',
      hsn_code: item.hsn_code ?? '',
      dimension: item.dimension ?? '',
      description: item.description ?? '',
      unit_id: item.unit_id ? String(item.unit_id) : '',
      parent_category_id: parentId,
      subcategory_id: subId,
      supplier_id: item.supplier_id ? String(item.supplier_id) : '',
      purchase_price: item.purchase_price ?? '',
      selling_price: item.selling_price ?? '',
      opening_stock: item.opening_stock ?? 0,
      opening_stock_unit_id: '',
      site_id: getStockSiteId(item),
      status: item.status ?? 'active',
    };
  }, [lookups.categories]);

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
    if (canSeePrices) {
      cols.splice(5, 0, {
        header: 'Selling Price',
        cell: (row) => <Money value={row.selling_price} />,
      });
    }
    return cols;
  }, [canSeePrices]);

  const fields = useMemo(() => {
    const flds = [
      { name: 'sku', label: 'SKU (leave empty for auto-generate)' },
      { name: 'name', label: 'Product Name', required: true },
      { name: 'hsn_code', label: 'HSN Code' },
      { name: 'dimension', label: 'Dimension' },
      { name: 'description', label: 'Description', type: 'textarea', rows: 3, fullWidth: true, emptyAsNull: true, placeholder: 'Shown on BOQ lines when this product is added' },
      { name: 'unit_id', label: 'Unit', type: 'select', optionsKey: 'units', required: true },
      { name: 'parent_category_id', label: 'Category', type: 'select', optionsKey: 'parentCategories', emptyAsNull: true },
      { name: 'subcategory_id', label: 'Sub Category', type: 'select', optionsKey: 'subCategories', emptyAsNull: true },
      { name: 'supplier_id', label: 'Supplier', type: 'select', optionsKey: 'suppliers', emptyAsNull: true },
    ];
    if (canSeePrices) {
      flds.push({ name: 'purchase_price', label: 'Purchase Price', type: 'number', step: '0.01', required: true });
      flds.push({ name: 'selling_price', label: 'Selling Price', type: 'number', step: '0.01', required: true });
    }
    flds.push(
      {
        name: 'opening_stock',
        label: 'Opening Stock',
        type: 'number',
        step: '1',
        defaultValue: 0,
        // The keyed quantity is converted into the product's unit on save;
        // show the result (or the missing-rate warning) as the user types.
        hint: (formData) => (
          <UnitConversionHint
            quantity={formData.opening_stock}
            fromUnitId={formData.opening_stock_unit_id}
            toUnitId={formData.unit_id}
          />
        ),
      },
      { name: 'opening_stock_unit_id', label: 'Opening Stock Unit', type: 'select', optionsKey: 'units', emptyAsNull: true, placeholder: 'Product unit' },
      { name: 'site_id', label: 'Store At (Site)', type: 'select', optionsKey: 'sites', emptyAsNull: true, placeholder: 'Select site (required if opening stock > 0)', hint: (formData) => {
        if (Number(formData.opening_stock) <= 0) return null;
        return (
          <p className="text-xs text-gray-500">
            Required while this product has no stock yet. &ldquo;Stock&rdquo; is tracked per site and starts from this quantity.
          </p>
        );
      } },
      { name: 'status', label: 'Status', type: 'select', defaultValue: 'active', options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
    );
    return flds;
  }, [canSeePrices]);

  const renderItems = (products, { openModal, deleteItem, detailBasePath }) => {
    const categoryById = new Map(lookups.categories.map((c) => [c.id, c]));

    const filtered = products.filter((product) => {
      const { parentBucket, subName } = getPlacement(product, categoryById);
      if (filters.parent && parentBucket !== filters.parent) return false;
      if (filters.sub && subName !== filters.sub) return false;
      if (filters.supplier && String(product.supplier_id) !== filters.supplier) return false;
      if (filters.status && product.status !== filters.status) return false;
      if (filters.stock === 'out' && !isOutOfStock(product)) return false;
      if (filters.stock === 'low' && !isLowStock(product)) return false;
      if (filters.stock === 'in' && isOutOfStock(product)) return false;
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
          <div className="grid grid-cols-1 gap-3 rounded-lg border border-gray-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-6">
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
            <label className="block">
              <span className="mb-1 block text-xs font-medium text-gray-600">Stock</span>
              <select value={filters.stock} onChange={(e) => setFilter('stock', e.target.value)} className={selectClass}>
                <option value="">All</option>
                <option value="in">In stock</option>
                <option value="low">Low stock (at/below reorder)</option>
                <option value="out">Out of stock</option>
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
                        {group.direct.map((product) => renderCard(product, { openModal, deleteItem, detailBasePath, canSeePrices }))}
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
                              {sub.products.map((product) => renderCard(product, { openModal, deleteItem, detailBasePath, canSeePrices }))}
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
      module={MODULE}
      detailBasePath="/dashboard/products"
      searchPlaceholder="Search products or SKUs..."
      lookups={formLookups}
      columns={columns}
      fields={fields}
      fetchParams={fetchParams}
      renderItems={renderItems}
      onFieldChange={handleFieldChange}
      transformPayload={transformProductPayload}
      getInitialValues={getProductInitialValues}
    />
  );
}

const renderCard = (product, { openModal, deleteItem, detailBasePath, canSeePrices }) => (
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
      {canSeePrices && (
        <div className="flex items-center justify-between">
          <dt className="text-gray-500">Price</dt>
          <dd className="font-semibold text-gray-900"><Money value={product.selling_price} /></dd>
        </div>
      )}
      <ProductStockSection product={product} />
      <div className="flex items-center justify-between">
        <dt className="text-gray-500">Opening Stock</dt>
        <dd className="font-medium text-gray-900">{product.opening_stock ?? 0}</dd>
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
        <PermissionGate module={MODULE} action="update" subject="this product">
          <button type="button" className="rounded-md p-1.5 text-blue-600 hover:bg-blue-50 disabled:cursor-not-allowed disabled:text-gray-300 disabled:hover:bg-transparent" onClick={() => openModal(product)} title="Edit">
            <Edit2 className="h-4 w-4" />
          </button>
        </PermissionGate>
        <PermissionGate module={MODULE} action="delete" subject="this product">
          <button
            type="button"
            className="rounded-md p-1.5 text-red-600 hover:bg-red-50 disabled:cursor-not-allowed disabled:text-gray-300 disabled:hover:bg-transparent"
            onClick={() => {
              if (window.confirm(`Delete ${product.name}?`)) deleteItem(product.id);
            }}
            title="Delete"
          >
            <Trash2 className="h-4 w-4" />
          </button>
        </PermissionGate>
      </div>
    </div>
  </div>
);
