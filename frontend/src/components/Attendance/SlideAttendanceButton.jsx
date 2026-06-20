import React, { useState, useRef, useCallback, useEffect } from 'react';
import { ChevronRight, Loader2, MapPinOff, Lock, CheckCircle, LogOut, LogIn } from 'lucide-react';

const STATES = {
  gps_loading: {
    label: 'Acquiring GPS...',
    disabled: true,
    icon: Loader2,
    bg: 'bg-gray-300',
    text: 'text-gray-500',
    sliderBg: 'bg-gray-400',
  },
  outside_radius: {
    label: 'Outside Radius',
    disabled: true,
    icon: MapPinOff,
    bg: 'bg-red-100',
    text: 'text-red-600',
    sliderBg: 'bg-red-400',
  },
  permission_denied: {
    label: 'Location Permission Denied',
    disabled: true,
    icon: Lock,
    bg: 'bg-red-100',
    text: 'text-red-600',
    sliderBg: 'bg-red-400',
  },
  gps_error: {
    label: 'GPS Unavailable',
    disabled: true,
    icon: MapPinOff,
    bg: 'bg-orange-100',
    text: 'text-orange-600',
    sliderBg: 'bg-orange-400',
  },
  ready_checkin: {
    label: 'Slide to Check In',
    disabled: false,
    icon: LogIn,
    bg: 'bg-green-50',
    text: 'text-green-700',
    sliderBg: 'bg-green-600',
  },
  checked_in: {
    label: 'Slide to Check Out',
    disabled: false,
    icon: LogOut,
    bg: 'bg-blue-50',
    text: 'text-blue-700',
    sliderBg: 'bg-blue-600',
  },
  checked_out: {
    label: 'Already Checked Out',
    disabled: true,
    icon: CheckCircle,
    bg: 'bg-gray-100',
    text: 'text-gray-500',
    sliderBg: 'bg-gray-400',
  },
  loading_action: {
    label: 'Processing...',
    disabled: true,
    icon: Loader2,
    bg: 'bg-gray-100',
    text: 'text-gray-500',
    sliderBg: 'bg-gray-400',
  },
};

export default function SlideAttendanceButton({
  status,
  onCheckIn,
  onCheckOut,
  isCheckedIn,
  isCheckedOut,
}) {
  const [sliding, setSliding] = useState(false);
  const [slidePercent, setSlidePercent] = useState(0);
  const [transitioning, setTransitioning] = useState(false);
  const containerRef = useRef(null);
  const startXRef = useRef(0);
  const currentXRef = useRef(0);
  const isDraggingRef = useRef(false);
  const maxTravelRef = useRef(0);

  const stateKey = transitioning
    ? 'loading_action'
    : status === 'gps_loading' ? 'gps_loading'
    : status === 'outside_radius' ? 'outside_radius'
    : status === 'permission_denied' ? 'permission_denied'
    : status === 'gps_error' ? 'gps_error'
    : isCheckedOut ? 'checked_out'
    : isCheckedIn ? 'checked_in'
    : 'ready_checkin';

  const state = STATES[stateKey];
  const Icon = state.icon;

  const handleStart = useCallback((clientX) => {
    if (state.disabled || transitioning) return;
    isDraggingRef.current = true;
    startXRef.current = clientX;
    setSliding(true);
  }, [state.disabled, transitioning]);

  const handleMove = useCallback((clientX) => {
    if (!isDraggingRef.current || !containerRef.current) return;
    currentXRef.current = clientX;
    const rect = containerRef.current.getBoundingClientRect();
    const maxTravel = rect.width - 56;
    maxTravelRef.current = maxTravel;
    const traveled = Math.max(0, Math.min(clientX - startXRef.current, maxTravel));
    const percent = (traveled / maxTravel) * 100;
    setSlidePercent(percent);
  }, []);

  const handleEnd = useCallback(() => {
    if (!isDraggingRef.current) return;
    isDraggingRef.current = false;
    if (slidePercent >= 85) {
      setTransitioning(true);
      const action = isCheckedIn ? onCheckOut : onCheckIn;
      if (action) {
        action().finally(() => {
          setTransitioning(false);
          setSlidePercent(0);
          setSliding(false);
        });
      }
    } else {
      setSlidePercent(0);
      setSliding(false);
    }
  }, [slidePercent, isCheckedIn, onCheckIn, onCheckOut]);

  const handleMouseDown = (e) => handleStart(e.clientX);
  const handleMouseMove = (e) => { if (isDraggingRef.current) handleMove(e.clientX); };
  const handleMouseUp = handleEnd;

  const handleTouchStart = (e) => handleStart(e.touches[0].clientX);
  const handleTouchMove = (e) => { if (isDraggingRef.current) handleMove(e.touches[0].clientX); };
  const handleTouchEnd = handleEnd;

  useEffect(() => {
    if (sliding) {
      document.addEventListener('mousemove', handleMouseMove);
      document.addEventListener('mouseup', handleMouseUp);
      document.addEventListener('touchmove', handleTouchMove);
      document.addEventListener('touchend', handleTouchEnd);
      return () => {
        document.removeEventListener('mousemove', handleMouseMove);
        document.removeEventListener('mouseup', handleMouseUp);
        document.removeEventListener('touchmove', handleTouchMove);
        document.removeEventListener('touchend', handleTouchEnd);
      };
    }
  }, [sliding, handleMouseMove, handleMouseUp, handleTouchMove, handleTouchEnd]);

  return (
    <div
      ref={containerRef}
      className={`relative w-full h-14 rounded-full overflow-hidden select-none touch-none border transition-colors ${state.bg} ${state.disabled ? 'opacity-70' : 'cursor-grab active:cursor-grabbing'}`}
      onMouseDown={handleMouseDown}
      onTouchStart={handleTouchStart}
    >
      <div className="absolute inset-0 flex items-center justify-center">
        <span className={`text-sm font-bold transition-opacity ${state.text} ${slidePercent > 30 ? 'opacity-0' : 'opacity-100'}`}>
          {state.label}
        </span>
      </div>

      <div
        className={`absolute top-1 left-1 h-12 w-12 rounded-full flex items-center justify-center shadow-md transition-shadow ${state.sliderBg} ${state.disabled ? '' : 'hover:shadow-lg'}`}
        style={{
          transform: sliding ? `translateX(${(slidePercent / 100) * maxTravelRef.current}px)` : 'translateX(0)',
          transition: sliding ? 'none' : 'transform 0.3s ease-out',
        }}
      >
        <Icon className={`w-5 h-5 text-white ${stateKey === 'loading_action' || stateKey === 'gps_loading' ? 'animate-spin' : ''}`} />
      </div>

      {!state.disabled && slidePercent > 30 && (
        <div className="absolute inset-0 flex items-center justify-center pointer-events-none">
          <ChevronRight className="w-5 h-5 text-gray-400 animate-pulse" />
        </div>
      )}
    </div>
  );
}
