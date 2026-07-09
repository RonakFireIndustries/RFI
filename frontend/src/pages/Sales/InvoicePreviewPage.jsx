import { useState, useEffect, useRef, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useAuthStore } from '../../store/authStore';
import api from '../../services/api';
import {
  ArrowLeft, Download, Printer, Mail, CheckCircle, AlertTriangle, XCircle,
  Settings, FileText, Banknote, Signature, ChevronDown, ChevronUp,
  Eye,   EyeOff, Search, ExternalLink, RefreshCw, Loader2, Trash2
} from 'lucide-react';

const statusStyles = {
  Paid: 'bg-green-100 text-green-800',
  'Partially Paid': 'bg-yellow-100 text-yellow-800',
  Overdue: 'bg-red-100 text-red-800',
  Sent: 'bg-blue-100 text-blue-800',
  Draft: 'bg-gray-100 text-gray-600',
  Unpaid: 'bg-orange-100 text-orange-800',
  Cancelled: 'bg-red-50 text-red-400',
};

function Skeleton({ className = '' }) {
  return <div className={`animate-pulse bg-gray-200 rounded ${className}`} />;
}

export default function InvoicePreviewPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { user, permissions, roles } = useAuthStore();
  const printRef = useRef(null);

  const [data, setData] = useState(null);
  const [validation, setValidation] = useState(null);
  const [loading, setLoading] = useState(true);
  const [validating, setValidating] = useState(true);
  const [error, setError] = useState(null);
  const [downloading, setDownloading] = useState(false);
  const [emailing, setEmailing] = useState(false);
  const [emailModal, setEmailModal] = useState(false);
  const [emailForm, setEmailForm] = useState({ recipient: '', subject: '', message: '' });
  const [emailResult, setEmailResult] = useState(null);
  const [deleting, setDeleting] = useState(false);
  const [showSignature, setShowSignature] = useState(true);
  const [showTaxBreakdown, setShowTaxBreakdown] = useState(true);
  const [showBankDetails, setShowBankDetails] = useState(true);
  const [showPaymentTerms, setShowPaymentTerms] = useState(true);
  const [paperSize, setPaperSize] = useState('a4');
  const [showExportSettings, setShowExportSettings] = useState(true);
  const [showCompliance, setShowCompliance] = useState(true);

  const canView = permissions?.includes('view_invoices') || roles?.includes('Admin');
  const canExport = permissions?.includes('view_invoices') || roles?.includes('Admin') || roles?.includes('Accountant');

  const invoice = data?.invoice;
  const company = data?.company;
  const bankDetails = data?.bank_details;
  const signature = data?.signature;

  useEffect(() => {
    if (!canView) {
      setError('You do not have permission to view invoices.');
      setLoading(false);
      return;
    }
    fetchData();
  }, [id]);

  const fetchData = async () => {
    setLoading(true);
    setError(null);
    try {
      const [previewRes, validateRes] = await Promise.all([
        api.get(`/invoices/${id}/preview`),
        api.get(`/invoices/${id}/validate`).catch(() => null),
      ]);
      setData(previewRes.data?.data || previewRes.data);
      if (validateRes) {
        setValidation(validateRes.data?.data || validateRes.data);
      }
    } catch (err) {
      if (err.response?.status === 404) {
        setError('Invoice could not be located.');
      } else if (err.response?.status === 403) {
        setError('You do not have permission to view this invoice.');
      } else {
        setError('Failed to load invoice. Please try again.');
      }
    } finally {
      setLoading(false);
      setValidating(false);
    }
  };

  const handleDownloadPDF = async () => {
    if (!canExport) return;
    setDownloading(true);
    try {
      const response = await api.getBlob(`/invoices/${id}/pdf`);
      const url = window.URL.createObjectURL(response);
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `invoice-${invoice?.invoice_number || id}.pdf`);
      document.body.appendChild(link);
      link.click();
      link.parentNode.removeChild(link);
      window.URL.revokeObjectURL(url);
    } catch (err) {
      alert('Failed to download PDF.');
    } finally {
      setDownloading(false);
    }
  };

  const handlePrint = () => {
    window.print();
  };

  const handleEmail = async (e) => {
    e.preventDefault();
    if (!canExport) return;
    setEmailing(true);
    setEmailResult(null);
    try {
      const payload = {};
      if (emailForm.recipient) payload.recipient_email = emailForm.recipient;
      if (emailForm.subject) payload.subject = emailForm.subject;
      if (emailForm.message) payload.message = emailForm.message;
      const res = await api.post(`/invoices/${id}/email`, payload);
      setEmailResult({ success: true, message: res.meta?.message || res.data?.message || 'Invoice emailed successfully.' });
      setTimeout(() => { setEmailModal(false); setEmailResult(null); }, 2000);
    } catch (err) {
      setEmailResult({ success: false, message: err.response?.data?.message || 'Failed to send email.' });
    } finally {
      setEmailing(false);
    }
  };

  const handleDelete = async () => {
    if (!window.confirm('Are you sure you want to delete this invoice? This action cannot be undone.')) return;
    setDeleting(true);
    try {
      await api.delete(`/invoices/${id}`);
      navigate('/invoices');
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to delete invoice.');
      setDeleting(false);
    }
  };

  const formatCurrency = (val) => {
    const num = parseFloat(val || 0);
    return '₹' + num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  };

  const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
  };

  if (!canView) {
    return (
      <div className="flex items-center justify-center min-h-[70vh]">
        <div className="text-center max-w-md">
          <XCircle className="w-16 h-16 text-red-300 mx-auto mb-4" />
          <h2 className="text-xl font-bold text-gray-900 mb-2">Access Denied</h2>
          <p className="text-gray-500 mb-6">You do not have permission to view invoices.</p>
          <button onClick={() => navigate('/dashboard/invoices')} className="text-primary hover:underline font-medium">Return to Invoices</button>
        </div>
      </div>
    );
  }

  if (loading) {
    return (
      <div className="flex gap-6">
        <div className="flex-1">
          <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <div className="flex justify-between mb-8">
              <div className="space-y-3"><Skeleton className="h-8 w-48" /><Skeleton className="h-4 w-32" /></div>
              <div className="space-y-3 text-right"><Skeleton className="h-6 w-36 ml-auto" /><Skeleton className="h-4 w-24 ml-auto" /></div>
            </div>
            <Skeleton className="h-4 w-24 mb-4" />
            <Skeleton className="h-6 w-64 mb-2" />
            <Skeleton className="h-4 w-48 mb-8" />
            <Skeleton className="h-64 w-full mb-6" />
            <div className="flex justify-end"><Skeleton className="h-40 w-56" /></div>
          </div>
        </div>
        <div className="w-80 space-y-4">
          <Skeleton className="h-48 w-full rounded-xl" />
          <Skeleton className="h-52 w-full rounded-xl" />
          <Skeleton className="h-36 w-full rounded-xl" />
        </div>
      </div>
    );
  }

  if (error && !invoice) {
    return (
      <div className="flex items-center justify-center min-h-[70vh]">
        <div className="text-center max-w-md">
          <FileText className="w-16 h-16 text-gray-300 mx-auto mb-4" />
          <h2 className="text-xl font-bold text-gray-900 mb-2">{error === 'Invoice could not be located.' ? 'Invoice Not Found' : 'Error'}</h2>
          <p className="text-gray-500 mb-6">{error}</p>
          <button onClick={() => navigate('/dashboard/invoices')} className="text-primary hover:underline font-medium">Return to Invoices</button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-muted pb-10">
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center gap-4">
          <button onClick={() => navigate('/dashboard/invoices')} className="p-2 hover:bg-gray-200 rounded-lg transition-colors text-gray-500 hover:text-gray-700">
            <ArrowLeft className="w-5 h-5" />
          </button>
          <div>
            <h1 className="text-xl font-bold text-gray-900">Invoice {invoice?.invoice_number}</h1>
            <div className="flex items-center gap-3 mt-1">
              <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusStyles[invoice?.status] || 'bg-gray-100 text-gray-600'}`}>
                {invoice?.status}
              </span>
              <span className="text-sm text-gray-500">Created {formatDate(invoice?.created_at)}</span>
            </div>
          </div>
        </div>
        <div className="hidden lg:flex items-center gap-2">
          <button onClick={fetchData} className="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            <RefreshCw className="w-4 h-4 mr-1.5" /> Refresh
          </button>
        </div>
      </div>

      <div className="flex flex-col lg:flex-row gap-6">
        <div className="flex-1 min-w-0">
          <div ref={printRef} className="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden print:shadow-none print:border-0" style={{ maxWidth: '816px' }}>
            <div className="p-8 sm:p-10 print:p-0">
              <table className="w-full border-b border-gray-200 pb-6 mb-6">
                <tbody>
                  <tr>
                    <td className="align-top w-3/5">
                      <h2 className="text-xl font-bold text-primary">{company?.name || 'RFI Global ERP'}</h2>
                      {company?.address && <p className="text-xs text-gray-500 mt-1 leading-relaxed">{company.address}</p>}
                      {company?.gst_number && <p className="text-xs text-gray-500 mt-1">GST: {company.gst_number}</p>}
                      {company?.contact_email && <p className="text-xs text-gray-500 mt-1">{company.contact_email}{company?.contact_phone ? ` | ${company.contact_phone}` : ''}</p>}
                    </td>
                    <td className="align-top text-right w-2/5">
                      <h1 className="text-3xl font-bold text-gray-200 tracking-widest uppercase">Invoice</h1>
                      <p className="text-base font-bold text-gray-900 mt-3">#{invoice?.invoice_number}</p>
                      <p className="text-xs text-gray-500 mt-1">Date: {formatDate(invoice?.created_at)}</p>
                      {invoice?.due_date && <p className="text-xs text-gray-500 mt-1">Due Date: <span className="font-semibold text-gray-700">{formatDate(invoice.due_date)}</span></p>}
                      {invoice?.gst_type && <p className="text-xs text-gray-500 mt-1">GST: {invoice.gst_type.toUpperCase()} @ {invoice.gst_rate}%</p>}
                    </td>
                  </tr>
                </tbody>
              </table>

              <div className="flex justify-between mb-8">
                <div className="w-1/2 pr-4">
                  <p className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Bill To</p>
                  {invoice?.customer ? (
                    <>
                      <h3 className="text-base font-bold text-gray-900">{invoice.customer.name}</h3>
                      {invoice.customer.address && <p className="text-xs text-gray-500 mt-1">{invoice.customer.address}</p>}
                      {invoice.customer.email && <p className="text-xs text-gray-500 mt-1">{invoice.customer.email}</p>}
                      {invoice.customer.phone && <p className="text-xs text-gray-500 mt-1">{invoice.customer.phone}</p>}
                      {invoice.customer.gst_number && <span className="inline-block mt-2 text-xs font-medium bg-gray-100 text-gray-700 px-2 py-1 rounded">GSTIN: {invoice.customer.gst_number}</span>}
                    </>
                  ) : (
                    <p className="text-sm text-gray-400 italic">Customer information unavailable.</p>
                  )}
                </div>
                <div className="w-1/2 pl-4 text-right">
                  {invoice?.salesOrder?.shipping_address && (
                    <>
                      <p className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Ship To</p>
                      <p className="text-xs text-gray-500">{invoice.salesOrder.shipping_address}</p>
                    </>
                  )}
                </div>
              </div>

              <div className="overflow-x-auto mb-8">
                <table className="w-full text-left">
                  <thead>
                    <tr className="bg-gray-50 border-y border-gray-200">
                      <th className="py-3 px-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-8">#</th>
                      <th className="py-3 px-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Description</th>
                      <th className="py-3 px-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">HSN/SKU</th>
                      <th className="py-3 px-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Qty</th>
                      <th className="py-3 px-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Rate</th>
                      <th className="py-3 px-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Amount</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-100">
                    {invoice?.items?.length > 0 ? invoice.items.map((item, index) => (
                      <tr key={item.id} className="text-sm hover:bg-gray-50/50">
                        <td className="py-3 px-3 text-gray-400">{index + 1}</td>
                        <td className="py-3 px-3">
                          <p className="font-medium text-gray-900">{item.item_description}</p>
                          {item.product?.sku && <p className="text-xs text-gray-400 mt-0.5">SKU: {item.product.sku}</p>}
                        </td>
                        <td className="py-3 px-3 text-center text-gray-500 text-xs">{item.hsn_code || item.product?.hsn_code || '-'}</td>
                        <td className="py-3 px-3 text-center font-medium text-gray-900">{item.quantity}</td>
                        <td className="py-3 px-3 text-right text-gray-600">{formatCurrency(item.unit_price)}</td>
                        <td className="py-3 px-3 text-right font-medium text-gray-900">{formatCurrency(item.total)}</td>
                      </tr>
                    )) : (
                      <tr>
                        <td colSpan="6" className="py-8 text-center text-gray-400 text-sm">No invoice line items found.</td>
                      </tr>
                    )}
                  </tbody>
                </table>
              </div>

              <div className="flex flex-col sm:flex-row justify-between gap-6 mb-8">
                <div className="space-y-4 sm:w-1/2">
                  {invoice?.notes && (
                    <div>
                      <p className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Notes</p>
                      <p className="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg border border-gray-100">{invoice.notes}</p>
                    </div>
                  )}
                  {showPaymentTerms && invoice?.terms && (
                    <div>
                      <p className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Terms & Conditions</p>
                      <p className="text-xs text-gray-500 whitespace-pre-line bg-gray-50 p-3 rounded-lg border border-gray-100">{invoice.terms}</p>
                    </div>
                  )}
                </div>
                <div className="sm:w-72">
                  <div className="bg-gray-50 rounded-xl border border-gray-100 p-5">
                    <div className="space-y-2.5">
                      <div className="flex justify-between text-sm">
                        <span className="text-gray-500">Subtotal</span>
                        <span className="font-medium text-gray-900">{formatCurrency(invoice?.subtotal)}</span>
                      </div>

                      {showTaxBreakdown && parseFloat(invoice?.cgst_total || 0) > 0 && (
                        <div className="flex justify-between text-sm">
                          <span className="text-gray-500">CGST</span>
                          <span className="font-medium text-gray-900">{formatCurrency(invoice.cgst_total)}</span>
                        </div>
                      )}
                      {showTaxBreakdown && parseFloat(invoice?.sgst_total || 0) > 0 && (
                        <div className="flex justify-between text-sm">
                          <span className="text-gray-500">SGST</span>
                          <span className="font-medium text-gray-900">{formatCurrency(invoice.sgst_total)}</span>
                        </div>
                      )}
                      {showTaxBreakdown && parseFloat(invoice?.igst_total || 0) > 0 && (
                        <div className="flex justify-between text-sm">
                          <span className="text-gray-500">IGST</span>
                          <span className="font-medium text-gray-900">{formatCurrency(invoice.igst_total)}</span>
                        </div>
                      )}

                      <div className="pt-3 mt-3 border-t border-gray-200 flex justify-between items-center">
                        <span className="font-bold text-gray-900">Grand Total</span>
                        <span className="font-bold text-primary text-lg">{formatCurrency(invoice?.grand_total)}</span>
                      </div>

                      {parseFloat(invoice?.paid_amount || 0) > 0 && (
                        <>
                          <div className="flex justify-between text-sm text-green-600 pt-2 mt-2 border-t border-gray-100">
                            <span>Paid</span>
                            <span className="font-medium">{formatCurrency(invoice.paid_amount)}</span>
                          </div>
                          <div className="flex justify-between text-sm text-red-600 font-bold">
                            <span>Balance Due</span>
                            <span>{formatCurrency(parseFloat(invoice.grand_total) - parseFloat(invoice.paid_amount))}</span>
                          </div>
                        </>
                      )}
                    </div>
                  </div>
                </div>
              </div>

              {showBankDetails && bankDetails && (
                <div className="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6 max-w-md">
                  <div className="flex items-center gap-2 mb-2">
                    <Banknote className="w-4 h-4 text-gray-400" />
                    <span className="text-xs font-bold text-gray-500 uppercase tracking-wider">Bank Details</span>
                  </div>
                  <div className="space-y-1 text-xs text-gray-600">
                    <p><span className="font-medium text-gray-700">Bank:</span> Ronak Fire Industries Pvt. Ltd.</p>
                    <p><span className="font-medium text-gray-700">Account Name:</span> {bankDetails.account_name}</p>
                    <p><span className="font-medium text-gray-700">Account No:</span> {bankDetails.account_number}</p>
                    <p><span className="font-medium text-gray-700">IFSC:</span> {bankDetails.ifsc_code}</p>
                    <p><span className="font-medium text-gray-700">Branch:</span> {bankDetails.branch}</p>
                  </div>
                </div>
              )}

              {showSignature && signature && (
                <div className="border-t border-gray-200 pt-6 mt-6">
                  <div className="flex justify-between items-end">
                    <div>
                      <p className="text-xs text-gray-400 uppercase tracking-wider font-bold mb-1">Authorised Signatory</p>
                      <div className="w-48 h-px bg-gray-300 mt-8 mb-1"></div>
                      <p className="text-sm font-semibold text-gray-800">{signature.signatory_name}</p>
                      <p className="text-xs text-gray-500">For {company?.name || 'RFI Global ERP'}</p>
                    </div>
                    {signature.seal_url && (
                      <img src={signature.seal_url} alt="Company Seal" className="h-16 w-16 object-contain opacity-70" />
                    )}
                  </div>
                </div>
              )}

              <div className="border-t border-gray-200 pt-5 mt-6 text-center">
                <p className="text-xs text-gray-400 font-medium">Thank you for your business!</p>
                <p className="text-[10px] text-gray-400 mt-2">
                  Generated on {new Date().toLocaleString('en-US', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })} |
                  by {user?.name || 'System'} | Ref: {invoice?.invoice_number}
                </p>
                <p className="text-[10px] text-gray-400 mt-1">This is a computer-generated invoice. | Subject to local jurisdiction</p>
              </div>
            </div>
          </div>
        </div>

        <div className="w-full lg:w-80 shrink-0 space-y-4">
          <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div className="px-5 py-4 border-b border-gray-100">
              <h3 className="text-sm font-semibold text-gray-900">Actions</h3>
            </div>
            <div className="p-4 space-y-2.5">
              <button onClick={handleDownloadPDF} disabled={!canExport || downloading}
                className="w-full inline-flex items-center justify-center px-4 py-2.5 bg-primary hover:bg-primary/90 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                {downloading ? <Loader2 className="w-4 h-4 mr-2 animate-spin" /> : <Download className="w-4 h-4 mr-2" />}
                {downloading ? 'Downloading...' : 'Download PDF'}
              </button>
              <button onClick={handlePrint}
                className="w-full inline-flex items-center justify-center px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                <Printer className="w-4 h-4 mr-2" /> Print Invoice
              </button>
              <button onClick={() => { setEmailForm({ recipient: invoice?.customer?.email || '', subject: `Invoice ${invoice?.invoice_number} from ${company?.name || 'RFI Global ERP'}`, message: '' }); setEmailModal(true); }}
                disabled={!canExport}
                className="w-full inline-flex items-center justify-center px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 disabled:opacity-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                <Mail className="w-4 h-4 mr-2" /> Email Invoice
              </button>
              <button onClick={handleDelete} disabled={deleting}
                className="w-full inline-flex items-center justify-center px-4 py-2.5 bg-white border border-red-300 hover:bg-red-50 disabled:opacity-50 text-red-600 text-sm font-medium rounded-lg transition-colors">
                <Trash2 className="w-4 h-4 mr-2" /> {deleting ? 'Deleting...' : 'Delete Invoice'}
              </button>
            </div>
          </div>

          <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <button onClick={() => setShowExportSettings(!showExportSettings)} className="w-full px-5 py-4 flex items-center justify-between border-b border-gray-100 hover:bg-gray-50 transition-colors">
              <div className="flex items-center gap-2">
                <Settings className="w-4 h-4 text-gray-400" />
                <h3 className="text-sm font-semibold text-gray-900">Export Settings</h3>
              </div>
              {showExportSettings ? <ChevronUp className="w-4 h-4 text-gray-400" /> : <ChevronDown className="w-4 h-4 text-gray-400" />}
            </button>
            {showExportSettings && (
              <div className="p-4 space-y-3">
                <div>
                  <label className="text-xs font-medium text-gray-500 mb-1.5 block">Paper Size</label>
                  <select value={paperSize} onChange={e => setPaperSize(e.target.value)} className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-ring focus:border-ring">
                    <option value="a4">A4</option>
                    <option value="letter">Letter</option>
                    <option value="legal">Legal</option>
                  </select>
                </div>
                <div className="space-y-2">
                  <label className="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" checked={showSignature} onChange={e => setShowSignature(e.target.checked)} className="rounded border-gray-300 text-primary focus:ring-ring" />
                    <span className="text-sm text-gray-600">Include Signature</span>
                  </label>
                  <label className="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" checked={showTaxBreakdown} onChange={e => setShowTaxBreakdown(e.target.checked)} className="rounded border-gray-300 text-primary focus:ring-ring" />
                    <span className="text-sm text-gray-600">Include Tax Breakdown</span>
                  </label>
                  <label className="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" checked={showBankDetails} onChange={e => setShowBankDetails(e.target.checked)} className="rounded border-gray-300 text-primary focus:ring-ring" />
                    <span className="text-sm text-gray-600">Include Bank Details</span>
                  </label>
                  <label className="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" checked={showPaymentTerms} onChange={e => setShowPaymentTerms(e.target.checked)} className="rounded border-gray-300 text-primary focus:ring-ring" />
                    <span className="text-sm text-gray-600">Include Payment Terms</span>
                  </label>
                </div>
              </div>
            )}
          </div>

          <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <button onClick={() => setShowCompliance(!showCompliance)} className="w-full px-5 py-4 flex items-center justify-between border-b border-gray-100 hover:bg-gray-50 transition-colors">
              <div className="flex items-center gap-2">
                <CheckCircle className="w-4 h-4 text-gray-400" />
                <h3 className="text-sm font-semibold text-gray-900">Compliance</h3>
              </div>
              {showCompliance ? <ChevronUp className="w-4 h-4 text-gray-400" /> : <ChevronDown className="w-4 h-4 text-gray-400" />}
            </button>
            {showCompliance && (
              <div className="p-4">
                {validating ? (
                  <div className="space-y-3">
                    {[1, 2, 3].map(i => <Skeleton key={i} className="h-12 w-full" />)}
                  </div>
                ) : validation?.validations?.length > 0 ? (
                  <div className="space-y-2.5">
                    {validation.validations.map((v, i) => (
                      <div key={i} className={`flex items-start gap-2.5 p-2.5 rounded-lg text-sm ${v.status === 'passed' ? 'bg-green-50' : v.status === 'failed' ? 'bg-red-50' : 'bg-yellow-50'}`}>
                        {v.status === 'passed' ? <CheckCircle className="w-4 h-4 text-green-500 mt-0.5 shrink-0" />
                          : v.status === 'failed' ? <XCircle className="w-4 h-4 text-red-500 mt-0.5 shrink-0" />
                            : <AlertTriangle className="w-4 h-4 text-yellow-500 mt-0.5 shrink-0" />}
                        <div>
                          <p className={`font-medium ${v.status === 'passed' ? 'text-green-800' : v.status === 'failed' ? 'text-red-800' : 'text-yellow-800'}`}>{v.type}</p>
                          <p className={`text-xs mt-0.5 ${v.status === 'passed' ? 'text-green-600' : v.status === 'failed' ? 'text-red-600' : 'text-yellow-600'}`}>{v.message}</p>
                        </div>
                      </div>
                    ))}
                    <div className={`mt-3 text-center text-xs font-medium py-2 rounded-lg ${validation.overall_status === 'passed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}`}>
                      {validation.overall_status === 'passed' ? 'All checks passed' : 'Some checks require attention'}
                    </div>
                  </div>
                ) : (
                  <div className="text-center py-4">
                    <Loader2 className="w-6 h-6 animate-spin text-gray-300 mx-auto mb-2" />
                    <p className="text-xs text-gray-400">Running compliance checks...</p>
                  </div>
                )}
              </div>
            )}
          </div>
        </div>
      </div>

      {emailModal && (
        <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onClick={() => { if (!emailing) { setEmailModal(false); setEmailResult(null); } }}>
          <div className="bg-white rounded-xl shadow-xl max-w-lg w-full p-6" onClick={e => e.stopPropagation()}>
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-bold text-gray-900">Email Invoice</h3>
              <button onClick={() => { setEmailModal(false); setEmailResult(null); }} className="text-gray-400 hover:text-gray-600" disabled={emailing}>
                <XCircle className="w-5 h-5" />
              </button>
            </div>
            <form onSubmit={handleEmail} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Recipient Email</label>
                <input type="email" value={emailForm.recipient} onChange={e => setEmailForm({ ...emailForm, recipient: e.target.value })}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-ring focus:border-ring"
                  placeholder="customer@example.com" required />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                <input type="text" value={emailForm.subject} onChange={e => setEmailForm({ ...emailForm, subject: e.target.value })}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-ring focus:border-ring"
                  placeholder={`Invoice ${invoice?.invoice_number} from ${company?.name || 'RFI Global ERP'}`} />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Message</label>
                <textarea value={emailForm.message} onChange={e => setEmailForm({ ...emailForm, message: e.target.value })} rows={4}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-ring focus:border-ring resize-none"
                  placeholder="Optional message..." />
              </div>
              {emailResult && (
                <div className={`p-3 rounded-lg text-sm ${emailResult.success ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'}`}>
                  {emailResult.message}
                </div>
              )}
              <div className="flex justify-end gap-3 pt-2">
                <button type="button" onClick={() => { setEmailModal(false); setEmailResult(null); }} disabled={emailing}
                  className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                <button type="submit" disabled={emailing}
                  className="px-4 py-2 text-sm font-medium text-white bg-primary hover:bg-primary/90 disabled:opacity-50 rounded-lg transition-colors inline-flex items-center">
                  {emailing ? <Loader2 className="w-4 h-4 mr-2 animate-spin" /> : <Mail className="w-4 h-4 mr-2" />}
                  {emailing ? 'Sending...' : 'Send Email'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
