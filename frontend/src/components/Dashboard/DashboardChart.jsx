import {
  AreaChart, Area, BarChart, Bar, LineChart, Line, PieChart, Pie, Cell,
  XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Legend,
} from 'recharts';

const COLORS = ['#1a56db', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6'];

const renderShape = (chartType, data) => {
  switch (chartType) {
    case 'area':
      return (
        <AreaChart data={data} margin={{ top: 10, right: 10, left: 0, bottom: 0 }}>
          <defs>
            <linearGradient id="colorValue" x1="0" y1="0" x2="0" y2="1">
              <stop offset="5%" stopColor="#1a56db" stopOpacity={0.1} />
              <stop offset="95%" stopColor="#1a56db" stopOpacity={0} />
            </linearGradient>
          </defs>
          <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
          <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fontSize: 10, fill: '#9ca3af', fontWeight: 600 }} dy={10} />
          <YAxis axisLine={false} tickLine={false} tick={{ fontSize: 10, fill: '#9ca3af' }} />
          <Tooltip contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 6px -1px rgba(0,0,0,0.1)' }} />
          <Area type="monotone" dataKey="value" stroke="#1a56db" strokeWidth={3} fill="url(#colorValue)" />
        </AreaChart>
      );

    case 'bar': {
      const keys = Object.keys(data[0] || {}).filter(k => k !== 'name');
      return (
        <BarChart data={data} margin={{ top: 10, right: 10, left: 0, bottom: 0 }} barSize={keys.length > 1 ? 16 : 24}>
          <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" vertical={false} />
          <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fontSize: 10, fill: '#9ca3af', fontWeight: 600 }} dy={10} />
          <YAxis axisLine={false} tickLine={false} tick={{ fontSize: 10, fill: '#9ca3af' }} />
          <Tooltip contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 6px -1px rgba(0,0,0,0.1)' }} />
          {keys.length === 1 ? (
            <Bar dataKey={keys[0]} radius={[4, 4, 0, 0]}>
              {data.map((entry, index) => (
                <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
              ))}
            </Bar>
          ) : (
            keys.map((key, idx) => (
              <Bar key={key} dataKey={key} radius={[4, 4, 0, 0]} fill={COLORS[idx % COLORS.length]} />
            ))
          )}
        </BarChart>
      );
    }

    case 'line':
      return (
        <LineChart data={data} margin={{ top: 10, right: 10, left: 0, bottom: 0 }}>
          <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
          <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fontSize: 10, fill: '#9ca3af', fontWeight: 600 }} dy={10} />
          <YAxis axisLine={false} tickLine={false} tick={{ fontSize: 10, fill: '#9ca3af' }} />
          <Tooltip contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 6px -1px rgba(0,0,0,0.1)' }} />
          <Line type="monotone" dataKey="value" stroke="#1a56db" strokeWidth={3} dot={{ fill: '#1a56db', r: 4 }} />
        </LineChart>
      );

    case 'pie':
      return (
        <PieChart>
          <Pie data={data} cx="50%" cy="50%" innerRadius={60} outerRadius={90} paddingAngle={4} dataKey="value">
            {data.map((entry, index) => (
              <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
            ))}
          </Pie>
          <Tooltip contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 6px -1px rgba(0,0,0,0.1)' }} />
          <Legend wrapperStyle={{ fontSize: '11px', fontWeight: 500 }} />
        </PieChart>
      );

    default:
      return (
        <BarChart data={data} margin={{ top: 10, right: 10, left: 0, bottom: 0 }}>
          <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" vertical={false} />
          <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fontSize: 10, fill: '#9ca3af', fontWeight: 600 }} dy={10} />
          <YAxis axisLine={false} tickLine={false} tick={{ fontSize: 10, fill: '#9ca3af' }} />
          <Tooltip contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 6px -1px rgba(0,0,0,0.1)' }} />
          <Bar dataKey="value" radius={[4, 4, 0, 0]} fill="#1a56db" />
        </BarChart>
      );
  }
};

export default function DashboardChart({ chart }) {
  if (!chart.data || chart.data.length === 0) return null;

  return (
    <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <div className="flex justify-between items-center mb-6">
        <div>
          <h2 className="text-lg font-bold text-gray-900">{chart.name}</h2>
        </div>
      </div>
      <div className="h-72 min-w-0">
        <ResponsiveContainer width="99%" height="100%">
          {renderShape(chart.chart_type, chart.data)}
        </ResponsiveContainer>
      </div>
    </div>
  );
}
