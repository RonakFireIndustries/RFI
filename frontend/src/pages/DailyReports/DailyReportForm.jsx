import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useDailyReportsStore } from '../../store/dailyReportStore';
import { useSiteStore } from '../../store/siteStore';

export default function DailyReportForm() {
  const navigate = useNavigate();
  const { createItem } = useDailyReportsStore();
  const { items: sites, fetchItems: fetchSites } = useSiteStore();
  const [loading, setLoading] = useState(false);

  const [formData, setFormData] = useState({
    site_id: '',
    date: new Date().toISOString().split('T')[0],
    hours_worked: 8,
    work_description: '',
    tasks_completed: '',
    issues_faced: '',
    materials_used: '',
    equipment_used: '',
    remarks: '',
  });

  useEffect(() => {
    fetchSites({ per_page: 100 });
  }, [fetchSites]);

  const handleSubmit = async (e, isSubmit) => {
    e.preventDefault();
    setLoading(true);
    try {
      const payload = {
        ...formData,
        site_id: formData.site_id || null,
        hours_worked: Number(formData.hours_worked),
        status: isSubmit ? 'Submitted' : 'Draft',
      };
      await createItem(payload);
      alert(`Report successfully ${isSubmit ? 'submitted' : 'saved as draft'}!`);
      navigate('/dashboard/daily-reports');
    } catch (err) {
      alert(`Failed to save report: ${err.message || err.response?.data?.message || 'Unknown error'}`);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="max-w-4xl mx-auto space-y-6 pb-12">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Submit Daily Progress Report</h1>
        <p className="mt-1 text-sm text-gray-500">Log your daily work activity and resource usage.</p>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-200">
        <form className="p-6 space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-bold text-gray-700 mb-1">Date</label>
              <input 
                type="date" 
                required
                value={formData.date}
                onChange={e => setFormData({...formData, date: e.target.value})}
                className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              />
            </div>
            <div>
              <label className="block text-sm font-bold text-gray-700 mb-1">Project Site</label>
              <select 
                required
                value={formData.site_id}
                onChange={e => setFormData({...formData, site_id: e.target.value})}
                className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              >
                <option value="">Select a Site</option>
                {sites.map(s => <option key={s.id} value={s.id}>{s.name} ({s.code})</option>)}
              </select>
            </div>
            <div>
              <label className="block text-sm font-bold text-gray-700 mb-1">Hours Worked</label>
              <input 
                type="number" 
                min="0"
                max="24"
                step="0.5"
                required
                value={formData.hours_worked}
                onChange={e => setFormData({...formData, hours_worked: e.target.value})}
                className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              />
            </div>
          </div>

          <div className="border-t border-gray-100 pt-6 space-y-6">
            <h3 className="text-lg font-bold text-gray-900">Work Details</h3>
            <div>
              <label className="block text-sm font-bold text-gray-700 mb-1">Work Description *</label>
              <textarea 
                required
                rows={3}
                placeholder="Briefly describe the work done today..."
                value={formData.work_description}
                onChange={e => setFormData({...formData, work_description: e.target.value})}
                className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              />
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block text-sm font-bold text-gray-700 mb-1">Tasks Completed</label>
                <textarea 
                  rows={2}
                  value={formData.tasks_completed}
                  onChange={e => setFormData({...formData, tasks_completed: e.target.value})}
                  className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                />
              </div>
              <div>
                <label className="block text-sm font-bold text-gray-700 mb-1">Issues Faced</label>
                <textarea 
                  rows={2}
                  placeholder="Any roadblocks?"
                  value={formData.issues_faced}
                  onChange={e => setFormData({...formData, issues_faced: e.target.value})}
                  className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          <div className="border-t border-gray-100 pt-6 space-y-6">
            <h3 className="text-lg font-bold text-gray-900">Resources & Materials</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block text-sm font-bold text-gray-700 mb-1">Materials Used</label>
                <textarea 
                  rows={2}
                  placeholder="Cement (50 bags), Steel (2 tons)..."
                  value={formData.materials_used}
                  onChange={e => setFormData({...formData, materials_used: e.target.value})}
                  className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                />
              </div>
              <div>
                <label className="block text-sm font-bold text-gray-700 mb-1">Equipment Used</label>
                <textarea 
                  rows={2}
                  placeholder="Excavator, Crane..."
                  value={formData.equipment_used}
                  onChange={e => setFormData({...formData, equipment_used: e.target.value})}
                  className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          <div className="flex justify-end gap-3 pt-6 border-t border-gray-100">
            <button 
              type="button"
              disabled={loading}
              onClick={(e) => handleSubmit(e, false)}
              className="px-6 py-2 rounded-lg font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 transition"
            >
              Save as Draft
            </button>
            <button 
              type="button"
              disabled={loading}
              onClick={(e) => handleSubmit(e, true)}
              className="px-6 py-2 rounded-lg font-bold text-white bg-blue-600 hover:bg-blue-700 transition shadow-sm"
            >
              {loading ? 'Submitting...' : 'Submit Report'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
