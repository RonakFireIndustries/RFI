import { useState, useEffect, useRef, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Edit2, Save, X, Camera, Mic, MicOff, Trash2, Upload, Calendar, Building2, FileText, Play, Pause, Download, Image } from 'lucide-react';
import { siteVisitService } from '../../services/siteVisitService';
import { format } from 'date-fns';

export default function SiteVisitDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [visit, setVisit] = useState(null);
  const [loading, setLoading] = useState(true);
  const [editing, setEditing] = useState(false);
  const [form, setForm] = useState({});
  const [activeTab, setActiveTab] = useState('details');
  const fileInputRef = useRef(null);
  const voiceInputRef = useRef(null);

  // Voice recording state
  const [isRecording, setIsRecording] = useState(false);
  const [mediaRecorder, setMediaRecorder] = useState(null);
  const [recordingTime, setRecordingTime] = useState(0);
  const timerRef = useRef(null);

  useEffect(() => {
    loadVisit();
    return () => {
      if (timerRef.current) clearInterval(timerRef.current);
    };
  }, [id]);

  const loadVisit = async () => {
    try {
      const data = await siteVisitService.get(id);
      setVisit(data);
      setForm({
        visit_date: data.visit_date?.split('T')[0] || '',
        purpose: data.purpose || '',
        discussion_notes: data.discussion_notes || '',
        next_followup_date: data.next_followup_date || '',
      });
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleSave = async () => {
    try {
      const updated = await siteVisitService.update(id, form);
      setVisit(updated);
      setEditing(false);
    } catch (err) {
      console.error(err);
    }
  };

  const handlePhotoUpload = async (e) => {
    const files = Array.from(e.target.files);
    if (files.length === 0) return;
    const formData = new FormData();
    files.forEach(f => formData.append('photos[]', f));
    try {
      await siteVisitService.uploadPhotos(id, formData);
      loadVisit();
    } catch (err) {
      console.error(err);
    }
    e.target.value = '';
  };

  const handleDeletePhoto = async (photoId) => {
    if (!confirm('Delete this photo?')) return;
    try {
      await siteVisitService.deletePhoto(id, photoId);
      loadVisit();
    } catch (err) {
      console.error(err);
    }
  };

  const startRecording = async () => {
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      const recorder = new MediaRecorder(stream, { mimeType: 'audio/webm' });
      const chunks = [];
      recorder.ondataavailable = e => chunks.push(e.data);
      recorder.onstop = async () => {
        const blob = new Blob(chunks, { type: 'audio/webm' });
        const file = new File([blob], `voice-note-${Date.now()}.webm`, { type: 'audio/webm' });
        const formData = new FormData();
        formData.append('voice_notes[]', file);
        formData.append('durations[]', recordingTime);
        try {
          await siteVisitService.uploadVoiceNotes(id, formData);
          loadVisit();
        } catch (err) {
          console.error(err);
        }
        stream.getTracks().forEach(t => t.stop());
      };
      recorder.start();
      setMediaRecorder(recorder);
      setIsRecording(true);
      setRecordingTime(0);
      timerRef.current = setInterval(() => setRecordingTime(t => t + 1), 1000);
    } catch (err) {
      console.error('Could not start recording:', err);
      alert('Microphone access denied. Please allow microphone access to record voice notes.');
    }
  };

  const stopRecording = () => {
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
      mediaRecorder.stop();
    }
    setIsRecording(false);
    if (timerRef.current) clearInterval(timerRef.current);
  };

  const handleDeleteVoiceNote = async (noteId) => {
    if (!confirm('Delete this voice note?')) return;
    try {
      await siteVisitService.deleteVoiceNote(id, noteId);
      loadVisit();
    } catch (err) {
      console.error(err);
    }
  };

  const formatDuration = (seconds) => {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${m}:${s.toString().padStart(2, '0')}`;
  };

  if (loading) {
    return <div className="flex items-center justify-center h-64"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600" /></div>;
  }

  if (!visit) {
    return <div className="text-center py-12 text-gray-500">Site visit not found</div>;
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <button onClick={() => navigate('/dashboard/site-visits')} className="p-2 hover:bg-gray-100 rounded-lg"><ArrowLeft className="w-5 h-5" /></button>
          <div>
            <h1 className="text-2xl font-bold text-gray-900">{visit.building?.name || 'Site Visit'}</h1>
            <p className="text-sm text-gray-500">{format(new Date(visit.visit_date), 'dd MMM yyyy')} • {visit.user?.name || ''}</p>
          </div>
        </div>
        <div className="flex gap-2">
          {editing ? (
            <>
              <button onClick={handleSave} className="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg text-sm"><Save className="w-4 h-4" /> Save</button>
              <button onClick={() => setEditing(false)} className="flex items-center gap-2 px-4 py-2 border rounded-lg text-sm"><X className="w-4 h-4" /> Cancel</button>
            </>
          ) : (
            <button onClick={() => setEditing(true)} className="flex items-center gap-2 px-4 py-2 border rounded-lg text-sm"><Edit2 className="w-4 h-4" /> Edit</button>
          )}
        </div>
      </div>

      {/* Tabs */}
      <div className="flex border-b">
        {['details', 'photos', 'voice-notes'].map(tab => (
          <button key={tab} onClick={() => setActiveTab(tab)}
            className={`px-4 py-2 text-sm font-medium border-b-2 ${activeTab === tab ? 'border-red-600 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700'}`}>
            {tab === 'details' ? 'Details' : tab === 'photos' ? `Photos (${visit.photos?.length || 0})` : `Voice Notes (${visit.voice_notes?.length || 0})`}
          </button>
        ))}
      </div>

      {/* Details Tab */}
      {activeTab === 'details' && (
        <div className="bg-white rounded-lg border p-6">
          {editing ? (
            <div className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-color-white">Visit Date</label>
                  <input type="date" value={form.visit_date} onChange={e => setForm({ ...form, visit_date: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-color-white">Purpose</label>
                  <input type="text" value={form.purpose} onChange={e => setForm({ ...form, purpose: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g. Initial survey" />
                </div>
              </div>
              <div>
                <label className="block text-sm font-medium text-color-white">Discussion Notes</label>
                <textarea value={form.discussion_notes} onChange={e => setForm({ ...form, discussion_notes: e.target.value })} rows={6} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
              </div>
              <div>
                <label className="block text-sm font-medium text-color-white">Next Follow-up Date</label>
                <input type="date" value={form.next_followup_date} onChange={e => setForm({ ...form, next_followup_date: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
              </div>
            </div>
          ) : (
            <div className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div><p className="text-sm text-gray-500">Purpose</p><p className="text-sm text-gray-900 mt-1">{visit.purpose || '-'}</p></div>
                <div><p className="text-sm text-gray-500">Follow-up Date</p><p className="text-sm text-gray-900 mt-1">{visit.next_followup_date ? format(new Date(visit.next_followup_date), 'dd MMM yyyy') : '-'}</p></div>
              </div>
              {visit.discussion_notes && (
                <div>
                  <p className="text-sm text-gray-500 mb-1">Discussion Notes</p>
                  <div className="p-3 bg-gray-50 rounded-lg text-sm text-gray-900 whitespace-pre-wrap">{visit.discussion_notes}</div>
                </div>
              )}
            </div>
          )}
        </div>
      )}

      {/* Photos Tab */}
      {activeTab === 'photos' && (
        <div className="space-y-4">
          <div className="flex gap-2">
            <button onClick={() => fileInputRef.current?.click()} className="flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
              <Upload className="w-4 h-4" /> Upload Photos
            </button>
            <input ref={fileInputRef} type="file" accept="image/*" multiple onChange={handlePhotoUpload} className="hidden" />
          </div>
          {(visit.photos || []).length === 0 ? (
            <div className="text-center py-12 text-gray-500 bg-white border rounded-lg">
              <Camera className="w-12 h-12 mx-auto text-gray-300 mb-3" />
              <p>No photos uploaded yet</p>
            </div>
          ) : (
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
              {(visit.photos || []).map(photo => (
                <div key={photo.id} className="relative group bg-white border rounded-lg overflow-hidden">
                  <img src={photo.url || `/storage/${photo.file_path}`} alt={photo.file_name} className="w-full h-40 object-cover" />
                  <div className="p-2">
                    <p className="text-xs text-gray-500 truncate">{photo.file_name}</p>
                    <p className="text-xs text-gray-400">{(photo.file_size / 1024).toFixed(0)} KB</p>
                  </div>
                  <button onClick={() => handleDeletePhoto(photo.id)} className="absolute top-2 right-2 p-1 bg-red-600 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                    <Trash2 className="w-3 h-3" />
                  </button>
                </div>
              ))}
            </div>
          )}
        </div>
      )}

      {/* Voice Notes Tab */}
      {activeTab === 'voice-notes' && (
        <div className="space-y-4">
          <div className="flex items-center gap-3">
            {!isRecording ? (
              <button onClick={startRecording} className="flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                <Mic className="w-4 h-4" /> Start Recording
              </button>
            ) : (
              <button onClick={stopRecording} className="flex items-center gap-2 px-4 py-2 bg-red-700 text-white rounded-lg text-sm animate-pulse">
                <MicOff className="w-4 h-4" /> Stop ({formatDuration(recordingTime)})
              </button>
            )}
            <button onClick={() => voiceInputRef.current?.click()} className="flex items-center gap-2 px-4 py-2 border rounded-lg text-sm hover:bg-gray-50">
              <Upload className="w-4 h-4" /> Upload Audio File
            </button>
            <input ref={voiceInputRef} type="file" accept="audio/*" multiple onChange={async (e) => {
              const files = Array.from(e.target.files);
              if (files.length === 0) return;
              const formData = new FormData();
              files.forEach(f => formData.append('voice_notes[]', f));
              try {
                await siteVisitService.uploadVoiceNotes(id, formData);
                loadVisit();
              } catch (err) { console.error(err); }
              e.target.value = '';
            }} className="hidden" />
          </div>

          {(visit.voice_notes || []).length === 0 ? (
            <div className="text-center py-12 text-gray-500 bg-white border rounded-lg">
              <Mic className="w-12 h-12 mx-auto text-gray-300 mb-3" />
              <p>No voice notes yet</p>
            </div>
          ) : (
            <div className="space-y-3">
              {(visit.voice_notes || []).map(note => (
                <div key={note.id} className="flex items-center gap-4 p-4 bg-white border rounded-lg">
                  <div className="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                    <Mic className="w-5 h-5 text-purple-600" />
                  </div>
                  <div className="flex-1">
                    <p className="text-sm font-medium text-gray-900">{note.file_name}</p>
                    <p className="text-xs text-gray-500">
                      {note.duration_seconds ? formatDuration(note.duration_seconds) : 'Unknown duration'} • {(note.file_size / 1024).toFixed(0)} KB
                    </p>
                  </div>
                  <audio controls src={note.url || `/storage/${note.file_path}`} className="h-8 flex-1 max-w-md" />
                  <button onClick={() => handleDeleteVoiceNote(note.id)} className="text-red-500 hover:text-red-700">
                    <Trash2 className="w-4 h-4" />
                  </button>
                </div>
              ))}
            </div>
          )}
        </div>
      )}
    </div>
  );
}
