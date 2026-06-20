import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../../services/api';
import { FileText, Download, Plus } from 'lucide-react';
import { useAuthStore } from '../../store/authStore';

const EmployeePayrollPage = () => {
  const navigate = useNavigate();
  const { user } = useAuthStore();
  const [payrolls, setPayrolls] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const empId = user?.employee?.id;
    if (empId) {
      api.get(`/payroll?employee_id=${empId}`).then(res => {
        setPayrolls(res.data);
        setLoading(false);
      });
    } else {
      setLoading(false);
    }
  }, [user]);

  if (loading) return <div className="p-6">Loading your payroll history...</div>;

  return (
    <div className="p-6 max-w-6xl mx-auto">
      <h1 className="text-2xl font-bold text-gray-900 mb-6">My Payroll History</h1>
      
      <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Pay</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Payslip</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {payrolls.map((p) => (
              <tr key={p.id}>
                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {p.payroll_period ? `${p.payroll_period.month}/${p.payroll_period.year}` : 'Unknown Period'}
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">
                  ₹{p.net_salary}
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm">
                  <span className="px-2 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">{p.status}</span>
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  {p.payslip ? (
                    <a href={`/payslip/${p.payslip.id}`} className="text-orange-600 hover:text-orange-900 inline-flex items-center">
                      <FileText className="w-4 h-4 mr-1" /> View
                    </a>
                  ) : (
                    <button onClick={async () => {
                      try {
                        const res = await api.post(`/payrolls/${p.id}/payslips/generate`);
                        if (res?.data?.payslip) {
                          navigate(`/dashboard/payslip/${res.data.payslip.id}`);
                        } else {
                          alert('Payslip generated');
                        }
                        setPayrolls(prev => prev.map(r => r.id === p.id ? { ...r, payslip: res?.data?.payslip || { id: 'new' } } : r));
                      } catch (e) {
                        alert(e?.response?.data?.message || e?.message || 'Failed to generate payslip');
                      }
                    }} className="text-orange-600 hover:text-orange-900 inline-flex items-center text-xs font-medium">
                      <Plus className="w-3.5 h-3.5 mr-1" /> Generate
                    </button>
                  )}
                </td>
              </tr>
            ))}
            {payrolls.length === 0 && (
              <tr>
                <td colSpan="4" className="px-6 py-8 text-center text-gray-500">
                  No payroll history available.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default EmployeePayrollPage;
