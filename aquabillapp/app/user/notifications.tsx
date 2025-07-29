import Ionicons from '@expo/vector-icons/Ionicons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useFocusEffect, useRouter } from 'expo-router';
import React, { useCallback, useEffect, useRef, useState } from 'react';
import {
  Alert,
  Animated,
  FlatList,
  SafeAreaView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View
} from 'react-native';
import { IP_ADDRESS } from '../utils/constants';

type Notification = {
  id: string;
  title: string;
  message: string;
  date: string;
};

export default function NotificationScreen() {
  const router = useRouter();
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [loading, setLoading] = useState(true);
  const firstLoad = useRef(true);

  const fetchNotifications = async () => {
    try {
      const userType = await AsyncStorage.getItem('userType');
      const userId   = await AsyncStorage.getItem('userId');
      if (!userType || !userId) {
        throw new Error('Missing auth info');
      }

      const resp = await fetch(
        `${IP_ADDRESS}/aquabill-api/notifications.php` +
        `?type=${userType}&user_id=${userId}`
      );
      const json = await resp.json();
      if (json.status === 'success') {
        setNotifications(
          json.data.map((n: any) => ({
            id:      String(n.id),
            title:   n.title,
            message: n.message,
            date:    n.date,
          }))
        );
      } else {
        Alert.alert('❌ Failed', json.message);
      }
    } catch (err) {
      Alert.alert('⚠️ Error', err.message || 'Cannot connect to server.');
    } finally {
      if (firstLoad.current) {
        setLoading(false);
        firstLoad.current = false;
      }
    }
  };

  useEffect(() => {
    fetchNotifications();
    const iv = setInterval(fetchNotifications, 2000);
    return () => clearInterval(iv);
  }, []);

  useFocusEffect(
    useCallback(() => {
      fetchNotifications();
    }, [])
  );

  const [showProfile, setShowProfile] = useState(false);
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const openProfile = () => {
    setShowProfile(true);
    Animated.timing(fadeAnim, { toValue: 1, duration: 300, useNativeDriver: true }).start();
  };
  const closeProfile = (path?: string) => {
    Animated.timing(fadeAnim, { toValue: 0, duration: 200, useNativeDriver: true }).start(() => {
      setShowProfile(false);
      if (path) router.replace(path);
    });
  };

  const renderItem = ({ item }: { item: Notification }) => (
    <View style={styles.card}>
      <Text style={styles.cardTitle}>{item.title}</Text>
      <Text style={styles.cardMessage}>{item.message}</Text>
      <Text style={styles.cardDate}>{item.date}</Text>
    </View>
  );

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.topBar}>
        <Text style={styles.topBarText}>Notifications</Text>
        <TouchableOpacity onPress={openProfile}>
          <Ionicons name="person-circle" size={44} color="white" />
        </TouchableOpacity>
      </View>

      {loading ? (
        <Text style={styles.loadingText}>Loading notifications…</Text>
      ) : (
        <FlatList
          data={notifications}
          keyExtractor={i => i.id}
          contentContainerStyle={styles.list}
          renderItem={renderItem}
          ListEmptyComponent={
            <Text style={styles.emptyText}>No notifications yet.</Text>
          }
        />
      )}

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
  loadingText: { textAlign: 'center', marginTop: 20 },
  list: { padding: 20, paddingBottom: 40 },
  card: {
    backgroundColor: '#e0f0ff',
    padding: 15,
    borderRadius: 10,
    marginBottom: 15,
  },
  cardTitle: { fontWeight: 'bold', fontSize: 16, marginBottom: 5 },
  cardMessage: { marginBottom: 5 },
  cardDate: { fontSize: 12, color: '#555' },
  emptyText: { textAlign: 'center', marginTop: 50, color: '#999' },
  overlay: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'center', alignItems: 'center', zIndex: 999,
  },
  modal: {
    width: '90%', backgroundColor: 'white', borderRadius: 12,
    padding: 20, alignItems: 'center', elevation: 10,
    shadowColor: '#000', shadowOpacity: 0.2,
    shadowOffset: { width: 0, height: 3 }, shadowRadius: 4,
  },
  closeBtn: { alignSelf: 'flex-end' },
  profilePic: {
    width: 150, height: 150, borderRadius: 75,
    backgroundColor: '#f3f4f6', justifyContent: 'center',
    alignItems: 'center', marginBottom: 10,
  },
  profileName: { fontSize: 18, fontWeight: 'bold', marginBottom: 20 },
  button: {
    backgroundColor: '#0077B6', padding: 12,
    borderRadius: 8, flexDirection: 'row',
    alignItems: 'center', width: '100%', marginBottom: 10,
  },
  buttonText: { color: 'white', fontSize: 16, marginLeft: 10 },
  icon: { marginRight: 8 },
});
