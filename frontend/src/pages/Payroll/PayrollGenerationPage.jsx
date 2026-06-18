import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { usePayrollStore } from '../../store/payrollStore';
import { usePayrollPeriodStore } from '../../store/payrollPeriodStore';
import { useEmployeesStore } from '../../store/employeesStore';
import { Play, CheckCircle, Lock, RefreshCw, FileText } from 'lucide-react';

const PayrollGenerationPage = () => {
  const navigate = useNavigate();
  const { payrolls, loading, fetchPayrolls, generatePayroll, approvePayroll, lockPayroll, generatePayslip } = usePayrollStore();
  const { items: periods, fetchItems: fetchPeriods } = usePayrollPeriodStore();
  const { items: employees, fetchItems: fetchEmployees } = useEmployeesStore();
  const [selectedPeriod, setSelectedPeriod] = useState('');

  useEffect(() => {
    fetchPeriods();
    fetchEmployees({ per_page: 1000 });
  }, [fetchPeriods, fetchEmployees]);

  useEffect(() => {
    if (selectedPeriod) {
      fetchPayrolls({ payroll_period_id: selectedPeriod });
    }
  }, [selectedPeriod, fetchPayrolls]);

  const handleGenerate = async () => {
    if (!selectedPeriod) return alert("Select a period first");
    await generatePayroll({ payroll_period_id: selectedPeriod });
    fetchPayrolls({ payroll_period_id: selectedPeriod });
  };

  return (
    <div className="p-6 max-w-7xl mx-auto">
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h1 className="text-2xl font-bold text-gray-900">Payroll Processing</h1>

        <div className="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
          <select
            className="w-full sm:w-auto border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500"
            value={selectedPeriod}
            onChange={(e) => setSelectedPeriod(e.target.value)}
          >
            <option value="">Select Period</option>
            {periods.map(p => (
              <option key={p.id} value={p.id}>{p.month}/{p.year} ({p.status})</option>
            ))}
          </select>

          <button onClick={handleGenerate} disabled={loading || !selectedPeriod} className="w-full sm:w-auto justify-center bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg flex items-center disabled:opacity-50">
            <Play className="w-4 h-4 mr-2" /> Generate Payroll
          </button>
        </div>
      </div>

      {loading ? (
        <div className="text-center py-12">Processing...</div>
      ) : (
        <div className="bg-white rounded-lg shadow overflow-hidden">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gross Pay</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deductions</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Pay</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {payrolls.map((p) => {
                const gross = parseFloat(p.basic_salary) + parseFloat(p.hra) + parseFloat(p.other_allowance) + parseFloat(p.overtime_pay);
                const deductions = parseFloat(p.pf) + parseFloat(p.esic) + parseFloat(p.tds) + parseFloat(p.late_penalty);
                return (
                  <tr key={p.id}>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {p.employee ? `${p.employee.full_name}` : `EMP-${p.employee_id}`}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₹{gross.toFixed(2)}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-red-500">₹{deductions.toFixed(2)}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">₹{p.net_salary}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm">
                      <span className="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100">{p.status}</span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                      {p.status === 'Draft' && (
                        <button onClick={() => approvePayroll(p.id)} title="Approve" className="text-green-600 hover:text-green-900"><CheckCircle className="w-4 h-4 inline" /></button>
                      )}
                      {p.status === 'Approved' && (
                        <button onClick={() => lockPayroll(p.id)} title="Lock" className="text-red-600 hover:text-red-900"><Lock className="w-4 h-4 inline" /></button>
                      )}
                      {(p.status === 'Approved' || p.status === 'Locked' || p.status === 'Paid') && !p.payslip && (
                        <button onClick={async () => {
                          try {
                            const res = await generatePayslip(p.id);
                            fetchPayrolls({ payroll_period_id: selectedPeriod });
                            if (res && res.payslip) {
                              navigate(`/dashboard/payslip/${res.payslip.id}`);
                            }
                          } catch (e) {
                            alert("Failed to generate payslip");
                          }
                        }} title="Generate Payslip" className="text-blue-600 hover:text-blue-900"><FileText className="w-4 h-4 inline" /></button>
                      )}
                      {p.payslip && (
                        <button onClick={() => navigate(`/dashboard/payslip/${p.payslip.id}`)} title="View Payslip" className="text-purple-600 hover:text-purple-900"><FileText className="w-4 h-4 inline" /></button>
                      )}
                    </td>
                  </tr>
                );
              })}
              {payrolls.length === 0 && selectedPeriod && (
                <tr>
                  <td colSpan="6" className="px-6 py-4 text-center text-sm text-gray-500">No payroll records generated for this period yet.</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
};

export default PayrollGenerationPage;
