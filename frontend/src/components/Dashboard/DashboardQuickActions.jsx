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
      <h2 className="text-lg font-bold text-gray-900 mb-4">Quick Actions</h2>
      <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
        {quickActions.map((action) => {
          const Icon = iconMap[action.icon] || Plus;
          const button = (
            <button
              key={action.key}
              onClick={() => navigate(action.link)}
              className="py-4 bg-gray-100 hover:bg-gray-200 rounded-xl flex flex-col items-center justify-center text-gray-700 transition-colors"
            >
              <Icon className="w-6 h-6 mb-2" />
              <span className="text-xs font-medium text-center leading-tight">{action.label}</span>
            </button>
          );

          return <div key={action.key}>{button}</div>;
        })}
      </div>
    </div>
  );
}
