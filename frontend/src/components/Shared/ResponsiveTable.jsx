import React from 'react';

export default function ResponsiveTable({ children, className = '', ...props }) {
  return (
    <div className={`table-responsive ${className}`} {...props}>
      {children}
    </div>
  );
}
