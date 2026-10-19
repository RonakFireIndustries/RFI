import React from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import LedgerStatementReport from './reports/LedgerStatementReport';
import DayBookReport from './reports/DayBookReport';
import OutstandingReport from './reports/OutstandingReport';
import ProfitLossReport from './reports/ProfitLossReport';
import BalanceSheetReport from './reports/BalanceSheetReport';
import TrialBalanceReport from './reports/TrialBalanceReport';
import CashBankBookReport from './reports/CashBankBookReport';

/*
 * Financial report pack (slice 5). One route hosts all seven reports behind a
 * tab bar; each tab is deep-linkable (`/accounting/reports/balance-sheet`) so
 * a report can be bookmarked or shared. The tab content is keyed so switching
 * resets a report's own filters rather than carrying stale dates across.
 *
 * Amount visibility: utils/finance.js masks prices behind purchase/sales order
 * access on the operational screens. Deliberately not applied here — these are
 * the accounts themselves, so `finance-reports.view` (Accounts only) is the
 * whole control. Gating the reports on order permissions as well would mask
 * rows in a P&L and make it meaningless.
 */
const TABS = [
  { key: 'day-book', label: 'Day Book', component: DayBookReport },
  { key: 'ledger', label: 'Ledger Statement', component: LedgerStatementReport },
  { key: 'outstanding', label: 'Outstandings', component: OutstandingReport },
  { key: 'profit-loss', label: 'Profit & Loss', component: ProfitLossReport },
  { key: 'balance-sheet', label: 'Balance Sheet', component: BalanceSheetReport },
  { key: 'trial-balance', label: 'Trial Balance', component: TrialBalanceReport },
  { key: 'cash-bank-book', label: 'Cash / Bank Book', component: CashBankBookReport },
];

const DEFAULT_TAB = 'day-book';

const FinanceReportsPage = () => {
  const { tab } = useParams();
  const navigate = useNavigate();

  const active = TABS.some((item) => item.key === tab) ? tab : DEFAULT_TAB;
  const Current = TABS.find((item) => item.key === active).component;

  return (
    <div className="space-y-5">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Financial Reports</h1>
        <p className="text-sm text-gray-500 mt-1">
          Statements, registers and statements of account derived entirely from posted vouchers.
        </p>
      </div>

      <div className="border-b border-gray-200 overflow-x-auto">
        <nav className="flex gap-1 min-w-max">
          {TABS.map((item) => (
            <button
              key={item.key}
              type="button"
              onClick={() => navigate(`/dashboard/accounting/reports/${item.key}`)}
              className={`px-4 py-2.5 text-sm font-medium border-b-2 -mb-px whitespace-nowrap ${
                active === item.key
                  ? 'border-orange-500 text-orange-600'
                  : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300'
              }`}
            >
              {item.label}
            </button>
          ))}
        </nav>
      </div>

      <div key={active}>
        <Current />
      </div>
    </div>
  );
};

export default FinanceReportsPage;
