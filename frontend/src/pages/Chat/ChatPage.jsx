import React, { useEffect, useState, useRef, useCallback } from 'react';
import { useChatStore } from '../../store/chatStore';
import { useAuthStore } from '../../store/authStore';
import { useEmployeeStore } from '../../store/employeeStore';
import { getEcho } from '../../bootstrap';
import api from '../../services/api';
import {
  Send, Paperclip, X, Reply, Search, Plus, Users, CheckCheck, Check,
  Image as ImageIcon, FileText, ArrowLeft, MessageSquare, Hash, MapPin
} from 'lucide-react';

export default function ChatPage() {
  const { user } = useAuthStore();
  const {
    channels, activeChannel, messages, hasMoreMessages, loadingChannels,
    loadingMessages, typingUsers,
    fetchChannels, setActiveChannel, loadMessages, addMessage,
    updateReadReceipts, setTypingUser, clearTypingUser,
    sendMessage, sendTyping, createChannel,
  } = useChatStore();

  const { employees, fetchEmployees } = useEmployeeStore();

  const [messageText, setMessageText] = useState('');
  const [replyTo, setReplyTo] = useState(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [showNewChannel, setShowNewChannel] = useState(false);
  const [showMembers, setShowMembers] = useState(false);
  const [channelMembers, setChannelMembers] = useState([]);
  const [newChannelName, setNewChannelName] = useState('');
  const [selectedMembers, setSelectedMembers] = useState([]);
  const [channelSearch, setChannelSearch] = useState('');
  const [isDragOver, setIsDragOver] = useState(false);

  const messagesEndRef = useRef(null);
  const messagesContainerRef = useRef(null);
  const fileInputRef = useRef(null);
  const typingTimeoutRef = useRef(null);
  const [autoScroll, setAutoScroll] = useState(true);

  useEffect(() => {
    fetchChannels();
    fetchEmployees({ per_page: 1000 });
  }, []);

  useEffect(() => {
    if (!activeChannel) return;

    const echo = getEcho();
    const channel = echo.private(`chat.${activeChannel.id}`);

    channel.listen('.message.sent', (event) => {
      addMessage({
        ...event,
        is_mine: event.user_id === user?.id,
      });
    });

    channel.listen('.user.typing', (event) => {
      if (event.user_id !== user?.id) {
        setTypingUser(activeChannel.id, event.user_id, event.user_name);
      }
    });

    channel.listen('.message.read', (event) => {
      updateReadReceipts(event.message_id, event.user_id, event.user_name, event.read_at);
    });

    return () => {
      channel.stopListening('.message.sent');
      channel.stopListening('.user.typing');
      channel.stopListening('.message.read');
    };
  }, [activeChannel?.id]);

  useEffect(() => {
    if (autoScroll) {
      messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }
  }, [messages]);

  const handleScroll = useCallback(() => {
    const container = messagesContainerRef.current;
    if (!container) return;
    const isAtBottom = container.scrollHeight - container.scrollTop - container.clientHeight < 100;
    setAutoScroll(isAtBottom);

    if (container.scrollTop < 50 && hasMoreMessages && activeChannel && !loadingMessages) {
      const oldestId = messages[0]?.id;
      if (oldestId) loadMessages(activeChannel.id, oldestId);
    }
  }, [hasMoreMessages, activeChannel, messages, loadingMessages]);

  const handleSend = async () => {
    if (!messageText.trim() && !fileInputRef.current?.files?.length) return;
    if (!activeChannel) return;

    const payload = new FormData();
    if (messageText.trim()) payload.append('message', messageText.trim());
    if (replyTo) payload.append('reply_to_id', replyTo.id);
    if (fileInputRef.current?.files?.length) {
      payload.append('file', fileInputRef.current.files[0]);
    }

    setMessageText('');
    setReplyTo(null);
    if (fileInputRef.current) fileInputRef.current.value = '';
    setAutoScroll(true);

    try {
      await sendMessage(activeChannel.id, payload);
    } catch (err) {
      console.error('Failed to send message', err);
    }
  };

  const handleKeyDown = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      handleSend();
    }
  };

  const handleTyping = () => {
    if (!activeChannel) return;
    if (typingTimeoutRef.current) return;
    sendTyping(activeChannel.id);
    typingTimeoutRef.current = setTimeout(() => {
      typingTimeoutRef.current = null;
    }, 2000);
  };

  const handleFileDrop = (e) => {
    e.preventDefault();
    setIsDragOver(false);
    if (e.dataTransfer.files.length) {
      if (fileInputRef.current) {
        fileInputRef.current.files = e.dataTransfer.files;
        setMessageText(prev => prev || '');
      }
    }
  };

  const handleChannelSelect = (channel) => {
    setActiveChannel(channel);
  };

  const handleCreateChannel = async () => {
    if (!newChannelName.trim() || selectedMembers.length === 0) return;
    try {
      const channel = await createChannel({
        name: newChannelName,
        type: 'topic',
        member_ids: selectedMembers,
      });
      setShowNewChannel(false);
      setNewChannelName('');
      setSelectedMembers([]);
      setActiveChannel(channel);
    } catch (err) {
      console.error('Failed to create channel', err);
    }
  };

  const openMembers = async () => {
    if (!activeChannel) return;
    setShowMembers(true);
    try {
      const res = await api.get(`/chat/channels/${activeChannel.id}/members`);
      setChannelMembers(res.data?.members || []);
    } catch { setChannelMembers([]); }
  };

  const getTypingText = () => {
    const typing = typingUsers[activeChannel?.id] || {};
    const names = Object.values(typing).map(t => t.name);
    if (names.length === 0) return null;
    if (names.length === 1) return `${names[0]} is typing...`;
    if (names.length === 2) return `${names[0]} and ${names[1]} are typing...`;
    return `${names[0]} and ${names.length - 1} others are typing...`;
  };

  const formatTime = (dateStr) => {
    const d = new Date(dateStr);
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  };

  const formatDate = (dateStr) => {
    const d = new Date(dateStr);
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    if (d.toDateString() === today.toDateString()) return 'Today';
    if (d.toDateString() === yesterday.toDateString()) return 'Yesterday';
    return d.toLocaleDateString([], { month: 'short', day: 'numeric', year: 'numeric' });
  };

  const shouldShowDateSeparator = (msg, idx) => {
    if (idx === 0) return true;
    const prev = new Date(messages[idx - 1].created_at);
    const curr = new Date(msg.created_at);
    return prev.toDateString() !== curr.toDateString();
  };

  const filteredChannels = channels.filter(c =>
    c.name.toLowerCase().includes(channelSearch.toLowerCase())
  );

  const filteredEmployees = employees?.filter(e =>
    e.full_name?.toLowerCase().includes(channelSearch.toLowerCase()) ||
    e.name?.toLowerCase().includes(channelSearch.toLowerCase())
  ) || [];

  const getReadStatus = (msg) => {
    if (!msg.is_mine || !msg.reads) return null;
    const otherReads = msg.reads.filter(r => r.user_id !== user?.id);
    return otherReads.length > 0;
  };

  return (
    <div className="flex h-[calc(100vh-4rem)] bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      {/* Channel List Sidebar */}
      <div className={`${activeChannel ? 'hidden md:flex' : 'flex'} flex-col w-full md:w-80 lg:w-96 border-r border-gray-100`}>
        <div className="p-4 border-b border-gray-100">
          <div className="flex items-center justify-between mb-3">
            <h2 className="text-lg font-bold text-gray-900 flex items-center gap-2">
              <MessageSquare className="w-5 h-5 text-primary" />
              Chat
            </h2>
            <button onClick={() => setShowNewChannel(true)} className="p-2 text-gray-500 hover:text-primary hover:bg-primary/5 rounded-lg transition-colors" title="New Channel">
              <Plus className="w-5 h-5" />
            </button>
          </div>
          <div className="relative">
            <Search className="absolute left-3 top-2.5 w-4 h-4 text-gray-400" />
            <input type="text" placeholder="Search channels..." className="w-full pl-9 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary" value={channelSearch} onChange={(e) => setChannelSearch(e.target.value)} />
          </div>
        </div>

        <div className="flex-1 overflow-y-auto">
          {loadingChannels ? (
            <div className="flex justify-center py-8"><div className="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div></div>
          ) : filteredChannels.length === 0 ? (
            <div className="text-center py-8 text-gray-400 text-sm">No channels found</div>
          ) : (
            filteredChannels.map(channel => (
              <button key={channel.id} onClick={() => handleChannelSelect(channel)}
                className={`w-full text-left px-4 py-3 border-b border-gray-50 hover:bg-gray-50 transition-colors ${activeChannel?.id === channel.id ? 'bg-primary/5 border-l-2 border-l-primary' : ''}`}>
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center flex-shrink-0">
                    {channel.type === 'site' ? <MapPin className="w-5 h-5 text-primary" /> : <Hash className="w-5 h-5 text-primary" />}
                  </div>
                  <div className="flex-1 min-w-0">
                    <div className="flex justify-between items-center">
                      <span className="font-semibold text-sm text-gray-900 truncate">{channel.name}</span>
                      {channel.last_message && <span className="text-[10px] text-gray-400 flex-shrink-0 ml-2">{formatTime(channel.last_message.created_at)}</span>}
                    </div>
                    <div className="flex justify-between items-center mt-0.5">
                      <p className="text-xs text-gray-500 truncate">{channel.last_message?.message || 'No messages yet'}</p>
                      {channel.unread_count > 0 && (
                        <span className="ml-2 flex-shrink-0 min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-primary rounded-full flex items-center justify-center">
                          {channel.unread_count > 99 ? '99+' : channel.unread_count}
                        </span>
                      )}
                    </div>
                    <p className="text-[10px] text-gray-400 mt-0.5">{channel.members_count} members</p>
                  </div>
                </div>
              </button>
            ))
          )}
        </div>
      </div>

      {/* Chat Area */}
      <div className={`${activeChannel ? 'flex' : 'hidden md:flex'} flex-col flex-1 min-w-0`}>
        {activeChannel ? (
          <>
            {/* Chat Header */}
            <div className="px-4 py-3 border-b border-gray-100 flex items-center gap-3 bg-white">
              <button onClick={() => setActiveChannel(null)} className="md:hidden p-1 text-gray-500 hover:text-gray-700">
                <ArrowLeft className="w-5 h-5" />
              </button>
              <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center flex-shrink-0">
                {activeChannel.type === 'site' ? <MapPin className="w-5 h-5 text-primary" /> : <Hash className="w-5 h-5 text-primary" />}
              </div>
              <div className="flex-1 min-w-0">
                <h3 className="font-bold text-gray-900 truncate">{activeChannel.name}</h3>
                <p className="text-xs text-gray-500">{activeChannel.members_count} members{activeChannel.site ? ` \u00b7 ${activeChannel.site.name}` : ''}</p>
              </div>
              <button onClick={openMembers} className="p-2 text-gray-500 hover:text-primary hover:bg-primary/5 rounded-lg transition-colors" title="Members">
                <Users className="w-5 h-5" />
              </button>
            </div>

            {/* Messages Area */}
            <div ref={messagesContainerRef} onScroll={handleScroll}
              onDragOver={(e) => { e.preventDefault(); setIsDragOver(true); }}
              onDragLeave={() => setIsDragOver(false)}
              onDrop={handleFileDrop}
              className={`flex-1 overflow-y-auto px-4 py-4 space-y-1 ${isDragOver ? 'bg-primary/5' : ''}`}>

              {isDragOver && (
                <div className="absolute inset-0 bg-primary/10 border-2 border-dashed border-primary rounded-xl flex items-center justify-center z-10 pointer-events-none">
                  <div className="text-primary font-semibold">Drop file here to send</div>
                </div>
              )}

              {loadingMessages && messages.length === 0 && (
                <div className="flex justify-center py-8"><div className="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div></div>
              )}

              {!loadingMessages && messages.length === 0 && (
                <div className="flex flex-col items-center justify-center h-full text-gray-400">
                  <MessageSquare className="w-12 h-12 mb-3 text-gray-300" />
                  <p className="font-medium">No messages yet</p>
                  <p className="text-sm mt-1">Start the conversation!</p>
                </div>
              )}

              {hasMoreMessages && messages.length > 0 && (
                <div className="text-center py-2">
                  <button onClick={() => loadMessages(activeChannel.id, messages[0]?.id)} className="text-xs text-primary hover:text-primary/80 font-medium">
                    Load older messages
                  </button>
                </div>
              )}

              {messages.map((msg, idx) => (
                <React.Fragment key={msg.id}>
                  {shouldShowDateSeparator(msg, idx) && (
                    <div className="flex justify-center my-4">
                      <span className="px-3 py-1 bg-gray-100 text-gray-500 text-xs rounded-full font-medium">{formatDate(msg.created_at)}</span>
                    </div>
                  )}
                  <div className={`flex ${msg.is_mine ? 'justify-end' : 'justify-start'} mb-1 group`}>
                    <div className={`max-w-[75%] ${msg.is_mine ? 'order-2' : ''}`}>
                      {!msg.is_mine && (
                        <p className="text-[10px] font-semibold text-gray-500 ml-1 mb-0.5">{msg.user?.name}</p>
                      )}
                      {msg.reply_to && (
                        <div className={`px-3 py-1.5 rounded-t-lg text-xs border-l-2 ${msg.is_mine ? 'bg-primary/10 border-primary/40' : 'bg-gray-100 border-gray-400'}`}>
                          <p className="font-semibold text-gray-600">{msg.reply_to.user?.name}</p>
                          <p className="text-gray-500 truncate">{msg.reply_to.message}</p>
                        </div>
                      )}
                      <div className={`px-3 py-2 rounded-2xl ${
                        msg.is_mine
                          ? 'bg-primary text-white rounded-br-md'
                          : 'bg-gray-100 text-gray-900 rounded-bl-md'
                      } ${msg.reply_to ? 'rounded-t-none' : ''}`}>
                        {msg.type === 'image' && msg.file_path && (
                          <img src={`${import.meta.env.VITE_API_URL || 'http://localhost:8000'}/storage/${msg.file_path}`} alt="Shared" className="rounded-lg max-w-full mb-1 max-h-60 object-cover" />
                        )}
                        {msg.type === 'file' && msg.file_path && (
                            <a href={`${import.meta.env.VITE_API_URL || 'http://localhost:8000'}/storage/${msg.file_path}`} target="_blank" rel="noopener noreferrer"
                            className={`flex items-center gap-2 text-sm underline ${msg.is_mine ? 'text-white/90' : 'text-primary'}`}>
                            <FileText className="w-4 h-4" />
                            {msg.file_name || 'Download'}
                          </a>
                        )}
                        {msg.message && (
                          <p className="text-sm whitespace-pre-wrap break-words">{msg.message}</p>
                        )}
                        <div className={`flex items-center justify-end gap-1 mt-0.5 ${msg.is_mine ? 'text-white/70' : 'text-gray-400'}`}>
                          <span className="text-[10px]">{formatTime(msg.created_at)}</span>
                          {msg.is_mine && (
                            getReadStatus(msg) ? <CheckCheck className="w-3.5 h-3.5 text-blue-200" /> : <Check className="w-3.5 h-3.5" />
                          )}
                        </div>
                      </div>
                      <button onClick={() => setReplyTo(msg)}
                        className="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-gray-400 hover:text-gray-600 mt-0.5">
                        <Reply className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>
                </React.Fragment>
              ))}

              {getTypingText() && (
                <div className="flex items-center gap-2 px-2 py-1 text-xs text-gray-400">
                  <div className="flex gap-0.5">
                    <span className="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '0ms' }}></span>
                    <span className="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '150ms' }}></span>
                    <span className="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '300ms' }}></span>
                  </div>
                  {getTypingText()}
                </div>
              )}

              <div ref={messagesEndRef} />
            </div>

            {/* Reply Preview */}
            {replyTo && (
              <div className="px-4 py-2 bg-gray-50 border-t border-gray-100 flex items-center gap-2">
                <Reply className="w-4 h-4 text-primary flex-shrink-0" />
                <div className="flex-1 min-w-0">
                  <p className="text-xs font-semibold text-primary">{replyTo.user?.name}</p>
                  <p className="text-xs text-gray-500 truncate">{replyTo.message}</p>
                </div>
                <button onClick={() => setReplyTo(null)} className="text-gray-400 hover:text-gray-600"><X className="w-4 h-4" /></button>
              </div>
            )}

            {/* Message Input */}
            <div className="px-4 py-3 border-t border-gray-100 bg-white">
              <div className="flex items-end gap-2">
                <button onClick={() => fileInputRef.current?.click()} className="p-2 text-gray-500 hover:text-primary hover:bg-primary/5 rounded-lg transition-colors flex-shrink-0">
                  <Paperclip className="w-5 h-5" />
                </button>
                <input type="file" ref={fileInputRef} className="hidden" onChange={() => {}} accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt" />
                <div className="flex-1 min-w-0">
                  <textarea
                    rows={1}
                    placeholder="Type a message..."
                    className="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm resize-none focus:outline-none focus:ring-2 focus:ring-primary max-h-32"
                    value={messageText}
                    onChange={(e) => { setMessageText(e.target.value); handleTyping(); }}
                    onKeyDown={handleKeyDown}
                    style={{ minHeight: '42px' }}
                  />
                </div>
                <button onClick={handleSend}
                  disabled={!messageText.trim() && !fileInputRef.current?.files?.length}
                  className="p-2.5 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0">
                  <Send className="w-5 h-5" />
                </button>
              </div>
            </div>
          </>
        ) : (
          <div className="flex-1 flex flex-col items-center justify-center text-gray-400">
            <MessageSquare className="w-16 h-16 mb-4 text-gray-300" />
            <h3 className="text-lg font-bold text-gray-600">Select a Channel</h3>
            <p className="text-sm mt-1">Choose a channel from the sidebar to start chatting</p>
          </div>
        )}
      </div>

      {/* New Channel Modal */}
      {showNewChannel && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-2xl max-w-md w-full shadow-2xl">
            <div className="flex justify-between items-center px-6 py-4 border-b border-gray-100">
              <h2 className="text-lg font-bold text-gray-900">New Channel</h2>
              <button onClick={() => setShowNewChannel(false)} className="text-gray-400 hover:text-gray-600"><X className="w-5 h-5" /></button>
            </div>
            <div className="p-6 space-y-4">
              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Channel Name</label>
                <input type="text" value={newChannelName} onChange={(e) => setNewChannelName(e.target.value)} placeholder="e.g. Safety Updates" className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary" />
              </div>
              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Add Members</label>
                <div className="max-h-48 overflow-y-auto border border-gray-200 rounded-lg">
                  {filteredEmployees.map(emp => (
                    <label key={emp.id} className="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-50 last:border-0">
                      <input type="checkbox" checked={selectedMembers.includes(emp.id)}
                        onChange={(e) => setSelectedMembers(prev => e.target.checked ? [...prev, emp.id] : prev.filter(id => id !== emp.id))}
                        className="w-4 h-4 rounded border-gray-300" />
                      <span className="text-sm text-gray-700">{emp.full_name || emp.name}</span>
                    </label>
                  ))}
                </div>
              </div>
            </div>
            <div className="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
              <button onClick={() => setShowNewChannel(false)} className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">Cancel</button>
              <button onClick={handleCreateChannel} disabled={!newChannelName.trim() || selectedMembers.length === 0} className="px-5 py-2 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary/90 disabled:opacity-50">Create</button>
            </div>
          </div>
        </div>
      )}

      {/* Members Sidebar */}
      {showMembers && (
        <div className="fixed inset-0 bg-black/50 flex justify-end z-50">
          <div className="w-80 bg-white h-full shadow-2xl overflow-y-auto">
            <div className="p-4 border-b border-gray-100 flex items-center justify-between">
              <h3 className="font-bold text-gray-900">Members ({channelMembers.length})</h3>
              <button onClick={() => setShowMembers(false)} className="text-gray-400 hover:text-gray-600"><X className="w-5 h-5" /></button>
            </div>
            <div className="p-2">
              {channelMembers.map(member => (
                <div key={member.id} className="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50">
                  <div className="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center text-sm font-bold">
                    {(member.name || '?').substring(0, 2).toUpperCase()}
                  </div>
                  <span className="text-sm font-medium text-gray-700">{member.name}</span>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
