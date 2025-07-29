// app/(tabs)/notifications.tsx

import { useFocusEffect, useRouter } from 'expo-router';
import React, { useCallback, useEffect, useRef, useState } from 'react';
import {
  Alert,
  Animated,
  Image,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import Ionicons from 'react-native-vector-icons/Ionicons';
import { IP_ADDRESS } from '../utils/constants';

type Notification = {
  id: string;
  title: string;
  message: string;
  image_url?: string;
  date: string;
};

export default function Notifications() {
  const router = useRouter();
  const [items, setItems] = useState<Notification[]>([]);
  const [loading, setLoading] = useState(true);
  const firstLoad = useRef(true);

  // Profile overlay state
  const [showProfile, setShowProfile] = useState(false);
  const fadeAnim = useRef(new Animated.Value(0)).current;

  const collectorId = 1; // TODO: from auth/context
  const FETCH_URL = `${IP_ADDRESS}/aquabill-api/notifications.php?type=collector&user_id=${collectorId}`;

  const fetchNotifications = async () => {
    try {
      const resp = await fetch(FETCH_URL);
      const json = await resp.json();
      if (json.status === 'success') {
        setItems(json.data);
      } else {
        Alert.alert('❌ Failed', json.message || 'Could not load notifications.');
      }
    } catch {
      Alert.alert('⚠️ Network Error', 'Cannot connect to server.');
    } finally {
      if (firstLoad.current) {
        setLoading(false);
        firstLoad.current = false;
      }
    }
  };

  useEffect(() => {
    fetchNotifications();
    const iv = setInterval(fetchNotifications, 5000);
    return () => clearInterval(iv);
  }, []);

  useFocusEffect(
    useCallback(() => {
      fetchNotifications();
    }, [FETCH_URL])
  );

  // Profile handlers
  const openProfile = () => {
    setShowProfile(true);
    Animated.timing(fadeAnim, {
      toValue: 1,
      duration: 300,
      useNativeDriver: true,
    }).start();
  };
  const closeProfile = (path?: string) => {
    Animated.timing(fadeAnim, {
      toValue: 0,
      duration: 200,
      useNativeDriver: true,
    }).start(() => {
      setShowProfile(false);
      if (path) router.push(path);
    });
  };

  const renderItem = (item: Notification) => (
    <View key={item.id} style={styles.card}>
      <Text style={styles.cardTitle}>{item.title}</Text>
      <Text style={styles.cardMessage}>{item.message}</Text>
      {item.image_url && <Image source={{ uri: item.image_url }} style={styles.cardImage} />}
      <Text style={styles.cardDate}>{new Date(item.date).toLocaleString()}</Text>
    </View>
  );

  return (
    <SafeAreaView style={styles.container}>
      {/* Top Bar */}
      <View style={styles.topBar}>
        <Text style={styles.topBarText}>Notifications</Text>
        <TouchableOpacity onPress={openProfile}>
          <Ionicons name="person-circle" size={44} color="white" />
        </TouchableOpacity>
      </View>

      {/* Notification List */}
      <View style={styles.listContainer}>
        {loading ? (
          <Text style={styles.loadingText}>Loading…</Text>
        ) : items.length === 0 ? (
          <Text style={styles.emptyText}>No notifications.</Text>
        ) : (
          <ScrollView
            contentContainerStyle={styles.scrollContent}
            showsVerticalScrollIndicator={false}
          >
            {items.map(renderItem)}
          </ScrollView>
        )}
      </View>

      {/* Profile Overlay */}
      {showProfile && (
        <Animated.View style={[styles.overlay, { opacity: fadeAnim }]}>
          <View style={styles.modal}>
            <TouchableOpacity style={styles.closeBtn} onPress={() => closeProfile()}>
              <Ionicons name="close" size={24} color="black" />
            </TouchableOpacity>

            <View style={styles.profilePic}>
              <Ionicons name="person-circle-outline" size={100} color="#9ca3af" />
            </View>
            <Text style={styles.profileName}>Profile Name</Text>

            <TouchableOpacity style={styles.button} onPress={() => closeProfile('/help')}>
              <Ionicons name="help-circle-outline" size={24} color="white" style={styles.icon} />
              <Text style={styles.buttonText}>Help & Support</Text>
            </TouchableOpacity>

            <TouchableOpacity style={styles.button} onPress={() => closeProfile('/settings')}>
              <Ionicons name="settings-outline" size={24} color="white" style={styles.icon} />
              <Text style={styles.buttonText}>Settings</Text>
            </TouchableOpacity>

            <TouchableOpacity style={styles.button} onPress={() => closeProfile('/')}>
              <Ionicons name="log-out-outline" size={24} color="white" style={styles.icon} />
              <Text style={styles.buttonText}>Log Out</Text>
            </TouchableOpacity>
          </View>
        </Animated.View>
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#fff' },

  topBar: {
    height: 90,
    backgroundColor: '#0077B6',
    paddingHorizontal: 20,
    paddingTop: 30,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  topBarText: { color: 'white', fontSize: 24, fontWeight: 'bold' },

  listContainer: { flex: 1 },
  loadingText: { textAlign: 'center', marginTop: 20 },
  emptyText: { textAlign: 'center', marginTop: 20, color: '#555' },

  scrollContent: {
    padding: 16,
    paddingBottom: 100,     // extra bottom padding so last items are visible
  },

  card: {
    backgroundColor: '#f1f5f9',
    borderRadius: 8,
    padding: 12,
    marginBottom: 12,
  },
  cardTitle: { fontSize: 16, fontWeight: '600', marginBottom: 4 },
  cardMessage: { fontSize: 14, marginBottom: 8 },
  cardImage: {
    width: '100%',
    height: 200,
    borderRadius: 8,
    marginBottom: 8,
    resizeMode: 'cover',
  },
  cardDate: { fontSize: 12, color: '#777', textAlign: 'right' },

  overlay: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 999,
  },
  modal: {
    width: '80%',
    backgroundColor: 'white',
    padding: 20,
    borderRadius: 12,
    elevation: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 3 },
    shadowOpacity: 0.2,
    shadowRadius: 4,
    alignItems: 'center',
  },
  closeBtn: { alignSelf: 'flex-end' },
  profilePic: {
    width: 150,
    height: 150,
    borderRadius: 75,
    backgroundColor: '#f3f4f6',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 10,
  },
  profileName: { fontSize: 18, fontWeight: 'bold', marginBottom: 20 },
  button: {
    backgroundColor: '#0077B6',
    padding: 12,
    borderRadius: 8,
    flexDirection: 'row',
    alignItems: 'center',
    width: '100%',
    marginBottom: 10,
  },
  buttonText: { color: 'white', fontSize: 16, marginLeft: 10 },
  icon: { marginRight: 8 },
});
