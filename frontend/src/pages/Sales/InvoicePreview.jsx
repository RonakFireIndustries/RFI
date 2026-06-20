import { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { ArrowLeft, Printer, Download, Mail, Edit, Share2 } from 'lucide-react';
import api from '../../services/api';

export default function InvoicePreview() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [invoice, setInvoice] = useState(null);
  const [loading, setLoading] = useState(true);
  const [downloading, setDownloading] = useState(false);

  useEffect(() => {
    fetchInvoice();
  }, [id]);

  const fetchInvoice = async () => {
    setLoading(true);
    try {
      const res = await api.get(`/invoices/${id}`);
      setInvoice(res.data);
    } catch (error) {
      console.error("Error fetching invoice:", error);
    } finally {
      setLoading(false);
    }
  };

  const handleDownloadPDF = async () => {
    setDownloading(true);
    try {
      const response = await api.get(`/invoices/${id}/pdf`, {
        responseType: 'blob', // Important
      });
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('href');
      link.href = url;
      link.setAttribute('download', `${invoice.invoice_number}.pdf`);
      document.body.appendChild(link);
      link.click();
      link.parentNode.removeChild(link);
    } catch (error) {
      console.error("Error downloading PDF:", error);
      alert("Failed to download PDF.");
    } finally {
      setDownloading(false);
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-96">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-[#1a56db]"></div>
      </div>
    );
  }

  if (!invoice) {
    return (
      <div className="text-center py-20">
        <h2 className="text-2xl font-bold text-gray-900 mb-2">Invoice Not Found</h2>
        <p className="text-gray-500 mb-6">The invoice you are looking for does not exist or has been deleted.</p>
        <button onClick={() => navigate('/dashboard/invoices')} className="text-[#1a56db] hover:underline font-medium">
          Return to Invoices
        </button>
      </div>
    );
  }

  return (
    <div className="max-w-5xl mx-auto pb-20">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div className="flex items-center">
          <button onClick={() => navigate('/dashboard/invoices')} className="mr-4 text-gray-500 hover:text-gray-700 transition-colors p-2 hover:bg-gray-100 rounded-full">
            <ArrowLeft className="w-5 h-5" />
          </button>
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Invoice {invoice.invoice_number}</h1>
            <p className="text-sm text-gray-500 flex items-center mt-1">
              <span className={`inline-block w-2 h-2 rounded-full mr-2 ${invoice.status === 'Paid' ? 'bg-green-500' : 'bg-yellow-500'}`}></span>
              Status: <span className="font-semibold ml-1 text-gray-700">{invoice.status}</span>
            </p>
          </div>
        </div>
        <div className="flex space-x-3 w-full sm:w-auto">
          <button 
            className="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors shadow-sm"
          >
            <Mail className="w-4 h-4 mr-2" />
            Send Email
          </button>
          <button 
            onClick={handleDownloadPDF}
            disabled={downloading}
            className="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 bg-[#1a56db] hover:bg-[#1e40af] text-white text-sm font-medium rounded-lg transition-colors shadow-sm disabled:opacity-70"
          >
            {downloading ? <div className="w-4 h-4 mr-2 border-2 border-white border-t-transparent rounded-full animate-spin"></div> : <Download className="w-4 h-4 mr-2" />}
            {downloading ? 'Downloading...' : 'Download PDF'}
          </button>
        </div>
      </div>

      {/* A4 Document Preview Wrapper */}
      <div className="bg-white shadow-xl mx-auto rounded-none sm:rounded-lg overflow-hidden" style={{ minHeight: '1056px', width: '100%', maxWidth: '816px' }}>
        <div className="p-10 sm:p-16">
          
          {/* Header */}
          <div className="flex justify-between items-start border-b-2 border-gray-100 pb-8 mb-8">
            <div>
              <h2 className="text-3xl font-black text-[#1a56db] tracking-tighter">RFI ERP</h2>
              <div className="mt-4 text-gray-500 text-sm">
              </div>
            </div>
            <div className="text-right">
              <h1 className="text-4xl font-bold text-gray-200 tracking-widest uppercase">Invoice</h1>
              <div className="mt-4">
                <p className="text-gray-500 text-sm mb-1">Invoice Number</p>
                <p className="font-bold text-gray-900 text-lg">{invoice.invoice_number}</p>
              </div>
              <div className="mt-4">
                <p className="text-gray-500 text-sm mb-1">Date of Issue</p>
                <p className="font-medium text-gray-900">{new Date(invoice.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
              </div>
              {invoice.due_date && (
                <div className="mt-4 bg-gray-50 p-2 rounded border border-gray-100 inline-block text-right">
                  <p className="text-gray-500 text-xs uppercase font-bold tracking-wider mb-1">Due Date</p>
                  <p className="font-bold text-red-600">{new Date(invoice.due_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
                </div>
              )}
            </div>
          </div>

          {/* Billing Info */}
          <div className="flex justify-between mb-10">
            <div className="w-1/2 pr-4">
              <p className="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Billed To</p>
              <h3 className="text-lg font-bold text-gray-900">{invoice.customer?.name}</h3>
              <div className="mt-2 text-sm text-gray-600 space-y-1">
                <p>{invoice.customer?.address}</p>
                <p>{invoice.customer?.email}</p>
                <p>{invoice.customer?.phone}</p>
                {invoice.customer?.gst_number && (
                  <p className="mt-2 font-medium text-gray-800 bg-gray-100 inline-block px-2 py-1 rounded">GSTIN: {invoice.customer.gst_number}</p>
                )}
              </div>
            </div>
          </div>

          {/* Table */}
          <div className="mb-10">
            <table className="w-full text-left">
              <thead>
                <tr className="bg-gray-50 border-y border-gray-200">
                  <th className="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Item Description</th>
                  <th className="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">HSN/SAC</th>
                  <th className="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Qty</th>
                  <th className="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Rate</th>
                  <th className="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Amount</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {invoice.items?.map((item, index) => (
                  <tr key={item.id} className="text-sm">
                    <td className="py-4 px-4">
                      <p className="font-semibold text-gray-900">{item.item_description}</p>
                      {item.product && <p className="text-xs text-gray-500 mt-1">SKU: {item.product.sku}</p>}
                    </td>
                    <td className="py-4 px-4 text-center text-gray-600">{item.hsn_code || '-'}</td>
                    <td className="py-4 px-4 text-center font-medium text-gray-900">{item.quantity}</td>
                    <td className="py-4 px-4 text-right text-gray-600">₹{parseFloat(item.unit_price).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td className="py-4 px-4 text-right font-medium text-gray-900">₹{parseFloat(item.total).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {/* Totals */}
          <div className="flex justify-between items-start mb-12">
            <div className="w-1/2 pr-10">
              {invoice.notes && (
                <div className="mb-6">
                  <p className="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Notes</p>
                  <p className="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg border border-gray-100">{invoice.notes}</p>
                </div>
              )}
              {invoice.terms && (
                <div>
                  <p className="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Terms & Conditions</p>
                  <p className="text-xs text-gray-500 whitespace-pre-line">{invoice.terms}</p>
                </div>
              )}
            </div>
            
            <div className="w-1/2 max-w-sm ml-auto bg-gray-50 p-6 rounded-xl border border-gray-100">
              <div className="space-y-3 text-sm">
                <div className="flex justify-between text-gray-600">
                  <span>Subtotal</span>
                  <span className="font-medium text-gray-900">₹{parseFloat(invoice.subtotal).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                </div>
                
                {parseFloat(invoice.cgst_total) > 0 && (
                  <div className="flex justify-between text-gray-600">
                    <span>CGST</span>
                    <span className="font-medium text-gray-900">₹{parseFloat(invoice.cgst_total).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                  </div>
                )}
                
                {parseFloat(invoice.sgst_total) > 0 && (
                  <div className="flex justify-between text-gray-600">
                    <span>SGST</span>
                    <span className="font-medium text-gray-900">₹{parseFloat(invoice.sgst_total).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                  </div>
                )}
                
                {parseFloat(invoice.igst_total) > 0 && (
                  <div className="flex justify-between text-gray-600">
                    <span>IGST</span>
                    <span className="font-medium text-gray-900">₹{parseFloat(invoice.igst_total).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                  </div>
                )}
  
                <div className="pt-4 mt-4 border-t border-gray-200 flex justify-between items-center">
                  <span className="font-bold text-gray-900">Grand Total</span>
                  <span className="font-black text-[#1a56db] text-xl">
                    ₹{parseFloat(invoice.grand_total).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                  </span>
                </div>

                {parseFloat(invoice.paid_amount) > 0 && (
                  <>
                    <div className="flex justify-between text-green-600 pt-2 mt-2 border-t border-gray-100">
                      <span>Amount Paid</span>
                      <span className="font-medium">₹{parseFloat(invoice.paid_amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                    </div>
                    <div className="flex justify-between text-red-600 font-bold mt-1">
                      <span>Balance Due</span>
                      <span>₹{(parseFloat(invoice.grand_total) - parseFloat(invoice.paid_amount)).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                    </div>
                  </>
                )}
              </div>
            </div>
          </div>

          <div className="border-t-2 border-gray-100 pt-8 mt-auto text-center">
            <p className="text-gray-400 text-xs font-medium uppercase tracking-widest">Thank you for your business!</p>
            <p className="text-gray-500 text-xs mt-2">This is a computer generated invoice and requires no signature.</p>
          </div>

        </div>
      </div>
    </div>
  );
}
