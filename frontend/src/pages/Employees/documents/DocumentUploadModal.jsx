import React, { useState } from 'react';
import { X } from 'lucide-react';
import { useDocumentStore } from '../../../store/documentStore';

const DOCUMENT_TYPES = [
  'Employee Photo', 'Resume', 'CV', 'Offer Letter', 'Employment Contract',
  'Joining Letter', 'Aadhaar', 'PAN', 'Passport', 'Driving License',
  'Bank Details', 'Educational Certificates', 'Experience Certificates',
  'Medical Documents', 'Other Attachments'
];

export default function DocumentUploadModal({ employeeId, document = null, onClose }) {
  const uploadDocument = useDocumentStore(state => state.uploadDocument);
  const updateDocument = useDocumentStore(state => state.updateDocument);

  const [file, setFile] = useState(null);
  const [documentType, setDocumentType] = useState(document?.document_type || '');
  const [expiryDate, setExpiryDate] = useState(document?.expiry_date || '');
  const [remarks, setRemarks] = useState(document?.remarks || '');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError(null);

    if (!document && !file) {
      setError('Please select a file to upload.');
      return;
    }

    if (file) {
      const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
      if (!allowedTypes.includes(file.type)) {
        setError('Invalid file type. Only PDF, JPG, PNG, WEBP allowed.');
        return;
      }
      if (file.size > 10 * 1024 * 1024) {
        setError('File size exceeds 10MB limit.');
        return;
      }
    }

    const formData = new FormData();
    if (file) formData.append('file', file);
    formData.append('document_type', documentType);
    if (expiryDate) formData.append('expiry_date', expiryDate);
    if (remarks) formData.append('remarks', remarks);

    setLoading(true);
    try {
      if (document) {
        await updateDocument(document.id, formData);
      } else {
        await uploadDocument(employeeId, formData);
      }
      onClose();
    } catch (err) {
      setError(err.message || 'Failed to upload document');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
      <div className="w-full max-w-md rounded-lg bg-white shadow-xl">
        <div className="flex items-center justify-between border-b border-gray-100 px-5 py-4">
          <h2 className="text-lg font-semibold text-gray-900">{document ? 'Update' : 'Upload'} Document</h2>
          <button onClick={onClose} className="rounded-md p-1 text-gray-500 hover:bg-gray-100">
            <X className="h-5 w-5" />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="p-5 space-y-4">
          {error && <div className="p-3 bg-red-50 text-red-700 rounded text-sm">{error}</div>}

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Document Type *</label>
            <select
              required
              value={documentType}
              onChange={(e) => setDocumentType(e.target.value)}
              className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-ring focus:ring-1 focus:ring-ring"
            >
              <option value="">Select Type</option>
              {DOCUMENT_TYPES.map(type => (
                <option key={type} value={type}>{type}</option>
              ))}
            </select>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              File {document && '(Leave blank to keep existing)'} *
            </label>
            <input
              type="file"
              accept=".pdf,.jpg,.jpeg,.png,.webp"
              onChange={(e) => setFile(e.target.files[0])}
              required={!document}
              className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-ring focus:ring-1 focus:ring-ring"
            />
            <p className="text-xs text-gray-500 mt-1">Max 10MB. Allowed: PDF, JPG, PNG, WEBP</p>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
            <input
              type="date"
              value={expiryDate}
              onChange={(e) => setExpiryDate(e.target.value)}
              className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-ring focus:ring-1 focus:ring-ring"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
            <textarea
              value={remarks}
              onChange={(e) => setRemarks(e.target.value)}
              rows="2"
              className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-ring focus:ring-1 focus:ring-ring"
            />
          </div>

          <div className="flex justify-end gap-3 pt-2">
            <button type="button" onClick={onClose} className="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 border rounded-md">Cancel</button>
            <button type="submit" disabled={loading} className="px-4 py-2 text-sm font-semibold text-white bg-primary hover:bg-primary/90 rounded-md disabled:opacity-50">
              {loading ? 'Saving...' : 'Save'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
