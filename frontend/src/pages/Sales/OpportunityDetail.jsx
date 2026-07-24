import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Edit2, Save, X, Plus, Trash2, Calendar, Building2, DollarSign, Briefcase, FileText } from 'lucide-react';
import { opportunityService } from '../../services/opportunityService';
import { buildingDetailService } from '../../services/buildingDetailService';
import { useBuildingStore } from '../../store/buildingStore';
import { format } from 'date-fns';

const STAGES = ['Prospect', 'Follow-Up', 'Quotation Sent', 'Negotiation', 'Won', 'Lost'];
const WORK_TYPES = ['Fire Fighting System', 'Fire Alarm System', 'Fire NOC Consultation', 'Fire Safety Audit', 'AMC', 'Others'];

export default function OpportunityDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { items: buildings, fetchItems: fetchBuildings } = useBuildingStore();
  const [opp, setOpp] = useState(null);
  const [loading, setLoading] = useState(true);
  const [editing, setEditing] = useState(false);
  const [form, setForm] = useState({});
  const [workTypes, setWorkTypes] = useState([]);
  const [newWorkType, setNewWorkType] = useState('');

  useEffect(() => {
    loadOpportunity();
    fetchBuildings();
  }, [id]);

  const loadOpportunity = async () => {
    try {
      const data = await opportunityService.get(id);
      setOpp(data);
      setForm({
        name: data.name,
        building_id: data.building_id,
        estimated_value: data.estimated_value || '',
        probability: data.probability || 50,
        stage: data.stage,
        expected_closing_date: data.expected_closing_date || '',
        notes: data.notes || '',
        actual_final_value: data.actual_final_value || '',
        project_start_date: data.project_start_date || '',
        lost_reason: data.lost_reason || '',
      });
      setWorkTypes(data.work_types?.map(wt => wt.work_type) || []);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleSave = async () => {
    try {
      const payload = { ...form, work_types: workTypes };
      const updated = await opportunityService.update(id, payload);
      setOpp(updated);
      setEditing(false);
    } catch (err) {
      console.error(err);
    }
  };

  const addWorkType = () => {
    if (newWorkType && !workTypes.includes(newWorkType)) {
      setWorkTypes([...workTypes, newWorkType]);
      setNewWorkType('');
    }
  };

  const removeWorkType = (wt) => {
    setWorkTypes(workTypes.filter(t => t !== wt));
  };

  if (loading) {
    return <div className="flex items-center justify-center h-64"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600" /></div>;
  }

  if (!opp) {
    return <div className="text-center py-12 text-gray-500">Opportunity not found</div>;
  }

  const formatCurrency = (val) => val ? `₹${parseFloat(val).toLocaleString('en-IN')}` : '-';

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <button onClick={() => navigate('/dashboard/opportunities')} className="p-2 hover:bg-gray-100 rounded-lg"><ArrowLeft className="w-5 h-5" /></button>
          <div>
            <h1 className="text-2xl font-bold text-gray-900">{opp.name}</h1>
            <p className="text-sm text-gray-500">{opp.building?.name || 'No building assigned'}</p>
          </div>
        </div>
        <div className="flex gap-2">
          {editing ? (
            <>
              <button onClick={handleSave} className="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm"><Save className="w-4 h-4" /> Save</button>
              <button onClick={() => setEditing(false)} className="flex items-center gap-2 px-4 py-2 border rounded-lg text-sm hover:bg-gray-50"><X className="w-4 h-4" /> Cancel</button>
            </>
          ) : (
            <button onClick={() => setEditing(true)} className="flex items-center gap-2 px-4 py-2 border rounded-lg text-sm hover:bg-gray-50"><Edit2 className="w-4 h-4" /> Edit</button>
          )}
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Main Info */}
        <div className="lg:col-span-2 space-y-6">
          <div className="bg-white rounded-lg border p-6">
            <h2 className="text-lg font-semibold text-gray-900 mb-4">Opportunity Details</h2>
            {editing ? (
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-color-white">Name</label>
                  <input type="text" value={form.name} onChange={e => setForm({ ...form, name: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-color-white">Building</label>
                  <select value={form.building_id || ''} onChange={e => setForm({ ...form, building_id: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
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
                <div>
                  <label className="block text-sm font-medium text-color-white">Estimated Value (₹)</label>
                  <input type="number" value={form.estimated_value} onChange={e => setForm({ ...form, estimated_value: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-color-white">Probability ({form.probability}%)</label>
                  <input type="range" min="0" max="100" value={form.probability} onChange={e => setForm({ ...form, probability: parseInt(e.target.value) })} className="w-full" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-color-white">Expected Closing Date</label>
                  <input type="date" value={form.expected_closing_date} onChange={e => setForm({ ...form, expected_closing_date: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                </div>
                <div className="col-span-2">
                  <label className="block text-sm font-medium text-color-white">Notes</label>
                  <textarea value={form.notes} onChange={e => setForm({ ...form, notes: e.target.value })} rows={3} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                </div>

                {(form.stage === 'Won' || form.stage === 'Lost') && (
                  <>
                    {form.stage === 'Won' && (
                      <>
                        <div>
                          <label className="block text-sm font-medium text-color-white">Actual Final Value (₹)</label>
                          <input type="number" value={form.actual_final_value} onChange={e => setForm({ ...form, actual_final_value: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                        </div>
                        <div>
                          <label className="block text-sm font-medium text-color-white">Project Start Date</label>
                          <input type="date" value={form.project_start_date} onChange={e => setForm({ ...form, project_start_date: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                        </div>
                      </>
                    )}
                    {form.stage === 'Lost' && (
                      <div className="col-span-2">
                        <label className="block text-sm font-medium text-color-white">Lost Reason</label>
                        <textarea value={form.lost_reason} onChange={e => setForm({ ...form, lost_reason: e.target.value })} rows={2} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                      </div>
                    )}
                  </>
                )}
              </div>
            ) : (
              <div className="grid grid-cols-2 gap-4">
                <InfoItem label="Stage" value={<span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStageBadgeClass(opp.stage)}`}>{opp.stage}</span>} />
                <InfoItem label="Estimated Value" value={formatCurrency(opp.estimated_value)} />
                <InfoItem label="Probability" value={`${opp.probability}%`} />
                <InfoItem label="Expected Closing" value={opp.expected_closing_date ? format(new Date(opp.expected_closing_date), 'dd MMM yyyy') : '-'} />
                <InfoItem label="Actual Value" value={formatCurrency(opp.actual_final_value)} />
                <InfoItem label="Project Start" value={opp.project_start_date ? format(new Date(opp.project_start_date), 'dd MMM yyyy') : '-'} />
                {opp.notes && <div className="col-span-2"><p className="text-sm text-gray-500">Notes</p><p className="text-sm text-gray-900 mt-1">{opp.notes}</p></div>}
              </div>
            )}
          </div>

          {/* Work Types */}
          <div className="bg-white rounded-lg border p-6">
            <h2 className="text-lg font-semibold text-gray-900 mb-4">Work Types</h2>
            <div className="flex flex-wrap gap-2">
              {workTypes.map((wt, i) => (
                <span key={i} className="inline-flex items-center gap-1 px-3 py-1 bg-red-50 text-red-700 rounded-full text-sm border border-red-200">
                  {wt}
                  {editing && <button onClick={() => removeWorkType(wt)} className="ml-1 text-red-500 hover:text-red-700"><X className="w-3 h-3" /></button>}
                </span>
              ))}
            </div>
            {editing && (
              <div className="flex gap-2 mt-3">
                <select value={newWorkType} onChange={e => setNewWorkType(e.target.value)} className="flex-1 px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                  <option value="">Select work type</option>
                  {WORK_TYPES.filter(wt => !workTypes.includes(wt)).map(wt => <option key={wt} value={wt}>{wt}</option>)}
                </select>
                <button onClick={addWorkType} className="px-3 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700"><Plus className="w-4 h-4" /></button>
              </div>
            )}
          </div>
        </div>

        {/* Sidebar */}
        <div className="space-y-6">
          <div className="bg-white rounded-lg border p-6">
            <h3 className="text-sm font-semibold text-gray-900 mb-3">Quick Actions</h3>
            <div className="space-y-2">
              <button onClick={() => navigate(`/dashboard/site-visits/new?building_id=${opp.building_id}&opportunity_id=${opp.id}`)} className="w-full flex items-center gap-2 px-3 py-2 text-sm border rounded-lg hover:bg-gray-50">
                <Calendar className="w-4 h-4" /> Schedule Site Visit
              </button>
              <button onClick={() => navigate(`/dashboard/follow-ups/new?opportunity_id=${opp.id}`)} className="w-full flex items-center gap-2 px-3 py-2 text-sm border rounded-lg hover:bg-gray-50">
                <Plus className="w-4 h-4" /> Add Follow-up
              </button>
              <button onClick={() => navigate(`/dashboard/reports/building/${opp.building_id}`)} className="w-full flex items-center gap-2 px-3 py-2 text-sm border rounded-lg hover:bg-gray-50">
                <FileText className="w-4 h-4" /> Building Report
              </button>
            </div>
          </div>

          <div className="bg-white rounded-lg border p-6">
            <h3 className="text-sm font-semibold text-gray-900 mb-3">Building Info</h3>
            {opp.building ? (
              <div className="space-y-2">
                <button onClick={() => navigate(`/dashboard/buildings/${opp.building.id}`)} className="text-sm text-red-600 hover:text-red-700 font-medium">{opp.building.name}</button>
                <p className="text-xs text-gray-500">{opp.building.address || ''} {opp.building.city || ''}</p>
              </div>
            ) : (
              <p className="text-sm text-gray-500">No building assigned</p>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

function InfoItem({ label, value }) {
  return (
    <div>
      <p className="text-sm text-gray-500">{label}</p>
      <p className="text-sm text-gray-900 mt-1">{value}</p>
    </div>
  );
}

function getStageBadgeClass(stage) {
  const map = {
    'Prospect': 'bg-gray-100 text-gray-700',
    'Follow-Up': 'bg-blue-100 text-blue-700',
    'Quotation Sent': 'bg-yellow-100 text-yellow-700',
    'Negotiation': 'bg-orange-100 text-orange-700',
    'Won': 'bg-green-100 text-green-700',
    'Lost': 'bg-red-100 text-red-700',
  };
  return map[stage] || 'bg-gray-100 text-gray-700';
}
