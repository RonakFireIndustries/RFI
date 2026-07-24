import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Plus, Search, Calendar, Building2, User, Camera, Mic, ChevronRight, Filter } from 'lucide-react';
import { useSiteVisitStore } from '../../store/siteVisitStore';
import { format } from 'date-fns';

export default function SiteVisits() {
  const navigate = useNavigate();
  const { items: visits, loading, fetchItems } = useSiteVisitStore();
  const [search, setSearch] = useState('');
  const [dateFrom, setDateFrom] = useState('');
  const [dateTo, setDateTo] = useState('');

  useEffect(() => {
    fetchItems({ search, date_from: dateFrom, date_to: dateTo });
  }, [fetchItems, search, dateFrom, dateTo]);

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
          {(visits || []).length === 0 ? (
            <div className="col-span-full text-center py-12 text-gray-500">No site visits found</div>
          ) : (visits || []).map(visit => (
            <div key={visit.id} onClick={() => navigate(`/dashboard/site-visits/${visit.id}`)} className="bg-white border rounded-lg p-4 cursor-pointer hover:shadow-sm transition-shadow">
              <div className="flex items-start justify-between">
                <div>
                  <p className="font-medium text-gray-900">{visit.building?.name || 'Unknown Building'}</p>
                  <p className="text-sm text-gray-500 mt-1">{visit.purpose || 'General Visit'}</p>
                </div>
                <ChevronRight className="w-5 h-5 text-gray-400" />
              </div>
              <div className="flex items-center gap-4 mt-3 text-xs text-gray-500">
                <span className="flex items-center gap-1"><Calendar className="w-3 h-3" />{format(new Date(visit.visit_date), 'dd MMM yyyy')}</span>
                <span className="flex items-center gap-1"><User className="w-3 h-3" />{visit.user?.name || 'Unknown'}</span>
              </div>
              <div className="flex items-center gap-3 mt-3">
                {visit.photos?.length > 0 && (
                  <span className="flex items-center gap-1 text-xs text-blue-600"><Camera className="w-3 h-3" />{visit.photos.length} photos</span>
                )}
                {visit.voice_notes?.length > 0 && (
                  <span className="flex items-center gap-1 text-xs text-purple-600"><Mic className="w-3 h-3" />{visit.voice_notes.length} voice notes</span>
                )}
              </div>
              {visit.next_followup_date && (
                <div className="mt-3 px-2 py-1 bg-yellow-50 border border-yellow-200 rounded text-xs text-yellow-700">
                  Follow-up: {format(new Date(visit.next_followup_date), 'dd MMM yyyy')}
                </div>
              )}
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
