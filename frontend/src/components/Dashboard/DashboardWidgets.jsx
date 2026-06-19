import { Package, FileText, FileSpreadsheet, CreditCard, Calendar } from 'lucide-react';

const iconMap = {
  Package, FileText, FileSpreadsheet, CreditCard, Calendar,
};

export default function DashboardWidgets({ widgets }) {
  if (!widgets || widgets.length === 0) return null;

  return (
    <div>
      <h2 className="text-lg font-bold text-gray-900 mb-4">My Summary</h2>
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        {widgets.map((widget) => {
          const Icon = iconMap[widget.icon] || Package;
          return (
            <div
              key={widget.key}
              className="bg-white rounded-xl border border-gray-100 p-4 flex items-center gap-4"
            >
              <div className="shrink-0 rounded-lg bg-blue-50 p-3 text-[#1a56db]">
                <Icon className="h-5 w-5" />
              </div>
              <div className="min-w-0">
                <p className="text-xl font-bold text-gray-900">{widget.value ?? '--'}</p>
                <p className="text-xs text-gray-500 truncate">{widget.subtitle || widget.name}</p>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}
