import { Link } from 'react-router-dom';
import { Info } from 'lucide-react';

/*
 * Banner shown on the retired Purchases / Sales / Invoices pages (see
 * config/legacyDocuments.js). A user can still reach those pages by URL, so
 * each one explains why its forms are gone and where the equivalent work
 * moved to.
 *
 * `title` states the page's situation, e.g. "Invoices are read-only".
 * `creationHref` / `creationLabel` point at the replacement voucher register.
 */
export default function LegacyDocumentsNotice({ title, creationHref, creationLabel }) {
  return (
    <div className="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
      <Info className="w-4 h-4 mt-0.5 shrink-0 text-amber-500" />
      <div className="text-sm text-amber-900 space-y-1">
        <p className="font-semibold">{title}</p>
        <p className="text-amber-800">
          These documents were migrated to Tally-style vouchers, which are now the system of
          record. New entries are recorded under{' '}
          <Link to={creationHref} className="font-medium underline hover:no-underline">
            {creationLabel}
          </Link>
          . The old documents remain available for viewing and printing.
        </p>
      </div>
    </div>
  );
}
