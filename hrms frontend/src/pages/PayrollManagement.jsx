import React from 'react';
import { 
  Download, Play, Eye, SlidersHorizontal, User, DollarSign, CheckCircle2
} from 'lucide-react';

const transactions = [
  {
    id: 1,
    name: 'Arthur Morgan',
    role: 'Project Manager',
    amount: '$8,450.00',
    date: 'Oct 12, 2023',
    status: 'Paid',
    statusColor: 'text-[#38A169]',
    dotColor: 'bg-[#38A169]',
    img: '/static/images/avatar/1.jpg',
  },
  {
    id: 2,
    name: 'John Smith',
    role: 'Foreman (Electrical)',
    amount: '$5,120.00',
    date: 'Oct 14, 2023',
    status: 'Processing',
    statusColor: 'text-[#3182CE]',
    dotColor: 'bg-[#3182CE]',
    img: '/static/images/avatar/2.jpg',
  },
  {
    id: 3,
    name: 'Sarah Kovacs',
    role: 'Site Engineer',
    amount: '$6,890.00',
    date: '--',
    status: 'Pending',
    statusColor: 'text-[#DD6B20]',
    dotColor: 'bg-[#DD6B20]',
    img: '/static/images/avatar/3.jpg',
  },
  {
    id: 4,
    name: 'Bill Jensen',
    role: 'Lead Crane Op',
    amount: '$7,200.00',
    date: 'Oct 12, 2023',
    status: 'Paid',
    statusColor: 'text-[#38A169]',
    dotColor: 'bg-[#38A169]',
    img: '/static/images/avatar/4.jpg',
  }
];

export default function PayrollManagement() {
  return (
    <div className="w-full">
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
          <h1 className="text-3xl font-extrabold text-[#0B1B36] mb-1">
            Financial Overview
          </h1>
          <p className="text-[#718096]">
            October 2023 Payroll Cycle
          </p>
        </div>
        <div className="flex gap-3">
          <button className="flex items-center px-4 py-2 bg-[#EDF2F7] text-[#4A5568] rounded-xl font-semibold hover:bg-gray-200 transition-colors">
            <Download className="w-4 h-4 mr-2" />
            Export Report
          </button>
          <button className="flex items-center px-4 py-2 bg-[#0B1B36] text-white rounded-xl font-semibold hover:bg-[#081428] transition-colors shadow-sm">
            <Play className="w-4 h-4 mr-2 fill-white" />
            Run Payroll
          </button>
        </div>
      </div>

      {/* Top Summary Cards */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {/* Total Payout */}
        <div className="bg-[#0B1B36] rounded-2xl p-6 text-white shadow-sm flex flex-col justify-between relative overflow-hidden">
          <div className="absolute right-[-20px] bottom-[-20px] opacity-10">
            <DollarSign className="w-48 h-48" />
          </div>
          <div>
            <h3 className="text-sm font-semibold text-[#A0AEC0] mb-2">Total Monthly Payout</h3>
            <div className="text-4xl font-extrabold tracking-tight mb-4">$1,482,900.00</div>
          </div>
          <div className="text-sm font-semibold text-[#48BB78] flex items-center">
            ↗ +4.2% from last month
          </div>
        </div>

        {/* Salaries Processed */}
        <div className="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
          <div className="flex justify-between items-start mb-2">
            <h3 className="text-sm font-semibold text-[#718096]">Salaries Processed</h3>
            <span className="text-[10px] font-bold text-[#3182CE] bg-[#EBF8FF] px-2 py-1 rounded tracking-wider">
              IN PROGRESS
            </span>
          </div>
          <div className="text-4xl font-extrabold text-[#0B1B36] mb-4">82%</div>
          <div>
            <div className="w-full h-2 bg-gray-100 rounded-full mb-2 overflow-hidden">
              <div className="h-full bg-[#0B1B36] rounded-full" style={{ width: '82%' }} />
            </div>
            <div className="text-xs font-semibold text-[#718096]">245 of 298 processed</div>
          </div>
        </div>

        {/* Tax Compliance */}
        <div className="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
          <div className="flex justify-between items-start mb-2">
            <h3 className="text-sm font-semibold text-[#718096]">Tax Compliance</h3>
            <span className="text-[10px] font-bold text-[#38A169] bg-[#C6F6D5] px-2 py-1 rounded tracking-wider">
              READY
            </span>
          </div>
          <div className="text-4xl font-extrabold text-[#0B1B36] mb-4">100%</div>
          <div>
            <div className="w-full h-2 bg-gray-100 rounded-full mb-2 overflow-hidden">
              <div className="h-full bg-[#38A169] rounded-full" style={{ width: '100%' }} />
            </div>
            <div className="text-xs font-semibold text-[#718096]">All filings generated</div>
          </div>
        </div>
      </div>

      {/* Middle Section */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {/* Budget Allocation */}
        <div className="lg:col-span-2 bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
          <div className="flex justify-between items-center mb-8">
            <h2 className="text-xl font-bold text-[#0B1B36]">Budget Allocation</h2>
            <div className="flex items-center gap-4 text-xs font-semibold text-[#4A5568]">
              <div className="flex items-center"><div className="w-2.5 h-2.5 rounded-full bg-[#0B1B36] mr-2"/> Earnings</div>
              <div className="flex items-center"><div className="w-2.5 h-2.5 rounded-full bg-[#ED8936] mr-2"/> Deductions</div>
            </div>
          </div>

          <div className="space-y-6">
            {/* Base Salary */}
            <div>
              <div className="flex justify-between text-sm font-bold text-[#1A202C] mb-2">
                <span>Base Salary</span>
                <span>$1,200,000</span>
              </div>
              <div className="w-full h-8 bg-gray-200 rounded flex overflow-hidden">
                <div className="bg-[#0B1B36] h-full" style={{ width: '85%' }} />
                <div className="bg-[#CBD5E0] h-full" style={{ width: '15%' }} />
              </div>
            </div>

            {/* Overtime & Bonuses */}
            <div>
              <div className="flex justify-between text-sm font-bold text-[#1A202C] mb-2">
                <span>Overtime & Bonuses</span>
                <span>$282,900</span>
              </div>
              <div className="w-full h-8 bg-gray-200 rounded flex overflow-hidden">
                <div className="bg-[#0B1B36] h-full" style={{ width: '60%' }} />
                <div className="bg-[#CBD5E0] h-full" style={{ width: '40%' }} />
              </div>
            </div>

            {/* Taxes & Benefits */}
            <div>
              <div className="flex justify-between text-sm font-bold text-[#DD6B20] mb-2">
                <span>Taxes & Benefits (Deductions)</span>
                <span>-$342,000</span>
              </div>
              <div className="w-full h-8 bg-[#FEEBC8] rounded flex overflow-hidden">
                <div className="bg-[#ED8936] h-full" style={{ width: '75%' }} />
              </div>
            </div>
          </div>
        </div>

        {/* Quick Slip Preview */}
        <div className="bg-[#F7FAFC] border border-dashed border-[#CBD5E0] rounded-2xl p-6 flex flex-col justify-between">
          <div className="flex justify-between items-center mb-6">
            <h3 className="font-bold text-[#0B1B36]">Quick Slip Preview</h3>
            <Eye className="w-5 h-5 text-[#718096]" />
          </div>

          <div className="bg-white rounded-xl border border-gray-200 p-5 shadow-sm mb-4">
            <div className="flex justify-between items-start border-b border-gray-100 pb-4 mb-4">
              <div>
                <div className="text-[10px] font-bold text-[#718096] tracking-widest uppercase mb-1">EMPLOYEE ID</div>
                <div className="text-lg font-bold text-[#0B1B36]">BF-99201</div>
              </div>
              <div className="w-12 h-12 bg-gray-900 rounded opacity-80" />
            </div>

            <div className="space-y-3 text-sm font-semibold mb-6">
              <div className="flex justify-between text-[#4A5568]">
                <span>Basic Salary</span>
                <span className="text-[#1A202C]">$4,500.00</span>
              </div>
              <div className="flex justify-between text-[#4A5568]">
                <span>HRA Allowance</span>
                <span className="text-[#1A202C]">$1,200.00</span>
              </div>
              <div className="flex justify-between text-[#4A5568]">
                <span>Site Allowance</span>
                <span className="text-[#1A202C]">$850.00</span>
              </div>
              <div className="flex justify-between text-[#E53E3E]">
                <span>Tax (PF/ESI)</span>
                <span>-$420.00</span>
              </div>
            </div>

            <div className="flex justify-between items-center border-t border-gray-100 pt-4 mb-6">
              <span className="text-sm font-bold text-[#718096]">Net<br/>Payable</span>
              <span className="text-2xl font-extrabold text-[#0B1B36]">$6,130.00</span>
            </div>

            <button className="w-full bg-[#4A5568] hover:bg-[#2D3748] text-white py-2 rounded-lg text-sm font-bold transition-colors">
              Download PDF
            </button>
          </div>
          
          <p className="text-center text-xs text-[#718096] italic">
            Draft preview for: Marc Thompson (Site Lead)
          </p>
        </div>
      </div>

      {/* Transactions Table */}
      <div className="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden mb-8">
        <div className="p-6 border-b border-gray-200 flex justify-between items-center">
          <h2 className="text-xl font-bold text-[#0B1B36]">Recent Payroll Transactions</h2>
          <div className="flex gap-3">
            <button className="bg-white border border-gray-300 px-3 py-1.5 rounded-lg text-sm font-semibold text-[#4A5568]">
              All Departments ▾
            </button>
            <button className="bg-white border border-gray-300 p-1.5 rounded-lg text-[#4A5568]">
              <SlidersHorizontal className="w-5 h-5" />
            </button>
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-[#F7FAFC] border-b border-gray-200 text-xs font-bold text-[#718096] uppercase tracking-widest">
                <th className="p-4 pl-6">Employee</th>
                <th className="p-4">Designation</th>
                <th className="p-4">Amount</th>
                <th className="p-4">Date</th>
                <th className="p-4">Status</th>
                <th className="p-4 text-right pr-6">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {transactions.map((txn) => (
                <tr key={txn.id} className="hover:bg-gray-50 transition-colors">
                  <td className="p-4 pl-6">
                    <div className="flex items-center gap-3">
                      <img src={txn.img} alt={txn.name} className="w-10 h-10 rounded-full object-cover" />
                      <div className="font-bold text-[#1A202C]">{txn.name}</div>
                    </div>
                  </td>
                  <td className="p-4 text-sm font-semibold text-[#4A5568]">{txn.role}</td>
                  <td className="p-4 text-sm font-bold text-[#1A202C]">{txn.amount}</td>
                  <td className="p-4 text-sm text-[#718096]">{txn.date}</td>
                  <td className="p-4">
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-white border border-gray-200 ${txn.statusColor}`}>
                      <span className={`w-1.5 h-1.5 rounded-full ${txn.dotColor} mr-1.5`}></span>
                      {txn.status}
                    </span>
                  </td>
                  <td className="p-4 text-right pr-6">
                    {txn.status === 'Pending' ? (
                      <button className="bg-[#0B1B36] text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-[#081428] transition-colors">
                        Process Now
                      </button>
                    ) : (
                      <button className="text-sm font-bold text-[#4A5568] hover:text-[#1A202C]">
                        {txn.status === 'Processing' ? 'Track Bank' : 'View Receipt'}
                      </button>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        
        <div className="bg-white p-4 flex justify-center items-center border-t border-gray-200">
          <button className="text-sm font-bold text-[#0B1B36] hover:underline flex items-center">
            View All Employees <span>→</span>
          </button>
        </div>
      </div>
    </div>
  );
}
