import { useState, useEffect, useRef } from 'react';
import { createPortal } from 'react-dom';
import { Link, useNavigate } from 'react-router-dom';
import { Bell, CheckCheck, X, Loader2, Clock } from 'lucide-react';
import { useNotificationStore } from '../../store/notificationStore';
import { useAuthStore } from '../../store/authStore';

const TYPE_STYLES = {
  info: 'bg-primary/10 text-primary',
  success: 'bg-success/10 text-success',
  warning: 'bg-warning/10 text-warning',
  error: 'bg-destructive/10 text-destructive',
};

export default function NotificationBell() {
  const [open, setOpen] = useState(false);

  // The panel is portalled to <body> and pinned to the bell with
  // `position: fixed`. The header is `sticky z-10`, and any sticky or fixed
  // element forms a stacking context, so a `z-50` panel inside it still
  // paints at z=10 relative to the page and ended up underneath page content
  // that sets its own z-index. Portalling puts it outside that context.
  const [panelStyle, setPanelStyle] = useState({ top: 0, right: 0 });
  const dropdownRef = useRef(null);
  const buttonRef = useRef(null);
  const navigate = useNavigate();
  const user = useAuthStore((s) => s.user);

  const {
    notifications,
    unreadCount,
    loading,
    fetchNotifications,
    markRead,
    markAllRead,
    startPolling,
    stopPolling,
    listenForNotifications,
    stopListeningForNotifications,
  } = useNotificationStore();

  useEffect(() => {
    if (user?.id) {
      listenForNotifications(user.id);
    }
    startPolling(30000);
    return () => {
      if (user?.id) {
        stopListeningForNotifications(user.id);
      }
      stopPolling();
    };
  }, [user?.id, listenForNotifications, stopListeningForNotifications, startPolling, stopPolling]);

  useEffect(() => {
    if (open) {
      fetchNotifications({ per_page: 8 });
    }
  }, [open, fetchNotifications]);

  // Keep the panel's top-right corner pinned to the bell. The header does not
  // move while the page scrolls - <main> is its own scroll container - so a
  // resize is the only thing that needs re-measuring.
  useEffect(() => {
    if (!open) {
      return undefined;
    }
    const place = () => {
      const rect = buttonRef.current?.getBoundingClientRect();
      if (!rect) {
        return;
      }
      setPanelStyle({ top: rect.bottom + 8, right: window.innerWidth - rect.right });
    };
    place();
    window.addEventListener('resize', place);
    return () => window.removeEventListener('resize', place);
  }, [open]);

  useEffect(() => {
    const handleClickOutside = (e) => {
      // The panel is portalled, so the bell button is no longer an ancestor of
      // it. Without the second check, pressing the bell would close the
      // dropdown on mousedown and immediately reopen it on click.
      if (dropdownRef.current?.contains(e.target) || buttonRef.current?.contains(e.target)) {
        return;
      }
      setOpen(false);
    };
    if (open) {
      document.addEventListener('mousedown', handleClickOutside);
    }
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, [open]);

  const handleNotificationClick = async (notification) => {
    if (!notification.is_read) {
      await markRead(notification.id);
    }
    if (notification.link) {
      navigate(notification.link);
    }
    setOpen(false);
  };

  const timeAgo = (dateStr) => {
    const diff = Date.now() - new Date(dateStr).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 1) return 'Just now';
    if (mins < 60) return `${mins}m ago`;
    const hours = Math.floor(mins / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.floor(hours / 24);
    return `${days}d ago`;
  };

  return (
    <div className="relative">
      <button
        ref={buttonRef}
        onClick={() => setOpen(!open)}
        className="relative text-muted-foreground hover:text-foreground transition-colors"
      >
        <span className="sr-only">View notifications</span>
        <Bell className="h-5 w-5" />
        {unreadCount > 0 && (
          <span className="absolute -top-1.5 -right-1.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-destructive rounded-full animate-in zoom-in">
            {unreadCount > 99 ? '99+' : unreadCount}
          </span>
        )}
      </button>

      {open &&
        createPortal(
          <div
            ref={dropdownRef}
            style={{ position: 'fixed', top: panelStyle.top, right: panelStyle.right }}
            className="w-80 sm:w-96 bg-card rounded-xl shadow-2xl border border-border overflow-hidden z-[100] animate-in fade-in slide-in-from-top-2 duration-150"
          >
          <div className="px-4 py-3 border-b border-border flex items-center justify-between">
            <h3 className="font-semibold text-foreground text-sm">Notifications</h3>
            <div className="flex items-center gap-1">
              {unreadCount > 0 && (
                <button
                  onClick={markAllRead}
                  className="text-xs text-primary hover:text-primary/80 font-medium transition-colors flex items-center gap-1 px-2 py-1 rounded hover:bg-primary/5"
                >
                  <CheckCheck className="w-3.5 h-3.5" />
                  Mark all read
                </button>
              )}
              <button
                onClick={() => setOpen(false)}
                className="text-muted-foreground hover:text-foreground p-1 rounded hover:bg-muted transition-colors"
              >
                <X className="w-4 h-4" />
              </button>
            </div>
          </div>

          <div className="max-h-[360px] overflow-y-auto">
            {loading ? (
              <div className="flex items-center justify-center py-8">
                <Loader2 className="w-5 h-5 animate-spin text-muted-foreground" />
              </div>
            ) : notifications.length === 0 ? (
              <div className="flex flex-col items-center justify-center py-8 text-center">
                <Bell className="w-8 h-8 text-muted-foreground/40 mb-2" />
                <p className="text-sm text-muted-foreground">No notifications yet</p>
              </div>
            ) : (
              <ul>
                {notifications.map((notification) => (
                  <li key={notification.id}>
                    <button
                      onClick={() => handleNotificationClick(notification)}
                      className={`w-full text-left px-4 py-3 border-b border-border last:border-b-0 hover:bg-muted/50 transition-colors flex gap-3 ${
                        !notification.is_read ? 'bg-primary/[0.02]' : ''
                      }`}
                    >
                      <div
                        className={`mt-0.5 w-2 h-2 rounded-full flex-shrink-0 ${
                          notification.is_read ? 'bg-transparent' : 'bg-primary'
                        }`}
                      />
                      <div className="flex-1 min-w-0">
                        <div className="flex items-center gap-2 mb-0.5">
                          {notification.type && (
                            <span
                              className={`text-[10px] font-semibold px-1.5 py-0.5 rounded ${
                                TYPE_STYLES[notification.type] || TYPE_STYLES.info
                              }`}
                            >
                              {notification.type}
                            </span>
                          )}
                          <span className="text-xs text-muted-foreground flex items-center gap-1 ml-auto flex-shrink-0">
                            <Clock className="w-3 h-3" />
                            {timeAgo(notification.created_at)}
                          </span>
                        </div>
                        <p
                          className={`text-sm leading-snug ${
                            !notification.is_read ? 'font-medium text-foreground' : 'text-muted-foreground'
                          }`}
                        >
                          {notification.title}
                        </p>
                        {notification.message && (
                          <p className="text-xs text-muted-foreground mt-0.5 line-clamp-2">
                            {notification.message}
                          </p>
                        )}
                      </div>
                    </button>
                  </li>
                ))}
              </ul>
            )}
          </div>

          <Link
            to="/dashboard/notifications"
            onClick={() => setOpen(false)}
            className="block px-4 py-3 text-center text-sm font-medium text-primary hover:bg-muted/50 border-t border-border transition-colors"
          >
            View all notifications
          </Link>
          </div>,
          document.body,
        )}
    </div>
  );
}
