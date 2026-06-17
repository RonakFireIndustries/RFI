import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useLeaveRequestStore } from '../../store/leaveRequestStore';
import { useLeaveTypeStore } from '../../store/leaveTypeStore';
import { useEmployeesStore } from '../../store/employeesStore';

export default function LeaveRequestForm() {
  const navigate = useNavigate();
  const { createItem, loading } = useLeaveRequestStore();
  const { items: leaveTypes, fetchItems: fetchLeaveTypes } = useLeaveTypeStore();
  const { items: employees, fetchItems: fetchEmployees } = useEmployeesStore();

  const [formData, setFormData] = useState({
    employee_id: '',
    leave_type_id: '',
    start_date: '',
    end_date: '',
    is_half_day: false,
    reason: '',
    status: 'Submitted',
  });

  useEffect(() => {
    fetchLeaveTypes({ status: 'Active' });
    fetchEmployees({ per_page: 100 });
  }, [fetchLeaveTypes, fetchEmployees]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      await createItem(formData);
      navigate('/dashboard/leave-management/requests');
    } catch (error) {
      alert('Error creating leave request: ' + error.message);
    }
  };

  return (
    <div className="max-w-2xl mx-auto">
      <h1 className="text-2xl font-bold text-gray-800 mb-6">Apply for Leave</h1>
      <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form onSubmit={handleSubmit} className="space-y-6">
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Employee</label>
            <select
              value={formData.employee_id}
              onChange={(e) => setFormData({ ...formData, employee_id: e.target.value })}
              className="w-full border-gray-300 rounded-lg shadow-sm"
              required
            >
              <option value="">Select Employee...</option>
              {employees.map(emp => (
                <option key={emp.id} value={emp.id}>{emp.first_name} {emp.last_name}</option>
              ))}
            </select>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Leave Type</label>
            <select
              value={formData.leave_type_id}
              onChange={(e) => setFormData({ ...formData, leave_type_id: e.target.value })}
              className="w-full border-gray-300 rounded-lg shadow-sm"
              required
            >
              <option value="">Select Leave Type...</option>
              {leaveTypes.map(type => (
                <option key={type.id} value={type.id}>{type.name}</option>
              ))}
            </select>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
              <input
                type="date"
                value={formData.start_date}
                onChange={(e) => setFormData({ ...formData, start_date: e.target.value })}
                className="w-full border-gray-300 rounded-lg shadow-sm"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">End Date</label>
              <input
                type="date"
                value={formData.end_date}
                onChange={(e) => setFormData({ ...formData, end_date: e.target.value })}
                className="w-full border-gray-300 rounded-lg shadow-sm"
                required
              />
            </div>
          </div>

          <div className="flex items-center">
            <input
              type="checkbox"
              id="is_half_day"
              checked={formData.is_half_day}
              onChange={(e) => setFormData({ ...formData, is_half_day: e.target.checked })}
              className="rounded border-gray-300 text-[#1a56db] focus:ring-[#1a56db]"
            />
            <label htmlFor="is_half_day" className="ml-2 text-sm text-gray-700">This is a half day leave</label>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Reason</label>
            <textarea
              value={formData.reason}
              onChange={(e) => setFormData({ ...formData, reason: e.target.value })}
              className="w-full border-gray-300 rounded-lg shadow-sm"
              rows="4"
              required
            ></textarea>
          </div>

          <div className="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              onClick={() => navigate(-1)}
              className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={loading}
              className="px-4 py-2 bg-[#1a56db] text-white rounded-lg hover:bg-[#1546b5] disabled:opacity-50"
            >
              {loading ? 'Submitting...' : 'Submit Leave Request'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
