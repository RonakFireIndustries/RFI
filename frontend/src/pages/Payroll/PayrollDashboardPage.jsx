import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { usePayrollStore } from '../../store/payrollStore';
import { usePayrollPeriodStore } from '../../store/payrollPeriodStore';
import { DollarSign, Users, CheckCircle, Clock, Lock, CreditCard, ArrowRight } from 'lucide-react';

const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-GB') : '--';

const StatCard = ({ icon: Icon, label, value, color }) => (
  <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex items-center gap-4">
    <div className={`w-12 h-12 rounded-lg flex items-center justify-center ${color}`}>
      <Icon className="w-6 h-6 text-white" />
    </div>
    <div>
      <p className="text-sm text-gray-500">{label}</p>
      <p className="text-2xl font-bold text-gray-900">{value}</p>
    </div>
  </div>
);

export default function PayrollDashboardPage() {
  const navigate = useNavigate();
  const { payrolls, fetchPayrolls } = usePayrollStore();
  const { items: periods, fetchItems: fetchPeriods } = usePayrollPeriodStore();
  const [latestPeriod, setLatestPeriod] = useState(null);

  useEffect(() => {
    fetchPayrolls();
    fetchPeriods();
  }, [fetchPayrolls, fetchPeriods]);

  useEffect(() => {
    if (periods.length > 0) {
      const sorted = [...periods].sort((a, b) => b.year - a.year || b.month - a.month);
      setLatestPeriod(sorted[0]);
    }
  }, [periods]);

  const draftCount = payrolls.filter(p => p.status === 'Draft').length;
  const approvedCount = payrolls.filter(p => p.status === 'Approved').length;
  const lockedCount = payrolls.filter(p => p.status === 'Locked').length;
  const paidCount = payrolls.filter(p => p.status === 'Paid').length;
  const totalGross = payrolls.reduce((s, p) => s + parseFloat(p.basic_salary || 0) + parseFloat(p.hra || 0) + parseFloat(p.other_allowance || 0) + parseFloat(p.overtime_pay || 0), 0);
  const totalNet = payrolls.reduce((s, p) => s + parseFloat(p.net_salary || 0), 0);

  return (
    <div className="p-6 max-w-7xl mx-auto space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Payroll Dashboard</h1>
          {latestPeriod && (
            <p className="text-sm text-gray-500 mt-1">
              Latest Period: {latestPeriod.month}/{latestPeriod.year} ({fmtDate(latestPeriod.start_date)} - {fmtDate(latestPeriod.end_date)})
            </p>
          )}
        </div>
        <button onClick={() => navigate('/dashboard/process-payroll')} className="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 text-sm">
          Go to Process Payroll <ArrowRight className="w-4 h-4" />
        </button>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard icon={DollarSign} label="Total Gross Pay" value={`₹${totalGross.toFixed(2)}`} color="bg-blue-500" />
        <StatCard icon={CreditCard} label="Total Net Pay" value={`₹${totalNet.toFixed(2)}`} color="bg-green-500" />
        <StatCard icon={Users} label="Total Employees" value={payrolls.length} color="bg-purple-500" />
        <StatCard icon={CheckCircle} label="Paid" value={paidCount} color="bg-emerald-500" />
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <StatCard icon={Clock} label="Draft" value={draftCount} color="bg-yellow-500" />
        <StatCard icon={CheckCircle} label="Approved" value={approvedCount} color="bg-green-500" />
        <StatCard icon={Lock} label="Locked" value={lockedCount} color="bg-red-500" />
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div className="p-5 border-b border-gray-100">
          <h2 className="font-semibold text-gray-900">Payroll Periods</h2>
        </div>
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">End</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Employees</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-200">
            {periods.slice(0, 5).map(p => {
              const count = payrolls.filter(r => r.payroll_period_id === p.id).length;
              return (
                <tr key={p.id} className="hover:bg-gray-50 cursor-pointer" onClick={() => navigate('/dashboard/process-payroll')}>
                  <td className="px-6 py-3 text-sm font-medium text-gray-900">{p.month}/{p.year}</td>
                  <td className="px-6 py-3 text-sm text-gray-500">{fmtDate(p.start_date)}</td>
                  <td className="px-6 py-3 text-sm text-gray-500">{fmtDate(p.end_date)}</td>
                  <td className="px-6 py-3">
                    <span className={`px-2 py-1 rounded-full text-xs font-semibold ${
                      p.status === 'Paid' ? 'bg-green-100 text-green-800' :
                      p.status === 'Locked' ? 'bg-red-100 text-red-800' :
                      p.status === 'Approved' ? 'bg-blue-100 text-blue-800' :
                      'bg-yellow-100 text-yellow-800'
                    }`}>{p.status}</span>
                  </td>
                  <td className="px-6 py-3 text-sm text-gray-500 text-right">{count}</td>
                </tr>
              );
            })}
            {periods.length === 0 && (
              <tr><td colSpan="5" className="px-6 py-4 text-center text-sm text-gray-500">No periods yet.</td></tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}
