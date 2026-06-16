import React from 'react';
import { Outlet } from 'react-router-dom';

export default function AuthLayout() {
  return (
    <div className="min-h-screen flex flex-col items-center justify-center bg-[#F8FAFC] py-8 px-4">
      <main className="flex-grow flex flex-col justify-center w-full max-w-md mx-auto">
        
        {/* Header */}
        <div className="flex items-center justify-center mb-12">
          <h1 className="text-2xl font-extrabold text-[#0B1B36] tracking-tight">
            Ronak Fire Industries
          </h1>
        </div>
        
        <div className="mb-8 text-left">
          <h2 className="text-3xl font-bold text-[#1A202C] mb-2">
            Welcome Back
          </h2>
          <p className="text-sm text-[#718096] leading-relaxed">
            Please enter your credentials to access the construction management portal.
          </p>
        </div>
        
        {/* Form Content */}
        <Outlet />
      </main>
      
      {/* Absolute Bottom Footer */}
      <footer className="w-full max-w-6xl mx-auto flex justify-between items-center pb-4 pt-8 text-xs text-[#A0AEC0]">
        <span>
          &copy; {new Date().getFullYear()} Ronak Fire Industries
        </span>
        <span>
          v1.0
        </span>
      </footer>
    </div>
  );
}
