import { useState, useEffect } from 'react';
import { Plus, Edit2, Trash2, X, Search } from 'lucide-react';
import api from '../../services/api';

export default function EmployeesPage() {
  const [employees, setEmployees] = useState([]);
  const [branches, setBranches] = useState([]);
  const [roles, setRoles] = useState([]);
  const [permissionOptions, setPermissionOptions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState('');
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [currentEmployee, setCurrentEmployee] = useState(null); // null means adding new

  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    branch_id: '',
    department: '',
    salary: '',
    shift: '',
    joining_date: '',
    role: '',
    status: 'active',
    permissions: [],
  });

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    setLoading(true);
    try {
      const [empRes, branchRes, rolesRes, permissionsRes] = await Promise.all([
        api.get('/employees'),
        api.get('/branches'),
        api.get('/roles'),
        api.get('/permissions'),
      ]);

      setEmployees(empRes.data);
      setBranches(branchRes.data);
      setRoles(rolesRes.data);
      setPermissionOptions(permissionsRes.data);
    } catch (error) {
      console.error("Error fetching data:", error);
    } finally {
      setLoading(false);
    }
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    if (name === 'role') {
      const selectedRole = roles.find((role) => role.name === value);
      setFormData((prev) => ({
        ...prev,
        role: value,
        permissions: selectedRole ? selectedRole.permissions : prev.permissions,
      }));
      return;
    }

    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const togglePermission = (permission) => {
    setFormData((prev) => {
      const hasPermission = prev.permissions.includes(permission);
      return {
        ...prev,
        permissions: hasPermission
          ? prev.permissions.filter((item) => item !== permission)
          : [...prev.permissions, permission],
      };
    });
  };

  const openAddModal = () => {
    const defaultRole = roles[0]?.name || '';
    const defaultPermissions = roles[0]?.permissions || [];

    setCurrentEmployee(null);
    setFormData({
      name: '',
      email: '',
      password: '',
      branch_id: branches[0]?.id || '',
      department: '',
      salary: '',
      shift: '',
      joining_date: '',
      role: defaultRole,
      status: 'active',
      permissions: defaultPermissions,
    });
    setIsModalOpen(true);
  };

  const openEditModal = (employee) => {
    const currentRole = employee.role || employee.user?.roles?.[0]?.name || '';

    setCurrentEmployee(employee);
    setFormData({
      name: employee.user?.name || '',
      email: employee.user?.email || '',
      password: '', // Leave blank for edit unless changing
      branch_id: employee.user?.branch_id || branches[0]?.id || '',
      department: employee.department || '',
      salary: employee.salary || '',
      shift: employee.shift || '',
      joining_date: employee.joining_date || '',
      role: currentRole,
      status: employee.status || 'active',
      permissions: employee.permissions || [],
    });
    setIsModalOpen(true);
  };

  const closeModal = () => setIsModalOpen(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      if (currentEmployee) {
        // Update
        const dataToSend = { ...formData };
        if (!dataToSend.password) delete dataToSend.password; // Don't send empty password
        await api.put(`/employees/${currentEmployee.id}`, dataToSend);
      } else {
        // Create
        await api.post('/employees', formData);
      }
      closeModal();
      fetchData(); // Refresh list
    } catch (error) {
      console.error("Error saving employee:", error);
      alert("Failed to save employee. Check console for details.");
    }
  };

  const handleDelete = async (id) => {
    if (window.confirm("Are you sure you want to delete this employee?")) {
      try {
        await api.delete(`/employees/${id}`);
        fetchData(); // Refresh
      } catch (error) {
        console.error("Error deleting employee:", error);
      }
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-full">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-[#1a56db]"></div>
      </div>
    );
  }

  return (
    <div className="space-y-6 pb-12">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-4 sm:space-y-0">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Employees</h1>
          <p className="text-sm text-gray-500">Manage your workforce.</p>
        </div>
        <div className="flex space-x-3">
          <div className="relative">
            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <Search className="h-4 w-4 text-gray-400" />
            </div>
            <input
              type="text"
              placeholder="Search employees..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#1a56db] focus:border-[#1a56db] sm:text-sm"
            />
          </div>
          <button 
            onClick={openAddModal}
            className="flex items-center px-4 py-2 bg-[#1a56db] text-white rounded-md text-sm font-medium hover:bg-blue-700 whitespace-nowrap"
          >
            <Plus className="w-4 h-4 mr-2" />
            Add Employee
          </button>
        </div>
      </div>

      {/* Employees Table */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm text-gray-600">
            <thead className="text-xs text-gray-500 uppercase bg-gray-50 font-semibold tracking-wider">
              <tr>
                <th className="px-6 py-4">Name</th>
                <th className="px-6 py-4">Email</th>
                <th className="px-6 py-4">Role</th>
                <th className="px-6 py-4">Department</th>
                <th className="px-6 py-4">Branch</th>
                <th className="px-6 py-4">Status</th>
                <th className="px-6 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {employees
                .filter(emp => 
                  emp.user?.name?.toLowerCase().includes(searchQuery.toLowerCase()) || 
                  emp.user?.email?.toLowerCase().includes(searchQuery.toLowerCase()) ||
                  emp.role?.toLowerCase().includes(searchQuery.toLowerCase()) ||
                  emp.department?.toLowerCase().includes(searchQuery.toLowerCase())
                )
                .map((emp) => (
                <tr key={emp.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 font-medium text-gray-900">{emp.user?.name}</td>
                  <td className="px-6 py-4">{emp.user?.email}</td>
                  <td className="px-6 py-4">{emp.role || emp.user?.roles?.[0]?.name || '-'}</td>
                  <td className="px-6 py-4">{emp.department || '-'}</td>
                  <td className="px-6 py-4">{emp.user?.branch?.name || '-'}</td>
                  <td className="px-6 py-4">
                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                      emp.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                    }`}>
                      {emp.status}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-right space-x-2">
                    <button 
                      onClick={() => openEditModal(emp)}
                      className="text-blue-600 hover:text-blue-800"
                    >
                      <Edit2 className="w-4 h-4 inline" />
                    </button>
                    <button 
                      onClick={() => handleDelete(emp.id)}
                      className="text-red-600 hover:text-red-800"
                    >
                      <Trash2 className="w-4 h-4 inline" />
                    </button>
                  </td>
                </tr>
              ))}
              {employees.length === 0 && (
                <tr>
                  <td colSpan="6" className="px-6 py-8 text-center text-gray-500">
                    No employees found.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
          <div className="bg-white rounded-xl shadow-lg w-full max-w-2xl overflow-hidden">
            <div className="flex justify-between items-center px-6 py-4 border-b border-gray-100">
              <h2 className="text-xl font-bold text-gray-900">
                {currentEmployee ? 'Edit Employee' : 'Add Employee'}
              </h2>
              <button onClick={closeModal} className="text-gray-400 hover:text-gray-600">
                <X className="w-6 h-6" />
              </button>
            </div>
            <form onSubmit={handleSubmit} className="p-6 space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Name</label>
                  <input required type="text" name="name" value={formData.name} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db]" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                  <input required type="email" name="email" value={formData.email} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db]" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Password {currentEmployee && '(leave blank to keep current)'}</label>
                  <input type="password" name="password" value={formData.password} onChange={handleInputChange} required={!currentEmployee} className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db]" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                  <select required name="branch_id" value={formData.branch_id} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db]">
                    <option value="" disabled>Select Branch</option>
                    {branches.map(b => (
                      <option key={b.id} value={b.id}>{b.name}</option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Department</label>
                  <input type="text" name="department" value={formData.department} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db]" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Salary</label>
                  <input type="number" name="salary" value={formData.salary} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db]" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Joining Date</label>
                  <input type="date" name="joining_date" value={formData.joining_date} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db]" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Role</label>
                  <select required name="role" value={formData.role} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db]">
                    <option value="" disabled>Select role</option>
                    {roles.map((role) => (
                      <option key={role.name} value={role.name}>{role.name}</option>
                    ))}
                  </select>
                </div>
                <div className="col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-1">Applicable Website Sections</label>
                  <div className="grid grid-cols-2 gap-2 max-h-56 overflow-y-auto border border-gray-200 rounded-lg p-3 bg-gray-50">
                    {permissionOptions.map((permission) => (
                      <label key={permission} className="flex items-center gap-2 text-sm text-gray-700">
                        <input
                          type="checkbox"
                          checked={formData.permissions.includes(permission)}
                          onChange={() => togglePermission(permission)}
                          className="h-4 w-4 rounded border-gray-300 text-[#1a56db] focus:ring-[#1a56db]"
                        />
                        {permission}
                      </label>
                    ))}
                  </div>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                  <select name="status" value={formData.status} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db]">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
              </div>
              <div className="flex justify-end space-x-3 pt-4 border-t border-gray-100 mt-6">
                <button type="button" onClick={closeModal} className="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                  Cancel
                </button>
                <button type="submit" className="px-4 py-2 bg-[#1a56db] text-white rounded-md text-sm font-medium hover:bg-blue-700">
                  Save
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
