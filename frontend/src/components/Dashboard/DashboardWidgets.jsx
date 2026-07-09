import { Package, FileText, FileSpreadsheet, CreditCard, Calendar } from 'lucide-react';

const iconMap = {
  Package, FileText, FileSpreadsheet, CreditCard, Calendar,
};

export default function DashboardWidgets({ widgets }) {
  if (!widgets || widgets.length === 0) return null;

  return (
    <div>
      <h2 className="text-lg font-bold text-foreground mb-4">My Summary</h2>
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        {widgets.map((widget) => {
          const Icon = iconMap[widget.icon] || Package;
          return (
            <div
              key={widget.key}
              className="bg-card rounded-xl border border-border p-4 flex items-center gap-4"
            >
              <div className="shrink-0 rounded-lg bg-primary/10 p-3 text-primary">
                <Icon className="h-5 w-5" />
              </div>
              <div className="min-w-0">
                <p className="text-xl font-bold text-foreground">{widget.value ?? '--'}</p>
                <p className="text-xs text-muted-foreground truncate">{widget.subtitle || widget.name}</p>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}
