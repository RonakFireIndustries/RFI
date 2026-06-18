import React, { useEffect, useState } from 'react';
import api from '../../services/api';
import { FileText, Download } from 'lucide-react';
import { useAuthStore } from '../../store/authStore';

const EmployeePayrollPage = () => {
  const { user } = useAuthStore();
  const [payrolls, setPayrolls] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Assuming user has employee_id or we fetch by logged in user
    if (user?.employee_id) {
      api.get(`/payroll?employee_id=${user.employee_id}`).then(res => {
        setPayrolls(res.data);
        setLoading(false);
      });
    } else {
      setLoading(false); // Admin or user without employee profile
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
                  {p.payrollPeriod ? `${p.payrollPeriod.month}/${p.payrollPeriod.year}` : 'Unknown Period'}
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
                    <span className="text-gray-400 text-xs">Not Generated</span>
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
