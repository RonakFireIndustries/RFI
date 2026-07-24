import { useState, useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { ArrowLeft, Save } from 'lucide-react';
import { siteVisitService } from '../../services/siteVisitService';
import { useBuildingStore } from '../../store/buildingStore';

export default function NewSiteVisit() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const { items: buildings, fetchItems: fetchBuildings } = useBuildingStore();
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState({
    building_id: searchParams.get('building_id') || '',
    visit_date: new Date().toISOString().split('T')[0],
    purpose: '',
    discussion_notes: '',
    next_followup_date: '',
  });

  useEffect(() => {
    fetchBuildings();
  }, [fetchBuildings]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    try {
      const payload = { ...form };
      if (!payload.building_id) delete payload.building_id;
      if (!payload.next_followup_date) delete payload.next_followup_date;
      const visit = await siteVisitService.create(payload);
      navigate(`/dashboard/site-visits/${visit.id}`);
    } catch (err) {
      console.error(err);
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="max-w-2xl mx-auto space-y-6">
      <div className="flex items-center gap-3">
        <button onClick={() => navigate(-1)} className="p-2 hover:bg-gray-100 rounded-lg"><ArrowLeft className="w-5 h-5" /></button>
        <h1 className="text-2xl font-bold text-gray-900">New Site Visit</h1>
      </div>

      <form onSubmit={handleSubmit} className="bg-white rounded-lg border p-6 space-y-4">
        <div className="grid grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-color-white">Building *</label>
            <select required value={form.building_id} onChange={e => setForm({ ...form, building_id: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
              <option value="">Select Building</option>
              {(buildings || []).map(b => <option key={b.id} value={b.id}>{b.name}</option>)}
            </select>
          </div>
          <div>
            <label className="block text-sm font-medium text-color-white">Visit Date *</label>
            <input type="date" required value={form.visit_date} onChange={e => setForm({ ...form, visit_date: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
          </div>
        </div>

        <div>
          <label className="block text-sm font-medium text-color-white">Purpose</label>
          <input type="text" value={form.purpose} onChange={e => setForm({ ...form, purpose: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g. Initial survey, Follow-up meeting" />
        </div>

        <div>
          <label className="block text-sm font-medium text-color-white">Discussion Notes</label>
          <textarea value={form.discussion_notes} onChange={e => setForm({ ...form, discussion_notes: e.target.value })} rows={6} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Document key points discussed during the visit..." />
        </div>

        <div>
          <label className="block text-sm font-medium text-color-white">Next Follow-up Date</label>
          <input type="date" value={form.next_followup_date} onChange={e => setForm({ ...form, next_followup_date: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
        </div>

        <div className="flex justify-end gap-2 pt-4">
          <button type="button" onClick={() => navigate(-1)} className="px-4 py-2 border rounded-lg text-sm hover:bg-gray-50">Cancel</button>
          <button type="submit" disabled={saving} className="flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 disabled:opacity-50">
            <Save className="w-4 h-4" /> {saving ? 'Saving...' : 'Create Site Visit'}
          </button>
        </div>
      </form>
    </div>
  );
}
