// app/tabs/_layout.tsx

import { Ionicons } from '@expo/vector-icons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Tabs, useFocusEffect, useSegments } from 'expo-router';
import React, { useEffect, useRef, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { IP_ADDRESS } from '../utils/constants';

const API_BASE = `${IP_ADDRESS}/aquabill-api`;

export default function TabLayout() {
  const segments        = useSegments();
  const isNotifications = segments.includes('notifications');

  const [total, setTotal] = useState(0);
  const [read,  setRead]  = useState<number | null>(null);
  const firstLoad         = useRef(true);

  // derive a storage key that’s unique per user
  const [storageKey, setStorageKey] = useState<string>('');
  useEffect(() => {
    AsyncStorage.getItem('userId').then(uid => {
      if (uid) setStorageKey(`@notifications_read_count_${uid}`);
    });
  }, []);

  // load stored read count once (per-user key)
  useEffect(() => {
    if (!storageKey) return;
    AsyncStorage.getItem(storageKey).then(stored => {
      setRead(stored !== null ? Number(stored) : null);
    });
  }, [storageKey]);

  // fetch total notifications+announcements
  const fetchTotal = async () => {
    try {
      const userType = await AsyncStorage.getItem('userType');
      const userId   = await AsyncStorage.getItem('userId');
      if (!userType || !userId) return;

      const resp = await fetch(
        `${API_BASE}/notifications.php?type=${userType}&user_id=${userId}`
      );
      const json = await resp.json();
      if (json.status === 'success' && Array.isArray(json.data)) {
        const n = json.data.length;
        setTotal(n);

        // on first ever load (and only if read===null), keep badge intact
        if (read === null && firstLoad.current) {
          firstLoad.current = false;
        }
      }
    } catch {
      // ignore
    }
  };

  // poll every 2s
  useEffect(() => {
    fetchTotal();
    const iv = setInterval(fetchTotal, 2000);
    return () => clearInterval(iv);
  }, [read, storageKey]);

  // refresh on focus
  useFocusEffect(
    React.useCallback(() => {
      fetchTotal();
    }, [storageKey])
  );

  // compute badge = either total (if never read) or total-read
  const badge = read === null ? total : Math.max(0, total - read);

  // mark all read
  const markAllRead = () => {
    if (!storageKey) return;
    setRead(total);
    AsyncStorage.setItem(storageKey, total.toString());
  };

  // clear on entering Notifications
  useEffect(() => {
    if (isNotifications) markAllRead();
  }, [isNotifications, total, storageKey]);

  return (
    <View style={styles.root}>
      <View style={styles.topBar} />
      <View style={styles.tabsContainer}>
        <Tabs
          screenOptions={{
            tabBarShowLabel:     false,
            tabBarActiveTintColor:   '#fff',
            tabBarInactiveTintColor: 'rgba(255,255,255,0.6)',
            tabBarStyle: styles.tabBar,
            headerShown:  false,
          }}
        >
          <Tabs.Screen
            name="home"
            options={{
              tabBarIcon: ({ color }) =>
                <Ionicons name="home" size={30} color={color} />
            }}
          />
          <Tabs.Screen
            name="residents"
            options={{
              tabBarIcon: ({ color }) =>
                <Ionicons name="people" size={30} color={color} />
            }}
          />
          <Tabs.Screen
            name="qrscanner"
            options={{
              tabBarIcon: ({ color }) =>
                <Ionicons name="qr-code" size={30} color={color} />
            }}
          />
          <Tabs.Screen
            name="history"
            options={{
              tabBarIcon: ({ color }) =>
                <Ionicons name="time" size={30} color={color} />
            }}
          />
          <Tabs.Screen
            name="notifications"
            options={{
              tabBarIcon: ({ color }) => (
                <View style={styles.iconWithBadge}>
                  <Ionicons name="notifications" size={30} color={color} />
                  {badge > 0 && (
                    <View style={styles.badge}>
                      <Text style={styles.badgeText}>
                        {badge > 9 ? '9+' : badge}
                      </Text>
                    </View>
                  )}
                </View>
              )
            }}
          />
        </Tabs>
      </View>
      <View style={styles.bottomBar} />
    </View>
  );
}

const styles = StyleSheet.create({
  root:          { flex: 1 },
  topBar:        { height: 25, backgroundColor: 'black' },
  tabsContainer: { flex: 1 },
  tabBar: {
    position:      'absolute',
    bottom:        20,
    left:          15,
    right:         15,
    backgroundColor: '#0077B6',
    borderRadius:  30,
    height:        60,
    paddingBottom: 3,
    paddingTop:    8,
    shadowColor:   '#000',
    shadowOffset:  { width: 0, height: 4 },
    shadowOpacity: 0.15,
    shadowRadius:  4,
    elevation:     8,
  },
  iconWithBadge: { width: 30, height: 30 },
  badge: {
    position:      'absolute',
    top:           -4,
    right:         -6,
    backgroundColor: 'red',
    borderRadius:  8,
    minWidth:      16,
    height:        16,
    paddingHorizontal: 2,
    justifyContent:    'center',
    alignItems:        'center',
  },
  badgeText: {
    color:      'white',
    fontSize:   10,
    fontWeight: 'bold',
  },
  bottomBar: { height:45, backgroundColor:'black' },
});
