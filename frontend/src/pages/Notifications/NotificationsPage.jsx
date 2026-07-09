import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import {
  Bell,
  CheckCheck,
  Trash2,
  ArrowLeft,
  Loader2,
  Clock,
  Inbox,
} from 'lucide-react';
import { useNotificationStore } from '../../store/notificationStore';

const TYPE_STYLES = {
  info: 'bg-primary/10 text-primary',
  success: 'bg-success/10 text-success',
  warning: 'bg-warning/10 text-warning',
  error: 'bg-destructive/10 text-destructive',
};

export default function NotificationsPage() {
  const [filter, setFilter] = useState('all');

  const {
    notifications,
    loading,
    fetchNotifications,
    markRead,
    markAllRead,
    deleteNotification,
    startPolling,
    stopPolling,
  } = useNotificationStore();

  useEffect(() => {
    fetchNotifications();
    startPolling(30000);
    return () => stopPolling();
  }, [fetchNotifications, startPolling, stopPolling]);

  const filtered = notifications.filter((n) => {
    if (filter === 'unread') return !n.is_read;
    return true;
  });

  const timeAgo = (dateStr) => {
    const diff = Date.now() - new Date(dateStr).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 1) return 'Just now';
    if (mins < 60) return `${mins} minute${mins !== 1 ? 's' : ''} ago`;
    const hours = Math.floor(mins / 60);
    if (hours < 24) return `${hours} hour${hours !== 1 ? 's' : ''} ago`;
    const days = Math.floor(hours / 24);
    if (days < 30) return `${days} day${days !== 1 ? 's' : ''} ago`;
    const months = Math.floor(days / 30);
    return `${months} month${months !== 1 ? 's' : ''} ago`;
  };

  return (
    <div className="max-w-3xl mx-auto">
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center gap-3">
          <Link
            to="/dashboard"
            className="p-2 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg transition-colors"
          >
            <ArrowLeft className="w-5 h-5" />
          </Link>
          <div>
            <h1 className="text-2xl font-bold text-foreground">Notifications</h1>
            <p className="text-sm text-muted-foreground mt-0.5">
              Stay updated with important alerts and updates
            </p>
          </div>
        </div>
        <div className="flex items-center gap-2">
          {notifications.some((n) => !n.is_read) && (
            <button
              onClick={markAllRead}
              className="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-primary hover:bg-primary/10 rounded-lg transition-colors"
            >
              <CheckCheck className="w-4 h-4" />
              Mark all read
            </button>
          )}
        </div>
      </div>

      <div className="flex gap-1 mb-4 p-1 bg-muted rounded-lg w-fit">
        {['all', 'unread'].map((f) => (
          <button
            key={f}
            onClick={() => setFilter(f)}
            className={`px-3 py-1.5 text-sm font-medium rounded-md transition-colors capitalize ${
              filter === f
                ? 'bg-card text-foreground shadow-sm'
                : 'text-muted-foreground hover:text-foreground'
            }`}
          >
            {f}
            {f === 'unread' && notifications.filter((n) => !n.is_read).length > 0 && (
              <span className="ml-1.5 text-xs text-destructive font-bold">
                ({notifications.filter((n) => !n.is_read).length})
              </span>
            )}
          </button>
        ))}
      </div>

      {loading && notifications.length === 0 ? (
        <div className="flex flex-col items-center justify-center py-20">
          <Loader2 className="w-8 h-8 animate-spin text-muted-foreground mb-3" />
          <p className="text-sm text-muted-foreground">Loading notifications...</p>
        </div>
      ) : filtered.length === 0 ? (
        <div className="flex flex-col items-center justify-center py-20">
          <Inbox className="w-12 h-12 text-muted-foreground/30 mb-3" />
          <p className="text-lg font-medium text-foreground">All caught up!</p>
          <p className="text-sm text-muted-foreground mt-1">
            {filter === 'unread'
              ? 'No unread notifications'
              : 'No notifications yet'}
          </p>
        </div>
      ) : (
        <div className="bg-card border border-border rounded-xl overflow-hidden divide-y divide-border">
          {filtered.map((notification) => (
            <div
              key={notification.id}
              className={`flex gap-4 px-4 py-4 ${
                !notification.is_read ? 'bg-primary/[0.02]' : ''
              }`}
            >
              <div className="flex-shrink-0 mt-1">
                <div
                  className={`w-10 h-10 rounded-full flex items-center justify-center ${
                    TYPE_STYLES[notification.type] || TYPE_STYLES.info
                  }`}
                >
                  <Bell className="w-5 h-5" />
                </div>
              </div>

              <div className="flex-1 min-w-0">
                <div className="flex items-center gap-2 mb-0.5">
                  <p
                    className={`text-sm ${
                      !notification.is_read ? 'font-semibold text-foreground' : 'text-foreground'
                    }`}
                  >
                    {notification.title}
                  </p>
                  {notification.type && (
                    <span
                      className={`text-[10px] font-semibold px-1.5 py-0.5 rounded flex-shrink-0 ${
                        TYPE_STYLES[notification.type] || TYPE_STYLES.info
                      }`}
                    >
                      {notification.type}
                    </span>
                  )}
                  {!notification.is_read && (
                    <span className="w-2 h-2 rounded-full bg-primary flex-shrink-0" />
                  )}
                </div>

                {notification.message && (
                  <p className="text-sm text-muted-foreground mt-0.5">
                    {notification.message}
                  </p>
                )}

                <div className="flex items-center gap-3 mt-2">
                  <span className="text-xs text-muted-foreground flex items-center gap-1">
                    <Clock className="w-3 h-3" />
                    {timeAgo(notification.created_at)}
                  </span>

                  {notification.link && (
                    <Link
                      to={notification.link}
                      className="text-xs font-medium text-primary hover:text-primary/80 transition-colors"
                    >
                      View details
                    </Link>
                  )}
                </div>
              </div>

              <div className="flex flex-col gap-1 flex-shrink-0">
                {notification.is_read === false && (
                  <button
                    onClick={() => markRead(notification.id)}
                    className="p-1.5 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-lg transition-colors"
                    title="Mark as read"
                  >
                    <CheckCheck className="w-4 h-4" />
                  </button>
                )}
                <button
                  onClick={() => deleteNotification(notification.id)}
                  className="p-1.5 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-lg transition-colors"
                  title="Delete"
                >
                  <Trash2 className="w-4 h-4" />
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
