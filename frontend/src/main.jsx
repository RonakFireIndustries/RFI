import React from 'react'
import ReactDOM from 'react-dom/client'
import App from './App.jsx'
import './index.css'

// Redirect non-www to www to match backend CORS policy
if (
  window.location.hostname === 'rfi.ronakfire.com' &&
  !window.location.hostname.startsWith('www.')
) {
  window.location.replace(
    window.location.protocol + '//www.' + window.location.hostname + window.location.pathname + window.location.search
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>,
)
