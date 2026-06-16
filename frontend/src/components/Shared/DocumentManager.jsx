import { useState } from 'react';
import { Upload, Download, Trash2, FileText, Loader2 } from 'lucide-react';
import api from '../../services/api';

export default function DocumentManager({ documentableType, documentableId, initialDocuments = [] }) {
  const [documents, setDocuments] = useState(initialDocuments);
  const [uploading, setUploading] = useState(false);

  const handleUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);
    formData.append('documentable_type', documentableType);
    formData.append('documentable_id', documentableId);

    setUploading(true);
    try {
      const res = await api.post('/documents', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      setDocuments([...documents, res.data]);
    } catch (error) {
      console.error('Error uploading document:', error);
      alert('Failed to upload document');
    } finally {
      setUploading(false);
      e.target.value = null; // Reset input
    }
  };

  const handleDownload = async (doc) => {
    try {
      const response = await api.get(`/documents/${doc.id}/download`, {
        responseType: 'blob',
      });
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', doc.file_name);
      document.body.appendChild(link);
      link.click();
      link.remove();
    } catch (error) {
      console.error('Error downloading document:', error);
      alert('Failed to download document');
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Are you sure you want to delete this document?')) return;
    
    try {
      await api.delete(`/documents/${id}`);
      setDocuments(documents.filter(d => d.id !== id));
    } catch (error) {
      console.error('Error deleting document:', error);
      alert('Failed to delete document');
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h3 className="text-lg font-bold text-gray-900">Documents</h3>
        <div>
          <input
            type="file"
            id="file-upload"
            className="hidden"
            onChange={handleUpload}
            disabled={uploading}
            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
          />
          <label
            htmlFor="file-upload"
            className={`cursor-pointer flex items-center px-4 py-2 bg-white border border-gray-200 text-[#1a56db] text-sm font-bold rounded-lg hover:bg-gray-50 shadow-sm transition-colors ${uploading ? 'opacity-50' : ''}`}
          >
            {uploading ? <Loader2 className="w-4 h-4 mr-2 animate-spin" /> : <Upload className="w-4 h-4 mr-2" />}
            Upload Document
          </label>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {documents.length === 0 ? (
          <div className="col-span-full py-8 text-center text-gray-500 bg-gray-50 border border-dashed border-gray-200 rounded-xl">
            <FileText className="w-8 h-8 text-gray-400 mx-auto mb-2" />
            <p>No documents uploaded yet.</p>
          </div>
        ) : (
          documents.map(doc => (
            <div key={doc.id} className="p-4 border border-gray-200 rounded-xl flex items-start justify-between bg-white hover:shadow-sm transition-shadow">
              <div className="flex items-start overflow-hidden mr-3">
                <div className="w-10 h-10 rounded bg-blue-50 flex items-center justify-center shrink-0 mr-3">
                  <FileText className="w-5 h-5 text-[#1a56db]" />
                </div>
                <div className="overflow-hidden">
                  <p className="text-sm font-bold text-gray-900 truncate" title={doc.file_name}>{doc.file_name}</p>
                  <p className="text-xs text-gray-500 uppercase">{doc.file_type} • {new Date(doc.created_at).toLocaleDateString()}</p>
                  <p className="text-xs text-gray-400 mt-1">By {doc.uploader?.name || 'Unknown'}</p>
                </div>
              </div>
              <div className="flex space-x-1 shrink-0">
                <button 
                  onClick={() => handleDownload(doc)}
                  className="p-1.5 text-gray-400 hover:text-[#1a56db] hover:bg-blue-50 rounded"
                  title="Download"
                >
                  <Download className="w-4 h-4" />
                </button>
                <button 
                  onClick={() => handleDelete(doc.id)}
                  className="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded"
                  title="Delete"
                >
                  <Trash2 className="w-4 h-4" />
                </button>
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  );
}
