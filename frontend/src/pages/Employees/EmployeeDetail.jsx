import React, { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { useEmployeesStore } from '../../store/employeesStore';
import { useDocumentStore } from '../../store/documentStore';
import { useEmployeeSiteStore } from '../../store/employeeSiteStore';
import { useSiteStore } from '../../store/siteStore';
import { useAttendanceStore } from '../../store/attendanceStore';
import { useDailyReportsStore } from '../../store/dailyReportStore';
import DataTable from '../../components/Shared/DataTable';
import EmployeeDocumentsPage from './documents/EmployeeDocumentsPage';
import DocumentHistory from './documents/DocumentHistory';
import { 
  MapPin, Plus, ArrowLeftRight, Trash2, Calendar, User, 
  MessageSquare, FileText, CheckCircle2, AlertCircle, X, ShieldAlert 
} from 'lucide-react';

const valueAt = (item, path) => path.split('.').reduce((value, key) => value?.[key], item);

const simpleColumns = [
  { header: 'Date', accessorKey: 'report_date' },
  { header: 'Status/Type', accessorKey: 'status' },
  { header: 'Notes', accessorKey: 'content' },
];

export default function EmployeeDetail() {
  const { id } = useParams();
  const { selected, loading: employeeLoading, fetchItem, clearSelected } = useEmployeesStore();
  const { documents } = useDocumentStore();
  const { items: attendances, fetchItems: fetchAttendances, loading: attendancesLoading } = useAttendanceStore();
  
  // Site Store & Employee Site Store
  const { 
    assignments, 
    history: siteHistory, 
    loading: sitesLoading, 
    fetchAssignments, 
    fetchHistory, 
    assignEmployee, 
    transferEmployee, 
    removeEmployee 
  } = useEmployeeSiteStore();

  const { items: allSites, fetchItems: fetchAllSites } = useSiteStore();
  const { items: dailyReports, fetchItems: fetchDailyReports, loading: dailyReportsLoading } = useDailyReportsStore();

  const [activeTab, setActiveTab] = useState('profile');

  // Modals for site allocation
  const [isAssignModalOpen, setIsAssignModalOpen] = useState(false);
  const [isTransferModalOpen, setIsTransferModalOpen] = useState(false);
  const [isRemoveModalOpen, setIsRemoveModalOpen] = useState(false);

  // Form states
  const [selectedSiteId, setSelectedSiteId] = useState('');
  const [selectedNewSiteId, setSelectedNewSiteId] = useState('');
  const [selectedCurrentSiteId, setSelectedCurrentSiteId] = useState('');
  const [role, setRole] = useState('');
  const [remarks, setRemarks] = useState('');
  const [errors, setErrors] = useState({});

  useEffect(() => {
    fetchItem(id);
    fetchAssignments(id);
    fetchHistory(id);
    fetchAllSites({ status: 'Active', per_page: 100 });
    fetchAttendances({ employee_id: id });
    fetchDailyReports({ employee_id: id });
    return () => clearSelected();
  }, [clearSelected, fetchItem, id, fetchAssignments, fetchHistory, fetchAllSites, fetchAttendances, fetchDailyReports]);

  if (employeeLoading || !selected) {
    return <div className="rounded-lg border border-gray-200 bg-white p-8 text-center text-sm text-gray-500">Loading detail...</div>;
  }

  const sections = [
    { title: 'Leaves', path: 'leaves', emptyText: 'No leave records.', columns: [{ header: 'Type', accessorKey: 'type' }, { header: 'Status', accessorKey: 'status' }] },
    { title: 'Payroll', path: 'payroll', emptyText: 'No payroll records.', columns: [{ header: 'Month', accessorKey: 'month' }, { header: 'Net Pay', accessorKey: 'net_pay' }, { header: 'Status', accessorKey: 'status' }] },
  ];

  const profileSections = [
    {
      title: 'Basic Information',
      fields: [
        { label: 'Employee ID', path: 'emp_id' },
        { label: 'Full Name', path: 'full_name' },
        { label: 'Email', render: (employee) => employee.user?.email || '-' },
        { label: 'Date of Birth', path: 'dob' },
        { label: 'Gender', path: 'gender' },
        { label: 'Marital Status', path: 'marital_status' },
        { label: 'Contact Number', path: 'contact_number' },
        { label: 'Alternative Number', path: 'personal_number' },
        { label: 'Emergency Contact', path: 'emergency_contact' },
        { label: 'Address', path: 'address' },
      ]
    },
    {
      title: 'Employment Details',
      fields: [
        { label: 'Department', render: (employee) => employee.department?.name || '-' },
        { label: 'Designation', render: (employee) => employee.designation?.name || '-' },
        { label: 'Employment Type', path: 'employment_type' },
        { label: 'Reporting Manager', render: (employee) => employee.manager?.full_name || '-' },
        { label: 'Joining Date', path: 'joining_date' },
        { label: 'Interview Date', path: 'interview_date' },
        { label: 'Probation End Date', path: 'probation_end_date' },
      ]
    },
    {
      title: 'Status & Compliance',
      fields: [
        { label: 'Government ID Type', path: 'government_id_type' },
        { label: 'Highest Qualification', path: 'qualification' },
        { label: 'Employment Bond?', render: (e) => e.employment_bond_status ? 'Yes' : 'No' },
        { label: 'Previously Terminated?', render: (e) => e.previous_termination_status ? 'Yes' : 'No' },
        { label: 'Legal Proceedings?', render: (e) => e.legal_proceedings_status ? 'Yes' : 'No' },
      ]
    },
    {
      title: 'Uploaded Documents',
      fields: [
        { label: 'Profile Photo', render: (e) => e.photo_path ? <a href={`http://127.0.0.1:8000/storage/${e.photo_path}`} target="_blank" rel="noopener noreferrer" className="text-[#1a56db] hover:underline font-semibold flex items-center gap-1">View Photo</a> : '-' },
        { label: 'Resume', render: (e) => e.resume_path ? <a href={`http://127.0.0.1:8000/storage/${e.resume_path}`} target="_blank" rel="noopener noreferrer" className="text-[#1a56db] hover:underline font-semibold flex items-center gap-1">View Resume</a> : '-' },
        { label: 'Aadhaar Card', render: (e) => e.aadhaar_path ? <a href={`http://127.0.0.1:8000/storage/${e.aadhaar_path}`} target="_blank" rel="noopener noreferrer" className="text-[#1a56db] hover:underline font-semibold flex items-center gap-1">View Aadhaar</a> : '-' },
        { label: 'PAN Card', render: (e) => e.pan_path ? <a href={`http://127.0.0.1:8000/storage/${e.pan_path}`} target="_blank" rel="noopener noreferrer" className="text-[#1a56db] hover:underline font-semibold flex items-center gap-1">View PAN</a> : '-' },
        { label: 'Offer Letter', render: (e) => e.offer_letter_path ? <a href={`http://127.0.0.1:8000/storage/${e.offer_letter_path}`} target="_blank" rel="noopener noreferrer" className="text-[#1a56db] hover:underline font-semibold flex items-center gap-1">View Offer Letter</a> : '-' },
      ]
    }
  ];

  const title = selected.full_name || selected.user?.name || `Employee #${selected.id}`;

  // Action Handlers
  const handleAssign = async (e) => {
    e.preventDefault();
    setErrors({});
    if (!selectedSiteId) {
      setErrors({ site_id: 'Please select a site' });
      return;
    }

    try {
      await assignEmployee(selected.id, {
        site_id: parseInt(selectedSiteId),
        role,
        remarks
      });
      setIsAssignModalOpen(false);
      // Reset form
      setSelectedSiteId('');
      setRole('');
      setRemarks('');
    } catch (err) {
      setErrors({ general: err.message || 'Failed to assign site.' });
    }
  };

  const handleTransfer = async (e) => {
    e.preventDefault();
    setErrors({});
    if (!selectedNewSiteId) {
      setErrors({ new_site_id: 'Please select a new site' });
      return;
    }

    try {
      await transferEmployee(selected.id, {
        current_site_id: parseInt(selectedCurrentSiteId),
        new_site_id: parseInt(selectedNewSiteId),
        role,
        remarks
      });
      setIsTransferModalOpen(false);
      // Reset form
      setSelectedNewSiteId('');
      setRole('');
      setRemarks('');
    } catch (err) {
      setErrors({ general: err.message || 'Failed to transfer employee.' });
    }
  };

  const handleRemove = async (e) => {
    e.preventDefault();
    setErrors({});
    try {
      await removeEmployee(selected.id, parseInt(selectedCurrentSiteId), remarks);
      setIsRemoveModalOpen(false);
      setRemarks('');
    } catch (err) {
      setErrors({ general: err.message || 'Failed to remove assignment.' });
    }
  };

  const openTransferModal = (currentSiteId) => {
    setSelectedCurrentSiteId(currentSiteId);
    setSelectedNewSiteId('');
    setRole('');
    setRemarks('');
    setErrors({});
    setIsTransferModalOpen(true);
  };

  const openRemoveModal = (currentSiteId) => {
    setSelectedCurrentSiteId(currentSiteId);
    setRemarks('');
    setErrors({});
    setIsRemoveModalOpen(true);
  };

  return (
    <div className="space-y-5 pb-10">
      <div>
        <Link to="/dashboard/employees" className="text-sm font-medium text-[#1a56db] hover:underline">Back to list</Link>
        <h1 className="mt-2 text-2xl font-bold text-gray-900">{title}</h1>
      </div>

      <div className="border-b border-gray-200">
        <nav className="-mb-px flex space-x-8">
          <button
            onClick={() => setActiveTab('profile')}
            className={`whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm ${activeTab === 'profile' ? 'border-[#1a56db] text-[#1a56db]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'}`}
          >
            Profile Overview
          </button>
          <button
            onClick={() => setActiveTab('sites')}
            className={`whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm ${activeTab === 'sites' ? 'border-[#1a56db] text-[#1a56db]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'}`}
          >
            Site Allocation & History
          </button>
          <button
            onClick={() => setActiveTab('documents')}
            className={`whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm ${activeTab === 'documents' ? 'border-[#1a56db] text-[#1a56db]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'}`}
          >
            Other Documents
          </button>
          <button
            onClick={() => setActiveTab('attendance')}
            className={`whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm ${activeTab === 'attendance' ? 'border-[#1a56db] text-[#1a56db]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'}`}
          >
            Attendance
          </button>
          <button
            onClick={() => setActiveTab('history')}
            className={`whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm ${activeTab === 'history' ? 'border-[#1a56db] text-[#1a56db]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'}`}
          >
            Document History
          </button>
          <button
            onClick={() => setActiveTab('daily_reports')}
            className={`whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm ${activeTab === 'daily_reports' ? 'border-[#1a56db] text-[#1a56db]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'}`}
          >
            Daily Reports
          </button>
          <button
            onClick={() => setActiveTab('leave')}
            className={`whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm ${activeTab === 'leave' ? 'border-[#1a56db] text-[#1a56db]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'}`}
          >
            Leave
          </button>
        </nav>
      </div>

      {activeTab === 'profile' && (
        <div className="space-y-6">
          {profileSections.map((section) => (
            <div key={section.title} className="rounded-lg border border-gray-200 bg-white overflow-hidden shadow-sm">
              <div className="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h3 className="text-sm font-semibold text-gray-800 uppercase tracking-wider">{section.title}</h3>
              </div>
              <div className="grid gap-6 p-5 sm:grid-cols-2 lg:grid-cols-3">
                {section.fields.map((field) => (
                  <div key={field.label}>
                    <div className="text-xs font-medium text-gray-500">{field.label}</div>
                    <div className="mt-1 text-sm font-medium text-gray-900">{field.render ? field.render(selected) : (valueAt(selected, field.path) || '-')}</div>
                  </div>
                ))}
              </div>
            </div>
          ))}

          {sections.map((section) => {
            const rows = valueAt(selected, section.path) || [];
            if (rows.length === 0) return null;
            return (
              <section key={section.title} className="space-y-3">
                <h2 className="text-lg font-semibold text-gray-900">{section.title}</h2>
                <DataTable columns={section.columns} data={Array.isArray(rows) ? rows : []} emptyText={section.emptyText} />
              </section>
            );
          })}
        </div>
      )}

      {/* Sites tab with interactive assignment and history timeline */}
      {activeTab === 'sites' && (
        <div className="space-y-6">
          {/* Current Assignments Card */}
          <div className="rounded-lg border border-gray-200 bg-white overflow-hidden shadow-sm">
            <div className="border-b border-gray-200 bg-gray-50 px-5 py-3 flex justify-between items-center">
              <h3 className="text-sm font-semibold text-gray-800 uppercase tracking-wider">Current Site Allocation</h3>
              <button 
                onClick={() => {
                  setSelectedSiteId('');
                  setRole('');
                  setRemarks('');
                  setErrors({});
                  setIsAssignModalOpen(true);
                }}
                className="flex items-center text-xs font-bold bg-[#1a56db] text-white px-3 py-1.5 rounded-lg hover:bg-[#1546b5] transition-colors"
              >
                <Plus className="w-3.5 h-3.5 mr-1" />
                Assign to Site
              </button>
            </div>
            
            <div className="p-5">
              {sitesLoading ? (
                <div className="text-center py-4 text-sm text-gray-500">Loading assignments...</div>
              ) : assignments?.length === 0 ? (
                <div className="text-center py-8 text-sm text-gray-500">
                  <MapPin className="w-8 h-8 mx-auto text-gray-300 mb-2" />
                  No active site assignments for this employee.
                </div>
              ) : (
                <div className="overflow-x-auto">
                  <table className="w-full text-left border-collapse">
                    <thead>
                      <tr className="border-b border-gray-150 text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50/50">
                        <th className="py-3 px-4">Site Name</th>
                        <th className="py-3 px-4">Code</th>
                        <th className="py-3 px-4">Role</th>
                        <th className="py-3 px-4">Assigned At</th>
                        <th className="py-3 px-4 text-right">Actions</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100 text-sm">
                      {assignments.map((assignment) => (
                        <tr key={assignment.id} className="hover:bg-gray-50/50">
                          <td className="py-3.5 px-4 font-semibold text-gray-800">
                            {assignment.site?.name || 'Unknown Site'}
                          </td>
                          <td className="py-3.5 px-4">
                            <span className="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">
                              {assignment.site?.code || 'N/A'}
                            </span>
                          </td>
                          <td className="py-3.5 px-4 font-medium text-gray-600">
                            {assignment.role || 'General Staff'}
                          </td>
                          <td className="py-3.5 px-4 text-gray-500">
                            {assignment.assigned_at ? new Date(assignment.assigned_at).toLocaleDateString() : 'N/A'}
                          </td>
                          <td className="py-3.5 px-4 text-right flex justify-end gap-2">
                            <button
                              onClick={() => openTransferModal(assignment.site_id)}
                              className="flex items-center text-xs font-semibold text-blue-600 hover:text-blue-800 border border-blue-200 hover:border-blue-300 px-2.5 py-1 rounded bg-blue-50/30"
                            >
                              <ArrowLeftRight className="w-3.5 h-3.5 mr-1" />
                              Transfer
                            </button>
                            <button
                              onClick={() => openRemoveModal(assignment.site_id)}
                              className="flex items-center text-xs font-semibold text-red-600 hover:text-red-800 border border-red-200 hover:border-red-300 px-2.5 py-1 rounded bg-red-50/30"
                            >
                              <Trash2 className="w-3.5 h-3.5 mr-1" />
                              Remove
                            </button>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </div>
          </div>

          {/* Allocation & Transfer History Card */}
          <div className="rounded-lg border border-gray-200 bg-white overflow-hidden shadow-sm">
            <div className="border-b border-gray-200 bg-gray-50 px-5 py-3">
              <h3 className="text-sm font-semibold text-gray-800 uppercase tracking-wider">Allocation & Transfer History Log</h3>
            </div>
            
            <div className="p-5">
              {sitesLoading ? (
                <div className="text-center py-4 text-sm text-gray-500">Loading history...</div>
              ) : siteHistory?.length === 0 ? (
                <div className="text-center py-8 text-sm text-gray-500">
                  <Calendar className="w-8 h-8 mx-auto text-gray-300 mb-2" />
                  No historical logs found.
                </div>
              ) : (
                <div className="relative border-l border-gray-200 ml-3 space-y-6 py-2">
                  {siteHistory.map((log) => {
                    const hasPrev = !!log.previous_site;
                    const hasNew = !!log.new_site;
                    let typeColor = 'bg-blue-500';
                    let typeText = 'Assigned';

                    if (hasPrev && hasNew) {
                      typeColor = 'bg-amber-500';
                      typeText = 'Transferred';
                    } else if (hasPrev && !hasNew) {
                      typeColor = 'bg-red-500';
                      typeText = 'Removed';
                    }

                    return (
                      <div key={log.id} className="relative pl-6">
                        {/* Dot indicator */}
                        <div className={`absolute -left-1.5 top-1.5 w-3 h-3 rounded-full ${typeColor} ring-4 ring-white`}></div>
                        
                        <div className="bg-gray-50/50 border border-gray-100 rounded-xl p-4">
                          <div className="flex flex-col sm:flex-row justify-between sm:items-center gap-2 mb-2">
                            <span className={`inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold text-white ${typeColor}`}>
                              {typeText}
                            </span>
                            <span className="text-xs text-gray-500 font-medium">
                              {log.created_at ? new Date(log.created_at).toLocaleString() : ''}
                            </span>
                          </div>

                          <div className="text-sm text-gray-700 font-medium space-y-1">
                            {typeText === 'Assigned' && (
                              <p>Assigned to <span className="font-bold text-gray-900">{log.new_site?.name}</span></p>
                            )}
                            {typeText === 'Transferred' && (
                              <p>Transferred from <span className="font-bold text-gray-900">{log.previous_site?.name}</span> to <span className="font-bold text-gray-900">{log.new_site?.name}</span></p>
                            )}
                            {typeText === 'Removed' && (
                              <p>Removed from <span className="font-bold text-gray-900">{log.previous_site?.name}</span></p>
                            )}
                          </div>

                          <div className="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs border-t border-gray-100/60 pt-3 text-gray-500">
                            <div className="flex items-center gap-1.5">
                              <User className="w-3.5 h-3.5 text-gray-400" />
                              <span>Action by: {log.assigned_by?.name || 'System'}</span>
                            </div>
                            <div className="flex items-start gap-1.5">
                              <MessageSquare className="w-3.5 h-3.5 text-gray-400 mt-0.5" />
                              <span>Remarks: {log.remarks || 'No remarks provided'}</span>
                            </div>
                          </div>
                        </div>
                      </div>
                    );
                  })}
                </div>
              )}
            </div>
          </div>
        </div>
      )}

      {activeTab === 'documents' && (
        <EmployeeDocumentsPage employeeId={id} />
      )}

      {activeTab === 'history' && (
        <DocumentHistory documents={documents} />
      )}

      {activeTab === 'attendance' && (
        <div className="space-y-6">
          <div className="rounded-lg border border-gray-200 bg-white overflow-hidden shadow-sm">
            <div className="border-b border-gray-200 bg-gray-50 px-5 py-3">
              <h3 className="text-sm font-semibold text-gray-800 uppercase tracking-wider">Attendance Records</h3>
            </div>
            <div className="p-5">
              {attendancesLoading ? (
                <div className="text-center py-4 text-sm text-gray-500">Loading attendance...</div>
              ) : attendances?.length === 0 ? (
                <div className="text-center py-8 text-sm text-gray-500">
                  <Calendar className="w-8 h-8 mx-auto text-gray-300 mb-2" />
                  No attendance records found.
                </div>
              ) : (
                <DataTable 
                  columns={[
                    { header: 'Date', accessorKey: 'date' },
                    { header: 'Check In', accessorKey: 'check_in', cell: (row) => row.check_in ? new Date(row.check_in).toLocaleTimeString() : '-' },
                    { header: 'Check Out', accessorKey: 'check_out', cell: (row) => row.check_out ? new Date(row.check_out).toLocaleTimeString() : '-' },
                    { header: 'Working Hours', accessorKey: 'working_hours' },
                    { header: 'Overtime', accessorKey: 'overtime_hours' },
                    { header: 'Status', accessorKey: 'status' },
                    { header: 'Site', accessorKey: 'site.name', cell: (row) => row.site?.name || '-' }
                  ]} 
                  data={attendances} 
                  emptyText="No attendance records." 
                />
              )}
            </div>
          </div>
        </div>
      )}

      {activeTab === 'daily_reports' && (
        <div className="space-y-6">
          <div className="rounded-lg border border-gray-200 bg-white overflow-hidden shadow-sm">
            <div className="border-b border-gray-200 bg-gray-50 px-5 py-3 flex justify-between items-center">
              <h3 className="text-sm font-semibold text-gray-800 uppercase tracking-wider">Daily Progress Reports</h3>
              <Link 
                to={`/dashboard/daily-reports/new`}
                className="flex items-center text-xs font-bold bg-[#1a56db] text-white px-3 py-1.5 rounded-lg hover:bg-[#1546b5] transition-colors"
              >
                <Plus className="w-3.5 h-3.5 mr-1" />
                Add Report
              </Link>
            </div>
            <div className="p-5">
              {dailyReportsLoading ? (
                <div className="text-center py-4 text-sm text-gray-500">Loading reports...</div>
              ) : dailyReports?.length === 0 ? (
                <div className="text-center py-8 text-sm text-gray-500">
                  <FileText className="w-8 h-8 mx-auto text-gray-300 mb-2" />
                  No daily progress reports found.
                </div>
              ) : (
                <DataTable 
                  columns={[
                    { header: 'Date', accessorKey: 'date' },
                    { header: 'Hours', accessorKey: 'hours_worked' },
                    { header: 'Site', accessorKey: 'site.name', cell: (row) => row.site?.name || '-' },
                    { header: 'Status', accessorKey: 'status' },
                    { 
                      id: 'actions', 
                      header: 'Actions', 
                      cell: ({row}) => (
                        <Link 
                          to={`/dashboard/daily-reports/${row.original.id}`}
                          className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                        >
                          View
                        </Link>
                      )
                    }
                  ]} 
                  data={dailyReports} 
                  emptyText="No daily reports." 
                />
              )}
            </div>
          </div>
        </div>
      )}

      {activeTab === 'leave' && (
        <div className="space-y-6">
          <div className="rounded-lg border border-gray-200 bg-white overflow-hidden shadow-sm">
            <div className="border-b border-gray-200 bg-gray-50 px-5 py-3 flex justify-between items-center">
              <h3 className="text-sm font-semibold text-gray-800 uppercase tracking-wider">Leave Balances</h3>
            </div>
            <div className="p-5">
              <div className="text-sm text-gray-500 mb-4">Leave balance summary will be displayed here.</div>
              <Link 
                to="/dashboard/leave-management/balances"
                className="text-blue-600 hover:text-blue-800 text-sm font-medium"
              >
                View Full Leave Balances
              </Link>
            </div>
          </div>
          
          <div className="rounded-lg border border-gray-200 bg-white overflow-hidden shadow-sm">
            <div className="border-b border-gray-200 bg-gray-50 px-5 py-3 flex justify-between items-center">
              <h3 className="text-sm font-semibold text-gray-800 uppercase tracking-wider">Leave Requests</h3>
            </div>
            <div className="p-5">
              <Link 
                to="/dashboard/leave-management/requests"
                className="text-blue-600 hover:text-blue-800 text-sm font-medium"
              >
                View Leave Requests
              </Link>
            </div>
          </div>
        </div>
      )}

      {/* Modal - Assign Employee to Site */}
      {isAssignModalOpen && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden">
            <div className="flex justify-between items-center px-6 py-4 border-b border-gray-150">
              <h2 className="text-lg font-bold text-gray-900">Assign to Site</h2>
              <button onClick={() => setIsAssignModalOpen(false)} className="text-gray-400 hover:text-gray-600">
                <X className="w-5 h-5" />
              </button>
            </div>
            
            <form onSubmit={handleAssign} className="p-6 space-y-4">
              {errors.general && (
                <div className="bg-red-50 text-red-700 p-3 rounded-lg text-xs flex items-center">
                  <ShieldAlert className="w-4 h-4 mr-2" />
                  {errors.general}
                </div>
              )}

              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Select Site *</label>
                <select
                  value={selectedSiteId}
                  onChange={(e) => setSelectedSiteId(e.target.value)}
                  className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                >
                  <option value="">Choose an active site...</option>
                  {allSites?.filter(s => s.status === 'Active').map(s => (
                    <option key={s.id} value={s.id}>{s.name} ({s.code || 'No Code'})</option>
                  ))}
                </select>
                {errors.site_id && <p className="text-red-500 text-xs mt-1">{errors.site_id}</p>}
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Role / Designation at Site</label>
                <input 
                  type="text" 
                  value={role}
                  onChange={(e) => setRole(e.target.value)}
                  placeholder="e.g. Supervisor, Site Engineer, Worker"
                  className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Remarks</label>
                <textarea 
                  value={remarks}
                  onChange={(e) => setRemarks(e.target.value)}
                  rows="3"
                  placeholder="Reason or notes for the assignment..."
                  className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                ></textarea>
              </div>

              <div className="flex justify-end gap-3 pt-4 border-t border-gray-150">
                <button 
                  type="button" 
                  onClick={() => setIsAssignModalOpen(false)}
                  className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-xs hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button 
                  type="submit" 
                  className="px-4 py-2 bg-[#1a56db] text-white rounded-lg text-xs hover:bg-[#1546b5] font-semibold"
                >
                  Assign Site
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal - Transfer Employee */}
      {isTransferModalOpen && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden">
            <div className="flex justify-between items-center px-6 py-4 border-b border-gray-150">
              <h2 className="text-lg font-bold text-gray-900">Transfer Employee</h2>
              <button onClick={() => setIsTransferModalOpen(false)} className="text-gray-400 hover:text-gray-600">
                <X className="w-5 h-5" />
              </button>
            </div>
            
            <form onSubmit={handleTransfer} className="p-6 space-y-4">
              {errors.general && (
                <div className="bg-red-50 text-red-700 p-3 rounded-lg text-xs flex items-center">
                  <ShieldAlert className="w-4 h-4 mr-2" />
                  {errors.general}
                </div>
              )}

              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase mb-1">New Site *</label>
                <select
                  value={selectedNewSiteId}
                  onChange={(e) => setSelectedNewSiteId(e.target.value)}
                  className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                >
                  <option value="">Select new active site...</option>
                  {allSites?.filter(s => s.status === 'Active' && s.id !== parseInt(selectedCurrentSiteId)).map(s => (
                    <option key={s.id} value={s.id}>{s.name} ({s.code || 'No Code'})</option>
                  ))}
                </select>
                {errors.new_site_id && <p className="text-red-500 text-xs mt-1">{errors.new_site_id}</p>}
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Role / Designation at New Site</label>
                <input 
                  type="text" 
                  value={role}
                  onChange={(e) => setRole(e.target.value)}
                  placeholder="e.g. Supervisor, Site Engineer, Worker"
                  className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Remarks / Reason *</label>
                <textarea 
                  value={remarks}
                  onChange={(e) => setRemarks(e.target.value)}
                  rows="3"
                  placeholder="Reason for site transfer..."
                  className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                ></textarea>
              </div>

              <div className="flex justify-end gap-3 pt-4 border-t border-gray-150">
                <button 
                  type="button" 
                  onClick={() => setIsTransferModalOpen(false)}
                  className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-xs hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button 
                  type="submit" 
                  className="px-4 py-2 bg-[#1a56db] text-white rounded-lg text-xs hover:bg-[#1546b5] font-semibold"
                >
                  Confirm Transfer
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal - Remove Employee from Site */}
      {isRemoveModalOpen && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden">
            <div className="flex justify-between items-center px-6 py-4 border-b border-gray-150">
              <h2 className="text-lg font-bold text-gray-900">Remove from Site</h2>
              <button onClick={() => setIsRemoveModalOpen(false)} className="text-gray-400 hover:text-gray-600">
                <X className="w-5 h-5" />
              </button>
            </div>
            
            <form onSubmit={handleRemove} className="p-6 space-y-4">
              {errors.general && (
                <div className="bg-red-50 text-red-700 p-3 rounded-lg text-xs flex items-center">
                  <ShieldAlert className="w-4 h-4 mr-2" />
                  {errors.general}
                </div>
              )}

              <p className="text-sm text-gray-600">
                Are you sure you want to remove this employee from the site assignment? This will log this removal in their history.
              </p>

              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Remarks / Reason *</label>
                <textarea 
                  value={remarks}
                  onChange={(e) => setRemarks(e.target.value)}
                  rows="3"
                  placeholder="Reason for removal..."
                  className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                ></textarea>
              </div>

              <div className="flex justify-end gap-3 pt-4 border-t border-gray-150">
                <button 
                  type="button" 
                  onClick={() => setIsRemoveModalOpen(false)}
                  className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-xs hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button 
                  type="submit" 
                  className="px-4 py-2 bg-red-600 text-white rounded-lg text-xs hover:bg-red-700 font-semibold"
                >
                  Remove Assignment
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
