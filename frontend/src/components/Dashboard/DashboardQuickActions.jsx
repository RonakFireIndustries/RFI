import { useNavigate } from 'react-router-dom';
import {
  UserPlus, CheckCircle, DollarSign, ShoppingCart, Receipt, Users, Shield, Key, Settings,
  MapPin, Upload, Lock, Download, FileText, DownloadCloud, ClipboardCheck, ArrowLeftRight,
  ClipboardList, FilePlus, CalendarPlus, Fingerprint, PackagePlus, LogIn, LogOut,
  Plus, Search, ScrollText,
} from 'lucide-react';

const iconMap = {
  UserPlus, CheckCircle, DollarSign, ShoppingCart, Receipt, Users, Shield, Key, Settings,
  MapPin, Upload, Lock, Download, FileText, DownloadCloud, ClipboardCheck, ArrowLeftRight,
  ClipboardList, FilePlus, CalendarPlus, Fingerprint, PackagePlus, LogIn, LogOut,
  Plus, Search, ScrollText,
};

export default function DashboardQuickActions({ quickActions }) {
  const navigate = useNavigate();

  if (!quickActions || quickActions.length === 0) return null;

  return (
    <div>
      <h2 className="text-lg font-bold text-foreground mb-4">Quick Actions</h2>
      <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
        {quickActions.map((action) => {
          const Icon = iconMap[action.icon] || Plus;

          return (
            <button
              key={action.key}
              onClick={() => navigate(action.link)}
              className="w-full h-full py-4 px-2 bg-card border border-border hover:bg-muted rounded-xl flex flex-col items-center justify-center gap-2 text-foreground transition-colors"
            >
              <span className="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                <Icon className="w-5 h-5" />
              </span>
              <span className="text-xs font-medium text-center leading-tight">{action.label}</span>
            </button>
          );
        })}
      </div>
    </div>
  );
}
