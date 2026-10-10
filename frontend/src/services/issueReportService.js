import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const issueReportService = {
  list: async (params) => unwrapList((await api.get(`/issue-reports${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/issue-reports/${id}`)).data),
  create: async (formData) => unwrapItem((await api.post('/issue-reports', formData)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/issue-reports/${id}`, payload)).data),
  remove: async (id) => api.delete(`/issue-reports/${id}`),
};

export const ISSUE_CATEGORIES = [
  { value: 'bug', label: 'Bug / Error' },
  { value: 'ui', label: 'UI / Display' },
  { value: 'performance', label: 'Performance' },
  { value: 'data', label: 'Data Issue' },
  { value: 'access', label: 'Access / Permissions' },
  { value: 'other', label: 'Other' },
];

export const ISSUE_PRIORITIES = [
  { value: 'low', label: 'Low' },
  { value: 'medium', label: 'Medium' },
  { value: 'high', label: 'High' },
  { value: 'critical', label: 'Critical' },
];

export const ISSUE_STATUSES = [
  { value: 'open', label: 'Open' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'resolved', label: 'Resolved' },
  { value: 'closed', label: 'Closed' },
];

export const categoryLabel = (value) =>
  ISSUE_CATEGORIES.find((c) => c.value === value)?.label || value;

export const statusLabel = (value) =>
  ISSUE_STATUSES.find((s) => s.value === value)?.label || value;

export const STATUS_STYLES = {
  open: 'bg-warning/10 text-warning',
  in_progress: 'bg-primary/10 text-primary',
  resolved: 'bg-success/10 text-success',
  closed: 'bg-muted text-muted-foreground',
};

export const PRIORITY_STYLES = {
  low: 'bg-muted text-muted-foreground',
  medium: 'bg-primary/10 text-primary',
  high: 'bg-warning/10 text-warning',
  critical: 'bg-destructive/10 text-destructive',
};
