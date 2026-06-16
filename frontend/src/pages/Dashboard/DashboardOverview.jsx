import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { 
  AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer,
  BarChart, Bar, Cell
} from 'recharts';
import { useBranchStore } from '../../store/branchStore';
import { 
  Banknote, Users, Rocket, AlertTriangle, ArrowUp, ArrowDown,
  ShoppingCart, UserPlus, FileText, Settings, Download, Plus, ShoppingBag
} from 'lucide-react';
import api from '../../services/api';

export default function DashboardOverview() {
  const navigate = useNavigate();
  const activeBranchId = useBranchStore(state => state.activeBranchId);
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchDashboardData = async () => {
      setLoading(true);
      try {
        const response = await api.get('/dashboard/summary');
        setData(response.data);
      } catch (error) {
        console.error('Failed to fetch dashboard summary', error);
      } finally {
        setLoading(false);
      }
    };
    fetchDashboardData();
  }, [activeBranchId]);

  if (loading) {
    return (
      <div className="flex justify-center items-center h-full">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-[#1a56db]"></div>
      </div>
    );
  }

  if (!data) return null;

  return (
    <div className="space-y-6 pb-12">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Overview</h1>
          <p className="text-sm text-gray-500">Welcome back. Here's what's happening today.</p>
        </div>
        <div className="flex space-x-3">
          <button className="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
            <Download className="w-4 h-4 mr-2" />
            Export Report
          </button>
          <button 
            onClick={() => navigate('/dashboard/sales')}
            className="flex items-center px-4 py-2 bg-[#1a56db] text-white rounded-md text-sm font-medium hover:bg-blue-700"
          >
            <Plus className="w-4 h-4 mr-2" />
            New Transaction
          </button>
        </div>
      </div>

      {/* KPI Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
          <div className="flex justify-between items-start mb-4">
            <div>
              <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Revenue</p>
              <h3 className="text-2xl font-bold text-gray-900">₹{numberWithCommas(data.revenue.total)}</h3>
            </div>
            <div className="p-2 bg-blue-50 rounded-lg text-[#1a56db]">
              <Banknote className="w-5 h-5" />
            </div>
          </div>
          <div className="flex items-center text-sm">
            <span className="text-green-600 font-medium flex items-center">
              <ArrowUp className="w-3 h-3 mr-1" />
              {data.revenue.growth}%
            </span>
            <span className="text-gray-400 ml-2">from last month</span>
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
          <div className="flex justify-between items-start mb-4">
            <div>
              <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Employees</p>
              <h3 className="text-2xl font-bold text-gray-900">{numberWithCommas(data.employees.total)}</h3>
            </div>
            <div className="p-2 bg-gray-100 rounded-lg text-gray-600">
              <Users className="w-5 h-5" />
            </div>
          </div>
          <div className="flex items-center text-sm">
            <span className="text-orange-500 font-medium flex items-center">
              <ArrowUp className="w-3 h-3 mr-1" />
              {data.employees.new_hires}
            </span>
            <span className="text-gray-400 ml-2">new hires this week</span>
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
          <div className="flex justify-between items-start mb-4">
            <div>
              <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Active Projects</p>
              <h3 className="text-2xl font-bold text-gray-900">{data.projects.active}</h3>
            </div>
            <div className="p-2 bg-orange-50 rounded-lg text-orange-500">
              <Rocket className="w-5 h-5" />
            </div>
          </div>
          <div className="flex items-center text-sm">
            <span className="text-gray-500 font-medium">{data.projects.nearing_completion} nearing completion</span>
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-red-200 p-6 flex flex-col justify-between relative overflow-hidden">
          <div className="absolute top-0 right-0 p-4 opacity-10 text-red-500">
             <AlertTriangle className="w-16 h-16" />
          </div>
          <div className="flex justify-between items-start mb-4 relative z-10">
            <div>
              <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Inventory Alerts</p>
              <h3 className="text-2xl font-bold text-gray-900">{data.inventory.alerts}</h3>
            </div>
            <div className="p-2 bg-red-50 rounded-lg text-red-500">
              <AlertTriangle className="w-5 h-5" />
            </div>
          </div>
          <div className="flex items-center text-sm relative z-10">
            <span className="text-red-600 font-semibold flex items-center">
              Action Required
            </span>
            <span className="text-gray-500 ml-1">for {data.inventory.action_required} SKUs</span>
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Revenue Growth Chart */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:col-span-3">
          <div className="flex justify-between items-center mb-6">
            <div>
              <h2 className="text-lg font-bold text-gray-900">Revenue Growth</h2>
              <p className="text-sm text-gray-500">Annual trend analysis</p>
            </div>
            <div className="flex bg-gray-100 rounded-lg p-1">
              <button className="px-3 py-1 text-xs font-medium rounded-md bg-white shadow-sm text-gray-900">12M</button>
              <button className="px-3 py-1 text-xs font-medium rounded-md text-gray-500 hover:text-gray-900">6M</button>
              <button className="px-3 py-1 text-xs font-medium rounded-md text-gray-500 hover:text-gray-900">3M</button>
            </div>
          </div>
          <div className="h-72">  
            <ResponsiveContainer width="100%" height="100%">
              <AreaChart data={data.revenue_trend} margin={{ top: 10, right: 10, left: 0, bottom: 0 }}>
                <defs>
                  <linearGradient id="colorValue" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stopColor="#1a56db" stopOpacity={0.1}/>
                    <stop offset="95%" stopColor="#1a56db" stopOpacity={0}/>
                  </linearGradient>
                </defs>
                <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fontSize: 10, fill: '#9ca3af', fontWeight: 600 }} dy={10} />
                <Tooltip 
                  contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)' }}
                  formatter={(value) => [`₹${value}`, 'Revenue']}
                />
                <Area type="monotone" dataKey="value" stroke="#1a56db" strokeWidth={3} fillOpacity={1} fill="url(#colorValue)" />
              </AreaChart>
            </ResponsiveContainer>
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Branch Performance */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h2 className="text-lg font-bold text-gray-900 mb-6">Branch Performance</h2>
          <div className="space-y-4">
            {data.branch_performance.map((branch) => {
              const maxRev = Math.max(...data.branch_performance.map(b => b.revenue));
              const percent = (branch.revenue / maxRev) * 100;
              const isSF = branch.id === 'SF';
              const isNY = branch.id === 'NY';
              const isLD = branch.id === 'LD';
              
              const bgColor = isSF ? 'bg-[#1a56db]' : (isNY ? 'bg-gray-200' : 'bg-orange-600');
              const iconBgColor = isSF ? 'bg-[#1a56db] text-white' : (isNY ? 'bg-[#e1effe] text-[#1a56db]' : 'bg-orange-600 text-white');
              
              return (
                <div key={branch.id} className="p-4 border border-gray-100 rounded-lg shadow-sm flex items-center">
                  <div className={`w-12 h-12 rounded-lg flex items-center justify-center font-bold text-lg mr-4 shrink-0 ${iconBgColor}`}>
                    {branch.id}
                  </div>
                  <div className="flex-1 min-w-0 pr-4">
                    <p className="text-sm font-bold text-gray-900 truncate">{branch.name}</p>
                    <p className="text-xs text-gray-500">{branch.type}</p>
                  </div>
                  <div className="w-1/3 text-right">
                    <p className="text-sm font-bold text-gray-900 mb-2">₹{numberWithCommas(branch.revenue)}</p>
                    <div className="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden flex justify-end">
                      <div className={`h-full ${bgColor}`} style={{ width: `${percent}%` }}></div>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Monthly Sales Targets */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div className="flex justify-between items-center mb-6">
            <h2 className="text-lg font-bold text-gray-900">Monthly Sales Targets</h2>
            <span className="px-2 py-1 bg-orange-100 text-orange-700 text-xs font-bold rounded-md">82% Overall Goal</span>
          </div>
          <div className="h-64 mt-4 relative">
             <ResponsiveContainer width="100%" height="100%">
              <BarChart data={data.monthly_targets} margin={{ top: 0, right: 0, left: -20, bottom: 0 }} barGap={0} barSize={24}>
                <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fontSize: 10, fill: '#9ca3af', fontWeight: 600 }} dy={10} />
                <YAxis hide />
                <Tooltip cursor={{fill: 'transparent'}} />
                <Bar dataKey="achieved" radius={[2, 2, 0, 0]}>
                  {data.monthly_targets.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={entry.name === 'Nov' ? '#1a56db' : '#c3ddfd'} />
                  ))}
                </Bar>
              </BarChart>
            </ResponsiveContainer>
          </div>
          <div className="flex justify-center mt-6 space-x-6 text-xs text-gray-500 font-medium">
            <div className="flex items-center">
              <div className="w-2 h-2 rounded-full bg-[#1a56db] mr-2"></div>
              Sales Achieved
            </div>
            <div className="flex items-center">
              <div className="w-2 h-2 rounded-full bg-gray-200 mr-2"></div>
              Projected
            </div>
          </div>
        </div>
      </div>

      {/* Quick Actions */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4">
        <button onClick={() => navigate('/dashboard/products')} className="py-4 bg-gray-100 hover:bg-gray-200 rounded-xl flex flex-col items-center justify-center text-gray-700 transition-colors">
          <Plus className="w-6 h-6 mb-2 border-2 border-gray-700 rounded-md p-0.5" />
          <span className="text-sm font-medium">Add Product</span>
        </button>
        <button onClick={() => navigate('/dashboard/invoices/create')} className="py-4 bg-gray-100 hover:bg-gray-200 rounded-xl flex flex-col items-center justify-center text-gray-700 transition-colors">
          <FileText className="w-6 h-6 mb-2" />
          <span className="text-sm font-medium">New Invoice</span>
        </button>
        <button onClick={() => navigate('/dashboard/employees')} className="py-4 bg-gray-100 hover:bg-gray-200 rounded-xl flex flex-col items-center justify-center text-gray-700 transition-colors">
          <UserPlus className="w-6 h-6 mb-2" />
          <span className="text-sm font-medium">Onboard User</span>
        </button>
        <button onClick={() => navigate('/dashboard/settings')} className="py-4 bg-gray-100 hover:bg-gray-200 rounded-xl flex flex-col items-center justify-center text-gray-700 transition-colors">
          <Settings className="w-6 h-6 mb-2" />
          <span className="text-sm font-medium">Preferences</span>
        </button>
      </div>
      
      {/* Footer text */}
      <div className="text-center text-xs text-gray-400 font-medium tracking-wider mt-12 mb-4">
        &copy; 2024 NEXUS INTELLIGENCE SYSTEMS &bull; PLATFORM V4.2.0
      </div>

    </div>
  );
}

function numberWithCommas(x) {
  return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
