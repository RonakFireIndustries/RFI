import { useState, useEffect, useMemo } from 'react';
import { useNavigate } from 'react-router-dom';
import { Plus, Search, Building2, ChevronRight, ChevronDown, MapPin, TrendingUp, ArrowRightLeft, CheckCircle2, Loader2 } from 'lucide-react';
import { useOpportunityStore } from '../../store/opportunityStore';
import { usePriceVisibility } from '../../hooks/usePriceVisibility';
import { buildingService } from '../../services/buildingService';
import PermissionGate from '../../components/Guards/PermissionGate';
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
  const { canSeePrices, maskedValue } = usePriceVisibility();
  const navigate = useNavigate();
  const { items: opportunities, loading, fetchItems } = useOpportunityStore();
  const [search, setSearch] = useState('');
  const [stageFilter, setStageFilter] = useState('');
  const [viewMode, setViewMode] = useState('list');
  const [expanded, setExpanded] = useState({});
  const [convertingId, setConvertingId] = useState(null);

  useEffect(() => {
    fetchItems({ search, stage: stageFilter });
  }, [fetchItems, search, stageFilter]);

  /**
   * Promote the opportunity's building into a working site.
   *
   * Mirrors the action on the Buildings list: the site is created on demand
   * from the building's survey data, and re-running it refreshes the site the
   * building already produced.
   */
  const handleConvertToSite = async (group) => {
    if (!group.id) return;

    const building = group.opportunities[0]?.building;
    const already = !!building?.converted_at;
    const prompt = already
      ? `Refresh the site "${building.converted_site_name || group.name}" from this building's current survey data?`
      : `Convert "${group.name}" into a working site?\n\nThis creates a site with the building's survey details, which then becomes available for attendance and stock movements. The building record stays as-is.`;

    if (!window.confirm(prompt)) return;

    setConvertingId(group.id);
    try {
      await buildingService.convertToSite(group.id);
      await fetchItems({ search, stage: stageFilter });
      alert(`"${group.name}" is now a working site.`);
    } catch (err) {
      alert(err.message || 'Failed to convert building to a site.');
    } finally {
      setConvertingId(null);
    }
  };

  const formatCurrency = (val) => {
    if (!canSeePrices) return maskedValue;
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
          converted_at: building?.converted_at || null,
          converted_site_name: building?.converted_site_name || null,
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
          className="px-3 py-2 border rounded-lg text-sm bg-white">
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
                        {group.converted_at && (
                          <span
                            title={`Converted to site "${group.converted_site_name}" on ${new Date(group.converted_at).toLocaleDateString()}`}
                            className="inline-flex items-center gap-1 mt-1.5 px-2 py-0.5 text-[11px] font-bold rounded-full bg-emerald-50 text-emerald-700"
                          >
                            <CheckCircle2 className="w-3 h-3" /> Working Site
                          </span>
                        )}
                      </div>
                    </div>
                    <ChevronDown className={`w-5 h-5 text-gray-400 transition-transform ${isOpen ? 'rotate-180' : ''}`} />
                  </div>
                  <div className="flex items-center gap-4 mt-3 text-xs text-gray-500">
                    <span className="flex items-center gap-1"><TrendingUp className="w-3 h-3" />{group.opportunities.length} {group.opportunities.length === 1 ? 'opportunity' : 'opportunities'}</span>
                    <span className="flex items-center gap-1"><TrendingUp className="w-3 h-3" />{formatCurrency(totalValue)}</span>
                    <span className="ml-auto">
                      <PermissionGate module="sites" action="create" subject="this building">
                        <button
                          type="button"
                          onClick={(e) => { e.stopPropagation(); handleConvertToSite(group); }}
                          disabled={!group.id || convertingId === group.id}
                          title={group.converted_at ? 'Refresh the site from this building' : 'Convert this building into a working site'}
                          className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold border border-gray-200 text-gray-600 hover:text-emerald-600 hover:border-emerald-200 hover:bg-emerald-50 transition-colors disabled:cursor-not-allowed disabled:text-gray-300 disabled:border-gray-100 disabled:hover:bg-transparent"
                        >
                          {convertingId === group.id
                            ? <Loader2 className="w-3 h-3 animate-spin" />
                            : (group.converted_at ? <CheckCircle2 className="w-3 h-3" /> : <ArrowRightLeft className="w-3 h-3" />)}
                          {group.converted_at ? 'Refresh Site' : 'Convert to Site'}
                        </button>
                      </PermissionGate>
                    </span>
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
