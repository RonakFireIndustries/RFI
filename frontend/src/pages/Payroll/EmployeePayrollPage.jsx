import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../../services/api';
import { FileText, Plus } from 'lucide-react';
import PermissionGate from '../../components/Guards/PermissionGate';

const EmployeePayrollPage = () => {
  const navigate = useNavigate();
  const [payrolls, setPayrolls] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    let cancelled = false;

    // /my-payroll is the self-service endpoint: it reads the payroll rows of the
    // signed-in user's employee profile and needs no payroll.view permission.
    // The admin listing endpoint (/payroll) returns 403 for most roles.
    api.get('/my-payroll')
      .then((res) => {
        if (cancelled) return;
        setPayrolls(Array.isArray(res.data) ? res.data : []);
      })
      .catch((err) => {
        if (cancelled) return;
        setError(err?.response?.data?.message || 'Could not load your payroll history.');
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });

    return () => { cancelled = true; };
  }, []);

  const periodLabel = (p) => {
    const period = p.payroll_period || p.payrollPeriod || null;
    if (period?.month) return `${period.month}/${period.year}`;
    if (p.month) return `${p.month}/${p.year}`;
    return 'Unknown Period';
  };

  const netPay = (p) => {
    const value = p.net_salary ?? p.net_pay ?? null;
    return value === null || value === '' ? '-' : `₹${Number(value).toLocaleString('en-IN')}`;
  };

  const generatePayslip = async (p) => {
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
  };

  if (loading) return <div className="p-6">Loading your payroll history...</div>;

  return (
    <div className="p-6 max-w-6xl mx-auto">
      <h1 className="text-2xl font-bold text-gray-900 mb-6">My Payroll History</h1>

      {error && (
        <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {error}
        </div>
      )}

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
                  {periodLabel(p)}
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">
                  {netPay(p)}
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm">
                  <span className="px-2 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">{p.status}</span>
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                  {/* The report is always viewable by its owner. Until a payslip
                      row exists, open the payroll record directly instead. */}
                  <button
                    onClick={() => navigate(p.payslip ? `/dashboard/payslip/${p.payslip.id}` : `/dashboard/payroll-report/${p.id}`)}
                    title={p.payslip ? 'View payslip' : 'View payroll report'}
                    className="text-orange-600 hover:text-orange-900 inline-flex items-center"
                  >
                    <FileText className="w-4 h-4 mr-1" /> View
                  </button>
                  {!p.payslip && (
                    <PermissionGate module="payroll" action="manage" subject="payslips">
                      <button
                        onClick={() => generatePayslip(p)}
                        className="text-orange-600 hover:text-orange-900 inline-flex items-center text-xs font-medium"
                      >
                        <Plus className="w-3.5 h-3.5 mr-1" /> Generate
                      </button>
                    </PermissionGate>
                  )}
                </td>
              </tr>
            ))}
            {payrolls.length === 0 && !error && (
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
