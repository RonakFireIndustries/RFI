import { useState, useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { ArrowLeft, Save } from 'lucide-react';
import { opportunityService } from '../../services/opportunityService';
import { useBuildingStore } from '../../store/buildingStore';

const STAGES = ['Prospect', 'Follow-Up', 'Quotation Sent', 'Negotiation', 'Won', 'Lost'];
const WORK_TYPES = ['Fire Fighting System', 'Fire Alarm System', 'Fire NOC Consultation', 'Fire Safety Audit', 'AMC', 'Others'];

export default function NewOpportunity() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const { items: buildings, fetchItems: fetchBuildings } = useBuildingStore();
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState({
    name: '',
    building_id: searchParams.get('building_id') || '',
    estimated_value: '',
    probability: 50,
    stage: 'Prospect',
    expected_closing_date: '',
    notes: '',
  });
  const [workTypes, setWorkTypes] = useState([]);
  const [newWorkType, setNewWorkType] = useState('');

  useEffect(() => {
    fetchBuildings();
  }, [fetchBuildings]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    try {
      const payload = { ...form, work_types: workTypes };
      if (!payload.building_id) delete payload.building_id;
      const opp = await opportunityService.create(payload);
      navigate(`/dashboard/opportunities/${opp.id}`);
    } catch (err) {
      console.error(err);
    } finally {
      setSaving(false);
    }
  };

  const addWorkType = () => {
    if (newWorkType && !workTypes.includes(newWorkType)) {
      setWorkTypes([...workTypes, newWorkType]);
      setNewWorkType('');
    }
  };

  return (
    <div className="max-w-2xl mx-auto space-y-6">
      <div className="flex items-center gap-3">
        <button onClick={() => navigate(-1)} className="p-2 hover:bg-gray-100 rounded-lg"><ArrowLeft className="w-5 h-5" /></button>
        <h1 className="text-2xl font-bold text-gray-900">New Opportunity</h1>
      </div>

      <form onSubmit={handleSubmit} className="bg-white rounded-lg border p-6 space-y-4">
        <div>
          <label className="block text-sm font-medium text-color-white">Name *</label>
          <input type="text" required value={form.name} onChange={e => setForm({ ...form, name: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-red-500" placeholder="e.g. Fire Alarm Installation - Green Valley" />
        </div>

        <div className="grid grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-color-white mb-1">Building</label>
            <select value={form.building_id} onChange={e => setForm({ ...form, building_id: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
              <option value="">Select Building</option>
              {(buildings || []).map(b => <option key={b.id} value={b.id}>{b.name}</option>)}
            </select>
          </div>
          <div>
            <label className="block text-sm font-medium text-color-white">Stage</label>
            <select value={form.stage} onChange={e => setForm({ ...form, stage: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
              {STAGES.map(s => <option key={s} value={s}>{s}</option>)}
            </select>
          </div>
        </div>

        <div className="grid grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-color-white">Estimated Value (₹)</label>
            <input type="number" value={form.estimated_value} onChange={e => setForm({ ...form, estimated_value: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0" />
          </div>
          <div>
            <label className="block text-sm font-medium text-color-white">Probability ({form.probability}%)</label>
            <input type="range" min="0" max="100" value={form.probability} onChange={e => setForm({ ...form, probability: parseInt(e.target.value) })} className="w-full mt-2" />
          </div>
        </div>

        <div>
          <label className="block text-sm font-medium text-color-white">Expected Closing Date</label>
          <input type="date" value={form.expected_closing_date} onChange={e => setForm({ ...form, expected_closing_date: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
        </div>

        <div>
          <label className="block text-sm font-medium text-color-white">Work Types</label>
          <div className="flex flex-wrap gap-2 mb-2">
            {workTypes.map((wt, i) => (
              <span key={i} className="inline-flex items-center gap-1 px-3 py-1 bg-red-50 text-red-700 rounded-full text-sm border border-red-200">
                {wt}
                <button type="button" onClick={() => setWorkTypes(workTypes.filter(t => t !== wt))} className="ml-1 text-red-500 hover:text-red-700">×</button>
              </span>
            ))}
          </div>
          <div className="flex gap-2">
            <select value={newWorkType} onChange={e => setNewWorkType(e.target.value)} className="flex-1 px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
              <option value="">Select work type</option>
              {WORK_TYPES.filter(wt => !workTypes.includes(wt)).map(wt => <option key={wt} value={wt}>{wt}</option>)}
            </select>
            <button type="button" onClick={addWorkType} className="px-4 py-2 border rounded-lg text-sm hover:bg-gray-50">Add</button>
          </div>
        </div>

        <div>
          <label className="block text-sm font-medium text-color-white">Notes</label>
          <textarea value={form.notes} onChange={e => setForm({ ...form, notes: e.target.value })} rows={3} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Additional notes..." />
        </div>

        <div className="flex justify-end gap-2 pt-4">
          <button type="button" onClick={() => navigate(-1)} className="px-4 py-2 border rounded-lg text-sm hover:bg-gray-50">Cancel</button>
          <button type="submit" disabled={saving} className="flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 disabled:opacity-50">
            <Save className="w-4 h-4" /> {saving ? 'Saving...' : 'Create Opportunity'}
          </button>
        </div>
      </form>
    </div>
  );
}
