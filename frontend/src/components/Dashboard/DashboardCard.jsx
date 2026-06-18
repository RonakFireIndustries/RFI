import { TrendingUp, TrendingDown, Minus, Users, UserCheck, UserX, UserMinus, Building2, Package, AlertTriangle, XCircle, DollarSign, ClipboardList, UserPlus, CalendarClock, FileText, FileWarning, Percent, Clock, CheckCircle, CreditCard, Database, Wallet, HardHat, BarChart3, ListChecks, Calendar, CalendarDays, Fingerprint, MapPin, Activity, ScrollText, ShieldAlert, Shield, Server, RefreshCw, Briefcase, ArrowLeftRight, Truck, Palette, CheckSquare, Square, TrendingUpDown } from 'lucide-react';
import { useNavigate } from 'react-router-dom';

const iconMap = {
  Users, UserCheck, UserX, UserMinus, Building2, Package, AlertTriangle, XCircle,
  DollarSign, ClipboardList, UserPlus, CalendarClock, FileText, FileWarning,
  Percent, Clock, CheckCircle, CreditCard, Database, Wallet, HardHat, BarChart3,
  ListChecks, Calendar, CalendarDays, Fingerprint, MapPin, Activity, ScrollText,
  ShieldAlert, Shield, Server, RefreshCw, Briefcase, ArrowLeftRight, Truck,
  Palette, CheckSquare, Square, TrendingUp, TrendingDown,
};

const trendColors = {
  up: 'text-green-600',
  down: 'text-red-600',
  neutral: 'text-gray-500',
};

const trendIcons = {
  up: TrendingUp,
  down: TrendingDown,
  neutral: Minus,
};

export default function DashboardCard({ card }) {
  const navigate = useNavigate();
  const Icon = iconMap[card.icon] || BarChart3;
  const TrendIcon = trendIcons[card.trend] || null;

  const isAlert = card.key?.includes('low_stock') || card.key?.includes('alert') || card.key?.includes('out_of_stock');

  return (
    <div
      onClick={() => card.link ? navigate(card.link) : undefined}
      className={`bg-white rounded-xl shadow-sm border ${isAlert ? 'border-red-200' : 'border-gray-100'} p-6 flex flex-col justify-between relative overflow-hidden ${card.link ? 'cursor-pointer hover:shadow-md transition-shadow' : ''}`}
    >
      {isAlert && (
        <div className="absolute top-0 right-0 p-4 opacity-10 text-red-500">
          <AlertTriangle className="w-16 h-16" />
        </div>
      )}
      <div className="flex justify-between items-start mb-4 relative z-10">
        <div className="min-w-0 flex-1">
          <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1 truncate">{card.name}</p>
          <h3 className="text-2xl font-bold text-gray-900">
            {card.prefix}{card.value}
          </h3>
        </div>
        <div className={`p-2 rounded-lg shrink-0 ml-3 ${isAlert ? 'bg-red-50 text-red-500' : 'bg-blue-50 text-[#1a56db]'}`}>
          <Icon className="w-5 h-5" />
        </div>
      </div>
      {card.subtitle && (
        <div className="flex items-center text-sm relative z-10">
          {TrendIcon && (
            <span className={`font-medium flex items-center mr-2 ${trendColors[card.trend]}`}>
              <TrendIcon className="w-3 h-3 mr-1" />
            </span>
          )}
          <span className={`text-sm ${isAlert ? 'text-red-600 font-semibold' : 'text-gray-500'}`}>
            {card.subtitle}
          </span>
        </div>
      )}
    </div>
  );
}
