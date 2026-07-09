import React, { useEffect, useState } from 'react';
import { Eye, Download, Trash2, Edit2, FileText, Plus } from 'lucide-react';
import { useDocumentStore } from '../../../store/documentStore';
import DocumentUploadModal from './DocumentUploadModal';
import DocumentPreviewModal from './DocumentPreviewModal';

export default function EmployeeDocumentsPage({ employeeId }) {
  const { documents, loading, error, fetchDocuments, deleteDocument } = useDocumentStore();

  const [uploadModalOpen, setUploadModalOpen] = useState(false);
  const [previewDoc, setPreviewDoc] = useState(null);
  const [editingDoc, setEditingDoc] = useState(null);

  useEffect(() => {
    if (employeeId) fetchDocuments(employeeId);
  }, [employeeId, fetchDocuments]);

  const handleDelete = async (id, name) => {
    if (window.confirm(`Are you sure you want to delete ${name}?`)) {
      await deleteDocument(id);
    }
  };

  if (loading && documents.length === 0) return <div className="p-4 text-gray-500">Loading documents...</div>;

  return (
    <div className="space-y-4">
      <div className="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-gray-100">
        <div>
          <h3 className="text-lg font-semibold text-gray-900">Employee Documents</h3>
          <p className="text-sm text-gray-500">Manage sensitive files and employment records</p>
        </div>
          <button
            onClick={() => setUploadModalOpen(true)}
            className="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-md hover:bg-primary/90 text-sm font-medium"
          >
            <Plus className="w-4 h-4" /> Upload Document
          </button>
      </div>

      {error && <div className="p-4 bg-red-50 text-red-700 rounded-lg text-sm">{error}</div>}

      <div className="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <table className="w-full text-left text-sm text-gray-600">
          <thead className="bg-gray-50 text-xs uppercase text-gray-700 border-b">
            <tr>
              <th className="px-6 py-3">Document</th>
              <th className="px-6 py-3">Type</th>
              <th className="px-6 py-3">Uploaded By</th>
              <th className="px-6 py-3">Expiry Date</th>
              <th className="px-6 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            {documents.length === 0 ? (
              <tr><td colSpan="5" className="px-6 py-8 text-center text-gray-500">No documents uploaded yet.</td></tr>
            ) : (
              documents.map(doc => (
                <tr key={doc.id} className="border-b last:border-0 hover:bg-gray-50">
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-3">
                      <div className="w-8 h-8 rounded bg-blue-50 text-blue-600 flex items-center justify-center">
                        <FileText className="w-4 h-4" />
                      </div>
                      <div>
                        <p className="font-medium text-gray-900">{doc.original_file_name}</p>
                        <p className="text-xs text-gray-500">{(doc.file_size / 1024).toFixed(1)} KB • {doc.mime_type}</p>
                      </div>
                    </div>
                  </td>
                  <td className="px-6 py-4">{doc.document_type}</td>
                  <td className="px-6 py-4">
                    <p className="text-gray-900">{doc.uploaded_by?.name || 'System'}</p>
                    <p className="text-xs text-gray-500">{new Date(doc.created_at).toLocaleDateString()}</p>
                  </td>
                  <td className="px-6 py-4">
                    {doc.expiry_date ? (
                      <span className={`px-2 py-1 rounded text-xs font-medium ${new Date(doc.expiry_date) < new Date() ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'}`}>
                        {new Date(doc.expiry_date).toLocaleDateString()}
                      </span>
                    ) : '-'}
                  </td>
                  <td className="px-6 py-4 text-right space-x-2">
                      <button onClick={() => setPreviewDoc(doc)} className="text-blue-600 hover:text-blue-800 p-1" title="Preview">
                        <Eye className="w-4 h-4" />
                      </button>
                      <button onClick={() => useDocumentStore.getState().downloadDocument(doc.id)} className="text-green-600 hover:text-green-800 p-1" title="Download">
                        <Download className="w-4 h-4" />
                      </button>
                      <button onClick={() => setEditingDoc(doc)} className="text-gray-600 hover:text-gray-800 p-1" title="Edit/Replace">
                        <Edit2 className="w-4 h-4" />
                      </button>
                      <button onClick={() => handleDelete(doc.id, doc.original_file_name)} className="text-red-600 hover:text-red-800 p-1" title="Delete">
                        <Trash2 className="w-4 h-4" />
                      </button>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {(uploadModalOpen || editingDoc) && (
        <DocumentUploadModal
          employeeId={employeeId}
          document={editingDoc}
          onClose={() => {
            setUploadModalOpen(false);
            setEditingDoc(null);
          }}
        />
      )}

      {previewDoc && (
        <DocumentPreviewModal
          document={previewDoc}
          onClose={() => setPreviewDoc(null)}
        />
      )}
    </div>
  );
}
