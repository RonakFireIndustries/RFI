import { useState } from 'react';
import { Send, Trash2, MessageSquare, Clock } from 'lucide-react';
import api from '../../services/api';

export default function NotesSection({ notableType, notableId, initialNotes = [] }) {
  const [notes, setNotes] = useState(initialNotes);
  const [newNote, setNewNote] = useState('');
  const [loading, setLoading] = useState(false);

  const handleAddNote = async (e) => {
    e.preventDefault();
    if (!newNote.trim()) return;

    setLoading(true);
    try {
      const res = await api.post('/notes', {
        note: newNote,
        notable_type: notableType,
        notable_id: notableId
      });
      setNotes([res.data, ...notes]);
      setNewNote('');
    } catch (error) {
      console.error('Error adding note:', error);
      alert('Failed to add note');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Delete this note?')) return;
    
    try {
      await api.delete(`/notes/${id}`);
      setNotes(notes.filter(n => n.id !== id));
    } catch (error) {
      console.error('Error deleting note:', error);
      alert('Failed to delete note');
    }
  };

  return (
    <div className="space-y-6">
      <div className="bg-gray-50 border border-gray-200 rounded-xl p-4">
        <form onSubmit={handleAddNote} className="relative">
          <textarea
            value={newNote}
            onChange={(e) => setNewNote(e.target.value)}
            placeholder="Type a new internal note..."
            className="w-full px-4 py-3 pb-12 border border-gray-200 rounded-lg text-sm focus:ring-[#1a56db] focus:border-[#1a56db] bg-white resize-none"
            rows="3"
            disabled={loading}
          ></textarea>
          <div className="absolute bottom-3 right-3">
            <button 
              type="submit" 
              disabled={loading || !newNote.trim()}
              className="px-4 py-1.5 bg-[#1a56db] text-white text-sm font-bold rounded hover:bg-[#1e40af] disabled:opacity-50 transition-colors flex items-center"
            >
              {loading ? 'Posting...' : <><Send className="w-3 h-3 mr-1.5" /> Post Note</>}
            </button>
          </div>
        </form>
      </div>

      <div className="space-y-4">
        <h3 className="text-sm font-bold text-gray-900 flex items-center">
          <MessageSquare className="w-4 h-4 mr-2 text-gray-400" /> Note History
        </h3>
        
        {notes.length === 0 ? (
          <p className="text-sm text-gray-500 py-4 italic">No notes added yet.</p>
        ) : (
          <div className="space-y-4 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-300 before:to-transparent">
            {notes.map(note => (
              <div key={note.id} className="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                <div className="flex items-center justify-center w-10 h-10 rounded-full border border-white bg-slate-200 text-slate-500 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2">
                   {note.creator?.name ? note.creator.name.charAt(0).toUpperCase() : 'U'}
                </div>
                
                <div className="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded border border-slate-200 bg-white shadow-sm">
                  <div className="flex items-center justify-between mb-2">
                    <span className="font-bold text-slate-900 text-sm">{note.creator?.name || 'Unknown User'}</span>
                    <div className="flex items-center space-x-2">
                      <span className="text-xs text-slate-500 flex items-center">
                        <Clock className="w-3 h-3 mr-1" />
                        {new Date(note.created_at).toLocaleString([], { dateStyle: 'short', timeStyle: 'short' })}
                      </span>
                      <button 
                        onClick={() => handleDelete(note.id)}
                        className="text-gray-400 hover:text-red-600 transition-colors"
                        title="Delete note"
                      >
                        <Trash2 className="w-3 h-3" />
                      </button>
                    </div>
                  </div>
                  <div className="text-sm text-slate-700 whitespace-pre-wrap">{note.note}</div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
