import { useState, useRef, useEffect, useCallback } from 'react';
import { createPortal } from 'react-dom';

export default function ProductSelect({ products, value, onChange, placeholder = 'Select Product...' }) {
  const [query, setQuery] = useState('');
  const [open, setOpen] = useState(false);
  const [pos, setPos] = useState({ top: 0, left: 0, width: 0 });
  const inputRef = useRef(null);
  const ref = useRef(null);
  const blurTimeout = useRef(null);
  const isCustom = useRef(false);

  const selected = value && products.find(p => p.id == value);

  useEffect(() => {
    if (selected) {
      setQuery(`${selected.sku} - ${selected.name}`);
      isCustom.current = false;
    } else if (value && typeof value === 'string') {
      setQuery(value);
      isCustom.current = true;
    } else {
      setQuery('');
      isCustom.current = false;
    }
  }, [value]);

  const close = useCallback(() => setOpen(false), []);

  useEffect(() => {
    const handleClick = (e) => {
      if (ref.current && !ref.current.contains(e.target)) close();
    };
    document.addEventListener('mousedown', handleClick);
    return () => document.removeEventListener('mousedown', handleClick);
  }, [close]);

  const updatePosition = () => {
    if (inputRef.current) {
      const rect = inputRef.current.getBoundingClientRect();
      setPos({ top: rect.bottom + 4, left: rect.left, width: rect.width });
    }
  };

  const filtered = query
    ? products.filter(p =>
        p.sku?.toLowerCase().includes(query.toLowerCase()) ||
        p.name?.toLowerCase().includes(query.toLowerCase()) ||
        p.product_code?.toLowerCase().includes(query.toLowerCase())
      )
    : products;

  const commitValue = useCallback((product) => {
    onChange({ product_id: product.id, custom_product_name: null });
    setQuery(`${product.sku} - ${product.name}`);
    isCustom.current = false;
    setOpen(false);
  }, [onChange]);

  const commitCustom = useCallback((name) => {
    if (!name.trim()) {
      onChange({ product_id: null, custom_product_name: null });
      return;
    }
    onChange({ product_id: null, custom_product_name: name.trim() });
    setQuery(name.trim());
    isCustom.current = true;
    setOpen(false);
  }, [onChange]);

  const tryAutoSelect = useCallback(() => {
    if (!query) return;
    const match = products.find(p =>
      p.sku?.toLowerCase() === query.toLowerCase() ||
      p.name?.toLowerCase() === query.toLowerCase() ||
      `${p.sku} - ${p.name}`.toLowerCase() === query.toLowerCase()
    );
    if (match) {
      commitValue(match);
      return true;
    }
    if (filtered.length === 1) {
      commitValue(filtered[0]);
      return true;
    }
    return false;
  }, [query, products, filtered, commitValue]);

  const handleBlur = () => {
    blurTimeout.current = setTimeout(() => {
      if (!tryAutoSelect() && query.trim()) {
        commitCustom(query);
      }
    }, 200);
  };

  useEffect(() => {
    return () => { if (blurTimeout.current) clearTimeout(blurTimeout.current); };
  }, []);

  return (
    <div ref={ref}>
      <input
        ref={inputRef}
        type="text"
        value={query}
        onChange={(e) => { setQuery(e.target.value); setOpen(true); updatePosition(); if (!e.target.value) onChange({ product_id: null, custom_product_name: null }); }}
        onFocus={() => { setOpen(true); updatePosition(); }}
        onClick={() => updatePosition()}
        onBlur={handleBlur}
        placeholder={placeholder}
        className="w-full bg-transparent border-0 border-b border-gray-200 focus:ring-0 focus:border-[#1a56db] font-semibold text-gray-900 p-0 pb-1"
        autoComplete="off"
      />
      {open && createPortal(
        <div
          style={{ position: 'fixed', top: pos.top, left: pos.left, width: pos.width }}
          className="z-[9999] bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto"
        >
          {filtered.length === 0 ? (
            <div className="px-3 py-2 text-sm text-gray-400">No products found.</div>
          ) : (
            filtered.map(p => (
              <div
                key={p.id}
                onMouseDown={() => { commitValue(p); }}
                className={`px-3 py-2 text-sm cursor-pointer hover:bg-blue-50 ${
                  p.id == value ? 'bg-blue-50 font-semibold text-blue-700' : 'text-gray-900'
                }`}
              >
                <span className="font-medium">{p.sku}</span> - {p.name}
              </div>
            ))
          )}
        </div>,
        document.body
      )}
    </div>
  );
}
