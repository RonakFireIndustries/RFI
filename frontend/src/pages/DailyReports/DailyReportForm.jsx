import { useState, useEffect } from 'react';
import { useNavigate, useParams, useSearchParams } from 'react-router-dom';
import { AlertTriangle, ArrowLeft, Clock } from 'lucide-react';
import { useDailyReportsStore } from '../../store/dailyReportStore';
import { useSiteStore } from '../../store/siteStore';
import { useAuthStore } from '../../store/authStore';
import { employeeSiteService } from '../../services/employeeSiteService';
import { attendancesService } from '../../services/attendancesService';
import FormErrors from '../../components/Shared/FormErrors';
import {
  formatHours,
  formatClockTime,
  parseDurationToHours,
  todayLocalISO,
} from '../../utils/duration';

const FIELD_LABELS = {
  site_id: 'Project Site',
  date: 'Date',
  hours_worked: 'Hours Worked',
  work_description: 'Work Description',
  tasks_completed: 'Tasks Completed',
  issues_faced: 'Issues Faced',
  materials_used: 'Materials Used',
  equipment_used: 'Equipment Used',
  status: 'Status',
};

export default function DailyReportForm() {
  const navigate = useNavigate();
  const { id } = useParams();
  const [searchParams] = useSearchParams();
  const isEdit = Boolean(id);

  const { createItem, updateItem, fetchItem } = useDailyReportsStore();
  const { items: sites, fetchItems: fetchSites } = useSiteStore();
  const { user, permissions } = useAuthStore();

  const [loading, setLoading] = useState(false);
  const [loadingReport, setLoadingReport] = useState(isEdit);
  const [editReport, setEditReport] = useState(null);
  const [assignedSite, setAssignedSite] = useState(null);
  const [siteLoading, setSiteLoading] = useState(false);
  const [formErrors, setFormErrors] = useState({});

  // Hours Worked is auto-filled from attendance but stays editable; once the
  // user edits it we stop overwriting their value.
  const [hoursTouched, setHoursTouched] = useState(false);
  const [attendance, setAttendance] = useState(null);
  const [attendanceLoaded, setAttendanceLoaded] = useState(false);

  const employeeId = user?.employee?.id;
  const canViewAllSites = !!(user?.is_super_admin || permissions.includes('sites.view'));

  const [formData, setFormData] = useState({
    site_id: '',
    date: searchParams.get('date') || todayLocalISO(),
    hours_worked: '',
    work_description: '',
    tasks_completed: '',
    issues_faced: '',
    materials_used: '',
    equipment_used: '',
  });

  // Load the report being edited and pre-fill the form.
  useEffect(() => {
    if (!isEdit) return;
    let cancelled = false;
    setLoadingReport(true);
    fetchItem(id)
      .then((report) => {
        if (cancelled || !report) return;
        if (report.can_edit === false) {
          alert('You are not allowed to edit this report.');
          navigate('/dashboard/daily-reports');
          return;
        }
        setEditReport(report);
        setFormData({
          site_id: report.site_id ? String(report.site_id) : '',
          date: report.date || todayLocalISO(),
          hours_worked: formatHours(report.hours_worked),
          work_description: report.work_description || '',
          tasks_completed: report.tasks_completed || '',
          issues_faced: report.issues_faced || '',
          materials_used: report.materials_used || '',
          equipment_used: report.equipment_used || '',
        });
        // Keep the hours the employee originally logged.
        setHoursTouched(true);
      })
      .catch(() => {
        if (!cancelled) {
          alert('Unable to load this report. It may have been removed.');
          navigate('/dashboard/daily-reports');
        }
      })
      .finally(() => {
        if (!cancelled) setLoadingReport(false);
      });
    return () => { cancelled = true; };
  }, [isEdit, id, fetchItem, navigate]);

  // Resolve the site shown to employees who cannot pick any site.
  useEffect(() => {
    if (canViewAllSites) {
      fetchSites({ per_page: 100 });
      return;
    }
    if (!employeeId || isEdit) return;
    let cancelled = false;
    setSiteLoading(true);
    employeeSiteService.getCurrentSite(employeeId)
      .then((data) => {
        if (cancelled) return;
        const site = data?.site || (data?.id ? data : null);
        setAssignedSite(site || null);
        if (site?.id) setFormData((prev) => ({ ...prev, site_id: String(site.id) }));
      })
      .catch(() => {
        if (!cancelled) setAssignedSite(null);
      })
      .finally(() => {
        if (!cancelled) setSiteLoading(false);
      });
    return () => { cancelled = true; };
  }, [canViewAllSites, employeeId, isEdit, fetchSites]);

  // Fetch the employee's attendance for the report's date so Hours Worked can
  // be derived from check-in / check-out.
  useEffect(() => {
    if (!formData.date) return;
    if (!employeeId) {
      setAttendance(null);
      setAttendanceLoaded(true);
      return;
    }
    let cancelled = false;
    setAttendanceLoaded(false);
    attendancesService.myAttendance({ date: formData.date, per_page: 1 })
      .then((list) => {
        if (cancelled) return;
        setAttendance(Array.isArray(list) ? (list[0] || null) : null);
      })
      .catch(() => {
        if (!cancelled) setAttendance(null);
      })
      .finally(() => {
        if (!cancelled) setAttendanceLoaded(true);
      });
    return () => { cancelled = true; };
  }, [formData.date, employeeId]);

  const displaySite = isEdit ? (editReport?.site || assignedSite) : assignedSite;

  // Derive Hours Worked from the attendance record. Kept as a pure helper so it
  // can be recomputed at submit time (the "time the DPR is being submitted").
  const deriveAutoHours = (att, date) => {
    if (!att?.check_in) return { status: 'no-checkin' };

    const start = new Date(att.check_in);
    if (Number.isNaN(start.getTime())) return { status: 'no-checkin' };

    if (att.check_out) {
      const end = new Date(att.check_out);
      if (!Number.isNaN(end.getTime())) {
        const hours = Math.min(24, Math.max(0, (end - start) / 3600000));
        return { status: 'ok', hours, checkIn: start, checkOut: end };
      }
    }

    // Still checked in today: use the current time. A past day with no
    // check-out cannot be derived reliably, so ask the user to type it.
    if (date !== todayLocalISO()) {
      return { status: 'no-checkout', checkIn: start };
    }
    const hours = Math.min(24, Math.max(0, (Date.now() - start) / 3600000));
    return { status: 'ok', hours, checkIn: start, checkOut: null };
  };

  const auto = attendanceLoaded ? deriveAutoHours(attendance, formData.date) : { status: 'loading' };

  const hoursDisplay = hoursTouched
    ? formData.hours_worked
    : (auto.status === 'ok' ? formatHours(auto.hours) : formData.hours_worked);

  const reworkEntry = isEdit && editReport?.status === 'Rework Required'
    ? [...(editReport.histories || [])].reverse().find((h) => h.action === 'Rework Requested')
    : null;

  const handleSiteChange = (e) => {
    setFormData({ ...formData, site_id: e.target.value });
    if (!canViewAllSites) {
      const site = sites.find((s) => String(s.id) === e.target.value);
      setAssignedSite(site || null);
    }
  };

  const handleHoursChange = (e) => {
    setHoursTouched(true);
    setFormData({ ...formData, hours_worked: e.target.value });
  };

  const handleHoursBlur = () => {
    const parsed = parseDurationToHours(formData.hours_worked);
    if (Number.isFinite(parsed)) {
      setFormData((prev) => ({ ...prev, hours_worked: formatHours(parsed) }));
    }
  };

  const resetHoursToAuto = () => {
    setHoursTouched(false);
    setFormData((prev) => ({ ...prev, hours_worked: '' }));
  };

  const handleSubmit = async (e, isSubmit) => {
    e.preventDefault();
    if (!formData.site_id) {
      alert(canViewAllSites ? 'Please select a site.' : 'No site is currently assigned to you. Contact your administrator.');
      return;
    }

    const freshAuto = deriveAutoHours(attendance, formData.date);
    const hoursSource = hoursTouched
      ? formData.hours_worked
      : (freshAuto.status === 'ok' ? formatHours(freshAuto.hours) : formData.hours_worked);
    const hours = parseDurationToHours(hoursSource);

    if (!Number.isFinite(hours)) {
      setFormErrors({ hours_worked: ['Enter hours such as "3h 22m" or a number like 3.5.'] });
      return;
    }
    if (hours < 0 || hours > 24) {
      setFormErrors({ hours_worked: ['Hours worked must be between 0 and 24.'] });
      return;
    }

    setLoading(true);
    try {
      const payload = {
        site_id: formData.site_id || null,
        date: formData.date,
        hours_worked: Math.round(hours * 100) / 100,
        work_description: formData.work_description,
        tasks_completed: formData.tasks_completed,
        issues_faced: formData.issues_faced,
        materials_used: formData.materials_used,
        equipment_used: formData.equipment_used,
        status: isSubmit ? 'Submitted' : 'Draft',
      };

      if (isEdit) {
        await updateItem(id, payload);
      } else {
        await createItem(payload);
      }
      setFormErrors({});
      alert(
        isEdit
          ? (isSubmit ? 'Report resubmitted for approval!' : 'Draft saved.')
          : `Report successfully ${isSubmit ? 'submitted' : 'saved as draft'}!`
      );
      navigate('/dashboard/daily-reports');
    } catch (err) {
      const data = err.response?.data;
      if (data?.errors) {
        setFormErrors(data.errors);
      } else {
        setFormErrors({ message: data?.message || err.message || 'Something went wrong. Please try again.' });
      }
    } finally {
      setLoading(false);
    }
  };

  if (isEdit && loadingReport) {
    return <div className="p-8 text-center text-gray-500">Loading report...</div>;
  }

  return (
    <div className="max-w-4xl mx-auto space-y-6 pb-12">
      <div className="flex items-start gap-3">
        <button
          type="button"
          onClick={() => navigate(-1)}
          className="mt-0.5 p-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50"
        >
          <ArrowLeft className="w-5 h-5 text-gray-600" />
        </button>
        <div>
          <h1 className="text-2xl font-bold text-gray-900">
            {isEdit ? 'Edit Daily Progress Report' : 'Submit Daily Progress Report'}
          </h1>
          <p className="mt-1 text-sm text-gray-500">
            {isEdit
              ? 'Update the details and resubmit it for approval.'
              : 'Log your daily work activity and resource usage.'}
          </p>
        </div>
      </div>

      {reworkEntry && (
        <div className="rounded-xl border border-orange-300 bg-orange-50 px-4 py-3 text-sm text-orange-900">
          <div className="flex items-center gap-2 font-bold">
            <AlertTriangle className="w-4 h-4" />
            This report was sent back for rework
          </div>
          {reworkEntry.comments && (
            <p className="mt-1 italic">"{reworkEntry.comments}"</p>
          )}
          <p className="mt-1">Please make the requested changes and resubmit it.</p>
        </div>
      )}

      {attendanceLoaded && auto.status === 'no-checkin' && (
        <div className="rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800 flex items-start gap-2">
          <AlertTriangle className="w-4 h-4 mt-0.5 shrink-0" />
          <div>
            <span className="font-bold">Check-in not found.</span> You have not checked in on{' '}
            {new Date(formData.date).toLocaleDateString()}, so Hours Worked cannot be auto-calculated.
            Please enter it manually.
          </div>
        </div>
      )}

      <div className="bg-white rounded-xl shadow-sm border border-gray-200">
        <FormErrors errors={formErrors} fieldLabels={FIELD_LABELS} className="m-6 mb-0" />
        <form className="p-6 space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-bold text-gray-700 mb-1">Date</label>
              <input
                type="date"
                required
                value={formData.date}
                onChange={e => setFormData({ ...formData, date: e.target.value })}
                className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              />
            </div>
            <div>
              <label className="block text-sm font-bold text-gray-700 mb-1">Project Site</label>
              {canViewAllSites ? (
                <select
                  required
                  value={formData.site_id}
                  onChange={handleSiteChange}
                  className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                >
                  <option value="">Select a Site</option>
                  {sites.map(s => <option key={s.id} value={s.id}>{s.name}</option>)}
                </select>
              ) : siteLoading ? (
                <div className="rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500">Loading assigned site...</div>
              ) : displaySite ? (
                <div className="rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800">
                  {displaySite.name}
                </div>
              ) : (
                <div className="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                  No site assigned yet — contact your administrator.
                </div>
              )}
              {!canViewAllSites && displaySite && (
                <p className="mt-1 text-xs text-gray-500">Reports can only be filed for your currently assigned site.</p>
              )}
            </div>
            <div>
              <label className="block text-sm font-bold text-gray-700 mb-1">Hours Worked</label>
              <input
                type="text"
                required
                inputMode="decimal"
                placeholder="e.g. 3h 22m"
                value={hoursDisplay}
                onChange={handleHoursChange}
                onFocus={(e) => e.target.select()}
                onBlur={handleHoursBlur}
                className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              />
              {auto.status === 'ok' ? (
                <p className="mt-1 text-xs text-gray-500 flex items-center gap-1">
                  <Clock className="w-3 h-3" />
                  Auto from check-in {formatClockTime(auto.checkIn)}
                  {' → '}
                  {auto.checkOut ? formatClockTime(auto.checkOut) : 'now'}
                  {hoursTouched && (
                    <button type="button" onClick={resetHoursToAuto} className="ml-1 text-blue-600 hover:underline">
                      Reset to auto
                    </button>
                  )}
                </p>
              ) : auto.status === 'no-checkout' ? (
                <p className="mt-1 text-xs text-amber-700">
                  No check-out was recorded for that day — please enter the hours manually.
                </p>
              ) : null}
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
                onChange={e => setFormData({ ...formData, work_description: e.target.value })}
                className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              />
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block text-sm font-bold text-gray-700 mb-1">Tasks Completed</label>
                <textarea
                  rows={2}
                  value={formData.tasks_completed}
                  onChange={e => setFormData({ ...formData, tasks_completed: e.target.value })}
                  className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                />
              </div>
              <div>
                <label className="block text-sm font-bold text-gray-700 mb-1">Issues Faced</label>
                <textarea
                  rows={2}
                  placeholder="Any roadblocks?"
                  value={formData.issues_faced}
                  onChange={e => setFormData({ ...formData, issues_faced: e.target.value })}
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
                  onChange={e => setFormData({ ...formData, materials_used: e.target.value })}
                  className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                />
              </div>
              <div>
                <label className="block text-sm font-bold text-gray-700 mb-1">Equipment Used</label>
                <textarea
                  rows={2}
                  placeholder="Excavator, Crane..."
                  value={formData.equipment_used}
                  onChange={e => setFormData({ ...formData, equipment_used: e.target.value })}
                  className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          <div className="flex justify-end gap-3 pt-6 border-t border-gray-100">
            <button
              type="button"
              disabled={loading || (!canViewAllSites && !formData.site_id)}
              onClick={(e) => handleSubmit(e, false)}
              className="px-6 py-2 rounded-lg font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Save as Draft
            </button>
            <button
              type="button"
              disabled={loading || (!canViewAllSites && !formData.site_id)}
              onClick={(e) => handleSubmit(e, true)}
              className="px-6 py-2 rounded-lg font-bold text-white bg-blue-600 hover:bg-blue-700 transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {loading ? 'Submitting...' : (isEdit ? 'Resubmit for Approval' : 'Submit Report')}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
