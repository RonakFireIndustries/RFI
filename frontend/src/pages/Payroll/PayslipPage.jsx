import React, { useEffect, useState } from 'react';
import api from '../../services/api';
import { Printer, ArrowLeft, AlertTriangle } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import { formatDate } from '../../utils/date';

const numberToWords = (num) => {
  const a = ['', 'One ', 'Two ', 'Three ', 'Four ', 'Five ', 'Six ', 'Seven ', 'Eight ', 'Nine ', 'Ten ', 'Eleven ', 'Twelve ', 'Thirteen ', 'Fourteen ', 'Fifteen ', 'Sixteen ', 'Seventeen ', 'Eighteen ', 'Nineteen '];
  const b = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
  if (!num) return '';
  num = Math.floor(num);
  if ((num = num.toString()).length > 9) return 'overflow';
  let n = ('000000000' + num).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/);
  if (!n) return; let str = '';
  str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + ' ' + a[n[1][1]]) + 'Crore ' : '';
  str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + ' ' + a[n[2][1]]) + 'Lakh ' : '';
  str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + ' ' + a[n[3][1]]) + 'Thousand ' : '';
  str += (n[4] != 0) ? (a[Number(n[4])] || b[n[4][0]] + ' ' + a[n[4][1]]) + 'Hundred ' : '';
  str += (n[5] != 0) ? ((str != '') ? 'and ' : '') + (a[Number(n[5])] || b[n[5][0]] + ' ' + a[n[5][1]]) : '';
  return str.trim() + ' Only';
};

// parseFloat(null) === NaN, so every money field goes through this first.
const num = (value) => {
  const parsed = parseFloat(value);
  return Number.isFinite(parsed) ? parsed : 0;
};

const PayslipPage = () => {
  const [payslip, setPayslip] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const navigate = useNavigate();

  useEffect(() => {
    const id = window.location.pathname.split('/').pop();
    // Two entry points share this page:
    //   /dashboard/payslip/:id        -> a generated payslip row
    //   /dashboard/payroll-report/:id -> the payroll row itself (self-service,
    //                                    works before a payslip exists)
    const byPayroll = window.location.pathname.includes('payroll-report');
    const endpoint = byPayroll ? `/payroll/${id}` : `/payslips/${id}`;

    api.get(endpoint)
      .then(res => {
        setPayslip(byPayroll ? { payroll: res.data } : res.data);
        setLoading(false);
      })
      .catch(err => {
        // The owner of a payslip/payroll can always open it; anyone else needs
        // payroll.view. Surface the real reason instead of spinning forever.
        setError(err?.response?.data?.message
          || (err?.response?.status === 403
            ? 'You do not have permission to view this payslip.'
            : 'Unable to load this payslip.'));
        setLoading(false);
      });
  }, []);

  const handlePrint = () => {
    window.print();
  };

  if (loading) return <div className="p-8">Loading Payslip...</div>;

  if (error) {
    return (
      <div className="p-8">
        <div className="max-w-lg mx-auto bg-white border border-amber-300 rounded-lg shadow-sm p-6 text-center">
          <AlertTriangle className="w-10 h-10 text-amber-500 mx-auto mb-3" />
          <h2 className="text-lg font-semibold mb-2">Payslip unavailable</h2>
          <p className="text-gray-600 text-sm mb-4">{error}</p>
          <button
            onClick={() => navigate(-1)}
            className="inline-flex items-center bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded font-medium"
          >
            <ArrowLeft className="w-4 h-4 mr-2" /> Back
          </button>
        </div>
      </div>
    );
  }

  if (!payslip || !payslip.payroll) return <div className="p-8">Payslip not found</div>;

  const { payroll } = payslip;
  const { employee, payroll_period: period = {} } = payroll;

  // Attendance-driven breakdown: attached by the backend so the employee can
  // see exactly how many days were paid and why.
  const breakdown = payroll.breakdown || null;

  const basic = num(payroll.basic_salary);
  const hra = num(payroll.hra);
  const otherAllowance = num(payroll.other_allowance);
  const conveyance = num(payroll.conveyance);
  const specialAllowance = num(payroll.special_allowance);
  const siteAllowance = num(payroll.site_allowance);
  const travelAllowance = num(payroll.travel_allowance);
  const foodAllowance = num(payroll.food_allowance);
  const overtimePay = num(payroll.overtime_pay);
  const bonuses = num(payroll.bonuses);

  const pf = num(payroll.pf);
  const pt = num(payroll.pt);
  const esic = num(payroll.esic);
  const tds = num(payroll.tds);
  const otherDeductions = num(payroll.other_deductions);
  const advance = num(payroll.salary_advance);
  const latePenalty = num(payroll.late_penalty);

  const earningsRows = [
    { label: 'Basic Salary', value: basic },
    { label: 'Hra', value: hra },
    { label: 'Other Allowance', value: otherAllowance },
    { label: 'Conveyance Allowance', value: conveyance },
    { label: 'Special Allowance', value: specialAllowance },
    { label: 'Site Allowance', value: siteAllowance },
    { label: 'Travel Allowance', value: travelAllowance },
    { label: 'Food Allowance', value: foodAllowance },
    { label: 'Overtime', value: overtimePay },
    { label: 'Bonus', value: bonuses },
  ].filter(row => row.value > 0);

  const deductionRows = [
    { label: 'Provident Fund', value: pf },
    { label: 'Prof tax', value: pt },
    { label: 'ESIC', value: esic },
    { label: 'TDS', value: tds },
    { label: 'Advance', value: advance },
    { label: 'Late Deduction', value: latePenalty },
    { label: 'Other Deductions', value: otherDeductions },
  ].filter(row => row.value > 0);

  const totalEarnings = earningsRows.reduce((sum, row) => sum + row.value, 0);
  const totalDeductions = deductionRows.reduce((sum, row) => sum + row.value, 0);
  const netSalary = num(payroll.net_salary) || (totalEarnings - totalDeductions);

  const monthName = period.month ? new Date(2026, period.month - 1).toLocaleString('default', { month: 'long' }) : '';

  // Days column: prefer the live breakdown; older payrolls fall back to the
  // summary columns stored on the row.
  const days = breakdown
    ? [
        { label: 'Working Days', value: num(breakdown.total_working_days) },
        { label: 'Days Present', value: num(breakdown.present_days) },
        { label: 'Half Days', value: num(breakdown.half_days), showZero: false },
        { label: 'Paid Leaves', value: num(breakdown.paid_leaves), showZero: false },
        { label: 'Unpaid Leaves', value: num(breakdown.unpaid_leaves), showZero: false },
        { label: 'Absent Days', value: num(breakdown.absent_days), showZero: false },
        { label: 'Late Marks', value: num(breakdown.late_marks), showZero: false },
        { label: 'Overtime (Hrs)', value: num(breakdown.overtime_hours), showZero: false },
        { label: 'Paid Days =>', value: num(payroll.paid_days ?? breakdown.paid_days), bold: true },
      ]
    : [
        { label: 'Days Present', value: num(payroll.present_days) },
        { label: 'Weekly Holidays', value: num(payroll.working_days) - num(payroll.present_days) },
        { label: 'Total Days =>', value: num(payroll.working_days), bold: true },
      ];

  return (
    <div className="bg-gray-100 min-h-screen py-8 print:bg-white print:py-0">
      <div className="max-w-[850px] mx-auto">
        {/* Actions Header */}
        <div className="flex justify-between items-center mb-6 print:hidden px-4">
          <button onClick={() => navigate(-1)} className="flex items-center text-gray-600 hover:text-gray-900">
            <ArrowLeft className="w-5 h-5 mr-2" /> Back
          </button>
          <div className="space-x-4">
            <button onClick={handlePrint} className="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded shadow-sm flex items-center inline-flex font-medium">
              <Printer className="w-4 h-4 mr-2" /> Print / Save PDF
            </button>
          </div>
        </div>

        {/* Payslip Format */}
        <div className="bg-white p-8 shadow-xl print:shadow-none print:p-0">
          <div className="text-center mb-4 leading-tight">
            <h1 className="text-xl font-bold font-serif mb-1">RONAK FIRE INDUSTRIES</h1>
            <p className="text-sm">E-1, KASTURI VANDANA COMPLEX, KASURI ESTATE, FATAK ROAD,</p>
            <p className="text-sm">BHAYANDAR EAST THANE 401105</p>
            <h2 className="text-base font-bold mt-3">Payslip for the Month of :{monthName} {period.year}</h2>
          </div>

          <table className="w-full text-[13px] border-collapse border border-black mb-2">
            <tbody>
              <tr>
                <td className="w-1/2 p-1 border-r border-black align-top">
                  <div className="grid grid-cols-[100px_1fr] gap-x-2 leading-snug">
                    <span>ID/Name</span><span>: {employee.emp_id || employee.id} {employee.full_name || `${employee.first_name || ''} ${employee.last_name || ''}`.trim()}</span>
                    <span>Designation</span><span>: {employee.designation?.name || 'STAFF'}</span>
                    <span>PF No/UAN</span><span>: {employee.pf_number || '-'} UAN : {employee.uan || '-'}</span>
                    <span>ESIC No.</span><span>: {employee.esic_number || '-'}</span>
                  </div>
                </td>
                <td className="w-1/2 p-1 align-top">
                  <div className="grid grid-cols-[100px_1fr] gap-x-2 leading-snug">
                    <span>Location</span><span>: FIELD STAFF</span>
                    <span>DOJ / PAN</span><span>: {formatDate(employee.joining_date, '-')} / {employee.pan_number || '-'}</span>
                    <span>AADHAR No.</span><span>: {employee.aadhaar_number || '-'}</span>
                    <span>Dt.of Birth</span><span>: {formatDate(employee.dob, '-')}</span>
                    <span>S.B. A/c No</span><span>: {employee.bank_account_number || '-'}</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <table className="w-full text-[13px] border-collapse border border-black">
            <thead className="bg-muted">
              <tr className="border-b border-black">
                <th className="font-normal p-1 text-center border-r border-black w-[25%]">Days Description</th>
                <th className="font-normal p-1 text-center border-r border-black w-[25%]">Rate Per Month</th>
                <th className="font-normal p-1 text-center border-r border-black w-[25%]">Earnings</th>
                <th className="font-normal p-1 text-center w-[25%]">Deductions</th>
              </tr>
            </thead>
            <tbody>
              <tr className="align-top">
                <td className="border-r border-black p-1">
                  {days.map((row, index) => (
                    <div
                      key={row.label}
                      className={`flex justify-between ${index === days.length - 1 ? 'mt-2 font-medium' : 'mb-1'}`}
                    >
                      <span>{row.label}</span>
                      <span>{row.showZero === false && row.value === 0 ? '-' : row.value}</span>
                    </div>
                  ))}
                </td>
                <td className="border-r border-black p-1 pr-8">
                  {earningsRows.map(row => (
                    <div key={row.label} className="flex justify-between mb-1"><span>{row.label}</span></div>
                  ))}
                  {earningsRows.length === 0 && <div className="mb-1 text-gray-400">No earnings</div>}
                </td>
                <td className="border-r border-black p-1 text-right pr-2">
                  {earningsRows.map(row => (
                    <div key={row.label} className="mb-1">{row.value.toFixed(2)}</div>
                  ))}
                  {earningsRows.length === 0 && <div className="mb-1 text-gray-400">0.00</div>}
                </td>
                <td className="p-1 pr-2">
                  {deductionRows.length === 0
                    ? <div className="text-gray-400">No deductions</div>
                    : deductionRows.map(row => (
                        <div key={row.label} className="flex justify-between mb-1">
                          <span>{row.label}</span><span>{row.value.toFixed(2)}</span>
                        </div>
                      ))}
                </td>
              </tr>
              <tr className="border-t border-b border-black font-bold">
                <td className="border-r border-black p-1 text-center">Total</td>
                <td className="border-r border-black p-1 text-right pr-2">{totalEarnings.toFixed(2)}</td>
                <td className="border-r border-black p-1 text-right pr-2">{totalEarnings.toFixed(2)}</td>
                <td className="p-1 text-right pr-2">{totalDeductions.toFixed(2)}</td>
              </tr>
              <tr>
                <td colSpan="2" className="border-r border-black p-1 italic align-middle">
                  Rs. {numberToWords(netSalary)}
                </td>
                <td colSpan="2" className="p-1">
                  <div className="flex justify-between font-bold items-center">
                    <span>Net Salary</span>
                    <span className="pr-1">{netSalary.toFixed(2)}</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          {breakdown && (
            <p className="text-[11px] text-gray-600 mt-3 leading-snug">
              Salary is pro-rated on attendance: earnings = configured salary x (paid days / working days).
              Weekly offs and gazetted holidays are not deducted. Approved paid leave keeps the day paid,
              LWP / unpaid leave and absent days do not. Half days pay half.
              Late marks: every 3rd costs half a day, every 5th a full day.
              {breakdown.total_working_days > 0 && (
                <> Paid {num(payroll.paid_days ?? breakdown.paid_days)} of {num(breakdown.total_working_days)} working days.</>
              )}
            </p>
          )}
        </div>
      </div>
    </div>
  );
};

export default PayslipPage;
