import React from 'react';
import { X, Download } from 'lucide-react';
import { useDocumentStore } from '../../../store/documentStore';

export default function DocumentPreviewModal({ document, onClose }) {
  const downloadDocument = useDocumentStore(state => state.downloadDocument);
  const previewUrl = useDocumentStore.getState().previewDocument(document.id);

  const isImage = document.mime_type?.startsWith('image/');
  const isPdf = document.mime_type === 'application/pdf';

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4">
      <div className="w-full max-w-4xl h-[85vh] rounded-lg bg-white shadow-xl flex flex-col overflow-hidden">
        <div className="flex items-center justify-between border-b border-gray-100 px-5 py-4 bg-gray-50">
          <div>
            <h2 className="text-lg font-semibold text-gray-900">{document.original_file_name}</h2>
            <p className="text-xs text-gray-500">{document.document_type} • {(document.file_size / 1024 / 1024).toFixed(2)} MB</p>
          </div>
          <div className="flex gap-2">
            <button onClick={() => downloadDocument(document.id)} className="rounded-md p-2 text-blue-600 hover:bg-blue-50" title="Download">
              <Download className="h-5 w-5" />
            </button>
            <button onClick={onClose} className="rounded-md p-2 text-gray-500 hover:bg-gray-200" title="Close">
              <X className="h-5 w-5" />
            </button>
          </div>
        </div>

        <div className="flex-1 bg-gray-100 p-4 flex items-center justify-center overflow-auto relative">
          {isImage ? (
            <img src={previewUrl} alt={document.original_file_name} className="max-w-full max-h-full object-contain shadow-sm bg-white" />
          ) : isPdf ? (
            <iframe src={previewUrl} className="w-full h-full bg-white shadow-sm border-0" title={document.original_file_name} />
          ) : (
            <div className="text-center text-gray-500">
              <p>Preview not available for this file type.</p>
              <button onClick={() => downloadDocument(document.id)} className="mt-4 px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">Download File</button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
