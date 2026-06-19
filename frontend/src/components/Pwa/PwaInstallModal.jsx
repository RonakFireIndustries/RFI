import { X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { usePwaInstall } from '@/hooks/usePwaInstall';

export default function PwaInstallModal() {
  const { isInstallable, handleInstall, handleDismiss } = usePwaInstall();

  if (!isInstallable) return null;

  return (
    <div className="fixed bottom-0 left-0 right-0 z-50 p-4 pb-6">
      <div className="mx-auto max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-4">
        <img src="/logo.png" alt="RFI" className="shrink-0 w-10 h-10 object-contain" />
        <div className="flex-1 min-w-0">
          <p className="text-sm font-semibold text-gray-900 dark:text-gray-100">
            Install RFI App
          </p>
          <p className="text-xs text-gray-500 dark:text-gray-400 truncate">
            Add to your home screen for a better experience
          </p>
        </div>
        <Button size="sm" onClick={handleInstall}>
          Install
        </Button>
        <button
          onClick={handleDismiss}
          className="shrink-0 p-1 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
        >
          <X className="w-4 h-4" />
        </button>
      </div>
    </div>
  );
}
