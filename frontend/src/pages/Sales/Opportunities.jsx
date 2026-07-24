import { useState, useEffect, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { Plus, Search, Filter, TrendingUp, Calendar, Building2, MoreVertical, Edit2, Trash2, Eye } from 'lucide-react';
import { useOpportunityStore } from '../../store/opportunityStore';
import { useBuildingStore } from '../../store/buildingStore';
import { format } from 'date-fns';

const STAGES = ['Prospect', 'Follow-Up', 'Quotation Sent', 'Negotiation', 'Won', 'Lost'];
const STAGE_COLORS = {
  'Prospect': 'bg-gray-100 text-gray-700 border-gray-300',
  'Follow-Up': 'bg-blue-100 text-blue-700 border-blue-300',
  'Quotation Sent': 'bg-yellow-100 text-yellow-700 border-yellow-300',
  'Negotiation': 'bg-orange-100 text-orange-700 border-orange-300',
  'Won': 'bg-green-100 text-green-700 border-green-300',
  'Lost': 'bg-red-100 text-red-700 border-red-300',
};

export default function Opportunities() {
  const navigate = useNavigate();
  const { items: opportunities, loading, fetchItems } = useOpportunityStore();
  const { items: buildings, fetchItems: fetchBuildings } = useBuildingStore();
  const [search, setSearch] = useState('');
  const [stageFilter, setStageFilter] = useState('');
  const [viewMode, setViewMode] = useState('list');

  useEffect(() => {
    fetchItems({ search, stage: stageFilter });
    fetchBuildings();
  }, [fetchItems, fetchBuildings, search, stageFilter]);

  const formatCurrency = (val) => {
    if (!val) return '-';
    return `₹${parseFloat(val).toLocaleString('en-IN')}`;
  };

  const groupedOpportunities = {};
  STAGES.forEach(s => { groupedOpportunities[s] = []; });
  (opportunities || []).forEach(opp => {
    if (groupedOpportunities[opp.stage]) groupedOpportunities[opp.stage].push(opp);
  });

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Opportunities</h1>
          <p className="text-sm text-gray-500">Manage your sales pipeline</p>
        </div>
        <button onClick={() => navigate('/dashboard/opportunities/new')} className="flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
          <Plus className="w-4 h-4" /> New Opportunity
        </button>
      </div>

      {/* Filters */}
      <div className="flex items-center gap-3">
        <div className="relative flex-1 max-w-md">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input type="text" placeholder="Search by building name..." value={search} onChange={e => setSearch(e.target.value)}
            className="w-full pl-10 pr-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500" />
        </div>
        <select value={stageFilter} onChange={e => setStageFilter(e.target.value)}
          className="px-3 py-2 border rounded-lg text-sm bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white">
          <option value="">All Stages</option>
          {STAGES.map(s => <option key={s} value={s}>{s}</option>)}
        </select>
        <div className="flex border rounded-lg overflow-hidden">
          <button onClick={() => setViewMode('list')} className={`px-3 py-2 text-sm ${viewMode === 'list' ? 'bg-red-600 text-white' : 'bg-white text-gray-700'}`}>List</button>
          <button onClick={() => setViewMode('pipeline')} className={`px-3 py-2 text-sm ${viewMode === 'pipeline' ? 'bg-red-600 text-white' : 'bg-white text-gray-700'}`}>Pipeline</button>
        </div>
      </div>

      {loading ? (
        <div className="flex items-center justify-center h-40"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600" /></div>
      ) : viewMode === 'list' ? (
        <div className="bg-white rounded-lg border overflow-hidden">
          <table className="w-full">
            <thead className="bg-gray-50 border-b">
              <tr>
                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Building</th>
                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stage</th>
                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Probability</th>
                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expected Close</th>
                <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {(opportunities || []).length === 0 ? (
                <tr><td colSpan={7} className="px-4 py-12 text-center text-gray-500">No opportunities found</td></tr>
              ) : (opportunities || []).map(opp => (
                <tr key={opp.id} className="hover:bg-gray-50">
                  <td className="px-4 py-3">
                    <button onClick={() => navigate(`/dashboard/opportunities/${opp.id}`)} className="text-sm font-medium text-red-600 hover:text-red-700">{opp.name}</button>
                  </td>
                  <td className="px-4 py-3 text-sm text-gray-600">{opp.building?.name || '-'}</td>
                  <td className="px-4 py-3">
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full border ${STAGE_COLORS[opp.stage] || ''}`}>{opp.stage}</span>
                  </td>
                  <td className="px-4 py-3 text-sm text-gray-900">{formatCurrency(opp.estimated_value)}</td>
                  <td className="px-4 py-3 text-sm text-gray-600">{opp.probability}%</td>
                  <td className="px-4 py-3 text-sm text-gray-600">{opp.expected_closing_date ? format(new Date(opp.expected_closing_date), 'dd MMM yyyy') : '-'}</td>
                  <td className="px-4 py-3 text-right">
                    <button onClick={() => navigate(`/dashboard/opportunities/${opp.id}`)} className="text-gray-400 hover:text-gray-600"><Eye className="w-4 h-4" /></button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      ) : (
        /* Pipeline View */
        <div className="grid grid-cols-6 gap-3 overflow-x-auto">
          {STAGES.map(stage => (
            <div key={stage} className="min-w-[200px]">
              <div className={`px-3 py-2 rounded-t-lg border-t-2 ${STAGE_COLORS[stage]?.split(' ').slice(1).join(' ') || 'border-gray-300'}`}>
                <div className="flex items-center justify-between">
                  <span className="text-sm font-semibold text-gray-800">{stage}</span>
                  <span className="text-xs bg-white/80 rounded-full px-2 py-0.5">{(groupedOpportunities[stage] || []).length}</span>
                </div>
              </div>
              <div className="space-y-2 mt-2">
                {(groupedOpportunities[stage] || []).map(opp => (
                  <div key={opp.id} onClick={() => navigate(`/dashboard/opportunities/${opp.id}`)} className="bg-white border rounded-lg p-3 cursor-pointer hover:shadow-sm transition-shadow">
                    <p className="text-sm font-medium text-gray-900 truncate">{opp.name}</p>
                    <p className="text-xs text-gray-500 mt-1">{opp.building?.name || 'No building'}</p>
                    <div className="flex items-center justify-between mt-2">
                      <span className="text-xs font-medium text-gray-700">{formatCurrency(opp.estimated_value)}</span>
                      <span className="text-xs text-gray-500">{opp.probability}%</span>
                    </div>
                    {opp.work_types?.length > 0 && (
                      <div className="flex flex-wrap gap-1 mt-2">
                        {opp.work_types.slice(0, 2).map((wt, i) => (
                          <span key={i} className="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">{wt.work_type}</span>
                        ))}
                      </div>
                    )}
                  </div>
                ))}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
