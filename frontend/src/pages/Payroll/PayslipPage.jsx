import React, { useEffect, useState } from 'react';
import api from '../../services/api';
import { Download, Printer } from 'lucide-react';

const PayslipPage = ({ match }) => {
  const [payslip, setPayslip] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Basic route extraction, assuming standard react-router setup
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
    // If download endpoint exists
    window.open(`http://localhost:8000/api/v1/payslips/${payslip.id}/download`, '_blank');
  };

  if (loading) return <div>Loading Payslip...</div>;
  if (!payslip) return <div>Payslip not found</div>;

  const { payroll } = payslip;
  const { employee, payrollPeriod: period } = payroll;

  return (
    <div className="p-8 max-w-4xl mx-auto bg-white my-8 shadow-lg print:shadow-none print:m-0">
      <div className="flex justify-between items-center border-b pb-6 mb-6 print:hidden">
        <h1 className="text-2xl font-bold">Payslip View</h1>
        <div className="space-x-4">
          <button onClick={handlePrint} className="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded flex items-center inline-flex">
            <Printer className="w-4 h-4 mr-2" /> Print
          </button>
          <button onClick={handleDownload} className="bg-orange-500 text-white hover:bg-orange-600 px-4 py-2 rounded flex items-center inline-flex">
            <Download className="w-4 h-4 mr-2" /> Download
          </button>
        </div>
      </div>

      <div className="text-center mb-8 border-b pb-4">
        <h2 className="text-3xl font-bold uppercase tracking-widest text-orange-600">Ronak Fire Industries</h2>
        <p className="text-gray-500 mt-2">Payslip for the period {period.month}/{period.year}</p>
      </div>

      <div className="grid grid-cols-2 gap-8 mb-8">
        <div>
          <h3 className="font-bold border-b mb-2 pb-1 text-gray-700">Employee Details</h3>
          <p><span className="font-semibold w-32 inline-block">Name:</span> {employee.first_name} {employee.last_name}</p>
          <p><span className="font-semibold w-32 inline-block">ID:</span> EMP-{employee.id}</p>
          <p><span className="font-semibold w-32 inline-block">Department:</span> {employee.department?.name || 'N/A'}</p>
          <p><span className="font-semibold w-32 inline-block">Designation:</span> {employee.designation?.name || 'N/A'}</p>
        </div>
        <div>
          <h3 className="font-bold border-b mb-2 pb-1 text-gray-700">Attendance Summary</h3>
          <p><span className="font-semibold w-32 inline-block">Total Days:</span> {payroll.working_days + payroll.unpaid_leaves + payroll.absent_days}</p>
          <p><span className="font-semibold w-32 inline-block">Payable Days:</span> {payroll.working_days}</p>
          <p><span className="font-semibold w-32 inline-block">Present:</span> {payroll.present_days}</p>
          <p><span className="font-semibold w-32 inline-block">Absent/LWP:</span> {parseFloat(payroll.absent_days) + parseFloat(payroll.unpaid_leaves)}</p>
        </div>
      </div>

      <div className="grid grid-cols-2 gap-8 mb-8">
        {/* Earnings */}
        <div>
          <h3 className="font-bold border-b mb-2 pb-1 text-gray-700 bg-gray-50 p-2">Earnings</h3>
          <div className="flex justify-between py-1"><span className="text-gray-600">Basic Salary</span><span>₹{payroll.basic_salary}</span></div>
          <div className="flex justify-between py-1"><span className="text-gray-600">HRA</span><span>₹{payroll.hra}</span></div>
          <div className="flex justify-between py-1"><span className="text-gray-600">Conveyance</span><span>₹{payroll.conveyance}</span></div>
          <div className="flex justify-between py-1"><span className="text-gray-600">Allowances</span><span>₹{payroll.other_allowance}</span></div>
          <div className="flex justify-between py-1 font-bold border-t mt-2 pt-2">
            <span>Total Earnings</span>
            <span>₹{(parseFloat(payroll.basic_salary) + parseFloat(payroll.hra) + parseFloat(payroll.conveyance) + parseFloat(payroll.other_allowance)).toFixed(2)}</span>
          </div>
        </div>

        {/* Deductions */}
        <div>
          <h3 className="font-bold border-b mb-2 pb-1 text-gray-700 bg-gray-50 p-2">Deductions</h3>
          <div className="flex justify-between py-1"><span className="text-gray-600">PF</span><span>₹{payroll.pf}</span></div>
          <div className="flex justify-between py-1"><span className="text-gray-600">ESIC</span><span>₹{payroll.esic}</span></div>
          <div className="flex justify-between py-1"><span className="text-gray-600">Professional Tax</span><span>₹{payroll.pt}</span></div>
          <div className="flex justify-between py-1"><span className="text-gray-600">TDS</span><span>₹{payroll.tds}</span></div>
          <div className="flex justify-between py-1 font-bold border-t mt-2 pt-2">
            <span>Total Deductions</span>
            <span>₹{(parseFloat(payroll.pf) + parseFloat(payroll.esic) + parseFloat(payroll.pt) + parseFloat(payroll.tds)).toFixed(2)}</span>
          </div>
        </div>
      </div>

      <div className="bg-orange-50 p-4 rounded-lg flex justify-between items-center mb-16 border border-orange-200">
        <span className="text-xl font-bold text-gray-800">Net Salary Payable</span>
        <span className="text-3xl font-bold text-orange-600">₹{payroll.net_salary}</span>
      </div>

      <div className="flex justify-between mt-12 text-gray-400 text-sm border-t pt-8">
        <div className="text-center w-48">
          <div className="border-b border-gray-300 h-8 mb-2"></div>
          Employer Signature
        </div>
        <div className="text-center w-48">
          <div className="border-b border-gray-300 h-8 mb-2"></div>
          Employee Signature
        </div>
      </div>
    </div>
  );
};

export default PayslipPage;
