import { useState, useEffect, useMemo } from 'react';
import { useNavigate } from 'react-router-dom';
import { Plus, Search, Building2, ChevronRight, ChevronDown, MapPin, TrendingUp } from 'lucide-react';
import { useOpportunityStore } from '../../store/opportunityStore';
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
  const [search, setSearch] = useState('');
  const [stageFilter, setStageFilter] = useState('');
  const [viewMode, setViewMode] = useState('list');
  const [expanded, setExpanded] = useState({});

  useEffect(() => {
    fetchItems({ search, stage: stageFilter });
  }, [fetchItems, search, stageFilter]);

  const formatCurrency = (val) => {
    if (!val) return '-';
    return `₹${parseFloat(val).toLocaleString('en-IN')}`;
  };

  const groups = useMemo(() => {
    const map = new Map();
    (opportunities || []).forEach(opp => {
      const building = opp.building;
      const key = building?.id ?? 'unknown';
      if (!map.has(key)) {
        map.set(key, {
          key,
          id: building?.id ?? null,
          name: building?.name || 'Unknown Building',
          address: [building?.address, building?.area, building?.city].filter(Boolean).join(', '),
          opportunities: [],
        });
      }
      map.get(key).opportunities.push(opp);
    });
    return Array.from(map.values())
      .map(group => ({
        ...group,
        opportunities: group.opportunities.sort((a, b) => new Date(b.expected_closing_date || 0) - new Date(a.expected_closing_date || 0)),
      }))
      .sort((a, b) => a.name.localeCompare(b.name));
  }, [opportunities]);

  const groupedOpportunities = {};
  STAGES.forEach(s => { groupedOpportunities[s] = []; });
  (opportunities || []).forEach(opp => {
    if (groupedOpportunities[opp.stage]) groupedOpportunities[opp.stage].push(opp);
  });

  const toggle = (key) => setExpanded(prev => ({ ...prev, [key]: !prev[key] }));

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
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {groups.length === 0 ? (
            <div className="col-span-full text-center py-12 text-gray-500">No opportunities found</div>
          ) : groups.map(group => {
            const totalValue = group.opportunities.reduce((sum, opp) => sum + (parseFloat(opp.estimated_value) || 0), 0);
            const isOpen = !!expanded[group.key];
            return (
              <div key={group.key} className="bg-white border rounded-lg overflow-hidden">
                <button onClick={() => toggle(group.key)} className="w-full text-left p-4 hover:bg-gray-50 transition-colors">
                  <div className="flex items-start justify-between">
                    <div className="flex items-start gap-3">
                      <div className="flex-shrink-0 w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center">
                        <Building2 className="w-5 h-5 text-red-600" />
                      </div>
                      <div>
                        <p className="font-medium text-gray-900">{group.name}</p>
                        <p className="text-sm text-gray-500 mt-0.5">{group.address || 'No address'}</p>
                      </div>
                    </div>
                    <ChevronDown className={`w-5 h-5 text-gray-400 transition-transform ${isOpen ? 'rotate-180' : ''}`} />
                  </div>
                  <div className="flex items-center gap-4 mt-3 text-xs text-gray-500">
                    <span className="flex items-center gap-1"><TrendingUp className="w-3 h-3" />{group.opportunities.length} {group.opportunities.length === 1 ? 'opportunity' : 'opportunities'}</span>
                    <span className="flex items-center gap-1"><TrendingUp className="w-3 h-3" />{formatCurrency(totalValue)}</span>
                  </div>
                </button>

                {isOpen && (
                  <div className="border-t bg-gray-50">
                    {group.opportunities.map(opp => (
                      <button key={opp.id} onClick={() => navigate(`/dashboard/opportunities/${opp.id}`)}
                        className="w-full text-left px-4 py-3 border-b last:border-b-0 hover:bg-gray-100 transition-colors">
                        <div className="flex items-center justify-between gap-3">
                          <div className="min-w-0">
                            <div className="flex items-center gap-2">
                              <p className="text-sm font-medium text-gray-900 truncate">{opp.name}</p>
                              <span className={`flex-shrink-0 inline-flex px-2 py-0.5 text-[11px] font-medium rounded-full border ${STAGE_COLORS[opp.stage] || ''}`}>{opp.stage}</span>
                            </div>
                            <p className="text-xs text-gray-500 mt-0.5">
                              {formatCurrency(opp.estimated_value)} • {opp.probability != null ? `${opp.probability}%` : ''} • {opp.expected_closing_date ? `Closes ${format(new Date(opp.expected_closing_date), 'dd MMM yyyy')}` : ''}
                            </p>
                            {opp.work_types?.length > 0 && (
                              <div className="flex flex-wrap gap-1 mt-1.5">
                                {opp.work_types.slice(0, 3).map((wt, i) => (
                                  <span key={i} className="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">{wt.work_type}</span>
                                ))}
                              </div>
                            )}
                          </div>
                          <ChevronRight className="w-4 h-4 text-gray-400 flex-shrink-0" />
                        </div>
                      </button>
                    ))}
                  </div>
                )}
              </div>
            );
          })}
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
