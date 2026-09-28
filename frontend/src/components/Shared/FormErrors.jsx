import { XCircle } from 'lucide-react';

const GENERAL_KEYS = ['message', 'general'];

export default function FormErrors({ errors, fieldLabels = {}, className = '' }) {
  if (!errors || Object.keys(errors).length === 0) return null;

  const entries = Object.entries(errors);
  const generalEntries = entries.filter(([key]) => GENERAL_KEYS.includes(key));
  const fieldEntries = entries.filter(([key]) => !GENERAL_KEYS.includes(key));

  return (
    <div className={`rounded-md border border-red-200 bg-red-50 p-4 ${className}`}>
      <div className="flex items-start gap-2">
        <XCircle className="w-5 h-5 text-red-500 mt-0.5 shrink-0" />
        <div className="flex-1 space-y-2">
          {generalEntries.map(([key, value]) => (
            <p key={key} className="text-sm font-semibold text-red-800">
              {Array.isArray(value) ? value[0] : value}
            </p>
          ))}
          {fieldEntries.map(([field, messages]) => {
            const list = Array.isArray(messages) ? messages : [messages];
            return (
              <p key={field} className="text-sm text-red-700">
                <span className="font-medium">{fieldLabels[field] || field}:</span>{' '}
                {list[0]}
              </p>
            );
          })}
        </div>
      </div>
    </div>
  );
}