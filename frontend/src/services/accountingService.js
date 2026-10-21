import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

/**
 * Tally-style double-entry accounting API.
 *
 * Slice 1 covers the chart of accounts (groups + ledgers) and financial years.
 * Vouchers, BRS and reports are added by later slices.
 */

export const accountGroupService = {
  list: async (params) => unwrapList((await api.get(`/account-groups${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/account-groups/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/account-groups', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/account-groups/${id}`, payload)).data),
  remove: async (id) => api.delete(`/account-groups/${id}`),
};

export const ledgerService = {
  list: async (params) => unwrapList((await api.get(`/ledgers${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/ledgers/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/ledgers', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/ledgers/${id}`, payload)).data),
  remove: async (id) => api.delete(`/ledgers/${id}`),
};

export const financialYearService = {
  list: async (params) => unwrapList((await api.get(`/financial-years${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/financial-years/${id}`)).data),
  current: async () => unwrapItem((await api.get('/financial-years/current')).data),
  create: async (payload) => unwrapItem((await api.post('/financial-years', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/financial-years/${id}`, payload)).data),
  activate: async (id) => unwrapItem((await api.post(`/financial-years/${id}/activate`)).data),
  remove: async (id) => api.delete(`/financial-years/${id}`),
};

/**
 * Tally-style vouchers (slice 2): Contra, Payment, Receipt and Journal.
 * `partyOutstanding` feeds the bill-wise panel of party vouchers.
 */
export const voucherService = {
  list: async (params) => unwrapList((await api.get(`/vouchers${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/vouchers/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/vouchers', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/vouchers/${id}`, payload)).data),
  remove: async (id) => api.delete(`/vouchers/${id}`),
  partyOutstanding: async (ledgerId) =>
    unwrapItem((await api.get(`/vouchers/party-outstanding/${ledgerId}`)).data),
  meta: async () => unwrapItem((await api.get('/vouchers/meta')).data),
  /**
   * Products a line can pick. Scoped to the voucher's own module (and its
   * voucher type), so accounting-only users need no products permission.
   */
  products: async (voucherType) =>
    unwrapList((await api.get(`/vouchers/products${normalizeQuery({ voucher_type: voucherType })}`)).data),
};

/**
 * Bank reconciliation (slice 4).
 * `ledgers` lists the bank accounts with their book balances; `statement`
 * renders one account's reconciliation, and reconcile/unreconcile stamp or
 * clear the cleared date on the voucher lines.
 */
export const bankReconciliationService = {
  ledgers: async () => unwrapList((await api.get('/bank-reconciliation')).data),
  statement: async (ledgerId, params) =>
    unwrapItem((await api.get(`/bank-reconciliation/${ledgerId}${normalizeQuery(params)}`)).data),
  reconcile: async (ledgerId, payload) =>
    unwrapItem((await api.post(`/bank-reconciliation/${ledgerId}/reconcile`, payload)).data),
  unreconcile: async (ledgerId, payload) =>
    unwrapItem((await api.post(`/bank-reconciliation/${ledgerId}/unreconcile`, payload)).data),
};

/*
 * Financial report pack (slice 5).
 *
 * Reports are paginated-by-nothing, so the `per_page` default that
 * normalizeQuery injects for list resources is suppressed here.
 */
const reportQuery = (params = {}) => normalizeQuery({ per_page: undefined, ...params });

export const financeReportService = {
  ledgerStatement: async (ledgerId, params) =>
    unwrapItem((await api.get(`/accounting-reports/ledger/${ledgerId}${reportQuery(params)}`)).data),
  dayBook: async (params) =>
    unwrapItem((await api.get(`/accounting-reports/day-book${reportQuery(params)}`)).data),
  outstanding: async (params) =>
    unwrapItem((await api.get(`/accounting-reports/outstanding${reportQuery(params)}`)).data),
  profitLoss: async (params) =>
    unwrapItem((await api.get(`/accounting-reports/profit-loss${reportQuery(params)}`)).data),
  balanceSheet: async (params) =>
    unwrapItem((await api.get(`/accounting-reports/balance-sheet${reportQuery(params)}`)).data),
  cashBankBook: async (params) =>
    unwrapItem((await api.get(`/accounting-reports/cash-bank-book${reportQuery(params)}`)).data),
  trialBalance: async (params) =>
    unwrapItem((await api.get(`/accounting-reports/trial-balance${reportQuery(params)}`)).data),

  /** Goods receipt / delivery breakdown (warehouse side, `stock-receipts.view`). */
  receiptBreakdown: async (params) =>
    unwrapItem((await api.get(`/accounting-reports/receipt-breakdown${reportQuery(params)}`)).data),
};
