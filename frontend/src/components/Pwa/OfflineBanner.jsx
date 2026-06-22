import { WifiOff } from 'lucide-react';
import { useOnlineStatus } from '../../hooks/useOnlineStatus';

export default function OfflineBanner() {
  const isOnline = useOnlineStatus();

  if (isOnline) return null;

  return (
    <div className="bg-amber-500 text-white text-center text-xs font-medium py-1.5 px-4 flex items-center justify-center gap-2 sticky top-0 z-50 print:hidden">
      <WifiOff className="w-3.5 h-3.5 shrink-0" />
      <span>You are offline. Some features may be unavailable.</span>
    </div>
  );
}
