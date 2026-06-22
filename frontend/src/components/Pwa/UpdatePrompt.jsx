import { useState, useEffect } from 'react';
import { RefreshCw, X } from 'lucide-react';

export default function UpdatePrompt() {
  const [needRefresh, setNeedRefresh] = useState(false);
  const [offlineReady, setOfflineReady] = useState(false);
  const [updateFn, setUpdateFn] = useState(null);

  useEffect(() => {
    if (typeof window === 'undefined' || !('serviceWorker' in navigator)) return;

    const swUrl = import.meta.env.DEV ? '/dev-sw.js?dev-sw' : '/sw.js';

    const registerSW = async () => {
      try {
        const reg = await navigator.serviceWorker.register(swUrl);

        if (reg.active && reg.active.state === 'activated') {
          setOfflineReady(true);
          setTimeout(() => setOfflineReady(false), 3000);
        }

        reg.addEventListener('updatefound', () => {
          const newWorker = reg.installing;
          if (!newWorker) return;

          newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
              setNeedRefresh(true);
              setUpdateFn(() => () => {
                newWorker.postMessage({ type: 'SKIP_WAITING' });
                window.location.reload();
              });
            }
          });
        });
      } catch (e) {
        if (import.meta.env.DEV) console.debug('SW registration skipped');
      }
    };

    registerSW();
  }, []);

  if (offlineReady) {
    return (
      <div className="fixed bottom-4 right-4 z-[100] animate-slide-up">
        <div className="bg-green-50 border border-green-200 rounded-xl shadow-lg px-4 py-3 flex items-center gap-2">
          <div className="w-2 h-2 rounded-full bg-green-500 animate-pulse" />
          <p className="text-sm text-green-800 font-medium">Ready for offline use</p>
        </div>
      </div>
    );
  }

  if (!needRefresh) return null;

  return (
    <div className="fixed bottom-4 right-4 z-[100] max-w-sm animate-slide-up">
      <div className="bg-white rounded-xl shadow-2xl border border-gray-200 p-4 flex items-start gap-3">
        <div className="shrink-0 w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
          <RefreshCw className="w-4 h-4 text-blue-600" />
        </div>
        <div className="flex-1 min-w-0">
          <p className="text-sm font-semibold text-gray-900">Update Available</p>
          <p className="text-xs text-gray-500 mt-0.5">
            A new version of RFI is ready. Refresh to apply.
          </p>
          <div className="flex items-center gap-2 mt-3">
            <button
              onClick={() => updateFn?.()}
              className="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors"
            >
              <RefreshCw className="w-3 h-3" />
              Refresh
            </button>
            <button
              onClick={() => setNeedRefresh(false)}
              className="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-800 transition-colors"
            >
              Later
            </button>
          </div>
        </div>
        <button
          onClick={() => setNeedRefresh(false)}
          className="shrink-0 p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
        >
          <X className="w-4 h-4" />
        </button>
      </div>
    </div>
  );
}
