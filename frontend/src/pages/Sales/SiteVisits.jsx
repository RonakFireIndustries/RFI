import { useState, useEffect, useMemo } from 'react';
import { useNavigate } from 'react-router-dom';
import { Plus, Search, Calendar, Building2, User, Camera, Mic, ChevronRight, ChevronDown, MapPin } from 'lucide-react';
import { useSiteVisitStore } from '../../store/siteVisitStore';
import { format } from 'date-fns';

export default function SiteVisits() {
  const navigate = useNavigate();
  const { items: visits, loading, fetchItems } = useSiteVisitStore();
  const [search, setSearch] = useState('');
  const [dateFrom, setDateFrom] = useState('');
  const [dateTo, setDateTo] = useState('');
  const [expanded, setExpanded] = useState({});

  useEffect(() => {
    fetchItems({ search, date_from: dateFrom, date_to: dateTo });
  }, [fetchItems, search, dateFrom, dateTo]);

  const groups = useMemo(() => {
    const map = new Map();
    (visits || []).forEach(visit => {
      const building = visit.building;
      const key = building?.id ?? 'unknown';
      if (!map.has(key)) {
        map.set(key, {
          key,
          id: building?.id ?? null,
          name: building?.name || 'Unknown Building',
          address: [building?.address, building?.area, building?.city].filter(Boolean).join(', '),
          visits: [],
        });
      }
      map.get(key).visits.push(visit);
    });
    return Array.from(map.values())
      .map(group => ({
        ...group,
        visits: group.visits.sort((a, b) => new Date(b.visit_date) - new Date(a.visit_date)),
      }))
      .sort((a, b) => new Date(b.visits[0].visit_date) - new Date(a.visits[0].visit_date));
  }, [visits]);

  const toggle = (key) => setExpanded(prev => ({ ...prev, [key]: !prev[key] }));

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Site Visits</h1>
          <p className="text-sm text-gray-500">Track and manage your site visits</p>
        </div>
        <button onClick={() => navigate('/dashboard/site-visits/new')} className="flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
          <Plus className="w-4 h-4" /> New Site Visit
        </button>
      </div>

      {/* Filters */}
      <div className="flex items-center gap-3">
        <div className="relative flex-1 max-w-md">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input type="text" placeholder="Search site visits..." value={search} onChange={e => setSearch(e.target.value)}
            className="w-full pl-10 pr-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-red-500" />
        </div>
        <div className="flex items-center gap-2">
          <Calendar className="w-4 h-4 text-gray-400" />
          <input type="date" value={dateFrom} onChange={e => setDateFrom(e.target.value)} className="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="From" />
          <span className="text-gray-400">-</span>
          <input type="date" value={dateTo} onChange={e => setDateTo(e.target.value)} className="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="To" />
        </div>
      </div>

      {loading ? (
        <div className="flex items-center justify-center h-40"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600" /></div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {groups.length === 0 ? (
            <div className="col-span-full text-center py-12 text-gray-500">No site visits found</div>
          ) : groups.map(group => {
            const totalPhotos = group.visits.reduce((sum, v) => sum + (v.photos?.length || 0), 0);
            const totalVoiceNotes = group.visits.reduce((sum, v) => sum + (v.voice_notes?.length || 0), 0);
            const isOpen = !!expanded[group.key];
            const latest = group.visits[0];
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
                    <span className="flex items-center gap-1"><Calendar className="w-3 h-3" />{group.visits.length} {group.visits.length === 1 ? 'visit' : 'visits'}</span>
                    <span className="flex items-center gap-1"><Calendar className="w-3 h-3" />Latest {format(new Date(latest.visit_date), 'dd MMM yyyy')}</span>
                  </div>
                  <div className="flex items-center gap-3 mt-2">
                    {totalPhotos > 0 && (
                      <span className="flex items-center gap-1 text-xs text-blue-600"><Camera className="w-3 h-3" />{totalPhotos} photos</span>
                    )}
                    {totalVoiceNotes > 0 && (
                      <span className="flex items-center gap-1 text-xs text-purple-600"><Mic className="w-3 h-3" />{totalVoiceNotes} voice notes</span>
                    )}
                  </div>
                </button>

                {isOpen && (
                  <div className="border-t bg-gray-50">
                    {group.visits.map(visit => (
                      <button key={visit.id} onClick={() => navigate(`/dashboard/site-visits/${visit.id}`)}
                        className="w-full text-left px-4 py-3 border-b last:border-b-0 hover:bg-gray-100 transition-colors">
                        <div className="flex items-center justify-between gap-3">
                          <div className="min-w-0">
                            <div className="flex items-center gap-2">
                              <p className="text-sm font-medium text-gray-900">{visit.purpose || 'General Visit'}</p>
                              {visit.next_followup_date && (
                                <span className="px-2 py-0.5 bg-yellow-50 border border-yellow-200 rounded text-[11px] text-yellow-700">
                                  Follow-up {format(new Date(visit.next_followup_date), 'dd MMM')}
                                </span>
                              )}
                            </div>
                            <p className="text-xs text-gray-500 mt-0.5">
                              {format(new Date(visit.visit_date), 'dd MMM yyyy')} • {visit.user?.name || 'Unknown'}
                            </p>
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
      )}
    </div>
  );
}
