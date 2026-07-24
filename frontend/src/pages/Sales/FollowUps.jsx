import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Plus, Search, Calendar, CheckCircle, Clock, AlertTriangle, Building2, User, X } from 'lucide-react';
import { useFollowUpStore } from '../../store/followUpStore';
import { followUpService } from '../../services/followUpService';
import { format, isPast, isToday, isTomorrow, differenceInDays } from 'date-fns';

export default function FollowUps() {
  const navigate = useNavigate();
  const { items: followUps, loading, fetchItems } = useFollowUpStore();
  const [statusFilter, setStatusFilter] = useState('Pending');
  const [typeFilter, setTypeFilter] = useState('');

  useEffect(() => {
    fetchItems({ status: statusFilter, type: typeFilter });
  }, [fetchItems, statusFilter, typeFilter]);

  const handleMarkComplete = async (id) => {
    try {
      await followUpService.markComplete(id);
      fetchItems({ status: statusFilter, type: typeFilter });
    } catch (err) {
      console.error(err);
    }
  };

  const getDateBadge = (dateStr) => {
    const date = new Date(dateStr);
    if (isToday(date)) return { text: 'Today', class: 'bg-red-100 text-red-700' };
    if (isTomorrow(date)) return { text: 'Tomorrow', class: 'bg-yellow-100 text-yellow-700' };
    if (isPast(date)) return { text: 'Overdue', class: 'bg-red-100 text-red-700' };
    const days = differenceInDays(date, new Date());
    if (days <= 3) return { text: `${days} days`, class: 'bg-yellow-100 text-yellow-700' };
    return { text: format(date, 'dd MMM'), class: 'bg-gray-100 text-gray-600' };
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Follow-ups</h1>
          <p className="text-sm text-gray-500">Track your reminders and follow-ups</p>
        </div>
        <button onClick={() => navigate('/dashboard/follow-ups/new')} className="flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
          <Plus className="w-4 h-4" /> New Follow-up
        </button>
      </div>

      {/* Filters */}
      <div className="flex items-center gap-3">
        <div className="flex border rounded-lg overflow-hidden">
          {['Pending', 'Completed', 'All'].map(status => (
            <button key={status} onClick={() => setStatusFilter(status)}
              className={`px-4 py-2 text-sm ${statusFilter === status ? 'bg-red-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'}`}>
              {status}
            </button>
          ))}
        </div>
        <select value={typeFilter} onChange={e => setTypeFilter(e.target.value)} className="px-3 py-2 border rounded-lg text-sm bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white">
          <option value="">All Types</option>
          <option value="Call">Call</option>
          <option value="Email">Email</option>
          <option value="Meeting">Meeting</option>
          <option value="Site Visit">Site Visit</option>
          <option value="Quotation Follow-up">Quotation Follow-up</option>
        </select>
      </div>

      {loading ? (
        <div className="flex items-center justify-center h-40"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600" /></div>
      ) : (followUps || []).length === 0 ? (
        <div className="text-center py-12 bg-white border rounded-lg text-gray-500">
          <Clock className="w-12 h-12 mx-auto text-gray-300 mb-3" />
          <p>No follow-ups found</p>
        </div>
      ) : (
        <div className="space-y-3">
          {(followUps || []).map(fu => {
            const dateBadge = getDateBadge(fu.reminder_date);
            const isOverdue = isPast(new Date(fu.reminder_date)) && fu.status === 'Pending';
            return (
              <div key={fu.id} className={`flex items-center gap-4 p-4 bg-white border rounded-lg ${isOverdue ? 'border-red-200 bg-red-50/50' : ''}`}>
                <div className="flex-shrink-0">
                  <span className={`inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full ${dateBadge.class}`}>
                    {isOverdue && <AlertTriangle className="w-3 h-3" />}
                    {dateBadge.text}
                  </span>
                </div>
                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-2">
                    <p className="text-sm font-medium text-gray-900">{fu.type}</p>
                    {fu.reminder_time && <span className="text-xs text-gray-400">{fu.reminder_time}</span>}
                  </div>
                  <div className="flex items-center gap-3 mt-1 text-xs text-gray-500">
                    {fu.building && <span className="flex items-center gap-1"><Building2 className="w-3 h-3" />{fu.building.name}</span>}
                    {fu.opportunity && <span className="text-blue-600">{fu.opportunity.name}</span>}
                  </div>
                  {fu.notes && <p className="text-xs text-gray-500 mt-1 truncate">{fu.notes}</p>}
                </div>
                <div className="flex items-center gap-2">
                  {fu.status === 'Pending' && (
                    <button onClick={() => handleMarkComplete(fu.id)} className="flex items-center gap-1 px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs hover:bg-green-700">
                      <CheckCircle className="w-3 h-3" /> Complete
                    </button>
                  )}
                  {fu.status === 'Completed' && (
                    <span className="flex items-center gap-1 px-3 py-1.5 bg-green-100 text-green-700 rounded-lg text-xs">
                      <CheckCircle className="w-3 h-3" /> Done
                    </span>
                  )}
                </div>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}
