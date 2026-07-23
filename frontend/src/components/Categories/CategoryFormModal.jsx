import { useState, useEffect } from 'react';
import { X, Save, Loader2 } from 'lucide-react';
import api from '../../services/api';

export default function CategoryFormModal({ isOpen, onClose, onSuccess, category = null, categories = [] }) {
  const [formData, setFormData] = useState({
    name: '',
    description: '',
    status: 'Active',
    parent_id: ''
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (category) {
      setFormData({
        name: category.name || '',
        description: category.description || '',
        status: category.status || 'Active',
        parent_id: category.parent_id || ''
      });
    } else {
      setFormData({
        name: '',
        description: '',
        status: 'Active',
        parent_id: ''
      });
    }
  }, [category]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
      const payload = { ...formData };
      if (!payload.parent_id) delete payload.parent_id;

      if (category) {
        await api.put(`/categories/${category.id}`, payload);
      } else {
        await api.post('/categories', payload);
      }
      onSuccess();
    } catch (err) {
      console.error("Error saving category:", err);
      setError(err.response?.data?.message || "Failed to save category. Please check your inputs.");
    } finally {
      setLoading(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-foreground/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div className="bg-card rounded-xl shadow-2xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div className="px-6 py-4 border-b border-border flex justify-between items-center bg-muted/50">
          <h2 className="text-xl font-bold text-foreground">{category ? 'Edit Category' : 'New Category'}</h2>
          <button 
            onClick={onClose}
            className="text-muted-foreground hover:text-foreground transition-colors p-2 hover:bg-muted rounded-full"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="p-6 space-y-5">
          {error && (
            <div className="p-3 bg-destructive/10 border border-destructive/20 text-destructive text-sm rounded-lg">
              {error}
            </div>
          )}

          <div>
            <label className="block text-sm font-semibold text-foreground mb-1">Category Name *</label>
            <input
              type="text"
              required
              className="w-full border border-input rounded-lg p-2.5 focus:ring-2 focus:ring-ring focus:border-ring transition-all outline-none"
              placeholder="e.g., Electronics"
              value={formData.name}
              onChange={(e) => setFormData({...formData, name: e.target.value})}
            />
          </div>

          <div>
            <label className="block text-sm font-semibold text-foreground mb-1">Parent Category (Optional)</label>
            <select
              className="w-full border border-input rounded-lg p-2.5 focus:ring-2 focus:ring-ring focus:border-ring transition-all outline-none bg-card"
              value={formData.parent_id}
              onChange={(e) => setFormData({...formData, parent_id: e.target.value})}
            >
              <option value="">None (Root Category)</option>
              {categories
                .filter((c, i, arr) => arr.findIndex(x => x.id === c.id) === i)
                .filter(c => c.id !== category?.id)
                .map(c => (
                  <option key={c.id} value={c.id}>{c.name}</option>
                ))}
            </select>
          </div>

          <div>
            <label className="block text-sm font-semibold text-foreground mb-1">Status</label>
            <select
              className="w-full border border-input rounded-lg p-2.5 focus:ring-2 focus:ring-ring focus:border-ring transition-all outline-none bg-card"
              value={formData.status}
              onChange={(e) => setFormData({...formData, status: e.target.value})}
            >
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>

          <div>
            <label className="block text-sm font-semibold text-foreground mb-1">Description</label>
            <textarea
              className="w-full border border-input rounded-lg p-2.5 focus:ring-2 focus:ring-ring focus:border-ring transition-all outline-none resize-none"
              rows="3"
              placeholder="Brief description of this category..."
              value={formData.description}
              onChange={(e) => setFormData({...formData, description: e.target.value})}
            ></textarea>
          </div>

          <div className="pt-4 flex justify-end space-x-3 border-t border-border">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 text-foreground bg-muted hover:bg-muted/80 rounded-lg text-sm font-medium transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={loading}
              className="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary/90 text-primary-foreground rounded-lg text-sm font-medium transition-colors disabled:opacity-70 disabled:cursor-not-allowed shadow-sm"
            >
              {loading ? <Loader2 className="w-4 h-4 mr-2 animate-spin" /> : <Save className="w-4 h-4 mr-2" />}
              {loading ? 'Saving...' : 'Save Category'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
