import React from 'react';
import { Clock, Upload, Trash2, Edit2, RefreshCw } from 'lucide-react';

export default function DocumentHistory({ documents }) {
  // Generate timeline events from documents
  const events = [];

  documents.forEach(doc => {
    // Upload event
    events.push({
      id: `${doc.id}-upload`,
      type: 'upload',
      title: 'Document Uploaded',
      description: `${doc.original_file_name} (${doc.document_type}) uploaded.`,
      date: new Date(doc.created_at),
      icon: Upload,
      iconBg: 'bg-blue-100 text-blue-600',
      user: doc.uploaded_by?.name || 'System'
    });

    // Update event (if updated_at is significantly different from created_at and not deleted)
    if (doc.updated_at && new Date(doc.updated_at).getTime() - new Date(doc.created_at).getTime() > 1000) {
      if (!doc.deleted_at || new Date(doc.updated_at).getTime() < new Date(doc.deleted_at).getTime()) {
        events.push({
          id: `${doc.id}-update`,
          type: 'update',
          title: 'Document Updated',
          description: `Metadata or file for ${doc.original_file_name} was updated.`,
          date: new Date(doc.updated_at),
          icon: Edit2,
          iconBg: 'bg-yellow-100 text-yellow-600',
          user: 'System'
        });
      }
    }

    // Delete event
    if (doc.deleted_at) {
      events.push({
        id: `${doc.id}-delete`,
        type: 'delete',
        title: 'Document Deleted',
        description: `${doc.original_file_name} was deleted.`,
        date: new Date(doc.deleted_at),
        icon: Trash2,
        iconBg: 'bg-red-100 text-red-600',
        user: 'System'
      });
    }
  });

  // Sort events by date descending
  events.sort((a, b) => b.date - a.date);

  if (events.length === 0) {
    return <div className="text-center p-8 text-gray-500">No document activity recorded yet.</div>;
  }

  return (
    <div className="flow-root p-6 bg-white rounded-lg shadow-sm border border-gray-100">
      <h3 className="text-lg font-semibold text-gray-900 mb-6">Activity Timeline</h3>
      <ul className="-mb-8">
        {events.map((event, eventIdx) => (
          <li key={event.id}>
            <div className="relative pb-8">
              {eventIdx !== events.length - 1 ? (
                <span className="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true" />
              ) : null}
              <div className="relative flex space-x-3">
                <div>
                  <span className={`h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white ${event.iconBg}`}>
                    <event.icon className="h-4 w-4" aria-hidden="true" />
                  </span>
                </div>
                <div className="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                  <div>
                    <p className="text-sm text-gray-500">
                      <span className="font-medium text-gray-900">{event.title}</span> - {event.description}
                    </p>
                  </div>
                  <div className="whitespace-nowrap text-right text-sm text-gray-500">
                    <time dateTime={event.date.toISOString()}>
                      {event.date.toLocaleDateString()} {event.date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                    </time>
                  </div>
                </div>
              </div>
            </div>
          </li>
        ))}
      </ul>
    </div>
  );
}
