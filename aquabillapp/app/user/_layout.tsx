// app/tabs/user/layout.tsx

import { Ionicons } from '@expo/vector-icons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Tabs, useFocusEffect, useSegments } from 'expo-router';
import React, { useEffect, useRef, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { IP_ADDRESS } from '../utils/constants';

const API_BASE = `${IP_ADDRESS}/aquabill-api`;

export default function UserTabsLayout() {
  const segments        = useSegments();
  const isNotifications = segments.includes('notifications');

  const [total,     setTotal]     = useState(0);
  const [read,      setRead]      = useState<number | null>(null);
  const firstLoad    = useRef(true);
  const [storageKey, setStorageKey] = useState<string>('');

  // 1) Build per-user storage key once we know userId
  useEffect(() => {
    AsyncStorage.getItem('userId').then(uid => {
      if (uid) setStorageKey(`@notifications_read_count_${uid}`);
    });
  }, []);

  // 2) Load stored read count once
  useEffect(() => {
    if (!storageKey) return;
    AsyncStorage.getItem(storageKey).then(stored => {
      setRead(stored !== null ? Number(stored) : null);
    });
  }, [storageKey]);

  // 3) Fetch unified notifications+announcements count
  const fetchTotal = async () => {
    try {
      const userType = await AsyncStorage.getItem('userType');
      const userId   = await AsyncStorage.getItem('userId');
      if (!userType || !userId) return;

      const resp = await fetch(
        `${API_BASE}/notifications.php?type=${userType}&user_id=${userId}`
      );
      const json = await resp.json();
      const n = (json.status === 'success' && Array.isArray(json.data))
                ? json.data.length
                : 0;
      setTotal(n);

      // on first ever load, leave read===null so badge = total
      if (read === null && firstLoad.current) {
        firstLoad.current = false;
      }
    } catch {
      // ignore errors
    }
  };

  // 4) Poll every 2s
  useEffect(() => {
    fetchTotal();
    const iv = setInterval(fetchTotal, 2000);
    return () => clearInterval(iv);
  }, [read, storageKey]);

  // 5) Refresh on focus
  useFocusEffect(
    React.useCallback(() => {
      fetchTotal();
    }, [storageKey])
  );

  // 6) Compute badge = total (if read===null) or total-read
  const badge = read === null ? total : Math.max(0, total - read);

  // 7) Mark all as read
  const markAllRead = () => {
    if (!storageKey) return;
    setRead(total);
    AsyncStorage.setItem(storageKey, total.toString());
  };

  // 8) Clear badge when opening Notifications tab
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
            name="userhome"
            options={{
              tabBarIcon: ({ color }) =>
                <Ionicons name="home" size={30} color={color} />,
            }}
          />

          <Tabs.Screen
            name="payment"
            options={{
              tabBarIcon: ({ color }) =>
                <Ionicons name="card" size={30} color={color} />,
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
              ),
            }}
          />

          <Tabs.Screen
            name="history"
            options={{
              tabBarIcon: ({ color }) =>
                <Ionicons name="time" size={30} color={color} />,
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
