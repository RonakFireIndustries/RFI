import React, { useEffect, useState } from 'react';
import api from '../../services/api';
import { Download, Printer, ArrowLeft } from 'lucide-react';
import { useNavigate } from 'react-router-dom';

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

const PayslipPage = () => {
  const [payslip, setPayslip] = useState(null);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  useEffect(() => {
    const id = window.location.pathname.split('/').pop();
    api.get(`/payslips/${id}`).then(res => {
      setPayslip(res.data);
      setLoading(false);
    });
  }, []);

  const handlePrint = () => {
    window.print();
  };

  const handleDownload = () => {
    window.print(); // Using print-to-pdf for image generation as standard practice
  };

  if (loading) return <div className="p-8">Loading Payslip...</div>;
  if (!payslip) return <div className="p-8">Payslip not found</div>;

  const { payroll } = payslip;
  const { employee, payroll_period: period = {} } = payroll;

  const basic = parseFloat(payroll.basic_salary) || 0;
  const hra = parseFloat(payroll.hra) || 0;
  const otherAllowance = parseFloat(payroll.other_allowance) || 0;
  const conveyance = parseFloat(payroll.conveyance) || 0;

  const pf = parseFloat(payroll.pf) || 0;
  const pt = parseFloat(payroll.pt) || 0;
  const esic = parseFloat(payroll.esic) || 0;
  const advance = parseFloat(payroll.late_penalty) || 0; // Using late_penalty as deduction example if advance not present
  const tds = parseFloat(payroll.tds) || 0;

  const totalEarnings = basic + hra + otherAllowance + conveyance;
  const totalDeductions = pf + pt + esic + advance + tds;
  const netSalary = parseFloat(payroll.net_salary) || (totalEarnings - totalDeductions);

  const monthName = period.month ? new Date(2026, period.month - 1).toLocaleString('default', { month: 'long' }) : '';

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
                    <span>ID/Name</span><span>: {employee.emp_id || employee.id} {employee.full_name || `${employee.first_name} ${employee.last_name}`}</span>
                    <span>Designation</span><span>: {employee.designation?.name || 'STAFF'}</span>
                    <span>PF No/UAN</span><span>: {employee.pf_number || '-'} UAN : {employee.uan || '-'}</span>
                    <span>ESIC No.</span><span>: {employee.esic_number || '-'}</span>
                  </div>
                </td>
                <td className="w-1/2 p-1 align-top">
                  <div className="grid grid-cols-[100px_1fr] gap-x-2 leading-snug">
                    <span>Location</span><span>: FIELD STAFF</span>
                    <span>DOJ / PAN</span><span>: {employee.joining_date ? new Date(employee.joining_date).toLocaleDateString('en-GB') : '-'} / {employee.pan_number || '-'}</span>
                    <span>AADHAR No.</span><span>: {employee.aadhaar_number || '-'}</span>
                    <span>Dt.of Birth</span><span>: {employee.dob ? new Date(employee.dob).toLocaleDateString('en-GB') : '-'}</span>
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
              <tr className="align-top h-48">
                <td className="border-r border-black p-1">
                  <div className="flex justify-between mb-1"><span>Days Present</span><span>{(payroll.present_days || 26)}</span></div>
                  <div className="flex justify-between mb-1"><span>Weekly Holidays</span><span>{((payroll.working_days || 31) - (payroll.present_days || 26))}</span></div>
                  <div className="flex justify-between mt-2 ml-4 font-medium"><span>Total Days =&gt;</span><span>{(payroll.working_days || 31)}</span></div>
                </td>
                <td className="border-r border-black p-1 pr-8">
                  <div className="flex justify-between mb-1"><span>Basic Salary</span></div>
                  <div className="flex justify-between mb-1"><span>Hra</span></div>
                  <div className="flex justify-between mb-1"><span>Other Allowance</span></div>
                  {conveyance > 0 && <div className="flex justify-between mb-1"><span>Conveyance Allowance</span></div>}
                </td>
                <td className="border-r border-black p-1 text-right pr-2">
                  <div className="mb-1">{basic.toFixed(2)}</div>
                  <div className="mb-1">{hra.toFixed(2)}</div>
                  <div className="mb-1">{otherAllowance.toFixed(2)}</div>
                  {conveyance > 0 && <div className="mb-1">{conveyance.toFixed(2)}</div>}
                </td>
                <td className="p-1 pr-2">
                  <div className="flex justify-between mb-1"><span>Provident Fund</span><span>{pf > 0 ? pf.toFixed(2) : '-'}</span></div>
                  <div className="flex justify-between mb-1"><span>Prof tax</span><span>{pt > 0 ? pt.toFixed(2) : '-'}</span></div>
                  <div className="flex justify-between mb-1"><span>ESIC</span><span>{esic > 0 ? esic.toFixed(2) : '-'}</span></div>
                  <div className="flex justify-between mb-1"><span>Advance / Late</span><span>{advance > 0 ? advance.toFixed(2) : '-'}</span></div>
                  <div className="flex justify-between mb-1"><span>TDS</span><span>{tds > 0 ? tds.toFixed(2) : '-'}</span></div>
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
        </div>
      </div>
    </div>
  );
};

export default PayslipPage;
