import React, { useState, useEffect } from 'react';
import {
  Search,
  Plus,
  Filter,
  Edit,
  Eye,
  Trash2,
  Loader2
} from 'lucide-react';


export default function EmployeesList() {
  const [employees, setEmployees] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    setLoading(true);
    fetch('http://localhost:8000/api/v1/employees')
      .then(res => {
        if (!res.ok) throw new Error('API not available yet');
        return res.json();
      })
      .then(data => {
        if (data.status === 'success') {
          const formatted = data.data.map(emp => ({
            id: emp.id,
            emp_id: emp.emp_id,
            name: emp.full_name,
            department: emp.department?.name || 'Unassigned',
            role: emp.designation?.name || 'Unassigned',
            status: 'Active',
            site: 'Unassigned'
          }));
          setEmployees(formatted);
        }
        setLoading(false);
      })
      .catch(err => {
        console.error(err);
        // Fallback to mock data if API is not running
        // setEmployees([]);
        setLoading(false);
      });
  }, []);

  return (
    <div className="w-full">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
          <h1 className="text-3xl font-extrabold text-[#0B1B36] mb-1">
            Employee Directory
          </h1>
          <p className="text-[#718096]">
            Manage all company personnel and assignments.
          </p>
        </div>
        <button className="flex items-center px-4 py-2 bg-[#0B1B36] text-white rounded-xl font-semibold hover:bg-[#081428] transition-colors shadow-sm">
          <Plus className="w-4 h-4 mr-2" />
          Add Employee
        </button>
      </div>

      <div className="bg-white border border-gray-200 p-4 mb-6 rounded-2xl flex gap-3 shadow-sm">
        <div className="relative flex-grow">
          <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
            <Search className="h-5 w-5" />
          </div>
          <input
            type="text"
            placeholder="Search by name or ID..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0B1B36] focus:border-transparent transition-colors text-sm"
          />
        </div>
        <button className="flex items-center px-4 py-2 bg-white border border-gray-300 text-[#4A5568] rounded-lg font-semibold hover:bg-gray-50 transition-colors">
          <Filter className="w-4 h-4 mr-2" />
          Filters
        </button>
      </div>

      <div className="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-[#F7FAFC] border-b border-gray-200 text-xs font-bold text-[#718096] uppercase tracking-widest">
                <th className="p-4 pl-6">Employee</th>
                <th className="p-4">Employee ID</th>
                <th className="p-4">Department</th>
                <th className="p-4">Designation</th>
                <th className="p-4">Status</th>
                <th className="p-4 text-right pr-6">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {loading ? (
                <tr>
                  <td colSpan="6" className="p-8 text-center text-gray-500">
                    <Loader2 className="w-6 h-6 animate-spin mx-auto mb-2" />
                    Loading employees...
                  </td>
                </tr>
              ) : employees.length === 0 ? (
                <tr>
                  <td colSpan="6" className="p-8 text-center text-gray-500 font-semibold">
                    No employees found.
                  </td>
                </tr>
              ) : (
                employees.map((emp) => (
                  <tr key={emp.id} className="hover:bg-gray-50 transition-colors">
                    <td className="p-4 pl-6">
                      <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-full bg-[#EBF8FF] text-[#3182CE] flex items-center justify-center font-bold text-sm border border-[#BEE3F8]">
                          {emp.name.charAt(0)}
                        </div>
                        <div className="font-bold text-[#1A202C]">{emp.name}</div>
                      </div>
                    </td>
                    <td className="p-4 text-sm font-semibold text-[#4A5568]">{emp.emp_id}</td>
                    <td className="p-4 text-sm text-[#718096]">{emp.department}</td>
                    <td className="p-4 text-sm text-[#718096]">{emp.role}</td>
                    <td className="p-4">
                      <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold ${emp.status === 'Active' ? 'bg-[#C6F6D5] text-[#22543D]' : 'bg-[#FEEBC8] text-[#7B341E]'
                        }`}>
                        {emp.status}
                      </span>
                    </td>
                    <td className="p-4 text-right pr-6">
                      <div className="flex justify-end gap-2 text-[#A0AEC0]">
                        <button className="p-1.5 hover:text-[#3182CE] hover:bg-[#EBF8FF] rounded transition-colors" title="View">
                          <Eye className="w-4 h-4" />
                        </button>
                        <button className="p-1.5 hover:text-[#0B1B36] hover:bg-gray-200 rounded transition-colors" title="Edit">
                          <Edit className="w-4 h-4" />
                        </button>
                        <button className="p-1.5 hover:text-[#E53E3E] hover:bg-[#FFF5F5] rounded transition-colors" title="Delete">
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
