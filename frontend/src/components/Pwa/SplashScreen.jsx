import { useState, useEffect } from 'react';

export default function SplashScreen() {
  const [visible, setVisible] = useState(true);

  useEffect(() => {
    const timer = setTimeout(() => setVisible(false), 1200);
    return () => clearTimeout(timer);
  }, []);

  return (
    <div className={`pwa-splash${visible ? '' : ' hidden'}`}>
      <img src="/logo.png" alt="RFI" />
      <p>RFI Management Suite</p>
      <div className="loader" />
    </div>
  );
}
