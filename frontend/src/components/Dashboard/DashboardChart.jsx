import {
  AreaChart, Area, BarChart, Bar, LineChart, Line, PieChart, Pie, Cell,
  XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Legend,
} from 'recharts';

const OCEAN_BLUE = {
  primary: '#2563EB',
  secondary: '#38BDF8',
  accent: '#0EA5E9',
  success: '#22C55E',
  warning: '#F59E0B',
  error: '#EF4444',
  info: '#3B82F6',
};

const CHART_COLORS = [OCEAN_BLUE.primary, OCEAN_BLUE.secondary, OCEAN_BLUE.success, OCEAN_BLUE.warning, OCEAN_BLUE.info, OCEAN_BLUE.accent, OCEAN_BLUE.error];

const getCSSVar = (varName) => {
  if (typeof document === 'undefined') return OCEAN_BLUE.primary;
  return getComputedStyle(document.documentElement).getPropertyValue(varName).trim() || OCEAN_BLUE.primary;
};

const axisStyle = { fontSize: 10, fill: '#94a3b8' };
const tooltipStyle = { borderRadius: '8px', border: '1px solid var(--color-border)', background: 'var(--color-surface)', boxShadow: '0 4px 6px -1px rgba(0,0,0,0.1)' };

const renderShape = (chartType, data) => {
  const primaryColor = getCSSVar('--color-primary');
  const gridColor = 'var(--color-divider)';

  switch (chartType) {
    case 'area':
      return (
        <AreaChart data={data} margin={{ top: 10, right: 10, left: 0, bottom: 0 }}>
          <defs>
            <linearGradient id="colorValue" x1="0" y1="0" x2="0" y2="1">
              <stop offset="5%" stopColor={primaryColor} stopOpacity={0.15} />
              <stop offset="95%" stopColor={primaryColor} stopOpacity={0} />
            </linearGradient>
          </defs>
          <CartesianGrid strokeDasharray="3 3" stroke={gridColor} />
          <XAxis dataKey="name" axisLine={false} tickLine={false} tick={axisStyle} dy={10} />
          <YAxis axisLine={false} tickLine={false} tick={axisStyle} />
          <Tooltip contentStyle={tooltipStyle} />
          <Area type="monotone" dataKey="value" stroke={primaryColor} strokeWidth={3} fill="url(#colorValue)" />
        </AreaChart>
      );

    case 'bar': {
      const keys = Object.keys(data[0] || {}).filter(k => k !== 'name');
      return (
        <BarChart data={data} margin={{ top: 10, right: 10, left: 0, bottom: 0 }} barSize={keys.length > 1 ? 16 : 24}>
          <CartesianGrid strokeDasharray="3 3" stroke={gridColor} vertical={false} />
          <XAxis dataKey="name" axisLine={false} tickLine={false} tick={axisStyle} dy={10} />
          <YAxis axisLine={false} tickLine={false} tick={axisStyle} />
          <Tooltip contentStyle={tooltipStyle} />
          {keys.length === 1 ? (
            <Bar dataKey={keys[0]} radius={[4, 4, 0, 0]}>
              {data.map((entry, index) => (
                <Cell key={`cell-${index}`} fill={CHART_COLORS[index % CHART_COLORS.length]} />
              ))}
            </Bar>
          ) : (
            keys.map((key, idx) => (
              <Bar key={key} dataKey={key} radius={[4, 4, 0, 0]} fill={CHART_COLORS[idx % CHART_COLORS.length]} />
            ))
          )}
        </BarChart>
      );
    }

    case 'line':
      return (
        <LineChart data={data} margin={{ top: 10, right: 10, left: 0, bottom: 0 }}>
          <CartesianGrid strokeDasharray="3 3" stroke={gridColor} />
          <XAxis dataKey="name" axisLine={false} tickLine={false} tick={axisStyle} dy={10} />
          <YAxis axisLine={false} tickLine={false} tick={axisStyle} />
          <Tooltip contentStyle={tooltipStyle} />
          <Line type="monotone" dataKey="value" stroke={primaryColor} strokeWidth={3} dot={{ fill: primaryColor, r: 4 }} />
        </LineChart>
      );

    case 'pie':
      return (
        <PieChart>
          <Pie data={data} cx="50%" cy="50%" innerRadius={60} outerRadius={90} paddingAngle={4} dataKey="value">
            {data.map((entry, index) => (
              <Cell key={`cell-${index}`} fill={CHART_COLORS[index % CHART_COLORS.length]} />
            ))}
          </Pie>
          <Tooltip contentStyle={tooltipStyle} />
          <Legend wrapperStyle={{ fontSize: '11px', fontWeight: 500 }} />
        </PieChart>
      );

    default:
      return (
        <BarChart data={data} margin={{ top: 10, right: 10, left: 0, bottom: 0 }}>
          <CartesianGrid strokeDasharray="3 3" stroke={gridColor} vertical={false} />
          <XAxis dataKey="name" axisLine={false} tickLine={false} tick={axisStyle} dy={10} />
          <YAxis axisLine={false} tickLine={false} tick={axisStyle} />
          <Tooltip contentStyle={tooltipStyle} />
          <Bar dataKey="value" radius={[4, 4, 0, 0]} fill={primaryColor} />
        </BarChart>
      );
  }
};

export default function DashboardChart({ chart }) {
  if (!chart.data || chart.data.length === 0) return null;

  return (
    <div className="bg-card rounded-xl shadow-sm border border-border p-6">
      <div className="flex justify-between items-center mb-6">
        <div>
          <h2 className="text-lg font-bold text-foreground">{chart.name}</h2>
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
