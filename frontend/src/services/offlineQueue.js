import { openDB } from 'idb';

const DB_NAME = 'rfi-offline-db';
const DB_VERSION = 1;
const STORE_NAME = 'sync-queue';

export const initDB = async () => {
  return openDB(DB_NAME, DB_VERSION, {
    upgrade(db) {
      if (!db.objectStoreNames.contains(STORE_NAME)) {
        const store = db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
        store.createIndex('timestamp', 'timestamp');
      }
    },
  });
};

export const enqueueRequest = async (requestData) => {
  const db = await initDB();
  const tx = db.transaction(STORE_NAME, 'readwrite');
  await tx.store.add({
    url: requestData.url,
    method: requestData.method,
    headers: requestData.headers,
    body: requestData.body,
    timestamp: Date.now(),
  });
  await tx.done;

  if ('serviceWorker' in navigator && 'SyncManager' in window) {
    try {
      const registration = await navigator.serviceWorker.ready;
      await registration.sync.register('sync-offline-requests');
    } catch (err) {
      console.warn('Background sync registration failed:', err);
    }
  }
};

export const getQueue = async () => {
  const db = await initDB();
  return db.getAll(STORE_NAME);
};

export const removeFromQueue = async (id) => {
  const db = await initDB();
  const tx = db.transaction(STORE_NAME, 'readwrite');
  await tx.store.delete(id);
  await tx.done;
};
