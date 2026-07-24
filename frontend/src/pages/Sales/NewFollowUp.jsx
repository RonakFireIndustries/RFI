import { useState, useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { ArrowLeft, Save } from 'lucide-react';
import { followUpService } from '../../services/followUpService';
import { useBuildingStore } from '../../store/buildingStore';
import { opportunityService } from '../../services/opportunityService';

const FOLLOWUP_TYPES = ['Call', 'Email', 'Meeting', 'Site Visit', 'Quotation Follow-up', 'Other'];

export default function NewFollowUp() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const { items: buildings, fetchItems: fetchBuildings } = useBuildingStore();
  const [opportunities, setOpportunities] = useState([]);
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState({
    building_id: searchParams.get('building_id') || '',
    opportunity_id: searchParams.get('opportunity_id') || '',
    reminder_date: new Date().toISOString().split('T')[0],
    reminder_time: '',
    type: 'Call',
    notes: '',
  });

  useEffect(() => {
    fetchBuildings();
    opportunityService.list({ status: 'Active' }).then(data => {
      if (Array.isArray(data)) setOpportunities(data);
    }).catch(() => { });
  }, [fetchBuildings]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    try {
      const payload = { ...form };
      if (!payload.building_id) delete payload.building_id;
      if (!payload.opportunity_id) delete payload.opportunity_id;
      if (!payload.reminder_time) delete payload.reminder_time;
      const fu = await followUpService.create(payload);
      navigate('/dashboard/follow-ups');
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
        <h1 className="text-2xl font-bold text-gray-900">New Follow-up</h1>
      </div>

      <form onSubmit={handleSubmit} className="bg-white rounded-lg border p-6 space-y-4">
        <div className="grid grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-color-white">Type *</label>
            <select required value={form.type} onChange={e => setForm({ ...form, type: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
              {FOLLOWUP_TYPES.map(t => <option key={t} value={t}>{t}</option>)}
            </select>
          </div>
          <div>
            <label className="block text-sm font-medium text-color-white">Building</label>
            <select value={form.building_id} onChange={e => setForm({ ...form, building_id: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
              <option value="">Select Building</option>
              {(buildings || []).map(b => <option key={b.id} value={b.id}>{b.name}</option>)}
            </select>
          </div>
        </div>

        <div>
          <label className="block text-sm font-medium text-color-white">Related Opportunity</label>
          <select value={form.opportunity_id} onChange={e => setForm({ ...form, opportunity_id: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            <option value="">None</option>
            {(opportunities || []).map(o => <option key={o.id} value={o.id}>{o.name} ({o.building?.name || 'No building'})</option>)}
          </select>
        </div>

        <div className="grid grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-color-white">Reminder Date *</label>
            <input type="date" required value={form.reminder_date} onChange={e => setForm({ ...form, reminder_date: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
          </div>
          <div>
            <label className="block text-sm font-medium text-color-white">Reminder Time</label>
            <input type="time" value={form.reminder_time} onChange={e => setForm({ ...form, reminder_time: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
          </div>
        </div>

        <div>
          <label className="block text-sm font-medium text-color-white">Notes</label>
          <textarea value={form.notes} onChange={e => setForm({ ...form, notes: e.target.value })} rows={3} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="What needs to be done..." />
        </div>

        <div className="flex justify-end gap-2 pt-4">
          <button type="button" onClick={() => navigate(-1)} className="px-4 py-2 border rounded-lg text-sm hover:bg-gray-50">Cancel</button>
          <button type="submit" disabled={saving} className="flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 disabled:opacity-50">
            <Save className="w-4 h-4" /> {saving ? 'Saving...' : 'Create Follow-up'}
          </button>
        </div>
      </form>
    </div>
  );
}
