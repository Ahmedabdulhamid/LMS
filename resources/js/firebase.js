// Import the functions you need from the SDKs you need
import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, isSupported, onMessage } from 'firebase/messaging';
// TODO: Add SDKs for Firebase products that you want to use
// https://firebase.google.com/docs/web/setup#available-libraries

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
  apiKey: "AIzaSyATw3cybeCiNWsUjzWfMC_RodwocNTrLTA",
  authDomain: "learning-plateform-b44b3.firebaseapp.com",
  projectId: "learning-plateform-b44b3",
  storageBucket: "learning-plateform-b44b3.firebasestorage.app",
  messagingSenderId: "1078411211894",
  appId: "1:1078411211894:web:3b6d48b268f4463ed4db57",
  measurementId: "G-TLJBCT0LFG"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);

const registerDeviceToken = async () => {
  const isAuthenticatedStudent = document.querySelector(
    'meta[name="student-authenticated"][content="true"]',
  );

  if (!isAuthenticatedStudent) {
    return;
  }

  if (!('serviceWorker' in navigator) || !(await isSupported())) {
    return;
  }

  const permission = await Notification.requestPermission();
  if (permission !== 'granted') {
    return;
  }

  const serviceWorkerRegistration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
  const messaging = getMessaging(app);
  onMessage(messaging, ({ notification, data }) => {
    if (!notification?.title) {
      return;
    }

    new Notification(notification.title, {
      body: notification.body,
      data,
    });
  });

  const currentToken = await getToken(messaging, {
    vapidKey: 'BG47Ud0dVW9c1hwModylCvy7F3X39dyOZC3HJrZo1ON9zzULQD8G9g6qQchJ3hp0tV-Mvsu_28y6axd_jrDnoVs',
    serviceWorkerRegistration,
  });

  if (!currentToken) {
    return;
  }

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
  if (!csrfToken) {
    return;
  }

  const response = await fetch('/api/student/device-tokens', {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
    },
    body: JSON.stringify({ token: currentToken, platform: 'web' }),
  });

  if (!response.ok && response.status !== 401) {
    throw new Error(`Unable to register the Firebase device token (${response.status}).`);
  }

  if (response.ok) {
    console.info('Firebase device token registered.');
  }
};

registerDeviceToken().catch((error) => {
  console.error('Firebase messaging setup failed.', error);
});
